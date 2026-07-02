<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/app_local_sqlite_dbaccess.php';
require_once dirname(__DIR__, 2) . '/app/app_local_sqlite_schema.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/database.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_app_local_executor.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_server_dbaccess_executor.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_sync_outbox_processor.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_sync_outbox_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/app/no_code_managed_operation_bridge.php';
require_once dirname(__DIR__, 2) . '/app/no_code_runtime.php';
require_once dirname(__DIR__, 2) . '/app/project_db_access_bootstrap_service.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/shared_contract_manifest.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE30_SYNC_PROJECT_KEY = 'SAMPLE30';
const APP_SAMPLE30_SYNC_TABLE_NAME = 'sync_task';
const APP_SAMPLE30_APP_LOCAL_SOURCE_OUTPUT_KEY = 'APP-LOCAL-PERSISTENCE';
const APP_SAMPLE30_NO_CODE_SOURCE_OUTPUT_KEY = 'NO-CODE-RUNTIME';

/**
 * @param list<string> $errors
 */
function app_sample30_no_code_app_local_sync_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @return array{ok:bool,data:array<string,mixed>,error:string}
 */
function app_sample30_no_code_app_local_sync_read_json_file(string $path): array
{
    if (!is_file($path)) {
        return ['ok' => false, 'data' => [], 'error' => 'file was not found: ' . $path];
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'data' => [], 'error' => 'failed to decode JSON file: ' . $path];
    }

    return ['ok' => true, 'data' => $decoded, 'error' => ''];
}

/**
 * @param list<array<string,mixed>> $items
 * @return list<string>
 */
function app_sample30_no_code_app_local_sync_extract_names(array $items, string $key): array
{
    return array_values(array_map(
        static fn (array $item): string => (string) ($item[$key] ?? ''),
        $items,
    ));
}

function app_sample30_no_code_app_local_sync_clear_outbox(array $app): void
{
    $pdo = app_create_config_pdo($app);
    $statement = $pdo->prepare(
        'DELETE outbox
         FROM project_managed_operation_sync_outbox AS outbox
         INNER JOIN projects AS projects
            ON projects.id = outbox.project_id
         WHERE projects.project_key = :project_key'
    );
    if ($statement === false) {
        throw new RuntimeException('failed to prepare sample30 outbox cleanup.');
    }

    $statement->execute([':project_key' => APP_SAMPLE30_SYNC_PROJECT_KEY]);
}

/**
 * @return array<string,mixed>
 */
function app_sample30_no_code_app_local_sync_editor_principal(): array
{
    return [
        'id' => 'sample30-editor',
        'display_name' => 'Sample30 Editor',
        'auth_source' => 'sample-pack',
        'site' => 'local',
        'roles' => [],
        'project_roles' => [
            APP_SAMPLE30_SYNC_PROJECT_KEY => ['editor'],
        ],
        'scopes' => ['sync_task:write'],
        'claims' => [],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     table_name:string,
 *     requested_by:string,
 *     steps:array<string,mixed>,
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample30_no_code_app_local_sync_demo_run(array $app, string $requestedBy): array
{
    $projectKey = APP_SAMPLE30_SYNC_PROJECT_KEY;
    $tableName = APP_SAMPLE30_SYNC_TABLE_NAME;
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'manifest' => null,
        'schema' => null,
        'app_local_artifact' => null,
        'no_code_artifact' => null,
        'screen_definition' => null,
        'local_seed' => null,
        'dispatch' => null,
        'outbox_process' => null,
        'local_read_after_sync' => null,
        'server_runtime_entity' => null,
        'server_binding' => null,
        'server_seed' => null,
        'server_dispatch' => null,
        'server_outbox_process' => null,
        'server_read_after_sync' => null,
        'sync_error_state_dispatch' => null,
        'sync_error_state_process' => null,
        'sync_handoff_visibility' => null,
    ];
    $assertionErrors = [];

    try {
        app_sample30_no_code_app_local_sync_clear_outbox($app);

        $tableImport = app_project_table_import_apply($app, $projectKey, 'live-schema', $tableName);
        $steps['table_import'] = [
            'ok' => $tableImport['ok'],
            'summary' => $tableImport['summary'],
            'errors' => $tableImport['errors'],
            'error' => $tableImport['error'],
        ];
        if (!$tableImport['ok']) {
            throw new RuntimeException('sample30 table import failed.');
        }

        $dataClassSync = app_project_data_class_sync_apply($app, $projectKey);
        $steps['data_class_sync'] = [
            'ok' => $dataClassSync['ok'],
            'summary' => $dataClassSync['summary'],
            'errors' => $dataClassSync['errors'],
            'error' => $dataClassSync['error'],
        ];
        if (!$dataClassSync['ok']) {
            throw new RuntimeException('sample30 data class sync failed.');
        }

        $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
        $steps['manifest'] = [
            'ok' => $manifestResult['ok'],
            'validation' => $manifestResult['validation'],
            'compare' => $manifestResult['compare'],
            'error' => $manifestResult['error'],
        ];
        if (!$manifestResult['ok']) {
            throw new RuntimeException('sample30 shared contract manifest build failed.');
        }
        $manifest = $manifestResult['manifest'];

        $schema = app_local_sqlite_schema_generate($manifest);
        $steps['schema'] = [
            'ok' => $schema['ok'],
            'summary' => $schema['summary'],
            'validation' => $schema['validation'],
            'error' => $schema['error'],
        ];
        if (!$schema['ok']) {
            throw new RuntimeException('sample30 App-local schema generation failed.');
        }

        $appLocalSourceOutput = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE30_APP_LOCAL_SOURCE_OUTPUT_KEY,
        );
        if (!$appLocalSourceOutput['ok'] || $appLocalSourceOutput['item'] === null) {
            throw new RuntimeException(
                $appLocalSourceOutput['error'] !== ''
                    ? $appLocalSourceOutput['error']
                    : 'sample30 App-local source output definition was not found.',
            );
        }
        $appLocalArtifact = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $appLocalSourceOutput['item'],
            $requestedBy,
        );
        if (!$appLocalArtifact['ok'] || $appLocalArtifact['artifact'] === null) {
            throw new RuntimeException('sample30 App-local artifact generation failed: ' . $appLocalArtifact['error']);
        }
        $steps['app_local_artifact'] = [
            'ok' => true,
            'source_output_key' => (string) ($appLocalArtifact['artifact']['source_output_key'] ?? ''),
            'artifact_strategy' => (string) ($appLocalArtifact['artifact']['artifact_strategy'] ?? ''),
            'source_file_count' => (int) ($appLocalArtifact['artifact']['source_file_count'] ?? 0),
        ];

        $noCodeSourceOutput = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE30_NO_CODE_SOURCE_OUTPUT_KEY,
        );
        if (!$noCodeSourceOutput['ok'] || $noCodeSourceOutput['item'] === null) {
            throw new RuntimeException(
                $noCodeSourceOutput['error'] !== ''
                    ? $noCodeSourceOutput['error']
                    : 'sample30 no-code source output definition was not found.',
            );
        }
        $noCodeArtifact = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $noCodeSourceOutput['item'],
            $requestedBy,
        );
        if (!$noCodeArtifact['ok'] || $noCodeArtifact['artifact'] === null) {
            throw new RuntimeException('sample30 no-code artifact generation failed: ' . $noCodeArtifact['error']);
        }
        $noCodePublish = app_project_output_publish_artifact(
            $app,
            $noCodeArtifact['artifact'],
            $noCodeSourceOutput['item'],
        );
        if (!$noCodePublish['ok'] || $noCodePublish['published'] === null) {
            throw new RuntimeException('sample30 no-code artifact publish failed: ' . $noCodePublish['error']);
        }
        $publishedRoot = (string) ($noCodePublish['published']['published_root'] ?? '');
        $steps['no_code_artifact'] = [
            'ok' => true,
            'source_output_key' => (string) ($noCodeArtifact['artifact']['source_output_key'] ?? ''),
            'artifact_strategy' => (string) ($noCodeArtifact['artifact']['artifact_strategy'] ?? ''),
            'source_file_count' => (int) ($noCodeArtifact['artifact']['source_file_count'] ?? 0),
            'published_root' => $publishedRoot,
        ];

        $screenDefinitionJson = app_sample30_no_code_app_local_sync_read_json_file($publishedRoot . '/screen-definition.json');
        if (!$screenDefinitionJson['ok']) {
            throw new RuntimeException($screenDefinitionJson['error']);
        }
        $screenDefinition = $screenDefinitionJson['data'];
        $contracts = is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : [];
        $contract = is_array($contracts[0] ?? null) ? $contracts[0] : [];
        $screens = is_array($contract['screens'] ?? null) ? $contract['screens'] : [];
        $actions = is_array($contract['actions'] ?? null) ? $contract['actions'] : [];
        $listScreen = is_array($screens[0] ?? null) ? $screens[0] : [];
        $fields = is_array($listScreen['fields'] ?? null) ? $listScreen['fields'] : [];
        $runtimePreviewJson = app_sample30_no_code_app_local_sync_read_json_file($publishedRoot . '/runtime-preview.json');
        if (!$runtimePreviewJson['ok']) {
            throw new RuntimeException($runtimePreviewJson['error']);
        }
        $runtimePreview = $runtimePreviewJson['data'];
        $runtimeScreens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];
        $runtimeListScreen = is_array($runtimeScreens[0] ?? null) ? $runtimeScreens[0] : [];
        $runtimePreviewHtml = is_file($publishedRoot . '/runtime-preview.html')
            ? (string) file_get_contents($publishedRoot . '/runtime-preview.html')
            : '';
        $steps['screen_definition'] = [
            'definition_version' => $screenDefinition['definition_version'] ?? '',
            'project_key' => $screenDefinition['project_key'] ?? '',
            'contract_key' => $contract['contract_key'] ?? '',
            'screen_types' => app_sample30_no_code_app_local_sync_extract_names($screens, 'screen_type'),
            'field_keys' => app_sample30_no_code_app_local_sync_extract_names($fields, 'field_key'),
            'action_key' => $actions[0]['action_key'] ?? '',
            'action_availability' => $actions[0]['availability'] ?? '',
        ];

        $authorizedDefinitionResult = app_no_code_screen_definition_from_project(
            $app,
            $projectKey,
            app_sample30_no_code_app_local_sync_editor_principal(),
        );
        if (!$authorizedDefinitionResult['ok']) {
            throw new RuntimeException('sample30 authorized screen definition failed: ' . $authorizedDefinitionResult['error']);
        }
        $authorizedDefinition = $authorizedDefinitionResult['definition'];
        $authorizedContracts = is_array($authorizedDefinition['contracts'] ?? null) ? $authorizedDefinition['contracts'] : [];
        $authorizedContract = is_array($authorizedContracts[0] ?? null) ? $authorizedContracts[0] : [];
        $authorizedActions = is_array($authorizedContract['actions'] ?? null) ? $authorizedContract['actions'] : [];
        $steps['screen_definition']['authorized_action_availability'] = $authorizedActions[0]['availability'] ?? '';

        $localPdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($localPdo, $schema['schema_sql']);
        if (!$apply['ok']) {
            throw new RuntimeException('sample30 App-local schema apply failed: ' . $apply['error']);
        }
        $steps['schema']['apply'] = $apply;

        $localSeedDto = [
            'id' => 3001,
            'title' => 'App-local sync no-code task',
            'status' => 'draft',
            'note' => 'Before no-code sync handoff.',
        ];
        $localSeed = app_local_sqlite_dbaccess_save_dto($localPdo, $manifest, $tableName, $localSeedDto, [
            'dirty' => false,
            'sync_status' => 'clean',
        ]);
        $steps['local_seed'] = $localSeed;
        if (!$localSeed['ok']) {
            throw new RuntimeException('sample30 local seed failed: ' . $localSeed['error']);
        }

        $dispatch = app_no_code_runtime_dispatch_action(
            $authorizedDefinition,
            'update_sync_task',
            [
                'id' => 3001,
                'status' => 'ready_for_sync',
                'note' => 'Updated through no-code App-local sync handoff.',
            ],
            app_no_code_managed_operation_dispatcher(
                [
                    'contract_key' => $tableName,
                    'storage_mode' => 'local-copy',
                    'origin' => 'app-local',
                    'target' => 'server',
                ],
                static function (array $syncIntent) use ($app): array {
                    $enqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $syncIntent);

                    return [
                        'ok' => $enqueue['ok'],
                        'executed' => $enqueue['ok'],
                        'enqueue' => $enqueue['item'],
                        'error' => $enqueue['error'],
                    ];
                },
            ),
        );
        $steps['dispatch'] = $dispatch;
        if (!$dispatch['ok']) {
            throw new RuntimeException('sample30 no-code dispatch failed: ' . $dispatch['error']);
        }
        if (!((bool) ($dispatch['result']['ok'] ?? false))) {
            throw new RuntimeException('sample30 sync intent enqueue failed: ' . (string) ($dispatch['result']['error'] ?? ''));
        }

        $outboxProcess = app_managed_operation_sync_outbox_process_next(
            $app,
            $projectKey,
            app_managed_operation_app_local_outbox_handler($localPdo, $manifest),
        );
        $steps['outbox_process'] = $outboxProcess;
        if (!$outboxProcess['ok']) {
            throw new RuntimeException('sample30 outbox process failed: ' . $outboxProcess['error']);
        }

        $localRead = app_local_sqlite_dbaccess_read_dto($localPdo, $manifest, $tableName, ['id' => 3001]);
        $steps['local_read_after_sync'] = $localRead;
        if (!$localRead['ok']) {
            throw new RuntimeException('sample30 local read after sync failed: ' . $localRead['error']);
        }

        $serverRuntimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity(
            $app,
            $projectKey,
            $tableName,
        );
        $steps['server_runtime_entity'] = $serverRuntimeEntity;
        if (!$serverRuntimeEntity['ok'] || $serverRuntimeEntity['entity'] === null) {
            throw new RuntimeException('sample30 server runtime entity materialize failed: ' . $serverRuntimeEntity['error']);
        }
        $serverEntity = $serverRuntimeEntity['entity'];
        require_once (string) ($serverEntity['data_path'] ?? '');
        require_once (string) ($serverEntity['dbaccess_path'] ?? '');

        $operationSnapshot = app_pdo_fetch_managed_operation_snapshot($app, $projectKey);
        if (!$operationSnapshot['ok']) {
            throw new RuntimeException('sample30 managed operation snapshot failed: ' . $operationSnapshot['error']);
        }
        $operation = is_array($operationSnapshot['items'][0] ?? null) ? $operationSnapshot['items'][0] : [];
        $serverBinding = app_managed_operation_server_dbaccess_binding_from_project_catalog(
            $app,
            $projectKey,
            $operation,
        );
        if (!$serverBinding['ok']) {
            $serverBinding = app_managed_operation_server_dbaccess_binding_from_candidate(
                [
                    'source_name' => (string) ($serverEntity['source_name'] ?? ''),
                    'generated_name' => (string) ($serverEntity['data_class'] ?? '') !== ''
                        ? preg_replace('/Data$/', '', (string) ($serverEntity['data_class'] ?? ''))
                        : '',
                    'data_class' => (string) ($serverEntity['data_class'] ?? ''),
                    'dbaccess_class' => (string) ($serverEntity['dbaccess_class'] ?? ''),
                    'method_catalog' => app_generated_file_method_catalog((string) ($serverEntity['dbaccess_path'] ?? '')),
                ],
                $operation,
                [
                    'source_name' => (string) ($serverEntity['source_name'] ?? ''),
                ],
            );
        }
        $steps['server_binding'] = $serverBinding;
        if (!$serverBinding['ok'] || $serverBinding['binding'] === null) {
            throw new RuntimeException('sample30 server DBAccess binding failed: ' . $serverBinding['error']);
        }

        $serverSqlitePath = sys_get_temp_dir()
            . '/dego-sample30-server-sync-'
            . getmypid()
            . '-'
            . bin2hex(random_bytes(4))
            . '.sqlite';
        $serverPdo = new PDO('sqlite:' . $serverSqlitePath);
        $serverPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $serverPdo->exec(
            'CREATE TABLE sync_task (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                status TEXT NOT NULL,
                note TEXT NOT NULL
            )',
        );
        $serverPdo->prepare('INSERT INTO sync_task (id, title, status, note) VALUES (?, ?, ?, ?)')->execute([
            3001,
            'App-local sync no-code task',
            'draft',
            'Before server sync processing.',
        ]);
        $steps['server_seed'] = [
            'sqlite_path' => $serverSqlitePath,
            'table_name' => $tableName,
            'id' => 3001,
        ];

        global $mtooldb;
        $mtooldb = null;
        $previousRuntimeSqlitePath = getenv('MTOOL_RUNTIME_SQLITE_PATH');
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $serverSqlitePath);

        try {
            $serverDispatch = app_no_code_runtime_dispatch_action(
                $authorizedDefinition,
                'update_sync_task',
                [
                    'id' => 3001,
                    'status' => 'synced_to_server',
                    'note' => 'Processed by generated server DBAccess handler.',
                ],
                app_no_code_managed_operation_dispatcher(
                    [
                        'contract_key' => $tableName,
                        'storage_mode' => 'local-copy',
                        'origin' => 'app-local',
                        'target' => 'server',
                    ],
                    static function (array $syncIntent) use ($app): array {
                        $enqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $syncIntent);

                        return [
                            'ok' => $enqueue['ok'],
                            'executed' => $enqueue['ok'],
                            'enqueue' => $enqueue['item'],
                            'error' => $enqueue['error'],
                        ];
                    },
                ),
            );
            $steps['server_dispatch'] = $serverDispatch;
            if (!$serverDispatch['ok']) {
                throw new RuntimeException('sample30 server no-code dispatch failed: ' . $serverDispatch['error']);
            }
            if (!((bool) ($serverDispatch['result']['ok'] ?? false))) {
                throw new RuntimeException('sample30 server sync intent enqueue failed: ' . (string) ($serverDispatch['result']['error'] ?? ''));
            }

            $serverOutboxProcess = app_managed_operation_sync_outbox_process_next(
                $app,
                $projectKey,
                app_managed_operation_server_dbaccess_outbox_handler($serverBinding['binding']),
            );
            $steps['server_outbox_process'] = $serverOutboxProcess;
            if (!$serverOutboxProcess['ok']) {
                throw new RuntimeException('sample30 server outbox process failed: ' . $serverOutboxProcess['error']);
            }

            $serverReadStatement = $serverPdo->prepare('SELECT title, status, note FROM sync_task WHERE id = ?');
            if ($serverReadStatement === false) {
                throw new RuntimeException('sample30 server read prepare failed.');
            }
            $serverReadStatement->execute([3001]);
            $serverRow = $serverReadStatement->fetch(PDO::FETCH_ASSOC);
            $steps['server_read_after_sync'] = [
                'ok' => is_array($serverRow),
                'row' => is_array($serverRow) ? $serverRow : [],
            ];
            if (!is_array($serverRow)) {
                throw new RuntimeException('sample30 server row was not found after sync.');
            }

            $syncErrorStateDispatch = app_no_code_runtime_dispatch_action(
                $authorizedDefinition,
                'update_sync_task',
                [
                    'id' => 3002,
                    'status' => 'queued_for_failed_sync',
                    'note' => 'Exercise deterministic failed sync visibility.',
                ],
                app_no_code_managed_operation_dispatcher(
                    [
                        'contract_key' => $tableName,
                        'storage_mode' => 'local-copy',
                        'origin' => 'app-local',
                        'target' => 'server',
                    ],
                    static function (array $syncIntent) use ($app): array {
                        $enqueue = app_pdo_enqueue_managed_operation_sync_intent($app, $syncIntent);

                        return [
                            'ok' => $enqueue['ok'],
                            'executed' => $enqueue['ok'],
                            'enqueue' => $enqueue['item'],
                            'error' => $enqueue['error'],
                        ];
                    },
                ),
            );
            $steps['sync_error_state_dispatch'] = $syncErrorStateDispatch;
            if (!$syncErrorStateDispatch['ok']) {
                throw new RuntimeException('sample30 sync error-state dispatch failed: ' . $syncErrorStateDispatch['error']);
            }
            if (!((bool) ($syncErrorStateDispatch['result']['ok'] ?? false))) {
                throw new RuntimeException('sample30 sync error-state intent enqueue failed: ' . (string) ($syncErrorStateDispatch['result']['error'] ?? ''));
            }

            $syncErrorStateProcess = app_managed_operation_sync_outbox_process_next(
                $app,
                $projectKey,
                static fn (array $item): array => [
                    'ok' => false,
                    'error' => 'sample30 deterministic sync failure for visibility.',
                    'dedupe_key' => (string) ($item['dedupe_key'] ?? ''),
                ],
            );
            $steps['sync_error_state_process'] = $syncErrorStateProcess;
            if (!$syncErrorStateProcess['ok']) {
                throw new RuntimeException('sample30 sync error-state process failed: ' . $syncErrorStateProcess['error']);
            }
        } finally {
            if ($previousRuntimeSqlitePath === false) {
                putenv('MTOOL_RUNTIME_SQLITE_PATH');
            } else {
                putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $previousRuntimeSqlitePath);
            }
            $mtooldb = null;
            $serverPdo = null;
            @unlink($serverSqlitePath);
        }

        $steps['sync_handoff_visibility'] = [
            'ok' => true,
            'app_local' => [
                'handoff_state' => ($outboxProcess['outcome'] ?? '') === 'done' ? 'processed' : 'not_processed',
                'outbox_status' => (string) ($outboxProcess['item']['status'] ?? ''),
                'row_status' => (string) ($localRead['dto']['status'] ?? ''),
                'row_sync_status' => (string) ($localRead['local_metadata']['sync_status'] ?? ''),
                'dirty' => (int) ($localRead['local_metadata']['dirty'] ?? 0),
            ],
            'server' => [
                'handoff_state' => ($steps['server_outbox_process']['outcome'] ?? '') === 'done' ? 'processed' : 'not_processed',
                'outbox_status' => (string) ($steps['server_outbox_process']['item']['status'] ?? ''),
                'handler_method' => (string) ($steps['server_outbox_process']['handler_result']['method_name'] ?? ''),
                'row_status' => (string) ($steps['server_read_after_sync']['row']['status'] ?? ''),
                'title_preserved' => (string) ($steps['server_read_after_sync']['row']['title'] ?? '') === 'App-local sync no-code task',
            ],
            'runtime_artifact' => [
                'list_sync_status_hint' => (bool) ($listScreen['sync_status_hint'] ?? false),
                'detail_sync_status_hint' => (bool) ($screens[1]['sync_status_hint'] ?? false),
                'form_sync_status_hint' => (bool) ($screens[2]['sync_status_hint'] ?? false),
                'list_sync_error_retry_hint' => (string) ($runtimeListScreen['sync_error_retry_hint'] ?? ''),
                'html_sync_retry_hint_visible' => str_contains($runtimePreviewHtml, 'data-sync-retry-hint="operator-outbox"'),
            ],
            'error_state' => [
                'handoff_state' => ($steps['sync_error_state_process']['outcome'] ?? '') === 'failed' ? 'failed' : 'not_failed',
                'outbox_status' => (string) ($steps['sync_error_state_process']['item']['status'] ?? ''),
                'attempts' => (int) ($steps['sync_error_state_process']['item']['attempts'] ?? 0),
                'last_error' => (string) ($steps['sync_error_state_process']['item']['last_error'] ?? ''),
            ],
        ];

        app_sample30_no_code_app_local_sync_assert_same('no-code-screen-definition-v0', $steps['screen_definition']['definition_version'] ?? '', 'definition_version', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same($projectKey, $steps['screen_definition']['project_key'] ?? '', 'project_key', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same($tableName, $steps['screen_definition']['contract_key'] ?? '', 'contract_key', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(['list', 'detail', 'form'], $steps['screen_definition']['screen_types'] ?? [], 'screen types', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(['id', 'title', 'status', 'note'], $steps['screen_definition']['field_keys'] ?? [], 'field keys', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('update_sync_task', $steps['screen_definition']['action_key'] ?? '', 'action key', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('disabled', $steps['screen_definition']['action_availability'] ?? '', 'preview action availability', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('enabled', $steps['screen_definition']['authorized_action_availability'] ?? '', 'authorized action availability', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(APP_SAMPLE30_APP_LOCAL_SOURCE_OUTPUT_KEY, $steps['app_local_artifact']['source_output_key'] ?? '', 'app local artifact key', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('app-local-persistence-php', $steps['app_local_artifact']['artifact_strategy'] ?? '', 'app local artifact strategy', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('managed-operation-sync-intent-v0', $dispatch['result']['sync_intent']['intent_version'] ?? '', 'sync intent version', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('pending', $dispatch['result']['sync_intent']['status'] ?? '', 'sync intent status', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('local-copy', $dispatch['result']['sync_intent']['storage_mode'] ?? '', 'sync intent storage mode', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('app-local', $dispatch['result']['sync_intent']['origin'] ?? '', 'sync intent origin', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('server', $dispatch['result']['sync_intent']['target'] ?? '', 'sync intent target', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('done', $outboxProcess['outcome'] ?? '', 'outbox outcome', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('done', $outboxProcess['item']['status'] ?? '', 'outbox status', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('ready_for_sync', $localRead['dto']['status'] ?? '', 'local status after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('Updated through no-code App-local sync handoff.', $localRead['dto']['note'] ?? '', 'local note after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(1, $localRead['local_metadata']['dirty'] ?? 0, 'local dirty after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('dirty', $localRead['local_metadata']['sync_status'] ?? '', 'local sync status after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('canonical-bootstrap', $steps['server_runtime_entity']['entity']['source_kind'] ?? '', 'server runtime entity source kind', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('SyncTaskData', $steps['server_binding']['binding']['data_class'] ?? '', 'server binding data class', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('SyncTaskDBAccess', $steps['server_binding']['binding']['dbaccess_class'] ?? '', 'server binding DBAccess class', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(['update' => 'Updatesync_task'], $steps['server_binding']['binding']['method_map'] ?? [], 'server binding method map', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('managed-operation-sync-intent-v0', $steps['server_dispatch']['result']['sync_intent']['intent_version'] ?? '', 'server sync intent version', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('done', $steps['server_outbox_process']['outcome'] ?? '', 'server outbox outcome', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('Updatesync_task', $steps['server_outbox_process']['handler_result']['method_name'] ?? '', 'server handler method name', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('App-local sync no-code task', $steps['server_read_after_sync']['row']['title'] ?? '', 'server title after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('synced_to_server', $steps['server_read_after_sync']['row']['status'] ?? '', 'server status after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('Processed by generated server DBAccess handler.', $steps['server_read_after_sync']['row']['note'] ?? '', 'server note after sync', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('processed', $steps['sync_handoff_visibility']['app_local']['handoff_state'] ?? '', 'App-local handoff visibility state', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('processed', $steps['sync_handoff_visibility']['server']['handoff_state'] ?? '', 'server handoff visibility state', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(true, $steps['sync_handoff_visibility']['runtime_artifact']['list_sync_status_hint'] ?? false, 'runtime list sync status hint', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(true, $steps['sync_handoff_visibility']['runtime_artifact']['detail_sync_status_hint'] ?? false, 'runtime detail sync status hint', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(false, $steps['sync_handoff_visibility']['runtime_artifact']['form_sync_status_hint'] ?? true, 'runtime form sync status hint', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('Failed or retryable sync items are reviewed from the operator sync outbox.', $steps['sync_handoff_visibility']['runtime_artifact']['list_sync_error_retry_hint'] ?? '', 'runtime list sync error/retry hint', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(true, $steps['sync_handoff_visibility']['runtime_artifact']['html_sync_retry_hint_visible'] ?? false, 'runtime html sync retry hint', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('failed', $steps['sync_error_state_process']['outcome'] ?? '', 'sync error-state process outcome', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('failed', $steps['sync_error_state_process']['item']['status'] ?? '', 'sync error-state outbox status', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same(1, $steps['sync_error_state_process']['item']['attempts'] ?? 0, 'sync error-state attempts', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('sample30 deterministic sync failure for visibility.', $steps['sync_error_state_process']['item']['last_error'] ?? '', 'sync error-state last error', $assertionErrors);
        app_sample30_no_code_app_local_sync_assert_same('failed', $steps['sync_handoff_visibility']['error_state']['handoff_state'] ?? '', 'sync error-state visibility state', $assertionErrors);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => $assertionErrors === [],
        'project_key' => $projectKey,
        'table_name' => $tableName,
        'requested_by' => $requestedBy,
        'steps' => $steps,
        'assertion_errors' => $assertionErrors,
        'error' => $assertionErrors === []
            ? ''
            : 'sample30 no-code App-local sync demo assertions failed.',
    ];
}
