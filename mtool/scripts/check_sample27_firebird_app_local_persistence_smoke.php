#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/app_local_sqlite_dbaccess.php';

/**
 * Focused sample27 Firebird App-local persistence smoke.
 *
 * Proves the sample27 server-side DTO shape can be read from a Firebird local
 * durable profile, then saved to and read back from the generated App-local
 * SQLite persistence helpers. This intentionally does not prove Mtool
 * config-store-on-Firebird; that belongs to the later F100 Mtool-own-profile
 * slice.
 *
 * @param list<string> $argv
 * @return array{help:bool,dsn:string,user:string,password:string,pretty:bool,error:string}
 */
function app_cli_sample27_firebird_parse_args(array $argv): array
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

function app_cli_sample27_firebird_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample27_firebird_app_local_persistence_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN
  MTOOL_FIREBIRD_USER
  MTOOL_FIREBIRD_PASSWORD

Notes:
  - Requires PHP PDO_FIREBIRD and PDO_SQLITE.
  - Proves Firebird server DTO -> App-local SQLite save/read.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_sample27_firebird_smoke_result(
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
function app_sample27_firebird_manifest(): array
{
    $manifest = app_shared_contract_core_sample02_task_manifest();
    $manifest['contracts'][0]['contract_key'] = 'app_local_task';
    $manifest['contracts'][0]['entity'] = [
        'logical_name' => 'App Local Task',
        'physical_name' => 'app_local_task',
        'generated_name' => 'AppLocalTask',
    ];

    return $manifest;
}

function app_sample27_firebird_create_schema(PDO $pdo): void
{
    try {
        $pdo->exec('DROP TABLE APP_LOCAL_TASK');
    } catch (Throwable) {
        // APP_LOCAL_TASK is a disposable smoke table.
    }

    $pdo->exec(
        'CREATE TABLE APP_LOCAL_TASK (
            ID INTEGER NOT NULL PRIMARY KEY,
            TITLE VARCHAR(255) NOT NULL,
            STATUS VARCHAR(20) DEFAULT \'draft\' NOT NULL,
            SORT_ORDER INTEGER DEFAULT 0 NOT NULL,
            IS_PINNED SMALLINT DEFAULT 0 NOT NULL,
            PUBLISHED_AT TIMESTAMP,
            NOTE BLOB SUB_TYPE TEXT
        )'
    );
}

function app_sample27_firebird_seed(PDO $pdo): void
{
    $insert = $pdo->prepare(
        'INSERT INTO APP_LOCAL_TASK (
            ID, TITLE, STATUS, SORT_ORDER, IS_PINNED, PUBLISHED_AT, NOTE
        ) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if ($insert === false) {
        throw new RuntimeException('failed to prepare sample27 Firebird seed insert.');
    }
    $insert->execute([
        1001,
        'Server task for App-local persistence',
        'draft',
        10,
        0,
        null,
        'server read fixture for sample27',
    ]);
}

/**
 * @param array<string,mixed> $manifest
 * @return array<string,mixed>
 */
function app_sample27_firebird_read_server_dto(PDO $pdo, array $manifest, int $id): array
{
    $contract = $manifest['contracts'][0] ?? null;
    if (!is_array($contract)) {
        throw new RuntimeException('sample27 manifest contract was not found.');
    }

    $statement = $pdo->prepare('SELECT * FROM APP_LOCAL_TASK WHERE ID = ?');
    if ($statement === false) {
        throw new RuntimeException('failed to prepare sample27 Firebird server read.');
    }
    $statement->execute([$id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) {
        throw new RuntimeException('sample27 Firebird server row was not found: ' . (string) $id);
    }

    $dto = [];
    foreach (($contract['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $generatedName = (string) ($field['generated_name'] ?? '');
        $physicalName = (string) ($field['physical_name'] ?? '');
        $value = $row[$physicalName] ?? $row[strtoupper($physicalName)] ?? null;
        if ($value === null) {
            $dto[$generatedName] = null;
            continue;
        }

        $dto[$generatedName] = match ((string) ($field['type'] ?? 'string')) {
            'integer' => (int) $value,
            'boolean' => ((int) $value) === 1,
            'datetime', 'string', 'text' => (string) $value,
            default => $value,
        };
    }

    return $dto;
}

/**
 * @param list<string> $errors
 */
function app_sample27_firebird_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/** @return array<string,mixed> */
function app_sample27_firebird_app_local_persistence_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample27_firebird_smoke_result(
            false,
            'runtime_preflight',
            'pdo_firebird_extension_missing',
            [
                'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
                'required_driver' => 'firebird',
            ],
        );
    }
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        return app_sample27_firebird_smoke_result(
            false,
            'runtime_preflight',
            'pdo_sqlite_driver_missing',
            [
                'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
                'required_driver' => 'sqlite',
            ],
        );
    }
    if (trim($dsn) === '') {
        return app_sample27_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
    }

    try {
        $firebirdPdo = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );

        app_sample27_firebird_create_schema($firebirdPdo);
        app_sample27_firebird_seed($firebirdPdo);

        $manifest = app_sample27_firebird_manifest();
        $schema = app_local_sqlite_schema_generate($manifest);
        if (!$schema['ok']) {
            return app_sample27_firebird_smoke_result(false, 'app_local_schema', $schema['error'], [
                'validation' => $schema['validation'],
            ], true);
        }

        $localPdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($localPdo, $schema['schema_sql']);
        if (!$apply['ok']) {
            return app_sample27_firebird_smoke_result(false, 'app_local_schema_apply', $apply['error'], [
                'schema_summary' => $schema['summary'],
            ], true);
        }

        $serverDto = app_sample27_firebird_read_server_dto($firebirdPdo, $manifest, 1001);
        $save = app_local_sqlite_dbaccess_save_dto($localPdo, $manifest, 'app_local_task', $serverDto);
        if (!$save['ok']) {
            return app_sample27_firebird_smoke_result(false, 'app_local_save', $save['error'], [
                'server_dto' => $serverDto,
            ], true);
        }

        $read = app_local_sqlite_dbaccess_read_dto($localPdo, $manifest, 'app_local_task', ['id' => 1001]);
        if (!$read['ok']) {
            return app_sample27_firebird_smoke_result(false, 'app_local_read', $read['error'], [
                'server_dto' => $serverDto,
            ], true);
        }

        $assertionErrors = [];
        app_sample27_firebird_assert_same(
            [
                'id' => 1001,
                'title' => 'Server task for App-local persistence',
                'status' => 'draft',
                'sortOrder' => 10,
                'isPinned' => false,
                'publishedAt' => null,
                'note' => 'server read fixture for sample27',
            ],
            $serverDto,
            'server DTO',
            $assertionErrors,
        );
        app_sample27_firebird_assert_same($serverDto, $read['dto'], 'App-local read DTO', $assertionErrors);
        app_sample27_firebird_assert_same(1, $read['local_metadata']['dirty'] ?? null, 'local metadata dirty', $assertionErrors);
        app_sample27_firebird_assert_same('dirty', $read['local_metadata']['sync_status'] ?? null, 'local metadata sync_status', $assertionErrors);
        app_sample27_firebird_assert_same(0, $read['local_metadata']['tombstone'] ?? null, 'local metadata tombstone', $assertionErrors);

        if ($assertionErrors !== []) {
            return app_sample27_firebird_smoke_result(false, 'assertions', 'sample27 Firebird App-local persistence assertions failed.', [
                'assertion_errors' => $assertionErrors,
                'server_dto' => $serverDto,
                'read' => $read,
            ], true);
        }

        return app_sample27_firebird_smoke_result(true, 'ok', '', [
            'sample' => 'sample27-app-local-persistence-demo',
            'firebird_pdo_driver' => (string) $firebirdPdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'local_pdo_driver' => (string) $localPdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'server_table' => 'APP_LOCAL_TASK',
            'local_contract_key' => 'app_local_task',
            'local_table_count' => (int) ($schema['summary']['table_count'] ?? 0),
            'server_dto' => $serverDto,
            'local_metadata' => $read['local_metadata'],
        ], true);
    } catch (Throwable $throwable) {
        return app_sample27_firebird_smoke_result(false, 'connection_or_round_trip', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
        ], true);
    }
}

$parsed = app_cli_sample27_firebird_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample27_firebird_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample27_firebird_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample27_firebird_app_local_persistence_smoke(
    $parsed['dsn'],
    $parsed['user'],
    $parsed['password'],
);

fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);
