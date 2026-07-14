#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Focused sample09 Firebird DBAccess smoke.
 *
 * Proves the generated sample09 aggregate/report DBAccess can read from a
 * Firebird profile without changing generated classes.
 *
 * @param list<string> $argv
 * @return array{help:bool,dsn:string,user:string,password:string,pretty:bool,error:string}
 */
function app_cli_sample09_firebird_parse_args(array $argv): array
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

function app_cli_sample09_firebird_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample09_firebird_dbaccess_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN
  MTOOL_FIREBIRD_USER
  MTOOL_FIREBIRD_PASSWORD

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Uses the generated sample09 DBAccess classes unchanged.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_sample09_firebird_smoke_result(
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
function app_sample09_firebird_dbaccess_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample09_firebird_smoke_result(
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
        return app_sample09_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
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

        foreach (['SALES_RECORD', 'SALES_CATEGORY'] as $tableName) {
            try {
                $pdo->exec('DROP TABLE ' . $tableName);
            } catch (Throwable) {
                // Disposable smoke tables may not exist.
            }
        }

        $pdo->exec(
            'CREATE TABLE SALES_CATEGORY (
                ID INTEGER NOT NULL PRIMARY KEY,
                NAME VARCHAR(120) NOT NULL,
                IS_ACTIVE SMALLINT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE SALES_RECORD (
                ID INTEGER NOT NULL PRIMARY KEY,
                SALES_CATEGORY_ID INTEGER NOT NULL,
                AMOUNT INTEGER NOT NULL,
                STATUS VARCHAR(40) NOT NULL
            )'
        );

        $insertCategory = $pdo->prepare('INSERT INTO SALES_CATEGORY (ID, NAME, IS_ACTIVE) VALUES (?, ?, ?)');
        $insertCategory->execute([1, 'Hardware', 1]);
        $insertCategory->execute([2, 'Software', 1]);
        $insertCategory->execute([3, 'Inactive Services', 0]);

        $insertRecord = $pdo->prepare('INSERT INTO SALES_RECORD (ID, SALES_CATEGORY_ID, AMOUNT, STATUS) VALUES (?, ?, ?, ?)');
        $insertRecord->execute([101, 1, 70, 'closed']);
        $insertRecord->execute([102, 1, 80, 'closed']);
        $insertRecord->execute([103, 1, 999, 'open']);
        $insertRecord->execute([201, 2, 40, 'closed']);
        $insertRecord->execute([202, 2, 50, 'closed']);
        $insertRecord->execute([301, 3, 100, 'closed']);
        $insertRecord->execute([302, 3, 100, 'closed']);

        putenv('MTOOL_RUNTIME_DB_DSN=' . $dsn);
        putenv('MTOOL_RUNTIME_DB_USER=' . $user);
        putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $password);

        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample09-dbaccess-aggregate-report/reference/DATACLASS-PHP/data-SalesCategoryReport.php';
        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample09-dbaccess-aggregate-report/reference/DBACCESS-PHP/dbaccess-SalesRecord.php';

        $dbAccess = new SalesRecordDBAccess();
        $rows = $dbAccess->GetClosedSalesCategoryReportList();
        if (!is_array($rows)) {
            return app_sample09_firebird_smoke_result(false, 'dbaccess_read', 'dbaccess_returned_non_array', [
                'type' => get_debug_type($rows),
            ], true);
        }
        if (count($rows) !== 1) {
            return app_sample09_firebird_smoke_result(false, 'dbaccess_read', 'unexpected_row_count', [
                'row_count' => count($rows),
            ], true);
        }

        $summary = array_map(
            static fn (object $row): array => [
                'salesCategoryId' => (int) ($row->salesCategoryId ?? 0),
                'salesCategoryName' => (string) ($row->salesCategoryName ?? ''),
                'closedSaleCount' => (int) ($row->closedSaleCount ?? 0),
                'closedSaleTotalAmount' => (int) ($row->closedSaleTotalAmount ?? 0),
            ],
            $rows,
        );

        $expected = [[
            'salesCategoryId' => 1,
            'salesCategoryName' => 'Hardware',
            'closedSaleCount' => 2,
            'closedSaleTotalAmount' => 150,
        ]];
        if ($summary !== $expected) {
            return app_sample09_firebird_smoke_result(false, 'dbaccess_read', 'unexpected_rows', [
                'expected' => $expected,
                'actual' => $summary,
            ], true);
        }

        return app_sample09_firebird_smoke_result(true, 'ok', '', [
            'sample' => 'sample09-dbaccess-aggregate-report',
            'pdo_driver' => (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'tables' => ['SALES_CATEGORY', 'SALES_RECORD'],
            'row_count' => count($summary),
            'rows' => $summary,
        ], true);
    } catch (Throwable $throwable) {
        return app_sample09_firebird_smoke_result(false, 'connection_or_dbaccess', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
        ], true);
    }
}

$parsed = app_cli_sample09_firebird_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample09_firebird_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample09_firebird_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample09_firebird_dbaccess_smoke(
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

