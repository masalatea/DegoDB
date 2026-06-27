<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/db_access_repository.php';
require_once dirname(__DIR__, 2) . '/app/domain_validation.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_PROJECT_KEY = 'SAMPLE09';
const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_TABLE_NAMES = [
    'sales_category',
    'sales_category_report',
    'sales_record',
];
const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_SOURCE_NAME = 'sales_record';
const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_FUNCTION_NAME = 'GetClosedSalesCategoryReportList';
const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_DATA_CLASS_BASE_NAME = 'SalesCategoryReport';
const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_REFERENCE_SOURCE_OUTPUT_KEYS = [
    'DATACLASS-PHP',
    'DBACCESS-PHP',
];
const APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_SELECT_TARGET_LABELS = [
    'sales_record.sales_category_id->salesCategoryId',
    'sales_category.name->salesCategoryName',
    'sales_record.id->closedSaleCount',
    'sales_record.amount->closedSaleTotalAmount',
];

function app_sample9_dbaccess_aggregate_report_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample09-dbaccess-aggregate-report');
}

function app_sample9_dbaccess_aggregate_report_assert_same(
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

function app_sample9_dbaccess_aggregate_report_tree_snapshot(string $root): array
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

function app_sample9_dbaccess_aggregate_report_compare_file_sets(
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

function app_sample9_dbaccess_aggregate_report_extract_names(array $items, string $key): array
{
    $names = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $name = (string) ($item[$key] ?? '');
        if ($name === '') {
            continue;
        }

        $names[] = $name;
    }

    sort($names, SORT_STRING);

    return $names;
}

function app_sample9_dbaccess_aggregate_report_select_target_labels(array $items): array
{
    $labels = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $labels[] = (string) ($item['target_table_name'] ?? '')
            . '.'
            . (string) ($item['target_table_column_name'] ?? '')
            . '->'
            . (string) ($item['store_class_field_name'] ?? '');
    }

    return $labels;
}

function app_sample9_dbaccess_aggregate_report_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $projectKey = APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_PROJECT_KEY;
    $tableNames = APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_TABLE_NAMES;
    $sourceName = APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_SOURCE_NAME;
    $functionName = APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_FUNCTION_NAME;
    $steps = [
        'table_imports' => [],
        'table_previews_after_import' => [],
        'data_class_sync' => null,
        'data_class_preview_after_sync' => null,
        'db_access_class_catalog' => null,
        'db_access_function_catalog' => null,
        'db_access_function' => null,
        'db_access_select_target_fields' => null,
        'db_access_select_wheres' => null,
        'db_access_select_havings' => null,
        'outputs' => [],
    ];
    $assertionErrors = [];

    if ($referenceRoot === '' || !is_dir($referenceRoot)) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'reference root が見つかりません: ' . $referenceRoot,
        ];
    }

    foreach ($tableNames as $tableName) {
        $tableImport = app_project_table_import_apply($app, $projectKey, 'live-schema', $tableName);
        $steps['table_imports'][] = [
            'table_name' => $tableName,
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
                'table_names' => $tableNames,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => [],
                'error' => 'table import に失敗しました: ' . $tableName,
            ];
        }

        $tablePreview = app_project_table_import_preview($app, $projectKey, 'live-schema', $tableName);
        $steps['table_previews_after_import'][] = [
            'table_name' => $tableName,
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
                'table_names' => $tableNames,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => [],
                'error' => 'table preview の確認に失敗しました: ' . $tableName,
            ];
        }

        app_sample9_dbaccess_aggregate_report_assert_same(
            1,
            $tablePreview['summary']['source_table_count'],
            'table ' . $tableName . ' source_table_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            0,
            $tablePreview['summary']['table_insert_count'],
            'table ' . $tableName . ' table_insert_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            0,
            $tablePreview['summary']['table_changed_count'],
            'table ' . $tableName . ' table_changed_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            0,
            $tablePreview['summary']['table_delete_count'],
            'table ' . $tableName . ' table_delete_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            0,
            $tablePreview['summary']['column_insert_count'],
            'table ' . $tableName . ' column_insert_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            0,
            $tablePreview['summary']['column_update_count'],
            'table ' . $tableName . ' column_update_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            0,
            $tablePreview['summary']['column_delete_count'],
            'table ' . $tableName . ' column_delete_count',
            $assertionErrors,
        );
        app_sample9_dbaccess_aggregate_report_assert_same(
            $tableName,
            $tablePreview['tables'][0]['name'] ?? '',
            'table preview table name ' . $tableName,
            $assertionErrors,
        );
    }

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
            'table_names' => $tableNames,
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
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'data class preview の確認に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same(3, $dataClassPreview['summary']['table_count'], 'data_class table_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(3, $dataClassPreview['summary']['canonical_data_class_count'], 'data_class canonical_data_class_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(0, $dataClassPreview['summary']['class_insert_count'], 'data_class class_insert_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(0, $dataClassPreview['summary']['class_update_count'], 'data_class class_update_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(0, $dataClassPreview['summary']['field_insert_count'], 'data_class field_insert_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(0, $dataClassPreview['summary']['field_update_count'], 'data_class field_update_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(0, $dataClassPreview['summary']['stale_class_count'], 'data_class stale_class_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(0, $dataClassPreview['summary']['stale_field_count'], 'data_class stale_field_count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(
        $tableNames,
        app_sample9_dbaccess_aggregate_report_extract_names($dataClassPreview['classes'], 'name'),
        'data_class class names',
        $assertionErrors,
    );

    $classCatalogResult = app_fetch_db_access_class_metadata_catalog($app, $projectKey);
    $steps['db_access_class_catalog'] = [
        'ok' => $classCatalogResult['ok'],
        'items' => $classCatalogResult['items'],
        'error' => $classCatalogResult['error'],
    ];
    if (!$classCatalogResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db access class catalog の取得に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same(1, count($classCatalogResult['items']), 'db_access class count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(
        [$sourceName],
        app_sample9_dbaccess_aggregate_report_extract_names($classCatalogResult['items'], 'source_name'),
        'db_access class names',
        $assertionErrors,
    );
    app_sample9_dbaccess_aggregate_report_assert_same('manual', $classCatalogResult['items'][0]['source_of_truth'] ?? '', 'db_access class source_of_truth', $assertionErrors);

    $functionCatalogResult = app_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
    $steps['db_access_function_catalog'] = [
        'ok' => $functionCatalogResult['ok'],
        'items' => $functionCatalogResult['items'],
        'error' => $functionCatalogResult['error'],
    ];
    if (!$functionCatalogResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db access function catalog の取得に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same(1, count($functionCatalogResult['items']), 'db_access function count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(
        [$functionName],
        app_sample9_dbaccess_aggregate_report_extract_names($functionCatalogResult['items'], 'function_name'),
        'db_access function names',
        $assertionErrors,
    );

    $functionResult = app_fetch_db_access_function_metadata($app, $projectKey, $sourceName, $functionName);
    $steps['db_access_function'] = [
        'ok' => $functionResult['ok'],
        'item' => $functionResult['item'],
        'error' => $functionResult['error'],
    ];
    if (!$functionResult['ok'] || !is_array($functionResult['item'])) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db access function metadata の取得に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same('SELECTLIST', $functionResult['item']['action_type'] ?? '', 'db_access function action_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(
        APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_DATA_CLASS_BASE_NAME,
        $functionResult['item']['data_class_base_name'] ?? '',
        'db_access function data_class_base_name',
        $assertionErrors,
    );
    app_sample9_dbaccess_aggregate_report_assert_same('sales_record', $functionResult['item']['target_table_name'] ?? '', 'db_access function target_table_name', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(
        'sum(sales_record.amount) desc, sales_record.sales_category_id asc',
        $functionResult['item']['sort_order_columns'] ?? '',
        'db_access function sort_order_columns',
        $assertionErrors,
    );
    app_sample9_dbaccess_aggregate_report_assert_same(
        '',
        $functionResult['item']['detected_signature'] ?? '',
        'db_access function detected_signature',
        $assertionErrors,
    );
    app_sample9_dbaccess_aggregate_report_assert_same('manual', $functionResult['item']['source_of_truth'] ?? '', 'db_access function source_of_truth', $assertionErrors);

    $selectTargetFieldResult = app_fetch_db_access_function_select_target_field_catalog($app, $projectKey, $sourceName, $functionName);
    $steps['db_access_select_target_fields'] = [
        'ok' => $selectTargetFieldResult['ok'],
        'items' => $selectTargetFieldResult['items'],
        'error' => $selectTargetFieldResult['error'],
    ];
    if (!$selectTargetFieldResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db access select target fields の取得に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same(
        APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_SELECT_TARGET_LABELS,
        app_sample9_dbaccess_aggregate_report_select_target_labels($selectTargetFieldResult['items']),
        'db_access select target labels',
        $assertionErrors,
    );
    app_sample9_dbaccess_aggregate_report_assert_same('1', $selectTargetFieldResult['items'][0]['group_by_target'] ?? '', 'first group_by_target', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('1', $selectTargetFieldResult['items'][1]['group_by_target'] ?? '', 'second group_by_target', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('count(', $selectTargetFieldResult['items'][2]['target_table_column_prefix'] ?? '', 'count prefix', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(')', $selectTargetFieldResult['items'][2]['target_table_column_suffix'] ?? '', 'count suffix', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('sum(', $selectTargetFieldResult['items'][3]['target_table_column_prefix'] ?? '', 'sum prefix', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same(')', $selectTargetFieldResult['items'][3]['target_table_column_suffix'] ?? '', 'sum suffix', $assertionErrors);

    $selectWhereResult = app_fetch_db_access_function_select_where_catalog($app, $projectKey, $sourceName, $functionName);
    $steps['db_access_select_wheres'] = [
        'ok' => $selectWhereResult['ok'],
        'items' => $selectWhereResult['items'],
        'error' => $selectWhereResult['error'],
    ];
    if (!$selectWhereResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db access select wheres の取得に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same(3, count($selectWhereResult['items']), 'db_access select where count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('sales_record', $selectWhereResult['items'][0]['target_table_name'] ?? '', 'db_access join where target table', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('sales_category_id', $selectWhereResult['items'][0]['target_table_column_name'] ?? '', 'db_access join where target column', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('anotherfield', $selectWhereResult['items'][0]['parameter_type'] ?? '', 'db_access join where parameter_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('sales_category', $selectWhereResult['items'][0]['another_table_name'] ?? '', 'db_access join where another_table_name', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('id', $selectWhereResult['items'][0]['another_field_name'] ?? '', 'db_access join where another_field_name', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('inner', $selectWhereResult['items'][0]['join_type'] ?? '', 'db_access join where join_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('10', $selectWhereResult['items'][0]['where_order'] ?? '', 'db_access join where order', $assertionErrors);

    app_sample9_dbaccess_aggregate_report_assert_same('sales_record', $selectWhereResult['items'][1]['target_table_name'] ?? '', 'db_access fixed record where target table', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('status', $selectWhereResult['items'][1]['target_table_column_name'] ?? '', 'db_access fixed record where target column', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('fixed', $selectWhereResult['items'][1]['parameter_type'] ?? '', 'db_access fixed record where parameter_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('closed', $selectWhereResult['items'][1]['fixed_parameter'] ?? '', 'db_access fixed record where fixed_parameter', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('20', $selectWhereResult['items'][1]['where_order'] ?? '', 'db_access fixed record where order', $assertionErrors);

    app_sample9_dbaccess_aggregate_report_assert_same('sales_category', $selectWhereResult['items'][2]['target_table_name'] ?? '', 'db_access fixed category where target table', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('is_active', $selectWhereResult['items'][2]['target_table_column_name'] ?? '', 'db_access fixed category where target column', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('fixed', $selectWhereResult['items'][2]['parameter_type'] ?? '', 'db_access fixed category where parameter_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('raw', $selectWhereResult['items'][2]['parameter_data_type'] ?? '', 'db_access fixed category where parameter_data_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('1', $selectWhereResult['items'][2]['fixed_parameter'] ?? '', 'db_access fixed category where fixed_parameter', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('30', $selectWhereResult['items'][2]['where_order'] ?? '', 'db_access fixed category where order', $assertionErrors);

    $selectHavingResult = app_fetch_db_access_function_select_having_catalog($app, $projectKey, $sourceName, $functionName);
    $steps['db_access_select_havings'] = [
        'ok' => $selectHavingResult['ok'],
        'items' => $selectHavingResult['items'],
        'error' => $selectHavingResult['error'],
    ];
    if (!$selectHavingResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_names' => $tableNames,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db access select havings の取得に失敗しました。',
        ];
    }

    app_sample9_dbaccess_aggregate_report_assert_same(2, count($selectHavingResult['items']), 'db_access select having count', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('>=', $selectHavingResult['items'][0]['relational_operator'] ?? '', 'first having operator', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('fixed', $selectHavingResult['items'][0]['right_parameter_type'] ?? '', 'first having parameter_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('raw', $selectHavingResult['items'][0]['right_parameter_data_type'] ?? '', 'first having parameter_data_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('2', $selectHavingResult['items'][0]['right_fixed_parameter'] ?? '', 'first having fixed_parameter', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('10', $selectHavingResult['items'][0]['having_order'] ?? '', 'first having order', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('>=', $selectHavingResult['items'][1]['relational_operator'] ?? '', 'second having operator', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('fixed', $selectHavingResult['items'][1]['right_parameter_type'] ?? '', 'second having parameter_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('raw', $selectHavingResult['items'][1]['right_parameter_data_type'] ?? '', 'second having parameter_data_type', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('100', $selectHavingResult['items'][1]['right_fixed_parameter'] ?? '', 'second having fixed_parameter', $assertionErrors);
    app_sample9_dbaccess_aggregate_report_assert_same('20', $selectHavingResult['items'][1]['having_order'] ?? '', 'second having order', $assertionErrors);

    foreach (APP_SAMPLE9_DBACCESS_AGGREGATE_REPORT_REFERENCE_SOURCE_OUTPUT_KEYS as $sourceOutputKey) {
        $sourceOutputResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
        if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
            return [
                'ok' => false,
                'project_key' => $projectKey,
                'table_names' => $tableNames,
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
                'table_names' => $tableNames,
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
                'table_names' => $tableNames,
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
        $expectedSnapshot = app_sample9_dbaccess_aggregate_report_tree_snapshot($expectedRoot);
        $actualSnapshot = app_sample9_dbaccess_aggregate_report_tree_snapshot($publishedRoot);

        if (!$expectedSnapshot['ok'] || !$actualSnapshot['ok']) {
            return [
                'ok' => false,
                'project_key' => $projectKey,
                'table_names' => $tableNames,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'steps' => $steps,
                'assertion_errors' => $assertionErrors,
                'error' => !$expectedSnapshot['ok']
                    ? 'reference snapshot の取得に失敗しました: ' . $expectedSnapshot['error']
                    : 'published snapshot の取得に失敗しました: ' . $actualSnapshot['error'],
            ];
        }

        $fileChecks = app_sample9_dbaccess_aggregate_report_compare_file_sets(
            $expectedSnapshot['files'],
            $actualSnapshot['files'],
            $sourceOutputKey,
            $assertionErrors,
        );

        $steps['outputs'][] = [
            'source_output_key' => $sourceOutputKey,
            'published_root' => $publishedRoot,
            'reference_root' => $expectedRoot,
            'file_checks' => $fileChecks,
            'artifact_key' => $publishResult['published']['artifact_key'] ?? '',
        ];
    }

    return [
        'ok' => $assertionErrors === [],
        'project_key' => $projectKey,
        'table_names' => $tableNames,
        'requested_by' => $requestedBy,
        'reference_root' => $referenceRoot,
        'steps' => $steps,
        'assertion_errors' => $assertionErrors,
        'error' => $assertionErrors === [] ? '' : 'sample09 dbaccess aggregate report assertions failed',
    ];
}
