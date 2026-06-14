<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function app_config_db_bootstrap_default_sql_dir(): string
{
    return dirname(__DIR__, 2) . '/docker/mariadb/config-initdb';
}

function app_config_db_bootstrap_path_is_absolute(string $path): bool
{
    if ($path === '') {
        return false;
    }

    if ($path[0] === '/') {
        return true;
    }

    return preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
}

function app_config_db_bootstrap_resolve_sql_dir(string $path = ''): string
{
    $trimmed = trim($path);
    if ($trimmed === '') {
        return str_replace('\\', '/', app_config_db_bootstrap_default_sql_dir());
    }

    $normalized = str_replace('\\', '/', $trimmed);
    if (app_config_db_bootstrap_path_is_absolute($normalized)) {
        return rtrim($normalized, '/');
    }

    $cwd = getcwd();
    if (!is_string($cwd) || trim($cwd) === '') {
        $cwd = '.';
    }

    return rtrim(str_replace('\\', '/', $cwd), '/') . '/' . ltrim($normalized, '/');
}

function app_config_db_bootstrap_target_mode(array $dbConfig): string
{
    $host = strtolower(trim((string) ($dbConfig['host'] ?? '')));
    $port = trim((string) ($dbConfig['port'] ?? ''));

    if ($host === 'db-config' && $port === '3306') {
        return 'compose-local-service';
    }

    if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
        return 'host-loopback';
    }

    return 'external';
}

function app_config_db_bootstrap_admin_db_matches_config_db(array $app): bool
{
    if (!app_database_config_exists($app, 'db') || !app_database_config_exists($app, 'config_db')) {
        return false;
    }

    $dbConfig = app_database_config($app, 'db');
    $configDbConfig = app_database_config($app, 'config_db');

    foreach (['host', 'port', 'name', 'user', 'password'] as $field) {
        if ((string) ($dbConfig[$field] ?? '') !== (string) ($configDbConfig[$field] ?? '')) {
            return false;
        }
    }

    return true;
}

function app_config_db_bootstrap_admin_metadata_routing_warning(): string
{
    return 'admin canonical metadata repository は config_db を読みます。built-in db は live schema import source として残ります。';
}

/**
 * @return list<string>
 */
function app_config_db_bootstrap_required_tables(): array
{
    return [
        'projects',
        'project_memberships',
        'project_page_security_policies',
        'project_page_security_policy_capabilities',
        'project_host_assignments',
        'project_db_access_classes',
        'project_db_access_functions',
        'project_db_access_function_select_wheres',
        'project_db_access_function_select_target_fields',
        'project_db_access_function_select_havings',
        'project_db_access_function_update_delete_wheres',
        'project_db_access_function_insert_target_fields',
        'project_db_access_function_update_target_fields',
        'project_source_outputs',
        'project_compare_outputs',
        'project_compare_output_additional_paths',
        'project_html_source_bindings',
        'project_custom_proxies',
        'project_custom_proxy_steps',
        'project_custom_proxy_source_output_targets',
        'project_html_definitions',
        'project_html_parameters',
        'html_templates',
        'html_template_parameters',
        'dbtable',
        'dbtablecolumns',
        'dataclass',
        'dataclassfields',
        'project_db_access_function_source_output_targets',
        'database_sources',
    ];
}

/**
 * @return array<string,list<string>>
 */
function app_config_db_bootstrap_required_columns(): array
{
    return [
        'projects' => [
            'lifecycle_status',
            'owner_login_id',
        ],
        'project_source_outputs' => [
            'artifact_strategy',
            'target_binding_type',
            'spec_visibility',
            'output_archive_format',
        ],
        'database_sources' => [
            'supports_live_schema_import',
            'supports_proxy_runtime_read',
            'proxy_runtime_priority',
        ],
    ];
}

/**
 * @return array<string,list<string>>
 */
function app_config_db_bootstrap_forbidden_columns(): array
{
    return [
        'project_db_access_function_select_wheres' => ['legacy_source_pid'],
        'project_db_access_function_select_target_fields' => ['legacy_source_pid'],
        'project_db_access_function_select_havings' => ['legacy_source_pid'],
        'project_db_access_function_update_delete_wheres' => ['legacy_source_pid'],
        'project_db_access_function_insert_target_fields' => ['legacy_source_pid'],
        'project_db_access_function_update_target_fields' => ['legacy_source_pid'],
    ];
}

/**
 * @return list<string>
 */
function app_config_db_bootstrap_sql_files(string $sqlDir): array
{
    $files = glob(rtrim($sqlDir, '/') . '/*.sql');
    if ($files === false) {
        return [];
    }

    $normalized = array_map(
        static fn (string $path): string => str_replace('\\', '/', $path),
        $files,
    );
    sort($normalized, SORT_STRING);

    return array_values($normalized);
}

function app_config_db_bootstrap_pdo_table_exists(PDO $pdo, string $tableName): bool
{
    $statement = $pdo->prepare(
        'SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = :table_name
        LIMIT 1'
    );
    $statement->execute([
        ':table_name' => $tableName,
    ]);

    return $statement->fetchColumn() !== false;
}

function app_config_db_bootstrap_pdo_column_exists(PDO $pdo, string $tableName, string $columnName): bool
{
    $statement = $pdo->prepare(
        'SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = :table_name
          AND column_name = :column_name
        LIMIT 1'
    );
    $statement->execute([
        ':table_name' => $tableName,
        ':column_name' => $columnName,
    ]);

    return $statement->fetchColumn() !== false;
}

/**
 * @param array{
 *     sql_dir?:string
 * } $options
 * @return array{
 *     ok:bool,
 *     target:array{
 *         site:string,
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         target_mode:string,
 *         admin_db_matches_config_db:bool
 *     },
 *     summary:array{
 *         version:string,
 *         resolved_database_name:string,
 *         sql_dir:string,
 *         sql_file_count:int,
 *         latest_sql_file:string,
 *         required_table_count:int,
 *         required_column_count:int,
 *         forbidden_column_count:int,
 *         missing_table_count:int,
 *         missing_column_count:int,
 *         unexpected_legacy_column_count:int,
 *         schema_current:bool
 *     },
 *     missing_tables:list<string>,
 *     missing_columns:list<string>,
 *     unexpected_legacy_columns:list<string>,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_config_db_bootstrap_preflight(array $app, array $options = []): array
{
    $configDb = app_database_config($app, 'config_db');
    $resolvedSqlDir = app_config_db_bootstrap_resolve_sql_dir((string) ($options['sql_dir'] ?? ''));
    $sqlFiles = app_config_db_bootstrap_sql_files($resolvedSqlDir);
    $site = trim((string) ($app['site'] ?? ''));
    $adminDbMatchesConfigDb = $site === 'admin'
        ? app_config_db_bootstrap_admin_db_matches_config_db($app)
        : true;
    $targetMode = app_config_db_bootstrap_target_mode($configDb);
    $warnings = [];

    if ($targetMode !== 'compose-local-service') {
        $warnings[] = 'config DB target は local compose 既定値ではありません。target DB に current initdb を適用してから使ってください。';
    }
    if (!is_dir($resolvedSqlDir)) {
        $warnings[] = 'config-initdb directory が見つかりません: ' . $resolvedSqlDir;
    }
    if ($site === 'admin' && !$adminDbMatchesConfigDb) {
        $warnings[] = app_config_db_bootstrap_admin_metadata_routing_warning();
    }

    $target = [
        'site' => $site,
        'host' => $configDb['host'],
        'port' => $configDb['port'],
        'name' => $configDb['name'],
        'user' => $configDb['user'],
        'target_mode' => $targetMode,
        'admin_db_matches_config_db' => $adminDbMatchesConfigDb,
    ];
    $summary = [
        'version' => '',
        'resolved_database_name' => '',
        'sql_dir' => $resolvedSqlDir,
        'sql_file_count' => count($sqlFiles),
        'latest_sql_file' => $sqlFiles !== [] ? basename($sqlFiles[count($sqlFiles) - 1]) : '',
        'required_table_count' => count(app_config_db_bootstrap_required_tables()),
        'required_column_count' => array_sum(array_map('count', app_config_db_bootstrap_required_columns())),
        'forbidden_column_count' => array_sum(array_map('count', app_config_db_bootstrap_forbidden_columns())),
        'missing_table_count' => 0,
        'missing_column_count' => 0,
        'unexpected_legacy_column_count' => 0,
        'schema_current' => false,
    ];

    try {
        $pdo = app_create_config_pdo($app);
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $missingTables = [];
        foreach (app_config_db_bootstrap_required_tables() as $tableName) {
            if (!app_config_db_bootstrap_pdo_table_exists($pdo, $tableName)) {
                $missingTables[] = $tableName;
            }
        }

        $missingColumns = [];
        foreach (app_config_db_bootstrap_required_columns() as $tableName => $columns) {
            foreach ($columns as $columnName) {
                if (!app_config_db_bootstrap_pdo_column_exists($pdo, $tableName, $columnName)) {
                    $missingColumns[] = $tableName . '.' . $columnName;
                }
            }
        }

        $unexpectedLegacyColumns = [];
        foreach (app_config_db_bootstrap_forbidden_columns() as $tableName => $columns) {
            foreach ($columns as $columnName) {
                if (app_config_db_bootstrap_pdo_column_exists($pdo, $tableName, $columnName)) {
                    $unexpectedLegacyColumns[] = $tableName . '.' . $columnName;
                }
            }
        }

        $summary['version'] = is_string($version) ? $version : '';
        $summary['resolved_database_name'] = is_string($databaseName) ? $databaseName : '';
        $summary['missing_table_count'] = count($missingTables);
        $summary['missing_column_count'] = count($missingColumns);
        $summary['unexpected_legacy_column_count'] = count($unexpectedLegacyColumns);
        $summary['schema_current'] = $missingTables === []
            && $missingColumns === []
            && $unexpectedLegacyColumns === [];

        if (!$summary['schema_current']) {
            $warnings[] = 'config DB schema が current initdb marker と一致しません。target DB に current config-initdb を適用してください。';
        }

        $ok = $summary['schema_current'];

        return [
            'ok' => $ok,
            'target' => $target,
            'summary' => $summary,
            'missing_tables' => $missingTables,
            'missing_columns' => $missingColumns,
            'unexpected_legacy_columns' => $unexpectedLegacyColumns,
            'warnings' => $warnings,
            'error' => $ok ? '' : 'config DB bootstrap preflight failed.',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'target' => $target,
            'summary' => $summary,
            'missing_tables' => [],
            'missing_columns' => [],
            'unexpected_legacy_columns' => [],
            'warnings' => $warnings,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     sql_dir?:string
 * } $options
 * @return array{
 *     ok:bool,
 *     target:array{
 *         site:string,
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         target_mode:string,
 *         admin_db_matches_config_db:bool
 *     },
 *     summary:array{
 *         version:string,
 *         resolved_database_name:string,
 *         sql_dir:string,
 *         sql_file_count:int,
 *         applied_file_count:int,
 *         last_sql_file:string,
 *         schema_current:bool
 *     },
 *     applied_files:list<string>,
 *     missing_tables:list<string>,
 *     missing_columns:list<string>,
 *     unexpected_legacy_columns:list<string>,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_config_db_bootstrap_apply(array $app, array $options = []): array
{
    $configDb = app_database_config($app, 'config_db');
    $resolvedSqlDir = app_config_db_bootstrap_resolve_sql_dir((string) ($options['sql_dir'] ?? ''));
    $sqlFiles = app_config_db_bootstrap_sql_files($resolvedSqlDir);
    $site = trim((string) ($app['site'] ?? ''));
    $adminDbMatchesConfigDb = $site === 'admin'
        ? app_config_db_bootstrap_admin_db_matches_config_db($app)
        : true;
    $targetMode = app_config_db_bootstrap_target_mode($configDb);
    $target = [
        'site' => $site,
        'host' => $configDb['host'],
        'port' => $configDb['port'],
        'name' => $configDb['name'],
        'user' => $configDb['user'],
        'target_mode' => $targetMode,
        'admin_db_matches_config_db' => $adminDbMatchesConfigDb,
    ];
    $summary = [
        'version' => '',
        'resolved_database_name' => '',
        'sql_dir' => $resolvedSqlDir,
        'sql_file_count' => count($sqlFiles),
        'applied_file_count' => 0,
        'last_sql_file' => $sqlFiles !== [] ? basename($sqlFiles[count($sqlFiles) - 1]) : '',
        'schema_current' => false,
    ];
    $warnings = [];

    if ($sqlFiles === []) {
        return [
            'ok' => false,
            'target' => $target,
            'summary' => $summary,
            'applied_files' => [],
            'missing_tables' => [],
            'missing_columns' => [],
            'unexpected_legacy_columns' => [],
            'warnings' => [
                'config-initdb SQL file が見つかりません: ' . $resolvedSqlDir,
            ],
            'error' => 'config-initdb SQL file が見つかりません。',
        ];
    }

    if ($site === 'admin' && !$adminDbMatchesConfigDb) {
        $warnings[] = app_config_db_bootstrap_admin_metadata_routing_warning();
    }

    try {
        $pdo = app_create_config_pdo($app);
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $appliedFiles = [];

        foreach ($sqlFiles as $sqlFile) {
            $contents = file_get_contents($sqlFile);
            if (!is_string($contents)) {
                throw new RuntimeException('SQL file を読み込めません: ' . $sqlFile);
            }

            if (trim($contents) === '') {
                continue;
            }

            $pdo->exec($contents);
            $appliedFiles[] = basename($sqlFile);
        }

        $postPreflight = app_config_db_bootstrap_preflight($app, [
            'sql_dir' => $resolvedSqlDir,
        ]);
        $summary['version'] = is_string($version) ? $version : '';
        $summary['resolved_database_name'] = is_string($databaseName) ? $databaseName : '';
        $summary['applied_file_count'] = count($appliedFiles);
        $summary['schema_current'] = (bool) ($postPreflight['summary']['schema_current'] ?? false);
        $warnings = array_values(array_unique(array_merge(
            $warnings,
            $postPreflight['warnings'],
        )));

        return [
            'ok' => $postPreflight['ok'],
            'target' => $target,
            'summary' => $summary,
            'applied_files' => $appliedFiles,
            'missing_tables' => $postPreflight['missing_tables'],
            'missing_columns' => $postPreflight['missing_columns'],
            'unexpected_legacy_columns' => $postPreflight['unexpected_legacy_columns'],
            'warnings' => $warnings,
            'error' => $postPreflight['ok']
                ? ''
                : ($postPreflight['error'] !== '' ? $postPreflight['error'] : 'config DB migrate 後の preflight に失敗しました。'),
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'target' => $target,
            'summary' => $summary,
            'applied_files' => [],
            'missing_tables' => [],
            'missing_columns' => [],
            'unexpected_legacy_columns' => [],
            'warnings' => $warnings,
            'error' => $throwable->getMessage(),
        ];
    }
}
