<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/legacy_data_class_editable_area_migrator.php';
require_once __DIR__ . '/project_output_db_access_generator.php';
require_once __DIR__ . '/project_output_runtime_sql_generator.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/table_metadata_repository.php';
require_once __DIR__ . '/table_constraint_metadata_repository.php';
require_once __DIR__ . '/sso_app_user_project_policy_repository.php';
require_once __DIR__ . '/sso_app_user_canonical_schema_validation.php';
require_once __DIR__ . '/sso_app_user_generated_resolver.php';

function app_project_output_runtime_stage_relative_path(string $projectKey, string $sourceOutputKey): string
{
    return app_runtime_storage_project_output_runtime_staging_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @return list<string>
 */
function app_project_output_runtime_excluded_source_names(): array
{
    // These legacy sources were used for Apache config template expansion and
    // host-assignment infrastructure, not for the runtime dbclasses self-loop.
    // Current host assignment state stays in project_host_assignments and can
    // later be split into a dedicated infra catalog if needed.
    return [
        'ApacheHostSetting',
        'ApacheHostSettingTemplate',
    ];
}

function app_project_output_runtime_source_name_is_excluded(string $sourceName): bool
{
    foreach (app_project_output_runtime_excluded_source_names() as $excludedSourceName) {
        if (strcasecmp($sourceName, $excludedSourceName) === 0) {
            return true;
        }
    }

    return false;
}

function app_project_output_runtime_source_name_from_relative_path(string $relativePath): string
{
    $normalizedRelativePath = str_replace('\\', '/', trim($relativePath, '/'));
    if ($normalizedRelativePath === '') {
        return '';
    }

    $basename = basename($normalizedRelativePath);
    $directory = dirname($normalizedRelativePath);
    if ($directory === '.') {
        $directory = '';
    }

    if ($directory === 'base') {
        if (preg_match('/^(?:dbaccess|data)-(.+)Base\.php$/', $basename, $matches) === 1) {
            return trim($matches[1]);
        }

        return '';
    }

    if (
        $directory === ''
        || $directory === '_base'
        || $directory === '_wrappers'
        || $directory === '_support/legacy-dbaccess'
    ) {
        if (preg_match('/^(?:dbaccess|data)-(.+)\.php$/', $basename, $matches) === 1) {
            return trim($matches[1]);
        }
    }

    return '';
}

function app_project_output_runtime_relative_path_is_excluded(string $relativePath): bool
{
    $sourceName = app_project_output_runtime_source_name_from_relative_path($relativePath);

    return $sourceName !== '' && app_project_output_runtime_source_name_is_excluded($sourceName);
}

/**
 * @param list<array{
 *     relative_path:string,
 *     size:int
 * }> $files
 * @return list<array{
 *     relative_path:string,
 *     size:int
 * }>
 */
function app_project_output_runtime_filter_files(array $files): array
{
    return array_values(
        array_filter(
            $files,
            static fn (array $file): bool => !app_project_output_runtime_relative_path_is_excluded(
                (string) ($file['relative_path'] ?? ''),
            ),
        ),
    );
}

function app_project_output_runtime_filter_autoload_contents(string $contents): string
{
    $rewritten = $contents;

    foreach (app_project_output_runtime_excluded_source_names() as $sourceName) {
        $rewritten = preg_replace(
            '/^\s*include_once\("(?:data|dbaccess)-' . preg_quote($sourceName, '/') . '\.php"\);\s*\R?/m',
            '',
            $rewritten,
        );
        if (!is_string($rewritten)) {
            throw new RuntimeException('autoload_mtool.php の除外行変換に失敗しました。');
        }
    }

    return $rewritten;
}

function app_project_output_runtime_strip_eager_include_lines(string $contents): string
{
    $rewritten = preg_replace(
        '/^\s*include_once\("(?:data|dbaccess)-[^"\r\n]+\.php"\);\s*\R?/m',
        '',
        $contents,
    );
    if (!is_string($rewritten)) {
        throw new RuntimeException('autoload_mtool.php の eager include 行除去に失敗しました。');
    }

    return $rewritten;
}

function app_project_output_runtime_previous_significant_token_id(array $tokens, int $startIndex): int|string|null
{
    for ($index = $startIndex - 1; $index >= 0; $index--) {
        $token = $tokens[$index];
        if (is_array($token)) {
            if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $token[0];
        }

        if (trim($token) === '') {
            continue;
        }

        return $token;
    }

    return null;
}

function app_project_output_runtime_next_named_token(array $tokens, int $startIndex): string
{
    $tokenCount = count($tokens);
    for ($index = $startIndex; $index < $tokenCount; $index++) {
        $token = $tokens[$index];
        if (is_array($token)) {
            if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            if ($token[0] === T_STRING) {
                return trim($token[1]);
            }

            return '';
        }

        if ($token === '(' || $token === '{' || $token === ';') {
            return '';
        }
    }

    return '';
}

/**
 * @return array{
 *     class_names:list<string>,
 *     function_names:list<string>
 * }
 */
function app_project_output_runtime_php_symbol_catalog(string $contents): array
{
    if ($contents === '') {
        return [
            'class_names' => [],
            'function_names' => [],
        ];
    }

    $tokens = token_get_all($contents);
    $classNames = [];
    $functionNames = [];
    $classSeen = [];
    $functionSeen = [];
    $classLikeTokenIds = [T_CLASS, T_INTERFACE, T_TRAIT];
    if (defined('T_ENUM')) {
        $classLikeTokenIds[] = constant('T_ENUM');
    }

    $braceDepth = 0;
    $tokenCount = count($tokens);
    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];

        if (is_string($token)) {
            if ($token === '{') {
                $braceDepth++;
            } elseif ($token === '}' && $braceDepth > 0) {
                $braceDepth--;
            }
            continue;
        }

        if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if ($braceDepth !== 0) {
            continue;
        }

        if (in_array($token[0], $classLikeTokenIds, true)) {
            if ($token[0] === T_CLASS && app_project_output_runtime_previous_significant_token_id($tokens, $index) === T_NEW) {
                continue;
            }

            $className = app_project_output_runtime_next_named_token($tokens, $index + 1);
            if ($className !== '') {
                app_generated_append_unique_string($classNames, $classSeen, $className);
            }
            continue;
        }

        if ($token[0] !== T_FUNCTION) {
            continue;
        }

        $functionName = app_project_output_runtime_next_named_token($tokens, $index + 1);
        if ($functionName !== '') {
            app_generated_append_unique_string($functionNames, $functionSeen, $functionName);
        }
    }

    return [
        'class_names' => $classNames,
        'function_names' => $functionNames,
    ];
}

/**
 * @return list<string>
 */
function app_project_output_runtime_php_relative_paths(string $runtimeRoot): array
{
    if ($runtimeRoot === '' || !is_dir($runtimeRoot)) {
        return [];
    }

    $relativePaths = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($runtimeRoot, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST,
    );

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        if (!$item->isFile() || strtolower($item->getExtension()) !== 'php') {
            continue;
        }

        $pathname = $item->getPathname();
        if ($pathname === $runtimeRoot . '/autoload_mtool.php') {
            continue;
        }

        $relativePaths[] = str_replace('\\', '/', substr($pathname, strlen($runtimeRoot) + 1));
    }

    sort($relativePaths, SORT_STRING);

    return $relativePaths;
}

/**
 * @return array{
 *     preload_files:list<string>,
 *     class_map:array<string,string>
 * }
 */
function app_project_output_runtime_generated_autoload_registry(string $runtimeRoot): array
{
    $preloadFiles = [];
    $preloadSeen = [];
    $classMap = [];

    foreach (app_project_output_runtime_php_relative_paths($runtimeRoot) as $relativePath) {
        $contents = file_get_contents($runtimeRoot . '/' . $relativePath);
        if (!is_string($contents)) {
            throw new RuntimeException('runtime autoload symbol catalog の読み込みに失敗しました: ' . $relativePath);
        }

        $catalog = app_project_output_runtime_php_symbol_catalog($contents);
        if ($catalog['function_names'] !== []) {
            app_generated_append_unique_string($preloadFiles, $preloadSeen, $relativePath);
        }

        foreach ($catalog['class_names'] as $className) {
            if (!isset($classMap[$className])) {
                $classMap[$className] = $relativePath;
            }
        }
    }

    ksort($classMap, SORT_STRING);

    return [
        'preload_files' => $preloadFiles,
        'class_map' => $classMap,
    ];
}

function app_project_output_runtime_generated_autoload_block(string $runtimeRoot): string
{
    $registry = app_project_output_runtime_generated_autoload_registry($runtimeRoot);
    $exportedPreloadFiles = var_export($registry['preload_files'], true);
    $exportedClassMap = var_export($registry['class_map'], true);

    return <<<PHP
// == START OF GENERATED RUNTIME AUTOLOAD ==
\$__mtoolRuntimeAutoloadRoot = __DIR__;
\$__mtoolRuntimePreloadFiles = {$exportedPreloadFiles};
foreach (\$__mtoolRuntimePreloadFiles as \$__mtoolRuntimePreloadFile) {
    require_once \$__mtoolRuntimeAutoloadRoot . '/' . \$__mtoolRuntimePreloadFile;
}

if (!isset(\$GLOBALS['__mtool_runtime_classmap_maps'])) {
    \$GLOBALS['__mtool_runtime_classmap_maps'] = [];
}
if (!isset(\$GLOBALS['__mtool_runtime_classmap_registered_roots'])) {
    \$GLOBALS['__mtool_runtime_classmap_registered_roots'] = [];
}

\$GLOBALS['__mtool_runtime_classmap_maps'][\$__mtoolRuntimeAutoloadRoot] = {$exportedClassMap};
if (!isset(\$GLOBALS['__mtool_runtime_classmap_registered_roots'][\$__mtoolRuntimeAutoloadRoot])) {
    spl_autoload_register(
        static function (string \$class) use (\$__mtoolRuntimeAutoloadRoot): void {
            \$classMap = \$GLOBALS['__mtool_runtime_classmap_maps'][\$__mtoolRuntimeAutoloadRoot] ?? [];
            if (!isset(\$classMap[\$class])) {
                return;
            }

            require_once \$__mtoolRuntimeAutoloadRoot . '/' . \$classMap[\$class];
        },
    );
    \$GLOBALS['__mtool_runtime_classmap_registered_roots'][\$__mtoolRuntimeAutoloadRoot] = true;
}

unset(\$__mtoolRuntimePreloadFile, \$__mtoolRuntimePreloadFiles, \$__mtoolRuntimeAutoloadRoot);
// == END OF GENERATED RUNTIME AUTOLOAD ==
PHP;
}

function app_project_output_runtime_rewrite_autoload_contents(string $contents, string $runtimeRoot): string
{
    $rewritten = app_project_output_runtime_filter_autoload_contents($contents);
    $rewritten = preg_replace(
        '/^\s*\/\/ == START OF GENERATED RUNTIME AUTOLOAD ==\R.*?^\s*\/\/ == END OF GENERATED RUNTIME AUTOLOAD ==\R?/ms',
        '',
        $rewritten,
    );
    if (!is_string($rewritten)) {
        throw new RuntimeException('autoload_mtool.php の generated autoload block 除去に失敗しました。');
    }

    $rewritten = app_project_output_runtime_strip_eager_include_lines($rewritten);
    $generatedBlock = app_project_output_runtime_generated_autoload_block($runtimeRoot);
    $bottomMarker = '// == START OF EDITABLE AREA FOR AUTOLOAD BOTTOM ==';

    if (str_contains($rewritten, $bottomMarker)) {
        return str_replace(
            $bottomMarker,
            $generatedBlock . "\n\n" . $bottomMarker,
            $rewritten,
        );
    }

    $rewritten = preg_replace(
        '/\?>\s*$/',
        $generatedBlock . "\n\n?>",
        $rewritten,
        1,
        $count,
    );
    if (!is_string($rewritten)) {
        throw new RuntimeException('autoload_mtool.php の lazy autoload block 埋め込みに失敗しました。');
    }

    if ($count === 1) {
        return $rewritten;
    }

    return rtrim($rewritten) . "\n\n" . $generatedBlock . "\n";
}

function app_project_output_runtime_rewrite_autoload_file(string $filePath, ?string $runtimeRoot = null): void
{
    if (!is_file($filePath)) {
        return;
    }

    $contents = file_get_contents($filePath);
    if (!is_string($contents)) {
        throw new RuntimeException('autoload_mtool.php の読み込みに失敗しました。');
    }

    $rewritten = $runtimeRoot === null
        ? app_project_output_runtime_filter_autoload_contents($contents)
        : app_project_output_runtime_rewrite_autoload_contents($contents, $runtimeRoot);
    if ($rewritten === $contents) {
        return;
    }

    if (file_put_contents($filePath, $rewritten) === false) {
        throw new RuntimeException('autoload_mtool.php の保存に失敗しました。');
    }
}

/**
 * @return array{
 *     entities:list<array{
 *         source_name:string,
 *         dbaccess_file:string,
 *         dbaccess_path:string
 *     }>
 * }
 */
function app_project_output_runtime_dbaccess_entities(string $sourceRoot): array
{
    $dbaccessFiles = glob($sourceRoot . '/dbaccess-*.php');
    $entities = [];

    if (is_array($dbaccessFiles)) {
        sort($dbaccessFiles, SORT_STRING);

        foreach ($dbaccessFiles as $filePath) {
            $sourceName = app_generated_catalog_source_name($filePath, 'dbaccess-');
            if ($sourceName === '') {
                continue;
            }
            if (app_project_output_runtime_source_name_is_excluded($sourceName)) {
                continue;
            }

            $entities[] = [
                'source_name' => $sourceName,
                'dbaccess_file' => basename($filePath),
                'dbaccess_path' => $filePath,
            ];
        }
    }

    return [
        'entities' => $entities,
    ];
}

/**
 * @return array{
 *     entities:list<array{
 *         source_name:string,
 *         data_file:string,
 *         data_path:string
 *     }>
 * }
 */
function app_project_output_runtime_data_entities(string $sourceRoot): array
{
    $dataFiles = glob($sourceRoot . '/data-*.php');
    $entities = [];

    if (is_array($dataFiles)) {
        sort($dataFiles, SORT_STRING);

        foreach ($dataFiles as $filePath) {
            $sourceName = app_generated_catalog_source_name($filePath, 'data-');
            if ($sourceName === '') {
                continue;
            }
            if (app_project_output_runtime_source_name_is_excluded($sourceName)) {
                continue;
            }

            $entities[] = [
                'source_name' => $sourceName,
                'data_file' => basename($filePath),
                'data_path' => $filePath,
            ];
        }
    }

    return [
        'entities' => $entities,
    ];
}

function app_project_output_runtime_data_parent_source_name(string $sourceName): string
{
    foreach (['_leftouterjoin_', '_and_', '_with_'] as $separator) {
        $position = strpos($sourceName, $separator);
        if ($position === false) {
            continue;
        }

        return substr($sourceName, 0, $position);
    }

    return '';
}

/**
 * @return array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     property_names:list<string>,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool,
 *     is_plain_candidate:bool,
 *     source_layout:string,
 *     error:string
 * }
 */
function app_project_output_runtime_bootstrap_data_file_info(string $filePath): array
{
    $contents = file_get_contents($filePath);
    if (!is_string($contents) || $contents === '') {
        return app_project_output_runtime_bootstrap_data_file_info_error('data file の読み込みに失敗しました');
    }

    $sourceLayout = 'legacy-source';
    $logicalClassName = '';
    $logicalParentClass = '';
    $logicalClassCount = 0;
    $analysisPaths = [$filePath];
    $expectedBaseClassName = app_generated_runtime_expected_base_class_name($filePath);
    $baseCompanionPath = app_generated_runtime_base_companion_path($filePath);
    $layeredBaseCompanionPath = app_generated_runtime_layered_base_companion_path($filePath);
    $layeredWrapperCompanionPath = app_generated_runtime_layered_wrapper_companion_path($filePath);

    if (
        app_generated_file_exists_and_readable($layeredBaseCompanionPath)
        && app_generated_file_exists_and_readable($layeredWrapperCompanionPath)
    ) {
        $sourceLayout = 'generated-layered-stub';
        $wrapperContents = file_get_contents($layeredWrapperCompanionPath);
        $baseContents = file_get_contents($layeredBaseCompanionPath);
        if (!is_string($wrapperContents) || $wrapperContents === '' || !is_string($baseContents) || $baseContents === '') {
            return app_project_output_runtime_bootstrap_data_file_info_error(
                'layered runtime data file の読み込みに失敗しました',
                $sourceLayout,
            );
        }

        $wrapperClass = app_project_output_runtime_first_class_signature($wrapperContents);
        $baseClass = app_project_output_runtime_first_class_signature($baseContents);
        if (!$wrapperClass['ok'] || !$baseClass['ok']) {
            return app_project_output_runtime_bootstrap_data_file_info_error(
                'layered runtime data class 定義を解析できません',
                $sourceLayout,
            );
        }

        $logicalClassName = $wrapperClass['class_name'];
        $logicalParentClass = $baseClass['parent_class'];
        $logicalClassCount = 1 + count(
            array_values(
                array_filter(
                    app_generated_file_class_names_raw($layeredBaseCompanionPath),
                    static fn (string $className): bool => $className !== $expectedBaseClassName,
                ),
            ),
        );
        $analysisPaths = [$layeredWrapperCompanionPath, $layeredBaseCompanionPath];
    } elseif (app_generated_file_exists_and_readable($baseCompanionPath)) {
        $sourceLayout = 'generated-wrapper-base';
        $wrapperClass = app_project_output_runtime_first_class_signature($contents);
        $baseContents = file_get_contents($baseCompanionPath);
        if (!$wrapperClass['ok'] || !is_string($baseContents) || $baseContents === '') {
            return app_project_output_runtime_bootstrap_data_file_info_error(
                'wrapper/base runtime data class 定義を解析できません',
                $sourceLayout,
            );
        }

        $baseClass = app_project_output_runtime_first_class_signature($baseContents);
        if (!$baseClass['ok']) {
            return app_project_output_runtime_bootstrap_data_file_info_error(
                'wrapper/base runtime data base 定義を解析できません',
                $sourceLayout,
            );
        }

        $logicalClassName = $wrapperClass['class_name'];
        $logicalParentClass = $baseClass['parent_class'];
        $logicalClassCount = 1 + count(
            array_values(
                array_filter(
                    app_generated_file_class_names_raw($baseCompanionPath),
                    static fn (string $className): bool => $className !== $expectedBaseClassName,
                ),
            ),
        );
        $analysisPaths = [$filePath, $baseCompanionPath];
    } else {
        $rootClass = app_project_output_runtime_first_class_signature($contents);
        if (!$rootClass['ok']) {
            return app_project_output_runtime_bootstrap_data_file_info_error(
                'data class 定義を解析できません',
                $sourceLayout,
            );
        }

        $logicalClassName = $rootClass['class_name'];
        $logicalParentClass = $rootClass['parent_class'];
        $logicalClassCount = count(app_generated_file_class_names($filePath));
    }

    $extraMethodNames = array_values(
        array_filter(
            app_generated_file_method_names($filePath),
            static fn (string $name): bool => $name !== '__construct',
        ),
    );
    $hasTopLevelFunction = false;
    $hasDefaultPropertyValue = false;
    foreach ($analysisPaths as $analysisPath) {
        $analysisContents = file_get_contents($analysisPath);
        if (!is_string($analysisContents) || $analysisContents === '') {
            continue;
        }

        if (!$hasTopLevelFunction) {
            $hasTopLevelFunction = preg_match('/^function\s+[A-Za-z0-9_]+\s*\(/m', $analysisContents) === 1;
        }
        if (!$hasDefaultPropertyValue) {
            $hasDefaultPropertyValue = preg_match('/^\s*public\s+\$[A-Za-z0-9_]+\s*=/m', $analysisContents) === 1;
        }
    }

    return [
        'ok' => true,
        'class_name' => $logicalClassName,
        'parent_class' => $logicalParentClass,
        'property_names' => app_generated_file_property_names($filePath),
        'class_count' => $logicalClassCount,
        'extra_method_names' => $extraMethodNames,
        'has_top_level_function' => $hasTopLevelFunction,
        'has_default_property_value' => $hasDefaultPropertyValue,
        'is_plain_candidate' => (
            $logicalClassCount === 1
            && $extraMethodNames === []
            && !$hasTopLevelFunction
            && !$hasDefaultPropertyValue
        ),
        'source_layout' => $sourceLayout,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string
 * }
 */
function app_project_output_runtime_first_class_signature(string $contents): array
{
    $matches = [];
    if (!preg_match('/^\s*class\s+([A-Za-z0-9_]+)(?:\s+extends\s+([A-Za-z0-9_]+))?/m', $contents, $matches)) {
        return [
            'ok' => false,
            'class_name' => '',
            'parent_class' => '',
        ];
    }

    return [
        'ok' => true,
        'class_name' => $matches[1],
        'parent_class' => $matches[2] ?? '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     property_names:list<string>,
 *     class_count:int,
 *     extra_method_names:list<string>,
 *     has_top_level_function:bool,
 *     has_default_property_value:bool,
 *     is_plain_candidate:bool,
 *     source_layout:string,
 *     error:string
 * }
 */
function app_project_output_runtime_bootstrap_data_file_info_error(
    string $error,
    string $sourceLayout = 'legacy-source',
): array {
    return [
        'ok' => false,
        'class_name' => '',
        'parent_class' => '',
        'property_names' => [],
        'class_count' => 0,
        'extra_method_names' => [],
        'has_top_level_function' => false,
        'has_default_property_value' => false,
        'is_plain_candidate' => false,
        'source_layout' => $sourceLayout,
        'error' => $error,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     all_property_names:list<string>,
 *     generated_property_names:list<string>,
 *     wrapper_property_names:list<string>,
 *     wrapper_method_names:list<string>,
 *     generated_trailing_section:string,
 *     generated_trailing_class_names:list<string>,
 *     editable_areas:array{
 *         above:string,
 *         additional_class_definition:string,
 *         bottom:string
 *     },
 *     generated_base_additional_section:string,
 *     generated_bottom_class_names:list<string>,
 *     generated_wrapper_bottom_section:string,
 *     additional_class_definition_has_non_method_code:bool,
 *     additional_class_definition_has_unsupported_code:bool,
 *     bottom_has_unsupported_code:bool,
 *     has_default_property_value_outside_additional_class_definition:bool,
 *     error:string
 * }
 */
function app_project_output_runtime_empty_legacy_data_class_migration_info(): array
{
    return [
        'ok' => false,
        'class_name' => '',
        'parent_class' => '',
        'all_property_names' => [],
        'generated_property_names' => [],
        'wrapper_property_names' => [],
        'wrapper_method_names' => [],
        'generated_trailing_section' => '',
        'generated_trailing_class_names' => [],
        'editable_areas' => [
            'above' => '',
            'additional_class_definition' => '',
            'bottom' => '',
        ],
        'generated_base_additional_section' => '',
        'generated_bottom_class_names' => [],
        'generated_wrapper_bottom_section' => '',
        'additional_class_definition_has_non_method_code' => false,
        'additional_class_definition_has_unsupported_code' => false,
        'bottom_has_unsupported_code' => false,
        'has_default_property_value_outside_additional_class_definition' => false,
        'error' => '',
    ];
}

/**
 * @param array{
 *     ok:bool,
 *     class_name:string,
 *     parent_class:string,
 *     source_layout:string
 * } $bootstrapInfo
 * @return array{
 *     migration_source_path:string,
 *     expected_class_name:string,
 *     expected_parent_class:string,
 *     migration_info:array{
 *         ok:bool,
 *         class_name:string,
 *         parent_class:string,
 *         all_property_names:list<string>,
 *         generated_property_names:list<string>,
 *         wrapper_property_names:list<string>,
 *         wrapper_method_names:list<string>,
 *         generated_trailing_section:string,
 *         generated_trailing_class_names:list<string>,
 *         editable_areas:array{
 *             above:string,
 *             additional_class_definition:string,
 *             bottom:string
 *         },
 *         generated_base_additional_section:string,
 *         generated_bottom_class_names:list<string>,
 *         generated_wrapper_bottom_section:string,
 *         additional_class_definition_has_non_method_code:bool,
 *         additional_class_definition_has_unsupported_code:bool,
 *         bottom_has_unsupported_code:bool,
 *         has_default_property_value_outside_additional_class_definition:bool,
 *         error:string
 *     },
 *     supports_legacy_enum_migration:bool,
 *     supports_legacy_default_property_migration:bool,
 *     supports_legacy_method_only_migration:bool,
 *     supports_legacy_wrapper_property_method_migration:bool,
 *     supports_legacy_method_and_enum_migration:bool,
 *     supports_legacy_top_level_declaration_migration:bool
 * }
 */
function app_project_output_runtime_data_legacy_migration_support(
    string $filePath,
    array $bootstrapInfo,
): array {
    $migrationSourcePath = $filePath;
    $expectedClassName = trim((string) ($bootstrapInfo['class_name'] ?? ''));

    if ((string) ($bootstrapInfo['source_layout'] ?? '') === 'generated-layered-stub') {
        $layeredBaseCompanionPath = app_generated_runtime_layered_base_companion_path($filePath);
        if (app_generated_file_exists_and_readable($layeredBaseCompanionPath)) {
            $migrationSourcePath = $layeredBaseCompanionPath;
            $expectedClassName = app_generated_runtime_expected_base_class_name($filePath);
        }
    }

    $migrationInfo = app_legacy_data_class_migration_info($migrationSourcePath);
    $expectedParentClass = trim((string) ($bootstrapInfo['parent_class'] ?? ''));
    $classIdentityMatches = (string) ($migrationInfo['class_name'] ?? '') === $expectedClassName
        && (string) ($migrationInfo['parent_class'] ?? '') === $expectedParentClass;

    return [
        'migration_source_path' => $migrationSourcePath,
        'expected_class_name' => $expectedClassName,
        'expected_parent_class' => $expectedParentClass,
        'migration_info' => $migrationInfo,
        'supports_legacy_enum_migration' => $classIdentityMatches
            && app_legacy_data_class_supports_generated_enum_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
        'supports_legacy_default_property_migration' => $classIdentityMatches
            && app_legacy_data_class_supports_default_property_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
        'supports_legacy_method_only_migration' => $classIdentityMatches
            && app_legacy_data_class_supports_method_only_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
        'supports_legacy_wrapper_property_method_migration' => $classIdentityMatches
            && app_legacy_data_class_supports_wrapper_property_method_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
        'supports_legacy_method_and_enum_migration' => $classIdentityMatches
            && app_legacy_data_class_supports_method_and_enum_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
        'supports_legacy_top_level_declaration_migration' => $classIdentityMatches
            && app_legacy_data_class_supports_top_level_declaration_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
    ];
}

/**
 * @return list<string>
 */
function app_project_output_runtime_known_helper_data_properties(string $sourceName): array
{
    return match ($sourceName) {
        'MySQLShowColumn' => ['Field', 'Type', 'IsNull', 'IsKey', 'IsDefault', 'Extra'],
        default => [],
    };
}

/**
 * 補助 metadata slice がまだない間だけ、legacy dataclass / table schema 側で確認できる
 * property を canonical plain DTO へ補完する。
 *
 * @return list<string>
 */
function app_project_output_runtime_known_supplemental_data_properties(string $sourceName): array
{
    return match ($sourceName) {
        'ProjectUser' => [
            'ProjectUserInOtherProjectEmailForDropboxSharing',
            'ProjectUserInOtherProjectProjectPID',
        ],
        default => [],
    };
}

/**
 * @return array<string,string>
 */
function app_project_output_runtime_data_rollout_sample_gate_references(): array
{
    return [
        'default-property' => 'tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php',
        'companion-declarations' => 'tests/Integration/Sample10CompareOutputCompanionDeclarationsOutputTest.php',
        'method-only' => 'tests/Integration/Sample11DaDataclassMethodOnlyOutputTest.php',
        'wrapper-property-method' => 'tests/Integration/Sample12DbtablecolumnsWrapperPropertyOutputTest.php',
        'method-and-enum' => 'tests/Integration/Sample13ReqMethodAndEnumOutputTest.php',
        'top-level-declaration' => 'tests/Integration/LegacyTopLevelDeclarationMigrationTest.php',
    ];
}

/**
 * @return array<string,list<string>>
 */
function app_project_output_runtime_data_rollout_complex_source_names_by_lane(): array
{
    return [
        'default-property' => [
            'TestConditionSelection',
            'TestPattern',
        ],
        'companion-declarations' => [
            'BuildLog',
            'BuildSourceCache',
            'BuildSourceFuncCache',
            'BuildTokenCompletedItem',
            'BuildTokenProjectSourceOutput',
            'CompareOutput',
            'DBConnection',
            'LastBuild',
            'LiveCheckResult',
            'LiveCheckResultSummaryForEachHour',
            'ProjectGroup',
            'ProjectGroupTemplate',
            'TestPatternExecuteResult',
        ],
        'method-only' => [
            'CompareOutputAdditionalPath',
            'Da',
            'Dataclass',
            'LanguageResource',
            'MinutesAndRelatedTables',
            'da',
            'dataclass',
            'minutes_and_RelatedTables',
        ],
        'wrapper-property-method' => [
            'Dbtablecolumns',
            'dbtablecolumns',
        ],
        'method-and-enum' => [
            'DaCustomProxy',
            'DaCustomProxyFunc',
            'Dafunc',
            'Dafuncinserttargetfields',
            'Dafuncselecthaving',
            'Dafuncselectwhere',
            'Dafuncupdatedeletewhere',
            'Dafuncupdatetargetfields',
            'HtmlTemplateParameter',
            'Project',
            'ProjectSourceOutput',
            'Req',
            'daCustomProxy',
            'daCustomProxyFunc',
            'dafunc',
            'dafuncinserttargetfields',
            'dafuncselecthaving',
            'dafuncselectwhere',
            'dafuncupdatedeletewhere',
            'dafuncupdatetargetfields',
            'htmlTemplateParameter',
        ],
        'top-level-declaration' => [
            'HtmlTemplate',
            'ProjectUser',
            'SpecContent',
            'htmlTemplate',
        ],
    ];
}

/**
 * @return array{
 *     gate_type:string,
 *     lane:string,
 *     gate_reference:string,
 *     note:string
 * }
 */
function app_project_output_runtime_data_rollout_gate(string $sourceName, bool $isPlainCandidate): array
{
    if ($isPlainCandidate) {
        return [
            'gate_type' => 'direct-replacement',
            'lane' => 'plain-dto',
            'gate_reference' => '',
            'note' => 'plain DTO lane は manifest/self-loop が green なら direct replacement で進める。',
        ];
    }

    $sampleGateReferences = app_project_output_runtime_data_rollout_sample_gate_references();
    foreach (app_project_output_runtime_data_rollout_complex_source_names_by_lane() as $lane => $sourceNames) {
        if (in_array($sourceName, $sourceNames, true)) {
            return [
                'gate_type' => 'sample-test',
                'lane' => $lane,
                'gate_reference' => $sampleGateReferences[$lane] ?? '',
                'note' => 'complex/new form は sample gate を通してから promote する。',
            ];
        }
    }

    return [
        'gate_type' => 'manual-classification',
        'lane' => 'unknown-complex',
        'gate_reference' => '',
        'note' => 'non-plain source だが current rollout lane に未分類のため、sample gate を先に決める必要がある。',
    ];
}

function app_project_output_runtime_normalize_generation_reason_code(string $reasonCode): string
{
    return match ($reasonCode) {
        'generated-layered-runtime-wrapper-base' => 'generated-existing-runtime-wrapper-base',
        default => $reasonCode,
    };
}

/**
 * legacy DTO / table schema と一致させるため、canonical 側の stale property を一時的に除外する。
 *
 * @return list<string>
 */
function app_project_output_runtime_known_excluded_canonical_data_properties(string $sourceName): array
{
    return match ($sourceName) {
        default => [],
    };
}

/**
 * @return list<string>
 */
function app_project_output_runtime_known_bootstrap_only_data_properties(string $sourceName): array
{
    return match ($sourceName) {
        'daCustomProxyFunc_leftouterjoin_dafunc_and_da' => ['AuthType', 'SingleGetFuncPID'],
        default => [],
    };
}

function app_project_output_runtime_should_preserve_bootstrap_only_data_properties(string $sourceName): bool
{
    return match ($sourceName) {
        default => false,
    };
}

function app_project_output_runtime_append_unique_property(array &$properties, array &$seen, string $propertyName): void
{
    $normalizedPropertyName = trim($propertyName);
    if ($normalizedPropertyName === '' || isset($seen[$normalizedPropertyName])) {
        return;
    }

    $seen[$normalizedPropertyName] = true;
    $properties[] = $normalizedPropertyName;
}

/**
 * @param array<string,bool> $upstreamPropertySet
 */
function app_project_output_runtime_should_accept_designer_property(
    string $propertyName,
    string $sourceOfTruth,
    array $upstreamPropertySet,
): bool {
    $normalizedPropertyName = trim($propertyName);
    if ($normalizedPropertyName === '') {
        return false;
    }

    if ($upstreamPropertySet === []) {
        return true;
    }

    if (trim($sourceOfTruth) !== 'sync-bootstrap') {
        return true;
    }

    return isset($upstreamPropertySet[$normalizedPropertyName]);
}

function app_project_output_runtime_metadata_source_key(string $sourceName): string
{
    return strtolower(trim($sourceName));
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,list<string>>
 */
function app_project_output_runtime_metadata_property_map(array $items, string $childrenKey): array
{
    $map = [];

    foreach ($items as $item) {
        $sourceKey = app_project_output_runtime_metadata_source_key((string) ($item['name'] ?? ''));
        if ($sourceKey === '') {
            continue;
        }

        $properties = [];
        $seen = [];
        $children = $item[$childrenKey] ?? [];
        if (!is_array($children)) {
            continue;
        }

        foreach ($children as $child) {
            if (!is_array($child)) {
                continue;
            }

            app_project_output_runtime_append_unique_property(
                $properties,
                $seen,
                (string) ($child['name'] ?? ''),
            );
        }

        if ($properties === []) {
            continue;
        }

        $map[$sourceKey] = $properties;
    }

    return $map;
}

/**
 * @return array{
 *     data_class_fields_by_source:array<string,list<string>>,
 *     table_columns_by_source:array<string,list<string>>
 * }
 */
function app_project_output_runtime_upstream_metadata_property_maps(array $app, string $projectKey): array
{
    $dataClassFieldNamesBySource = [];
    $tableColumnNamesBySource = [];

    $dataClassSnapshot = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if ($dataClassSnapshot['ok']) {
        $dataClassFieldNamesBySource = app_project_output_runtime_metadata_property_map(
            $dataClassSnapshot['items'],
            'fields',
        );
    }

    $tableSnapshot = app_fetch_table_metadata_snapshot($app, $projectKey);
    if ($tableSnapshot['ok']) {
        $tableColumnNamesBySource = app_project_output_runtime_metadata_property_map(
            $tableSnapshot['items'],
            'columns',
        );
    }

    return [
        'data_class_fields_by_source' => $dataClassFieldNamesBySource,
        'table_columns_by_source' => $tableColumnNamesBySource,
    ];
}

/**
 * @param array<string,list<string>> $dataClassFieldNamesBySource
 * @param array<string,list<string>> $tableColumnNamesBySource
 * @return list<string>
 */
function app_project_output_runtime_upstream_data_properties(
    string $sourceName,
    array $dataClassFieldNamesBySource,
    array $tableColumnNamesBySource,
): array {
    $sourceKey = app_project_output_runtime_metadata_source_key($sourceName);
    if ($sourceKey === '') {
        return [];
    }

    $properties = [];
    $seen = [];

    foreach (($dataClassFieldNamesBySource[$sourceKey] ?? []) as $propertyName) {
        app_project_output_runtime_append_unique_property($properties, $seen, $propertyName);
    }

    foreach (($tableColumnNamesBySource[$sourceKey] ?? []) as $propertyName) {
        app_project_output_runtime_append_unique_property($properties, $seen, $propertyName);
    }

    return $properties;
}

/**
 * @param list<string> $properties
 * @param list<string> $propertiesToRemove
 * @return list<string>
 */
function app_project_output_runtime_without_properties(array $properties, array $propertiesToRemove): array
{
    if ($propertiesToRemove === []) {
        return array_values($properties);
    }

    $removeSet = [];
    foreach ($propertiesToRemove as $propertyName) {
        $normalizedPropertyName = trim($propertyName);
        if ($normalizedPropertyName === '') {
            continue;
        }

        $removeSet[$normalizedPropertyName] = true;
    }

    if ($removeSet === []) {
        return array_values($properties);
    }

    return array_values(
        array_filter(
            $properties,
            static fn (string $propertyName): bool => !isset($removeSet[$propertyName]),
        ),
    );
}

/**
 * @param list<string> $left
 * @param list<string> $right
 */
function app_project_output_runtime_property_lists_match_as_set(array $left, array $right): bool
{
    if (count($left) !== count($right)) {
        return false;
    }

    $leftSet = array_fill_keys($left, true);
    $rightSet = array_fill_keys($right, true);

    if (count($leftSet) !== count($rightSet)) {
        return false;
    }

    foreach ($leftSet as $propertyName => $_unused) {
        if (!isset($rightSet[$propertyName])) {
            return false;
        }
    }

    return true;
}

/**
 * @return array{
 *     ok:bool,
 *     property_names:list<string>,
 *     error:string
 * }
 */
function app_project_output_runtime_canonical_data_raw_properties(
    array $app,
    string $projectKey,
    string $sourceName,
    array $dataClassFieldNamesBySource = [],
    array $tableColumnNamesBySource = [],
): array {
    $upstreamProperties = app_project_output_runtime_upstream_data_properties(
        $sourceName,
        $dataClassFieldNamesBySource,
        $tableColumnNamesBySource,
    );

    $functionCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
    if (!$functionCatalogResult['ok']) {
        if ($upstreamProperties !== []) {
            return [
                'ok' => true,
                'property_names' => app_project_output_runtime_without_properties(
                    $upstreamProperties,
                    app_project_output_runtime_known_excluded_canonical_data_properties($sourceName),
                ),
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'property_names' => [],
            'error' => $functionCatalogResult['error'],
        ];
    }

    $properties = [];
    $seen = [];
    $upstreamPropertySet = array_fill_keys($upstreamProperties, true);

    foreach ($functionCatalogResult['items'] as $functionItem) {
        $functionName = (string) ($functionItem['function_name'] ?? '');
        $actionType = strtoupper(trim((string) ($functionItem['action_type'] ?? '')));
        $sourceOfTruth = trim((string) ($functionItem['source_of_truth'] ?? ''));
        $resolvedDataClassBaseName = trim((string) ($functionItem['data_class_base_name'] ?? ''));
        if ($resolvedDataClassBaseName === '') {
            $resolvedDataClassBaseName = $sourceName;
        }

        if (!in_array($actionType, ['SELECTLIST', 'SELECTSINGLE', 'INSERT', 'UPDATE', 'DELETE'], true)) {
            continue;
        }

        $designer = app_project_output_runtime_sql_fetch_designer_resources($app, $projectKey, $sourceName, $functionName);
        if (!$designer['ok']) {
            return [
                'ok' => false,
                'property_names' => [],
                'error' => $functionName . ': ' . $designer['error'],
            ];
        }

        if (in_array($actionType, ['SELECTLIST', 'SELECTSINGLE'], true) && $resolvedDataClassBaseName === $sourceName) {
            foreach ($designer['select_target_fields'] as $field) {
                $propertyName = trim((string) ($field['store_class_field_name'] ?? ''));
                if ($propertyName === '') {
                    $propertyName = trim((string) ($field['target_table_column_name'] ?? ''));
                }
                if (!app_project_output_runtime_should_accept_designer_property(
                    $propertyName,
                    $sourceOfTruth,
                    $upstreamPropertySet,
                )) {
                    continue;
                }

                app_project_output_runtime_append_unique_property($properties, $seen, $propertyName);
            }
        }

        if ($actionType === 'INSERT') {
            foreach ($designer['insert_target_fields'] as $field) {
                $propertyName = (string) ($field['target_table_column_name'] ?? '');
                if (!app_project_output_runtime_should_accept_designer_property(
                    $propertyName,
                    $sourceOfTruth,
                    $upstreamPropertySet,
                )) {
                    continue;
                }
                app_project_output_runtime_append_unique_property(
                    $properties,
                    $seen,
                    $propertyName,
                );
            }
        }

        if ($actionType === 'UPDATE') {
            foreach ($designer['update_target_fields'] as $field) {
                $propertyName = (string) ($field['target_table_column_name'] ?? '');
                if (!app_project_output_runtime_should_accept_designer_property(
                    $propertyName,
                    $sourceOfTruth,
                    $upstreamPropertySet,
                )) {
                    continue;
                }
                app_project_output_runtime_append_unique_property(
                    $properties,
                    $seen,
                    $propertyName,
                );
            }
        }

        if (in_array($actionType, ['UPDATE', 'DELETE'], true)) {
            foreach ($designer['update_delete_wheres'] as $field) {
                $propertyName = (string) ($field['target_table_column_name'] ?? '');
                if (!app_project_output_runtime_should_accept_designer_property(
                    $propertyName,
                    $sourceOfTruth,
                    $upstreamPropertySet,
                )) {
                    continue;
                }
                app_project_output_runtime_append_unique_property(
                    $properties,
                    $seen,
                    $propertyName,
                );
            }
        }
    }

    foreach (app_project_output_runtime_known_helper_data_properties($sourceName) as $propertyName) {
        app_project_output_runtime_append_unique_property($properties, $seen, $propertyName);
    }

    foreach ($upstreamProperties as $propertyName) {
        app_project_output_runtime_append_unique_property($properties, $seen, $propertyName);
    }

    foreach (app_project_output_runtime_known_supplemental_data_properties($sourceName) as $propertyName) {
        app_project_output_runtime_append_unique_property($properties, $seen, $propertyName);
    }

    $properties = app_project_output_runtime_without_properties(
        $properties,
        app_project_output_runtime_known_excluded_canonical_data_properties($sourceName),
    );

    return [
        'ok' => true,
        'property_names' => $properties,
        'error' => '',
    ];
}

/**
 * @param array<string,list<string>> $rawPropertiesBySource
 * @param array<string,string> $parentBySource
 * @param array<string,list<string>> $cache
 * @param array<string,bool> $stack
 * @return list<string>
 */
function app_project_output_runtime_effective_data_properties(
    string $sourceName,
    array $rawPropertiesBySource,
    array $parentBySource,
    array &$cache,
    array &$stack,
): array {
    if (isset($cache[$sourceName])) {
        return $cache[$sourceName];
    }

    if (isset($stack[$sourceName])) {
        return $rawPropertiesBySource[$sourceName] ?? [];
    }

    $stack[$sourceName] = true;

    $parentSourceName = $parentBySource[$sourceName] ?? '';
    $parentEffectiveProperties = [];
    if ($parentSourceName !== '' && isset($rawPropertiesBySource[$parentSourceName])) {
        $parentEffectiveProperties = app_project_output_runtime_effective_data_properties(
            $parentSourceName,
            $rawPropertiesBySource,
            $parentBySource,
            $cache,
            $stack,
        );
    }

    $declaredProperties = [];
    $parentPropertySet = array_fill_keys($parentEffectiveProperties, true);
    foreach (($rawPropertiesBySource[$sourceName] ?? []) as $propertyName) {
        if (isset($parentPropertySet[$propertyName])) {
            continue;
        }

        $declaredProperties[] = $propertyName;
    }

    $effectiveProperties = array_values(array_merge($parentEffectiveProperties, $declaredProperties));
    $cache[$sourceName] = $effectiveProperties;
    unset($stack[$sourceName]);

    return $effectiveProperties;
}

/**
 * @param list<string> $declaredProperties
 */
function app_project_output_runtime_generated_data_text(
    string $className,
    string $parentClassName,
    array $declaredProperties,
): string {
    $lines = [
        '<?php',
        '',
        '// Generated from canonical DB Access metadata.',
        '// Thin DTO generated from canonical select/write metadata.',
        '',
        'class ' . $className . ($parentClassName !== '' ? ' extends ' . $parentClassName : ''),
        '{',
    ];

    foreach ($declaredProperties as $propertyName) {
        $lines[] = '    public $' . $propertyName . ';';
    }

    if ($declaredProperties !== []) {
        $lines[] = '';
    }

    $lines[] = '    public function __construct()';
    $lines[] = '    {';
    $lines[] = '    }';
    $lines[] = '}';
    $lines[] = '';
    $lines[] = '?>';

    return implode("\n", $lines);
}

/**
 * @param list<array{
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     select_by_distinct:string,
 *     is_blob_target:string,
 *     detected_line:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $canonicalFunctions
 * @param array<string,array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * }> $methodByName
 * @param array<string,array{
 *     mode:string,
 *     body_lines:list<string>,
 *     reason:string,
 *     warning:string
 * }> $generatedMethodResults
 * @param list<string> $extraClassLines
 */
function app_project_output_runtime_generated_dbaccess_text(
    array $canonicalClassItem,
    array $canonicalFunctions,
    array $methodByName,
    array $generatedMethodResults,
    array $extraClassLines,
    string $legacySupportRelativePath,
    string $originalClass,
    string $legacyClass,
): string {
    $lines = [
        '<?php',
        '',
    ];

    if ($legacySupportRelativePath !== '') {
        $lines[] = "require_once __DIR__ . '/../" . $legacySupportRelativePath . "';";
        $lines[] = '';
    }

    $lines[] = '// Generated from canonical DB Access metadata.';
    $lines[] = '// Simple CRUD / joined select methods regenerate SQL when canonical metadata is sufficient.';
    $lines[] = '// Known helper-style methods regenerate canonical PHP bodies when SQL regeneration is not the right fit.';
    if ($legacySupportRelativePath !== '') {
        $lines[] = '// Remaining unsupported methods continue to delegate to copied legacy support under `_support/legacy-dbaccess/`.';
    } else {
        $lines[] = '// Current canonical runtime generation fully owns this class, so no legacy DBAccess parent is required.';
    }
    $lines[] = '';
    $lines[] = 'class ' . $originalClass . ($legacyClass !== '' ? ' extends ' . $legacyClass : '');
    $lines[] = '{';

    if ($canonicalFunctions === []) {
        $lines[] = '}';
        $lines[] = '';
        $lines[] = '?>';

        return implode("\n", $lines);
    }

    if ($extraClassLines !== []) {
        foreach ($extraClassLines as $classLine) {
            $lines[] = $classLine;
        }
        $lines[] = '';
    }

    foreach ($canonicalFunctions as $functionItem) {
        $functionName = $functionItem['function_name'];
        $method = $methodByName[$functionName] ?? null;
        if ($method === null) {
            continue;
        }

        $signature = trim((string) $method['signature']);
        if ($signature === '') {
            $signature = 'public function ' . $functionName . '()';
        }
        $signature = preg_replace('/\s*\{\s*$/', '', $signature) ?? $signature;

        $generatedMethodResult = $generatedMethodResults[$functionName] ?? [
            'mode' => 'legacy-delegate',
            'body_lines' => [
                '        return parent::' . $functionName . '(...func_get_args());',
            ],
            'reason' => 'generation result was not recorded',
            'warning' => '',
        ];
        $generationMode = trim((string) ($generatedMethodResult['mode'] ?? 'legacy-delegate'));
        $reason = trim((string) ($generatedMethodResult['reason'] ?? ''));

        $lines[] = '    // source_of_truth=' . $functionItem['source_of_truth']
            . ' action_type=' . ($functionItem['action_type'] !== '' ? $functionItem['action_type'] : '(blank)')
            . ' order=' . $functionItem['function_list_order']
            . ' generation=' . ($generationMode !== '' ? $generationMode : 'legacy-delegate');
        if ($reason !== '') {
            $lines[] = '    // reason=' . preg_replace('/\s+/', ' ', $reason);
        }
        $lines[] = '    ' . $signature;
        $lines[] = '    {';
        $bodyLines = $generatedMethodResult['body_lines'] ?? [];
        if (!is_array($bodyLines)) {
            $bodyLines = [];
        }

        foreach ($bodyLines as $bodyLine) {
            $lines[] = (string) $bodyLine;
        }
        $lines[] = '    }';
        $lines[] = '';
    }

    if (end($lines) === '') {
        array_pop($lines);
    }

    $lines[] = '}';
    $lines[] = '';
    $lines[] = '?>';

    return implode("\n", $lines);
}

function app_project_output_runtime_is_generated_dbaccess_wrapper_text(string $contents): bool
{
    return preg_match('/mtool_runtime_bundle_load_custom_wrapper\s*\(/', $contents) === 1
        && preg_match('/require_once __DIR__ \. \'\/base\/dbaccess-[^\']+Base\.php\';/', $contents) === 1;
}

function app_project_output_runtime_placeholder_legacy_dbaccess_support_text(string $legacyClass): string
{
    return <<<PHP
<?php

// Legacy DBAccess compatibility placeholder.
// Current canonical runtime generation does not require copied legacy methods for this class.

class {$legacyClass}
{
}

?>
PHP;
}

function app_project_output_runtime_transform_legacy_dbaccess_support_text(
    string $contents,
    string $originalClass,
    string $legacyClass,
    string $relativePath,
): string {
    if (trim($contents) === '' || app_project_output_runtime_is_generated_dbaccess_wrapper_text($contents)) {
        return app_project_output_runtime_placeholder_legacy_dbaccess_support_text($legacyClass);
    }

    $rewritten = preg_replace(
        '/^require_once __DIR__ \. \'\/(?:_runtime_loader\.php|base\/[^\']+\.php)\'\s*;\s*$/m',
        '',
        $contents,
    );
    if (!is_string($rewritten)) {
        throw new RuntimeException('legacy dbaccess support require 行の除去に失敗しました: ' . $relativePath);
    }

    $rewritten = preg_replace_callback(
        '/^(\s*)class\s+(?:' . preg_quote($originalClass, '/') . '|' . preg_quote($legacyClass, '/') . ')\b/m',
        static fn (array $matches): string => $matches[1] . 'class ' . $legacyClass,
        $rewritten,
        1,
        $count,
    );

    if (!is_string($rewritten) || $count !== 1) {
        throw new RuntimeException('legacy dbaccess support class 変換に失敗しました: ' . $relativePath);
    }

    return $rewritten;
}

/**
 * @return array{
 *     mode:string,
 *     bootstrap_file_count:int,
 *     canonical_class_count:int,
 *     canonical_function_count:int,
 *     generated_dbaccess_count:int,
 *     fallback_dbaccess_count:int,
 *     sql_regenerated_dbaccess_count:int,
 *     sql_regenerated_function_count:int,
 *     canonical_helper_function_count:int,
 *     legacy_delegate_function_count:int,
 *     generated_items:list<array{
 *         source_name:string,
 *         function_count:int,
 *         sql_regenerated_function_count:int,
 *         canonical_helper_function_count:int,
 *         legacy_delegate_function_count:int,
 *         source_of_truth:string
 *     }>,
 *     warnings:list<string>
 * }
 */
function app_project_output_runtime_overlay_canonical_dbaccess(
    array $app,
    string $projectKey,
    string $bootstrapSourceRoot,
    string $stageRoot,
): array {
    $dbaccessEntities = app_project_output_runtime_dbaccess_entities($bootstrapSourceRoot);
    $summary = [
        'mode' => 'bootstrap-copy',
        'bootstrap_file_count' => count($dbaccessEntities['entities']),
        'canonical_class_count' => 0,
        'canonical_function_count' => 0,
        'generated_dbaccess_count' => 0,
        'fallback_dbaccess_count' => count($dbaccessEntities['entities']),
        'sql_regenerated_dbaccess_count' => 0,
        'sql_regenerated_function_count' => 0,
        'canonical_helper_function_count' => 0,
        'legacy_delegate_function_count' => 0,
        'generated_items' => [],
        'warnings' => [],
    ];

    $classCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    if (!$classCatalogResult['ok']) {
        $summary['warnings'][] = 'canonical class catalog を読めないため runtime reference を使います: '
            . $classCatalogResult['error'];
        return $summary;
    }

    if ($classCatalogResult['items'] === []) {
        $summary['warnings'][] = 'canonical class row が空のため runtime reference を使います。';
        return $summary;
    }

    $classBySource = [];
    foreach ($classCatalogResult['items'] as $item) {
        $classBySource[$item['source_name']] = $item;
    }
    $summary['canonical_class_count'] = count($classCatalogResult['items']);

    foreach ($dbaccessEntities['entities'] as $entity) {
        $canonicalClassItem = $classBySource[$entity['source_name']] ?? null;
        if ($canonicalClassItem === null) {
            continue;
        }

        $functionCatalogResult = app_fetch_db_access_function_metadata_catalog(
            $app,
            $projectKey,
            $entity['source_name'],
        );
        if (!$functionCatalogResult['ok']) {
            $summary['warnings'][] = $entity['source_name']
                . ': canonical function catalog を読めないため runtime reference を維持します: '
                . $functionCatalogResult['error'];
            continue;
        }

        if ($functionCatalogResult['items'] === []) {
            $summary['warnings'][] = $entity['source_name']
                . ': canonical function row が空のため runtime reference を維持します。';
            continue;
        }

        $contents = file_get_contents($entity['dbaccess_path']);
        if (!is_string($contents) || $contents === '') {
            $summary['warnings'][] = $entity['source_name']
                . ': runtime reference dbaccess file を読めないため runtime reference を維持します。';
            continue;
        }

        if (!preg_match('/^\s*class\s+([A-Za-z0-9_]+)\b/m', $contents, $matches)) {
            $summary['warnings'][] = $entity['source_name']
                . ': class 名を解決できないため runtime reference を維持します。';
            continue;
        }

        $originalClass = $matches[1];
        $legacyClass = $originalClass . 'Legacy';
        $methodCatalog = app_generated_file_method_catalog($entity['dbaccess_path']);
        $methodByName = [];
        foreach ($methodCatalog as $method) {
            $methodByName[$method['name']] = $method;
        }

        $canonicalFunctions = $functionCatalogResult['items'];
        $missingSyncBootstrapMethods = [];
        $blockingMissingMethods = [];

        foreach ($canonicalFunctions as $functionItem) {
            if (!array_key_exists($functionItem['function_name'], $methodByName)) {
                if (($functionItem['source_of_truth'] ?? '') === 'sync-bootstrap') {
                    $missingSyncBootstrapMethods[] = $functionItem['function_name'];
                } else {
                    $blockingMissingMethods[] = $functionItem['function_name'];
                }
            }
        }

        if ($blockingMissingMethods !== []) {
            $summary['warnings'][] = $entity['source_name']
                . ': runtime reference dbaccess file に存在しない canonical function があるため runtime reference を維持します: '
                . implode(', ', $blockingMissingMethods);
            continue;
        }

        if ($missingSyncBootstrapMethods !== []) {
            $summary['warnings'][] = $entity['source_name']
                . ': stale sync-bootstrap function を runtime generation から除外します: '
                . implode(', ', $missingSyncBootstrapMethods);
            $canonicalFunctions = array_values(
                array_filter(
                    $canonicalFunctions,
                    static fn (array $functionItem): bool => array_key_exists(
                        (string) ($functionItem['function_name'] ?? ''),
                        $methodByName,
                    ),
                ),
            );
        }

        if ($canonicalFunctions === []) {
            $summary['warnings'][] = $entity['source_name']
                . ': runtime generation 対象の canonical function が残らないため runtime reference を維持します。';
            continue;
        }

        $legacySupportRelativePath = '_support/legacy-dbaccess/' . $entity['dbaccess_file'];
        $legacySupportSourceContents = $contents;
        $legacySupportSourcePath = $bootstrapSourceRoot . '/' . $legacySupportRelativePath;
        if (is_file($legacySupportSourcePath) && is_readable($legacySupportSourcePath)) {
            $legacySupportCandidateContents = file_get_contents($legacySupportSourcePath);
            if (
                is_string($legacySupportCandidateContents)
                && $legacySupportCandidateContents !== ''
                && !app_project_output_runtime_is_generated_dbaccess_wrapper_text($legacySupportCandidateContents)
            ) {
                $legacySupportSourceContents = $legacySupportCandidateContents;
            }
        }

        $generatedMethodResults = [];
        $regeneratedFunctionCount = 0;
        $helperFunctionCount = 0;
        $delegatedFunctionCount = 0;
        $extraClassLines = app_project_output_runtime_sql_known_helper_class_lines($entity['source_name']);

        foreach ($canonicalFunctions as $functionItem) {
            $functionName = (string) ($functionItem['function_name'] ?? '');
            $method = $methodByName[$functionName] ?? null;
            if ($method === null) {
                continue;
            }

            $generationResult = app_project_output_runtime_sql_try_generate_method(
                $app,
                $projectKey,
                $entity['source_name'],
                $functionItem,
                $method,
            );
            if (!$generationResult['ok']) {
                $generatedMethodResults[$functionName] = [
                    'mode' => 'legacy-delegate',
                    'body_lines' => [
                        '        return parent::' . $functionName . '(...func_get_args());',
                    ],
                    'reason' => 'generation helper failed',
                    'warning' => $entity['source_name'] . '::' . $functionName . ' generation helper failed',
                ];
                $delegatedFunctionCount++;
                continue;
            }

            $generatedMethodResults[$functionName] = $generationResult['result'];
            $generationMode = (string) ($generationResult['result']['mode'] ?? '');
            if ($generationMode === 'canonical-sql') {
                $regeneratedFunctionCount++;
            } elseif ($generationMode === 'canonical-helper') {
                $helperFunctionCount++;
            } elseif ($generationMode === 'canonical-constructor') {
                // Empty bootstrap constructors do not need legacy delegation.
            } else {
                $delegatedFunctionCount++;
            }

            $warning = trim((string) ($generationResult['result']['warning'] ?? ''));
            if ($warning !== '') {
                $summary['warnings'][] = $warning;
            }
        }

        if (
            $delegatedFunctionCount > 0
            && app_project_output_runtime_is_generated_dbaccess_wrapper_text($legacySupportSourceContents)
        ) {
            $summary['warnings'][] = $entity['source_name']
                . ': legacy delegate が必要ですが、有効な legacy support source を解決できないため runtime reference を維持します。';
            continue;
        }

        $legacySupportContents = app_project_output_runtime_transform_legacy_dbaccess_support_text(
            $legacySupportSourceContents,
            $originalClass,
            $legacyClass,
            $entity['dbaccess_file'],
        );
        app_project_output_write_text_file(
            $stageRoot . '/' . $legacySupportRelativePath,
            $legacySupportContents,
        );

        $generatedContents = app_project_output_runtime_generated_dbaccess_text(
            $canonicalClassItem,
            $canonicalFunctions,
            $methodByName,
            $generatedMethodResults,
            $extraClassLines,
            $delegatedFunctionCount > 0 ? $legacySupportRelativePath : '',
            $originalClass,
            $delegatedFunctionCount > 0 ? $legacyClass : '',
        );
        app_project_output_write_text_file(
            $stageRoot . '/' . $entity['dbaccess_file'],
            $generatedContents,
        );

        $summary['generated_dbaccess_count']++;
        $summary['fallback_dbaccess_count']--;
        $summary['canonical_function_count'] += count($canonicalFunctions);
        if ($regeneratedFunctionCount > 0) {
            $summary['sql_regenerated_dbaccess_count']++;
        }
        $summary['sql_regenerated_function_count'] += $regeneratedFunctionCount;
        $summary['canonical_helper_function_count'] += $helperFunctionCount;
        $summary['legacy_delegate_function_count'] += $delegatedFunctionCount;
        $summary['generated_items'][] = [
            'source_name' => $entity['source_name'],
            'function_count' => count($canonicalFunctions),
            'sql_regenerated_function_count' => $regeneratedFunctionCount,
            'canonical_helper_function_count' => $helperFunctionCount,
            'legacy_delegate_function_count' => $delegatedFunctionCount,
            'source_of_truth' => $canonicalClassItem['source_of_truth'],
        ];
    }

    if ($summary['sql_regenerated_function_count'] > 0) {
        $summary['mode'] = 'canonical-dbaccess-partial-sql-regenerated';
    } elseif ($summary['generated_dbaccess_count'] > 0) {
        $summary['mode'] = 'canonical-dbaccess-delegating-legacy';
    }

    return $summary;
}

/**
 * @return array{
 *     canonical_data_class_count:int,
 *     data_entity_count:int,
 *     plain_data_candidate_count:int,
 *     non_plain_data_candidate_count:int,
 *     bootstrap_data_class_count:int,
 *     data_generation_items:list<array<string,mixed>>,
 *     warnings:list<string>
 * }
 */
function app_project_output_runtime_overlay_canonical_data_classes(
    array $app,
    string $projectKey,
    string $bootstrapSourceRoot,
    string $stageRoot,
): array {
    $dataEntities = app_project_output_runtime_data_entities($bootstrapSourceRoot);
    $summary = [
        'canonical_data_class_count' => 0,
        'data_entity_count' => count($dataEntities['entities']),
        'plain_data_candidate_count' => 0,
        'non_plain_data_candidate_count' => 0,
        'bootstrap_data_class_count' => count($dataEntities['entities']),
        'data_generation_items' => [],
        'warnings' => [],
    ];

    $rawPropertiesBySource = [];
    $parentBySource = [];
    $bootstrapInfoBySource = [];
    $legacyMigrationSupportBySource = [];
    $itemsBySource = [];
    $upstreamMetadata = app_project_output_runtime_upstream_metadata_property_maps($app, $projectKey);

    foreach ($dataEntities['entities'] as $entity) {
        $sourceName = $entity['source_name'];
        $bootstrapInfo = app_project_output_runtime_bootstrap_data_file_info($entity['data_path']);
        $bootstrapInfoBySource[$sourceName] = $bootstrapInfo;
        $item = [
            'source_name' => $sourceName,
            'data_file' => $entity['data_file'],
            'decision' => 'bootstrap-copy',
            'reason_code' => 'bootstrap-data-file-parse-error',
            'reason' => 'runtime reference data file を解析できませんでした。',
            'rollout_gate_type' => 'manual-classification',
            'rollout_lane' => 'unknown-complex',
            'rollout_gate_reference' => '',
            'rollout_note' => 'bootstrap info が取れないため rollout lane を判定できません。',
            'is_plain_candidate' => false,
            'class_name' => '',
            'bootstrap_parent_class' => '',
            'canonical_parent_class' => '',
            'class_count' => 0,
            'extra_method_names' => [],
            'has_top_level_function' => false,
            'has_default_property_value' => false,
            'bootstrap_property_names' => [],
            'canonical_raw_property_names' => [],
            'generated_property_names' => [],
            'expected_declared_property_names' => [],
        ];

        if (!$bootstrapInfo['ok']) {
            $item['reason'] = 'data file を解析できないため runtime reference を維持します: '
                . $bootstrapInfo['error'];
            $itemsBySource[$sourceName] = $item;
            $summary['warnings'][] = $sourceName
                . ': data file を解析できないため runtime reference を維持します: '
                . $bootstrapInfo['error'];
            continue;
        }

        $rolloutGate = app_project_output_runtime_data_rollout_gate(
            $sourceName,
            (bool) $bootstrapInfo['is_plain_candidate'],
        );
        $item['rollout_gate_type'] = $rolloutGate['gate_type'];
        $item['rollout_lane'] = $rolloutGate['lane'];
        $item['rollout_gate_reference'] = $rolloutGate['gate_reference'];
        $item['rollout_note'] = $rolloutGate['note'];

        if ($bootstrapInfo['is_plain_candidate']) {
            $summary['plain_data_candidate_count']++;
        } else {
            $summary['non_plain_data_candidate_count']++;
            if ($rolloutGate['gate_type'] === 'manual-classification') {
                $summary['warnings'][] = $sourceName
                    . ': non-plain source の rollout lane が未分類です。sample gate を追加してください。';
            }
        }

        $legacyMigrationSupportBySource[$sourceName] = !$bootstrapInfo['is_plain_candidate']
            ? app_project_output_runtime_data_legacy_migration_support(
                $entity['data_path'],
                $bootstrapInfo,
            )
            : [
                'migration_source_path' => '',
                'expected_class_name' => '',
                'expected_parent_class' => '',
                'migration_info' => app_project_output_runtime_empty_legacy_data_class_migration_info(),
                'supports_legacy_enum_migration' => false,
                'supports_legacy_default_property_migration' => false,
                'supports_legacy_method_only_migration' => false,
                'supports_legacy_wrapper_property_method_migration' => false,
                'supports_legacy_method_and_enum_migration' => false,
                'supports_legacy_top_level_declaration_migration' => false,
            ];

        $parentBySource[$sourceName] = app_project_output_runtime_data_parent_source_name($sourceName);
        $canonicalParentClass = $parentBySource[$sourceName] !== '' ? ($parentBySource[$sourceName] . 'Data') : '';
        $item['is_plain_candidate'] = $bootstrapInfo['is_plain_candidate'];
        $item['class_name'] = $bootstrapInfo['class_name'];
        $item['bootstrap_parent_class'] = $bootstrapInfo['parent_class'];
        $item['canonical_parent_class'] = $canonicalParentClass;
        $item['class_count'] = $bootstrapInfo['class_count'];
        $item['extra_method_names'] = $bootstrapInfo['extra_method_names'];
        $item['has_top_level_function'] = $bootstrapInfo['has_top_level_function'];
        $item['has_default_property_value'] = $bootstrapInfo['has_default_property_value'];
        $item['bootstrap_property_names'] = $bootstrapInfo['property_names'];

        if (!$bootstrapInfo['is_plain_candidate']) {
            $plainCandidateBlockers = [];
            if ($bootstrapInfo['class_count'] !== 1) {
                $plainCandidateBlockers[] = 'class_count=' . $bootstrapInfo['class_count'];
            }
            if ($bootstrapInfo['extra_method_names'] !== []) {
                $plainCandidateBlockers[] = 'extra_methods=' . implode(',', $bootstrapInfo['extra_method_names']);
            }
            if ($bootstrapInfo['has_top_level_function']) {
                $plainCandidateBlockers[] = 'top_level_function';
            }
            if ($bootstrapInfo['has_default_property_value']) {
                $plainCandidateBlockers[] = 'default_property_value';
            }

            $item['reason_code'] = 'non-plain-bootstrap';
            $item['reason'] = $plainCandidateBlockers === []
                ? 'plain DTO 条件を満たさないため runtime reference を維持します。'
                : 'plain DTO 条件を満たさないため runtime reference を維持します: '
                    . implode('; ', $plainCandidateBlockers);
        }

        $canonicalRawProperties = app_project_output_runtime_canonical_data_raw_properties(
            $app,
            $projectKey,
            $sourceName,
            $upstreamMetadata['data_class_fields_by_source'],
            $upstreamMetadata['table_columns_by_source'],
        );
        if (!$canonicalRawProperties['ok']) {
            if ($bootstrapInfo['is_plain_candidate']) {
                $summary['warnings'][] = $sourceName
                    . ': canonical data property を導出できないため runtime reference を維持します: '
                    . $canonicalRawProperties['error'];
            }
            $item['reason_code'] = 'canonical-properties-unavailable';
            $item['reason'] = 'canonical data property を導出できないため runtime reference を維持します: '
                . $canonicalRawProperties['error'];
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        $item['canonical_raw_property_names'] = $canonicalRawProperties['property_names'];
        if ($canonicalRawProperties['property_names'] === []) {
            if ($bootstrapInfo['is_plain_candidate']) {
                $summary['warnings'][] = $sourceName
                    . ': canonical data property が空のため runtime reference を維持します。';
            }
            $item['reason_code'] = 'canonical-properties-empty';
            $item['reason'] = 'canonical data property が空のため runtime reference を維持します。';
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        $rawPropertiesBySource[$sourceName] = $canonicalRawProperties['property_names'];
        if ($bootstrapInfo['is_plain_candidate']) {
            $item['reason_code'] = 'pending-parent-check';
            $item['reason'] = 'plain DTO 候補として parent/property 整合性を確認中です。';
        }

        $itemsBySource[$sourceName] = $item;
    }

    $effectiveCache = [];
    foreach ($dataEntities['entities'] as $entity) {
        $sourceName = $entity['source_name'];
        $item = $itemsBySource[$sourceName] ?? [
            'source_name' => $sourceName,
            'data_file' => $entity['data_file'],
            'decision' => 'bootstrap-copy',
            'reason_code' => 'missing-item',
            'reason' => 'data overlay item が記録されていません。',
            'is_plain_candidate' => false,
            'class_name' => '',
            'bootstrap_parent_class' => '',
            'canonical_parent_class' => '',
            'class_count' => 0,
            'extra_method_names' => [],
            'has_top_level_function' => false,
            'has_default_property_value' => false,
            'bootstrap_property_names' => [],
            'canonical_raw_property_names' => [],
            'generated_property_names' => [],
            'expected_declared_property_names' => [],
        ];
        if (!isset($rawPropertiesBySource[$sourceName])) {
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        $bootstrapInfo = $bootstrapInfoBySource[$sourceName] ?? null;
        if ($bootstrapInfo === null || !$bootstrapInfo['ok']) {
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        $legacyMigrationSupport = $legacyMigrationSupportBySource[$sourceName] ?? [
            'migration_source_path' => '',
            'expected_class_name' => '',
            'expected_parent_class' => '',
            'migration_info' => app_project_output_runtime_empty_legacy_data_class_migration_info(),
            'supports_legacy_enum_migration' => false,
            'supports_legacy_default_property_migration' => false,
            'supports_legacy_method_only_migration' => false,
            'supports_legacy_wrapper_property_method_migration' => false,
            'supports_legacy_method_and_enum_migration' => false,
            'supports_legacy_top_level_declaration_migration' => false,
        ];
        $legacyMigrationInfo = $legacyMigrationSupport['migration_info'];
        $supportsLegacyEnumMigration = (bool) ($legacyMigrationSupport['supports_legacy_enum_migration'] ?? false);
        $supportsLegacyDefaultPropertyMigration = (bool) ($legacyMigrationSupport['supports_legacy_default_property_migration'] ?? false);
        $supportsLegacyMethodOnlyMigration = (bool) ($legacyMigrationSupport['supports_legacy_method_only_migration'] ?? false);
        $supportsLegacyWrapperPropertyMethodMigration = (bool) ($legacyMigrationSupport['supports_legacy_wrapper_property_method_migration'] ?? false);
        $supportsLegacyMethodAndEnumMigration = (bool) ($legacyMigrationSupport['supports_legacy_method_and_enum_migration'] ?? false);
        $supportsLegacyTopLevelDeclarationMigration = (bool) ($legacyMigrationSupport['supports_legacy_top_level_declaration_migration'] ?? false);
        $isGeneratedWrapperBaseRuntime = (string) ($bootstrapInfo['source_layout'] ?? '') === 'generated-wrapper-base';

        if (
            $isGeneratedWrapperBaseRuntime
            && !$bootstrapInfo['is_plain_candidate']
            && !$supportsLegacyDefaultPropertyMigration
            && !$supportsLegacyEnumMigration
            && !$supportsLegacyMethodOnlyMigration
            && !$supportsLegacyWrapperPropertyMethodMigration
            && !$supportsLegacyMethodAndEnumMigration
            && !$supportsLegacyTopLevelDeclarationMigration
        ) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-existing-runtime-wrapper-base';
            $item['reason'] = 'already-generated runtime wrapper/base data class をそのまま維持します。';
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        if (
            !$bootstrapInfo['is_plain_candidate']
            && !$supportsLegacyDefaultPropertyMigration
            && !$supportsLegacyEnumMigration
            && !$supportsLegacyMethodOnlyMigration
            && !$supportsLegacyWrapperPropertyMethodMigration
            && !$supportsLegacyMethodAndEnumMigration
            && !$supportsLegacyTopLevelDeclarationMigration
        ) {
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        $parentSourceName = $parentBySource[$sourceName] ?? '';
        $parentClassName = $parentSourceName !== '' ? ($parentSourceName . 'Data') : '';
        if ($bootstrapInfo['parent_class'] !== $parentClassName) {
            $summary['warnings'][] = $sourceName
                . ': parent class が canonical 推定と一致しないため runtime reference を維持します。';
            $item['reason_code'] = 'parent-class-mismatch';
            $item['reason'] = 'parent class が canonical 推定と一致しないため runtime reference を維持します。';
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        $stack = [];
        $effectiveProperties = app_project_output_runtime_effective_data_properties(
            $sourceName,
            $rawPropertiesBySource,
            $parentBySource,
            $effectiveCache,
            $stack,
        );
        $parentEffectiveProperties = [];
        if ($parentSourceName !== '' && isset($rawPropertiesBySource[$parentSourceName])) {
            $parentStack = [];
            $parentEffectiveProperties = app_project_output_runtime_effective_data_properties(
                $parentSourceName,
                $rawPropertiesBySource,
                $parentBySource,
                $effectiveCache,
                $parentStack,
            );
        }

        $parentPropertySet = array_fill_keys($parentEffectiveProperties, true);
        $declaredProperties = [];
        foreach ($effectiveProperties as $propertyName) {
            if (isset($parentPropertySet[$propertyName])) {
                continue;
            }

            $declaredProperties[] = $propertyName;
        }
        $item['generated_property_names'] = $declaredProperties;

        $usesLegacyMigrationDeclaredProperties = (
            $supportsLegacyDefaultPropertyMigration
            || $supportsLegacyEnumMigration
            || $supportsLegacyMethodOnlyMigration
            || $supportsLegacyWrapperPropertyMethodMigration
            || $supportsLegacyMethodAndEnumMigration
            || $supportsLegacyTopLevelDeclarationMigration
        );

        $bootstrapOnlyProperties = $usesLegacyMigrationDeclaredProperties
            ? []
            : app_project_output_runtime_known_bootstrap_only_data_properties($sourceName);
        $bootstrapComparisonProperties = $usesLegacyMigrationDeclaredProperties
            ? ($legacyMigrationInfo['generated_property_names'] ?? [])
            : app_project_output_runtime_without_properties(
                $bootstrapInfo['property_names'],
                $bootstrapOnlyProperties,
            );

        $expectedDeclaredProperties = $usesLegacyMigrationDeclaredProperties
            ? ($legacyMigrationInfo['generated_property_names'] ?? [])
            : $bootstrapInfo['property_names'];
        if ($declaredProperties !== $expectedDeclaredProperties) {
            if (
                $usesLegacyMigrationDeclaredProperties
                && app_project_output_runtime_property_lists_match_as_set(
                    $declaredProperties,
                    $expectedDeclaredProperties,
                )
            ) {
                $declaredProperties = $expectedDeclaredProperties;
                $item['generated_property_names'] = $declaredProperties;
            } elseif (
                $bootstrapOnlyProperties !== []
                && app_project_output_runtime_property_lists_match_as_set(
                    $declaredProperties,
                    $bootstrapComparisonProperties,
                )
            ) {
                if (app_project_output_runtime_should_preserve_bootstrap_only_data_properties($sourceName)) {
                    $expectedDeclaredProperties = $bootstrapInfo['property_names'];
                } else {
                    $expectedDeclaredProperties = $bootstrapComparisonProperties;
                }
                $declaredProperties = $expectedDeclaredProperties;
                $item['generated_property_names'] = $declaredProperties;
            } else {
                $item['expected_declared_property_names'] = $expectedDeclaredProperties;

                if (!app_project_output_runtime_property_lists_match_as_set(
                    $declaredProperties,
                    $expectedDeclaredProperties,
                )) {
                    $summary['warnings'][] = $sourceName
                        . ': canonical property list が runtime reference plain DTO と一致しないため runtime reference を維持します。';
                    $item['reason_code'] = 'property-list-mismatch';
                    $item['reason'] = 'canonical property list が runtime reference plain DTO と一致しないため runtime reference を維持します。';
                    $itemsBySource[$sourceName] = $item;
                    continue;
                }

                $declaredProperties = $expectedDeclaredProperties;
                $item['generated_property_names'] = $declaredProperties;
            }
        }

        if ($supportsLegacyEnumMigration) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-legacy-enum-wrapper-base';
            $item['reason'] = 'legacy data class に同居している enum type class を base file に分離し、editable area を wrapper へ移行します。';
            if ($item['expected_declared_property_names'] === []) {
                $item['expected_declared_property_names'] = $declaredProperties;
            }
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        if ($supportsLegacyMethodOnlyMigration) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-legacy-method-only-wrapper-base';
            $item['reason'] = 'legacy additional class definition の helper method 群を wrapper へ移し、generated property は base file へ分離します。';
            if ($item['expected_declared_property_names'] === []) {
                $item['expected_declared_property_names'] = $declaredProperties;
            }
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        if ($supportsLegacyWrapperPropertyMethodMigration) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-legacy-wrapper-property-method-wrapper-base';
            $item['reason'] = 'legacy additional class definition の wrapper property と helper method を wrapper へ移し、generated property は base file へ分離します。';
            if ($item['expected_declared_property_names'] === []) {
                $item['expected_declared_property_names'] = $declaredProperties;
            }
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        if ($supportsLegacyMethodAndEnumMigration) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-legacy-method-and-enum-wrapper-base';
            $item['reason'] = 'legacy helper method と trailing enum/type class を wrapper/base 構成へ分離し、bottom helper は wrapper に残します。';
            if ($item['expected_declared_property_names'] === []) {
                $item['expected_declared_property_names'] = $declaredProperties;
            }
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        if ($supportsLegacyTopLevelDeclarationMigration) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-legacy-top-level-declaration-wrapper-base';
            $item['reason'] = 'legacy helper method / top-level helper / support class declaration を wrapper/base 構成へ分離します。';
            if ($item['expected_declared_property_names'] === []) {
                $item['expected_declared_property_names'] = $declaredProperties;
            }
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        if ($supportsLegacyDefaultPropertyMigration) {
            $summary['canonical_data_class_count']++;
            $summary['bootstrap_data_class_count']--;
            $item['decision'] = 'generated';
            $item['reason_code'] = 'generated-legacy-default-property-wrapper-base';
            $item['reason'] = 'legacy default property editable area を wrapper/base 構成へ移行します。';
            if ($item['expected_declared_property_names'] === []) {
                $item['expected_declared_property_names'] = $declaredProperties;
            }
            $itemsBySource[$sourceName] = $item;
            continue;
        }

        app_project_output_write_text_file(
            $stageRoot . '/' . $entity['data_file'],
            app_project_output_runtime_generated_data_text(
                $bootstrapInfo['class_name'],
                $bootstrapInfo['parent_class'],
                $declaredProperties,
            ) . PHP_EOL,
        );
        $summary['canonical_data_class_count']++;
        $summary['bootstrap_data_class_count']--;
        $item['decision'] = 'generated';
        $item['reason_code'] = 'generated-canonical-plain-dto';
        $item['reason'] = 'canonical metadata から plain DTO を再生成しました。';
        if ($item['expected_declared_property_names'] === []) {
            $item['expected_declared_property_names'] = $declaredProperties;
        }
        $itemsBySource[$sourceName] = $item;
    }

    ksort($itemsBySource, SORT_STRING);
    $summary['data_generation_items'] = array_values($itemsBySource);

    return $summary;
}

/**
 * @param array{
 *     source_output_key:string,
 *     runtime_source_relative_path:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{
 *             relative_path:string,
 *             size:int
 *         }>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     generation_summary:array{
 *         mode:string,
 *         bootstrap_file_count:int,
 *         canonical_class_count:int,
 *         canonical_function_count:int,
 *         generated_dbaccess_count:int,
 *         fallback_dbaccess_count:int,
 *         sql_regenerated_dbaccess_count:int,
 *         sql_regenerated_function_count:int,
 *         canonical_helper_function_count:int,
 *         canonical_data_class_count:int,
 *         data_entity_count:int,
 *         plain_data_candidate_count:int,
 *         non_plain_data_candidate_count:int,
 *         bootstrap_data_class_count:int,
 *         legacy_delegate_function_count:int,
 *         generated_items:list<array{
 *             source_name:string,
 *             function_count:int,
 *             sql_regenerated_function_count:int,
 *             canonical_helper_function_count:int,
 *             legacy_delegate_function_count:int,
 *             source_of_truth:string
 *         }>,
 *         data_generation_items:list<array<string,mixed>>,
 *         warnings:list<string>
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_runtime_source_tree(array $app, string $projectKey, array $definition): array
{
    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '' || !app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_root' => '',
            'scan_result' => null,
            'generation_summary' => null,
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $bootstrapSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $bootstrapScanResult = app_project_output_scan_tree($bootstrapSourceRoot);
    if (!$bootstrapScanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_root' => '',
            'scan_result' => null,
            'generation_summary' => null,
            'error' => $bootstrapScanResult['error'],
        ];
    }

    $stageRoot = app_runtime_storage_project_output_runtime_staging_root(
        $app,
        $projectKey,
        (string) ($definition['source_output_key'] ?? ''),
    );
    $bootstrapFiles = app_project_output_runtime_filter_files($bootstrapScanResult['files']);

    try {
        app_project_output_delete_tree($stageRoot);
        app_project_output_copy_tree($bootstrapSourceRoot, $stageRoot, $bootstrapFiles);

        $generationSummary = app_project_output_runtime_overlay_canonical_dbaccess(
            $app,
            $projectKey,
            $bootstrapSourceRoot,
            $stageRoot,
        );
        $dataOverlaySummary = app_project_output_runtime_overlay_canonical_data_classes(
            $app,
            $projectKey,
            $bootstrapSourceRoot,
            $stageRoot,
        );
        $generationSummary['canonical_data_class_count'] = $dataOverlaySummary['canonical_data_class_count'];
        $generationSummary['data_entity_count'] = $dataOverlaySummary['data_entity_count'];
        $generationSummary['plain_data_candidate_count'] = $dataOverlaySummary['plain_data_candidate_count'];
        $generationSummary['non_plain_data_candidate_count'] = $dataOverlaySummary['non_plain_data_candidate_count'];
        $generationSummary['bootstrap_data_class_count'] = $dataOverlaySummary['bootstrap_data_class_count'];
        $generationSummary['data_generation_items'] = $dataOverlaySummary['data_generation_items'];
        $generationSummary['warnings'] = array_values(
            array_merge(
                $generationSummary['warnings'],
                $dataOverlaySummary['warnings'],
            ),
        );

        $ssoResolverSummary = app_project_output_runtime_emit_sso_app_user_resolver(
            $app,
            $projectKey,
            $stageRoot,
        );
        $generationSummary['sso_app_user_resolver'] = $ssoResolverSummary;
        if ($ssoResolverSummary['warning'] !== '') {
            $generationSummary['warnings'][] = $ssoResolverSummary['warning'];
        }

        app_project_output_write_json_file(
            $stageRoot . '/_support/runtime-generation-manifest.json',
            [
                'generated_at' => date(DATE_ATOM),
                'project_key' => app_normalize_project_key($projectKey),
                'source_output_key' => app_normalize_source_output_key((string) ($definition['source_output_key'] ?? '')),
                'runtime_source_relative_path' => $runtimeSourceRelativePath,
                'generation_summary' => $generationSummary,
            ],
        );
        app_project_output_runtime_rewrite_autoload_file(
            $stageRoot . '/autoload_mtool.php',
            $stageRoot,
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_root' => '',
            'scan_result' => null,
            'generation_summary' => null,
            'error' => 'runtime staging tree の作成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($stageRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_root' => '',
            'scan_result' => null,
            'generation_summary' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_root' => $stageRoot,
        'scan_result' => $scanResult,
        'generation_summary' => $generationSummary,
        'error' => '',
    ];
}

/** @return array{status:string,emitted:bool,files:list<string>,warning:string} */
function app_project_output_runtime_emit_sso_app_user_resolver(array $app, string $projectKey, string $stageRoot): array
{
    $policyResult = app_fetch_sso_app_user_project_policy($app, $projectKey);
    if (!$policyResult['ok']) {
        return ['status' => 'policy_read_failed', 'emitted' => false, 'files' => [], 'warning' => 'SSO app-user policy read failed: ' . $policyResult['error']];
    }
    if (!is_array($policyResult['item'])) {
        return ['status' => 'not_configured', 'emitted' => false, 'files' => [], 'warning' => ''];
    }
    $policy = $policyResult['item']['policy'];
    if (($policy['enabled'] ?? false) !== true) {
        return ['status' => 'disabled', 'emitted' => false, 'files' => [], 'warning' => ''];
    }

    $tableResult = app_fetch_table_metadata_snapshot($app, $projectKey);
    $dataClassResult = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    $constraintResult = app_fetch_project_table_constraints($app, $projectKey);
    $classResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    foreach ([$tableResult, $dataClassResult, $constraintResult, $classResult] as $result) {
        if (($result['ok'] ?? false) !== true) {
            return ['status' => 'metadata_read_failed', 'emitted' => false, 'files' => [], 'warning' => 'SSO app-user resolver metadata read failed: ' . (string) ($result['error'] ?? '')];
        }
    }

    $dbAccessClasses = [];
    foreach ($classResult['items'] as $class) {
        $sourceName = (string) ($class['source_name'] ?? '');
        $functions = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
        if (!$functions['ok']) {
            return ['status' => 'metadata_read_failed', 'emitted' => false, 'files' => [], 'warning' => 'SSO app-user DBAccess function read failed: ' . $functions['error']];
        }
        $class['functions'] = $functions['items'];
        $dbAccessClasses[] = $class;
    }
    $schemaValidation = app_sso_app_user_validate_canonical_schema(
        $policy,
        $tableResult['items'],
        $dataClassResult['items'],
        $dbAccessClasses,
        $constraintResult['snapshot'],
    );
    if (($schemaValidation['ready_for_generation'] ?? false) !== true) {
        $details = array_merge(
            is_array($schemaValidation['errors'] ?? null) ? $schemaValidation['errors'] : [],
            is_array($schemaValidation['blocking_gaps'] ?? null) ? $schemaValidation['blocking_gaps'] : [],
        );
        return [
            'status' => (string) ($schemaValidation['status'] ?? 'schema_not_ready'),
            'emitted' => false,
            'files' => [],
            'warning' => 'SSO app-user resolver was not emitted: ' . implode(' ', $details),
        ];
    }
    $contract = app_sso_app_user_generated_resolver_contract($policy, $schemaValidation, $dbAccessClasses);
    if (!$contract['ok']) {
        return [
            'status' => $contract['status'],
            'emitted' => false,
            'files' => [],
            'warning' => 'SSO app-user resolver was not emitted: ' . implode(' ', $contract['errors']),
        ];
    }

    $relativeRoot = '_support/sso-app-user';
    $sourceFiles = [
        'generated_name.php',
        'sso_app_user_design_guidance.php',
        'sso_app_user_project_policy.php',
        'sso_app_user_generated_resolver.php',
    ];
    $files = [];
    foreach ($sourceFiles as $sourceFile) {
        $contents = file_get_contents(__DIR__ . '/' . $sourceFile);
        if (!is_string($contents)) {
            throw new RuntimeException('SSO app-user runtime support source is unreadable: ' . $sourceFile);
        }
        $relativePath = $relativeRoot . '/' . $sourceFile;
        app_project_output_write_text_file($stageRoot . '/' . $relativePath, $contents);
        $files[] = $relativePath;
    }
    $contractPath = $relativeRoot . '/resolver-contract.php';
    app_project_output_write_text_file($stageRoot . '/' . $contractPath, $contract['artifact_text']);
    $files[] = $contractPath;
    return ['status' => 'emitted', 'emitted' => true, 'files' => $files, 'warning' => ''];
}
