#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/project_repository.php';
require_once dirname(__DIR__) . '/app/project_language_resource_catalog_loader.php';
require_once dirname(__DIR__) . '/app/project_language_resource_route_common.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_lang_res_smoke_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_generated_html_db_language_resource_wrappers.php [options]

Options:
  --project-key=KEY             project key (default: MTOOL)
  --legacy-project-pid=PID      legacy Project.PID (default: 1)
  --artifact-key=KEY            target HTML-DB artifact key (default: latest HTML-DB artifact)
  --docroot=PATH                explicit HTML-DB docroot path (skip artifact lookup)
  --group-pid=PID               stable group pid for read/bridge checks (default: 5)
  --resource-key=KEY            stable resource key for read/bridge checks (default: ACTION_LOGIN_CAPTION)
  --host=HOST                   local server host (default: 127.0.0.1)
  --port=PORT                   local server port (default: 18080)
  --stub-user=USER              stub login username (default: .env ADMIN_AUTH_STUB_USER or admin)
  --stub-password=PASSWORD      stub login password (default: .env ADMIN_AUTH_STUB_PASSWORD)
  --db-host=HOST                APP_DB_HOST / APP_CONFIG_DB_HOST override
  --db-port=PORT                APP_DB_PORT / APP_CONFIG_DB_PORT override
  --db-name=NAME                APP_DB_NAME override
  --db-user=USER                APP_DB_USER override
  --db-password=PASSWORD        APP_DB_PASSWORD override
  --config-db-host=HOST         APP_CONFIG_DB_HOST override
  --config-db-port=PORT         APP_CONFIG_DB_PORT override
  --config-db-name=NAME         APP_CONFIG_DB_NAME override
  --config-db-user=USER         APP_CONFIG_DB_USER override
  --config-db-password=PASSWORD APP_CONFIG_DB_PASSWORD override
  --server-timeout=SECONDS      server readiness timeout (default: 20)
  --http-timeout=SECONDS        per-request timeout (default: 10)
  --publish                     publish target HTML-DB artifact before smoke and use published root
  --allow-mutate                run legacy write-entry bridge checks in addition to read-only checks (non-mutating)
  --help                        show this help
TEXT;
}

function app_cli_lang_res_smoke_repo_root(): string
{
    return dirname(__DIR__, 2);
}

function app_cli_lang_res_smoke_write_json(array $payload, bool $ok): void
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

/**
 * @return array<string,string>
 */
function app_cli_lang_res_smoke_env_defaults(): array
{
    $envPath = app_cli_lang_res_smoke_repo_root() . '/.env';
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
 * @return array<string,string>
 */
function app_cli_lang_res_smoke_current_environment(): array
{
    $env = getenv();
    if (is_array($env)) {
        $normalized = [];
        foreach ($env as $key => $value) {
            if (!is_string($key) || !is_scalar($value)) {
                continue;
            }

            $normalized[$key] = (string) $value;
        }

        return $normalized;
    }

    $normalized = [];
    foreach ($_ENV as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }

        $normalized[$key] = (string) $value;
    }

    return $normalized;
}

function app_cli_lang_res_smoke_apply_env(array $overrides): void
{
    foreach ($overrides as $key => $value) {
        if (!is_string($key)) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

/**
 * @param list<string> $argv
 * @param array<string,string> $defaults
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     legacy_project_pid:int,
 *     artifact_key:string,
 *     docroot:string,
 *     group_pid:int,
 *     resource_key:string,
 *     host:string,
 *     port:int,
 *     stub_user:string,
 *     stub_password:string,
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
 *     server_timeout_seconds:int,
 *     http_timeout_seconds:int,
 *     publish:bool,
 *     allow_mutate:bool,
 *     error:string
 * }
 */
function app_cli_lang_res_smoke_parse_args(array $argv, array $defaults): array
{
    $parsed = [
        'project_key' => 'MTOOL',
        'legacy_project_pid' => 1,
        'artifact_key' => '',
        'docroot' => '',
        'group_pid' => 5,
        'resource_key' => 'ACTION_LOGIN_CAPTION',
        'host' => '127.0.0.1',
        'port' => 18080,
        'stub_user' => getenv('APP_AUTH_STUB_USER') ?: ($defaults['ADMIN_AUTH_STUB_USER'] ?? 'admin'),
        'stub_password' => getenv('APP_AUTH_STUB_PASSWORD') ?: ($defaults['ADMIN_AUTH_STUB_PASSWORD'] ?? ''),
        'db_host' => getenv('APP_DB_HOST') ?: '127.0.0.1',
        'db_port' => getenv('APP_DB_PORT') ?: ($defaults['CONFIG_DB_HOST_PORT'] ?? '33061'),
        'db_name' => getenv('APP_DB_NAME') ?: ($defaults['CONFIG_DB_NAME'] ?? 'config_app'),
        'db_user' => getenv('APP_DB_USER') ?: ($defaults['CONFIG_DB_USER'] ?? 'config_app'),
        'db_password' => getenv('APP_DB_PASSWORD') ?: ($defaults['CONFIG_DB_PASSWORD'] ?? ''),
        'config_db_host' => getenv('APP_CONFIG_DB_HOST') ?: (getenv('APP_DB_HOST') ?: '127.0.0.1'),
        'config_db_port' => getenv('APP_CONFIG_DB_PORT') ?: (getenv('APP_DB_PORT') ?: ($defaults['CONFIG_DB_HOST_PORT'] ?? '33061')),
        'config_db_name' => getenv('APP_CONFIG_DB_NAME') ?: (getenv('APP_DB_NAME') ?: ($defaults['CONFIG_DB_NAME'] ?? 'config_app')),
        'config_db_user' => getenv('APP_CONFIG_DB_USER') ?: (getenv('APP_DB_USER') ?: ($defaults['CONFIG_DB_USER'] ?? 'config_app')),
        'config_db_password' => getenv('APP_CONFIG_DB_PASSWORD') ?: (getenv('APP_DB_PASSWORD') ?: ($defaults['CONFIG_DB_PASSWORD'] ?? '')),
        'server_timeout_seconds' => 20,
        'http_timeout_seconds' => 10,
        'publish' => false,
        'allow_mutate' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'legacy_project_pid' => 0,
                'artifact_key' => '',
                'group_pid' => 0,
                'resource_key' => '',
                'host' => '',
                'port' => 0,
                'stub_user' => '',
                'stub_password' => '',
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
                'server_timeout_seconds' => 0,
                'http_timeout_seconds' => 0,
                'allow_mutate' => false,
                'error' => '',
            ];
        }

        if ($argument === '--allow-mutate') {
            $parsed['allow_mutate'] = true;
            continue;
        }

        if ($argument === '--publish') {
            $parsed['publish'] = true;
            continue;
        }

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'legacy_project_pid' => 0,
                'artifact_key' => '',
                'group_pid' => 0,
                'resource_key' => '',
                'host' => '',
                'port' => 0,
                'stub_user' => '',
                'stub_password' => '',
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
                'server_timeout_seconds' => 0,
                'http_timeout_seconds' => 0,
                'allow_mutate' => false,
                'error' => '未対応の引数です: ' . $argument,
            ];
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        switch ($name) {
            case 'project-key':
                $parsed['project_key'] = strtoupper(trim($value));
                break;
            case 'legacy-project-pid':
                if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                    return [
                        'ok' => false,
                        'help' => false,
                        'project_key' => '',
                        'legacy_project_pid' => 0,
                        'artifact_key' => '',
                        'group_pid' => 0,
                        'resource_key' => '',
                        'host' => '',
                        'port' => 0,
                        'stub_user' => '',
                        'stub_password' => '',
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
                        'server_timeout_seconds' => 0,
                        'http_timeout_seconds' => 0,
                        'allow_mutate' => false,
                        'error' => 'legacy-project-pid は 1 以上の整数で指定してください。',
                    ];
                }

                $parsed['legacy_project_pid'] = (int) $value;
                break;
            case 'artifact-key':
                $parsed['artifact_key'] = trim($value);
                break;
            case 'docroot':
                $parsed['docroot'] = trim($value);
                break;
            case 'group-pid':
                if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                    return [
                        'ok' => false,
                        'help' => false,
                        'project_key' => '',
                        'legacy_project_pid' => 0,
                        'artifact_key' => '',
                        'group_pid' => 0,
                        'resource_key' => '',
                        'host' => '',
                        'port' => 0,
                        'stub_user' => '',
                        'stub_password' => '',
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
                        'server_timeout_seconds' => 0,
                        'http_timeout_seconds' => 0,
                        'allow_mutate' => false,
                        'error' => 'group-pid は 1 以上の整数で指定してください。',
                    ];
                }

                $parsed['group_pid'] = (int) $value;
                break;
            case 'resource-key':
                $parsed['resource_key'] = trim($value);
                break;
            case 'host':
                $parsed['host'] = trim($value);
                break;
            case 'port':
                if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                    return [
                        'ok' => false,
                        'help' => false,
                        'project_key' => '',
                        'legacy_project_pid' => 0,
                        'artifact_key' => '',
                        'group_pid' => 0,
                        'resource_key' => '',
                        'host' => '',
                        'port' => 0,
                        'stub_user' => '',
                        'stub_password' => '',
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
                        'server_timeout_seconds' => 0,
                        'http_timeout_seconds' => 0,
                        'allow_mutate' => false,
                        'error' => 'port は 1 以上の整数で指定してください。',
                    ];
                }

                $parsed['port'] = (int) $value;
                break;
            case 'stub-user':
                $parsed['stub_user'] = trim($value);
                break;
            case 'stub-password':
                $parsed['stub_password'] = $value;
                break;
            case 'db-host':
                $parsed['db_host'] = trim($value);
                break;
            case 'db-port':
                $parsed['db_port'] = trim($value);
                break;
            case 'db-name':
                $parsed['db_name'] = trim($value);
                break;
            case 'db-user':
                $parsed['db_user'] = trim($value);
                break;
            case 'db-password':
                $parsed['db_password'] = $value;
                break;
            case 'config-db-host':
                $parsed['config_db_host'] = trim($value);
                break;
            case 'config-db-port':
                $parsed['config_db_port'] = trim($value);
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
            case 'server-timeout':
                if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                    return [
                        'ok' => false,
                        'help' => false,
                        'project_key' => '',
                        'legacy_project_pid' => 0,
                        'artifact_key' => '',
                        'group_pid' => 0,
                        'resource_key' => '',
                        'host' => '',
                        'port' => 0,
                        'stub_user' => '',
                        'stub_password' => '',
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
                        'server_timeout_seconds' => 0,
                        'http_timeout_seconds' => 0,
                        'allow_mutate' => false,
                        'error' => 'server-timeout は 1 以上の整数で指定してください。',
                    ];
                }

                $parsed['server_timeout_seconds'] = (int) $value;
                break;
            case 'http-timeout':
                if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
                    return [
                        'ok' => false,
                        'help' => false,
                        'project_key' => '',
                        'legacy_project_pid' => 0,
                        'artifact_key' => '',
                        'group_pid' => 0,
                        'resource_key' => '',
                        'host' => '',
                        'port' => 0,
                        'stub_user' => '',
                        'stub_password' => '',
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
                        'server_timeout_seconds' => 0,
                        'http_timeout_seconds' => 0,
                        'allow_mutate' => false,
                        'error' => 'http-timeout は 1 以上の整数で指定してください。',
                    ];
                }

                $parsed['http_timeout_seconds'] = (int) $value;
                break;
            default:
                return [
                    'ok' => false,
                    'help' => false,
                    'project_key' => '',
                    'legacy_project_pid' => 0,
                    'artifact_key' => '',
                    'group_pid' => 0,
                    'resource_key' => '',
                    'host' => '',
                    'port' => 0,
                    'stub_user' => '',
                    'stub_password' => '',
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
                    'server_timeout_seconds' => 0,
                    'http_timeout_seconds' => 0,
                    'allow_mutate' => false,
                    'error' => '未対応の引数です: --' . $name,
                ];
        }
    }

    if ($parsed['project_key'] === '' || !preg_match('/\A[A-Z0-9][A-Z0-9_-]*\z/', $parsed['project_key'])) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'artifact_key' => '',
            'group_pid' => 0,
            'resource_key' => '',
            'host' => '',
            'port' => 0,
            'stub_user' => '',
            'stub_password' => '',
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
            'server_timeout_seconds' => 0,
            'http_timeout_seconds' => 0,
            'allow_mutate' => false,
            'error' => 'project-key の形式が不正です。',
        ];
    }

    foreach ([
        'host',
        'stub_user',
        'db_host',
        'db_port',
        'db_name',
        'db_user',
        'config_db_host',
        'config_db_port',
        'config_db_name',
        'config_db_user',
    ] as $requiredKey) {
        if (trim((string) $parsed[$requiredKey]) === '') {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'legacy_project_pid' => 0,
                'artifact_key' => '',
                'group_pid' => 0,
                'resource_key' => '',
                'host' => '',
                'port' => 0,
                'stub_user' => '',
                'stub_password' => '',
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
                'server_timeout_seconds' => 0,
                'http_timeout_seconds' => 0,
                'allow_mutate' => false,
                'error' => $requiredKey . ' を指定してください。',
            ];
        }
    }

    if ($parsed['resource_key'] === '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'artifact_key' => '',
            'group_pid' => 0,
            'resource_key' => '',
            'host' => '',
            'port' => 0,
            'stub_user' => '',
            'stub_password' => '',
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
            'server_timeout_seconds' => 0,
            'http_timeout_seconds' => 0,
            'allow_mutate' => false,
            'error' => 'resource-key を指定してください。',
        ];
    }

    if ($parsed['docroot'] !== '' && $parsed['artifact_key'] !== '') {
        return [
            'ok' => false,
            'help' => false,
            'error' => 'docroot 指定時は artifact-key を併用できません。',
        ];
    }

    if ($parsed['docroot'] !== '' && $parsed['publish']) {
        return [
            'ok' => false,
            'help' => false,
            'error' => 'docroot 指定時は publish を併用できません。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $parsed['project_key'],
        'legacy_project_pid' => $parsed['legacy_project_pid'],
        'artifact_key' => $parsed['artifact_key'],
        'docroot' => $parsed['docroot'],
        'group_pid' => $parsed['group_pid'],
        'resource_key' => $parsed['resource_key'],
        'host' => $parsed['host'],
        'port' => $parsed['port'],
        'stub_user' => $parsed['stub_user'],
        'stub_password' => $parsed['stub_password'],
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
        'server_timeout_seconds' => $parsed['server_timeout_seconds'],
        'http_timeout_seconds' => $parsed['http_timeout_seconds'],
        'publish' => $parsed['publish'],
        'allow_mutate' => $parsed['allow_mutate'],
        'error' => '',
    ];
}

function app_cli_lang_res_smoke_resolve_docroot_path(string $docroot): string
{
    if (trim($docroot) === '') {
        throw new RuntimeException('docroot を指定してください。');
    }

    if (!is_dir($docroot)) {
        throw new RuntimeException('docroot が見つかりません: ' . $docroot);
    }

    $resolved = realpath($docroot);
    if ($resolved === false || !is_dir($resolved)) {
        throw new RuntimeException('docroot を解決できません: ' . $docroot);
    }

    return rtrim(str_replace('\\', '/', $resolved), '/');
}

/**
 * @param array{
 *     project_key:string
 * } $parsed
 * @return array{
 *     ok:bool,
 *     artifact_key:string,
 *     artifact_dir:string,
 *     manifest_path:string,
 *     docroot:string,
 *     bundle_entry_root:string,
 *     runtime_source_relative_path:string,
 *     manifest:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_cli_lang_res_smoke_load_artifact(array $parsed): array
{
    $artifactRoot = app_cli_lang_res_smoke_repo_root()
        . '/work/artifacts/source-outputs/'
        . $parsed['project_key'];
    if (!is_dir($artifactRoot)) {
        return [
            'ok' => false,
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'bundle_entry_root' => '',
            'runtime_source_relative_path' => '',
            'manifest' => null,
            'error' => 'artifact root が見つかりません: ' . $artifactRoot,
        ];
    }

    $artifactCandidates = [];
    if ($parsed['artifact_key'] !== '') {
        $artifactCandidates[] = $parsed['artifact_key'];
    } else {
        foreach (glob($artifactRoot . '/*', GLOB_ONLYDIR) ?: [] as $artifactDir) {
            $artifactCandidates[] = basename($artifactDir);
        }
        rsort($artifactCandidates, SORT_NATURAL);
    }

    foreach ($artifactCandidates as $artifactKey) {
        $artifactDir = $artifactRoot . '/' . $artifactKey;
        $manifestPath = $artifactDir . '/manifest.json';
        if (!is_file($manifestPath)) {
            continue;
        }

        $manifestJson = file_get_contents($manifestPath);
        if (!is_string($manifestJson) || trim($manifestJson) === '') {
            continue;
        }

        $manifest = json_decode($manifestJson, true);
        if (!is_array($manifest)) {
            continue;
        }

        if (($manifest['source_output_key'] ?? null) !== 'HTML-DB') {
            continue;
        }

        $bundleEntryRoot = trim((string) ($manifest['bundle_entry_root'] ?? ''));
        $runtimeSourceRelativePath = trim((string) ($manifest['runtime_source_relative_path'] ?? ''));
        if ($bundleEntryRoot === '' || $runtimeSourceRelativePath === '') {
            continue;
        }

        $docroot = $artifactDir . '/bundle/' . $bundleEntryRoot . '/' . $runtimeSourceRelativePath;
        if (!is_dir($docroot)) {
            continue;
        }

        return [
            'ok' => true,
            'artifact_key' => $artifactKey,
            'artifact_dir' => $artifactDir,
            'manifest_path' => $manifestPath,
            'docroot' => $docroot,
            'bundle_entry_root' => $bundleEntryRoot,
            'runtime_source_relative_path' => $runtimeSourceRelativePath,
            'manifest' => $manifest,
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'artifact_key' => '',
        'artifact_dir' => '',
        'manifest_path' => '',
        'docroot' => '',
        'bundle_entry_root' => '',
        'runtime_source_relative_path' => '',
        'manifest' => null,
        'error' => $parsed['artifact_key'] !== ''
            ? '指定した HTML-DB artifact が見つかりません。'
            : 'HTML-DB artifact が見つかりません。',
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     artifact_key:string,
 *     docroot:string,
 *     publish:bool
 * } $parsed
 * @return array{
 *     ok:bool,
 *     docroot_mode:string,
 *     artifact_key:string,
 *     artifact_dir:string,
 *     manifest_path:string,
 *     docroot:string,
 *     published:array{
 *         project_key:string,
 *         source_output_key:string,
 *         artifact_key:string,
 *         source_output_dir:string,
 *         published_root:string,
 *         published_file_count:int,
 *         published_total_bytes:int,
 *         published_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_cli_lang_res_smoke_resolve_runtime_target(array $app, array $parsed): array
{
    if ($parsed['docroot'] !== '') {
        try {
            $docroot = app_cli_lang_res_smoke_resolve_docroot_path($parsed['docroot']);
        } catch (Throwable $throwable) {
            return [
                'ok' => false,
                'docroot_mode' => '',
                'artifact_key' => '',
                'artifact_dir' => '',
                'manifest_path' => '',
                'docroot' => '',
                'published' => null,
                'error' => $throwable->getMessage(),
            ];
        }

        return [
            'ok' => true,
            'docroot_mode' => 'direct',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => $docroot,
            'published' => null,
            'error' => '',
        ];
    }

    $artifactInfo = app_cli_lang_res_smoke_load_artifact($parsed);
    if (!$artifactInfo['ok']) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => $artifactInfo['error'],
        ];
    }

    if (!$parsed['publish']) {
        return [
            'ok' => true,
            'docroot_mode' => 'artifact',
            'artifact_key' => $artifactInfo['artifact_key'],
            'artifact_dir' => $artifactInfo['artifact_dir'],
            'manifest_path' => $artifactInfo['manifest_path'],
            'docroot' => $artifactInfo['docroot'],
            'published' => null,
            'error' => '',
        ];
    }

    $sourceOutputResult = app_fetch_project_source_output_item($app, $parsed['project_key'], 'HTML-DB');
    if (!$sourceOutputResult['ok']) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => $sourceOutputResult['error'],
        ];
    }

    $sourceOutput = $sourceOutputResult['item'] ?? null;
    if (!is_array($sourceOutput)) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => 'HTML-DB source output definition が見つかりません。',
        ];
    }

    $artifactResult = app_project_output_find($app, $parsed['project_key'], $artifactInfo['artifact_key']);
    if (!$artifactResult['ok']) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => $artifactResult['error'],
        ];
    }

    $artifact = $artifactResult['item'] ?? null;
    if (!is_array($artifact)) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => 'publish 対象の artifact が見つかりません。',
        ];
    }

    $publishResult = app_project_output_publish_artifact($app, $artifact, $sourceOutput);
    if (!$publishResult['ok']) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => $publishResult['error'],
        ];
    }

    $published = $publishResult['published'] ?? null;
    if (!is_array($published)) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => 'publish 結果が不正です。',
        ];
    }

    try {
        $docroot = app_cli_lang_res_smoke_resolve_docroot_path((string) ($published['published_root'] ?? ''));
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'published' => null,
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => true,
        'docroot_mode' => 'published',
        'artifact_key' => $artifactInfo['artifact_key'],
        'artifact_dir' => $artifactInfo['artifact_dir'],
        'manifest_path' => $artifactInfo['manifest_path'],
        'docroot' => $docroot,
        'published' => $published,
        'error' => '',
    ];
}

/**
 * @param array{
 *     stub_user:string,
 *     stub_password:string,
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
function app_cli_lang_res_smoke_app_env(array $parsed): array
{
    return [
        'APP_SITE' => 'admin',
        'APP_AUTH_MODE' => 'stub',
        'APP_AUTH_STUB_USER' => $parsed['stub_user'],
        'APP_AUTH_STUB_PASSWORD' => $parsed['stub_password'],
        'APP_AUTH_STUB_NAME' => 'Language Resource Smoke',
        'APP_AUTH_STUB_ROLES' => 'admin,config',
        'APP_DB_HOST' => $parsed['db_host'],
        'APP_DB_PORT' => $parsed['db_port'],
        'APP_DB_NAME' => $parsed['db_name'],
        'APP_DB_USER' => $parsed['db_user'],
        'APP_DB_PASSWORD' => $parsed['db_password'],
        'APP_CONFIG_DB_HOST' => $parsed['config_db_host'],
        'APP_CONFIG_DB_PORT' => $parsed['config_db_port'],
        'APP_CONFIG_DB_NAME' => $parsed['config_db_name'],
        'APP_CONFIG_DB_USER' => $parsed['config_db_user'],
        'APP_CONFIG_DB_PASSWORD' => $parsed['config_db_password'],
        'APP_APP_ROOT' => '',
    ];
}

/**
 * @param array{
 *     port:int
 * } $parsed
 * @param array<string,string> $baseEnv
 * @return array<string,string>
 */
function app_cli_lang_res_smoke_server_env(array $parsed, array $baseEnv, string $docroot): array
{
    $environment = app_cli_lang_res_smoke_current_environment();
    foreach ($baseEnv as $key => $value) {
        $environment[$key] = $value;
    }
    $environment['APP_SESSION_NAME'] = 'MTOOL_HTML_DB_SMOKE_' . (string) $parsed['port'];
    $environment['MTOOL_HTML_DB_DOCROOT'] = $docroot;

    return $environment;
}

function app_cli_lang_res_smoke_ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * @param array{
 *     project_key:string,
 *     legacy_project_pid:int,
 *     group_pid:int,
 *     resource_key:string
 * } $parsed
 * @return array{
 *     project:array<string,mixed>,
 *     catalog:array<string,mixed>,
 *     group:array<string,mixed>,
 *     resource:array<string,mixed>,
 *     group_language_pids:list<int>,
 *     group_source_output_pids:list<int>,
 *     default_language_pid:int,
 *     english_language_pid:int,
 *     japanese_language_pid:int
 * }
 */
function app_cli_lang_res_smoke_load_context(array $app, array $parsed): array
{
    $projectResult = app_fetch_project_by_key($app, $parsed['project_key']);
    app_cli_lang_res_smoke_ensure($projectResult['ok'], $projectResult['error'] !== '' ? $projectResult['error'] : 'project の取得に失敗しました。');
    app_cli_lang_res_smoke_ensure(is_array($projectResult['item'] ?? null), 'project が見つかりません。');

    $catalogResult = app_fetch_project_language_resource_catalog(
        $app,
        $parsed['project_key'],
        $parsed['legacy_project_pid'],
    );
    app_cli_lang_res_smoke_ensure(
        $catalogResult['ok'],
        $catalogResult['error'] !== '' ? $catalogResult['error'] : 'language resource catalog の取得に失敗しました。',
    );
    app_cli_lang_res_smoke_ensure(is_array($catalogResult['item'] ?? null), 'language resource catalog が空です。');

    $catalog = $catalogResult['item'];
    $groupsByPid = app_project_language_resource_groups_by_pid($catalog['groups']);
    $group = $groupsByPid[(string) $parsed['group_pid']] ?? null;
    app_cli_lang_res_smoke_ensure(is_array($group), '指定した group_pid が見つかりません: ' . $parsed['group_pid']);

    $resource = app_project_language_resource_resource_by_key($catalog['resources'], $parsed['resource_key']);
    app_cli_lang_res_smoke_ensure(is_array($resource), '指定した resource_key が見つかりません: ' . $parsed['resource_key']);

    $groupLanguagesByGroupPid = app_project_language_resource_group_languages_by_group_pid($catalog['group_languages']);
    $groupSourceOutputsByGroupPid = app_project_language_resource_group_source_outputs_by_group_pid(
        $catalog['group_source_outputs'],
    );

    $groupLanguagePids = [];
    foreach ($groupLanguagesByGroupPid[(string) $parsed['group_pid']] ?? [] as $groupLanguage) {
        $legacyLanguagePid = (int) ($groupLanguage['legacy_language_pid'] ?? 0);
        if ($legacyLanguagePid <= 0) {
            continue;
        }

        $groupLanguagePids[] = $legacyLanguagePid;
    }
    $groupLanguagePids = array_values(array_unique($groupLanguagePids));

    $groupSourceOutputPids = [];
    foreach ($groupSourceOutputsByGroupPid[(string) $parsed['group_pid']] ?? [] as $groupSourceOutput) {
        $legacySourceOutputPid = (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0);
        if ($legacySourceOutputPid <= 0) {
            continue;
        }

        $groupSourceOutputPids[] = $legacySourceOutputPid;
    }
    $groupSourceOutputPids = array_values(array_unique($groupSourceOutputPids));

    return [
        'project' => $projectResult['item'],
        'catalog' => $catalog,
        'group' => $group,
        'resource' => $resource,
        'group_language_pids' => $groupLanguagePids,
        'group_source_output_pids' => $groupSourceOutputPids,
        'default_language_pid' => app_project_language_resource_default_language_pid($catalog['languages']),
        'english_language_pid' => app_project_language_resource_find_language_pid_by_suffix($catalog['languages'], 'en'),
        'japanese_language_pid' => app_project_language_resource_find_language_pid_by_suffix($catalog['languages'], 'ja'),
    ];
}

/**
 * @return array{
 *     base_url:string,
 *     timeout_seconds:int,
 *     cookies:array<string,string>
 * }
 */
function app_cli_lang_res_smoke_http_client(string $host, int $port, int $timeoutSeconds): array
{
    return [
        'base_url' => 'http://' . $host . ':' . $port,
        'timeout_seconds' => $timeoutSeconds,
        'cookies' => [],
    ];
}

/**
 * @param list<string> $headerLines
 * @return array<string,list<string>>
 */
function app_cli_lang_res_smoke_header_map(array $headerLines): array
{
    $headers = [];
    foreach ($headerLines as $line) {
        if (!is_string($line) || !str_contains($line, ':')) {
            continue;
        }

        [$name, $value] = explode(':', $line, 2);
        $normalizedName = strtolower(trim($name));
        if ($normalizedName === '') {
            continue;
        }

        if (!array_key_exists($normalizedName, $headers)) {
            $headers[$normalizedName] = [];
        }

        $headers[$normalizedName][] = trim($value);
    }

    return $headers;
}

/**
 * @param list<string> $headerLines
 */
function app_cli_lang_res_smoke_http_status(array $headerLines): int
{
    foreach ($headerLines as $line) {
        if (!is_string($line)) {
            continue;
        }

        if (preg_match('#^HTTP/\S+\s+(\d{3})\b#', $line, $matches) === 1) {
            return (int) ($matches[1] ?? 0);
        }
    }

    return 0;
}

/**
 * @param list<string> $headerLines
 * @param array<string,string> $cookies
 */
function app_cli_lang_res_smoke_store_cookies(array &$cookies, array $headerLines): void
{
    foreach ($headerLines as $line) {
        if (!is_string($line) || stripos($line, 'Set-Cookie:') !== 0) {
            continue;
        }

        $cookieValue = trim(substr($line, strlen('Set-Cookie:')));
        if ($cookieValue === '') {
            continue;
        }

        $cookiePair = explode(';', $cookieValue, 2)[0] ?? '';
        if ($cookiePair === '' || !str_contains($cookiePair, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $cookiePair, 2);
        $name = trim($name);
        if ($name === '') {
            continue;
        }

        $cookies[$name] = trim($value);
    }
}

/**
 * @param array{
 *     base_url:string,
 *     timeout_seconds:int,
 *     cookies:array<string,string>
 * } $client
 */
function app_cli_lang_res_smoke_absolute_url(array $client, string $path): string
{
    if (preg_match('#\Ahttps?://#i', $path) === 1) {
        return $path;
    }

    return rtrim($client['base_url'], '/') . '/' . ltrim($path, '/');
}

/**
 * @param array{
 *     base_url:string,
 *     timeout_seconds:int,
 *     cookies:array<string,string>
 * } $client
 * @param array{
 *     headers?:array<string,string>,
 *     form_params?:array<string,mixed>,
 *     body?:string
 * } $options
 * @return array{
 *     ok:bool,
 *     status:int,
 *     url:string,
 *     path:string,
 *     headers:array<string,list<string>>,
 *     header_lines:list<string>,
 *     body:string,
 *     location:string,
 *     error:string
 * }
 */
function app_cli_lang_res_smoke_http_request_once(array &$client, string $method, string $path, array $options = []): array
{
    $url = app_cli_lang_res_smoke_absolute_url($client, $path);
    $headerLines = [];
    foreach (($options['headers'] ?? []) as $name => $value) {
        if (!is_string($name) || !is_string($value) || trim($name) === '') {
            continue;
        }

        $headerLines[] = trim($name) . ': ' . $value;
    }

    if ($client['cookies'] !== []) {
        $cookiePairs = [];
        foreach ($client['cookies'] as $cookieName => $cookieValue) {
            $cookiePairs[] = $cookieName . '=' . $cookieValue;
        }
        $headerLines[] = 'Cookie: ' . implode('; ', $cookiePairs);
    }

    $body = $options['body'] ?? null;
    if ($body === null && array_key_exists('form_params', $options)) {
        $body = http_build_query(
            $options['form_params'],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );

        $hasContentType = false;
        foreach ($headerLines as $headerLine) {
            if (stripos($headerLine, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }
        if (!$hasContentType) {
            $headerLines[] = 'Content-Type: application/x-www-form-urlencoded';
        }
    }

    if (function_exists('curl_init')) {
        $curl = curl_init();
        if ($curl === false) {
            return [
                'ok' => false,
                'status' => 0,
                'url' => $url,
                'path' => $path,
                'headers' => [],
                'header_lines' => [],
                'body' => '',
                'location' => '',
                'error' => 'curl 初期化に失敗しました。',
            ];
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => $client['timeout_seconds'],
            CURLOPT_HTTPHEADER => $headerLines,
        ]);
        if ($body !== null && strtoupper($method) !== 'GET' && strtoupper($method) !== 'HEAD') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $rawResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!is_string($rawResponse)) {
            $rawResponse = '';
        }

        $headerText = substr($rawResponse, 0, $headerSize);
        $responseBody = substr($rawResponse, $headerSize);
        if (!is_string($headerText)) {
            $headerText = '';
        }
        if (!is_string($responseBody)) {
            $responseBody = '';
        }

        $headerBlocks = preg_split("/\r\n\r\n|\n\n|\r\r/", trim($headerText));
        if (!is_array($headerBlocks) || $headerBlocks === []) {
            $headerBlocks = [$headerText];
        }

        $allHeaderLines = [];
        foreach ($headerBlocks as $headerBlock) {
            foreach (preg_split("/\r\n|\n|\r/", (string) $headerBlock) ?: [] as $headerLine) {
                if (is_string($headerLine) && trim($headerLine) !== '') {
                    $allHeaderLines[] = $headerLine;
                }
            }
        }

        $finalHeaderLines = [];
        $finalHeaderBlock = (string) end($headerBlocks);
        foreach (preg_split("/\r\n|\n|\r/", $finalHeaderBlock) ?: [] as $headerLine) {
            if (is_string($headerLine) && trim($headerLine) !== '') {
                $finalHeaderLines[] = $headerLine;
            }
        }

        app_cli_lang_res_smoke_store_cookies($client['cookies'], $allHeaderLines);
        $headers = app_cli_lang_res_smoke_header_map($finalHeaderLines);
        $location = (string) (($headers['location'][0] ?? ''));

        if ($curlError !== '') {
            return [
                'ok' => false,
                'status' => $status,
                'url' => $url,
                'path' => $path,
                'headers' => $headers,
                'header_lines' => $finalHeaderLines,
                'body' => $responseBody,
                'location' => $location,
                'error' => $curlError,
            ];
        }

        return [
            'ok' => true,
            'status' => $status,
            'url' => $url,
            'path' => $path,
            'headers' => $headers,
            'header_lines' => $finalHeaderLines,
            'body' => $responseBody,
            'location' => $location,
            'error' => '',
        ];
    }

    $streamError = '';
    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headerLines),
            'content' => $body ?? '',
            'timeout' => $client['timeout_seconds'],
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
        ],
    ]);

    $previousErrorHandler = set_error_handler(
        static function (int $severity, string $message) use (&$streamError): bool {
            $streamError = $message;
            return true;
        },
    );

    try {
        $responseBody = file_get_contents($url, false, $context);
    } finally {
        restore_error_handler();
    }

    if (!is_string($responseBody)) {
        $responseBody = '';
    }

    $responseHeaderLines = [];
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $responseHeaderLine) {
            if (is_string($responseHeaderLine) && trim($responseHeaderLine) !== '') {
                $responseHeaderLines[] = $responseHeaderLine;
            }
        }
    }

    app_cli_lang_res_smoke_store_cookies($client['cookies'], $responseHeaderLines);
    $headers = app_cli_lang_res_smoke_header_map($responseHeaderLines);
    $location = (string) (($headers['location'][0] ?? ''));
    $status = app_cli_lang_res_smoke_http_status($responseHeaderLines);

    if ($streamError !== '' && $status === 0) {
        return [
            'ok' => false,
            'status' => 0,
            'url' => $url,
            'path' => $path,
            'headers' => $headers,
            'header_lines' => $responseHeaderLines,
            'body' => $responseBody,
            'location' => $location,
            'error' => $streamError,
        ];
    }

    return [
        'ok' => true,
        'status' => $status,
        'url' => $url,
        'path' => $path,
        'headers' => $headers,
        'header_lines' => $responseHeaderLines,
        'body' => $responseBody,
        'location' => $location,
        'error' => '',
    ];
}

/**
 * @param array{
 *     headers?:array<string,string>,
 *     form_params?:array<string,mixed>,
 *     body?:string,
 *     follow_redirects?:bool,
 *     max_redirects?:int
 * } $options
 * @return array{
 *     ok:bool,
 *     status:int,
 *     url:string,
 *     path:string,
 *     final_path:string,
 *     headers:array<string,list<string>>,
 *     header_lines:list<string>,
 *     body:string,
 *     location:string,
 *     redirects:list<array{
 *         from_path:string,
 *         status:int,
 *         location:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_lang_res_smoke_http_request(array &$client, string $method, string $path, array $options = []): array
{
    $followRedirects = (bool) ($options['follow_redirects'] ?? false);
    $maxRedirects = max(0, (int) ($options['max_redirects'] ?? 10));

    $currentMethod = strtoupper($method);
    $currentPath = $path;
    $currentOptions = $options;
    unset($currentOptions['follow_redirects'], $currentOptions['max_redirects']);

    $redirects = [];
    while (true) {
        $response = app_cli_lang_res_smoke_http_request_once($client, $currentMethod, $currentPath, $currentOptions);
        if (!$response['ok']) {
            return [
                'ok' => false,
                'status' => $response['status'],
                'url' => $response['url'],
                'path' => $currentPath,
                'final_path' => $currentPath,
                'headers' => $response['headers'],
                'header_lines' => $response['header_lines'],
                'body' => $response['body'],
                'location' => $response['location'],
                'redirects' => $redirects,
                'error' => $response['error'],
            ];
        }

        if (
            !$followRedirects
            || !in_array($response['status'], [301, 302, 303, 307, 308], true)
            || trim($response['location']) === ''
        ) {
            return [
                'ok' => true,
                'status' => $response['status'],
                'url' => $response['url'],
                'path' => $currentPath,
                'final_path' => $currentPath,
                'headers' => $response['headers'],
                'header_lines' => $response['header_lines'],
                'body' => $response['body'],
                'location' => $response['location'],
                'redirects' => $redirects,
                'error' => '',
            ];
        }

        if (count($redirects) >= $maxRedirects) {
            return [
                'ok' => false,
                'status' => $response['status'],
                'url' => $response['url'],
                'path' => $currentPath,
                'final_path' => $currentPath,
                'headers' => $response['headers'],
                'header_lines' => $response['header_lines'],
                'body' => $response['body'],
                'location' => $response['location'],
                'redirects' => $redirects,
                'error' => 'redirect が多すぎます。',
            ];
        }

        $redirects[] = [
            'from_path' => $currentPath,
            'status' => $response['status'],
            'location' => $response['location'],
        ];

        $location = $response['location'];
        if (preg_match('#\Ahttps?://#i', $location) === 1) {
            $redirectPath = (string) parse_url($location, PHP_URL_PATH);
            $redirectQuery = (string) parse_url($location, PHP_URL_QUERY);
            $currentPath = $redirectPath . ($redirectQuery !== '' ? '?' . $redirectQuery : '');
        } else {
            $currentPath = $location;
        }

        if (!str_starts_with($currentPath, '/')) {
            $currentPath = '/' . ltrim($currentPath, '/');
        }

        if (in_array($response['status'], [301, 302, 303], true) && !in_array($currentMethod, ['GET', 'HEAD'], true)) {
            $currentMethod = 'GET';
            $currentOptions = [];
        } elseif (in_array($response['status'], [307, 308], true)) {
            $currentMethod = strtoupper($method);
        } else {
            $currentMethod = 'GET';
            $currentOptions = [];
        }
    }
}

function app_cli_lang_res_smoke_extract_input_value(string $html, string $name): string
{
    $patterns = [
        '/<input\b[^>]*\bname="' . preg_quote($name, '/') . '"[^>]*\bvalue="([^"]*)"[^>]*>/iu',
        '/<input\b[^>]*\bvalue="([^"]*)"[^>]*\bname="' . preg_quote($name, '/') . '"[^>]*>/iu',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches) === 1) {
            return html_entity_decode((string) ($matches[1] ?? ''), ENT_QUOTES, 'UTF-8');
        }
    }

    return '';
}

function app_cli_lang_res_smoke_query_value(string $path, string $name): string
{
    $query = (string) parse_url('http://local' . $path, PHP_URL_QUERY);
    if ($query === '') {
        return '';
    }

    parse_str($query, $queryParams);
    $value = $queryParams[$name] ?? null;
    if (is_array($value)) {
        return '';
    }

    return is_string($value) || is_numeric($value) ? trim((string) $value) : '';
}

/**
 * @return list<string>
 */
function app_cli_lang_res_smoke_query_values(string $path, string $name): array
{
    $query = (string) parse_url('http://local' . $path, PHP_URL_QUERY);
    if ($query === '') {
        return [];
    }

    parse_str($query, $queryParams);
    $rawValue = $queryParams[$name] ?? null;
    if ($rawValue === null) {
        return [];
    }

    $rawItems = is_array($rawValue) ? $rawValue : [$rawValue];
    $items = [];
    foreach ($rawItems as $rawItem) {
        if (!is_string($rawItem) && !is_numeric($rawItem)) {
            continue;
        }

        $normalized = trim((string) $rawItem);
        if ($normalized === '') {
            continue;
        }

        $items[$normalized] = $normalized;
    }

    return array_values($items);
}

function app_cli_lang_res_smoke_path_only(string $path): string
{
    $pathOnly = (string) parse_url('http://local' . $path, PHP_URL_PATH);
    if ($pathOnly === '') {
        return '/';
    }

    return $pathOnly;
}

/**
 * @param array{
 *     project_key:string
 * } $parsed
 */
function app_cli_lang_res_smoke_resource_key_from_path(array $parsed, string $path): string
{
    $pathOnly = (string) parse_url('http://local' . $path, PHP_URL_PATH);
    $pattern = '#^/projects/' . preg_quote(rawurlencode($parsed['project_key']), '#') . '/language-resources/([^/]+)$#';
    if (preg_match($pattern, $pathOnly, $matches) !== 1) {
        return '';
    }

    return rawurldecode((string) ($matches[1] ?? ''));
}

/**
 * @param array<string,mixed> $payload
 */
function app_cli_lang_res_smoke_record_check(array &$checks, string $name, callable $callback): array
{
    try {
        $result = $callback();
        if (!is_array($result)) {
            $result = [];
        }

        $check = array_merge([
            'name' => $name,
            'ok' => true,
        ], $result);
        $checks[] = $check;

        return $check;
    } catch (Throwable $throwable) {
        $check = [
            'name' => $name,
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
        $checks[] = $check;
        throw $throwable;
    }
}

/**
 * @param array{
 *     project_key:string,
 *     stub_user:string,
 *     stub_password:string
 * } $parsed
 */
function app_cli_lang_res_smoke_login(array &$client, array $parsed): array
{
    $redirectPath = '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources/groups';
    $loginPage = app_cli_lang_res_smoke_http_request(
        $client,
        'GET',
        '/login?redirect=' . rawurlencode($redirectPath),
        [
            'follow_redirects' => false,
        ],
    );
    app_cli_lang_res_smoke_ensure($loginPage['ok'], 'login page の取得に失敗しました: ' . $loginPage['error']);
    app_cli_lang_res_smoke_ensure($loginPage['status'] === 200, 'login page の HTTP status が 200 ではありません: ' . $loginPage['status']);

    $csrfToken = app_cli_lang_res_smoke_extract_input_value($loginPage['body'], '_csrf');
    app_cli_lang_res_smoke_ensure($csrfToken !== '', 'login form の CSRF token を取得できませんでした。');

    $loginSubmit = app_cli_lang_res_smoke_http_request(
        $client,
        'POST',
        '/login',
        [
            'follow_redirects' => true,
            'form_params' => [
                '_csrf' => $csrfToken,
                'redirect' => $redirectPath,
                'username' => $parsed['stub_user'],
                'password' => $parsed['stub_password'],
            ],
        ],
    );
    app_cli_lang_res_smoke_ensure($loginSubmit['ok'], 'login submit に失敗しました: ' . $loginSubmit['error']);
    app_cli_lang_res_smoke_ensure($loginSubmit['status'] === 200, 'login submit 後の HTTP status が 200 ではありません: ' . $loginSubmit['status']);
    app_cli_lang_res_smoke_ensure(
        $loginSubmit['final_path'] === $redirectPath,
        'login 後の landing path が想定外です: ' . $loginSubmit['final_path'],
    );
    app_cli_lang_res_smoke_ensure(
        str_contains($loginSubmit['body'], 'Language Resource Groups'),
        'login 後の landing page が groups page ではありません。',
    );

    return [
        'final_path' => $loginSubmit['final_path'],
        'redirect_count' => count($loginSubmit['redirects']),
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     group_pid:int,
 *     resource_key:string
 * } $parsed
 * @param array{
 *     group:array<string,mixed>,
 *     resource:array<string,mixed>
 * } $context
 */
function app_cli_lang_res_smoke_run_non_mutating_checks(array &$client, array $parsed, array $context, array &$checks): void
{
    $groupName = (string) ($context['group']['name'] ?? '');
    $resourceLegacyPid = (int) ($context['resource']['legacy_resource_pid'] ?? 0);

    app_cli_lang_res_smoke_record_check($checks, 'login', static function () use (&$client, $parsed): array {
        return app_cli_lang_res_smoke_login($client, $parsed);
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res.php', static function () use (&$client, $parsed): array {
        $response = app_cli_lang_res_smoke_http_request($client, 'GET', '/lang_res.php', [
            'follow_redirects' => true,
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res.php の取得に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res.php の HTTP status が 200 ではありません: ' . $response['status']);
        app_cli_lang_res_smoke_ensure(
            $response['final_path'] === '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources/groups',
            'lang_res.php の landing path が想定外です: ' . $response['final_path'],
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], 'Language Resource Groups'),
            'lang_res.php が current groups page に着地していません。',
        );

        return [
            'final_path' => $response['final_path'],
            'redirect_count' => count($response['redirects']),
        ];
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res_list.php', static function () use (&$client, $parsed, $groupName): array {
        $requestPath = '/lang_res_list.php?ProjectPID='
            . $parsed['legacy_project_pid']
            . '&LanguageResourceGroupPID='
            . $parsed['group_pid'];
        $response = app_cli_lang_res_smoke_http_request($client, 'GET', $requestPath, [
            'follow_redirects' => true,
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res_list.php の取得に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res_list.php の HTTP status が 200 ではありません: ' . $response['status']);
        app_cli_lang_res_smoke_ensure(
            $response['final_path'] === '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources?group_pid=' . $parsed['group_pid'],
            'lang_res_list.php の landing path が想定外です: ' . $response['final_path'],
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], 'Language Resources'),
            'lang_res_list.php が current resources page に着地していません。',
        );
        app_cli_lang_res_smoke_ensure(
            $groupName === '' || str_contains($response['body'], $groupName),
            'lang_res_list.php の current resources page に group 名が見つかりません。',
        );

        return [
            'final_path' => $response['final_path'],
            'redirect_count' => count($response['redirects']),
        ];
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res_group_edit.php', static function () use (&$client, $parsed, $groupName): array {
        $requestPath = '/lang_res_group_edit.php?ProjectPID='
            . $parsed['legacy_project_pid']
            . '&PID='
            . $parsed['group_pid'];
        $response = app_cli_lang_res_smoke_http_request($client, 'GET', $requestPath, [
            'follow_redirects' => true,
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res_group_edit.php の取得に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res_group_edit.php の HTTP status が 200 ではありません: ' . $response['status']);
        app_cli_lang_res_smoke_ensure(
            $response['final_path'] === '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources/groups?group_pid=' . $parsed['group_pid'],
            'lang_res_group_edit.php の landing path が想定外です: ' . $response['final_path'],
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], 'group PID: <code>' . (string) $parsed['group_pid'] . '</code>'),
            'lang_res_group_edit.php の inspector に group pid が表示されていません。',
        );
        app_cli_lang_res_smoke_ensure(
            $groupName === '' || str_contains($response['body'], $groupName),
            'lang_res_group_edit.php の inspector に group 名が表示されていません。',
        );

        return [
            'final_path' => $response['final_path'],
            'redirect_count' => count($response['redirects']),
        ];
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res_edit.php', static function () use (&$client, $parsed, $resourceLegacyPid): array {
        $requestPath = '/lang_res_edit.php?ProjectPID='
            . $parsed['legacy_project_pid']
            . '&PID_BY_KEYNAME='
            . rawurlencode($parsed['resource_key']);
        $response = app_cli_lang_res_smoke_http_request($client, 'GET', $requestPath, [
            'follow_redirects' => true,
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res_edit.php の取得に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res_edit.php の HTTP status が 200 ではありません: ' . $response['status']);
        app_cli_lang_res_smoke_ensure(
            $response['final_path'] === '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources/' . rawurlencode($parsed['resource_key']),
            'lang_res_edit.php の landing path が想定外です: ' . $response['final_path'],
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], 'Language Resource Detail'),
            'lang_res_edit.php が current detail page に着地していません。',
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], $parsed['resource_key']),
            'lang_res_edit.php の detail page に resource key が見つかりません。',
        );
        if ($resourceLegacyPid > 0) {
            app_cli_lang_res_smoke_ensure(
                str_contains($response['body'], 'legacy PID: <code>' . (string) $resourceLegacyPid . '</code>'),
                'lang_res_edit.php の inspector に legacy resource pid が表示されていません。',
            );
        }

        return [
            'final_path' => $response['final_path'],
            'redirect_count' => count($response['redirects']),
        ];
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res_move.php', static function () use (&$client, $resourceLegacyPid, $parsed): array {
        app_cli_lang_res_smoke_ensure($resourceLegacyPid > 0, 'move check 用の legacy resource pid を取得できません。');
        $requestPath = '/lang_res_move.php?ProjectPID='
            . $parsed['legacy_project_pid']
            . '&PID='
            . $resourceLegacyPid;
        $response = app_cli_lang_res_smoke_http_request($client, 'GET', $requestPath, [
            'follow_redirects' => true,
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res_move.php の取得に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res_move.php の HTTP status が 200 ではありません: ' . $response['status']);
        app_cli_lang_res_smoke_ensure(
            $response['final_path'] === '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources/' . rawurlencode($parsed['resource_key']),
            'lang_res_move.php の landing path が想定外です: ' . $response['final_path'],
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], 'Language Resource Detail'),
            'lang_res_move.php が current detail page に着地していません。',
        );

        return [
            'final_path' => $response['final_path'],
            'redirect_count' => count($response['redirects']),
        ];
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res_assign_additional_group.php', static function () use (&$client, $resourceLegacyPid, $parsed): array {
        app_cli_lang_res_smoke_ensure($resourceLegacyPid > 0, 'additional-group check 用の legacy resource pid を取得できません。');
        $requestPath = '/lang_res_assign_additional_group.php'
            . '?ProjectPID=' . $parsed['legacy_project_pid']
            . '&BaseLanguageResourceGroupPID=' . $parsed['group_pid']
            . '&LanguageResourcePID=' . $resourceLegacyPid;
        $response = app_cli_lang_res_smoke_http_request($client, 'GET', $requestPath, [
            'follow_redirects' => true,
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res_assign_additional_group.php の取得に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res_assign_additional_group.php の HTTP status が 200 ではありません: ' . $response['status']);
        app_cli_lang_res_smoke_ensure(
            $response['final_path'] === '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources/' . rawurlencode($parsed['resource_key']),
            'lang_res_assign_additional_group.php の landing path が想定外です: ' . $response['final_path'],
        );
        app_cli_lang_res_smoke_ensure(
            str_contains($response['body'], 'Language Resource Detail'),
            'lang_res_assign_additional_group.php が current detail page に着地していません。',
        );

        return [
            'final_path' => $response['final_path'],
            'redirect_count' => count($response['redirects']),
        ];
    });

    app_cli_lang_res_smoke_record_check($checks, 'lang_res_auto_translate_ajax.php', static function () use (&$client, $parsed): array {
        $response = app_cli_lang_res_smoke_http_request($client, 'POST', '/lang_res_auto_translate_ajax.php', [
            'follow_redirects' => false,
            'form_params' => [
                'ProjectPID' => (string) $parsed['legacy_project_pid'],
                'SourceText' => 'Codex smoke login caption',
                'SourceLang' => 'en',
                'TargetLang' => 'ja',
            ],
        ]);
        app_cli_lang_res_smoke_ensure($response['ok'], 'lang_res_auto_translate_ajax.php の実行に失敗しました: ' . $response['error']);
        app_cli_lang_res_smoke_ensure($response['status'] === 200, 'lang_res_auto_translate_ajax.php の HTTP status が 200 ではありません: ' . $response['status']);

        $payload = json_decode($response['body'], true);
        app_cli_lang_res_smoke_ensure(is_array($payload), 'lang_res_auto_translate_ajax.php の response JSON を解釈できませんでした。');
        $status = trim((string) ($payload['_status'] ?? ''));
        $message = trim((string) ($payload['Message'] ?? ''));
        app_cli_lang_res_smoke_ensure(in_array($status, ['OK', 'NG'], true), '_status が legacy 互換ではありません。');
        app_cli_lang_res_smoke_ensure($message !== '', 'auto translate wrapper の Message が空です。');

        if ($status === 'OK') {
            app_cli_lang_res_smoke_ensure(
                trim((string) ($payload['Message'] ?? '')) === 'Successfully called',
                '成功時 Message が想定外です。',
            );
            app_cli_lang_res_smoke_ensure(array_key_exists('TranslatedText', $payload), '成功時 TranslatedText がありません。');
        } else {
            app_cli_lang_res_smoke_ensure(
                array_key_exists('Provider', $payload) && array_key_exists('ProviderCaption', $payload),
                '失敗時 legacy provider fields が不足しています。',
            );
            app_cli_lang_res_smoke_ensure(
                str_contains($message, 'repo 配下の JSON file を直接編集'),
                'auto translate wrapper が file workflow 案内を返していません: ' . $message,
            );
            foreach ([
                '認証が必要です。',
                'admin または config role が必要です。',
                'shared bootstrap が見つかりません。',
                'response が空です。',
                'response の解釈に失敗しました。',
                'フォームの有効期限が切れています。',
            ] as $blockedMessage) {
                app_cli_lang_res_smoke_ensure(
                    !str_contains($message, $blockedMessage),
                    'bridge/auth/bootstrap error が返っています: ' . $message,
                );
            }
        }

        return [
            'status' => $status,
            'message' => $message,
            'provider' => (string) ($payload['Provider'] ?? ''),
            'provider_caption' => (string) ($payload['ProviderCaption'] ?? ''),
        ];
    });
}

function app_cli_lang_res_smoke_assert_bridge_error_response(
    string $label,
    array $response,
    string $expectedPath,
    string $expectedBridgeError
): array {
    app_cli_lang_res_smoke_ensure($response['ok'], $label . ' に失敗しました: ' . $response['error']);
    app_cli_lang_res_smoke_ensure(
        $response['status'] === 200,
        $label . ' の HTTP status が 200 ではありません: ' . $response['status'],
    );
    app_cli_lang_res_smoke_ensure(
        app_cli_lang_res_smoke_path_only($response['final_path']) === $expectedPath,
        $label . ' の landing path が想定外です: ' . $response['final_path'],
    );

    $bridgeErrors = app_cli_lang_res_smoke_query_values($response['final_path'], 'bridge_errors');
    app_cli_lang_res_smoke_ensure(
        in_array($expectedBridgeError, $bridgeErrors, true),
        $label . ' の bridge_errors が想定外です: '
            . json_encode($bridgeErrors, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    );
    app_cli_lang_res_smoke_ensure(
        str_contains($response['body'], $expectedBridgeError),
        $label . ' の current page に bridge error が表示されていません。',
    );

    return [
        'final_path' => $response['final_path'],
        'redirect_count' => count($response['redirects']),
        'bridge_errors' => $bridgeErrors,
    ];
}

/**
 * @param array<string,mixed> $formParams
 * @return array{
 *     ok:bool,
 *     status:int,
 *     url:string,
 *     path:string,
 *     final_path:string,
 *     headers:array<string,list<string>>,
 *     header_lines:list<string>,
 *     body:string,
 *     location:string,
 *     redirects:list<array{
 *         from_path:string,
 *         status:int,
 *         location:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_lang_res_smoke_post_bridge_redirect(
    array &$client,
    string $path,
    array $formParams
): array {
    $initialResponse = app_cli_lang_res_smoke_http_request($client, 'POST', $path, [
        'follow_redirects' => false,
        'form_params' => $formParams,
    ]);
    if (
        !$initialResponse['ok']
        || !in_array($initialResponse['status'], [301, 302, 303, 307, 308], true)
        || trim($initialResponse['location']) === ''
    ) {
        return $initialResponse;
    }

    $followResponse = app_cli_lang_res_smoke_http_request($client, 'GET', $initialResponse['location'], [
        'follow_redirects' => true,
    ]);
    if (!$followResponse['ok']) {
        return $followResponse;
    }

    $followResponse['redirects'] = array_merge(
        [[
            'from_path' => $path,
            'status' => $initialResponse['status'],
            'location' => $initialResponse['location'],
        ]],
        $followResponse['redirects'],
    );

    return $followResponse;
}

/**
 * @param array{
 *     project_key:string,
 *     legacy_project_pid:int,
 *     group_pid:int,
 *     resource_key:string
 * } $parsed
 * @param array{
 *     group:array{
 *         legacy_group_pid:int,
 *         name:string
 *     },
 *     resource:array{
 *         legacy_resource_pid:int,
 *         resource_key:string
 *     }
 * } $context
 */
function app_cli_lang_res_smoke_run_legacy_write_bridge_checks(
    array &$client,
    array $parsed,
    array $context,
    array &$checks
): void {
    $projectPid = (string) $parsed['legacy_project_pid'];
    $groupPid = (int) ($context['group']['legacy_group_pid'] ?? 0);
    $resourceLegacyPid = (int) ($context['resource']['legacy_resource_pid'] ?? 0);
    $resourceKey = trim((string) ($context['resource']['resource_key'] ?? $parsed['resource_key']));

    app_cli_lang_res_smoke_ensure($groupPid > 0, 'legacy write bridge check 用の group pid が取得できません。');
    app_cli_lang_res_smoke_ensure($resourceLegacyPid > 0, 'legacy write bridge check 用の resource pid が取得できません。');
    app_cli_lang_res_smoke_ensure($resourceKey !== '', 'legacy write bridge check 用の resource key が取得できません。');

    $listPath = '/projects/' . rawurlencode($parsed['project_key']) . '/language-resources';
    $groupsPath = $listPath . '/groups';
    $detailPath = $listPath . '/' . rawurlencode($resourceKey);

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:group-create',
        static function () use (&$client, $projectPid, $groupsPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_group_edit.php', [
                'ProjectPID' => $projectPid,
                'UPDATE' => '1',
                'Name' => 'Codex Bridge Check Group',
            ]);

            return app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy group create bridge',
                $response,
                $groupsPath,
                'LanguageResource group の current route は read-only inspector に切り替わりました。group 編集は current admin では扱わず、repo 配下の group.json を直接編集してください。',
            );
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:group-update',
        static function () use (&$client, $projectPid, $groupPid, $groupsPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_group_edit.php', [
                'ProjectPID' => $projectPid,
                'PID' => (string) $groupPid,
                'UPDATE' => '1',
                'Name' => 'Codex Bridge Check Group Update',
            ]);

            $result = app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy group update bridge',
                $response,
                $groupsPath,
                'LanguageResource group の current route は read-only inspector に切り替わりました。group 編集は current admin では扱わず、repo 配下の group.json を直接編集してください。',
            );
            app_cli_lang_res_smoke_ensure(
                app_cli_lang_res_smoke_query_value($response['final_path'], 'group_pid') === (string) $groupPid,
                'legacy group update bridge の group_pid handoff が想定外です。',
            );

            return $result;
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:group-delete',
        static function () use (&$client, $projectPid, $groupPid, $groupsPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_group_edit.php', [
                'ProjectPID' => $projectPid,
                'PID' => (string) $groupPid,
                'DELETE' => '1',
            ]);

            $result = app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy group delete bridge',
                $response,
                $groupsPath,
                'LanguageResource group の current route は read-only inspector に切り替わりました。group 編集は current admin では扱わず、repo 配下の group.json を直接編集してください。',
            );
            app_cli_lang_res_smoke_ensure(
                app_cli_lang_res_smoke_query_value($response['final_path'], 'group_pid') === (string) $groupPid,
                'legacy group delete bridge の group_pid handoff が想定外です。',
            );

            return $result;
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:resource-create',
        static function () use (&$client, $projectPid, $groupPid, $listPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_edit.php', [
                'ProjectPID' => $projectPid,
                'UPDATE' => '1',
                'LanguageResourceGroupPID' => (string) $groupPid,
                'KeyName' => 'CODEX_BRIDGE_CHECK',
            ]);

            $result = app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy resource create bridge',
                $response,
                $listPath,
                'LanguageResource の current route は read-only inspector に切り替わりました。保存・削除・複製は current admin では扱わず、repo 配下の JSON file を直接編集してください。',
            );

            return $result;
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:resource-update',
        static function () use (&$client, $projectPid, $resourceLegacyPid, $detailPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_edit.php', [
                'ProjectPID' => $projectPid,
                'PID' => (string) $resourceLegacyPid,
                'UPDATE' => '1',
            ]);

            return app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy resource update bridge',
                $response,
                $detailPath,
                'LanguageResource の current route は read-only inspector に切り替わりました。保存・削除・複製は current admin では扱わず、repo 配下の JSON file を直接編集してください。',
            );
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:resource-delete',
        static function () use (&$client, $projectPid, $resourceLegacyPid, $detailPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_edit.php', [
                'ProjectPID' => $projectPid,
                'PID' => (string) $resourceLegacyPid,
                'DELETE' => '1',
            ]);

            return app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy resource delete bridge',
                $response,
                $detailPath,
                'LanguageResource の current route は read-only inspector に切り替わりました。保存・削除・複製は current admin では扱わず、repo 配下の JSON file を直接編集してください。',
            );
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:move-resource',
        static function () use (&$client, $projectPid, $resourceLegacyPid, $groupPid, $detailPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_move.php', [
                'ProjectPID' => $projectPid,
                'PID' => (string) $resourceLegacyPid,
                'UPDATE' => '1',
                'LanguageResourceGroupPID' => (string) $groupPid,
            ]);

            return app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy resource move bridge',
                $response,
                $detailPath,
                'LanguageResource move は current admin では扱いません。base group の変更は対象 resource.json を直接編集してください。',
            );
        },
    );

    app_cli_lang_res_smoke_record_check(
        $checks,
        'legacy-write-bridge:update-additional-groups',
        static function () use (&$client, $projectPid, $resourceLegacyPid, $groupPid, $detailPath): array {
            $response = app_cli_lang_res_smoke_post_bridge_redirect($client, '/lang_res_assign_additional_group.php', [
                'ProjectPID' => $projectPid,
                'LanguageResourcePID' => (string) $resourceLegacyPid,
                'BaseLanguageResourceGroupPID' => (string) $groupPid,
                'UPDATE' => '1',
                'AdditionalResourceGroupPIDList' => [(string) $groupPid],
            ]);

            return app_cli_lang_res_smoke_assert_bridge_error_response(
                'legacy additional-group bridge',
                $response,
                $detailPath,
                'LanguageResource additional group 更新は current admin では扱いません。additional_group_keys は対象 resource.json を直接編集してください。',
            );
        },
    );
}

/**
 * @return array{
 *     process:resource,
 *     log_path:string
 * }
 */
function app_cli_lang_res_smoke_start_server(array $serverEnv, array $parsed): array
{
    $logDir = app_cli_lang_res_smoke_repo_root() . '/work/tmp';
    if (!is_dir($logDir) && !mkdir($logDir, 0777, true) && !is_dir($logDir)) {
        throw new RuntimeException('server log directory を作成できません: ' . $logDir);
    }

    $logPath = $logDir . '/generated-html-db-language-resource-smoke-'
        . gmdate('Ymd_His')
        . '-'
        . $parsed['port']
        . '.log';
    $routerPath = app_cli_lang_res_smoke_repo_root() . '/mtool/scripts/generated_html_db_dev_router.php';

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['file', $logPath, 'a'],
        2 => ['file', $logPath, 'a'],
    ];

    $process = proc_open(
        [
            PHP_BINARY,
            '-S',
            $parsed['host'] . ':' . $parsed['port'],
            $routerPath,
        ],
        $descriptorSpec,
        $pipes,
        app_cli_lang_res_smoke_repo_root(),
        $serverEnv,
    );

    if (!is_resource($process)) {
        throw new RuntimeException('local server の起動に失敗しました。');
    }

    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    return [
        'process' => $process,
        'log_path' => $logPath,
    ];
}

function app_cli_lang_res_smoke_log_excerpt(string $logPath, int $maxBytes = 4000): string
{
    if (!is_file($logPath)) {
        return '';
    }

    $size = filesize($logPath);
    if (!is_int($size) || $size <= 0) {
        return '';
    }

    $handle = fopen($logPath, 'rb');
    if (!is_resource($handle)) {
        return '';
    }

    try {
        $offset = max(0, $size - $maxBytes);
        fseek($handle, $offset);
        $contents = stream_get_contents($handle);
    } finally {
        fclose($handle);
    }

    return is_string($contents) ? trim($contents) : '';
}

function app_cli_lang_res_smoke_wait_for_server(array &$client, array $serverState, int $timeoutSeconds): void
{
    $deadline = microtime(true) + $timeoutSeconds;
    $lastError = '';
    while (microtime(true) < $deadline) {
        $status = proc_get_status($serverState['process']);
        if (!is_array($status) || !($status['running'] ?? false)) {
            $logExcerpt = app_cli_lang_res_smoke_log_excerpt($serverState['log_path']);
            throw new RuntimeException(
                'local server が起動直後に停止しました。'
                . ($logExcerpt !== '' ? ' log=' . $logExcerpt : '')
            );
        }

        $response = app_cli_lang_res_smoke_http_request($client, 'GET', '/login', [
            'follow_redirects' => false,
        ]);
        if ($response['ok'] && $response['status'] === 200) {
            return;
        }

        $lastError = $response['ok']
            ? ('HTTP status=' . $response['status'])
            : $response['error'];
        usleep(200000);
    }

    $logExcerpt = app_cli_lang_res_smoke_log_excerpt($serverState['log_path']);
    throw new RuntimeException(
        'local server readiness timeout: '
        . $lastError
        . ($logExcerpt !== '' ? ' log=' . $logExcerpt : '')
    );
}

/**
 * @param resource $process
 */
function app_cli_lang_res_smoke_stop_server($process): void
{
    if (!is_resource($process)) {
        return;
    }

    $status = proc_get_status($process);
    if (is_array($status) && ($status['running'] ?? false)) {
        proc_terminate($process);
        usleep(300000);

        $status = proc_get_status($process);
        if (is_array($status) && ($status['running'] ?? false)) {
            proc_terminate($process, 9);
            usleep(300000);
        }
    }

    proc_close($process);
}

$defaults = app_cli_lang_res_smoke_env_defaults();
$parsed = app_cli_lang_res_smoke_parse_args($argv, $defaults);
if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_lang_res_smoke_usage() . PHP_EOL);
    exit(1);
}

if ($parsed['help']) {
    fwrite(STDOUT, app_cli_lang_res_smoke_usage() . PHP_EOL);
    exit(0);
}

$checks = [];
$cleanup = [];
$serverState = null;
$exitCode = 1;
$runtimeTarget = null;
$baseEnv = app_cli_lang_res_smoke_app_env($parsed);

try {
    app_cli_lang_res_smoke_apply_env($baseEnv);

    $app = app_bootstrap();
    $runtimeTarget = app_cli_lang_res_smoke_resolve_runtime_target($app, $parsed);
    app_cli_lang_res_smoke_ensure($runtimeTarget['ok'], $runtimeTarget['error']);
    $context = app_cli_lang_res_smoke_load_context($app, $parsed);

    $client = app_cli_lang_res_smoke_http_client(
        $parsed['host'],
        $parsed['port'],
        $parsed['http_timeout_seconds'],
    );
    $serverState = app_cli_lang_res_smoke_start_server(
        app_cli_lang_res_smoke_server_env($parsed, $baseEnv, $runtimeTarget['docroot']),
        $parsed,
    );
    app_cli_lang_res_smoke_wait_for_server(
        $client,
        $serverState,
        $parsed['server_timeout_seconds'],
    );

    app_cli_lang_res_smoke_run_non_mutating_checks($client, $parsed, $context, $checks);
    if ($parsed['allow_mutate']) {
        app_cli_lang_res_smoke_run_legacy_write_bridge_checks($client, $parsed, $context, $checks);
    }

    $exitCode = 0;
} catch (Throwable $throwable) {
    app_cli_lang_res_smoke_write_json([
        'ok' => false,
        'error' => $throwable->getMessage(),
        'docroot_mode' => (string) ($runtimeTarget['docroot_mode'] ?? ''),
        'artifact_key' => (string) ($runtimeTarget['artifact_key'] ?? ''),
        'artifact_dir' => (string) ($runtimeTarget['artifact_dir'] ?? ''),
        'manifest_path' => (string) ($runtimeTarget['manifest_path'] ?? ''),
        'docroot' => (string) ($runtimeTarget['docroot'] ?? ''),
        'published' => is_array($runtimeTarget) ? ($runtimeTarget['published'] ?? null) : null,
        'base_url' => 'http://' . $parsed['host'] . ':' . $parsed['port'],
        'publish' => $parsed['publish'],
        'allow_mutate' => $parsed['allow_mutate'],
        'checks' => $checks,
        'cleanup' => $cleanup,
        'server_log_path' => is_array($serverState) ? (string) ($serverState['log_path'] ?? '') : '',
        'server_log_excerpt' => is_array($serverState)
            ? app_cli_lang_res_smoke_log_excerpt((string) ($serverState['log_path'] ?? ''))
            : '',
    ], false);
} finally {
    if (is_array($serverState) && is_resource($serverState['process'] ?? null)) {
        app_cli_lang_res_smoke_stop_server($serverState['process']);
    }
}

if ($exitCode === 0) {
    app_cli_lang_res_smoke_write_json([
        'ok' => true,
        'docroot_mode' => (string) ($runtimeTarget['docroot_mode'] ?? ''),
        'artifact_key' => (string) ($runtimeTarget['artifact_key'] ?? ''),
        'artifact_dir' => (string) ($runtimeTarget['artifact_dir'] ?? ''),
        'manifest_path' => (string) ($runtimeTarget['manifest_path'] ?? ''),
        'docroot' => (string) ($runtimeTarget['docroot'] ?? ''),
        'published' => is_array($runtimeTarget) ? ($runtimeTarget['published'] ?? null) : null,
        'base_url' => 'http://' . $parsed['host'] . ':' . $parsed['port'],
        'publish' => $parsed['publish'],
        'allow_mutate' => $parsed['allow_mutate'],
        'checks' => $checks,
        'cleanup' => $cleanup,
        'server_log_path' => is_array($serverState) ? (string) ($serverState['log_path'] ?? '') : '',
    ], true);
}

exit($exitCode);
