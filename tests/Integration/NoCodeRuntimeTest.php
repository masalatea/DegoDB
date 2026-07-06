<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_runtime.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_managed_operation_bridge.php';
require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_server_dbaccess_executor.php';
require_once dirname(__DIR__, 2) . '/mtool/shared/shared_contract_core.php';

use PHPUnit\Framework\TestCase;

final class NoCodeRuntimeTest extends TestCase
{
    public function testRendersListDetailAndFormModelsFromGeneratedScreenDefinition(): void
    {
        $definition = $this->screenDefinition();
        $list = app_no_code_runtime_render_screen($definition, 'task_list', [
            [
                'id' => 1,
                'title' => 'Write runtime test',
                'status' => 'open',
                'sort_order' => 10,
                'is_pinned' => true,
                'published_at' => null,
                'note' => 'List row note',
            ],
        ]);

        self::assertTrue($list['ok'], $list['error']);
        self::assertSame('no-code-runtime-v0', $list['render']['runtime_version'] ?? '');
        self::assertSame('task_list', $list['render']['screen_key'] ?? '');
        self::assertSame('list', $list['render']['screen_type'] ?? '');
        self::assertSame('Task List', $list['render']['screen_title'] ?? '');
        self::assertSame('Task / List', $list['render']['screen_subtitle'] ?? '');
        self::assertSame('No records to show yet.', $list['render']['empty_state_message'] ?? '');
        self::assertTrue($list['render']['sync_status_hint'] ?? false);
        self::assertSame(
            'Failed or retryable sync items are reviewed from the operator sync outbox.',
            $list['render']['sync_error_retry_hint'] ?? '',
        );
        self::assertSame('Write runtime test', $list['render']['data']['rows'][0]['title']['display_value'] ?? '');
        self::assertSame('true', $list['render']['data']['rows'][0]['is_pinned']['display_value'] ?? '');

        $listActions = $this->indexBy($list['render']['actions'] ?? [], 'action_key');
        self::assertTrue($listActions['read_task']['enabled'] ?? false);
        self::assertTrue($listActions['update_note']['enabled'] ?? false);
        self::assertSame('update', $listActions['update_note']['operation_type'] ?? '');
        self::assertSame(
            [
                ['field_key' => 'id', 'role' => 'key', 'required' => true, 'client_write' => false],
                ['field_key' => 'note', 'role' => 'input', 'required' => true, 'client_write' => true],
            ],
            $listActions['update_note']['fields'] ?? [],
        );

        $detail = app_no_code_runtime_render_screen($definition, 'task_detail', [], [
            'id' => 1,
            'title' => 'Write runtime test',
            'status' => 'open',
            'sort_order' => 10,
            'is_pinned' => true,
            'published_at' => null,
            'note' => 'Detail note',
        ]);
        self::assertTrue($detail['ok'], $detail['error']);
        self::assertSame('detail', $detail['render']['screen_type'] ?? '');
        self::assertSame('Detail note', $detail['render']['data']['item']['note']['display_value'] ?? '');

        $form = app_no_code_runtime_render_screen($definition, 'task_form', [], [
            'id' => 1,
            'title' => 'Write runtime test',
            'note' => 'Editable note',
        ]);
        self::assertTrue($form['ok'], $form['error']);
        self::assertSame('form', $form['render']['screen_type'] ?? '');
        self::assertFalse($form['render']['sync_status_hint'] ?? true);
        self::assertSame('', $form['render']['sync_error_retry_hint'] ?? 'unexpected');
        self::assertArrayNotHasKey('id', $form['render']['data']['item'] ?? []);
        self::assertSame('Editable note', $form['render']['data']['item']['note']['display_value'] ?? '');
    }

    public function testRendersRuntimePreviewHtmlFromGeneratedScreenModels(): void
    {
        $definition = $this->screenDefinition();
        $list = app_no_code_runtime_render_screen($definition, 'task_list', [
            [
                'id' => 1,
                'title' => 'Write runtime HTML',
                'status' => 'open',
                'note' => 'Visible in table',
            ],
        ]);
        $detail = app_no_code_runtime_render_screen($definition, 'task_detail', [], [
            'id' => 1,
            'title' => 'Write runtime HTML',
            'note' => 'Visible in detail',
        ]);
        $form = app_no_code_runtime_render_screen($definition, 'task_form', [], [
            'id' => 1,
            'title' => 'Write runtime HTML',
            'note' => 'Editable in form',
        ]);

        self::assertTrue($list['ok'], $list['error']);
        self::assertTrue($detail['ok'], $detail['error']);
        self::assertTrue($form['ok'], $form['error']);

        $html = app_no_code_runtime_render_preview_html([
            'runtime_version' => app_no_code_runtime_version(),
            'definition_version' => $definition['definition_version'] ?? '',
            'project_key' => $definition['project_key'] ?? '',
            'screens' => [
                $list['render'],
                $detail['render'],
                $form['render'],
            ],
        ]);

        self::assertStringContainsString('<!doctype html>', $html);
        self::assertStringContainsString('<main class="no-code-preview" aria-labelledby="no-code-preview-title"', $html);
        self::assertStringContainsString('<h1 id="no-code-preview-title">RUNTIME-TEST</h1>', $html);
        self::assertStringContainsString('data-runtime-version="no-code-runtime-v0"', $html);
        self::assertStringContainsString('data-runtime-state="ready"', $html);
        self::assertStringContainsString('id="no-code-runtime-preview-data"', $html);
        self::assertStringContainsString('window.noCodeRuntimeDispatchAction', $html);
        self::assertStringContainsString('Preview ready', $html);
        self::assertStringContainsString('data-screen-state="ready"', $html);
        self::assertStringContainsString('data-sync-status-hint="visible">Sync status tracked</span>', $html);
        self::assertStringContainsString('data-sync-retry-hint="operator-outbox"', $html);
        self::assertStringContainsString('Failed or retryable sync items are reviewed from the operator sync outbox.', $html);
        self::assertStringContainsString('Task List', $html);
        self::assertStringContainsString('Task Detail', $html);
        self::assertStringContainsString('Task Form', $html);
        self::assertStringContainsString('role="region" aria-labelledby="no-code-screen-title-task_list"', $html);
        self::assertStringContainsString('data-screen-type="list"', $html);
        self::assertStringContainsString('data-screen-body="task_list"', $html);
        self::assertStringContainsString('<h2 id="no-code-screen-title-task_list">Task List</h2>', $html);
        self::assertStringContainsString('<caption class="no-code-table-caption">Task List records</caption>', $html);
        self::assertStringContainsString('class="no-code-screen-summary" data-screen-summary="task_list" data-field-count="7" data-action-count="2"', $html);
        self::assertStringContainsString('<span>7 fields</span><span>2 actions</span><code>task_list</code>', $html);
        self::assertStringContainsString('class="no-code-action-feedback" role="status" aria-live="polite" data-state="idle"', $html);
        self::assertStringContainsString('class="no-code-intent-draft" data-intent-draft-state="idle"', $html);
        self::assertStringContainsString('Action Intent Draft', $html);
        self::assertStringContainsString('class="no-code-intent-draft-state-badge" data-intent-draft-state-badge', $html);
        self::assertStringContainsString('Idle</span>', $html);
        self::assertStringContainsString('Editing this screen updates a local action-intent preview.', $html);
        self::assertStringContainsString('class="no-code-intent-draft-summary" data-intent-draft-summary', $html);
        self::assertStringContainsString('Draft summary will update when this screen is ready.', $html);
        self::assertStringContainsString('class="no-code-intent-draft-meta" data-intent-draft-meta', $html);
        self::assertStringContainsString('Action metadata will update with the draft.', $html);
        self::assertStringContainsString('class="no-code-intent-draft-fields" data-intent-draft-fields', $html);
        self::assertStringContainsString('Field summary will update with the draft.', $html);
        self::assertStringContainsString('class="no-code-intent-draft-payload" data-intent-draft-payload', $html);
        self::assertStringContainsString('Payload summary will update with the draft.', $html);
        self::assertStringContainsString('data-intent-draft-copy', $html);
        self::assertStringContainsString('Copy draft JSON', $html);
        self::assertStringContainsString('data-intent-draft-copy-status', $html);
        self::assertStringContainsString('Draft JSON copy will be available when this screen is ready.', $html);
        self::assertStringContainsString('data-runtime-execute disabled data-runtime-execute-state="unavailable"', $html);
        self::assertStringContainsString('Submit to server', $html);
        self::assertStringContainsString('data-runtime-execute-status', $html);
        self::assertStringContainsString('Server execution is available from an authenticated current or alias preview.', $html);
        self::assertStringContainsString('data-runtime-result-refresh disabled', $html);
        self::assertStringContainsString('Refresh preview', $html);
        self::assertStringContainsString('data-runtime-result-refresh-status', $html);
        self::assertStringContainsString('Artifact-key previews stay static; current or alias previews can fetch live runtime data when available.', $html);
        self::assertStringContainsString('data-runtime-outbox-detail-copy disabled', $html);
        self::assertStringContainsString('Copy outbox path', $html);
        self::assertStringContainsString('data-runtime-outbox-detail-link hidden href=""', $html);
        self::assertStringContainsString('Open outbox detail', $html);
        self::assertStringContainsString('data-runtime-outbox-detail-copy-status', $html);
        self::assertStringContainsString('Outbox detail path will be available after server submit.', $html);
        self::assertStringContainsString('class="no-code-runtime-flow" data-runtime-flow-state="waiting"', $html);
        self::assertStringContainsString('Runtime flow', $html);
        self::assertStringContainsString('data-runtime-flow-step="submit" data-state="waiting"', $html);
        self::assertStringContainsString('Submit waits for a ready draft.', $html);
        self::assertStringContainsString('data-runtime-flow-step="track" data-state="waiting"', $html);
        self::assertStringContainsString('Outbox tracking appears after submit.', $html);
        self::assertStringContainsString('data-runtime-flow-step="refresh" data-state="waiting"', $html);
        self::assertStringContainsString('Refresh appears after submit.', $html);
        self::assertStringContainsString('class="no-code-intent-draft-json" data-intent-draft-json-details', $html);
        self::assertStringContainsString('<summary>Draft JSON</summary>', $html);
        self::assertStringContainsString('data-intent-draft-output', $html);
        self::assertStringContainsString('no-code-runtime-execution-binding', $html);
        self::assertStringContainsString('window.__noCodeRuntimeDispatches = [];', $html);
        self::assertStringContainsString('window.__noCodeRuntimeExecutionBinding = executionBinding;', $html);
        self::assertStringContainsString('function buildActionIntentDraft(action, input)', $html);
        self::assertStringContainsString('function copyIntentDraft(button)', $html);
        self::assertStringContainsString('function copyRuntimeOutboxDetailPath(button)', $html);
        self::assertStringContainsString('function writeRuntimeFlow(screen, state, detailPath)', $html);
        self::assertStringContainsString('function refreshRuntimePreview(button)', $html);
        self::assertStringContainsString('function refreshRuntimeDataForScreen(screen, button, workingMessage)', $html);
        self::assertStringContainsString('Sync outbox is done. Fetching read-only live runtime data...', $html);
        self::assertStringContainsString('function runtimeDataBindingUrl()', $html);
        self::assertStringContainsString('function applyRuntimeDataPayload(payload)', $html);
        self::assertStringContainsString('function hiddenActionKeyControls(render, item, visibleFieldKeys)', $html);
        self::assertStringContainsString('data-runtime-hidden-action-key', $html);
        self::assertStringContainsString('fetch(runtimeDataBindingUrl()', $html);
        self::assertStringContainsString('function submitRuntimeAction(button)', $html);
        self::assertStringContainsString("formData.append('_csrf', executionBinding.csrf_token || '');", $html);
        self::assertStringContainsString("if (executionBinding.demo_processing === 'available')", $html);
        self::assertStringContainsString("formData.append('runtime_demo_process', '1');", $html);
        self::assertStringContainsString("fetch(executionBinding.execution_url", $html);
        self::assertStringContainsString('Server execution endpoint is ready: ', $html);
        self::assertStringContainsString('Next result check: process the sync outbox item, then reload this generated preview artifact or open the outbox detail.', $html);
        self::assertStringContainsString('Demo processing completed this item; reload this generated preview artifact to re-read the current preview.', $html);
        self::assertStringContainsString('Demo processing did not run: ', $html);
        self::assertStringContainsString('data-runtime-outbox-detail-path', $html);
        self::assertStringContainsString("'Action: ' + (draft.action_key || '(unknown)')", $html);
        self::assertStringContainsString("function actionFieldSummary(action)", $html);
        self::assertStringContainsString("'Fields: key=' + list(buckets.key)", $html);
        self::assertStringContainsString("'Payload: ' + keyCount + ' key fields'", $html);
        self::assertStringContainsString("stateBadge.setAttribute('data-state', draftState);", $html);
        self::assertStringContainsString('draft_checks: []', $html);
        self::assertStringContainsString('policy_failed_checks:', $html);
        self::assertStringContainsString("draft.draft_checks.push('action.disabled');", $html);
        self::assertStringContainsString("draft.draft_checks.push((field.role || 'input') + '.missing:' + fieldKey);", $html);
        self::assertStringContainsString("summaryChecks.push('policy: ' + policyChecks.join(', '));", $html);
        self::assertStringContainsString('Ready draft: no blocking checks found.', $html);
        self::assertStringContainsString('Blocked draft: ', $html);
        self::assertStringContainsString('<nav class="no-code-actions" aria-label="Task List actions">', $html);
        self::assertStringContainsString('Select an enabled action to preview its intent.', $html);
        self::assertStringContainsString('data-action-state="ready"', $html);
        self::assertStringContainsString('data-action-affordance="keyboard-intent-preview"', $html);
        self::assertStringContainsString('data-keyboard-activation="enter-space"', $html);
        self::assertStringContainsString('class="no-code-required-badge">Required</span>', $html);
        self::assertStringContainsString('class="no-code-required-hint" data-required-field="title" data-required-label="Title" data-required-state="pending">Required for the generated action intent.</span>', $html);
        self::assertStringContainsString('required aria-describedby="field-hint-title"', $html);
        self::assertStringContainsString('function writeRequiredFieldHints(screen, draft, action)', $html);
        self::assertStringContainsString('Missing required \' + roleLabel + \' for generated action intent: \' + displayLabel + \'.', $html);
        self::assertStringContainsString('Required \' + roleLabel + \' is present for generated action intent: \' + displayLabel + \'.', $html);
        self::assertStringContainsString('aria-describedby="no-code-action-hint-task_list-update_note"', $html);
        self::assertStringContainsString('Keyboard: Tab to this action, then press Enter or Space to preview the update intent.', $html);
        self::assertStringContainsString('data-operation-key="update_note"', $html);
        self::assertStringContainsString('Write runtime HTML', $html);
        self::assertStringContainsString('Visible in table', $html);
        self::assertStringContainsString('Visible in detail', $html);
        self::assertStringContainsString('<form class="no-code-form" method="post">', $html);
        self::assertStringContainsString('Editable in form', $html);
    }

    public function testRendersRuntimePreviewEmptyStateCopy(): void
    {
        $definition = $this->screenDefinition();
        $list = app_no_code_runtime_render_screen($definition, 'task_list');

        self::assertTrue($list['ok'], $list['error']);

        $html = app_no_code_runtime_render_preview_html([
            'runtime_version' => app_no_code_runtime_version(),
            'definition_version' => $definition['definition_version'] ?? '',
            'project_key' => $definition['project_key'] ?? '',
            'screens' => [
                $list['render'],
            ],
        ]);

        self::assertStringContainsString('No records to show yet.', $html);
        self::assertStringContainsString('data-screen-state="empty"', $html);
        self::assertStringContainsString('data-state="empty">Empty</span>', $html);
        self::assertStringContainsString('class="no-code-empty-row"', $html);
        self::assertStringContainsString('class="no-code-empty-state"', $html);
    }

    public function testDispatchesEnabledActionThroughRuntimeDispatcherIntent(): void
    {
        $capturedIntent = null;
        $result = app_no_code_runtime_dispatch_action(
            $this->screenDefinition(),
            'update_note',
            [
                'id' => 1,
                'note' => 'Updated by no-code runtime',
            ],
            static function (array $intent) use (&$capturedIntent): array {
                $capturedIntent = $intent;

                return [
                    'ok' => true,
                    'operation_key' => $intent['operation_key'] ?? '',
                    'payload' => $intent['payload'] ?? [],
                ];
            },
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertTrue($result['executed']);
        self::assertSame('no-code-runtime-action-intent-v0', $result['intent']['intent_version'] ?? '');
        self::assertSame('update_note', $capturedIntent['operation_key'] ?? '');
        self::assertSame('update', $capturedIntent['operation_type'] ?? '');
        self::assertSame(['id' => 1], $capturedIntent['payload']['key'] ?? []);
        self::assertSame(['note' => 'Updated by no-code runtime'], $capturedIntent['payload']['input'] ?? []);
        self::assertSame('update_note', $result['result']['operation_key'] ?? '');
    }

    public function testDispatchesRuntimeActionThroughManagedOperationServerDbAccessExecutor(): void
    {
        NoCodeRuntimeTestFakeDbAccess::$calls = [];
        $result = app_no_code_runtime_dispatch_action(
            $this->screenDefinition(),
            'update_note',
            [
                'id' => 1,
                'note' => 'Persisted through no-code bridge',
            ],
            app_no_code_managed_operation_dispatcher(
                [
                    'contract_key' => 'task',
                    'storage_mode' => 'local-copy',
                    'origin' => 'app-local',
                    'target' => 'server',
                ],
                static fn (array $syncIntent): array => app_managed_operation_server_dbaccess_execute_intent(
                    $syncIntent,
                    [
                        'endpoint' => 'server',
                        'data_class' => NoCodeRuntimeTestFakeTaskData::class,
                        'dbaccess_class' => NoCodeRuntimeTestFakeDbAccess::class,
                        'method_map' => [
                            'update' => 'UpdateTask',
                        ],
                    ],
                ),
            ),
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertTrue($result['executed']);
        self::assertSame('no-code-runtime-action-intent-v0', $result['intent']['intent_version'] ?? '');
        self::assertSame('managed-operation-sync-intent-v0', $result['result']['sync_intent']['intent_version'] ?? '');
        self::assertSame('task', $result['result']['sync_intent']['contract_key'] ?? '');
        self::assertSame(['id' => 1], $result['result']['sync_intent']['payload']['key'] ?? []);
        self::assertSame(
            ['note' => 'Persisted through no-code bridge'],
            $result['result']['sync_intent']['payload']['input'] ?? [],
        );
        self::assertSame('UpdateTask', $result['result']['executor_result']['method_name'] ?? '');
        self::assertSame(1, NoCodeRuntimeTestFakeDbAccess::$calls[0]['object']->id ?? 0);
        self::assertSame(
            'Persisted through no-code bridge',
            NoCodeRuntimeTestFakeDbAccess::$calls[0]['object']->note ?? '',
        );
    }

    public function testBuildsServerBackedExecutionRequestContractFromPost(): void
    {
        $request = app_no_code_runtime_execution_request_from_post(
            'POST',
            [
                '_csrf' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'action_key' => 'update_note',
                'input' => [
                    'id' => '1',
                    'note' => 'Submitted through runtime endpoint',
                    'ignored_nested' => ['unsafe'],
                    '' => 'ignored blank key',
                ],
            ],
            [
                'csrf_token' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'source_output_key' => 'NO-CODE-RUNTIME',
                'revision_id' => 'rev-runtime-1',
            ],
        );

        self::assertTrue($request['ok'], $request['error']);
        self::assertSame('update_note', $request['action_key']);
        self::assertSame(
            [
                'id' => '1',
                'note' => 'Submitted through runtime endpoint',
            ],
            $request['input'],
        );
        self::assertSame(
            [
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'source_output_key' => 'NO-CODE-RUNTIME',
                'revision_id' => 'rev-runtime-1',
            ],
            $request['binding'],
        );
    }

    public function testServerBackedExecutionRequestFailsClosedBeforeDispatch(): void
    {
        $expectedBinding = [
            'csrf_token' => 'known-token',
            'project_key' => 'RUNTIME-TEST',
            'artifact_key' => 'NO-CODE-RUNTIME-abc123',
        ];

        $notPost = app_no_code_runtime_execution_request_from_post('GET', [], $expectedBinding);
        self::assertFalse($notPost['ok']);
        self::assertSame('runtime execution requires POST', $notPost['error']);

        $badCsrf = app_no_code_runtime_execution_request_from_post('POST', [
            '_csrf' => 'wrong-token',
            'project_key' => 'RUNTIME-TEST',
            'artifact_key' => 'NO-CODE-RUNTIME-abc123',
            'action_key' => 'update_note',
        ], $expectedBinding);
        self::assertFalse($badCsrf['ok']);
        self::assertSame('runtime execution csrf token is invalid', $badCsrf['error']);

        $badBinding = app_no_code_runtime_execution_request_from_post('POST', [
            '_csrf' => 'known-token',
            'project_key' => 'OTHER',
            'artifact_key' => 'NO-CODE-RUNTIME-abc123',
            'action_key' => 'update_note',
        ], $expectedBinding);
        self::assertFalse($badBinding['ok']);
        self::assertSame('runtime execution project binding does not match', $badBinding['error']);

        $missingAction = app_no_code_runtime_execution_request_from_post('POST', [
            '_csrf' => 'known-token',
            'project_key' => 'RUNTIME-TEST',
            'artifact_key' => 'NO-CODE-RUNTIME-abc123',
        ], $expectedBinding);
        self::assertFalse($missingAction['ok']);
        self::assertSame('runtime execution action key is missing', $missingAction['error']);
    }

    public function testExecutesServerBackedRequestOnlyAfterRequestContractPasses(): void
    {
        $dispatchIntents = [];
        $dispatcher = static function (array $intent) use (&$dispatchIntents): array {
            $dispatchIntents[] = $intent;
            return [
                'status' => 'ok',
                'updated' => true,
            ];
        };

        $badRequest = app_no_code_runtime_execute_request_from_post(
            $this->screenDefinition(),
            'POST',
            [
                '_csrf' => 'wrong-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'action_key' => 'update_note',
                'input' => [
                    'id' => '1',
                    'note' => 'Should not dispatch',
                ],
            ],
            [
                'csrf_token' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
            ],
            $dispatcher,
        );

        self::assertFalse($badRequest['ok']);
        self::assertFalse($badRequest['executed']);
        self::assertSame('runtime execution csrf token is invalid', $badRequest['error']);
        self::assertSame([], $dispatchIntents);

        $result = app_no_code_runtime_execute_request_from_post(
            $this->screenDefinition(),
            'POST',
            [
                '_csrf' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'action_key' => 'update_note',
                'input' => [
                    'id' => '1',
                    'note' => 'Dispatch through endpoint helper',
                ],
            ],
            [
                'csrf_token' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'source_output_key' => 'NO-CODE-RUNTIME',
                'revision_id' => 'rev-runtime-1',
            ],
            $dispatcher,
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertTrue($result['executed']);
        self::assertSame('update_note', $result['request']['action_key'] ?? '');
        self::assertSame('rev-runtime-1', $result['request']['binding']['revision_id'] ?? '');
        self::assertSame(['status' => 'ok', 'updated' => true], $result['result']);
        self::assertCount(1, $dispatchIntents);
        self::assertSame(
            [
                'id' => '1',
            ],
            $dispatchIntents[0]['payload']['key'] ?? [],
        );
        self::assertSame(
            [
                'note' => 'Dispatch through endpoint helper',
            ],
            $dispatchIntents[0]['payload']['input'] ?? [],
        );
    }

    public function testBuildsEndpointResponseForServerBackedExecution(): void
    {
        $dispatcher = static function (array $intent): array {
            return [
                'status' => 'ok',
                'operation_key' => $intent['operation_key'] ?? '',
            ];
        };

        $result = app_no_code_runtime_execute_request_from_post(
            $this->screenDefinition(),
            'POST',
            [
                '_csrf' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'action_key' => 'update_note',
                'input' => [
                    'id' => '1',
                    'note' => 'Endpoint response payload',
                ],
            ],
            [
                'csrf_token' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'source_output_key' => 'NO-CODE-RUNTIME',
                'revision_id' => 'rev-runtime-1',
            ],
            $dispatcher,
        );

        $response = app_no_code_runtime_execution_endpoint_response($result);

        self::assertSame(200, $response['status_code']);
        self::assertTrue($response['payload']['ok']);
        self::assertTrue($response['payload']['executed']);
        self::assertSame('', $response['payload']['error']);
        self::assertSame('update_note', $response['payload']['request']['action_key'] ?? '');
        self::assertSame('rev-runtime-1', $response['payload']['request']['binding']['revision_id'] ?? '');
        self::assertSame('update_note', $response['payload']['intent']['operation_key'] ?? '');
        self::assertSame('ok', $response['payload']['result']['status'] ?? '');

        $badCsrf = app_no_code_runtime_execute_request_from_post(
            $this->screenDefinition(),
            'POST',
            [
                '_csrf' => 'wrong-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'action_key' => 'update_note',
            ],
            [
                'csrf_token' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
            ],
            $dispatcher,
        );
        $badCsrfResponse = app_no_code_runtime_execution_endpoint_response($badCsrf);

        self::assertSame(403, $badCsrfResponse['status_code']);
        self::assertFalse($badCsrfResponse['payload']['ok']);
        self::assertFalse($badCsrfResponse['payload']['executed']);
        self::assertSame('runtime execution csrf token is invalid', $badCsrfResponse['payload']['error']);

        $missingRequired = app_no_code_runtime_execute_request_from_post(
            $this->screenDefinition(),
            'POST',
            [
                '_csrf' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
                'action_key' => 'update_note',
                'input' => [
                    'id' => '1',
                ],
            ],
            [
                'csrf_token' => 'known-token',
                'project_key' => 'RUNTIME-TEST',
                'artifact_key' => 'NO-CODE-RUNTIME-abc123',
            ],
            $dispatcher,
        );
        $missingRequiredResponse = app_no_code_runtime_execution_endpoint_response($missingRequired);

        self::assertSame(422, $missingRequiredResponse['status_code']);
        self::assertFalse($missingRequiredResponse['payload']['ok']);
        self::assertFalse($missingRequiredResponse['payload']['executed']);
        self::assertStringContainsString('input.missing:note', $missingRequiredResponse['payload']['error']);
        self::assertSame('Required input is missing: note', $missingRequiredResponse['payload']['message']);
        self::assertSame('update_note', $missingRequiredResponse['payload']['intent']['operation_key'] ?? '');
    }

    public function testDoesNotDispatchDisabledAction(): void
    {
        $called = false;
        $result = app_no_code_runtime_dispatch_action(
            $this->screenDefinition(false),
            'update_note',
            [
                'id' => 1,
                'note' => 'Should not dispatch',
            ],
            static function (array $_intent) use (&$called): array {
                $called = true;

                return ['ok' => true];
            },
        );

        self::assertFalse($result['ok']);
        self::assertFalse($result['executed']);
        self::assertFalse($called);
        self::assertSame('action is not enabled: update_note', $result['error']);
    }

    public function testActionPolicyOverlayCanEnableStoredDisabledRuntimeDefinition(): void
    {
        $storedDefinition = $this->screenDefinition(false);
        $policyDefinition = $this->screenDefinition(true);
        $overlaidDefinition = app_no_code_runtime_definition_with_action_policy_overlay($storedDefinition, $policyDefinition);

        $render = app_no_code_runtime_render_screen($overlaidDefinition, 'task_form');
        self::assertTrue($render['ok'], $render['error']);
        $actionsByKey = $this->indexBy($render['render']['actions'] ?? [], 'action_key');
        self::assertTrue($actionsByKey['update_note']['enabled'] ?? false);
        self::assertSame('enabled', $actionsByKey['update_note']['availability'] ?? '');
        self::assertSame([], $actionsByKey['update_note']['failed_checks'] ?? ['missing']);

        $called = false;
        $result = app_no_code_runtime_dispatch_action(
            $overlaidDefinition,
            'update_note',
            [
                'id' => 1,
                'note' => 'Policy overlay dispatch payload',
            ],
            static function (array $_intent) use (&$called): array {
                $called = true;

                return ['ok' => true];
            },
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertTrue($result['executed']);
        self::assertTrue($called);
    }

    public function testDispatchFailsClosedWhenRequiredActionInputIsMissing(): void
    {
        $called = false;
        $result = app_no_code_runtime_dispatch_action(
            $this->screenDefinition(),
            'update_note',
            [
                'id' => 1,
            ],
            static function (array $_intent) use (&$called): array {
                $called = true;

                return ['ok' => true];
            },
        );

        self::assertFalse($result['ok']);
        self::assertFalse($result['executed']);
        self::assertFalse($called);
        self::assertStringContainsString('input.missing:note', $result['error']);
        self::assertSame('Required input is missing: note', $result['message']);
    }

    public function testDispatchFailsClosedWhenRequiredActionInputIsBlank(): void
    {
        $called = false;
        $result = app_no_code_runtime_dispatch_action(
            $this->screenDefinition(),
            'update_note',
            [
                'id' => 1,
                'note' => '   ',
            ],
            static function (array $_intent) use (&$called): array {
                $called = true;

                return ['ok' => true];
            },
        );

        self::assertFalse($result['ok']);
        self::assertFalse($result['executed']);
        self::assertFalse($called);
        self::assertStringContainsString('input.missing:note', $result['error']);
        self::assertSame('Required input is missing: note', $result['message']);
    }

    /**
     * @return array<string,mixed>
     */
    private function screenDefinition(bool $updateEnabled = true): array
    {
        $result = app_no_code_screen_definition_from_snapshots(
            'RUNTIME-TEST',
            $this->managedScreenManifest(),
            $this->managedOperations(),
            $updateEnabled ? $this->editorPrincipal() : $this->viewerPrincipal(),
        );

        self::assertTrue($result['ok'], $result['error']);

        return $result['definition'];
    }

    /**
     * @return array<string,mixed>
     */
    private function managedScreenManifest(): array
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $manifest['project_key'] = 'RUNTIME-TEST';
        $manifest['contracts'][0]['contract_metadata'] = [
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'source_of_truth' => 'phpunit',
        ];

        foreach ($manifest['contracts'][0]['fields'] as $index => $field) {
            if (($field['physical_name'] ?? '') === 'note') {
                $manifest['contracts'][0]['fields'][$index]['contract_metadata'] = [
                    'operation_role' => 'editable',
                    'source_of_truth' => 'phpunit',
                ];
            }
        }

        return $manifest;
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function managedOperations(): array
    {
        return [
            [
                'project_key' => 'RUNTIME-TEST',
                'operation_key' => 'read_task',
                'contract_key' => 'task',
                'name' => 'Read Task',
                'operation_type' => 'read',
                'status' => 'active',
                'storage_policy' => 'business-only',
                'permission_key' => 'project.read',
                'required_roles' => ['viewer'],
                'required_scopes' => [],
                'required_claims' => [],
                'fields' => [
                    [
                        'field_physical_name' => 'id',
                        'field_role' => 'key',
                        'is_required' => true,
                        'allow_client_write' => false,
                    ],
                ],
            ],
            [
                'project_key' => 'RUNTIME-TEST',
                'operation_key' => 'update_note',
                'contract_key' => 'task',
                'name' => 'Update Note',
                'operation_type' => 'update',
                'status' => 'active',
                'storage_policy' => 'business-only',
                'permission_key' => 'project.edit',
                'required_roles' => ['editor'],
                'required_scopes' => ['task:write'],
                'required_claims' => [],
                'fields' => [
                    [
                        'field_physical_name' => 'id',
                        'field_role' => 'key',
                        'is_required' => true,
                        'allow_client_write' => false,
                    ],
                    [
                        'field_physical_name' => 'note',
                        'field_role' => 'input',
                        'is_required' => true,
                        'allow_client_write' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function editorPrincipal(): array
    {
        return [
            'id' => 'editor-1',
            'display_name' => 'Editor',
            'auth_source' => 'stub',
            'site' => 'local',
            'roles' => ['viewer', 'editor'],
            'project_roles' => [
                'RUNTIME-TEST' => ['viewer', 'editor'],
            ],
            'scopes' => ['task:write'],
            'claims' => [],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function viewerPrincipal(): array
    {
        return [
            'id' => 'viewer-1',
            'display_name' => 'Viewer',
            'auth_source' => 'stub',
            'site' => 'local',
            'roles' => ['viewer'],
            'project_roles' => [
                'RUNTIME-TEST' => ['viewer'],
            ],
            'scopes' => [],
            'claims' => [],
        ];
    }

    /**
     * @param list<array<string,mixed>> $items
     * @return array<string,array<string,mixed>>
     */
    private function indexBy(array $items, string $key): array
    {
        $indexed = [];
        foreach ($items as $item) {
            $indexed[(string) ($item[$key] ?? '')] = $item;
        }

        return $indexed;
    }
}

final class NoCodeRuntimeTestFakeTaskData
{
    public int $id = 0;
    public string $note = '';
}

final class NoCodeRuntimeTestFakeDbAccess
{
    /**
     * @var list<array<string,mixed>>
     */
    public static array $calls = [];

    public function UpdateTask(NoCodeRuntimeTestFakeTaskData $object): array
    {
        self::$calls[] = [
            'method' => 'UpdateTask',
            'object' => $object,
        ];

        return [
            'status' => 'server-updated',
        ];
    }
}
