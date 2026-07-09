<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY = 'SAMPLE18';
const APP_SAMPLE18_MINI_TASK_BOARD_DEMO_TABLE_NAME = 'task_card';
const APP_SAMPLE18_MINI_TASK_BOARD_DEMO_KEYS = [
    'DATACLASS-PHP',
    'DBACCESS-PHP',
    'HTML-PAGE',
    'OPENAPI-JSON',
];
const APP_SAMPLE18_MINI_TASK_BOARD_DEMO_OPENAPI_PATHS = [
    '/proxyserver-TaskCard-GetTaskCardList.php',
    '/proxyserver-TaskCard-GetTaskCard.php',
    '/proxyserver-TaskCard-InsertTaskCard.php',
    '/proxyserver-TaskCard-UpdateTaskCard.php',
    '/proxyserver-TaskCard-CompleteTaskCard.php',
];
const APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY = 'NO-CODE-RUNTIME';

function app_sample18_mini_task_board_demo_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample18-mini-task-board-demo');
}

function app_sample18_mini_task_board_demo_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample18_mini_task_board_demo_tree_snapshot(string $root): array
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

function app_sample18_mini_task_board_demo_compare_file_sets(
    string $sourceOutputKey,
    array $expectedFiles,
    array $actualFiles,
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
            $errors[] = $sourceOutputKey . ' unexpected extra file: ' . $relativePath;
        } elseif (!$actualExists) {
            $errors[] = $sourceOutputKey . ' missing file: ' . $relativePath;
        } elseif ($expectedSha256 !== $actualSha256) {
            $errors[] = $sourceOutputKey . ' digest mismatch: ' . $relativePath
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

function app_sample18_mini_task_board_demo_read_json_file(string $path): array
{
    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'payload' => null,
            'error' => 'JSON file を読めません: ' . $path,
        ];
    }

    $payload = json_decode($contents, true);
    if (!is_array($payload)) {
        return [
            'ok' => false,
            'payload' => null,
            'error' => 'JSON file の解析に失敗しました: ' . json_last_error_msg(),
        ];
    }

    return [
        'ok' => true,
        'payload' => $payload,
        'error' => '',
    ];
}

function app_sample18_mini_task_board_demo_golden_fixture(): array
{
    $path = dirname(__DIR__, 3) . '/sample/tutorials/sample18-mini-task-board-demo/golden/no-code-ui-golden.json';
    $result = app_sample18_mini_task_board_demo_read_json_file($path);

    return $result['ok'] ? $result['payload'] : [];
}

function app_sample18_mini_task_board_demo_publish_one(
    array $app,
    string $requestedBy,
    string $referenceRoot,
    string $sourceOutputKey,
    array &$errors,
): array {
    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY,
        $sourceOutputKey,
    );
    if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
        return [
            'ok' => false,
            'source_output_key' => $sourceOutputKey,
            'error' => $sourceOutputResult['error'] !== ''
                ? $sourceOutputResult['error']
                : 'source output definition が見つかりません: ' . $sourceOutputKey,
        ];
    }

    $artifactResult = app_project_output_create_from_definition(
        $app,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY,
        $sourceOutputResult['item'],
        $requestedBy,
    );
    if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
        return [
            'ok' => false,
            'source_output_key' => $sourceOutputKey,
            'error' => $artifactResult['error'],
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
            'source_output_key' => $sourceOutputKey,
            'error' => $publishResult['error'],
        ];
    }

    $publishedRoot = (string) $publishResult['published']['published_root'];
    $expectedRoot = $referenceRoot . '/' . $sourceOutputKey;
    $expectedSnapshot = app_sample18_mini_task_board_demo_tree_snapshot($expectedRoot);
    $actualSnapshot = app_sample18_mini_task_board_demo_tree_snapshot($publishedRoot);
    if (!$expectedSnapshot['ok'] || !$actualSnapshot['ok']) {
        return [
            'ok' => false,
            'source_output_key' => $sourceOutputKey,
            'error' => !$expectedSnapshot['ok'] ? $expectedSnapshot['error'] : $actualSnapshot['error'],
        ];
    }

    $fileChecks = app_sample18_mini_task_board_demo_compare_file_sets(
        $sourceOutputKey,
        $expectedSnapshot['files'],
        $actualSnapshot['files'],
        $errors,
    );

    $openApiSummary = [];
    if ($sourceOutputKey === 'OPENAPI-JSON') {
        $openApiJsonResult = app_sample18_mini_task_board_demo_read_json_file($publishedRoot . '/openapi.json');
        if (!$openApiJsonResult['ok']) {
            return [
                'ok' => false,
                'source_output_key' => $sourceOutputKey,
                'error' => $openApiJsonResult['error'],
            ];
        }

        $spec = $openApiJsonResult['payload'];
        app_sample18_mini_task_board_demo_assert_same('3.0.3', (string) ($spec['openapi'] ?? ''), 'openapi version', $errors);
        app_sample18_mini_task_board_demo_assert_same('Sample18 OpenAPI JSON', (string) ($spec['info']['title'] ?? ''), 'openapi info title', $errors);
        foreach (APP_SAMPLE18_MINI_TASK_BOARD_DEMO_OPENAPI_PATHS as $path) {
            app_sample18_mini_task_board_demo_assert_same(true, is_array($spec['paths'][$path] ?? null), 'openapi path exists ' . $path, $errors);
        }
        app_sample18_mini_task_board_demo_assert_same(
            true,
            is_array($spec['components']['schemas']['TaskCardData'] ?? null),
            'TaskCardData schema exists',
            $errors,
        );
        $openApiSummary = [
            'paths' => array_keys($spec['paths'] ?? []),
            'schema_keys' => array_keys($spec['components']['schemas'] ?? []),
        ];
    }

    return [
        'ok' => true,
        'source_output_key' => $sourceOutputKey,
        'artifact_key' => $artifactResult['artifact']['artifact_key'],
        'published_root' => $publishedRoot,
        'reference_root' => $expectedRoot,
        'expected_snapshot' => $expectedSnapshot,
        'actual_snapshot' => $actualSnapshot,
        'file_checks' => $fileChecks,
        'openapi' => $openApiSummary,
        'error' => '',
    ];
}

function app_sample18_mini_task_board_demo_publish_no_code_metadata(
    array $app,
    string $requestedBy,
    array &$errors,
): array {
    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY,
    );
    if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
        return [
            'ok' => false,
            'source_output_key' => APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY,
            'error' => $sourceOutputResult['error'] !== ''
                ? $sourceOutputResult['error']
                : 'no-code source output definition が見つかりません。',
        ];
    }

    $artifactResult = app_project_output_create_from_definition(
        $app,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY,
        $sourceOutputResult['item'],
        $requestedBy,
    );
    if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
        return [
            'ok' => false,
            'source_output_key' => APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY,
            'error' => $artifactResult['error'],
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
            'source_output_key' => APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY,
            'error' => $publishResult['error'],
        ];
    }

    $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
    $screenDefinitionJson = app_sample18_mini_task_board_demo_read_json_file($publishedRoot . '/screen-definition.json');
    $runtimePreviewJson = app_sample18_mini_task_board_demo_read_json_file($publishedRoot . '/runtime-preview.json');
    if (!$screenDefinitionJson['ok'] || !$runtimePreviewJson['ok']) {
        return [
            'ok' => false,
            'source_output_key' => APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY,
            'error' => !$screenDefinitionJson['ok'] ? $screenDefinitionJson['error'] : $runtimePreviewJson['error'],
        ];
    }

    $screenDefinition = $screenDefinitionJson['payload'];
    $runtimePreview = $runtimePreviewJson['payload'];
    $runtimePreviewHtml = is_file($publishedRoot . '/runtime-preview.html')
        ? (string) file_get_contents($publishedRoot . '/runtime-preview.html')
        : '';
    $goldenFixture = app_sample18_mini_task_board_demo_golden_fixture();
    $goldenRows = is_array($goldenFixture['seed_rows'] ?? null) ? $goldenFixture['seed_rows'] : [];
    $goldenDomContract = is_array($goldenFixture['dom_contract'] ?? null) ? $goldenFixture['dom_contract'] : [];
    $contracts = is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : [];
    $contract = is_array($contracts[0] ?? null) ? $contracts[0] : [];
    $screens = is_array($contract['screens'] ?? null) ? $contract['screens'] : [];
    $listScreen = is_array($screens[0] ?? null) ? $screens[0] : [];
    $fields = is_array($listScreen['fields'] ?? null) ? $listScreen['fields'] : [];
    $runtimeScreens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];
    $runtimeListScreen = [];
    foreach ($runtimeScreens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_key'] ?? '') === 'task_card_list') {
            $runtimeListScreen = $screen;
            break;
        }
    }
    $runtimeRows = is_array($runtimeListScreen['data']['rows'] ?? null) ? $runtimeListScreen['data']['rows'] : [];

    app_sample18_mini_task_board_demo_assert_same(
        'no-code-screen-definition-v0',
        (string) ($screenDefinition['definition_version'] ?? ''),
        'sample18 no-code definition_version',
        $errors,
    );
    app_sample18_mini_task_board_demo_assert_same(
        'task_card',
        (string) ($contract['contract_key'] ?? ''),
        'sample18 no-code contract_key',
        $errors,
    );
    app_sample18_mini_task_board_demo_assert_same(
        ['list', 'detail', 'form'],
        array_values(array_map(static fn (array $screen): string => (string) ($screen['screen_type'] ?? ''), $screens)),
        'sample18 no-code screen types',
        $errors,
    );
    app_sample18_mini_task_board_demo_assert_same(
        ['id', 'title', 'body', 'status', 'assigned_to', 'priority', 'due_date', 'completed_at', 'updated_at'],
        array_values(array_map(static fn (array $field): string => (string) ($field['field_key'] ?? ''), $fields)),
        'sample18 no-code field keys',
        $errors,
    );
    app_sample18_mini_task_board_demo_assert_same(
        'no-code-runtime-v0',
        (string) ($runtimePreview['runtime_version'] ?? ''),
        'sample18 no-code runtime_version',
        $errors,
    );
    app_sample18_mini_task_board_demo_assert_same(
        3,
        count($runtimeScreens),
        'sample18 no-code runtime screen count',
        $errors,
    );
    app_sample18_mini_task_board_demo_assert_same(
        count($goldenRows),
        count($runtimeRows),
        'sample18 no-code runtime row count',
        $errors,
    );
    foreach ($goldenRows as $index => $goldenRow) {
        $runtimeRow = is_array($runtimeRows[$index] ?? null) ? $runtimeRows[$index] : [];
        foreach (['title', 'status', 'assigned_to', 'due_date'] as $fieldKey) {
            app_sample18_mini_task_board_demo_assert_same(
                (string) ($goldenRow[$fieldKey] ?? ''),
                (string) ($runtimeRow[$fieldKey]['display_value'] ?? ''),
                'sample18 no-code runtime row ' . $index . ' ' . $fieldKey,
                $errors,
            );
        }
        app_sample18_mini_task_board_demo_assert_same(
            true,
            str_contains($runtimePreviewHtml, (string) ($goldenRow['title'] ?? '')),
            'sample18 no-code runtime html title ' . $index,
            $errors,
        );
    }
    foreach (($goldenDomContract['form_fields'] ?? []) as $fieldKey) {
        app_sample18_mini_task_board_demo_assert_same(
            true,
            str_contains($runtimePreviewHtml, 'name="' . (string) $fieldKey . '"'),
            'sample18 no-code runtime html field ' . (string) $fieldKey,
            $errors,
        );
    }

    return [
        'ok' => true,
        'source_output_key' => APP_SAMPLE18_MINI_TASK_BOARD_DEMO_NO_CODE_SOURCE_OUTPUT_KEY,
        'artifact_key' => $artifactResult['artifact']['artifact_key'],
        'published_root' => $publishedRoot,
        'definition_version' => (string) ($screenDefinition['definition_version'] ?? ''),
        'runtime_version' => (string) ($runtimePreview['runtime_version'] ?? ''),
        'contract_key' => (string) ($contract['contract_key'] ?? ''),
        'screen_types' => array_values(array_map(static fn (array $screen): string => (string) ($screen['screen_type'] ?? ''), $screens)),
        'field_keys' => array_values(array_map(static fn (array $field): string => (string) ($field['field_key'] ?? ''), $fields)),
        'runtime_screen_count' => count($runtimeScreens),
        'runtime_row_count' => count($runtimeRows),
        'golden_row_count' => count($goldenRows),
        'error' => '',
    ];
}

function app_sample18_mini_task_board_demo_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'table_import' => null,
        'table_preview_after_import' => null,
        'data_class_sync' => null,
        'data_class_preview_after_sync' => null,
        'outputs' => [],
        'no_code_metadata' => null,
    ];
    $errors = [];

    if ($referenceRoot === '' || !is_dir($referenceRoot)) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'reference root が見つかりません: ' . $referenceRoot,
        ];
    }

    $tableImport = app_project_table_import_apply(
        $app,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY,
        'live-schema',
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_TABLE_NAME,
    );
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
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'table import に失敗しました: ' . $tableImport['error'],
        ];
    }

    $tablePreview = app_project_table_import_preview(
        $app,
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY,
        'live-schema',
        APP_SAMPLE18_MINI_TASK_BOARD_DEMO_TABLE_NAME,
    );
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
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'table preview に失敗しました: ' . $tablePreview['error'],
        ];
    }
    app_sample18_mini_task_board_demo_assert_same(1, $tablePreview['summary']['source_table_count'], 'table source_table_count', $errors);

    $dataClassSync = app_project_data_class_sync_apply($app, APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY);
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
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'data class sync に失敗しました: ' . $dataClassSync['error'],
        ];
    }

    $dataClassPreview = app_project_data_class_sync_preview($app, APP_SAMPLE18_MINI_TASK_BOARD_DEMO_PROJECT_KEY);
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
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'data class preview に失敗しました: ' . $dataClassPreview['error'],
        ];
    }
    app_sample18_mini_task_board_demo_assert_same(1, $dataClassPreview['summary']['canonical_data_class_count'], 'data_class canonical_data_class_count', $errors);

    foreach (APP_SAMPLE18_MINI_TASK_BOARD_DEMO_KEYS as $sourceOutputKey) {
        $outputResult = app_sample18_mini_task_board_demo_publish_one(
            $app,
            $requestedBy,
            $referenceRoot,
            $sourceOutputKey,
            $errors,
        );
        $steps['outputs'][$sourceOutputKey] = $outputResult;
        if (!$outputResult['ok']) {
            return [
                'ok' => false,
                'steps' => $steps,
                'assertion_errors' => $errors,
                'error' => $outputResult['error'],
            ];
        }
    }

    $noCodeResult = app_sample18_mini_task_board_demo_publish_no_code_metadata(
        $app,
        $requestedBy,
        $errors,
    );
    $steps['no_code_metadata'] = $noCodeResult;
    if (!$noCodeResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $noCodeResult['error'],
        ];
    }

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : 'sample18 mini task board verification failed.',
    ];
}
