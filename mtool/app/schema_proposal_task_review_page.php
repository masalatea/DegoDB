<?php

declare(strict_types=1);

require_once __DIR__ . '/project_shared_contract_route_common.php';
require_once __DIR__ . '/schema_proposal_review_page.php';
require_once __DIR__ . '/schema_proposal_task.php';

function app_schema_proposal_task_review_enabled(): bool
{
    return in_array(strtolower(trim((string) getenv('MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED'))), ['1', 'true', 'yes', 'on'], true);
}

/** @return array<string,mixed> */
function app_schema_proposal_task_review_load(string $taskId, ?string $tasksRoot = null): array
{
    if (preg_match('/^sample19-schema-proposal-[a-f0-9]{12}$/', $taskId) !== 1) return app_schema_proposal_review_error(404, 'invalid_task_id');
    $tasksRoot ??= dirname(__DIR__, 2) . '/work/ai-tasks';
    $base = realpath($tasksRoot); $root = realpath($tasksRoot . '/' . $taskId);
    if (!is_string($base) || !is_string($root) || !str_starts_with($root, $base . DIRECTORY_SEPARATOR)) return app_schema_proposal_review_error(404, 'task_not_found');
    $taskPath = $root . '/task.json';
    if (!is_readable($taskPath)) return app_schema_proposal_review_error(500, 'task_unreadable');
    try { $task = json_decode((string) file_get_contents($taskPath), true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException) { return app_schema_proposal_review_error(500, 'task_invalid_json'); }
    if (!is_array($task) || ($task['task_id'] ?? '') !== $taskId) return app_schema_proposal_review_error(500, 'task_identity_mismatch');
    $contractErrors = app_schema_proposal_task_contract_errors($task);
    if ($contractErrors !== []) return app_schema_proposal_review_error(500, 'task_invalid:' . implode(',', $contractErrors));

    $readDeclared = static function (string $relative) use ($root): string|false {
        $real = realpath($root . '/' . $relative);
        return is_string($real) && str_starts_with($real, $root . DIRECTORY_SEPARATOR) && is_file($real) ? (string) file_get_contents($real) : false;
    };
    $bytes = [];
    foreach (['source', 'canonical', 'output_shape', 'scan'] as $key) {
        $bytes[$key] = $readDeclared((string) $task['inputs'][$key]['path']);
        if (!is_string($bytes[$key])) return app_schema_proposal_review_error(500, 'task_input_unreadable:' . $key);
        if (!hash_equals((string) $task['inputs'][$key]['sha256'], hash('sha256', $bytes[$key]))) return app_schema_proposal_review_error(409, 'task_input_hash_mismatch:' . $key);
    }
    $candidate = $readDeclared((string) $task['outputs']['candidate']);
    $validationJson = $readDeclared((string) $task['outputs']['validation']);
    $reviewJson = $readDeclared((string) $task['outputs']['review_artifact']);
    if (!is_string($candidate) || !is_string($validationJson) || !is_string($reviewJson)) return app_schema_proposal_review_error(404, 'task_review_artifact_not_ready');
    try { $validation = json_decode($validationJson, true, 512, JSON_THROW_ON_ERROR); $snapshot = json_decode($bytes['canonical'], true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException) { return app_schema_proposal_review_error(500, 'task_review_metadata_invalid_json'); }
    if (!is_array($validation) || ($validation['ok'] ?? null) !== true || ($validation['stage'] ?? '') !== 'review_artifact_ready' || ($validation['mutation_performed'] ?? null) !== false) return app_schema_proposal_review_error(409, 'task_validation_not_ready');
    $candidateHash = hash('sha256', $candidate); $reviewHash = hash('sha256', $reviewJson);
    if (!hash_equals((string) ($validation['candidate_sha256'] ?? ''), $candidateHash)) return app_schema_proposal_review_error(409, 'task_candidate_hash_mismatch');
    if (!hash_equals((string) ($validation['review_artifact_sha256'] ?? ''), $reviewHash)) return app_schema_proposal_review_error(409, 'task_review_artifact_hash_mismatch');
    $decoded = app_schema_proposal_decode($reviewJson);
    if (!$decoded['ok']) return app_schema_proposal_review_error(500, 'task_review_artifact_invalid:' . implode(',', $decoded['errors']));
    $proposal = $decoded['proposal']; $derivation = is_array($proposal['canonical_diff_derivation'] ?? null) ? $proposal['canonical_diff_derivation'] : [];
    if (($derivation['kind'] ?? '') !== 'mtool_derived' || ($derivation['candidate_sha256'] ?? '') !== $candidateHash || ($derivation['canonical_snapshot_sha256'] ?? '') !== hash('sha256', $bytes['canonical'])) return app_schema_proposal_review_error(409, 'task_derivation_mismatch');
    if (($proposal['source']['sha256'] ?? '') !== hash('sha256', $bytes['source'])) return app_schema_proposal_review_error(409, 'source_hash_mismatch');
    $verification = app_schema_proposal_verify_declared_diff($proposal, is_array($snapshot) ? $snapshot : []);
    if (!$verification['ok']) return app_schema_proposal_review_error(409, implode(',', $verification['errors']));
    return ['ok' => true, 'status_code' => 200, 'error' => '', 'proposal' => $proposal, 'diff' => $verification['derived_diff'], 'source_hash' => hash('sha256', $bytes['source']), 'page_state' => app_schema_proposal_review_page_state($verification['derived_diff']), 'task_id' => $taskId, 'candidate_hash' => $candidateHash, 'review_artifact_hash' => $reviewHash];
}

function app_schema_proposal_task_review_html(array $review): string
{
    $html = app_schema_proposal_review_html($review);
    $section = '<section data-schema-proposal-task-evidence="true"><h2>Task evidence</h2><dl><dt>Task</dt><dd><code>' . app_schema_proposal_review_h((string) $review['task_id']) . '</code></dd><dt>Candidate SHA-256</dt><dd><code data-candidate-hash-verified="true">' . app_schema_proposal_review_h((string) $review['candidate_hash']) . '</code></dd><dt>Review artifact SHA-256</dt><dd><code data-review-artifact-hash-verified="true">' . app_schema_proposal_review_h((string) $review['review_artifact_hash']) . '</code></dd><dt>Canonical diff</dt><dd data-canonical-diff-owner="mtool">Mtool-derived</dd></dl></section>';
    return str_replace('<section><h2>Entity candidates and evidence</h2>', $section . '<section><h2>Entity candidates and evidence</h2>', $html);
}

function app_render_schema_proposal_task_review_page(array $app, array $request): void
{
    if (!app_schema_proposal_task_review_enabled()) { app_render_not_found_page($app, $request); return; }
    $bootstrap = app_project_shared_contract_route_bootstrap($app, $request, ['GET']); if ($bootstrap === null) return;
    if ($bootstrap['project_key'] !== 'SAMPLE19') { app_render_not_found_page($app, $request); return; }
    $review = app_schema_proposal_task_review_load(app_route_param($request, 'task_id'));
    if (!$review['ok']) { app_send_html_response_headers($request, $review['status_code']); echo '<!doctype html><html><body><main data-schema-proposal-task-review-error="true"><h1>Task Review Error</h1><code>' . app_schema_proposal_review_h($review['error']) . '</code></main></body></html>'; return; }
    app_send_html_response_headers($request, 200); echo app_schema_proposal_task_review_html($review);
}
