<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_dbaccess.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_schema.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_app_local_executor.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_executor.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_policy.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_server_dbaccess_executor.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_sync.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_sync_outbox_processor.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_sync_outbox_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/shared/shared_contract_core.php';

use PHPUnit\Framework\TestCase;

final class ManagedOperationLayerFoundationTest extends TestCase
{
    public function testManagedOperationMetadataAndPolicyEvaluateAgainstSharedContract(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedProject($app);
        $this->seedCanonicalDbAccessCatalogFixture($app);

        $operation = app_pdo_upsert_managed_operation($app, 'MANAGED-OPS-TEST', [
            'operation_key' => 'update_note',
            'contract_key' => 'task',
            'name' => 'Update Task Note',
            'operation_type' => 'update',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.edit',
            'required_roles' => ['editor'],
            'required_scopes' => ['task:write'],
            'required_claims' => ['department' => 'sales'],
            'notes' => 'managed operation first slice fixture',
            'source_of_truth' => 'phpunit',
        ]);
        self::assertTrue($operation['ok'], $operation['error']);
        self::assertSame('update_note', $operation['item']['operation_key'] ?? '');
        self::assertSame(['editor'], $operation['item']['required_roles'] ?? []);
        self::assertSame(['task:write'], $operation['item']['required_scopes'] ?? []);
        self::assertSame(['department' => 'sales'], $operation['item']['required_claims'] ?? []);

        $operationUpdate = app_pdo_upsert_managed_operation($app, 'MANAGED-OPS-TEST', [
            'operation_key' => 'update_note',
            'contract_key' => 'task',
            'name' => 'Update Task Note v2',
            'operation_type' => 'update',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.edit',
            'required_roles' => ['editor'],
            'required_scopes' => ['task:write'],
            'required_claims' => ['department' => 'sales'],
            'notes' => 'managed operation update path fixture',
            'source_of_truth' => 'phpunit',
        ]);
        self::assertTrue($operationUpdate['ok'], $operationUpdate['error']);
        self::assertSame('Update Task Note v2', $operationUpdate['item']['name'] ?? '');

        $keyField = app_pdo_upsert_managed_operation_field($app, 'MANAGED-OPS-TEST', 'update_note', [
            'field_physical_name' => 'id',
            'field_role' => 'key',
            'is_required' => true,
            'allow_client_write' => false,
            'source_of_truth' => 'phpunit',
        ]);
        self::assertTrue($keyField['ok'], $keyField['error']);

        $inputField = app_pdo_upsert_managed_operation_field($app, 'MANAGED-OPS-TEST', 'update_note', [
            'field_physical_name' => 'note',
            'field_role' => 'input',
            'is_required' => true,
            'allow_client_write' => true,
            'source_of_truth' => 'phpunit',
        ]);
        self::assertTrue($inputField['ok'], $inputField['error']);
        self::assertSame(2, count($inputField['item']['fields'] ?? []));

        $inputFieldUpdate = app_pdo_upsert_managed_operation_field($app, 'MANAGED-OPS-TEST', 'update_note', [
            'field_physical_name' => 'note',
            'field_role' => 'input',
            'is_required' => true,
            'allow_client_write' => true,
            'notes' => 'update path',
            'source_of_truth' => 'phpunit',
        ]);
        self::assertTrue($inputFieldUpdate['ok'], $inputFieldUpdate['error']);
        self::assertSame(2, count($inputFieldUpdate['item']['fields'] ?? []));

        $snapshot = app_pdo_fetch_managed_operation_snapshot($app, 'MANAGED-OPS-TEST');
        self::assertTrue($snapshot['ok'], $snapshot['error']);
        self::assertSame(1, count($snapshot['items']));
        self::assertSame('task', $snapshot['items'][0]['contract_key'] ?? '');

        $contract = $this->editableTaskContract();
        $decision = app_managed_operation_policy_evaluate(
            $this->editorPrincipal(),
            $inputFieldUpdate['item'],
            $contract,
        );
        self::assertTrue($decision['ok'], $decision['error']);
        self::assertTrue($decision['allowed'], json_encode($decision, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::assertSame([], $decision['failed_checks']);

        $missingScopePrincipal = $this->editorPrincipal();
        $missingScopePrincipal['scopes'] = [];
        $missingScopeDecision = app_managed_operation_policy_evaluate(
            $missingScopePrincipal,
            $inputFieldUpdate['item'],
            $contract,
        );
        self::assertTrue($missingScopeDecision['ok'], $missingScopeDecision['error']);
        self::assertFalse($missingScopeDecision['allowed']);
        self::assertContains('required_scope:task:write', $missingScopeDecision['failed_checks']);

        $readonlyContract = $contract;
        foreach ($readonlyContract['fields'] as &$field) {
            if (($field['physical_name'] ?? '') === 'note') {
                $field['contract_metadata']['operation_role'] = 'readonly';
            }
        }
        unset($field);
        $storageDecision = app_managed_operation_policy_evaluate(
            $this->editorPrincipal(),
            $inputFieldUpdate['item'],
            $readonlyContract,
        );
        self::assertTrue($storageDecision['ok'], $storageDecision['error']);
        self::assertFalse($storageDecision['allowed']);
        self::assertContains('field.operation_role:note', $storageDecision['failed_checks']);

        $plan = app_managed_operation_execution_prepare(
            $this->editorPrincipal(),
            $inputFieldUpdate['item'],
            $contract,
            [
                'id' => '1001',
                'note' => 'updated by managed operation',
            ],
        );
        self::assertTrue($plan['ok'], $plan['error']);
        self::assertTrue($plan['allowed'], json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::assertSame('plan-only', $plan['plan']['execution_mode'] ?? '');
        self::assertSame('update_note', $plan['plan']['operation_key'] ?? '');
        self::assertSame('update', $plan['plan']['operation_type'] ?? '');
        self::assertSame(['id' => 1001], $plan['plan']['key'] ?? []);
        self::assertSame(['note' => 'updated by managed operation'], $plan['plan']['input'] ?? []);
        self::assertSame([], $plan['plan']['filter'] ?? []);

        $syncIntent = app_managed_operation_sync_intent_from_plan($plan['plan'], [
            'storage_mode' => 'local-copy',
            'origin' => 'app-local',
            'target' => 'server',
        ]);
        self::assertTrue($syncIntent['ok'], $syncIntent['error']);
        self::assertSame('managed-operation-sync-intent-v0', $syncIntent['intent']['intent_version'] ?? '');
        self::assertSame('pending', $syncIntent['intent']['status'] ?? '');
        self::assertSame('local-copy', $syncIntent['intent']['storage_mode'] ?? '');
        self::assertSame('app-local', $syncIntent['intent']['origin'] ?? '');
        self::assertSame('server', $syncIntent['intent']['target'] ?? '');
        self::assertSame('update_note', $syncIntent['intent']['operation_key'] ?? '');
        self::assertSame(['id' => 1001], $syncIntent['intent']['payload']['key'] ?? []);
        self::assertSame(['note' => 'updated by managed operation'], $syncIntent['intent']['payload']['input'] ?? []);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) ($syncIntent['intent']['dedupe_key'] ?? ''));

        $enqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $syncIntent['intent']);
        self::assertTrue($enqueue['ok'], $enqueue['error']);
        self::assertSame('pending', $enqueue['item']['status'] ?? '');
        self::assertSame('local-copy', $enqueue['item']['storage_mode'] ?? '');
        self::assertSame('app-local', $enqueue['item']['origin'] ?? '');
        self::assertSame('server', $enqueue['item']['target'] ?? '');
        self::assertSame($syncIntent['intent']['dedupe_key'], $enqueue['item']['dedupe_key'] ?? '');
        self::assertSame($syncIntent['intent'], $enqueue['item']['intent'] ?? []);

        $duplicateEnqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $syncIntent['intent']);
        self::assertTrue($duplicateEnqueue['ok'], $duplicateEnqueue['error']);
        self::assertSame($enqueue['item']['id'] ?? '', $duplicateEnqueue['item']['id'] ?? '');

        $outbox = app_pdo_fetch_managed_operation_sync_outbox_catalog($app, 'MANAGED-OPS-TEST');
        self::assertTrue($outbox['ok'], $outbox['error']);
        self::assertSame(1, count($outbox['items']));
        self::assertSame('update_note', $outbox['items'][0]['operation_key'] ?? '');

        $nextPending = app_pdo_fetch_next_pending_managed_operation_sync_outbox_item($app, 'MANAGED-OPS-TEST');
        self::assertTrue($nextPending['ok'], $nextPending['error']);
        self::assertSame($enqueue['item']['id'] ?? '', $nextPending['item']['id'] ?? '');

        $secondIntent = $syncIntent['intent'];
        $secondIntent['payload']['input']['note'] = 'second pending operation';
        $secondIntent['dedupe_key'] = app_managed_operation_sync_intent_dedupe_key($secondIntent);
        $secondEnqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $secondIntent);
        self::assertTrue($secondEnqueue['ok'], $secondEnqueue['error']);
        self::assertNotSame($enqueue['item']['id'] ?? '', $secondEnqueue['item']['id'] ?? '');

        $running = app_pdo_mark_managed_operation_sync_outbox_running(
            $app,
            'MANAGED-OPS-TEST',
            (string) ($syncIntent['intent']['dedupe_key'] ?? ''),
        );
        self::assertTrue($running['ok'], $running['error']);
        self::assertSame('running', $running['item']['status'] ?? '');
        self::assertSame(1, $running['item']['attempts'] ?? 0);
        self::assertSame('', $running['item']['last_error'] ?? '');

        $failed = app_pdo_mark_managed_operation_sync_outbox_failed(
            $app,
            'MANAGED-OPS-TEST',
            (string) ($syncIntent['intent']['dedupe_key'] ?? ''),
            'temporary server unavailable',
        );
        self::assertTrue($failed['ok'], $failed['error']);
        self::assertSame('failed', $failed['item']['status'] ?? '');
        self::assertSame(1, $failed['item']['attempts'] ?? 0);
        self::assertSame('temporary server unavailable', $failed['item']['last_error'] ?? '');

        $retry = app_pdo_mark_managed_operation_sync_outbox_running(
            $app,
            'MANAGED-OPS-TEST',
            (string) ($syncIntent['intent']['dedupe_key'] ?? ''),
        );
        self::assertTrue($retry['ok'], $retry['error']);
        self::assertSame('running', $retry['item']['status'] ?? '');
        self::assertSame(2, $retry['item']['attempts'] ?? 0);
        self::assertSame('', $retry['item']['last_error'] ?? '');

        $done = app_pdo_mark_managed_operation_sync_outbox_done(
            $app,
            'MANAGED-OPS-TEST',
            (string) ($syncIntent['intent']['dedupe_key'] ?? ''),
        );
        self::assertTrue($done['ok'], $done['error']);
        self::assertSame('done', $done['item']['status'] ?? '');
        self::assertSame(2, $done['item']['attempts'] ?? 0);
        self::assertSame('', $done['item']['last_error'] ?? '');

        $nextPendingAfterDone = app_pdo_fetch_next_pending_managed_operation_sync_outbox_item($app, 'MANAGED-OPS-TEST');
        self::assertTrue($nextPendingAfterDone['ok'], $nextPendingAfterDone['error']);
        self::assertSame($secondEnqueue['item']['id'] ?? '', $nextPendingAfterDone['item']['id'] ?? '');

        $claim = app_pdo_claim_managed_operation_sync_outbox_item(
            $app,
            'MANAGED-OPS-TEST',
            (string) ($secondIntent['dedupe_key'] ?? ''),
        );
        self::assertTrue($claim['ok'], $claim['error']);
        self::assertTrue($claim['claimed']);
        self::assertSame('running', $claim['item']['status'] ?? '');
        self::assertSame(1, $claim['item']['attempts'] ?? 0);

        $doubleClaim = app_pdo_claim_managed_operation_sync_outbox_item(
            $app,
            'MANAGED-OPS-TEST',
            (string) ($secondIntent['dedupe_key'] ?? ''),
        );
        self::assertTrue($doubleClaim['ok'], $doubleClaim['error']);
        self::assertFalse($doubleClaim['claimed']);
        self::assertSame('running', $doubleClaim['item']['status'] ?? '');
        self::assertSame(1, $doubleClaim['item']['attempts'] ?? 0);

        $noPendingAfterClaim = app_pdo_fetch_next_pending_managed_operation_sync_outbox_item($app, 'MANAGED-OPS-TEST');
        self::assertTrue($noPendingAfterClaim['ok'], $noPendingAfterClaim['error']);
        self::assertNull($noPendingAfterClaim['item']);

        $thirdIntent = $syncIntent['intent'];
        $thirdIntent['payload']['input']['note'] = 'processor success operation';
        $thirdIntent['dedupe_key'] = app_managed_operation_sync_intent_dedupe_key($thirdIntent);
        $thirdEnqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $thirdIntent);
        self::assertTrue($thirdEnqueue['ok'], $thirdEnqueue['error']);

        $handledItems = [];
        $processed = app_managed_operation_sync_outbox_process_next(
            $app,
            'MANAGED-OPS-TEST',
            static function (array $item) use (&$handledItems): array {
                $handledItems[] = $item;

                return [
                    'ok' => true,
                    'processed_by' => 'phpunit',
                ];
            },
        );
        self::assertTrue($processed['ok'], $processed['error']);
        self::assertTrue($processed['processed']);
        self::assertSame('done', $processed['outcome']);
        self::assertSame('done', $processed['item']['status'] ?? '');
        self::assertSame(1, $processed['item']['attempts'] ?? 0);
        self::assertSame($thirdEnqueue['item']['dedupe_key'] ?? '', $handledItems[0]['dedupe_key'] ?? '');
        self::assertSame('phpunit', $processed['handler_result']['processed_by'] ?? '');

        $localManifest = app_shared_contract_core_sample02_task_manifest();
        $localManifest['contracts'][0] = $contract;
        $localPdo = $this->createLocalPdo($localManifest);
        $localSeed = app_local_sqlite_dbaccess_save_dto($localPdo, $localManifest, 'task', [
            'id' => 1001,
            'title' => 'Local task before operation',
            'status' => 'draft',
            'sortOrder' => 10,
            'isPinned' => false,
            'publishedAt' => null,
            'note' => 'before managed operation',
        ]);
        self::assertTrue($localSeed['ok'], $localSeed['error']);

        $localExecute = app_managed_operation_app_local_execute_intent(
            $localPdo,
            $localManifest,
            $syncIntent['intent'],
        );
        self::assertTrue($localExecute['ok'], $localExecute['error']);
        self::assertTrue($localExecute['executed']);
        self::assertSame('update', $localExecute['operation_type']);
        self::assertSame(['id' => 1001], $localExecute['result']['key'] ?? []);

        $localReadAfterExecute = app_local_sqlite_dbaccess_read_dto($localPdo, $localManifest, 'task', ['id' => 1001]);
        self::assertTrue($localReadAfterExecute['ok'], $localReadAfterExecute['error']);
        self::assertSame('Local task before operation', $localReadAfterExecute['dto']['title'] ?? '');
        self::assertSame('updated by managed operation', $localReadAfterExecute['dto']['note'] ?? '');
        self::assertSame(1, $localReadAfterExecute['local_metadata']['dirty'] ?? 0);

        $fourthIntent = $syncIntent['intent'];
        $fourthIntent['payload']['input']['note'] = 'processor failure operation';
        $fourthIntent['dedupe_key'] = app_managed_operation_sync_intent_dedupe_key($fourthIntent);
        $fourthEnqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $fourthIntent);
        self::assertTrue($fourthEnqueue['ok'], $fourthEnqueue['error']);

        $failedProcess = app_managed_operation_sync_outbox_process_next(
            $app,
            'MANAGED-OPS-TEST',
            static fn (array $_item): array => [
                'ok' => false,
                'error' => 'server write rejected',
            ],
        );
        self::assertTrue($failedProcess['ok'], $failedProcess['error']);
        self::assertTrue($failedProcess['processed']);
        self::assertSame('failed', $failedProcess['outcome']);
        self::assertSame('failed', $failedProcess['item']['status'] ?? '');
        self::assertSame(1, $failedProcess['item']['attempts'] ?? 0);
        self::assertSame('server write rejected', $failedProcess['item']['last_error'] ?? '');

        $fifthIntent = $syncIntent['intent'];
        $fifthIntent['payload']['input']['note'] = 'processed through App-local outbox handler';
        $fifthIntent['dedupe_key'] = app_managed_operation_sync_intent_dedupe_key($fifthIntent);
        $fifthEnqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $fifthIntent);
        self::assertTrue($fifthEnqueue['ok'], $fifthEnqueue['error']);

        $localOutboxProcess = app_managed_operation_sync_outbox_process_next(
            $app,
            'MANAGED-OPS-TEST',
            app_managed_operation_app_local_outbox_handler($localPdo, $localManifest),
        );
        self::assertTrue($localOutboxProcess['ok'], $localOutboxProcess['error']);
        self::assertTrue($localOutboxProcess['processed']);
        self::assertSame('done', $localOutboxProcess['outcome']);
        self::assertSame('done', $localOutboxProcess['item']['status'] ?? '');
        self::assertSame('update', $localOutboxProcess['handler_result']['operation_type'] ?? '');

        $localReadAfterOutboxHandler = app_local_sqlite_dbaccess_read_dto($localPdo, $localManifest, 'task', ['id' => 1001]);
        self::assertTrue($localReadAfterOutboxHandler['ok'], $localReadAfterOutboxHandler['error']);
        self::assertSame('processed through App-local outbox handler', $localReadAfterOutboxHandler['dto']['note'] ?? '');

        ManagedOperationLayerFoundationFakeDbAccess::$calls = [];
        $serverExecute = app_managed_operation_server_dbaccess_execute_intent($syncIntent['intent'], [
            'endpoint' => 'server',
            'dbaccess_class' => ManagedOperationLayerFoundationFakeDbAccess::class,
            'data_class' => ManagedOperationLayerFoundationFakeTaskData::class,
            'method_map' => [
                'update' => 'UpdateTask',
            ],
        ]);
        self::assertTrue($serverExecute['ok'], $serverExecute['error']);
        self::assertTrue($serverExecute['executed']);
        self::assertSame('update', $serverExecute['operation_type']);
        self::assertSame('UpdateTask', $serverExecute['method_name']);
        self::assertSame('server-updated', $serverExecute['result']['status'] ?? '');
        self::assertSame(1001, ManagedOperationLayerFoundationFakeDbAccess::$calls[0]['object']->id ?? 0);
        self::assertSame('updated by managed operation', ManagedOperationLayerFoundationFakeDbAccess::$calls[0]['object']->note ?? '');

        $serverBinding = app_managed_operation_server_dbaccess_binding_from_candidate(
            [
                'source_name' => 'Task',
                'data_class' => ManagedOperationLayerFoundationFakeTaskData::class,
                'dbaccess_class' => ManagedOperationLayerFoundationFakeDbAccess::class,
                'method_catalog' => [
                    ['name' => 'GetTask'],
                    ['name' => 'InsertTask'],
                    ['name' => 'UpdateTask'],
                    ['name' => 'DeleteTask'],
                ],
            ],
            $inputFieldUpdate['item'],
        );
        self::assertTrue($serverBinding['ok'], $serverBinding['error']);
        self::assertSame('server', $serverBinding['binding']['endpoint'] ?? '');
        self::assertSame(
            ['update' => 'UpdateTask'],
            $serverBinding['binding']['method_map'] ?? [],
        );

        $serverBindingFromCandidates = app_managed_operation_server_dbaccess_binding_from_candidates(
            [
                [
                    'source_name' => 'Other',
                    'data_class' => ManagedOperationLayerFoundationFakeTaskData::class,
                    'dbaccess_class' => ManagedOperationLayerFoundationFakeDbAccess::class,
                    'method_catalog' => [
                        ['name' => 'UpdateOther'],
                    ],
                ],
                [
                    'source_name' => 'Task',
                    'data_class' => ManagedOperationLayerFoundationFakeTaskData::class,
                    'dbaccess_class' => ManagedOperationLayerFoundationFakeDbAccess::class,
                    'method_catalog' => [
                        ['name' => 'GetTask'],
                        ['name' => 'InsertTask'],
                        ['name' => 'UpdateTask'],
                        ['name' => 'DeleteTask'],
                    ],
                ],
            ],
            $inputFieldUpdate['item'],
        );
        self::assertTrue($serverBindingFromCandidates['ok'], $serverBindingFromCandidates['error']);
        self::assertSame('Task', $serverBindingFromCandidates['binding']['source_name'] ?? '');
        self::assertSame(
            ['update' => 'UpdateTask'],
            $serverBindingFromCandidates['binding']['method_map'] ?? [],
        );

        $serverBindingFromProjectCatalog = app_managed_operation_server_dbaccess_binding_from_project_catalog(
            $app,
            'MANAGED-OPS-TEST',
            $inputFieldUpdate['item'],
        );
        self::assertTrue($serverBindingFromProjectCatalog['ok'], $serverBindingFromProjectCatalog['error']);
        self::assertSame('Task', $serverBindingFromProjectCatalog['binding']['source_name'] ?? '');
        self::assertSame('TaskData', $serverBindingFromProjectCatalog['binding']['data_class'] ?? '');
        self::assertSame('TaskDBAccess', $serverBindingFromProjectCatalog['binding']['dbaccess_class'] ?? '');
        self::assertSame(
            ['update' => 'UpdateTask'],
            $serverBindingFromProjectCatalog['binding']['method_map'] ?? [],
        );

        ManagedOperationLayerFoundationFakeDbAccess::$calls = [];
        $serverExecuteFromBinding = app_managed_operation_server_dbaccess_execute_intent(
            $syncIntent['intent'],
            $serverBindingFromCandidates['binding'],
        );
        self::assertTrue($serverExecuteFromBinding['ok'], $serverExecuteFromBinding['error']);
        self::assertSame('UpdateTask', $serverExecuteFromBinding['method_name']);
        self::assertSame('updated by managed operation', ManagedOperationLayerFoundationFakeDbAccess::$calls[0]['object']->note ?? '');

        $sixthIntent = $syncIntent['intent'];
        $sixthIntent['payload']['input']['note'] = 'processed through server DBAccess handler';
        $sixthIntent['dedupe_key'] = app_managed_operation_sync_intent_dedupe_key($sixthIntent);
        $sixthEnqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $sixthIntent);
        self::assertTrue($sixthEnqueue['ok'], $sixthEnqueue['error']);

        ManagedOperationLayerFoundationFakeDbAccess::$calls = [];
        $serverOutboxProcess = app_managed_operation_sync_outbox_process_next(
            $app,
            'MANAGED-OPS-TEST',
            app_managed_operation_server_dbaccess_outbox_handler([
                'endpoint' => 'server',
                'dbaccess_class' => ManagedOperationLayerFoundationFakeDbAccess::class,
                'data_class' => ManagedOperationLayerFoundationFakeTaskData::class,
                'method_map' => [
                    'update' => 'UpdateTask',
                ],
            ]),
        );
        self::assertTrue($serverOutboxProcess['ok'], $serverOutboxProcess['error']);
        self::assertTrue($serverOutboxProcess['processed']);
        self::assertSame('done', $serverOutboxProcess['outcome']);
        self::assertSame('done', $serverOutboxProcess['item']['status'] ?? '');
        self::assertSame('UpdateTask', $serverOutboxProcess['handler_result']['method_name'] ?? '');
        self::assertSame('processed through server DBAccess handler', ManagedOperationLayerFoundationFakeDbAccess::$calls[0]['object']->note ?? '');

        $noPendingProcess = app_managed_operation_sync_outbox_process_next(
            $app,
            'MANAGED-OPS-TEST',
            static fn (array $_item): array => ['ok' => true],
        );
        self::assertTrue($noPendingProcess['ok'], $noPendingProcess['error']);
        self::assertFalse($noPendingProcess['processed']);
        self::assertSame('no_pending', $noPendingProcess['outcome']);
        self::assertNull($noPendingProcess['item']);

        $invalidSyncMode = app_managed_operation_sync_intent_from_plan($plan['plan'], [
            'storage_mode' => 'full-sync-now',
        ]);
        self::assertFalse($invalidSyncMode['ok']);
        self::assertSame('managed operation sync storage mode is invalid.', $invalidSyncMode['error']);

        $invalidPlan = app_managed_operation_sync_intent_from_plan([
            'execution_mode' => 'execute-now',
        ]);
        self::assertFalse($invalidPlan['ok']);
        self::assertSame('managed operation sync intent requires a plan-only execution plan.', $invalidPlan['error']);

        $unknownInput = app_managed_operation_execution_prepare(
            $this->editorPrincipal(),
            $inputFieldUpdate['item'],
            $contract,
            [
                'id' => 1001,
                'note' => 'updated by managed operation',
                'local_updated_at' => 'must not be writable',
            ],
        );
        self::assertTrue($unknownInput['ok'], $unknownInput['error']);
        self::assertFalse($unknownInput['allowed']);
        self::assertContains('input.unknown:local_updated_at', $unknownInput['failed_checks']);

        $missingRequiredInput = app_managed_operation_execution_prepare(
            $this->editorPrincipal(),
            $inputFieldUpdate['item'],
            $contract,
            [
                'id' => 1001,
            ],
        );
        self::assertTrue($missingRequiredInput['ok'], $missingRequiredInput['error']);
        self::assertFalse($missingRequiredInput['allowed']);
        self::assertContains('input.missing:note', $missingRequiredInput['failed_checks']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-managed-ops-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
        $app = [
            'site' => 'admin',
            'db' => $configDb,
            'config_db' => $configDb,
        ];

        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        self::assertTrue($bootstrap['summary']['schema_current']);
        self::assertSame([], $bootstrap['missing_tables']);

        return $app;
    }

    /**
     * @param array<string,mixed> $manifest
     */
    private function createLocalPdo(array $manifest): PDO
    {
        $schema = app_local_sqlite_schema_generate($manifest);
        self::assertTrue($schema['ok'], $schema['error']);

        $pdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($pdo, $schema['schema_sql']);
        self::assertTrue($apply['ok'], $apply['error']);

        return $pdo;
    }

    private function seedProject(array $app): void
    {
        $project = app_pdo_insert_project($app, [
            'project_key' => 'MANAGED-OPS-TEST',
            'name' => 'Managed Ops Test',
            'slug' => 'managed-ops-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'managed operation layer fixture',
        ]);
        self::assertTrue($project['ok'], $project['error']);
    }

    private function seedCanonicalDbAccessCatalogFixture(array $app): void
    {
        $table = app_create_table_metadata_item($app, 'MANAGED-OPS-TEST', 'Task');
        self::assertTrue($table['ok'], $table['error']);

        foreach ([
            [
                'name' => 'id',
                'datatype' => 'int',
                'is_null' => '0',
                'is_key' => 'PRI',
                'is_default' => '',
                'extra' => '',
                'memo' => '',
            ],
            [
                'name' => 'note',
                'datatype' => 'varchar(255)',
                'is_null' => '1',
                'is_key' => '',
                'is_default' => '',
                'extra' => '',
                'memo' => '',
            ],
        ] as $columnInput) {
            $column = app_create_table_metadata_column(
                $app,
                'MANAGED-OPS-TEST',
                (string) ($table['item']['pid'] ?? ''),
                $columnInput,
            );
            self::assertTrue($column['ok'], $column['error']);
        }

        $dataClass = app_create_data_class_metadata_item($app, 'MANAGED-OPS-TEST', [
            'name' => 'Task',
            'store_base_path' => '',
            'is_autoload' => '0',
            'inherit_parent_data_class_name' => '',
        ]);
        self::assertTrue($dataClass['ok'], $dataClass['error']);

        foreach ([
            [
                'name' => 'id',
                'datatype' => 'int',
                'ref_data_class_name' => '',
                'ref_data_class_field_name' => '',
            ],
            [
                'name' => 'note',
                'datatype' => 'string',
                'ref_data_class_name' => '',
                'ref_data_class_field_name' => '',
            ],
        ] as $fieldInput) {
            $field = app_create_data_class_metadata_field(
                $app,
                'MANAGED-OPS-TEST',
                (string) ($dataClass['item']['pid'] ?? ''),
                $fieldInput,
            );
            self::assertTrue($field['ok'], $field['error']);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function editableTaskContract(): array
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $contract = $manifest['contracts'][0];

        foreach ($contract['fields'] as &$field) {
            if (($field['physical_name'] ?? '') === 'note') {
                $field['contract_metadata'] = [
                    'operation_role' => 'editable',
                ];
            }
        }
        unset($field);

        return $contract;
    }

    /**
     * @return array<string,mixed>
     */
    private function editorPrincipal(): array
    {
        return [
            'id' => 'editor@example.test',
            'display_name' => 'Editor',
            'auth_source' => 'phpunit',
            'site' => 'admin',
            'roles' => [],
            'project_roles' => [
                'MANAGED-OPS-TEST' => ['editor'],
            ],
            'scopes' => ['task:write'],
            'claims' => [
                'department' => 'sales',
            ],
        ];
    }
}

final class ManagedOperationLayerFoundationFakeTaskData
{
    public int $id = 0;
    public ?string $note = null;
}

final class ManagedOperationLayerFoundationFakeDbAccess
{
    /**
     * @var list<array{method:string,object:ManagedOperationLayerFoundationFakeTaskData}>
     */
    public static array $calls = [];

    /**
     * @return array<string,mixed>
     */
    public function UpdateTask(ManagedOperationLayerFoundationFakeTaskData $task): array
    {
        self::$calls[] = [
            'method' => 'UpdateTask',
            'object' => $task,
        ];

        return [
            'status' => 'server-updated',
            'id' => $task->id,
        ];
    }
}
