<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once __DIR__ . '/reference_tree_compare.php';

const APP_SAMPLE9_TESTPATTERN_DEFAULT_PROPERTY_DATA_CLASS_NAME = 'TestPattern';

function app_sample9_testpattern_default_property_input_path(): string
{
    return app_runtime_storage_legacy_dbclasses_fixture_path('data-TestPattern.php');
}

function app_sample9_testpattern_default_property_output_root(): string
{
    return dirname(__DIR__, 3) . '/work/sample-packs/pattern01-default-property-split/output';
}

function app_sample9_testpattern_default_property_default_reference_root(): string
{
    return app_sample_pack_reference_root('pattern01-default-property-split');
}

/**
 * @return array{
 *     ok:bool,
 *     requested_by:string,
 *     input_file:string,
 *     published_root:string,
 *     reference_root:string,
 *     compare_reference:bool,
 *     migration_info:array<string,mixed>|null,
 *     file_checks:list<array<string,mixed>>,
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample9_testpattern_default_property_run(
    string $requestedBy,
    string $referenceRoot,
    bool $compareReference = true,
): array {
    $inputFile = app_sample9_testpattern_default_property_input_path();
    $outputRoot = app_sample9_testpattern_default_property_output_root();
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
            'migration_info' => null,
            'file_checks' => [],
            'assertion_errors' => [],
            'error' => 'sample9 input file がありません。',
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
            'migration_info' => $migrationInfo,
            'file_checks' => [],
            'assertion_errors' => [],
            'error' => 'legacy data class migration info の取得に失敗しました: ' . $migrationInfo['error'],
        ];
    }

    $className = (string) ($migrationInfo['class_name'] ?? '');
    $dataClassName = app_legacy_data_class_source_name_from_class_name($className);
    $parentClassName = (string) ($migrationInfo['parent_class'] ?? '');
    $parentDataClassName = app_legacy_data_class_source_name_from_class_name($parentClassName);
    $baseClassName = $className . 'Base';
    $wrapperRelativePath = 'data-' . $dataClassName . '.php';
    $baseRelativePath = 'base/data-' . $dataClassName . 'Base.php';
    $parentWrapperRelativePath = $parentDataClassName !== ''
        ? ('data-' . $parentDataClassName . '.php')
        : '';

    app_reference_tree_assert_same(
        APP_SAMPLE9_TESTPATTERN_DEFAULT_PROPERTY_DATA_CLASS_NAME,
        $dataClassName,
        'sample9 data class name',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        false,
        (bool) ($migrationInfo['has_default_property_value_outside_additional_class_definition'] ?? false),
        'sample9 default property must stay inside additional class definition',
        $assertionErrors,
    );

    try {
        app_project_output_delete_tree($outputRoot);
        app_project_output_ensure_directory($publishedRoot);
        app_project_output_write_text_file(
            $publishedRoot . '/' . $baseRelativePath,
            app_project_output_generated_legacy_data_class_base_php_text(
                $baseClassName,
                $parentClassName,
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
                $parentWrapperRelativePath,
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
            'migration_info' => $migrationInfo,
            'file_checks' => [],
            'assertion_errors' => $assertionErrors,
            'error' => 'sample9 output の生成に失敗しました: ' . $throwable->getMessage(),
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
                'migration_info' => $migrationInfo,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => 'sample9 reference root がありません。',
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
                'migration_info' => $migrationInfo,
                'file_checks' => [],
                'assertion_errors' => $assertionErrors,
                'error' => $actualSnapshot['error'],
            ];
        }

        $fileChecks = app_reference_tree_compare_file_sets(
            $expectedSnapshot['files'],
            $actualSnapshot['files'],
            'sample9 DATACLASS-PHP',
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
        'migration_info' => $migrationInfo,
        'file_checks' => $fileChecks,
        'assertion_errors' => $assertionErrors,
        'error' => $ok ? '' : 'sample9 TestPattern default-property output check failed.',
    ];
}
