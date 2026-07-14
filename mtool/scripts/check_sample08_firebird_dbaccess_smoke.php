#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Focused sample08 Firebird DBAccess smoke.
 *
 * Proves the generated sample08 join/read-model DBAccess can read from a
 * Firebird profile without changing generated classes.
 *
 * @param list<string> $argv
 * @return array{help:bool,dsn:string,user:string,password:string,pretty:bool,error:string}
 */
function app_cli_sample08_firebird_parse_args(array $argv): array
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

function app_cli_sample08_firebird_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample08_firebird_dbaccess_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN
  MTOOL_FIREBIRD_USER
  MTOOL_FIREBIRD_PASSWORD

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Uses the generated sample08 DBAccess classes unchanged.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_sample08_firebird_smoke_result(
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
function app_sample08_firebird_dbaccess_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample08_firebird_smoke_result(
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
        return app_sample08_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
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

        foreach (['BLOG_POST', 'BLOG_AUTHOR'] as $tableName) {
            try {
                $pdo->exec('DROP TABLE ' . $tableName);
            } catch (Throwable) {
                // Disposable smoke tables may not exist.
            }
        }

        $pdo->exec(
            'CREATE TABLE BLOG_AUTHOR (
                ID INTEGER NOT NULL PRIMARY KEY,
                NAME VARCHAR(120) NOT NULL,
                IS_ACTIVE SMALLINT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE BLOG_POST (
                ID INTEGER NOT NULL PRIMARY KEY,
                BLOG_AUTHOR_ID INTEGER NOT NULL,
                TITLE VARCHAR(160) NOT NULL,
                STATUS VARCHAR(40) NOT NULL
            )'
        );

        $insertAuthor = $pdo->prepare('INSERT INTO BLOG_AUTHOR (ID, NAME, IS_ACTIVE) VALUES (?, ?, ?)');
        $insertAuthor->execute([1, 'Active Author', 1]);
        $insertAuthor->execute([2, 'Inactive Author', 0]);

        $insertPost = $pdo->prepare('INSERT INTO BLOG_POST (ID, BLOG_AUTHOR_ID, TITLE, STATUS) VALUES (?, ?, ?, ?)');
        $insertPost->execute([10, 1, 'Published Firebird post', 'published']);
        $insertPost->execute([20, 1, 'Draft Firebird post', 'draft']);
        $insertPost->execute([30, 2, 'Inactive author post', 'published']);

        putenv('MTOOL_RUNTIME_DB_DSN=' . $dsn);
        putenv('MTOOL_RUNTIME_DB_USER=' . $user);
        putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $password);

        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample08-dbaccess-join-read-model/reference/DATACLASS-PHP/data-BlogPostAuthorSummary.php';
        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample08-dbaccess-join-read-model/reference/DBACCESS-PHP/dbaccess-BlogPost.php';

        $dbAccess = new BlogPostDBAccess();
        $rows = $dbAccess->GetPublishedBlogPostAuthorSummaryList();
        if (!is_array($rows)) {
            return app_sample08_firebird_smoke_result(false, 'dbaccess_read', 'dbaccess_returned_non_array', [
                'type' => get_debug_type($rows),
            ], true);
        }
        if (count($rows) !== 1) {
            return app_sample08_firebird_smoke_result(false, 'dbaccess_read', 'unexpected_row_count', [
                'row_count' => count($rows),
            ], true);
        }

        $summary = array_map(
            static fn (object $row): array => [
                'blogPostId' => (int) ($row->blogPostId ?? 0),
                'blogPostTitle' => (string) ($row->blogPostTitle ?? ''),
                'blogAuthorId' => (int) ($row->blogAuthorId ?? 0),
                'blogAuthorName' => (string) ($row->blogAuthorName ?? ''),
            ],
            $rows,
        );

        $expected = [[
            'blogPostId' => 10,
            'blogPostTitle' => 'Published Firebird post',
            'blogAuthorId' => 1,
            'blogAuthorName' => 'Active Author',
        ]];
        if ($summary !== $expected) {
            return app_sample08_firebird_smoke_result(false, 'dbaccess_read', 'unexpected_rows', [
                'expected' => $expected,
                'actual' => $summary,
            ], true);
        }

        return app_sample08_firebird_smoke_result(true, 'ok', '', [
            'sample' => 'sample08-dbaccess-join-read-model',
            'pdo_driver' => (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'tables' => ['BLOG_AUTHOR', 'BLOG_POST'],
            'row_count' => count($summary),
            'rows' => $summary,
        ], true);
    } catch (Throwable $throwable) {
        return app_sample08_firebird_smoke_result(false, 'connection_or_dbaccess', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
        ], true);
    }
}

$parsed = app_cli_sample08_firebird_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample08_firebird_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample08_firebird_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample08_firebird_dbaccess_smoke(
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

