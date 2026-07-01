#!/usr/bin/env php
<?php

declare(strict_types=1);

function app_cli_local_sqlite_schema_spike_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/experimental/app_local_sqlite_schema_spike.php --sample=task-default --output-dir=work/feasibility/app-local-sqlite-schema-sample02
  php mtool/scripts/experimental/app_local_sqlite_schema_spike.php --input-manifest=work/feasibility/contract.json --output-dir=work/feasibility/app-local-sqlite-schema

Options:
  --sample=task-default      use built-in explicit Task contract
  --input-manifest=PATH      read manifest JSON from PATH
  --output-dir=PATH          output directory for schema.sql and summary.json
  --sqlite-path=PATH         SQLite DB path. default: OUTPUT_DIR/app-local.sqlite
  --help                     show this help
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     sample:string,
 *     input_manifest:string,
 *     output_dir:string,
 *     sqlite_path:string,
 *     error:string
 * }
 */
function app_cli_local_sqlite_schema_spike_parse_args(array $argv): array
{
    $sample = '';
    $inputManifest = '';
    $outputDir = '';
    $sqlitePath = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'sample' => '',
                'input_manifest' => '',
                'output_dir' => '',
                'sqlite_path' => '',
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--sample=')) {
            $sample = trim(substr($argument, strlen('--sample=')));
            continue;
        }
        if (str_starts_with($argument, '--input-manifest=')) {
            $inputManifest = trim(substr($argument, strlen('--input-manifest=')));
            continue;
        }
        if (str_starts_with($argument, '--output-dir=')) {
            $outputDir = trim(substr($argument, strlen('--output-dir=')));
            continue;
        }
        if (str_starts_with($argument, '--sqlite-path=')) {
            $sqlitePath = trim(substr($argument, strlen('--sqlite-path=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'sample' => '',
            'input_manifest' => '',
            'output_dir' => '',
            'sqlite_path' => '',
            'error' => 'unsupported argument: ' . $argument,
        ];
    }

    if ($outputDir === '') {
        return [
            'ok' => false,
            'help' => false,
            'sample' => '',
            'input_manifest' => '',
            'output_dir' => '',
            'sqlite_path' => '',
            'error' => '--output-dir=... is required.',
        ];
    }

    if (($sample === '') === ($inputManifest === '')) {
        return [
            'ok' => false,
            'help' => false,
            'sample' => '',
            'input_manifest' => '',
            'output_dir' => '',
            'sqlite_path' => '',
            'error' => 'specify exactly one of --sample=... or --input-manifest=...',
        ];
    }

    if ($sqlitePath === '') {
        $sqlitePath = rtrim($outputDir, '/') . '/app-local.sqlite';
    }

    return [
        'ok' => true,
        'help' => false,
        'sample' => $sample,
        'input_manifest' => $inputManifest,
        'output_dir' => $outputDir,
        'sqlite_path' => $sqlitePath,
        'error' => '',
    ];
}

/**
 * @return array<string,mixed>
 */
function app_local_sqlite_schema_spike_task_default_manifest(): array
{
    return [
        'manifest_version' => 'shared-contract-manifest-spike-v0-explicit',
        'project_key' => 'SAMPLE02',
        'contracts' => [
            [
                'contract_key' => 'task',
                'dataclass' => [
                    'logical_name' => 'Task',
                    'physical_name' => 'task',
                    'generated_name' => 'Task',
                ],
                'fields' => [
                    [
                        'logical_name' => 'Id',
                        'physical_name' => 'id',
                        'generated_name' => 'id',
                        'type' => 'int',
                        'nullable' => false,
                        'default' => null,
                        'is_key' => true,
                    ],
                    [
                        'logical_name' => 'Title',
                        'physical_name' => 'title',
                        'generated_name' => 'title',
                        'type' => 'string',
                        'nullable' => false,
                        'default' => null,
                        'is_key' => false,
                    ],
                    [
                        'logical_name' => 'Status',
                        'physical_name' => 'status',
                        'generated_name' => 'status',
                        'type' => 'string',
                        'nullable' => false,
                        'default' => 'draft',
                        'is_key' => false,
                        'enum_like_status' => true,
                    ],
                    [
                        'logical_name' => 'SortOrder',
                        'physical_name' => 'sort_order',
                        'generated_name' => 'sortOrder',
                        'type' => 'int',
                        'nullable' => false,
                        'default' => 0,
                        'is_key' => false,
                    ],
                    [
                        'logical_name' => 'IsPinned',
                        'physical_name' => 'is_pinned',
                        'generated_name' => 'isPinned',
                        'type' => 'bool',
                        'nullable' => false,
                        'default' => false,
                        'is_key' => false,
                    ],
                    [
                        'logical_name' => 'PublishedAt',
                        'physical_name' => 'published_at',
                        'generated_name' => 'publishedAt',
                        'type' => 'datetime',
                        'nullable' => true,
                        'default' => null,
                        'is_key' => false,
                    ],
                    [
                        'logical_name' => 'Note',
                        'physical_name' => 'note',
                        'generated_name' => 'note',
                        'type' => 'text',
                        'nullable' => true,
                        'default' => null,
                        'is_key' => false,
                    ],
                ],
            ],
        ],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_local_sqlite_schema_spike_load_manifest(string $sample, string $inputManifest): array
{
    if ($sample !== '') {
        if ($sample !== 'task-default') {
            throw new RuntimeException('unknown sample: ' . $sample);
        }

        return app_local_sqlite_schema_spike_task_default_manifest();
    }

    $json = file_get_contents($inputManifest);
    if (!is_string($json)) {
        throw new RuntimeException('failed to read input manifest: ' . $inputManifest);
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('input manifest is not valid JSON: ' . json_last_error_msg());
    }

    return $decoded;
}

/**
 * @return list<string>
 */
function app_local_sqlite_schema_spike_validate_manifest(array $manifest): array
{
    $errors = [];
    $contracts = $manifest['contracts'] ?? null;
    if (!is_array($contracts) || $contracts === []) {
        return ['manifest.contracts must be a non-empty array'];
    }

    foreach ($contracts as $contractIndex => $contract) {
        if (!is_array($contract)) {
            $errors[] = 'contract[' . $contractIndex . '] must be an object';
            continue;
        }

        $tableName = (string) ($contract['dataclass']['physical_name'] ?? $contract['contract_key'] ?? '');
        if (!app_local_sqlite_schema_spike_identifier_is_safe($tableName)) {
            $errors[] = 'contract[' . $contractIndex . '] has unsafe table name: ' . $tableName;
        }

        $fields = $contract['fields'] ?? null;
        if (!is_array($fields) || $fields === []) {
            $errors[] = 'contract[' . $contractIndex . '].fields must be a non-empty array';
            continue;
        }

        foreach ($fields as $fieldIndex => $field) {
            if (!is_array($field)) {
                $errors[] = 'contract[' . $contractIndex . '].fields[' . $fieldIndex . '] must be an object';
                continue;
            }

            $fieldName = (string) ($field['physical_name'] ?? '');
            if (!app_local_sqlite_schema_spike_identifier_is_safe($fieldName)) {
                $errors[] = 'field has unsafe physical name: ' . $fieldName;
            }
            foreach (['nullable', 'is_key'] as $requiredKey) {
                if (!array_key_exists($requiredKey, $field)) {
                    $errors[] = 'field ' . $fieldName . ' is missing ' . $requiredKey;
                }
            }
            if (!array_key_exists('default', $field)) {
                $errors[] = 'field ' . $fieldName . ' is missing default';
            }
        }
    }

    return $errors;
}

function app_local_sqlite_schema_spike_identifier_is_safe(string $identifier): bool
{
    return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) === 1;
}

function app_local_sqlite_schema_spike_quote_identifier(string $identifier): string
{
    if (!app_local_sqlite_schema_spike_identifier_is_safe($identifier)) {
        throw new RuntimeException('unsafe identifier: ' . $identifier);
    }

    return '"' . $identifier . '"';
}

function app_local_sqlite_schema_spike_type(string $type): string
{
    return match (strtolower(trim($type))) {
        'bool', 'boolean' => 'INTEGER',
        'int', 'integer', 'bigint' => 'INTEGER',
        'float', 'double', 'decimal' => 'REAL',
        'datetime', 'timestamp', 'date' => 'TEXT',
        'text' => 'TEXT',
        default => 'TEXT',
    };
}

function app_local_sqlite_schema_spike_default_sql(mixed $value): string
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
 * @return array{schema_sql:string,summary:array<string,mixed>}
 */
function app_local_sqlite_schema_spike_generate_schema(array $manifest): array
{
    $metadataColumns = [
        'local_updated_at',
        'last_synced_at',
        'sync_status',
        'dirty',
        'tombstone',
    ];
    $statements = [
        'PRAGMA foreign_keys = ON;',
        'CREATE TABLE IF NOT EXISTS "__app_local_schema_version" ("schema_key" TEXT PRIMARY KEY, "version" INTEGER NOT NULL);',
    ];
    $tables = [];

    foreach ($manifest['contracts'] as $contract) {
        $tableName = (string) ($contract['dataclass']['physical_name'] ?? $contract['contract_key']);
        $fieldNames = [];
        $keyFields = [];
        $columns = [];
        foreach ($contract['fields'] as $field) {
            $fieldName = (string) $field['physical_name'];
            $fieldNames[] = $fieldName;
            if (in_array($fieldName, $metadataColumns, true)) {
                throw new RuntimeException('business field collides with local metadata column: ' . $fieldName);
            }

            $column = app_local_sqlite_schema_spike_quote_identifier($fieldName)
                . ' '
                . app_local_sqlite_schema_spike_type((string) ($field['type'] ?? 'string'));
            if ((bool) ($field['nullable'] ?? true) === false) {
                $column .= ' NOT NULL';
            }
            $column .= app_local_sqlite_schema_spike_default_sql($field['default'] ?? null);
            $columns[] = $column;

            if ((bool) ($field['is_key'] ?? false)) {
                $keyFields[] = $fieldName;
            }
        }

        $columns[] = '"local_updated_at" TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP';
        $columns[] = '"last_synced_at" TEXT NULL';
        $columns[] = '"sync_status" TEXT NOT NULL DEFAULT ' . "'clean'";
        $columns[] = '"dirty" INTEGER NOT NULL DEFAULT 0';
        $columns[] = '"tombstone" INTEGER NOT NULL DEFAULT 0';
        if ($keyFields !== []) {
            $columns[] = 'PRIMARY KEY (' . implode(', ', array_map('app_local_sqlite_schema_spike_quote_identifier', $keyFields)) . ')';
        }

        $statements[] = 'CREATE TABLE IF NOT EXISTS '
            . app_local_sqlite_schema_spike_quote_identifier($tableName)
            . " (\n    "
            . implode(",\n    ", $columns)
            . "\n);";
        $statements[] = 'CREATE INDEX IF NOT EXISTS '
            . app_local_sqlite_schema_spike_quote_identifier('idx_' . $tableName . '_sync_status_dirty')
            . ' ON '
            . app_local_sqlite_schema_spike_quote_identifier($tableName)
            . ' ("sync_status", "dirty");';

        $tables[] = [
            'table' => $tableName,
            'core_fields' => $fieldNames,
            'key_fields' => $keyFields,
            'local_metadata_columns' => $metadataColumns,
        ];
    }

    return [
        'schema_sql' => implode("\n\n", $statements) . "\n",
        'summary' => [
            'table_count' => count($tables),
            'tables' => $tables,
            'metadata_column_policy' => 'append reserved local_* / sync columns after core contract fields',
        ],
    ];
}

/**
 * @return array<string,mixed>
 */
function app_local_sqlite_schema_spike_apply_schema(string $sqlitePath, string $schemaSql): array
{
    $dir = dirname($sqlitePath);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('failed to create SQLite directory: ' . $dir);
    }
    if (is_file($sqlitePath) && !unlink($sqlitePath)) {
        throw new RuntimeException('failed to replace SQLite DB: ' . $sqlitePath);
    }

    $pdo = new PDO('sqlite:' . $sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec($schemaSql);

    $tables = [];
    $tableRows = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tableRows as $row) {
        $tableName = (string) ($row['name'] ?? '');
        $columns = $pdo->query('PRAGMA table_info(' . app_local_sqlite_schema_spike_quote_identifier($tableName) . ')')->fetchAll(PDO::FETCH_ASSOC);
        $tables[$tableName] = array_map(
            static fn (array $column): string => (string) ($column['name'] ?? ''),
            $columns,
        );
    }

    return [
        'sqlite_path' => $sqlitePath,
        'tables' => $tables,
    ];
}

function app_local_sqlite_schema_spike_write(string $path, string $text): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('failed to create output directory: ' . $dir);
    }
    if (file_put_contents($path, $text) === false) {
        throw new RuntimeException('failed to write: ' . $path);
    }
}

$parsed = app_cli_local_sqlite_schema_spike_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_local_sqlite_schema_spike_usage() . PHP_EOL);
    exit(0);
}
if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_local_sqlite_schema_spike_usage() . PHP_EOL);
    exit(64);
}

try {
    $manifest = app_local_sqlite_schema_spike_load_manifest($parsed['sample'], $parsed['input_manifest']);
    $errors = app_local_sqlite_schema_spike_validate_manifest($manifest);
    if ($errors !== []) {
        throw new RuntimeException('manifest validation failed: ' . implode('; ', $errors));
    }

    $generated = app_local_sqlite_schema_spike_generate_schema($manifest);
    $outputDir = rtrim($parsed['output_dir'], '/');
    app_local_sqlite_schema_spike_write($outputDir . '/schema.sql', $generated['schema_sql']);
    $apply = app_local_sqlite_schema_spike_apply_schema($parsed['sqlite_path'], $generated['schema_sql']);

    $summary = $generated['summary'] + [
        'ok' => true,
        'manifest_source' => $parsed['sample'] !== '' ? 'sample:' . $parsed['sample'] : $parsed['input_manifest'],
        'schema_sql' => $outputDir . '/schema.sql',
        'apply' => $apply,
        'conclusion' => 'Explicit contract semantics can produce and apply a small App-local SQLite schema with local metadata columns kept separate from core fields.',
    ];
    app_local_sqlite_schema_spike_write(
        $outputDir . '/summary.json',
        json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL,
    );

    fwrite(STDOUT, json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
    exit(0);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
