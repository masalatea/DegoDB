<?php

declare(strict_types=1);

require_once __DIR__ . '/app_local_sqlite_schema.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/shared_contract_manifest.php';

function app_project_output_app_local_persistence_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'app-local-persistence-php';
}

function app_project_output_app_local_package_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'app-local-package-manifest';
}

function app_project_output_app_local_persistence_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_app_local_persistence_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_app_local_package_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_app_local_package_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<string,mixed> $manifestResult
 * @return array<string,string>
 */
function app_project_output_app_local_persistence_build_emitted_files(array $manifestResult): array
{
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $schema = app_local_sqlite_schema_generate($manifest);
    if (!$schema['ok']) {
        throw new RuntimeException($schema['error'] !== '' ? $schema['error'] : 'App-local SQLite schema generation failed.');
    }

    return [
        'app-local-contract.json' => app_project_output_app_local_persistence_json_text($manifest),
        'app-local-summary.json' => app_project_output_app_local_persistence_json_text([
            'ok' => (bool) ($manifestResult['ok'] ?? false),
            'schema' => $schema['summary'],
            'validation' => $manifestResult['validation'] ?? [],
            'compare' => $manifestResult['compare'] ?? [],
            'error' => (string) ($manifestResult['error'] ?? ''),
        ]),
        'schema.sql' => $schema['schema_sql'],
        'AppLocalPersistence.php' => app_project_output_app_local_persistence_php_text($manifest),
        'README.md' => app_project_output_app_local_persistence_readme_text($manifestResult),
    ];
}

/**
 * @param array<string,mixed> $manifestResult
 * @param array<string,mixed> $definition
 * @return array<string,string>
 */
function app_project_output_app_local_package_build_emitted_files(
    array $manifestResult,
    array $definition,
    string $projectKey,
): array {
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $contracts = is_array($manifest['contracts'] ?? null) ? $manifest['contracts'] : [];
    $persistenceFiles = app_project_output_app_local_persistence_build_emitted_files($manifestResult);
    $includedFiles = [];

    foreach (array_keys($persistenceFiles) as $relativePath) {
        $includedFiles[] = [
            'relative_path' => $relativePath,
            'source_output_role' => 'app-local-persistence',
            'required' => true,
        ];
    }

    $packageManifest = [
        'package_manifest_version' => 'app-local-package-manifest-v0',
        'package_runtime' => 'app-local-package-v0',
        'project_key' => app_normalize_project_key($projectKey),
        'source_output_key' => app_normalize_source_output_key((string) ($definition['source_output_key'] ?? '')),
        'artifact_strategy' => 'app-local-package-manifest',
        'contract_manifest_version' => (string) ($manifest['manifest_version'] ?? ''),
        'contract_count' => count($contracts),
        'included_file_count' => count($includedFiles),
        'included_files' => $includedFiles,
        'dependencies' => [
            [
                'source_output_role' => 'app-local-persistence',
                'artifact_strategy' => 'app-local-persistence-php',
                'required' => true,
            ],
        ],
        'deferred_scope' => [
            'native_ios_android_packaging',
            'flutter_output',
            'installer_signing',
            'remote_sync_transport',
            'conflict_resolution',
            'background_scheduler',
            'visual_builder',
        ],
    ];

    $summary = [
        'ok' => (bool) ($manifestResult['ok'] ?? false),
        'package_manifest_version' => $packageManifest['package_manifest_version'],
        'project_key' => $packageManifest['project_key'],
        'source_output_key' => $packageManifest['source_output_key'],
        'contract_count' => $packageManifest['contract_count'],
        'included_file_count' => $packageManifest['included_file_count'],
        'blockers' => [],
        'error' => (string) ($manifestResult['error'] ?? ''),
    ];

    return [
        'app-local-package-manifest.json' => app_project_output_app_local_persistence_json_text($packageManifest),
        'app-local-package-summary.json' => app_project_output_app_local_persistence_json_text($summary),
        'README.md' => app_project_output_app_local_package_readme_text($packageManifest),
    ];
}

/**
 * @param array<mixed> $payload
 */
function app_project_output_app_local_persistence_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('App-local persistence JSON generation failed.');
    }

    return $json . PHP_EOL;
}

/**
 * @param array<string,mixed> $manifest
 */
function app_project_output_app_local_persistence_php_text(array $manifest): string
{
    $manifestJson = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($manifestJson) || $manifestJson === '') {
        throw new RuntimeException('App-local persistence PHP manifest generation failed.');
    }

    $lines = [
        '<?php',
        '',
        'declare(strict_types=1);',
        '',
        '// Generated from shared contract manifest. Do not edit directly.',
        '// Requires app_local_sqlite_schema.php and app_local_sqlite_dbaccess.php to be loaded by the runtime.',
        '',
    ];

    foreach (($manifest['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $entity = is_array($contract['entity'] ?? null) ? $contract['entity'] : [];
        $className = app_project_output_app_local_persistence_class_name(
            (string) ($entity['generated_name'] ?? $contract['contract_key'] ?? ''),
        );
        $contractKey = (string) ($contract['contract_key'] ?? '');

        $lines[] = 'final class ' . $className;
        $lines[] = '{';
        $lines[] = "    private const CONTRACT_KEY = '" . app_project_output_app_local_persistence_php_string($contractKey) . "';";
        $lines[] = '';
        $lines[] = '    public static function manifest(): array';
        $lines[] = '    {';
        $lines[] = "        return json_decode('" . app_project_output_app_local_persistence_php_string($manifestJson) . "', true);";
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public static function applySchema(PDO $pdo): array';
        $lines[] = '    {';
        $lines[] = '        $schema = app_local_sqlite_schema_generate(self::manifest());';
        $lines[] = "        if (!\$schema['ok']) {";
        $lines[] = '            return $schema;';
        $lines[] = '        }';
        $lines[] = "        return app_local_sqlite_schema_apply_to_pdo(\$pdo, \$schema['schema_sql']);";
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public static function save(PDO $pdo, array $dto, array $localMetadata = []): array';
        $lines[] = '    {';
        $lines[] = '        return app_local_sqlite_dbaccess_save_dto($pdo, self::manifest(), self::CONTRACT_KEY, $dto, $localMetadata);';
        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public static function read(PDO $pdo, array $keyDto): array';
        $lines[] = '    {';
        $lines[] = '        return app_local_sqlite_dbaccess_read_dto($pdo, self::manifest(), self::CONTRACT_KEY, $keyDto);';
        $lines[] = '    }';
        $lines[] = '}';
        $lines[] = '';
    }

    return implode("\n", $lines);
}

function app_project_output_app_local_persistence_class_name(string $name): string
{
    $candidate = preg_replace('/[^A-Za-z0-9_]+/', '', trim($name));
    if (!is_string($candidate) || $candidate === '') {
        $candidate = 'Contract';
    }
    if (preg_match('/^[A-Za-z_]/', $candidate) !== 1) {
        $candidate = 'Contract' . $candidate;
    }
    if (!str_ends_with($candidate, 'AppLocalPersistence')) {
        $candidate .= 'AppLocalPersistence';
    }

    return $candidate;
}

function app_project_output_app_local_persistence_php_string(string $value): string
{
    return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
}

/**
 * @param array<string,mixed> $manifestResult
 */
function app_project_output_app_local_persistence_readme_text(array $manifestResult): string
{
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $contracts = is_array($manifest['contracts'] ?? null) ? $manifest['contracts'] : [];

    return implode("\n", [
        '# App-local Persistence',
        '',
        'Generated App-local persistence artifact from the shared contract manifest.',
        '',
        '- `schema.sql` contains the generated App-local SQLite schema.',
        '- `app-local-contract.json` contains the manifest used by the artifact.',
        '- `AppLocalPersistence.php` contains manifest-backed wrapper classes for apply / save / read.',
        '- The PHP wrappers expect the Mtool App-local runtime helpers to be loaded.',
        '- Do not hand-edit generated files; update canonical Mtool metadata instead.',
        '',
        'Contract count: ' . count($contracts),
        'Status: ' . ((bool) ($manifestResult['ok'] ?? false) ? 'ok' : 'failed'),
        '',
    ]);
}

/**
 * @param array<string,mixed> $packageManifest
 */
function app_project_output_app_local_package_readme_text(array $packageManifest): string
{
    return implode("\n", [
        '# App-local Package Manifest',
        '',
        'Generated package manifest for the App-local runtime boundary.',
        '',
        '- `app-local-package-manifest.json` records package metadata and included generated files.',
        '- `app-local-package-summary.json` records the package readiness summary.',
        '- This first slice does not generate native installers, remote transport, or conflict resolution behavior.',
        '- Do not hand-edit generated files; update canonical Mtool metadata instead.',
        '',
        'Project: ' . (string) ($packageManifest['project_key'] ?? ''),
        'Included files: ' . (string) ($packageManifest['included_file_count'] ?? 0),
        '',
    ]);
}

/**
 * @param array{
 *     source_output_key:string,
 *     program_language:string,
 *     artifact_strategy:string,
 *     runtime_source_relative_path:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{relative_path:string,size:int}>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_app_local_persistence_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_app_local_persistence_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Unsupported App-local persistence artifact strategy.',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'php') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'App-local persistence artifact currently supports php only.',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_app_local_persistence_default_runtime_source_relative_path(
            $projectKey,
            (string) ($definition['source_output_key'] ?? ''),
        );
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path is invalid.',
        ];
    }

    $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
    if (!$manifestResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $manifestResult['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);

    try {
        $files = app_project_output_app_local_persistence_build_emitted_files($manifestResult);
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($files as $relativePath => $contents) {
            app_project_output_write_text_file($runtimeSourceRoot . '/' . $relativePath, $contents);
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Failed to create App-local persistence staging tree: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}

/**
 * @param array{
 *     source_output_key:string,
 *     program_language:string,
 *     artifact_strategy:string,
 *     runtime_source_relative_path:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{relative_path:string,size:int}>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_app_local_package_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_app_local_package_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Unsupported App-local package artifact strategy.',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'json') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'App-local package artifact currently supports json only.',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_app_local_package_default_runtime_source_relative_path(
            $projectKey,
            (string) ($definition['source_output_key'] ?? ''),
        );
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path is invalid.',
        ];
    }

    $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
    if (!$manifestResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $manifestResult['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);

    try {
        $files = app_project_output_app_local_package_build_emitted_files($manifestResult, $definition, $projectKey);
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($files as $relativePath => $contents) {
            app_project_output_write_text_file($runtimeSourceRoot . '/' . $relativePath, $contents);
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Failed to create App-local package staging tree: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}
