<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_runtime_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once __DIR__ . '/reference_tree_compare.php';

const APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_DATA_CLASS_NAME = 'ProjectSourceOutput';
const APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_EXPECTED_METHOD_NAMES = [
    'GetOneLineShortCaptionForHtml',
    'GetOneLineShortCaptionForLanguageResource',
    'IsProxyServer',
    'IsProxyClient',
    'IsDBaaSProxy',
    'IsNonDBaaSProxy',
    'IsXCode',
    'IsDotNetUWP',
    'GetCSNameSpaceByConsideringDefault',
    'GetJavaPackageNameByConsideringDefault',
    'GetTargtServerPSOProxyBaseURLWithLastSlush',
];
const APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_TRAILING_CLASS_NAMES = [
    'ProjectSourceOutputProgramLanguageEnum',
    'ProjectSourceOutputClassTypeEnum',
    'ProjectSourceOutputReleaseTargetTypeEnum',
    'ProjectSourceOutputJavaFunctionTypeEnum',
    'ProjectSourceOutputDotNetLanguageResourceTypeEnum',
];

function app_sample22_projectsourceoutput_input_path(): string
{
    return app_runtime_storage_legacy_dbclasses_fixture_path('data-ProjectSourceOutput.php');
}

function app_sample22_projectsourceoutput_output_root(): string
{
    return dirname(__DIR__, 3) . '/work/sample-packs/pattern14-method-and-enum-heavy-multimethod/output';
}

function app_sample22_projectsourceoutput_default_reference_root(): string
{
    return app_sample_pack_reference_root('pattern14-method-and-enum-heavy-multimethod');
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
function app_sample22_projectsourceoutput_run(
    string $requestedBy,
    string $referenceRoot,
    bool $compareReference = true,
): array {
    $inputFile = app_sample22_projectsourceoutput_input_path();
    $outputRoot = app_sample22_projectsourceoutput_output_root();
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
            'error' => 'sample22 input file がありません。',
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
            'error' => 'sample22 bootstrap info の取得に失敗しました: ' . $bootstrapInfo['error'],
        ];
    }

    $legacyMigrationSupport = app_project_output_runtime_data_legacy_migration_support(
        $inputFile,
        $bootstrapInfo,
    );
    $migrationInfo = $legacyMigrationSupport['migration_info'];
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
            'error' => 'sample22 migration info の取得に失敗しました: ' . $migrationInfo['error'],
        ];
    }

    $className = (string) ($migrationInfo['class_name'] ?? '');
    $dataClassName = app_legacy_data_class_source_name_from_class_name($className);
    $baseClassName = $className . 'Base';
    $wrapperRelativePath = 'data-' . $dataClassName . '.php';
    $baseRelativePath = 'base/data-' . $dataClassName . 'Base.php';

    app_reference_tree_assert_same(
        APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_DATA_CLASS_NAME,
        $dataClassName,
        'sample22 data class name',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        6,
        (int) ($bootstrapInfo['class_count'] ?? 0),
        'sample22 class_count',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        (bool) ($bootstrapInfo['has_top_level_function'] ?? false),
        'sample22 has_top_level_function',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_EXPECTED_METHOD_NAMES,
        $bootstrapInfo['extra_method_names'] ?? [],
        'sample22 bootstrap extra_method_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        $migrationInfo['wrapper_property_names'] ?? [],
        'sample22 wrapper_property_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_EXPECTED_METHOD_NAMES,
        $migrationInfo['wrapper_method_names'] ?? [],
        'sample22 wrapper_method_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE22_PROJECT_SOURCE_OUTPUT_TRAILING_CLASS_NAMES,
        $migrationInfo['generated_trailing_class_names'] ?? [],
        'sample22 generated trailing class names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        app_legacy_data_class_class_names_from_section(
            (string) ($migrationInfo['editable_areas']['bottom'] ?? ''),
        ),
        'sample22 bottom section class names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        $migrationInfo['generated_bottom_class_names'] ?? [],
        'sample22 generated_bottom_class_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        app_legacy_data_class_supports_method_and_enum_wrapper_base_migration(
            $bootstrapInfo,
            $migrationInfo,
        ),
        'sample22 migration support',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        (bool) ($legacyMigrationSupport['supports_legacy_method_and_enum_migration'] ?? false),
        'sample22 runtime migration support',
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
                (string) ($migrationInfo['generated_trailing_section'] ?? ''),
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
            'error' => 'sample22 output の生成に失敗しました: ' . $throwable->getMessage(),
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
                'error' => 'sample22 reference root がありません。',
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
            'sample22 DATACLASS-PHP',
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
        'error' => $ok ? '' : 'sample22 assertion failed',
    ];
}
