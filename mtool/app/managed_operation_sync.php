<?php

declare(strict_types=1);

/**
 * @param array<string,mixed> $plan
 * @param array<string,mixed> $options
 * @return array{ok:bool,intent:array<string,mixed>|null,error:string}
 */
function app_managed_operation_sync_intent_from_plan(array $plan, array $options = []): array
{
    if ((string) ($plan['execution_mode'] ?? '') !== 'plan-only') {
        return [
            'ok' => false,
            'intent' => null,
            'error' => 'managed operation sync intent requires a plan-only execution plan.',
        ];
    }

    $storageMode = app_managed_operation_sync_storage_mode((string) ($options['storage_mode'] ?? 'local-copy'));
    if ($storageMode === '') {
        return [
            'ok' => false,
            'intent' => null,
            'error' => 'managed operation sync storage mode is invalid.',
        ];
    }

    $origin = app_managed_operation_sync_endpoint((string) ($options['origin'] ?? 'app-local'));
    $target = app_managed_operation_sync_endpoint((string) ($options['target'] ?? 'server'));
    if ($origin === '' || $target === '') {
        return [
            'ok' => false,
            'intent' => null,
            'error' => 'managed operation sync endpoint is invalid.',
        ];
    }

    $intent = [
        'intent_version' => 'managed-operation-sync-intent-v0',
        'status' => 'pending',
        'storage_mode' => $storageMode,
        'origin' => $origin,
        'target' => $target,
        'project_key' => (string) ($plan['project_key'] ?? ''),
        'operation_key' => (string) ($plan['operation_key'] ?? ''),
        'operation_type' => (string) ($plan['operation_type'] ?? ''),
        'contract_key' => (string) ($plan['contract_key'] ?? ''),
        'payload' => [
            'key' => is_array($plan['key'] ?? null) ? $plan['key'] : [],
            'input' => is_array($plan['input'] ?? null) ? $plan['input'] : [],
            'filter' => is_array($plan['filter'] ?? null) ? $plan['filter'] : [],
        ],
        'result_policy' => [
            'output_fields' => is_array($plan['output_fields'] ?? null) ? array_values($plan['output_fields']) : [],
        ],
    ];
    $intent['dedupe_key'] = app_managed_operation_sync_intent_dedupe_key($intent);

    return [
        'ok' => true,
        'intent' => $intent,
        'error' => '',
    ];
}

function app_managed_operation_sync_storage_mode(string $value): string
{
    $normalized = strtolower(trim($value));
    return in_array($normalized, ['server-copy', 'local-copy'], true) ? $normalized : '';
}

function app_managed_operation_sync_endpoint(string $value): string
{
    $normalized = strtolower(trim($value));
    return in_array($normalized, ['server', 'app-local', 'public-runtime'], true) ? $normalized : '';
}

/**
 * @param array<string,mixed> $intent
 */
function app_managed_operation_sync_intent_dedupe_key(array $intent): string
{
    $json = json_encode([
        'intent_version' => $intent['intent_version'] ?? '',
        'storage_mode' => $intent['storage_mode'] ?? '',
        'origin' => $intent['origin'] ?? '',
        'target' => $intent['target'] ?? '',
        'project_key' => $intent['project_key'] ?? '',
        'operation_key' => $intent['operation_key'] ?? '',
        'operation_type' => $intent['operation_type'] ?? '',
        'contract_key' => $intent['contract_key'] ?? '',
        'payload' => $intent['payload'] ?? [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return hash('sha256', is_string($json) ? $json : '');
}
