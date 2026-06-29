<?php

declare(strict_types=1);

function app_shared_contract_core_manifest_version(): string
{
    return 'shared-contract-manifest-v0';
}

/**
 * @return list<string>
 */
function app_shared_contract_core_supported_field_types(): array
{
    return ['integer', 'string', 'text', 'boolean', 'datetime'];
}

/**
 * @return list<string>
 */
function app_shared_contract_core_reserved_local_metadata_columns(): array
{
    return [
        'local_updated_at',
        'last_synced_at',
        'sync_status',
        'dirty',
        'tombstone',
    ];
}

/**
 * @return array<string,mixed>
 */
function app_shared_contract_core_sample02_task_manifest(): array
{
    return [
        'manifest_version' => app_shared_contract_core_manifest_version(),
        'contracts' => [
            [
                'contract_key' => 'task',
                'entity' => [
                    'logical_name' => 'Task',
                    'physical_name' => 'task',
                    'generated_name' => 'Task',
                ],
                'fields' => [
                    [
                        'logical_name' => 'ID',
                        'physical_name' => 'id',
                        'generated_name' => 'id',
                        'type' => 'integer',
                        'nullable' => false,
                        'default' => null,
                        'is_key' => true,
                        'storage_role' => 'business',
                    ],
                    [
                        'logical_name' => 'Title',
                        'physical_name' => 'title',
                        'generated_name' => 'title',
                        'type' => 'string',
                        'nullable' => false,
                        'default' => null,
                        'is_key' => false,
                        'storage_role' => 'business',
                    ],
                    [
                        'logical_name' => 'Status',
                        'physical_name' => 'status',
                        'generated_name' => 'status',
                        'type' => 'string',
                        'nullable' => false,
                        'default' => 'draft',
                        'is_key' => false,
                        'storage_role' => 'business',
                    ],
                    [
                        'logical_name' => 'Sort Order',
                        'physical_name' => 'sort_order',
                        'generated_name' => 'sortOrder',
                        'type' => 'integer',
                        'nullable' => false,
                        'default' => 0,
                        'is_key' => false,
                        'storage_role' => 'business',
                    ],
                    [
                        'logical_name' => 'Is Pinned',
                        'physical_name' => 'is_pinned',
                        'generated_name' => 'isPinned',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => false,
                        'is_key' => false,
                        'storage_role' => 'business',
                    ],
                    [
                        'logical_name' => 'Published At',
                        'physical_name' => 'published_at',
                        'generated_name' => 'publishedAt',
                        'type' => 'datetime',
                        'nullable' => true,
                        'default' => null,
                        'is_key' => false,
                        'storage_role' => 'business',
                    ],
                    [
                        'logical_name' => 'Note',
                        'physical_name' => 'note',
                        'generated_name' => 'note',
                        'type' => 'text',
                        'nullable' => true,
                        'default' => null,
                        'is_key' => false,
                        'storage_role' => 'business',
                    ],
                ],
                'local_metadata' => [
                    'reserved_columns' => app_shared_contract_core_reserved_local_metadata_columns(),
                    'collision_policy' => 'reject',
                ],
            ],
        ],
    ];
}

/**
 * @return array{ok:bool,errors:list<string>}
 */
function app_shared_contract_core_validate_manifest(array $manifest): array
{
    $errors = [];

    if (($manifest['manifest_version'] ?? '') !== app_shared_contract_core_manifest_version()) {
        $errors[] = 'manifest_version must be ' . app_shared_contract_core_manifest_version();
    }

    if (!array_key_exists('contracts', $manifest) || !is_array($manifest['contracts']) || !array_is_list($manifest['contracts']) || $manifest['contracts'] === []) {
        $errors[] = 'contracts must be a non-empty list';
        return [
            'ok' => false,
            'errors' => $errors,
        ];
    }

    $contractKeys = [];
    foreach ($manifest['contracts'] as $contractIndex => $contract) {
        if (!is_array($contract)) {
            $errors[] = 'contracts[' . $contractIndex . '] must be an object';
            continue;
        }

        $errors = array_merge($errors, app_shared_contract_core_validate_contract($contract, 'contracts[' . $contractIndex . ']'));
        $contractKey = (string) ($contract['contract_key'] ?? '');
        if ($contractKey !== '') {
            if (isset($contractKeys[$contractKey])) {
                $errors[] = 'duplicate contract_key: ' . $contractKey;
            }
            $contractKeys[$contractKey] = true;
        }
    }

    return [
        'ok' => $errors === [],
        'errors' => $errors,
    ];
}

/**
 * @return list<string>
 */
function app_shared_contract_core_validate_contract(array $contract, string $path): array
{
    $errors = [];

    $contractKey = (string) ($contract['contract_key'] ?? '');
    if ($contractKey === '' || preg_match('/^[a-z][a-z0-9_]*$/', $contractKey) !== 1) {
        $errors[] = $path . '.contract_key must be lower snake case';
    }

    if (!isset($contract['entity']) || !is_array($contract['entity'])) {
        $errors[] = $path . '.entity must be an object';
    } else {
        foreach (['logical_name', 'physical_name', 'generated_name'] as $nameKey) {
            if (trim((string) ($contract['entity'][$nameKey] ?? '')) === '') {
                $errors[] = $path . '.entity.' . $nameKey . ' is required';
            }
        }
    }

    if (!isset($contract['fields']) || !is_array($contract['fields']) || !array_is_list($contract['fields']) || $contract['fields'] === []) {
        $errors[] = $path . '.fields must be a non-empty list';
        return $errors;
    }

    $reservedColumns = app_shared_contract_core_reserved_local_metadata_columns();
    $localMetadata = $contract['local_metadata'] ?? [];
    if (!is_array($localMetadata)) {
        $errors[] = $path . '.local_metadata must be an object';
    } else {
        $declaredReservedColumns = $localMetadata['reserved_columns'] ?? null;
        if (!is_array($declaredReservedColumns) || array_values($declaredReservedColumns) !== $reservedColumns) {
            $errors[] = $path . '.local_metadata.reserved_columns must match shared contract reserved local metadata columns';
        }
        if (($localMetadata['collision_policy'] ?? '') !== 'reject') {
            $errors[] = $path . '.local_metadata.collision_policy must be reject';
        }
    }

    $physicalNames = [];
    $generatedNames = [];
    $keyCount = 0;
    foreach ($contract['fields'] as $fieldIndex => $field) {
        if (!is_array($field)) {
            $errors[] = $path . '.fields[' . $fieldIndex . '] must be an object';
            continue;
        }

        $fieldPath = $path . '.fields[' . $fieldIndex . ']';
        $errors = array_merge($errors, app_shared_contract_core_validate_field($field, $fieldPath));

        $physicalName = (string) ($field['physical_name'] ?? '');
        if ($physicalName !== '') {
            if (isset($physicalNames[$physicalName])) {
                $errors[] = 'duplicate physical_name: ' . $physicalName;
            }
            $physicalNames[$physicalName] = true;
            if (in_array($physicalName, $reservedColumns, true)) {
                $errors[] = 'business field collides with reserved local metadata column: ' . $physicalName;
            }
        }

        $generatedName = (string) ($field['generated_name'] ?? '');
        if ($generatedName !== '') {
            if (isset($generatedNames[$generatedName])) {
                $errors[] = 'duplicate generated_name: ' . $generatedName;
            }
            $generatedNames[$generatedName] = true;
        }

        if (($field['is_key'] ?? null) === true) {
            $keyCount++;
        }
    }

    if ($keyCount === 0) {
        $errors[] = $path . '.fields must include at least one key field';
    }

    return $errors;
}

/**
 * @return list<string>
 */
function app_shared_contract_core_validate_field(array $field, string $path): array
{
    $errors = [];

    foreach (['logical_name', 'physical_name', 'generated_name', 'type', 'storage_role'] as $fieldKey) {
        if (!array_key_exists($fieldKey, $field) || trim((string) $field[$fieldKey]) === '') {
            $errors[] = $path . '.' . $fieldKey . ' is required';
        }
    }

    $physicalName = (string) ($field['physical_name'] ?? '');
    if ($physicalName !== '' && preg_match('/^[a-z][a-z0-9_]*$/', $physicalName) !== 1) {
        $errors[] = $path . '.physical_name must be lower snake case';
    }

    $generatedName = (string) ($field['generated_name'] ?? '');
    if ($generatedName !== '' && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $generatedName) !== 1) {
        $errors[] = $path . '.generated_name must be a generated identifier';
    }

    if (!in_array((string) ($field['type'] ?? ''), app_shared_contract_core_supported_field_types(), true)) {
        $errors[] = $path . '.type is unsupported';
    }

    if (!array_key_exists('nullable', $field) || !is_bool($field['nullable'])) {
        $errors[] = $path . '.nullable must be boolean';
    }

    if (!array_key_exists('default', $field)) {
        $errors[] = $path . '.default must be present';
    } elseif (!is_null($field['default']) && !is_scalar($field['default'])) {
        $errors[] = $path . '.default must be scalar or null';
    }

    if (!array_key_exists('is_key', $field) || !is_bool($field['is_key'])) {
        $errors[] = $path . '.is_key must be boolean';
    }

    if (($field['storage_role'] ?? '') !== 'business') {
        $errors[] = $path . '.storage_role must be business';
    }

    return $errors;
}

