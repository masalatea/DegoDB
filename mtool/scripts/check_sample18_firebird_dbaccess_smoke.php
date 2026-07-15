#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Focused sample18 Firebird DBAccess smoke.
 *
 * Proves generated sample18 DBAccess list/detail/insert/update/complete paths
 * can operate against Firebird, including no-code runtime read/presentation,
 * a shared generated runtime transaction commit/rollback boundary, and the
 * guarded generated-submit route.
 *
 * @param list<string> $argv
 * @return array{help:bool,dsn:string,user:string,password:string,pretty:bool,error:string}
 */
function app_cli_sample18_firebird_parse_args(array $argv): array
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

function app_cli_sample18_firebird_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample18_firebird_dbaccess_smoke.php --dsn='firebird:dbname=...' --user=USER --password=PASSWORD [--pretty]

Environment:
  MTOOL_FIREBIRD_DSN
  MTOOL_FIREBIRD_USER
  MTOOL_FIREBIRD_PASSWORD

Notes:
  - Requires PHP PDO_FIREBIRD.
  - Uses the generated sample18 DBAccess classes unchanged.
  - Use only against a disposable smoke database.
  - Not part of normal make test.
TEXT;
}

/** @return array<string,mixed> */
function app_sample18_firebird_smoke_result(
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
function app_sample18_firebird_no_code_list_definition(): array
{
    $fields = [
        ['field_key' => 'id', 'generated_name' => 'id', 'field_type' => 'integer', 'label' => 'ID'],
        ['field_key' => 'title', 'generated_name' => 'title', 'field_type' => 'string', 'label' => 'Title'],
        ['field_key' => 'status', 'generated_name' => 'status', 'field_type' => 'string', 'label' => 'Status'],
        ['field_key' => 'assigned_to', 'generated_name' => 'assignedTo', 'field_type' => 'string', 'label' => 'Assigned To'],
        ['field_key' => 'priority', 'generated_name' => 'priority', 'field_type' => 'integer', 'label' => 'Priority'],
        ['field_key' => 'due_date', 'generated_name' => 'dueDate', 'field_type' => 'date', 'label' => 'Due Date'],
    ];

    return [
        'definition_version' => 'no-code-screen-definition-v0',
        'project_key' => 'SAMPLE18',
        'contracts' => [
            [
                'contract_key' => 'task_card',
                'screens' => [
                    [
                        'screen_key' => 'task_card_list',
                        'screen_type' => 'list',
                        'fields' => $fields,
                    ],
                ],
            ],
        ],
    ];
}

/**
 * @param list<object> $rows
 * @return list<array<string,mixed>>
 */
function app_sample18_firebird_no_code_rows_from_task_cards(array $rows): array
{
    return array_values(array_map(
        static fn (object $row): array => [
            'id' => (int) ($row->id ?? 0),
            'title' => (string) ($row->title ?? ''),
            'status' => (string) ($row->status ?? ''),
            'assignedTo' => (string) ($row->assignedTo ?? ''),
            'priority' => (int) ($row->priority ?? 0),
            'dueDate' => (string) ($row->dueDate ?? ''),
        ],
        $rows,
    ));
}

/** @return array<string,mixed> */
function app_sample18_firebird_dbaccess_smoke(string $dsn, string $user, string $password): array
{
    if (!extension_loaded('pdo_firebird')) {
        return app_sample18_firebird_smoke_result(
            false,
            'runtime_preflight',
            'pdo_firebird_extension_missing',
            [
                'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
                'required_driver' => 'firebird',
            ],
        );
    }
    if (!extension_loaded('pdo_sqlite')) {
        return app_sample18_firebird_smoke_result(
            false,
            'runtime_preflight',
            'pdo_sqlite_extension_missing',
            [
                'loaded_pdo_drivers' => PDO::getAvailableDrivers(),
                'required_driver' => 'sqlite',
                'why' => 'guarded route smoke uses disposable SQLite for config-store audit/idempotency while Firebird remains the app DB',
            ],
        );
    }
    if (trim($dsn) === '') {
        return app_sample18_firebird_smoke_result(false, 'runtime_preflight', 'firebird_dsn_required');
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
            $pdo->exec('DROP TABLE TASK_CARD');
        } catch (Throwable) {
            // Disposable smoke table may not exist.
        }

        $pdo->exec(
            'CREATE TABLE TASK_CARD (
                ID INTEGER GENERATED BY DEFAULT AS IDENTITY NOT NULL PRIMARY KEY,
                TITLE VARCHAR(160) NOT NULL,
                BODY BLOB SUB_TYPE TEXT,
                STATUS VARCHAR(40) NOT NULL,
                ASSIGNED_TO VARCHAR(120),
                PRIORITY INTEGER NOT NULL,
                DUE_DATE DATE,
                COMPLETED_AT TIMESTAMP,
                UPDATED_AT TIMESTAMP NOT NULL
            )'
        );

        $insert = $pdo->prepare(
            'INSERT INTO TASK_CARD (TITLE, BODY, STATUS, ASSIGNED_TO, PRIORITY, DUE_DATE, COMPLETED_AT, UPDATED_AT)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute(['Later todo', 'later body', 'todo', 'Alice', 1, '2026-07-20', null, '2026-07-14 09:00:00']);
        $insert->execute(['First todo', 'first body', 'todo', 'Bob', 5, '2026-07-15', null, '2026-07-14 09:10:00']);
        $insert->execute(['Doing ignored', 'doing body', 'doing', 'Carol', 9, '2026-07-10', null, '2026-07-14 09:20:00']);

        $seedIds = [];
        $seedIdStatement = $pdo->query('SELECT ID, TITLE FROM TASK_CARD');
        foreach ($seedIdStatement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $seedIds[(string) $row['TITLE']] = (int) $row['ID'];
        }

        putenv('MTOOL_RUNTIME_DB_DSN=' . $dsn);
        putenv('MTOOL_RUNTIME_DB_USER=' . $user);
        putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $password);

        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/reference/DATACLASS-PHP/data-TaskCard.php';
        require_once dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/dbaccess-TaskCard.php';

        $dbAccess = new TaskCardDBAccess();
        $todoRows = $dbAccess->GetTaskCardList('todo', 2);
        if (!is_array($todoRows) || count($todoRows) !== 2) {
            return app_sample18_firebird_smoke_result(false, 'list_read', 'unexpected_todo_list', [
                'type' => get_debug_type($todoRows),
                'row_count' => is_array($todoRows) ? count($todoRows) : null,
            ], true);
        }
        $todoSummary = array_map(
            static fn (object $row): array => [
                'id' => (int) ($row->id ?? 0),
                'title' => (string) ($row->title ?? ''),
                'status' => (string) ($row->status ?? ''),
                'priority' => (int) ($row->priority ?? 0),
            ],
            $todoRows,
        );
        $expectedTodo = [
            ['id' => $seedIds['First todo'] ?? 0, 'title' => 'First todo', 'status' => 'todo', 'priority' => 5],
            ['id' => $seedIds['Later todo'] ?? 0, 'title' => 'Later todo', 'status' => 'todo', 'priority' => 1],
        ];
        if ($todoSummary !== $expectedTodo) {
            return app_sample18_firebird_smoke_result(false, 'list_read', 'unexpected_todo_order', [
                'expected' => $expectedTodo,
                'actual' => $todoSummary,
            ], true);
        }

        require_once dirname(__DIR__) . '/app/no_code_runtime.php';

        $noCodeRows = app_sample18_firebird_no_code_rows_from_task_cards($todoRows);
        $noCodeRenderResult = app_no_code_runtime_render_screen(
            app_sample18_firebird_no_code_list_definition(),
            'task_card_list',
            $noCodeRows,
            $noCodeRows[0] ?? [],
        );
        if (!($noCodeRenderResult['ok'] ?? false)) {
            return app_sample18_firebird_smoke_result(false, 'no_code_runtime_render', (string) ($noCodeRenderResult['error'] ?? 'render failed'), [
                'rows' => $noCodeRows,
            ], true);
        }
        $noCodeListRender = is_array($noCodeRenderResult['render'] ?? null) ? $noCodeRenderResult['render'] : [];
        $noCodeRuntimeRows = is_array($noCodeListRender['data']['rows'] ?? null) ? $noCodeListRender['data']['rows'] : [];
        if (
            count($noCodeRuntimeRows) !== 2
            || (string) ($noCodeRuntimeRows[0]['title']['display_value'] ?? '') !== 'First todo'
            || (string) ($noCodeRuntimeRows[0]['assigned_to']['display_value'] ?? '') !== 'Bob'
            || (string) ($noCodeRuntimeRows[1]['title']['display_value'] ?? '') !== 'Later todo'
        ) {
            return app_sample18_firebird_smoke_result(false, 'no_code_runtime_render', 'unexpected_no_code_runtime_rows', [
                'runtime_rows' => $noCodeRuntimeRows,
            ], true);
        }
        $noCodePreviewHtml = app_no_code_runtime_render_preview_html([
            'runtime_version' => app_no_code_runtime_version(),
            'definition_version' => 'no-code-screen-definition-v0',
            'project_key' => 'SAMPLE18',
            'screens' => [$noCodeListRender],
        ]);
        foreach (['First todo', 'Later todo', 'data-runtime-version="no-code-runtime-v0"'] as $expectedHtmlMarker) {
            if (!str_contains($noCodePreviewHtml, $expectedHtmlMarker)) {
                return app_sample18_firebird_smoke_result(false, 'no_code_runtime_html', 'missing_no_code_runtime_html_marker', [
                    'marker' => $expectedHtmlMarker,
                ], true);
            }
        }

        $detail = $dbAccess->GetTaskCard($seedIds['First todo'] ?? 0);
        if (!$detail instanceof TaskCardData || (string) $detail->body !== 'first body') {
            return app_sample18_firebird_smoke_result(false, 'detail_read', 'unexpected_detail', [
                'detail_type' => get_debug_type($detail),
                'body' => is_object($detail) ? (string) ($detail->body ?? '') : null,
            ], true);
        }

        $newTask = new TaskCardData();
        $newTask->title = 'Created from Firebird smoke';
        $newTask->body = 'created body';
        $newTask->status = 'todo';
        $newTask->assignedTo = 'Dora';
        $newTask->priority = 7;
        $newTask->dueDate = '2026-07-18';
        $newTask->updatedAt = '2026-07-14 10:00:00';
        $dbAccess->InsertTaskCard($newTask);

        $createdId = (int) $pdo
            ->query("SELECT ID FROM TASK_CARD WHERE TITLE = 'Created from Firebird smoke'")
            ->fetchColumn();
        if ($createdId <= 0) {
            return app_sample18_firebird_smoke_result(false, 'insert', 'created_row_not_found', [], true);
        }

        $created = $dbAccess->GetTaskCard($createdId);
        if (!$created instanceof TaskCardData) {
            return app_sample18_firebird_smoke_result(false, 'insert_readback', 'created_detail_missing', [
                'created_id' => $createdId,
            ], true);
        }
        $created->title = 'Updated from Firebird smoke';
        $created->status = 'doing';
        $created->completedAt = null;
        $created->updatedAt = '2026-07-14 10:15:00';
        $dbAccess->UpdateTaskCard($created);

        $updated = $dbAccess->GetTaskCard($createdId);
        if (!$updated instanceof TaskCardData || (string) $updated->status !== 'doing') {
            return app_sample18_firebird_smoke_result(false, 'update_readback', 'updated_detail_unexpected', [
                'created_id' => $createdId,
                'status' => is_object($updated) ? (string) ($updated->status ?? '') : null,
            ], true);
        }

        $updated->completedAt = '2026-07-14 10:30:00';
        $updated->updatedAt = '2026-07-14 10:30:00';
        $dbAccess->CompleteTaskCard($updated);

        $completed = $dbAccess->GetTaskCard($createdId);
        if (
            !$completed instanceof TaskCardData
            || (string) $completed->status !== 'done'
            || (string) $completed->completedAt === ''
        ) {
            return app_sample18_firebird_smoke_result(false, 'complete_readback', 'completed_detail_unexpected', [
                'created_id' => $createdId,
                'status' => is_object($completed) ? (string) ($completed->status ?? '') : null,
                'completed_at' => is_object($completed) ? (string) ($completed->completedAt ?? '') : null,
            ], true);
        }

        $transactionDb = new MtoolGeneratedDbAccessRuntimeDb();
        $GLOBALS['mtooldb'] = $transactionDb;
        $transactionAccess = new TaskCardDBAccess();

        if (!$transactionDb->beginTransaction() || !$transactionDb->inTransaction()) {
            return app_sample18_firebird_smoke_result(false, 'transaction_commit_begin', 'transaction_begin_failed', [
                'errno' => $transactionDb->errno,
                'error' => $transactionDb->error,
            ], true);
        }

        $commitTask = new TaskCardData();
        $commitTask->title = 'Firebird transaction committed';
        $commitTask->body = 'transaction commit body';
        $commitTask->status = 'todo';
        $commitTask->assignedTo = 'Eve';
        $commitTask->priority = 3;
        $commitTask->dueDate = '2026-07-19';
        $commitTask->updatedAt = '2026-07-14 11:00:00';
        $transactionAccess->InsertTaskCard($commitTask);
        if ($transactionDb->errno !== 0) {
            $transactionDb->rollBack();
            return app_sample18_firebird_smoke_result(false, 'transaction_commit_insert', 'transaction_insert_failed', [
                'errno' => $transactionDb->errno,
                'error' => $transactionDb->error,
            ], true);
        }
        if (!$transactionDb->commit() || $transactionDb->inTransaction()) {
            return app_sample18_firebird_smoke_result(false, 'transaction_commit', 'transaction_commit_failed', [
                'errno' => $transactionDb->errno,
                'error' => $transactionDb->error,
                'in_transaction' => $transactionDb->inTransaction(),
            ], true);
        }

        $committedCount = (int) $pdo
            ->query("SELECT COUNT(*) FROM TASK_CARD WHERE TITLE = 'Firebird transaction committed'")
            ->fetchColumn();
        if ($committedCount !== 1) {
            return app_sample18_firebird_smoke_result(false, 'transaction_commit_readback', 'committed_row_missing', [
                'committed_count' => $committedCount,
            ], true);
        }

        $rollbackDb = new MtoolGeneratedDbAccessRuntimeDb();
        $GLOBALS['mtooldb'] = $rollbackDb;
        $rollbackAccess = new TaskCardDBAccess();
        if (!$rollbackDb->beginTransaction() || !$rollbackDb->inTransaction()) {
            return app_sample18_firebird_smoke_result(false, 'transaction_rollback_begin', 'transaction_begin_failed', [
                'errno' => $rollbackDb->errno,
                'error' => $rollbackDb->error,
            ], true);
        }

        $rollbackTask = new TaskCardData();
        $rollbackTask->title = 'Firebird transaction rolled back';
        $rollbackTask->body = 'transaction rollback body';
        $rollbackTask->status = 'todo';
        $rollbackTask->assignedTo = 'Frank';
        $rollbackTask->priority = 4;
        $rollbackTask->dueDate = '2026-07-21';
        $rollbackTask->updatedAt = '2026-07-14 11:10:00';
        $rollbackAccess->InsertTaskCard($rollbackTask);
        if ($rollbackDb->errno !== 0) {
            $rollbackDb->rollBack();
            return app_sample18_firebird_smoke_result(false, 'transaction_rollback_insert', 'rollback_insert_failed_before_required_failure', [
                'errno' => $rollbackDb->errno,
                'error' => $rollbackDb->error,
            ], true);
        }

        $requiredFailure = $rollbackDb->execute(
            'INSERT INTO TASK_CARD (TITLE, STATUS, PRIORITY, UPDATED_AT) VALUES (?, ?, ?, ?)',
            [null, 'todo', 1, '2026-07-14 11:15:00'],
        );
        if ($requiredFailure !== false || $rollbackDb->errno === 0) {
            $rollbackDb->rollBack();
            return app_sample18_firebird_smoke_result(false, 'transaction_required_failure', 'required_failure_did_not_fail', [
                'result_type' => get_debug_type($requiredFailure),
                'errno' => $rollbackDb->errno,
                'error' => $rollbackDb->error,
            ], true);
        }
        if (!$rollbackDb->rollBack() || $rollbackDb->inTransaction()) {
            return app_sample18_firebird_smoke_result(false, 'transaction_rollback', 'transaction_rollback_failed', [
                'errno' => $rollbackDb->errno,
                'error' => $rollbackDb->error,
                'in_transaction' => $rollbackDb->inTransaction(),
            ], true);
        }

        $rolledBackCount = (int) $pdo
            ->query("SELECT COUNT(*) FROM TASK_CARD WHERE TITLE = 'Firebird transaction rolled back'")
            ->fetchColumn();
        if ($rolledBackCount !== 0) {
            return app_sample18_firebird_smoke_result(false, 'transaction_rollback_readback', 'rolled_back_row_survived', [
                'rolled_back_count' => $rolledBackCount,
            ], true);
        }

        require_once dirname(__DIR__) . '/app/config.php';
        require_once dirname(__DIR__) . '/app/config_db_bootstrap.php';
        require_once dirname(__DIR__) . '/app/lab_sample18_task_board_page.php';

        $checklistPath = dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json';
        $checklist = json_decode((string) file_get_contents($checklistPath), true);
        if (!is_array($checklist)) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_preflight', 'sample18_checklist_json_unreadable', [
                'path' => $checklistPath,
            ], true);
        }
        $submitContract = is_array($checklist['generated_submit_request_contract'] ?? null)
            ? $checklist['generated_submit_request_contract']
            : [];
        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '2026-07-10 12:34:56');
        $createExpectation = is_array($submitContract['operations']['create_task_card'] ?? null)
            ? $submitContract['operations']['create_task_card']
            : [];
        $validCreateInput = is_array($createExpectation['valid_input'] ?? null)
            ? $createExpectation['valid_input']
            : [
                'title' => 'Firebird guarded route task',
                'body' => 'guarded route body',
                'assigned_to' => 'Grace',
                'priority' => '5',
            ];

        $configStoreDir = sys_get_temp_dir() . '/dego-sample18-firebird-config-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            sys_get_temp_dir(),
            $configStoreDir,
        );
        $app = [
            'site' => 'lab',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
        $routeDb = new MtoolGeneratedDbAccessRuntimeDb();
        $GLOBALS['mtooldb'] = $routeDb;
        $routeCallables = app_lab_sample18_task_board_generated_submit_transaction_binding_callables(
            $routeDb,
            static function (array $context): object {
                $GLOBALS['mtooldb'] = $context['transaction_db'];

                return new TaskCardDBAccess();
            },
        );
        $app['sample18_generated_submit_mutation_enabled'] = true;
        $app['sample18_generated_submit_executor_enabled'] = true;
        $app['sample18_generated_submit_transaction_callables'] = $routeCallables;

        $bootstrap = app_config_db_bootstrap_apply($app);
        if (!($bootstrap['ok'] ?? false)) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_bootstrap', (string) ($bootstrap['error'] ?? 'config bootstrap failed'), [
                'bootstrap' => $bootstrap,
            ], true);
        }

        $principal = [
            'id' => 'sample18-firebird-route@example.test',
            'auth_source' => 'firebird-smoke',
        ];
        $createPost = array_merge(
            ['operation_key' => 'create_task_card', '_csrf_token' => 'client-token'],
            $validCreateInput,
        );
        $createdRoute = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $createPost,
            $timestamp,
            'valid',
            $app,
            $principal,
        );
        if (
            (int) ($createdRoute['status_code'] ?? 0) !== 200
            || (string) ($createdRoute['payload']['result'] ?? '') !== 'executed'
            || (string) ($createdRoute['payload']['transaction_result']['transaction_status'] ?? '') !== 'committed'
            || (string) ($createdRoute['payload']['post_commit_recording']['recording_status'] ?? '') !== 'recorded'
        ) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_create', 'guarded_route_create_failed', [
                'response' => $createdRoute,
            ], true);
        }

        $guardedCreatedCount = (int) $pdo
            ->query("SELECT COUNT(*) FROM TASK_CARD WHERE TITLE = 'New generated task'")
            ->fetchColumn();
        if ($guardedCreatedCount !== 1) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_create_readback', 'guarded_route_created_row_missing', [
                'guarded_created_count' => $guardedCreatedCount,
            ], true);
        }

        $completeRoute = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'complete_task_card', '_csrf_token' => 'client-token', 'id' => (string) ($seedIds['First todo'] ?? 0)],
            $timestamp,
            'valid',
            $app,
            $principal,
        );
        if (
            (int) ($completeRoute['status_code'] ?? 0) !== 200
            || (string) ($completeRoute['payload']['result'] ?? '') !== 'executed'
            || (string) ($completeRoute['payload']['transaction_result']['transaction_status'] ?? '') !== 'committed'
        ) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_complete', 'guarded_route_complete_failed', [
                'response' => $completeRoute,
            ], true);
        }

        $guardedCompleted = $pdo
            ->query('SELECT STATUS, COMPLETED_AT FROM TASK_CARD WHERE ID = ' . (int) ($seedIds['First todo'] ?? 0))
            ->fetch(PDO::FETCH_ASSOC);
        if (
            !is_array($guardedCompleted)
            || (string) ($guardedCompleted['STATUS'] ?? '') !== 'done'
            || (string) ($guardedCompleted['COMPLETED_AT'] ?? '') === ''
        ) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_complete_readback', 'guarded_route_completed_row_unexpected', [
                'row' => $guardedCompleted,
            ], true);
        }

        $duplicateRoute = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $createPost,
            $timestamp,
            'valid',
            $app,
            $principal,
        );
        if (
            (int) ($duplicateRoute['status_code'] ?? 0) !== 409
            || (string) ($duplicateRoute['payload']['idempotency']['status'] ?? '') !== 'duplicate'
            || array_key_exists('route_execution', is_array($duplicateRoute['payload'] ?? null) ? $duplicateRoute['payload'] : [])
        ) {
            return app_sample18_firebird_smoke_result(false, 'guarded_route_duplicate', 'guarded_route_duplicate_not_blocked', [
                'response' => $duplicateRoute,
            ], true);
        }

        return app_sample18_firebird_smoke_result(true, 'ok', '', [
            'sample' => 'sample18-mini-task-board-demo',
            'pdo_driver' => (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'table' => 'TASK_CARD',
            'list_rows' => $todoSummary,
            'created_id' => $createdId,
            'completed_status' => (string) $completed->status,
            'no_code_runtime_rows' => count($noCodeRuntimeRows),
            'no_code_runtime_html_markers' => 3,
            'transaction_commit_rows' => $committedCount,
            'transaction_rollback_rows' => $rolledBackCount,
            'guarded_route_created_rows' => $guardedCreatedCount,
            'guarded_route_completed_status' => (string) ($guardedCompleted['STATUS'] ?? ''),
            'guarded_route_duplicate_status' => (string) ($duplicateRoute['payload']['idempotency']['status'] ?? ''),
        ], true);
    } catch (Throwable $throwable) {
        return app_sample18_firebird_smoke_result(false, 'connection_or_dbaccess', $throwable->getMessage(), [
            'dsn_prefix' => preg_replace('/=.*/', '=...', $dsn),
        ], true);
    }
}

$parsed = app_cli_sample18_firebird_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample18_firebird_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample18_firebird_usage() . PHP_EOL);
    exit(64);
}

$result = app_sample18_firebird_dbaccess_smoke(
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
