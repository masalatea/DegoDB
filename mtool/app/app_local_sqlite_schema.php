<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/shared/shared_contract_core.php';

/**
 * @return array{
 *     ok:bool,
 *     schema_sql:string,
 *     summary:array<string,mixed>,
 *     validation:array{ok:bool,errors:list<string>},
 *     error:string
 * }
 */
function app_local_sqlite_schema_generate(array $manifest): array
{
    $validation = app_shared_contract_core_validate_manifest($manifest);
    if (!$validation['ok']) {
        return [
            'ok' => false,
            'schema_sql' => '',
            'summary' => [],
            'validation' => $validation,
            'error' => 'shared contract manifest validation failed.',
        ];
    }

    try {
        $statements = [
            'PRAGMA foreign_keys = ON;',
            'CREATE TABLE IF NOT EXISTS "__app_local_schema_version" ("schema_key" TEXT PRIMARY KEY, "version" INTEGER NOT NULL);',
        ];
        $tables = [];
        $metadataColumns = app_shared_contract_core_reserved_local_metadata_columns();

        foreach ($manifest['contracts'] as $contract) {
            if (!is_array($contract)) {
                continue;
            }

            $entity = is_array($contract['entity'] ?? null) ? $contract['entity'] : [];
            $tableName = app_local_sqlite_schema_identifier((string) ($entity['physical_name'] ?? $contract['contract_key'] ?? ''));
            $columns = [];
            $fieldNames = [];
            $keyFields = [];

            foreach (($contract['fields'] ?? []) as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $fieldName = app_local_sqlite_schema_identifier((string) ($field['physical_name'] ?? ''));
                $fieldNames[] = $fieldName;
                if ((bool) ($field['is_key'] ?? false)) {
                    $keyFields[] = $fieldName;
                }

                $column = app_local_sqlite_schema_quote_identifier($fieldName)
                    . ' '
                    . app_local_sqlite_schema_field_type((string) ($field['type'] ?? 'string'));
                if ((bool) ($field['nullable'] ?? true) === false) {
                    $column .= ' NOT NULL';
                }
                $column .= app_local_sqlite_schema_default_sql($field['default'] ?? null);
                $columns[] = $column;
            }

            $columns = array_merge($columns, app_local_sqlite_schema_local_metadata_column_sql());
            if ($keyFields !== []) {
                $columns[] = 'PRIMARY KEY (' . implode(', ', array_map('app_local_sqlite_schema_quote_identifier', $keyFields)) . ')';
            }

            $statements[] = 'CREATE TABLE IF NOT EXISTS '
                . app_local_sqlite_schema_quote_identifier($tableName)
                . " (\n    "
                . implode(",\n    ", $columns)
                . "\n);";
            $statements[] = 'CREATE INDEX IF NOT EXISTS '
                . app_local_sqlite_schema_quote_identifier('idx_' . $tableName . '_sync_status_dirty')
                . ' ON '
                . app_local_sqlite_schema_quote_identifier($tableName)
                . ' ("sync_status", "dirty");';

            $tables[] = [
                'table' => $tableName,
                'core_fields' => $fieldNames,
                'key_fields' => $keyFields,
                'local_metadata_columns' => $metadataColumns,
            ];
        }

        return [
            'ok' => true,
            'schema_sql' => implode("\n\n", $statements) . "\n",
            'summary' => [
                'manifest_version' => (string) ($manifest['manifest_version'] ?? ''),
                'table_count' => count($tables),
                'tables' => $tables,
                'metadata_column_policy' => 'append reserved local metadata columns after core contract fields',
            ],
            'validation' => $validation,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'schema_sql' => '',
            'summary' => [],
            'validation' => $validation,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return list<string>
 */
function app_local_sqlite_schema_local_metadata_column_sql(): array
{
    return [
        '"local_updated_at" TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP',
        '"last_synced_at" TEXT NULL',
        '"sync_status" TEXT NOT NULL DEFAULT ' . "'clean'",
        '"dirty" INTEGER NOT NULL DEFAULT 0',
        '"tombstone" INTEGER NOT NULL DEFAULT 0',
    ];
}

function app_local_sqlite_schema_identifier(string $identifier): string
{
    $identifier = trim($identifier);
    if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
        throw new RuntimeException('unsafe SQLite identifier: ' . $identifier);
    }

    return $identifier;
}

function app_local_sqlite_schema_quote_identifier(string $identifier): string
{
    return '"' . app_local_sqlite_schema_identifier($identifier) . '"';
}

function app_local_sqlite_schema_field_type(string $type): string
{
    return match ($type) {
        'integer', 'boolean' => 'INTEGER',
        'datetime', 'string', 'text' => 'TEXT',
        default => throw new RuntimeException('unsupported shared contract field type: ' . $type),
    };
}

function app_local_sqlite_schema_default_sql(mixed $value): string
{
    if ($value === null) {
        return '';
    }
    if (is_bool($value)) {
        return ' DEFAULT ' . ($value ? '1' : '0');
    }
    if (is_int($value) || is_float($value)) {
        return ' DEFAULT ' . (string) $value;
    }

    return " DEFAULT '" . str_replace("'", "''", (string) $value) . "'";
}

/**
 * @return array{ok:bool,tables:array<string,list<string>>,indexes:list<string>,error:string}
 */
function app_local_sqlite_schema_apply_to_pdo(PDO $pdo, string $schemaSql): array
{
    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec($schemaSql);

        $tables = [];
        $tableRows = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name");
        if ($tableRows === false) {
            throw new RuntimeException('failed to read SQLite table list.');
        }
        foreach ($tableRows->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $tableName = (string) ($row['name'] ?? '');
            $columnRows = $pdo->query('PRAGMA table_info(' . app_local_sqlite_schema_quote_identifier($tableName) . ')');
            if ($columnRows === false) {
                throw new RuntimeException('failed to read SQLite table info: ' . $tableName);
            }
            $tables[$tableName] = array_map(
                static fn (array $column): string => (string) ($column['name'] ?? ''),
                $columnRows->fetchAll(PDO::FETCH_ASSOC),
            );
        }

        $indexes = [];
        $indexRows = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'index' ORDER BY name");
        if ($indexRows === false) {
            throw new RuntimeException('failed to read SQLite index list.');
        }
        foreach ($indexRows->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $indexes[] = (string) ($row['name'] ?? '');
        }

        return [
            'ok' => true,
            'tables' => $tables,
            'indexes' => $indexes,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'tables' => [],
            'indexes' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}
