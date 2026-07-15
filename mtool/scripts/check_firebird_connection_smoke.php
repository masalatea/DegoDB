#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Focused, opt-in Firebird connection smoke.
 *
 * This script intentionally does not participate in normal `make test`.
 * It is a runtime proof entry point for the Firebird local durable profile
 * feasibility lane. It should be safe to run against a disposable test
 * database only.
 *
 * @param list<string> $argv
 * @return array{
 *     help:bool,
 *     dsn:string,
 *     user:string,
 *     password:string,
 *     table:string,
 *     pretty:bool,
 *     error:string
 * }
 */
function app_cli_firebird_smoke_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'dsn' => trim((string) getenv('MTOOL_FIREBIRD_DSN')),
        'user' => trim((string) (getenv('MTOOL_FIREBIRD_USER') ?: '')),
        'password' => (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: ''),
        'table' => trim((string) (getenv('MTOOL_FIREBIRD_SMOKE_TABLE') ?: 'MTOOL_FIREBIRD_SMOKE')),
        'pretty' => false,
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
        if (str_starts_with($argument, '--table=')) {
            $parsed['table'] = trim(substr($argument, strlen('--table=')));
            continue;
        }

        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_firebird_smoke_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_firebird_connection_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN              PDO Firebird DSN, e.g. firebird:dbname=127.0.0.1/3050:/path/to/test.fdb;charset=UTF8
  MTOOL_FIREBIRD_USER             Firebird user
  MTOOL_FIREBIRD_PASSWORD         Firebird password
  MTOOL_FIREBIRD_SMOKE_TABLE      Disposable smoke table name. default: MTOOL_FIREBIRD_SMOKE

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_firebird_smoke_result(
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

function app_firebird_smoke_identifier(string $identifier): string
{
    $trimmed = strtoupper(trim($identifier));
    if (preg_match('/^[A-Z][A-Z0-9_]{0,30}$/', $trimmed) !== 1) {
        throw new InvalidArgumentException('invalid Firebird smoke table identifier: ' . $identifier);
    }

    return $trimmed;
}

/** @return array<string,mixed> */
function app_firebird_connection_smoke(string $dsn, string $user, string $password, string $table): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_firebird_smoke_result(
            false,
            'runtime_preflight',
            'pdo_firebird_extension_missing',
            [
                'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
                'required_driver' => 'firebird',
            ],
        );
    }
    if (trim($dsn) === '') {
        return app_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    }

    $tableName = app_firebird_smoke_identifier($table);
    try {
        $pdo = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );

        try {
            $pdo->exec('DROP TABLE ' . $tableName);
        } catch (Throwable) {
            // The table may not exist. This is a disposable smoke table.
        }

        $pdo->exec(
            'CREATE TABLE ' . $tableName . ' (
                ID INTEGER NOT NULL PRIMARY KEY,
                NOTE VARCHAR(80),
                CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->beginTransaction();
        $insert = $pdo->prepare('INSERT INTO ' . $tableName . ' (ID, NOTE) VALUES (?, ?)');
        $insert->execute([1, 'rollback']);
        $pdo->rollBack();

        $rollbackCount = (int) $pdo->query('SELECT COUNT(*) FROM ' . $tableName)->fetchColumn();
        if ($rollbackCount !== 0) {
            return app_firebird_smoke_result(false, 'transaction', 'rollback_did_not_clear_row', [
                'rollback_count' => $rollbackCount,
            ], true);
        }

        $pdo->beginTransaction();
        $insert->execute([2, 'commit']);
        $pdo->commit();

        $select = $pdo->prepare('SELECT ID, NOTE FROM ' . $tableName . ' WHERE ID = ?');
        $select->execute([2]);
        $row = $select->fetch();

        $version = '';
        try {
            $versionValue = $pdo->query("SELECT RDB\$GET_CONTEXT('SYSTEM', 'ENGINE_VERSION') AS ENGINE_VERSION FROM RDB\$DATABASE")->fetchColumn();
            $version = is_string($versionValue) ? $versionValue : '';
        } catch (Throwable) {
            $version = '';
        }

        return app_firebird_smoke_result(true, 'ok', '', [
            'pdo_driver' => (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'engine_version' => $version,
            'table' => $tableName,
            'selected_row' => $row,
        ], true);
    } catch (Throwable $throwable) {
        return app_firebird_smoke_result(false, 'connection_or_sql', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
            'table' => $tableName,
        ], true);
    }
}

$parsed = app_cli_firebird_smoke_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_firebird_smoke_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_firebird_smoke_usage() . PHP_EOL);
    exit(64);
}

$result = app_firebird_connection_smoke(
    $parsed['dsn'],
    $parsed['user'],
    $parsed['password'],
    $parsed['table'],
);

fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0),
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);
