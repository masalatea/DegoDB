<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_runtime_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once __DIR__ . '/reference_tree_compare.php';

const APP_SAMPLE11_METHOD_ONLY_EXPECTED_METHOD_NAMES = [
    'NormalizeIsAutoloadProperty',
    'GetIsAutoloadBoolean',
    'GetIsAutoloadCaption',
];
const APP_SAMPLE11_METHOD_ONLY_INPUTS = [
    'da',
    'dataclass',
];

function app_sample11_da_dataclass_method_only_input_path(string $dataClassName): string
{
    return app_runtime_storage_legacy_dbclasses_fixture_path('data-' . $dataClassName . '.php');
}

function app_sample11_da_dataclass_method_only_output_root(): string
{
    return dirname(__DIR__, 3) . '/work/sample-packs/pattern03-method-only-split/output';
}

function app_sample11_da_dataclass_method_only_default_reference_root(): string
{
    return app_sample_pack_reference_root('pattern03-method-only-split');
}

/**
 * @return array{
 *     ok:bool,
 *     requested_by:string,
 *     input_files:list<string>,
 *     published_root:string,
 *     reference_root:string,
 *     compare_reference:bool,
 *     bootstrap_info_by_data_class:array<string,array<string,mixed>>,
 *     migration_info_by_data_class:array<string,array<string,mixed>>,
 *     file_checks:list<array<string,mixed>>,
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample11_da_dataclass_method_only_run(
    string $requestedBy,
    string $referenceRoot,
    bool $compareReference = true,
): array {
    $outputRoot = app_sample11_da_dataclass_method_only_output_root();
    $publishedRoot = $outputRoot . '/DATACLASS-PHP';
    $assertionErrors = [];
    $fileChecks = [];
    $inputFiles = [];
    $bootstrapInfoByDataClass = [];
    $migrationInfoByDataClass = [];

    try {
        app_project_output_delete_tree($outputRoot);
        app_project_output_ensure_directory($publishedRoot);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'requested_by' => $requestedBy,
            'input_files' => [],
            'published_root' => $publishedRoot,
            'reference_root' => $referenceRoot,
            'compare_reference' => $compareReference,
            'bootstrap_info_by_data_class' => [],
            'migration_info_by_data_class' => [],
            'file_checks' => [],
            'assertion_errors' => [],
            'error' => 'sample11 output root の初期化に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    foreach (APP_SAMPLE11_METHOD_ONLY_INPUTS as $expectedDataClassName) {
        $inputFile = app_sample11_da_dataclass_method_only_input_path($expectedDataClassName);
        $inputFiles[] = $inputFile;

        if ($inputFile === '' || !is_file($inputFile)) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => $compareReference,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample11 input file がありません: ' . $expectedDataClassName,
            ];
        }

        $bootstrapInfo = app_project_output_runtime_bootstrap_data_file_info($inputFile);
        $bootstrapInfoByDataClass[$expectedDataClassName] = $bootstrapInfo;
        if (!$bootstrapInfo['ok']) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => $compareReference,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample11 bootstrap info の取得に失敗しました: ' . $expectedDataClassName . ': ' . $bootstrapInfo['error'],
            ];
        }

        $migrationInfo = app_legacy_data_class_migration_info($inputFile);
        $migrationInfoByDataClass[$expectedDataClassName] = $migrationInfo;
        if (!$migrationInfo['ok']) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => $compareReference,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample11 migration info の取得に失敗しました: ' . $expectedDataClassName . ': ' . $migrationInfo['error'],
            ];
        }

        $className = (string) ($migrationInfo['class_name'] ?? '');
        $dataClassName = app_legacy_data_class_source_name_from_class_name($className);
        $baseClassName = $className . 'Base';
        $wrapperRelativePath = 'data-' . $dataClassName . '.php';
        $baseRelativePath = 'base/data-' . $dataClassName . 'Base.php';

        app_reference_tree_assert_same(
            $expectedDataClassName,
            $dataClassName,
            'sample11 data class name: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            1,
            (int) ($bootstrapInfo['class_count'] ?? 0),
            'sample11 class_count: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            APP_SAMPLE11_METHOD_ONLY_EXPECTED_METHOD_NAMES,
            $bootstrapInfo['extra_method_names'] ?? [],
            'sample11 bootstrap extra_method_names: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            [],
            $migrationInfo['wrapper_property_names'] ?? [],
            'sample11 wrapper_property_names: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            APP_SAMPLE11_METHOD_ONLY_EXPECTED_METHOD_NAMES,
            $migrationInfo['wrapper_method_names'] ?? [],
            'sample11 wrapper_method_names: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            false,
            (bool) ($migrationInfo['additional_class_definition_has_non_method_code'] ?? true),
            'sample11 additional_class_definition_has_non_method_code: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            [],
            $migrationInfo['generated_trailing_class_names'] ?? [],
            'sample11 generated_trailing_class_names: ' . $expectedDataClassName,
            $assertionErrors,
        );
        app_reference_tree_assert_same(
            true,
            app_legacy_data_class_supports_method_only_wrapper_base_migration(
                $bootstrapInfo,
                $migrationInfo,
            ),
            'sample11 migration support: ' . $expectedDataClassName,
            $assertionErrors,
        );

        try {
            app_project_output_write_text_file(
                $publishedRoot . '/' . $baseRelativePath,
                app_project_output_generated_legacy_data_class_base_php_text(
                    $baseClassName,
                    (string) ($migrationInfo['parent_class'] ?? ''),
                    $migrationInfo['generated_property_names'] ?? [],
                ),
            );
            app_project_output_write_text_file(
                $publishedRoot . '/' . $wrapperRelativePath,
                app_project_output_generated_legacy_data_class_wrapper_php_text(
                    $className,
                    $baseClassName,
                    $wrapperRelativePath,
                    $baseRelativePath,
                    '',
                    (string) ($migrationInfo['editable_areas']['above'] ?? ''),
                    (string) ($migrationInfo['editable_areas']['additional_class_definition'] ?? ''),
                    (string) ($migrationInfo['editable_areas']['bottom'] ?? ''),
                ),
            );
        } catch (Throwable $throwable) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => $compareReference,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample11 output の生成に失敗しました: ' . $expectedDataClassName . ': ' . $throwable->getMessage(),
            ];
        }
    }

    if ($compareReference) {
        if ($referenceRoot === '' || !is_dir($referenceRoot)) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => true,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample11 reference root がありません。',
            ];
        }

        $expectedSnapshot = app_reference_tree_snapshot($referenceRoot . '/DATACLASS-PHP');
        if (!$expectedSnapshot['ok']) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => true,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => $expectedSnapshot['error'],
            ];
        }

        $actualSnapshot = app_reference_tree_snapshot($publishedRoot);
        if (!$actualSnapshot['ok']) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_files' => $inputFiles,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => true,
                'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
                'migration_info_by_data_class' => $migrationInfoByDataClass,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => $actualSnapshot['error'],
            ];
        }

        $fileChecks = app_reference_tree_compare_file_sets(
            $expectedSnapshot['files'],
            $actualSnapshot['files'],
            'sample11 DATACLASS-PHP',
            $assertionErrors,
        );
    }

    $ok = $assertionErrors === [];

    return [
        'ok' => $ok,
        'requested_by' => $requestedBy,
        'input_files' => $inputFiles,
        'published_root' => $publishedRoot,
        'reference_root' => $referenceRoot,
        'compare_reference' => $compareReference,
        'bootstrap_info_by_data_class' => $bootstrapInfoByDataClass,
        'migration_info_by_data_class' => $migrationInfoByDataClass,
        'file_checks' => $fileChecks,
        'assertion_errors' => $assertionErrors,
        'error' => $ok ? '' : 'sample11 da / dataclass method-only output check failed.',
    ];
}
