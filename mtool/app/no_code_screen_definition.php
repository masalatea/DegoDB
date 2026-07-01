<?php

declare(strict_types=1);

require_once __DIR__ . '/managed_operation_policy.php';
require_once __DIR__ . '/managed_operation_repository_pdo.php';
require_once __DIR__ . '/shared_contract_manifest.php';

function app_no_code_screen_definition_version(): string
{
    return 'no-code-screen-definition-v0';
}

/**
 * @param array<string,mixed>|null $principal
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_screen_definition_from_project(
    array $app,
    string $projectKey,
    ?array $principal = null,
): array
{
    $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
    if (!$manifestResult['ok']) {
        return app_no_code_screen_definition_error($manifestResult['error']);
    }

    $operationSnapshot = app_pdo_fetch_managed_operation_snapshot($app, $projectKey);
    if (!$operationSnapshot['ok']) {
        return app_no_code_screen_definition_error($operationSnapshot['error']);
    }

    return app_no_code_screen_definition_from_snapshots(
        $projectKey,
        $manifestResult['manifest'],
        $operationSnapshot['items'],
        $principal,
    );
}

/**
 * @param array<string,mixed> $manifest
 * @param list<array<string,mixed>> $operations
 * @param array<string,mixed>|null $principal
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_screen_definition_from_snapshots(
    string $projectKey,
    array $manifest,
    array $operations,
    ?array $principal = null,
): array
{
    $contracts = app_no_code_screen_definition_managed_contracts($manifest);
    if ($contracts === []) {
        return app_no_code_screen_definition_error('managed-screen contract がありません。');
    }

    $operationsByContract = app_no_code_screen_definition_operations_by_contract($operations);
    $contractDefinitions = [];
    foreach ($contracts as $contract) {
        $contractKey = (string) ($contract['contract_key'] ?? '');
        $contractOperations = $operationsByContract[$contractKey] ?? [];
        $contractDefinitions[] = app_no_code_screen_definition_contract_definition(
            $contract,
            $contractOperations,
            $principal,
        );
    }

    return [
        'ok' => true,
        'definition' => [
            'definition_version' => app_no_code_screen_definition_version(),
            'project_key' => $projectKey,
            'contracts' => $contractDefinitions,
        ],
        'error' => '',
    ];
}

/**
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_screen_definition_error(string $error): array
{
    return [
        'ok' => false,
        'definition' => [],
        'error' => $error,
    ];
}

/**
 * @param array<string,mixed> $manifest
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_managed_contracts(array $manifest): array
{
    $contracts = [];
    foreach (($manifest['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];
        if ((string) ($metadata['status'] ?? 'active') !== 'active') {
            continue;
        }
        if ((string) ($metadata['no_code_role'] ?? '') !== 'managed-screen') {
            continue;
        }

        $contracts[] = $contract;
    }

    return $contracts;
}

/**
 * @param list<array<string,mixed>> $operations
 * @return array<string,list<array<string,mixed>>>
 */
function app_no_code_screen_definition_operations_by_contract(array $operations): array
{
    $grouped = [];
    foreach ($operations as $operation) {
        $contractKey = (string) ($operation['contract_key'] ?? '');
        if ($contractKey === '') {
            continue;
        }

        $grouped[$contractKey] ??= [];
        $grouped[$contractKey][] = $operation;
    }

    foreach ($grouped as $contractKey => $items) {
        usort(
            $items,
            static fn (array $left, array $right): int => strcmp(
                (string) ($left['operation_key'] ?? ''),
                (string) ($right['operation_key'] ?? ''),
            ),
        );
        $grouped[$contractKey] = $items;
    }

    return $grouped;
}

/**
 * @param array<string,mixed> $contract
 * @param list<array<string,mixed>> $operations
 * @param array<string,mixed>|null $principal
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_contract_definition(
    array $contract,
    array $operations,
    ?array $principal,
): array {
    $contractKey = (string) ($contract['contract_key'] ?? '');
    $fields = app_no_code_screen_definition_fields($contract);
    $actions = app_no_code_screen_definition_actions($contract, $operations, $principal);
    $storageHint = app_no_code_screen_definition_storage_hint($contract);
    $syncStatusDisplay = (bool) ($storageHint['sync_status_display'] ?? false);

    return [
        'contract_key' => $contractKey,
        'entity' => $contract['entity'] ?? [],
        'storage_hint' => $storageHint,
        'screens' => [
            app_no_code_screen_definition_list_screen($contractKey, $fields, $actions, $syncStatusDisplay),
            app_no_code_screen_definition_detail_screen($contractKey, $fields, $actions, $syncStatusDisplay),
            app_no_code_screen_definition_form_screen($contractKey, $fields, $actions),
        ],
        'actions' => $actions,
    ];
}

/**
 * @param array<string,mixed> $contract
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_fields(array $contract): array
{
    $fields = [];
    foreach (($contract['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $metadata = is_array($field['contract_metadata'] ?? null) ? $field['contract_metadata'] : [];
        $isKey = (bool) ($field['is_key'] ?? false);
        $operationRole = (string) ($metadata['operation_role'] ?? '');
        $fields[] = [
            'field_key' => (string) ($field['physical_name'] ?? ''),
            'generated_name' => (string) ($field['generated_name'] ?? ''),
            'label' => app_no_code_screen_definition_field_label($field),
            'type' => (string) ($field['type'] ?? 'string'),
            'is_key' => $isKey,
            'nullable' => (bool) ($field['nullable'] ?? false),
            'required' => !$isKey && !(bool) ($field['nullable'] ?? false) && ($field['default'] ?? null) === null,
            'readonly' => $isKey || $operationRole !== 'editable',
            'visibility' => 'visible',
        ];
    }

    return $fields;
}

/**
 * @param array<string,mixed> $field
 */
function app_no_code_screen_definition_field_label(array $field): string
{
    $logicalName = trim((string) ($field['logical_name'] ?? ''));
    if ($logicalName !== '') {
        return $logicalName;
    }

    return (string) ($field['generated_name'] ?? $field['physical_name'] ?? '');
}

/**
 * @param array<string,mixed> $contract
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_storage_hint(array $contract): array
{
    $metadata = is_array($contract['contract_metadata'] ?? null) ? $contract['contract_metadata'] : [];

    return [
        'sync_role' => (string) ($metadata['sync_role'] ?? 'unknown'),
        'app_persistence_role' => (string) ($metadata['app_persistence_role'] ?? 'unknown'),
        'sync_status_display' => in_array(
            (string) ($metadata['sync_role'] ?? ''),
            ['server-copy', 'app-source', 'bidirectional-sync'],
            true,
        ),
    ];
}

/**
 * @param array<string,mixed> $contract
 * @param list<array<string,mixed>> $operations
 * @param array<string,mixed>|null $principal
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_actions(array $contract, array $operations, ?array $principal): array
{
    $actions = [];
    foreach ($operations as $operation) {
        if ((string) ($operation['status'] ?? '') !== 'active') {
            continue;
        }

        $policy = app_no_code_screen_definition_policy($contract, $operation, $principal);
        $actions[] = [
            'action_key' => (string) ($operation['operation_key'] ?? ''),
            'label' => (string) ($operation['name'] ?? $operation['operation_key'] ?? ''),
            'operation_key' => (string) ($operation['operation_key'] ?? ''),
            'operation_type' => (string) ($operation['operation_type'] ?? ''),
            'permission_key' => (string) ($operation['permission_key'] ?? ''),
            'availability' => $policy['allowed'] ? 'enabled' : 'disabled',
            'policy' => $policy,
            'fields' => app_no_code_screen_definition_action_fields($operation),
        ];
    }

    return $actions;
}

/**
 * @param array<string,mixed> $contract
 * @param array<string,mixed> $operation
 * @param array<string,mixed>|null $principal
 * @return array{evaluated:bool,allowed:bool,failed_checks:list<string>}
 */
function app_no_code_screen_definition_policy(array $contract, array $operation, ?array $principal): array
{
    if ($principal === null) {
        return [
            'evaluated' => false,
            'allowed' => false,
            'failed_checks' => ['principal.missing'],
        ];
    }

    $decision = app_managed_operation_policy_evaluate($principal, $operation, $contract);

    return [
        'evaluated' => true,
        'allowed' => (bool) ($decision['allowed'] ?? false),
        'failed_checks' => is_array($decision['failed_checks'] ?? null) ? $decision['failed_checks'] : [],
    ];
}

/**
 * @param array<string,mixed> $operation
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_action_fields(array $operation): array
{
    $fields = [];
    foreach (($operation['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fields[] = [
            'field_key' => (string) ($field['field_physical_name'] ?? ''),
            'role' => (string) ($field['field_role'] ?? ''),
            'required' => (bool) ($field['is_required'] ?? false),
            'client_write' => (bool) ($field['allow_client_write'] ?? false),
        ];
    }

    return $fields;
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_list_screen(
    string $contractKey,
    array $fields,
    array $actions,
    bool $syncStatusDisplay,
): array {
    return [
        'screen_key' => $contractKey . '_list',
        'screen_type' => 'list',
        'contract_key' => $contractKey,
        'fields' => app_no_code_screen_definition_screen_fields($fields, 'list'),
        'actions' => app_no_code_screen_definition_screen_actions($actions, ['list', 'read', 'create', 'update', 'delete']),
        'sync_status_hint' => $syncStatusDisplay,
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_detail_screen(
    string $contractKey,
    array $fields,
    array $actions,
    bool $syncStatusDisplay,
): array {
    return [
        'screen_key' => $contractKey . '_detail',
        'screen_type' => 'detail',
        'contract_key' => $contractKey,
        'fields' => app_no_code_screen_definition_screen_fields($fields, 'detail'),
        'actions' => app_no_code_screen_definition_screen_actions($actions, ['read', 'update', 'delete']),
        'sync_status_hint' => $syncStatusDisplay,
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @param list<array<string,mixed>> $actions
 * @return array<string,mixed>
 */
function app_no_code_screen_definition_form_screen(string $contractKey, array $fields, array $actions): array
{
    return [
        'screen_key' => $contractKey . '_form',
        'screen_type' => 'form',
        'contract_key' => $contractKey,
        'fields' => app_no_code_screen_definition_screen_fields($fields, 'form'),
        'actions' => app_no_code_screen_definition_screen_actions($actions, ['create', 'update']),
        'sync_status_hint' => false,
    ];
}

/**
 * @param list<array<string,mixed>> $fields
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_screen_fields(array $fields, string $screenType): array
{
    if ($screenType === 'form') {
        return array_values(array_filter($fields, static fn (array $field): bool => !(bool) ($field['is_key'] ?? false)));
    }

    return $fields;
}

/**
 * @param list<array<string,mixed>> $actions
 * @param list<string> $operationTypes
 * @return list<array<string,mixed>>
 */
function app_no_code_screen_definition_screen_actions(array $actions, array $operationTypes): array
{
    $screenActions = [];
    foreach ($actions as $action) {
        if (in_array((string) ($action['operation_type'] ?? ''), $operationTypes, true)) {
            $screenActions[] = [
                'action_key' => (string) ($action['action_key'] ?? ''),
                'operation_key' => (string) ($action['operation_key'] ?? ''),
                'operation_type' => (string) ($action['operation_type'] ?? ''),
                'availability' => (string) ($action['availability'] ?? 'disabled'),
            ];
        }
    }

    return $screenActions;
}
