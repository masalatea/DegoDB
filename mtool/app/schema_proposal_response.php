<?php

declare(strict_types=1);

require_once __DIR__ . '/schema_proposal.php';

const APP_SCHEMA_PROPOSAL_RESPONSE_ATTEMPT_VERSION = 'schema-proposal-response-attempt-v0';

/**
 * @param array<string,mixed> $run
 * @return array{ok:bool,status:string,attempt:array<string,mixed>,proposal:array<string,mixed>,derived_diff:list<array<string,mixed>>,errors:list<string>}
 */
function app_schema_proposal_response_accept(
    string $responseBytes,
    array $run,
    string $sourceBytes,
    string $canonicalBytes,
): array {
    $errors = app_schema_proposal_response_validate_run($run);
    $attempt = [
        'attempt_version' => APP_SCHEMA_PROPOSAL_RESPONSE_ATTEMPT_VERSION,
        'provider' => trim((string) ($run['provider'] ?? '')),
        'model' => trim((string) ($run['model'] ?? '')),
        'prompt_template_sha256' => strtolower((string) ($run['prompt_template_sha256'] ?? '')),
        'prompt_final_sha256' => strtolower((string) ($run['prompt_final_sha256'] ?? '')),
        'source_sha256' => hash('sha256', $sourceBytes),
        'canonical_sha256' => hash('sha256', $canonicalBytes),
        'attempt_number' => $run['attempt_number'] ?? null,
        'generated_at' => (string) ($run['generated_at'] ?? ''),
        'provider_request_id' => trim((string) ($run['provider_request_id'] ?? '')),
        'response_sha256' => hash('sha256', $responseBytes),
        'response_byte_length' => strlen($responseBytes),
        'network_performed_here' => false,
        'credential_accessed_here' => false,
        'persisted' => false,
        'mutation_performed' => false,
    ];

    $decoded = app_schema_proposal_decode($responseBytes);
    if (!$decoded['ok']) {
        $errors = array_merge($errors, $decoded['errors']);
    }
    $proposal = $decoded['proposal'];
    if ($proposal !== []) {
        if (($proposal['project_key'] ?? '') !== 'SAMPLE19') {
            $errors[] = 'project_must_be_sample19';
        }
        if (($proposal['source']['sha256'] ?? '') !== $attempt['source_sha256']) {
            $errors[] = 'source_sha256_mismatch';
        }
        if (($proposal['source']['logical_filename'] ?? '') !== 'article.json'
            || ($proposal['source']['root_pointer'] ?? '') !== '/article') {
            $errors[] = 'unexpected_source_identity';
        }
        $provenance = is_array($proposal['provenance'] ?? null) ? $proposal['provenance'] : [];
        if (($provenance['kind'] ?? '') !== 'ai_generated_proposal' || ($provenance['ai_authored'] ?? null) !== true) {
            $errors[] = 'ai_provenance_required';
        }
        if (($proposal['created_at'] ?? '') !== $attempt['generated_at']) {
            $errors[] = 'generated_at_mismatch';
        }
    }

    $snapshot = app_schema_proposal_response_decode_snapshot($canonicalBytes, $errors);
    $derivedDiff = [];
    if ($proposal !== [] && $snapshot !== [] && $decoded['ok']) {
        $verification = app_schema_proposal_verify_declared_diff($proposal, $snapshot);
        $derivedDiff = $verification['derived_diff'];
        if (!$verification['ok']) {
            $errors = array_merge($errors, $verification['errors']);
        }
    }

    $errors = array_values(array_unique($errors));
    $ok = $errors === [];
    $attempt['acceptance_status'] = $ok ? 'accepted' : 'rejected';
    $attempt['errors'] = $errors;

    return [
        'ok' => $ok,
        'status' => $ok ? 'accepted_for_read_only_review' : 'rejected',
        'attempt' => $attempt,
        'proposal' => $ok ? $proposal : [],
        'derived_diff' => $ok ? $derivedDiff : [],
        'errors' => $errors,
    ];
}

/** @param array<string,mixed> $run @return list<string> */
function app_schema_proposal_response_validate_run(array $run): array
{
    $errors = [];
    foreach (['provider', 'model', 'generated_at'] as $key) {
        if (trim((string) ($run[$key] ?? '')) === '') {
            $errors[] = 'missing_run_' . $key;
        }
    }
    foreach (['prompt_template_sha256', 'prompt_final_sha256'] as $key) {
        if (preg_match('/^[a-f0-9]{64}$/', strtolower((string) ($run[$key] ?? ''))) !== 1) {
            $errors[] = 'invalid_run_' . $key;
        }
    }
    if (!is_int($run['attempt_number'] ?? null) || !in_array($run['attempt_number'], [1, 2], true)) {
        $errors[] = 'invalid_run_attempt_number';
    }
    return $errors;
}

/** @param list<string> $errors @return array<string,mixed> */
function app_schema_proposal_response_decode_snapshot(string $bytes, array &$errors): array
{
    try {
        $snapshot = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $errors[] = 'invalid_canonical_json';
        return [];
    }
    if (!is_array($snapshot) || array_is_list($snapshot)) {
        $errors[] = 'canonical_must_be_object';
        return [];
    }
    return $snapshot;
}
