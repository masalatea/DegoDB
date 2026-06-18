<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_lab_published_single_proxy_path(
    string $projectKey,
    string $sourceOutputKey,
    string $endpointFilename = '',
): string {
    $path = '/runs/proxy/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode(app_normalize_source_output_key($sourceOutputKey));

    $normalizedEndpointFilename = trim($endpointFilename);
    if ($normalizedEndpointFilename !== '') {
        $path .= '/' . rawurlencode($normalizedEndpointFilename);
    }

    return $path;
}

function app_lab_published_single_proxy_endpoint_filename_is_valid(string $endpointFilename): bool
{
    return preg_match('/^proxyserver-[A-Za-z0-9_-]+\.php$/', $endpointFilename) === 1;
}

/**
 * @return array{
 *     ok:bool,
 *     status_code:int,
 *     source_output:array<string,mixed>|null,
 *     published_root:string,
 *     endpoint_path:string,
 *     error:string
 * }
 */
function app_lab_published_single_proxy_resolve(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
    string $endpointFilename,
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'status_code' => 400,
            'source_output' => null,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => 'project key の形式が不正です。',
        ];
    }

    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
    if ($normalizedSourceOutputKey === '' || !app_source_output_key_is_valid($normalizedSourceOutputKey)) {
        return [
            'ok' => false,
            'status_code' => 400,
            'source_output' => null,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => 'source output key の形式が不正です。',
        ];
    }

    if (!app_lab_published_single_proxy_endpoint_filename_is_valid($endpointFilename)) {
        return [
            'ok' => false,
            'status_code' => 400,
            'source_output' => null,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => 'endpoint filename の形式が不正です。',
        ];
    }

    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        $normalizedProjectKey,
        $normalizedSourceOutputKey,
    );
    if (!$sourceOutputResult['ok']) {
        return [
            'ok' => false,
            'status_code' => 500,
            'source_output' => null,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => $sourceOutputResult['error'],
        ];
    }

    $sourceOutput = is_array($sourceOutputResult['item'] ?? null)
        ? $sourceOutputResult['item']
        : null;
    if ($sourceOutput === null) {
        return [
            'ok' => false,
            'status_code' => 404,
            'source_output' => null,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => 'single proxy source output が見つかりません。',
        ];
    }

    if (trim((string) ($sourceOutput['artifact_strategy'] ?? '')) !== 'single-proxy-server') {
        return [
            'ok' => false,
            'status_code' => 422,
            'source_output' => $sourceOutput,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => 'この route は published single-proxy-server output のみ実行できます。',
        ];
    }

    $sourceOutputDir = trim(str_replace('\\', '/', (string) ($sourceOutput['source_output_dir'] ?? '')));
    if ($sourceOutputDir === '' || !app_project_output_relative_path_is_safe($sourceOutputDir)) {
        return [
            'ok' => false,
            'status_code' => 422,
            'source_output' => $sourceOutput,
            'published_root' => '',
            'endpoint_path' => '',
            'error' => 'source output dir が未設定か不正です。',
        ];
    }

    $publishedRoot = app_project_output_workspace_path_from_relative($sourceOutputDir);
    $resolvedPublishedRoot = realpath($publishedRoot);
    if (!is_string($resolvedPublishedRoot) || $resolvedPublishedRoot === '' || !is_dir($resolvedPublishedRoot)) {
        return [
            'ok' => false,
            'status_code' => 404,
            'source_output' => $sourceOutput,
            'published_root' => $publishedRoot,
            'endpoint_path' => '',
            'error' => 'published source output が見つかりません。artifact を generate/publish してください。',
        ];
    }

    $endpointPath = $resolvedPublishedRoot . '/' . $endpointFilename;
    $resolvedEndpointPath = realpath($endpointPath);
    if (
        !is_string($resolvedEndpointPath)
        || $resolvedEndpointPath === ''
        || !is_file($resolvedEndpointPath)
        || !str_starts_with($resolvedEndpointPath, $resolvedPublishedRoot . '/')
    ) {
        return [
            'ok' => false,
            'status_code' => 404,
            'source_output' => $sourceOutput,
            'published_root' => $resolvedPublishedRoot,
            'endpoint_path' => $endpointPath,
            'error' => 'published single proxy endpoint が見つかりません。',
        ];
    }

    return [
        'ok' => true,
        'status_code' => 200,
        'source_output' => $sourceOutput,
        'published_root' => $resolvedPublishedRoot,
        'endpoint_path' => $resolvedEndpointPath,
        'error' => '',
    ];
}

function app_lab_published_single_proxy_endpoint_requested_db_config_key(array $app): string
{
    $validation = app_lab_published_single_proxy_validate_requested_db_source_key($app);
    if ($validation['ok']) {
        return $validation['requested_key'];
    }

    return '';
}

/**
 * @return array{
 *     ok:bool,
 *     status_code:int,
 *     requested_key:string,
 *     error:string
 * }
 */
function app_lab_published_single_proxy_validate_requested_db_source_key(array $app): array
{
    $requestedKey = '';
    foreach (['db_source_key', 'db_config_key'] as $queryKey) {
        $candidateKey = trim((string) app_query_param($queryKey));
        if ($candidateKey !== '') {
            $requestedKey = $candidateKey;
            break;
        }
    }

    if ($requestedKey === '') {
        return [
            'ok' => true,
            'status_code' => 200,
            'requested_key' => '',
            'error' => '',
        ];
    }

    if (!app_database_source_exists($app, $requestedKey)) {
        return [
            'ok' => false,
            'status_code' => 422,
            'requested_key' => $requestedKey,
            'error' => '指定した db_source_key は database source catalog に見つかりません。',
        ];
    }

    if (!app_database_source_supports_proxy_runtime_read($app, $requestedKey)) {
        return [
            'ok' => false,
            'status_code' => 422,
            'requested_key' => $requestedKey,
            'error' => '指定した db_source_key は proxy runtime read が無効です。',
        ];
    }

    return [
        'ok' => true,
        'status_code' => 200,
        'requested_key' => $requestedKey,
        'error' => '',
    ];
}

function app_lab_published_single_proxy_resolve_source_name_from_build_plan(
    string $publishedRoot,
    string $endpointFilename,
): string {
    $buildPlanPath = $publishedRoot . '/build-plan.json';
    if (!is_file($buildPlanPath) || !is_readable($buildPlanPath)) {
        return '';
    }

    $contents = file_get_contents($buildPlanPath);
    if (!is_string($contents) || $contents === '') {
        return '';
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return '';
    }

    foreach (['operations', 'items'] as $collectionKey) {
        foreach (($decoded[$collectionKey] ?? []) as $operation) {
            if (!is_array($operation)) {
                continue;
            }

            $candidateEndpointFilename = trim((string) ($operation['endpoint_filename'] ?? ''));
            if ($candidateEndpointFilename === '') {
                $sourceName = trim((string) ($operation['source_name'] ?? ''));
                $functionName = trim((string) ($operation['function_name'] ?? ''));
                if ($sourceName !== '' && $functionName !== '') {
                    $candidateEndpointFilename = 'proxyserver-' . $sourceName . '-' . $functionName . '.php';
                }
            }

            if ($candidateEndpointFilename === $endpointFilename) {
                return trim((string) ($operation['source_name'] ?? ''));
            }
        }
    }

    return '';
}

function app_lab_published_single_proxy_source_exists_in_database_source(
    array $app,
    string $databaseSourceKey,
    string $sourceName,
): bool {
    if (!app_database_source_exists($app, $databaseSourceKey)) {
        return false;
    }

    try {
        $dbConfig = app_database_source_config($app, $databaseSourceKey);
        $pdo = app_create_pdo_from_db_config($dbConfig);
        $statement = $pdo->prepare(
            'SELECT 1
            FROM information_schema.tables
            WHERE table_schema = :schema_name
              AND table_name = :table_name
            LIMIT 1'
        );
        $statement->execute([
            ':schema_name' => $dbConfig['name'],
            ':table_name' => $sourceName,
        ]);

        return $statement->fetchColumn() !== false;
    } catch (Throwable) {
        return false;
    }
}

/**
 * @return list<string>
 */
function app_lab_published_single_proxy_runtime_database_source_key_candidates(array $app): array
{
    return array_values(array_map(
        static fn (array $source): string => (string) ($source['key'] ?? ''),
        app_database_source_proxy_runtime_candidates($app),
    ));
}

function app_lab_published_single_proxy_runtime_db_config_key(
    array $app,
    string $publishedRoot,
    string $endpointFilename,
    string $requestedKey = '',
): string {
    if ($requestedKey === '') {
        $requestedKey = app_lab_published_single_proxy_endpoint_requested_db_config_key($app);
    }
    if ($requestedKey !== '') {
        return $requestedKey;
    }

    $sourceName = app_lab_published_single_proxy_resolve_source_name_from_build_plan($publishedRoot, $endpointFilename);
    $canonicalStoreKey = app_database_source_canonical_store_key($app);
    if ($sourceName !== '') {
        foreach (app_lab_published_single_proxy_runtime_database_source_key_candidates($app) as $candidateKey) {
            if ($candidateKey === '' || $candidateKey === $canonicalStoreKey) {
                continue;
            }

            if (
                app_lab_published_single_proxy_source_exists_in_database_source($app, $candidateKey, $sourceName)
                && (
                    $canonicalStoreKey === ''
                    || !app_lab_published_single_proxy_source_exists_in_database_source($app, $canonicalStoreKey, $sourceName)
                )
            ) {
                return $candidateKey;
            }
        }
    }

    return $canonicalStoreKey !== '' ? $canonicalStoreKey : 'config_db';
}

function app_lab_published_single_proxy_apply_runtime_globals(array $app, string $dbConfigKey = 'config_db'): void
{
    $fallbackDatabaseSourceKey = app_database_source_canonical_store_key($app);
    $effectiveDatabaseSourceKey = app_database_source_exists($app, $dbConfigKey)
        ? $dbConfigKey
        : ($fallbackDatabaseSourceKey !== '' ? $fallbackDatabaseSourceKey : 'config_db');
    $configDb = app_database_source_config($app, $effectiveDatabaseSourceKey);

    $GLOBALS['CustomMySQLDBServerNameFormtooldb'] = $configDb['host'];
    $GLOBALS['CustomMySQLDBPortFormtooldb'] = $configDb['port'];
    $GLOBALS['CustomMySQLDBUserFormtooldb'] = $configDb['user'];
    $GLOBALS['CustomMySQLDBPasswordFormtooldb'] = $configDb['password'];
    $GLOBALS['CustomMySQLDBNameFormtooldb'] = $configDb['name'];

    $dialect = app_sql_dialect_from_db_config($configDb);
    if ($dialect === 'sqlite') {
        putenv('MTOOL_RUNTIME_DB_DSN=' . (string) ($configDb['dsn'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_USER=');
        putenv('MTOOL_RUNTIME_DB_PASSWORD=');
        putenv('MTOOL_RUNTIME_DB_HOST=');
        putenv('MTOOL_RUNTIME_DB_PORT=');
        putenv('MTOOL_RUNTIME_DB_NAME=');
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . (string) ($configDb['name'] ?? ''));
    } else {
        putenv('MTOOL_RUNTIME_DB_DSN');
        putenv('MTOOL_RUNTIME_SQLITE_PATH');
        putenv('MTOOL_RUNTIME_DB_HOST=' . (string) ($configDb['host'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_PORT=' . (string) ($configDb['port'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_USER=' . (string) ($configDb['user'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_PASSWORD=' . (string) ($configDb['password'] ?? ''));
        putenv('MTOOL_RUNTIME_DB_NAME=' . (string) ($configDb['name'] ?? ''));
    }

    $_SERVER['MTOOL_PROXY_RUNTIME_DB_CONFIG_KEY'] = $effectiveDatabaseSourceKey;
    $_SERVER['MTOOL_PROXY_RUNTIME_DB_SOURCE_KEY'] = $effectiveDatabaseSourceKey;
}

function app_render_lab_published_single_proxy_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab' && $app['site'] !== 'admin') {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'この route は 実験用サイト または 設定変更用サイト でのみ利用します。',
        ], 403);
        return;
    }

    if (
        !app_request_method_is($request, 'GET')
        && !app_request_method_is($request, 'POST')
        && !app_request_method_is($request, 'OPTIONS')
    ) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'GET / POST / OPTIONS のみ利用できます。',
        ], 405);
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => '認証が必要です。',
        ], 401);
        return;
    }

    if (!app_auth_has_any_role(['lab', 'admin'], $principal)) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'published proxy の実行には lab または admin role が必要です。',
        ], 403);
        return;
    }

    $projectKey = app_route_param($request, 'project_key');
    $sourceOutputKey = app_route_param($request, 'source_output_key');
    $endpointFilename = rawurldecode(app_route_param($request, 'endpoint_filename'));

    $resolved = app_lab_published_single_proxy_resolve(
        $app,
        $projectKey,
        $sourceOutputKey,
        $endpointFilename,
    );
    if (!$resolved['ok']) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => $resolved['error'],
            'project_key' => app_normalize_project_key($projectKey),
            'source_output_key' => app_normalize_source_output_key($sourceOutputKey),
            'endpoint_filename' => $endpointFilename,
        ], $resolved['status_code']);
        return;
    }

    $requestedDatabaseSourceValidation = app_lab_published_single_proxy_validate_requested_db_source_key($app);
    if (!$requestedDatabaseSourceValidation['ok']) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => $requestedDatabaseSourceValidation['error'],
            'project_key' => app_normalize_project_key($projectKey),
            'source_output_key' => app_normalize_source_output_key($sourceOutputKey),
            'endpoint_filename' => $endpointFilename,
            'requested_db_source_key' => $requestedDatabaseSourceValidation['requested_key'],
        ], $requestedDatabaseSourceValidation['status_code']);
        return;
    }

    $runtimeDbConfigKey = app_lab_published_single_proxy_runtime_db_config_key(
        $app,
        $resolved['published_root'],
        $endpointFilename,
        $requestedDatabaseSourceValidation['requested_key'],
    );
    app_lab_published_single_proxy_apply_runtime_globals($app, $runtimeDbConfigKey);

    header('Cache-Control: no-store');
    header('X-Request-Id: ' . $request['request_id']);

    try {
        require $resolved['endpoint_path'];
    } catch (Throwable $throwable) {
        if (!headers_sent()) {
            app_send_json_response($request, [
                'ok' => false,
                'error' => 'published single proxy の実行に失敗しました: ' . $throwable->getMessage(),
            ], 500);
            return;
        }

        throw $throwable;
    }
}
