#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/database.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_language_resource_catalog_loader.php';
require_once __DIR__ . '/debug/language_resource/lib/project_language_resource_db_bridge.php';
require_once dirname(__DIR__) . '/app/project_language_resource_route_common.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';

function app_cli_lang_res_db_retirement_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_language_resource_db_retirement_readiness.php [options]

Options:
  --project-key=KEY             target project key
  --legacy-project-pid=PID      legacy Project.PID fallback (default: 1 for MTOOL, else 0)
  --artifact-key=KEY            inspect a specific HTML-DB artifact
  --docroot=PATH                inspect an explicit generated/published HTML-DB docroot
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

function app_cli_lang_res_db_retirement_repo_root(): string
{
    return dirname(__DIR__, 2);
}

function app_cli_lang_res_db_retirement_relative_path(string $path): string
{
    $normalizedPath = str_replace('\\', '/', $path);
    $normalizedRoot = str_replace('\\', '/', app_cli_lang_res_db_retirement_repo_root());
    if (str_starts_with($normalizedPath, $normalizedRoot . '/')) {
        return substr($normalizedPath, strlen($normalizedRoot) + 1);
    }

    return $normalizedPath;
}

function app_cli_lang_res_db_retirement_apply_env(array $overrides): void
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
 *     project_key:string,
 *     legacy_project_pid:int,
 *     artifact_key:string,
 *     docroot:string,
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
function app_cli_lang_res_db_retirement_parse_args(array $argv): array
{
    $parsed = [
        'project_key' => '',
        'legacy_project_pid' => 0,
        'artifact_key' => '',
        'docroot' => '',
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
                'project_key' => '',
                'legacy_project_pid' => 0,
                'artifact_key' => '',
                'docroot' => '',
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

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'legacy_project_pid' => 0,
                'artifact_key' => '',
                'docroot' => '',
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
            case 'project-key':
                $parsed['project_key'] = app_normalize_project_key($normalizedValue);
                break;
            case 'legacy-project-pid':
                $parsed['legacy_project_pid'] = ctype_digit($normalizedValue)
                    ? (int) $normalizedValue
                    : -1;
                break;
            case 'artifact-key':
                $parsed['artifact_key'] = $normalizedValue;
                break;
            case 'docroot':
                $parsed['docroot'] = $normalizedValue;
                break;
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
                    'project_key' => '',
                    'legacy_project_pid' => 0,
                    'artifact_key' => '',
                    'docroot' => '',
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

    if ($parsed['project_key'] === '' || !app_project_key_is_valid($parsed['project_key'])) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'artifact_key' => '',
            'docroot' => '',
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
            'error' => 'valid --project-key=... is required.',
        ];
    }

    if ($parsed['legacy_project_pid'] < 0) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'artifact_key' => '',
            'docroot' => '',
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
            'error' => '--legacy-project-pid must be a positive integer or 0.',
        ];
    }

    if ($parsed['legacy_project_pid'] === 0 && $parsed['project_key'] === 'MTOOL') {
        $parsed['legacy_project_pid'] = 1;
    }

    if ($parsed['artifact_key'] !== '' && !app_project_output_artifact_key_is_valid($parsed['artifact_key'])) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'artifact_key' => '',
            'docroot' => '',
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
            'error' => 'artifact key format is invalid.',
        ];
    }

    if ($parsed['artifact_key'] !== '' && $parsed['docroot'] !== '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'artifact_key' => '',
            'docroot' => '',
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
            'error' => '--artifact-key and --docroot cannot be combined.',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $parsed['project_key'],
        'legacy_project_pid' => $parsed['legacy_project_pid'],
        'artifact_key' => $parsed['artifact_key'],
        'docroot' => $parsed['docroot'],
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
function app_cli_lang_res_db_retirement_app_env(array $parsed): array
{
    $env = [
        'APP_SITE' => 'admin',
        'APP_AUTH_MODE' => 'stub',
        'APP_AUTH_STUB_USER' => 'admin',
        'APP_AUTH_STUB_PASSWORD' => getenv('APP_AUTH_STUB_PASSWORD') ?: '',
        'APP_AUTH_STUB_NAME' => 'Language Resource DB Retirement Check',
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
 * @return array{
 *     status:string,
 *     ok:bool,
 *     blocking:bool,
 *     message:string,
 *     details:array<string,mixed>
 * }
 */
function app_cli_lang_res_db_retirement_check(
    string $status,
    string $message,
    array $details = [],
    bool $blocking = true,
): array {
    return [
        'status' => $status,
        'ok' => $status === 'pass',
        'blocking' => $blocking,
        'message' => $message,
        'details' => $details,
    ];
}

/**
 * @param array<string,mixed> $payload
 */
function app_cli_lang_res_db_retirement_write_json(array $payload, bool $fatal = false): void
{
    $stream = $fatal ? STDERR : STDOUT;
    fwrite(
        $stream,
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
}

function app_cli_lang_res_db_retirement_load_text(string $absolutePath): string
{
    $contents = file_get_contents($absolutePath);
    if (!is_string($contents)) {
        throw new RuntimeException('failed to read file: ' . $absolutePath);
    }

    return $contents;
}

/**
 * @param list<string> $relativeRoots
 * @return list<string>
 */
function app_cli_lang_res_db_retirement_php_files(array $relativeRoots): array
{
    static $cache = [];

    $cacheKey = implode("\n", $relativeRoots);
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $files = [];
    $repoRoot = app_cli_lang_res_db_retirement_repo_root();
    foreach ($relativeRoots as $relativeRoot) {
        $absoluteRoot = $repoRoot . '/' . trim($relativeRoot, '/');
        if (!is_dir($absoluteRoot)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absoluteRoot, FilesystemIterator::SKIP_DOTS),
        );
        /** @var SplFileInfo $entry */
        foreach ($iterator as $entry) {
            if (!$entry->isFile() || !str_ends_with($entry->getFilename(), '.php')) {
                continue;
            }

            $files[] = app_cli_lang_res_db_retirement_relative_path($entry->getPathname());
        }
    }

    $files = array_values(array_unique($files));
    sort($files, SORT_NATURAL);
    $cache[$cacheKey] = $files;

    return $files;
}

/**
 * @param list<string> $relativeRoots
 * @param list<string> $excludedRelativePaths
 * @return list<string>
 */
function app_cli_lang_res_db_retirement_php_string_references(
    string $needle,
    array $relativeRoots,
    array $excludedRelativePaths = [],
): array {
    $excluded = [];
    foreach ($excludedRelativePaths as $excludedRelativePath) {
        $excluded[app_cli_lang_res_db_retirement_relative_path($excludedRelativePath)] = true;
    }

    $repoRoot = app_cli_lang_res_db_retirement_repo_root();
    $matches = [];
    foreach (app_cli_lang_res_db_retirement_php_files($relativeRoots) as $relativePath) {
        if (isset($excluded[$relativePath])) {
            continue;
        }

        $contents = file_get_contents($repoRoot . '/' . $relativePath);
        if (!is_string($contents) || !str_contains($contents, $needle)) {
            continue;
        }

        $matches[] = $relativePath;
    }

    sort($matches, SORT_NATURAL);

    return $matches;
}

/**
 * @param list<string> $items
 * @param list<string> $allowed
 * @return list<string>
 */
function app_cli_lang_res_db_retirement_disallowed_items(array $items, array $allowed): array
{
    $allowedLookup = [];
    foreach ($allowed as $item) {
        $allowedLookup[$item] = true;
    }

    $disallowed = [];
    foreach ($items as $item) {
        if (isset($allowedLookup[$item])) {
            continue;
        }

        $disallowed[] = $item;
    }

    return $disallowed;
}

function app_cli_lang_res_db_retirement_resolve_docroot_path(string $docroot): string
{
    if (trim($docroot) === '') {
        throw new RuntimeException('docroot is required.');
    }

    $resolved = realpath($docroot);
    if ($resolved === false || !is_dir($resolved)) {
        throw new RuntimeException('docroot was not found: ' . $docroot);
    }

    return rtrim(str_replace('\\', '/', $resolved), '/');
}

/**
 * @param array{
 *     project_key:string,
 *     artifact_key:string,
 *     docroot:string
 * } $parsed
 * @return array{
 *     ok:bool,
 *     docroot_mode:string,
 *     artifact_key:string,
 *     artifact_dir:string,
 *     manifest_path:string,
 *     docroot:string,
 *     error:string
 * }
 */
function app_cli_lang_res_db_retirement_resolve_runtime_target(array $parsed): array
{
    if ($parsed['docroot'] !== '') {
        try {
            $docroot = app_cli_lang_res_db_retirement_resolve_docroot_path($parsed['docroot']);
        } catch (Throwable $throwable) {
            return [
                'ok' => false,
                'docroot_mode' => '',
                'artifact_key' => '',
                'artifact_dir' => '',
                'manifest_path' => '',
                'docroot' => '',
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
            'error' => '',
        ];
    }

    $artifactRoot = app_cli_lang_res_db_retirement_repo_root()
        . '/work/artifacts/source-outputs/'
        . $parsed['project_key'];
    if (!is_dir($artifactRoot)) {
        return [
            'ok' => false,
            'docroot_mode' => '',
            'artifact_key' => '',
            'artifact_dir' => '',
            'manifest_path' => '',
            'docroot' => '',
            'error' => 'artifact root was not found: ' . $artifactRoot,
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
        if (!is_array($manifest) || ($manifest['source_output_key'] ?? null) !== 'HTML-DB') {
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
            'docroot_mode' => 'artifact',
            'artifact_key' => $artifactKey,
            'artifact_dir' => $artifactDir,
            'manifest_path' => $manifestPath,
            'docroot' => str_replace('\\', '/', $docroot),
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'docroot_mode' => '',
        'artifact_key' => '',
        'artifact_dir' => '',
        'manifest_path' => '',
        'docroot' => '',
        'error' => $parsed['artifact_key'] !== ''
            ? 'specified HTML-DB artifact was not found.'
            : 'HTML-DB artifact was not found.',
    ];
}

/**
 * @return array{
 *     docroot:string,
 *     inspected_files:array<string,array{
 *         exists:bool,
 *         absolute_path:string,
 *         uses_loader:bool,
 *         calls_catalog_loader:bool,
 *         uses_repository_shim:bool,
 *         uses_db_bridge:bool,
 *         error:string
 *     }>,
 *     required_loader_files:list<string>,
 *     error:string
 * }
 */
function app_cli_lang_res_db_retirement_runtime_wrapper_report(string $docroot): array
{
    $requiredLoaderFiles = [
        'lang_res_edit.php',
        'lang_res_move.php',
        'lang_res_assign_additional_group.php',
    ];
    $inspectedFiles = array_values(array_unique(array_merge(
        [
            'lang_res.php',
            'lang_res_list.php',
            'lang_res_group_edit.php',
        ],
        $requiredLoaderFiles,
    )));
    sort($inspectedFiles, SORT_NATURAL);

    $report = [];
    foreach ($inspectedFiles as $relativePath) {
        $absolutePath = rtrim($docroot, '/') . '/' . $relativePath;
        if (!is_file($absolutePath)) {
            $report[$relativePath] = [
                'exists' => false,
                'absolute_path' => $absolutePath,
                'uses_loader' => false,
                'calls_catalog_loader' => false,
                'uses_repository_shim' => false,
                'uses_db_bridge' => false,
                'error' => 'file was not found.',
            ];
            continue;
        }

        $contents = file_get_contents($absolutePath);
        if (!is_string($contents)) {
            $report[$relativePath] = [
                'exists' => true,
                'absolute_path' => $absolutePath,
                'uses_loader' => false,
                'calls_catalog_loader' => false,
                'uses_repository_shim' => false,
                'uses_db_bridge' => false,
                'error' => 'file could not be read.',
            ];
            continue;
        }

        $report[$relativePath] = [
            'exists' => true,
            'absolute_path' => $absolutePath,
            'uses_loader' => str_contains($contents, 'project_language_resource_catalog_loader.php'),
            'calls_catalog_loader' => str_contains($contents, 'app_fetch_project_language_resource_catalog('),
            'uses_repository_shim' => str_contains($contents, 'project_language_resource_repository.php'),
            'uses_db_bridge' => str_contains($contents, 'project_language_resource_db_bridge.php'),
            'error' => '',
        ];
    }

    return [
        'docroot' => $docroot,
        'inspected_files' => $report,
        'required_loader_files' => $requiredLoaderFiles,
        'error' => '',
    ];
}

/**
 * @return list<string>
 */
function app_cli_lang_res_db_retirement_canonical_table_names(): array
{
    return [
        'project_language_resource_groups',
        'project_language_resource_group_languages',
        'project_language_resource_group_source_outputs',
        'project_language_resource_languages',
        'project_language_resources',
        'project_language_resource_captions',
        'project_language_resource_additional_groups',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     connection:array{
 *         ok:bool,
 *         label:string,
 *         detail:string
 *     },
 *     schema:string,
 *     table_statuses:array<string,array{
 *         exists:bool,
 *         row_count:int
 *     }>,
 *     total_row_count:int,
 *     project_id:int,
 *     project_counts:array<string,int>|null,
 *     replaceable_counts:array<string,int>|null,
 *     residual_counts:array<string,int>|null,
 *     project_source_of_truth_counts:array<string,array<string,int>>|null,
 *     residual_source_of_truth_counts:array<string,array<string,int>>|null,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_cli_lang_res_db_retirement_database_report(array $app, string $projectKey): array
{
    $probe = app_probe_database($app);
    $report = [
        'ok' => false,
        'connection' => $probe,
        'schema' => '',
        'table_statuses' => [],
        'total_row_count' => 0,
        'project_id' => 0,
        'project_counts' => null,
        'replaceable_counts' => null,
        'residual_counts' => null,
        'project_source_of_truth_counts' => null,
        'residual_source_of_truth_counts' => null,
        'warnings' => [],
        'error' => '',
    ];

    if (!$probe['ok']) {
        $report['warnings'][] = 'DB inspection skipped: ' . $probe['detail'];
        $report['error'] = $probe['detail'];

        return $report;
    }

    try {
        $pdo = app_create_pdo($app);
    } catch (Throwable $throwable) {
        $report['warnings'][] = 'DB inspection skipped: ' . $throwable->getMessage();
        $report['error'] = $throwable->getMessage();

        return $report;
    }

    $report['ok'] = true;
    $report['schema'] = app_project_language_resource_pdo_current_schema($pdo);

    foreach (app_cli_lang_res_db_retirement_canonical_table_names() as $tableName) {
        $exists = app_project_language_resource_pdo_table_exists($pdo, $tableName);
        $rowCount = 0;
        if ($exists) {
            $rowCount = (int) ($pdo->query('SELECT COUNT(*) FROM ' . $tableName)->fetchColumn() ?? 0);
            $report['total_row_count'] += $rowCount;
        }

        $report['table_statuses'][$tableName] = [
            'exists' => $exists,
            'row_count' => $rowCount,
        ];
    }

    if (!app_project_language_resource_canonical_tables_available($pdo)) {
        return $report;
    }

    try {
        $projectId = app_project_language_resource_pdo_resolve_project_id($pdo, $projectKey);
        $report['project_id'] = $projectId;
        $projectCounts = app_project_language_resource_canonical_table_counts($pdo, $projectId);
        $replaceableCounts = app_project_language_resource_canonical_table_counts(
            $pdo,
            $projectId,
            [
                app_project_language_resource_file_catalog_source_of_truth(),
                app_project_language_resource_bootstrap_reference_source_of_truth(),
            ],
        );

        $residualCounts = [];
        foreach ($projectCounts as $key => $count) {
            $residualCounts[$key] = max(0, $count - (int) ($replaceableCounts[$key] ?? 0));
        }

        $tableMap = [
            'captions' => 'project_language_resource_captions',
            'additional_group_assignments' => 'project_language_resource_additional_groups',
            'group_source_outputs' => 'project_language_resource_group_source_outputs',
            'group_languages' => 'project_language_resource_group_languages',
            'resources' => 'project_language_resources',
            'groups' => 'project_language_resource_groups',
            'languages' => 'project_language_resource_languages',
        ];
        $projectSourceOfTruthCounts = [];
        $residualSourceOfTruthCounts = [];
        $allowedSourceOfTruths = [
            app_project_language_resource_file_catalog_source_of_truth(),
            app_project_language_resource_bootstrap_reference_source_of_truth(),
        ];
        foreach ($tableMap as $summaryKey => $tableName) {
            $statement = $pdo->prepare(
                'SELECT source_of_truth, COUNT(*) AS count_rows
                FROM ' . $tableName . '
                WHERE project_id = :project_id
                GROUP BY source_of_truth
                ORDER BY source_of_truth'
            );
            $statement->execute([
                ':project_id' => $projectId,
            ]);

            $countsBySourceOfTruth = [];
            foreach ($statement->fetchAll() as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $sourceOfTruth = trim((string) ($row['source_of_truth'] ?? ''));
                if ($sourceOfTruth === '') {
                    $sourceOfTruth = '(empty)';
                }

                $countRows = (int) ($row['count_rows'] ?? 0);
                $countsBySourceOfTruth[$sourceOfTruth] = $countRows;

                if (!in_array($sourceOfTruth, $allowedSourceOfTruths, true) && $countRows > 0) {
                    $residualSourceOfTruthCounts[$summaryKey][$sourceOfTruth] = $countRows;
                }
            }

            $projectSourceOfTruthCounts[$summaryKey] = $countsBySourceOfTruth;
            if (!array_key_exists($summaryKey, $residualSourceOfTruthCounts)) {
                $residualSourceOfTruthCounts[$summaryKey] = [];
            }
        }

        $report['project_counts'] = $projectCounts;
        $report['replaceable_counts'] = $replaceableCounts;
        $report['residual_counts'] = $residualCounts;
        $report['project_source_of_truth_counts'] = $projectSourceOfTruthCounts;
        $report['residual_source_of_truth_counts'] = $residualSourceOfTruthCounts;
    } catch (Throwable $throwable) {
        $report['warnings'][] = 'project-scoped DB counts were not available: ' . $throwable->getMessage();
    }

    return $report;
}

try {
    $parsed = app_cli_lang_res_db_retirement_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_lang_res_db_retirement_usage() . PHP_EOL);
        exit(0);
    }

    if (!$parsed['ok']) {
        fwrite(
            STDERR,
            $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_lang_res_db_retirement_usage() . PHP_EOL,
        );
        exit(64);
    }

    app_cli_lang_res_db_retirement_apply_env(
        app_cli_lang_res_db_retirement_app_env($parsed),
    );

    $app = app_bootstrap();
    $catalogRoots = ['mtool/app', 'mtool/scripts'];
    $selfRelativePath = 'mtool/scripts/check_language_resource_db_retirement_readiness.php';

    $fileCatalog = app_project_language_resource_load_file_catalog($parsed['project_key']);
    $catalogResult = app_fetch_project_language_resource_catalog(
        $app,
        $parsed['project_key'],
        $parsed['legacy_project_pid'],
    );
    $catalog = is_array($catalogResult['item'] ?? null) ? $catalogResult['item'] : [];
    $moduleState = app_project_language_resource_module_state_for_project(
        $app,
        $parsed['project_key'],
        $parsed['legacy_project_pid'],
    );
    $fileLocations = app_project_language_resource_file_locations($parsed['project_key']);

    $catalogCounts = [
        'resources' => (int) ($catalog['resource_count'] ?? 0),
        'groups' => (int) ($catalog['group_count'] ?? 0),
        'languages' => (int) ($catalog['language_count'] ?? 0),
        'group_languages' => (int) ($catalog['group_language_count'] ?? 0),
        'group_source_outputs' => (int) ($catalog['group_source_output_count'] ?? 0),
        'additional_group_assignments' => (int) ($catalog['additional_group_assignment_count'] ?? 0),
        'captions' => (int) ($catalog['caption_count'] ?? 0),
    ];

    $loaderPath = app_cli_lang_res_db_retirement_repo_root() . '/mtool/app/project_language_resource_catalog_loader.php';
    $generatorPath = app_cli_lang_res_db_retirement_repo_root() . '/mtool/app/project_output_html_module_generator.php';
    $loaderText = app_cli_lang_res_db_retirement_load_text($loaderPath);
    $generatorText = app_cli_lang_res_db_retirement_load_text($generatorPath);

    $loaderConsumers = app_cli_lang_res_db_retirement_php_string_references(
        'app_fetch_project_language_resource_catalog(',
        $catalogRoots,
        [
            $selfRelativePath,
            'mtool/app/project_language_resource_catalog_loader.php',
        ],
    );
    $expectedLoaderConsumers = [
        'mtool/app/project_language_resource_route_common.php',
        'mtool/app/project_output_html_module_generator.php',
        'mtool/scripts/check_generated_html_db_language_resource_wrappers.php',
    ];
    $missingLoaderConsumers = [];
    foreach ($expectedLoaderConsumers as $expectedLoaderConsumer) {
        if (!in_array($expectedLoaderConsumer, $loaderConsumers, true)) {
            $missingLoaderConsumers[] = $expectedLoaderConsumer;
        }
    }

    $dbBridgeReferences = app_cli_lang_res_db_retirement_php_string_references(
        'project_language_resource_db_bridge.php',
        $catalogRoots,
    );
    $allowedDbBridgeReferences = [
        'mtool/scripts/debug/language_resource/drop_project_language_resource_db_tables.php',
        'mtool/scripts/debug/language_resource/inspect_language_resource_db_residual_rows.php',
        'mtool/scripts/debug/language_resource/lib/project_language_resource_sync_service.php',
        'mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php',
        $selfRelativePath,
    ];
    $unexpectedDbBridgeReferences = app_cli_lang_res_db_retirement_disallowed_items(
        $dbBridgeReferences,
        $allowedDbBridgeReferences,
    );

    $repositoryShimReferences = app_cli_lang_res_db_retirement_php_string_references(
        'project_language_resource_repository.php',
        $catalogRoots,
        [$selfRelativePath],
    );

    $runtimeTarget = app_cli_lang_res_db_retirement_resolve_runtime_target($parsed);
    $runtimeWrapperReport = [
        'docroot' => '',
        'inspected_files' => [],
        'required_loader_files' => [],
        'error' => $runtimeTarget['error'],
    ];
    if ($runtimeTarget['ok']) {
        $runtimeWrapperReport = app_cli_lang_res_db_retirement_runtime_wrapper_report(
            $runtimeTarget['docroot'],
        );
    }

    $missingWrapperFiles = [];
    $loaderMissingWrapperFiles = [];
    $unexpectedWrapperDbDependencies = [];
    foreach ($runtimeWrapperReport['inspected_files'] as $relativePath => $wrapperFileReport) {
        if (!$wrapperFileReport['exists']) {
            $missingWrapperFiles[] = $relativePath;
            continue;
        }

        if (
            in_array($relativePath, $runtimeWrapperReport['required_loader_files'], true)
            && (!$wrapperFileReport['uses_loader'] || !$wrapperFileReport['calls_catalog_loader'])
        ) {
            $loaderMissingWrapperFiles[] = $relativePath;
        }

        if ($wrapperFileReport['uses_repository_shim'] || $wrapperFileReport['uses_db_bridge']) {
            $unexpectedWrapperDbDependencies[] = $relativePath;
        }
    }

    $databaseReport = app_cli_lang_res_db_retirement_database_report(
        $app,
        $parsed['project_key'],
    );

    $checks = [];

    $checks['file_catalog_exists'] = app_cli_lang_res_db_retirement_check(
        $fileCatalog['exists'] ? 'pass' : 'fail',
        $fileCatalog['exists']
            ? 'file catalog manifest was found.'
            : 'file catalog manifest is missing.',
        [
            'root_path' => $fileCatalog['root_path'],
            'manifest_path' => $fileLocations['manifest_path'],
        ],
    );

    $checks['file_catalog_loads'] = app_cli_lang_res_db_retirement_check(
        $fileCatalog['exists'] && $fileCatalog['ok'] ? 'pass' : 'fail',
        $fileCatalog['exists'] && $fileCatalog['ok']
            ? 'file catalog loaded successfully.'
            : ($fileCatalog['error'] !== '' ? $fileCatalog['error'] : 'file catalog load failed.'),
        [
            'root_path' => $fileCatalog['root_path'],
            'warnings' => $fileCatalog['warnings'],
            'errors' => $fileCatalog['errors'],
            'catalog_counts' => $catalogCounts,
        ],
    );

    $allowedCatalogSources = ['file-canonical', 'reference', 'empty'];
    $catalogSource = (string) ($catalogResult['source'] ?? '');
    $checks['runtime_loader_source_allowed'] = app_cli_lang_res_db_retirement_check(
        $catalogResult['ok'] && in_array($catalogSource, $allowedCatalogSources, true) ? 'pass' : 'fail',
        $catalogResult['ok'] && in_array($catalogSource, $allowedCatalogSources, true)
            ? 'runtime loader resolves through the allowed non-DB sources.'
            : ($catalogResult['error'] !== '' ? $catalogResult['error'] : 'runtime loader source is not allowed.'),
        [
            'source' => $catalogSource,
            'allowed_sources' => $allowedCatalogSources,
            'error' => (string) ($catalogResult['error'] ?? ''),
        ],
    );

    $checks['runtime_loader_source_file_canonical'] = app_cli_lang_res_db_retirement_check(
        $catalogResult['ok'] && $catalogSource === 'file-canonical' ? 'pass' : 'fail',
        $catalogResult['ok'] && $catalogSource === 'file-canonical'
            ? 'project currently resolves to file-canonical.'
            : 'project is not yet resolved from file-canonical.',
        [
            'source' => $catalogSource,
            'catalog_counts' => $catalogCounts,
        ],
    );

    $checks['current_admin_editor_retired'] = app_cli_lang_res_db_retirement_check(
        ($moduleState['editor_available'] ?? true) === false ? 'pass' : 'fail',
        ($moduleState['editor_available'] ?? true) === false
            ? 'current admin remains inspector-only.'
            : 'current admin still exposes an editor path.',
        [
            'state' => $moduleState['state'] ?? '',
            'module_status' => $moduleState['module_status'] ?? '',
            'title' => $moduleState['title'] ?? '',
            'readonly_message' => $moduleState['readonly_message'] ?? '',
        ],
    );

    $loaderForbiddenMatches = [];
    foreach ([
        'project_language_resource_db_bridge.php',
        'project_language_resource_repository.php',
        'app_create_pdo(',
        'app_project_language_resource_canonical_table_counts(',
    ] as $needle) {
        if (str_contains($loaderText, $needle)) {
            $loaderForbiddenMatches[] = $needle;
        }
    }
    $checks['loader_free_of_db_runtime'] = app_cli_lang_res_db_retirement_check(
        $loaderForbiddenMatches === [] ? 'pass' : 'fail',
        $loaderForbiddenMatches === []
            ? 'catalog loader does not pull DB canonical helpers.'
            : 'catalog loader still contains DB-facing references.',
        [
            'loader_path' => app_cli_lang_res_db_retirement_relative_path($loaderPath),
            'forbidden_matches' => $loaderForbiddenMatches,
        ],
    );

    $checks['expected_live_loader_consumers_present'] = app_cli_lang_res_db_retirement_check(
        $missingLoaderConsumers === [] ? 'pass' : 'fail',
        $missingLoaderConsumers === []
            ? 'expected live/runtime consumers use the loader.'
            : 'some expected live/runtime consumers are missing the loader call.',
        [
            'loader_consumers' => $loaderConsumers,
            'expected_consumers' => $expectedLoaderConsumers,
            'missing_consumers' => $missingLoaderConsumers,
        ],
    );

    $checks['db_bridge_references_isolated'] = app_cli_lang_res_db_retirement_check(
        $unexpectedDbBridgeReferences === [] ? 'pass' : 'fail',
        $unexpectedDbBridgeReferences === []
            ? 'DB bridge references are isolated to migration/debug paths.'
            : 'unexpected DB bridge references remain outside migration/debug paths.',
        [
            'references' => $dbBridgeReferences,
            'allowed_references' => $allowedDbBridgeReferences,
            'unexpected_references' => $unexpectedDbBridgeReferences,
        ],
    );

    $checks['repository_shim_unused'] = app_cli_lang_res_db_retirement_check(
        $repositoryShimReferences === [] ? 'pass' : 'fail',
        $repositoryShimReferences === []
            ? 'compatibility shim has no remaining live references.'
            : 'compatibility shim is still referenced.',
        [
            'references' => $repositoryShimReferences,
        ],
    );

    $generatorForbiddenMatches = [];
    foreach ([
        'project_language_resource_repository.php',
        'project_language_resource_db_bridge.php',
    ] as $needle) {
        if (str_contains($generatorText, $needle)) {
            $generatorForbiddenMatches[] = $needle;
        }
    }
    $generatorLoaderReferenceCount = substr_count($generatorText, 'project_language_resource_catalog_loader.php');
    $generatorCatalogCallCount = substr_count($generatorText, 'app_fetch_project_language_resource_catalog(');
    $checks['generator_uses_loader'] = app_cli_lang_res_db_retirement_check(
        $generatorLoaderReferenceCount > 0
            && $generatorCatalogCallCount > 0
            && $generatorForbiddenMatches === []
            ? 'pass'
            : 'fail',
        $generatorLoaderReferenceCount > 0
            && $generatorCatalogCallCount > 0
            && $generatorForbiddenMatches === []
            ? 'generator/wrapper template points at the loader.'
            : 'generator/wrapper template still has an invalid LanguageResource dependency contract.',
        [
            'generator_path' => app_cli_lang_res_db_retirement_relative_path($generatorPath),
            'loader_reference_count' => $generatorLoaderReferenceCount,
            'catalog_call_count' => $generatorCatalogCallCount,
            'forbidden_matches' => $generatorForbiddenMatches,
        ],
    );

    $wrapperStatus = 'fail';
    $wrapperMessage = 'runtime wrapper contract could not be verified.';
    if ($runtimeTarget['ok']) {
        if (
            $missingWrapperFiles === []
            && $loaderMissingWrapperFiles === []
            && $unexpectedWrapperDbDependencies === []
        ) {
            $wrapperStatus = 'pass';
            $wrapperMessage = 'runtime wrapper contract points at the loader and avoids DB bridge/runtime shims.';
        } else {
            $wrapperStatus = 'fail';
            $wrapperMessage = 'runtime wrapper contract still needs cleanup.';
        }
    } elseif ($runtimeTarget['error'] !== '') {
        $wrapperStatus = 'skip';
        $wrapperMessage = $runtimeTarget['error'];
    }
    $checks['runtime_wrappers_use_loader_bridge'] = app_cli_lang_res_db_retirement_check(
        $wrapperStatus,
        $wrapperMessage,
        [
            'docroot_mode' => $runtimeTarget['docroot_mode'] ?? '',
            'artifact_key' => $runtimeTarget['artifact_key'] ?? '',
            'docroot' => $runtimeTarget['docroot'] ?? '',
            'missing_files' => $missingWrapperFiles,
            'files_missing_loader' => $loaderMissingWrapperFiles,
            'unexpected_db_dependencies' => $unexpectedWrapperDbDependencies,
            'inspected_files' => $runtimeWrapperReport['inspected_files'],
        ],
    );

    $dbStatus = 'skip';
    $dbMessage = 'DB state could not be verified.';
    if ($databaseReport['ok']) {
        if ($databaseReport['total_row_count'] === 0) {
            $dbStatus = 'pass';
            $dbMessage = 'LanguageResource DB tables are absent or empty.';
        } else {
            $dbStatus = 'fail';
            $dbMessage = 'LanguageResource DB tables still contain rows.';
        }
    } elseif ($databaseReport['error'] !== '') {
        $dbStatus = 'skip';
        $dbMessage = $databaseReport['error'];
    }
    $checks['db_tables_absent_or_empty'] = app_cli_lang_res_db_retirement_check(
        $dbStatus,
        $dbMessage,
        [
            'connection' => $databaseReport['connection'],
            'schema' => $databaseReport['schema'],
            'table_statuses' => $databaseReport['table_statuses'],
            'total_row_count' => $databaseReport['total_row_count'],
            'project_id' => $databaseReport['project_id'],
            'project_counts' => $databaseReport['project_counts'],
            'replaceable_counts' => $databaseReport['replaceable_counts'],
            'residual_counts' => $databaseReport['residual_counts'],
            'project_source_of_truth_counts' => $databaseReport['project_source_of_truth_counts'],
            'residual_source_of_truth_counts' => $databaseReport['residual_source_of_truth_counts'],
            'warnings' => $databaseReport['warnings'],
        ],
    );

    $blockingFailures = [];
    $blockingSkips = [];
    foreach ($checks as $key => $check) {
        if (!$check['blocking']) {
            continue;
        }

        if ($check['status'] === 'fail') {
            $blockingFailures[$key] = $check['message'];
            continue;
        }

        if ($check['status'] === 'skip') {
            $blockingSkips[$key] = $check['message'];
        }
    }

    $ready = $blockingFailures === [] && $blockingSkips === [];

    $warnings = array_values(array_unique(array_merge(
        $fileCatalog['warnings'],
        $fileLocations['warnings'],
        $databaseReport['warnings'],
    )));

    $summary = [
        'project_key' => $parsed['project_key'],
        'legacy_project_pid' => $parsed['legacy_project_pid'],
        'ready' => $ready,
        'catalog_source' => $catalogSource,
        'module_state' => $moduleState['state'] ?? '',
        'module_status' => $moduleState['module_status'] ?? '',
        'catalog_root_path' => $fileCatalog['root_path'],
        'docroot_mode' => $runtimeTarget['docroot_mode'] ?? '',
        'docroot' => $runtimeTarget['docroot'] ?? '',
        'artifact_key' => $runtimeTarget['artifact_key'] ?? '',
        'blocking_check_count' => count($checks),
        'blocking_fail_count' => count($blockingFailures),
        'blocking_skip_count' => count($blockingSkips),
    ];

    $payload = [
        'ok' => true,
        'ready' => $ready,
        'summary' => $summary,
        'blocking_failures' => $blockingFailures,
        'blocking_skips' => $blockingSkips,
        'checks' => $checks,
        'catalog' => [
            'file_catalog' => [
                'exists' => $fileCatalog['exists'],
                'ok' => $fileCatalog['ok'],
                'root_path' => $fileCatalog['root_path'],
                'manifest_path' => $fileLocations['manifest_path'],
                'group_file_count' => count($fileLocations['group_file_paths_by_pid']),
                'resource_file_count' => count($fileLocations['resource_file_paths_by_key']),
                'warnings' => $fileCatalog['warnings'],
                'errors' => $fileCatalog['errors'],
            ],
            'runtime_loader' => [
                'ok' => $catalogResult['ok'],
                'source' => $catalogSource,
                'error' => (string) ($catalogResult['error'] ?? ''),
                'counts' => $catalogCounts,
            ],
            'module_state' => $moduleState,
        ],
        'dependencies' => [
            'loader_consumers' => $loaderConsumers,
            'db_bridge_references' => $dbBridgeReferences,
            'repository_shim_references' => $repositoryShimReferences,
        ],
        'runtime_target' => $runtimeTarget,
        'runtime_wrappers' => $runtimeWrapperReport,
        'database' => $databaseReport,
        'warnings' => $warnings,
    ];

    app_cli_lang_res_db_retirement_write_json($payload, false);
    exit($ready ? 0 : 1);
} catch (Throwable $throwable) {
    app_cli_lang_res_db_retirement_write_json(
        [
            'ok' => false,
            'ready' => false,
            'error' => $throwable->getMessage(),
        ],
        true,
    );
    exit(1);
}
