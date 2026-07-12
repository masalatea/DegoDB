<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/custom_proxy_repository.php';
require_once dirname(__DIR__, 2) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY = 'SAMPLE14';
const APP_SAMPLE14_CUSTOM_PROXY_KEY = 'CATALOG-SUMMARY';
const APP_SAMPLE14_TRANSACTION_PROXY_KEY = 'TRANSACTION-PAIR';
const APP_SAMPLE14_TRANSACTION_TABLE_NAME = 'sample14_transaction_item';
const APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY = 'CUSTOM-PROXY-SERVER';
const APP_SAMPLE14_CUSTOM_PROXY_REFERENCE_FILES = [
    'README.md',
    'build-plan.json',
    'proxyserver-Catalog-Summary.php',
    '_base/handlers/CatalogSummaryProxyHandler.php',
    '_wrappers/handlers/CatalogSummaryProxyHandler.php',
    'proxyserver-Transaction-Pair.php',
    '_base/handlers/TransactionPairProxyHandler.php',
    '_wrappers/handlers/TransactionPairProxyHandler.php',
];

function app_sample14_custom_proxy_default_reference_root(): string
{
    return app_sample_pack_reference_root('sample14-custom-proxy-runtime');
}

function app_sample14_custom_proxy_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function app_sample14_custom_proxy_file_digest(string $root, string $relativePath): array
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

function app_sample14_custom_proxy_compare_reference_files(string $referenceRoot, string $actualRoot, array &$errors): array
{
    $checks = [];

    foreach (APP_SAMPLE14_CUSTOM_PROXY_REFERENCE_FILES as $relativePath) {
        if ($relativePath === 'build-plan.json') {
            $checks[] = app_sample14_custom_proxy_compare_build_plan_json($referenceRoot, $actualRoot, $errors);
            continue;
        }

        $expected = app_sample14_custom_proxy_file_digest($referenceRoot, $relativePath);
        $actual = app_sample14_custom_proxy_file_digest($actualRoot, $relativePath);
        $ok = $expected['ok'] && $actual['ok'] && $expected['sha256'] === $actual['sha256'];

        if (!$expected['ok']) {
            $errors[] = $expected['error'];
        } elseif (!$actual['ok']) {
            $errors[] = $actual['error'];
        } elseif ($expected['sha256'] !== $actual['sha256']) {
            $errors[] = APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY . ' digest mismatch: ' . $relativePath
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

function app_sample14_custom_proxy_read_json_file(string $path): array
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

function app_sample14_custom_proxy_normalize_build_plan_for_compare(mixed $payload): mixed
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
            unset($item['updated_at']);

            if (isset($item['steps']) && is_array($item['steps'])) {
                foreach ($item['steps'] as $stepIndex => $step) {
                    if (!is_array($step)) {
                        continue;
                    }
                    unset($step['id'], $step['updated_at'], $step['line']);
                    $item['steps'][$stepIndex] = $step;
                }
            }

            $normalized['items'][$itemIndex] = $item;
        }
    }

    return $normalized;
}

function app_sample14_custom_proxy_compare_build_plan_json(
    string $referenceRoot,
    string $actualRoot,
    array &$errors,
): array {
    $relativePath = 'build-plan.json';
    $expected = app_sample14_custom_proxy_file_digest($referenceRoot, $relativePath);
    $actual = app_sample14_custom_proxy_file_digest($actualRoot, $relativePath);
    $ok = $expected['ok'] && $actual['ok'];

    if (!$expected['ok']) {
        $errors[] = $expected['error'];
    } elseif (!$actual['ok']) {
        $errors[] = $actual['error'];
    } else {
        $expectedJson = app_sample14_custom_proxy_read_json_file(rtrim($referenceRoot, '/') . '/' . $relativePath);
        $actualJson = app_sample14_custom_proxy_read_json_file(rtrim($actualRoot, '/') . '/' . $relativePath);
        if (!$expectedJson['ok'] || !$actualJson['ok']) {
            $ok = false;
            $errors[] = !$expectedJson['ok'] ? $expectedJson['error'] : $actualJson['error'];
        } else {
            $expectedPayload = app_sample14_custom_proxy_normalize_build_plan_for_compare($expectedJson['payload']);
            $actualPayload = app_sample14_custom_proxy_normalize_build_plan_for_compare($actualJson['payload']);
            $ok = $expectedPayload === $actualPayload;
            if (!$ok) {
                $errors[] = APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY . ' normalized build-plan mismatch';
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

function app_sample14_custom_proxy_post_json(string $url, array $payload): array
{
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($body)) {
        return ['ok' => false, 'status' => 0, 'payload' => null, 'error' => 'request JSON encode に失敗しました。'];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $body,
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);
    $responseBody = file_get_contents($url, false, $context);
    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches) === 1) {
            $status = (int) $matches[1];
            break;
        }
    }
    $decoded = is_string($responseBody) ? json_decode($responseBody, true) : null;

    return [
        'ok' => is_array($decoded),
        'status' => $status,
        'payload' => is_array($decoded) ? $decoded : null,
        'error' => is_array($decoded) ? '' : 'endpoint response JSON を解析できません。',
    ];
}

function app_sample14_custom_proxy_verify_transaction_endpoint(array $app, string $publishedRoot): array
{
    if (!function_exists('proc_open')) {
        return ['ok' => false, 'success' => null, 'failure' => null, 'rows' => [], 'error' => 'proc_open が利用できません。'];
    }

    $labPdo = app_create_pdo_from_db_config(app_database_config($app, 'lab_db'));
    $labPdo->exec(
        'CREATE TABLE IF NOT EXISTS sample14_transaction_item (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_key VARCHAR(100) NOT NULL,
            step_name VARCHAR(100) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_sample14_transaction_item_key (transaction_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $labPdo->exec('DELETE FROM sample14_transaction_item');

    $port = random_int(19000, 24999);
    $env = getenv();
    $env = is_array($env) ? $env : [];
    $env['MTOOL_PROXY_DB_HOST'] = (string) getenv('APP_LAB_DB_HOST');
    $env['MTOOL_PROXY_DB_PORT'] = (string) getenv('APP_LAB_DB_PORT');
    $env['MTOOL_PROXY_DB_NAME'] = (string) getenv('APP_LAB_DB_NAME');
    $env['MTOOL_PROXY_DB_USER'] = (string) getenv('APP_LAB_DB_USER');
    $env['MTOOL_PROXY_DB_PASSWORD'] = (string) getenv('APP_LAB_DB_PASSWORD');
    unset(
        $env['MTOOL_RUNTIME_DB_HOST'],
        $env['MTOOL_RUNTIME_DB_PORT'],
        $env['MTOOL_RUNTIME_DB_NAME'],
        $env['MTOOL_RUNTIME_DB_USER'],
        $env['MTOOL_RUNTIME_DB_PASSWORD'],
    );

    $process = proc_open(
        [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $publishedRoot],
        [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', '/dev/null', 'a'],
            2 => ['file', '/dev/null', 'a'],
        ],
        $pipes,
        $publishedRoot,
        $env,
    );
    if (!is_resource($process)) {
        return ['ok' => false, 'success' => null, 'failure' => null, 'rows' => [], 'error' => 'generated endpoint server を起動できません。'];
    }

    try {
        $ready = false;
        for ($attempt = 0; $attempt < 40; $attempt++) {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $error, 0.1);
            if (is_resource($socket)) {
                fclose($socket);
                $ready = true;
                break;
            }
            usleep(50000);
        }
        if (!$ready) {
            return ['ok' => false, 'success' => null, 'failure' => null, 'rows' => [], 'error' => 'generated endpoint server がreadyになりません。'];
        }

        $url = 'http://127.0.0.1:' . $port . '/proxyserver-Transaction-Pair.php';
        $success = app_sample14_custom_proxy_post_json($url, [
            'step1' => [
                'Sample14TransactionItemObj' => ['transactionKey' => 'commit-one', 'stepName' => 'first'],
            ],
            'step2' => [
                'Sample14TransactionItemObj' => ['transactionKey' => 'commit-two', 'stepName' => 'second'],
            ],
        ]);
        $failure = app_sample14_custom_proxy_post_json($url, [
            'step1' => [
                'Sample14TransactionItemObj' => ['transactionKey' => 'rollback-one', 'stepName' => 'must-rollback'],
            ],
            'step2' => [
                'Sample14TransactionItemObj' => ['transactionKey' => 'commit-one', 'stepName' => 'duplicate'],
            ],
        ]);

        $rows = $labPdo->query(
            'SELECT transaction_key, step_name FROM sample14_transaction_item ORDER BY id'
        )->fetchAll(PDO::FETCH_ASSOC);
        $ok = $success['ok']
            && $success['status'] === 200
            && ($success['payload']['_status'] ?? '') === 'OK'
            && (int) ($success['payload']['insert_id1'] ?? 0) > 0
            && (int) ($success['payload']['insert_id2'] ?? 0) > 0
            && $failure['ok']
            && $failure['status'] === 500
            && ($failure['payload']['_status'] ?? '') === 'NG'
            && array_column($rows, 'transaction_key') === ['commit-one', 'commit-two'];

        return [
            'ok' => $ok,
            'success' => $success,
            'failure' => $failure,
            'rows' => $rows,
            'error' => $ok ? '' : 'generated transaction endpoint の commit/rollback 結果が期待と一致しません。',
        ];
    } finally {
        proc_terminate($process);
        proc_close($process);
    }
}

function app_sample14_custom_proxy_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'table_import' => null,
        'data_class_sync' => null,
        'custom_proxy' => null,
        'custom_proxy_steps' => null,
        'custom_proxy_targets' => null,
        'transaction_proxy' => null,
        'transaction_proxy_steps' => null,
        'transaction_proxy_targets' => null,
        'transaction_execution' => null,
        'output' => null,
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
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        'live-schema',
        APP_SAMPLE14_TRANSACTION_TABLE_NAME,
    );
    $steps['table_import'] = $tableImport;
    if (!$tableImport['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'transaction tutorial table import に失敗しました。',
        ];
    }

    $dataClassSync = app_project_data_class_sync_apply($app, APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY);
    $steps['data_class_sync'] = $dataClassSync;
    if (!$dataClassSync['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'transaction tutorial data class sync に失敗しました。',
        ];
    }

    $customProxyResult = app_fetch_project_custom_proxy_item(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_CUSTOM_PROXY_KEY,
    );
    $steps['custom_proxy'] = $customProxyResult;
    if (!$customProxyResult['ok'] || $customProxyResult['item'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => $customProxyResult['error'] !== '' ? $customProxyResult['error'] : 'custom proxy が見つかりません。',
        ];
    }
    app_sample14_custom_proxy_assert_same('NoSecurity', (string) ($customProxyResult['item']['auth_type'] ?? ''), 'custom proxy auth_type', $errors);
    app_sample14_custom_proxy_assert_same(2, (int) ($customProxyResult['item']['step_count'] ?? 0), 'custom proxy step_count', $errors);
    app_sample14_custom_proxy_assert_same(1, (int) ($customProxyResult['item']['target_count'] ?? 0), 'custom proxy target_count', $errors);

    $stepCatalogResult = app_fetch_project_custom_proxy_step_catalog(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_CUSTOM_PROXY_KEY,
    );
    $steps['custom_proxy_steps'] = $stepCatalogResult;
    if (!$stepCatalogResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $stepCatalogResult['error'],
        ];
    }
    app_sample14_custom_proxy_assert_same(
        ['dbtable', 'project_source_output'],
        array_column($stepCatalogResult['items'], 'db_access_source_name'),
        'custom proxy step sources',
        $errors,
    );

    $targetKeysResult = app_fetch_project_custom_proxy_target_keys(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_CUSTOM_PROXY_KEY,
    );
    $steps['custom_proxy_targets'] = $targetKeysResult;
    if (!$targetKeysResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $targetKeysResult['error'],
        ];
    }
    app_sample14_custom_proxy_assert_same([APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY], $targetKeysResult['items'], 'custom proxy target keys', $errors);

    $transactionProxyResult = app_fetch_project_custom_proxy_item(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_TRANSACTION_PROXY_KEY,
    );
    $steps['transaction_proxy'] = $transactionProxyResult;
    if (!$transactionProxyResult['ok'] || $transactionProxyResult['item'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $transactionProxyResult['error'] !== ''
                ? $transactionProxyResult['error']
                : 'transaction custom proxy が見つかりません。',
        ];
    }
    app_sample14_custom_proxy_assert_same(1, (int) ($transactionProxyResult['item']['in_transaction'] ?? 0), 'transaction proxy in_transaction', $errors);
    app_sample14_custom_proxy_assert_same(2, (int) ($transactionProxyResult['item']['step_count'] ?? 0), 'transaction proxy step_count', $errors);
    app_sample14_custom_proxy_assert_same(1, (int) ($transactionProxyResult['item']['target_count'] ?? 0), 'transaction proxy target_count', $errors);

    $transactionStepResult = app_fetch_project_custom_proxy_step_catalog(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_TRANSACTION_PROXY_KEY,
    );
    $steps['transaction_proxy_steps'] = $transactionStepResult;
    if (!$transactionStepResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $transactionStepResult['error'],
        ];
    }
    app_sample14_custom_proxy_assert_same(
        [APP_SAMPLE14_TRANSACTION_TABLE_NAME, APP_SAMPLE14_TRANSACTION_TABLE_NAME],
        array_column($transactionStepResult['items'], 'db_access_source_name'),
        'transaction proxy step sources',
        $errors,
    );

    $transactionTargetResult = app_fetch_project_custom_proxy_target_keys(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_TRANSACTION_PROXY_KEY,
    );
    $steps['transaction_proxy_targets'] = $transactionTargetResult;
    if (!$transactionTargetResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $transactionTargetResult['error'],
        ];
    }
    app_sample14_custom_proxy_assert_same(
        [APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY],
        $transactionTargetResult['items'],
        'transaction proxy target keys',
        $errors,
    );

    $sourceOutputResult = app_fetch_project_source_output_item(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
        APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY,
    );
    if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $sourceOutputResult['error'] !== ''
                ? $sourceOutputResult['error']
                : 'source output definition が見つかりません。',
        ];
    }

    $artifactResult = app_project_output_create_from_definition(
        $app,
        APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY,
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
    if (!$publishResult['ok'] || $publishResult['published'] === null) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $publishResult['error'],
        ];
    }

    $publishedRoot = (string) $publishResult['published']['published_root'];
    $expectedRoot = $referenceRoot . '/' . APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY;
    $fileChecks = app_sample14_custom_proxy_compare_reference_files($expectedRoot, $publishedRoot, $errors);

    $buildPlanResult = app_sample14_custom_proxy_read_json_file($publishedRoot . '/build-plan.json');
    if (!$buildPlanResult['ok']) {
        return [
            'ok' => false,
            'steps' => $steps,
            'assertion_errors' => $errors,
            'error' => $buildPlanResult['error'],
        ];
    }

    $buildPlan = $buildPlanResult['payload'];
    app_sample14_custom_proxy_assert_same(2, (int) ($buildPlan['custom_proxy_count'] ?? 0), 'build plan custom_proxy_count', $errors);
    app_sample14_custom_proxy_assert_same(4, (int) ($buildPlan['step_count'] ?? 0), 'build plan step_count', $errors);
    app_sample14_custom_proxy_assert_same(0, (int) ($buildPlan['unresolved_step_count'] ?? -1), 'build plan unresolved_step_count', $errors);
    app_sample14_custom_proxy_assert_same(
        APP_SAMPLE14_CUSTOM_PROXY_KEY,
        (string) ($buildPlan['items'][0]['custom_proxy_key'] ?? ''),
        'build plan custom_proxy_key',
        $errors,
    );
    app_sample14_custom_proxy_assert_same(
        APP_SAMPLE14_TRANSACTION_PROXY_KEY,
        (string) ($buildPlan['items'][1]['custom_proxy_key'] ?? ''),
        'build plan transaction custom_proxy_key',
        $errors,
    );

    $steps['output'] = [
        'source_output_key' => APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY,
        'artifact_key' => $artifactResult['artifact']['artifact_key'],
        'published_root' => $publishedRoot,
        'reference_root' => $expectedRoot,
        'published_file_count' => (int) ($publishResult['published']['published_file_count'] ?? 0),
        'file_checks' => $fileChecks,
        'build_plan_summary' => [
            'custom_proxy_count' => (int) ($buildPlan['custom_proxy_count'] ?? 0),
            'step_count' => (int) ($buildPlan['step_count'] ?? 0),
            'unresolved_step_count' => (int) ($buildPlan['unresolved_step_count'] ?? -1),
        ],
    ];

    $transactionExecution = app_sample14_custom_proxy_verify_transaction_endpoint($app, $publishedRoot);
    $steps['transaction_execution'] = $transactionExecution;
    if (!$transactionExecution['ok']) {
        $errors[] = $transactionExecution['error'];
    }

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : 'sample14 custom proxy runtime verification failed.',
    ];
}
