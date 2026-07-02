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
function app_config_db_bootstrap_split_sql_statements(string $sql): array
{
    $statements = [];
    $current = '';
    $quote = '';
    $length = strlen($sql);

    for ($index = 0; $index < $length; $index++) {
        $char = $sql[$index];
        $current .= $char;

        if (($char === "'" || $char === '"') && ($index === 0 || $sql[$index - 1] !== '\\')) {
            if ($quote === '') {
                $quote = $char;
            } elseif ($quote === $char) {
                $quote = '';
            }
        }

        if ($char === ';' && $quote === '') {
            $trimmed = trim($current);
            if ($trimmed !== '') {
                $statements[] = rtrim($trimmed, ';');
            }
            $current = '';
        }
    }

    $trimmed = trim($current);
    if ($trimmed !== '') {
        $statements[] = rtrim($trimmed, ';');
    }

    return $statements;
}

/**
 * @return list<string>
 */
function app_config_db_bootstrap_split_top_level_csv(string $input): array
{
    $items = [];
    $current = '';
    $quote = '';
    $depth = 0;
    $length = strlen($input);

    for ($index = 0; $index < $length; $index++) {
        $char = $input[$index];

        if (($char === "'" || $char === '"') && ($index === 0 || $input[$index - 1] !== '\\')) {
            if ($quote === '') {
                $quote = $char;
            } elseif ($quote === $char) {
                $quote = '';
            }
        }

        if ($quote === '') {
            if ($char === '(') {
                $depth++;
            } elseif ($char === ')' && $depth > 0) {
                $depth--;
            } elseif ($char === ',' && $depth === 0) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $items[] = $trimmed;
                }
                $current = '';
                continue;
            }
        }

        $current .= $char;
    }

    $trimmed = trim($current);
    if ($trimmed !== '') {
        $items[] = $trimmed;
    }

    return $items;
}

function app_config_db_bootstrap_sqlite_convert_column_definition(string $definition): string
{
    $converted = preg_replace('/\s+AFTER\s+[A-Za-z0-9_]+$/i', '', trim($definition));
    if (!is_string($converted)) {
        $converted = trim($definition);
    }
    $converted = preg_replace('/\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $converted);
    if (!is_string($converted)) {
        $converted = trim($definition);
    }

    if (preg_match('/^([A-Za-z0-9_]+)\s+BIGINT\s+UNSIGNED\s+NOT\s+NULL\s+AUTO_INCREMENT$/i', $converted, $matches) === 1) {
        return $matches[1] . ' INTEGER PRIMARY KEY AUTOINCREMENT';
    }

    $replacements = [
        '/\bBIGINT\s+UNSIGNED\b/i' => 'INTEGER',
        '/\bINT\s+UNSIGNED\b/i' => 'INTEGER',
        '/\bTINYINT\s*\(\s*1\s*\)/i' => 'INTEGER',
        '/\bVARCHAR\s*\(\s*\d+\s*\)/i' => 'TEXT',
        '/\bMEDIUMTEXT\b/i' => 'TEXT',
        '/\bDATETIME\b/i' => 'TEXT',
        '/\bTIMESTAMP\b/i' => 'TEXT',
        '/\bINT\b/i' => 'INTEGER',
    ];

    foreach ($replacements as $pattern => $replacement) {
        $next = preg_replace($pattern, $replacement, $converted);
        if (is_string($next)) {
            $converted = $next;
        }
    }

    if (preg_match('/^(IsNull)\b/', $converted, $matches) === 1) {
        $converted = '"' . $matches[1] . '"' . substr($converted, strlen($matches[1]));
    }

    return $converted;
}

function app_config_db_bootstrap_sqlite_convert_create_table_statement(string $statement): string
{
    if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?([A-Za-z0-9_]+)\s*\((.*)\)\s*ENGINE\s*=/is', trim($statement), $matches) !== 1) {
        throw new RuntimeException('SQLite schema へ変換できない CREATE TABLE です。');
    }

    $tableName = $matches[1];
    $entries = app_config_db_bootstrap_split_top_level_csv($matches[2]);
    $autoIncrementColumns = [];
    foreach ($entries as $entry) {
        if (preg_match('/^([A-Za-z0-9_]+)\s+BIGINT\s+UNSIGNED\s+NOT\s+NULL\s+AUTO_INCREMENT/i', $entry, $columnMatches) === 1) {
            $autoIncrementColumns[] = $columnMatches[1];
        }
    }

    $convertedEntries = [];
    foreach ($entries as $entry) {
        $normalized = ltrim($entry);
        if (preg_match('/^(UNIQUE\s+KEY|KEY|CONSTRAINT|FOREIGN\s+KEY)\b/i', $normalized) === 1) {
            continue;
        }
        if (preg_match('/^PRIMARY\s+KEY\s*\(\s*([A-Za-z0-9_]+)\s*\)$/i', $normalized, $primaryMatches) === 1
            && in_array($primaryMatches[1], $autoIncrementColumns, true)
        ) {
            continue;
        }

        if (preg_match('/^PRIMARY\s+KEY\b/i', $normalized) === 1) {
            $convertedEntries[] = $normalized;
            continue;
        }

        $convertedEntries[] = app_config_db_bootstrap_sqlite_convert_column_definition($normalized);
    }

    return sprintf(
        "CREATE TABLE IF NOT EXISTS %s (\n    %s\n)",
        $tableName,
        implode(",\n    ", $convertedEntries),
    );
}

/**
 * @return list<string>
 */
function app_config_db_bootstrap_sqlite_prepare_statement(PDO $pdo, string $statement): array
{
    $trimmed = trim(preg_replace('/^\s*--.*$/m', '', $statement) ?? $statement);
    if ($trimmed === '') {
        return [];
    }

    if (preg_match('/^CREATE\s+TABLE\b/i', $trimmed) === 1) {
        return [app_config_db_bootstrap_sqlite_convert_create_table_statement($trimmed)];
    }

    if (preg_match('/^ALTER\s+TABLE\s+([A-Za-z0-9_]+)\s+ADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\s+(.+)$/is', $trimmed, $matches) === 1) {
        $tableName = $matches[1];
        $columnDefinition = app_config_db_bootstrap_sqlite_convert_column_definition($matches[2]);
        $columnName = strtok($columnDefinition, " \t\r\n");
        if (is_string($columnName)) {
            $columnName = trim($columnName, '"');
        }
        if (!is_string($columnName) || $columnName === '' || app_sql_column_exists($pdo, $tableName, $columnName)) {
            return [];
        }

        return ['ALTER TABLE ' . $tableName . ' ADD COLUMN ' . $columnDefinition];
    }

    if (preg_match('/^(DROP\s+INDEX|ALTER\s+TABLE\s+[A-Za-z0-9_]+\s+DROP\s+COLUMN)\b/i', $trimmed) === 1) {
        return [];
    }

    return [$trimmed];
}

/**
 * @return list<string>
 */
function app_config_db_bootstrap_required_tables(): array
{
    return [
        'projects',
        'project_memberships',
        'project_identity_memberships',
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
        'project_shared_contracts',
        'project_shared_contract_fields',
        'project_managed_operations',
        'project_managed_operation_fields',
        'project_managed_operation_sync_outbox',
        'no_code_publish_candidate_revisions',
        'no_code_publish_candidate_transition_events',
        'no_code_public_runtime_current_revisions',
        'no_code_public_runtime_aliases',
        'no_code_public_runtime_alias_events',
        'database_sources',
        'audit_events',
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
            'php_namespace',
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
        'dbtable' => [
            'physical_name',
        ],
        'dbtablecolumns' => [
            'physical_name',
        ],
        'dataclass' => [
            'physical_name',
        ],
        'dataclassfields' => [
            'physical_name',
        ],
        'project_db_access_functions' => [
            'auth_policy_version',
            'auth_policy_json',
        ],
        'project_custom_proxies' => [
            'auth_policy_version',
            'auth_policy_json',
        ],
        'no_code_publish_candidate_revisions' => [
            'revision_id',
            'source_output_key',
            'artifact_key',
            'readiness_state',
            'snapshot_json',
            'status',
            'created_by',
        ],
        'no_code_publish_candidate_transition_events' => [
            'candidate_revision_id',
            'revision_id',
            'source_output_key',
            'transition',
            'from_status',
            'to_status',
            'created_by',
        ],
        'no_code_public_runtime_current_revisions' => [
            'candidate_revision_id',
            'revision_id',
            'source_output_key',
            'artifact_key',
            'selected_by',
        ],
        'no_code_public_runtime_aliases' => [
            'alias_key',
            'candidate_revision_id',
            'revision_id',
            'source_output_key',
            'artifact_key',
            'selected_by',
        ],
        'no_code_public_runtime_alias_events' => [
            'alias_key',
            'candidate_revision_id',
            'revision_id',
            'source_output_key',
            'artifact_key',
            'event_type',
            'created_by',
            'metadata_json',
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
    return app_sql_table_exists($pdo, $tableName);
}

function app_config_db_bootstrap_pdo_column_exists(PDO $pdo, string $tableName, string $columnName): bool
{
    return app_sql_column_exists($pdo, $tableName, $columnName);
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
    $dialect = app_sql_dialect_from_db_config($configDb);
    $warnings = [];

    if ($targetMode !== 'compose-local-service') {
        $warnings[] = $dialect === 'sqlite'
            ? 'config DB target は folder-backed SQLite です。空の SQLite store は初回 bootstrap 時に current initdb から自動作成されます。'
            : 'config DB target は local compose 既定値ではありません。target DB に current initdb を適用してから使ってください。';
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
        $version = app_sql_server_version($pdo);
        $databaseName = app_sql_current_database_name($pdo);
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

        $summary['version'] = $version;
        $summary['resolved_database_name'] = $databaseName;
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
    $dialect = app_sql_dialect_from_db_config($configDb);
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
        if ($dialect === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }
        $version = app_sql_server_version($pdo);
        $databaseName = app_sql_current_database_name($pdo);
        $appliedFiles = [];

        foreach ($sqlFiles as $sqlFile) {
            $contents = file_get_contents($sqlFile);
            if (!is_string($contents)) {
                throw new RuntimeException('SQL file を読み込めません: ' . $sqlFile);
            }

            if (trim($contents) === '') {
                continue;
            }

            if ($dialect === 'sqlite') {
                $appliedStatementCount = 0;
                foreach (app_config_db_bootstrap_split_sql_statements($contents) as $statement) {
                    foreach (app_config_db_bootstrap_sqlite_prepare_statement($pdo, $statement) as $sqliteStatement) {
                        $pdo->exec($sqliteStatement);
                        $appliedStatementCount++;
                    }
                }
                if ($appliedStatementCount > 0) {
                    $appliedFiles[] = basename($sqlFile);
                }
                continue;
            }

            $pdo->exec($contents);
            $appliedFiles[] = basename($sqlFile);
        }

        $postPreflight = app_config_db_bootstrap_preflight($app, [
            'sql_dir' => $resolvedSqlDir,
        ]);
        $summary['version'] = $version;
        $summary['resolved_database_name'] = $databaseName;
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
