<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/app_local_sqlite_dbaccess.php';
require_once dirname(__DIR__, 2) . '/app/app_local_sqlite_schema.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/database.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_app_local_executor.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_sync_outbox_processor.php';
require_once dirname(__DIR__, 2) . '/app/managed_operation_sync_outbox_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/app/no_code_managed_operation_bridge.php';
require_once dirname(__DIR__, 2) . '/app/no_code_runtime.php';
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
