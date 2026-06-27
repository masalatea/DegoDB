<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/domain_validation.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE1_SIMPLE_TABLE_PROJECT_KEY = 'SAMPLE1';
const APP_SAMPLE1_SIMPLE_TABLE_NAME = 'article';
const APP_SAMPLE1_SIMPLE_TABLE_REFERENCE_SOURCE_OUTPUT_KEYS = [
    'DATACLASS-PHP',
    'DBACCESS-PHP',
];

function app_sample1_simple_table_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample01-simple-table-runtime');
}

/**
 * @param list<string> $errors
 */
function app_sample1_simple_table_assert_same(
    mixed $expected,
    mixed $actual,
    string $label,
    array &$errors,
): void {
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @return array{
 *     ok:bool,
 *     root:string,
 *     file_count:int,
 *     total_bytes:int,
 *     files:list<array{
 *         relative_path:string,
 *         sha256:string,
 *         size:int
 *     }>,
 *     error:string
 * }
 */
function app_sample1_simple_table_tree_snapshot(string $root): array
{
    $scanResult = app_project_output_scan_tree($root);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'root' => $root,
            'file_count' => 0,
            'total_bytes' => 0,
            'files' => [],
            'error' => $scanResult['error'],
        ];
    }

    $files = [];
    foreach ($scanResult['files'] as $file) {
        $relativePath = (string) ($file['relative_path'] ?? '');
        if ($relativePath === '') {
            continue;
        }

        $absolutePath = $root . '/' . $relativePath;
        $sha256 = hash_file('sha256', $absolutePath);
        if (!is_string($sha256) || $sha256 === '') {
            return [
                'ok' => false,
                'root' => $root,
                'file_count' => 0,
                'total_bytes' => 0,
                'files' => [],
                'error' => 'sha256 の計算に失敗しました: ' . $relativePath,
            ];
        }

        $files[] = [
            'relative_path' => $relativePath,
            'sha256' => strtolower($sha256),
            'size' => (int) ($file['size'] ?? 0),
        ];
    }

    usort(
        $files,
        static fn (array $left, array $right): int => strcmp($left['relative_path'], $right['relative_path']),
    );

    return [
        'ok' => true,
        'root' => $root,
        'file_count' => count($files),
        'total_bytes' => (int) ($scanResult['total_bytes'] ?? 0),
        'files' => $files,
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     relative_path:string,
 *     sha256:string,
 *     size:int
 * }> $expectedFiles
 * @param list<array{
 *     relative_path:string,
 *     sha256:string,
 *     size:int
 * }> $actualFiles
 * @param list<string> $errors
 * @return list<array{
 *     relative_path:string,
 *     expected_exists:bool,
 *     actual_exists:bool,
 *     expected_sha256:string,
 *     actual_sha256:string,
 *     ok:bool
 * }>
 */
function app_sample1_simple_table_compare_file_sets(
    array $expectedFiles,
    array $actualFiles,
    string $label,
    array &$errors,
): array {
    $expectedByPath = [];
    foreach ($expectedFiles as $file) {
        $expectedByPath[$file['relative_path']] = $file;
    }

    $actualByPath = [];
    foreach ($actualFiles as $file) {
        $actualByPath[$file['relative_path']] = $file;
    }

    $paths = array_values(array_unique(array_merge(array_keys($expectedByPath), array_keys($actualByPath))));
    sort($paths, SORT_STRING);

    $checks = [];
    foreach ($paths as $relativePath) {
        $expectedFile = $expectedByPath[$relativePath] ?? null;
        $actualFile = $actualByPath[$relativePath] ?? null;
        $expectedExists = is_array($expectedFile);
        $actualExists = is_array($actualFile);
        $expectedSha256 = $expectedExists ? (string) $expectedFile['sha256'] : '';
        $actualSha256 = $actualExists ? (string) $actualFile['sha256'] : '';
        $ok = $expectedExists && $actualExists && $expectedSha256 === $actualSha256;

        if (!$expectedExists) {
            $errors[] = $label . ' unexpected extra file: ' . $relativePath;
        } elseif (!$actualExists) {
            $errors[] = $label . ' missing file: ' . $relativePath;
        } elseif ($expectedSha256 !== $actualSha256) {
            $errors[] = $label . ' digest mismatch: ' . $relativePath
                . ' expected=' . $expectedSha256
                . ' actual=' . $actualSha256;
        }

        $checks[] = [
            'relative_path' => $relativePath,
            'expected_exists' => $expectedExists,
            'actual_exists' => $actualExists,
            'expected_sha256' => $expectedSha256,
            'actual_sha256' => $actualSha256,
            'ok' => $ok,
        ];
    }

    return $checks;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     site_role_summary:string,
 *     session:array{name:string},
 *     auth:array{
 *         mode:string,
 *         stub:array{
 *             username:string,
 *             password:string,
 *             display_name:string,
 *             roles:list<string>
 *         }
 *     },
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     config_db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     },
 *     generated:array{
 *         root:string,
 *         dbclasses_root:string,
 *         dbclasses_loader:string,
 *         dbclasses_mode:string
 *     },
 *     repositories:array{
 *         project_driver:string,
 *         experiment_driver:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     table_name:string,
 *     requested_by:string,
 *     reference_root:string,
 *     steps:array{
 *         table_import:array<string,mixed>|null,
 *         table_preview_after_import:array<string,mixed>|null,
 *         data_class_sync:array<string,mixed>|null,
 *         data_class_preview_after_sync:array<string,mixed>|null,
 *         outputs:list<array<string,mixed>>
 *     },
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample1_simple_table_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $projectKey = APP_SAMPLE1_SIMPLE_TABLE_PROJECT_KEY;
    $tableName = APP_SAMPLE1_SIMPLE_TABLE_NAME;
    $steps = [
        'table_import' => null,
        'table_preview_after_import' => null,
        'data_class_sync' => null,
        'data_class_preview_after_sync' => null,
        'outputs' => [],
    ];
    $assertionErrors = [];

    if ($referenceRoot === '' || !is_dir($referenceRoot)) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'reference root が見つかりません: ' . $referenceRoot,
        ];
    }

    $tableImport = app_project_table_import_apply($app, $projectKey, 'live-schema', $tableName);
    $steps['table_import'] = [
        'ok' => $tableImport['ok'],
        'summary' => $tableImport['summary'],
        'tables' => $tableImport['tables'],
        'errors' => $tableImport['errors'],
        'error' => $tableImport['error'],
    ];
    if (!$tableImport['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'table import に失敗しました。',
        ];
    }

    $tablePreview = app_project_table_import_preview($app, $projectKey, 'live-schema', $tableName);
    $steps['table_preview_after_import'] = [
        'ok' => $tablePreview['ok'],
        'summary' => $tablePreview['summary'],
        'tables' => $tablePreview['tables'],
        'errors' => $tablePreview['errors'],
        'error' => $tablePreview['error'],
    ];
    if (!$tablePreview['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'table preview の確認に失敗しました。',
        ];
    }

    app_sample1_simple_table_assert_same(
        1,
        $tablePreview['summary']['source_table_count'],
        'table source_table_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $tablePreview['summary']['table_insert_count'],
        'table table_insert_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $tablePreview['summary']['table_changed_count'],
        'table table_changed_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $tablePreview['summary']['table_delete_count'],
        'table table_delete_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $tablePreview['summary']['column_insert_count'],
        'table column_insert_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $tablePreview['summary']['column_update_count'],
        'table column_update_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $tablePreview['summary']['column_delete_count'],
        'table column_delete_count',
        $assertionErrors,
    );

    $dataClassSync = app_project_data_class_sync_apply($app, $projectKey);
    $steps['data_class_sync'] = [
        'ok' => $dataClassSync['ok'],
        'summary' => $dataClassSync['summary'],
        'classes' => $dataClassSync['classes'],
        'errors' => $dataClassSync['errors'],
        'error' => $dataClassSync['error'],
    ];
    if (!$dataClassSync['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'data class sync に失敗しました。',
        ];
    }

    $dataClassPreview = app_project_data_class_sync_preview($app, $projectKey);
    $steps['data_class_preview_after_sync'] = [
        'ok' => $dataClassPreview['ok'],
        'summary' => $dataClassPreview['summary'],
        'classes' => $dataClassPreview['classes'],
        'errors' => $dataClassPreview['errors'],
        'error' => $dataClassPreview['error'],
    ];
    if (!$dataClassPreview['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'data class preview の確認に失敗しました。',
        ];
    }

    app_sample1_simple_table_assert_same(
        1,
        $dataClassPreview['summary']['table_count'],
        'data_class table_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        1,
        $dataClassPreview['summary']['canonical_data_class_count'],
        'data_class canonical_data_class_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $dataClassPreview['summary']['class_insert_count'],
        'data_class class_insert_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $dataClassPreview['summary']['class_update_count'],
        'data_class class_update_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $dataClassPreview['summary']['field_insert_count'],
        'data_class field_insert_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $dataClassPreview['summary']['field_update_count'],
        'data_class field_update_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $dataClassPreview['summary']['stale_class_count'],
        'data_class stale_class_count',
        $assertionErrors,
    );
    app_sample1_simple_table_assert_same(
        0,
        $dataClassPreview['summary']['stale_field_count'],
        'data_class stale_field_count',
        $assertionErrors,
    );

    foreach (APP_SAMPLE1_SIMPLE_TABLE_REFERENCE_SOURCE_OUTPUT_KEYS as $sourceOutputKey) {
        $sourceOutputResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
        if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
            return [
                'ok' => false,
                'project_key' => $projectKey,
                'table_name' => $tableName,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => $assertionErrors,
                'error' => $sourceOutputResult['error'] !== ''
                    ? $sourceOutputResult['error']
                    : 'source output definition が見つかりません: ' . $sourceOutputKey,
            ];
        }

        $artifactResult = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $sourceOutputResult['item'],
            $requestedBy,
        );
        if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
            return [
                'ok' => false,
                'project_key' => $projectKey,
                'table_name' => $tableName,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => $assertionErrors,
                'error' => $artifactResult['error'] !== ''
                    ? $artifactResult['error']
                    : 'source output 生成に失敗しました: ' . $sourceOutputKey,
            ];
        }

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $sourceOutputResult['item'],
        );
        if (!$publishResult['ok'] || $publishResult['published'] === null) {
            return [
                'ok' => false,
                'project_key' => $projectKey,
                'table_name' => $tableName,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => $assertionErrors,
                'error' => $publishResult['error'] !== ''
                    ? $publishResult['error']
                    : 'source output publish に失敗しました: ' . $sourceOutputKey,
            ];
        }

        $publishedRoot = (string) $publishResult['published']['published_root'];
        $expectedRoot = $referenceRoot . '/' . $sourceOutputKey;
        $expectedSnapshot = app_sample1_simple_table_tree_snapshot($expectedRoot);
        $actualSnapshot = app_sample1_simple_table_tree_snapshot($publishedRoot);

        if (!$expectedSnapshot['ok'] || !$actualSnapshot['ok']) {
            return [
                'ok' => false,
                'project_key' => $projectKey,
                'table_name' => $tableName,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => $assertionErrors,
                'error' => !$expectedSnapshot['ok']
                    ? $expectedSnapshot['error']
                    : $actualSnapshot['error'],
            ];
        }

        $fileChecks = app_sample1_simple_table_compare_file_sets(
            $expectedSnapshot['files'],
            $actualSnapshot['files'],
            $sourceOutputKey,
            $assertionErrors,
        );

        $steps['outputs'][] = [
            'source_output_key' => $sourceOutputKey,
            'artifact_key' => $artifactResult['artifact']['artifact_key'],
            'customization_model' => $artifactResult['artifact']['customization_model'],
            'source_file_count' => $artifactResult['artifact']['source_file_count'],
            'source_total_bytes' => $artifactResult['artifact']['source_total_bytes'],
            'published_root' => $publishedRoot,
            'reference_root' => $expectedRoot,
            'reference_file_count' => $expectedSnapshot['file_count'],
            'published_file_count' => $actualSnapshot['file_count'],
            'file_checks' => $fileChecks,
        ];
    }

    $ok = $assertionErrors === [];

    return [
        'ok' => $ok,
        'project_key' => $projectKey,
        'table_name' => $tableName,
        'requested_by' => $requestedBy,
        'reference_root' => $referenceRoot,
        'steps' => $steps,
        'assertion_errors' => $assertionErrors,
        'error' => $ok ? '' : 'sample1 simple-table output check failed.',
    ];
}
