<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE16_AUTH_PROXY_PROJECT_KEY = 'SAMPLE16';
const APP_SAMPLE16_AUTH_PROXY_TABLE_NAME = 'AuthTask';
const APP_SAMPLE16_AUTH_PROXY_SOURCE_OUTPUT_KEY = 'AUTH-PROXY-SERVER';
const APP_SAMPLE16_AUTH_PROXY_HANDLER_CLASS = 'AuthTaskGetAuthTaskProxyHandlerBase';
const APP_SAMPLE16_AUTH_PROXY_REFERENCE_FILES = [
    'README.md',
    'build-plan.json',
    'proxyserver-AuthTask-GetAuthTask.php',
    '_base/handlers/AuthTaskGetAuthTaskProxyHandler.php',
    '_support/runtime_dbclasses/_support/mtool_runtime_db.php',
    '_support/runtime_dbclasses/autoload_proxy_runtime.php',
    '_support/runtime_dbclasses/base/data-AuthTaskBase.php',
    '_support/runtime_dbclasses/base/dbaccess-AuthTaskBase.php',
    '_support/runtime_dbclasses/data-AuthTask.php',
    '_support/runtime_dbclasses/dbaccess-AuthTask.php',
    '_support/single_proxy_loader.php',
    '_support/single_proxy_runtime.php',
    '_wrappers/handlers/AuthTaskGetAuthTaskProxyHandler.php',
];

function app_sample16_auth_proxy_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample16-authenticated-proxy');
}

function app_sample16_auth_proxy_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample16_auth_proxy_file_digest(string $root, string $relativePath): array
{
    $path = rtrim($root, '/') . '/' . $relativePath;
    if (!is_file($path)) {
        return [
            'ok' => false,
            'relative_path' => $relativePath,
            'sha256' => '',
            'size' => 0,
            'error' => 'file が見つかりません: ' . $path,
        ];
    }

    $contents = file_get_contents($path);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'relative_path' => $relativePath,
            'sha256' => '',
            'size' => 0,
            'error' => 'file を読めません: ' . $relativePath,
        ];
    }

    if (str_ends_with($relativePath, '.php')) {
        $contents = preg_replace('/[ \t]+(\r?\n)/', '$1', $contents) ?? $contents;
    }

    return [
        'ok' => true,
        'relative_path' => $relativePath,
        'sha256' => hash('sha256', $contents),
        'size' => strlen($contents),
        'error' => '',
    ];
}

function app_sample16_auth_proxy_read_json_file(string $path): array
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

function app_sample16_auth_proxy_normalize_build_plan_for_compare(mixed $payload): mixed
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

function app_sample16_auth_proxy_compare_build_plan_json(
    string $referenceRoot,
    string $actualRoot,
    array &$errors,
): array {
    $relativePath = 'build-plan.json';
    $expected = app_sample16_auth_proxy_file_digest($referenceRoot, $relativePath);
    $actual = app_sample16_auth_proxy_file_digest($actualRoot, $relativePath);
    $ok = $expected['ok'] && $actual['ok'];

    if (!$expected['ok']) {
        $errors[] = $expected['error'];
    } elseif (!$actual['ok']) {
        $errors[] = $actual['error'];
    } else {
        $expectedJson = app_sample16_auth_proxy_read_json_file(rtrim($referenceRoot, '/') . '/' . $relativePath);
        $actualJson = app_sample16_auth_proxy_read_json_file(rtrim($actualRoot, '/') . '/' . $relativePath);
        if (!$expectedJson['ok'] || !$actualJson['ok']) {
            $ok = false;
            $errors[] = !$expectedJson['ok'] ? $expectedJson['error'] : $actualJson['error'];
        } else {
            $expectedPayload = app_sample16_auth_proxy_normalize_build_plan_for_compare($expectedJson['payload']);
            $actualPayload = app_sample16_auth_proxy_normalize_build_plan_for_compare($actualJson['payload']);
            $ok = $expectedPayload === $actualPayload;
            if (!$ok) {
                $errors[] = APP_SAMPLE16_AUTH_PROXY_SOURCE_OUTPUT_KEY . ' normalized build-plan mismatch';
            }
        }
    }

    return [
        'relative_path' => $relativePath,
        'expected_exists' => $expected['ok'],
        'actual_exists' => $actual['ok'],
        'expected_sha256' => $expected['sha256'],
        'actual_sha256' => $actual['sha256'],
        'ok' => $ok,
        'comparison' => 'normalized-json',
    ];
}

function app_sample16_auth_proxy_compare_reference_files(string $referenceRoot, string $actualRoot, array &$errors): array
{
    $checks = [];

    foreach (APP_SAMPLE16_AUTH_PROXY_REFERENCE_FILES as $relativePath) {
        if ($relativePath === 'build-plan.json') {
            $checks[] = app_sample16_auth_proxy_compare_build_plan_json($referenceRoot, $actualRoot, $errors);
            continue;
        }

        $expected = app_sample16_auth_proxy_file_digest($referenceRoot, $relativePath);
        $actual = app_sample16_auth_proxy_file_digest($actualRoot, $relativePath);
        $ok = $expected['ok'] && $actual['ok'] && $expected['sha256'] === $actual['sha256'];

        if (!$expected['ok']) {
            $errors[] = $expected['error'];
        } elseif (!$actual['ok']) {
            $errors[] = $actual['error'];
        } elseif ($expected['sha256'] !== $actual['sha256']) {
            $errors[] = APP_SAMPLE16_AUTH_PROXY_SOURCE_OUTPUT_KEY . ' digest mismatch: ' . $relativePath
                . ' expected=' . $expected['sha256']
                . ' actual=' . $actual['sha256'];
        }

        $checks[] = [
            'relative_path' => $relativePath,
            'expected_exists' => $expected['ok'],
            'actual_exists' => $actual['ok'],
            'expected_sha256' => $expected['sha256'],
            'actual_sha256' => $actual['sha256'],
            'ok' => $ok,
        ];
    }

    return $checks;
}

function app_sample16_auth_proxy_restore_env(string $name, string|false $previousValue): void
{
    if ($previousValue === false) {
        putenv($name);
        return;
    }

    putenv($name . '=' . $previousValue);
}

function app_sample16_auth_proxy_authorize_generated_handler(string $actualRoot, array $payload): void
{
    require_once rtrim($actualRoot, '/') . '/_support/single_proxy_runtime.php';
    require_once rtrim($actualRoot, '/') . '/_base/handlers/AuthTaskGetAuthTaskProxyHandler.php';

    $handlerClass = APP_SAMPLE16_AUTH_PROXY_HANDLER_CLASS;
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

function app_sample16_auth_proxy_capture_auth_case(
    string $actualRoot,
    string $caseName,
    array $payload,
    string|false $tokenEnv,
): array {
    $previousToken = getenv('DEGODB_PROXY_BEARER_TOKEN');
    $previousAuthorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    try {
        app_sample16_auth_proxy_restore_env('DEGODB_PROXY_BEARER_TOKEN', $tokenEnv);
        if (isset($payload['_authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = (string) $payload['_authorization'];
            unset($payload['_authorization']);
        } else {
            unset($_SERVER['HTTP_AUTHORIZATION']);
        }
        app_sample16_auth_proxy_authorize_generated_handler($actualRoot, $payload);

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
        app_sample16_auth_proxy_restore_env('DEGODB_PROXY_BEARER_TOKEN', $previousToken);
        if ($previousAuthorization === null) {
            unset($_SERVER['HTTP_AUTHORIZATION']);
        } else {
            $_SERVER['HTTP_AUTHORIZATION'] = $previousAuthorization;
        }
    }
}

function app_sample16_auth_proxy_run_auth_cases(string $actualRoot, array &$errors): array
{
    $cases = [
        app_sample16_auth_proxy_capture_auth_case($actualRoot, 'missing_authorization', [], 'sample16-token'),
        app_sample16_auth_proxy_capture_auth_case($actualRoot, 'malformed_authorization', ['_authorization' => 'Token sample16-token'], 'sample16-token'),
        app_sample16_auth_proxy_capture_auth_case($actualRoot, 'missing_env', ['_authorization' => 'Bearer sample16-token'], false),
        app_sample16_auth_proxy_capture_auth_case($actualRoot, 'wrong_token', ['_authorization' => 'Bearer wrong-token'], 'sample16-token'),
        app_sample16_auth_proxy_capture_auth_case($actualRoot, 'matching_token', ['_authorization' => 'Bearer sample16-token'], 'sample16-token'),
    ];

    $expected = [
        'missing_authorization' => ['ok' => false, 'message_contains' => 'Authorization bearer header が必要です。'],
        'malformed_authorization' => ['ok' => false, 'message_contains' => 'Authorization header は Bearer token 形式'],
        'missing_env' => ['ok' => false, 'message_contains' => 'DEGODB_PROXY_BEARER_TOKEN が未設定です。'],
        'wrong_token' => ['ok' => false, 'message_contains' => 'Bearer token が一致しません。'],
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

function app_sample16_auth_proxy_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'source_output' => null,
        'output' => null,
        'auth_cases' => [],
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
        APP_SAMPLE16_AUTH_PROXY_PROJECT_KEY,
        'live-schema',
        APP_SAMPLE16_AUTH_PROXY_TABLE_NAME,
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

    $dataClassSync = app_project_data_class_sync_apply($app, APP_SAMPLE16_AUTH_PROXY_PROJECT_KEY);
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

    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE16_AUTH_PROXY_PROJECT_KEY,
        APP_SAMPLE16_AUTH_PROXY_SOURCE_OUTPUT_KEY,
    );
    $steps['source_output'] = $sourceOutputResult;
    if (!$sourceOutputResult['ok'] || !is_array($sourceOutputResult['item'] ?? null)) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $sourceOutputResult['error'] !== '' ? $sourceOutputResult['error'] : 'source output が見つかりません。',
        ];
    }
    app_sample16_auth_proxy_assert_same(
        'single-proxy-server',
        (string) ($sourceOutputResult['item']['artifact_strategy'] ?? ''),
        'source output artifact_strategy',
        $errors,
    );

    $artifactResult = app_project_output_create_from_definition(
        $app,
        APP_SAMPLE16_AUTH_PROXY_PROJECT_KEY,
        $sourceOutputResult['item'],
        $requestedBy,
    );
    if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $artifactResult['error'],
        ];
    }

    $publishResult = app_project_output_publish_artifact(
        $app,
        $artifactResult['artifact'],
        $sourceOutputResult['item'],
    );
    $steps['output'] = [
        'ok' => $publishResult['ok'],
        'artifact' => $artifactResult['artifact'],
        'published' => $publishResult['published'],
        'error' => $publishResult['error'],
    ];
    if (!$publishResult['ok'] || $publishResult['published'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => 'project output publish に失敗しました: ' . $publishResult['error'],
        ];
    }

    $actualRoot = (string) ($publishResult['published']['published_root'] ?? '');
    $outputChecks = app_sample16_auth_proxy_compare_reference_files(
        rtrim($referenceRoot, '/') . '/' . APP_SAMPLE16_AUTH_PROXY_SOURCE_OUTPUT_KEY,
        $actualRoot,
        $errors,
    );
    $steps['output']['file_checks'] = $outputChecks;
    $steps['auth_cases'] = app_sample16_auth_proxy_run_auth_cases($actualRoot, $errors);

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : implode("\n", $errors),
    ];
}
