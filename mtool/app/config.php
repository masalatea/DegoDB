<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_storage_paths.php';

/**
 * @return array{
 *     site:string,
 *     site_name:string,
 *     site_role_summary:string,
 *     session:array{
 *         name:string
 *     },
 *     auth:array{
 *         mode:string,
 *         stub:array{
 *             username:string,
 *             password:string,
 *             display_name:string,
 *             roles:list<string>
 *         }
 *     },
 *     translation:array{
 *         provider:string,
 *         google_api_key:string,
 *         timeout_seconds:int
 *     },
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     config_db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     lab_db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     database_sources:array<string,array{
 *         key:string,
 *         label:string,
 *         description:string,
 *         source_of_truth:string,
 *         db_config_key:string,
 *         supports_live_schema_import:bool,
 *         supports_proxy_runtime_read:bool,
 *         proxy_runtime_priority:int,
 *         is_canonical_store:bool,
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     generated:array{
 *         root:string,
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     },
 *     work:array{
 *         root:string
 *     },
 *     repositories:array{
 *         project_driver:string,
 *         experiment_driver:string
 *     }
 * }
 */
function app_load_config(): array
{
    $site = app_config_env('APP_SITE', 'admin');
    $defaults = app_site_defaults($site);
    $labDefaults = app_site_defaults('lab');

    $sessionName = app_config_env('APP_SESSION_NAME', $defaults['session_name']);
    $authMode = app_config_env('APP_AUTH_MODE', 'stub');
    $authStubUsername = app_config_env('APP_AUTH_STUB_USER', $defaults['auth_stub_user']);
    $authStubPassword = app_config_env('APP_AUTH_STUB_PASSWORD', $defaults['auth_stub_password']);
    $authStubDisplayName = app_config_env('APP_AUTH_STUB_NAME', $defaults['auth_stub_name']);
    $authStubRoles = app_config_csv_env('APP_AUTH_STUB_ROLES', $defaults['auth_stub_roles']);
    $translationProvider = app_config_env('APP_TRANSLATION_PROVIDER', 'disabled');
    $translationGoogleApiKey = app_config_env('APP_TRANSLATION_GOOGLE_API_KEY', '');
    $translationTimeoutSeconds = max(1, (int) app_config_env('APP_TRANSLATION_TIMEOUT_SECONDS', '10'));

    $dbHost = app_config_env('APP_DB_HOST', $defaults['db_host']);
    $dbPort = app_config_env('APP_DB_PORT', '3306');
    $dbName = app_config_env('APP_DB_NAME', $defaults['db_name']);
    $dbUser = app_config_env('APP_DB_USER', $defaults['db_user']);
    $dbPassword = app_config_env('APP_DB_PASSWORD', $defaults['db_password']);
    $configDbHost = app_config_env('APP_CONFIG_DB_HOST', $defaults['config_db_host']);
    $configDbPort = app_config_env('APP_CONFIG_DB_PORT', '3306');
    $configDbName = app_config_env('APP_CONFIG_DB_NAME', $defaults['config_db_name']);
    $configDbUser = app_config_env('APP_CONFIG_DB_USER', $defaults['config_db_user']);
    $configDbPassword = app_config_env('APP_CONFIG_DB_PASSWORD', $defaults['config_db_password']);
    $labDbHost = app_config_env('APP_LAB_DB_HOST', $labDefaults['db_host']);
    $labDbPort = app_config_env('APP_LAB_DB_PORT', '3306');
    $labDbName = app_config_env('APP_LAB_DB_NAME', $labDefaults['db_name']);
    $labDbUser = app_config_env('APP_LAB_DB_USER', $labDefaults['db_user']);
    $labDbPassword = app_config_env('APP_LAB_DB_PASSWORD', $labDefaults['db_password']);
    $generatedRoot = rtrim(
        app_config_env(
            'APP_REFERENCE_ROOT',
            app_config_env('APP_GENERATED_ROOT', app_runtime_storage_default_reference_root()),
        ),
        '/',
    );
    $workRoot = rtrim(
        app_config_env('APP_WORK_ROOT', app_runtime_storage_default_work_root()),
        '/',
    );
    $generatedDbclassesRoot = app_runtime_storage_runtime_dbclasses_root_from_generated_root(
        $generatedRoot,
    );
    $generatedDbclassesMode = app_config_env(
        'APP_GENERATED_DBCLASSES_MODE',
        app_config_detect_generated_dbclasses_mode($generatedDbclassesRoot),
    );
    $projectRepositoryDriver = app_config_env('APP_PROJECT_REPOSITORY_DRIVER', 'pdo');
    $experimentRepositoryDriver = app_config_env('APP_EXPERIMENT_REPOSITORY_DRIVER', 'pdo');

    $dbConfig = [
        'host' => $dbHost,
        'port' => $dbPort,
        'name' => $dbName,
        'user' => $dbUser,
        'password' => $dbPassword,
        'dsn' => sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $dbHost,
            $dbPort,
            $dbName,
        ),
    ];
    $configDbConfig = [
        'host' => $configDbHost,
        'port' => $configDbPort,
        'name' => $configDbName,
        'user' => $configDbUser,
        'password' => $configDbPassword,
        'dsn' => sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $configDbHost,
            $configDbPort,
            $configDbName,
        ),
    ];
    $labDbConfig = [
        'host' => $labDbHost,
        'port' => $labDbPort,
        'name' => $labDbName,
        'user' => $labDbUser,
        'password' => $labDbPassword,
        'dsn' => sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $labDbHost,
            $labDbPort,
            $labDbName,
        ),
    ];

    return [
        'site' => $site,
        'site_name' => app_config_env('APP_SITE_NAME', $defaults['site_name']),
        'site_role_summary' => $defaults['site_role_summary'],
        'session' => [
            'name' => $sessionName,
        ],
        'auth' => [
            'mode' => $authMode,
            'stub' => [
                'username' => $authStubUsername,
                'password' => $authStubPassword,
                'display_name' => $authStubDisplayName,
                'roles' => $authStubRoles,
            ],
        ],
        'translation' => [
            'provider' => $translationProvider,
            'google_api_key' => $translationGoogleApiKey,
            'timeout_seconds' => $translationTimeoutSeconds,
        ],
        'db' => $dbConfig,
        'config_db' => $configDbConfig,
        'lab_db' => $labDbConfig,
        'database_sources' => app_config_builtin_database_source_catalog(
            $dbConfig,
            $configDbConfig,
            $labDbConfig,
        ),
        'generated' => [
            'root' => $generatedRoot,
            'dbclasses_root' => $generatedDbclassesRoot,
            'dbclasses_loader' => $generatedDbclassesRoot . '/autoload_mtool.php',
            'dbclasses_mode' => $generatedDbclassesMode,
        ],
        'work' => [
            'root' => $workRoot,
        ],
        'repositories' => [
            'project_driver' => $projectRepositoryDriver,
            'experiment_driver' => $experimentRepositoryDriver,
        ],
    ];
}

function app_config_detect_generated_dbclasses_mode(string $generatedDbclassesRoot): string
{
    $normalizedRoot = rtrim(str_replace('\\', '/', $generatedDbclassesRoot), '/');
    if ($normalizedRoot === '' || !is_dir($normalizedRoot)) {
        return 'legacy-copy-bootstrap';
    }

    $runtimeManifestPath = $normalizedRoot . '/_support/runtime-generation-manifest.json';
    if (is_file($runtimeManifestPath)) {
        $contents = file_get_contents($runtimeManifestPath);
        if (is_string($contents) && $contents !== '') {
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) {
                $mode = trim((string) (($decoded['mode'] ?? '') ?: ($decoded['generation_summary']['mode'] ?? '')));
                if ($mode !== '') {
                    return 'self-generated-reference:' . $mode;
                }
            }
        }

        return 'self-generated-reference';
    }

    if (
        is_file($normalizedRoot . '/_runtime_loader.php')
        || is_dir($normalizedRoot . '/base')
        || is_dir($normalizedRoot . '/_base')
        || is_dir($normalizedRoot . '/_wrappers')
    ) {
        return 'self-generated-reference';
    }

    return 'legacy-copy-bootstrap';
}

function app_config_env(string $name, string $default): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

/**
 * @param list<string> $default
 * @return list<string>
 */
function app_config_csv_env(string $name, array $default): array
{
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        return $default;
    }

    $items = array_map(
        static fn (string $item): string => trim($item),
        explode(',', $value),
    );

    return array_values(
        array_filter(
            $items,
            static fn (string $item): bool => $item !== '',
        ),
    );
}

/**
 * @return array{
 *     site_name:string,
 *     site_role_summary:string,
 *     session_name:string,
 *     auth_stub_user:string,
 *     auth_stub_password:string,
 *     auth_stub_name:string,
 *     auth_stub_roles:list<string>,
 *     db_host:string,
 *     db_name:string,
 *     db_user:string,
 *     db_password:string,
 *     config_db_host:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string
 * }
 */
function app_site_defaults(string $site): array
{
    return match ($site) {
        'lab' => [
            'site_name' => '実験用サイト',
            'site_role_summary' => 'このサイトでは試験、比較、実験的な動作確認を行います。',
            'session_name' => 'MTOOL_LAB_SESSID',
            'auth_stub_user' => 'lab-user',
            'auth_stub_password' => '',
            'auth_stub_name' => 'Lab Local User',
            'auth_stub_roles' => ['lab'],
            'db_host' => 'db-lab',
            'db_name' => 'lab_app',
            'db_user' => 'lab_app',
            'db_password' => '',
            'config_db_host' => 'db-config',
            'config_db_name' => 'config_app',
            'config_db_user' => 'config_app',
            'config_db_password' => '',
        ],
        'admin' => [
            'site_name' => '設定変更用サイト',
            'site_role_summary' => 'このサイトでは設定管理、設計編集、公開操作を行います。',
            'session_name' => 'MTOOL_ADMIN_SESSID',
            'auth_stub_user' => 'admin',
            'auth_stub_password' => '',
            'auth_stub_name' => 'Admin Local User',
            'auth_stub_roles' => ['admin', 'config'],
            'db_host' => 'db-config',
            'db_name' => 'config_app',
            'db_user' => 'config_app',
            'db_password' => '',
            'config_db_host' => 'db-config',
            'config_db_name' => 'config_app',
            'config_db_user' => 'config_app',
            'config_db_password' => '',
        ],
        default => [
            'site_name' => '未定義サイト',
            'site_role_summary' => 'このサイトの役割はまだ定義されていません。',
            'session_name' => 'MTOOL_APP_SESSID',
            'auth_stub_user' => 'app-user',
            'auth_stub_password' => '',
            'auth_stub_name' => 'Local User',
            'auth_stub_roles' => ['user'],
            'db_host' => 'localhost',
            'db_name' => 'app',
            'db_user' => 'app',
            'db_password' => '',
            'config_db_host' => 'localhost',
            'config_db_name' => 'app',
            'config_db_user' => 'app',
            'config_db_password' => '',
        ],
    };
}

/**
 * @param array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * } $dbConfig
 * @return array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }
 */
function app_config_database_source_definition(
    string $key,
    string $label,
    string $description,
    array $dbConfig,
    bool $supportsLiveSchemaImport,
    bool $supportsProxyRuntimeRead,
    int $proxyRuntimePriority,
    bool $isCanonicalStore = false,
): array {
    return [
        'key' => $key,
        'label' => $label,
        'description' => $description,
        'source_of_truth' => 'app-config',
        'db_config_key' => $key,
        'supports_live_schema_import' => $supportsLiveSchemaImport,
        'supports_proxy_runtime_read' => $supportsProxyRuntimeRead,
        'proxy_runtime_priority' => $proxyRuntimePriority,
        'is_canonical_store' => $isCanonicalStore,
        'host' => $dbConfig['host'],
        'port' => $dbConfig['port'],
        'name' => $dbConfig['name'],
        'user' => $dbConfig['user'],
        'password' => $dbConfig['password'],
        'dsn' => $dbConfig['dsn'],
    ];
}

/**
 * @param array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * } $dbConfig
 * @param array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * } $configDbConfig
 * @param array{
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * } $labDbConfig
 * @return array<string,array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string
 * }>
 */
function app_config_builtin_database_source_catalog(
    array $dbConfig,
    array $configDbConfig,
    array $labDbConfig,
): array {
    return [
        'db' => app_config_database_source_definition(
            'db',
            'site default db',
            '現在サイトの default DB 接続です。live schema import の既定 source として使います。',
            $dbConfig,
            true,
            false,
            300,
        ),
        'config_db' => app_config_database_source_definition(
            'config_db',
            'config db',
            'canonical metadata store と self-host runtime default に使う DB 接続です。',
            $configDbConfig,
            true,
            true,
            200,
            true,
        ),
        'lab_db' => app_config_database_source_definition(
            'lab_db',
            'lab db',
            'editable lab DB と import source preview に使う DB 接続です。',
            $labDbConfig,
            true,
            true,
            100,
        ),
    ];
}
