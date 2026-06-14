<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_runtime_generator.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once __DIR__ . '/reference_tree_compare.php';

const APP_SAMPLE18_PROJECT_USER_DATA_CLASS_NAME = 'ProjectUser';
const APP_SAMPLE18_PROJECT_USER_BOTTOM_CLASS_NAMES = [
    'ProjectUserSerurityEnum',
];
const APP_SAMPLE18_PROJECT_USER_TRAILING_CLASS_NAMES = [
    'ProjectUserIsOwnerEnum',
];
const APP_SAMPLE18_PROJECT_USER_BOTTOM_FUNCTION_NAMES = [
    'GetProjectUserSerurityCaption',
    'GetCategoryOfProjectUserSerurityCaption',
    'GetActionTypeOfProjectUserSerurityCaption',
    'GetAllSecurityTypeListOfProjectUser',
];

function app_sample18_projectuser_input_path(): string
{
    return app_runtime_storage_legacy_dbclasses_fixture_path('data-ProjectUser.php');
}

function app_sample18_projectuser_output_root(): string
{
    return dirname(__DIR__, 3) . '/work/sample-packs/pattern10-top-level-declaration-multiclass/output';
}

function app_sample18_projectuser_default_reference_root(): string
{
    return app_sample_pack_reference_root('pattern10-top-level-declaration-multiclass');
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
function app_sample18_projectuser_run(
    string $requestedBy,
    string $referenceRoot,
    bool $compareReference = true,
): array {
    $inputFile = app_sample18_projectuser_input_path();
    $outputRoot = app_sample18_projectuser_output_root();
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
            'error' => 'sample18 input file がありません。',
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
            'error' => 'sample18 bootstrap info の取得に失敗しました: ' . $bootstrapInfo['error'],
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
            'error' => 'sample18 migration info の取得に失敗しました: ' . $migrationInfo['error'],
        ];
    }

    $className = (string) ($migrationInfo['class_name'] ?? '');
    $dataClassName = app_legacy_data_class_source_name_from_class_name($className);
    $baseClassName = $className . 'Base';
    $wrapperRelativePath = 'data-' . $dataClassName . '.php';
    $baseRelativePath = 'base/data-' . $dataClassName . 'Base.php';

    app_reference_tree_assert_same(
        APP_SAMPLE18_PROJECT_USER_DATA_CLASS_NAME,
        $dataClassName,
        'sample18 data class name',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        3,
        (int) ($bootstrapInfo['class_count'] ?? 0),
        'sample18 class_count',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        (bool) ($bootstrapInfo['has_top_level_function'] ?? false),
        'sample18 has_top_level_function',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        $bootstrapInfo['extra_method_names'] ?? [],
        'sample18 bootstrap extra_method_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        $migrationInfo['wrapper_property_names'] ?? [],
        'sample18 wrapper_property_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        [],
        $migrationInfo['wrapper_method_names'] ?? [],
        'sample18 wrapper_method_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE18_PROJECT_USER_BOTTOM_CLASS_NAMES,
        $migrationInfo['generated_bottom_class_names'] ?? [],
        'sample18 generated_bottom_class_names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        APP_SAMPLE18_PROJECT_USER_TRAILING_CLASS_NAMES,
        $migrationInfo['generated_trailing_class_names'] ?? [],
        'sample18 generated_trailing_class_names',
        $assertionErrors,
    );

    $wrapperBottomFunctionNames = app_legacy_data_class_top_level_function_section_analysis(
        (string) ($migrationInfo['generated_wrapper_bottom_section'] ?? ''),
    )['function_names'] ?? [];
    app_reference_tree_assert_same(
        APP_SAMPLE18_PROJECT_USER_BOTTOM_FUNCTION_NAMES,
        $wrapperBottomFunctionNames,
        'sample18 wrapper bottom function names',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        str_contains(
            (string) ($migrationInfo['generated_base_additional_section'] ?? ''),
            'class ProjectUserSerurityEnum',
        ),
        'sample18 base additional section keeps ProjectUserSerurityEnum',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        str_contains(
            (string) ($migrationInfo['generated_base_additional_section'] ?? ''),
            'class ProjectUserIsOwnerEnum',
        ),
        'sample18 base additional section keeps ProjectUserIsOwnerEnum',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        app_legacy_data_class_supports_top_level_declaration_wrapper_base_migration(
            $bootstrapInfo,
            $migrationInfo,
        ),
        'sample18 migration support',
        $assertionErrors,
    );
    app_reference_tree_assert_same(
        true,
        (bool) ($legacyMigrationSupport['supports_legacy_top_level_declaration_migration'] ?? false),
        'sample18 runtime migration support',
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
                (string) ($migrationInfo['generated_base_additional_section'] ?? ''),
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
                (string) ($migrationInfo['generated_wrapper_bottom_section'] ?? ''),
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
            'error' => 'sample18 output の生成に失敗しました: ' . $throwable->getMessage(),
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
                'error' => 'sample18 reference root がありません。',
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
            'sample18 DATACLASS-PHP',
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
        'error' => $ok ? '' : 'sample18 top-level declaration verification failed',
    ];
}
