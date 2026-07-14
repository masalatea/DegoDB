#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Focused sample05 Firebird DBAccess smoke.
 *
 * This is an opt-in Firebird sample proof for F100 sample-first support.
 * It intentionally uses the generated sample05 DBAccess classes unchanged:
 *
 * 1. create a disposable NOTICE table in Firebird;
 * 2. insert deterministic rows;
 * 3. read them through NoticeDBAccess::GetNoticeList();
 * 4. verify ordering and field hydration.
 *
 * It must not participate in normal `make test`.
 *
 * @param list<string> $argv
 * @return array{help:bool,dsn:string,user:string,password:string,pretty:bool,error:string}
 */
function app_cli_sample05_firebird_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
        'dsn' => trim((string) getenv('MTOOL_FIREBIRD_DSN')),
        'user' => trim((string) (getenv('MTOOL_FIREBIRD_USER') ?: '')),
        'password' => (string) (getenv('MTOOL_FIREBIRD_PASSWORD') ?: ''),
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

        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_sample05_firebird_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample05_firebird_dbaccess_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN
  MTOOL_FIREBIRD_USER
  MTOOL_FIREBIRD_PASSWORD

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Uses the generated sample05 DBAccess classes unchanged.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_sample05_firebird_smoke_result(
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
function app_sample05_firebird_dbaccess_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample05_firebird_smoke_result(
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
        return app_sample05_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    }

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
            $pdo->exec('DROP TABLE NOTICE');
        } catch (Throwable) {
            // NOTICE is a disposable smoke table.
        }

        $pdo->exec(
            'CREATE TABLE NOTICE (
                ID INTEGER NOT NULL PRIMARY KEY,
                TITLE VARCHAR(120) NOT NULL,
                BODY BLOB SUB_TYPE TEXT,
                SORT_ORDER INTEGER NOT NULL
            )'
        );

        $insert = $pdo->prepare('INSERT INTO NOTICE (ID, TITLE, BODY, SORT_ORDER) VALUES (?, ?, ?, ?)');
        $insert->execute([20, 'Second notice', 'Firebird sample05 body B', 20]);
        $insert->execute([10, 'First notice', 'Firebird sample05 body A', 10]);

        putenv('MTOOL_RUNTIME_DB_DSN=' . $dsn);
        putenv('MTOOL_RUNTIME_DB_USER=' . $user);
        putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $password);

        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample05-dbaccess-select-basic/reference/DATACLASS-PHP/data-Notice.php';
        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample05-dbaccess-select-basic/reference/DBACCESS-PHP/dbaccess-Notice.php';

        $dbAccess = new NoticeDBAccess();
        $rows = $dbAccess->GetNoticeList();
        if (!is_array($rows)) {
            return app_sample05_firebird_smoke_result(false, 'dbaccess_read', 'dbaccess_returned_non_array', [
                'type' => get_debug_type($rows),
            ], true);
        }
        if (count($rows) !== 2) {
            return app_sample05_firebird_smoke_result(false, 'dbaccess_read', 'unexpected_row_count', [
                'row_count' => count($rows),
            ], true);
        }

        $summary = array_map(
            static fn (object $row): array => [
                'id' => (int) ($row->id ?? 0),
                'title' => (string) ($row->title ?? ''),
                'body' => (string) ($row->body ?? ''),
                'sortOrder' => (int) ($row->sortOrder ?? 0),
            ],
            $rows,
        );

        $expected = [
            ['id' => 10, 'title' => 'First notice', 'body' => 'Firebird sample05 body A', 'sortOrder' => 10],
            ['id' => 20, 'title' => 'Second notice', 'body' => 'Firebird sample05 body B', 'sortOrder' => 20],
        ];
        if ($summary !== $expected) {
            return app_sample05_firebird_smoke_result(false, 'dbaccess_read', 'unexpected_rows', [
                'expected' => $expected,
                'actual' => $summary,
            ], true);
        }

        return app_sample05_firebird_smoke_result(true, 'ok', '', [
            'sample' => 'sample05-dbaccess-select-basic',
            'pdo_driver' => (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'table' => 'NOTICE',
            'row_count' => count($summary),
            'rows' => $summary,
        ], true);
    } catch (Throwable $throwable) {
        return app_sample05_firebird_smoke_result(false, 'connection_or_dbaccess', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
        ], true);
    }
}

$parsed = app_cli_sample05_firebird_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample05_firebird_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample05_firebird_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample05_firebird_dbaccess_smoke(
    $parsed['dsn'],
    $parsed['user'],
    $parsed['password'],
);

fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0),
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);

