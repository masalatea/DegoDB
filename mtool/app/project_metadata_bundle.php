<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/database_source_repository.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/generated_runtime_auth_policy.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/project_membership_repository.php';
require_once __DIR__ . '/table_metadata_repository.php';
require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_metadata_bundle_type(): string
{
    return 'mtool-project-metadata';
}

function app_project_metadata_bundle_schema_version(): string
{
    return '2026-05-26.project-core.v2';
}

function app_project_metadata_bundle_default_scope(): string
{
    return 'project-core';
}

/**
 * @return list<string>
 */
function app_project_metadata_bundle_supported_scopes(): array
{
    return [app_project_metadata_bundle_default_scope()];
}

function app_project_metadata_bundle_scope_is_supported(string $scope): bool
{
    return in_array(trim($scope), app_project_metadata_bundle_supported_scopes(), true);
}

/**
 * @return list<string>
 */
function app_project_metadata_bundle_included_sections(string $scope = ''): array
{
    $normalizedScope = trim($scope);
    if ($normalizedScope === '') {
        $normalizedScope = app_project_metadata_bundle_default_scope();
    }

    return match ($normalizedScope) {
        'project-core' => [
            'project',
            'memberships',
            'tables',
            'data_classes',
            'db_access',
            'source_outputs',
        ],
        default => [],
    };
}

/**
 * @return list<string>
 */
function app_project_metadata_bundle_excluded_sections(string $scope = ''): array
{
    $normalizedScope = trim($scope);
    if ($normalizedScope === '') {
        $normalizedScope = app_project_metadata_bundle_default_scope();
    }

    return match ($normalizedScope) {
        'project-core' => [
            'page_security',
            'host_assignments',
            'compare_outputs',
            'compare_output_assets',
            'custom_proxies',
            'project_html',
            'project_html_source_bindings',
            'html_templates',
            'language_resource_file_tree',
        ],
        default => [],
    };
}

function app_project_metadata_bundle_secrets_policy(string $scope = ''): string
{
    $normalizedScope = trim($scope);
    if ($normalizedScope === '') {
        $normalizedScope = app_project_metadata_bundle_default_scope();
    }

    return match ($normalizedScope) {
        'project-core' => 'exclude-all',
        default => 'exclude-all',
    };
}

/**
 * @return array<string,string>
 */
function app_project_metadata_bundle_section_filename_map(): array
{
    return [
        'project' => 'project.json',
        'memberships' => 'memberships.json',
        'database_sources' => 'database-sources.json',
        'tables' => 'tables.json',
        'data_classes' => 'data-classes.json',
        'db_access' => 'db-access.json',
        'source_outputs' => 'source-outputs.json',
    ];
}

function app_project_metadata_bundle_normalize_requested_by(string $value): string
{
    $normalized = strtolower(trim($value));
    $normalized = preg_replace('/[^a-z0-9._-]+/', '-', $normalized);
    $normalized = trim((string) $normalized, '-');

    return $normalized !== '' ? $normalized : 'cli';
}

/**
 * @param mixed $value
 * @return array{
 *     ok:bool,
 *     keys:list<string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_parse_database_source_keys($value): array
{
    $rawValues = [];
    if (is_string($value)) {
        $rawValues = preg_split('/[\s,]+/', $value) ?: [];
    } elseif (is_array($value)) {
        foreach ($value as $candidate) {
            if (is_string($candidate)) {
                $rawValues[] = $candidate;
            }
        }
    }

    $keys = [];
    foreach ($rawValues as $rawValue) {
        $sourceKey = app_normalize_database_source_key((string) $rawValue);
        if ($sourceKey === '') {
            continue;
        }
        if (!app_database_source_key_is_valid($sourceKey)) {
            return [
                'ok' => false,
                'keys' => [],
                'error' => 'database source key が不正です: ' . $rawValue,
            ];
        }
        if (app_database_source_is_builtin_key($sourceKey)) {
            return [
                'ok' => false,
                'keys' => [],
                'error' => 'built-in database source key は bundle export 対象にできません: ' . $sourceKey,
            ];
        }

        $keys[$sourceKey] = true;
    }

    return [
        'ok' => true,
        'keys' => array_keys($keys),
        'error' => '',
    ];
}

/**
 * Some repositories still read from `db`, so make them explicitly point at the
 * canonical config DB for bundle export/import.
 */
function app_project_metadata_bundle_repository_app(array $app): array
{
    $canonicalApp = $app;
    $canonicalApp['db'] = app_database_config($app, 'config_db');

    return $canonicalApp;
}

function app_project_metadata_bundle_path_is_absolute(string $path): bool
{
    if ($path === '') {
        return false;
    }

    if ($path[0] === '/') {
        return true;
    }

    return preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
}

function app_project_metadata_bundle_resolve_path(string $path, string $baseDirectory = ''): string
{
    $trimmed = trim($path);
    if ($trimmed === '') {
        return '';
    }

    $normalized = str_replace('\\', '/', $trimmed);
    if (app_project_metadata_bundle_path_is_absolute($normalized)) {
        return rtrim($normalized, '/');
    }

    $base = $baseDirectory !== '' ? $baseDirectory : getcwd();
    if (!is_string($base) || trim($base) === '') {
        $base = '.';
    }

    return rtrim(str_replace('\\', '/', $base), '/') . '/' . ltrim($normalized, '/');
}

function app_project_metadata_bundle_default_output_dir(array $app, string $projectKey): string
{
    $workRoot = rtrim(str_replace('\\', '/', (string) ($app['work']['root'] ?? 'work')), '/');
    $timestamp = gmdate('Ymd-His');
    $suffix = substr(bin2hex(random_bytes(4)), 0, 8);

    return $workRoot . '/project-metadata-bundles/' . $projectKey . '/' . $timestamp . '-' . $suffix;
}

function app_project_metadata_bundle_database_source_secrets_template_filename(): string
{
    return 'database-source-secrets.template.json';
}

/**
 * @param list<array<string,mixed>> $databaseSources
 * @return array<string,mixed>
 */
function app_project_metadata_bundle_build_database_source_secrets_template(
    string $projectKey,
    array $databaseSources,
): array {
    $passwords = [];
    foreach ($databaseSources as $item) {
        if (!is_array($item) || !($item['has_password'] ?? false)) {
            continue;
        }

        $sourceKey = app_normalize_database_source_key((string) ($item['source_key'] ?? ''));
        if ($sourceKey === '') {
            continue;
        }

        $passwords[$sourceKey] = '';
    }
    ksort($passwords);

    return [
        'bundle_type' => 'mtool-project-metadata-database-source-secrets-template',
        'schema_version' => '2026-05-26.database-source-secrets-template.v1',
        'source_project_key' => $projectKey,
        'generated_at' => gmdate(DATE_ATOM),
        'instructions' => [
            'Fill database_source_passwords locally or replace entries with {"password_env":"ENV_NAME"}.',
            'Do not commit populated secrets files.',
        ],
        'database_source_passwords' => $passwords,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     bundle_root:string,
 *     manifest:array<string,mixed>,
 *     sections:array<string,mixed>,
 *     summary:array<string,int|string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_export(array $app, string $projectKey, array $options = []): array
{
    $scope = trim((string) ($options['scope'] ?? app_project_metadata_bundle_default_scope()));
    if (!app_project_metadata_bundle_scope_is_supported($scope)) {
        return [
            'ok' => false,
            'bundle_root' => '',
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => '未対応の bundle scope です: ' . $scope,
        ];
    }

    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'bundle_root' => '',
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => '有効な project key が必要です。',
        ];
    }

    $canonicalApp = app_project_metadata_bundle_repository_app($app);
    $snapshot = app_project_metadata_bundle_collect_core_snapshot(
        $canonicalApp,
        $normalizedProjectKey,
        $scope,
        $options,
    );
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'bundle_root' => '',
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => $snapshot['error'],
        ];
    }

    $requestedBy = app_project_metadata_bundle_normalize_requested_by((string) ($options['requested_by'] ?? 'cli'));
    $outputDirectory = trim((string) ($options['output_dir'] ?? ''));
    if ($outputDirectory === '') {
        $outputDirectory = app_project_metadata_bundle_default_output_dir($canonicalApp, $normalizedProjectKey);
    }
    $bundleRoot = app_project_metadata_bundle_resolve_path($outputDirectory);

    if ($bundleRoot === '') {
        return [
            'ok' => false,
            'bundle_root' => '',
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => 'bundle 出力先を解決できません。',
        ];
    }

    if (file_exists($bundleRoot)) {
        return [
            'ok' => false,
            'bundle_root' => $bundleRoot,
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => 'bundle 出力先が既に存在します: ' . $bundleRoot,
        ];
    }

    $parentDirectory = dirname($bundleRoot);
    if (!is_dir($parentDirectory) && !mkdir($parentDirectory, 0777, true) && !is_dir($parentDirectory)) {
        return [
            'ok' => false,
            'bundle_root' => $bundleRoot,
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => 'bundle parent directory を作成できません: ' . $parentDirectory,
        ];
    }
    if (!mkdir($bundleRoot, 0777, true) && !is_dir($bundleRoot)) {
        return [
            'ok' => false,
            'bundle_root' => $bundleRoot,
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => 'bundle directory を作成できません: ' . $bundleRoot,
        ];
    }

    try {
        $sectionFilenameMap = app_project_metadata_bundle_section_filename_map();
        $writtenFiles = [];
        $writtenSupplementalFiles = [];

        foreach ($snapshot['sections'] as $sectionName => $payload) {
            $filename = $sectionFilenameMap[$sectionName] ?? '';
            if ($filename === '') {
                throw new RuntimeException('bundle file mapping が不足しています: ' . $sectionName);
            }

            $json = app_project_metadata_bundle_json_encode($payload);
            $path = $bundleRoot . '/' . $filename;
            app_project_metadata_bundle_write_text($path, $json);

            $writtenFiles[$sectionName] = [
                'path' => $filename,
                'sha256' => hash('sha256', $json),
                'bytes' => strlen($json),
            ];
        }

        $databaseSourceItems = is_array($snapshot['sections']['database_sources']['database_sources'] ?? null)
            ? $snapshot['sections']['database_sources']['database_sources']
            : [];
        if ($databaseSourceItems !== []) {
            $filename = app_project_metadata_bundle_database_source_secrets_template_filename();
            $json = app_project_metadata_bundle_json_encode(
                app_project_metadata_bundle_build_database_source_secrets_template(
                    $normalizedProjectKey,
                    $databaseSourceItems,
                ),
            );
            app_project_metadata_bundle_write_text($bundleRoot . '/' . $filename, $json);

            $writtenSupplementalFiles['database_source_secrets_template'] = [
                'path' => $filename,
                'sha256' => hash('sha256', $json),
                'bytes' => strlen($json),
            ];
        }

        $manifest = [
            'bundle_type' => app_project_metadata_bundle_type(),
            'schema_version' => app_project_metadata_bundle_schema_version(),
            'scope' => $scope,
            'source_project_key' => $normalizedProjectKey,
            'exported_at' => gmdate(DATE_ATOM),
            'requested_by' => $requestedBy,
            'target_policy' => 'replace-core-scope',
            'secrets_policy' => app_project_metadata_bundle_secrets_policy($scope),
            'included_sections' => array_values(array_keys($snapshot['sections'])),
            'excluded_sections' => app_project_metadata_bundle_excluded_sections($scope),
            'summary' => $snapshot['summary'],
            'files' => $writtenFiles,
        ];
        if ($writtenSupplementalFiles !== []) {
            $manifest['supplemental_files'] = $writtenSupplementalFiles;
        }
        app_project_metadata_bundle_write_text(
            $bundleRoot . '/manifest.json',
            app_project_metadata_bundle_json_encode($manifest),
        );

        return [
            'ok' => true,
            'bundle_root' => $bundleRoot,
            'manifest' => $manifest,
            'sections' => $snapshot['sections'],
            'summary' => $snapshot['summary'],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_project_metadata_bundle_delete_tree($bundleRoot);

        return [
            'ok' => false,
            'bundle_root' => $bundleRoot,
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     bundle_root:string,
 *     manifest:array<string,mixed>,
 *     sections:array<string,mixed>,
 *     database_source_secrets:array<string,string>,
 *     database_source_missing_secret_keys:list<string>,
 *     summary:array<string,int|string>,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_import_preview(array $app, string $bundlePath, array $options = []): array
{
    $prepared = app_project_metadata_bundle_import_prepare($app, $bundlePath, $options);
    if (!$prepared['ok']) {
        return [
            'ok' => false,
            'bundle_root' => $prepared['bundle_root'],
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'warnings' => $prepared['warnings'],
            'error' => $prepared['error'],
        ];
    }

    return [
        'ok' => true,
        'bundle_root' => $prepared['bundle_root'],
        'manifest' => $prepared['manifest'],
        'sections' => $prepared['sections'],
        'summary' => $prepared['summary'],
        'warnings' => $prepared['warnings'],
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     bundle_root:string,
 *     manifest:array<string,mixed>,
 *     sections:array<string,mixed>,
 *     summary:array<string,int|string>,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_import_apply(array $app, string $bundlePath, array $options = []): array
{
    $prepared = app_project_metadata_bundle_import_prepare($app, $bundlePath, $options);
    if (!$prepared['ok']) {
        return [
            'ok' => false,
            'bundle_root' => $prepared['bundle_root'],
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'warnings' => $prepared['warnings'],
            'error' => $prepared['error'],
        ];
    }

    $canonicalApp = app_project_metadata_bundle_repository_app($app);
    $pdo = null;

    try {
        if ($prepared['database_source_missing_secret_keys'] !== []) {
            throw new RuntimeException(
                'database_sources の新規 row に必要な password secret が不足しています: '
                . implode(', ', $prepared['database_source_missing_secret_keys']),
            );
        }

        $pdo = app_create_config_pdo($canonicalApp);
        $pdo->beginTransaction();

        app_project_metadata_bundle_apply_core_sections(
            $pdo,
            $prepared['sections'],
            (string) $prepared['summary']['target_project_key'],
            $prepared['database_source_secrets'],
        );

        $pdo->commit();

        return [
            'ok' => true,
            'bundle_root' => $prepared['bundle_root'],
            'manifest' => $prepared['manifest'],
            'sections' => $prepared['sections'],
            'summary' => $prepared['summary'],
            'warnings' => $prepared['warnings'],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'bundle_root' => $prepared['bundle_root'],
            'manifest' => $prepared['manifest'],
            'sections' => $prepared['sections'],
            'summary' => $prepared['summary'],
            'warnings' => $prepared['warnings'],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     bundle_root:string,
 *     manifest:array<string,mixed>,
 *     sections:array<string,mixed>,
 *     summary:array<string,int|string>,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_import_prepare(array $app, string $bundlePath, array $options = []): array
{
    $canonicalApp = app_project_metadata_bundle_repository_app($app);
    $loaded = app_project_metadata_bundle_load($bundlePath);
    if (!$loaded['ok']) {
        return [
            'ok' => false,
            'bundle_root' => $loaded['bundle_root'],
            'manifest' => [],
            'sections' => [],
            'database_source_secrets' => [],
            'database_source_missing_secret_keys' => [],
            'summary' => [],
            'warnings' => [],
            'error' => $loaded['error'],
        ];
    }

    $scope = (string) ($loaded['manifest']['scope'] ?? '');
    $sourceProjectKey = trim((string) ($loaded['manifest']['source_project_key'] ?? ''));
    $requestedTargetProjectKey = trim((string) ($options['target_project_key'] ?? ''));
    $targetProjectKey = $requestedTargetProjectKey !== ''
        ? $requestedTargetProjectKey
        : $sourceProjectKey;
    $targetProjectKey = app_normalize_project_key($targetProjectKey);
    if ($targetProjectKey === '' || !app_project_key_is_valid($targetProjectKey)) {
        return [
            'ok' => false,
            'bundle_root' => $loaded['bundle_root'],
            'manifest' => $loaded['manifest'],
            'sections' => $loaded['sections'],
            'database_source_secrets' => [],
            'database_source_missing_secret_keys' => [],
            'summary' => [],
            'warnings' => [],
            'error' => '有効な target project key が必要です。',
        ];
    }

    $validation = app_project_metadata_bundle_validate_sections($canonicalApp, $loaded['manifest'], $loaded['sections']);
    if ($validation['errors'] !== []) {
        return [
            'ok' => false,
            'bundle_root' => $loaded['bundle_root'],
            'manifest' => $loaded['manifest'],
            'sections' => $loaded['sections'],
            'database_source_secrets' => [],
            'database_source_missing_secret_keys' => [],
            'summary' => [],
            'warnings' => $validation['warnings'],
            'error' => implode("\n", $validation['errors']),
        ];
    }

    $databaseSourceSecretsResult = app_project_metadata_bundle_load_database_source_secrets(
        (string) ($options['database_source_secrets_path'] ?? ''),
    );
    if (!$databaseSourceSecretsResult['ok']) {
        return [
            'ok' => false,
            'bundle_root' => $loaded['bundle_root'],
            'manifest' => $loaded['manifest'],
            'sections' => $loaded['sections'],
            'database_source_secrets' => [],
            'database_source_missing_secret_keys' => [],
            'summary' => [],
            'warnings' => $validation['warnings'],
            'error' => $databaseSourceSecretsResult['error'],
        ];
    }

    $targetSummary = app_project_metadata_bundle_target_summary($canonicalApp, $targetProjectKey, $scope);
    $databaseSourceItems = is_array($loaded['sections']['database_sources']['database_sources'] ?? null)
        ? $loaded['sections']['database_sources']['database_sources']
        : [];
    $databaseSourcePlan = [
        'summary' => [
            'database_source_existing_count' => 0,
            'database_source_create_count' => 0,
            'database_source_missing_secret_count' => 0,
            'database_source_preserve_password_count' => 0,
            'database_source_secret_supplied_count' => 0,
            'database_source_with_password_count' => 0,
        ],
        'warnings' => [],
        'missing_secret_keys' => [],
    ];
    if ($databaseSourceItems !== []) {
        $databaseSourceCatalogResult = app_fetch_database_sources($canonicalApp);
        if (!$databaseSourceCatalogResult['ok']) {
            return [
                'ok' => false,
                'bundle_root' => $loaded['bundle_root'],
                'manifest' => $loaded['manifest'],
                'sections' => $loaded['sections'],
                'database_source_secrets' => [],
                'database_source_missing_secret_keys' => [],
                'summary' => [],
                'warnings' => $validation['warnings'],
                'error' => $databaseSourceCatalogResult['error'],
            ];
        }

        $databaseSourcePlan = app_project_metadata_bundle_database_source_import_plan(
            $databaseSourceItems,
            app_project_metadata_bundle_database_source_catalog_by_key($databaseSourceCatalogResult['items']),
            $databaseSourceSecretsResult['secrets'],
        );
    } elseif ($databaseSourceSecretsResult['secrets'] !== []) {
        $databaseSourcePlan['warnings'][] = 'bundle に database_sources section がないため database source secret を無視します。';
    }
    $warnings = $validation['warnings'];
    $warnings = array_merge($warnings, $databaseSourceSecretsResult['warnings']);
    if ($targetProjectKey !== $sourceProjectKey) {
        $warnings[] = 'target project key を override します: '
            . $sourceProjectKey . ' -> ' . $targetProjectKey;
    }
    if ($targetSummary['excluded_row_total'] > 0) {
        $warnings[] = 'target project には bundle scope 外 row があり、そのまま preserve されます。';
    }

    $summary = array_merge(
        $loaded['summary'],
        [
            'bundle_scope' => $scope,
            'source_project_key' => $sourceProjectKey,
            'target_project_key' => $targetProjectKey,
            'target_action' => $targetSummary['exists'] ? 'replace-core' : 'create',
            'target_exists' => $targetSummary['exists'] ? '1' : '0',
            'excluded_row_total' => $targetSummary['excluded_row_total'],
        ],
        $databaseSourceSecretsResult['summary'],
        $databaseSourcePlan['summary'],
    );
    foreach ($targetSummary['excluded_counts'] as $key => $count) {
        $summary['excluded_' . $key] = $count;
    }

    return [
        'ok' => true,
        'bundle_root' => $loaded['bundle_root'],
        'manifest' => $loaded['manifest'],
        'sections' => $loaded['sections'],
        'database_source_secrets' => $databaseSourceSecretsResult['secrets'],
        'database_source_missing_secret_keys' => $databaseSourcePlan['missing_secret_keys'],
        'summary' => $summary,
        'warnings' => array_values(array_unique(array_merge($warnings, $databaseSourcePlan['warnings']))),
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     bundle_root:string,
 *     manifest:array<string,mixed>,
 *     sections:array<string,mixed>,
 *     summary:array<string,int|string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_load(string $bundlePath): array
{
    $resolvedPath = app_project_metadata_bundle_resolve_path($bundlePath);
    if ($resolvedPath === '') {
        return [
            'ok' => false,
            'bundle_root' => '',
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => 'bundle path が空です。',
        ];
    }

    $bundleRoot = $resolvedPath;
    if (is_file($resolvedPath)) {
        if (basename($resolvedPath) !== 'manifest.json') {
            return [
                'ok' => false,
                'bundle_root' => dirname($resolvedPath),
                'manifest' => [],
                'sections' => [],
                'summary' => [],
                'error' => 'bundle path は directory か manifest.json を指定してください。',
            ];
        }

        $bundleRoot = dirname($resolvedPath);
    }

    $manifestPath = $bundleRoot . '/manifest.json';
    if (!is_file($manifestPath)) {
        return [
            'ok' => false,
            'bundle_root' => $bundleRoot,
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => 'manifest.json が見つかりません。',
        ];
    }

    try {
        $manifest = app_project_metadata_bundle_decode_json_file($manifestPath);
        if (($manifest['bundle_type'] ?? '') !== app_project_metadata_bundle_type()) {
            throw new RuntimeException('bundle_type が未対応です。');
        }
        if (($manifest['schema_version'] ?? '') !== app_project_metadata_bundle_schema_version()) {
            throw new RuntimeException('schema_version が未対応です。');
        }

        $files = is_array($manifest['files'] ?? null) ? $manifest['files'] : [];
        $sections = [];
        $sectionFilenameMap = app_project_metadata_bundle_section_filename_map();
        $includedSections = is_array($manifest['included_sections'] ?? null)
            ? array_values(array_filter($manifest['included_sections'], 'is_string'))
            : app_project_metadata_bundle_included_sections((string) ($manifest['scope'] ?? ''));

        foreach ($includedSections as $sectionName) {
            $fileInfo = $files[$sectionName] ?? null;
            if (!is_array($fileInfo)) {
                throw new RuntimeException('manifest files に ' . $sectionName . ' がありません。');
            }

            $relativePath = trim((string) ($fileInfo['path'] ?? ($sectionFilenameMap[$sectionName] ?? '')));
            if ($relativePath === '') {
                throw new RuntimeException('bundle file path が空です: ' . $sectionName);
            }

            $sectionPath = $bundleRoot . '/' . $relativePath;
            $contents = file_get_contents($sectionPath);
            if (!is_string($contents)) {
                throw new RuntimeException('bundle file を読み込めません: ' . $relativePath);
            }

            $expectedHash = trim((string) ($fileInfo['sha256'] ?? ''));
            if ($expectedHash !== '' && !hash_equals($expectedHash, hash('sha256', $contents))) {
                throw new RuntimeException('bundle checksum が一致しません: ' . $relativePath);
            }

            $decoded = json_decode($contents, true);
            if (!is_array($decoded)) {
                throw new RuntimeException('bundle JSON を解釈できません: ' . $relativePath);
            }

            $sections[$sectionName] = $decoded;
        }

        $summary = is_array($manifest['summary'] ?? null) ? $manifest['summary'] : [];

        return [
            'ok' => true,
            'bundle_root' => $bundleRoot,
            'manifest' => $manifest,
            'sections' => $sections,
            'summary' => $summary,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'bundle_root' => $bundleRoot,
            'manifest' => [],
            'sections' => [],
            'summary' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $item
 * @return list<string>
 */
function app_project_metadata_bundle_validate_database_source_item(array $item): array
{
    $errors = [];
    $sourceKey = app_normalize_database_source_key((string) ($item['source_key'] ?? ''));
    if ($sourceKey === '') {
        $errors[] = 'database_sources.source_key が空です。';
    } elseif (!app_database_source_key_is_valid($sourceKey)) {
        $errors[] = 'database_sources.source_key が不正です: ' . $sourceKey;
    } elseif (app_database_source_is_builtin_key($sourceKey)) {
        $errors[] = 'database_sources.source_key に built-in key は使えません: ' . $sourceKey;
    }

    foreach (['label', 'host', 'port', 'database_name', 'user_name'] as $fieldName) {
        if (trim((string) ($item[$fieldName] ?? '')) === '') {
            $errors[] = 'database_sources.' . $fieldName . ' が空です: ' . $sourceKey;
        }
    }

    $port = trim((string) ($item['port'] ?? ''));
    if ($port !== '' && preg_match('/^[0-9]{1,5}$/', $port) !== 1) {
        $errors[] = 'database_sources.port は数値文字列で指定してください: ' . $sourceKey;
    } elseif ($port !== '' && ((int) $port < 1 || (int) $port > 65535)) {
        $errors[] = 'database_sources.port は 1 から 65535 の範囲で指定してください: ' . $sourceKey;
    }

    if (!array_key_exists('has_password', $item)) {
        $errors[] = 'database_sources.has_password がありません: ' . $sourceKey;
    }

    if (array_key_exists('password', $item)) {
        $errors[] = 'database_sources.password は bundle に含められません: ' . $sourceKey;
    }

    $supportsLiveSchemaImport = (bool) ($item['supports_live_schema_import'] ?? false);
    $supportsProxyRuntimeRead = (bool) ($item['supports_proxy_runtime_read'] ?? false);
    if (!$supportsLiveSchemaImport && !$supportsProxyRuntimeRead) {
        $errors[] = 'database_sources は live schema import または proxy runtime read のどちらかを有効にしてください: '
            . $sourceKey;
    }

    return $errors;
}

/**
 * @return array{
 *     errors:list<string>,
 *     warnings:list<string>
 * }
 */
function app_project_metadata_bundle_validate_sections(
    array $app,
    array $manifest,
    array $sections,
): array {
    $errors = [];
    $warnings = [];
    $scope = trim((string) ($manifest['scope'] ?? ''));

    if (!app_project_metadata_bundle_scope_is_supported($scope)) {
        $errors[] = '未対応の bundle scope です: ' . $scope;
        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    $projectSection = $sections['project'] ?? null;
    if (!is_array($projectSection)) {
        $errors[] = 'project section がありません。';
        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    $projectKey = app_normalize_project_key((string) ($projectSection['project_key'] ?? ''));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        $errors[] = 'project.project_key が不正です。';
    }
    if ($projectKey !== trim((string) ($manifest['source_project_key'] ?? ''))) {
        $errors[] = 'manifest.source_project_key と project.project_key が一致しません。';
    }

    if (array_key_exists('database_sources', $sections)) {
        $databaseSourceItems = $sections['database_sources']['database_sources'] ?? null;
        if (!is_array($databaseSourceItems)) {
            $errors[] = 'database_sources section が不正です。';
            $databaseSourceItems = [];
        }
        $databaseSourceKeys = [];
        foreach ($databaseSourceItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            foreach (app_project_metadata_bundle_validate_database_source_item($item) as $databaseSourceError) {
                $errors[] = $databaseSourceError;
            }

            $databaseSourceKey = app_normalize_database_source_key((string) ($item['source_key'] ?? ''));
            if ($databaseSourceKey === '' || !app_database_source_key_is_valid($databaseSourceKey)) {
                continue;
            }
            if (isset($databaseSourceKeys[$databaseSourceKey])) {
                $errors[] = 'database_sources.source_key が重複しています: ' . $databaseSourceKey;
                continue;
            }

            $databaseSourceKeys[$databaseSourceKey] = true;
        }
    }

    $sourceOutputs = $sections['source_outputs']['source_outputs'] ?? null;
    if (!is_array($sourceOutputs)) {
        $errors[] = 'source_outputs section が不正です。';
        $sourceOutputs = [];
    }
    $sourceOutputKeys = [];
    foreach ($sourceOutputs as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceOutputKey = app_normalize_source_output_key((string) ($item['source_output_key'] ?? ''));
        if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
            $errors[] = 'source_outputs.source_output_key が不正です。';
            continue;
        }
        if (isset($sourceOutputKeys[$sourceOutputKey])) {
            $errors[] = 'source_outputs.source_output_key が重複しています: ' . $sourceOutputKey;
            continue;
        }

        $sourceOutputKeys[$sourceOutputKey] = true;
    }

    $dbAccessClasses = $sections['db_access']['classes'] ?? null;
    if (!is_array($dbAccessClasses)) {
        $errors[] = 'db_access section が不正です。';
        $dbAccessClasses = [];
    }

    $classNames = [];
    foreach ($dbAccessClasses as $classItem) {
        if (!is_array($classItem)) {
            continue;
        }

        $sourceName = trim((string) ($classItem['source_name'] ?? ''));
        if ($sourceName === '') {
            $errors[] = 'db_access.class.source_name が空です。';
            continue;
        }
        $classKey = strtolower($sourceName);
        if (isset($classNames[$classKey])) {
            $errors[] = 'db_access.class.source_name が重複しています: ' . $sourceName;
            continue;
        }
        $classNames[$classKey] = true;

        $functions = $classItem['functions'] ?? null;
        if (!is_array($functions)) {
            $errors[] = 'db_access.class.functions が不正です: ' . $sourceName;
            continue;
        }

        $functionNames = [];
        foreach ($functions as $functionItem) {
            if (!is_array($functionItem)) {
                continue;
            }

            $functionName = trim((string) ($functionItem['function_name'] ?? ''));
            if ($functionName === '') {
                $errors[] = 'db_access.function_name が空です: ' . $sourceName;
                continue;
            }

            $functionKey = strtolower($functionName);
            if (isset($functionNames[$functionKey])) {
                $errors[] = 'db_access.function_name が重複しています: ' . $sourceName . '.' . $functionName;
                continue;
            }
            $functionNames[$functionKey] = true;

            $blobConstraintError = app_pdo_validate_db_access_function_blob_target_constraint(
                $app,
                [
                    'source_name' => $sourceName,
                    'function_name' => $functionName,
                    'action_type' => (string) ($functionItem['action_type'] ?? ''),
                    'is_blob_target' => (string) ($functionItem['is_blob_target'] ?? '0'),
                    'last_detected_dbaccess_file' => (string) ($classItem['last_detected_dbaccess_file'] ?? ''),
                ],
            );
            if ($blobConstraintError !== '') {
                $errors[] = 'db_access ' . $sourceName . '.' . $functionName . ': ' . $blobConstraintError;
            }

            $authPolicyVersion = (int) ((string) ($functionItem['auth_policy_version'] ?? '1'));
            $authPolicyJson = (string) ($functionItem['auth_policy_json'] ?? '');
            if ($authPolicyVersion > 1 || trim($authPolicyJson) !== '') {
                $authPolicy = app_generated_runtime_auth_policy_validate_json($authPolicyVersion, $authPolicyJson);
                if (!$authPolicy['is_valid']) {
                    $errors[] = 'db_access auth_policy_json が不正です: '
                        . $sourceName . '.' . $functionName . ' -> '
                        . implode(' / ', $authPolicy['notes']);
                }
            }

            $targetKeys = is_array($functionItem['source_output_keys'] ?? null)
                ? $functionItem['source_output_keys']
                : [];
            foreach ($targetKeys as $targetKey) {
                if (!is_string($targetKey) || !isset($sourceOutputKeys[$targetKey])) {
                    $errors[] = 'db_access target source_output_key が bundle に存在しません: '
                        . $sourceName . '.' . $functionName . ' -> ' . (string) $targetKey;
                }
            }

            $selectTargetFields = is_array($functionItem['select_target_fields'] ?? null)
                ? $functionItem['select_target_fields']
                : [];
            $targetFieldKeys = [];
            foreach ($selectTargetFields as $targetFieldItem) {
                if (!is_array($targetFieldItem)) {
                    continue;
                }

                $fieldKey = trim((string) ($targetFieldItem['select_target_field_key'] ?? ''));
                if ($fieldKey === '') {
                    $errors[] = 'select_target_field_key が空です: ' . $sourceName . '.' . $functionName;
                    continue;
                }
                if (isset($targetFieldKeys[$fieldKey])) {
                    $errors[] = 'select_target_field_key が重複しています: ' . $sourceName . '.'
                        . $functionName . ' -> ' . $fieldKey;
                    continue;
                }
                $targetFieldKeys[$fieldKey] = true;

                $fileConstraintError = app_pdo_validate_db_access_function_file_parameter_constraint(
                    $app,
                    $sourceName,
                    $functionName,
                    (string) ($targetFieldItem['parameter_data_type'] ?? ''),
                    (string) ($functionItem['is_blob_target'] ?? '0'),
                    (string) ($classItem['last_detected_dbaccess_file'] ?? ''),
                );
                if ($fileConstraintError !== '') {
                    $errors[] = 'select_target_field ' . $sourceName . '.' . $functionName . ': ' . $fileConstraintError;
                }
            }

            $simpleFieldSections = [
                'select_wheres',
                'update_delete_wheres',
                'insert_target_fields',
                'update_target_fields',
            ];
            foreach ($simpleFieldSections as $sectionName) {
                $rows = is_array($functionItem[$sectionName] ?? null) ? $functionItem[$sectionName] : [];
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $fileConstraintError = app_pdo_validate_db_access_function_file_parameter_constraint(
                        $app,
                        $sourceName,
                        $functionName,
                        (string) ($row['parameter_data_type'] ?? ''),
                        (string) ($functionItem['is_blob_target'] ?? '0'),
                        (string) ($classItem['last_detected_dbaccess_file'] ?? ''),
                    );
                    if ($fileConstraintError !== '') {
                        $errors[] = $sectionName . ' ' . $sourceName . '.' . $functionName . ': ' . $fileConstraintError;
                    }
                }
            }

            $selectHavings = is_array($functionItem['select_havings'] ?? null)
                ? $functionItem['select_havings']
                : [];
            foreach ($selectHavings as $havingItem) {
                if (!is_array($havingItem)) {
                    continue;
                }

                $leftKey = trim((string) ($havingItem['left_target_field_key'] ?? ''));
                $rightKey = trim((string) ($havingItem['right_target_field_key'] ?? ''));
                if ($leftKey !== '' && !isset($targetFieldKeys[$leftKey])) {
                    $errors[] = 'select_having.left_target_field_key が見つかりません: '
                        . $sourceName . '.' . $functionName . ' -> ' . $leftKey;
                }
                if ($rightKey !== '' && !isset($targetFieldKeys[$rightKey])) {
                    $errors[] = 'select_having.right_target_field_key が見つかりません: '
                        . $sourceName . '.' . $functionName . ' -> ' . $rightKey;
                }
            }
        }
    }

    if (($manifest['secrets_policy'] ?? '') !== app_project_metadata_bundle_secrets_policy($scope)) {
        $warnings[] = 'bundle secrets policy が想定と異なります。';
    }

    return [
        'errors' => array_values(array_unique($errors)),
        'warnings' => array_values(array_unique($warnings)),
    ];
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,array<string,mixed>>
 */
function app_project_metadata_bundle_database_source_catalog_by_key(array $items): array
{
    $catalog = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceKey = app_normalize_database_source_key((string) ($item['source_key'] ?? ''));
        if ($sourceKey === '') {
            continue;
        }

        $catalog[$sourceKey] = $item;
    }

    return $catalog;
}

function app_project_metadata_bundle_secret_env_name_is_valid(string $value): bool
{
    return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) === 1;
}

/**
 * @param mixed $value
 * @return array{
 *     password:string,
 *     source:string,
 *     warning:string,
 *     error:string
 * }
 */
function app_project_metadata_bundle_resolve_database_source_secret_value(string $sourceKey, $value): array
{
    if (is_array($value)) {
        if (array_key_exists('password', $value)) {
            return [
                'password' => (string) $value['password'],
                'source' => 'literal',
                'warning' => '',
                'error' => '',
            ];
        }

        $envName = trim((string) ($value['password_env'] ?? $value['env'] ?? $value['env_name'] ?? ''));
        if ($envName !== '') {
            if (!app_project_metadata_bundle_secret_env_name_is_valid($envName)) {
                return [
                    'password' => '',
                    'source' => 'env',
                    'warning' => '',
                    'error' => 'database source secrets file の env 名が不正です: '
                        . $sourceKey . ' -> ' . $envName,
                ];
            }

            $envValue = getenv($envName);
            if ($envValue === false) {
                return [
                    'password' => '',
                    'source' => 'env',
                    'warning' => 'database source secret env が未設定です: '
                        . $sourceKey . ' -> ' . $envName,
                    'error' => '',
                ];
            }

            return [
                'password' => (string) $envValue,
                'source' => 'env',
                'warning' => '',
                'error' => '',
            ];
        }

        return [
            'password' => '',
            'source' => 'empty',
            'warning' => '',
            'error' => '',
        ];
    }

    if (is_string($value) || is_numeric($value)) {
        return [
            'password' => (string) $value,
            'source' => 'literal',
            'warning' => '',
            'error' => '',
        ];
    }

    return [
        'password' => '',
        'source' => 'empty',
        'warning' => '',
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     file_path:string,
 *     secrets:array<string,string>,
 *     summary:array<string,int|string>,
 *     warnings:list<string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_load_database_source_secrets(string $path): array
{
    $trimmedPath = trim($path);
    if ($trimmedPath === '') {
        return [
            'ok' => true,
            'file_path' => '',
            'secrets' => [],
            'summary' => [
                'database_source_secrets_file_provided' => '0',
                'database_source_secret_literal_count' => 0,
                'database_source_secret_env_ref_count' => 0,
                'database_source_secret_missing_env_count' => 0,
            ],
            'warnings' => [],
            'error' => '',
        ];
    }

    $resolvedPath = app_project_metadata_bundle_resolve_path($trimmedPath);
    if ($resolvedPath === '' || !is_file($resolvedPath)) {
        return [
            'ok' => false,
            'file_path' => $resolvedPath,
            'secrets' => [],
            'summary' => [],
            'warnings' => [],
            'error' => 'database source secrets file が見つかりません: ' . $trimmedPath,
        ];
    }

    try {
        $decoded = app_project_metadata_bundle_decode_json_file($resolvedPath);
        $payload = is_array($decoded['database_source_passwords'] ?? null)
            ? $decoded['database_source_passwords']
            : $decoded;

        $secrets = [];
        $literalCount = 0;
        $envRefCount = 0;
        $missingEnvCount = 0;
        $warnings = [];
        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $sourceKey = app_normalize_database_source_key($key);
            if ($sourceKey === '' || !app_database_source_key_is_valid($sourceKey)) {
                throw new RuntimeException('database source secrets file の key が不正です: ' . $key);
            }

            $resolvedSecret = app_project_metadata_bundle_resolve_database_source_secret_value($sourceKey, $value);
            if ($resolvedSecret['error'] !== '') {
                throw new RuntimeException($resolvedSecret['error']);
            }

            if ($resolvedSecret['source'] === 'literal' && $resolvedSecret['password'] !== '') {
                $literalCount++;
            }
            if ($resolvedSecret['source'] === 'env') {
                $envRefCount++;
                if ($resolvedSecret['warning'] !== '') {
                    $missingEnvCount++;
                    $warnings[] = $resolvedSecret['warning'];
                }
            }

            $secrets[$sourceKey] = $resolvedSecret['password'];
        }

        return [
            'ok' => true,
            'file_path' => $resolvedPath,
            'secrets' => $secrets,
            'summary' => [
                'database_source_secrets_file_provided' => '1',
                'database_source_secret_literal_count' => $literalCount,
                'database_source_secret_env_ref_count' => $envRefCount,
                'database_source_secret_missing_env_count' => $missingEnvCount,
            ],
            'warnings' => array_values(array_unique($warnings)),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'file_path' => $resolvedPath,
            'secrets' => [],
            'summary' => [],
            'warnings' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param list<array<string,mixed>> $databaseSources
 * @param array<string,array<string,mixed>> $existingCatalog
 * @param array<string,string> $databaseSourceSecrets
 * @return array{
 *     summary:array<string,int>,
 *     warnings:list<string>,
 *     missing_secret_keys:list<string>
 * }
 */
function app_project_metadata_bundle_database_source_import_plan(
    array $databaseSources,
    array $existingCatalog,
    array $databaseSourceSecrets,
): array {
    $existingCount = 0;
    $createCount = 0;
    $withPasswordCount = 0;
    $secretSuppliedCount = 0;
    $preservePasswordCount = 0;
    $missingSecretKeys = [];
    $bundleKeys = [];

    foreach ($databaseSources as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceKey = app_normalize_database_source_key((string) ($item['source_key'] ?? ''));
        if ($sourceKey === '') {
            continue;
        }

        $bundleKeys[$sourceKey] = true;
        $hasPassword = (bool) ($item['has_password'] ?? false);
        $secretProvided = array_key_exists($sourceKey, $databaseSourceSecrets)
            && $databaseSourceSecrets[$sourceKey] !== '';

        if ($hasPassword) {
            $withPasswordCount++;
        }
        if ($secretProvided) {
            $secretSuppliedCount++;
        }

        if (isset($existingCatalog[$sourceKey])) {
            $existingCount++;
            if ($hasPassword && !$secretProvided) {
                $preservePasswordCount++;
            }
            continue;
        }

        $createCount++;
        if ($hasPassword && !$secretProvided) {
            $missingSecretKeys[] = $sourceKey;
        }
    }

    $unusedSecretKeys = array_values(
        array_diff(array_keys($databaseSourceSecrets), array_keys($bundleKeys))
    );
    sort($missingSecretKeys);
    sort($unusedSecretKeys);

    $warnings = [];
    if ($missingSecretKeys !== []) {
        $warnings[] = 'database_sources の新規 row に必要な password secret が不足しています: '
            . implode(', ', $missingSecretKeys);
    }
    if ($unusedSecretKeys !== []) {
        $warnings[] = 'bundle に存在しない database source secret を無視します: '
            . implode(', ', $unusedSecretKeys);
    }

    return [
        'summary' => [
            'database_source_existing_count' => $existingCount,
            'database_source_create_count' => $createCount,
            'database_source_missing_secret_count' => count($missingSecretKeys),
            'database_source_preserve_password_count' => $preservePasswordCount,
            'database_source_secret_supplied_count' => $secretSuppliedCount,
            'database_source_with_password_count' => $withPasswordCount,
        ],
        'warnings' => $warnings,
        'missing_secret_keys' => $missingSecretKeys,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     sections:array<string,mixed>,
 *     summary:array<string,int|string>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_collect_core_snapshot(
    array $app,
    string $projectKey,
    string $scope,
    array $options = [],
): array {
    if ($scope !== app_project_metadata_bundle_default_scope()) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => '未対応の bundle scope です: ' . $scope,
        ];
    }

    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if (!$projectResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $projectResult['error'],
        ];
    }
    if (!is_array($projectResult['item'])) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => 'project が見つかりません: ' . $projectKey,
        ];
    }

    $membershipResult = app_fetch_project_membership_summary($app, $projectKey);
    if (!$membershipResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $membershipResult['error'],
        ];
    }

    $tableResult = app_fetch_table_metadata_snapshot($app, $projectKey);
    if (!$tableResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $tableResult['error'],
        ];
    }

    $dataClassResult = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if (!$dataClassResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $dataClassResult['error'],
        ];
    }

    $sourceOutputResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$sourceOutputResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $sourceOutputResult['error'],
        ];
    }

    $dbAccessResult = app_project_metadata_bundle_collect_db_access_snapshot($app, $projectKey);
    if (!$dbAccessResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $dbAccessResult['error'],
        ];
    }

    $projectItem = $projectResult['item'];
    $sections = [
        'project' => [
            'project_key' => (string) ($projectItem['project_key'] ?? ''),
            'name' => (string) ($projectItem['name'] ?? ''),
            'slug' => (string) ($projectItem['slug'] ?? ''),
            'lifecycle_status' => (string) ($projectItem['lifecycle_status'] ?? ''),
            'owner_login_id' => (string) ($projectItem['owner_login_id'] ?? ''),
            'description' => (string) ($projectItem['description'] ?? ''),
        ],
        'memberships' => app_project_metadata_bundle_export_memberships($membershipResult['item']),
        'tables' => [
            'tables' => array_map(
                'app_project_metadata_bundle_export_table_item',
                $tableResult['items'],
            ),
        ],
        'data_classes' => [
            'data_classes' => array_map(
                'app_project_metadata_bundle_export_data_class_item',
                $dataClassResult['items'],
            ),
        ],
        'db_access' => $dbAccessResult['snapshot'],
        'source_outputs' => [
            'source_outputs' => array_map(
                'app_project_metadata_bundle_export_source_output_item',
                $sourceOutputResult['items'],
            ),
        ],
    ];

    $selectedDatabaseSourceKeysResult = app_project_metadata_bundle_parse_database_source_keys(
        $options['database_source_keys'] ?? [],
    );
    if (!$selectedDatabaseSourceKeysResult['ok']) {
        return [
            'ok' => false,
            'sections' => [],
            'summary' => [],
            'error' => $selectedDatabaseSourceKeysResult['error'],
        ];
    }

    if ($selectedDatabaseSourceKeysResult['keys'] !== []) {
        $databaseSourceResult = app_fetch_database_sources($app);
        if (!$databaseSourceResult['ok']) {
            return [
                'ok' => false,
                'sections' => [],
                'summary' => [],
                'error' => $databaseSourceResult['error'],
            ];
        }

        $databaseSourceCatalog = app_project_metadata_bundle_database_source_catalog_by_key($databaseSourceResult['items']);
        $databaseSourceItems = [];
        foreach ($selectedDatabaseSourceKeysResult['keys'] as $sourceKey) {
            if (!isset($databaseSourceCatalog[$sourceKey])) {
                return [
                    'ok' => false,
                    'sections' => [],
                    'summary' => [],
                    'error' => 'bundle export 対象の database source が見つかりません: ' . $sourceKey,
                ];
            }

            $databaseSourceItems[] = app_project_metadata_bundle_export_database_source_item($databaseSourceCatalog[$sourceKey]);
        }

        $sections['database_sources'] = [
            'database_sources' => $databaseSourceItems,
        ];
    }

    return [
        'ok' => true,
        'sections' => $sections,
        'summary' => app_project_metadata_bundle_summary_from_sections($sections),
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     snapshot:array{classes:list<array<string,mixed>>},
 *     error:string
 * }
 */
function app_project_metadata_bundle_collect_db_access_snapshot(array $app, string $projectKey): array
{
    $classCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    if (!$classCatalogResult['ok']) {
        return [
            'ok' => false,
            'snapshot' => ['classes' => []],
            'error' => $classCatalogResult['error'],
        ];
    }

    $classes = [];
    foreach ($classCatalogResult['items'] as $classSummary) {
        if (!is_array($classSummary)) {
            continue;
        }

        $sourceName = trim((string) ($classSummary['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }

        $classDetailResult = app_fetch_db_access_class_metadata($app, $projectKey, $sourceName);
        if (!$classDetailResult['ok']) {
            return [
                'ok' => false,
                'snapshot' => ['classes' => []],
                'error' => $classDetailResult['error'],
            ];
        }
        if (!is_array($classDetailResult['item'])) {
            continue;
        }

        $functionCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
        if (!$functionCatalogResult['ok']) {
            return [
                'ok' => false,
                'snapshot' => ['classes' => []],
                'error' => $functionCatalogResult['error'],
            ];
        }

        $functions = [];
        foreach ($functionCatalogResult['items'] as $functionItem) {
            if (!is_array($functionItem)) {
                continue;
            }

            $exportedFunction = app_project_metadata_bundle_export_db_access_function(
                $app,
                $projectKey,
                $sourceName,
                $classDetailResult['item'],
                $functionItem,
            );
            if (!$exportedFunction['ok']) {
                return [
                    'ok' => false,
                    'snapshot' => ['classes' => []],
                    'error' => $exportedFunction['error'],
                ];
            }

            $functions[] = $exportedFunction['item'];
        }

        $classes[] = [
            'source_name' => $sourceName,
            'store_base_path' => (string) ($classDetailResult['item']['store_base_path'] ?? ''),
            'is_autoload' => (string) ($classDetailResult['item']['is_autoload'] ?? '0'),
            'notes' => (string) ($classDetailResult['item']['notes'] ?? ''),
            'source_of_truth' => (string) ($classDetailResult['item']['source_of_truth'] ?? ''),
            'last_detected_dbaccess_file' => (string) ($classDetailResult['item']['last_detected_dbaccess_file'] ?? ''),
            'last_detected_data_file' => (string) ($classDetailResult['item']['last_detected_data_file'] ?? ''),
            'functions' => $functions,
        ];
    }

    return [
        'ok' => true,
        'snapshot' => ['classes' => $classes],
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array<string,mixed>,
 *     error:string
 * }
 */
function app_project_metadata_bundle_export_db_access_function(
    array $app,
    string $projectKey,
    string $sourceName,
    array $classItem,
    array $functionItem,
): array {
    $functionName = trim((string) ($functionItem['function_name'] ?? ''));
    if ($functionName === '') {
        return [
            'ok' => false,
            'item' => [],
            'error' => 'db access function name が空です: ' . $sourceName,
        ];
    }

    $sourceOutputTargetResult = app_fetch_db_access_function_source_output_target_keys(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$sourceOutputTargetResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $sourceOutputTargetResult['error'],
        ];
    }

    $selectWhereResult = app_fetch_db_access_function_select_where_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$selectWhereResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $selectWhereResult['error'],
        ];
    }

    $selectTargetFieldResult = app_fetch_db_access_function_select_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$selectTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $selectTargetFieldResult['error'],
        ];
    }

    $selectHavingResult = app_fetch_db_access_function_select_having_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$selectHavingResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $selectHavingResult['error'],
        ];
    }

    $updateDeleteWhereResult = app_fetch_db_access_function_update_delete_where_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$updateDeleteWhereResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $updateDeleteWhereResult['error'],
        ];
    }

    $insertTargetFieldResult = app_fetch_db_access_function_insert_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$insertTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $insertTargetFieldResult['error'],
        ];
    }

    $updateTargetFieldResult = app_fetch_db_access_function_update_target_field_catalog(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
    if (!$updateTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $updateTargetFieldResult['error'],
        ];
    }

    $targetFieldKeyById = [];
    $exportedSelectTargetFields = [];
    foreach (array_values($selectTargetFieldResult['items']) as $index => $fieldItem) {
        if (!is_array($fieldItem)) {
            continue;
        }

        $fieldKey = sprintf('field-%03d', $index + 1);
        $targetFieldKeyById[(string) ($fieldItem['select_target_field_id'] ?? '')] = $fieldKey;
        $exportedSelectTargetFields[] = [
            'select_target_field_key' => $fieldKey,
            'target_table_name' => (string) ($fieldItem['target_table_name'] ?? ''),
            'target_table_alias_name' => (string) ($fieldItem['target_table_alias_name'] ?? ''),
            'target_table_column_name' => (string) ($fieldItem['target_table_column_name'] ?? ''),
            'target_table_column_prefix' => (string) ($fieldItem['target_table_column_prefix'] ?? ''),
            'target_table_column_suffix' => (string) ($fieldItem['target_table_column_suffix'] ?? ''),
            'store_class_field_name' => (string) ($fieldItem['store_class_field_name'] ?? ''),
            'group_by_target' => (string) ($fieldItem['group_by_target'] ?? '0'),
            'field_list_order' => (string) ($fieldItem['field_list_order'] ?? '0'),
            'source_of_truth' => (string) ($fieldItem['source_of_truth'] ?? ''),
        ];
    }

    $exportedSelectHavings = [];
    foreach ($selectHavingResult['items'] as $havingItem) {
        if (!is_array($havingItem)) {
            continue;
        }

        $exportedSelectHavings[] = [
            'left_target_prefix' => (string) ($havingItem['left_target_prefix'] ?? ''),
            'left_target_field_key' => $targetFieldKeyById[(string) ($havingItem['left_target_field_id'] ?? '')] ?? '',
            'left_target_suffix' => (string) ($havingItem['left_target_suffix'] ?? ''),
            'relational_operator' => (string) ($havingItem['relational_operator'] ?? ''),
            'right_target_prefix' => (string) ($havingItem['right_target_prefix'] ?? ''),
            'right_parameter_type' => (string) ($havingItem['right_parameter_type'] ?? ''),
            'right_parameter_data_type' => (string) ($havingItem['right_parameter_data_type'] ?? ''),
            'right_fixed_parameter' => (string) ($havingItem['right_fixed_parameter'] ?? ''),
            'right_target_field_key' => $targetFieldKeyById[(string) ($havingItem['right_target_field_id'] ?? '')] ?? '',
            'right_target_suffix' => (string) ($havingItem['right_target_suffix'] ?? ''),
            'having_order' => (string) ($havingItem['having_order'] ?? '0'),
            'source_of_truth' => (string) ($havingItem['source_of_truth'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'function_name' => $functionName,
            'function_list_order' => (string) ($functionItem['function_list_order'] ?? '0'),
            'function_suffix' => (string) ($functionItem['function_suffix'] ?? ''),
            'action_type' => (string) ($functionItem['action_type'] ?? ''),
            'data_class_base_name' => (string) ($functionItem['data_class_base_name'] ?? ''),
            'target_table_name' => (string) ($functionItem['target_table_name'] ?? ''),
            'parameter_type' => (string) ($functionItem['parameter_type'] ?? ''),
            'select_by_distinct' => (string) ($functionItem['select_by_distinct'] ?? '0'),
            'sort_order_columns' => (string) ($functionItem['sort_order_columns'] ?? ''),
            'memo' => (string) ($functionItem['memo'] ?? ''),
            'limit_parameter_type' => (string) ($functionItem['limit_parameter_type'] ?? ''),
            'limit_fixed_parameter' => (string) ($functionItem['limit_fixed_parameter'] ?? ''),
            'or_group_type' => (string) ($functionItem['or_group_type'] ?? ''),
            'single_proxy_auth_type' => (string) ($functionItem['single_proxy_auth_type'] ?? ''),
            'single_proxy_single_get_function_name' => (string) ($functionItem['single_proxy_single_get_function_name'] ?? ''),
            'auth_policy_version' => (string) ((int) ($functionItem['auth_policy_version'] ?? 1)),
            'auth_policy_json' => (string) ($functionItem['auth_policy_json'] ?? ''),
            'is_blob_target' => (string) ($functionItem['is_blob_target'] ?? '0'),
            'detected_signature' => (string) ($functionItem['detected_signature'] ?? ''),
            'detected_line' => (string) ($functionItem['detected_line'] ?? '0'),
            'source_of_truth' => (string) ($functionItem['source_of_truth'] ?? ''),
            'source_output_keys' => array_values($sourceOutputTargetResult['items']),
            'select_wheres' => array_map(
                'app_project_metadata_bundle_export_select_where_item',
                $selectWhereResult['items'],
            ),
            'select_target_fields' => $exportedSelectTargetFields,
            'select_havings' => $exportedSelectHavings,
            'update_delete_wheres' => array_map(
                'app_project_metadata_bundle_export_update_delete_where_item',
                $updateDeleteWhereResult['items'],
            ),
            'insert_target_fields' => array_map(
                'app_project_metadata_bundle_export_simple_target_field_item',
                $insertTargetFieldResult['items'],
            ),
            'update_target_fields' => array_map(
                'app_project_metadata_bundle_export_simple_target_field_item',
                $updateTargetFieldResult['items'],
            ),
        ],
        'error' => '',
    ];
}

function app_project_metadata_bundle_export_memberships(?array $membershipSummary): array
{
    if ($membershipSummary === null) {
        return [
            'owner' => [
                'login_id' => '',
                'role_code' => 'owner',
                'can_administer' => true,
            ],
            'members' => [],
        ];
    }

    $members = [];
    foreach ($membershipSummary['members'] ?? [] as $member) {
        if (!is_array($member)) {
            continue;
        }

        $members[] = [
            'login_id' => (string) ($member['login_id'] ?? ''),
            'role_code' => (string) ($member['role_code'] ?? 'member'),
            'can_administer' => (bool) ($member['can_administer'] ?? false),
        ];
    }

    $owner = is_array($membershipSummary['owner'] ?? null) ? $membershipSummary['owner'] : [];

    return [
        'owner' => [
            'login_id' => (string) ($owner['login_id'] ?? ''),
            'role_code' => 'owner',
            'can_administer' => true,
        ],
        'members' => $members,
    ];
}

function app_project_metadata_bundle_export_table_item(array $item): array
{
    $columns = [];
    foreach ($item['columns'] ?? [] as $column) {
        if (!is_array($column)) {
            continue;
        }

        $columns[] = [
            'name' => (string) ($column['name'] ?? ''),
            'datatype' => (string) ($column['datatype'] ?? ''),
            'is_null' => (string) ($column['is_null'] ?? ''),
            'is_key' => (string) ($column['is_key'] ?? ''),
            'is_default' => (string) ($column['is_default'] ?? ''),
            'extra' => (string) ($column['extra'] ?? ''),
            'column_list_order' => (int) ($column['column_list_order'] ?? 0),
            'memo' => (string) ($column['memo'] ?? ''),
        ];
    }

    return [
        'name' => (string) ($item['name'] ?? ''),
        'columns' => $columns,
    ];
}

function app_project_metadata_bundle_export_database_source_item(array $item): array
{
    $password = (string) ($item['password'] ?? '');

    return [
        'source_key' => (string) ($item['source_key'] ?? ''),
        'label' => (string) ($item['label'] ?? ''),
        'description' => (string) ($item['description'] ?? ''),
        'host' => (string) ($item['host'] ?? ''),
        'port' => (string) ($item['port'] ?? ''),
        'database_name' => (string) ($item['name'] ?? ''),
        'user_name' => (string) ($item['user'] ?? ''),
        'supports_live_schema_import' => (bool) ($item['supports_live_schema_import'] ?? false),
        'supports_proxy_runtime_read' => (bool) ($item['supports_proxy_runtime_read'] ?? false),
        'proxy_runtime_priority' => (int) ($item['proxy_runtime_priority'] ?? 1000),
        'source_of_truth' => (string) ($item['source_of_truth'] ?? ''),
        'has_password' => $password !== '',
    ];
}

function app_project_metadata_bundle_export_data_class_item(array $item): array
{
    $fields = [];
    foreach ($item['fields'] ?? [] as $field) {
        if (!is_array($field)) {
            continue;
        }

        $fields[] = [
            'name' => (string) ($field['name'] ?? ''),
            'datatype' => (string) ($field['datatype'] ?? ''),
            'field_list_order' => (int) ($field['field_list_order'] ?? 0),
            'ref_data_class_name' => (string) ($field['ref_data_class_name'] ?? ''),
            'ref_data_class_field_name' => (string) ($field['ref_data_class_field_name'] ?? ''),
        ];
    }

    return [
        'name' => (string) ($item['name'] ?? ''),
        'store_base_path' => (string) ($item['store_base_path'] ?? ''),
        'is_autoload' => (string) ($item['is_autoload'] ?? '0'),
        'inherit_parent_data_class_name' => (string) ($item['inherit_parent_data_class_name'] ?? ''),
        'fields' => $fields,
    ];
}

function app_project_metadata_bundle_export_source_output_item(array $item): array
{
    return [
        'source_output_key' => (string) ($item['source_output_key'] ?? ''),
        'name' => (string) ($item['name'] ?? ''),
        'program_language' => (string) ($item['program_language'] ?? ''),
        'class_type' => (string) ($item['class_type'] ?? ''),
        'release_target_type' => (string) ($item['release_target_type'] ?? ''),
        'source_template_dir' => (string) ($item['source_template_dir'] ?? ''),
        'source_output_dir' => (string) ($item['source_output_dir'] ?? ''),
        'source_temp_output_dir' => (string) ($item['source_temp_output_dir'] ?? ''),
        'proxy_base_url' => (string) ($item['proxy_base_url'] ?? ''),
        'autoload_filename_suffix' => (string) ($item['autoload_filename_suffix'] ?? ''),
        'source_text_char_code' => (string) ($item['source_text_char_code'] ?? ''),
        'runtime_source_relative_path' => (string) ($item['runtime_source_relative_path'] ?? ''),
        'artifact_strategy' => (string) ($item['artifact_strategy'] ?? ''),
        'target_binding_type' => (string) ($item['target_binding_type'] ?? ''),
        'spec_visibility' => (string) ($item['spec_visibility'] ?? ''),
        'output_archive_format' => (string) ($item['output_archive_format'] ?? ''),
        'source_output_list_order' => (string) ($item['source_output_list_order'] ?? '0'),
        'notes' => (string) ($item['notes'] ?? ''),
        'source_of_truth' => (string) ($item['source_of_truth'] ?? ''),
    ];
}

function app_project_metadata_bundle_export_select_where_item(array $item): array
{
    return [
        'target_table_name' => (string) ($item['target_table_name'] ?? ''),
        'target_table_alias_name' => (string) ($item['target_table_alias_name'] ?? ''),
        'target_table_column_name' => (string) ($item['target_table_column_name'] ?? ''),
        'parameter_type' => (string) ($item['parameter_type'] ?? ''),
        'parameter_data_type' => (string) ($item['parameter_data_type'] ?? ''),
        'fixed_parameter' => (string) ($item['fixed_parameter'] ?? ''),
        'another_table_name' => (string) ($item['another_table_name'] ?? ''),
        'another_table_alias_name' => (string) ($item['another_table_alias_name'] ?? ''),
        'another_field_name' => (string) ($item['another_field_name'] ?? ''),
        'join_type' => (string) ($item['join_type'] ?? ''),
        'or_group' => (string) ($item['or_group'] ?? ''),
        'relational_operator' => (string) ($item['relational_operator'] ?? ''),
        'where_order' => (string) ($item['where_order'] ?? '0'),
        'source_of_truth' => (string) ($item['source_of_truth'] ?? ''),
    ];
}

function app_project_metadata_bundle_export_update_delete_where_item(array $item): array
{
    return [
        'target_table_column_name' => (string) ($item['target_table_column_name'] ?? ''),
        'parameter_type' => (string) ($item['parameter_type'] ?? ''),
        'parameter_data_type' => (string) ($item['parameter_data_type'] ?? ''),
        'fixed_parameter' => (string) ($item['fixed_parameter'] ?? ''),
        'or_group' => (string) ($item['or_group'] ?? ''),
        'relational_operator' => (string) ($item['relational_operator'] ?? ''),
        'where_order' => (string) ($item['where_order'] ?? '0'),
        'source_of_truth' => (string) ($item['source_of_truth'] ?? ''),
    ];
}

function app_project_metadata_bundle_export_simple_target_field_item(array $item): array
{
    return [
        'target_table_column_name' => (string) ($item['target_table_column_name'] ?? ''),
        'parameter_type' => (string) ($item['parameter_type'] ?? ''),
        'parameter_data_type' => (string) ($item['parameter_data_type'] ?? ''),
        'fixed_parameter' => (string) ($item['fixed_parameter'] ?? ''),
        'field_list_order' => (string) ($item['field_list_order'] ?? '0'),
        'source_of_truth' => (string) ($item['source_of_truth'] ?? ''),
    ];
}

/**
 * @return array<string,int|string>
 */
function app_project_metadata_bundle_summary_from_sections(array $sections): array
{
    $members = is_array($sections['memberships']['members'] ?? null) ? $sections['memberships']['members'] : [];
    $databaseSources = is_array($sections['database_sources']['database_sources'] ?? null)
        ? $sections['database_sources']['database_sources']
        : [];
    $tables = is_array($sections['tables']['tables'] ?? null) ? $sections['tables']['tables'] : [];
    $dataClasses = is_array($sections['data_classes']['data_classes'] ?? null)
        ? $sections['data_classes']['data_classes']
        : [];
    $sourceOutputs = is_array($sections['source_outputs']['source_outputs'] ?? null)
        ? $sections['source_outputs']['source_outputs']
        : [];
    $dbAccessClasses = is_array($sections['db_access']['classes'] ?? null)
        ? $sections['db_access']['classes']
        : [];

    $tableColumnCount = 0;
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }
        $tableColumnCount += count(is_array($table['columns'] ?? null) ? $table['columns'] : []);
    }

    $dataClassFieldCount = 0;
    foreach ($dataClasses as $dataClass) {
        if (!is_array($dataClass)) {
            continue;
        }
        $dataClassFieldCount += count(is_array($dataClass['fields'] ?? null) ? $dataClass['fields'] : []);
    }

    $databaseSourceWithPasswordCount = 0;
    foreach ($databaseSources as $databaseSource) {
        if (!is_array($databaseSource) || !($databaseSource['has_password'] ?? false)) {
            continue;
        }

        $databaseSourceWithPasswordCount++;
    }

    $dbAccessFunctionCount = 0;
    $selectWhereCount = 0;
    $selectTargetFieldCount = 0;
    $selectHavingCount = 0;
    $updateDeleteWhereCount = 0;
    $insertTargetFieldCount = 0;
    $updateTargetFieldCount = 0;
    $sourceOutputTargetCount = 0;
    foreach ($dbAccessClasses as $classItem) {
        if (!is_array($classItem)) {
            continue;
        }

        $functions = is_array($classItem['functions'] ?? null) ? $classItem['functions'] : [];
        $dbAccessFunctionCount += count($functions);
        foreach ($functions as $functionItem) {
            if (!is_array($functionItem)) {
                continue;
            }

            $selectWhereCount += count(is_array($functionItem['select_wheres'] ?? null) ? $functionItem['select_wheres'] : []);
            $selectTargetFieldCount += count(is_array($functionItem['select_target_fields'] ?? null) ? $functionItem['select_target_fields'] : []);
            $selectHavingCount += count(is_array($functionItem['select_havings'] ?? null) ? $functionItem['select_havings'] : []);
            $updateDeleteWhereCount += count(is_array($functionItem['update_delete_wheres'] ?? null) ? $functionItem['update_delete_wheres'] : []);
            $insertTargetFieldCount += count(is_array($functionItem['insert_target_fields'] ?? null) ? $functionItem['insert_target_fields'] : []);
            $updateTargetFieldCount += count(is_array($functionItem['update_target_fields'] ?? null) ? $functionItem['update_target_fields'] : []);
            $sourceOutputTargetCount += count(is_array($functionItem['source_output_keys'] ?? null) ? $functionItem['source_output_keys'] : []);
        }
    }

    return [
        'project_count' => 1,
        'membership_user_count' => 1 + count($members),
        'database_source_count' => count($databaseSources),
        'database_source_with_password_count' => $databaseSourceWithPasswordCount,
        'table_count' => count($tables),
        'table_column_count' => $tableColumnCount,
        'data_class_count' => count($dataClasses),
        'data_class_field_count' => $dataClassFieldCount,
        'source_output_count' => count($sourceOutputs),
        'db_access_class_count' => count($dbAccessClasses),
        'db_access_function_count' => $dbAccessFunctionCount,
        'db_access_select_where_count' => $selectWhereCount,
        'db_access_select_target_field_count' => $selectTargetFieldCount,
        'db_access_select_having_count' => $selectHavingCount,
        'db_access_update_delete_where_count' => $updateDeleteWhereCount,
        'db_access_insert_target_field_count' => $insertTargetFieldCount,
        'db_access_update_target_field_count' => $updateTargetFieldCount,
        'db_access_source_output_target_count' => $sourceOutputTargetCount,
    ];
}

/**
 * @return array{
 *     exists:bool,
 *     excluded_row_total:int,
 *     excluded_counts:array<string,int>
 * }
 */
function app_project_metadata_bundle_target_summary(array $app, string $projectKey, string $scope): array
{
    $pdo = app_create_config_pdo($app);
    $projectId = app_project_metadata_bundle_find_project_id($pdo, $projectKey);
    if ($projectId === null) {
        return [
            'exists' => false,
            'excluded_row_total' => 0,
            'excluded_counts' => app_project_metadata_bundle_empty_excluded_counts($scope),
        ];
    }

    $excludedCounts = app_project_metadata_bundle_excluded_section_counts($pdo, $projectId, $scope);

    return [
        'exists' => true,
        'excluded_row_total' => array_sum($excludedCounts),
        'excluded_counts' => $excludedCounts,
    ];
}

/**
 * @return array<string,int>
 */
function app_project_metadata_bundle_empty_excluded_counts(string $scope): array
{
    if ($scope !== app_project_metadata_bundle_default_scope()) {
        return [];
    }

    return [
        'page_security_policies' => 0,
        'page_security_policy_capabilities' => 0,
        'host_assignments' => 0,
        'compare_outputs' => 0,
        'compare_output_additional_paths' => 0,
        'custom_proxies' => 0,
        'custom_proxy_steps' => 0,
        'custom_proxy_source_output_targets' => 0,
        'project_html_source_bindings' => 0,
        'project_html_definitions' => 0,
        'project_html_parameters' => 0,
    ];
}

/**
 * @return array<string,int>
 */
function app_project_metadata_bundle_excluded_section_counts(PDO $pdo, int $projectId, string $scope): array
{
    $counts = app_project_metadata_bundle_empty_excluded_counts($scope);
    if ($scope !== app_project_metadata_bundle_default_scope()) {
        return $counts;
    }

    $queries = [
        'page_security_policies' => [
            'SELECT COUNT(*) FROM project_page_security_policies WHERE project_id = :project_id',
        ],
        'page_security_policy_capabilities' => [
            'SELECT COUNT(*)
             FROM project_page_security_policy_capabilities AS cap
             INNER JOIN project_page_security_policies AS policy
                 ON policy.id = cap.page_security_policy_id
             WHERE policy.project_id = :project_id',
        ],
        'host_assignments' => [
            'SELECT COUNT(*) FROM project_host_assignments WHERE project_id = :project_id',
        ],
        'compare_outputs' => [
            'SELECT COUNT(*) FROM project_compare_outputs WHERE project_id = :project_id',
        ],
        'compare_output_additional_paths' => [
            'SELECT COUNT(*)
             FROM project_compare_output_additional_paths AS path
             INNER JOIN project_compare_outputs AS output
                 ON output.id = path.compare_output_id
             WHERE output.project_id = :project_id',
        ],
        'custom_proxies' => [
            'SELECT COUNT(*) FROM project_custom_proxies WHERE project_id = :project_id',
        ],
        'custom_proxy_steps' => [
            'SELECT COUNT(*)
             FROM project_custom_proxy_steps AS step
             INNER JOIN project_custom_proxies AS proxy
                 ON proxy.id = step.custom_proxy_id
             WHERE proxy.project_id = :project_id',
        ],
        'custom_proxy_source_output_targets' => [
            'SELECT COUNT(*)
             FROM project_custom_proxy_source_output_targets AS target
             INNER JOIN project_custom_proxies AS proxy
                 ON proxy.id = target.custom_proxy_id
             WHERE proxy.project_id = :project_id',
        ],
        'project_html_source_bindings' => [
            'SELECT COUNT(*) FROM project_html_source_bindings WHERE project_id = :project_id',
        ],
        'project_html_definitions' => [
            'SELECT COUNT(*) FROM project_html_definitions WHERE project_id = :project_id',
        ],
        'project_html_parameters' => [
            'SELECT COUNT(*)
             FROM project_html_parameters
             WHERE project_id = :project_id',
        ],
    ];

    foreach ($queries as $key => $sqlParts) {
        $statement = $pdo->prepare(implode("\n", $sqlParts));
        $statement->execute([
            ':project_id' => $projectId,
        ]);

        $counts[$key] = (int) $statement->fetchColumn();
    }

    return $counts;
}

function app_project_metadata_bundle_find_project_id(PDO $pdo, string $projectKey): ?int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM projects
         WHERE project_key = :project_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
    ]);

    $value = $statement->fetchColumn();
    if ($value === false) {
        return null;
    }

    $projectId = (int) $value;

    return $projectId > 0 ? $projectId : null;
}

/**
 * @param array<string,string> $databaseSourceSecrets
 */
function app_project_metadata_bundle_apply_core_sections(
    PDO $pdo,
    array $sections,
    string $targetProjectKey,
    array $databaseSourceSecrets = [],
): void {
    $projectSection = is_array($sections['project'] ?? null) ? $sections['project'] : [];
    $membershipsSection = is_array($sections['memberships'] ?? null) ? $sections['memberships'] : [];
    $databaseSourcesSection = is_array($sections['database_sources'] ?? null) ? $sections['database_sources'] : [];
    $tablesSection = is_array($sections['tables'] ?? null) ? $sections['tables'] : [];
    $dataClassesSection = is_array($sections['data_classes'] ?? null) ? $sections['data_classes'] : [];
    $sourceOutputsSection = is_array($sections['source_outputs'] ?? null) ? $sections['source_outputs'] : [];
    $dbAccessSection = is_array($sections['db_access'] ?? null) ? $sections['db_access'] : [];

    $projectId = app_project_metadata_bundle_upsert_project($pdo, $projectSection, $targetProjectKey);
    app_project_metadata_bundle_replace_memberships($pdo, $projectId, $projectSection, $membershipsSection);
    app_project_metadata_bundle_delete_core_scope_rows($pdo, $projectId);

    app_project_metadata_bundle_insert_tables(
        $pdo,
        $projectId,
        is_array($tablesSection['tables'] ?? null) ? $tablesSection['tables'] : [],
    );
    app_project_metadata_bundle_insert_data_classes(
        $pdo,
        $projectId,
        is_array($dataClassesSection['data_classes'] ?? null) ? $dataClassesSection['data_classes'] : [],
    );
    $sourceOutputKeys = app_project_metadata_bundle_insert_source_outputs(
        $pdo,
        $projectId,
        is_array($sourceOutputsSection['source_outputs'] ?? null) ? $sourceOutputsSection['source_outputs'] : [],
    );
    app_project_metadata_bundle_insert_db_access(
        $pdo,
        $projectId,
        is_array($dbAccessSection['classes'] ?? null) ? $dbAccessSection['classes'] : [],
        $sourceOutputKeys,
    );
    app_project_metadata_bundle_upsert_database_sources(
        $pdo,
        is_array($databaseSourcesSection['database_sources'] ?? null)
            ? $databaseSourcesSection['database_sources']
            : [],
        $databaseSourceSecrets,
    );
}

function app_project_metadata_bundle_upsert_project(PDO $pdo, array $projectSection, string $targetProjectKey): int
{
    $existingProjectId = app_project_metadata_bundle_find_project_id($pdo, $targetProjectKey);
    if ($existingProjectId !== null) {
        $statement = $pdo->prepare(
            'UPDATE projects
             SET
                 name = :name,
                 slug = :slug,
                 lifecycle_status = :lifecycle_status,
                 owner_login_id = :owner_login_id,
                 description = :description,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $statement->execute([
            ':id' => $existingProjectId,
            ':name' => (string) ($projectSection['name'] ?? ''),
            ':slug' => (string) ($projectSection['slug'] ?? ''),
            ':lifecycle_status' => (string) ($projectSection['lifecycle_status'] ?? 'draft'),
            ':owner_login_id' => (string) ($projectSection['owner_login_id'] ?? ''),
            ':description' => (string) ($projectSection['description'] ?? ''),
        ]);

        return $existingProjectId;
    }

    $statement = $pdo->prepare(
        'INSERT INTO projects (
            project_key,
            name,
            slug,
            lifecycle_status,
            owner_login_id,
            description
        ) VALUES (
            :project_key,
            :name,
            :slug,
            :lifecycle_status,
            :owner_login_id,
            :description
        )'
    );
    $statement->execute([
        ':project_key' => $targetProjectKey,
        ':name' => (string) ($projectSection['name'] ?? ''),
        ':slug' => (string) ($projectSection['slug'] ?? ''),
        ':lifecycle_status' => (string) ($projectSection['lifecycle_status'] ?? 'draft'),
        ':owner_login_id' => (string) ($projectSection['owner_login_id'] ?? ''),
        ':description' => (string) ($projectSection['description'] ?? ''),
    ]);

    return (int) $pdo->lastInsertId();
}

function app_project_metadata_bundle_replace_memberships(PDO $pdo, int $projectId, array $projectSection, array $membershipsSection): void
{
    $owner = is_array($membershipsSection['owner'] ?? null) ? $membershipsSection['owner'] : [];
    $ownerLoginId = trim((string) ($projectSection['owner_login_id'] ?? ($owner['login_id'] ?? '')));
    if ($ownerLoginId === '') {
        throw new RuntimeException('owner_login_id が空の bundle は import できません。');
    }

    $deleteStatement = $pdo->prepare(
        'DELETE FROM project_memberships
         WHERE project_id = :project_id'
    );
    $deleteStatement->execute([
        ':project_id' => $projectId,
    ]);

    $insertStatement = $pdo->prepare(
        'INSERT INTO project_memberships (
            project_id,
            login_id,
            role_code,
            can_administer
        ) VALUES (
            :project_id,
            :login_id,
            :role_code,
            :can_administer
        )'
    );
    $insertStatement->execute([
        ':project_id' => $projectId,
        ':login_id' => $ownerLoginId,
        ':role_code' => 'owner',
        ':can_administer' => 1,
    ]);

    $members = is_array($membershipsSection['members'] ?? null) ? $membershipsSection['members'] : [];
    $normalizedMembers = [];
    foreach ($members as $member) {
        if (!is_array($member)) {
            continue;
        }

        $loginId = trim((string) ($member['login_id'] ?? ''));
        if ($loginId === '' || $loginId === $ownerLoginId) {
            continue;
        }

        $normalizedMembers[$loginId] = [
            'role_code' => ((string) ($member['role_code'] ?? 'member')) === 'admin' ? 'admin' : 'member',
            'can_administer' => (bool) ($member['can_administer'] ?? false),
        ];
    }

    foreach ($normalizedMembers as $loginId => $member) {
        $roleCode = $member['role_code'] === 'admin' ? 'admin' : 'member';
        $canAdminister = $roleCode === 'admin' || $member['can_administer'];

        $insertStatement->execute([
            ':project_id' => $projectId,
            ':login_id' => $loginId,
            ':role_code' => $roleCode,
            ':can_administer' => $canAdminister ? 1 : 0,
        ]);
    }
}

/**
 * @return array<string,array{id:int,password:string}>
 */
function app_project_metadata_bundle_fetch_existing_database_sources(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT
            id,
            source_key,
            password
         FROM database_sources
         ORDER BY source_key, id'
    );

    $catalog = [];
    foreach ($statement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $sourceKey = app_normalize_database_source_key((string) ($row['source_key'] ?? ''));
        if ($sourceKey === '') {
            continue;
        }

        $catalog[$sourceKey] = [
            'id' => (int) ($row['id'] ?? 0),
            'password' => (string) ($row['password'] ?? ''),
        ];
    }

    return $catalog;
}

/**
 * @param list<array<string,mixed>> $databaseSources
 * @param array<string,string> $databaseSourceSecrets
 */
function app_project_metadata_bundle_upsert_database_sources(
    PDO $pdo,
    array $databaseSources,
    array $databaseSourceSecrets,
): void {
    if ($databaseSources === []) {
        return;
    }

    $existingCatalog = app_project_metadata_bundle_fetch_existing_database_sources($pdo);
    $insertStatement = $pdo->prepare(
        'INSERT INTO database_sources (
            source_key,
            label,
            description,
            host,
            port,
            database_name,
            user_name,
            password,
            supports_live_schema_import,
            supports_proxy_runtime_read,
            proxy_runtime_priority,
            source_of_truth
        ) VALUES (
            :source_key,
            :label,
            :description,
            :host,
            :port,
            :database_name,
            :user_name,
            :password,
            :supports_live_schema_import,
            :supports_proxy_runtime_read,
            :proxy_runtime_priority,
            :source_of_truth
        )'
    );
    $updateStatement = $pdo->prepare(
        'UPDATE database_sources
         SET
            source_key = :source_key,
            label = :label,
            description = :description,
            host = :host,
            port = :port,
            database_name = :database_name,
            user_name = :user_name,
            password = :password,
            supports_live_schema_import = :supports_live_schema_import,
            supports_proxy_runtime_read = :supports_proxy_runtime_read,
            proxy_runtime_priority = :proxy_runtime_priority,
            source_of_truth = :source_of_truth
         WHERE id = :id'
    );

    foreach ($databaseSources as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceKey = app_normalize_database_source_key((string) ($item['source_key'] ?? ''));
        if ($sourceKey === '') {
            continue;
        }

        $hasPassword = (bool) ($item['has_password'] ?? false);
        $secretProvided = array_key_exists($sourceKey, $databaseSourceSecrets)
            && $databaseSourceSecrets[$sourceKey] !== '';
        $password = $secretProvided ? $databaseSourceSecrets[$sourceKey] : '';

        $parameters = [
            ':source_key' => $sourceKey,
            ':label' => (string) ($item['label'] ?? ''),
            ':description' => (string) ($item['description'] ?? ''),
            ':host' => (string) ($item['host'] ?? ''),
            ':port' => (string) ($item['port'] ?? '3306'),
            ':database_name' => (string) ($item['database_name'] ?? ''),
            ':user_name' => (string) ($item['user_name'] ?? ''),
            ':password' => $password,
            ':supports_live_schema_import' => (bool) ($item['supports_live_schema_import'] ?? false) ? 1 : 0,
            ':supports_proxy_runtime_read' => (bool) ($item['supports_proxy_runtime_read'] ?? false) ? 1 : 0,
            ':proxy_runtime_priority' => (int) ($item['proxy_runtime_priority'] ?? 1000),
            ':source_of_truth' => (string) ($item['source_of_truth'] ?? 'manual'),
        ];

        if (isset($existingCatalog[$sourceKey])) {
            if (!$secretProvided) {
                $parameters[':password'] = $existingCatalog[$sourceKey]['password'];
            }

            $updateStatement->execute($parameters + [
                ':id' => $existingCatalog[$sourceKey]['id'],
            ]);
            continue;
        }

        if ($hasPassword && !$secretProvided) {
            throw new RuntimeException('database_source secret が必要です: ' . $sourceKey);
        }

        $insertStatement->execute($parameters);
    }
}

function app_project_metadata_bundle_delete_core_scope_rows(PDO $pdo, int $projectId): void
{
    foreach (
        [
            'project_db_access_classes' => 'project_id',
            'project_source_outputs' => 'project_id',
            'dataclass' => 'ProjectPID',
            'dbtable' => 'ProjectPID',
        ] as $tableName => $projectColumn
    ) {
        $statement = $pdo->prepare(
            'DELETE FROM ' . $tableName . '
             WHERE ' . $projectColumn . ' = :project_id'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);
    }
}

/**
 * @param list<array<string,mixed>> $tables
 */
function app_project_metadata_bundle_insert_tables(PDO $pdo, int $projectId, array $tables): void
{
    $dialect = app_sql_dialect_from_pdo($pdo);
    $isNullIdentifier = app_sql_identifier($dialect, 'IsNull');
    $tableStatement = $pdo->prepare(
        'INSERT INTO dbtable (ProjectPID, name)
         VALUES (:project_id, :name)'
    );
    $columnStatement = $pdo->prepare(
        'INSERT INTO dbtablecolumns (
            ProjectPID,
            dbtablePID,
            name,
            datatype,
            ' . $isNullIdentifier . ',
            IsKey,
            IsDefault,
            Extra,
            ColumnListOrder,
            memo
        ) VALUES (
            :project_id,
            :table_id,
            :name,
            :datatype,
            :is_null,
            :is_key,
            :is_default,
            :extra,
            :column_list_order,
            :memo
        )'
    );

    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }

        $tableStatement->execute([
            ':project_id' => $projectId,
            ':name' => (string) ($table['name'] ?? ''),
        ]);
        $tableId = (int) $pdo->lastInsertId();

        foreach ($table['columns'] ?? [] as $column) {
            if (!is_array($column)) {
                continue;
            }

            $columnStatement->execute([
                ':project_id' => $projectId,
                ':table_id' => $tableId,
                ':name' => (string) ($column['name'] ?? ''),
                ':datatype' => (string) ($column['datatype'] ?? ''),
                ':is_null' => (string) ($column['is_null'] ?? ''),
                ':is_key' => (string) ($column['is_key'] ?? ''),
                ':is_default' => (string) ($column['is_default'] ?? ''),
                ':extra' => (string) ($column['extra'] ?? ''),
                ':column_list_order' => (int) ($column['column_list_order'] ?? 0),
                ':memo' => (string) ($column['memo'] ?? ''),
            ]);
        }
    }
}

/**
 * @param list<array<string,mixed>> $dataClasses
 */
function app_project_metadata_bundle_insert_data_classes(PDO $pdo, int $projectId, array $dataClasses): void
{
    $dataClassStatement = $pdo->prepare(
        'INSERT INTO dataclass (
            ProjectPID,
            name,
            StoreBasePath,
            IsAutoload,
            InheritParentDataClassName
        ) VALUES (
            :project_id,
            :name,
            :store_base_path,
            :is_autoload,
            :inherit_parent_data_class_name
        )'
    );
    $fieldStatement = $pdo->prepare(
        'INSERT INTO dataclassfields (
            ProjectPID,
            dataclassPID,
            name,
            datatype,
            FieldListOrder,
            RefDataClassName,
            RefDataClassFieldName
        ) VALUES (
            :project_id,
            :dataclass_id,
            :name,
            :datatype,
            :field_list_order,
            :ref_data_class_name,
            :ref_data_class_field_name
        )'
    );

    foreach ($dataClasses as $dataClass) {
        if (!is_array($dataClass)) {
            continue;
        }

        $dataClassStatement->execute([
            ':project_id' => $projectId,
            ':name' => (string) ($dataClass['name'] ?? ''),
            ':store_base_path' => (string) ($dataClass['store_base_path'] ?? ''),
            ':is_autoload' => ((string) ($dataClass['is_autoload'] ?? '0')) === '1' ? 1 : 0,
            ':inherit_parent_data_class_name' => (string) ($dataClass['inherit_parent_data_class_name'] ?? ''),
        ]);
        $dataClassId = (int) $pdo->lastInsertId();

        foreach ($dataClass['fields'] ?? [] as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldStatement->execute([
                ':project_id' => $projectId,
                ':dataclass_id' => $dataClassId,
                ':name' => (string) ($field['name'] ?? ''),
                ':datatype' => (string) ($field['datatype'] ?? ''),
                ':field_list_order' => (int) ($field['field_list_order'] ?? 0),
                ':ref_data_class_name' => (string) ($field['ref_data_class_name'] ?? ''),
                ':ref_data_class_field_name' => (string) ($field['ref_data_class_field_name'] ?? ''),
            ]);
        }
    }
}

/**
 * @param list<array<string,mixed>> $sourceOutputs
 * @return array<string,bool>
 */
function app_project_metadata_bundle_insert_source_outputs(PDO $pdo, int $projectId, array $sourceOutputs): array
{
    $statement = $pdo->prepare(
        'INSERT INTO project_source_outputs (
            project_id,
            source_output_key,
            name,
            program_language,
            class_type,
            release_target_type,
            source_template_dir,
            source_output_dir,
            source_temp_output_dir,
            proxy_base_url,
            autoload_filename_suffix,
            source_text_char_code,
            runtime_source_relative_path,
            artifact_strategy,
            target_binding_type,
            spec_visibility,
            output_archive_format,
            source_output_list_order,
            notes,
            source_of_truth
        ) VALUES (
            :project_id,
            :source_output_key,
            :name,
            :program_language,
            :class_type,
            :release_target_type,
            :source_template_dir,
            :source_output_dir,
            :source_temp_output_dir,
            :proxy_base_url,
            :autoload_filename_suffix,
            :source_text_char_code,
            :runtime_source_relative_path,
            :artifact_strategy,
            :target_binding_type,
            :spec_visibility,
            :output_archive_format,
            :source_output_list_order,
            :notes,
            :source_of_truth
        )'
    );

    $keys = [];
    foreach ($sourceOutputs as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceOutputKey = (string) ($item['source_output_key'] ?? '');
        $statement->execute([
            ':project_id' => $projectId,
            ':source_output_key' => $sourceOutputKey,
            ':name' => (string) ($item['name'] ?? ''),
            ':program_language' => (string) ($item['program_language'] ?? ''),
            ':class_type' => (string) ($item['class_type'] ?? ''),
            ':release_target_type' => (string) ($item['release_target_type'] ?? ''),
            ':source_template_dir' => (string) ($item['source_template_dir'] ?? ''),
            ':source_output_dir' => (string) ($item['source_output_dir'] ?? ''),
            ':source_temp_output_dir' => (string) ($item['source_temp_output_dir'] ?? ''),
            ':proxy_base_url' => (string) ($item['proxy_base_url'] ?? ''),
            ':autoload_filename_suffix' => (string) ($item['autoload_filename_suffix'] ?? ''),
            ':source_text_char_code' => (string) ($item['source_text_char_code'] ?? ''),
            ':runtime_source_relative_path' => (string) ($item['runtime_source_relative_path'] ?? ''),
            ':artifact_strategy' => (string) ($item['artifact_strategy'] ?? ''),
            ':target_binding_type' => (string) ($item['target_binding_type'] ?? ''),
            ':spec_visibility' => (string) ($item['spec_visibility'] ?? 'internal-only'),
            ':output_archive_format' => (string) ($item['output_archive_format'] ?? 'tar.gz'),
            ':source_output_list_order' => (int) ((string) ($item['source_output_list_order'] ?? '0')),
            ':notes' => (string) ($item['notes'] ?? ''),
            ':source_of_truth' => (string) ($item['source_of_truth'] ?? 'manual'),
        ]);
        $keys[$sourceOutputKey] = true;
    }

    return $keys;
}

/**
 * @param list<array<string,mixed>> $classes
 * @param array<string,bool> $sourceOutputKeys
 */
function app_project_metadata_bundle_insert_db_access(
    PDO $pdo,
    int $projectId,
    array $classes,
    array $sourceOutputKeys,
): void {
    $classStatement = $pdo->prepare(
        'INSERT INTO project_db_access_classes (
            project_id,
            source_name,
            store_base_path,
            is_autoload,
            notes,
            source_of_truth,
            last_detected_dbaccess_file,
            last_detected_data_file
        ) VALUES (
            :project_id,
            :source_name,
            :store_base_path,
            :is_autoload,
            :notes,
            :source_of_truth,
            :last_detected_dbaccess_file,
            :last_detected_data_file
        )'
    );
    $functionStatement = $pdo->prepare(
        'INSERT INTO project_db_access_functions (
            db_access_class_id,
            function_name,
            function_list_order,
            function_suffix,
            action_type,
            data_class_base_name,
            target_table_name,
            parameter_type,
            select_by_distinct,
            sort_order_columns,
            memo,
            limit_parameter_type,
            limit_fixed_parameter,
            or_group_type,
            single_proxy_auth_type,
            single_proxy_single_get_function_name,
            auth_policy_version,
            auth_policy_json,
            is_blob_target,
            detected_signature,
            detected_line,
            source_of_truth
        ) VALUES (
            :db_access_class_id,
            :function_name,
            :function_list_order,
            :function_suffix,
            :action_type,
            :data_class_base_name,
            :target_table_name,
            :parameter_type,
            :select_by_distinct,
            :sort_order_columns,
            :memo,
            :limit_parameter_type,
            :limit_fixed_parameter,
            :or_group_type,
            :single_proxy_auth_type,
            :single_proxy_single_get_function_name,
            :auth_policy_version,
            :auth_policy_json,
            :is_blob_target,
            :detected_signature,
            :detected_line,
            :source_of_truth
        )'
    );
    $sourceOutputTargetStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_source_output_targets (
            db_access_function_id,
            source_output_key
        ) VALUES (
            :db_access_function_id,
            :source_output_key
        )'
    );
    $selectWhereStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_select_wheres (
            db_access_function_id,
            target_table_name,
            target_table_alias_name,
            target_table_column_name,
            parameter_type,
            parameter_data_type,
            fixed_parameter,
            another_table_name,
            another_table_alias_name,
            another_field_name,
            join_type,
            or_group,
            relational_operator,
            where_order,
            source_of_truth
        ) VALUES (
            :db_access_function_id,
            :target_table_name,
            :target_table_alias_name,
            :target_table_column_name,
            :parameter_type,
            :parameter_data_type,
            :fixed_parameter,
            :another_table_name,
            :another_table_alias_name,
            :another_field_name,
            :join_type,
            :or_group,
            :relational_operator,
            :where_order,
            :source_of_truth
        )'
    );
    $selectTargetFieldStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_select_target_fields (
            db_access_function_id,
            target_table_name,
            target_table_alias_name,
            target_table_column_name,
            target_table_column_prefix,
            target_table_column_suffix,
            store_class_field_name,
            group_by_target,
            field_list_order,
            source_of_truth
        ) VALUES (
            :db_access_function_id,
            :target_table_name,
            :target_table_alias_name,
            :target_table_column_name,
            :target_table_column_prefix,
            :target_table_column_suffix,
            :store_class_field_name,
            :group_by_target,
            :field_list_order,
            :source_of_truth
        )'
    );
    $selectHavingStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_select_havings (
            db_access_function_id,
            left_target_prefix,
            left_target_field_id,
            left_target_suffix,
            relational_operator,
            right_target_prefix,
            right_parameter_type,
            right_parameter_data_type,
            right_fixed_parameter,
            right_target_field_id,
            right_target_suffix,
            having_order,
            source_of_truth
        ) VALUES (
            :db_access_function_id,
            :left_target_prefix,
            :left_target_field_id,
            :left_target_suffix,
            :relational_operator,
            :right_target_prefix,
            :right_parameter_type,
            :right_parameter_data_type,
            :right_fixed_parameter,
            :right_target_field_id,
            :right_target_suffix,
            :having_order,
            :source_of_truth
        )'
    );
    $updateDeleteWhereStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_update_delete_wheres (
            db_access_function_id,
            target_table_column_name,
            parameter_type,
            parameter_data_type,
            fixed_parameter,
            or_group,
            relational_operator,
            where_order,
            source_of_truth
        ) VALUES (
            :db_access_function_id,
            :target_table_column_name,
            :parameter_type,
            :parameter_data_type,
            :fixed_parameter,
            :or_group,
            :relational_operator,
            :where_order,
            :source_of_truth
        )'
    );
    $insertTargetFieldStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_insert_target_fields (
            db_access_function_id,
            target_table_column_name,
            parameter_type,
            parameter_data_type,
            fixed_parameter,
            field_list_order,
            source_of_truth
        ) VALUES (
            :db_access_function_id,
            :target_table_column_name,
            :parameter_type,
            :parameter_data_type,
            :fixed_parameter,
            :field_list_order,
            :source_of_truth
        )'
    );
    $updateTargetFieldStatement = $pdo->prepare(
        'INSERT INTO project_db_access_function_update_target_fields (
            db_access_function_id,
            target_table_column_name,
            parameter_type,
            parameter_data_type,
            fixed_parameter,
            field_list_order,
            source_of_truth
        ) VALUES (
            :db_access_function_id,
            :target_table_column_name,
            :parameter_type,
            :parameter_data_type,
            :fixed_parameter,
            :field_list_order,
            :source_of_truth
        )'
    );

    foreach ($classes as $classItem) {
        if (!is_array($classItem)) {
            continue;
        }

        $classStatement->execute([
            ':project_id' => $projectId,
            ':source_name' => (string) ($classItem['source_name'] ?? ''),
            ':store_base_path' => (string) ($classItem['store_base_path'] ?? ''),
            ':is_autoload' => ((string) ($classItem['is_autoload'] ?? '0')) === '1' ? 1 : 0,
            ':notes' => (string) ($classItem['notes'] ?? ''),
            ':source_of_truth' => (string) ($classItem['source_of_truth'] ?? 'manual'),
            ':last_detected_dbaccess_file' => (string) ($classItem['last_detected_dbaccess_file'] ?? ''),
            ':last_detected_data_file' => (string) ($classItem['last_detected_data_file'] ?? ''),
        ]);
        $classId = (int) $pdo->lastInsertId();

        foreach ($classItem['functions'] ?? [] as $functionItem) {
            if (!is_array($functionItem)) {
                continue;
            }

            $functionStatement->execute([
                ':db_access_class_id' => $classId,
                ':function_name' => (string) ($functionItem['function_name'] ?? ''),
                ':function_list_order' => (int) ((string) ($functionItem['function_list_order'] ?? '0')),
                ':function_suffix' => (string) ($functionItem['function_suffix'] ?? ''),
                ':action_type' => (string) ($functionItem['action_type'] ?? ''),
                ':data_class_base_name' => (string) ($functionItem['data_class_base_name'] ?? ''),
                ':target_table_name' => (string) ($functionItem['target_table_name'] ?? ''),
                ':parameter_type' => (string) ($functionItem['parameter_type'] ?? ''),
                ':select_by_distinct' => ((string) ($functionItem['select_by_distinct'] ?? '0')) === '1' ? 1 : 0,
                ':sort_order_columns' => (string) ($functionItem['sort_order_columns'] ?? ''),
                ':memo' => (string) ($functionItem['memo'] ?? ''),
                ':limit_parameter_type' => (string) ($functionItem['limit_parameter_type'] ?? ''),
                ':limit_fixed_parameter' => (string) ($functionItem['limit_fixed_parameter'] ?? ''),
                ':or_group_type' => (string) ($functionItem['or_group_type'] ?? ''),
                ':single_proxy_auth_type' => (string) ($functionItem['single_proxy_auth_type'] ?? ''),
                ':single_proxy_single_get_function_name' => (string) ($functionItem['single_proxy_single_get_function_name'] ?? ''),
                ':auth_policy_version' => (int) ((string) ($functionItem['auth_policy_version'] ?? '1')),
                ':auth_policy_json' => (string) ($functionItem['auth_policy_json'] ?? ''),
                ':is_blob_target' => ((string) ($functionItem['is_blob_target'] ?? '0')) === '1' ? 1 : 0,
                ':detected_signature' => (string) ($functionItem['detected_signature'] ?? ''),
                ':detected_line' => (int) ((string) ($functionItem['detected_line'] ?? '0')),
                ':source_of_truth' => (string) ($functionItem['source_of_truth'] ?? 'manual'),
            ]);
            $functionId = (int) $pdo->lastInsertId();

            foreach ($functionItem['source_output_keys'] ?? [] as $sourceOutputKey) {
                if (!is_string($sourceOutputKey) || !isset($sourceOutputKeys[$sourceOutputKey])) {
                    throw new RuntimeException(
                        'bundle 内に存在しない source_output_key を db_access target へ import できません: '
                        . (string) ($classItem['source_name'] ?? '') . '.'
                        . (string) ($functionItem['function_name'] ?? '') . ' -> ' . (string) $sourceOutputKey
                    );
                }

                $sourceOutputTargetStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':source_output_key' => $sourceOutputKey,
                ]);
            }

            foreach ($functionItem['select_wheres'] ?? [] as $whereItem) {
                if (!is_array($whereItem)) {
                    continue;
                }

                $selectWhereStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':target_table_name' => (string) ($whereItem['target_table_name'] ?? ''),
                    ':target_table_alias_name' => (string) ($whereItem['target_table_alias_name'] ?? ''),
                    ':target_table_column_name' => (string) ($whereItem['target_table_column_name'] ?? ''),
                    ':parameter_type' => (string) ($whereItem['parameter_type'] ?? ''),
                    ':parameter_data_type' => (string) ($whereItem['parameter_data_type'] ?? ''),
                    ':fixed_parameter' => (string) ($whereItem['fixed_parameter'] ?? ''),
                    ':another_table_name' => (string) ($whereItem['another_table_name'] ?? ''),
                    ':another_table_alias_name' => (string) ($whereItem['another_table_alias_name'] ?? ''),
                    ':another_field_name' => (string) ($whereItem['another_field_name'] ?? ''),
                    ':join_type' => (string) ($whereItem['join_type'] ?? ''),
                    ':or_group' => (string) ($whereItem['or_group'] ?? ''),
                    ':relational_operator' => (string) ($whereItem['relational_operator'] ?? '='),
                    ':where_order' => (int) ((string) ($whereItem['where_order'] ?? '0')),
                    ':source_of_truth' => (string) ($whereItem['source_of_truth'] ?? 'manual'),
                ]);
            }

            $selectTargetFieldIdByKey = [];
            foreach ($functionItem['select_target_fields'] ?? [] as $targetFieldItem) {
                if (!is_array($targetFieldItem)) {
                    continue;
                }

                $selectTargetFieldStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':target_table_name' => (string) ($targetFieldItem['target_table_name'] ?? ''),
                    ':target_table_alias_name' => (string) ($targetFieldItem['target_table_alias_name'] ?? ''),
                    ':target_table_column_name' => (string) ($targetFieldItem['target_table_column_name'] ?? ''),
                    ':target_table_column_prefix' => (string) ($targetFieldItem['target_table_column_prefix'] ?? ''),
                    ':target_table_column_suffix' => (string) ($targetFieldItem['target_table_column_suffix'] ?? ''),
                    ':store_class_field_name' => (string) ($targetFieldItem['store_class_field_name'] ?? ''),
                    ':group_by_target' => ((string) ($targetFieldItem['group_by_target'] ?? '0')) === '1' ? 1 : 0,
                    ':field_list_order' => (int) ((string) ($targetFieldItem['field_list_order'] ?? '0')),
                    ':source_of_truth' => (string) ($targetFieldItem['source_of_truth'] ?? 'manual'),
                ]);
                $fieldKey = (string) ($targetFieldItem['select_target_field_key'] ?? '');
                if ($fieldKey !== '') {
                    $selectTargetFieldIdByKey[$fieldKey] = (int) $pdo->lastInsertId();
                }
            }

            foreach ($functionItem['select_havings'] ?? [] as $havingItem) {
                if (!is_array($havingItem)) {
                    continue;
                }

                $leftFieldKey = trim((string) ($havingItem['left_target_field_key'] ?? ''));
                $rightFieldKey = trim((string) ($havingItem['right_target_field_key'] ?? ''));
                $leftFieldId = $leftFieldKey === '' ? 0 : (int) ($selectTargetFieldIdByKey[$leftFieldKey] ?? 0);
                $rightFieldId = $rightFieldKey === '' ? 0 : (int) ($selectTargetFieldIdByKey[$rightFieldKey] ?? 0);
                if ($leftFieldKey !== '' && $leftFieldId <= 0) {
                    throw new RuntimeException('select_having.left_target_field_key を解決できません: ' . $leftFieldKey);
                }
                if ($rightFieldKey !== '' && $rightFieldId <= 0) {
                    throw new RuntimeException('select_having.right_target_field_key を解決できません: ' . $rightFieldKey);
                }

                $selectHavingStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':left_target_prefix' => (string) ($havingItem['left_target_prefix'] ?? ''),
                    ':left_target_field_id' => $leftFieldId,
                    ':left_target_suffix' => (string) ($havingItem['left_target_suffix'] ?? ''),
                    ':relational_operator' => (string) ($havingItem['relational_operator'] ?? '='),
                    ':right_target_prefix' => (string) ($havingItem['right_target_prefix'] ?? ''),
                    ':right_parameter_type' => (string) ($havingItem['right_parameter_type'] ?? ''),
                    ':right_parameter_data_type' => (string) ($havingItem['right_parameter_data_type'] ?? ''),
                    ':right_fixed_parameter' => (string) ($havingItem['right_fixed_parameter'] ?? ''),
                    ':right_target_field_id' => $rightFieldId,
                    ':right_target_suffix' => (string) ($havingItem['right_target_suffix'] ?? ''),
                    ':having_order' => (int) ((string) ($havingItem['having_order'] ?? '0')),
                    ':source_of_truth' => (string) ($havingItem['source_of_truth'] ?? 'manual'),
                ]);
            }

            foreach ($functionItem['update_delete_wheres'] ?? [] as $whereItem) {
                if (!is_array($whereItem)) {
                    continue;
                }

                $updateDeleteWhereStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':target_table_column_name' => (string) ($whereItem['target_table_column_name'] ?? ''),
                    ':parameter_type' => (string) ($whereItem['parameter_type'] ?? ''),
                    ':parameter_data_type' => (string) ($whereItem['parameter_data_type'] ?? ''),
                    ':fixed_parameter' => (string) ($whereItem['fixed_parameter'] ?? ''),
                    ':or_group' => (string) ($whereItem['or_group'] ?? ''),
                    ':relational_operator' => (string) ($whereItem['relational_operator'] ?? '='),
                    ':where_order' => (int) ((string) ($whereItem['where_order'] ?? '0')),
                    ':source_of_truth' => (string) ($whereItem['source_of_truth'] ?? 'manual'),
                ]);
            }

            foreach ($functionItem['insert_target_fields'] ?? [] as $fieldItem) {
                if (!is_array($fieldItem)) {
                    continue;
                }

                $insertTargetFieldStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':target_table_column_name' => (string) ($fieldItem['target_table_column_name'] ?? ''),
                    ':parameter_type' => (string) ($fieldItem['parameter_type'] ?? ''),
                    ':parameter_data_type' => (string) ($fieldItem['parameter_data_type'] ?? ''),
                    ':fixed_parameter' => (string) ($fieldItem['fixed_parameter'] ?? ''),
                    ':field_list_order' => (int) ((string) ($fieldItem['field_list_order'] ?? '0')),
                    ':source_of_truth' => (string) ($fieldItem['source_of_truth'] ?? 'manual'),
                ]);
            }

            foreach ($functionItem['update_target_fields'] ?? [] as $fieldItem) {
                if (!is_array($fieldItem)) {
                    continue;
                }

                $updateTargetFieldStatement->execute([
                    ':db_access_function_id' => $functionId,
                    ':target_table_column_name' => (string) ($fieldItem['target_table_column_name'] ?? ''),
                    ':parameter_type' => (string) ($fieldItem['parameter_type'] ?? ''),
                    ':parameter_data_type' => (string) ($fieldItem['parameter_data_type'] ?? ''),
                    ':fixed_parameter' => (string) ($fieldItem['fixed_parameter'] ?? ''),
                    ':field_list_order' => (int) ((string) ($fieldItem['field_list_order'] ?? '0')),
                    ':source_of_truth' => (string) ($fieldItem['source_of_truth'] ?? 'manual'),
                ]);
            }
        }
    }
}

function app_project_metadata_bundle_json_encode(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );

    if (!is_string($json)) {
        throw new RuntimeException('bundle JSON encode に失敗しました。');
    }

    return $json . PHP_EOL;
}

function app_project_metadata_bundle_decode_json_file(string $path): array
{
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        throw new RuntimeException('bundle file を読み込めません: ' . $path);
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('bundle JSON を解釈できません: ' . $path);
    }

    return $decoded;
}

function app_project_metadata_bundle_write_text(string $path, string $contents): void
{
    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('bundle file を書き込めません: ' . $path);
    }
}

function app_project_metadata_bundle_delete_tree(string $path): void
{
    $normalizedPath = rtrim(str_replace('\\', '/', $path), '/');
    if ($normalizedPath === '' || !file_exists($normalizedPath)) {
        return;
    }

    if (is_file($normalizedPath) || is_link($normalizedPath)) {
        @unlink($normalizedPath);
        return;
    }

    $entries = scandir($normalizedPath);
    if (!is_array($entries)) {
        @rmdir($normalizedPath);
        return;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        app_project_metadata_bundle_delete_tree($normalizedPath . '/' . $entry);
    }

    @rmdir($normalizedPath);
}

function app_cli_project_metadata_export_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_project_metadata.php --project-key=MTOOL

Options:
  --project-key=KEY    export 対象 project key
  --output-dir=PATH    bundle 出力先 directory
  --scope=project-core first slice scope。default: project-core
  --database-sources=LIST
                       optional comma-separated database source keys
  --requested-by=NAME  manifest に残す requested_by。default: cli
  --help               このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     output_dir:string,
 *     scope:string,
 *     database_sources:string,
 *     requested_by:string,
 *     error:string
 * }
 */
function app_cli_project_metadata_export_parse_args(array $argv): array
{
    $projectKey = '';
    $outputDir = '';
    $scope = app_project_metadata_bundle_default_scope();
    $databaseSources = '';
    $requestedBy = 'cli';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'output_dir' => '',
                'scope' => $scope,
                'database_sources' => '',
                'requested_by' => $requestedBy,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--output-dir=')) {
            $outputDir = trim(substr($argument, strlen('--output-dir=')));
            continue;
        }
        if (str_starts_with($argument, '--scope=')) {
            $scope = trim(substr($argument, strlen('--scope=')));
            continue;
        }
        if (str_starts_with($argument, '--database-sources=')) {
            $databaseSources = trim(substr($argument, strlen('--database-sources=')));
            continue;
        }
        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = trim(substr($argument, strlen('--requested-by=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'scope' => $scope,
            'database_sources' => '',
            'requested_by' => $requestedBy,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'scope' => $scope,
            'database_sources' => $databaseSources,
            'requested_by' => $requestedBy,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if (!app_project_metadata_bundle_scope_is_supported($scope)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'scope' => $scope,
            'database_sources' => $databaseSources,
            'requested_by' => $requestedBy,
            'error' => '未対応の --scope=... です。',
        ];
    }

    $databaseSourceKeysResult = app_project_metadata_bundle_parse_database_source_keys($databaseSources);
    if (!$databaseSourceKeysResult['ok']) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'scope' => $scope,
            'database_sources' => $databaseSources,
            'requested_by' => $requestedBy,
            'error' => $databaseSourceKeysResult['error'],
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'output_dir' => $outputDir,
        'scope' => $scope,
        'database_sources' => implode(',', $databaseSourceKeysResult['keys']),
        'requested_by' => $requestedBy,
        'error' => '',
    ];
}

function app_cli_project_metadata_import_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/import_project_metadata.php --bundle=work/project-metadata-bundles/MTOOL/... --mode=preview

Options:
  --bundle=PATH            bundle directory か manifest.json
  --mode=preview|apply     preview または apply
  --target-project-key=KEY import 先 project key。default: bundle source_project_key
  --database-source-secrets=PATH
                           database_sources.password 用の optional JSON secrets map
  --requested-by=NAME      summary に残す requested_by。default: cli
  --help                   このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     bundle_path:string,
 *     mode:string,
 *     target_project_key:string,
 *     database_source_secrets_path:string,
 *     requested_by:string,
 *     error:string
 * }
 */
function app_cli_project_metadata_import_parse_args(array $argv): array
{
    $bundlePath = '';
    $mode = 'preview';
    $targetProjectKey = '';
    $databaseSourceSecretsPath = '';
    $requestedBy = 'cli';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'bundle_path' => '',
                'mode' => $mode,
                'target_project_key' => '',
                'database_source_secrets_path' => '',
                'requested_by' => $requestedBy,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--bundle=')) {
            $bundlePath = trim(substr($argument, strlen('--bundle=')));
            continue;
        }
        if (str_starts_with($argument, '--mode=')) {
            $mode = trim(substr($argument, strlen('--mode=')));
            continue;
        }
        if (str_starts_with($argument, '--target-project-key=')) {
            $targetProjectKey = app_normalize_project_key(substr($argument, strlen('--target-project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--database-source-secrets=')) {
            $databaseSourceSecretsPath = trim(substr($argument, strlen('--database-source-secrets=')));
            continue;
        }
        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = trim(substr($argument, strlen('--requested-by=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'bundle_path' => '',
            'mode' => $mode,
            'target_project_key' => '',
            'database_source_secrets_path' => '',
            'requested_by' => $requestedBy,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($bundlePath === '') {
        return [
            'ok' => false,
            'help' => false,
            'bundle_path' => '',
            'mode' => $mode,
            'target_project_key' => '',
            'database_source_secrets_path' => '',
            'requested_by' => $requestedBy,
            'error' => '有効な --bundle=... を指定してください。',
        ];
    }

    if (!in_array($mode, ['preview', 'apply'], true)) {
        return [
            'ok' => false,
            'help' => false,
            'bundle_path' => '',
            'mode' => $mode,
            'target_project_key' => '',
            'database_source_secrets_path' => '',
            'requested_by' => $requestedBy,
            'error' => '--mode は preview または apply を指定してください。',
        ];
    }

    if ($targetProjectKey !== '' && !app_project_key_is_valid($targetProjectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'bundle_path' => '',
            'mode' => $mode,
            'target_project_key' => '',
            'database_source_secrets_path' => '',
            'requested_by' => $requestedBy,
            'error' => '有効な --target-project-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'bundle_path' => $bundlePath,
        'mode' => $mode,
        'target_project_key' => $targetProjectKey,
        'database_source_secrets_path' => $databaseSourceSecretsPath,
        'requested_by' => $requestedBy,
        'error' => '',
    ];
}
