#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/check_sample34_firebird_mysql_export_smoke.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,pretty:bool,firebird_dsn:string,firebird_user:string,firebird_password:string,mysql_dsn:string,mysql_user:string,mysql_password:string,error:string}
 */
function app_cli_sample34_firebird_mysql_import_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'pretty' => false,
        'firebird_dsn' => trim((string) getenv('MTOOL_FIREBIRD_DSN')),
        'firebird_user' => trim((string) (getenv('MTOOL_FIREBIRD_USER') ?: '')),
        'firebird_password' => (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: ''),
        'mysql_dsn' => trim((string) getenv('MTOOL_MYSQL_DSN')),
        'mysql_user' => trim((string) (getenv('MTOOL_MYSQL_USER') ?: '')),
        'mysql_password' => (string) (getenv('MTOOL_MYSQL_PASSWORD') ?: ''),
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
        if (str_starts_with($argument, '--firebird-dsn=')) {
            $parsed['firebird_dsn'] = trim(substr($argument, strlen('--firebird-dsn=')));
            continue;
        }
        if (str_starts_with($argument, '--firebird-user=')) {
            $parsed['firebird_user'] = trim(substr($argument, strlen('--firebird-user=')));
            continue;
        }
        if (str_starts_with($argument, '--firebird-password=')) {
            $parsed['firebird_password'] = substr($argument, strlen('--firebird-password='));
            continue;
        }
        if (str_starts_with($argument, '--mysql-dsn=')) {
            $parsed['mysql_dsn'] = trim(substr($argument, strlen('--mysql-dsn=')));
            continue;
        }
        if (str_starts_with($argument, '--mysql-user=')) {
            $parsed['mysql_user'] = trim(substr($argument, strlen('--mysql-user=')));
            continue;
        }
        if (str_starts_with($argument, '--mysql-password=')) {
            $parsed['mysql_password'] = substr($argument, strlen('--mysql-password='));
            continue;
        }
        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }
    return $parsed;
}

function app_cli_sample34_firebird_mysql_import_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample34_firebird_mysql_import_smoke.php [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN       PDO Firebird DSN
  MTOOL_FIREBIRD_USER      Firebird user
  MTOOL_FIREBIRD_PASSWORD  Firebird password
  MTOOL_MYSQL_DSN          PDO MySQL/MariaDB DSN for a disposable target
  MTOOL_MYSQL_USER         MySQL/MariaDB user
  MTOOL_MYSQL_PASSWORD     MySQL/MariaDB password

Notes:
  - Requires PHP PDO_FIREBIRD and PDO_MYSQL.
  - Reads the disposable sample34 Firebird tables produced by sample34-firebird-promotion-smoke.
  - Mutates only the disposable MySQL/MariaDB target tables and requires explicit import approval internally.
TEXT;
}

/** @return array<string,mixed> */
function app_sample34_firebird_mysql_import_smoke_result(
    bool $ok,
    string $stage,
    string $error,
    array $details = [],
    bool $mutationPerformed = false,
): array {
    return [
        'ok' => $ok,
        'stage' => $stage,
        'error' => $error,
        'mutation_performed' => $mutationPerformed,
        'details' => $details,
    ];
}

/** @return array<string,mixed> */
function app_sample34_firebird_mysql_import_smoke(
    string $firebirdDsn,
    string $firebirdUser,
    string $firebirdPassword,
    string $mysqlDsn,
    string $mysqlUser,
    string $mysqlPassword,
): array {
    if (!extension_loaded('pdo_firebird')) {
        return app_sample34_firebird_mysql_import_smoke_result(false, 'runtime_preflight', 'pdo_firebird_extension_missing', [
            'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
            'required_driver' => 'firebird',
        ]);
    }
    if (!extension_loaded('pdo_mysql')) {
        return app_sample34_firebird_mysql_import_smoke_result(false, 'runtime_preflight', 'pdo_mysql_extension_missing', [
            'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
            'required_driver' => 'mysql',
        ]);
    }
    if (trim($firebirdDsn) === '') return app_sample34_firebird_mysql_import_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    if (trim($mysqlDsn) === '') return app_sample34_firebird_mysql_import_smoke_result(false, 'runtime_preflight', 'mysql_dsn_required');

    try {
        $fixture = app_sample34_firebird_mysql_export_smoke_fixture();
        $manifest = app_firebird_mysql_promotion_manifest_build(
            $fixture['canonical_snapshot'],
            app_sample34_firebird_mysql_export_smoke_inspection($fixture),
            ['target_identity' => 'sample34-mysql-target'],
        );
        $schema = app_firebird_mysql_target_schema_plan($manifest);
        if (($schema['ok'] ?? false) !== true) {
            return app_sample34_firebird_mysql_import_smoke_result(false, 'target_schema', 'target_schema_not_ready', [
                'errors' => $schema['errors'] ?? [],
            ]);
        }

        $firebird = new PDO($firebirdDsn, $firebirdUser, $firebirdPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $export = app_firebird_mysql_export($firebird, $manifest, 1);
        $package = app_firebird_mysql_promotion_rehearsal_package($manifest, $schema, $export);
        if (($package['rehearsal_ready'] ?? false) !== true) {
            return app_sample34_firebird_mysql_import_smoke_result(false, 'rehearsal', 'firebird_mysql_rehearsal_not_ready', [
                'export' => ['ok' => $export['ok'] ?? false, 'errors' => $export['errors'] ?? []],
                'package' => $package,
            ]);
        }

        $mysql = new PDO($mysqlDsn, $mysqlUser, $mysqlPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        app_sample34_firebird_mysql_import_drop_tables($mysql, $manifest);
        foreach ($schema['statements'] as $statement) {
            $mysql->exec((string) $statement);
        }

        $checkpoint = [];
        foreach ($export['chunks'] as $chunk) {
            $import = app_firebird_mysql_import_chunk($mysql, $manifest, $chunk, $checkpoint, true);
            if (($import['ok'] ?? false) !== true) {
                return app_sample34_firebird_mysql_import_smoke_result(false, 'import', (string) ($import['error'] ?? 'import_failed'), [
                    'import' => $import,
                    'chunk' => ['table' => $chunk['table'] ?? '', 'chunk_index' => $chunk['chunk_index'] ?? null],
                ], true);
            }
            $checkpoint = is_array($import['checkpoint'] ?? null) ? $import['checkpoint'] : [];
        }

        $retry = app_firebird_mysql_import_chunk($mysql, $manifest, $export['chunks'][0], $checkpoint, true);
        $verification = app_sample34_firebird_mysql_import_verify($mysql);
        if (($retry['stage'] ?? '') !== 'already_committed' || ($retry['mutation_performed'] ?? true) !== false) {
            $verification['errors'][] = 'checkpoint_retry_failed';
        }
        if (($verification['ok'] ?? false) !== true) {
            return app_sample34_firebird_mysql_import_smoke_result(false, 'verification', 'mysql_import_verification_failed', [
                'verification' => $verification,
                'checkpoint' => $checkpoint,
                'retry' => $retry,
            ], true);
        }

        return app_sample34_firebird_mysql_import_smoke_result(true, 'sample34_firebird_mysql_import_smoke', '', [
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'rehearsal_package_sha256' => (string) $package['rehearsal_package_sha256'],
            'checkpoint_sha256' => app_sqlite_mysql_promotion_digest($checkpoint),
            'checkpoint_completed_count' => count(is_array($checkpoint['completed'] ?? null) ? $checkpoint['completed'] : []),
            'verification' => $verification,
        ], true);
    } catch (Throwable $throwable) {
        return app_sample34_firebird_mysql_import_smoke_result(false, 'exception', $throwable->getMessage(), [], true);
    }
}

/** @param array<string,mixed> $manifest */
function app_sample34_firebird_mysql_import_drop_tables(PDO $pdo, array $manifest): void
{
    foreach (array_reverse(is_array($manifest['tables'] ?? null) ? $manifest['tables'] : []) as $table) {
        if (is_array($table)) $pdo->exec('DROP TABLE IF EXISTS ' . app_sqlite_mysql_target_quote_identifier((string) $table['name']));
    }
}

/** @return array<string,mixed> */
function app_sample34_firebird_mysql_import_verify(PDO $pdo): array
{
    $errors = [];
    $parentCount = (int) $pdo->query('SELECT COUNT(*) FROM `parent`')->fetchColumn();
    $recordCount = (int) $pdo->query('SELECT COUNT(*) FROM `record`')->fetchColumn();
    if ($parentCount !== 1) $errors[] = 'parent_row_count';
    if ($recordCount !== 2) $errors[] = 'record_row_count';
    if ((string) $pdo->query('SELECT `code` FROM `parent` WHERE `id` = 2')->fetchColumn() !== 'P2') $errors[] = 'parent_value';
    if ((string) $pdo->query('SELECT JSON_COMPACT(`payload`) FROM `record` WHERE `id` = 9')->fetchColumn() !== '{"a":1,"z":2}') $errors[] = 'json_value';
    if ((string) $pdo->query('SELECT TO_BASE64(`bytes`) FROM `record` WHERE `id` = 9')->fetchColumn() !== 'AEEB') $errors[] = 'blob_value';
    return [
        'ok' => $errors === [],
        'row_counts' => ['parent' => $parentCount, 'record' => $recordCount],
        'checks' => ['row_counts', 'parent_value', 'json_value', 'blob_value', 'checkpoint_retry'],
        'errors' => $errors,
    ];
}

$parsed = app_cli_sample34_firebird_mysql_import_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample34_firebird_mysql_import_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample34_firebird_mysql_import_usage() . PHP_EOL);
    exit(2);
}
$result = app_sample34_firebird_mysql_import_smoke(
    $parsed['firebird_dsn'],
    $parsed['firebird_user'],
    $parsed['firebird_password'],
    $parsed['mysql_dsn'],
    $parsed['mysql_user'],
    $parsed['mysql_password'],
);
$json = json_encode($result, ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
fwrite(($result['ok'] ?? false) ? STDOUT : STDERR, $json . PHP_EOL);
exit(($result['ok'] ?? false) ? 0 : 1);
