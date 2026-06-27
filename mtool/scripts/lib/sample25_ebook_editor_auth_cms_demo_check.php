<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY = 'SAMPLE25';
const APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_TABLE_NAMES = [
    'ebook_editor_book',
    'ebook_editor_chapter',
];
const APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_KEYS = [
    'DATACLASS-PHP',
    'DBACCESS-PHP',
    'OPENAPI-JSON',
    'AUTH-PROXY-SERVER',
];
const APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_OPENAPI_PATHS = [
    '/proxyserver-EbookEditorChapter-GetEditorEbookChapter.php',
    '/proxyserver-EbookEditorChapter-UpdateEditorEbookChapterDraft.php',
    '/proxyserver-EbookEditorChapter-PublishEditorEbookChapter.php',
];
const APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_AUTH_HANDLER_CLASS = 'EbookEditorChapterPublishEditorEbookChapterProxyHandlerBase';

function app_sample25_ebook_editor_auth_cms_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample25-ebook-editor-auth-cms-demo');
}

function app_sample25_ebook_editor_auth_cms_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample25_ebook_editor_auth_cms_tree_snapshot(string $root): array
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
        $contents = file_get_contents($absolutePath);
        if (!is_string($contents)) {
            return [
                'ok' => false,
                'root' => $root,
                'file_count' => 0,
                'total_bytes' => 0,
                'files' => [],
                'error' => 'file を読めません: ' . $relativePath,
            ];
        }
        if (str_ends_with($relativePath, '.php')) {
            $contents = preg_replace('/[ \t]+(\r?\n)/', '$1', $contents) ?? $contents;
        }

        $files[] = [
            'relative_path' => $relativePath,
            'sha256' => hash('sha256', $contents),
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

function app_sample25_ebook_editor_auth_cms_compare_file_sets(
    string $sourceOutputKey,
    array $expectedFiles,
    array $actualFiles,
    array &$errors,
    ?string $expectedRoot = null,
    ?string $actualRoot = null,
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

        if (
            $sourceOutputKey === 'AUTH-PROXY-SERVER'
            && $relativePath === 'build-plan.json'
            && $expectedExists
            && $actualExists
            && is_string($expectedRoot)
            && is_string($actualRoot)
        ) {
            $ok = app_sample25_ebook_editor_auth_cms_compare_build_plan_json(
                $sourceOutputKey,
                $expectedRoot,
                $actualRoot,
                $errors,
            );
        } elseif (!$expectedExists) {
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

function app_sample25_ebook_editor_auth_cms_read_json_file(string $path): array
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

function app_sample25_ebook_editor_auth_cms_normalize_build_plan_for_compare(mixed $payload): mixed
{
    if (!is_array($payload)) {
        return $payload;
    }

    $normalized = $payload;
    unset($normalized['generated_catalog_summary']);

    if (isset($normalized['items']) && is_array($normalized['items'])) {
        foreach ($normalized['items'] as $itemIndex => $item) {
            if (!is_array($item)) {
                continue;
            }
            unset($item['function_updated_at'], $item['target_updated_at'], $item['line']);
            $normalized['items'][$itemIndex] = $item;
        }
    }

    return $normalized;
}

function app_sample25_ebook_editor_auth_cms_compare_build_plan_json(
    string $sourceOutputKey,
    string $expectedRoot,
    string $actualRoot,
    array &$errors,
): bool {
    $relativePath = 'build-plan.json';
    $expectedJson = app_sample25_ebook_editor_auth_cms_read_json_file(rtrim($expectedRoot, '/') . '/' . $relativePath);
    $actualJson = app_sample25_ebook_editor_auth_cms_read_json_file(rtrim($actualRoot, '/') . '/' . $relativePath);
    if (!$expectedJson['ok'] || !$actualJson['ok']) {
        $errors[] = !$expectedJson['ok'] ? $expectedJson['error'] : $actualJson['error'];
        return false;
    }

    $expectedPayload = app_sample25_ebook_editor_auth_cms_normalize_build_plan_for_compare($expectedJson['payload']);
    $actualPayload = app_sample25_ebook_editor_auth_cms_normalize_build_plan_for_compare($actualJson['payload']);
    if ($expectedPayload === $actualPayload) {
        return true;
    }

    $errors[] = $sourceOutputKey . ' normalized build-plan mismatch';
    return false;
}

function app_sample25_ebook_editor_auth_cms_restore_env(string $name, string|false $previousValue): void
{
    if ($previousValue === false) {
        putenv($name);
        return;
    }

    putenv($name . '=' . $previousValue);
}

function app_sample25_ebook_editor_auth_cms_authorize_generated_handler(string $actualRoot, array $payload): void
{
    if (!class_exists('MtoolGeneratedSingleProxyEndpointBase', false)) {
        require_once rtrim($actualRoot, '/') . '/_support/single_proxy_runtime.php';
    }
    require_once rtrim($actualRoot, '/') . '/_base/handlers/EbookEditorChapterPublishEditorEbookChapterProxyHandler.php';

    $handlerClass = APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_AUTH_HANDLER_CLASS;
    if (!class_exists($handlerClass)) {
        throw new RuntimeException('generated handler class が見つかりません: ' . $handlerClass);
    }

    $handler = new $handlerClass();
    $authorize = Closure::bind(
        function (array $payload): void {
            $this->authorizeRequest($payload);
        },
        $handler,
        'MtoolGeneratedSingleProxyEndpointBase',
    );
    if (!$authorize instanceof Closure) {
        throw new RuntimeException('authorizeRequest closure を作成できません。');
    }

    $authorize($payload);
}

function app_sample25_ebook_editor_auth_cms_capture_auth_case(
    string $actualRoot,
    string $caseName,
    array $payload,
    string|false $tokenEnv,
): array {
    $previousToken = getenv('MTOOL_PROXY_PROJECT_TOKEN');
    try {
        app_sample25_ebook_editor_auth_cms_restore_env('MTOOL_PROXY_PROJECT_TOKEN', $tokenEnv);
        app_sample25_ebook_editor_auth_cms_authorize_generated_handler($actualRoot, $payload);

        return [
            'case' => $caseName,
            'ok' => true,
            'message' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'case' => $caseName,
            'ok' => false,
            'message' => $throwable->getMessage(),
        ];
    } finally {
        app_sample25_ebook_editor_auth_cms_restore_env('MTOOL_PROXY_PROJECT_TOKEN', $previousToken);
    }
}

function app_sample25_ebook_editor_auth_cms_run_auth_cases(string $actualRoot, array &$errors): array
{
    $cases = [
        app_sample25_ebook_editor_auth_cms_capture_auth_case($actualRoot, 'missing_token', [], 'sample25-token'),
        app_sample25_ebook_editor_auth_cms_capture_auth_case($actualRoot, 'empty_token', ['TOKEN' => ''], 'sample25-token'),
        app_sample25_ebook_editor_auth_cms_capture_auth_case($actualRoot, 'missing_env', ['TOKEN' => 'sample25-token'], false),
        app_sample25_ebook_editor_auth_cms_capture_auth_case($actualRoot, 'wrong_token', ['TOKEN' => 'wrong-token'], 'sample25-token'),
        app_sample25_ebook_editor_auth_cms_capture_auth_case($actualRoot, 'matching_token', ['TOKEN' => 'sample25-token'], 'sample25-token'),
    ];

    $expected = [
        'missing_token' => ['ok' => false, 'message_contains' => 'TOKEN が必要です。'],
        'empty_token' => ['ok' => false, 'message_contains' => 'TOKEN は空でない string'],
        'missing_env' => ['ok' => false, 'message_contains' => 'MTOOL_PROXY_PROJECT_TOKEN が未設定です。'],
        'wrong_token' => ['ok' => false, 'message_contains' => 'TOKEN が一致しません。'],
        'matching_token' => ['ok' => true, 'message_contains' => ''],
    ];

    foreach ($cases as $case) {
        $caseName = (string) ($case['case'] ?? '');
        $expectedCase = $expected[$caseName] ?? null;
        if (!is_array($expectedCase)) {
            $errors[] = 'unknown auth case: ' . $caseName;
            continue;
        }

        if ((bool) $case['ok'] !== (bool) $expectedCase['ok']) {
            $errors[] = 'auth case result mismatch: ' . $caseName;
            continue;
        }

        $messageContains = (string) ($expectedCase['message_contains'] ?? '');
        if ($messageContains !== '' && !str_contains((string) ($case['message'] ?? ''), $messageContains)) {
            $errors[] = 'auth case message mismatch: ' . $caseName
                . ' expected_contains=' . $messageContains
                . ' actual=' . (string) ($case['message'] ?? '');
        }
    }

    return $cases;
}

function app_sample25_ebook_editor_auth_cms_publish_one(
    array $app,
    string $requestedBy,
    string $referenceRoot,
    string $sourceOutputKey,
    array &$errors,
): array {
    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY,
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
        APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY,
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
    $expectedSnapshot = app_sample25_ebook_editor_auth_cms_tree_snapshot($expectedRoot);
    $actualSnapshot = app_sample25_ebook_editor_auth_cms_tree_snapshot($publishedRoot);
    if (!$expectedSnapshot['ok'] || !$actualSnapshot['ok']) {
        return [
            'ok' => false,
            'source_output_key' => $sourceOutputKey,
            'error' => !$expectedSnapshot['ok'] ? $expectedSnapshot['error'] : $actualSnapshot['error'],
        ];
    }

    $fileChecks = app_sample25_ebook_editor_auth_cms_compare_file_sets(
        $sourceOutputKey,
        $expectedSnapshot['files'],
        $actualSnapshot['files'],
        $errors,
        $expectedRoot,
        $publishedRoot,
    );

    $openApiSummary = [];
    $authCases = [];
    if ($sourceOutputKey === 'OPENAPI-JSON') {
        $openApiJsonResult = app_sample25_ebook_editor_auth_cms_read_json_file($publishedRoot . '/openapi.json');
        if (!$openApiJsonResult['ok']) {
            return [
                'ok' => false,
                'source_output_key' => $sourceOutputKey,
                'error' => $openApiJsonResult['error'],
            ];
        }

        $spec = $openApiJsonResult['payload'];
        app_sample25_ebook_editor_auth_cms_assert_same('3.0.3', (string) ($spec['openapi'] ?? ''), 'openapi version', $errors);
        app_sample25_ebook_editor_auth_cms_assert_same('Sample25 OpenAPI JSON', (string) ($spec['info']['title'] ?? ''), 'openapi info title', $errors);
        foreach (APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_OPENAPI_PATHS as $path) {
            app_sample25_ebook_editor_auth_cms_assert_same(true, is_array($spec['paths'][$path] ?? null), 'openapi path exists ' . $path, $errors);
        }
        app_sample25_ebook_editor_auth_cms_assert_same(
            true,
            is_array($spec['components']['schemas']['EbookEditorChapterData'] ?? null),
            'EbookEditorChapterData schema exists',
            $errors,
        );
        $openApiSummary = [
            'paths' => array_keys($spec['paths'] ?? []),
            'schema_keys' => array_keys($spec['components']['schemas'] ?? []),
        ];
    } elseif ($sourceOutputKey === 'AUTH-PROXY-SERVER') {
        $authCases = app_sample25_ebook_editor_auth_cms_run_auth_cases($publishedRoot, $errors);
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
        'auth_cases' => $authCases,
        'error' => '',
    ];
}

function app_sample25_ebook_editor_auth_cms_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'table_imports' => [],
        'table_previews_after_import' => [],
        'data_class_sync' => null,
        'data_class_preview_after_sync' => null,
        'outputs' => [],
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

    foreach (APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_TABLE_NAMES as $tableName) {
        $tableImport = app_project_table_import_apply(
            $app,
            APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY,
            'live-schema',
            $tableName,
        );
        $steps['table_imports'][$tableName] = [
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
                'error' => 'table import に失敗しました: ' . $tableName . ': ' . $tableImport['error'],
            ];
        }

        $tablePreview = app_project_table_import_preview(
            $app,
            APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY,
            'live-schema',
            $tableName,
        );
        $steps['table_previews_after_import'][$tableName] = [
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
                'error' => 'table preview に失敗しました: ' . $tableName . ': ' . $tablePreview['error'],
            ];
        }
        app_sample25_ebook_editor_auth_cms_assert_same(1, $tablePreview['summary']['source_table_count'], 'table source_table_count ' . $tableName, $errors);
    }

    $dataClassSync = app_project_data_class_sync_apply($app, APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY);
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

    $dataClassPreview = app_project_data_class_sync_preview($app, APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_PROJECT_KEY);
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
    app_sample25_ebook_editor_auth_cms_assert_same(2, $dataClassPreview['summary']['canonical_data_class_count'], 'data_class canonical_data_class_count', $errors);

    foreach (APP_SAMPLE25_EBOOK_EDITOR_AUTH_CMS_KEYS as $sourceOutputKey) {
        $outputResult = app_sample25_ebook_editor_auth_cms_publish_one(
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

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : 'sample25 ebook editor auth cms verification failed.',
    ];
}
