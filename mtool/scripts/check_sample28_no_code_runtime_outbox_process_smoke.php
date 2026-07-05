#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/database.php';
require_once dirname(__DIR__) . '/app/generated_catalog.php';
require_once dirname(__DIR__) . '/app/managed_operation_repository_pdo.php';
require_once dirname(__DIR__) . '/app/managed_operation_server_dbaccess_executor.php';
require_once dirname(__DIR__) . '/app/managed_operation_sync_outbox_processor.php';
require_once dirname(__DIR__) . '/app/project_db_access_bootstrap_service.php';

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php [options]

Options:
  --pretty                Pretty-print JSON result
  --help                  Show this help

TEXT;
}

function parse_args(array $argv): array
{
    $args = [
        'pretty' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            echo usage();
            exit(0);
        }
        if ($arg === '--pretty') {
            $args['pretty'] = true;
            continue;
        }

        throw new InvalidArgumentException('unsupported argument: ' . $arg);
    }

    return $args;
}

function ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function sample28_operation(array $app): array
{
    $snapshot = app_pdo_fetch_managed_operation_snapshot($app, 'SAMPLE28');
    ensure($snapshot['ok'], 'managed operation snapshot failed: ' . $snapshot['error']);

    foreach ($snapshot['items'] as $item) {
        if ((string) ($item['operation_key'] ?? '') === 'update_no_code_ticket') {
            return $item;
        }
    }

    throw new RuntimeException('sample28 update_no_code_ticket operation was not found.');
}

function sample28_server_binding(array $app, array $operation): array
{
    $runtimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity(
        $app,
        'SAMPLE28',
        'no_code_ticket',
    );
    ensure(
        $runtimeEntity['ok'] && is_array($runtimeEntity['entity'] ?? null),
        'sample28 runtime entity materialize failed: ' . $runtimeEntity['error'],
    );

    $entity = $runtimeEntity['entity'];
    require_once (string) ($entity['data_path'] ?? '');
    require_once (string) ($entity['dbaccess_path'] ?? '');

    $binding = app_managed_operation_server_dbaccess_binding_from_project_catalog(
        $app,
        'SAMPLE28',
        $operation,
    );
    if ($binding['ok'] && is_array($binding['binding'] ?? null)) {
        return [
            'runtime_entity' => $entity,
            'binding' => $binding['binding'],
        ];
    }

    $fallback = app_managed_operation_server_dbaccess_binding_from_candidate(
        [
            'source_name' => (string) ($entity['source_name'] ?? ''),
            'generated_name' => (string) ($entity['data_class'] ?? '') !== ''
                ? preg_replace('/Data$/', '', (string) ($entity['data_class'] ?? ''))
                : '',
            'data_class' => (string) ($entity['data_class'] ?? ''),
            'dbaccess_class' => (string) ($entity['dbaccess_class'] ?? ''),
            'method_catalog' => app_generated_file_method_catalog((string) ($entity['dbaccess_path'] ?? '')),
        ],
        $operation,
        [
            'source_name' => (string) ($entity['source_name'] ?? ''),
        ],
    );
    ensure(
        $fallback['ok'] && is_array($fallback['binding'] ?? null),
        'sample28 server DBAccess binding failed: ' . $fallback['error'],
    );

    return [
        'runtime_entity' => $entity,
        'binding' => $fallback['binding'],
    ];
}

function sample28_seed_sqlite(string $sqlitePath): PDO
{
    $pdo = new PDO('sqlite:' . $sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE no_code_ticket (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT "open",
            priority INTEGER NOT NULL DEFAULT 0,
            body TEXT NOT NULL
        )',
    );
    $statement = $pdo->prepare(
        'INSERT INTO no_code_ticket (id, title, status, priority, body)
         VALUES (?, ?, ?, ?, ?)',
    );
    $statement->execute([
        1001,
        'First no-code app ticket',
        'open',
        10,
        'This row is the first sample28 data-first no-code app fixture.',
    ]);

    return $pdo;
}

function sample28_read_row(PDO $pdo): array
{
    $statement = $pdo->prepare('SELECT id, title, status, priority, body FROM no_code_ticket WHERE id = ?');
    ensure($statement !== false, 'sample28 SQLite read prepare failed.');
    $statement->execute([1001]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    ensure(is_array($row), 'sample28 SQLite row was not found after processing.');

    return $row;
}

function sample28_process_pending_outbox(array $app, array $binding): array
{
    $processed = [];
    for ($index = 0; $index < 10; $index++) {
        $result = app_managed_operation_sync_outbox_process_next(
            $app,
            'SAMPLE28',
            app_managed_operation_server_dbaccess_outbox_handler($binding),
        );
        if (!$result['ok']) {
            throw new RuntimeException('sample28 outbox process failed: ' . $result['error']);
        }
        if (!$result['processed']) {
            ensure($result['outcome'] === 'no_pending', 'sample28 outbox process stopped unexpectedly: ' . $result['outcome']);
            break;
        }
        ensure($result['outcome'] === 'done', 'sample28 outbox process produced unexpected outcome: ' . $result['outcome']);
        $processed[] = $result;
    }

    ensure($processed !== [], 'sample28 outbox process found no pending items.');

    return $processed;
}

try {
    $args = parse_args($argv);
    $app = app_bootstrap();
    $operation = sample28_operation($app);
    $bindingResult = sample28_server_binding($app, $operation);

    $sqlitePath = sys_get_temp_dir()
        . '/dego-sample28-runtime-outbox-'
        . getmypid()
        . '-'
        . bin2hex(random_bytes(4))
        . '.sqlite';
    $pdo = sample28_seed_sqlite($sqlitePath);

    global $mtooldb;
    $mtooldb = null;
    $previousRuntimeSqlitePath = getenv('MTOOL_RUNTIME_SQLITE_PATH');
    putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);

    try {
        $processed = sample28_process_pending_outbox($app, $bindingResult['binding']);
        $row = sample28_read_row($pdo);
    } finally {
        $mtooldb = null;
        if ($previousRuntimeSqlitePath === false) {
            putenv('MTOOL_RUNTIME_SQLITE_PATH');
        } else {
            putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $previousRuntimeSqlitePath);
        }
    }

    ensure(
        (string) ($row['body'] ?? '') === 'Generated sample28 direct endpoint smoke payload',
        'sample28 SQLite row body was not updated from direct endpoint payload.',
    );

    $summary = [
        'ok' => true,
        'project_key' => 'SAMPLE28',
        'sqlite_path' => $sqlitePath,
        'processed_count' => count($processed),
        'processed_outcomes' => array_map(
            static fn (array $item): string => (string) ($item['outcome'] ?? ''),
            $processed,
        ),
        'last_status' => (string) ($processed[count($processed) - 1]['item']['status'] ?? ''),
        'method_name' => (string) ($processed[count($processed) - 1]['handler_result']['method_name'] ?? ''),
        'row' => $row,
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | ($args['pretty'] ? JSON_PRETTY_PRINT : 0);
    echo json_encode($summary, $jsonFlags) . PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
