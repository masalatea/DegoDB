#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/database_source_repository.php';

function app_cli_delete_database_source_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/delete_database_source.php --source-key=KEY [options]

Options:
  --source-key=KEY              source key to delete
  --config-db-host-port=PORT    host-side config DB port override
  --config-db-name=NAME         host-side config DB name override
  --config-db-user=USER         host-side config DB user override
  --config-db-password=PASS     host-side config DB password override
  --help                        show this help
TEXT;
}

function app_cli_delete_database_source_repo_root(): string
{
    return dirname(__DIR__, 2);
}

/**
 * @return array<string,string>
 */
function app_cli_delete_database_source_env_defaults(): array
{
    $envPath = app_cli_delete_database_source_repo_root() . '/.env';
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
 *     source_key:string,
 *     config_db_host_port:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string,
 *     error:string
 * }
 */
function app_cli_delete_database_source_parse_args(array $argv, array $defaults): array
{
    $parsed = [
        'source_key' => '',
        'config_db_host_port' => $defaults['CONFIG_DB_HOST_PORT'] ?? '33061',
        'config_db_name' => $defaults['CONFIG_DB_NAME'] ?? 'config_app',
        'config_db_user' => $defaults['CONFIG_DB_USER'] ?? 'config_app',
        'config_db_password' => $defaults['CONFIG_DB_PASSWORD'] ?? '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'source_key' => '',
                'config_db_host_port' => '',
                'config_db_name' => '',
                'config_db_user' => '',
                'config_db_password' => '',
                'error' => '',
            ];
        }

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return array_merge($parsed, [
                'ok' => false,
                'help' => false,
                'error' => '不明な引数です: ' . $argument,
            ]);
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        $name = trim($name);
        $value = (string) $value;

        switch ($name) {
            case 'source-key':
                $parsed['source_key'] = trim($value);
                break;
            case 'config-db-host-port':
                $parsed['config_db_host_port'] = trim($value);
                break;
            case 'config-db-name':
                $parsed['config_db_name'] = trim($value);
                break;
            case 'config-db-user':
                $parsed['config_db_user'] = trim($value);
                break;
            case 'config-db-password':
                $parsed['config_db_password'] = $value;
                break;
            default:
                return array_merge($parsed, [
                    'ok' => false,
                    'help' => false,
                    'error' => '不明な引数です: --' . $name,
                ]);
        }
    }

    if ($parsed['source_key'] === '') {
        return array_merge($parsed, [
            'ok' => false,
            'help' => false,
            'error' => 'source key は必須です。',
        ]);
    }

    foreach (['config_db_host_port', 'config_db_name', 'config_db_user'] as $field) {
        if (!is_string($parsed[$field]) || trim($parsed[$field]) === '') {
            return array_merge($parsed, [
                'ok' => false,
                'help' => false,
                'error' => $field . ' は必須です。',
            ]);
        }
    }

    return array_merge($parsed, [
        'ok' => true,
        'help' => false,
        'error' => '',
    ]);
}

function app_cli_delete_database_source_apply_host_runtime_env(array $parsed): void
{
    $overrides = [
        'APP_SITE' => 'admin',
        'APP_DB_HOST' => '127.0.0.1',
        'APP_DB_PORT' => $parsed['config_db_host_port'],
        'APP_DB_NAME' => $parsed['config_db_name'],
        'APP_DB_USER' => $parsed['config_db_user'],
        'APP_DB_PASSWORD' => $parsed['config_db_password'],
        'APP_CONFIG_DB_HOST' => '127.0.0.1',
        'APP_CONFIG_DB_PORT' => $parsed['config_db_host_port'],
        'APP_CONFIG_DB_NAME' => $parsed['config_db_name'],
        'APP_CONFIG_DB_USER' => $parsed['config_db_user'],
        'APP_CONFIG_DB_PASSWORD' => $parsed['config_db_password'],
    ];

    foreach ($overrides as $key => $value) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function app_cli_delete_database_source_write_json(array $payload, bool $ok): void
{
    $stream = $ok ? STDOUT : STDERR;
    fwrite(
        $stream,
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
}

$defaults = app_cli_delete_database_source_env_defaults();
$parsed = app_cli_delete_database_source_parse_args($argv, $defaults);

if (!$parsed['ok']) {
    fwrite(STDERR, app_cli_delete_database_source_usage() . PHP_EOL . PHP_EOL);
    fwrite(STDERR, 'Error: ' . $parsed['error'] . PHP_EOL);
    exit(1);
}

if ($parsed['help']) {
    fwrite(STDOUT, app_cli_delete_database_source_usage() . PHP_EOL);
    exit(0);
}

app_cli_delete_database_source_apply_host_runtime_env($parsed);

try {
    $app = app_bootstrap();
    $pdo = app_database_source_repository_create_config_pdo($app);
    if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
        throw new RuntimeException('database_sources canonical table が未初期化です。');
    }

    $statement = $pdo->prepare('DELETE FROM database_sources WHERE source_key = :source_key');
    $statement->execute([
        ':source_key' => $parsed['source_key'],
    ]);

    if ($statement->rowCount() === 0) {
        throw new RuntimeException('削除対象の database source が見つかりません。');
    }

    app_cli_delete_database_source_write_json([
        'ok' => true,
        'source_key' => $parsed['source_key'],
        'deleted' => true,
    ], true);
    exit(0);
} catch (Throwable $throwable) {
    app_cli_delete_database_source_write_json([
        'ok' => false,
        'source_key' => $parsed['source_key'],
        'deleted' => false,
        'error' => $throwable->getMessage(),
    ], false);
    exit(1);
}
