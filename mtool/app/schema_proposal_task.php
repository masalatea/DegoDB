<?php

declare(strict_types=1);

require_once __DIR__ . '/schema_proposal.php';

const APP_SCHEMA_PROPOSAL_TASK_VERSION = 'ai-schema-proposal-task-v0';
const APP_SCHEMA_PROPOSAL_DIFF_DERIVATION_VERSION = 'mtool-canonical-diff-v0';

/**
 * @param array<string,mixed> $task
 * @return array<string,mixed>
 */
function app_schema_proposal_task_validate(
    array $task,
    string $candidateJson,
    string $sourceBytes,
    string $canonicalSnapshotBytes,
    array $validationContext = [],
): array {
    $candidateHash = hash('sha256', $candidateJson);
    $errors = app_schema_proposal_task_contract_errors($task);
    if ($errors !== []) return app_schema_proposal_task_result(false, 'task_validation', $errors, $candidateHash, $validationContext);

    $sourceHash = hash('sha256', $sourceBytes);
    $canonicalHash = hash('sha256', $canonicalSnapshotBytes);
    $integrityErrors = [];
    if (!hash_equals((string) $task['inputs']['source']['sha256'], $sourceHash)) $integrityErrors[] = 'task_source_sha256_mismatch';
    if (!hash_equals((string) $task['inputs']['canonical']['sha256'], $canonicalHash)) $integrityErrors[] = 'task_canonical_sha256_mismatch';
    try {
        $snapshot = json_decode($canonicalSnapshotBytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $snapshot = [];
        $integrityErrors[] = 'invalid_canonical_json';
    }
    if (!is_array($snapshot) || array_is_list($snapshot)) $integrityErrors[] = 'canonical_must_be_object';
    if ($integrityErrors !== []) return app_schema_proposal_task_result(false, 'input_integrity', $integrityErrors, $candidateHash, $validationContext);

    $decoded = app_schema_proposal_decode($candidateJson);
    if (!$decoded['ok']) {
        $decodeStage = str_starts_with((string) ($decoded['errors'][0] ?? ''), 'invalid_json:') || in_array('proposal_must_be_object', $decoded['errors'], true)
            ? 'candidate_decode'
            : 'candidate_validation';
        return app_schema_proposal_task_result(false, $decodeStage, $decoded['errors'], $candidateHash, $validationContext);
    }
    $candidate = $decoded['proposal'];
    $candidateErrors = [];
    if (($candidate['project_key'] ?? '') !== $task['project_key']) $candidateErrors[] = 'candidate_project_mismatch';
    if (($candidate['source']['sha256'] ?? '') !== $sourceHash) $candidateErrors[] = 'candidate_source_sha256_mismatch';
    $provenance = is_array($candidate['provenance'] ?? null) ? $candidate['provenance'] : [];
    if (($provenance['ai_authored'] ?? null) !== true) $candidateErrors[] = 'candidate_ai_authored_required';
    if (($candidate['canonical_diff'] ?? null) !== []) $candidateErrors[] = 'candidate_canonical_diff_must_be_empty';
    if ($candidateErrors !== []) return app_schema_proposal_task_result(false, 'candidate_validation', $candidateErrors, $candidateHash, $validationContext);

    $derived = app_schema_proposal_derive_canonical_diff($candidate, $snapshot);
    if (!$derived['ok']) return app_schema_proposal_task_result(false, 'canonical_diff_derivation', $derived['errors'], $candidateHash, $validationContext);

    $reviewArtifact = $candidate;
    $reviewArtifact['canonical_diff'] = $derived['diff'];
    $reviewArtifact['canonical_diff_derivation'] = [
        'kind' => 'mtool_derived',
        'version' => APP_SCHEMA_PROPOSAL_DIFF_DERIVATION_VERSION,
        'candidate_sha256' => $candidateHash,
        'canonical_snapshot_sha256' => $canonicalHash,
    ];
    $verification = app_schema_proposal_verify_declared_diff($reviewArtifact, $snapshot);
    if (!$verification['ok']) return app_schema_proposal_task_result(false, 'review_artifact_validation', $verification['errors'], $candidateHash, $validationContext);

    $reviewJson = json_encode($reviewArtifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    return array_merge(app_schema_proposal_task_result(true, 'review_artifact_ready', [], $candidateHash, $validationContext), [
        'review_artifact_sha256' => hash('sha256', $reviewJson),
        'review_artifact_json' => $reviewJson,
        'derived_diff' => $derived['diff'],
    ]);
}

/** @param array<string,mixed> $task @return list<string> */
function app_schema_proposal_task_contract_errors(array $task): array
{
    $errors = [];
    if (($task['task_version'] ?? '') !== APP_SCHEMA_PROPOSAL_TASK_VERSION) $errors[] = 'unsupported_task_version';
    foreach (['task_id', 'project_key'] as $key) if (trim((string) ($task[$key] ?? '')) === '') $errors[] = 'missing_' . $key;
    if (($task['operation'] ?? '') !== 'schema_proposal_candidate') $errors[] = 'unsupported_task_operation';
    if (($task['state'] ?? '') !== 'pending_user_confirmation') $errors[] = 'invalid_task_state';
    foreach (['source', 'canonical', 'output_shape', 'scan'] as $key) {
        $input = is_array($task['inputs'][$key] ?? null) ? $task['inputs'][$key] : [];
        if (!app_schema_proposal_task_relative_path_is_safe((string) ($input['path'] ?? ''))) $errors[] = 'invalid_' . $key . '_path';
        if (preg_match('/^[a-f0-9]{64}$/', (string) ($input['sha256'] ?? '')) !== 1) $errors[] = 'invalid_' . $key . '_sha256';
    }
    foreach (['candidate', 'validation', 'review_artifact'] as $key) {
        if (!app_schema_proposal_task_relative_path_is_safe((string) ($task['outputs'][$key] ?? ''))) $errors[] = 'invalid_' . $key . '_output_path';
    }
    if (($task['confirmation']['required'] ?? null) !== true || trim((string) ($task['confirmation']['prompt'] ?? '')) === '') $errors[] = 'confirmation_required';
    $command = $task['validation']['command_argv'] ?? null;
    if (!is_array($command) || $command === [] || ($command[0] ?? '') !== 'php' || ($command[1] ?? '') !== 'mtool/scripts/validate_schema_proposal_task.php') $errors[] = 'invalid_validation_command';
    if (($task['validation']['success_stage'] ?? '') !== 'review_artifact_ready') $errors[] = 'invalid_validation_success_stage';
    $prohibitions = is_array($task['prohibitions'] ?? null) ? $task['prohibitions'] : [];
    foreach (['database_write', 'config_write', 'sql', 'import', 'apply', 'build', 'publish', 'network'] as $key) {
        if (($prohibitions[$key] ?? null) !== true) $errors[] = 'missing_prohibition:' . $key;
    }
    return array_values(array_unique($errors));
}

function app_schema_proposal_task_relative_path_is_safe(string $path): bool
{
    return $path !== '' && !str_starts_with($path, '/') && preg_match('#(^|/)\.\.(/|$)#', $path) !== 1 && !str_contains($path, "\0");
}

/** @param list<string> $errors @param array<string,mixed> $validationContext @return array<string,mixed> */
function app_schema_proposal_task_result(bool $ok, string $stage, array $errors, string $candidateHash, array $validationContext = []): array
{
    return [
        'ok' => $ok,
        'stage' => $stage,
        'errors' => array_values(array_unique($errors)),
        'candidate_sha256' => $candidateHash,
        'review_artifact_sha256' => '',
        'validation_pipeline' => [
            'validator' => 'app_schema_proposal_task_validate',
            'task_version' => APP_SCHEMA_PROPOSAL_TASK_VERSION,
            'candidate_authority' => (string) ($validationContext['candidate_authority'] ?? 'formal_candidate'),
            'review_artifact_authority' => (string) ($validationContext['review_artifact_authority'] ?? 'mtool_derived_formal_review'),
            'advisory' => (bool) ($validationContext['advisory'] ?? false),
        ],
        'mutation_performed' => false,
    ];
}
