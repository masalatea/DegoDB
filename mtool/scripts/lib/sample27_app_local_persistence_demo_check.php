<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/app_local_sqlite_dbaccess.php';
require_once dirname(__DIR__, 2) . '/app/app_local_sqlite_schema.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/database.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/shared_contract_manifest.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE27_APP_LOCAL_PROJECT_KEY = 'SAMPLE27';
const APP_SAMPLE27_APP_LOCAL_TABLE_NAME = 'app_local_task';
const APP_SAMPLE27_APP_LOCAL_SOURCE_OUTPUT_KEY = 'APP-LOCAL-PERSISTENCE';

function app_sample27_app_local_persistence_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample27-app-local-persistence-demo');
}

/**
 * @param list<string> $errors
 */
function app_sample27_app_local_persistence_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @param array<string,mixed> $manifest
 * @return array<string,mixed>
 */
function app_sample27_app_local_persistence_read_server_dto(array $app, array $manifest, int $id): array
{
    $contract = $manifest['contracts'][0] ?? null;
    if (!is_array($contract)) {
        throw new RuntimeException('sample27 manifest contract was not found.');
    }

    $pdo = app_create_config_pdo($app);
    $statement = $pdo->prepare('SELECT * FROM app_local_task WHERE id = :id');
    if ($statement === false) {
        throw new RuntimeException('failed to prepare sample27 server read.');
    }
    $statement->execute([':id' => $id]);
    $row = $statement->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) {
        throw new RuntimeException('sample27 server row was not found: ' . (string) $id);
    }

    $dto = [];
    foreach (($contract['fields'] ?? []) as $field) {
        if (!is_array($field)) {
            continue;
        }

        $generatedName = (string) ($field['generated_name'] ?? '');
        $physicalName = (string) ($field['physical_name'] ?? '');
        $value = $row[$physicalName] ?? null;
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
function app_sample27_app_local_persistence_run(array $app, string $requestedBy): array
{
    $projectKey = APP_SAMPLE27_APP_LOCAL_PROJECT_KEY;
    $tableName = APP_SAMPLE27_APP_LOCAL_TABLE_NAME;
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'manifest' => null,
        'schema' => null,
        'source_output_artifact' => null,
        'server_dto' => null,
        'save' => null,
        'read' => null,
    ];
    $assertionErrors = [];

    try {
        $tableImport = app_project_table_import_apply($app, $projectKey, 'live-schema', $tableName);
        $steps['table_import'] = [
            'ok' => $tableImport['ok'],
            'summary' => $tableImport['summary'],
            'errors' => $tableImport['errors'],
            'error' => $tableImport['error'],
        ];
        if (!$tableImport['ok']) {
            throw new RuntimeException('sample27 table import failed.');
        }

        $dataClassSync = app_project_data_class_sync_apply($app, $projectKey);
        $steps['data_class_sync'] = [
            'ok' => $dataClassSync['ok'],
            'summary' => $dataClassSync['summary'],
            'errors' => $dataClassSync['errors'],
            'error' => $dataClassSync['error'],
        ];
        if (!$dataClassSync['ok']) {
            throw new RuntimeException('sample27 data class sync failed.');
        }

        $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
        $steps['manifest'] = [
            'ok' => $manifestResult['ok'],
            'validation' => $manifestResult['validation'],
            'compare' => $manifestResult['compare'],
            'error' => $manifestResult['error'],
        ];
        if (!$manifestResult['ok']) {
            throw new RuntimeException('sample27 shared contract manifest build failed.');
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
            throw new RuntimeException('sample27 App-local schema generation failed.');
        }

        $sourceOutputResult = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE27_APP_LOCAL_SOURCE_OUTPUT_KEY,
        );
        if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
            throw new RuntimeException(
                $sourceOutputResult['error'] !== ''
                    ? $sourceOutputResult['error']
                    : 'sample27 App-local source output definition was not found.',
            );
        }

        $artifactResult = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $sourceOutputResult['item'],
            $requestedBy,
        );
        if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
            throw new RuntimeException('sample27 App-local source output artifact generation failed: ' . $artifactResult['error']);
        }
        $artifact = $artifactResult['artifact'];
        $steps['source_output_artifact'] = [
            'ok' => true,
            'source_output_key' => (string) ($artifact['source_output_key'] ?? ''),
            'artifact_strategy' => (string) ($artifact['artifact_strategy'] ?? ''),
            'runtime_source_relative_path' => (string) ($artifact['runtime_source_relative_path'] ?? ''),
            'source_file_count' => (int) ($artifact['source_file_count'] ?? 0),
        ];

        $localPdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($localPdo, $schema['schema_sql']);
        if (!$apply['ok']) {
            throw new RuntimeException('sample27 App-local schema apply failed: ' . $apply['error']);
        }
        $steps['schema']['apply'] = $apply;

        $serverDto = app_sample27_app_local_persistence_read_server_dto($app, $manifest, 1001);
        $steps['server_dto'] = $serverDto;
        app_sample27_app_local_persistence_assert_same(
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

        $save = app_local_sqlite_dbaccess_save_dto($localPdo, $manifest, $tableName, $serverDto);
        $steps['save'] = $save;
        if (!$save['ok']) {
            throw new RuntimeException('sample27 App-local DTO save failed: ' . $save['error']);
        }

        $read = app_local_sqlite_dbaccess_read_dto($localPdo, $manifest, $tableName, ['id' => 1001]);
        $steps['read'] = $read;
        if (!$read['ok']) {
            throw new RuntimeException('sample27 App-local DTO read failed: ' . $read['error']);
        }

        app_sample27_app_local_persistence_assert_same($serverDto, $read['dto'], 'App-local read DTO', $assertionErrors);
        app_sample27_app_local_persistence_assert_same(1, $read['local_metadata']['dirty'] ?? null, 'local metadata dirty', $assertionErrors);
        app_sample27_app_local_persistence_assert_same('dirty', $read['local_metadata']['sync_status'] ?? null, 'local metadata sync_status', $assertionErrors);
        app_sample27_app_local_persistence_assert_same(0, $read['local_metadata']['tombstone'] ?? null, 'local metadata tombstone', $assertionErrors);
        app_sample27_app_local_persistence_assert_same(APP_SAMPLE27_APP_LOCAL_SOURCE_OUTPUT_KEY, $steps['source_output_artifact']['source_output_key'] ?? null, 'source output artifact key', $assertionErrors);
        app_sample27_app_local_persistence_assert_same('app-local-persistence-php', $steps['source_output_artifact']['artifact_strategy'] ?? null, 'source output artifact strategy', $assertionErrors);
        app_sample27_app_local_persistence_assert_same(5, $steps['source_output_artifact']['source_file_count'] ?? null, 'source output artifact file count', $assertionErrors);

        $ok = $assertionErrors === [];

        return [
            'ok' => $ok,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => $ok ? '' : 'sample27 App-local persistence round trip check failed.',
        ];
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
}
