<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/shared_contract_metadata_repository_pdo.php';
require_once __DIR__ . '/table_metadata_repository_pdo.php';
require_once dirname(__DIR__) . '/shared/shared_contract_core.php';

/**
 * @return array{
 *     ok:bool,
 *     manifest:array<string,mixed>,
 *     validation:array{ok:bool,errors:list<string>},
 *     compare:array<string,mixed>,
 *     error:string
 * }
 */
function app_shared_contract_manifest_from_project(array $app, string $projectKey): array
{
    $dataClassSnapshot = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if (!$dataClassSnapshot['ok']) {
        return app_shared_contract_manifest_error('DataClass metadata の読み込みに失敗しました: ' . $dataClassSnapshot['error']);
    }

    $tableSnapshot = app_pdo_fetch_table_metadata_snapshot($app, $projectKey);
    if (!$tableSnapshot['ok']) {
        return app_shared_contract_manifest_error('table metadata の読み込みに失敗しました: ' . $tableSnapshot['error']);
    }

    $contractMetadataSnapshot = app_pdo_fetch_shared_contract_metadata_snapshot($app, $projectKey);
    if (!$contractMetadataSnapshot['ok']) {
        return app_shared_contract_manifest_error('shared contract metadata の読み込みに失敗しました: ' . $contractMetadataSnapshot['error']);
    }

    $manifest = app_shared_contract_manifest_from_snapshots(
        $projectKey,
        $dataClassSnapshot['items'],
        $tableSnapshot['items'],
        $contractMetadataSnapshot['items'],
    );
    $validation = app_shared_contract_core_validate_manifest($manifest);
    $compare = app_shared_contract_manifest_compare_dataclass_shape($manifest, $dataClassSnapshot['items']);

    return [
        'ok' => $validation['ok'] && (bool) ($compare['ok'] ?? false),
        'manifest' => $manifest,
        'validation' => $validation,
        'compare' => $compare,
        'error' => !$validation['ok']
            ? 'shared contract manifest validation failed'
            : ((bool) ($compare['ok'] ?? false) ? '' : 'shared contract manifest compare failed'),
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     manifest:array<string,mixed>,
 *     validation:array{ok:bool,errors:list<string>},
 *     compare:array<string,mixed>,
 *     error:string
 * }
 */
function app_shared_contract_manifest_error(string $error): array
{
    return [
        'ok' => false,
        'manifest' => [],
        'validation' => [
            'ok' => false,
            'errors' => [$error],
        ],
        'compare' => [
            'ok' => false,
            'mismatches' => [$error],
        ],
        'error' => $error,
    ];
}

/**
 * @param list<array<string,mixed>> $dataClassItems
 * @param list<array<string,mixed>> $tableItems
 * @param list<array<string,mixed>> $contractMetadataItems
 * @return array<string,mixed>
 */
function app_shared_contract_manifest_from_snapshots(
    string $projectKey,
    array $dataClassItems,
    array $tableItems,
    array $contractMetadataItems = [],
): array {
    $tablesByPhysicalName = app_shared_contract_manifest_index_by_physical_name($tableItems);
    $contractMetadataByPhysicalName = app_shared_contract_manifest_contract_metadata_by_physical_name(
        $contractMetadataItems,
    );
    $contracts = [];

    foreach ($dataClassItems as $dataClassItem) {
        $physicalName = (string) ($dataClassItem['physical_name'] ?? $dataClassItem['name'] ?? '');
        if ($physicalName === '' || !isset($tablesByPhysicalName[$physicalName])) {
            continue;
        }

        $tableItem = $tablesByPhysicalName[$physicalName];
        $contractMetadata = $contractMetadataByPhysicalName[$physicalName] ?? [];
        $fieldMetadataByPhysicalName = app_shared_contract_manifest_field_metadata_by_physical_name(
            is_array($contractMetadata['fields'] ?? null) ? $contractMetadata['fields'] : [],
        );
        $columnsByPhysicalName = app_shared_contract_manifest_columns_by_physical_name(
            is_array($tableItem['columns'] ?? null) ? $tableItem['columns'] : [],
        );

        $fields = [];
        foreach (($dataClassItem['fields'] ?? []) as $fieldItem) {
            if (!is_array($fieldItem)) {
                continue;
            }

            $fieldPhysicalName = (string) ($fieldItem['physical_name'] ?? $fieldItem['name'] ?? '');
            if ($fieldPhysicalName === '' || !isset($columnsByPhysicalName[$fieldPhysicalName])) {
                continue;
            }

            $columnItem = $columnsByPhysicalName[$fieldPhysicalName];
            $type = app_shared_contract_manifest_field_type($columnItem, $fieldItem);
            $field = [
                'logical_name' => (string) ($fieldItem['logical_name'] ?? $columnItem['logical_name'] ?? ''),
                'physical_name' => $fieldPhysicalName,
                'generated_name' => (string) ($fieldItem['generated_name'] ?? ''),
                'type' => $type,
                'nullable' => app_shared_contract_manifest_column_is_nullable($columnItem),
                'default' => app_shared_contract_manifest_column_default($columnItem, $type),
                'is_key' => app_shared_contract_manifest_column_is_key($columnItem),
                'storage_role' => 'business',
            ];
            $fieldMetadata = $fieldMetadataByPhysicalName[$fieldPhysicalName] ?? [];
            if ($fieldMetadata !== []) {
                $field['contract_metadata'] = app_shared_contract_manifest_normalize_field_metadata($fieldMetadata);
            }

            $fields[] = $field;
        }

        $contract = [
            'contract_key' => $physicalName,
            'entity' => [
                'logical_name' => (string) ($dataClassItem['logical_name'] ?? $tableItem['logical_name'] ?? ''),
                'physical_name' => $physicalName,
                'generated_name' => (string) ($dataClassItem['generated_name'] ?? $dataClassItem['name'] ?? ''),
            ],
            'fields' => $fields,
            'local_metadata' => [
                'reserved_columns' => app_shared_contract_core_reserved_local_metadata_columns(),
                'collision_policy' => 'reject',
            ],
        ];
        if ($contractMetadata !== []) {
            $contract['contract_metadata'] = app_shared_contract_manifest_normalize_contract_metadata($contractMetadata);
        }

        $contracts[] = $contract;
    }

    usort(
        $contracts,
        static fn (array $left, array $right): int => strcmp(
            (string) ($left['contract_key'] ?? ''),
            (string) ($right['contract_key'] ?? ''),
        ),
    );

    return [
        'manifest_version' => app_shared_contract_core_manifest_version(),
        'project_key' => $projectKey,
        'contracts' => $contracts,
    ];
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,array<string,mixed>>
 */
function app_shared_contract_manifest_index_by_physical_name(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $physicalName = (string) ($item['physical_name'] ?? $item['name'] ?? '');
        if ($physicalName !== '') {
            $indexed[$physicalName] = $item;
        }
    }

    return $indexed;
}

/**
 * @param list<array<string,mixed>> $columns
 * @return array<string,array<string,mixed>>
 */
function app_shared_contract_manifest_columns_by_physical_name(array $columns): array
{
    return app_shared_contract_manifest_index_by_physical_name($columns);
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,array<string,mixed>>
 */
function app_shared_contract_manifest_contract_metadata_by_physical_name(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $physicalName = (string) ($item['data_class_physical_name'] ?? $item['contract_key'] ?? '');
        if ($physicalName !== '') {
            $indexed[$physicalName] = $item;
        }
    }

    return $indexed;
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,array<string,mixed>>
 */
function app_shared_contract_manifest_field_metadata_by_physical_name(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $physicalName = (string) ($item['field_physical_name'] ?? '');
        if ($physicalName !== '') {
            $indexed[$physicalName] = $item;
        }
    }

    return $indexed;
}

/**
 * @return array<string,string>
 */
function app_shared_contract_manifest_normalize_contract_metadata(array $metadata): array
{
    return array_filter([
        'status' => (string) ($metadata['status'] ?? ''),
        'sync_role' => (string) ($metadata['sync_role'] ?? ''),
        'no_code_role' => (string) ($metadata['no_code_role'] ?? ''),
        'app_persistence_role' => (string) ($metadata['app_persistence_role'] ?? ''),
        'notes' => (string) ($metadata['notes'] ?? ''),
        'source_of_truth' => (string) ($metadata['source_of_truth'] ?? ''),
    ], static fn (string $value): bool => $value !== '');
}

/**
 * @return array<string,string>
 */
function app_shared_contract_manifest_normalize_field_metadata(array $metadata): array
{
    return array_filter([
        'sync_role' => (string) ($metadata['sync_role'] ?? ''),
        'operation_role' => (string) ($metadata['operation_role'] ?? ''),
        'no_code_role' => (string) ($metadata['no_code_role'] ?? ''),
        'app_persistence_role' => (string) ($metadata['app_persistence_role'] ?? ''),
        'notes' => (string) ($metadata['notes'] ?? ''),
        'source_of_truth' => (string) ($metadata['source_of_truth'] ?? ''),
    ], static fn (string $value): bool => $value !== '');
}

function app_shared_contract_manifest_field_type(array $columnItem, array $fieldItem): string
{
    $rawType = strtolower(trim((string) ($columnItem['datatype'] ?? $fieldItem['datatype'] ?? '')));

    if (preg_match('/tinyint\s*\(\s*1\s*\)|\bbool(ean)?\b/', $rawType) === 1) {
        return 'boolean';
    }
    if (preg_match('/\b(bigint|int|integer|smallint|mediumint|serial)\b/', $rawType) === 1) {
        return 'integer';
    }
    if (preg_match('/\b(text|longtext|mediumtext|tinytext)\b/', $rawType) === 1) {
        return 'text';
    }
    if (preg_match('/\b(datetime|timestamp)\b/', $rawType) === 1) {
        return 'datetime';
    }
    if (preg_match('/\bdate\b/', $rawType) === 1) {
        return 'date';
    }
    if (preg_match('/\btime\b/', $rawType) === 1) {
        return 'time';
    }

    return 'string';
}

function app_shared_contract_manifest_column_is_nullable(array $columnItem): bool
{
    return strtoupper(trim((string) ($columnItem['is_null'] ?? ''))) === 'YES';
}

function app_shared_contract_manifest_column_is_key(array $columnItem): bool
{
    return strtoupper(trim((string) ($columnItem['is_key'] ?? ''))) === 'PRI';
}

function app_shared_contract_manifest_column_default(array $columnItem, string $type): mixed
{
    $rawDefault = trim((string) ($columnItem['is_default'] ?? ''));
    if ($rawDefault === '' || strtoupper($rawDefault) === 'NULL') {
        return null;
    }

    $unquoted = $rawDefault;
    if (
        strlen($rawDefault) >= 2
        && (($rawDefault[0] === "'" && $rawDefault[strlen($rawDefault) - 1] === "'")
            || ($rawDefault[0] === '"' && $rawDefault[strlen($rawDefault) - 1] === '"'))
    ) {
        $unquoted = substr($rawDefault, 1, -1);
    }

    if ($type === 'integer' && preg_match('/^-?\d+$/', $unquoted) === 1) {
        return (int) $unquoted;
    }

    if ($type === 'boolean') {
        $normalized = strtolower($unquoted);
        if (in_array($normalized, ['1', 'true'], true)) {
            return true;
        }
        if (in_array($normalized, ['0', 'false'], true)) {
            return false;
        }
    }

    return $unquoted;
}

/**
 * @param list<array<string,mixed>> $dataClassItems
 * @return array{
 *     ok:bool,
 *     contract_count:int,
 *     mismatch_count:int,
 *     mismatches:list<string>,
 *     contracts:list<array<string,mixed>>
 * }
 */
function app_shared_contract_manifest_compare_dataclass_shape(array $manifest, array $dataClassItems): array
{
    $dataClassesByPhysicalName = app_shared_contract_manifest_index_by_physical_name($dataClassItems);
    $mismatches = [];
    $contracts = [];

    foreach (($manifest['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $contractKey = (string) ($contract['contract_key'] ?? '');
        $dataClassItem = $dataClassesByPhysicalName[$contractKey] ?? null;
        if (!is_array($dataClassItem)) {
            $mismatches[] = 'DataClass not found for contract: ' . $contractKey;
            continue;
        }

        $contractFields = array_values(array_map(
            static fn (array $field): string => (string) ($field['generated_name'] ?? ''),
            array_filter($contract['fields'] ?? [], 'is_array'),
        ));
        $dataClassFields = array_values(array_map(
            static fn (array $field): string => (string) ($field['generated_name'] ?? ''),
            array_filter($dataClassItem['fields'] ?? [], 'is_array'),
        ));

        $match = $contractFields === $dataClassFields;
        if (!$match) {
            $mismatches[] = 'field shape mismatch for contract: ' . $contractKey;
        }

        $contracts[] = [
            'contract_key' => $contractKey,
            'generated_name' => (string) ($contract['entity']['generated_name'] ?? ''),
            'contract_fields' => $contractFields,
            'dataclass_fields' => $dataClassFields,
            'match' => $match,
        ];
    }

    return [
        'ok' => $mismatches === [],
        'contract_count' => count($contracts),
        'mismatch_count' => count($mismatches),
        'mismatches' => $mismatches,
        'contracts' => $contracts,
    ];
}
