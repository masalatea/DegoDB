<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE32_NO_CODE_UI_TEST_LAB_PROJECT_KEY = 'SAMPLE32';
const APP_SAMPLE32_NO_CODE_UI_TEST_LAB_TABLE_NAME = 'no_code_lab_card';
const APP_SAMPLE32_NO_CODE_UI_TEST_LAB_SOURCE_OUTPUT_KEY = 'NO-CODE-RUNTIME';

function app_sample32_no_code_ui_test_lab_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @return array{ok:bool,data:array<string,mixed>,error:string}
 */
function app_sample32_no_code_ui_test_lab_read_json_file(string $path): array
{
    if (!is_file($path)) {
        return ['ok' => false, 'data' => [], 'error' => 'file was not found: ' . $path];
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'data' => [], 'error' => 'failed to decode JSON file: ' . $path];
    }

    return ['ok' => true, 'data' => $decoded, 'error' => ''];
}

/**
 * @param list<array<string,mixed>> $items
 * @return list<string>
 */
function app_sample32_no_code_ui_test_lab_extract_names(array $items, string $key): array
{
    return array_values(array_map(
        static fn (array $item): string => (string) ($item[$key] ?? ''),
        $items,
    ));
}

/**
 * @param list<array<string,mixed>> $items
 * @return array<string,mixed>
 */
function app_sample32_no_code_ui_test_lab_find_by_value(array $items, string $key, string $value): array
{
    foreach ($items as $item) {
        if ((string) ($item[$key] ?? '') === $value) {
            return $item;
        }
    }

    return [];
}

/**
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     table_name:string,
 *     requested_by:string,
 *     steps:array<string,mixed>,
 *     assertion_errors:list<string>,
 *     error:string
 * }
 */
function app_sample32_no_code_ui_test_lab_run(array $app, string $requestedBy): array
{
    $projectKey = APP_SAMPLE32_NO_CODE_UI_TEST_LAB_PROJECT_KEY;
    $tableName = APP_SAMPLE32_NO_CODE_UI_TEST_LAB_TABLE_NAME;
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'source_output' => null,
        'artifact' => null,
        'published' => null,
        'screen_definition' => null,
        'runtime_preview' => null,
    ];
    $assertionErrors = [];

    try {
        $tableImport = app_project_table_import_apply($app, $projectKey, 'live-schema', $tableName);
        $steps['table_import'] = [
            'ok' => $tableImport['ok'],
            'summary' => $tableImport['summary'],
            'errors' => $tableImport['errors'],
            'error' => $tableImport['error'],
        ];
        if (!$tableImport['ok']) {
            throw new RuntimeException('sample32 table import failed.');
        }

        $dataClassSync = app_project_data_class_sync_apply($app, $projectKey);
        $steps['data_class_sync'] = [
            'ok' => $dataClassSync['ok'],
            'summary' => $dataClassSync['summary'],
            'errors' => $dataClassSync['errors'],
            'error' => $dataClassSync['error'],
        ];
        if (!$dataClassSync['ok']) {
            throw new RuntimeException('sample32 data class sync failed.');
        }

        $sourceOutputResult = app_fetch_project_source_output_item(
            $app,
            $projectKey,
            APP_SAMPLE32_NO_CODE_UI_TEST_LAB_SOURCE_OUTPUT_KEY,
        );
        if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
            throw new RuntimeException(
                $sourceOutputResult['error'] !== ''
                    ? $sourceOutputResult['error']
                    : 'sample32 no-code source output definition was not found.',
            );
        }
        $steps['source_output'] = $sourceOutputResult['item'];

        $artifactResult = app_project_output_create_from_definition(
            $app,
            $projectKey,
            $sourceOutputResult['item'],
            $requestedBy,
        );
        if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
            throw new RuntimeException('sample32 no-code artifact generation failed: ' . $artifactResult['error']);
        }
        $steps['artifact'] = $artifactResult['artifact'];

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $sourceOutputResult['item'],
        );
        if (!$publishResult['ok'] || $publishResult['published'] === null) {
            throw new RuntimeException('sample32 no-code artifact publish failed: ' . $publishResult['error']);
        }
        $steps['published'] = $publishResult['published'];

        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        $screenDefinitionJson = app_sample32_no_code_ui_test_lab_read_json_file($publishedRoot . '/screen-definition.json');
        $runtimePreviewJson = app_sample32_no_code_ui_test_lab_read_json_file($publishedRoot . '/runtime-preview.json');
        if (!$screenDefinitionJson['ok'] || !$runtimePreviewJson['ok']) {
            throw new RuntimeException(
                !$screenDefinitionJson['ok']
                    ? $screenDefinitionJson['error']
                    : $runtimePreviewJson['error'],
            );
        }

        $screenDefinition = $screenDefinitionJson['data'];
        $runtimePreview = $runtimePreviewJson['data'];
        $contracts = is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : [];
        $contract = is_array($contracts[0] ?? null) ? $contracts[0] : [];
        $screens = is_array($contract['screens'] ?? null) ? $contract['screens'] : [];
        $actions = is_array($contract['actions'] ?? null) ? $contract['actions'] : [];
        $listScreen = is_array($screens[0] ?? null) ? $screens[0] : [];
        $fields = is_array($listScreen['fields'] ?? null) ? $listScreen['fields'] : [];
        $runtimeScreens = is_array($runtimePreview['screens'] ?? null) ? $runtimePreview['screens'] : [];
        $runtimeListScreen = app_sample32_no_code_ui_test_lab_find_by_value($runtimeScreens, 'screen_key', 'no_code_lab_card_list');
        $runtimeListRows = is_array($runtimeListScreen['data']['rows'] ?? null) ? $runtimeListScreen['data']['rows'] : [];

        app_sample32_no_code_ui_test_lab_assert_same('no-code-screen-definition-v0', $screenDefinition['definition_version'] ?? '', 'definition_version', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same($projectKey, $screenDefinition['project_key'] ?? '', 'project_key', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same(1, count($contracts), 'contract count', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same($tableName, $contract['contract_key'] ?? '', 'contract_key', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same(['list', 'detail', 'form'], app_sample32_no_code_ui_test_lab_extract_names($screens, 'screen_type'), 'screen types', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same(
            ['id', 'title', 'status', 'owner_name', 'priority', 'due_on', 'notes'],
            app_sample32_no_code_ui_test_lab_extract_names($fields, 'field_key'),
            'field keys',
            $assertionErrors,
        );
        app_sample32_no_code_ui_test_lab_assert_same(1, count($actions), 'action count', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same('archive_no_code_lab_card', $actions[0]['action_key'] ?? '', 'action key', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same('disabled', $actions[0]['availability'] ?? '', 'action availability', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same('no-code-runtime-v0', $runtimePreview['runtime_version'] ?? '', 'runtime_version', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same(3, count($runtimeScreens), 'runtime screen count', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same(2, count($runtimeListRows), 'runtime preview row count', $assertionErrors);
        app_sample32_no_code_ui_test_lab_assert_same('Fixture list card', $runtimeListRows[0]['title']['display_value'] ?? '', 'runtime first row title', $assertionErrors);

        $runtimePreviewHtml = is_file($publishedRoot . '/runtime-preview.html')
            ? (string) file_get_contents($publishedRoot . '/runtime-preview.html')
            : '';
        app_sample32_no_code_ui_test_lab_assert_same(
            true,
            str_contains($runtimePreviewHtml, 'no_code_lab_card_list')
                && str_contains($runtimePreviewHtml, 'archive_no_code_lab_card')
                && str_contains($runtimePreviewHtml, 'data-action-disabled-reason="policy-not-enabled"'),
            'runtime preview html',
            $assertionErrors,
        );

        $steps['screen_definition'] = [
            'definition_version' => $screenDefinition['definition_version'] ?? '',
            'project_key' => $screenDefinition['project_key'] ?? '',
            'contract_key' => $contract['contract_key'] ?? '',
            'screen_types' => app_sample32_no_code_ui_test_lab_extract_names($screens, 'screen_type'),
            'field_keys' => app_sample32_no_code_ui_test_lab_extract_names($fields, 'field_key'),
            'action_key' => $actions[0]['action_key'] ?? '',
            'action_availability' => $actions[0]['availability'] ?? '',
        ];
        $steps['runtime_preview'] = [
            'runtime_version' => $runtimePreview['runtime_version'] ?? '',
            'screen_count' => count($runtimeScreens),
            'seeded_preview_row_count' => count($runtimeListRows),
            'published_root' => $publishedRoot,
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'table_name' => $tableName,
            'requested_by' => $requestedBy,
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => $assertionErrors === [],
        'project_key' => $projectKey,
        'table_name' => $tableName,
        'requested_by' => $requestedBy,
        'steps' => $steps,
        'assertion_errors' => $assertionErrors,
        'error' => $assertionErrors === []
            ? ''
            : 'sample32 no-code UI test lab assertions failed.',
    ];
}
