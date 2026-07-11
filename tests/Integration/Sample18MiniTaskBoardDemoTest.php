<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/lab_sample18_task_board_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class Sample18MiniTaskBoardDemoTest extends TestCase
{
    public function testMiniTaskBoardGeneratedDbAccessRuntimeSupportsPdoTransactions(): void
    {
        $root = dirname(__DIR__, 2);
        $generatorSource = (string) file_get_contents($root . '/mtool/app/project_output_db_access_generator.php');
        $referenceSource = (string) file_get_contents(
            $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php',
        );
        foreach (['beginTransaction', 'commit', 'rollBack', 'inTransaction'] as $method) {
            self::assertStringContainsString('public function ' . $method . '()', $generatorSource);
            self::assertStringContainsString('public function ' . $method . '()', $referenceSource);
        }

        $sqlitePath = sys_get_temp_dir() . '/sample18-runtime-' . bin2hex(random_bytes(4)) . '.sqlite';
        $previousDsn = getenv('MTOOL_RUNTIME_DB_DSN');
        $previousSqlitePath = getenv('MTOOL_RUNTIME_SQLITE_PATH');
        $previousUser = getenv('MTOOL_RUNTIME_DB_USER');
        $previousPassword = getenv('MTOOL_RUNTIME_DB_PASSWORD');

        try {
            putenv('MTOOL_RUNTIME_DB_DSN');
            putenv('MTOOL_RUNTIME_DB_USER');
            putenv('MTOOL_RUNTIME_DB_PASSWORD');
            putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);
            require_once $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php';

            $db = new MtoolGeneratedDbAccessRuntimeDb();
            self::assertFalse($db->inTransaction());
            self::assertInstanceOf(
                MtoolGeneratedDbAccessPdoResult::class,
                $db->execute('CREATE TABLE items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)'),
            );

            self::assertTrue($db->beginTransaction());
            self::assertTrue($db->inTransaction());
            self::assertInstanceOf(
                MtoolGeneratedDbAccessPdoResult::class,
                $db->execute('INSERT INTO items (name) VALUES (?)', ['committed']),
            );
            self::assertTrue($db->commit());
            self::assertFalse($db->inTransaction());

            $committedRows = $db->query('SELECT COUNT(*) FROM items');
            self::assertInstanceOf(MtoolGeneratedDbAccessPdoResult::class, $committedRows);
            $committedCount = $committedRows->fetch_row();
            self::assertSame(1, (int) ($committedCount[0] ?? 0));

            self::assertTrue($db->beginTransaction());
            self::assertTrue($db->inTransaction());
            self::assertInstanceOf(
                MtoolGeneratedDbAccessPdoResult::class,
                $db->execute('INSERT INTO items (name) VALUES (?)', ['rolled back']),
            );
            self::assertTrue($db->rollBack());
            self::assertFalse($db->inTransaction());

            $rolledBackRows = $db->query('SELECT COUNT(*) FROM items');
            self::assertInstanceOf(MtoolGeneratedDbAccessPdoResult::class, $rolledBackRows);
            $rolledBackCount = $rolledBackRows->fetch_row();
            self::assertSame(1, (int) ($rolledBackCount[0] ?? 0));

            self::assertFalse($db->commit());
            self::assertSame(1, $db->errno);
            self::assertNotSame('', $db->error);
        } finally {
            $previousDsn === false ? putenv('MTOOL_RUNTIME_DB_DSN') : putenv('MTOOL_RUNTIME_DB_DSN=' . $previousDsn);
            $previousSqlitePath === false ? putenv('MTOOL_RUNTIME_SQLITE_PATH') : putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $previousSqlitePath);
            $previousUser === false ? putenv('MTOOL_RUNTIME_DB_USER') : putenv('MTOOL_RUNTIME_DB_USER=' . $previousUser);
            $previousPassword === false ? putenv('MTOOL_RUNTIME_DB_PASSWORD') : putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $previousPassword);
            if (is_file($sqlitePath)) {
                unlink($sqlitePath);
            }
        }
    }

    public function testMiniTaskBoardGeneratedSubmitDbBackedTransactionBindingCommitsAndRollsBack(): void
    {
        $root = dirname(__DIR__, 2);
        require_once $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/_support/mtool_runtime_db.php';
        require_once $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DATACLASS-PHP/base/data-TaskCardBase.php';
        require_once $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DATACLASS-PHP/data-TaskCard.php';
        require_once $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/base/dbaccess-TaskCardBase.php';
        require_once $root . '/sample/tutorials/sample18-mini-task-board-demo/reference/DBACCESS-PHP/dbaccess-TaskCard.php';

        $buildReady = static function (string $title): array {
            $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
                'create_task_card',
                ['title' => $title, 'body' => 'DB backed body', 'assigned_to' => 'Mina', 'priority' => '10', 'due_date' => ''],
                '2026-07-10 14:00:00',
            );
            self::assertTrue($normalized['ok']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
            $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-db-backed-' . md5($title)]];
            $idempotency = [
                'ok' => true,
                'status' => 'recorded',
                'created' => true,
                'dedupe_key' => 'dedupe-db-backed-' . md5($title),
                'item' => ['dedupe_key' => 'dedupe-db-backed-' . md5($title)],
            ];
            $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
                ['sample18_generated_submit_mutation_enabled' => true],
                $normalized,
                $dispatcher,
                $auditAppend,
                $idempotency,
            );
            $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
                $normalized,
                $dispatcher,
                $mutationGate,
            );
            $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
            $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
                $transactionPlan,
                $auditAppend,
                $idempotency,
            );
            $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
                $normalized,
                $auditAppend,
                $idempotency,
                $mutationGate,
                $executionPlan,
                $transactionPlan,
                $updatePlan,
            );
            $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
                $guard,
                $updatePlan,
                true,
            );

            return [$normalized, $dispatcher, $guard, $coordination];
        };

        $sqlitePath = sys_get_temp_dir() . '/sample18-db-backed-' . bin2hex(random_bytes(4)) . '.sqlite';
        $previousSqlitePath = getenv('MTOOL_RUNTIME_SQLITE_PATH');
        $previousDsn = getenv('MTOOL_RUNTIME_DB_DSN');
        $previousUser = getenv('MTOOL_RUNTIME_DB_USER');
        $previousPassword = getenv('MTOOL_RUNTIME_DB_PASSWORD');
        $previousMtoolDb = $GLOBALS['mtooldb'] ?? null;

        try {
            putenv('MTOOL_RUNTIME_DB_DSN');
            putenv('MTOOL_RUNTIME_DB_USER');
            putenv('MTOOL_RUNTIME_DB_PASSWORD');
            putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);
            $transactionDb = new MtoolGeneratedDbAccessRuntimeDb();
            $GLOBALS['mtooldb'] = $transactionDb;
            self::assertInstanceOf(
                MtoolGeneratedDbAccessPdoResult::class,
                $transactionDb->execute(
                    'CREATE TABLE task_card (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        title TEXT NOT NULL,
                        body TEXT NOT NULL,
                        status TEXT NOT NULL,
                        assigned_to TEXT NOT NULL,
                        priority INTEGER NOT NULL,
                        due_date TEXT DEFAULT NULL,
                        completed_at TEXT DEFAULT NULL,
                        updated_at TEXT NOT NULL
                    )',
                ),
            );

            [$normalized, $dispatcher, $guard, $coordination] = $buildReady('DB backed committed');
            $callables = app_lab_sample18_task_board_generated_submit_transaction_binding_callables(
                $transactionDb,
                static function (array $context): object {
                    $GLOBALS['mtooldb'] = $context['transaction_db'];

                    return new TaskCardDBAccess();
                },
            );
            $committed = app_lab_sample18_task_board_generated_submit_transaction_adapter(
                $normalized,
                $dispatcher,
                $guard,
                $coordination,
                true,
                $callables['begin'],
                $callables['commit'],
                $callables['rollback'],
                $callables['dbaccess'],
            );
            self::assertSame('executed', $committed['status']);
            self::assertTrue($committed['success']);
            self::assertSame('committed', $committed['transaction_status']);
            self::assertSame('executed', $committed['dbaccess_status']);
            self::assertSame('planned_not_written', $committed['recording_status']);

            $committedRows = $transactionDb->query("SELECT title FROM task_card WHERE title = 'DB backed committed'");
            self::assertInstanceOf(MtoolGeneratedDbAccessPdoResult::class, $committedRows);
            self::assertSame(['DB backed committed'], $committedRows->fetch_row());

            [$normalized, $dispatcher, $guard, $coordination] = $buildReady('DB backed rolled back');
            $failingCallables = app_lab_sample18_task_board_generated_submit_transaction_binding_callables(
                $transactionDb,
                static function (array $context): object {
                    $GLOBALS['mtooldb'] = $context['transaction_db'];

                    return new class {
                        public function InsertTaskCard(object $TaskCardObj): object
                        {
                            global $mtooldb;
                            $mtooldb->execute(
                                'insert into task_card (title, body, status, assigned_to, priority, due_date, updated_at) values(?, ?, ?, ?, ?, ?, ?)',
                                [
                                    $TaskCardObj->title,
                                    $TaskCardObj->body,
                                    $TaskCardObj->status,
                                    $TaskCardObj->assignedTo,
                                    $TaskCardObj->priority,
                                    $TaskCardObj->dueDate,
                                    $TaskCardObj->updatedAt,
                                ],
                            );

                            return (object) ['errno' => 1, 'error' => 'forced failure after insert'];
                        }
                    };
                },
            );
            $rolledBack = app_lab_sample18_task_board_generated_submit_transaction_adapter(
                $normalized,
                $dispatcher,
                $guard,
                $coordination,
                true,
                $failingCallables['begin'],
                $failingCallables['commit'],
                $failingCallables['rollback'],
                $failingCallables['dbaccess'],
            );
            self::assertSame('failed', $rolledBack['status']);
            self::assertFalse($rolledBack['success']);
            self::assertSame('rolled_back', $rolledBack['transaction_status']);
            self::assertSame('failed', $rolledBack['dbaccess_status']);
            self::assertSame('dbaccess_failed', $rolledBack['failure_code']);
            self::assertSame('forced failure after insert', $rolledBack['error']);

            $rolledBackRows = $transactionDb->query("SELECT COUNT(*) FROM task_card WHERE title = 'DB backed rolled back'");
            self::assertInstanceOf(MtoolGeneratedDbAccessPdoResult::class, $rolledBackRows);
            $rolledBackCount = $rolledBackRows->fetch_row();
            self::assertSame(0, (int) ($rolledBackCount[0] ?? -1));
        } finally {
            $previousDsn === false ? putenv('MTOOL_RUNTIME_DB_DSN') : putenv('MTOOL_RUNTIME_DB_DSN=' . $previousDsn);
            $previousSqlitePath === false ? putenv('MTOOL_RUNTIME_SQLITE_PATH') : putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $previousSqlitePath);
            $previousUser === false ? putenv('MTOOL_RUNTIME_DB_USER') : putenv('MTOOL_RUNTIME_DB_USER=' . $previousUser);
            $previousPassword === false ? putenv('MTOOL_RUNTIME_DB_PASSWORD') : putenv('MTOOL_RUNTIME_DB_PASSWORD=' . $previousPassword);
            if ($previousMtoolDb === null) {
                unset($GLOBALS['mtooldb']);
            } else {
                $GLOBALS['mtooldb'] = $previousMtoolDb;
            }
            if (is_file($sqlitePath)) {
                unlink($sqlitePath);
            }
        }
    }

    public function testMiniTaskBoardNoCodeGoldenFixtureMatchesSeedAndRouteContract(): void
    {
        $fixture = $this->sample18NoCodeGoldenFixture();
        $checklist = $this->sample18FastContractChecklist();
        $root = dirname(__DIR__, 2);
        $seedSql = (string) file_get_contents(
            $root . '/sample/tutorials/sample18-mini-task-board-demo/seed/900_020_sample18_table_seed.sql',
        );
        $routeSource = (string) file_get_contents($root . '/mtool/app/lab_sample18_task_board_page.php');
        $dbAccessSeed = (string) file_get_contents(
            $root . '/sample/tutorials/sample18-mini-task-board-demo/seed/900_025_sample18_db_access_seed.sql',
        );

        self::assertSame('sample18-no-code-ui-golden-v1', $fixture['fixture_version'] ?? '');
        self::assertSame('SAMPLE18', $fixture['project_key'] ?? '');
        self::assertSame('sample18-no-code-fast-contract-checklist-v1', $checklist['checklist_version'] ?? '');
        self::assertSame($fixture['project_key'] ?? '', $checklist['project_key'] ?? '');
        self::assertSame($fixture['source_table'] ?? '', $checklist['source_table'] ?? '');
        self::assertSame('/samples/sample18-task-board', $fixture['route_path'] ?? '');
        self::assertSame('task_card', $fixture['source_table'] ?? '');
        self::assertFalse($fixture['no_code_conversion_boundary']['generated_route_replacement'] ?? true);
        self::assertFalse($fixture['no_code_conversion_boundary']['generated_button_execution'] ?? true);
        self::assertSame(
            $fixture['no_code_conversion_boundary']['generated_route_replacement'] ?? null,
            $checklist['conversion_boundary']['generated_route_replacement'] ?? null,
        );
        self::assertSame(
            $fixture['no_code_conversion_boundary']['generated_button_execution'] ?? null,
            $checklist['conversion_boundary']['generated_button_execution'] ?? null,
        );

        foreach (($fixture['seed_rows'] ?? []) as $row) {
            self::assertIsArray($row);
            self::assertStringContainsString((string) ($row['title'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['status'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['assigned_to'] ?? ''), $seedSql);
            self::assertStringContainsString((string) ($row['due_date'] ?? ''), $seedSql);
        }

        $contract = $fixture['dom_contract'] ?? [];
        $statusFilterContract = $checklist['status_filter_contract'] ?? [];
        self::assertIsArray($contract);
        self::assertSame($contract['status_filter_values'] ?? [], $statusFilterContract['curated_route_values'] ?? []);
        self::assertStringContainsString((string) ($contract['title'] ?? ''), $routeSource);
        foreach (($statusFilterContract['curated_route_values'] ?? []) as $value) {
            self::assertStringContainsString('value="' . $value . '"', $routeSource);
        }
        foreach (($contract['form_fields'] ?? []) as $fieldName) {
            self::assertStringContainsString('name="' . $fieldName . '"', $routeSource);
        }
        foreach (($contract['table_columns'] ?? []) as $columnLabel) {
            self::assertStringContainsString('>' . $columnLabel . '<', $routeSource);
        }
        foreach (($contract['actions'] ?? []) as $action) {
            $needle = in_array($action, ['create', 'update'], true)
                ? "action === '" . $action . "'"
                : 'value="' . $action . '"';
            self::assertStringContainsString($needle, $routeSource);
        }
        $actionInputInventory = $checklist['action_input_mapping_inventory'] ?? [];
        self::assertFalse($actionInputInventory['generated_button_execution'] ?? true);
        self::assertFalse($actionInputInventory['route_replacement'] ?? true);
        foreach (($actionInputInventory['operations'] ?? []) as $operation) {
            self::assertIsArray($operation);
            $routeAction = (string) ($operation['curated_route_action'] ?? '');
            $routeNeedle = in_array($routeAction, ['create', 'update'], true)
                ? "action === '" . $routeAction . "'"
                : 'value="' . $routeAction . '"';
            self::assertStringContainsString($routeNeedle, $routeSource);
            foreach (array_merge(
                $operation['key_fields'] ?? [],
                $operation['required_client_fields'] ?? [],
                $operation['optional_client_fields'] ?? [],
            ) as $fieldName) {
                self::assertStringContainsString('name="' . $fieldName . '"', $routeSource);
            }
            $dbAccessFunction = (string) ($operation['db_access_function'] ?? '');
            if ($dbAccessFunction !== '') {
                self::assertStringContainsString("'" . $dbAccessFunction . "'", $dbAccessSeed);
            }
        }
        self::assertSame(
            $checklist['html_dom_contract']['disabled_extension_action_keys'] ?? [],
            $fixture['no_code_action_keys'] ?? [],
        );
        self::assertSame(
            $checklist['html_dom_contract']['managed_action_keys'] ?? [],
            $fixture['no_code_managed_action_keys'] ?? [],
        );
    }

    public function testMiniTaskBoardGeneratedSubmitRequestContractPreflight(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        self::assertFalse($submitContract['generated_route_added'] ?? true);
        self::assertFalse($submitContract['mutation_enabled'] ?? true);

        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        self::assertNotSame('', $timestamp);

        $contracts = app_lab_sample18_task_board_generated_submit_contracts();
        $operations = $submitContract['operations'] ?? [];
        self::assertIsArray($operations);
        self::assertSame(['create_task_card', 'update_task_card', 'complete_task_card'], array_keys($operations));

        $inventoryOperations = $checklist['action_input_mapping_inventory']['operations'] ?? [];
        self::assertIsArray($inventoryOperations);
        $inventoryByKey = [];
        foreach ($inventoryOperations as $operation) {
            self::assertIsArray($operation);
            $inventoryByKey[(string) ($operation['operation_key'] ?? '')] = $operation;
        }

        $dedupeKeys = [];
        foreach ($operations as $operationKey => $expectation) {
            self::assertIsArray($expectation);
            self::assertArrayHasKey($operationKey, $contracts);
            self::assertSame($expectation['curated_route_action'] ?? '', $contracts[$operationKey]['curated_route_action'] ?? '');
            self::assertSame($expectation['db_access_function'] ?? '', $contracts[$operationKey]['db_access_function'] ?? '');
            self::assertSame(
                $inventoryByKey[$operationKey]['curated_route_action'] ?? '',
                $contracts[$operationKey]['curated_route_action'] ?? '',
            );
            self::assertSame(
                $inventoryByKey[$operationKey]['db_access_function'] ?? '',
                $contracts[$operationKey]['db_access_function'] ?? '',
            );

            $valid = app_lab_sample18_task_board_normalize_generated_submit_request(
                (string) $operationKey,
                is_array($expectation['valid_input'] ?? null) ? $expectation['valid_input'] : [],
                $timestamp,
            );
            self::assertTrue($valid['ok'], (string) $operationKey);
            self::assertSame('', $valid['failure_code']);
            self::assertSame($expectation['expected_payload'] ?? [], $valid['payload']);
            self::assertSame($expectation['ignored_input_fields'] ?? [], $valid['ignored_input_fields']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($valid);
            self::assertTrue($dispatcher['ok'] ?? false, (string) $operationKey);
            self::assertSame('dry_run', $dispatcher['dispatch_state'] ?? '');
            self::assertFalse($dispatcher['executed'] ?? true);
            self::assertFalse($dispatcher['mutation_enabled'] ?? true);
            self::assertSame($expectation['db_access_function'] ?? '', $dispatcher['db_access_function'] ?? '');
            self::assertSame('TaskCardDBAccess', $dispatcher['db_access_class'] ?? '');
            self::assertSame('TaskCardData', $dispatcher['data_object'] ?? '');
            self::assertSame(
                $expectation['expected_dispatcher_bound_fields'] ?? [],
                $dispatcher['bound_fields'] ?? [],
            );
            self::assertSame(
                ['TaskCardObj' => $expectation['expected_dispatcher_bound_fields'] ?? []],
                $dispatcher['method_arguments'] ?? [],
            );
            $idempotencyAuditPreview = app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
                $valid,
                $dispatcher,
                'blocked',
                'generated_submit_disabled',
            );
            $dedupeKey = (string) ($idempotencyAuditPreview['dedupe_key_preview'] ?? '');
            $fingerprint = (string) ($idempotencyAuditPreview['payload_fingerprint'] ?? '');
            self::assertStringStartsWith('sample18.generated_submit.' . $operationKey . '.', $dedupeKey);
            self::assertSame(64, strlen($fingerprint));
            self::assertSame('sample18.generated_submit.requested', $idempotencyAuditPreview['audit_event_preview']['event_type'] ?? '');
            self::assertSame('blocked', $idempotencyAuditPreview['audit_event_preview']['result'] ?? '');
            self::assertSame('generated_submit_disabled', $idempotencyAuditPreview['audit_event_preview']['message'] ?? '');
            self::assertSame($dedupeKey, $idempotencyAuditPreview['audit_event_preview']['metadata']['dedupe_key'] ?? '');
            self::assertSame($fingerprint, $idempotencyAuditPreview['audit_event_preview']['metadata']['payload_fingerprint'] ?? '');
            self::assertSame(
                $expectation['expected_dispatcher_bound_fields'] ?? [],
                $idempotencyAuditPreview['audit_event_preview']['metadata']['dispatcher_bound_fields'] ?? [],
            );
            $reorderedDispatcher = $dispatcher;
            $reorderedDispatcher['bound_fields'] = array_reverse($dispatcher['bound_fields'] ?? [], true);
            self::assertSame(
                $fingerprint,
                app_lab_sample18_task_board_generated_submit_payload_fingerprint($reorderedDispatcher),
            );
            $dedupeKeys[] = $dedupeKey;

            $invalid = app_lab_sample18_task_board_normalize_generated_submit_request(
                (string) $operationKey,
                is_array($expectation['invalid_input'] ?? null) ? $expectation['invalid_input'] : [],
                $timestamp,
            );
            self::assertFalse($invalid['ok'], (string) $operationKey);
            self::assertSame('validation_error', $invalid['failure_code']);
            self::assertSame($expectation['expected_errors'] ?? [], $invalid['errors']);
            $invalidPreview = app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
                $invalid,
                app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($invalid),
                'invalid',
                'validation_error',
            );
            self::assertSame('', $invalidPreview['dedupe_key_preview'] ?? 'not-empty');
            self::assertSame([], $invalidPreview['audit_event_preview'] ?? ['not-empty']);
        }
        self::assertCount(count($dedupeKeys), array_unique($dedupeKeys));

        $unknown = app_lab_sample18_task_board_normalize_generated_submit_request(
            'delete_task_card',
            ['id' => '1801'],
            $timestamp,
        );
        self::assertFalse($unknown['ok']);
        self::assertSame('unknown_operation', $unknown['failure_code']);
        self::assertSame(['operation.unknown'], $unknown['errors']);
    }

    public function testMiniTaskBoardGeneratedSubmitRouteBlockedWrapper(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        $createExpectation = $submitContract['operations']['create_task_card'] ?? [];
        self::assertIsArray($createExpectation);

        $route = app_route_match([
            'path' => app_lab_sample18_task_board_generated_submit_path(),
        ]);
        self::assertSame('lab_sample18_task_board_generated_submit', $route['name']);
        self::assertTrue(app_route_requires_auth('lab_sample18_task_board_generated_submit'));

        $notPost = app_lab_sample18_task_board_generated_submit_blocked_response('GET', [], $timestamp);
        self::assertSame(405, $notPost['status_code']);
        self::assertSame('method_not_allowed', $notPost['payload']['failure_code'] ?? '');
        self::assertFalse($notPost['payload']['mutation_enabled'] ?? true);

        $validPost = array_merge(
            ['operation_key' => 'create_task_card', '_csrf_token' => 'client-token'],
            is_array($createExpectation['valid_input'] ?? null) ? $createExpectation['valid_input'] : [],
        );
        $blocked = app_lab_sample18_task_board_generated_submit_blocked_response('POST', $validPost, $timestamp);
        self::assertSame(409, $blocked['status_code']);
        self::assertFalse($blocked['payload']['ok'] ?? true);
        self::assertFalse($blocked['payload']['accepted'] ?? true);
        self::assertSame('blocked', $blocked['payload']['result'] ?? '');
        self::assertSame('generated_submit_disabled', $blocked['payload']['failure_code'] ?? '');
        self::assertSame('create_task_card', $blocked['payload']['operation_key'] ?? '');
        self::assertSame('create', $blocked['payload']['curated_route_action'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['db_access_function'] ?? '');
        self::assertSame($createExpectation['expected_payload'] ?? [], $blocked['payload']['normalized_payload'] ?? []);
        self::assertSame($createExpectation['ignored_input_fields'] ?? [], $blocked['payload']['ignored_input_fields'] ?? []);
        self::assertSame('dry_run', $blocked['payload']['dispatcher_result']['dispatch_state'] ?? '');
        self::assertFalse($blocked['payload']['dispatcher_result']['executed'] ?? true);
        self::assertFalse($blocked['payload']['dispatcher_result']['mutation_enabled'] ?? true);
        self::assertSame(
            $createExpectation['expected_dispatcher_bound_fields'] ?? [],
            $blocked['payload']['dispatcher_result']['bound_fields'] ?? [],
        );
        self::assertStringStartsWith(
            'sample18.generated_submit.create_task_card.',
            (string) ($blocked['payload']['dedupe_key_preview'] ?? ''),
        );
        self::assertSame(64, strlen((string) ($blocked['payload']['payload_fingerprint'] ?? '')));
        self::assertSame('sample18.generated_submit.requested', $blocked['payload']['audit_event_preview']['event_type'] ?? '');
        self::assertSame('blocked', $blocked['payload']['audit_event_preview']['result'] ?? '');
        self::assertSame(
            $blocked['payload']['dedupe_key_preview'] ?? '',
            $blocked['payload']['audit_event_preview']['metadata']['dedupe_key'] ?? 'missing',
        );
        self::assertSame('skipped', $blocked['payload']['audit_append']['status'] ?? '');
        self::assertSame('no_app', $blocked['payload']['audit_append']['reason'] ?? '');
        self::assertSame('skipped', $blocked['payload']['idempotency']['status'] ?? '');
        self::assertSame('no_app', $blocked['payload']['idempotency']['reason'] ?? '');
        self::assertSame('disabled', $blocked['payload']['mutation_gate']['status'] ?? '');
        self::assertFalse($blocked['payload']['mutation_gate']['mutation_enabled'] ?? true);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['mutation_gate']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['dbaccess_execution_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['executed'] ?? true);
        self::assertSame('TaskCardDBAccess', $blocked['payload']['dbaccess_execution_plan']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['dbaccess_execution_plan']['db_access_function'] ?? '');
        self::assertSame('TaskCardData', $blocked['payload']['dbaccess_execution_plan']['data_object'] ?? '');
        self::assertSame('not_opened', $blocked['payload']['dbaccess_execution_plan']['transaction'] ?? '');
        self::assertContains('mutation_gate_not_ready', $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['transaction_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['ready'] ?? true);
        self::assertSame('not_opened', $blocked['payload']['transaction_plan']['transaction'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['will_execute'] ?? true);
        self::assertFalse($blocked['payload']['transaction_plan']['will_update_audit'] ?? true);
        self::assertFalse($blocked['payload']['transaction_plan']['will_update_idempotency'] ?? true);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['transaction_plan']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['execution_update_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['execution_update_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_write_audit'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_update_idempotency'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_execute'] ?? true);
        self::assertContains('transaction_plan_not_ready', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['execution_guard']['status'] ?? '');
        self::assertFalse($blocked['payload']['execution_guard']['ready'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_open_transaction'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_call_dbaccess'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_write_execution_audit'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_update_idempotency_execution'] ?? true);
        self::assertSame('sample18_application_db', $blocked['payload']['execution_guard']['db_handle'] ?? '');
        self::assertSame('TaskCardDBAccess', $blocked['payload']['execution_guard']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['execution_guard']['db_access_function'] ?? '');
        self::assertSame('create_task_card', $blocked['payload']['execution_guard']['operation_key'] ?? '');
        self::assertContains('audit_append_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('idempotency_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('mutation_gate_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('execution_update_plan_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('request_audit_event_key_missing', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['executor_coordination_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['executor_coordination_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_call_dbaccess'] ?? true);
        self::assertContains('executor_feature_flag_disabled', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('execution_guard_not_ready', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('request_audit_event_key_missing', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertFalse($blocked['payload']['mutation_enabled'] ?? true);

        $missingCsrf = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'create_task_card'],
            $timestamp,
            'missing',
        );
        self::assertSame(403, $missingCsrf['status_code']);
        self::assertSame('missing_csrf', $missingCsrf['payload']['failure_code'] ?? '');
        self::assertSame(['csrf.missing'], $missingCsrf['payload']['errors'] ?? []);
        self::assertArrayNotHasKey('dbaccess_execution_plan', $missingCsrf['payload']);
        self::assertArrayNotHasKey('transaction_plan', $missingCsrf['payload']);
        self::assertArrayNotHasKey('execution_update_plan', $missingCsrf['payload']);
        self::assertArrayNotHasKey('execution_guard', $missingCsrf['payload']);
        self::assertArrayNotHasKey('executor_coordination_plan', $missingCsrf['payload']);
        self::assertFalse($missingCsrf['payload']['mutation_enabled'] ?? true);

        $invalidCsrf = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'create_task_card', '_csrf_token' => 'wrong-token'],
            $timestamp,
            'invalid',
        );
        self::assertSame(403, $invalidCsrf['status_code']);
        self::assertSame('invalid_csrf', $invalidCsrf['payload']['failure_code'] ?? '');
        self::assertSame(['csrf.invalid'], $invalidCsrf['payload']['errors'] ?? []);
        self::assertArrayNotHasKey('dbaccess_execution_plan', $invalidCsrf['payload']);
        self::assertArrayNotHasKey('transaction_plan', $invalidCsrf['payload']);
        self::assertArrayNotHasKey('execution_update_plan', $invalidCsrf['payload']);
        self::assertArrayNotHasKey('execution_guard', $invalidCsrf['payload']);
        self::assertArrayNotHasKey('executor_coordination_plan', $invalidCsrf['payload']);
        self::assertFalse($invalidCsrf['payload']['mutation_enabled'] ?? true);

        $invalid = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'update_task_card', 'id' => '0', 'title' => ''],
            $timestamp,
        );
        self::assertSame(422, $invalid['status_code']);
        self::assertSame('validation_error', $invalid['payload']['failure_code'] ?? '');
        self::assertSame(['id.invalid', 'title.required'], $invalid['payload']['errors'] ?? []);
        self::assertArrayNotHasKey('dbaccess_execution_plan', $invalid['payload']);
        self::assertArrayNotHasKey('transaction_plan', $invalid['payload']);
        self::assertArrayNotHasKey('execution_update_plan', $invalid['payload']);
        self::assertArrayNotHasKey('execution_guard', $invalid['payload']);
        self::assertArrayNotHasKey('executor_coordination_plan', $invalid['payload']);

        $unknown = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'delete_task_card', 'id' => '1801'],
            $timestamp,
        );
        self::assertSame(404, $unknown['status_code']);
        self::assertSame('unknown_operation', $unknown['payload']['failure_code'] ?? '');
        self::assertArrayNotHasKey('dbaccess_execution_plan', $unknown['payload']);
        self::assertArrayNotHasKey('transaction_plan', $unknown['payload']);
        self::assertArrayNotHasKey('execution_update_plan', $unknown['payload']);
        self::assertArrayNotHasKey('execution_guard', $unknown['payload']);
        self::assertArrayNotHasKey('executor_coordination_plan', $unknown['payload']);
    }

    public function testMiniTaskBoardGeneratedSubmitBlockedAuditAppendFirstSlice(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        $createExpectation = $submitContract['operations']['create_task_card'] ?? [];
        self::assertIsArray($createExpectation);

        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        $principal = [
            'id' => 'sample18-operator@example.test',
            'auth_source' => 'phpunit',
        ];

        $validPost = array_merge(
            ['operation_key' => 'create_task_card', '_csrf_token' => 'client-token'],
            is_array($createExpectation['valid_input'] ?? null) ? $createExpectation['valid_input'] : [],
        );
        $blocked = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $validPost,
            $timestamp,
            'valid',
            $app,
            $principal,
        );

        self::assertSame(409, $blocked['status_code']);
        self::assertSame('blocked', $blocked['payload']['result'] ?? '');
        self::assertSame('generated_submit_disabled', $blocked['payload']['failure_code'] ?? '');
        self::assertFalse($blocked['payload']['mutation_enabled'] ?? true);
        self::assertSame('appended', $blocked['payload']['audit_append']['status'] ?? '');
        self::assertFalse($blocked['payload']['audit_append']['skipped'] ?? true);
        self::assertSame('', $blocked['payload']['audit_append']['error'] ?? 'unexpected');
        self::assertSame('sample18-operator@example.test', $blocked['payload']['audit_event_preview']['actor_login_id'] ?? '');
        self::assertSame('web_lab_login', $blocked['payload']['audit_event_preview']['actor_source'] ?? '');
        self::assertSame(
            $blocked['payload']['dedupe_key_preview'] ?? '',
            $blocked['payload']['audit_append']['item']['target_key'] ?? 'missing',
        );
        self::assertSame('blocked', $blocked['payload']['audit_append']['item']['result'] ?? '');
        self::assertSame(
            'generated_submit_disabled',
            $blocked['payload']['audit_append']['item']['metadata']['failure_code'] ?? '',
        );
        self::assertSame(
            $createExpectation['expected_dispatcher_bound_fields'] ?? [],
            $blocked['payload']['audit_append']['item']['metadata']['dispatcher_bound_fields'] ?? [],
        );
        self::assertSame('recorded', $blocked['payload']['idempotency']['status'] ?? '');
        self::assertTrue($blocked['payload']['idempotency']['created'] ?? false);
        self::assertSame('', $blocked['payload']['idempotency']['error'] ?? 'unexpected');
        self::assertSame(
            $blocked['payload']['dedupe_key_preview'] ?? '',
            $blocked['payload']['idempotency']['dedupe_key'] ?? 'missing',
        );
        self::assertSame(
            $blocked['payload']['dedupe_key_preview'] ?? '',
            $blocked['payload']['idempotency']['item']['dedupe_key'] ?? 'missing',
        );
        self::assertSame(0, $blocked['payload']['idempotency']['item']['duplicate_count'] ?? -1);
        self::assertSame('disabled', $blocked['payload']['mutation_gate']['status'] ?? '');
        self::assertFalse($blocked['payload']['mutation_gate']['ready'] ?? true);
        self::assertFalse($blocked['payload']['mutation_gate']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['mutation_gate']['executed'] ?? true);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['mutation_gate']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['dbaccess_execution_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['executed'] ?? true);
        self::assertSame('not_opened', $blocked['payload']['dbaccess_execution_plan']['transaction'] ?? '');
        self::assertContains('enablement_flag_disabled', $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['transaction_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['ready'] ?? true);
        self::assertSame('not_opened', $blocked['payload']['transaction_plan']['transaction'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['will_execute'] ?? true);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['transaction_plan']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['execution_update_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['execution_update_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_write_audit'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_update_idempotency'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_execute'] ?? true);
        self::assertContains('transaction_plan_not_ready', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['execution_guard']['status'] ?? '');
        self::assertFalse($blocked['payload']['execution_guard']['ready'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_call_dbaccess'] ?? true);
        self::assertContains('mutation_gate_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertSame('blocked', $blocked['payload']['executor_coordination_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['executor_coordination_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_call_dbaccess'] ?? true);
        self::assertContains('executor_feature_flag_disabled', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('execution_guard_not_ready', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('enablement_flag_disabled', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);

        $latest = app_audit_log_fetch_latest($app, [
            'project_key' => 'SAMPLE18',
            'event_type' => 'sample18.generated_submit.requested',
            'target_key' => (string) ($blocked['payload']['dedupe_key_preview'] ?? ''),
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        self::assertSame('sample18_task_card', $latest['items'][0]['target_type'] ?? '');

        $duplicate = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $validPost,
            $timestamp,
            'valid',
            $app,
            $principal,
        );
        self::assertSame(409, $duplicate['status_code']);
        self::assertSame('duplicate', $duplicate['payload']['idempotency']['status'] ?? '');
        self::assertFalse($duplicate['payload']['idempotency']['created'] ?? true);
        self::assertSame(
            $blocked['payload']['dedupe_key_preview'] ?? '',
            $duplicate['payload']['idempotency']['dedupe_key'] ?? 'missing',
        );
        self::assertSame(1, $duplicate['payload']['idempotency']['item']['duplicate_count'] ?? -1);
        self::assertSame('disabled', $duplicate['payload']['mutation_gate']['status'] ?? '');
        self::assertContains('duplicate_generated_submit', $duplicate['payload']['mutation_gate']['reasons'] ?? []);

        $enabledDuplicate = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $validPost,
            $timestamp,
            'valid',
            array_merge($app, ['sample18_generated_submit_mutation_enabled' => true]),
            $principal,
        );
        self::assertSame(409, $enabledDuplicate['status_code']);
        self::assertSame('duplicate', $enabledDuplicate['payload']['idempotency']['status'] ?? '');
        self::assertSame('blocked', $enabledDuplicate['payload']['mutation_gate']['status'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['mutation_gate']['ready'] ?? true);
        self::assertFalse($enabledDuplicate['payload']['mutation_gate']['mutation_enabled'] ?? true);
        self::assertFalse($enabledDuplicate['payload']['mutation_gate']['executed'] ?? true);
        self::assertContains('duplicate_generated_submit', $enabledDuplicate['payload']['mutation_gate']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $enabledDuplicate['payload']['mutation_gate']['reasons'] ?? []);
        self::assertSame('blocked', $enabledDuplicate['payload']['dbaccess_execution_plan']['status'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['dbaccess_execution_plan']['ready'] ?? true);
        self::assertFalse($enabledDuplicate['payload']['dbaccess_execution_plan']['mutation_enabled'] ?? true);
        self::assertFalse($enabledDuplicate['payload']['dbaccess_execution_plan']['executed'] ?? true);
        self::assertSame('not_opened', $enabledDuplicate['payload']['dbaccess_execution_plan']['transaction'] ?? '');
        self::assertContains('duplicate_generated_submit', $enabledDuplicate['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $enabledDuplicate['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertSame('blocked', $enabledDuplicate['payload']['transaction_plan']['status'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['transaction_plan']['ready'] ?? true);
        self::assertSame('not_opened', $enabledDuplicate['payload']['transaction_plan']['transaction'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['transaction_plan']['will_execute'] ?? true);
        self::assertContains('duplicate_generated_submit', $enabledDuplicate['payload']['transaction_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $enabledDuplicate['payload']['transaction_plan']['reasons'] ?? []);
        self::assertSame('blocked', $enabledDuplicate['payload']['execution_update_plan']['status'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['execution_update_plan']['ready'] ?? true);
        self::assertContains('transaction_plan_not_ready', $enabledDuplicate['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertContains('duplicate_generated_submit', $enabledDuplicate['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $enabledDuplicate['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertSame('blocked', $enabledDuplicate['payload']['execution_guard']['status'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['execution_guard']['ready'] ?? true);
        self::assertFalse($enabledDuplicate['payload']['execution_guard']['will_call_dbaccess'] ?? true);
        self::assertContains('duplicate_generated_submit', $enabledDuplicate['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('execution_update_plan_not_ready', $enabledDuplicate['payload']['execution_guard']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $enabledDuplicate['payload']['execution_guard']['reasons'] ?? []);
        self::assertSame('blocked', $enabledDuplicate['payload']['executor_coordination_plan']['status'] ?? '');
        self::assertFalse($enabledDuplicate['payload']['executor_coordination_plan']['ready'] ?? true);
        self::assertFalse($enabledDuplicate['payload']['executor_coordination_plan']['will_call_dbaccess'] ?? true);
        self::assertContains('executor_feature_flag_disabled', $enabledDuplicate['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('execution_guard_not_ready', $enabledDuplicate['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('duplicate_generated_submit', $enabledDuplicate['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $enabledDuplicate['payload']['executor_coordination_plan']['reasons'] ?? []);

        $missingCsrf = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'create_task_card'],
            $timestamp,
            'missing',
            $app,
            $principal,
        );
        self::assertSame(403, $missingCsrf['status_code']);
        self::assertArrayNotHasKey('audit_append', $missingCsrf['payload']);
        self::assertArrayNotHasKey('idempotency', $missingCsrf['payload']);
        self::assertArrayNotHasKey('dbaccess_execution_plan', $missingCsrf['payload']);
        self::assertArrayNotHasKey('transaction_plan', $missingCsrf['payload']);
        self::assertArrayNotHasKey('execution_update_plan', $missingCsrf['payload']);
        self::assertArrayNotHasKey('execution_guard', $missingCsrf['payload']);
        self::assertArrayNotHasKey('executor_coordination_plan', $missingCsrf['payload']);

        $invalid = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            ['operation_key' => 'update_task_card', 'id' => '0', 'title' => ''],
            $timestamp,
            'valid',
            $app,
            $principal,
        );
        self::assertSame(422, $invalid['status_code']);
        self::assertArrayNotHasKey('audit_append', $invalid['payload']);
        self::assertArrayNotHasKey('idempotency', $invalid['payload']);
        self::assertArrayNotHasKey('dbaccess_execution_plan', $invalid['payload']);
        self::assertArrayNotHasKey('transaction_plan', $invalid['payload']);
        self::assertArrayNotHasKey('execution_update_plan', $invalid['payload']);
        self::assertArrayNotHasKey('execution_guard', $invalid['payload']);
        self::assertArrayNotHasKey('executor_coordination_plan', $invalid['payload']);

        $afterFailures = app_audit_log_fetch_latest($app, [
            'project_key' => 'SAMPLE18',
            'event_type' => 'sample18.generated_submit.requested',
            'limit' => 10,
        ]);
        self::assertTrue($afterFailures['ok'], $afterFailures['error']);
        self::assertCount(3, $afterFailures['items']);
        $idempotencyRecords = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'project_key' => 'SAMPLE18',
            'operation_key' => 'create_task_card',
            'limit' => 10,
        ]);
        self::assertTrue($idempotencyRecords['ok'], $idempotencyRecords['error']);
        self::assertCount(1, $idempotencyRecords['items']);
        self::assertSame(2, $idempotencyRecords['items'][0]['duplicate_count'] ?? -1);
    }

    public function testMiniTaskBoardGeneratedSubmitAuditAppendFailureIsVisibleWithoutMutation(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        $createExpectation = $submitContract['operations']['create_task_card'] ?? [];
        self::assertIsArray($createExpectation);

        $brokenApp = [
            'site' => 'lab',
            'db' => ['driver' => 'sqlite', 'dsn' => 'sqlite:/path/that/does/not/exist/sample18-audit.sqlite'],
            'config_db' => ['driver' => 'sqlite', 'dsn' => 'sqlite:/path/that/does/not/exist/sample18-audit.sqlite'],
            'sample18_generated_submit_mutation_enabled' => true,
        ];
        $validPost = array_merge(
            ['operation_key' => 'create_task_card', '_csrf_token' => 'client-token'],
            is_array($createExpectation['valid_input'] ?? null) ? $createExpectation['valid_input'] : [],
        );

        $blocked = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $validPost,
            $timestamp,
            'valid',
            $brokenApp,
            [
                'id' => 'sample18-operator@example.test',
                'auth_source' => 'phpunit',
            ],
        );

        self::assertSame(409, $blocked['status_code']);
        self::assertFalse($blocked['payload']['ok'] ?? true);
        self::assertFalse($blocked['payload']['accepted'] ?? true);
        self::assertSame('blocked', $blocked['payload']['result'] ?? '');
        self::assertSame('generated_submit_disabled', $blocked['payload']['failure_code'] ?? '');
        self::assertSame('dry_run', $blocked['payload']['dispatcher_result']['dispatch_state'] ?? '');
        self::assertFalse($blocked['payload']['dispatcher_result']['executed'] ?? true);
        self::assertFalse($blocked['payload']['dispatcher_result']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['mutation_enabled'] ?? true);
        self::assertSame('failed', $blocked['payload']['audit_append']['status'] ?? '');
        self::assertFalse($blocked['payload']['audit_append']['skipped'] ?? true);
        self::assertSame([], $blocked['payload']['audit_append']['item'] ?? ['unexpected']);
        self::assertNotSame('', $blocked['payload']['audit_append']['error'] ?? '');
        self::assertSame('', $blocked['payload']['audit_append']['reason'] ?? 'unexpected');
        self::assertSame('failed', $blocked['payload']['idempotency']['status'] ?? '');
        self::assertFalse($blocked['payload']['idempotency']['created'] ?? true);
        self::assertSame([], $blocked['payload']['idempotency']['item'] ?? ['unexpected']);
        self::assertNotSame('', $blocked['payload']['idempotency']['error'] ?? '');
        self::assertSame('failed', $blocked['payload']['mutation_gate']['status'] ?? '');
        self::assertContains('audit_append_failed', $blocked['payload']['mutation_gate']['reasons'] ?? []);
        self::assertContains('idempotency_failed', $blocked['payload']['mutation_gate']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $blocked['payload']['mutation_gate']['reasons'] ?? []);
        self::assertFalse($blocked['payload']['mutation_gate']['ready'] ?? true);
        self::assertFalse($blocked['payload']['mutation_gate']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['mutation_gate']['executed'] ?? true);
        self::assertSame('failed', $blocked['payload']['dbaccess_execution_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['executed'] ?? true);
        self::assertSame('not_opened', $blocked['payload']['dbaccess_execution_plan']['transaction'] ?? '');
        self::assertContains('audit_append_failed', $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertContains('idempotency_failed', $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? []);
        self::assertSame('failed', $blocked['payload']['transaction_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['ready'] ?? true);
        self::assertSame('not_opened', $blocked['payload']['transaction_plan']['transaction'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['will_execute'] ?? true);
        self::assertContains('audit_append_failed', $blocked['payload']['transaction_plan']['reasons'] ?? []);
        self::assertContains('idempotency_failed', $blocked['payload']['transaction_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $blocked['payload']['transaction_plan']['reasons'] ?? []);
        self::assertSame('failed', $blocked['payload']['execution_update_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['execution_update_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_write_audit'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_update_idempotency'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_execute'] ?? true);
        self::assertContains('transaction_plan_not_ready', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertContains('audit_append_failed', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertContains('idempotency_failed', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $blocked['payload']['execution_update_plan']['reasons'] ?? []);
        self::assertSame('failed', $blocked['payload']['execution_guard']['status'] ?? '');
        self::assertFalse($blocked['payload']['execution_guard']['ready'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_call_dbaccess'] ?? true);
        self::assertContains('audit_append_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('idempotency_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('execution_update_plan_not_ready', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('audit_append_failed', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertContains('idempotency_failed', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $blocked['payload']['execution_guard']['reasons'] ?? []);
        self::assertSame('failed', $blocked['payload']['executor_coordination_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['executor_coordination_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_call_dbaccess'] ?? true);
        self::assertContains('executor_feature_flag_disabled', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('execution_guard_not_ready', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('audit_append_failed', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertContains('idempotency_failed', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
        self::assertNotContains('enablement_flag_disabled', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);
    }

    public function testMiniTaskBoardGeneratedSubmitRouteReadyExecutionPlanIsMetadataOnly(): void
    {
        $checklist = $this->sample18FastContractChecklist();
        $submitContract = $checklist['generated_submit_request_contract'] ?? [];
        self::assertIsArray($submitContract);
        $timestamp = (string) ($submitContract['timestamp_fixture'] ?? '');
        $createExpectation = $submitContract['operations']['create_task_card'] ?? [];
        self::assertIsArray($createExpectation);

        $app = array_merge($this->sqliteApp(), ['sample18_generated_submit_mutation_enabled' => true]);
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        $principal = [
            'id' => 'sample18-ready@example.test',
            'auth_source' => 'phpunit',
        ];

        $validPost = array_merge(
            ['operation_key' => 'create_task_card', '_csrf_token' => 'client-token'],
            is_array($createExpectation['valid_input'] ?? null) ? $createExpectation['valid_input'] : [],
        );
        $blocked = app_lab_sample18_task_board_generated_submit_blocked_response(
            'POST',
            $validPost,
            $timestamp,
            'valid',
            $app,
            $principal,
        );

        self::assertSame(409, $blocked['status_code']);
        self::assertFalse($blocked['payload']['ok'] ?? true);
        self::assertFalse($blocked['payload']['accepted'] ?? true);
        self::assertSame('blocked', $blocked['payload']['result'] ?? '');
        self::assertSame('generated_submit_disabled', $blocked['payload']['failure_code'] ?? '');
        self::assertFalse($blocked['payload']['mutation_enabled'] ?? true);
        self::assertSame('appended', $blocked['payload']['audit_append']['status'] ?? '');
        self::assertSame('recorded', $blocked['payload']['idempotency']['status'] ?? '');
        self::assertTrue($blocked['payload']['idempotency']['created'] ?? false);
        self::assertSame(0, $blocked['payload']['idempotency']['item']['duplicate_count'] ?? -1);
        self::assertSame('ready', $blocked['payload']['mutation_gate']['status'] ?? '');
        self::assertTrue($blocked['payload']['mutation_gate']['ready'] ?? false);
        self::assertFalse($blocked['payload']['mutation_gate']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['mutation_gate']['executed'] ?? true);
        self::assertSame([], $blocked['payload']['mutation_gate']['reasons'] ?? ['unexpected']);
        self::assertSame('planned', $blocked['payload']['dbaccess_execution_plan']['status'] ?? '');
        self::assertTrue($blocked['payload']['dbaccess_execution_plan']['ready'] ?? false);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['mutation_enabled'] ?? true);
        self::assertFalse($blocked['payload']['dbaccess_execution_plan']['executed'] ?? true);
        self::assertSame('TaskCardDBAccess', $blocked['payload']['dbaccess_execution_plan']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['dbaccess_execution_plan']['db_access_function'] ?? '');
        self::assertSame('TaskCardData', $blocked['payload']['dbaccess_execution_plan']['data_object'] ?? '');
        self::assertSame(
            ['TaskCardObj' => $createExpectation['expected_dispatcher_bound_fields'] ?? []],
            $blocked['payload']['dbaccess_execution_plan']['method_arguments'] ?? [],
        );
        self::assertSame('not_opened', $blocked['payload']['dbaccess_execution_plan']['transaction'] ?? '');
        self::assertSame([], $blocked['payload']['dbaccess_execution_plan']['reasons'] ?? ['unexpected']);
        self::assertSame('planned', $blocked['payload']['transaction_plan']['status'] ?? '');
        self::assertTrue($blocked['payload']['transaction_plan']['ready'] ?? false);
        self::assertSame('planned_not_opened', $blocked['payload']['transaction_plan']['transaction'] ?? '');
        self::assertSame('sample18_application_db', $blocked['payload']['transaction_plan']['db_handle'] ?? '');
        self::assertSame('config_db_audit_log', $blocked['payload']['transaction_plan']['audit_store'] ?? '');
        self::assertSame('config_db_idempotency', $blocked['payload']['transaction_plan']['idempotency_store'] ?? '');
        self::assertFalse($blocked['payload']['transaction_plan']['will_execute'] ?? true);
        self::assertFalse($blocked['payload']['transaction_plan']['will_update_audit'] ?? true);
        self::assertFalse($blocked['payload']['transaction_plan']['will_update_idempotency'] ?? true);
        self::assertSame('planned_not_written', $blocked['payload']['transaction_plan']['post_execution_audit_update']['status'] ?? '');
        self::assertSame('planned_not_written', $blocked['payload']['transaction_plan']['post_execution_idempotency_update']['status'] ?? '');
        self::assertSame([], $blocked['payload']['transaction_plan']['reasons'] ?? ['unexpected']);
        self::assertSame('planned', $blocked['payload']['execution_update_plan']['status'] ?? '');
        self::assertTrue($blocked['payload']['execution_update_plan']['ready'] ?? false);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_write_audit'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_update_idempotency'] ?? true);
        self::assertFalse($blocked['payload']['execution_update_plan']['will_execute'] ?? true);
        self::assertSame('config_db_audit_log', $blocked['payload']['execution_update_plan']['audit_store'] ?? '');
        self::assertSame('config_db_idempotency', $blocked['payload']['execution_update_plan']['idempotency_store'] ?? '');
        self::assertSame('planned_not_written', $blocked['payload']['execution_update_plan']['execution_audit_update']['status'] ?? '');
        self::assertSame('sample18.generated_submit.executed', $blocked['payload']['execution_update_plan']['execution_audit_update']['event_type'] ?? '');
        self::assertSame($blocked['payload']['dedupe_key_preview'] ?? '', $blocked['payload']['execution_update_plan']['execution_audit_update']['target_key'] ?? 'missing');
        self::assertSame($blocked['payload']['audit_append']['item']['event_key'] ?? '', $blocked['payload']['execution_update_plan']['execution_audit_update']['request_audit_event_key'] ?? 'missing');
        self::assertSame('executed', $blocked['payload']['execution_update_plan']['execution_audit_update']['result'] ?? '');
        self::assertSame('planned_not_opened', $blocked['payload']['execution_update_plan']['execution_audit_update']['transaction_status'] ?? '');
        self::assertSame('TaskCardDBAccess', $blocked['payload']['execution_update_plan']['execution_audit_update']['metadata']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['execution_update_plan']['execution_audit_update']['metadata']['db_access_function'] ?? '');
        self::assertSame('planned_not_written', $blocked['payload']['execution_update_plan']['idempotency_execution_update']['status'] ?? '');
        self::assertSame($blocked['payload']['dedupe_key_preview'] ?? '', $blocked['payload']['execution_update_plan']['idempotency_execution_update']['dedupe_key'] ?? 'missing');
        self::assertSame('planned', $blocked['payload']['execution_update_plan']['idempotency_execution_update']['execution_status'] ?? '');
        self::assertSame('planned_not_executed', $blocked['payload']['execution_update_plan']['idempotency_execution_update']['execution_result_code'] ?? '');
        self::assertSame('planned_not_opened', $blocked['payload']['execution_update_plan']['idempotency_execution_update']['transaction_status'] ?? '');
        self::assertSame([], $blocked['payload']['execution_update_plan']['reasons'] ?? ['unexpected']);
        self::assertSame('allowed', $blocked['payload']['execution_guard']['status'] ?? '');
        self::assertTrue($blocked['payload']['execution_guard']['ready'] ?? false);
        self::assertFalse($blocked['payload']['execution_guard']['will_open_transaction'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_call_dbaccess'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_write_execution_audit'] ?? true);
        self::assertFalse($blocked['payload']['execution_guard']['will_update_idempotency_execution'] ?? true);
        self::assertSame('sample18_application_db', $blocked['payload']['execution_guard']['db_handle'] ?? '');
        self::assertSame('TaskCardDBAccess', $blocked['payload']['execution_guard']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $blocked['payload']['execution_guard']['db_access_function'] ?? '');
        self::assertSame('create_task_card', $blocked['payload']['execution_guard']['operation_key'] ?? '');
        self::assertSame($blocked['payload']['dedupe_key_preview'] ?? '', $blocked['payload']['execution_guard']['dedupe_key'] ?? 'missing');
        self::assertSame($blocked['payload']['audit_append']['item']['event_key'] ?? '', $blocked['payload']['execution_guard']['request_audit_event_key'] ?? 'missing');
        self::assertSame([], $blocked['payload']['execution_guard']['reasons'] ?? ['unexpected']);
        self::assertSame('blocked', $blocked['payload']['executor_coordination_plan']['status'] ?? '');
        self::assertFalse($blocked['payload']['executor_coordination_plan']['ready'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_open_transaction'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_call_dbaccess'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_write_execution_audit'] ?? true);
        self::assertFalse($blocked['payload']['executor_coordination_plan']['will_update_idempotency_execution'] ?? true);
        self::assertSame('sample18_application_db', $blocked['payload']['executor_coordination_plan']['app_db_transaction_boundary']['db_handle'] ?? '');
        self::assertSame('sample18_application_db_only', $blocked['payload']['executor_coordination_plan']['app_db_transaction_boundary']['transaction_scope'] ?? '');
        self::assertFalse($blocked['payload']['executor_coordination_plan']['app_db_transaction_boundary']['cross_store_atomic'] ?? true);
        self::assertSame('config_db_audit_log', $blocked['payload']['executor_coordination_plan']['config_db_persistence_boundary']['audit_store'] ?? '');
        self::assertSame('config_db_idempotency', $blocked['payload']['executor_coordination_plan']['config_db_persistence_boundary']['idempotency_store'] ?? '');
        self::assertFalse($blocked['payload']['executor_coordination_plan']['config_db_persistence_boundary']['cross_store_atomic'] ?? true);
        self::assertSame('recheck_execution_guard', $blocked['payload']['executor_coordination_plan']['ordered_steps'][0]['step'] ?? '');
        self::assertSame('call_dbaccess', $blocked['payload']['executor_coordination_plan']['ordered_steps'][2]['step'] ?? '');
        self::assertSame('append_execution_audit', $blocked['payload']['executor_coordination_plan']['ordered_steps'][5]['step'] ?? '');
        self::assertSame('update_idempotency_execution_outcome', $blocked['payload']['executor_coordination_plan']['ordered_steps'][6]['step'] ?? '');
        self::assertSame($blocked['payload']['dedupe_key_preview'] ?? '', $blocked['payload']['executor_coordination_plan']['dedupe_key'] ?? 'missing');
        self::assertSame($blocked['payload']['audit_append']['item']['event_key'] ?? '', $blocked['payload']['executor_coordination_plan']['request_audit_event_key'] ?? 'missing');
        self::assertContains('executor_feature_flag_disabled', $blocked['payload']['executor_coordination_plan']['reasons'] ?? []);

        $latest = app_audit_log_fetch_latest($app, [
            'project_key' => 'SAMPLE18',
            'event_type' => 'sample18.generated_submit.requested',
            'target_key' => (string) ($blocked['payload']['dedupe_key_preview'] ?? ''),
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);

        $idempotencyRecords = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'project_key' => 'SAMPLE18',
            'operation_key' => 'create_task_card',
            'limit' => 10,
        ]);
        self::assertTrue($idempotencyRecords['ok'], $idempotencyRecords['error']);
        self::assertCount(1, $idempotencyRecords['items']);
        self::assertSame(0, $idempotencyRecords['items'][0]['duplicate_count'] ?? -1);
    }

    public function testMiniTaskBoardGeneratedSubmitMutationGateHelperIsNonMutating(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Gate helper', 'body' => '', 'assigned_to' => '', 'priority' => '10', 'due_date' => ''],
            '2026-07-10 04:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);

        $ready = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_ready']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        self::assertSame('ready', $ready['status']);
        self::assertTrue($ready['ready']);
        self::assertFalse($ready['mutation_enabled']);
        self::assertFalse($ready['executed']);
        self::assertSame([], $ready['reasons']);

        $duplicate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_duplicate']],
            ['ok' => true, 'status' => 'duplicate', 'created' => false, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        self::assertSame('blocked', $duplicate['status']);
        self::assertFalse($duplicate['ready']);
        self::assertFalse($duplicate['mutation_enabled']);
        self::assertContains('duplicate_generated_submit', $duplicate['reasons']);
        self::assertNotContains('enablement_flag_disabled', $duplicate['reasons']);

        $auditSkipped = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'skipped', 'item' => [], 'reason' => 'missing_actor'],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        self::assertSame('blocked', $auditSkipped['status']);
        self::assertFalse($auditSkipped['ready']);
        self::assertFalse($auditSkipped['mutation_enabled']);
        self::assertContains('audit_append_not_appended', $auditSkipped['reasons']);
        self::assertNotContains('enablement_flag_disabled', $auditSkipped['reasons']);

        $auditFailed = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => false, 'status' => 'failed', 'item' => [], 'error' => 'audit down'],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        self::assertSame('failed', $auditFailed['status']);
        self::assertFalse($auditFailed['ready']);
        self::assertFalse($auditFailed['mutation_enabled']);
        self::assertContains('audit_append_failed', $auditFailed['reasons']);

        $idempotencySkipped = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_ready']],
            ['ok' => true, 'status' => 'skipped', 'created' => false, 'reason' => 'no_dedupe_key'],
        );
        self::assertSame('blocked', $idempotencySkipped['status']);
        self::assertFalse($idempotencySkipped['ready']);
        self::assertFalse($idempotencySkipped['mutation_enabled']);
        self::assertContains('idempotency_skipped', $idempotencySkipped['reasons']);
        self::assertNotContains('enablement_flag_disabled', $idempotencySkipped['reasons']);

        $idempotencyFailed = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_ready']],
            ['ok' => false, 'status' => 'failed', 'created' => false, 'error' => 'idempotency down'],
        );
        self::assertSame('failed', $idempotencyFailed['status']);
        self::assertFalse($idempotencyFailed['ready']);
        self::assertFalse($idempotencyFailed['mutation_enabled']);
        self::assertContains('idempotency_failed', $idempotencyFailed['reasons']);

        $invalid = app_lab_sample18_task_board_normalize_generated_submit_request(
            'update_task_card',
            ['id' => '0', 'title' => ''],
            '2026-07-10 04:00:00',
        );
        self::assertFalse($invalid['ok']);
        $invalidGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $invalid,
            app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($invalid),
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_invalid']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-invalid']],
        );
        self::assertSame('blocked', $invalidGate['status']);
        self::assertFalse($invalidGate['ready']);
        self::assertFalse($invalidGate['mutation_enabled']);
        self::assertContains('request_not_valid', $invalidGate['reasons']);

        $disabled = app_lab_sample18_task_board_generated_submit_mutation_gate(
            [],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_disabled']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        self::assertSame('disabled', $disabled['status']);
        self::assertContains('enablement_flag_disabled', $disabled['reasons']);
        self::assertFalse($disabled['mutation_enabled']);
    }

    public function testMiniTaskBoardGeneratedSubmitDbAccessExecutionPlanIsNonMutating(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Execution plan', 'body' => '', 'assigned_to' => '', 'priority' => '20', 'due_date' => ''],
            '2026-07-10 05:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $readyGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_ready']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        self::assertSame('ready', $readyGate['status']);

        $planned = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $readyGate,
        );
        self::assertSame('planned', $planned['status']);
        self::assertTrue($planned['ready']);
        self::assertFalse($planned['mutation_enabled']);
        self::assertFalse($planned['executed']);
        self::assertSame('create_task_card', $planned['operation_key']);
        self::assertSame('create', $planned['curated_route_action']);
        self::assertSame('TaskCardDBAccess', $planned['db_access_class']);
        self::assertSame('InsertTaskCard', $planned['db_access_function']);
        self::assertSame('TaskCardData', $planned['data_object']);
        self::assertSame($dispatcher['method_arguments'], $planned['method_arguments']);
        self::assertSame('not_opened', $planned['transaction']);
        self::assertSame([], $planned['reasons']);

        $duplicateGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_duplicate']],
            ['ok' => true, 'status' => 'duplicate', 'created' => false, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        $duplicatePlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $duplicateGate,
        );
        self::assertSame('blocked', $duplicatePlan['status']);
        self::assertFalse($duplicatePlan['ready']);
        self::assertFalse($duplicatePlan['mutation_enabled']);
        self::assertFalse($duplicatePlan['executed']);
        self::assertSame('not_opened', $duplicatePlan['transaction']);
        self::assertContains('mutation_gate_not_ready', $duplicatePlan['reasons']);
        self::assertContains('duplicate_generated_submit', $duplicatePlan['reasons']);

        $failedGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => false, 'status' => 'failed', 'item' => [], 'error' => 'audit down'],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        $failedPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $failedGate,
        );
        self::assertSame('failed', $failedPlan['status']);
        self::assertFalse($failedPlan['ready']);
        self::assertFalse($failedPlan['mutation_enabled']);
        self::assertFalse($failedPlan['executed']);
        self::assertSame('not_opened', $failedPlan['transaction']);
        self::assertContains('audit_append_failed', $failedPlan['reasons']);

        $nonDryRunDispatcher = $dispatcher;
        $nonDryRunDispatcher['executed'] = true;
        $nonDryRunPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $nonDryRunDispatcher,
            $readyGate,
        );
        self::assertSame('failed', $nonDryRunPlan['status']);
        self::assertContains('dispatcher_not_dry_run', $nonDryRunPlan['reasons']);
        self::assertFalse($nonDryRunPlan['executed']);
        self::assertSame('not_opened', $nonDryRunPlan['transaction']);

        $invalid = app_lab_sample18_task_board_normalize_generated_submit_request(
            'update_task_card',
            ['id' => '0', 'title' => ''],
            '2026-07-10 05:00:00',
        );
        self::assertFalse($invalid['ok']);
        $invalidPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $invalid,
            app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($invalid),
            [
                'status' => 'blocked',
                'ready' => false,
                'mutation_enabled' => false,
                'executed' => false,
                'reasons' => ['request_not_valid'],
            ],
        );
        self::assertSame('failed', $invalidPlan['status']);
        self::assertFalse($invalidPlan['ready']);
        self::assertFalse($invalidPlan['mutation_enabled']);
        self::assertFalse($invalidPlan['executed']);
        self::assertSame('not_opened', $invalidPlan['transaction']);
        self::assertContains('request_not_valid', $invalidPlan['reasons']);
        self::assertContains('dispatcher_not_ready', $invalidPlan['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitTransactionPlanIsNonMutating(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Transaction plan', 'body' => '', 'assigned_to' => '', 'priority' => '30', 'due_date' => ''],
            '2026-07-10 06:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $readyGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit_ready']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => ['dedupe_key' => 'dedupe-ready']],
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $readyGate,
        );
        self::assertSame('planned', $executionPlan['status']);

        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        self::assertSame('planned', $transactionPlan['status']);
        self::assertTrue($transactionPlan['ready']);
        self::assertSame('planned_not_opened', $transactionPlan['transaction']);
        self::assertSame('sample18_application_db', $transactionPlan['db_handle']);
        self::assertSame('config_db_audit_log', $transactionPlan['audit_store']);
        self::assertSame('config_db_idempotency', $transactionPlan['idempotency_store']);
        self::assertFalse($transactionPlan['will_execute']);
        self::assertFalse($transactionPlan['will_update_audit']);
        self::assertFalse($transactionPlan['will_update_idempotency']);
        self::assertSame('rollback', $transactionPlan['rollback_policy']['on_dbaccess_exception'] ?? '');
        self::assertSame('rollback', $transactionPlan['rollback_policy']['on_unexpected_result'] ?? '');
        self::assertSame('rollback', $transactionPlan['rollback_policy']['on_post_execution_update_failure'] ?? '');
        self::assertSame('planned_not_written', $transactionPlan['post_execution_audit_update']['status'] ?? '');
        self::assertSame('sample18.generated_submit.executed', $transactionPlan['post_execution_audit_update']['event_type'] ?? '');
        self::assertSame('TaskCardDBAccess', $transactionPlan['post_execution_audit_update']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $transactionPlan['post_execution_audit_update']['db_access_function'] ?? '');
        self::assertSame('planned_not_written', $transactionPlan['post_execution_idempotency_update']['status'] ?? '');
        self::assertSame('planned', $transactionPlan['post_execution_idempotency_update']['execution_status'] ?? '');
        self::assertSame([], $transactionPlan['reasons']);

        $blockedExecutionPlan = $executionPlan;
        $blockedExecutionPlan['status'] = 'blocked';
        $blockedExecutionPlan['ready'] = false;
        $blockedExecutionPlan['reasons'] = ['duplicate_generated_submit'];
        $blockedTransactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($blockedExecutionPlan);
        self::assertSame('blocked', $blockedTransactionPlan['status']);
        self::assertFalse($blockedTransactionPlan['ready']);
        self::assertSame('not_opened', $blockedTransactionPlan['transaction']);
        self::assertFalse($blockedTransactionPlan['will_execute']);
        self::assertContains('execution_plan_not_ready', $blockedTransactionPlan['reasons']);
        self::assertContains('duplicate_generated_submit', $blockedTransactionPlan['reasons']);

        $failedExecutionPlan = $executionPlan;
        $failedExecutionPlan['status'] = 'failed';
        $failedExecutionPlan['ready'] = false;
        $failedExecutionPlan['reasons'] = ['dispatcher_not_dry_run'];
        $failedTransactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($failedExecutionPlan);
        self::assertSame('failed', $failedTransactionPlan['status']);
        self::assertFalse($failedTransactionPlan['ready']);
        self::assertSame('not_opened', $failedTransactionPlan['transaction']);
        self::assertFalse($failedTransactionPlan['will_execute']);
        self::assertContains('dispatcher_not_dry_run', $failedTransactionPlan['reasons']);

        $unsafeExecutionPlan = $executionPlan;
        $unsafeExecutionPlan['executed'] = true;
        $unsafeTransactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($unsafeExecutionPlan);
        self::assertSame('failed', $unsafeTransactionPlan['status']);
        self::assertFalse($unsafeTransactionPlan['ready']);
        self::assertSame('not_opened', $unsafeTransactionPlan['transaction']);
        self::assertFalse($unsafeTransactionPlan['will_execute']);
        self::assertContains('execution_plan_not_metadata_only', $unsafeTransactionPlan['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitExecutionUpdatePlanIsNonMutating(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Execution update plan', 'body' => '', 'assigned_to' => '', 'priority' => '40', 'due_date' => ''],
            '2026-07-10 07:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $readyGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-request-1']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'dedupe_key' => 'dedupe-update-1', 'item' => ['dedupe_key' => 'dedupe-update-1']],
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $readyGate,
        );
        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        self::assertSame('planned', $transactionPlan['status']);

        $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-request-1']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'dedupe_key' => 'dedupe-update-1', 'item' => ['dedupe_key' => 'dedupe-update-1']],
        );
        self::assertSame('planned', $updatePlan['status']);
        self::assertTrue($updatePlan['ready']);
        self::assertFalse($updatePlan['will_write_audit']);
        self::assertFalse($updatePlan['will_update_idempotency']);
        self::assertFalse($updatePlan['will_execute']);
        self::assertSame('config_db_audit_log', $updatePlan['audit_store']);
        self::assertSame('config_db_idempotency', $updatePlan['idempotency_store']);
        self::assertSame('planned_not_written', $updatePlan['execution_audit_update']['status'] ?? '');
        self::assertSame('sample18.generated_submit.executed', $updatePlan['execution_audit_update']['event_type'] ?? '');
        self::assertSame('dedupe-update-1', $updatePlan['execution_audit_update']['target_key'] ?? '');
        self::assertSame('audit-request-1', $updatePlan['execution_audit_update']['request_audit_event_key'] ?? '');
        self::assertSame('executed', $updatePlan['execution_audit_update']['result'] ?? '');
        self::assertSame('planned_not_opened', $updatePlan['execution_audit_update']['transaction_status'] ?? '');
        self::assertSame('dedupe-update-1', $updatePlan['execution_audit_update']['metadata']['dedupe_key'] ?? '');
        self::assertSame('TaskCardDBAccess', $updatePlan['execution_audit_update']['metadata']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $updatePlan['execution_audit_update']['metadata']['db_access_function'] ?? '');
        self::assertSame('planned_not_written', $updatePlan['idempotency_execution_update']['status'] ?? '');
        self::assertSame('dedupe-update-1', $updatePlan['idempotency_execution_update']['dedupe_key'] ?? '');
        self::assertSame('planned', $updatePlan['idempotency_execution_update']['execution_status'] ?? '');
        self::assertSame('planned_not_executed', $updatePlan['idempotency_execution_update']['execution_result_code'] ?? '');
        self::assertSame('planned_not_opened', $updatePlan['idempotency_execution_update']['transaction_status'] ?? '');
        self::assertSame([], $updatePlan['reasons']);

        $blockedTransactionPlan = $transactionPlan;
        $blockedTransactionPlan['status'] = 'blocked';
        $blockedTransactionPlan['ready'] = false;
        $blockedTransactionPlan['reasons'] = ['duplicate_generated_submit'];
        $blockedUpdatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $blockedTransactionPlan,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-request-1']],
            ['ok' => true, 'status' => 'duplicate', 'created' => false, 'dedupe_key' => 'dedupe-update-1'],
        );
        self::assertSame('blocked', $blockedUpdatePlan['status']);
        self::assertFalse($blockedUpdatePlan['ready']);
        self::assertFalse($blockedUpdatePlan['will_write_audit']);
        self::assertFalse($blockedUpdatePlan['will_update_idempotency']);
        self::assertContains('transaction_plan_not_ready', $blockedUpdatePlan['reasons']);
        self::assertContains('duplicate_generated_submit', $blockedUpdatePlan['reasons']);

        $unsafeTransactionPlan = $transactionPlan;
        $unsafeTransactionPlan['will_execute'] = true;
        $unsafeUpdatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $unsafeTransactionPlan,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-request-1']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'dedupe_key' => 'dedupe-update-1'],
        );
        self::assertSame('failed', $unsafeUpdatePlan['status']);
        self::assertFalse($unsafeUpdatePlan['ready']);
        self::assertFalse($unsafeUpdatePlan['will_execute']);
        self::assertContains('transaction_plan_not_metadata_only', $unsafeUpdatePlan['reasons']);

        $missingDedupePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-request-1']],
            ['ok' => true, 'status' => 'recorded', 'created' => true, 'item' => []],
        );
        self::assertSame('blocked', $missingDedupePlan['status']);
        self::assertContains('dedupe_key_missing', $missingDedupePlan['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitExecutionGuardIsNonMutating(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Execution guard', 'body' => '', 'assigned_to' => '', 'priority' => '50', 'due_date' => ''],
            '2026-07-10 08:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-guard-1']];
        $idempotency = ['ok' => true, 'status' => 'recorded', 'created' => true, 'dedupe_key' => 'dedupe-guard-1', 'item' => ['dedupe_key' => 'dedupe-guard-1']];
        $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            $auditAppend,
            $idempotency,
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $mutationGate,
        );
        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            $auditAppend,
            $idempotency,
        );

        $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $updatePlan,
        );
        self::assertSame('allowed', $guard['status']);
        self::assertTrue($guard['ready']);
        self::assertFalse($guard['will_open_transaction']);
        self::assertFalse($guard['will_call_dbaccess']);
        self::assertFalse($guard['will_write_execution_audit']);
        self::assertFalse($guard['will_update_idempotency_execution']);
        self::assertSame('sample18_application_db', $guard['db_handle']);
        self::assertSame('TaskCardDBAccess', $guard['db_access_class']);
        self::assertSame('InsertTaskCard', $guard['db_access_function']);
        self::assertSame('create_task_card', $guard['operation_key']);
        self::assertSame('dedupe-guard-1', $guard['dedupe_key']);
        self::assertSame('audit-guard-1', $guard['request_audit_event_key']);
        self::assertSame([], $guard['reasons']);

        $duplicateGuard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            ['ok' => true, 'status' => 'duplicate', 'created' => false, 'dedupe_key' => 'dedupe-guard-1'],
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $updatePlan,
        );
        self::assertSame('blocked', $duplicateGuard['status']);
        self::assertContains('idempotency_not_ready', $duplicateGuard['reasons']);
        self::assertContains('duplicate_generated_submit', $duplicateGuard['reasons']);

        $unsafeUpdatePlan = $updatePlan;
        $unsafeUpdatePlan['will_write_audit'] = true;
        $unsafeGuard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $unsafeUpdatePlan,
        );
        self::assertSame('failed', $unsafeGuard['status']);
        self::assertContains('execution_metadata_not_metadata_only', $unsafeGuard['reasons']);

        $wrongDbPlan = $transactionPlan;
        $wrongDbPlan['db_handle'] = 'config_db_audit_log';
        $wrongDbGuard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $wrongDbPlan,
            $updatePlan,
        );
        self::assertSame('failed', $wrongDbGuard['status']);
        self::assertContains('db_handle_not_allowlisted', $wrongDbGuard['reasons']);

        $missingLinkPlan = $updatePlan;
        $missingLinkPlan['execution_audit_update']['request_audit_event_key'] = '';
        $missingLinkGuard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $missingLinkPlan,
        );
        self::assertSame('blocked', $missingLinkGuard['status']);
        self::assertContains('request_audit_event_key_missing', $missingLinkGuard['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitExecutorCoordinationPlanIsNonMutating(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Coordinator plan', 'body' => '', 'assigned_to' => '', 'priority' => '70', 'due_date' => ''],
            '2026-07-10 10:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-coordinator-1']];
        $idempotency = ['ok' => true, 'status' => 'recorded', 'created' => true, 'dedupe_key' => 'dedupe-coordinator-1', 'item' => ['dedupe_key' => 'dedupe-coordinator-1']];
        $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            $auditAppend,
            $idempotency,
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $mutationGate,
        );
        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            $auditAppend,
            $idempotency,
        );
        $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $updatePlan,
        );
        self::assertSame('allowed', $guard['status']);

        $plan = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
            $guard,
            $updatePlan,
            true,
        );
        self::assertSame('planned', $plan['status']);
        self::assertTrue($plan['ready']);
        self::assertFalse($plan['will_open_transaction']);
        self::assertFalse($plan['will_call_dbaccess']);
        self::assertFalse($plan['will_write_execution_audit']);
        self::assertFalse($plan['will_update_idempotency_execution']);
        self::assertSame('sample18_application_db', $plan['app_db_transaction_boundary']['db_handle'] ?? '');
        self::assertSame('sample18_application_db_only', $plan['app_db_transaction_boundary']['transaction_scope'] ?? '');
        self::assertFalse($plan['app_db_transaction_boundary']['cross_store_atomic'] ?? true);
        self::assertSame('config_db_audit_log', $plan['config_db_persistence_boundary']['audit_store'] ?? '');
        self::assertSame('config_db_idempotency', $plan['config_db_persistence_boundary']['idempotency_store'] ?? '');
        self::assertFalse($plan['config_db_persistence_boundary']['cross_store_atomic'] ?? true);
        self::assertSame('recheck_execution_guard', $plan['ordered_steps'][0]['step'] ?? '');
        self::assertSame('open_app_db_transaction', $plan['ordered_steps'][1]['step'] ?? '');
        self::assertSame('call_dbaccess', $plan['ordered_steps'][2]['step'] ?? '');
        self::assertSame('append_execution_audit', $plan['ordered_steps'][5]['step'] ?? '');
        self::assertSame('update_idempotency_execution_outcome', $plan['ordered_steps'][6]['step'] ?? '');
        self::assertSame('create_task_card', $plan['operation_key']);
        self::assertSame('dedupe-coordinator-1', $plan['dedupe_key']);
        self::assertSame('audit-coordinator-1', $plan['request_audit_event_key']);
        self::assertSame([], $plan['reasons']);

        $disabled = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
            $guard,
            $updatePlan,
            false,
        );
        self::assertSame('blocked', $disabled['status']);
        self::assertFalse($disabled['ready']);
        self::assertContains('executor_feature_flag_disabled', $disabled['reasons']);

        $unsafeGuard = $guard;
        $unsafeGuard['will_call_dbaccess'] = true;
        $unsafe = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
            $unsafeGuard,
            $updatePlan,
            true,
        );
        self::assertSame('failed', $unsafe['status']);
        self::assertFalse($unsafe['ready']);
        self::assertContains('coordination_metadata_not_dry_run', $unsafe['reasons']);

        $missingLinkGuard = $guard;
        $missingLinkGuard['request_audit_event_key'] = '';
        $missingLink = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
            $missingLinkGuard,
            $updatePlan,
            true,
        );
        self::assertSame('blocked', $missingLink['status']);
        self::assertContains('request_audit_event_key_missing', $missingLink['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitDbAccessCallAdapterUsesInjectedInvokerOnly(): void
    {
        $buildReady = static function (string $operationKey, array $input): array {
            $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
                $operationKey,
                $input,
                '2026-07-10 11:00:00',
            );
            self::assertTrue($normalized['ok']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
            $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-adapter-' . $operationKey]];
            $idempotency = [
                'ok' => true,
                'status' => 'recorded',
                'created' => true,
                'dedupe_key' => 'dedupe-adapter-' . $operationKey,
                'item' => ['dedupe_key' => 'dedupe-adapter-' . $operationKey],
            ];
            $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
                ['sample18_generated_submit_mutation_enabled' => true],
                $normalized,
                $dispatcher,
                $auditAppend,
                $idempotency,
            );
            $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
                $normalized,
                $dispatcher,
                $mutationGate,
            );
            $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
            $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
                $transactionPlan,
                $auditAppend,
                $idempotency,
            );
            $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
                $normalized,
                $auditAppend,
                $idempotency,
                $mutationGate,
                $executionPlan,
                $transactionPlan,
                $updatePlan,
            );
            $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
                $guard,
                $updatePlan,
                true,
            );

            return [$normalized, $dispatcher, $guard, $coordination];
        };

        $cases = [
            'create_task_card' => [
                ['title' => 'Adapter create', 'body' => '', 'assigned_to' => '', 'priority' => '10', 'due_date' => ''],
                'InsertTaskCard',
            ],
            'update_task_card' => [
                ['id' => '7', 'title' => 'Adapter update', 'body' => '', 'status' => 'doing', 'assigned_to' => '', 'priority' => '20', 'due_date' => ''],
                'UpdateTaskCard',
            ],
            'complete_task_card' => [
                ['id' => '7'],
                'CompleteTaskCard',
            ],
        ];

        foreach ($cases as $operationKey => [$input, $expectedFunction]) {
            [$normalized, $dispatcher, $guard, $coordination] = $buildReady($operationKey, $input);
            $calls = 0;
            $adapter = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
                $normalized,
                $dispatcher,
                $guard,
                $coordination,
                true,
                static function (array $call) use (&$calls, $operationKey, $expectedFunction): array {
                    $calls++;
                    self::assertSame($operationKey, $call['operation_key'] ?? '');
                    self::assertSame('TaskCardDBAccess', $call['db_access_class'] ?? '');
                    self::assertSame($expectedFunction, $call['db_access_function'] ?? '');
                    self::assertSame('TaskCardData', $call['data_object'] ?? '');
                    self::assertIsArray($call['method_arguments']['TaskCardObj'] ?? null);

                    return ['ok' => true, 'result_code' => 'dbaccess_executed', 'rows_affected' => 1, 'insert_id' => 42];
                },
            );
            self::assertSame(1, $calls);
            self::assertSame('executed', $adapter['status']);
            self::assertTrue($adapter['executed']);
            self::assertTrue($adapter['invoked']);
            self::assertSame($operationKey, $adapter['operation_key']);
            self::assertSame($expectedFunction, $adapter['db_access_function']);
            self::assertSame('dbaccess_executed', $adapter['result_code']);
            self::assertSame(1, $adapter['rows_affected']);
            self::assertSame(42, $adapter['insert_id']);
            self::assertSame('dedupe-adapter-' . $operationKey, $adapter['dedupe_key']);
            self::assertSame('audit-adapter-' . $operationKey, $adapter['request_audit_event_key']);
            self::assertSame([], $adapter['reasons']);
        }

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady(
            'create_task_card',
            ['title' => 'Adapter blocked', 'body' => '', 'assigned_to' => '', 'priority' => '10', 'due_date' => ''],
        );
        $calls = 0;
        $disabled = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            false,
            static function () use (&$calls): array {
                $calls++;

                return ['ok' => true];
            },
        );
        self::assertSame(0, $calls);
        self::assertSame('skipped', $disabled['status']);
        self::assertFalse($disabled['executed']);
        self::assertFalse($disabled['invoked']);
        self::assertContains('executor_feature_flag_disabled', $disabled['reasons']);

        $blockedGuard = $guard;
        $blockedGuard['status'] = 'blocked';
        $blockedGuard['ready'] = false;
        $blockedGuard['reasons'] = ['duplicate_generated_submit'];
        $blocked = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $dispatcher,
            $blockedGuard,
            $coordination,
            true,
            static function () use (&$calls): array {
                $calls++;

                return ['ok' => true];
            },
        );
        self::assertSame(0, $calls);
        self::assertSame('skipped', $blocked['status']);
        self::assertContains('execution_guard_not_ready', $blocked['reasons']);
        self::assertContains('duplicate_generated_submit', $blocked['reasons']);

        $blockedCoordination = $coordination;
        $blockedCoordination['status'] = 'blocked';
        $blockedCoordination['ready'] = false;
        $blockedCoordination['reasons'] = ['executor_feature_flag_disabled'];
        $blockedPlan = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $blockedCoordination,
            true,
            static function () use (&$calls): array {
                $calls++;

                return ['ok' => true];
            },
        );
        self::assertSame(0, $calls);
        self::assertSame('skipped', $blockedPlan['status']);
        self::assertContains('executor_coordination_plan_not_ready', $blockedPlan['reasons']);

        $wrongFunctionDispatcher = $dispatcher;
        $wrongFunctionDispatcher['db_access_function'] = 'DeleteTaskCard';
        $wrongFunction = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $wrongFunctionDispatcher,
            $guard,
            $coordination,
            true,
            static function () use (&$calls): array {
                $calls++;

                return ['ok' => true];
            },
        );
        self::assertSame(0, $calls);
        self::assertSame('skipped', $wrongFunction['status']);
        self::assertContains('dbaccess_not_allowlisted', $wrongFunction['reasons']);

        $missingPayloadDispatcher = $dispatcher;
        $missingPayloadDispatcher['method_arguments'] = [];
        $missingPayload = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $missingPayloadDispatcher,
            $guard,
            $coordination,
            true,
            static function () use (&$calls): array {
                $calls++;

                return ['ok' => true];
            },
        );
        self::assertSame(0, $calls);
        self::assertSame('skipped', $missingPayload['status']);
        self::assertContains('task_card_payload_missing', $missingPayload['reasons']);

        $failed = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static fn (): array => ['ok' => false, 'failure_code' => 'dbaccess_failed', 'error' => 'duplicate key'],
        );
        self::assertSame('failed', $failed['status']);
        self::assertFalse($failed['executed']);
        self::assertTrue($failed['invoked']);
        self::assertSame('dbaccess_failed', $failed['failure_code']);
        self::assertSame('duplicate key', $failed['error']);

        $exception = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static function (): array {
                throw new RuntimeException('db offline');
            },
        );
        self::assertSame('failed', $exception['status']);
        self::assertFalse($exception['executed']);
        self::assertTrue($exception['invoked']);
        self::assertSame('dbaccess_exception', $exception['failure_code']);
        self::assertSame('db offline', $exception['error']);
    }

    public function testMiniTaskBoardGeneratedSubmitRealDbAccessInvocationAdapterBuildsTaskCardObject(): void
    {
        $buildReady = static function (string $operationKey, array $input): array {
            $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
                $operationKey,
                $input,
                '2026-07-10 12:30:00',
            );
            self::assertTrue($normalized['ok']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
            $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-real-' . $operationKey]];
            $idempotency = [
                'ok' => true,
                'status' => 'recorded',
                'created' => true,
                'dedupe_key' => 'dedupe-real-' . $operationKey,
                'item' => ['dedupe_key' => 'dedupe-real-' . $operationKey],
            ];
            $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
                ['sample18_generated_submit_mutation_enabled' => true],
                $normalized,
                $dispatcher,
                $auditAppend,
                $idempotency,
            );
            $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
                $normalized,
                $dispatcher,
                $mutationGate,
            );
            $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
            $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
                $transactionPlan,
                $auditAppend,
                $idempotency,
            );
            $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
                $normalized,
                $auditAppend,
                $idempotency,
                $mutationGate,
                $executionPlan,
                $transactionPlan,
                $updatePlan,
            );
            $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
                $guard,
                $updatePlan,
                true,
            );

            return [$normalized, $dispatcher, $guard, $coordination];
        };

        $dbAccess = new class {
            /** @var list<array{method:string,obj:object}> */
            public array $calls = [];

            public function InsertTaskCard(object $TaskCardObj): object
            {
                $this->calls[] = ['method' => 'InsertTaskCard', 'obj' => $TaskCardObj];

                return (object) ['affected_rows' => 1, 'insert_id' => 42];
            }

            public function UpdateTaskCard(object $TaskCardObj): array
            {
                $this->calls[] = ['method' => 'UpdateTaskCard', 'obj' => $TaskCardObj];

                return ['ok' => true, 'rows_affected' => 1];
            }

            public function CompleteTaskCard(object $TaskCardObj): bool
            {
                $this->calls[] = ['method' => 'CompleteTaskCard', 'obj' => $TaskCardObj];

                return true;
            }
        };

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady(
            'create_task_card',
            ['title' => 'Real adapter create', 'body' => 'Body', 'assigned_to' => 'Mina', 'priority' => '30', 'due_date' => '2026-07-31'],
        );
        $created = app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $dbAccess,
            ['in_transaction' => true],
        );
        self::assertSame('executed', $created['status']);
        self::assertTrue($created['executed']);
        self::assertSame('dbaccess_executed', $created['result_code']);
        self::assertSame(1, $created['rows_affected']);
        self::assertSame(42, $created['insert_id']);
        self::assertSame('InsertTaskCard', $dbAccess->calls[0]['method']);
        self::assertSame('Real adapter create', $dbAccess->calls[0]['obj']->title);
        self::assertSame('Body', $dbAccess->calls[0]['obj']->body);
        self::assertSame('todo', $dbAccess->calls[0]['obj']->status);
        self::assertSame('Mina', $dbAccess->calls[0]['obj']->assignedTo);
        self::assertSame(30, $dbAccess->calls[0]['obj']->priority);
        self::assertSame('2026-07-31', $dbAccess->calls[0]['obj']->dueDate);
        self::assertSame('2026-07-10 12:30:00', $dbAccess->calls[0]['obj']->updatedAt);
        self::assertObjectNotHasProperty('Title', $dbAccess->calls[0]['obj']);

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady(
            'update_task_card',
            ['id' => '7', 'title' => 'Real adapter update', 'status' => 'done', 'body' => '', 'assigned_to' => '', 'priority' => '20', 'due_date' => ''],
        );
        $updated = app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $dbAccess,
            ['in_transaction' => true],
        );
        self::assertSame('executed', $updated['status']);
        self::assertSame('UpdateTaskCard', $dbAccess->calls[1]['method']);
        self::assertSame(7, $dbAccess->calls[1]['obj']->id);
        self::assertSame('Real adapter update', $dbAccess->calls[1]['obj']->title);
        self::assertSame('done', $dbAccess->calls[1]['obj']->status);
        self::assertSame('2026-07-10 12:30:00', $dbAccess->calls[1]['obj']->completedAt);

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady('complete_task_card', ['id' => '8']);
        $completed = app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $dbAccess,
            ['in_transaction' => true],
        );
        self::assertSame('executed', $completed['status']);
        self::assertSame('CompleteTaskCard', $dbAccess->calls[2]['method']);
        self::assertSame(8, $dbAccess->calls[2]['obj']->id);
        self::assertSame('done', $dbAccess->calls[2]['obj']->status);
        self::assertSame('2026-07-10 12:30:00', $dbAccess->calls[2]['obj']->completedAt);

        $missingTransaction = app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $dbAccess,
            ['in_transaction' => false],
        );
        self::assertSame('skipped', $missingTransaction['status']);
        self::assertFalse($missingTransaction['invoked']);
        self::assertSame('dbaccess_transaction_not_active', $missingTransaction['failure_code']);
        self::assertContains('dbaccess_transaction_not_active', $missingTransaction['reasons']);
        self::assertCount(3, $dbAccess->calls);

        $missingMethod = app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            new stdClass(),
            ['in_transaction' => true],
        );
        self::assertSame('failed', $missingMethod['status']);
        self::assertSame('dbaccess_method_missing', $missingMethod['failure_code']);

        $failingDbAccess = new class {
            public function CompleteTaskCard(object $TaskCardObj): object
            {
                return (object) ['errno' => 1062, 'error' => 'duplicate key'];
            }
        };
        $failed = app_lab_sample18_task_board_generated_submit_real_dbaccess_invocation_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $failingDbAccess,
            ['in_transaction' => true],
        );
        self::assertSame('failed', $failed['status']);
        self::assertSame('dbaccess_failed', $failed['failure_code']);
        self::assertSame('duplicate key', $failed['error']);
    }

    public function testMiniTaskBoardGeneratedSubmitTransactionBindingCallablesUseTransactionRuntime(): void
    {
        $buildReady = static function (): array {
            $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
                'create_task_card',
                ['title' => 'Transaction binding', 'body' => '', 'assigned_to' => 'Mina', 'priority' => '10', 'due_date' => ''],
                '2026-07-10 13:00:00',
            );
            self::assertTrue($normalized['ok']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
            $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-binding-1']];
            $idempotency = [
                'ok' => true,
                'status' => 'recorded',
                'created' => true,
                'dedupe_key' => 'dedupe-binding-1',
                'item' => ['dedupe_key' => 'dedupe-binding-1'],
            ];
            $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
                ['sample18_generated_submit_mutation_enabled' => true],
                $normalized,
                $dispatcher,
                $auditAppend,
                $idempotency,
            );
            $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
                $normalized,
                $dispatcher,
                $mutationGate,
            );
            $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
            $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
                $transactionPlan,
                $auditAppend,
                $idempotency,
            );
            $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
                $normalized,
                $auditAppend,
                $idempotency,
                $mutationGate,
                $executionPlan,
                $transactionPlan,
                $updatePlan,
            );
            $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
                $guard,
                $updatePlan,
                true,
            );

            return [$normalized, $dispatcher, $guard, $coordination];
        };

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady();
        $transactionDb = new class {
            public array $events = [];
            public bool $active = false;
            public string $error = '';

            public function beginTransaction(): bool
            {
                $this->events[] = 'begin';
                $this->active = true;

                return true;
            }

            public function commit(): bool
            {
                $this->events[] = 'commit';
                $this->active = false;

                return true;
            }

            public function rollBack(): bool
            {
                $this->events[] = 'rollback';
                $this->active = false;

                return true;
            }

            public function inTransaction(): bool
            {
                return $this->active;
            }
        };
        $factoryContexts = [];
        $dbAccessCalls = [];
        $callables = app_lab_sample18_task_board_generated_submit_transaction_binding_callables(
            $transactionDb,
            static function (array $context) use (&$factoryContexts, &$dbAccessCalls): object {
                $factoryContexts[] = $context;

                return new class($dbAccessCalls) {
                    private array $calls;

                    public function __construct(array &$calls)
                    {
                        $this->calls =& $calls;
                    }

                    public function InsertTaskCard(object $TaskCardObj): object
                    {
                        $this->calls[] = [
                            'method' => 'InsertTaskCard',
                            'title' => $TaskCardObj->title,
                            'assignedTo' => $TaskCardObj->assignedTo,
                        ];

                        return (object) ['affected_rows' => 1, 'insert_id' => 77];
                    }
                };
            },
        );
        $success = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $callables['begin'],
            $callables['commit'],
            $callables['rollback'],
            $callables['dbaccess'],
        );
        self::assertSame('executed', $success['status']);
        self::assertSame('committed', $success['transaction_status']);
        self::assertSame(['begin', 'commit'], $transactionDb->events);
        self::assertCount(1, $factoryContexts);
        self::assertTrue($factoryContexts[0]['in_transaction']);
        self::assertSame('TaskCardDBAccess', $factoryContexts[0]['call']['db_access_class']);
        self::assertSame('InsertTaskCard', $factoryContexts[0]['call']['db_access_function']);
        self::assertSame([['method' => 'InsertTaskCard', 'title' => 'Transaction binding', 'assignedTo' => 'Mina']], $dbAccessCalls);
        self::assertSame(77, $success['dbaccess_result']['insert_id']);

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady();
        $failingTransactionDb = new class {
            public array $events = [];
            public bool $active = false;
            public string $error = '';

            public function beginTransaction(): bool
            {
                $this->events[] = 'begin';
                $this->active = true;

                return true;
            }

            public function commit(): bool
            {
                $this->events[] = 'commit';
                $this->active = false;

                return true;
            }

            public function rollBack(): bool
            {
                $this->events[] = 'rollback';
                $this->active = false;

                return true;
            }

            public function inTransaction(): bool
            {
                return $this->active;
            }
        };
        $failingCallables = app_lab_sample18_task_board_generated_submit_transaction_binding_callables(
            $failingTransactionDb,
            static fn (): object => new class {
                public function InsertTaskCard(object $TaskCardObj): object
                {
                    return (object) ['errno' => 1062, 'error' => 'duplicate key'];
                }
            },
        );
        $failed = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            $failingCallables['begin'],
            $failingCallables['commit'],
            $failingCallables['rollback'],
            $failingCallables['dbaccess'],
        );
        self::assertSame('failed', $failed['status']);
        self::assertSame('rolled_back', $failed['transaction_status']);
        self::assertSame('dbaccess_failed', $failed['failure_code']);
        self::assertSame(['begin', 'rollback'], $failingTransactionDb->events);

        $wrongTargetCoordination = $coordination;
        $wrongTargetCoordination['app_db_transaction_boundary']['db_handle'] = 'config_db';
        $blocked = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $wrongTargetCoordination,
            true,
            $failingCallables['begin'],
            $failingCallables['commit'],
            $failingCallables['rollback'],
            $failingCallables['dbaccess'],
        );
        self::assertSame('failed', $blocked['status']);
        self::assertSame('begin_failed', $blocked['transaction_status']);
        self::assertSame('transaction_target_not_allowlisted', $blocked['failure_code']);
        self::assertSame(['begin', 'rollback'], $failingTransactionDb->events);
    }

    public function testMiniTaskBoardGeneratedSubmitTransactionAdapterUsesFakeBoundariesOnly(): void
    {
        $buildReady = static function (): array {
            $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
                'create_task_card',
                ['title' => 'Transaction adapter', 'body' => '', 'assigned_to' => '', 'priority' => '10', 'due_date' => ''],
                '2026-07-10 12:00:00',
            );
            self::assertTrue($normalized['ok']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
            $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-transaction-1']];
            $idempotency = [
                'ok' => true,
                'status' => 'recorded',
                'created' => true,
                'dedupe_key' => 'dedupe-transaction-1',
                'item' => ['dedupe_key' => 'dedupe-transaction-1'],
            ];
            $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
                ['sample18_generated_submit_mutation_enabled' => true],
                $normalized,
                $dispatcher,
                $auditAppend,
                $idempotency,
            );
            $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
                $normalized,
                $dispatcher,
                $mutationGate,
            );
            $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
            $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
                $transactionPlan,
                $auditAppend,
                $idempotency,
            );
            $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
                $normalized,
                $auditAppend,
                $idempotency,
                $mutationGate,
                $executionPlan,
                $transactionPlan,
                $updatePlan,
            );
            $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
                $guard,
                $updatePlan,
                true,
            );

            return [$normalized, $dispatcher, $guard, $coordination];
        };

        [$normalized, $dispatcher, $guard, $coordination] = $buildReady();
        $events = [];
        $success = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static function (array $context) use (&$events): array {
                $events[] = 'begin:' . ($context['transaction_scope'] ?? '');

                return ['ok' => true];
            },
            static function (array $context) use (&$events): array {
                $events[] = 'commit:' . (($context['dbaccess_result']['status'] ?? 'missing'));

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function (array $call) use (&$events): array {
                $events[] = 'dbaccess:' . ($call['db_access_function'] ?? '');

                return ['ok' => true, 'result_code' => 'dbaccess_executed', 'rows_affected' => 1];
            },
        );
        self::assertSame(['begin:sample18_application_db_only', 'dbaccess:InsertTaskCard', 'commit:executed'], $events);
        self::assertSame('executed', $success['status']);
        self::assertTrue($success['success']);
        self::assertTrue($success['executed']);
        self::assertSame('committed', $success['transaction_status']);
        self::assertSame('executed', $success['dbaccess_status']);
        self::assertSame('planned_not_written', $success['recording_status']);
        self::assertFalse($success['rolled_back']);
        self::assertFalse($success['recovery_required']);
        self::assertSame([], $success['reasons']);

        $events = [];
        $beginFailed = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => false, 'failure_code' => 'transaction_begin_failed', 'error' => 'begin down'];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => true];
            },
        );
        self::assertSame(['begin'], $events);
        self::assertSame('failed', $beginFailed['status']);
        self::assertFalse($beginFailed['success']);
        self::assertSame('begin_failed', $beginFailed['transaction_status']);
        self::assertSame('not_called', $beginFailed['dbaccess_status']);
        self::assertSame('transaction_begin_failed', $beginFailed['failure_code']);

        $events = [];
        $dbaccessFailed = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function (array $context) use (&$events): array {
                $events[] = 'rollback:' . ($context['failure_code'] ?? '');

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => false, 'failure_code' => 'dbaccess_failed', 'error' => 'write failed'];
            },
        );
        self::assertSame(['begin', 'dbaccess', 'rollback:dbaccess_failed'], $events);
        self::assertSame('failed', $dbaccessFailed['status']);
        self::assertFalse($dbaccessFailed['success']);
        self::assertSame('rolled_back', $dbaccessFailed['transaction_status']);
        self::assertSame('failed', $dbaccessFailed['dbaccess_status']);
        self::assertTrue($dbaccessFailed['rolled_back']);
        self::assertSame('dbaccess_failed', $dbaccessFailed['failure_code']);

        $events = [];
        $rollbackFailed = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => false, 'failure_code' => 'transaction_rollback_failed', 'error' => 'rollback down'];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => false, 'failure_code' => 'dbaccess_failed'];
            },
        );
        self::assertSame(['begin', 'dbaccess', 'rollback'], $events);
        self::assertSame('failed', $rollbackFailed['status']);
        self::assertSame('rollback_failed', $rollbackFailed['transaction_status']);
        self::assertFalse($rollbackFailed['rolled_back']);
        self::assertSame('transaction_rollback_failed', $rollbackFailed['failure_code']);

        $events = [];
        $commitFailed = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => false, 'failure_code' => 'transaction_commit_failed', 'error' => 'commit down'];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => true, 'result_code' => 'dbaccess_executed'];
            },
        );
        self::assertSame(['begin', 'dbaccess', 'commit'], $events);
        self::assertSame('failed', $commitFailed['status']);
        self::assertFalse($commitFailed['success']);
        self::assertSame('commit_failed', $commitFailed['transaction_status']);
        self::assertSame('executed', $commitFailed['dbaccess_status']);
        self::assertTrue($commitFailed['recovery_required']);
        self::assertSame('commit_status_unknown', $commitFailed['recovery_reason']);
        self::assertSame('transaction_commit_failed', $commitFailed['failure_code']);

        $events = [];
        $blockedGuard = $guard;
        $blockedGuard['status'] = 'blocked';
        $blockedGuard['ready'] = false;
        $blockedGuard['reasons'] = ['duplicate_generated_submit'];
        $blocked = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $blockedGuard,
            $coordination,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => true];
            },
        );
        self::assertSame([], $events);
        self::assertSame('failed', $blocked['status']);
        self::assertFalse($blocked['success']);
        self::assertSame('not_started', $blocked['transaction_status']);
        self::assertSame('not_called', $blocked['dbaccess_status']);
        self::assertContains('execution_guard_not_ready', $blocked['reasons']);
        self::assertContains('duplicate_generated_submit', $blocked['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitPostCommitRecordingAdapterRequiresAllRecorders(): void
    {
        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Recording adapter', 'body' => '', 'assigned_to' => '', 'priority' => '10', 'due_date' => ''],
            '2026-07-10 13:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-recording-1']];
        $idempotency = [
            'ok' => true,
            'status' => 'recorded',
            'created' => true,
            'dedupe_key' => 'dedupe-recording-1',
            'item' => ['dedupe_key' => 'dedupe-recording-1'],
        ];
        $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            $auditAppend,
            $idempotency,
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $mutationGate,
        );
        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            $auditAppend,
            $idempotency,
        );
        $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $updatePlan,
        );
        $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
            $guard,
            $updatePlan,
            true,
        );
        $transaction = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static fn (): array => ['ok' => true],
            static fn (): array => ['ok' => true],
            static fn (): array => ['ok' => true],
            static fn (): array => ['ok' => true, 'result_code' => 'dbaccess_executed', 'rows_affected' => 1],
        );
        self::assertSame('executed', $transaction['status']);
        self::assertTrue($transaction['success']);

        $events = [];
        $recorded = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
            $transaction,
            $updatePlan,
            $guard,
            static function (array $context) use (&$events): array {
                $events[] = 'audit:' . ($context['dedupe_key'] ?? '');

                return ['ok' => true, 'event_key' => 'execution-audit-1'];
            },
            static function (array $context) use (&$events): array {
                $events[] = 'idempotency:' . (($context['execution_audit_result']['event_key'] ?? 'missing'));

                return ['ok' => true, 'execution_status' => 'executed'];
            },
        );
        self::assertSame(['audit:dedupe-recording-1', 'idempotency:execution-audit-1'], $events);
        self::assertSame('recorded', $recorded['status']);
        self::assertTrue($recorded['success']);
        self::assertSame('recorded', $recorded['recording_status']);
        self::assertSame('recorded', $recorded['execution_audit_status']);
        self::assertSame('recorded', $recorded['idempotency_update_status']);
        self::assertFalse($recorded['recovery_required']);
        self::assertSame('dedupe-recording-1', $recorded['dedupe_key']);
        self::assertSame('audit-recording-1', $recorded['request_audit_event_key']);

        $events = [];
        $auditFailed = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
            $transaction,
            $updatePlan,
            $guard,
            static function () use (&$events): array {
                $events[] = 'audit';

                return ['ok' => false, 'failure_code' => 'execution_audit_failed', 'error' => 'audit down'];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency';

                return ['ok' => true];
            },
        );
        self::assertSame(['audit'], $events);
        self::assertSame('failed', $auditFailed['status']);
        self::assertFalse($auditFailed['success']);
        self::assertSame('failed', $auditFailed['recording_status']);
        self::assertSame('failed', $auditFailed['execution_audit_status']);
        self::assertSame('not_started', $auditFailed['idempotency_update_status']);
        self::assertTrue($auditFailed['recovery_required']);
        self::assertSame('post_commit_recording_failed', $auditFailed['recovery_reason']);
        self::assertSame('execution_audit_failed', $auditFailed['failure_code']);

        $events = [];
        $idempotencyFailed = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
            $transaction,
            $updatePlan,
            $guard,
            static function () use (&$events): array {
                $events[] = 'audit';

                return ['ok' => true, 'event_key' => 'execution-audit-2'];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency';

                return ['ok' => false, 'failure_code' => 'idempotency_update_failed', 'error' => 'idempotency down'];
            },
        );
        self::assertSame(['audit', 'idempotency'], $events);
        self::assertSame('failed', $idempotencyFailed['status']);
        self::assertFalse($idempotencyFailed['success']);
        self::assertSame('failed', $idempotencyFailed['recording_status']);
        self::assertSame('recorded', $idempotencyFailed['execution_audit_status']);
        self::assertSame('failed', $idempotencyFailed['idempotency_update_status']);
        self::assertTrue($idempotencyFailed['recovery_required']);
        self::assertSame('post_commit_recording_failed', $idempotencyFailed['recovery_reason']);
        self::assertSame('idempotency_update_failed', $idempotencyFailed['failure_code']);

        $notCommitted = $transaction;
        $notCommitted['transaction_status'] = 'rolled_back';
        $skipped = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
            $notCommitted,
            $updatePlan,
            $guard,
            static function () use (&$events): array {
                $events[] = 'audit-unexpected';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency-unexpected';

                return ['ok' => true];
            },
        );
        self::assertSame(['audit', 'idempotency'], $events);
        self::assertSame('failed', $skipped['status']);
        self::assertFalse($skipped['success']);
        self::assertSame('skipped', $skipped['recording_status']);
        self::assertContains('transaction_not_committed', $skipped['reasons']);
    }

    public function testMiniTaskBoardGeneratedSubmitRouteExecutionPlanComposesFakeAdapters(): void
    {
        $buildReady = static function (): array {
            $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
                'create_task_card',
                ['title' => 'Route execution plan', 'body' => '', 'assigned_to' => '', 'priority' => '10', 'due_date' => ''],
                '2026-07-10 14:00:00',
            );
            self::assertTrue($normalized['ok']);
            $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
            $auditAppend = ['ok' => true, 'status' => 'appended', 'item' => ['event_key' => 'audit-route-exec-1']];
            $idempotency = [
                'ok' => true,
                'status' => 'recorded',
                'created' => true,
                'dedupe_key' => 'dedupe-route-exec-1',
                'item' => ['dedupe_key' => 'dedupe-route-exec-1'],
            ];
            $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
                ['sample18_generated_submit_mutation_enabled' => true],
                $normalized,
                $dispatcher,
                $auditAppend,
                $idempotency,
            );
            $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
                $normalized,
                $dispatcher,
                $mutationGate,
            );
            $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
            $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
                $transactionPlan,
                $auditAppend,
                $idempotency,
            );
            $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
                $normalized,
                $auditAppend,
                $idempotency,
                $mutationGate,
                $executionPlan,
                $transactionPlan,
                $updatePlan,
            );
            $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
                $guard,
                $updatePlan,
                true,
            );

            return [$normalized, $dispatcher, $guard, $coordination, $updatePlan];
        };

        [$normalized, $dispatcher, $guard, $coordination, $updatePlan] = $buildReady();
        $events = [];
        $blocked = app_lab_sample18_task_board_generated_submit_route_execution_plan(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            $updatePlan,
            false,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'audit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency';

                return ['ok' => true];
            },
        );
        self::assertSame([], $events);
        self::assertSame('blocked', $blocked['result']);
        self::assertFalse($blocked['success']);
        self::assertContains('executor_feature_flag_disabled', $blocked['reasons']);

        $events = [];
        $executed = app_lab_sample18_task_board_generated_submit_route_execution_plan(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            $updatePlan,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => true, 'result_code' => 'dbaccess_executed', 'rows_affected' => 1];
            },
            static function () use (&$events): array {
                $events[] = 'audit';

                return ['ok' => true, 'event_key' => 'execution-audit-route-1'];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency';

                return ['ok' => true, 'execution_status' => 'executed'];
            },
        );
        self::assertSame(['begin', 'dbaccess', 'commit', 'audit', 'idempotency'], $events);
        self::assertTrue($executed['ok']);
        self::assertTrue($executed['accepted']);
        self::assertSame('executed', $executed['result']);
        self::assertTrue($executed['success']);
        self::assertSame('executed', $executed['execution_status']);
        self::assertSame('committed', $executed['transaction_result']['transaction_status'] ?? '');
        self::assertSame('recorded', $executed['post_commit_recording']['recording_status'] ?? '');
        self::assertFalse($executed['recovery_required']);

        $events = [];
        $dbaccessFailed = app_lab_sample18_task_board_generated_submit_route_execution_plan(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            $updatePlan,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => false, 'failure_code' => 'dbaccess_failed'];
            },
            static function () use (&$events): array {
                $events[] = 'audit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency';

                return ['ok' => true];
            },
        );
        self::assertSame(['begin', 'dbaccess', 'rollback'], $events);
        self::assertSame('failed', $dbaccessFailed['result']);
        self::assertFalse($dbaccessFailed['success']);
        self::assertSame('failed', $dbaccessFailed['execution_status']);
        self::assertSame('rolled_back', $dbaccessFailed['transaction_result']['transaction_status'] ?? '');
        self::assertSame([], $dbaccessFailed['post_commit_recording']);
        self::assertSame('dbaccess_failed', $dbaccessFailed['failure_code']);

        $events = [];
        $recordingFailed = app_lab_sample18_task_board_generated_submit_route_execution_plan(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            $updatePlan,
            true,
            static function () use (&$events): array {
                $events[] = 'begin';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'commit';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'rollback';

                return ['ok' => true];
            },
            static function () use (&$events): array {
                $events[] = 'dbaccess';

                return ['ok' => true, 'result_code' => 'dbaccess_executed'];
            },
            static function () use (&$events): array {
                $events[] = 'audit';

                return ['ok' => true, 'event_key' => 'execution-audit-route-2'];
            },
            static function () use (&$events): array {
                $events[] = 'idempotency';

                return ['ok' => false, 'failure_code' => 'idempotency_update_failed'];
            },
        );
        self::assertSame(['begin', 'dbaccess', 'commit', 'audit', 'idempotency'], $events);
        self::assertSame('failed', $recordingFailed['result']);
        self::assertFalse($recordingFailed['success']);
        self::assertSame('failed', $recordingFailed['execution_status']);
        self::assertSame('committed', $recordingFailed['transaction_result']['transaction_status'] ?? '');
        self::assertSame('failed', $recordingFailed['post_commit_recording']['recording_status'] ?? '');
        self::assertTrue($recordingFailed['recovery_required']);
        self::assertSame('idempotency_update_failed', $recordingFailed['failure_code']);
    }

    public function testMiniTaskBoardGeneratedSubmitExecutionAuditAppendIsIndependent(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        $principal = [
            'id' => 'sample18-executor@example.test',
            'auth_source' => 'phpunit',
        ];

        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Execution audit', 'body' => '', 'assigned_to' => '', 'priority' => '60', 'due_date' => ''],
            '2026-07-10 09:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $preview = app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
            $normalized,
            $dispatcher,
            'blocked',
            'generated_submit_disabled',
        );
        $requestAudit = app_lab_sample18_task_board_generated_submit_audit_event_with_actor(
            is_array($preview['audit_event_preview'] ?? null) ? $preview['audit_event_preview'] : [],
            $principal,
        );
        $auditAppend = app_lab_sample18_task_board_generated_submit_append_audit_event($app, $requestAudit);
        self::assertSame('appended', $auditAppend['status']);
        $idempotency = app_lab_sample18_task_board_generated_submit_apply_idempotency(
            $app,
            $normalized,
            $dispatcher,
            $preview,
            $auditAppend,
        );
        self::assertSame('recorded', $idempotency['status']);

        $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            $auditAppend,
            $idempotency,
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $mutationGate,
        );
        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            $auditAppend,
            $idempotency,
        );
        $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $updatePlan,
        );
        self::assertSame('allowed', $guard['status']);

        $append = app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
            $app,
            $updatePlan,
            $guard,
            $principal,
            'executed',
            'task_card_inserted',
            'committed',
            ['affected_rows' => 1],
        );
        self::assertTrue($append['ok'], $append['error']);
        self::assertSame('appended', $append['status']);
        self::assertSame('sample18.generated_submit.executed', $append['item']['event_type'] ?? '');
        self::assertSame('executed', $append['item']['result'] ?? '');
        self::assertSame('task_card_inserted', $append['item']['message'] ?? '');
        self::assertSame($preview['dedupe_key_preview'] ?? '', $append['item']['target_key'] ?? '');
        self::assertSame($auditAppend['item']['event_key'] ?? '', $append['item']['metadata']['request_audit_event_key'] ?? '');
        self::assertSame($preview['dedupe_key_preview'] ?? '', $append['item']['metadata']['dedupe_key'] ?? '');
        self::assertSame('TaskCardDBAccess', $append['item']['metadata']['db_access_class'] ?? '');
        self::assertSame('InsertTaskCard', $append['item']['metadata']['db_access_function'] ?? '');
        self::assertSame('committed', $append['item']['metadata']['transaction_status'] ?? '');
        self::assertSame(1, $append['item']['metadata']['details']['affected_rows'] ?? 0);

        $latest = app_audit_log_fetch_latest($app, [
            'project_key' => 'SAMPLE18',
            'event_type' => 'sample18.generated_submit.executed',
            'target_key' => (string) ($preview['dedupe_key_preview'] ?? ''),
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);

        $idempotencyRecords = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'dedupe_key' => (string) ($preview['dedupe_key_preview'] ?? ''),
            'limit' => 10,
        ]);
        self::assertTrue($idempotencyRecords['ok'], $idempotencyRecords['error']);
        self::assertCount(1, $idempotencyRecords['items']);
        self::assertArrayNotHasKey('execution', $idempotencyRecords['items'][0]['metadata'] ?? []);
        self::assertSame('blocked', $idempotencyRecords['items'][0]['result'] ?? '');

        $missingLinkGuard = $guard;
        $missingLinkGuard['request_audit_event_key'] = '';
        $missingLink = app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
            $app,
            $updatePlan,
            $missingLinkGuard,
            $principal,
            'executed',
            'task_card_inserted',
            'committed',
        );
        self::assertFalse($missingLink['ok']);
        self::assertSame('request_audit_event_key_missing', $missingLink['reason']);

        $blockedGuard = $guard;
        $blockedGuard['status'] = 'blocked';
        $blockedGuard['ready'] = false;
        $blocked = app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
            $app,
            $updatePlan,
            $blockedGuard,
            $principal,
            'executed',
            'task_card_inserted',
            'committed',
        );
        self::assertFalse($blocked['ok']);
        self::assertSame('execution_guard_not_ready', $blocked['reason']);

        $invalid = app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
            $app,
            $updatePlan,
            $guard,
            $principal,
            'planned',
            'planned_not_executed',
            'not_opened',
        );
        self::assertFalse($invalid['ok']);
        self::assertSame('invalid_execution_status', $invalid['reason']);
    }

    public function testMiniTaskBoardGeneratedSubmitPostCommitRecordingAdapterPersistsDbBackedOutcome(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        $principal = [
            'id' => 'sample18-recorder@example.test',
            'auth_source' => 'phpunit',
        ];

        $normalized = app_lab_sample18_task_board_normalize_generated_submit_request(
            'create_task_card',
            ['title' => 'Post commit recording', 'body' => '', 'assigned_to' => '', 'priority' => '70', 'due_date' => ''],
            '2026-07-10 15:00:00',
        );
        self::assertTrue($normalized['ok']);
        $dispatcher = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
        $preview = app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
            $normalized,
            $dispatcher,
            'blocked',
            'generated_submit_disabled',
        );
        $requestAudit = app_lab_sample18_task_board_generated_submit_audit_event_with_actor(
            is_array($preview['audit_event_preview'] ?? null) ? $preview['audit_event_preview'] : [],
            $principal,
        );
        $auditAppend = app_lab_sample18_task_board_generated_submit_append_audit_event($app, $requestAudit);
        self::assertSame('appended', $auditAppend['status']);
        $idempotency = app_lab_sample18_task_board_generated_submit_apply_idempotency(
            $app,
            $normalized,
            $dispatcher,
            $preview,
            $auditAppend,
        );
        self::assertSame('recorded', $idempotency['status']);

        $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
            ['sample18_generated_submit_mutation_enabled' => true],
            $normalized,
            $dispatcher,
            $auditAppend,
            $idempotency,
        );
        $executionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
            $normalized,
            $dispatcher,
            $mutationGate,
        );
        $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($executionPlan);
        $updatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
            $transactionPlan,
            $auditAppend,
            $idempotency,
        );
        $guard = app_lab_sample18_task_board_generated_submit_execution_guard(
            $normalized,
            $auditAppend,
            $idempotency,
            $mutationGate,
            $executionPlan,
            $transactionPlan,
            $updatePlan,
        );
        $coordination = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
            $guard,
            $updatePlan,
            true,
        );
        self::assertSame('allowed', $guard['status']);
        self::assertSame('planned', $coordination['status']);

        $transaction = app_lab_sample18_task_board_generated_submit_transaction_adapter(
            $normalized,
            $dispatcher,
            $guard,
            $coordination,
            true,
            static fn (): array => ['ok' => true],
            static fn (): array => ['ok' => true],
            static fn (): array => ['ok' => true],
            static fn (): array => ['ok' => true, 'result_code' => 'task_card_inserted', 'rows_affected' => 1],
        );
        self::assertSame('executed', $transaction['status']);
        self::assertTrue($transaction['success']);
        self::assertSame('committed', $transaction['transaction_status']);

        $recorded = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
            $transaction,
            $updatePlan,
            $guard,
            function (array $context) use ($app, $updatePlan, $guard, $principal, $transaction): array {
                return app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
                    $app,
                    $updatePlan,
                    $guard,
                    $principal,
                    'executed',
                    (string) ($transaction['dbaccess_result']['result_code'] ?? 'task_card_inserted'),
                    (string) ($context['transaction_status'] ?? 'committed'),
                    ['rows_affected' => (int) ($transaction['dbaccess_result']['rows_affected'] ?? 0)],
                );
            },
            function (array $context) use ($app, $transaction): array {
                $executionAuditResult = is_array($context['execution_audit_result'] ?? null)
                    ? $context['execution_audit_result']
                    : [];

                return app_lab_sample18_generated_submit_idempotency_update_execution_outcome($app, [
                    'dedupe_key' => (string) ($context['dedupe_key'] ?? ''),
                    'execution_status' => 'executed',
                    'execution_result_code' => (string) ($transaction['dbaccess_result']['result_code'] ?? 'task_card_inserted'),
                    'transaction_status' => (string) ($context['transaction_status'] ?? 'committed'),
                    'execution_audit_event_key' => (string) ($executionAuditResult['item']['event_key'] ?? ''),
                    'metadata' => ['rows_affected' => (int) ($transaction['dbaccess_result']['rows_affected'] ?? 0)],
                ]);
            },
        );
        self::assertSame('recorded', $recorded['status']);
        self::assertTrue($recorded['success']);
        self::assertSame('recorded', $recorded['recording_status']);
        self::assertSame('recorded', $recorded['execution_audit_status']);
        self::assertSame('recorded', $recorded['idempotency_update_status']);
        self::assertFalse($recorded['recovery_required']);

        $executionAuditKey = (string) ($recorded['execution_audit_result']['item']['event_key'] ?? '');
        self::assertNotSame('', $executionAuditKey);
        self::assertSame(
            $auditAppend['item']['event_key'] ?? '',
            $recorded['execution_audit_result']['item']['metadata']['request_audit_event_key'] ?? '',
        );

        $latest = app_audit_log_fetch_latest($app, [
            'project_key' => 'SAMPLE18',
            'event_type' => 'sample18.generated_submit.executed',
            'target_key' => (string) ($preview['dedupe_key_preview'] ?? ''),
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        self::assertSame($executionAuditKey, $latest['items'][0]['event_key'] ?? '');

        $idempotencyRecords = app_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, [
            'dedupe_key' => (string) ($preview['dedupe_key_preview'] ?? ''),
            'limit' => 10,
        ]);
        self::assertTrue($idempotencyRecords['ok'], $idempotencyRecords['error']);
        self::assertCount(1, $idempotencyRecords['items']);
        self::assertSame('executed', $idempotencyRecords['items'][0]['result'] ?? '');
        self::assertSame('executed', $idempotencyRecords['items'][0]['metadata']['execution']['execution_status'] ?? '');
        self::assertSame('task_card_inserted', $idempotencyRecords['items'][0]['metadata']['execution']['execution_result_code'] ?? '');
        self::assertSame('committed', $idempotencyRecords['items'][0]['metadata']['execution']['transaction_status'] ?? '');
        self::assertSame($executionAuditKey, $idempotencyRecords['items'][0]['metadata']['execution']['execution_audit_event_key'] ?? '');

        $missingRecordTransaction = $transaction;
        $missingRecordTransaction['dedupe_key'] = 'missing-post-commit-record';
        $failed = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
            $missingRecordTransaction,
            $updatePlan,
            $guard,
            static function () use ($app, $updatePlan, $guard, $principal, $transaction): array {
                return app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
                    $app,
                    $updatePlan,
                    $guard,
                    $principal,
                    'executed',
                    (string) ($transaction['dbaccess_result']['result_code'] ?? 'task_card_inserted'),
                    'committed',
                );
            },
            static function (array $context) use ($app, $transaction): array {
                return app_lab_sample18_generated_submit_idempotency_update_execution_outcome($app, [
                    'dedupe_key' => (string) ($context['dedupe_key'] ?? ''),
                    'execution_status' => 'executed',
                    'execution_result_code' => (string) ($transaction['dbaccess_result']['result_code'] ?? 'task_card_inserted'),
                    'transaction_status' => 'committed',
                    'execution_audit_event_key' => (string) ($context['execution_audit_result']['item']['event_key'] ?? ''),
                    'metadata' => [],
                ]);
            },
        );
        self::assertSame('failed', $failed['status']);
        self::assertFalse($failed['success']);
        self::assertSame('failed', $failed['recording_status']);
        self::assertSame('recorded', $failed['execution_audit_status']);
        self::assertSame('failed', $failed['idempotency_update_status']);
        self::assertTrue($failed['recovery_required']);
        self::assertSame('post_commit_recording_failed', $failed['recovery_reason']);
        self::assertSame('idempotency_update_failed', $failed['failure_code']);
    }

    public function testMiniTaskBoardDemoReferenceOutputs(): void
    {
        $fixture = $this->sample18NoCodeGoldenFixture();
        $checklist = $this->sample18FastContractChecklist();
        $metadataContract = $checklist['metadata_contract'] ?? [];
        $htmlDomContract = $checklist['html_dom_contract'] ?? [];
        $app = app_bootstrap();
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
        try {
            $result = app_sample18_mini_task_board_demo_run(
                $app,
                'phpunit-sample18',
                app_sample18_mini_task_board_demo_default_reference_root(),
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        if (!$result['ok']) {
            fwrite(
                STDERR,
                json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            );
        }

        self::assertTrue(
            $result['ok'],
            is_string($result['error'] ?? null) && $result['error'] !== ''
                ? $result['error']
                : 'sample18 mini task board verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(4, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('HTML-PAGE', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
        self::assertSame($metadataContract['definition_version'] ?? '', $result['steps']['no_code_metadata']['definition_version'] ?? '');
        self::assertSame($metadataContract['runtime_version'] ?? '', $result['steps']['no_code_metadata']['runtime_version'] ?? '');
        self::assertSame($metadataContract['contract_key'] ?? '', $result['steps']['no_code_metadata']['contract_key'] ?? '');
        self::assertSame($metadataContract['screen_types'] ?? [], $result['steps']['no_code_metadata']['screen_types'] ?? []);
        self::assertSame($metadataContract['field_keys'] ?? [], $result['steps']['no_code_metadata']['field_keys'] ?? []);
        self::assertSame(
            $htmlDomContract['disabled_extension_action_keys'] ?? [],
            $result['steps']['no_code_metadata']['custom_operation_keys'] ?? [],
        );
        self::assertSame(
            $htmlDomContract['disabled_extension_action_keys'] ?? [],
            $result['steps']['no_code_metadata']['runtime_action_keys'] ?? [],
        );
        self::assertSame(
            $htmlDomContract['managed_action_keys'] ?? [],
            $result['steps']['no_code_metadata']['managed_action_keys'] ?? [],
        );
        self::assertSame(count($fixture['seed_rows'] ?? []), $result['steps']['no_code_metadata']['runtime_row_count'] ?? null);
        self::assertSame(4, $result['steps']['no_code_metadata']['golden_row_count'] ?? null);

        $publishedRoot = (string) ($result['steps']['no_code_metadata']['published_root'] ?? '');
        self::assertDirectoryExists($publishedRoot);
        $screenDefinition = NoCodeUiContractAssertions::readJsonFile($this, $publishedRoot . '/screen-definition.json');
        $contractActions = $screenDefinition['contracts'][0]['actions'] ?? [];
        self::assertIsArray($contractActions);
        $fieldCountsByAction = [];
        $bindingGate = $checklist['submit_route_binding_gate'] ?? [];
        self::assertIsArray($bindingGate);
        foreach ($contractActions as $action) {
            self::assertIsArray($action);
            $actionKey = (string) ($action['action_key'] ?? '');
            $fieldCountsByAction[$actionKey] = count($action['fields'] ?? []);
            self::assertSame('disabled', (string) ($action['availability'] ?? ''));
            self::assertSame(
                $htmlDomContract['managed_action_submit_url'] ?? '',
                (string) ($action['submit_route'] ?? ''),
            );
            if (in_array($actionKey, $bindingGate['managed_action_keys'] ?? [], true)) {
                self::assertSame(
                    [
                        'binding_state' => $bindingGate['state'] ?? '',
                        'submit_route' => $bindingGate['submit_route'] ?? '',
                        'csrf_source' => $bindingGate['csrf_source'] ?? '',
                        'csrf_token_field' => $bindingGate['csrf_token_field'] ?? '',
                        'csrf_source_selector' => $bindingGate['csrf_source_selector'] ?? '',
                        'csrf_transport' => $bindingGate['csrf_transport'] ?? '',
                        'csrf_submit_field' => $bindingGate['csrf_submit_field'] ?? '',
                        'required_button_state' => $bindingGate['required_button_state'] ?? '',
                        'click_binding_state' => $bindingGate['click_binding_state'] ?? '',
                        'submit_trigger' => $bindingGate['submit_trigger'] ?? '',
                        'network_submit_enabled' => $bindingGate['network_submit_enabled'] ?? null,
                        'guarded_click_inventory_state' => $bindingGate['guarded_click_inventory_state'] ?? '',
                        'enablement_gate_set' => $bindingGate['enablement_gate_set'] ?? '',
                        'enablement_gates' => $bindingGate['enablement_gates'] ?? [],
                        'payload_assembly' => $bindingGate['payload_assembly'] ?? '',
                        'blocked_response_handling' => $bindingGate['blocked_response_handling'] ?? '',
                        'failure_display_target' => $bindingGate['failure_display_target'] ?? '',
                        'runtime_click_binding' => $bindingGate['runtime_click_binding'] ?? null,
                        'mutation_enabled' => $bindingGate['mutation_enabled'] ?? null,
                        'fail_closed_result' => $bindingGate['fail_closed_result'] ?? '',
                        'http_smoke_command' => $bindingGate['http_smoke_command'] ?? '',
                    ],
                    $action['submit_binding_gate'] ?? [],
                );
            }
        }
        self::assertSame($htmlDomContract['managed_action_field_counts'] ?? [], $fieldCountsByAction);
        $runtimePreview = NoCodeUiContractAssertions::readJsonFile($this, $publishedRoot . '/runtime-preview.json');
        NoCodeUiContractAssertions::assertRuntimePreviewScreenKeys(
            $this,
            $runtimePreview,
            $metadataContract['screen_keys'] ?? [],
        );
        NoCodeUiContractAssertions::assertRuntimePreviewScreenField(
            $this,
            $runtimePreview,
            (string) ($checklist['status_filter_contract']['screen_key'] ?? ''),
            $checklist['status_filter_contract']['field'] ?? [],
        );
        $runtimePreviewHtml = (string) file_get_contents($publishedRoot . '/runtime-preview.html');
        NoCodeUiContractAssertions::assertPreviewHtmlScreens(
            $this,
            $runtimePreviewHtml,
            $metadataContract['screen_types_by_key'] ?? [],
        );
        NoCodeUiContractAssertions::assertPreviewHtmlFormFields(
            $this,
            $runtimePreviewHtml,
            $htmlDomContract['form_fields'] ?? [],
        );
        NoCodeUiContractAssertions::assertPreviewHtmlDisabledExtensionActions(
            $this,
            $runtimePreviewHtml,
            $htmlDomContract['disabled_extension_action_keys'] ?? [],
        );
        self::assertStringContainsString(
            'data-action-submit-url="' . ($htmlDomContract['managed_action_submit_url'] ?? '') . '"',
            $runtimePreviewHtml,
        );
        foreach (($bindingGate['required_dom_attributes'] ?? []) as $attribute => $value) {
            self::assertStringContainsString($attribute . '="' . $value . '"', $runtimePreviewHtml);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function sample18NoCodeGoldenFixture(): array
    {
        $path = dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-ui-golden.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    /**
     * @return array<string,mixed>
     */
    private function sample18FastContractChecklist(): array
    {
        $path = dirname(__DIR__, 2) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json';
        $decoded = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    /**
     * @return array<string,mixed>
     */
    private function sqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-sample18-audit-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            $storeDir,
        );

        return [
            'site' => 'lab',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
    }
}
