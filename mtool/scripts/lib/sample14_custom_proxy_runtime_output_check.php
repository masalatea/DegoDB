<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/custom_proxy_repository.php';
require_once dirname(__DIR__, 2) . '/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/app/source_output_repository.php';

const APP_SAMPLE14_CUSTOM_PROXY_PROJECT_KEY = 'SAMPLE14';
const APP_SAMPLE14_CUSTOM_PROXY_KEY = 'CATALOG-SUMMARY';
const APP_SAMPLE14_CUSTOM_PROXY_SOURCE_OUTPUT_KEY = 'CUSTOM-PROXY-SERVER';
const APP_SAMPLE14_CUSTOM_PROXY_REFERENCE_FILES = [
    'README.md',
    'build-plan.json',
    'proxyserver-Catalog-Summary.php',
    '_base/handlers/CatalogSummaryProxyHandler.php',
    '_wrappers/handlers/CatalogSummaryProxyHandler.php',
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

function app_sample14_custom_proxy_run(array $app, string $requestedBy, string $referenceRoot): array
{
    $steps = [
        'custom_proxy' => null,
        'custom_proxy_steps' => null,
        'custom_proxy_targets' => null,
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
    app_sample14_custom_proxy_assert_same(1, (int) ($buildPlan['custom_proxy_count'] ?? 0), 'build plan custom_proxy_count', $errors);
    app_sample14_custom_proxy_assert_same(2, (int) ($buildPlan['step_count'] ?? 0), 'build plan step_count', $errors);
    app_sample14_custom_proxy_assert_same(0, (int) ($buildPlan['unresolved_step_count'] ?? -1), 'build plan unresolved_step_count', $errors);
    app_sample14_custom_proxy_assert_same(
        APP_SAMPLE14_CUSTOM_PROXY_KEY,
        (string) ($buildPlan['items'][0]['custom_proxy_key'] ?? ''),
        'build plan custom_proxy_key',
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

    return [
        'ok' => $errors === [],
        'steps' => $steps,
        'assertion_errors' => $errors,
        'error' => $errors === [] ? '' : 'sample14 custom proxy runtime verification failed.',
    ];
}
