<?php

declare(strict_types=1);

require_once __DIR__ . '/schema_proposal_task.php';

/**
 * @param callable(array<string,mixed>,array<string,string>):array{candidate_json:string,provider:string,model:string} $candidateProvider
 * @return array<string,mixed>
 */
function app_task_packet_local_fallback_run(string $taskPath, callable $candidateProvider): array
{
    $loaded = app_task_packet_local_fallback_load($taskPath);
    if (!$loaded['ok']) return $loaded;

    $task = $loaded['task'];
    $taskRoot = $loaded['task_root'];
    $inputResult = app_task_packet_local_fallback_read_inputs($task, $taskRoot);
    if (!$inputResult['ok']) return $inputResult;

    try {
        $provided = $candidateProvider($task, $inputResult['inputs']);
    } catch (Throwable $throwable) {
        return app_task_packet_local_fallback_result(false, 'provider_failed', [$throwable->getMessage()]);
    }
    $candidateJson = (string) ($provided['candidate_json'] ?? '');
    if ($candidateJson === '') return app_task_packet_local_fallback_result(false, 'provider_failed', ['empty_candidate']);

    $paths = app_task_packet_local_fallback_paths($task, $taskRoot);
    if (!$paths['ok']) return $paths;

    file_put_contents($paths['candidate_path'], $candidateJson . (str_ends_with($candidateJson, "\n") ? '' : "\n"));
    $validation = app_task_packet_local_fallback_validate_candidate($task, $candidateJson, $inputResult['inputs']);
    file_put_contents(
        $paths['validation_path'],
        json_encode(array_diff_key($validation, ['review_artifact_json' => true, 'derived_diff' => true]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
    );

    return array_merge(app_task_packet_local_fallback_result($validation['ok'], (string) $validation['stage'], $validation['errors']), [
        'provider' => (string) ($provided['provider'] ?? 'local-fallback'),
        'model' => (string) ($provided['model'] ?? ''),
        'advisory' => true,
        'candidate_path' => $paths['candidate_path'],
        'validation_path' => $paths['validation_path'],
        'candidate_sha256' => hash('sha256', $candidateJson),
        'review_artifact_sha256' => (string) ($validation['review_artifact_sha256'] ?? ''),
        'validation_pipeline' => is_array($validation['validation_pipeline'] ?? null) ? $validation['validation_pipeline'] : [],
    ]);
}

/** @return array<string,mixed> */
function app_task_packet_local_fallback_load(string $taskPath): array
{
    if ($taskPath === '' || !is_file($taskPath)) return app_task_packet_local_fallback_result(false, 'usage', ['task_required']);
    $taskRoot = realpath(dirname($taskPath));
    $taskReal = realpath($taskPath);
    if (!is_string($taskRoot) || !is_string($taskReal) || !str_starts_with($taskReal, $taskRoot . DIRECTORY_SEPARATOR)) {
        return app_task_packet_local_fallback_result(false, 'task_validation', ['task_outside_root']);
    }
    try {
        $task = json_decode((string) file_get_contents($taskReal), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return app_task_packet_local_fallback_result(false, 'task_validation', ['invalid_task_json']);
    }
    if (!is_array($task) || array_is_list($task)) return app_task_packet_local_fallback_result(false, 'task_validation', ['task_must_be_object']);
    $errors = app_task_packet_local_fallback_contract_errors($task);
    if ($errors !== []) return app_task_packet_local_fallback_result(false, 'task_validation', $errors);
    return ['ok' => true, 'stage' => 'task_loaded', 'errors' => [], 'task' => $task, 'task_root' => $taskRoot, 'mutation_performed' => false];
}

/** @param array<string,mixed> $task @return list<string> */
function app_task_packet_local_fallback_contract_errors(array $task): array
{
    $errors = [];
    if (($task['operation'] ?? '') !== 'schema_proposal_candidate') $errors[] = 'unsupported_task_operation';
    $schemaErrors = app_schema_proposal_task_contract_errors($task);
    foreach ($schemaErrors as $error) $errors[] = $error;
    $optional = is_array($task['optional_inputs'] ?? null) ? $task['optional_inputs'] : [];
    foreach (['fallback_candidate', 'fallback_validation'] as $key) {
        $declared = is_array($optional[$key] ?? null) ? $optional[$key] : [];
        if (($declared['authority'] ?? '') !== 'advisory') $errors[] = 'fallback_artifact_must_be_advisory:' . $key;
        if (!app_schema_proposal_task_relative_path_is_safe((string) ($declared['path'] ?? ''))) $errors[] = 'invalid_fallback_artifact_path:' . $key;
    }
    return array_values(array_unique($errors));
}

/** @param array<string,mixed> $task @return array<string,mixed> */
function app_task_packet_local_fallback_read_inputs(array $task, string $taskRoot): array
{
    $inputs = [];
    foreach (is_array($task['inputs'] ?? null) ? $task['inputs'] : [] as $key => $declared) {
        if (!is_array($declared)) continue;
        $path = realpath($taskRoot . DIRECTORY_SEPARATOR . (string) ($declared['path'] ?? ''));
        if (!is_string($path) || !str_starts_with($path, $taskRoot . DIRECTORY_SEPARATOR)) {
            return app_task_packet_local_fallback_result(false, 'input_integrity', ['invalid_task_input:' . (string) $key]);
        }
        $bytes = (string) file_get_contents($path);
        if (!hash_equals((string) ($declared['sha256'] ?? ''), hash('sha256', $bytes))) {
            return app_task_packet_local_fallback_result(false, 'input_integrity', ['task_input_hash_mismatch:' . (string) $key]);
        }
        $inputs[(string) $key] = $bytes;
    }
    foreach (['source', 'canonical'] as $required) {
        if (!array_key_exists($required, $inputs)) return app_task_packet_local_fallback_result(false, 'input_integrity', ['missing_input:' . $required]);
    }
    return ['ok' => true, 'stage' => 'inputs_loaded', 'errors' => [], 'inputs' => $inputs, 'mutation_performed' => false];
}

/** @param array<string,mixed> $task @return array<string,mixed> */
function app_task_packet_local_fallback_paths(array $task, string $taskRoot): array
{
    $optional = is_array($task['optional_inputs'] ?? null) ? $task['optional_inputs'] : [];
    $candidate = $taskRoot . DIRECTORY_SEPARATOR . (string) ($optional['fallback_candidate']['path'] ?? '');
    $validation = $taskRoot . DIRECTORY_SEPARATOR . (string) ($optional['fallback_validation']['path'] ?? '');
    foreach (['candidate' => $candidate, 'validation' => $validation] as $label => $path) {
        $dir = dirname($path);
        $realDir = is_dir($dir) ? realpath($dir) : false;
        if (!is_string($realDir) || !str_starts_with($realDir, $taskRoot . DIRECTORY_SEPARATOR)) {
            return app_task_packet_local_fallback_result(false, 'task_validation', ['invalid_fallback_artifact_directory:' . $label]);
        }
    }
    return ['ok' => true, 'stage' => 'fallback_paths_ready', 'errors' => [], 'candidate_path' => $candidate, 'validation_path' => $validation, 'mutation_performed' => false];
}

/** @param array<string,mixed> $task @param array<string,string> $inputs @return array<string,mixed> */
function app_task_packet_local_fallback_validate_candidate(array $task, string $candidateJson, array $inputs): array
{
    if (($task['operation'] ?? '') === 'schema_proposal_candidate') {
        return app_schema_proposal_task_validate($task, $candidateJson, (string) $inputs['source'], (string) $inputs['canonical'], [
            'candidate_authority' => 'advisory_fallback_candidate',
            'review_artifact_authority' => 'advisory_validation_result',
            'advisory' => true,
        ]);
    }
    return app_task_packet_local_fallback_result(false, 'candidate_validation', ['unsupported_task_operation']);
}

/** @param list<string> $errors @return array<string,mixed> */
function app_task_packet_local_fallback_result(bool $ok, string $stage, array $errors): array
{
    return [
        'ok' => $ok,
        'stage' => $stage,
        'errors' => array_values(array_unique($errors)),
        'provider' => '',
        'model' => '',
        'advisory' => true,
        'candidate_path' => '',
        'validation_path' => '',
        'candidate_sha256' => '',
        'review_artifact_sha256' => '',
        'mutation_performed' => false,
    ];
}
