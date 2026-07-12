<?php
declare(strict_types=1);

require_once __DIR__ . '/project_shared_contract_route_common.php';
require_once __DIR__ . '/schema_proposal.php';

const APP_SCHEMA_PROPOSAL_SAMPLE19_ID = 'sample19-article-content-model-v1';

function app_schema_proposal_review_enabled(): bool
{
    return in_array(strtolower(trim((string) getenv('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED'))), ['1', 'true', 'yes', 'on'], true);
}

/** @return array{source:string,proposal:string,snapshot:string} */
function app_schema_proposal_sample19_paths(): array
{
    $root = dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
    return ['source' => $root . '/proposal/source/article.json', 'proposal' => $root . '/golden/schema-proposal.json', 'snapshot' => $root . '/golden/canonical-schema-snapshot.json'];
}

/** @param array{source:string,proposal:string,snapshot:string}|null $paths @return array<string,mixed> */
function app_schema_proposal_review_load(?array $paths = null): array
{
    $paths ??= app_schema_proposal_sample19_paths();
    foreach (['source', 'proposal', 'snapshot'] as $key) {
        if (!is_readable($paths[$key] ?? '')) return app_schema_proposal_review_error(500, 'fixture_unreadable:' . $key);
    }
    $source = file_get_contents($paths['source']);
    $proposalJson = file_get_contents($paths['proposal']);
    $snapshotJson = file_get_contents($paths['snapshot']);
    if (!is_string($source) || !is_string($proposalJson) || !is_string($snapshotJson)) return app_schema_proposal_review_error(500, 'fixture_read_failed');
    $decoded = app_schema_proposal_decode($proposalJson);
    if (!$decoded['ok']) return app_schema_proposal_review_error(500, 'proposal_invalid:' . implode(',', $decoded['errors']));
    try {
        $snapshot = json_decode($snapshotJson, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return app_schema_proposal_review_error(500, 'snapshot_invalid_json');
    }
    if (!is_array($snapshot)) return app_schema_proposal_review_error(500, 'snapshot_must_be_object');
    $proposal = $decoded['proposal'];
    $sourceHash = hash('sha256', $source);
    if (!hash_equals((string) ($proposal['source']['sha256'] ?? ''), $sourceHash)) return app_schema_proposal_review_error(500, 'source_hash_mismatch');
    $verification = app_schema_proposal_verify_declared_diff($proposal, $snapshot);
    if (!$verification['ok']) {
        $status = in_array('declared_canonical_diff_mismatch', $verification['errors'], true) ? 409 : 500;
        return app_schema_proposal_review_error($status, implode(',', $verification['errors']));
    }
    return ['ok' => true, 'status_code' => 200, 'error' => '', 'proposal' => $proposal, 'diff' => $verification['derived_diff'], 'source_hash' => $sourceHash, 'page_state' => app_schema_proposal_review_page_state($verification['derived_diff'])];
}

/** @return array<string,mixed> */
function app_schema_proposal_review_error(int $status, string $error): array
{
    return ['ok' => false, 'status_code' => $status, 'error' => $error, 'proposal' => [], 'diff' => [], 'source_hash' => '', 'page_state' => 'invalid'];
}

/** @param list<array<string,mixed>> $diff */
function app_schema_proposal_review_page_state(array $diff): string
{
    $categories = array_column($diff, 'category');
    if (in_array('remove', $categories, true) || in_array('conflict', $categories, true)) return 'blocking';
    return in_array('change', $categories, true) ? 'review_required' : 'reviewable';
}

function app_schema_proposal_review_diff_severity(string $category): string
{
    return match ($category) {'remove', 'conflict' => 'blocking', 'change' => 'review_required', 'add' => 'info', default => 'ok'};
}

/** @param array<string,mixed> $review */
function app_schema_proposal_review_html(array $review): string
{
    if (!($review['ok'] ?? false)) throw new InvalidArgumentException('A valid review payload is required.');
    $proposal = $review['proposal'];
    $entityRows = '';
    foreach ($proposal['entities'] as $entity) {
        $signature = app_schema_proposal_entity_signature($entity, $proposal['relationships']);
        $evidence = '';
        foreach ($entity['evidence'] as $item) $evidence .= '<li><code>' . app_schema_proposal_review_h((string) $item['pointer']) . '</code> — ' . app_schema_proposal_review_h((string) $item['rationale']) . '</li>';
        $entityRows .= '<tr data-proposal-entity="' . app_schema_proposal_review_h((string) $entity['entity_key']) . '"><th><code>' . app_schema_proposal_review_h((string) $entity['entity_key']) . '</code></th><td>' . app_schema_proposal_review_h((string) $entity['purpose']) . '</td><td><code>' . app_schema_proposal_review_h(implode(', ', $signature['field_keys'])) . '</code></td><td><code>' . app_schema_proposal_review_h(implode(', ', $signature['key_keys'])) . '</code></td><td><code>' . app_schema_proposal_review_h(implode(', ', $signature['relationship_keys'])) . '</code></td><td><ul>' . $evidence . '</ul></td></tr>';
    }
    $diffRows = '';
    foreach ($review['diff'] as $diff) {
        $category = (string) $diff['category'];
        $diffRows .= '<tr data-proposal-diff-category="' . app_schema_proposal_review_h($category) . '" data-proposal-diff-severity="' . app_schema_proposal_review_h(app_schema_proposal_review_diff_severity($category)) . '"><td><code>' . app_schema_proposal_review_h($category) . '</code></td><th><code>' . app_schema_proposal_review_h((string) $diff['object_key']) . '</code></th><td><code>' . app_schema_proposal_review_h(json_encode($diff['proposal_value'], JSON_UNESCAPED_SLASHES) ?: 'null') . '</code></td><td><code>' . app_schema_proposal_review_h(json_encode($diff['canonical_value'], JSON_UNESCAPED_SLASHES) ?: 'null') . '</code></td><td>' . app_schema_proposal_review_h((string) $diff['review_note']) . '</td></tr>';
    }
    $questions = '';
    foreach ($proposal['blocking_questions'] as $item) $questions .= '<li data-proposal-blocking-question="' . app_schema_proposal_review_h((string) $item['question_key']) . '"><strong>' . app_schema_proposal_review_h((string) $item['question_key']) . '</strong>: ' . app_schema_proposal_review_h((string) $item['question']) . '</li>';
    $assumptions = '';
    foreach ($proposal['non_blocking_assumptions'] as $item) $assumptions .= '<li data-proposal-assumption="' . app_schema_proposal_review_h((string) $item['assumption_key']) . '"><strong>' . app_schema_proposal_review_h((string) $item['assumption_key']) . '</strong>: ' . app_schema_proposal_review_h((string) $item['assumption']) . '</li>';
    $source = $proposal['source']; $provenance = $proposal['provenance'];
    return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sample19 Schema Proposal Review</title></head><body><main data-schema-proposal-review="true" data-schema-proposal-review-state="' . app_schema_proposal_review_h((string) $review['page_state']) . '"><h1>Sample19 Schema Proposal Review</h1>'
        . '<section><h2>Safety</h2><ul><li data-proposal-state="proposal_only">Proposal only</li><li data-apply-supported="false">Apply is not supported</li><li data-review-mutation="none">Read-only; no mutation</li><li data-ai-authored="false">Deterministic fixture; not AI-authored</li></ul></section>'
        . '<section><h2>Source and provenance</h2><dl><dt>Proposal</dt><dd><code>' . app_schema_proposal_review_h((string) $proposal['proposal_id']) . '</code></dd><dt>Project</dt><dd><code>SAMPLE19</code></dd><dt>Version</dt><dd><code>' . app_schema_proposal_review_h((string) $proposal['proposal_version']) . '</code></dd><dt>Created</dt><dd>' . app_schema_proposal_review_h((string) $proposal['created_at']) . '</dd><dt>Filename</dt><dd>' . app_schema_proposal_review_h((string) $source['logical_filename']) . '</dd><dt>Media type</dt><dd><code>' . app_schema_proposal_review_h((string) $source['media_type']) . '</code></dd><dt>SHA-256</dt><dd><code data-source-hash-verified="true">' . app_schema_proposal_review_h((string) $review['source_hash']) . '</code></dd><dt>Root pointer</dt><dd><code>' . app_schema_proposal_review_h((string) $source['root_pointer']) . '</code></dd><dt>Redaction</dt><dd>' . app_schema_proposal_review_h((string) $source['redaction']) . '</dd><dt>Provenance</dt><dd><code>' . app_schema_proposal_review_h((string) $provenance['kind']) . '</code></dd><dt>Generator</dt><dd><code>' . app_schema_proposal_review_h((string) $provenance['generator']) . '</code></dd></dl></section>'
        . '<section><h2>Entity candidates and evidence</h2><table><thead><tr><th>Entity</th><th>Purpose</th><th>Fields</th><th>Keys</th><th>Relationships</th><th>Evidence</th></tr></thead><tbody>' . $entityRows . '</tbody></table></section>'
        . '<section><h2>Derived canonical diff</h2><p>Review state: <strong>' . app_schema_proposal_review_h((string) $review['page_state']) . '</strong></p><table><thead><tr><th>Category</th><th>Entity</th><th>Proposal signature</th><th>Canonical signature</th><th>Review note</th></tr></thead><tbody>' . $diffRows . '</tbody></table></section>'
        . '<section><h2>Blocking questions</h2><ul>' . $questions . '</ul></section><section><h2>Non-blocking assumptions</h2><ul>' . $assumptions . '</ul></section><p><a data-schema-proposal-return href="/projects/SAMPLE19">Return to Sample19 project</a></p></main></body></html>';
}

function app_schema_proposal_review_h(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function app_render_schema_proposal_review_page(array $app, array $request): void
{
    if (!app_schema_proposal_review_enabled()) { app_render_not_found_page($app, $request); return; }
    $bootstrap = app_project_shared_contract_route_bootstrap($app, $request, ['GET']);
    if ($bootstrap === null) return;
    if ($bootstrap['project_key'] !== 'SAMPLE19' || app_route_param($request, 'proposal_id') !== APP_SCHEMA_PROPOSAL_SAMPLE19_ID) { app_render_not_found_page($app, $request); return; }
    $review = app_schema_proposal_review_load();
    if (!$review['ok']) {
        app_send_html_response_headers($request, $review['status_code']);
        echo '<!doctype html><html lang="en"><body><main data-schema-proposal-integrity-error="true"><h1>Schema Proposal Review Integrity Error</h1><code>' . app_schema_proposal_review_h($review['error']) . '</code></main></body></html>';
        return;
    }
    app_send_html_response_headers($request, 200); echo app_schema_proposal_review_html($review);
}
