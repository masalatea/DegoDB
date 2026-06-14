#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/custom_proxy_build_plan_service.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

const APP_CLI_MTOOL_PROXY_PROJECT_KEY = 'MTOOL';
const APP_CLI_MTOOL_PROXY_SOURCE_OUTPUT_KEYS = [
    'DBIMPORT-PROXY-SERVER',
    'DBIMPORT-PROXY-CLIENT',
];

function app_cli_mtool_proxy_default_reference_path(): string
{
    return dirname(__DIR__) . '/reference/mtool-proxy-expected-output.json';
}

function app_cli_mtool_proxy_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_mtool_proxy_outputs.php [--requested-by=NAME] [--reference=PATH]

Options:
  --requested-by=NAME  artifact manifest に残す実行者名 (default: proxy-output-check)
  --reference=PATH     expected output baseline JSON (default: mtool/reference/mtool-proxy-expected-output.json)
  --help               このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     requested_by:string,
 *     reference_path:string,
 *     error:string
 * }
 */
function app_cli_mtool_proxy_parse_args(array $argv): array
{
    $requestedBy = 'proxy-output-check';
    $referencePath = app_cli_mtool_proxy_default_reference_path();

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'requested_by' => $requestedBy,
                'reference_path' => $referencePath,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = app_project_output_normalize_requested_by(
                substr($argument, strlen('--requested-by=')),
            );
            continue;
        }

        if (str_starts_with($argument, '--reference=')) {
            $referencePath = trim(substr($argument, strlen('--reference=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'requested_by' => $requestedBy,
            'reference_path' => $referencePath,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'requested_by' => $requestedBy,
        'reference_path' => $referencePath,
        'error' => '',
    ];
}

/**
 * @param array<mixed> $payload
 */
function app_cli_mtool_proxy_write_json(array $payload, bool $ok): void
{
    $stream = $ok ? STDOUT : STDERR;
    fwrite(
        $stream,
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
}

/**
 * @param list<string> $errors
 */
function app_cli_mtool_proxy_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @return array{
 *     custom_proxy_count:int|null,
 *     step_count:int|null,
 *     unresolved_step_count:int|null,
 *     generated_catalog_summary:array{
 *         total_entities:int|null,
 *         paired_count:int|null,
 *         data_only_count:int|null,
 *         dbaccess_only_count:int|null
 *     }
 * }
 */
function app_cli_mtool_proxy_build_plan_summary_projection(array $plan): array
{
    $generatedCatalogSummary = $plan['generated_catalog_summary'] ?? [];
    if (!is_array($generatedCatalogSummary)) {
        $generatedCatalogSummary = [];
    }

    return [
        'custom_proxy_count' => isset($plan['custom_proxy_count']) ? (int) $plan['custom_proxy_count'] : null,
        'step_count' => isset($plan['step_count']) ? (int) $plan['step_count'] : null,
        'unresolved_step_count' => isset($plan['unresolved_step_count']) ? (int) $plan['unresolved_step_count'] : null,
        'generated_catalog_summary' => [
            'total_entities' => isset($generatedCatalogSummary['total_entities']) ? (int) $generatedCatalogSummary['total_entities'] : null,
            'paired_count' => isset($generatedCatalogSummary['paired_count']) ? (int) $generatedCatalogSummary['paired_count'] : null,
            'data_only_count' => isset($generatedCatalogSummary['data_only_count']) ? (int) $generatedCatalogSummary['data_only_count'] : null,
            'dbaccess_only_count' => isset($generatedCatalogSummary['dbaccess_only_count']) ? (int) $generatedCatalogSummary['dbaccess_only_count'] : null,
        ],
    ];
}

/**
 * @return array{
 *     source_file_count:int|null,
 *     source_total_bytes:int|null,
 *     custom_layer_source:string|null,
 *     custom_layer_file_count:int|null,
 *     custom_layer_total_bytes:int|null
 * }
 */
function app_cli_mtool_proxy_artifact_summary_projection(array $artifact): array
{
    return [
        'source_file_count' => isset($artifact['source_file_count']) ? (int) $artifact['source_file_count'] : null,
        'source_total_bytes' => isset($artifact['source_total_bytes']) ? (int) $artifact['source_total_bytes'] : null,
        'custom_layer_source' => isset($artifact['custom_layer_source']) && is_string($artifact['custom_layer_source'])
            ? $artifact['custom_layer_source']
            : null,
        'custom_layer_file_count' => isset($artifact['custom_layer_file_count']) ? (int) $artifact['custom_layer_file_count'] : null,
        'custom_layer_total_bytes' => isset($artifact['custom_layer_total_bytes']) ? (int) $artifact['custom_layer_total_bytes'] : null,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         outputs:array<string,array{
 *             source_output_key:string,
 *             build_plan_summary:array<string,mixed>,
 *             artifact_summary:array<string,mixed>,
 *             runtime_files:list<array{
 *                 relative_path:string,
 *                 sha256:string
 *             }>
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_cli_mtool_proxy_load_expected_reference(string $path): array
{
    if ($path === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'expected output reference path が空です。',
        ];
    }
    if (!is_file($path)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'expected output reference が見つかりません: ' . $path,
        ];
    }

    $contents = file_get_contents($path);
    if (!is_string($contents) || $contents === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'expected output reference を読み込めません: ' . $path,
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'expected output reference JSON が不正です: ' . $path,
        ];
    }

    $outputs = $decoded['outputs'] ?? null;
    if (!is_array($outputs)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'expected output reference に outputs が必要です。',
        ];
    }

    $normalizedOutputs = [];
    foreach ($outputs as $output) {
        if (!is_array($output)) {
            continue;
        }

        $sourceOutputKey = app_normalize_source_output_key((string) ($output['source_output_key'] ?? ''));
        $buildPlanSummary = $output['build_plan_summary'] ?? null;
        $artifactSummary = $output['artifact_summary'] ?? null;
        $runtimeFiles = $output['runtime_files'] ?? null;

        if (
            $sourceOutputKey === ''
            || !is_array($buildPlanSummary)
            || !is_array($artifactSummary)
            || !is_array($runtimeFiles)
        ) {
            continue;
        }

        $normalizedRuntimeFiles = [];
        foreach ($runtimeFiles as $runtimeFile) {
            if (!is_array($runtimeFile)) {
                continue;
            }

            $relativePath = trim((string) ($runtimeFile['relative_path'] ?? ''));
            $sha256 = strtolower(trim((string) ($runtimeFile['sha256'] ?? '')));
            if ($relativePath === '' || $sha256 === '') {
                continue;
            }

            $normalizedRuntimeFiles[] = [
                'relative_path' => $relativePath,
                'sha256' => $sha256,
            ];
        }

        $normalizedOutputs[$sourceOutputKey] = [
            'source_output_key' => $sourceOutputKey,
            'build_plan_summary' => $buildPlanSummary,
            'artifact_summary' => $artifactSummary,
            'runtime_files' => $normalizedRuntimeFiles,
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'outputs' => $normalizedOutputs,
        ],
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     relative_path:string,
 *     sha256:string
 * }> $expectedFiles
 * @param list<string> $errors
 * @return list<array{
 *     relative_path:string,
 *     expected_sha256:string,
 *     actual_sha256:string,
 *     exists:bool,
 *     ok:bool
 * }>
 */
function app_cli_mtool_proxy_runtime_file_checks(
    string $runtimeRoot,
    array $expectedFiles,
    array &$errors,
): array {
    $checks = [];

    foreach ($expectedFiles as $expectedFile) {
        $relativePath = $expectedFile['relative_path'];
        $expectedSha256 = strtolower($expectedFile['sha256']);
        $absolutePath = $runtimeRoot . '/' . $relativePath;
        $exists = is_file($absolutePath);
        $actualSha256 = $exists ? strtolower((string) hash_file('sha256', $absolutePath)) : '';
        $ok = $exists && $actualSha256 === $expectedSha256;

        if (!$exists) {
            $errors[] = 'runtime file missing: ' . $relativePath;
        } elseif ($actualSha256 !== $expectedSha256) {
            $errors[] = 'runtime file digest mismatch: ' . $relativePath
                . ' expected=' . $expectedSha256
                . ' actual=' . $actualSha256;
        }

        $checks[] = [
            'relative_path' => $relativePath,
            'expected_sha256' => $expectedSha256,
            'actual_sha256' => $actualSha256,
            'exists' => $exists,
            'ok' => $ok,
        ];
    }

    return $checks;
}

$parsed = app_cli_mtool_proxy_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_mtool_proxy_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_mtool_proxy_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$projectKey = APP_CLI_MTOOL_PROXY_PROJECT_KEY;
$expectedReferenceResult = app_cli_mtool_proxy_load_expected_reference($parsed['reference_path']);
if (!$expectedReferenceResult['ok'] || $expectedReferenceResult['item'] === null) {
    app_cli_mtool_proxy_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'requested_by' => $parsed['requested_by'],
            'expected_reference_path' => $parsed['reference_path'],
            'outputs' => [],
            'assertion_errors' => [],
            'error' => $expectedReferenceResult['error'],
        ],
        false,
    );
    exit(1);
}

$expectedReference = $expectedReferenceResult['item'];
$assertionErrors = [];
$results = [];

app_cli_mtool_proxy_assert_same(
    $projectKey,
    $expectedReference['project_key'],
    'reference project_key',
    $assertionErrors,
);

$expectedOutputKeys = array_keys($expectedReference['outputs']);
sort($expectedOutputKeys);
$requiredOutputKeys = APP_CLI_MTOOL_PROXY_SOURCE_OUTPUT_KEYS;
sort($requiredOutputKeys);
app_cli_mtool_proxy_assert_same(
    $requiredOutputKeys,
    $expectedOutputKeys,
    'reference source_output_keys',
    $assertionErrors,
);

foreach (APP_CLI_MTOOL_PROXY_SOURCE_OUTPUT_KEYS as $sourceOutputKey) {
    $expectedOutput = $expectedReference['outputs'][$sourceOutputKey] ?? null;
    if (!is_array($expectedOutput)) {
        $results[$sourceOutputKey] = [
            'source_output_key' => $sourceOutputKey,
            'error' => 'expected reference から source output baseline を読み出せませんでした。',
        ];
        $assertionErrors[] = 'missing expected output reference: ' . $sourceOutputKey;
        continue;
    }

    $sourceOutputResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
    if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
        app_cli_mtool_proxy_write_json(
            [
                'ok' => false,
                'project_key' => $projectKey,
                'requested_by' => $parsed['requested_by'],
                'expected_reference_path' => $parsed['reference_path'],
                'outputs' => $results,
                'assertion_errors' => $assertionErrors,
                'error' => $sourceOutputResult['error'] !== ''
                    ? $sourceOutputResult['error']
                    : 'source output definition が見つかりません: ' . $sourceOutputKey,
            ],
            false,
        );
        exit(1);
    }

    $planResult = app_custom_proxy_build_plan_for_source_output($app, $projectKey, $sourceOutputKey);
    if (!$planResult['ok'] || $planResult['plan'] === null) {
        app_cli_mtool_proxy_write_json(
            [
                'ok' => false,
                'project_key' => $projectKey,
                'requested_by' => $parsed['requested_by'],
                'expected_reference_path' => $parsed['reference_path'],
                'outputs' => $results,
                'assertion_errors' => $assertionErrors,
                'error' => $planResult['error'] !== ''
                    ? $planResult['error']
                    : 'build plan を取得できませんでした: ' . $sourceOutputKey,
            ],
            false,
        );
        exit(1);
    }

    $buildPlanSummary = app_cli_mtool_proxy_build_plan_summary_projection($planResult['plan']);
    foreach ($expectedOutput['build_plan_summary'] as $key => $expectedValue) {
        $actualValue = $buildPlanSummary[$key] ?? null;
        app_cli_mtool_proxy_assert_same(
            $expectedValue,
            $actualValue,
            $sourceOutputKey . ' build_plan ' . $key,
            $assertionErrors,
        );
    }

    $artifactResult = app_project_output_create_from_definition(
        $app,
        $projectKey,
        $sourceOutputResult['item'],
        $parsed['requested_by'],
    );
    if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
        app_cli_mtool_proxy_write_json(
            [
                'ok' => false,
                'project_key' => $projectKey,
                'requested_by' => $parsed['requested_by'],
                'expected_reference_path' => $parsed['reference_path'],
                'outputs' => $results,
                'assertion_errors' => $assertionErrors,
                'error' => $artifactResult['error'] !== ''
                    ? $artifactResult['error']
                    : 'artifact 生成に失敗しました: ' . $sourceOutputKey,
            ],
            false,
        );
        exit(1);
    }

    $artifact = $artifactResult['artifact'];
    $artifactSummary = app_cli_mtool_proxy_artifact_summary_projection($artifact);
    foreach ($expectedOutput['artifact_summary'] as $key => $expectedValue) {
        $actualValue = $artifactSummary[$key] ?? null;
        app_cli_mtool_proxy_assert_same(
            $expectedValue,
            $actualValue,
            $sourceOutputKey . ' artifact ' . $key,
            $assertionErrors,
        );
    }

    $runtimeRoot = $artifact['bundle_root'] . '/' . $artifact['runtime_source_relative_path'];
    $runtimeFileChecks = app_cli_mtool_proxy_runtime_file_checks(
        $runtimeRoot,
        $expectedOutput['runtime_files'],
        $assertionErrors,
    );

    $results[$sourceOutputKey] = [
        'source_output_key' => $sourceOutputKey,
        'source_output_definition' => [
            'name' => (string) ($sourceOutputResult['item']['name'] ?? ''),
            'artifact_strategy' => (string) ($sourceOutputResult['item']['artifact_strategy'] ?? ''),
            'runtime_source_relative_path' => (string) ($sourceOutputResult['item']['runtime_source_relative_path'] ?? ''),
        ],
        'build_plan_summary' => $buildPlanSummary,
        'expected_build_plan_summary' => $expectedOutput['build_plan_summary'],
        'artifact' => [
            'artifact_key' => $artifact['artifact_key'],
            'manifest_path' => $artifact['manifest_path'],
            'bundle_manifest_path' => $artifact['bundle_manifest_path'],
            'runtime_root' => $runtimeRoot,
            'artifact_summary' => $artifactSummary,
            'expected_artifact_summary' => $expectedOutput['artifact_summary'],
            'runtime_file_checks' => $runtimeFileChecks,
        ],
        'error' => '',
    ];
}

$ok = $assertionErrors === [];
app_cli_mtool_proxy_write_json(
    [
        'ok' => $ok,
        'project_key' => $projectKey,
        'requested_by' => $parsed['requested_by'],
        'expected_reference_path' => $parsed['reference_path'],
        'outputs' => $results,
        'assertion_errors' => $assertionErrors,
        'error' => $ok ? '' : 'MTOOL proxy output check failed.',
    ],
    $ok,
);

exit($ok ? 0 : 1);
