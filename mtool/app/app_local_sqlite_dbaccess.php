<?php

declare(strict_types=1);

require_once __DIR__ . '/app_local_sqlite_schema.php';

/**
 * @return array{ok:bool,contract:array<string,mixed>,fields:list<array<string,mixed>>,key_fields:list<array<string,mixed>>,error:string}
 */
function app_local_sqlite_dbaccess_contract(array $manifest, string $contractKey): array
{
    $validation = app_shared_contract_core_validate_manifest($manifest);
    if (!$validation['ok']) {
        return [
            'ok' => false,
            'contract' => [],
            'fields' => [],
            'key_fields' => [],
            'error' => 'shared contract manifest validation failed: ' . implode('; ', $validation['errors']),
        ];
    }

    foreach (($manifest['contracts'] ?? []) as $contract) {
        if (!is_array($contract) || (string) ($contract['contract_key'] ?? '') !== $contractKey) {
            continue;
        }

        $fields = [];
        $keyFields = [];
        foreach (($contract['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }
            $fields[] = $field;
            if ((bool) ($field['is_key'] ?? false)) {
                $keyFields[] = $field;
            }
        }

        return [
            'ok' => true,
            'contract' => $contract,
            'fields' => $fields,
            'key_fields' => $keyFields,
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'contract' => [],
        'fields' => [],
        'key_fields' => [],
        'error' => 'shared contract was not found: ' . $contractKey,
    ];
}

/**
 * @param array<string,mixed> $dto
 * @param array<string,mixed> $localMetadata
 * @return array{ok:bool,contract_key:string,key:array<string,mixed>,local_metadata:array<string,mixed>,error:string}
 */
function app_local_sqlite_dbaccess_save_dto(
    PDO $pdo,
    array $manifest,
    string $contractKey,
    array $dto,
    array $localMetadata = [],
): array {
    $contractResult = app_local_sqlite_dbaccess_contract($manifest, $contractKey);
    if (!$contractResult['ok']) {
        return [
            'ok' => false,
            'contract_key' => $contractKey,
            'key' => [],
            'local_metadata' => [],
            'error' => $contractResult['error'],
        ];
    }

    try {
        $contract = $contractResult['contract'];
        $tableName = app_local_sqlite_dbaccess_table_name($contract);
        $fields = $contractResult['fields'];
        $keyFields = $contractResult['key_fields'];
        if ($keyFields === []) {
            throw new RuntimeException('contract has no key fields: ' . $contractKey);
        }

        $columns = [];
        $parameters = [];
        $parameterValues = [];
        $keyDto = [];
        foreach ($fields as $field) {
            $generatedName = (string) ($field['generated_name'] ?? '');
            if (!array_key_exists($generatedName, $dto)) {
                throw new RuntimeException('DTO is missing field: ' . $generatedName);
            }

            $physicalName = app_local_sqlite_schema_identifier((string) ($field['physical_name'] ?? ''));
            $parameter = ':business_' . $physicalName;
            $columns[] = $physicalName;
            $parameters[] = $parameter;
            $parameterValues[$parameter] = app_local_sqlite_dbaccess_to_storage_value($field, $dto[$generatedName]);

            if ((bool) ($field['is_key'] ?? false)) {
                $keyDto[$generatedName] = $dto[$generatedName];
            }
        }

        $metadata = app_local_sqlite_dbaccess_normalize_local_metadata($localMetadata);
        foreach (['dirty', 'sync_status', 'tombstone', 'last_synced_at'] as $metadataColumn) {
            $parameter = ':metadata_' . $metadataColumn;
            $columns[] = $metadataColumn;
            $parameters[] = $parameter;
            $parameterValues[$parameter] = $metadata[$metadataColumn];
        }

        $updateColumns = [];
        foreach ($fields as $field) {
            if ((bool) ($field['is_key'] ?? false)) {
                continue;
            }
            $physicalName = app_local_sqlite_schema_identifier((string) ($field['physical_name'] ?? ''));
            $updateColumns[] = app_local_sqlite_schema_quote_identifier($physicalName) . ' = excluded.' . app_local_sqlite_schema_quote_identifier($physicalName);
        }
        foreach (['dirty', 'sync_status', 'tombstone', 'last_synced_at'] as $metadataColumn) {
            $updateColumns[] = app_local_sqlite_schema_quote_identifier($metadataColumn) . ' = excluded.' . app_local_sqlite_schema_quote_identifier($metadataColumn);
        }
        $updateColumns[] = '"local_updated_at" = CURRENT_TIMESTAMP';

        $sql = 'INSERT INTO '
            . app_local_sqlite_schema_quote_identifier($tableName)
            . ' ('
            . implode(', ', array_map('app_local_sqlite_schema_quote_identifier', $columns))
            . ') VALUES ('
            . implode(', ', $parameters)
            . ') ON CONFLICT ('
            . implode(', ', array_map(
                static fn (array $field): string => app_local_sqlite_schema_quote_identifier((string) ($field['physical_name'] ?? '')),
                $keyFields,
            ))
            . ') DO UPDATE SET '
            . implode(', ', $updateColumns);

        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new RuntimeException('failed to prepare App-local DTO save statement.');
        }
        $statement->execute($parameterValues);

        return [
            'ok' => true,
            'contract_key' => $contractKey,
            'key' => $keyDto,
            'local_metadata' => $metadata,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'contract_key' => $contractKey,
            'key' => [],
            'local_metadata' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $keyDto
 * @return array{ok:bool,dto:array<string,mixed>|null,local_metadata:array<string,mixed>,error:string}
 */
function app_local_sqlite_dbaccess_read_dto(PDO $pdo, array $manifest, string $contractKey, array $keyDto): array
{
    $contractResult = app_local_sqlite_dbaccess_contract($manifest, $contractKey);
    if (!$contractResult['ok']) {
        return [
            'ok' => false,
            'dto' => null,
            'local_metadata' => [],
            'error' => $contractResult['error'],
        ];
    }

    try {
        $contract = $contractResult['contract'];
        $tableName = app_local_sqlite_dbaccess_table_name($contract);
        $fields = $contractResult['fields'];
        $keyFields = $contractResult['key_fields'];
        if ($keyFields === []) {
            throw new RuntimeException('contract has no key fields: ' . $contractKey);
        }

        $selectColumns = [];
        foreach ($fields as $field) {
            $selectColumns[] = app_local_sqlite_schema_quote_identifier((string) ($field['physical_name'] ?? ''));
        }
        foreach (app_shared_contract_core_reserved_local_metadata_columns() as $metadataColumn) {
            $selectColumns[] = app_local_sqlite_schema_quote_identifier($metadataColumn);
        }

        $where = [];
        $parameterValues = [];
        foreach ($keyFields as $field) {
            $generatedName = (string) ($field['generated_name'] ?? '');
            if (!array_key_exists($generatedName, $keyDto)) {
                throw new RuntimeException('DTO key is missing field: ' . $generatedName);
            }
            $physicalName = app_local_sqlite_schema_identifier((string) ($field['physical_name'] ?? ''));
            $parameter = ':key_' . $physicalName;
            $where[] = app_local_sqlite_schema_quote_identifier($physicalName) . ' = ' . $parameter;
            $parameterValues[$parameter] = app_local_sqlite_dbaccess_to_storage_value($field, $keyDto[$generatedName]);
        }

        $sql = 'SELECT '
            . implode(', ', $selectColumns)
            . ' FROM '
            . app_local_sqlite_schema_quote_identifier($tableName)
            . ' WHERE '
            . implode(' AND ', $where)
            . ' LIMIT 1';
        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new RuntimeException('failed to prepare App-local DTO read statement.');
        }
        $statement->execute($parameterValues);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return [
                'ok' => true,
                'dto' => null,
                'local_metadata' => [],
                'error' => '',
            ];
        }

        $dto = [];
        foreach ($fields as $field) {
            $generatedName = (string) ($field['generated_name'] ?? '');
            $physicalName = (string) ($field['physical_name'] ?? '');
            $dto[$generatedName] = app_local_sqlite_dbaccess_from_storage_value($field, $row[$physicalName] ?? null);
        }

        return [
            'ok' => true,
            'dto' => $dto,
            'local_metadata' => [
                'local_updated_at' => (string) ($row['local_updated_at'] ?? ''),
                'last_synced_at' => $row['last_synced_at'] ?? null,
                'sync_status' => (string) ($row['sync_status'] ?? ''),
                'dirty' => (int) ($row['dirty'] ?? 0),
                'tombstone' => (int) ($row['tombstone'] ?? 0),
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'dto' => null,
            'local_metadata' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $contract
 */
function app_local_sqlite_dbaccess_table_name(array $contract): string
{
    $entity = is_array($contract['entity'] ?? null) ? $contract['entity'] : [];

    return app_local_sqlite_schema_identifier((string) ($entity['physical_name'] ?? $contract['contract_key'] ?? ''));
}

/**
 * @param array<string,mixed> $metadata
 * @return array{dirty:int,sync_status:string,tombstone:int,last_synced_at:string|null}
 */
function app_local_sqlite_dbaccess_normalize_local_metadata(array $metadata): array
{
    return [
        'dirty' => array_key_exists('dirty', $metadata) ? (((bool) $metadata['dirty']) ? 1 : 0) : 1,
        'sync_status' => trim((string) ($metadata['sync_status'] ?? 'dirty')),
        'tombstone' => array_key_exists('tombstone', $metadata) ? (((bool) $metadata['tombstone']) ? 1 : 0) : 0,
        'last_synced_at' => array_key_exists('last_synced_at', $metadata) && $metadata['last_synced_at'] !== null
            ? (string) $metadata['last_synced_at']
            : null,
    ];
}

/**
 * @param array<string,mixed> $field
 */
function app_local_sqlite_dbaccess_to_storage_value(array $field, mixed $value): mixed
{
    if ($value === null) {
        return null;
    }

    return match ((string) ($field['type'] ?? 'string')) {
        'integer' => (int) $value,
        'boolean' => ((bool) $value) ? 1 : 0,
        'datetime', 'string', 'text' => (string) $value,
        default => $value,
    };
}

/**
 * @param array<string,mixed> $field
 */
function app_local_sqlite_dbaccess_from_storage_value(array $field, mixed $value): mixed
{
    if ($value === null) {
        return null;
    }

    return match ((string) ($field['type'] ?? 'string')) {
        'integer' => (int) $value,
        'boolean' => ((int) $value) === 1,
        'datetime', 'string', 'text' => (string) $value,
        default => $value,
    };
}
