#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/database.php';
require_once __DIR__ . '/lib/project_language_resource_db_bridge.php';

function app_cli_lang_res_drop_tables_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php [options]

Options:
  --apply                       drop LanguageResource canonical tables when all are empty
  --db-host=HOST                APP_DB_HOST override
  --db-port=PORT                APP_DB_PORT override
  --db-name=NAME                APP_DB_NAME override
  --db-user=USER                APP_DB_USER override
  --db-password=PASSWORD        APP_DB_PASSWORD override
  --config-db-host=HOST         APP_CONFIG_DB_HOST override
  --config-db-port=PORT         APP_CONFIG_DB_PORT override
  --config-db-name=NAME         APP_CONFIG_DB_NAME override
  --config-db-user=USER         APP_CONFIG_DB_USER override
  --config-db-password=PASSWORD APP_CONFIG_DB_PASSWORD override
  --help                        show this help
TEXT;
}

function app_cli_lang_res_drop_tables_apply_env(array $overrides): void
{
    foreach ($overrides as $key => $value) {
        if (!is_string($key) || !is_string($value)) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     apply:bool,
 *     db_host:string,
 *     db_port:string,
 *     db_name:string,
 *     db_user:string,
 *     db_password:string,
 *     config_db_host:string,
 *     config_db_port:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string,
 *     error:string
 * }
 */
function app_cli_lang_res_drop_tables_parse_args(array $argv): array
{
    $parsed = [
        'apply' => false,
        'db_host' => getenv('APP_DB_HOST') ?: '',
        'db_port' => getenv('APP_DB_PORT') ?: '',
        'db_name' => getenv('APP_DB_NAME') ?: '',
        'db_user' => getenv('APP_DB_USER') ?: '',
        'db_password' => getenv('APP_DB_PASSWORD') ?: '',
        'config_db_host' => getenv('APP_CONFIG_DB_HOST') ?: '',
        'config_db_port' => getenv('APP_CONFIG_DB_PORT') ?: '',
        'config_db_name' => getenv('APP_CONFIG_DB_NAME') ?: '',
        'config_db_user' => getenv('APP_CONFIG_DB_USER') ?: '',
        'config_db_password' => getenv('APP_CONFIG_DB_PASSWORD') ?: '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'apply' => false,
                'db_host' => '',
                'db_port' => '',
                'db_name' => '',
                'db_user' => '',
                'db_password' => '',
                'config_db_host' => '',
                'config_db_port' => '',
                'config_db_name' => '',
                'config_db_user' => '',
                'config_db_password' => '',
                'error' => '',
            ];
        }

        if ($argument === '--apply') {
            $parsed['apply'] = true;
            continue;
        }

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return [
                'ok' => false,
                'help' => false,
                'apply' => false,
                'db_host' => '',
                'db_port' => '',
                'db_name' => '',
                'db_user' => '',
                'db_password' => '',
                'config_db_host' => '',
                'config_db_port' => '',
                'config_db_name' => '',
                'config_db_user' => '',
                'config_db_password' => '',
                'error' => 'unsupported argument: ' . $argument,
            ];
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        $normalizedValue = trim($value);

        switch ($name) {
            case 'db-host':
                $parsed['db_host'] = $normalizedValue;
                break;
            case 'db-port':
                $parsed['db_port'] = $normalizedValue;
                break;
            case 'db-name':
                $parsed['db_name'] = $normalizedValue;
                break;
            case 'db-user':
                $parsed['db_user'] = $normalizedValue;
                break;
            case 'db-password':
                $parsed['db_password'] = $value;
                break;
            case 'config-db-host':
                $parsed['config_db_host'] = $normalizedValue;
                break;
            case 'config-db-port':
                $parsed['config_db_port'] = $normalizedValue;
                break;
            case 'config-db-name':
                $parsed['config_db_name'] = $normalizedValue;
                break;
            case 'config-db-user':
                $parsed['config_db_user'] = $normalizedValue;
                break;
            case 'config-db-password':
                $parsed['config_db_password'] = $value;
                break;
            default:
                return [
                    'ok' => false,
                    'help' => false,
                    'apply' => false,
                    'db_host' => '',
                    'db_port' => '',
                    'db_name' => '',
                    'db_user' => '',
                    'db_password' => '',
                    'config_db_host' => '',
                    'config_db_port' => '',
                    'config_db_name' => '',
                    'config_db_user' => '',
                    'config_db_password' => '',
                    'error' => 'unsupported option: --' . $name,
                ];
        }
    }

    return [
        'ok' => true,
        'help' => false,
        'apply' => $parsed['apply'],
        'db_host' => $parsed['db_host'],
        'db_port' => $parsed['db_port'],
        'db_name' => $parsed['db_name'],
        'db_user' => $parsed['db_user'],
        'db_password' => $parsed['db_password'],
        'config_db_host' => $parsed['config_db_host'],
        'config_db_port' => $parsed['config_db_port'],
        'config_db_name' => $parsed['config_db_name'],
        'config_db_user' => $parsed['config_db_user'],
        'config_db_password' => $parsed['config_db_password'],
        'error' => '',
    ];
}

/**
 * @param array{
 *     db_host:string,
 *     db_port:string,
 *     db_name:string,
 *     db_user:string,
 *     db_password:string,
 *     config_db_host:string,
 *     config_db_port:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string
 * } $parsed
 * @return array<string,string>
 */
function app_cli_lang_res_drop_tables_app_env(array $parsed): array
{
    $env = [
        'APP_SITE' => 'admin',
        'APP_AUTH_MODE' => 'stub',
        'APP_AUTH_STUB_USER' => 'admin',
        'APP_AUTH_STUB_PASSWORD' => getenv('APP_AUTH_STUB_PASSWORD') ?: '',
        'APP_AUTH_STUB_NAME' => 'Language Resource DB Table Drop',
        'APP_AUTH_STUB_ROLES' => 'admin,config',
    ];

    foreach ([
        'APP_DB_HOST' => $parsed['db_host'],
        'APP_DB_PORT' => $parsed['db_port'],
        'APP_DB_NAME' => $parsed['db_name'],
        'APP_DB_USER' => $parsed['db_user'],
        'APP_DB_PASSWORD' => $parsed['db_password'],
    ] as $key => $value) {
        if ($value !== '') {
            $env[$key] = $value;
        }
    }

    $configFallbacks = [
        'APP_CONFIG_DB_HOST' => $parsed['config_db_host'] !== '' ? $parsed['config_db_host'] : $parsed['db_host'],
        'APP_CONFIG_DB_PORT' => $parsed['config_db_port'] !== '' ? $parsed['config_db_port'] : $parsed['db_port'],
        'APP_CONFIG_DB_NAME' => $parsed['config_db_name'] !== '' ? $parsed['config_db_name'] : $parsed['db_name'],
        'APP_CONFIG_DB_USER' => $parsed['config_db_user'] !== '' ? $parsed['config_db_user'] : $parsed['db_user'],
        'APP_CONFIG_DB_PASSWORD' => $parsed['config_db_password'] !== '' ? $parsed['config_db_password'] : $parsed['db_password'],
    ];
    foreach ($configFallbacks as $key => $value) {
        if ($value !== '') {
            $env[$key] = $value;
        }
    }

    return $env;
}

/**
 * @return list<string>
 */
function app_cli_lang_res_drop_tables_order(): array
{
    return [
        'project_language_resource_captions',
        'project_language_resource_additional_groups',
        'project_language_resource_group_source_outputs',
        'project_language_resource_group_languages',
        'project_language_resources',
        'project_language_resource_languages',
        'project_language_resource_groups',
    ];
}

/**
 * @return array<string,array{exists:bool,row_count:int}>
 */
function app_cli_lang_res_drop_table_statuses(PDO $pdo): array
{
    $schema = app_project_language_resource_pdo_current_schema($pdo);
    $existsStatement = $pdo->prepare(
        'SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = :table_schema
          AND TABLE_NAME = :table_name
        LIMIT 1'
    );

    $statuses = [];
    foreach (app_cli_lang_res_drop_tables_order() as $tableName) {
        $existsStatement->execute([
            ':table_schema' => $schema,
            ':table_name' => $tableName,
        ]);
        $exists = $existsStatement->fetchColumn() !== false;
        $rowCount = 0;
        if ($exists) {
            $statement = $pdo->query('SELECT COUNT(*) FROM `' . $tableName . '`');
            $rowCount = (int) ($statement->fetchColumn() ?? 0);
        }

        $statuses[$tableName] = [
            'exists' => $exists,
            'row_count' => $rowCount,
        ];
    }

    return $statuses;
}

/**
 * @param array<string,array{exists:bool,row_count:int}> $tableStatuses
 * @return array{
 *     existing_table_count:int,
 *     total_row_count:int,
 *     nonempty_tables:list<string>,
 *     all_tables_absent:bool
 * }
 */
function app_cli_lang_res_drop_tables_summary(array $tableStatuses): array
{
    $existingTableCount = 0;
    $totalRowCount = 0;
    $nonemptyTables = [];

    foreach ($tableStatuses as $tableName => $status) {
        if (($status['exists'] ?? false) !== true) {
            continue;
        }

        $existingTableCount += 1;
        $rowCount = (int) ($status['row_count'] ?? 0);
        $totalRowCount += $rowCount;
        if ($rowCount > 0) {
            $nonemptyTables[] = $tableName;
        }
    }

    return [
        'existing_table_count' => $existingTableCount,
        'total_row_count' => $totalRowCount,
        'nonempty_tables' => $nonemptyTables,
        'all_tables_absent' => $existingTableCount === 0,
    ];
}

$parsed = app_cli_lang_res_drop_tables_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_lang_res_drop_tables_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_lang_res_drop_tables_usage() . PHP_EOL);
    exit(64);
}

try {
    app_cli_lang_res_drop_tables_apply_env(
        app_cli_lang_res_drop_tables_app_env($parsed),
    );

    $app = app_bootstrap();
    $probe = app_probe_database($app);
    if (!$probe['ok']) {
        throw new RuntimeException($probe['detail']);
    }

    $pdo = app_create_pdo($app);
    $schema = app_project_language_resource_pdo_current_schema($pdo);
    $tableStatusesBefore = app_cli_lang_res_drop_table_statuses($pdo);
    $summaryBefore = app_cli_lang_res_drop_tables_summary($tableStatusesBefore);
    $safeToDropTables = $summaryBefore['total_row_count'] === 0;

    $droppedTables = [];
    $tableStatusesAfter = $tableStatusesBefore;
    $summaryAfter = $summaryBefore;

    if ($parsed['apply']) {
        if (!$safeToDropTables) {
            throw new RuntimeException(
                'LanguageResource tables are not empty. Clear rows before dropping schema.',
            );
        }

        foreach (app_cli_lang_res_drop_tables_order() as $tableName) {
            if (($tableStatusesBefore[$tableName]['exists'] ?? false) !== true) {
                continue;
            }

            $pdo->exec('DROP TABLE IF EXISTS `' . $tableName . '`');
            $droppedTables[] = $tableName;
        }

        $postDropPdo = app_create_pdo($app);
        $tableStatusesAfter = app_cli_lang_res_drop_table_statuses($postDropPdo);
        $summaryAfter = app_cli_lang_res_drop_tables_summary($tableStatusesAfter);
    }

    fwrite(
        STDOUT,
        json_encode(
            [
                'ok' => true,
                'database' => [
                    'label' => $probe['label'],
                    'detail' => $probe['detail'],
                    'schema' => $schema,
                ],
                'apply' => $parsed['apply'],
                'safe_to_drop_tables' => $safeToDropTables,
                'table_statuses_before' => $tableStatusesBefore,
                'summary_before' => $summaryBefore,
                'dropped_tables' => $droppedTables,
                'table_statuses_after' => $tableStatusesAfter,
                'summary_after' => $summaryAfter,
                'error' => '',
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit(0);
} catch (Throwable $throwable) {
    fwrite(
        STDERR,
        json_encode(
            [
                'ok' => false,
                'error' => $throwable->getMessage(),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit(1);
}
