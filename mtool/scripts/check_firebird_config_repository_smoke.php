#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/config_db_bootstrap.php';
require_once dirname(__DIR__) . '/app/audit_log_repository_pdo.php';
require_once dirname(__DIR__) . '/app/db_access_repository_pdo.php';
require_once dirname(__DIR__) . '/app/project_repository_pdo.php';
require_once dirname(__DIR__) . '/app/source_output_repository_pdo.php';

/**
 * @param list<string> $argv
 * @return array{help:bool,pretty:bool,error:string}
 */
function app_cli_firebird_config_repository_smoke_parse_args(array $argv): array
{
    $parsed = [
        'help' => false,
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

        $parsed['error'] = 'unsupported argument: ' . $argument;
        return $parsed;
    }

    return $parsed;
}

function app_cli_firebird_config_repository_smoke_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_firebird_config_repository_smoke.php [--pretty]

Exercise representative Mtool config-store repository read/write behavior against an opt-in Firebird profile.
TEXT;
}

/**
 * @return array<string,mixed>
 */
function app_firebird_config_repository_smoke(): array
{
    $app = app_bootstrap();
    $configDb = $app['config_db'] ?? [];
    if (!is_array($configDb) || ($configDb['driver'] ?? '') !== 'firebird') {
        return app_firebird_config_repository_smoke_result(false, 'preflight', 'APP_CONFIG_STORE_DRIVER=firebird is required.', [
            'driver' => is_array($configDb) ? (string) ($configDb['driver'] ?? '') : '',
        ]);
    }

    $bootstrap = app_config_db_bootstrap_apply($app);
    if (!$bootstrap['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'bootstrap', $bootstrap['error'], [
            'bootstrap' => $bootstrap,
        ]);
    }

    $suffix = date('YmdHis') . '_' . bin2hex(random_bytes(4));
    $projectKey = 'FIREBIRD_CONFIG_REPOSITORY_SMOKE_' . strtoupper(str_replace('_', '', substr($suffix, -8)));
    $projectInsert = app_pdo_insert_project($app, [
        'project_key' => $projectKey,
        'name' => 'Firebird Config Repository Smoke',
        'slug' => strtolower($projectKey),
        'lifecycle_status' => 'draft',
        'owner_login_id' => 'firebird-smoke',
        'php_namespace' => 'Firebird\\Smoke',
        'description' => str_repeat('project-description-', 128),
    ]);
    if (!$projectInsert['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'project_insert', $projectInsert['error'], [
            'bootstrap_summary' => $bootstrap['summary'],
            'project_key' => $projectKey,
        ]);
    }

    $projectUpdate = app_pdo_update_project($app, [
        'project_key' => $projectKey,
        'name' => 'Firebird Config Repository Smoke Updated',
        'slug' => strtolower($projectKey) . '-updated',
        'lifecycle_status' => 'active',
        'php_namespace' => 'Firebird\\Smoke\\Updated',
        'description' => str_repeat('project-updated-description-', 96),
    ]);
    if (!$projectUpdate['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'project_update', $projectUpdate['error'], [
            'project_key' => $projectKey,
        ]);
    }

    $projectFetch = app_pdo_fetch_project_by_key($app, $projectKey);
    if (!$projectFetch['ok'] || !is_array($projectFetch['item'])) {
        return app_firebird_config_repository_smoke_result(false, 'project_fetch', $projectFetch['error'], [
            'project_key' => $projectKey,
        ]);
    }
    if (($projectFetch['item']['lifecycle_status'] ?? '') !== 'active' || (int) ($projectFetch['item']['member_count'] ?? 0) < 1) {
        return app_firebird_config_repository_smoke_result(false, 'project_round_trip', 'project insert/update/fetch did not round-trip expected values.', [
            'project_key' => $projectKey,
            'project' => $projectFetch['item'],
        ]);
    }

    $sourceOutputKey = 'FIREBIRD-SOURCE-OUTPUT';
    $sourceOutputCreateInput = [
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'name' => 'Firebird SourceOutput Smoke',
        'program_language' => 'php',
        'class_type' => 'DBAccess',
        'release_target_type' => 'Release',
        'source_template_dir' => 'templates/firebird',
        'source_output_dir' => 'work/source-outputs/firebird',
        'source_temp_output_dir' => 'work/source-outputs/firebird/tmp',
        'proxy_base_url' => '',
        'autoload_filename_suffix' => '.php',
        'source_text_char_code' => 'UTF-8',
        'runtime_source_relative_path' => 'mtool/firebird-config-repository-smoke/source-output',
        'artifact_strategy' => 'generated-bootstrap-dbclasses',
        'target_binding_type' => 'runtime',
        'spec_visibility' => 'internal-only',
        'output_archive_format' => 'tar.gz',
        'source_output_list_order' => '10',
        'notes' => str_repeat('source-output-notes-', 128),
        'source_of_truth' => 'firebird-smoke',
    ];
    $sourceOutputCreate = app_pdo_create_project_source_output($app, $sourceOutputCreateInput);
    if (!$sourceOutputCreate['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'source_output_create', $sourceOutputCreate['error'], [
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
        ]);
    }

    $sourceOutputUpdateInput = $sourceOutputCreateInput;
    $sourceOutputUpdateInput['name'] = 'Firebird SourceOutput Smoke Updated';
    $sourceOutputUpdateInput['source_output_list_order'] = '20';
    $sourceOutputUpdateInput['notes'] = str_repeat('source-output-updated-notes-', 96);
    $sourceOutputUpdate = app_pdo_update_project_source_output($app, $sourceOutputUpdateInput);
    if (!$sourceOutputUpdate['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'source_output_update', $sourceOutputUpdate['error'], [
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
        ]);
    }

    $sourceOutputFetch = app_pdo_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
    if (!$sourceOutputFetch['ok'] || !is_array($sourceOutputFetch['item'])) {
        return app_firebird_config_repository_smoke_result(false, 'source_output_fetch', $sourceOutputFetch['error'], [
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
        ]);
    }
    if (($sourceOutputFetch['item']['name'] ?? '') !== $sourceOutputUpdateInput['name']
        || ($sourceOutputFetch['item']['notes'] ?? '') !== $sourceOutputUpdateInput['notes']
    ) {
        return app_firebird_config_repository_smoke_result(false, 'source_output_round_trip', 'source output create/update/fetch did not round-trip expected values.', [
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output' => $sourceOutputFetch['item'],
        ]);
    }

    $sourceOutputCatalog = app_pdo_fetch_project_source_output_catalog($app, $projectKey);
    if (!$sourceOutputCatalog['ok'] || count($sourceOutputCatalog['items']) < 1) {
        return app_firebird_config_repository_smoke_result(false, 'source_output_catalog', $sourceOutputCatalog['error'], [
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
        ]);
    }

    $dbAccessSourceName = 'FirebirdSmokeAccess';
    $dbAccessFunctionName = 'SelectFirebirdSmokeRows';
    $dbAccessClassInput = [
        'project_key' => $projectKey,
        'source_name' => $dbAccessSourceName,
        'store_base_path' => 'app/dbaccess/firebird',
        'is_autoload' => '1',
        'notes' => str_repeat('dbaccess-class-notes-', 96),
        'source_of_truth' => 'firebird-smoke',
        'last_detected_dbaccess_file' => 'FirebirdSmokeAccess.php',
        'last_detected_data_file' => 'FirebirdSmokeData.php',
    ];
    $dbAccessClassUpsert = app_pdo_upsert_db_access_class_metadata($app, $dbAccessClassInput);
    if (!$dbAccessClassUpsert['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_class_upsert', $dbAccessClassUpsert['error'], [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
        ]);
    }

    $dbAccessClassFetch = app_pdo_fetch_db_access_class_metadata($app, $projectKey, $dbAccessSourceName);
    if (!$dbAccessClassFetch['ok'] || !is_array($dbAccessClassFetch['item'])) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_class_fetch', $dbAccessClassFetch['error'], [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
        ]);
    }
    if (($dbAccessClassFetch['item']['notes'] ?? '') !== $dbAccessClassInput['notes']
        || ($dbAccessClassFetch['item']['is_autoload'] ?? '') !== '1'
    ) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_class_round_trip', 'DBAccess class metadata did not round-trip expected values.', [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
            'db_access_class' => $dbAccessClassFetch['item'],
        ]);
    }

    $dbAccessFunctionInput = [
        'project_key' => $projectKey,
        'source_name' => $dbAccessSourceName,
        'function_name' => $dbAccessFunctionName,
        'function_list_order' => '10',
        'function_suffix' => 'List',
        'action_type' => 'SELECT',
        'data_class_base_name' => 'FirebirdSmokeData',
        'target_table_name' => 'firebird_smoke_rows',
        'parameter_type' => 'Array',
        'select_by_distinct' => '0',
        'sort_order_columns' => 'id ASC',
        'memo' => str_repeat('dbaccess-function-memo-', 96),
        'limit_parameter_type' => 'none',
        'limit_fixed_parameter' => '',
        'or_group_type' => 'none',
        'single_proxy_auth_type' => 'none',
        'single_proxy_single_get_function_name' => '',
        'is_blob_target' => '0',
        'detected_signature' => 'public function SelectFirebirdSmokeRows(array $params): array',
        'detected_line' => '123',
        'source_of_truth' => 'firebird-smoke',
        'last_detected_dbaccess_file' => 'FirebirdSmokeAccess.php',
        'last_detected_data_file' => 'FirebirdSmokeData.php',
    ];
    $dbAccessFunctionUpsert = app_pdo_upsert_db_access_function_metadata($app, $dbAccessFunctionInput);
    if (!$dbAccessFunctionUpsert['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_function_upsert', $dbAccessFunctionUpsert['error'], [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
            'function_name' => $dbAccessFunctionName,
        ]);
    }

    $dbAccessFunctionFetch = app_pdo_fetch_db_access_function_metadata($app, $projectKey, $dbAccessSourceName, $dbAccessFunctionName);
    if (!$dbAccessFunctionFetch['ok'] || !is_array($dbAccessFunctionFetch['item'])) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_function_fetch', $dbAccessFunctionFetch['error'], [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
            'function_name' => $dbAccessFunctionName,
        ]);
    }
    if (($dbAccessFunctionFetch['item']['action_type'] ?? '') !== 'SELECT'
        || ($dbAccessFunctionFetch['item']['memo'] ?? '') !== $dbAccessFunctionInput['memo']
        || ($dbAccessFunctionFetch['item']['detected_line'] ?? '') !== '123'
    ) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_function_round_trip', 'DBAccess function metadata did not round-trip expected values.', [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
            'function_name' => $dbAccessFunctionName,
            'db_access_function' => $dbAccessFunctionFetch['item'],
        ]);
    }

    $dbAccessClassCatalog = app_pdo_fetch_db_access_class_metadata_catalog($app, $projectKey);
    if (!$dbAccessClassCatalog['ok'] || count($dbAccessClassCatalog['items']) < 1) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_class_catalog', $dbAccessClassCatalog['error'], [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
        ]);
    }

    $dbAccessFunctionCatalog = app_pdo_fetch_db_access_function_metadata_catalog($app, $projectKey, $dbAccessSourceName);
    if (!$dbAccessFunctionCatalog['ok'] || count($dbAccessFunctionCatalog['items']) < 1) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_function_catalog', $dbAccessFunctionCatalog['error'], [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
            'function_name' => $dbAccessFunctionName,
        ]);
    }

    $dbAccessBlobContext = app_pdo_fetch_db_access_function_blob_target_context(
        app_create_metadata_pdo($app),
        $projectKey,
        $dbAccessSourceName,
        $dbAccessFunctionName,
    );
    if (($dbAccessBlobContext['action_type'] ?? '') !== 'SELECT'
        || ($dbAccessBlobContext['last_detected_dbaccess_file'] ?? '') !== $dbAccessClassInput['last_detected_dbaccess_file']
    ) {
        return app_firebird_config_repository_smoke_result(false, 'db_access_blob_context', 'DBAccess blob-target context did not round-trip expected values.', [
            'project_key' => $projectKey,
            'source_name' => $dbAccessSourceName,
            'function_name' => $dbAccessFunctionName,
            'blob_context' => $dbAccessBlobContext,
        ]);
    }

    $eventKey = 'firebird_config_repo_' . $suffix;
    $payload = [
        'event_key' => $eventKey,
        'actor_login_id' => 'firebird-smoke',
        'actor_source' => 'docker-smoke',
        'project_key' => $projectKey,
        'event_type' => 'firebird.config.repository.smoke',
        'target_type' => 'config-store',
        'target_key' => 'audit_events',
        'result' => 'success',
        'message' => 'Firebird config repository smoke',
        'metadata' => [
            'driver' => 'firebird',
            'blob_text_probe' => str_repeat('metadata-json-', 128),
            'nested' => [
                'ok' => true,
                'token' => 'must-redact',
            ],
        ],
    ];

    $append = app_pdo_audit_log_append($app, $payload);
    if (!$append['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'audit_append', $append['error'], [
            'bootstrap_summary' => $bootstrap['summary'],
        ]);
    }

    $latest = app_pdo_audit_log_fetch_latest($app, [
        'event_type' => 'firebird.config.repository.smoke',
        'limit' => 5,
    ]);
    if (!$latest['ok']) {
        return app_firebird_config_repository_smoke_result(false, 'audit_latest', $latest['error'], [
            'event_key' => $eventKey,
        ]);
    }

    $matched = null;
    foreach ($latest['items'] as $item) {
        if (($item['event_key'] ?? '') === $eventKey) {
            $matched = $item;
            break;
        }
    }
    if (!is_array($matched)) {
        return app_firebird_config_repository_smoke_result(false, 'audit_latest_match', 'inserted audit event was not returned by latest query.', [
            'event_key' => $eventKey,
            'latest_count' => count($latest['items']),
        ]);
    }

    $metadata = $matched['metadata'] ?? [];
    if (!is_array($metadata) || ($metadata['blob_text_probe'] ?? '') !== $payload['metadata']['blob_text_probe']) {
        return app_firebird_config_repository_smoke_result(false, 'blob_text_round_trip', 'metadata_json did not round-trip through Firebird BLOB text.', [
            'event_key' => $eventKey,
            'metadata' => $metadata,
        ]);
    }

    return app_firebird_config_repository_smoke_result(true, 'ok', '', [
        'bootstrap_summary' => $bootstrap['summary'],
        'project_key' => $projectKey,
        'project_member_count' => (int) ($projectFetch['item']['member_count'] ?? 0),
        'source_output_key' => $sourceOutputKey,
        'source_output_catalog_count' => count($sourceOutputCatalog['items']),
        'db_access_source_name' => $dbAccessSourceName,
        'db_access_class_catalog_count' => count($dbAccessClassCatalog['items']),
        'db_access_function_name' => $dbAccessFunctionName,
        'db_access_function_catalog_count' => count($dbAccessFunctionCatalog['items']),
        'event_key' => $eventKey,
        'latest_count' => count($latest['items']),
        'matched_metadata_keys' => array_keys($metadata),
    ]);
}

/**
 * @param array<string,mixed> $details
 * @return array<string,mixed>
 */
function app_firebird_config_repository_smoke_result(bool $ok, string $stage, string $error, array $details = []): array
{
    return [
        'ok' => $ok,
        'stage' => $stage,
        'error' => $error,
        'mutation_performed' => $stage !== 'preflight' && $stage !== 'bootstrap',
        'details' => $details,
    ];
}

$parsed = app_cli_firebird_config_repository_smoke_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_firebird_config_repository_smoke_usage() . PHP_EOL);
    exit(0);
}
if ($parsed['error'] !== '') {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_firebird_config_repository_smoke_usage() . PHP_EOL);
    exit(64);
}

$result = app_firebird_config_repository_smoke();
fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($parsed['pretty'] ? JSON_PRETTY_PRINT : 0),
    ) . PHP_EOL,
);
exit($result['ok'] ? 0 : 1);
