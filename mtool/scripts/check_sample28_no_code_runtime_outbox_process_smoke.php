#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/database.php';
require_once dirname(__DIR__) . '/app/generated_catalog.php';
require_once dirname(__DIR__) . '/app/managed_operation_repository_pdo.php';
require_once dirname(__DIR__) . '/app/managed_operation_server_dbaccess_executor.php';
require_once dirname(__DIR__) . '/app/managed_operation_sync_outbox_processor.php';
require_once dirname(__DIR__) . '/app/no_code_public_runtime_page.php';
require_once dirname(__DIR__) . '/app/project_db_access_bootstrap_service.php';

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_sample28_no_code_runtime_outbox_process_smoke.php [options]

Options:
  --profile=PROFILE       Smoke payload profile: sample28, sample29, or sample31 (default: sample28)
  --pretty                Pretty-print JSON result
  --help                  Show this help

TEXT;
}

function parse_args(array $argv): array
{
    $args = [
        'profile' => 'sample28',
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
        if (str_starts_with($arg, '--profile=')) {
            $args['profile'] = substr($arg, strlen('--profile='));
            continue;
        }

        throw new InvalidArgumentException('unsupported argument: ' . $arg);
    }
    if (!in_array($args['profile'], ['sample28', 'sample29', 'sample31'], true)) {
        throw new InvalidArgumentException('unsupported --profile: ' . $args['profile']);
    }

    return $args;
}

function ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function smoke_profile(string $profile): array
{
    $profiles = [
        'sample28' => [
            'project_key' => 'SAMPLE28',
            'table_name' => 'no_code_ticket',
            'operation_key' => 'update_no_code_ticket',
            'sqlite_schema' => 'CREATE TABLE no_code_ticket (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT "open",
                priority INTEGER NOT NULL DEFAULT 0,
                body TEXT NOT NULL
            )',
            'sqlite_insert' => 'INSERT INTO no_code_ticket (id, title, status, priority, body)
                VALUES (?, ?, ?, ?, ?)',
            'sqlite_insert_values' => [
                1001,
                'First no-code app ticket',
                'open',
                10,
                'This row is the first sample28 data-first no-code app fixture.',
            ],
            'sqlite_select' => 'SELECT id, title, status, priority, body FROM no_code_ticket WHERE id = ?',
            'sqlite_select_values' => [1001],
            'list_screen_key' => 'no_code_ticket_list',
            'detail_screen_key' => 'no_code_ticket_detail',
            'form_screen_key' => 'no_code_ticket_form',
            'key_field' => 'id',
            'expected_field' => 'body',
            'expected_value' => 'Generated sample28 direct endpoint smoke payload',
        ],
        'sample29' => [
            'project_key' => 'SAMPLE29',
            'table_name' => 'support_case',
            'operation_key' => 'update_support_case',
            'sqlite_schema' => 'CREATE TABLE support_case (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                case_number TEXT NOT NULL,
                customer_name TEXT NOT NULL,
                customer_tier TEXT NOT NULL DEFAULT "standard",
                subject TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT "open",
                severity TEXT NOT NULL DEFAULT "medium",
                next_action TEXT NOT NULL
            )',
            'sqlite_insert' => 'INSERT INTO support_case (
                id,
                case_number,
                customer_name,
                customer_tier,
                subject,
                status,
                severity,
                next_action
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'sqlite_insert_values' => [
                2001,
                'CASE-2026-0001',
                'Northwind Field Team',
                'enterprise',
                'Onboarding data import review',
                'triage',
                'high',
                'Confirm imported customer fields and prepare a generated follow-up workflow.',
            ],
            'sqlite_select' => 'SELECT id, case_number, customer_name, customer_tier, subject, status, severity, next_action FROM support_case WHERE id = ?',
            'sqlite_select_values' => [2001],
            'list_screen_key' => 'support_case_list',
            'detail_screen_key' => 'support_case_detail',
            'form_screen_key' => 'support_case_form',
            'key_field' => 'id',
            'expected_field' => 'next_action',
            'expected_value' => 'Generated sample29 direct endpoint smoke payload',
        ],
        'sample31' => [
            'project_key' => 'SAMPLE31',
            'table_name' => 'inventory_request',
            'operation_key' => 'update_inventory_request',
            'sqlite_schema' => 'CREATE TABLE inventory_request (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                request_number TEXT NOT NULL,
                requester_name TEXT NOT NULL,
                warehouse_code TEXT NOT NULL,
                item_sku TEXT NOT NULL,
                quantity_needed INTEGER NOT NULL DEFAULT 1,
                status TEXT NOT NULL DEFAULT "open",
                fulfillment_note TEXT NOT NULL
            )',
            'sqlite_insert' => 'INSERT INTO inventory_request (
                id,
                request_number,
                requester_name,
                warehouse_code,
                item_sku,
                quantity_needed,
                status,
                fulfillment_note
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'sqlite_insert_values' => [
                3101,
                'INV-REQ-2026-0001',
                'Northwind Warehouse Ops',
                'WH-TOKYO-01',
                'SKU-BOARD-42',
                12,
                'requested',
                'Prepare inventory pick review before approving replenishment.',
            ],
            'sqlite_select' => 'SELECT id, request_number, requester_name, warehouse_code, item_sku, quantity_needed, status, fulfillment_note FROM inventory_request WHERE id = ?',
            'sqlite_select_values' => [3101],
            'list_screen_key' => 'inventory_request_list',
            'detail_screen_key' => 'inventory_request_detail',
            'form_screen_key' => 'inventory_request_form',
            'key_field' => 'id',
            'expected_field' => 'fulfillment_note',
            'expected_value' => 'Generated sample31 direct endpoint smoke payload',
        ],
    ];

    return $profiles[$profile];
}

function profile_operation(array $app, array $profile): array
{
    $snapshot = app_pdo_fetch_managed_operation_snapshot($app, $profile['project_key']);
    ensure($snapshot['ok'], 'managed operation snapshot failed: ' . $snapshot['error']);

    foreach ($snapshot['items'] as $item) {
        if ((string) ($item['operation_key'] ?? '') === $profile['operation_key']) {
            return $item;
        }
    }

    throw new RuntimeException($profile['project_key'] . ' ' . $profile['operation_key'] . ' operation was not found.');
}

function profile_server_binding(array $app, array $profile, array $operation): array
{
    $runtimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity(
        $app,
        $profile['project_key'],
        $profile['table_name'],
    );
    ensure(
        $runtimeEntity['ok'] && is_array($runtimeEntity['entity'] ?? null),
        $profile['project_key'] . ' runtime entity materialize failed: ' . $runtimeEntity['error'],
    );

    $entity = $runtimeEntity['entity'];
    require_once (string) ($entity['data_path'] ?? '');
    require_once (string) ($entity['dbaccess_path'] ?? '');

    $binding = app_managed_operation_server_dbaccess_binding_from_project_catalog(
        $app,
        $profile['project_key'],
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
        $profile['project_key'] . ' server DBAccess binding failed: ' . $fallback['error'],
    );

    return [
        'runtime_entity' => $entity,
        'binding' => $fallback['binding'],
    ];
}

function profile_seed_sqlite(string $sqlitePath, array $profile): PDO
{
    $pdo = new PDO('sqlite:' . $sqlitePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec((string) $profile['sqlite_schema']);
    $statement = $pdo->prepare((string) $profile['sqlite_insert']);
    ensure($statement !== false, $profile['project_key'] . ' SQLite insert prepare failed.');
    $statement->execute($profile['sqlite_insert_values']);

    return $pdo;
}

function profile_read_row(PDO $pdo, array $profile): array
{
    $statement = $pdo->prepare((string) $profile['sqlite_select']);
    ensure($statement !== false, $profile['project_key'] . ' SQLite read prepare failed.');
    $statement->execute($profile['sqlite_select_values']);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    ensure(is_array($row), $profile['project_key'] . ' SQLite row was not found after processing.');

    return $row;
}

function profile_process_pending_outbox(array $app, array $profile, array $binding): array
{
    $processed = [];
    for ($index = 0; $index < 10; $index++) {
        $result = app_managed_operation_sync_outbox_process_next(
            $app,
            $profile['project_key'],
            app_managed_operation_server_dbaccess_outbox_handler($binding),
        );
        if (!$result['ok']) {
            throw new RuntimeException($profile['project_key'] . ' outbox process failed: ' . $result['error']);
        }
        if (!$result['processed']) {
            ensure($result['outcome'] === 'no_pending', $profile['project_key'] . ' outbox process stopped unexpectedly: ' . $result['outcome']);
            break;
        }
        ensure($result['outcome'] === 'done', $profile['project_key'] . ' outbox process produced unexpected outcome: ' . $result['outcome']);
        $processed[] = $result;
    }

    ensure($processed !== [], $profile['project_key'] . ' outbox process found no pending items.');

    return $processed;
}

function runtime_data_display_value(mixed $field): string
{
    if (is_array($field)) {
        if (array_key_exists('display_value', $field)) {
            return (string) $field['display_value'];
        }
        if (array_key_exists('value', $field)) {
            return (string) $field['value'];
        }
    }

    return (string) $field;
}

function profile_latest_runtime_candidate(array $app, array $profile): array
{
    $result = app_project_output_list(
        $app,
        (string) $profile['project_key'],
        APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
    );
    ensure(($result['ok'] ?? false) === true, $profile['project_key'] . ' runtime output list failed.');
    $items = is_array($result['items'] ?? null) ? $result['items'] : [];
    ensure($items !== [], $profile['project_key'] . ' runtime output artifact was not found.');

    return [
        'artifact_key' => (string) ($items[0]['artifact_key'] ?? ''),
    ];
}

function profile_runtime_data_screen(array $screens, string $screenKey): array
{
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_key'] ?? '') === $screenKey) {
            return $screen;
        }
    }

    return [];
}

function profile_runtime_data_row_by_key(array $rows, string $keyField, string $keyValue): array
{
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        if (runtime_data_display_value($row[$keyField] ?? '') === $keyValue) {
            return $row;
        }
    }

    return [];
}

function profile_runtime_data_after_processing(array $app, array $profile): array
{
    $candidate = profile_latest_runtime_candidate($app, $profile);
    $definition = app_no_code_public_runtime_candidate_screen_definition(
        $app,
        (string) $profile['project_key'],
        $candidate,
    );
    ensure($definition['ok'], $profile['project_key'] . ' runtime screen definition failed: ' . $definition['error']);

    $screens = app_no_code_public_runtime_data_screens(
        $app,
        (string) $profile['project_key'],
        $definition['definition'],
    );
    ensure(count($screens) === 3, $profile['project_key'] . ' runtime data screen count mismatch.');

    $keyField = (string) $profile['key_field'];
    $keyValue = (string) $profile['sqlite_select_values'][0];
    $expectedField = (string) $profile['expected_field'];
    $expectedValue = (string) $profile['expected_value'];

    $listScreen = profile_runtime_data_screen($screens, (string) $profile['list_screen_key']);
    $listRows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    $listMetadata = is_array($listScreen['metadata'] ?? null) ? $listScreen['metadata'] : [];
    $listRow = profile_runtime_data_row_by_key($listRows, $keyField, $keyValue);
    ensure($listRow !== [], $profile['project_key'] . ' runtime data list row was not found after processing.');
    ensure(
        ($listMetadata['row_count'] ?? null) === count($listRows),
        $profile['project_key'] . ' runtime data list row count metadata mismatch.',
    );
    ensure(
        runtime_data_display_value($listRow[$expectedField] ?? '') === $expectedValue,
        $profile['project_key'] . ' runtime data list row did not include processed value.',
    );

    $detailScreen = profile_runtime_data_screen($screens, (string) $profile['detail_screen_key']);
    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    ensure(
        ($detailMetadata['selected_key']['field_key'] ?? '') === $keyField
            && (string) ($detailMetadata['selected_key']['display_value'] ?? '') === $keyValue,
        $profile['project_key'] . ' runtime data detail selected key metadata mismatch.',
    );
    ensure(
        runtime_data_display_value($detailItem[$expectedField] ?? '') === $expectedValue,
        $profile['project_key'] . ' runtime data detail item did not include processed value.',
    );

    $formScreen = profile_runtime_data_screen($screens, (string) $profile['form_screen_key']);
    $formItem = is_array($formScreen['data']['item'] ?? null) ? $formScreen['data']['item'] : [];
    ensure(
        runtime_data_display_value($formItem[$expectedField] ?? '') === $expectedValue,
        $profile['project_key'] . ' runtime data form item did not include processed value.',
    );

    return [
        'screen_count' => count($screens),
        'list_row_count' => count($listRows),
        'first_row_key' => runtime_data_display_value($listRow[$keyField] ?? ''),
        'row_count_metadata' => (int) ($listMetadata['row_count'] ?? 0),
        'selected_key' => (string) ($detailMetadata['selected_key']['display_value'] ?? ''),
        'expected_field' => $expectedField,
        'expected_value' => runtime_data_display_value($listRow[$expectedField] ?? ''),
        'detail_expected_value' => runtime_data_display_value($detailItem[$expectedField] ?? ''),
        'form_expected_value' => runtime_data_display_value($formItem[$expectedField] ?? ''),
    ];
}

try {
    $args = parse_args($argv);
    $profile = smoke_profile($args['profile']);
    $app = app_bootstrap();
    $operation = profile_operation($app, $profile);
    $bindingResult = profile_server_binding($app, $profile, $operation);

    $sqlitePath = sys_get_temp_dir()
        . '/dego-' . strtolower((string) $profile['project_key']) . '-runtime-outbox-'
        . getmypid()
        . '-'
        . bin2hex(random_bytes(4))
        . '.sqlite';
    $pdo = profile_seed_sqlite($sqlitePath, $profile);

    global $mtooldb;
    $mtooldb = null;
    $previousRuntimeSqlitePath = getenv('MTOOL_RUNTIME_SQLITE_PATH');
    putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);

    try {
        $processed = profile_process_pending_outbox($app, $profile, $bindingResult['binding']);
        $row = profile_read_row($pdo, $profile);
        $runtimeData = profile_runtime_data_after_processing($app, $profile);
    } finally {
        $mtooldb = null;
        if ($previousRuntimeSqlitePath === false) {
            putenv('MTOOL_RUNTIME_SQLITE_PATH');
        } else {
            putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $previousRuntimeSqlitePath);
        }
    }

    ensure(
        (string) ($row[$profile['expected_field']] ?? '') === $profile['expected_value'],
        $profile['project_key'] . ' SQLite row was not updated from direct endpoint payload.',
    );

    $summary = [
        'ok' => true,
        'project_key' => $profile['project_key'],
        'sqlite_path' => $sqlitePath,
        'processed_count' => count($processed),
        'processed_outcomes' => array_map(
            static fn (array $item): string => (string) ($item['outcome'] ?? ''),
            $processed,
        ),
        'last_status' => (string) ($processed[count($processed) - 1]['item']['status'] ?? ''),
        'method_name' => (string) ($processed[count($processed) - 1]['handler_result']['method_name'] ?? ''),
        'row' => $row,
        'runtime_data' => $runtimeData,
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | ($args['pretty'] ? JSON_PRETTY_PRINT : 0);
    echo json_encode($summary, $jsonFlags) . PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
