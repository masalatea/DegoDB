#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/db_access_seed_export_guard.php';
require_once dirname(__DIR__) . '/app/legacy_db_access_dump.php';

/**
 * @return string
 */
function app_cli_export_mtool_db_access_seed_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_mtool_db_access_seed.php [options]

Options:
  --host-side               host-side 明示実行であることを確認する
  --project-key=KEY         canonical project key (default: MTOOL)
  --legacy-project-pid=PID  legacy Project.PID (default: 1)
  --host=HOST               MariaDB host (default: 127.0.0.1)
  --port=PORT               MariaDB port (default: .env CONFIG_DB_HOST_PORT or 33061)
  --config-db-name=NAME     canonical config DB name (default: .env CONFIG_DB_NAME or config_app)
  --legacy-db-name=NAME     temporary imported legacy DB name (default: legacy_seed_tmp)
  --sql-dump=PATH           legacy source SQL dump path on the host filesystem
  --db-user=USER            MariaDB user with access to both DBs (default: root)
  --db-password=PASSWORD    MariaDB password (default: .env CONFIG_DB_ROOT_PASSWORD)
  --dbclasses-root=DIR      runtime dbclasses root for blob/file contract checks (default: APP_REFERENCE_ROOT or mtool/reference/dbclasses)
  --output-dir=DIR          seed output directory relative to repo root (default: mtool/docker/mariadb/config-seed)
  --help                    show this help

Notes:
  - `original-codes/` is host-side reference only and is not mounted into the base Docker runtime.
  - Use `--sql-dump=original-codes/mtool.sql` from the host, or import a temporary `legacy_seed_tmp` DB explicitly.
  - Re-run with `--host-side` only when you intentionally refresh seed files from host-side inputs.
TEXT;
}

/**
 * @return array<string,string>
 */
function app_cli_export_mtool_db_access_seed_env_defaults(): array
{
    $envPath = dirname(__DIR__, 2) . '/.env';
    if (!is_file($envPath)) {
        return [];
    }

    $parsed = parse_ini_file($envPath, false, INI_SCANNER_RAW);
    if (!is_array($parsed)) {
        return [];
    }

    $defaults = [];
    foreach ($parsed as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }

        $defaults[$key] = (string) $value;
    }

    return $defaults;
}

/**
 * @param list<string> $argv
 * @param array<string,string> $defaults
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     legacy_project_pid:int,
 *     host:string,
 *     port:string,
 *     config_db_name:string,
 *     legacy_db_name:string,
 *     sql_dump_path:string,
 *     db_user:string,
 *     db_password:string,
 *     dbclasses_root:string,
 *     output_dir:string,
 *     error:string
 * }
 */
function app_cli_export_mtool_db_access_seed_parse_args(array $argv, array $defaults): array
{
    $parsed = [
        'project_key' => 'MTOOL',
        'legacy_project_pid' => 1,
        'host' => '127.0.0.1',
        'port' => $defaults['CONFIG_DB_HOST_PORT'] ?? '33061',
        'config_db_name' => $defaults['CONFIG_DB_NAME'] ?? 'config_app',
        'legacy_db_name' => 'legacy_seed_tmp',
        'sql_dump_path' => '',
        'db_user' => 'root',
        'db_password' => $defaults['CONFIG_DB_ROOT_PASSWORD'] ?? '',
        'dbclasses_root' => (string) (getenv('APP_REFERENCE_ROOT') !== false ? getenv('APP_REFERENCE_ROOT') : 'mtool/reference/dbclasses'),
        'output_dir' => 'mtool/docker/mariadb/config-seed',
    ];
    $hostSideConfirmed = false;

    $emptyResult = static function (bool $ok, bool $help, string $error): array {
        return [
            'ok' => $ok,
            'help' => $help,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'host' => '',
            'port' => '',
            'config_db_name' => '',
            'legacy_db_name' => '',
            'sql_dump_path' => '',
            'db_user' => '',
            'db_password' => '',
            'dbclasses_root' => '',
            'output_dir' => '',
            'error' => $error,
        ];
    };

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return $emptyResult(true, true, '');
        }
        if ($argument === '--host-side') {
            $hostSideConfirmed = true;
            continue;
        }

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return $emptyResult(false, false, '未対応の引数です: ' . $argument);
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        switch ($name) {
            case 'project-key':
                $parsed['project_key'] = strtoupper(trim($value));
                break;
            case 'legacy-project-pid':
                if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                    return $emptyResult(false, false, 'legacy-project-pid は 1 以上の整数で指定してください。');
                }

                $parsed['legacy_project_pid'] = (int) $value;
                break;
            case 'host':
                $parsed['host'] = trim($value);
                break;
            case 'port':
                $parsed['port'] = trim($value);
                break;
            case 'config-db-name':
                $parsed['config_db_name'] = trim($value);
                break;
            case 'legacy-db-name':
                $parsed['legacy_db_name'] = trim($value);
                break;
            case 'sql-dump':
                $parsed['sql_dump_path'] = trim($value);
                break;
            case 'db-user':
                $parsed['db_user'] = trim($value);
                break;
            case 'db-password':
                $parsed['db_password'] = $value;
                break;
            case 'dbclasses-root':
                $parsed['dbclasses_root'] = trim($value);
                break;
            case 'output-dir':
                $parsed['output_dir'] = trim($value);
                break;
            default:
                return $emptyResult(false, false, '未対応の引数です: --' . $name);
        }
    }

    if (!$hostSideConfirmed) {
        return $emptyResult(false, false, 'この helper は host-side 明示実行専用です。`--host-side` を付けて再実行してください。');
    }

    if ($parsed['project_key'] === '') {
        return $emptyResult(false, false, 'project-key を指定してください。');
    }

    $identifierKeys = ['config_db_name'];
    if ($parsed['sql_dump_path'] === '') {
        $identifierKeys[] = 'legacy_db_name';
    }

    foreach ($identifierKeys as $identifierKey) {
        if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $parsed[$identifierKey])) {
            return $emptyResult(false, false, $identifierKey . ' は英数字とアンダースコアのみで指定してください。');
        }
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $parsed['project_key'],
        'legacy_project_pid' => $parsed['legacy_project_pid'],
        'host' => $parsed['host'],
        'port' => $parsed['port'],
        'config_db_name' => $parsed['config_db_name'],
        'legacy_db_name' => $parsed['legacy_db_name'],
        'sql_dump_path' => $parsed['sql_dump_path'],
        'db_user' => $parsed['db_user'],
        'db_password' => $parsed['db_password'],
        'dbclasses_root' => $parsed['dbclasses_root'],
        'output_dir' => $parsed['output_dir'],
        'error' => '',
    ];
}

function app_cli_export_mtool_db_access_seed_create_pdo(
    string $host,
    string $port,
    string $databaseName,
    string $user,
    string $password,
): PDO {
    return new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $databaseName),
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
}

/**
 * @param array<string,mixed> $params
 * @return list<array<string,mixed>>
 */
function app_cli_export_mtool_db_access_seed_fetch_all(PDO $pdo, string $sql, array $params = []): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    $rows = $statement->fetchAll();
    $items = [];
    foreach ($rows as $row) {
        if (is_array($row)) {
            $items[] = $row;
        }
    }

    return $items;
}

/**
 * @param list<array<string,mixed>> $rows
 * @return list<array<string,mixed>>
 */
function app_cli_export_mtool_db_access_seed_normalize_rows(array $rows, array $stringColumns, array $intColumns): array
{
    $normalized = [];
    foreach ($rows as $row) {
        $item = [];
        foreach ($stringColumns as $column) {
            $item[$column] = (string) ($row[$column] ?? '');
        }
        foreach ($intColumns as $column) {
            $item[$column] = (int) ($row[$column] ?? 0);
        }
        $normalized[] = $item;
    }

    return $normalized;
}

function app_cli_export_mtool_db_access_seed_sql_literal(PDO $pdo, mixed $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return $pdo->quote((string) $value);
}

/**
 * @param list<string> $columns
 * @param list<array<string,mixed>> $rows
 * @return list<string>
 */
function app_cli_export_mtool_db_access_seed_build_temp_table_insert_lines(
    PDO $pdo,
    string $tableName,
    array $columns,
    array $rows,
    int $chunkSize = 250,
): array {
    $lines = [];
    if ($rows === []) {
        return $lines;
    }

    $columnList = implode(
        ', ',
        array_map(
            static fn (string $column): string => '`' . $column . '`',
            $columns,
        ),
    );

    foreach (array_chunk($rows, $chunkSize) as $chunk) {
        $lines[] = 'INSERT INTO `' . $tableName . '` (' . $columnList . ') VALUES';

        $valueLines = [];
        foreach ($chunk as $row) {
            $literals = [];
            foreach ($columns as $column) {
                $literals[] = app_cli_export_mtool_db_access_seed_sql_literal($pdo, $row[$column] ?? null);
            }
            $valueLines[] = '    (' . implode(', ', $literals) . ')';
        }

        $lines[] = implode(",\n", $valueLines) . ';';
        $lines[] = '';
    }

    return $lines;
}

/**
 * @param list<string> $lines
 */
function app_cli_export_mtool_db_access_seed_write_file(string $path, array $lines): void
{
    $content = implode(PHP_EOL, $lines);
    if (!str_ends_with($content, PHP_EOL)) {
        $content .= PHP_EOL;
    }

    $result = file_put_contents($path, $content);
    if ($result === false) {
        throw new RuntimeException('seed file を書き込めませんでした: ' . $path);
    }
}

/**
 * @param list<array<string,mixed>> $classRows
 * @param list<array<string,mixed>> $functionRows
 * @return list<string>
 */
function app_cli_export_mtool_db_access_seed_build_class_function_file(
    PDO $pdo,
    string $projectKey,
    array $classRows,
    array $functionRows,
): array {
    $lines = [
        '-- Generated by mtool/scripts/export_mtool_db_access_seed.php',
        '-- Canonical DB Access class/function seed for ' . $projectKey,
        '',
        'DROP TEMPORARY TABLE IF EXISTS `tmp_seed_project_db_access_classes`;',
        'CREATE TEMPORARY TABLE `tmp_seed_project_db_access_classes` (',
        '    `source_name` VARCHAR(191) NOT NULL,',
        '    `store_base_path` VARCHAR(512) NOT NULL,',
        '    `is_autoload` TINYINT(1) NOT NULL,',
        '    `notes` TEXT NOT NULL,',
        '    `source_of_truth` VARCHAR(32) NOT NULL,',
        '    `last_detected_dbaccess_file` VARCHAR(191) NOT NULL,',
        '    `last_detected_data_file` VARCHAR(191) NOT NULL',
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
        '',
    ];

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_temp_table_insert_lines(
            $pdo,
            'tmp_seed_project_db_access_classes',
            [
                'source_name',
                'store_base_path',
                'is_autoload',
                'notes',
                'source_of_truth',
                'last_detected_dbaccess_file',
                'last_detected_data_file',
            ],
            $classRows,
        ),
    );

    $projectLiteral = app_cli_export_mtool_db_access_seed_sql_literal($pdo, $projectKey);
    $lines = array_merge(
        $lines,
        [
            'INSERT INTO project_db_access_classes (',
            '    project_id,',
            '    source_name,',
            '    store_base_path,',
            '    is_autoload,',
            '    notes,',
            '    source_of_truth,',
            '    last_detected_dbaccess_file,',
            '    last_detected_data_file',
            ')',
            'SELECT',
            '    p.id,',
            '    seed.source_name,',
            '    seed.store_base_path,',
            '    seed.is_autoload,',
            '    seed.notes,',
            '    seed.source_of_truth,',
            '    seed.last_detected_dbaccess_file,',
            '    seed.last_detected_data_file',
            'FROM projects AS p',
            'INNER JOIN tmp_seed_project_db_access_classes AS seed',
            'WHERE p.project_key = ' . $projectLiteral,
            'ON DUPLICATE KEY UPDATE',
            '    store_base_path = IF(project_db_access_classes.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_classes.store_base_path, VALUES(store_base_path)),',
            '    is_autoload = IF(project_db_access_classes.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_classes.is_autoload, VALUES(is_autoload)),',
            '    notes = IF(project_db_access_classes.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_classes.notes, VALUES(notes)),',
            '    source_of_truth = IF(project_db_access_classes.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_classes.source_of_truth, VALUES(source_of_truth)),',
            '    last_detected_dbaccess_file = VALUES(last_detected_dbaccess_file),',
            '    last_detected_data_file = VALUES(last_detected_data_file),',
            '    updated_at = CURRENT_TIMESTAMP;',
            '',
            'DROP TEMPORARY TABLE IF EXISTS `tmp_seed_project_db_access_classes`;',
            '',
            'DROP TEMPORARY TABLE IF EXISTS `tmp_seed_project_db_access_functions`;',
            'CREATE TEMPORARY TABLE `tmp_seed_project_db_access_functions` (',
            '    `source_name` VARCHAR(191) NOT NULL,',
            '    `function_name` VARCHAR(191) NOT NULL,',
            '    `function_list_order` INT UNSIGNED NOT NULL,',
            '    `function_suffix` VARCHAR(191) NOT NULL,',
            '    `action_type` VARCHAR(32) NOT NULL,',
            '    `data_class_base_name` VARCHAR(191) NOT NULL,',
            '    `target_table_name` VARCHAR(191) NOT NULL,',
            '    `parameter_type` VARCHAR(64) NOT NULL,',
            '    `select_by_distinct` TINYINT(1) NOT NULL,',
            '    `sort_order_columns` VARCHAR(512) NOT NULL,',
            '    `memo` TEXT NOT NULL,',
            '    `limit_parameter_type` VARCHAR(64) NOT NULL,',
            '    `limit_fixed_parameter` VARCHAR(191) NOT NULL,',
            '    `or_group_type` VARCHAR(64) NOT NULL,',
            '    `single_proxy_auth_type` VARCHAR(64) NOT NULL,',
            '    `single_proxy_single_get_function_name` VARCHAR(191) NOT NULL,',
            '    `is_blob_target` TINYINT(1) NOT NULL,',
            '    `detected_signature` VARCHAR(512) NOT NULL,',
            '    `detected_line` INT UNSIGNED NOT NULL,',
            '    `source_of_truth` VARCHAR(32) NOT NULL',
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            '',
        ],
    );

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_temp_table_insert_lines(
            $pdo,
            'tmp_seed_project_db_access_functions',
            [
                'source_name',
                'function_name',
                'function_list_order',
                'function_suffix',
                'action_type',
                'data_class_base_name',
                'target_table_name',
                'parameter_type',
                'select_by_distinct',
                'sort_order_columns',
                'memo',
                'limit_parameter_type',
                'limit_fixed_parameter',
                'or_group_type',
                'single_proxy_auth_type',
                'single_proxy_single_get_function_name',
                'is_blob_target',
                'detected_signature',
                'detected_line',
                'source_of_truth',
            ],
            $functionRows,
        ),
    );

    $lines = array_merge(
        $lines,
        [
            'INSERT INTO project_db_access_functions (',
            '    db_access_class_id,',
            '    function_name,',
            '    function_list_order,',
            '    function_suffix,',
            '    action_type,',
            '    data_class_base_name,',
            '    target_table_name,',
            '    parameter_type,',
            '    select_by_distinct,',
            '    sort_order_columns,',
            '    memo,',
            '    limit_parameter_type,',
            '    limit_fixed_parameter,',
            '    or_group_type,',
            '    single_proxy_auth_type,',
            '    single_proxy_single_get_function_name,',
            '    is_blob_target,',
            '    detected_signature,',
            '    detected_line,',
            '    source_of_truth',
            ')',
            'SELECT',
            '    c.id,',
            '    seed.function_name,',
            '    seed.function_list_order,',
            '    seed.function_suffix,',
            '    seed.action_type,',
            '    seed.data_class_base_name,',
            '    seed.target_table_name,',
            '    seed.parameter_type,',
            '    seed.select_by_distinct,',
            '    seed.sort_order_columns,',
            '    seed.memo,',
            '    seed.limit_parameter_type,',
            '    seed.limit_fixed_parameter,',
            '    seed.or_group_type,',
            '    seed.single_proxy_auth_type,',
            '    seed.single_proxy_single_get_function_name,',
            '    seed.is_blob_target,',
            '    seed.detected_signature,',
            '    seed.detected_line,',
            '    seed.source_of_truth',
            'FROM projects AS p',
            'INNER JOIN project_db_access_classes AS c',
            '    ON c.project_id = p.id',
            'INNER JOIN tmp_seed_project_db_access_functions AS seed',
            '    ON seed.source_name = c.source_name',
            'WHERE p.project_key = ' . $projectLiteral,
            'ON DUPLICATE KEY UPDATE',
            '    function_list_order = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.function_list_order, VALUES(function_list_order)),',
            '    function_suffix = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.function_suffix, VALUES(function_suffix)),',
            '    action_type = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.action_type, VALUES(action_type)),',
            '    data_class_base_name = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.data_class_base_name, VALUES(data_class_base_name)),',
            '    target_table_name = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.target_table_name, VALUES(target_table_name)),',
            '    parameter_type = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.parameter_type, VALUES(parameter_type)),',
            '    select_by_distinct = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.select_by_distinct, VALUES(select_by_distinct)),',
            '    sort_order_columns = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.sort_order_columns, VALUES(sort_order_columns)),',
            '    memo = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.memo, VALUES(memo)),',
            '    limit_parameter_type = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.limit_parameter_type, VALUES(limit_parameter_type)),',
            '    limit_fixed_parameter = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.limit_fixed_parameter, VALUES(limit_fixed_parameter)),',
            '    or_group_type = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.or_group_type, VALUES(or_group_type)),',
            '    single_proxy_auth_type = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.single_proxy_auth_type, VALUES(single_proxy_auth_type)),',
            '    single_proxy_single_get_function_name = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.single_proxy_single_get_function_name, VALUES(single_proxy_single_get_function_name)),',
            '    is_blob_target = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.is_blob_target, VALUES(is_blob_target)),',
            '    detected_signature = VALUES(detected_signature),',
            '    detected_line = VALUES(detected_line),',
            '    source_of_truth = IF(project_db_access_functions.source_of_truth IN (\'manual\', \'seed-legacy\'), project_db_access_functions.source_of_truth, VALUES(source_of_truth)),',
            '    updated_at = CURRENT_TIMESTAMP;',
            '',
            'DROP TEMPORARY TABLE IF EXISTS `tmp_seed_project_db_access_functions`;',
        ],
    );

    return $lines;
}

/**
 * @param list<array<string,mixed>> $rows
 * @return list<string>
 */
function app_cli_export_mtool_db_access_seed_build_selectlist_sort_order_backfill_file(
    PDO $pdo,
    string $projectKey,
    array $rows,
): array {
    $lines = [
        '-- Generated by mtool/scripts/export_mtool_db_access_seed.php',
        '-- Backfill SELECTLIST sort_order_columns from Project 1 legacy baseline for ' . $projectKey,
        '',
    ];

    if ($rows === []) {
        $lines[] = '-- No legacy SELECTLIST sort_order_columns rows were matched.';

        return $lines;
    }

    $projectLiteral = app_cli_export_mtool_db_access_seed_sql_literal($pdo, $projectKey);
    foreach ($rows as $row) {
        $sourceNameLiteral = app_cli_export_mtool_db_access_seed_sql_literal(
            $pdo,
            (string) ($row['source_name'] ?? ''),
        );
        $functionNameLiteral = app_cli_export_mtool_db_access_seed_sql_literal(
            $pdo,
            (string) ($row['function_name'] ?? ''),
        );
        $sortOrderColumnsLiteral = app_cli_export_mtool_db_access_seed_sql_literal(
            $pdo,
            (string) ($row['sort_order_columns'] ?? ''),
        );

        $lines = array_merge(
            $lines,
            [
                'UPDATE project_db_access_functions AS f',
                'INNER JOIN project_db_access_classes AS c',
                '    ON c.id = f.db_access_class_id',
                'INNER JOIN projects AS p',
                '    ON p.id = c.project_id',
                'SET',
                '    f.sort_order_columns = IF(',
                '        f.source_of_truth = \'manual\' AND f.sort_order_columns <> \'\',',
                '        f.sort_order_columns,',
                '        ' . $sortOrderColumnsLiteral,
                '    ),',
                '    f.source_of_truth = IF(f.source_of_truth = \'manual\', f.source_of_truth, \'seed-legacy\'),',
                '    f.updated_at = CURRENT_TIMESTAMP',
                'WHERE p.project_key = ' . $projectLiteral,
                '  AND c.source_name = ' . $sourceNameLiteral,
                '  AND f.function_name = ' . $functionNameLiteral,
                '  AND f.action_type = \'SELECTLIST\';',
                '',
            ],
        );
    }

    return $lines;
}

/**
 * @param list<array<string,mixed>> $rows
 * @param list<string> $tempColumns
 * @return list<string>
 */
function app_cli_export_mtool_db_access_seed_build_designer_section(
    PDO $pdo,
    string $projectKey,
    string $tempTableName,
    string $targetTableName,
    string $createColumnsSql,
    array $tempColumns,
    array $rows,
    array $targetColumns,
): array {
    $projectLiteral = app_cli_export_mtool_db_access_seed_sql_literal($pdo, $projectKey);
    $lines = [
        'DROP TEMPORARY TABLE IF EXISTS `' . $tempTableName . '`;',
        'CREATE TEMPORARY TABLE `' . $tempTableName . '` (',
        $createColumnsSql,
        ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
        '',
    ];

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_temp_table_insert_lines(
            $pdo,
            $tempTableName,
            $tempColumns,
            $rows,
        ),
    );

    $lines = array_merge(
        $lines,
        [
            'DELETE target',
            'FROM ' . $targetTableName . ' AS target',
            'INNER JOIN project_db_access_functions AS f',
            '    ON f.id = target.db_access_function_id',
            'INNER JOIN project_db_access_classes AS c',
            '    ON c.id = f.db_access_class_id',
            'INNER JOIN projects AS p',
            '    ON p.id = c.project_id',
            'WHERE p.project_key = ' . $projectLiteral,
            '  AND target.source_of_truth = \'seed-legacy\';',
            '',
        ],
    );

    if ($rows !== []) {
        $targetColumnList = implode(
            ', ',
            array_map(
                static fn (string $column): string => '    ' . $column,
                $targetColumns,
            ),
        );
        $selectColumnList = [];
        foreach ($targetColumns as $column) {
            if ($column === 'db_access_function_id') {
                $selectColumnList[] = '    f.id';
                continue;
            }

            $selectColumnList[] = '    seed.' . $column;
        }

        $lines = array_merge(
            $lines,
            [
                'INSERT INTO ' . $targetTableName . ' (',
                $targetColumnList,
                ')',
                'SELECT',
                implode(',' . PHP_EOL, $selectColumnList),
                'FROM `' . $tempTableName . '` AS seed',
                'INNER JOIN projects AS p',
                '    ON p.project_key = ' . $projectLiteral,
                'INNER JOIN project_db_access_classes AS c',
                '    ON c.project_id = p.id',
                '   AND c.source_name = seed.source_name',
                'INNER JOIN project_db_access_functions AS f',
                '    ON f.db_access_class_id = c.id',
                '   AND f.function_name = seed.function_name;',
                '',
            ],
        );
    }

    $lines[] = 'DROP TEMPORARY TABLE IF EXISTS `' . $tempTableName . '`;';
    $lines[] = '';

    return $lines;
}

/**
 * @param array<string,list<array<string,mixed>>> $designerRows
 * @return list<string>
 */
function app_cli_export_mtool_db_access_seed_build_designer_file(PDO $pdo, string $projectKey, array $designerRows): array
{
    $lines = [
        '-- Generated by mtool/scripts/export_mtool_db_access_seed.php',
        '-- Canonical DB Access designer seed for ' . $projectKey,
        '',
    ];

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_designer_section(
            $pdo,
            $projectKey,
            'tmp_seed_project_db_access_function_select_wheres',
            'project_db_access_function_select_wheres',
            "    `source_name` VARCHAR(191) NOT NULL,\n    `function_name` VARCHAR(191) NOT NULL,\n    `target_table_name` VARCHAR(191) NOT NULL,\n    `target_table_alias_name` VARCHAR(191) NOT NULL,\n    `target_table_column_name` VARCHAR(191) NOT NULL,\n    `parameter_type` VARCHAR(32) NOT NULL,\n    `parameter_data_type` VARCHAR(32) NOT NULL,\n    `fixed_parameter` TEXT NOT NULL,\n    `another_table_name` VARCHAR(191) NOT NULL,\n    `another_table_alias_name` VARCHAR(191) NOT NULL,\n    `another_field_name` VARCHAR(191) NOT NULL,\n    `join_type` VARCHAR(32) NOT NULL,\n    `or_group` VARCHAR(64) NOT NULL,\n    `relational_operator` VARCHAR(32) NOT NULL,\n    `where_order` INT UNSIGNED NOT NULL,\n    `source_of_truth` VARCHAR(32) NOT NULL",
            [
                'source_name',
                'function_name',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'another_table_name',
                'another_table_alias_name',
                'another_field_name',
                'join_type',
                'or_group',
                'relational_operator',
                'where_order',
                'source_of_truth',
            ],
            $designerRows['select_wheres'],
            [
                'db_access_function_id',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'another_table_name',
                'another_table_alias_name',
                'another_field_name',
                'join_type',
                'or_group',
                'relational_operator',
                'where_order',
                'source_of_truth',
            ],
        ),
    );

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_designer_section(
            $pdo,
            $projectKey,
            'tmp_seed_project_db_access_function_select_target_fields',
            'project_db_access_function_select_target_fields',
            "    `source_name` VARCHAR(191) NOT NULL,\n    `function_name` VARCHAR(191) NOT NULL,\n    `target_table_name` VARCHAR(191) NOT NULL,\n    `target_table_alias_name` VARCHAR(191) NOT NULL,\n    `target_table_column_name` VARCHAR(191) NOT NULL,\n    `target_table_column_prefix` VARCHAR(191) NOT NULL,\n    `target_table_column_suffix` VARCHAR(191) NOT NULL,\n    `store_class_field_name` VARCHAR(191) NOT NULL,\n    `group_by_target` TINYINT(1) NOT NULL,\n    `field_list_order` INT UNSIGNED NOT NULL,\n    `source_of_truth` VARCHAR(32) NOT NULL",
            [
                'source_name',
                'function_name',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'target_table_column_prefix',
                'target_table_column_suffix',
                'store_class_field_name',
                'group_by_target',
                'field_list_order',
                'source_of_truth',
            ],
            $designerRows['select_target_fields'],
            [
                'db_access_function_id',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'target_table_column_prefix',
                'target_table_column_suffix',
                'store_class_field_name',
                'group_by_target',
                'field_list_order',
                'source_of_truth',
            ],
        ),
    );

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_designer_section(
            $pdo,
            $projectKey,
            'tmp_seed_project_db_access_function_insert_target_fields',
            'project_db_access_function_insert_target_fields',
            "    `source_name` VARCHAR(191) NOT NULL,\n    `function_name` VARCHAR(191) NOT NULL,\n    `target_table_column_name` VARCHAR(191) NOT NULL,\n    `parameter_type` VARCHAR(32) NOT NULL,\n    `parameter_data_type` VARCHAR(32) NOT NULL,\n    `fixed_parameter` TEXT NOT NULL,\n    `field_list_order` INT UNSIGNED NOT NULL,\n    `source_of_truth` VARCHAR(32) NOT NULL",
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'field_list_order',
                'source_of_truth',
            ],
            $designerRows['insert_target_fields'],
            [
                'db_access_function_id',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'field_list_order',
                'source_of_truth',
            ],
        ),
    );

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_designer_section(
            $pdo,
            $projectKey,
            'tmp_seed_project_db_access_function_update_target_fields',
            'project_db_access_function_update_target_fields',
            "    `source_name` VARCHAR(191) NOT NULL,\n    `function_name` VARCHAR(191) NOT NULL,\n    `target_table_column_name` VARCHAR(191) NOT NULL,\n    `parameter_type` VARCHAR(32) NOT NULL,\n    `parameter_data_type` VARCHAR(32) NOT NULL,\n    `fixed_parameter` TEXT NOT NULL,\n    `field_list_order` INT UNSIGNED NOT NULL,\n    `source_of_truth` VARCHAR(32) NOT NULL",
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'field_list_order',
                'source_of_truth',
            ],
            $designerRows['update_target_fields'],
            [
                'db_access_function_id',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'field_list_order',
                'source_of_truth',
            ],
        ),
    );

    $lines = array_merge(
        $lines,
        app_cli_export_mtool_db_access_seed_build_designer_section(
            $pdo,
            $projectKey,
            'tmp_seed_project_db_access_function_update_delete_wheres',
            'project_db_access_function_update_delete_wheres',
            "    `source_name` VARCHAR(191) NOT NULL,\n    `function_name` VARCHAR(191) NOT NULL,\n    `target_table_column_name` VARCHAR(191) NOT NULL,\n    `parameter_type` VARCHAR(32) NOT NULL,\n    `parameter_data_type` VARCHAR(32) NOT NULL,\n    `fixed_parameter` TEXT NOT NULL,\n    `or_group` VARCHAR(64) NOT NULL,\n    `relational_operator` VARCHAR(32) NOT NULL,\n    `where_order` INT UNSIGNED NOT NULL,\n    `source_of_truth` VARCHAR(32) NOT NULL",
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'or_group',
                'relational_operator',
                'where_order',
                'source_of_truth',
            ],
            $designerRows['update_delete_wheres'],
            [
                'db_access_function_id',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'or_group',
                'relational_operator',
                'where_order',
                'source_of_truth',
            ],
        ),
    );

    $projectLiteral = app_cli_export_mtool_db_access_seed_sql_literal($pdo, $projectKey);
    $lines = array_merge(
        $lines,
        [
            '-- Project 1 では select having row が 0 件だったため、ここでは stale seed cleanup のみ行う。',
            'DELETE target',
            'FROM project_db_access_function_select_havings AS target',
            'INNER JOIN project_db_access_functions AS f',
            '    ON f.id = target.db_access_function_id',
            'INNER JOIN project_db_access_classes AS c',
            '    ON c.id = f.db_access_class_id',
            'INNER JOIN projects AS p',
            '    ON p.id = c.project_id',
            'WHERE p.project_key = ' . $projectLiteral,
            '  AND target.source_of_truth = \'seed-legacy\';',
        ],
    );

    return $lines;
}

$defaults = app_cli_export_mtool_db_access_seed_env_defaults();
$parsed = app_cli_export_mtool_db_access_seed_parse_args($argv, $defaults);

if ($parsed['help']) {
    fwrite(STDOUT, app_cli_export_mtool_db_access_seed_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_export_mtool_db_access_seed_usage() . PHP_EOL);
    exit(64);
}

try {
    $pdo = app_cli_export_mtool_db_access_seed_create_pdo(
        $parsed['host'],
        $parsed['port'],
        $parsed['config_db_name'],
        $parsed['db_user'],
        $parsed['db_password'],
    );

    $configDbName = $parsed['config_db_name'];
    $legacyDbName = $parsed['legacy_db_name'];
    $projectKey = $parsed['project_key'];
    $legacyProjectPid = $parsed['legacy_project_pid'];

    $outputDir = $parsed['output_dir'];
    if (!str_starts_with($outputDir, '/')) {
        $outputDir = dirname(__DIR__, 2) . '/' . ltrim($outputDir, '/');
    }
    if (!is_dir($outputDir)) {
        throw new RuntimeException('output-dir が見つかりません: ' . $outputDir);
    }

    $dbclassesRoot = $parsed['dbclasses_root'];
    if ($dbclassesRoot === '') {
        $dbclassesRoot = 'mtool/reference/dbclasses';
    }
    if (!str_starts_with($dbclassesRoot, '/')) {
        $dbclassesRoot = dirname(__DIR__, 2) . '/' . ltrim($dbclassesRoot, '/');
    }
    if (!is_dir($dbclassesRoot)) {
        throw new RuntimeException('dbclasses-root が見つかりません: ' . $dbclassesRoot);
    }
    $sqlDumpPath = trim($parsed['sql_dump_path']);
    if ($sqlDumpPath !== '' && !str_starts_with($sqlDumpPath, '/')) {
        $sqlDumpPath = dirname(__DIR__, 2) . '/' . ltrim($sqlDumpPath, '/');
    }
    if ($sqlDumpPath !== '' && !is_file($sqlDumpPath)) {
        throw new RuntimeException('sql-dump が見つかりません (host-side path expected): ' . $sqlDumpPath);
    }
    $guardApp = [
        'generated' => [
            'dbclasses_root' => $dbclassesRoot,
        ],
    ];

    $classRows = app_cli_export_mtool_db_access_seed_normalize_rows(
        app_cli_export_mtool_db_access_seed_fetch_all(
            $pdo,
            <<<SQL
SELECT
    c.source_name,
    c.store_base_path,
    c.is_autoload,
    c.notes,
    c.source_of_truth,
    c.last_detected_dbaccess_file,
    c.last_detected_data_file
FROM {$configDbName}.project_db_access_classes AS c
INNER JOIN {$configDbName}.projects AS p
    ON p.id = c.project_id
WHERE p.project_key = :project_key
ORDER BY c.source_name
SQL,
            [
                ':project_key' => $projectKey,
            ],
        ),
        [
            'source_name',
            'store_base_path',
            'notes',
            'source_of_truth',
            'last_detected_dbaccess_file',
            'last_detected_data_file',
        ],
        [
            'is_autoload',
        ],
    );

    $functionRows = app_cli_export_mtool_db_access_seed_normalize_rows(
        app_cli_export_mtool_db_access_seed_fetch_all(
            $pdo,
            <<<SQL
SELECT
    c.source_name,
    f.function_name,
    f.function_list_order,
    f.function_suffix,
    f.action_type,
    f.data_class_base_name,
    f.target_table_name,
    f.parameter_type,
    f.select_by_distinct,
    f.sort_order_columns,
    f.memo,
    f.limit_parameter_type,
    f.limit_fixed_parameter,
    f.or_group_type,
    f.single_proxy_auth_type,
    f.single_proxy_single_get_function_name,
    f.is_blob_target,
    f.detected_signature,
    f.detected_line,
    f.source_of_truth
FROM {$configDbName}.project_db_access_functions AS f
INNER JOIN {$configDbName}.project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN {$configDbName}.projects AS p
    ON p.id = c.project_id
WHERE p.project_key = :project_key
ORDER BY c.source_name, f.function_list_order, f.function_name
SQL,
            [
                ':project_key' => $projectKey,
            ],
        ),
        [
            'source_name',
            'function_name',
            'function_suffix',
            'action_type',
            'data_class_base_name',
            'target_table_name',
            'parameter_type',
            'sort_order_columns',
            'memo',
            'limit_parameter_type',
            'limit_fixed_parameter',
            'or_group_type',
            'single_proxy_auth_type',
            'single_proxy_single_get_function_name',
            'detected_signature',
            'source_of_truth',
        ],
        [
            'function_list_order',
            'select_by_distinct',
            'is_blob_target',
            'detected_line',
        ],
    );

    if ($sqlDumpPath !== '') {
        $legacyData = app_legacy_db_access_extract_seed_export_data_from_dump($sqlDumpPath, $legacyProjectPid);
        if (!$legacyData['ok']) {
            throw new RuntimeException((string) ($legacyData['error'] ?? 'legacy dump の読込に失敗しました。'));
        }

        $legacySeedRows = app_legacy_db_access_build_seed_export_rows_from_dump(
            [
                'functions' => $legacyData['functions'],
                'select_wheres' => $legacyData['select_wheres'],
                'select_target_fields' => $legacyData['select_target_fields'],
                'insert_target_fields' => $legacyData['insert_target_fields'],
                'update_target_fields' => $legacyData['update_target_fields'],
                'update_delete_wheres' => $legacyData['update_delete_wheres'],
                'select_havings' => $legacyData['select_havings'],
            ],
            $functionRows,
        );

        $selectListSortOrderRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            $legacySeedRows['selectlist_sort_order_rows'],
            [
                'source_name',
                'function_name',
                'sort_order_columns',
            ],
            [],
        );
        $selectWhereRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            $legacySeedRows['designer_rows']['select_wheres'],
            [
                'source_name',
                'function_name',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'another_table_name',
                'another_table_alias_name',
                'another_field_name',
                'join_type',
                'or_group',
                'relational_operator',
                'source_of_truth',
            ],
            [
                'where_order',
            ],
        );
        $selectTargetFieldRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            $legacySeedRows['designer_rows']['select_target_fields'],
            [
                'source_name',
                'function_name',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'target_table_column_prefix',
                'target_table_column_suffix',
                'store_class_field_name',
                'source_of_truth',
            ],
            [
                'group_by_target',
                'field_list_order',
            ],
        );
        $insertTargetFieldRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            $legacySeedRows['designer_rows']['insert_target_fields'],
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'source_of_truth',
            ],
            [
                'field_list_order',
            ],
        );
        $updateTargetFieldRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            $legacySeedRows['designer_rows']['update_target_fields'],
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'source_of_truth',
            ],
            [
                'field_list_order',
            ],
        );
        $updateDeleteWhereRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            $legacySeedRows['designer_rows']['update_delete_wheres'],
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'or_group',
                'relational_operator',
                'source_of_truth',
            ],
            [
                'where_order',
            ],
        );
        $selectHavingCount = (int) $legacySeedRows['select_having_count'];
    } else {
        $projectLiteral = app_cli_export_mtool_db_access_seed_sql_literal($pdo, $projectKey);
        $legacyProjectPidLiteral = (string) $legacyProjectPid;
        $mappedFunctionNameSql = <<<SQL
CASE lf.ActionType
    WHEN 'selectsingle' THEN CONCAT('Get', lf.name)
    WHEN 'selectlist' THEN CONCAT('Get', lf.name, 'List')
    WHEN 'insert' THEN CONCAT('Insert', lf.name)
    WHEN 'update' THEN CONCAT('Update', lf.name)
    WHEN 'delete' THEN CONCAT('Delete', lf.name)
    ELSE lf.name
END
SQL;

        $selectListSortOrderRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            app_cli_export_mtool_db_access_seed_fetch_all(
                $pdo,
                <<<SQL
SELECT DISTINCT
    lda.name AS source_name,
    {$mappedFunctionNameSql} AS function_name,
    lf.SortOrderColumns AS sort_order_columns
FROM {$legacyDbName}.dafunc AS lf
INNER JOIN {$legacyDbName}.da AS lda
    ON lda.PID = lf.daPID
   AND lda.ProjectPID = {$legacyProjectPidLiteral}
INNER JOIN {$configDbName}.projects AS p
    ON p.project_key = {$projectLiteral}
INNER JOIN {$configDbName}.project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = lda.name
INNER JOIN {$configDbName}.project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = {$mappedFunctionNameSql}
WHERE lf.ProjectPID = {$legacyProjectPidLiteral}
  AND lf.ActionType = 'selectlist'
  AND TRIM(COALESCE(lf.SortOrderColumns, '')) <> ''
ORDER BY lda.name, f.function_list_order, {$mappedFunctionNameSql}
SQL,
                [],
            ),
            [
                'source_name',
                'function_name',
                'sort_order_columns',
            ],
            [],
        );

        $designerBaseJoins = <<<SQL
INNER JOIN {$legacyDbName}.dafunc AS lf
    ON lf.PID = resource.dafuncPID
   AND lf.ProjectPID = {$legacyProjectPidLiteral}
INNER JOIN {$legacyDbName}.da AS lda
    ON lda.PID = lf.daPID
   AND lda.ProjectPID = {$legacyProjectPidLiteral}
INNER JOIN {$configDbName}.projects AS p
    ON p.project_key = {$projectLiteral}
INNER JOIN {$configDbName}.project_db_access_classes AS c
    ON c.project_id = p.id
   AND c.source_name = lda.name
INNER JOIN {$configDbName}.project_db_access_functions AS f
    ON f.db_access_class_id = c.id
   AND f.function_name = {$mappedFunctionNameSql}
SQL;

        $selectWhereRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            app_cli_export_mtool_db_access_seed_fetch_all(
                $pdo,
                <<<SQL
SELECT
    lda.name AS source_name,
    {$mappedFunctionNameSql} AS function_name,
    resource.targetTableName AS target_table_name,
    resource.targetTableAliasName AS target_table_alias_name,
    resource.targetTableColumnName AS target_table_column_name,
    resource.ParameterType AS parameter_type,
    resource.ParameterDataType AS parameter_data_type,
    resource.FixedParameter AS fixed_parameter,
    resource.AnotherTableName AS another_table_name,
    resource.AnotherTableAliasName AS another_table_alias_name,
    resource.AnotherFieldName AS another_field_name,
    resource.JoinType AS join_type,
    resource.ORGroup AS or_group,
    resource.RelationalOperator AS relational_operator,
    resource.WhereOrder AS where_order,
    'seed-legacy' AS source_of_truth
FROM {$legacyDbName}.dafuncselectwhere AS resource
{$designerBaseJoins}
ORDER BY lda.name, lf.FunctionListOrder, lf.name, resource.WhereOrder, resource.PID
SQL,
                [],
            ),
            [
                'source_name',
                'function_name',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'another_table_name',
                'another_table_alias_name',
                'another_field_name',
                'join_type',
                'or_group',
                'relational_operator',
                'source_of_truth',
            ],
            [
                'where_order',
            ],
        );

        $selectTargetFieldRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            app_cli_export_mtool_db_access_seed_fetch_all(
                $pdo,
                <<<SQL
SELECT
    lda.name AS source_name,
    {$mappedFunctionNameSql} AS function_name,
    resource.targetTableName AS target_table_name,
    resource.targetTableAliasName AS target_table_alias_name,
    resource.targetTableColumnName AS target_table_column_name,
    resource.targetTableColumnPrefix AS target_table_column_prefix,
    resource.targetTableColumnSuffix AS target_table_column_suffix,
    resource.storeClassFieldName AS store_class_field_name,
    resource.GroupByTarget AS group_by_target,
    resource.FieldListOrder AS field_list_order,
    'seed-legacy' AS source_of_truth
FROM {$legacyDbName}.dafuncselecttargetfields AS resource
{$designerBaseJoins}
ORDER BY lda.name, lf.FunctionListOrder, lf.name, resource.FieldListOrder, resource.PID
SQL,
                [],
            ),
            [
                'source_name',
                'function_name',
                'target_table_name',
                'target_table_alias_name',
                'target_table_column_name',
                'target_table_column_prefix',
                'target_table_column_suffix',
                'store_class_field_name',
                'source_of_truth',
            ],
            [
                'group_by_target',
                'field_list_order',
            ],
        );

        $insertTargetFieldRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            app_cli_export_mtool_db_access_seed_fetch_all(
                $pdo,
                <<<SQL
SELECT
    lda.name AS source_name,
    {$mappedFunctionNameSql} AS function_name,
    resource.targetTableColumnName AS target_table_column_name,
    resource.ParameterType AS parameter_type,
    resource.ParameterDataType AS parameter_data_type,
    resource.FixedParameter AS fixed_parameter,
    resource.FieldListOrder AS field_list_order,
    'seed-legacy' AS source_of_truth
FROM {$legacyDbName}.dafuncinserttargetfields AS resource
{$designerBaseJoins}
ORDER BY lda.name, lf.FunctionListOrder, lf.name, resource.FieldListOrder, resource.PID
SQL,
                [],
            ),
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'source_of_truth',
            ],
            [
                'field_list_order',
            ],
        );

        $updateTargetFieldRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            app_cli_export_mtool_db_access_seed_fetch_all(
                $pdo,
                <<<SQL
SELECT
    lda.name AS source_name,
    {$mappedFunctionNameSql} AS function_name,
    resource.targetTableColumnName AS target_table_column_name,
    resource.ParameterType AS parameter_type,
    resource.ParameterDataType AS parameter_data_type,
    resource.FixedParameter AS fixed_parameter,
    resource.FieldListOrder AS field_list_order,
    'seed-legacy' AS source_of_truth
FROM {$legacyDbName}.dafuncupdatetargetfields AS resource
{$designerBaseJoins}
ORDER BY lda.name, lf.FunctionListOrder, lf.name, resource.FieldListOrder, resource.PID
SQL,
                [],
            ),
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'source_of_truth',
            ],
            [
                'field_list_order',
            ],
        );

        $updateDeleteWhereRows = app_cli_export_mtool_db_access_seed_normalize_rows(
            app_cli_export_mtool_db_access_seed_fetch_all(
                $pdo,
                <<<SQL
SELECT
    lda.name AS source_name,
    {$mappedFunctionNameSql} AS function_name,
    resource.targetTableColumnName AS target_table_column_name,
    resource.ParameterType AS parameter_type,
    resource.ParameterDataType AS parameter_data_type,
    resource.FixedParameter AS fixed_parameter,
    resource.ORGroup AS or_group,
    resource.RelationalOperator AS relational_operator,
    resource.WhereOrder AS where_order,
    'seed-legacy' AS source_of_truth
FROM {$legacyDbName}.dafuncupdatedeletewhere AS resource
{$designerBaseJoins}
ORDER BY lda.name, lf.FunctionListOrder, lf.name, resource.WhereOrder, resource.PID
SQL,
                [],
            ),
            [
                'source_name',
                'function_name',
                'target_table_column_name',
                'parameter_type',
                'parameter_data_type',
                'fixed_parameter',
                'or_group',
                'relational_operator',
                'source_of_truth',
            ],
            [
                'where_order',
            ],
        );

        $selectHavingRows = app_cli_export_mtool_db_access_seed_fetch_all(
            $pdo,
            <<<SQL
SELECT COUNT(*) AS row_count
FROM {$legacyDbName}.dafuncselecthaving AS resource
{$designerBaseJoins}
SQL,
            [],
        );
        $selectHavingCount = (int) (($selectHavingRows[0]['row_count'] ?? 0));
    }
    if ($selectHavingCount !== 0) {
        throw new RuntimeException('select having seed export はまだ 0 件前提です。legacy rows=' . $selectHavingCount);
    }

    $blobContractErrors = app_db_access_seed_export_collect_blob_contract_errors(
        $guardApp,
        $classRows,
        $functionRows,
        [
            'select_wheres' => $selectWhereRows,
            'insert_target_fields' => $insertTargetFieldRows,
            'update_target_fields' => $updateTargetFieldRows,
            'update_delete_wheres' => $updateDeleteWhereRows,
        ],
    );
    if ($blobContractErrors !== []) {
        throw new RuntimeException(
            "blob/file seed export guard に失敗しました:\n" . implode("\n", $blobContractErrors),
        );
    }

    $classFunctionFilePath = $outputDir . '/019_project_db_access_class_function_seed.sql';
    $designerFilePath = $outputDir . '/020_project_db_access_designer_seed.sql';
    $selectListSortOrderBackfillFilePath = $outputDir . '/022_backfill_runtime_legacy_selectlist_sort_order_columns.sql';

    app_cli_export_mtool_db_access_seed_write_file(
        $classFunctionFilePath,
        app_cli_export_mtool_db_access_seed_build_class_function_file(
            $pdo,
            $projectKey,
            $classRows,
            $functionRows,
        ),
    );

    app_cli_export_mtool_db_access_seed_write_file(
        $designerFilePath,
        app_cli_export_mtool_db_access_seed_build_designer_file(
            $pdo,
            $projectKey,
            [
                'select_wheres' => $selectWhereRows,
                'select_target_fields' => $selectTargetFieldRows,
                'insert_target_fields' => $insertTargetFieldRows,
                'update_target_fields' => $updateTargetFieldRows,
                'update_delete_wheres' => $updateDeleteWhereRows,
            ],
        ),
    );

    app_cli_export_mtool_db_access_seed_write_file(
        $selectListSortOrderBackfillFilePath,
        app_cli_export_mtool_db_access_seed_build_selectlist_sort_order_backfill_file(
            $pdo,
            $projectKey,
            $selectListSortOrderRows,
        ),
    );

    fwrite(
        STDOUT,
        json_encode(
            [
                'ok' => true,
                'project_key' => $projectKey,
                'legacy_project_pid' => $legacyProjectPid,
                'legacy_source' => $sqlDumpPath !== '' ? $sqlDumpPath : $legacyDbName,
                'dbclasses_root' => $dbclassesRoot,
                'class_count' => count($classRows),
                'function_count' => count($functionRows),
                'blob_contract_guard_error_count' => count($blobContractErrors),
                'selectlist_sort_order_backfill_count' => count($selectListSortOrderRows),
                'designer_counts' => [
                    'select_wheres' => count($selectWhereRows),
                    'select_target_fields' => count($selectTargetFieldRows),
                    'select_havings' => $selectHavingCount,
                    'insert_target_fields' => count($insertTargetFieldRows),
                    'update_target_fields' => count($updateTargetFieldRows),
                    'update_delete_wheres' => count($updateDeleteWhereRows),
                ],
                'files' => [
                    'class_function_seed' => $classFunctionFilePath,
                    'designer_seed' => $designerFilePath,
                    'selectlist_sort_order_backfill' => $selectListSortOrderBackfillFilePath,
                ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit(0);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
