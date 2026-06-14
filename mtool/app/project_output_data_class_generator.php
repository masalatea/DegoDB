<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/legacy_data_class_editable_area_migrator.php';
require_once __DIR__ . '/project_output_template_renderer.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_data_class_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'canonical-dataclass-php';
}

function app_project_output_data_class_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_data_class_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_data_class_wrapper_relative_path(
    string $storeBasePath,
    string $dataClassName,
): string {
    return ($storeBasePath !== '' ? $storeBasePath . '/' : '')
        . 'data-'
        . $dataClassName
        . '.php';
}

function app_project_output_data_class_base_relative_path(
    string $storeBasePath,
    string $dataClassName,
): string {
    return ($storeBasePath !== '' ? $storeBasePath . '/' : '')
        . 'base/data-'
        . $dataClassName
        . 'Base.php';
}

/**
 * @return list<string>
 */
function app_project_output_data_class_relative_segments(string $path): array
{
    $normalizedPath = trim(str_replace('\\', '/', $path), '/');
    if ($normalizedPath === '' || $normalizedPath === '.') {
        return [];
    }

    return array_values(
        array_filter(
            explode('/', $normalizedPath),
            static fn ($segment): bool => is_string($segment) && $segment !== '' && $segment !== '.',
        ),
    );
}

function app_project_output_data_class_relative_require_path(
    string $fromRelativePath,
    string $toRelativePath,
): string {
    $fromDirSegments = app_project_output_data_class_relative_segments(dirname($fromRelativePath));
    $toSegments = app_project_output_data_class_relative_segments($toRelativePath);

    while ($fromDirSegments !== [] && $toSegments !== [] && $fromDirSegments[0] === $toSegments[0]) {
        array_shift($fromDirSegments);
        array_shift($toSegments);
    }

    $relativeSegments = array_merge(
        array_fill(0, count($fromDirSegments), '..'),
        $toSegments,
    );

    return implode('/', $relativeSegments);
}

/**
 * @param list<array{
 *     name:string,
 *     fields:list<array{
 *         name:string
 *     }>
 * }> $items
 * @return array<string,list<string>>
 */
function app_project_output_data_class_raw_field_names_by_class(array $items): array
{
    $map = [];

    foreach ($items as $item) {
        $className = trim((string) ($item['name'] ?? ''));
        if ($className === '') {
            continue;
        }

        $fieldNames = [];
        $seen = [];
        foreach (($item['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldName = trim((string) ($field['name'] ?? ''));
            if ($fieldName === '' || isset($seen[$fieldName])) {
                continue;
            }

            $seen[$fieldName] = true;
            $fieldNames[] = $fieldName;
        }

        $map[$className] = $fieldNames;
    }

    return $map;
}

/**
 * @param list<array{
 *     name:string,
 *     inherit_parent_data_class_name:string
 * }> $items
 * @return array<string,string>
 */
function app_project_output_data_class_parent_by_class(array $items): array
{
    $map = [];

    foreach ($items as $item) {
        $className = trim((string) ($item['name'] ?? ''));
        if ($className === '') {
            continue;
        }

        $map[$className] = trim((string) ($item['inherit_parent_data_class_name'] ?? ''));
    }

    return $map;
}

/**
 * @param array<string,list<string>> $rawFieldNamesByClass
 * @param array<string,string> $parentByClass
 * @param array<string,list<string>> $cache
 * @param array<string,bool> $stack
 * @return list<string>
 */
function app_project_output_data_class_effective_field_names(
    string $className,
    array $rawFieldNamesByClass,
    array $parentByClass,
    array &$cache,
    array &$stack,
): array {
    if (isset($cache[$className])) {
        return $cache[$className];
    }

    if (isset($stack[$className])) {
        return $rawFieldNamesByClass[$className] ?? [];
    }

    $stack[$className] = true;

    $parentClassName = $parentByClass[$className] ?? '';
    $parentEffectiveFieldNames = [];
    if ($parentClassName !== '' && isset($rawFieldNamesByClass[$parentClassName])) {
        $parentEffectiveFieldNames = app_project_output_data_class_effective_field_names(
            $parentClassName,
            $rawFieldNamesByClass,
            $parentByClass,
            $cache,
            $stack,
        );
    }

    $declaredFieldNames = [];
    $parentFieldSet = array_fill_keys($parentEffectiveFieldNames, true);
    foreach (($rawFieldNamesByClass[$className] ?? []) as $fieldName) {
        if (isset($parentFieldSet[$fieldName])) {
            continue;
        }

        $declaredFieldNames[] = $fieldName;
    }

    $effectiveFieldNames = array_values(array_merge($parentEffectiveFieldNames, $declaredFieldNames));
    $cache[$className] = $effectiveFieldNames;
    unset($stack[$className]);

    return $effectiveFieldNames;
}

/**
 * @param list<string> $declaredProperties
 */
function app_project_output_data_class_declared_properties_section(array $declaredProperties): string
{
    if ($declaredProperties === []) {
        return '';
    }

    $lines = [];
    foreach ($declaredProperties as $propertyName) {
        $lines[] = '    public $' . $propertyName . ';';
    }

    return implode("\n", $lines) . "\n\n";
}

/**
 * @param list<string> $declaredProperties
 */
function app_project_output_generated_data_class_base_php_text(
    string $className,
    string $parentClassName,
    array $declaredProperties,
): string {
    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dataclass-php/base.php.tpl',
            [
                'CLASS_SIGNATURE' => $className . ($parentClassName !== '' ? ' extends ' . $parentClassName : ''),
                'DECLARED_PROPERTIES_SECTION' => app_project_output_data_class_declared_properties_section(
                    $declaredProperties,
                ),
            ],
        ),
        "\r\n",
    );
}

function app_project_output_generated_data_class_wrapper_php_text(
    string $dataClassName,
    string $wrapperRelativePath,
    string $parentClassName,
    string $parentWrapperRelativePath,
): string {
    $className = $dataClassName . 'Data';
    $baseClassName = $className . 'Base';
    $baseRequirePath = var_export('/base/data-' . $dataClassName . 'Base.php', true);
    $parentRequireSection = '';

    if ($parentClassName !== '' && $parentWrapperRelativePath !== '') {
        $parentRequirePath = app_project_output_data_class_relative_require_path(
            $wrapperRelativePath,
            $parentWrapperRelativePath,
        );
        $parentRequireSection = 'require_once __DIR__ . '
            . var_export('/' . $parentRequirePath, true)
            . ';'
            . "\n";
    }

    return rtrim(
        app_project_output_render_reference_template(
            'canonical-dataclass-php/wrapper.php.tpl',
            [
                'PARENT_REQUIRE_SECTION' => $parentRequireSection,
                'BASE_REQUIRE_PATH' => $baseRequirePath,
                'CLASS_NAME' => $className,
                'BASE_CLASS_NAME' => $baseClassName,
            ],
        ),
        "\r\n",
    );
}

/**
 * @param list<string> $declaredProperties
 */
function app_project_output_generated_legacy_data_class_base_php_text(
    string $className,
    string $parentClassName,
    array $declaredProperties,
    string $trailingDeclarations = '',
): string {
    return rtrim(
        app_project_output_render_reference_template(
            'legacy-dataclass-php/base.php.tpl',
            [
                'CLASS_SIGNATURE' => $className . ($parentClassName !== '' ? ' extends ' . $parentClassName : ''),
                'DECLARED_PROPERTIES_SECTION' => app_project_output_data_class_declared_properties_section(
                    $declaredProperties,
                ),
                'TRAILING_DECLARATIONS_SECTION' => app_legacy_data_class_base_trailing_section(
                    $trailingDeclarations,
                ),
            ],
        ),
        "\r\n",
    );
}

function app_project_output_generated_legacy_data_class_wrapper_php_text(
    string $className,
    string $baseClassName,
    string $wrapperRelativePath,
    string $baseRelativePath,
    string $parentWrapperRelativePath,
    string $customAbove,
    string $customClassBody,
    string $customBottom,
): string {
    $baseRequirePath = app_project_output_data_class_relative_require_path(
        $wrapperRelativePath,
        $baseRelativePath,
    );
    $parentRequireSection = '';

    if ($parentWrapperRelativePath !== '') {
        $parentRequirePath = app_project_output_data_class_relative_require_path(
            $wrapperRelativePath,
            $parentWrapperRelativePath,
        );
        $parentRequireSection = 'require_once __DIR__ . '
            . var_export('/' . $parentRequirePath, true)
            . ';'
            . "\n";
    }

    return rtrim(
        app_project_output_render_reference_template(
            'legacy-dataclass-php/wrapper.php.tpl',
            [
                'ABOVE_SECTION' => app_legacy_data_class_wrapper_top_level_section($customAbove),
                'PARENT_REQUIRE_SECTION' => $parentRequireSection,
                'BASE_REQUIRE_PATH' => var_export('/' . $baseRequirePath, true),
                'CLASS_NAME' => $className,
                'BASE_CLASS_NAME' => $baseClassName,
                'WRAPPER_CLASS_BODY_SECTION' => app_legacy_data_class_wrapper_class_body_section(
                    $customClassBody,
                ),
                'BOTTOM_SECTION' => app_legacy_data_class_wrapper_top_level_section($customBottom),
            ],
        ),
        "\r\n",
    );
}

/**
 * @param array{
 *     source_output_key:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     program_language:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
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
 *     error:string
 * }
 */
function app_project_output_prepare_data_class_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_data_class_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の data class artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'php') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical data class artifact は現在 php のみ対応です。',
        ];
    }

    $snapshotResult = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if (!$snapshotResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical data class metadata の読み込みに失敗しました: ' . $snapshotResult['error'],
        ];
    }

    if ($snapshotResult['items'] === []) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'canonical data class metadata がありません。先に data class sync を実行してください。',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_data_class_default_runtime_source_relative_path(
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
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $rawFieldNamesByClass = app_project_output_data_class_raw_field_names_by_class($snapshotResult['items']);
    $parentByClass = app_project_output_data_class_parent_by_class($snapshotResult['items']);
    $storeBasePathByClass = [];
    foreach ($snapshotResult['items'] as $item) {
        $dataClassName = trim((string) ($item['name'] ?? ''));
        if ($dataClassName === '') {
            continue;
        }

        $storeBasePathByClass[$dataClassName] = trim(
            str_replace('\\', '/', (string) ($item['store_base_path'] ?? '')),
            '/',
        );
    }
    $effectiveFieldNameCache = [];
    $effectiveFieldNameStack = [];

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($snapshotResult['items'] as $item) {
            $dataClassName = trim((string) ($item['name'] ?? ''));
            if ($dataClassName === '') {
                continue;
            }

            $parentClassName = trim((string) ($item['inherit_parent_data_class_name'] ?? ''));
            $parentEffectiveFieldNames = [];
            if ($parentClassName !== '' && isset($rawFieldNamesByClass[$parentClassName])) {
                $parentEffectiveFieldNames = app_project_output_data_class_effective_field_names(
                    $parentClassName,
                    $rawFieldNamesByClass,
                    $parentByClass,
                    $effectiveFieldNameCache,
                    $effectiveFieldNameStack,
                );
            }

            $declaredFieldNames = [];
            $parentFieldSet = array_fill_keys($parentEffectiveFieldNames, true);
            foreach (($rawFieldNamesByClass[$dataClassName] ?? []) as $fieldName) {
                if (isset($parentFieldSet[$fieldName])) {
                    continue;
                }

                $declaredFieldNames[] = $fieldName;
            }

            $storeBasePath = trim(str_replace('\\', '/', (string) ($item['store_base_path'] ?? '')), '/');
            if ($storeBasePath !== '' && !app_project_output_relative_path_is_safe($storeBasePath)) {
                throw new RuntimeException(
                    'StoreBasePath の形式が不正です: '
                    . $dataClassName
                    . ' -> '
                    . (string) ($item['store_base_path'] ?? '')
                );
            }

            $wrapperRelativePath = app_project_output_data_class_wrapper_relative_path($storeBasePath, $dataClassName);
            $baseRelativePath = app_project_output_data_class_base_relative_path($storeBasePath, $dataClassName);
            $parentWrapperRelativePath = '';
            if ($parentClassName !== '') {
                $parentWrapperRelativePath = app_project_output_data_class_wrapper_relative_path(
                    $storeBasePathByClass[$parentClassName] ?? '',
                    $parentClassName,
                );
            }

            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $baseRelativePath,
                app_project_output_generated_data_class_base_php_text(
                    $dataClassName . 'DataBase',
                    $parentClassName !== '' ? $parentClassName . 'Data' : '',
                    $declaredFieldNames,
                ),
            );
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $wrapperRelativePath,
                app_project_output_generated_data_class_wrapper_php_text(
                    $dataClassName,
                    $wrapperRelativePath,
                    $parentClassName,
                    $parentWrapperRelativePath,
                ),
            );
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'data class staging tree の作成に失敗しました: ' . $throwable->getMessage(),
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
