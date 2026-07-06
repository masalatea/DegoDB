<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/managed_operation_repository_pdo.php';
require_once __DIR__ . '/managed_operation_server_dbaccess_executor.php';
require_once __DIR__ . '/managed_operation_sync_outbox_repository_pdo.php';
require_once __DIR__ . '/managed_operation_sync_outbox_processor.php';
require_once __DIR__ . '/no_code_managed_operation_bridge.php';
require_once __DIR__ . '/no_code_publish_candidate_repository_pdo.php';
require_once __DIR__ . '/no_code_runtime.php';
require_once __DIR__ . '/project_db_access_bootstrap_service.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/sql_dialect.php';

function app_no_code_public_runtime_preview_path(string $projectKey, string $artifactKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode($artifactKey)
        . '/runtime-preview.html';
}

function app_no_code_public_runtime_execution_path(string $projectKey, string $artifactKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode($artifactKey)
        . '/execute.json';
}

function app_no_code_public_runtime_current_preview_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/runtime-preview.html';
}

function app_no_code_public_runtime_current_execution_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/execute.json';
}

function app_no_code_public_runtime_current_data_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/runtime-data.json';
}

function app_no_code_public_runtime_alias_preview_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/runtime-preview.html';
}

function app_no_code_public_runtime_alias_execution_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/execute.json';
}

function app_no_code_public_runtime_alias_data_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/runtime-data.json';
}

function app_no_code_public_runtime_artifact_cache_control(): string
{
    return 'public, max-age=31536000, immutable';
}

function app_no_code_public_runtime_current_cache_control(): string
{
    return 'no-store';
}

/**
 * @param array<string,mixed> $candidate
 * @return array<string,string>
 */
function app_no_code_public_runtime_execution_binding(string $projectKey, array $candidate): array
{
    $binding = [
        'csrf_token' => app_csrf_token(),
        'project_key' => app_normalize_project_key($projectKey),
        'artifact_key' => (string) ($candidate['artifact_key'] ?? ''),
        'source_output_key' => APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
    ];

    $revisionId = trim((string) ($candidate['revision_id'] ?? ''));
    if ($revisionId !== '') {
        $binding['revision_id'] = $revisionId;
    }
    if (app_no_code_public_runtime_demo_processing_enabled()) {
        $binding['demo_processing'] = 'available';
    }

    return $binding;
}

function app_no_code_public_runtime_demo_processing_enabled(): bool
{
    $enabled = strtolower(trim((string) getenv('MTOOL_NO_CODE_RUNTIME_SYNC_DEMO')));
    if (!in_array($enabled, ['1', 'true', 'yes', 'on'], true)) {
        return false;
    }

    $sqlitePath = trim((string) getenv('MTOOL_RUNTIME_SQLITE_PATH'));
    return $sqlitePath !== '';
}

/**
 * @param array<string,mixed> $post
 */
function app_no_code_public_runtime_demo_processing_requested(array $post): bool
{
    $value = strtolower(trim((string) ($post['runtime_demo_process'] ?? '')));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function app_no_code_public_runtime_data_contract_version(): string
{
    return 'no-code-runtime-data-v0';
}

/**
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_data_error_response(string $error, int $statusCode = 422): array
{
    return [
        'status_code' => $statusCode,
        'payload' => [
            'ok' => false,
            'contract_version' => app_no_code_public_runtime_data_contract_version(),
            'project_key' => '',
            'selection' => [],
            'screen_definition_version' => '',
            'runtime_preview_version' => app_no_code_runtime_version(),
            'screens' => [],
            'error' => $error,
        ],
    ];
}

function app_no_code_public_runtime_data_row_from_value(mixed $value): array
{
    if (is_array($value)) {
        return $value;
    }

    if (is_object($value)) {
        return get_object_vars($value);
    }

    return [];
}

/**
 * @return array<string,string|false>
 */
function app_no_code_public_runtime_capture_runtime_db_env(): array
{
    $keys = [
        'MTOOL_RUNTIME_DB_DSN',
        'MTOOL_RUNTIME_DB_USER',
        'MTOOL_RUNTIME_DB_PASSWORD',
        'MTOOL_RUNTIME_DB_HOST',
        'MTOOL_RUNTIME_DB_PORT',
        'MTOOL_RUNTIME_DB_NAME',
        'MTOOL_RUNTIME_SQLITE_PATH',
    ];
    $previous = [];
    foreach ($keys as $key) {
        $previous[$key] = getenv($key);
    }

    return $previous;
}

/**
 * @param array<string,string|false> $previous
 */
function app_no_code_public_runtime_restore_runtime_db_env(array $previous): void
{
    foreach ($previous as $key => $value) {
        if ($value === false) {
            putenv($key);
            continue;
        }

        putenv($key . '=' . $value);
    }

    $GLOBALS['mtooldb'] = null;
}

function app_no_code_public_runtime_apply_runtime_db_env(array $app, string $dbConfigKey = 'config_db'): void
{
    $configDb = app_database_config($app, $dbConfigKey);
    $dialect = app_sql_dialect_from_db_config($configDb);

    $GLOBALS['mtooldb'] = null;
    if ($dialect === 'sqlite') {
        putenv('MTOOL_RUNTIME_DB_DSN=' . (string) ($configDb['dsn'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_USER=');
        putenv('MTOOL_RUNTIME_DB_PASSWORD=');
        putenv('MTOOL_RUNTIME_DB_HOST=');
        putenv('MTOOL_RUNTIME_DB_PORT=');
        putenv('MTOOL_RUNTIME_DB_NAME=');
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . (string) ($configDb['name'] ?? ''));
        return;
    }

    putenv('MTOOL_RUNTIME_DB_DSN');
    putenv('MTOOL_RUNTIME_SQLITE_PATH');
    putenv('MTOOL_RUNTIME_DB_HOST=' . (string) ($configDb['host'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_PORT=' . (string) ($configDb['port'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_USER=' . (string) ($configDb['user'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_PASSWORD=' . (string) ($configDb['password'] ?? ''));
    putenv('MTOOL_RUNTIME_DB_NAME=' . (string) ($configDb['name'] ?? ''));
}

/**
 * @template T
 * @param callable():T $callback
 * @return T
 */
function app_no_code_public_runtime_with_runtime_db_env(array $app, callable $callback): mixed
{
    $previous = app_no_code_public_runtime_capture_runtime_db_env();
    app_no_code_public_runtime_apply_runtime_db_env($app);
    try {
        return $callback();
    } finally {
        app_no_code_public_runtime_restore_runtime_db_env($previous);
    }
}

/**
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_rows_for_contract(array $app, string $projectKey, string $contractKey): array
{
    $runtimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity($app, $projectKey, $contractKey);
    if (!$runtimeEntity['ok'] || !is_array($runtimeEntity['entity'] ?? null)) {
        throw new RuntimeException($runtimeEntity['error']);
    }

    $entity = $runtimeEntity['entity'];
    $dataPath = (string) ($entity['data_path'] ?? '');
    $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
    if ($dataPath === '' || $dbaccessPath === '' || !is_file($dataPath) || !is_file($dbaccessPath)) {
        throw new RuntimeException('runtime DBAccess files were not materialized for fresh runtime data.');
    }

    require_once $dataPath;
    require_once $dbaccessPath;

    $dbAccessClass = (string) ($entity['dbaccess_class'] ?? '');
    if ($dbAccessClass === '' || !class_exists($dbAccessClass)) {
        throw new RuntimeException('runtime DBAccess class was not found for fresh runtime data: ' . $dbAccessClass);
    }

    $sourceName = (string) ($entity['source_name'] ?? $contractKey);
    $generatedSourceName = preg_replace('/DBAccess$/', '', $dbAccessClass);
    $methodCandidates = array_values(array_unique(array_filter([
        'Get' . $sourceName . 'List',
        is_string($generatedSourceName) ? 'Get' . $generatedSourceName . 'List' : '',
    ])));

    $dbAccess = new $dbAccessClass();
    $listMethod = '';
    foreach ($methodCandidates as $methodCandidate) {
        if (method_exists($dbAccess, $methodCandidate)) {
            $listMethod = $methodCandidate;
            break;
        }
    }
    if ($listMethod === '') {
        throw new RuntimeException('runtime DBAccess list method was not found for fresh runtime data: ' . $contractKey);
    }

    $result = $dbAccess->$listMethod();
    if (!is_array($result)) {
        throw new RuntimeException('runtime DBAccess list method did not return rows: ' . $listMethod);
    }

    return array_values(array_map(
        static fn (mixed $item): array => app_no_code_public_runtime_data_row_from_value($item),
        $result,
    ));
}

/**
 * @param array<string,mixed> $render
 * @param array<string,mixed> $currentItem
 * @return array{field_key:string,value:mixed,display_value:string}|array{}
 */
function app_no_code_public_runtime_data_selected_key(array $render, array $currentItem): array
{
    foreach (($render['actions'] ?? []) as $action) {
        if (!is_array($action)) {
            continue;
        }
        foreach (($action['fields'] ?? []) as $field) {
            if (!is_array($field) || (string) ($field['role'] ?? '') !== 'key') {
                continue;
            }

            $fieldKey = (string) ($field['field_key'] ?? '');
            if ($fieldKey === '' || !array_key_exists($fieldKey, $currentItem)) {
                continue;
            }

            $value = $currentItem[$fieldKey];
            return [
                'field_key' => $fieldKey,
                'value' => $value,
                'display_value' => app_no_code_runtime_display_value($value),
            ];
        }
    }

    return [];
}

/**
 * @param array<string,mixed> $render
 * @param list<array<string,mixed>> $rows
 * @param array<string,mixed> $currentItem
 * @return array<string,mixed>
 */
function app_no_code_public_runtime_data_screen_metadata(array $render, array $rows, array $currentItem): array
{
    return [
        'row_count' => count($rows),
        'selected_key' => app_no_code_public_runtime_data_selected_key($render, $currentItem),
        'freshness' => 'live-read',
    ];
}

/**
 * @param array<string,mixed> $definition
 * @return list<array<string,mixed>>
 */
function app_no_code_public_runtime_data_screens(array $app, string $projectKey, array $definition): array
{
    $screens = [];
    foreach (($definition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $contractKey = (string) ($contract['contract_key'] ?? '');
        if ($contractKey === '') {
            continue;
        }

        $rows = app_no_code_public_runtime_data_rows_for_contract($app, $projectKey, $contractKey);
        $currentItem = $rows[0] ?? [];
        foreach (($contract['screens'] ?? []) as $screen) {
            if (!is_array($screen)) {
                continue;
            }
            $screenKey = (string) ($screen['screen_key'] ?? '');
            if ($screenKey === '') {
                continue;
            }

            $renderResult = app_no_code_runtime_render_screen($definition, $screenKey, $rows, $currentItem);
            if (!$renderResult['ok']) {
                throw new RuntimeException($renderResult['error']);
            }

            $render = $renderResult['render'];
            $screens[] = [
                'screen_key' => (string) ($render['screen_key'] ?? ''),
                'screen_type' => (string) ($render['screen_type'] ?? ''),
                'contract_key' => (string) ($render['contract_key'] ?? ''),
                'data' => is_array($render['data'] ?? null) ? $render['data'] : [],
                'metadata' => app_no_code_public_runtime_data_screen_metadata($render, $rows, $currentItem),
                'source' => [
                    'kind' => 'generated-dbaccess',
                    'contract_key' => $contractKey,
                ],
            ];
        }
    }

    return $screens;
}

/**
 * @param array<string,mixed> $candidate
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_data_response_for_candidate(
    array $app,
    string $projectKey,
    array $candidate,
    string $selectionKind,
    string $aliasKey = '',
): array {
    $definitionResult = app_no_code_public_runtime_candidate_screen_definition($app, $projectKey, $candidate);
    if (!$definitionResult['ok']) {
        return app_no_code_public_runtime_data_error_response($definitionResult['error']);
    }

    try {
        $definition = $definitionResult['definition'];
        $screens = app_no_code_public_runtime_with_runtime_db_env(
            $app,
            static fn (): array => app_no_code_public_runtime_data_screens($app, $projectKey, $definition),
        );
    } catch (Throwable $throwable) {
        return app_no_code_public_runtime_data_error_response($throwable->getMessage());
    }

    return [
        'status_code' => 200,
        'payload' => [
            'ok' => true,
            'contract_version' => app_no_code_public_runtime_data_contract_version(),
            'project_key' => app_normalize_project_key($projectKey),
            'selection' => [
                'kind' => $selectionKind,
                'alias_key' => $aliasKey,
                'artifact_key' => (string) ($candidate['artifact_key'] ?? ''),
                'revision_id' => (string) ($candidate['revision_id'] ?? ''),
            ],
            'screen_definition_version' => (string) ($definition['definition_version'] ?? ''),
            'runtime_preview_version' => app_no_code_runtime_version(),
            'screens' => $screens,
            'error' => '',
        ],
    ];
}

/**
 * @param array<string,mixed> $candidate
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_public_runtime_candidate_screen_definition(
    array $app,
    string $projectKey,
    array $candidate,
): array {
    $artifactKey = (string) ($candidate['artifact_key'] ?? '');
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact binding does not match',
        ];
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact was not found',
        ];
    }

    $artifact = $artifactResult['item'];
    if ($artifact['source_output_key'] !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact binding does not match',
        ];
    }

    try {
        $runtimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact was not found',
        ];
    }

    $definitionPath = $runtimeRoot . '/screen-definition.json';
    if (!is_file($definitionPath)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution screen definition is missing',
        ];
    }

    $definition = json_decode((string) file_get_contents($definitionPath), true);
    if (!is_array($definition)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution screen definition is invalid',
        ];
    }

    return [
        'ok' => true,
        'definition' => $definition,
        'error' => '',
    ];
}

/**
 * @return callable(array<string,mixed>):array<string,mixed>
 */
function app_no_code_public_runtime_dispatcher(array $app): callable
{
    return app_no_code_managed_operation_dispatcher(
        [
            'origin' => 'public-runtime',
            'target' => 'server',
        ],
        static fn (array $intent): array => app_pdo_enqueue_managed_operation_sync_intent($app, $intent),
    );
}

/**
 * @param array<string,mixed> $payload
 * @return array{ok:bool,processed:bool,outcome:string,item:array<string,mixed>|null,handler_result:array<string,mixed>|null,error:string}
 */
function app_no_code_public_runtime_demo_process_execution_outbox(array $app, string $projectKey, array $payload): array
{
    if (!app_no_code_public_runtime_demo_processing_enabled()) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'demo_processing_disabled',
            'item' => null,
            'handler_result' => null,
            'error' => 'no-code runtime synchronous demo processing is disabled',
        ];
    }

    $operationKey = (string) ($payload['result']['sync_intent']['operation_key'] ?? $payload['intent']['operation_key'] ?? '');
    if ($operationKey === '') {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'operation_missing',
            'item' => null,
            'handler_result' => null,
            'error' => 'no-code runtime synchronous demo processing requires an operation key',
        ];
    }

    $snapshot = app_pdo_fetch_managed_operation_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'operation_snapshot_failed',
            'item' => null,
            'handler_result' => null,
            'error' => $snapshot['error'],
        ];
    }

    $operation = null;
    foreach ($snapshot['items'] as $item) {
        if ((string) ($item['operation_key'] ?? '') === $operationKey) {
            $operation = $item;
            break;
        }
    }
    if (!is_array($operation)) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'operation_not_found',
            'item' => null,
            'handler_result' => null,
            'error' => 'managed operation was not found for synchronous demo processing: ' . $operationKey,
        ];
    }

    $contractKey = (string) ($operation['contract_key'] ?? '');
    if ($contractKey !== '') {
        $runtimeEntity = app_project_db_access_bootstrap_materialize_runtime_entity($app, $projectKey, $contractKey);
        if (!$runtimeEntity['ok'] || !is_array($runtimeEntity['entity'] ?? null)) {
            return [
                'ok' => false,
                'processed' => false,
                'outcome' => 'runtime_entity_failed',
                'item' => null,
                'handler_result' => null,
                'error' => $runtimeEntity['error'],
            ];
        }

        $entity = $runtimeEntity['entity'];
        $dataPath = (string) ($entity['data_path'] ?? '');
        $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
        if ($dataPath === '' || $dbaccessPath === '' || !is_file($dataPath) || !is_file($dbaccessPath)) {
            return [
                'ok' => false,
                'processed' => false,
                'outcome' => 'runtime_entity_failed',
                'item' => null,
                'handler_result' => null,
                'error' => 'runtime DBAccess files were not materialized for synchronous demo processing',
            ];
        }

        require_once $dataPath;
        require_once $dbaccessPath;
    }

    $binding = app_managed_operation_server_dbaccess_binding_from_project_catalog($app, $projectKey, $operation);
    if (!$binding['ok'] || !is_array($binding['binding'] ?? null)) {
        return [
            'ok' => false,
            'processed' => false,
            'outcome' => 'binding_failed',
            'item' => null,
            'handler_result' => null,
            'error' => $binding['error'],
        ];
    }

    return app_managed_operation_sync_outbox_process_next(
        $app,
        $projectKey,
        app_managed_operation_server_dbaccess_outbox_handler($binding['binding']),
    );
}

/**
 * @param array<string,mixed> $candidate
 * @param array<string,mixed> $post
 * @param array<string,mixed>|null $principal
 * @param callable(array<string,mixed>):array<string,mixed> $dispatcher
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_execution_response_for_candidate(
    array $app,
    string $projectKey,
    array $candidate,
    string $requestMethod,
    array $post,
    ?array $principal,
    callable $dispatcher,
): array {
    $definitionResult = app_no_code_public_runtime_candidate_screen_definition($app, $projectKey, $candidate);
    if (!$definitionResult['ok']) {
        return app_no_code_runtime_execution_endpoint_response(
            app_no_code_runtime_execution_response_error($definitionResult['error']),
        );
    }

    $definition = $definitionResult['definition'];
    if ($principal !== null) {
        $policyDefinitionResult = app_no_code_screen_definition_from_project($app, $projectKey, $principal);
        if (!$policyDefinitionResult['ok']) {
            return app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error($policyDefinitionResult['error']),
            );
        }
        $definition = app_no_code_runtime_definition_with_action_policy_overlay(
            $definition,
            $policyDefinitionResult['definition'],
        );
    }

    $execution = app_no_code_runtime_execute_request_from_post(
        $definition,
        $requestMethod,
        $post,
        app_no_code_public_runtime_execution_binding($projectKey, $candidate),
        $dispatcher,
    );

    $response = app_no_code_runtime_execution_endpoint_response($execution);
    if (($response['payload']['ok'] ?? false) && app_no_code_public_runtime_demo_processing_requested($post)) {
        $response['payload']['demo_processing'] = app_no_code_public_runtime_demo_process_execution_outbox(
            $app,
            $projectKey,
            $response['payload'],
        );
    }

    return $response;
}

/**
 * @param array{
 *     request_id:string
 * } $request
 */
/**
 * @param array<string,mixed>|null $executionBinding
 */
function app_send_no_code_public_runtime_file_response(
    array $request,
    string $filePath,
    string $cacheControl,
    ?array $executionBinding = null,
): void
{
    $body = null;
    if ($executionBinding !== null) {
        $html = (string) file_get_contents($filePath);
        $body = app_no_code_public_runtime_preview_html_with_execution_binding($html, $executionBinding);
    }

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Length: ' . (string) ($body !== null ? strlen($body) : filesize($filePath)));
    header('Cache-Control: ' . $cacheControl);
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);

    if ($body !== null) {
        echo $body;
        return;
    }

    if (readfile($filePath) === false) {
        throw new RuntimeException('public runtime preview の送信に失敗しました。');
    }
}

/**
 * @param array<string,mixed> $executionBinding
 */
function app_no_code_public_runtime_preview_html_with_execution_binding(string $html, array $executionBinding): string
{
    $script = '<script type="application/json" id="no-code-runtime-execution-binding">'
        . app_no_code_runtime_json_script_text($executionBinding)
        . '</script>';

    if (str_contains($html, '<script>')) {
        return str_replace('<script>', $script . "\n<script>", $html);
    }

    if (str_contains($html, '</body>')) {
        return str_replace('</body>', $script . "\n</body>", $html);
    }

    return $html . "\n" . $script . "\n";
}

/**
 * @param array<string,mixed> $candidate
 * @return array<string,string>
 */
function app_no_code_public_runtime_preview_execution_binding(
    string $projectKey,
    array $candidate,
    string $executionPath,
    ?string $dataPath = null,
): array {
    $binding = app_no_code_public_runtime_execution_binding($projectKey, $candidate);
    $binding['execution_url'] = $executionPath;
    if ($dataPath !== null && $dataPath !== '') {
        $binding['runtime_data_url'] = $dataPath;
    }
    return $binding;
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string
 * } $request
 * @param array<string,mixed> $candidate
 */
function app_send_no_code_public_runtime_candidate_preview_response(
    array $app,
    array $request,
    string $projectKey,
    array $candidate,
    string $cacheControl,
    ?string $executionPath = null,
    ?string $dataPath = null,
): bool {
    $artifactKey = (string) ($candidate['artifact_key'] ?? '');
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return false;
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        return false;
    }

    $artifact = $artifactResult['item'];
    if ($artifact['source_output_key'] !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
        return false;
    }

    try {
        $runtimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable) {
        return false;
    }

    $previewPath = $runtimeRoot . '/runtime-preview.html';
    if (!is_file($previewPath)) {
        return false;
    }

    $executionBinding = $executionPath !== null
        ? app_no_code_public_runtime_preview_execution_binding($projectKey, $candidate, $executionPath, $dataPath)
        : null;
    app_send_no_code_public_runtime_file_response($request, $previewPath, $cacheControl, $executionBinding);
    return true;
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $artifactKey = trim(app_route_param($request, 'artifact_key'));
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        app_render_bad_request_page($app, $request, 'artifact key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_artifact($app, $projectKey, $artifactKey);
    if (!$candidateResult['ok']) {
        app_render_not_found_page($app, $request);
        return;
    }
    if ($candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_artifact_cache_control(),
        null,
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $artifactKey = trim(app_route_param($request, 'artifact_key'));
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_artifact($app, $projectKey, $artifactKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_data_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data endpoint requires GET.', 405)['payload'],
            405,
        );
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data project binding does not match.', 409)['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data artifact was not found.', 422)['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_data_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        'current',
    );
    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution alias binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_data_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data endpoint requires GET.', 405)['payload'],
            405,
        );
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data project binding does not match.', 409)['payload'],
            409,
        );
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data alias binding does not match.', 409)['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_public_runtime_data_error_response('runtime data artifact was not found.', 422)['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_data_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        'alias',
        $aliasKey,
    );
    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_current_cache_control(),
        app_no_code_public_runtime_current_execution_path($projectKey),
        app_no_code_public_runtime_current_data_path($projectKey),
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_render_bad_request_page($app, $request, 'alias key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_current_cache_control(),
        app_no_code_public_runtime_alias_execution_path($projectKey, $aliasKey),
        app_no_code_public_runtime_alias_data_path($projectKey, $aliasKey),
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}
