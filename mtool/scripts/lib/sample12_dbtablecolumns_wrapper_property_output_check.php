<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_runtime_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once __DIR__ . '/reference_tree_compare.php';

const APP_SAMPLE12_DBTABLECOLUMNS_DATA_CLASS_NAME = 'dbtablecolumns';
const APP_SAMPLE12_DBTABLECOLUMNS_EXPECTED_PROPERTY_NAMES = [
    'ProjectPID',
    'dbtablePID',
    'PID',
    'name',
    'datatype',
    'IsNull',
    'IsKey',
    'IsDefault',
    'Extra',
    'ColumnListOrder',
    'memo',
];
const APP_SAMPLE12_DBTABLECOLUMNS_WRAPPER_PROPERTY_NAMES = [
    'ColumnListOrderSupposedToBe',
];
const APP_SAMPLE12_DBTABLECOLUMNS_EXPECTED_METHOD_NAMES = [
    'IsAutoIncrement',
    'NotSupportedDataTypeForInsertOrUpdateBasedOnDBType',
];

function app_sample12_dbtablecolumns_input_path(): string
{
    return app_runtime_storage_legacy_dbclasses_fixture_path('data-dbtablecolumns.php');
}

function app_sample12_dbtablecolumns_output_root(): string
{
    return dirname(__DIR__, 3) . '/work/sample-packs/pattern02-wrapper-property-helper/output';
}

function app_sample12_dbtablecolumns_default_reference_root(): string
{
    return app_sample_pack_reference_root('pattern02-wrapper-property-helper');
}

/**
 * @return array{
 *     ok:bool,
 *     requested_by:string,
 *     input_file:string,
 *     published_root:string,
 *     reference_root:string,
 *     compare_reference:bool,
 *     bootstrap_info:array<string,mixed>|null,
 *     migration_info:array<string,mixed>|null,
 *     file_checks:list<array<string,mixed>>,
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample12_dbtablecolumns_run(
    string $requestedBy,
    string $referenceRoot,
    bool $compareReference = true,
): array {
    $inputFile = app_sample12_dbtablecolumns_input_path();
    $outputRoot = app_sample12_dbtablecolumns_output_root();
    $publishedRoot = $outputRoot . '/DATACLASS-PHP';
    $assertionErrors = [];
    $fileChecks = [];

    if ($inputFile === '' || !is_file($inputFile)) {
        return [
            'ok' => false,
            'requested_by' => $requestedBy,
            'input_file' => $inputFile,
            'published_root' => $publishedRoot,
            'reference_root' => $referenceRoot,
            'compare_reference' => $compareReference,
            'bootstrap_info' => null,
            'migration_info' => null,
            'file_checks' => [],
            'assertion_errors' => [],
            'error' => 'sample12 input file がありません。',
        ];
    }

    $bootstrapInfo = app_project_output_runtime_bootstrap_data_file_info($inputFile);
    if (!$bootstrapInfo['ok']) {
        return [
            'ok' => false,
            'requested_by' => $requestedBy,
            'input_file' => $inputFile,
            'published_root' => $publishedRoot,
            'reference_root' => $referenceRoot,
            'compare_reference' => $compareReference,
            'bootstrap_info' => $bootstrapInfo,
            'migration_info' => null,
            'file_checks' => [],
            'assertion_errors' => [],
            'error' => 'sample12 bootstrap info の取得に失敗しました: ' . $bootstrapInfo['error'],
        ];
    }

    $migrationInfo = app_legacy_data_class_migration_info($inputFile);
    if (!$migrationInfo['ok']) {
        return [
            'ok' => false,
            'requested_by' => $requestedBy,
            'input_file' => $inputFile,
            'published_root' => $publishedRoot,
            'reference_root' => $referenceRoot,
            'compare_reference' => $compareReference,
            'bootstrap_info' => $bootstrapInfo,
            'migration_info' => $migrationInfo,
            'file_checks' => [],
            'assertion_errors' => [],
            'error' => 'sample12 migration info の取得に失敗しました: ' . $migrationInfo['error'],
        ];
    }

    $className = (string) ($migrationInfo['class_name'] ?? '');
    $dataClassName = app_legacy_data_class_source_name_from_class_name($className);
    $baseClassName = $className . 'Base';
    $wrapperRelativePath = 'data-' . $dataClassName . '.php';
    $baseRelativePath = 'base/data-' . $dataClassName . 'Base.php';

    app_reference_tree_assert_same(
        APP_SAMPLE12_DBTABLECOLUMNS_DATA_CLASS_NAME,
        $dataClassName,
        'sample12 data class name',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        1,
        (int) ($bootstrapInfo['class_count'] ?? 0),
        'sample12 class_count',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE12_DBTABLECOLUMNS_EXPECTED_METHOD_NAMES,
        $bootstrapInfo['extra_method_names'] ?? [],
        'sample12 bootstrap extra_method_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE12_DBTABLECOLUMNS_EXPECTED_PROPERTY_NAMES,
        $migrationInfo['generated_property_names'] ?? [],
        'sample12 generated_property_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE12_DBTABLECOLUMNS_WRAPPER_PROPERTY_NAMES,
        $migrationInfo['wrapper_property_names'] ?? [],
        'sample12 wrapper_property_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE12_DBTABLECOLUMNS_EXPECTED_METHOD_NAMES,
        $migrationInfo['wrapper_method_names'] ?? [],
        'sample12 wrapper_method_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        (bool) ($migrationInfo['additional_class_definition_has_non_method_code'] ?? false),
        'sample12 additional_class_definition_has_non_method_code',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        false,
        (bool) ($migrationInfo['additional_class_definition_has_unsupported_code'] ?? true),
        'sample12 additional_class_definition_has_unsupported_code',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        $migrationInfo['generated_trailing_class_names'] ?? [],
        'sample12 generated_trailing_class_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        app_legacy_data_class_supports_wrapper_property_method_wrapper_base_migration(
            $bootstrapInfo,
            $migrationInfo,
        ),
        'sample12 migration support',
        $assertionErrors,
    );

    try {
        app_project_output_delete_tree($outputRoot);
        app_project_output_ensure_directory($publishedRoot);
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
            'input_file' => $inputFile,
            'published_root' => $publishedRoot,
            'reference_root' => $referenceRoot,
            'compare_reference' => $compareReference,
            'bootstrap_info' => $bootstrapInfo,
            'migration_info' => $migrationInfo,
            'file_checks' => [],
            'assertion_errors' => $assertionErrors,
            'error' => 'sample12 output の生成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    if ($compareReference) {
        if ($referenceRoot === '' || !is_dir($referenceRoot)) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_file' => $inputFile,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => true,
                'bootstrap_info' => $bootstrapInfo,
                'migration_info' => $migrationInfo,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample12 reference root がありません。',
            ];
        }

        $expectedSnapshot = app_reference_tree_snapshot($referenceRoot . '/DATACLASS-PHP');
        if (!$expectedSnapshot['ok']) {
            return [
                'ok' => false,
                'requested_by' => $requestedBy,
                'input_file' => $inputFile,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => true,
                'bootstrap_info' => $bootstrapInfo,
                'migration_info' => $migrationInfo,
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
                'input_file' => $inputFile,
                'published_root' => $publishedRoot,
                'reference_root' => $referenceRoot,
                'compare_reference' => true,
                'bootstrap_info' => $bootstrapInfo,
                'migration_info' => $migrationInfo,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => $actualSnapshot['error'],
            ];
        }

        $fileChecks = app_reference_tree_compare_file_sets(
            $expectedSnapshot['files'],
            $actualSnapshot['files'],
            'sample12 DATACLASS-PHP',
            $assertionErrors,
        );
    }

    $ok = $assertionErrors === [];

    return [
        'ok' => $ok,
        'requested_by' => $requestedBy,
        'input_file' => $inputFile,
        'published_root' => $publishedRoot,
        'reference_root' => $referenceRoot,
        'compare_reference' => $compareReference,
        'bootstrap_info' => $bootstrapInfo,
        'migration_info' => $migrationInfo,
        'file_checks' => $fileChecks,
        'assertion_errors' => $assertionErrors,
        'error' => $ok ? '' : 'sample12 assertion failed',
    ];
}
