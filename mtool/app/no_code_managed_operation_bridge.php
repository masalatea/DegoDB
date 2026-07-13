<?php

declare(strict_types=1);

require_once __DIR__ . '/managed_operation_sync.php';

/**
 * @param array<string,mixed> $runtimeIntent
 * @param array<string,mixed> $options
 * @return array{ok:bool,intent:array<string,mixed>|null,error:string}
 */
function app_no_code_managed_operation_sync_intent_from_runtime_action(
    array $runtimeIntent,
    array $options = [],
): array {
    if ((string) ($runtimeIntent['intent_version'] ?? '') !== 'no-code-runtime-action-intent-v0') {
        return [
            'ok' => false,
            'intent' => null,
            'error' => 'no-code managed operation bridge requires no-code runtime action intent v0.',
        ];
    }

    $projectKey = trim((string) ($runtimeIntent['project_key'] ?? ''));
    $operationKey = trim((string) ($runtimeIntent['operation_key'] ?? ''));
    $operationType = trim((string) ($runtimeIntent['operation_type'] ?? ''));
    if ($projectKey === '' || $operationKey === '' || $operationType === '') {
        return [
            'ok' => false,
            'intent' => null,
            'error' => 'no-code runtime action intent is missing project / operation metadata.',
        ];
    }

    $payload = is_array($runtimeIntent['payload'] ?? null) ? $runtimeIntent['payload'] : [];
    $plan = [
        'execution_mode' => 'plan-only',
        'project_key' => $projectKey,
        'operation_key' => $operationKey,
        'operation_type' => $operationType,
        'contract_key' => (string) ($options['contract_key'] ?? $runtimeIntent['contract_key'] ?? ''),
        'key' => is_array($payload['key'] ?? null) ? $payload['key'] : [],
        'input' => is_array($payload['input'] ?? null) ? $payload['input'] : [],
        'filter' => is_array($payload['filter'] ?? null) ? $payload['filter'] : [],
        'output_fields' => is_array($options['output_fields'] ?? null) ? array_values($options['output_fields']) : [],
    ];

    return app_managed_operation_sync_intent_from_plan($plan, [
        'storage_mode' => (string) ($options['storage_mode'] ?? 'local-copy'),
        'origin' => (string) ($options['origin'] ?? 'app-local'),
        'target' => (string) ($options['target'] ?? 'server'),
        'actor' => is_array($options['actor'] ?? null) ? $options['actor'] : [],
    ]);
}

/**
 * @param array<string,mixed> $options
 * @param callable(array<string,mixed>):array<string,mixed> $executor
 * @return callable(array<string,mixed>):array<string,mixed>
 */
function app_no_code_managed_operation_dispatcher(
    array $options,
    callable $executor,
): callable {
    return static function (array $runtimeIntent) use ($options, $executor): array {
        $syncIntent = app_no_code_managed_operation_sync_intent_from_runtime_action($runtimeIntent, $options);
        if (!$syncIntent['ok'] || $syncIntent['intent'] === null) {
            return [
                'ok' => false,
                'executed' => false,
                'sync_intent' => null,
                'executor_result' => null,
                'error' => $syncIntent['error'],
            ];
        }

        $executorResult = $executor($syncIntent['intent']);

        return [
            'ok' => (bool) ($executorResult['ok'] ?? false),
            'executed' => (bool) ($executorResult['executed'] ?? false),
            'sync_intent' => $syncIntent['intent'],
            'executor_result' => $executorResult,
            'error' => (string) ($executorResult['error'] ?? ''),
        ];
    };
}
