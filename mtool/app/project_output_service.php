<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/project_output_ai_context_generator.php';
require_once __DIR__ . '/project_output_db_access_generator.php';
require_once __DIR__ . '/project_output_data_class_generator.php';
require_once __DIR__ . '/project_output_html_module_generator.php';
require_once __DIR__ . '/project_output_legacy_source_generator.php';
require_once __DIR__ . '/project_output_managed_operation_generator.php';
require_once __DIR__ . '/project_output_openapi_generator.php';
require_once __DIR__ . '/project_output_proxy_generator.php';
require_once __DIR__ . '/project_output_runtime_generator.php';
require_once __DIR__ . '/project_output_app_local_persistence_generator.php';
require_once __DIR__ . '/project_output_shared_contract_generator.php';
require_once __DIR__ . '/project_output_typescript_dto_generator.php';

function app_project_output_runtime_source_relative_path(): string
{
    return app_runtime_storage_runtime_dbclasses_relative_path();
}

function app_project_output_workspace_root(): string
{
    return app_runtime_storage_repo_root();
}

function app_project_output_runtime_project_key(): string
{
    return 'MTOOL';
}

function app_project_output_default_relative_path(string $projectKey, string $sourceOutputKey = ''): string
{
    return app_runtime_storage_work_repo_relative_path(
        app_runtime_storage_work_source_outputs_relative_path($projectKey, $sourceOutputKey),
    );
}

function app_project_output_default_temp_relative_path(string $projectKey, string $sourceOutputKey = ''): string
{
    return app_runtime_storage_work_repo_relative_path(
        app_runtime_storage_source_output_temp_relative_path($projectKey, $sourceOutputKey),
    );
}

function app_project_output_default_source_output_key(): string
{
    return 'RUNTIME-DBCLASSES';
}

function app_project_output_customization_model(string $artifactStrategy = ''): string
{
    return match ($artifactStrategy) {
        'canonical-dbaccess-php' => 'generated-wrapper-base-tree',
        'canonical-dataclass-php' => 'generated-wrapper-base-tree',
        'shared-contract-json',
        'app-local-persistence-php',
        'managed-operation-docs-md',
        'shared-contract-typescript',
        'openapi-json',
        'html-module-catalog',
        'legacy-directory-mirror',
        'ai-context-md',
        'modernization-audit-md' => 'mirrored-source-with-companion-notes',
        default => 'base-custom-wrapper-layer',
    };
}

function app_project_output_strategy_uses_layered_runtime_bundle(string $artifactStrategy): bool
{
    return $artifactStrategy === 'generated-bootstrap-dbclasses';
}

function app_project_output_custom_layer_relative_path(string $projectKey, string $sourceOutputKey): string
{
    return app_runtime_storage_custom_source_outputs_relative_path($projectKey, $sourceOutputKey);
}

function app_project_output_custom_layer_workspace_root(string $projectKey, string $sourceOutputKey): string
{
    return app_runtime_storage_custom_source_outputs_root($projectKey, $sourceOutputKey);
}

/**
 * @param array{
 *     artifact_strategy:string
 * } $definition
 * @return list<string>
 */
function app_project_output_custom_layer_entrypoints(array $definition): array
{
    return match ($definition['artifact_strategy']) {
        'generated-bootstrap-dbclasses' => [
            'bootstrap.php',
            'data-*.php',
            'dbaccess-*.php',
            'helpers/',
            'mappers/',
            'services/',
            'policies/',
        ],
        'canonical-dbaccess-php',
        'canonical-dataclass-php' => [
            'README.md',
        ],
        'shared-contract-json' => [
            'README.md',
        ],
        'shared-contract-typescript' => [
            'README.md',
        ],
        'app-local-persistence-php' => [
            'README.md',
        ],
        'managed-operation-docs-md' => [
            'README.md',
        ],
        'openapi-json' => [
            'README.md',
        ],
        'ai-context-md' => [
            'README.md',
        ],
        'modernization-audit-md' => [
            'README.md',
        ],
        'html-module-catalog' => [
            'README.md',
        ],
        'legacy-directory-mirror' => [
            'README.md',
        ],
        'custom-proxy-server' => [
            'bootstrap.php',
            'handlers/*.php',
            'helpers/',
            'mappers/',
            'services/',
            'policies/',
        ],
        'single-proxy-server' => [
            'bootstrap.php',
            'handlers/*.php',
            'helpers/',
            'mappers/',
            'services/',
            'policies/',
        ],
        'single-proxy-client' => [
            'ClientExtensions.cs',
            'collaborators/',
            'mappers/',
            'services/',
            'policies/',
        ],
        'custom-proxy-client' => [
            'ClientExtensions.cs',
            'collaborators/',
            'mappers/',
            'services/',
            'policies/',
        ],
        default => [
            'README.md',
        ],
    };
}

/**
 * @param array{
 *     artifact_strategy:string
 * } $definition
 * @return list<string>
 */
function app_project_output_custom_layer_scaffold_relative_paths(array $definition): array
{
    return match ($definition['artifact_strategy']) {
        'canonical-dbaccess-php',
        'canonical-dataclass-php',
        'shared-contract-json',
        'shared-contract-typescript',
        'app-local-persistence-php',
        'managed-operation-docs-md',
        'openapi-json',
        'ai-context-md',
        'modernization-audit-md',
        'html-module-catalog',
        'legacy-directory-mirror' => ['README.md'],
        'single-proxy-client', 'custom-proxy-client' => ['README.md', 'ClientExtensions.cs'],
        default => ['README.md', 'bootstrap.php'],
    };
}

/**
 * @return array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_output_local_default_source_output(string $projectKey): array
{
    $defaults = app_source_output_form_defaults();
    $defaults['source_output_key'] = app_project_output_default_source_output_key();
    $defaults['name'] = app_normalize_project_key($projectKey) . ' Runtime DBClasses';
    $defaults['source_output_dir'] = app_project_output_default_relative_path(
        $projectKey,
        $defaults['source_output_key'],
    );
    $defaults['source_temp_output_dir'] = app_project_output_default_temp_relative_path(
        $projectKey,
        $defaults['source_output_key'],
    );
    $defaults['runtime_source_relative_path'] = app_project_output_runtime_source_relative_path();
    $defaults['artifact_strategy'] = 'generated-bootstrap-dbclasses';
    $defaults['target_binding_type'] = 'runtime';
    $defaults['output_archive_format'] = 'tar.gz';
    $defaults['source_output_list_order'] = '10';
    $defaults['source_of_truth'] = 'bootstrap-default';

    return $defaults;
}

/**
 * @param array<string,mixed> $sourceOutput
 * @return array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_output_merge_source_output_definition(string $projectKey, array $sourceOutput): array
{
    $merged = app_project_output_local_default_source_output($projectKey);

    foreach (array_keys($merged) as $field) {
        if (!array_key_exists($field, $sourceOutput)) {
            continue;
        }

        $value = $sourceOutput[$field];
        if (is_string($value)) {
            $merged[$field] = $value;
        }
    }

    $merged['source_output_key'] = app_normalize_source_output_key($merged['source_output_key']);
    $merged['runtime_source_relative_path'] = app_runtime_storage_canonical_generated_relative_path(
        $merged['runtime_source_relative_path'],
    );

    return $merged;
}

function app_project_output_storage_root(array $app, string $projectKey): string
{
    return app_runtime_storage_generated_source_outputs_root($app, $projectKey);
}

function app_project_output_artifact_key_is_valid(string $artifactKey): bool
{
    return preg_match('/^[0-9]{8}-[0-9]{6}-[a-f0-9]{8}$/', $artifactKey) === 1;
}

function app_project_output_new_artifact_key(): string
{
    return date('Ymd-His') . '-' . bin2hex(random_bytes(4));
}

function app_project_output_normalize_requested_by(string $requestedBy): string
{
    $normalized = preg_replace('/\s+/', ' ', trim($requestedBy));
    if (!is_string($normalized) || $normalized === '') {
        return 'system';
    }

    if (strlen($normalized) > 128) {
        return substr($normalized, 0, 128);
    }

    return $normalized;
}

function app_project_output_archive_basename(string $projectKey, string $artifactKey, string $sourceOutputKey = ''): string
{
    $base = strtolower(app_normalize_project_key($projectKey)) . '-source-output';
    $normalizedSourceOutputKey = strtolower(trim($sourceOutputKey));
    if ($normalizedSourceOutputKey !== '') {
        $normalizedSourceOutputKey = preg_replace('/[^a-z0-9-]+/', '-', $normalizedSourceOutputKey) ?? $normalizedSourceOutputKey;
        $normalizedSourceOutputKey = trim($normalizedSourceOutputKey, '-');
        if ($normalizedSourceOutputKey !== '') {
            $base .= '-' . $normalizedSourceOutputKey;
        }
    }

    return $base . '-' . $artifactKey;
}

function app_project_output_relative_path_is_safe(string $value): bool
{
    return $value !== ''
        && !str_starts_with($value, '/')
        && !str_contains($value, '..')
        && preg_match('/^[A-Za-z0-9._\\/-]+$/', $value) === 1;
}

function app_project_output_workspace_path_from_relative(string $relativePath): string
{
    $normalizedRelativePath = str_replace('\\', '/', trim($relativePath));
    if (!app_project_output_relative_path_is_safe($normalizedRelativePath)) {
        throw new RuntimeException('relative path の形式が不正です: ' . $relativePath);
    }

    return app_project_output_workspace_root() . '/' . $normalizedRelativePath;
}

/**
 * @param array{
 *     source_output_dir:string
 * } $sourceOutput
 * @return array{
 *     ok:bool,
 *     relative_path:string,
 *     root_path:string,
 *     exists:bool,
 *     file_count:int,
 *     total_bytes:int,
 *     error:string
 * }
 */
function app_project_output_output_root_status(array $sourceOutput): array
{
    $relativePath = str_replace('\\', '/', trim((string) ($sourceOutput['source_output_dir'] ?? '')));
    if ($relativePath === '') {
        return [
            'ok' => true,
            'relative_path' => '',
            'root_path' => '',
            'exists' => false,
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => '',
        ];
    }

    try {
        $rootPath = app_project_output_workspace_path_from_relative($relativePath);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'relative_path' => $relativePath,
            'root_path' => '',
            'exists' => false,
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => $throwable->getMessage(),
        ];
    }

    if (!file_exists($rootPath)) {
        return [
            'ok' => true,
            'relative_path' => $relativePath,
            'root_path' => $rootPath,
            'exists' => false,
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => '',
        ];
    }

    if (!is_dir($rootPath)) {
        return [
            'ok' => false,
            'relative_path' => $relativePath,
            'root_path' => $rootPath,
            'exists' => false,
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => 'publish 先 path が directory ではありません。',
        ];
    }

    $scanResult = app_project_output_scan_tree($rootPath);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'relative_path' => $relativePath,
            'root_path' => $rootPath,
            'exists' => true,
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'relative_path' => $relativePath,
        'root_path' => $rootPath,
        'exists' => true,
        'file_count' => count($scanResult['files']),
        'total_bytes' => $scanResult['total_bytes'],
        'error' => '',
    ];
}

/**
 * @param array{
 *     bundle_root:string,
 *     runtime_source_relative_path:string
 * } $artifact
 */
function app_project_output_artifact_bundle_runtime_root(array $artifact): string
{
    $bundleRoot = $artifact['bundle_root'];
    $runtimeSourceRelativePath = str_replace('\\', '/', trim($artifact['runtime_source_relative_path']));
    if ($bundleRoot === '' || !is_dir($bundleRoot)) {
        throw new RuntimeException('artifact bundle root が見つかりません。');
    }

    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        throw new RuntimeException('artifact runtime source path の形式が不正です。');
    }

    return $bundleRoot . '/' . $runtimeSourceRelativePath;
}

/**
 * @return array{
 *     ok:bool,
 *     files:list<array{
 *         relative_path:string,
 *         size:int
 *     }>,
 *     total_bytes:int,
 *     error:string
 * }
 */
function app_project_output_scan_tree(string $root): array
{
    $maxAttempts = 3;
    $lastError = 'runtime source directory が見つかりません。';

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        // Sample/reference checks often delete and recreate the same tree immediately before scanning it.
        // Retry briefly so bind-mounted work trees can settle after recreation.
        clearstatcache(true);
        $resolvedRoot = realpath($root);
        $scanRoot = is_string($resolvedRoot) && $resolvedRoot !== ''
            ? str_replace('\\', '/', $resolvedRoot)
            : $root;

        if (!is_dir($scanRoot)) {
            $lastError = 'runtime source directory が見つかりません。';

            if ($attempt < $maxAttempts) {
                usleep(50000);
                continue;
            }

            return [
                'ok' => false,
                'files' => [],
                'total_bytes' => 0,
                'error' => $lastError,
            ];
        }

        $files = [];
        $totalBytes = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS),
            );

            /** @var SplFileInfo $fileInfo */
            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $pathname = $fileInfo->getPathname();
                $relativePath = substr($pathname, strlen($scanRoot) + 1);
                if (!is_string($relativePath) || $relativePath === '') {
                    continue;
                }

                $normalizedRelativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                $size = (int) $fileInfo->getSize();

                $files[] = [
                    'relative_path' => $normalizedRelativePath,
                    'size' => $size,
                ];
                $totalBytes += $size;
            }
        } catch (Throwable $throwable) {
            $lastError = 'runtime source directory の走査に失敗しました: ' . $throwable->getMessage();

            if ($attempt < $maxAttempts) {
                usleep(50000);
                continue;
            }

            return [
                'ok' => false,
                'files' => [],
                'total_bytes' => 0,
                'error' => $lastError,
            ];
        }

        usort(
            $files,
            static fn (array $left, array $right): int => strcmp($left['relative_path'], $right['relative_path']),
        );

        return [
            'ok' => true,
            'files' => $files,
            'total_bytes' => $totalBytes,
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'files' => [],
        'total_bytes' => 0,
        'error' => $lastError,
    ];
}

function app_project_output_ensure_directory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('directory を作成できませんでした: ' . $directory);
    }
}

/**
 * @param list<array{
 *     relative_path:string,
 *     size:int
 * }> $files
 */
function app_project_output_copy_tree(string $sourceRoot, string $destinationRoot, array $files): void
{
    app_project_output_ensure_directory($destinationRoot);

    foreach ($files as $file) {
        $relativePath = $file['relative_path'];
        $sourcePath = $sourceRoot . '/' . $relativePath;
        $destinationPath = $destinationRoot . '/' . $relativePath;

        app_project_output_ensure_directory(dirname($destinationPath));

        if (!copy($sourcePath, $destinationPath)) {
            throw new RuntimeException('runtime source file のコピーに失敗しました: ' . $relativePath);
        }
    }
}

function app_project_output_runtime_base_relative_path(): string
{
    return '_base';
}

function app_project_output_runtime_wrapper_relative_path(): string
{
    return '_wrappers';
}

function app_project_output_runtime_loader_relative_path(): string
{
    return '_runtime_loader.php';
}

function app_project_output_runtime_layered_class_kind(string $relativePath): string
{
    $normalizedRelativePath = str_replace('\\', '/', $relativePath);
    $basename = basename($normalizedRelativePath);

    if ($normalizedRelativePath !== $basename) {
        return '';
    }

    if (preg_match('/^dbaccess-.+\.php$/', $basename) === 1) {
        return 'dbaccess';
    }
    if (preg_match('/^data-.+\.php$/', $basename) === 1) {
        return 'data';
    }

    return '';
}

function app_project_output_runtime_is_layered_class_relative_path(string $relativePath): bool
{
    return app_project_output_runtime_layered_class_kind($relativePath) !== '';
}

function app_project_output_runtime_dbaccess_base_relative_path(string $relativePath): string
{
    $basename = preg_replace('/\.php$/', 'Base.php', basename($relativePath));
    if (!is_string($basename) || $basename === '') {
        throw new RuntimeException('runtime dbaccess base path の生成に失敗しました: ' . $relativePath);
    }

    return 'base/' . $basename;
}

function app_project_output_runtime_data_base_relative_path(string $relativePath): string
{
    $basename = preg_replace('/\.php$/', 'Base.php', basename($relativePath));
    if (!is_string($basename) || $basename === '') {
        throw new RuntimeException('runtime data base path の生成に失敗しました: ' . $relativePath);
    }

    return 'base/' . $basename;
}

function app_project_output_runtime_is_canonical_plain_data_file(string $contents, string $sourcePath): bool
{
    if (!str_contains($contents, '// Generated from canonical DB Access metadata.')) {
        return false;
    }

    $dataFileInfo = app_project_output_runtime_bootstrap_data_file_info($sourcePath);

    return $dataFileInfo['ok'] && $dataFileInfo['is_plain_candidate'];
}

/**
 * @param list<array{
 *     relative_path:string,
 *     size:int
 * }> $files
 * @return array{
 *     passthrough_files:list<array{
 *         relative_path:string,
 *         source_path:string,
 *         contents:string
 *     }>,
 *     layered_files:list<array{
 *         relative_path:string,
 *         source_path:string,
 *         contents:string,
 *         class_kind:string,
 *         original_class:string,
 *         base_class:string
 *     }>
 * }
 */
function app_project_output_runtime_is_generated_layered_entry_contents(string $contents): bool
{
    return str_contains($contents, 'mtool_runtime_bundle_load_layered_file(')
        || str_contains($contents, 'mtool_runtime_bundle_load_custom_wrapper(');
}

function app_project_output_runtime_build_plan(string $sourceRoot, array $files): array
{
    $passthroughFiles = [];
    $layeredFiles = [];
    $upgradeLayeredDataFiles = [];
    $suppressedRelativePaths = [];

    foreach ($files as $file) {
        $relativePath = (string) ($file['relative_path'] ?? '');
        if ($relativePath === '' || app_project_output_runtime_relative_path_is_excluded($relativePath)) {
            continue;
        }
        if (app_project_output_runtime_layered_class_kind($relativePath) !== 'data') {
            continue;
        }

        $sourcePath = $sourceRoot . '/' . $relativePath;
        $contents = file_get_contents($sourcePath);
        if (!is_string($contents)) {
            throw new RuntimeException('runtime source file の読み込みに失敗しました: ' . $relativePath);
        }
        if (!app_project_output_runtime_is_generated_layered_entry_contents($contents)) {
            continue;
        }

        $bootstrapInfo = app_project_output_runtime_bootstrap_data_file_info($sourcePath);
        if (
            !$bootstrapInfo['ok']
            || (string) ($bootstrapInfo['source_layout'] ?? '') !== 'generated-layered-stub'
        ) {
            continue;
        }

        $legacyMigrationSupport = app_project_output_runtime_data_legacy_migration_support(
            $sourcePath,
            $bootstrapInfo,
        );
        if (
            !($legacyMigrationSupport['supports_legacy_enum_migration'] ?? false)
            && !($legacyMigrationSupport['supports_legacy_default_property_migration'] ?? false)
            && !($legacyMigrationSupport['supports_legacy_method_only_migration'] ?? false)
            && !($legacyMigrationSupport['supports_legacy_wrapper_property_method_migration'] ?? false)
            && !($legacyMigrationSupport['supports_legacy_method_and_enum_migration'] ?? false)
            && !($legacyMigrationSupport['supports_legacy_top_level_declaration_migration'] ?? false)
        ) {
            continue;
        }

        $upgradeLayeredDataFiles[$relativePath] = [
            'relative_path' => $relativePath,
            'source_path' => $sourcePath,
            'contents' => $contents,
            'class_kind' => 'data',
            'original_class' => (string) ($bootstrapInfo['class_name'] ?? ''),
            'base_class' => app_generated_runtime_expected_base_class_name($sourcePath),
        ];
        $suppressedRelativePaths['_base/' . basename($relativePath)] = true;
        $suppressedRelativePaths['_wrappers/' . basename($relativePath)] = true;
    }

    foreach ($files as $file) {
        $relativePath = $file['relative_path'];
        if (app_project_output_runtime_relative_path_is_excluded($relativePath)) {
            continue;
        }
        if (isset($suppressedRelativePaths[$relativePath])) {
            continue;
        }

        $sourcePath = $sourceRoot . '/' . $relativePath;
        $contents = file_get_contents($sourcePath);
        if (!is_string($contents)) {
            throw new RuntimeException('runtime source file の読み込みに失敗しました: ' . $relativePath);
        }
        if ($relativePath === 'autoload_mtool.php') {
            $contents = app_project_output_runtime_filter_autoload_contents($contents);
        }
        if (isset($upgradeLayeredDataFiles[$relativePath])) {
            $layeredFiles[] = $upgradeLayeredDataFiles[$relativePath];
            continue;
        }

        if (!app_project_output_runtime_is_layered_class_relative_path($relativePath)) {
            $passthroughFiles[] = [
                'relative_path' => $relativePath,
                'source_path' => $sourcePath,
                'contents' => $contents,
            ];
            continue;
        }

        if (app_project_output_runtime_is_generated_layered_entry_contents($contents)) {
            $passthroughFiles[] = [
                'relative_path' => $relativePath,
                'source_path' => $sourcePath,
                'contents' => $contents,
            ];
            continue;
        }

        if (!preg_match('/^\s*class\s+([A-Za-z0-9_]+)\b/m', $contents, $matches)) {
            throw new RuntimeException('runtime source class の解析に失敗しました: ' . $relativePath);
        }

        $originalClass = $matches[1];
        $layeredFiles[] = [
            'relative_path' => $relativePath,
            'source_path' => $sourcePath,
            'contents' => $contents,
            'class_kind' => app_project_output_runtime_layered_class_kind($relativePath),
            'original_class' => $originalClass,
            'base_class' => $originalClass . 'Base',
        ];
    }

    return [
        'passthrough_files' => $passthroughFiles,
        'layered_files' => $layeredFiles,
    ];
}

function app_project_output_runtime_transform_base_contents(
    string $contents,
    string $originalClass,
    string $baseClass,
    string $relativePath,
    bool $prependGeneratedBaseHeader = false,
): string {
    $rewritten = preg_replace(
        '/^class\s+' . preg_quote($originalClass, '/') . '\b/m',
        'class ' . $baseClass,
        $contents,
        1,
        $count,
    );

    if (!is_string($rewritten) || $count !== 1) {
        throw new RuntimeException('runtime base class 変換に失敗しました: ' . $relativePath);
    }

    if ($prependGeneratedBaseHeader) {
        $rewritten = preg_replace(
            '/^<\?php\s*\n?/',
            "<?php\n\n"
                . "// AUTO-GENERATED BASE FILE.\n"
                . "// Do not edit this file manually or with AI/Codex.\n"
                . "// Keep customizations in the wrapper/custom class and use this base file exactly as regenerated by the tool.\n\n",
            $rewritten,
            1,
            $headerCount,
        );
        if (!is_string($rewritten) || $headerCount !== 1) {
            throw new RuntimeException('runtime base header の付与に失敗しました: ' . $relativePath);
        }
    }

    return $rewritten;
}

function app_project_output_runtime_stub_text(string $relativePath): string
{
    $exportedRelativePath = var_export($relativePath, true);

    return <<<PHP
<?php

require_once __DIR__ . '/_runtime_loader.php';
mtool_runtime_bundle_load_layered_file({$exportedRelativePath});

?>
PHP;
}

function app_project_output_runtime_default_wrapper_text(
    string $relativePath,
    string $originalClass,
    string $baseClass,
    string $customLayerRelativePath,
): string {
    return <<<PHP
<?php

// Generated wrapper fallback for `{$relativePath}`.
// Override by creating `{$customLayerRelativePath}/{$relativePath}` and extending `{$baseClass}`.

class {$originalClass} extends {$baseClass}
{
}

?>
PHP;
}

function app_project_output_runtime_dbaccess_wrapper_text(
    string $relativePath,
    string $baseRelativePath,
    string $originalClass,
    string $baseClass,
    string $customLayerRelativePath,
): string {
    $exportedRelativePath = var_export($relativePath, true);
    $exportedBaseRelativePath = var_export('/' . ltrim($baseRelativePath, '/'), true);

    return <<<PHP
<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . {$exportedBaseRelativePath};

if (!mtool_runtime_bundle_load_custom_wrapper({$exportedRelativePath})) {
    // Generated wrapper entry for runtime DB Access.
    // Override `{$customLayerRelativePath}/{$relativePath}` and extend `{$baseClass}` for project-specific customizations.

    class {$originalClass} extends {$baseClass}
    {
    }
}

?>
PHP;
}

function app_project_output_runtime_data_wrapper_text(
    string $relativePath,
    string $baseRelativePath,
    string $originalClass,
    string $baseClass,
    string $customLayerRelativePath,
    string $defaultAbove = '',
    string $defaultClassBody = '',
    string $defaultBottom = '',
): string {
    $exportedRelativePath = var_export($relativePath, true);
    $exportedBaseRelativePath = var_export('/' . ltrim($baseRelativePath, '/'), true);
    $aboveSection = app_legacy_data_class_wrapper_top_level_section($defaultAbove);
    $defaultClassBodySection = app_legacy_data_class_wrapper_class_body_section($defaultClassBody);
    $bottomSection = app_legacy_data_class_wrapper_top_level_section($defaultBottom);

    return <<<PHP
<?php

{$aboveSection}require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . {$exportedBaseRelativePath};

if (!mtool_runtime_bundle_load_custom_wrapper({$exportedRelativePath})) {
    // Generated wrapper entry for runtime data class.
    // Override `{$customLayerRelativePath}/{$relativePath}` and extend `{$baseClass}` for project-specific customizations.

    class {$originalClass} extends {$baseClass}
    {
{$defaultClassBodySection}    }
}
{$bottomSection}
?>
PHP;
}

function app_project_output_runtime_loader_text(string $customLayerRelativePath): string
{
    $exportedCustomLayerRelativePath = var_export($customLayerRelativePath, true);
    $baseRelativePath = app_project_output_runtime_base_relative_path();
    $wrapperRelativePath = app_project_output_runtime_wrapper_relative_path();

    return <<<PHP
<?php

function mtool_runtime_bundle_custom_layer_root(): string
{
    return dirname(__DIR__, 2) . '/' . {$exportedCustomLayerRelativePath};
}

function mtool_runtime_bundle_load_custom_bootstrap(): void
{
    static \$loaded = false;
    if (\$loaded) {
        return;
    }

    \$loaded = true;
    \$bootstrapPath = mtool_runtime_bundle_custom_layer_root() . '/bootstrap.php';
    if (is_file(\$bootstrapPath)) {
        require_once \$bootstrapPath;
    }
}

function mtool_runtime_bundle_custom_wrapper_path(string \$relativePath): string
{
    return mtool_runtime_bundle_custom_layer_root() . '/' . \$relativePath;
}

function mtool_runtime_bundle_load_custom_wrapper(string \$relativePath): bool
{
    mtool_runtime_bundle_load_custom_bootstrap();

    \$customWrapperPath = mtool_runtime_bundle_custom_wrapper_path(\$relativePath);
    if (!is_file(\$customWrapperPath)) {
        return false;
    }

    require_once \$customWrapperPath;

    return true;
}

function mtool_runtime_bundle_load_layered_file(string \$relativePath): void
{
    \$runtimeRoot = __DIR__;
    \$basePath = \$runtimeRoot . '/{$baseRelativePath}/' . \$relativePath;
    \$defaultWrapperPath = \$runtimeRoot . '/{$wrapperRelativePath}/' . \$relativePath;

    if (!is_file(\$basePath)) {
        throw new RuntimeException('Missing runtime base file: ' . \$relativePath);
    }

    require_once \$basePath;
    if (mtool_runtime_bundle_load_custom_wrapper(\$relativePath)) {
        return;
    }

    if (!is_file(\$defaultWrapperPath)) {
        throw new RuntimeException('Missing runtime wrapper file: ' . \$relativePath);
    }

    require_once \$defaultWrapperPath;
}

?>
PHP;
}

/**
 * @param list<array{
 *     relative_path:string,
 *     size:int
 * }> $files
 */
function app_project_output_build_layered_runtime_bundle(
    string $sourceRoot,
    string $destinationRoot,
    array $files,
    string $customLayerRelativePath,
): void {
    app_project_output_ensure_directory($destinationRoot);

    $plan = app_project_output_runtime_build_plan($sourceRoot, $files);

    foreach ($plan['passthrough_files'] as $file) {
        $destinationPath = $destinationRoot . '/' . $file['relative_path'];
        app_project_output_ensure_directory(dirname($destinationPath));
        if (file_put_contents($destinationPath, $file['contents']) === false) {
            throw new RuntimeException('runtime passthrough file の保存に失敗しました: ' . $file['relative_path']);
        }
    }

    app_project_output_write_text_file(
        $destinationRoot . '/' . app_project_output_runtime_loader_relative_path(),
        app_project_output_runtime_loader_text($customLayerRelativePath) . PHP_EOL,
    );

    foreach ($plan['layered_files'] as $file) {
        $relativePath = $file['relative_path'];
        $classKind = $file['class_kind'];

        if ($classKind === 'dbaccess') {
            $dbaccessBaseRelativePath = app_project_output_runtime_dbaccess_base_relative_path($relativePath);

            app_project_output_write_text_file(
                $destinationRoot . '/' . $relativePath,
                app_project_output_runtime_dbaccess_wrapper_text(
                    $relativePath,
                    $dbaccessBaseRelativePath,
                    $file['original_class'],
                    $file['base_class'],
                    $customLayerRelativePath,
                ) . PHP_EOL,
            );
            app_project_output_write_text_file(
                $destinationRoot . '/' . $dbaccessBaseRelativePath,
                app_project_output_runtime_transform_base_contents(
                    $file['contents'],
                    $file['original_class'],
                    $file['base_class'],
                    $relativePath,
                    true,
                ),
            );
            continue;
        }

        if (
            $classKind === 'data'
            && app_project_output_runtime_is_canonical_plain_data_file(
                $file['contents'],
                $file['source_path'],
            )
        ) {
            $dataBaseRelativePath = app_project_output_runtime_data_base_relative_path($relativePath);

            app_project_output_write_text_file(
                $destinationRoot . '/' . $relativePath,
                app_project_output_runtime_data_wrapper_text(
                    $relativePath,
                    $dataBaseRelativePath,
                    $file['original_class'],
                    $file['base_class'],
                    $customLayerRelativePath,
                ) . PHP_EOL,
            );
            app_project_output_write_text_file(
                $destinationRoot . '/' . $dataBaseRelativePath,
                app_project_output_runtime_transform_base_contents(
                    $file['contents'],
                    $file['original_class'],
                    $file['base_class'],
                    $relativePath,
                    true,
                ),
            );
            continue;
        }

        if ($classKind === 'data') {
            $bootstrapInfo = app_project_output_runtime_bootstrap_data_file_info($file['source_path']);
            $legacyMigrationSupport = app_project_output_runtime_data_legacy_migration_support(
                $file['source_path'],
                $bootstrapInfo,
            );
            $legacyMigrationInfo = $legacyMigrationSupport['migration_info'];
            $supportsLegacyEnumMigration = (bool) ($legacyMigrationSupport['supports_legacy_enum_migration'] ?? false);
            $supportsLegacyDefaultPropertyMigration = (bool) ($legacyMigrationSupport['supports_legacy_default_property_migration'] ?? false);
            $supportsLegacyMethodOnlyMigration = (bool) ($legacyMigrationSupport['supports_legacy_method_only_migration'] ?? false);
            $supportsLegacyWrapperPropertyMethodMigration = (bool) ($legacyMigrationSupport['supports_legacy_wrapper_property_method_migration'] ?? false);
            $supportsLegacyMethodAndEnumMigration = (bool) ($legacyMigrationSupport['supports_legacy_method_and_enum_migration'] ?? false);
            $supportsLegacyTopLevelDeclarationMigration = (bool) ($legacyMigrationSupport['supports_legacy_top_level_declaration_migration'] ?? false);

            if (
                $supportsLegacyEnumMigration
                || $supportsLegacyDefaultPropertyMigration
                || $supportsLegacyMethodOnlyMigration
                || $supportsLegacyWrapperPropertyMethodMigration
                || $supportsLegacyMethodAndEnumMigration
                || $supportsLegacyTopLevelDeclarationMigration
            ) {
                $dataBaseRelativePath = app_project_output_runtime_data_base_relative_path($relativePath);
                $generatedWrapperBottomSection = (string) ($legacyMigrationInfo['editable_areas']['bottom'] ?? '');
                if ($supportsLegacyTopLevelDeclarationMigration) {
                    $generatedWrapperBottomSection = (string) ($legacyMigrationInfo['generated_wrapper_bottom_section'] ?? '');
                }

                $generatedBaseAdditionalSection = '';
                if ($supportsLegacyEnumMigration || $supportsLegacyMethodAndEnumMigration) {
                    $generatedBaseAdditionalSection = (string) ($legacyMigrationInfo['generated_trailing_section'] ?? '');
                }
                if ($supportsLegacyTopLevelDeclarationMigration) {
                    $generatedBaseAdditionalSection = (string) ($legacyMigrationInfo['generated_base_additional_section'] ?? '');
                }

                app_project_output_write_text_file(
                    $destinationRoot . '/' . $relativePath,
                    app_project_output_runtime_data_wrapper_text(
                        $relativePath,
                        $dataBaseRelativePath,
                        $file['original_class'],
                        $file['base_class'],
                        $customLayerRelativePath,
                        (string) ($legacyMigrationInfo['editable_areas']['above'] ?? ''),
                        (string) ($legacyMigrationInfo['editable_areas']['additional_class_definition'] ?? ''),
                        $generatedWrapperBottomSection,
                    ) . PHP_EOL,
                );
                app_project_output_write_text_file(
                    $destinationRoot . '/' . $dataBaseRelativePath,
                    app_project_output_generated_legacy_data_class_base_php_text(
                        $file['base_class'],
                        (string) ($legacyMigrationInfo['parent_class'] ?? ''),
                        $legacyMigrationInfo['generated_property_names'] ?? [],
                        $generatedBaseAdditionalSection,
                    ) . PHP_EOL,
                );
                continue;
            }
        }

        app_project_output_write_text_file(
            $destinationRoot . '/' . $relativePath,
            app_project_output_runtime_stub_text($relativePath) . PHP_EOL,
        );
        app_project_output_write_text_file(
            $destinationRoot . '/' . app_project_output_runtime_base_relative_path() . '/' . $relativePath,
            app_project_output_runtime_transform_base_contents(
                $file['contents'],
                $file['original_class'],
                $file['base_class'],
                $relativePath,
            ),
        );
        app_project_output_write_text_file(
            $destinationRoot . '/' . app_project_output_runtime_wrapper_relative_path() . '/' . $relativePath,
            app_project_output_runtime_default_wrapper_text(
                $relativePath,
                $file['original_class'],
                $file['base_class'],
                $customLayerRelativePath,
            ) . PHP_EOL,
        );
    }

    app_project_output_runtime_rewrite_autoload_file(
        $destinationRoot . '/autoload_mtool.php',
        $destinationRoot,
    );
}

/**
 * @param array<mixed> $payload
 */
function app_project_output_write_json_file(string $path, array $payload): void
{
    app_project_output_ensure_directory(dirname($path));

    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );

    if (!is_string($json) || $json === '') {
        throw new RuntimeException('manifest JSON の生成に失敗しました。');
    }

    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('manifest file の保存に失敗しました: ' . $path);
    }
}

function app_project_output_set_runtime_generation_manifest_artifact_key(
    string $runtimeRoot,
    string $artifactKey,
): void {
    $normalizedRuntimeRoot = rtrim(str_replace('\\', '/', $runtimeRoot), '/');
    if ($normalizedRuntimeRoot === '') {
        throw new RuntimeException('runtime root が空です。');
    }

    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        throw new RuntimeException('artifact key の形式が不正です。');
    }

    $manifestPath = $normalizedRuntimeRoot . '/_support/runtime-generation-manifest.json';
    if (!is_file($manifestPath)) {
        return;
    }

    $manifest = app_project_output_read_manifest($manifestPath);
    if ($manifest === null) {
        throw new RuntimeException('runtime-generation-manifest.json の読み込みに失敗しました: ' . $manifestPath);
    }

    $manifest['artifact_key'] = $artifactKey;
    app_project_output_write_json_file($manifestPath, $manifest);
}

function app_project_output_write_text_file(string $path, string $contents): void
{
    app_project_output_ensure_directory(dirname($path));

    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('text file の保存に失敗しました: ' . $path);
    }
}

function app_project_output_delete_tree(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            $pathname = $fileInfo->getPathname();
            if ($fileInfo->isDir()) {
                @rmdir($pathname);
                continue;
            }

            @unlink($pathname);
        }
    } catch (Throwable $throwable) {
        // best effort cleanup
    }

    @rmdir($path);
}

function app_project_output_build_tar_archive(string $bundleParentRoot, string $bundleEntryRoot, string $archivePath): void
{
    if (!function_exists('proc_open')) {
        throw new RuntimeException('archive 生成に必要な proc_open が利用できません。');
    }

    $command = [
        'tar',
        '-czf',
        $archivePath,
        '-C',
        $bundleParentRoot,
        $bundleEntryRoot,
    ];

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $pipes = [];
    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('archive 生成プロセスの起動に失敗しました。');
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        $message = trim((string) $stderr);
        if ($message === '') {
            $message = trim((string) $stdout);
        }
        if ($message === '') {
            $message = 'tar command が異常終了しました。';
        }

        throw new RuntimeException('archive 生成に失敗しました: ' . $message);
    }
}

/**
 * @return array{
 *     exists:bool,
 *     relative_path:string,
 *     workspace_root:string,
 *     files:list<array{
 *         relative_path:string,
 *         size:int
 *     }>,
 *     file_count:int,
 *     total_bytes:int,
 *     error:string
 * }
 */
function app_project_output_scan_custom_layer_workspace(string $projectKey, string $sourceOutputKey): array
{
    $relativePath = app_project_output_custom_layer_relative_path($projectKey, $sourceOutputKey);
    $workspaceRoot = app_project_output_custom_layer_workspace_root($projectKey, $sourceOutputKey);

    if (!is_dir($workspaceRoot)) {
        return [
            'exists' => false,
            'relative_path' => $relativePath,
            'workspace_root' => $workspaceRoot,
            'files' => [],
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => '',
        ];
    }

    $scanResult = app_project_output_scan_tree($workspaceRoot);
    if (!$scanResult['ok']) {
        return [
            'exists' => true,
            'relative_path' => $relativePath,
            'workspace_root' => $workspaceRoot,
            'files' => [],
            'file_count' => 0,
            'total_bytes' => 0,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'exists' => true,
        'relative_path' => $relativePath,
        'workspace_root' => $workspaceRoot,
        'files' => $scanResult['files'],
        'file_count' => count($scanResult['files']),
        'total_bytes' => $scanResult['total_bytes'],
        'error' => '',
    ];
}

/**
 * @param array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $definition
 */
function app_project_output_custom_layer_scaffold_text(
    string $projectKey,
    array $definition,
    string $customLayerRelativePath,
): string {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_source_output_key($definition['source_output_key']);
    $runtimeSourceRelativePath = $definition['runtime_source_relative_path'];
    $entrypoints = app_project_output_custom_layer_entrypoints($definition);
    $entrypointLines = [];
    foreach ($entrypoints as $entrypoint) {
        $entrypointLines[] = '- `' . $entrypoint . '`';
    }
    $entrypointSource = implode("\n", $entrypointLines);

    if ($definition['artifact_strategy'] === 'custom-proxy-server') {
        return <<<TEXT
# Custom Layer

This directory is the user-owned custom layer for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated proxy server files are bundled under `{$runtimeSourceRelativePath}`.
- Do not edit generated endpoint or handler files directly.
- Keep project-owned policy / helper / handler override code in this directory.

Runtime artifact layout:

- `{$runtimeSourceRelativePath}/proxyserver-*.php` contains generated endpoint entrypoints.
- `{$runtimeSourceRelativePath}/_base/handlers/*.php` contains generated base handlers.
- `{$runtimeSourceRelativePath}/_wrappers/handlers/*.php` contains generated default wrapper handlers.
- `{$runtimeSourceRelativePath}/_support/` contains copied DB runtime subset and proxy loader files.

Custom layer entry points:

{$entrypointSource}

Guidance:

- Use `bootstrap.php` to `require_once` helper / collaborator files needed by custom handlers.
- To override a generated handler, create the same relative path here and extend the corresponding `*Base` class.
- Example: `handlers/DBImportProxyHandler.php` with `class DBImportProxyHandler extends DBImportProxyHandlerBase`.
- `GetFunc` / `ProjectTokenOrGetFunc` / `LoginCookieToken` が必要な場合は wrapper handler で `authorizeByGetFunction()` または `authorizeByLoginCookieToken()` を実装する。
- Keep DTO / data classes thin; put project-specific branching into handlers, policies, or collaborators.

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are copied into the artifact.
- If it does not exist yet, strategy-specific scaffold files are bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'single-proxy-server') {
        return <<<TEXT
# Custom Layer

This directory is the user-owned custom layer for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated single-function proxy server files are bundled under `{$runtimeSourceRelativePath}`.
- Do not edit generated endpoint or handler files directly.
- Keep project-owned policy / helper / handler override code in this directory.

Runtime artifact layout:

- `{$runtimeSourceRelativePath}/proxyserver-*.php` contains generated endpoint entrypoints.
- `{$runtimeSourceRelativePath}/_base/handlers/*.php` contains generated base handlers.
- `{$runtimeSourceRelativePath}/_wrappers/handlers/*.php` contains generated default wrapper handlers.
- `{$runtimeSourceRelativePath}/_support/` contains copied DB runtime subset and proxy loader files.

Custom layer entry points:

{$entrypointSource}

Guidance:

- Request / response payloads stay function-local and direct; do not reintroduce multi-step orchestration here.
- Use `bootstrap.php` to `require_once` helper / collaborator files needed by custom handlers.
- To override a generated handler, create the same relative path here and extend the corresponding `*Base` class.
- `GetFunc` / `ProjectTokenOrGetFunc` / `LoginCookieToken` が必要な場合は wrapper handler で `authorizeByGetFunction()` または `authorizeByLoginCookieToken()` を実装する。
- Keep DTO / data classes thin; put branching into handlers, policies, or collaborators.

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are copied into the artifact.
- If it does not exist yet, strategy-specific scaffold files are bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'custom-proxy-client') {
        return <<<TEXT
# Custom Layer

This directory is the user-owned companion source area for `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated proxy client files are bundled under `{$runtimeSourceRelativePath}`.
- Do not edit generated client / request / result files directly.
- Keep project-owned decorators, adapters, collaborators, and mapping code in this directory.

Runtime artifact layout:

- `{$runtimeSourceRelativePath}/*ProxyClientBase.cs` contains the generated transport base client.
- `{$runtimeSourceRelativePath}/*ProxyClient.cs` contains the generated wrapper client.
- `{$runtimeSourceRelativePath}/*RequestParams.cs` and `*Result*.cs` stay as thin DTO / request / result classes.

Custom layer entry points:

{$entrypointSource}

Guidance:

- Prefer decorators / collaborators around the generated client over rewriting generated files.
- Put translation, retry policy, and orchestration logic in companion classes under this directory.
- Keep DTO classes thin; move behavior into collaborators or service objects.

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are copied into the artifact as companion source.
- If it does not exist yet, strategy-specific scaffold files are bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'single-proxy-client') {
        return <<<TEXT
# Custom Layer

This directory is the user-owned companion source area for `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated single-function proxy client files are bundled under `{$runtimeSourceRelativePath}`.
- Do not edit generated client / request / result files directly.
- Keep project-owned decorators, adapters, collaborators, and mapping code in this directory.

Runtime artifact layout:

- `{$runtimeSourceRelativePath}/*SingleProxyClientBase.cs` contains the generated transport base client.
- `{$runtimeSourceRelativePath}/*SingleProxyClient.cs` contains the generated wrapper client.
- `{$runtimeSourceRelativePath}/*RequestParams.cs` and `*ProxyResult.cs` stay as thin DTO / request / result classes.

Custom layer entry points:

{$entrypointSource}

Guidance:

- Prefer decorators / collaborators around the generated client over rewriting generated files.
- Keep request / result classes direct per function. Cross-function orchestration belongs in collaborators or service objects.
- Keep DTO classes thin; move behavior into collaborators or service objects.

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are copied into the artifact as companion source.
- If it does not exist yet, strategy-specific scaffold files are bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'canonical-dbaccess-php') {
        return <<<TEXT
# Companion Notes

This directory is an optional companion area for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated DBAccess source is published under `{$runtimeSourceRelativePath}`.
- `{$runtimeSourceRelativePath}/dbaccess-*.php` are the generated wrapper entry files.
- `{$runtimeSourceRelativePath}/base/*Base.php` are the generated parent classes.
- current raw output is disposable; durable sample baselines belong under the corresponding `sample/<category>/<pack>/reference/` tree.

Companion files:

{$entrypointSource}

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are bundled as companion notes.
- If it does not exist yet, this scaffold `README.md` is bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'canonical-dataclass-php') {
        return <<<TEXT
# Companion Notes

This directory is an optional companion area for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated DTO source is published under `{$runtimeSourceRelativePath}`.
- `{$runtimeSourceRelativePath}/data-*.php` are the generated wrapper entry files.
- `{$runtimeSourceRelativePath}/base/*Base.php` are the generated parent classes.
- current raw output is disposable; durable sample baselines belong under the corresponding `sample/<category>/<pack>/reference/` tree.

Companion files:

{$entrypointSource}

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are bundled as companion notes.
- If it does not exist yet, this scaffold `README.md` is bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'openapi-json') {
        return <<<TEXT
# Companion Notes

This directory is an optional companion area for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated OpenAPI files are published under `{$runtimeSourceRelativePath}`.
- `{$runtimeSourceRelativePath}/openapi.json` is the generated spec for single-function proxy targets.
- `{$runtimeSourceRelativePath}/build-plan.json` records the bound function/auth summary used to emit the spec.
- current raw output is disposable; durable review notes belong in docs or sample references.

Companion files:

{$entrypointSource}

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are bundled as companion notes.
- If it does not exist yet, this scaffold `README.md` is bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'ai-context-md') {
        return <<<TEXT
# Companion Notes

This directory is an optional companion area for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated AI context files are published under `{$runtimeSourceRelativePath}`.
- `{$runtimeSourceRelativePath}/README.md` explains the generated context package.
- `{$runtimeSourceRelativePath}/schema-context.json` is the machine-readable context companion.
- AI is a reader / consumer of this output; the files are authored by DegoDB / Mtool generator code.
- current raw output is disposable; durable review notes belong in docs or sample references.

Companion files:

{$entrypointSource}

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are bundled as companion notes.
- If it does not exist yet, this scaffold `README.md` is bundled instead.
TEXT;
    }

    if ($definition['artifact_strategy'] === 'modernization-audit-md') {
        return <<<TEXT
# Companion Notes

This directory is an optional companion area for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Generated modernization audit files are published under `{$runtimeSourceRelativePath}`.
- `{$runtimeSourceRelativePath}/README.md` explains the generated diagnostic package.
- `{$runtimeSourceRelativePath}/modernization-audit.md` is the deterministic human-readable audit.
- `{$runtimeSourceRelativePath}/audit-summary.json` is the machine-readable audit companion.
- This output is read-only and does not modify generated runtime code.
- current raw output is disposable; durable review notes belong in docs or sample references.

Companion files:

{$entrypointSource}

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are bundled as companion notes.
- If it does not exist yet, this scaffold `README.md` is bundled instead.
TEXT;
    }

    if (
        app_project_output_html_module_strategy_is_supported($definition['artifact_strategy'])
        || $definition['artifact_strategy'] === 'legacy-directory-mirror'
    ) {
        $sourceTemplateDir = trim((string) ($definition['source_template_dir'] ?? ''));
        $isHtmlModuleCatalog = app_project_output_html_module_strategy_is_supported($definition['artifact_strategy']);
        $mirroredSourceCaption = $isHtmlModuleCatalog
            ? 'Canonical html module files'
            : 'Curated seed source files';
        $mirroredSourceEditCaption = $isHtmlModuleCatalog
            ? 'module files'
            : 'seed files';
        $runtimeLayoutCaption = $isHtmlModuleCatalog
            ? 'contains the mirrored canonical html module tree.'
            : 'contains the mirrored legacy source tree or a workspace placeholder.';
        $bridgeGuidance = $isHtmlModuleCatalog
            ? '- Treat this artifact as the canonical html module bridge while actual html generators are still being rebuilt.'
            : '- Treat this artifact as a bridge for parity verification while canonical modules are still being rebuilt.';
        $sourcePolicyGuidance = $isHtmlModuleCatalog
            ? '- The generator reads only curated html module roots resolved from the catalog ref; it must not read `original-codes/` directly.'
            : '- The generator must read only curated copied snapshots or placeholders; it must not read `original-codes/` directly.';

        return <<<TEXT
# Custom Layer

This directory is the user-owned companion area for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- {$mirroredSourceCaption} are mirrored into `{$runtimeSourceRelativePath}` from `{$sourceTemplateDir}`.
- Do not edit mirrored {$mirroredSourceEditCaption} directly inside the artifact.
- Keep bridge-only notes, patches, or replacement guidance in this directory.

Runtime artifact layout:

- `{$runtimeSourceRelativePath}/` {$runtimeLayoutCaption}

Custom layer entry points:

{$entrypointSource}

Guidance:

- {$bridgeGuidance}
- {$sourcePolicyGuidance}
- If the copied snapshot is not available yet, the mirrored tree may be a placeholder bundle instead.
- Record any manual replacement steps or known missing legacy assets in `README.md`.

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are copied into the artifact.
- If it does not exist yet, a scaffold `README.md` is bundled instead.
TEXT;
    }

    return <<<TEXT
# Custom Layer

This directory is the user-owned custom layer for source output `{$normalizedProjectKey}/{$normalizedSourceOutputKey}`.

- Runtime source files are bundled under `{$runtimeSourceRelativePath}`.
- Do not edit staged runtime files directly.
- Keep custom code in this directory or its children.

Runtime artifact layout:

- `{$runtimeSourceRelativePath}/dbaccess-*.php` are generated wrapper entry files and `{$runtimeSourceRelativePath}/base/dbaccess-*Base.php` contains the generated DBAccess base classes.
- `{$runtimeSourceRelativePath}/data-*.php` are generated wrapper entry files and `{$runtimeSourceRelativePath}/base/data-*Base.php` contains the generated data base classes.
- Current emitted runtime tree does not include `{$runtimeSourceRelativePath}/_base/` or `{$runtimeSourceRelativePath}/_wrappers/`.
- Those paths remain historical self-generated bundle input only for generator/runtime analysis helpers.
- `{$runtimeSourceRelativePath}/_runtime_loader.php` resolves the custom bootstrap and keeps historical layered-runtime compatibility helpers available.
- `{$runtimeSourceRelativePath}/_support/legacy-dbaccess/` is kept only for remaining legacy delegate support or compatibility placeholders; current fully generated DBAccess base classes do not require it unless delegation actually remains.
- `{$runtimeSourceRelativePath}/_support/runtime-generation-manifest.json` records mode/count/artifact provenance for the emitted runtime tree.

Custom layer entry points:

{$entrypointSource}

Guidance:

- Keep DTO / data classes thin.
- Prefer helper, mapper, service, and policy code over patching generated files.
- Use `bootstrap.php` to `require_once` helper / collaborator files needed by custom wrappers.
- To override a generated class, create the same basename here and extend the corresponding `*Base` class.
- Example: `data-Project.php` with `class ProjectData extends ProjectDataBase`.

Artifact generation convention:

- Workspace path: `{$customLayerRelativePath}`
- If that workspace path exists, its files are copied into the artifact.
- If it does not exist yet, scaffold `README.md` and `bootstrap.php` are bundled instead.
TEXT;
}

function app_project_output_custom_layer_bootstrap_scaffold_text(string $projectKey, array $definition): string
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_source_output_key($definition['source_output_key']);

    return <<<PHP
<?php

// Optional custom bootstrap for {$normalizedProjectKey}/{$normalizedSourceOutputKey}.
// Load helper / collaborator files needed by custom wrapper classes here.
//
// Example:
// require_once __DIR__ . '/helpers/project_runtime_policy.php';

?>
PHP;
}

function app_project_output_custom_layer_client_extensions_scaffold_text(
    string $projectKey,
    array $definition,
): string {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_source_output_key($definition['source_output_key']);

    return <<<CS
#nullable enable

// Optional custom companion source for {$normalizedProjectKey}/{$normalizedSourceOutputKey}.
// Keep project-owned decorators, adapters, and orchestration code here instead of editing
// the generated proxy client files directly.
//
// Example:
// public sealed class GeneratedProxyClientDecorator
// {
//     private readonly object _inner;
//
//     public GeneratedProxyClientDecorator(object inner)
//     {
//         _inner = inner;
//     }
// }
CS;
}

/**
 * @param array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $definition
 * @return array<string,string>
 */
function app_project_output_custom_layer_scaffold_files(
    string $projectKey,
    array $definition,
    string $customLayerRelativePath,
): array {
    if (in_array($definition['artifact_strategy'], ['canonical-dbaccess-php', 'canonical-dataclass-php'], true)) {
        return [];
    }

    $files = [
        'README.md' => app_project_output_custom_layer_scaffold_text(
            $projectKey,
            $definition,
            $customLayerRelativePath,
        ) . PHP_EOL,
    ];

    if (
        $definition['artifact_strategy'] === 'openapi-json'
        || $definition['artifact_strategy'] === 'ai-context-md'
        || app_project_output_html_module_strategy_is_supported($definition['artifact_strategy'])
        || $definition['artifact_strategy'] === 'legacy-directory-mirror'
    ) {
        return $files;
    }

    if (in_array($definition['artifact_strategy'], ['single-proxy-client', 'custom-proxy-client'], true)) {
        $files['ClientExtensions.cs'] = app_project_output_custom_layer_client_extensions_scaffold_text(
            $projectKey,
            $definition,
        ) . PHP_EOL;
    } else {
        $files['bootstrap.php'] = app_project_output_custom_layer_bootstrap_scaffold_text(
            $projectKey,
            $definition,
        ) . PHP_EOL;
    }

    return $files;
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_program_language:string,
 *     source_output_class_type:string,
 *     source_output_release_target_type:string,
 *     artifact_strategy:string,
 *     created_at:string,
 *     requested_by:string,
 *     archive_format:string,
 *     archive_filename:string,
 *     bundle_entry_root:string,
 *     runtime_source_relative_path:string,
 *     source_file_count:int,
 *     source_total_bytes:int,
 *     customization_model:string,
 *     custom_layer_relative_path:string,
 *     custom_layer_source:string,
 *     custom_layer_file_count:int,
 *     custom_layer_total_bytes:int
 * } $item
 * @return array<mixed>
 */
function app_project_output_manifest_from_item(array $item): array
{
    return [
        'schema_version' => 3,
        'artifact_type' => 'project-source-output',
        'project_key' => $item['project_key'],
        'source_output_key' => $item['source_output_key'],
        'source_output_name' => $item['source_output_name'],
        'source_output_program_language' => $item['source_output_program_language'],
        'source_output_class_type' => $item['source_output_class_type'],
        'source_output_release_target_type' => $item['source_output_release_target_type'],
        'artifact_strategy' => $item['artifact_strategy'],
        'artifact_key' => $item['artifact_key'],
        'created_at' => $item['created_at'],
        'requested_by' => $item['requested_by'],
        'archive_format' => $item['archive_format'],
        'archive_filename' => $item['archive_filename'],
        'bundle_entry_root' => $item['bundle_entry_root'],
        'runtime_source_relative_path' => $item['runtime_source_relative_path'],
        'source_file_count' => $item['source_file_count'],
        'source_total_bytes' => $item['source_total_bytes'],
        'customization_model' => $item['customization_model'],
        'custom_layer_relative_path' => $item['custom_layer_relative_path'],
        'custom_layer_source' => $item['custom_layer_source'],
        'custom_layer_file_count' => $item['custom_layer_file_count'],
        'custom_layer_total_bytes' => $item['custom_layer_total_bytes'],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     artifact:array{
 *         project_key:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_output_program_language:string,
 *         source_output_class_type:string,
 *         source_output_release_target_type:string,
 *         artifact_strategy:string,
 *         artifact_key:string,
 *         created_at:string,
 *         requested_by:string,
 *         archive_format:string,
 *         archive_filename:string,
 *         archive_path:string,
 *         archive_exists:bool,
 *         archive_size:int,
 *         bundle_entry_root:string,
 *         bundle_root:string,
 *         runtime_source_root:string,
 *         runtime_source_relative_path:string,
 *         source_file_count:int,
 *         source_total_bytes:int,
 *         customization_model:string,
 *         custom_layer_relative_path:string,
 *         custom_layer_source:string,
 *         custom_layer_file_count:int,
 *         custom_layer_total_bytes:int,
 *         artifact_dir:string,
 *         manifest_path:string,
 *         bundle_manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_create_from_definition(
    array $app,
    string $projectKey,
    array $sourceOutput,
    string $requestedBy = 'system',
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    $definition = app_project_output_merge_source_output_definition($normalizedProjectKey, $sourceOutput);
    if ($definition['source_output_key'] === '' || !app_source_output_key_is_valid($definition['source_output_key'])) {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => 'source output key の形式が不正です。',
        ];
    }

    if (!app_source_output_artifact_strategy_supports_generation($definition['artifact_strategy'])) {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => 'この source output の artifact strategy は artifact 生成をサポートしていません。',
        ];
    }

    if ($definition['output_archive_format'] !== 'tar.gz') {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => '未対応の archive format です。',
        ];
    }

    $runtimeSourceRelativePath = '';
    $runtimeSourceRoot = '';
    $scanResult = [
        'ok' => false,
        'files' => [],
        'total_bytes' => 0,
        'error' => '',
    ];

    if (app_project_output_strategy_uses_layered_runtime_bundle($definition['artifact_strategy'])) {
        $runtimeSourceRelativePath = $definition['runtime_source_relative_path'];
        $runtimeTreeResult = app_project_output_prepare_runtime_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$runtimeTreeResult['ok'] || $runtimeTreeResult['scan_result'] === null) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $runtimeTreeResult['error'],
            ];
        }

        $runtimeSourceRoot = $runtimeTreeResult['runtime_source_root'];
        $scanResult = $runtimeTreeResult['scan_result'];
    } elseif (app_project_output_proxy_strategy_is_supported($definition['artifact_strategy'])) {
        $proxyTreeResult = app_project_output_prepare_proxy_source_tree($app, $normalizedProjectKey, $definition);
        if (!$proxyTreeResult['ok']) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $proxyTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $proxyTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $proxyTreeResult['runtime_source_root'];
        if (is_array($proxyTreeResult['scan_result'])) {
            $scanResult = $proxyTreeResult['scan_result'];
        }
    } elseif (app_project_output_openapi_strategy_is_supported($definition['artifact_strategy'])) {
        $openApiTreeResult = app_project_output_prepare_openapi_source_tree($app, $normalizedProjectKey, $definition);
        if (!$openApiTreeResult['ok'] || !is_array($openApiTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $openApiTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $openApiTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $openApiTreeResult['runtime_source_root'];
        $scanResult = $openApiTreeResult['scan_result'];
    } elseif (app_project_output_shared_contract_strategy_is_supported($definition['artifact_strategy'])) {
        $sharedContractTreeResult = app_project_output_prepare_shared_contract_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$sharedContractTreeResult['ok'] || !is_array($sharedContractTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $sharedContractTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $sharedContractTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $sharedContractTreeResult['runtime_source_root'];
        $scanResult = $sharedContractTreeResult['scan_result'];
    } elseif (app_project_output_typescript_dto_strategy_is_supported($definition['artifact_strategy'])) {
        $typescriptDtoTreeResult = app_project_output_prepare_typescript_dto_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$typescriptDtoTreeResult['ok'] || !is_array($typescriptDtoTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $typescriptDtoTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $typescriptDtoTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $typescriptDtoTreeResult['runtime_source_root'];
        $scanResult = $typescriptDtoTreeResult['scan_result'];
    } elseif (app_project_output_app_local_persistence_strategy_is_supported($definition['artifact_strategy'])) {
        $appLocalTreeResult = app_project_output_prepare_app_local_persistence_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$appLocalTreeResult['ok'] || !is_array($appLocalTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $appLocalTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $appLocalTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $appLocalTreeResult['runtime_source_root'];
        $scanResult = $appLocalTreeResult['scan_result'];
    } elseif (app_project_output_managed_operation_strategy_is_supported($definition['artifact_strategy'])) {
        $managedOperationTreeResult = app_project_output_prepare_managed_operation_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$managedOperationTreeResult['ok'] || !is_array($managedOperationTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $managedOperationTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $managedOperationTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $managedOperationTreeResult['runtime_source_root'];
        $scanResult = $managedOperationTreeResult['scan_result'];
    } elseif (app_project_output_ai_context_strategy_is_supported($definition['artifact_strategy'])) {
        $aiContextTreeResult = app_project_output_prepare_ai_context_source_tree($app, $normalizedProjectKey, $definition);
        if (!$aiContextTreeResult['ok'] || !is_array($aiContextTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $aiContextTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $aiContextTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $aiContextTreeResult['runtime_source_root'];
        $scanResult = $aiContextTreeResult['scan_result'];
    } elseif (app_project_output_modernization_audit_strategy_is_supported($definition['artifact_strategy'])) {
        $auditTreeResult = app_project_output_prepare_modernization_audit_source_tree($app, $normalizedProjectKey, $definition);
        if (!$auditTreeResult['ok'] || !is_array($auditTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $auditTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $auditTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $auditTreeResult['runtime_source_root'];
        $scanResult = $auditTreeResult['scan_result'];
    } elseif (app_project_output_db_access_strategy_is_supported($definition['artifact_strategy'])) {
        $dbAccessTreeResult = app_project_output_prepare_db_access_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$dbAccessTreeResult['ok'] || !is_array($dbAccessTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $dbAccessTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $dbAccessTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $dbAccessTreeResult['runtime_source_root'];
        $scanResult = $dbAccessTreeResult['scan_result'];
    } elseif (app_project_output_html_module_strategy_is_supported($definition['artifact_strategy'])) {
        $htmlModuleTreeResult = app_project_output_prepare_html_module_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$htmlModuleTreeResult['ok'] || !is_array($htmlModuleTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $htmlModuleTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $htmlModuleTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $htmlModuleTreeResult['runtime_source_root'];
        $scanResult = $htmlModuleTreeResult['scan_result'];
    } elseif (app_project_output_data_class_strategy_is_supported($definition['artifact_strategy'])) {
        $dataClassTreeResult = app_project_output_prepare_data_class_source_tree(
            $app,
            $normalizedProjectKey,
            $definition,
        );
        if (!$dataClassTreeResult['ok'] || !is_array($dataClassTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $dataClassTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $dataClassTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $dataClassTreeResult['runtime_source_root'];
        $scanResult = $dataClassTreeResult['scan_result'];
    } elseif (app_project_output_legacy_source_strategy_is_supported($definition['artifact_strategy'])) {
        $legacyTreeResult = app_project_output_prepare_legacy_source_tree($app, $normalizedProjectKey, $definition);
        if (!$legacyTreeResult['ok'] || !is_array($legacyTreeResult['scan_result'])) {
            return [
                'ok' => false,
                'artifact' => null,
                'error' => $legacyTreeResult['error'],
            ];
        }

        $runtimeSourceRelativePath = $legacyTreeResult['runtime_source_relative_path'];
        $runtimeSourceRoot = $legacyTreeResult['runtime_source_root'];
        $scanResult = $legacyTreeResult['scan_result'];
    } else {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => '未対応の artifact strategy です。',
        ];
    }

    if ($scanResult['files'] === []) {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => 'runtime source directory に出力対象ファイルがありません。',
        ];
    }

    $artifactKey = app_project_output_new_artifact_key();
    $artifactDir = app_project_output_storage_root($app, $normalizedProjectKey) . '/' . $artifactKey;
    $bundleEntryRoot = app_project_output_archive_basename(
        $normalizedProjectKey,
        $artifactKey,
        $definition['source_output_key'],
    );
    $bundleRoot = $artifactDir . '/bundle/' . $bundleEntryRoot;
    $bundleRuntimeRoot = $bundleRoot . '/' . $runtimeSourceRelativePath;
    $manifestPath = $artifactDir . '/manifest.json';
    $bundleManifestPath = $bundleRoot . '/manifest.json';
    $archiveFilename = $bundleEntryRoot . '.tar.gz';
    $archivePath = $artifactDir . '/' . $archiveFilename;
    $customLayerWorkspace = app_project_output_scan_custom_layer_workspace(
        $normalizedProjectKey,
        $definition['source_output_key'],
    );
    if ($customLayerWorkspace['error'] !== '') {
        return [
            'ok' => false,
            'artifact' => null,
            'error' => $customLayerWorkspace['error'],
        ];
    }
    $customLayerRelativePath = $customLayerWorkspace['relative_path'];

    try {
        app_project_output_ensure_directory(dirname($bundleRuntimeRoot));
        if (app_project_output_strategy_uses_layered_runtime_bundle($definition['artifact_strategy'])) {
            app_project_output_build_layered_runtime_bundle(
                $runtimeSourceRoot,
                $bundleRuntimeRoot,
                $scanResult['files'],
                $customLayerRelativePath,
            );
        } else {
            app_project_output_copy_tree(
                $runtimeSourceRoot,
                $bundleRuntimeRoot,
                $scanResult['files'],
            );
        }

        $customLayerBundleRoot = $bundleRoot . '/' . $customLayerRelativePath;
        $customLayerSource = 'bundle-scaffold';
        $customLayerFileCount = 0;
        $customLayerTotalBytes = 0;

        if ($customLayerWorkspace['exists'] && $customLayerWorkspace['files'] !== []) {
            app_project_output_copy_tree(
                $customLayerWorkspace['workspace_root'],
                $customLayerBundleRoot,
                $customLayerWorkspace['files'],
            );
            $customLayerSource = 'workspace';
            $customLayerFileCount = $customLayerWorkspace['file_count'];
            $customLayerTotalBytes = $customLayerWorkspace['total_bytes'];
        } else {
            $customLayerSource = $customLayerWorkspace['exists']
                ? 'workspace-empty-scaffold'
                : 'bundle-scaffold';
            $customLayerScaffoldFiles = app_project_output_custom_layer_scaffold_files(
                $normalizedProjectKey,
                $definition,
                $customLayerRelativePath,
            );
            foreach ($customLayerScaffoldFiles as $relativePath => $contents) {
                app_project_output_write_text_file($customLayerBundleRoot . '/' . $relativePath, $contents);
                $customLayerFileCount++;
                $customLayerTotalBytes += strlen($contents);
            }
        }

        $artifact = [
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $definition['source_output_key'],
            'source_output_name' => $definition['name'],
            'source_output_program_language' => $definition['program_language'],
            'source_output_class_type' => $definition['class_type'],
            'source_output_release_target_type' => $definition['release_target_type'],
            'artifact_strategy' => $definition['artifact_strategy'],
            'artifact_key' => $artifactKey,
            'created_at' => date(DATE_ATOM),
            'requested_by' => app_project_output_normalize_requested_by($requestedBy),
            'archive_format' => $definition['output_archive_format'],
            'archive_filename' => $archiveFilename,
            'archive_path' => $archivePath,
            'archive_exists' => false,
            'archive_size' => 0,
            'bundle_entry_root' => $bundleEntryRoot,
            'bundle_root' => $bundleRoot,
            'runtime_source_root' => $runtimeSourceRoot,
            'runtime_source_relative_path' => $runtimeSourceRelativePath,
            'source_file_count' => count($scanResult['files']),
            'source_total_bytes' => $scanResult['total_bytes'],
            'customization_model' => app_project_output_customization_model($definition['artifact_strategy']),
            'custom_layer_relative_path' => $customLayerRelativePath,
            'custom_layer_source' => $customLayerSource,
            'custom_layer_file_count' => $customLayerFileCount,
            'custom_layer_total_bytes' => $customLayerTotalBytes,
            'artifact_dir' => $artifactDir,
            'manifest_path' => $manifestPath,
            'bundle_manifest_path' => $bundleManifestPath,
        ];

        app_project_output_set_runtime_generation_manifest_artifact_key(
            $bundleRuntimeRoot,
            $artifactKey,
        );

        $manifest = app_project_output_manifest_from_item($artifact);
        app_project_output_write_json_file($manifestPath, $manifest);
        app_project_output_write_json_file($bundleManifestPath, $manifest);
        app_project_output_build_tar_archive($artifactDir . '/bundle', $bundleEntryRoot, $archivePath);

        $artifact['archive_exists'] = is_file($archivePath);
        $artifact['archive_size'] = is_file($archivePath) ? (int) filesize($archivePath) : 0;

        return [
            'ok' => true,
            'artifact' => $artifact,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_project_output_delete_tree($artifactDir);

        return [
            'ok' => false,
            'artifact' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     artifact:array{
 *         project_key:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_output_program_language:string,
 *         source_output_class_type:string,
 *         source_output_release_target_type:string,
 *         artifact_strategy:string,
 *         artifact_key:string,
 *         created_at:string,
 *         requested_by:string,
 *         archive_format:string,
 *         archive_filename:string,
 *         archive_path:string,
 *         archive_exists:bool,
 *         archive_size:int,
 *         bundle_entry_root:string,
 *         bundle_root:string,
 *         runtime_source_root:string,
 *         runtime_source_relative_path:string,
 *         source_file_count:int,
 *         source_total_bytes:int,
 *         customization_model:string,
 *         custom_layer_relative_path:string,
 *         custom_layer_source:string,
 *         custom_layer_file_count:int,
 *         custom_layer_total_bytes:int,
 *         artifact_dir:string,
 *         manifest_path:string,
 *         bundle_manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_create(array $app, string $projectKey, string $requestedBy = 'system'): array
{
    return app_project_output_create_from_definition(
        $app,
        $projectKey,
        app_project_output_local_default_source_output($projectKey),
        $requestedBy,
    );
}

/**
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     artifact_key:string,
 *     bundle_root:string,
 *     runtime_source_relative_path:string
 * } $artifact
 * @param array<string,mixed> $sourceOutput
 * @return array{
 *     ok:bool,
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
function app_project_output_publish_artifact(array $app, array $artifact, array $sourceOutput): array
{
    $projectKey = app_normalize_project_key($artifact['project_key'] ?? '');
    $sourceOutputKey = app_normalize_source_output_key($artifact['source_output_key'] ?? '');
    $artifactKey = (string) ($artifact['artifact_key'] ?? '');

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'published' => null,
            'error' => 'artifact project key の形式が不正です。',
        ];
    }

    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'published' => null,
            'error' => 'artifact source output key の形式が不正です。',
        ];
    }

    if ($artifactKey === '' || !app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'published' => null,
            'error' => 'artifact key の形式が不正です。',
        ];
    }

    $definition = app_project_output_merge_source_output_definition($projectKey, $sourceOutput);
    $sourceOutputDir = str_replace('\\', '/', trim($definition['source_output_dir']));
    if (!app_project_output_relative_path_is_safe($sourceOutputDir)) {
        return [
            'ok' => false,
            'published' => null,
            'error' => 'publish 先 path の形式が不正です。',
        ];
    }

    try {
        $artifactRuntimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
        $publishedRoot = app_project_output_workspace_path_from_relative($sourceOutputDir);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'published' => null,
            'error' => $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($artifactRuntimeRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'published' => null,
            'error' => $scanResult['error'],
        ];
    }

    if ($scanResult['files'] === []) {
        return [
            'ok' => false,
            'published' => null,
            'error' => 'publish 対象 artifact に runtime source file がありません。',
        ];
    }

    $stagingRoot = $publishedRoot . '.__publishing__.' . bin2hex(random_bytes(4));
    $backupRoot = $publishedRoot . '.__backup__.' . bin2hex(random_bytes(4));

    try {
        app_project_output_ensure_directory(dirname($publishedRoot));
        app_project_output_delete_tree($stagingRoot);
        app_project_output_delete_tree($backupRoot);
        app_project_output_copy_tree($artifactRuntimeRoot, $stagingRoot, $scanResult['files']);

        $hadExistingRoot = file_exists($publishedRoot);
        if ($hadExistingRoot && !rename($publishedRoot, $backupRoot)) {
            throw new RuntimeException('既存 publish root の退避に失敗しました。');
        }

        if (!rename($stagingRoot, $publishedRoot)) {
            if ($hadExistingRoot && file_exists($backupRoot) && !file_exists($publishedRoot)) {
                @rename($backupRoot, $publishedRoot);
            }

            throw new RuntimeException('publish root の切り替えに失敗しました。');
        }

        app_project_output_delete_tree($backupRoot);

        return [
            'ok' => true,
            'published' => [
                'project_key' => $projectKey,
                'source_output_key' => $sourceOutputKey,
                'artifact_key' => $artifactKey,
                'source_output_dir' => $sourceOutputDir,
                'published_root' => $publishedRoot,
                'published_file_count' => count($scanResult['files']),
                'published_total_bytes' => $scanResult['total_bytes'],
                'published_at' => date(DATE_ATOM),
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_project_output_delete_tree($stagingRoot);
        if (file_exists($backupRoot) && !file_exists($publishedRoot)) {
            @rename($backupRoot, $publishedRoot);
        }

        return [
            'ok' => false,
            'published' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<mixed> $manifest
 * @return array{
 *     project_key:string,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_program_language:string,
 *     source_output_class_type:string,
 *     source_output_release_target_type:string,
 *     artifact_strategy:string,
 *     artifact_key:string,
 *     created_at:string,
 *     requested_by:string,
 *     archive_format:string,
 *     archive_filename:string,
 *     archive_path:string,
 *     archive_exists:bool,
 *     archive_size:int,
 *     bundle_entry_root:string,
 *     bundle_root:string,
 *     runtime_source_root:string,
 *     runtime_source_relative_path:string,
 *     source_file_count:int,
 *     source_total_bytes:int,
 *     artifact_dir:string,
 *     manifest_path:string,
 *     bundle_manifest_path:string
 * }|null
 */
function app_project_output_item_from_manifest(array $app, string $artifactDir, array $manifest): ?array
{
    $projectKey = $manifest['project_key'] ?? null;
    $artifactKey = $manifest['artifact_key'] ?? null;
    $createdAt = $manifest['created_at'] ?? null;
    $requestedBy = $manifest['requested_by'] ?? null;
    $archiveFormat = $manifest['archive_format'] ?? null;
    $archiveFilename = $manifest['archive_filename'] ?? null;
    $bundleEntryRoot = $manifest['bundle_entry_root'] ?? null;
    $runtimeSourceRelativePath = $manifest['runtime_source_relative_path'] ?? null;
    $sourceFileCount = $manifest['source_file_count'] ?? null;
    $sourceTotalBytes = $manifest['source_total_bytes'] ?? null;

    if (
        !is_string($projectKey) || !app_project_key_is_valid($projectKey)
        || !is_string($artifactKey) || !app_project_output_artifact_key_is_valid($artifactKey)
        || !is_string($createdAt) || $createdAt === ''
        || !is_string($requestedBy) || $requestedBy === ''
        || !is_string($archiveFormat) || $archiveFormat === ''
        || !is_string($archiveFilename) || $archiveFilename === ''
        || !is_string($bundleEntryRoot) || $bundleEntryRoot === ''
        || !is_string($runtimeSourceRelativePath) || $runtimeSourceRelativePath === ''
        || !is_int($sourceFileCount)
        || !is_int($sourceTotalBytes)
    ) {
        return null;
    }

    $defaultSourceOutput = app_project_output_local_default_source_output($projectKey);
    $sourceOutputKey = $manifest['source_output_key'] ?? $defaultSourceOutput['source_output_key'];
    $sourceOutputName = $manifest['source_output_name'] ?? $defaultSourceOutput['name'];
    $sourceOutputProgramLanguage = $manifest['source_output_program_language'] ?? $defaultSourceOutput['program_language'];
    $sourceOutputClassType = $manifest['source_output_class_type'] ?? $defaultSourceOutput['class_type'];
    $sourceOutputReleaseTargetType = $manifest['source_output_release_target_type'] ?? $defaultSourceOutput['release_target_type'];
    $artifactStrategy = $manifest['artifact_strategy'] ?? $defaultSourceOutput['artifact_strategy'];

    if (
        !is_string($sourceOutputKey) || !app_source_output_key_is_valid(app_normalize_source_output_key($sourceOutputKey))
        || !is_string($sourceOutputName) || $sourceOutputName === ''
        || !is_string($sourceOutputProgramLanguage) || $sourceOutputProgramLanguage === ''
        || !is_string($sourceOutputClassType) || $sourceOutputClassType === ''
        || !is_string($sourceOutputReleaseTargetType) || $sourceOutputReleaseTargetType === ''
        || !is_string($artifactStrategy) || $artifactStrategy === ''
    ) {
        return null;
    }

    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
    $customizationModel = $manifest['customization_model'] ?? 'legacy-bootstrap-dbclasses';
    $customLayerRelativePath = $manifest['custom_layer_relative_path']
        ?? app_project_output_custom_layer_relative_path($projectKey, $normalizedSourceOutputKey);
    $customLayerSource = $manifest['custom_layer_source'] ?? 'not-recorded';
    $customLayerFileCount = $manifest['custom_layer_file_count'] ?? 0;
    $customLayerTotalBytes = $manifest['custom_layer_total_bytes'] ?? 0;

    if (
        !is_string($customizationModel) || $customizationModel === ''
        || !is_string($customLayerRelativePath) || $customLayerRelativePath === ''
        || !is_string($customLayerSource) || $customLayerSource === ''
        || !is_int($customLayerFileCount)
        || !is_int($customLayerTotalBytes)
    ) {
        return null;
    }

    $archivePath = $artifactDir . '/' . $archiveFilename;
    $bundleRoot = $artifactDir . '/bundle/' . $bundleEntryRoot;

    return [
        'project_key' => $projectKey,
        'source_output_key' => $normalizedSourceOutputKey,
        'source_output_name' => $sourceOutputName,
        'source_output_program_language' => $sourceOutputProgramLanguage,
        'source_output_class_type' => $sourceOutputClassType,
        'source_output_release_target_type' => $sourceOutputReleaseTargetType,
        'artifact_strategy' => $artifactStrategy,
        'artifact_key' => $artifactKey,
        'created_at' => $createdAt,
        'requested_by' => $requestedBy,
        'archive_format' => $archiveFormat,
        'archive_filename' => $archiveFilename,
        'archive_path' => $archivePath,
        'archive_exists' => is_file($archivePath),
        'archive_size' => is_file($archivePath) ? (int) filesize($archivePath) : 0,
        'bundle_entry_root' => $bundleEntryRoot,
        'bundle_root' => $bundleRoot,
        'runtime_source_root' => app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath),
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'source_file_count' => $sourceFileCount,
        'source_total_bytes' => $sourceTotalBytes,
        'customization_model' => $customizationModel,
        'custom_layer_relative_path' => $customLayerRelativePath,
        'custom_layer_source' => $customLayerSource,
        'custom_layer_file_count' => $customLayerFileCount,
        'custom_layer_total_bytes' => $customLayerTotalBytes,
        'artifact_dir' => $artifactDir,
        'manifest_path' => $artifactDir . '/manifest.json',
        'bundle_manifest_path' => $bundleRoot . '/manifest.json',
    ];
}

/**
 * @return array<mixed>|null
 */
function app_project_output_read_manifest(string $manifestPath): ?array
{
    if (!is_file($manifestPath)) {
        return null;
    }

    $json = file_get_contents($manifestPath);
    if (!is_string($json) || $json === '') {
        return null;
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return null;
    }

    return $decoded;
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_output_program_language:string,
 *         source_output_class_type:string,
 *         source_output_release_target_type:string,
 *         artifact_strategy:string,
 *         artifact_key:string,
 *         created_at:string,
 *         requested_by:string,
 *         archive_format:string,
 *         archive_filename:string,
 *         archive_path:string,
 *         archive_exists:bool,
 *         archive_size:int,
 *         bundle_entry_root:string,
 *         bundle_root:string,
 *         runtime_source_root:string,
 *         runtime_source_relative_path:string,
 *         source_file_count:int,
 *         source_total_bytes:int,
 *         customization_model:string,
 *         custom_layer_relative_path:string,
 *         custom_layer_source:string,
 *         custom_layer_file_count:int,
 *         custom_layer_total_bytes:int,
 *         artifact_dir:string,
 *         manifest_path:string,
 *         bundle_manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_find(array $app, string $projectKey, string $artifactKey): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'artifact key の形式が不正です。',
        ];
    }

    $artifactDir = app_project_output_storage_root($app, $normalizedProjectKey) . '/' . $artifactKey;
    $manifest = app_project_output_read_manifest($artifactDir . '/manifest.json');
    if ($manifest === null) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    $item = app_project_output_item_from_manifest($app, $artifactDir, $manifest);
    if ($item === null) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'artifact manifest の形式が不正です。',
        ];
    }

    if ($item['project_key'] !== $normalizedProjectKey || $item['artifact_key'] !== $artifactKey) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'artifact manifest と要求 path が一致しません。',
        ];
    }

    return [
        'ok' => true,
        'item' => $item,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_key:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_output_program_language:string,
 *         source_output_class_type:string,
 *         source_output_release_target_type:string,
 *         artifact_strategy:string,
 *         artifact_key:string,
 *         created_at:string,
 *         requested_by:string,
 *         archive_format:string,
 *         archive_filename:string,
 *         archive_path:string,
 *         archive_exists:bool,
 *         archive_size:int,
 *         bundle_entry_root:string,
 *         bundle_root:string,
 *         runtime_source_root:string,
 *         runtime_source_relative_path:string,
 *         source_file_count:int,
 *         source_total_bytes:int,
 *         customization_model:string,
 *         custom_layer_relative_path:string,
 *         custom_layer_source:string,
 *         custom_layer_file_count:int,
 *         custom_layer_total_bytes:int,
 *         artifact_dir:string,
 *         manifest_path:string,
 *         bundle_manifest_path:string
 *     }>,
 *     error:string
 * }
 */
function app_project_output_list(array $app, string $projectKey, ?string $sourceOutputKey = null): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'items' => [],
            'error' => 'project key の形式が不正です。',
        ];
    }

    $normalizedSourceOutputKey = null;
    if ($sourceOutputKey !== null && trim($sourceOutputKey) !== '') {
        $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
        if (!app_source_output_key_is_valid($normalizedSourceOutputKey)) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'source output key の形式が不正です。',
            ];
        }
    }

    $storageRoot = app_project_output_storage_root($app, $normalizedProjectKey);
    if (!is_dir($storageRoot)) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $entries = scandir($storageRoot);
    if ($entries === false) {
        return [
            'ok' => false,
            'items' => [],
            'error' => 'artifact storage の読み込みに失敗しました。',
        ];
    }

    $items = [];
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $artifactDir = $storageRoot . '/' . $entry;
        if (!is_dir($artifactDir)) {
            continue;
        }

        $manifest = app_project_output_read_manifest($artifactDir . '/manifest.json');
        if ($manifest === null) {
            continue;
        }

        $item = app_project_output_item_from_manifest($app, $artifactDir, $manifest);
        if ($item === null || $item['project_key'] !== $normalizedProjectKey) {
            continue;
        }

        if ($normalizedSourceOutputKey !== null && $item['source_output_key'] !== $normalizedSourceOutputKey) {
            continue;
        }

        $items[] = $item;
    }

    usort(
        $items,
        static fn (array $left, array $right): int => strcmp($right['artifact_key'], $left['artifact_key']),
    );

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}
