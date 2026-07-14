#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__) . '/app/firebird_mysql_promotion_rehearsal.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,pretty:bool,dsn:string,user:string,password:string,error:string}
 */
function app_cli_sample34_firebird_mysql_export_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'pretty' => false,
        'dsn' => trim((string) getenv('MTOOL_FIREBIRD_DSN')),
        'user' => trim((string) (getenv('MTOOL_FIREBIRD_USER') ?: '')),
        'password' => (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: ''),
        'error' => '',
    ];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $parsed['help'] = true;
            continue;
        }
        if ($argument === '--pretty') {
            $parsed['pretty'] = true;
            continue;
        }
        if (str_starts_with($argument, '--dsn=')) {
            $parsed['dsn'] = trim(substr($argument, strlen('--dsn=')));
            continue;
        }
        if (str_starts_with($argument, '--user=')) {
            $parsed['user'] = trim(substr($argument, strlen('--user=')));
            continue;
        }
        if (str_starts_with($argument, '--password=')) {
            $parsed['password'] = substr($argument, strlen('--password='));
            continue;
        }
        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }
    return $parsed;
}

function app_cli_sample34_firebird_mysql_export_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample34_firebird_mysql_export_smoke.php [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN       PDO Firebird DSN
  MTOOL_FIREBIRD_USER      Firebird user
  MTOOL_FIREBIRD_PASSWORD  Firebird password

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Reads the disposable sample34 tables produced by sample34-firebird-promotion-smoke.
  - Opens no MySQL/MariaDB connection and performs no mutation.
TEXT;
}

/** @return array<string,mixed> */
function app_sample34_firebird_mysql_export_smoke_result(
    bool $ok,
    string $stage,
    string $error,
    array $details = [],
): array {
    return [
        'ok' => $ok,
        'stage' => $stage,
        'error' => $error,
        'mutation_performed' => false,
        'details' => $details,
    ];
}

/** @return array<string,mixed> */
function app_sample34_firebird_mysql_export_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample34_firebird_mysql_export_smoke_result(false, 'runtime_preflight', 'pdo_firebird_extension_missing', [
            'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
            'required_driver' => 'firebird',
        ]);
    }
    if (trim($dsn) === '') {
        return app_sample34_firebird_mysql_export_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    }

    try {
        $fixture = app_sample34_firebird_mysql_export_smoke_fixture();
        $manifest = app_firebird_mysql_promotion_manifest_build(
            $fixture['canonical_snapshot'],
            app_sample34_firebird_mysql_export_smoke_inspection($fixture),
            ['target_identity' => 'sample34-mysql-target'],
        );
        $schema = app_firebird_mysql_target_schema_plan($manifest);

        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $export = app_firebird_mysql_export($pdo, $manifest, 1);
        $package = app_firebird_mysql_promotion_rehearsal_package($manifest, $schema, $export);
        if (($package['rehearsal_ready'] ?? false) !== true) {
            return app_sample34_firebird_mysql_export_smoke_result(false, 'rehearsal', 'firebird_mysql_rehearsal_not_ready', [
                'manifest' => [
                    'ok' => $manifest['ok'] ?? false,
                    'blockers' => $manifest['blockers'] ?? [],
                ],
                'schema' => [
                    'ok' => $schema['ok'] ?? false,
                    'errors' => $schema['errors'] ?? [],
                ],
                'export' => [
                    'ok' => $export['ok'] ?? false,
                    'errors' => $export['errors'] ?? [],
                ],
                'package' => $package,
            ]);
        }

        $summary = app_firebird_mysql_chunk_summary($export);
        $errors = [];
        if ($summary['table_count'] !== 2) $errors[] = 'unexpected_table_count';
        if ($summary['chunk_count'] !== 3) $errors[] = 'unexpected_chunk_count';
        if ($summary['row_count'] !== 3) $errors[] = 'unexpected_row_count';
        $payload = $export['chunks'][2]['rows'][0]['payload'] ?? null;
        if ($payload !== ['encoding' => 'json', 'value' => ['a' => 1, 'z' => 2]]) $errors[] = 'payload_json_conversion_failed';
        $bytes = $export['chunks'][2]['rows'][0]['bytes'] ?? null;
        if ($bytes !== ['encoding' => 'base64', 'byte_length' => 3, 'value' => 'AEEB']) $errors[] = 'blob_base64_conversion_failed';
        if ($errors !== []) {
            return app_sample34_firebird_mysql_export_smoke_result(false, 'assertions', implode(',', $errors), [
                'export_summary' => $summary,
                'chunks' => $export['chunks'] ?? [],
            ]);
        }

        return app_sample34_firebird_mysql_export_smoke_result(true, 'sample34_firebird_mysql_export_smoke', '', [
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'rehearsal_package_sha256' => (string) $package['rehearsal_package_sha256'],
            'export_summary' => $summary,
        ]);
    } catch (Throwable $throwable) {
        return app_sample34_firebird_mysql_export_smoke_result(false, 'exception', $throwable->getMessage());
    }
}

/** @return array<string,mixed> */
function app_sample34_firebird_mysql_export_smoke_fixture(): array
{
    $path = app_sample_pack_reference_root('sample34-sqlite-to-firebird-promotion') . '/promotion-contract-input.json';
    $json = file_get_contents($path);
    if (!is_string($json)) throw new RuntimeException('sample34_fixture_not_found');
    $fixture = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($fixture)) throw new RuntimeException('sample34_fixture_invalid');
    return $fixture;
}

/** @param array<string,mixed> $fixture @return array<string,mixed> */
function app_sample34_firebird_mysql_export_smoke_inspection(array $fixture): array
{
    $canonicalTables = app_sqlite_mysql_promotion_tables($fixture['canonical_snapshot']['tables'] ?? []);
    $sourceTables = app_sqlite_mysql_promotion_tables($fixture['sqlite_inspection']['tables'] ?? []);
    $tables = [];
    foreach ($canonicalTables as $tableName => $canonical) {
        $source = $sourceTables[$tableName] ?? [];
        $columns = [];
        foreach ($canonical['columns'] as $columnName => $column) {
            $sourceColumn = is_array($source['columns'][$columnName] ?? null) ? $source['columns'][$columnName] : [];
            $columns[] = [
                'name' => $columnName,
                'type' => app_sample34_firebird_mysql_export_smoke_firebird_type((string) ($column['type'] ?? '')),
                'nullable' => (bool) ($column['nullable'] ?? false),
                'default' => $column['default'] ?? null,
                'profile' => app_sqlite_mysql_promotion_profile($sourceColumn['profile'] ?? []),
            ];
        }
        $tables[] = [
            'name' => $tableName,
            'row_count' => max(0, (int) ($source['row_count'] ?? 0)),
            'keys' => array_values($canonical['keys'] ?? []),
            'foreign_keys' => array_values($canonical['foreign_keys'] ?? []),
            'columns' => $columns,
        ];
    }
    usort($tables, static fn (array $a, array $b): int => (string) $a['name'] <=> (string) $b['name']);
    return [
        'inspection_version' => APP_FIREBIRD_SOURCE_INSPECTION_VERSION,
        'ok' => true,
        'stage' => 'source_inspection',
        'driver' => 'firebird',
        'mutation_performed' => false,
        'source_identity' => 'sample34-firebird-live-export',
        'tables' => $tables,
        'blockers' => [],
        'warnings' => [],
    ];
}

function app_sample34_firebird_mysql_export_smoke_firebird_type(string $canonicalType): string
{
    $type = strtolower(trim($canonicalType));
    if (preg_match('/^(int|integer|bigint)/', $type) === 1) return 'BIGINT';
    if (preg_match('/^(bool|boolean)/', $type) === 1) return 'SMALLINT';
    if (preg_match('/^(decimal|numeric)\s*\(/', $type) === 1) return strtoupper($type);
    if (preg_match('/^(datetime|timestamp)/', $type) === 1) return 'TIMESTAMP';
    if ($type === 'json' || in_array($type, ['text', 'string'], true)) return 'BLOB SUB_TYPE TEXT';
    if (preg_match('/^(blob|binary|varbinary)/', $type) === 1) return 'BLOB SUB_TYPE BINARY';
    if (preg_match('/^(varchar|char)\s*\(/', $type) === 1) return strtoupper($type);
    return strtoupper($canonicalType);
}

if (realpath((string) ($argv[0] ?? '')) === __FILE__) {
    $parsed = app_cli_sample34_firebird_mysql_export_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_sample34_firebird_mysql_export_usage() . PHP_EOL);
        exit(0);
    }
    if ($parsed['error'] !== '') {
        fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample34_firebird_mysql_export_usage() . PHP_EOL);
        exit(2);
    }
    $result = app_sample34_firebird_mysql_export_smoke($parsed['dsn'], $parsed['user'], $parsed['password']);
    $json = json_encode($result, ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    fwrite(($result['ok'] ?? false) ? STDOUT : STDERR, $json . PHP_EOL);
    exit(($result['ok'] ?? false) ? 0 : 1);
}
