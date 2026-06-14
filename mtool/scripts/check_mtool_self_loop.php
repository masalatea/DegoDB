#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_table_import_service.php';
require_once dirname(__DIR__) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__) . '/app/project_db_access_sync_service.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

const APP_CLI_MTOOL_SELF_LOOP_PROJECT_KEY = 'MTOOL';
const APP_CLI_MTOOL_SELF_LOOP_SOURCE_OUTPUT_KEY = 'RUNTIME-DBCLASSES';
const APP_CLI_MTOOL_SELF_LOOP_IMPORT_SOURCES = [
    'live-schema',
    'legacy-reference-test-module',
    'legacy-reference-build-run-state',
];

function app_cli_mtool_self_loop_default_reference_path(): string
{
    return dirname(__DIR__) . '/reference/mtool-self-loop-expected-output.json';
}

function app_cli_mtool_self_loop_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_mtool_self_loop.php [--requested-by=NAME] [--reference=PATH]

Options:
  --requested-by=NAME  artifact manifest に残す実行者名 (default: self-loop-check)
  --reference=PATH     expected output baseline JSON (default: mtool/reference/mtool-self-loop-expected-output.json)
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
function app_cli_mtool_self_loop_parse_args(array $argv): array
{
    $requestedBy = 'self-loop-check';
    $referencePath = app_cli_mtool_self_loop_default_reference_path();

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
function app_cli_mtool_self_loop_write_json(array $payload, bool $ok): void
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
 * @return array{
 *     mode:string|null,
 *     generated_dbaccess_count:int|null,
 *     fallback_dbaccess_count:int|null,
 *     canonical_function_count:int|null,
 *     sql_regenerated_dbaccess_count:int|null,
 *     sql_regenerated_function_count:int|null,
 *     canonical_helper_function_count:int|null,
 *     canonical_data_class_count:int|null,
 *     data_entity_count:int|null,
 *     plain_data_candidate_count:int|null,
 *     non_plain_data_candidate_count:int|null,
 *     bootstrap_data_class_count:int|null,
 *     legacy_delegate_function_count:int|null,
 *     warnings:list<string>
 * }
 */
function app_cli_mtool_self_loop_generation_summary_projection(array $summary): array
{
    $warnings = $summary['warnings'] ?? [];
    if (!is_array($warnings)) {
        $warnings = [];
    }

    return [
        'mode' => isset($summary['mode']) && is_string($summary['mode']) ? $summary['mode'] : null,
        'generated_dbaccess_count' => isset($summary['generated_dbaccess_count']) ? (int) $summary['generated_dbaccess_count'] : null,
        'fallback_dbaccess_count' => isset($summary['fallback_dbaccess_count']) ? (int) $summary['fallback_dbaccess_count'] : null,
        'canonical_function_count' => isset($summary['canonical_function_count']) ? (int) $summary['canonical_function_count'] : null,
        'sql_regenerated_dbaccess_count' => isset($summary['sql_regenerated_dbaccess_count']) ? (int) $summary['sql_regenerated_dbaccess_count'] : null,
        'sql_regenerated_function_count' => isset($summary['sql_regenerated_function_count']) ? (int) $summary['sql_regenerated_function_count'] : null,
        'canonical_helper_function_count' => isset($summary['canonical_helper_function_count']) ? (int) $summary['canonical_helper_function_count'] : null,
        'canonical_data_class_count' => isset($summary['canonical_data_class_count']) ? (int) $summary['canonical_data_class_count'] : null,
        'data_entity_count' => isset($summary['data_entity_count']) ? (int) $summary['data_entity_count'] : null,
        'plain_data_candidate_count' => isset($summary['plain_data_candidate_count']) ? (int) $summary['plain_data_candidate_count'] : null,
        'non_plain_data_candidate_count' => isset($summary['non_plain_data_candidate_count']) ? (int) $summary['non_plain_data_candidate_count'] : null,
        'bootstrap_data_class_count' => isset($summary['bootstrap_data_class_count']) ? (int) $summary['bootstrap_data_class_count'] : null,
        'legacy_delegate_function_count' => isset($summary['legacy_delegate_function_count']) ? (int) $summary['legacy_delegate_function_count'] : null,
        'warnings' => array_values(
            array_map(
                static fn ($warning): string => is_string($warning) ? $warning : (string) $warning,
                $warnings,
            ),
        ),
    ];
}

/**
 * @return array{
 *     total_candidate_entities:int|null,
 *     dbaccess_candidate_count:int|null,
 *     method_candidate_count:int|null
 * }
 */
function app_cli_mtool_self_loop_db_access_sync_summary_projection(array $summary): array
{
    return [
        'total_candidate_entities' => isset($summary['total_candidate_entities']) ? (int) $summary['total_candidate_entities'] : null,
        'dbaccess_candidate_count' => isset($summary['dbaccess_candidate_count']) ? (int) $summary['dbaccess_candidate_count'] : null,
        'method_candidate_count' => isset($summary['method_candidate_count']) ? (int) $summary['method_candidate_count'] : null,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         source_output_key:string,
 *         generation_summary:array<string,mixed>,
 *         db_access_sync_summary:array{
 *             total_candidate_entities:int|null,
 *             dbaccess_candidate_count:int|null,
 *             method_candidate_count:int|null
 *         }|null,
 *         runtime_files:list<array{
 *             relative_path:string,
 *             sha256:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_cli_mtool_self_loop_load_expected_reference(string $path): array
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

    $generationSummary = $decoded['generation_summary'] ?? null;
    $dbAccessSyncSummary = $decoded['db_access_sync_summary'] ?? null;
    $runtimeFiles = $decoded['runtime_files'] ?? null;
    if (!is_array($generationSummary) || !is_array($runtimeFiles)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'expected output reference に generation_summary / runtime_files が必要です。',
        ];
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

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'source_output_key' => (string) ($decoded['source_output_key'] ?? ''),
            'generation_summary' => $generationSummary,
            'db_access_sync_summary' => is_array($dbAccessSyncSummary)
                ? app_cli_mtool_self_loop_db_access_sync_summary_projection($dbAccessSyncSummary)
                : null,
            'runtime_files' => $normalizedRuntimeFiles,
        ],
        'error' => '',
    ];
}

/**
 * @param list<string> $errors
 */
function app_cli_mtool_self_loop_assert_same(mixed $expected, mixed $actual, string $label, array &$errors): void
{
    if ($expected === $actual) {
        return;
    }

    $errors[] = $label
        . ': expected=' . json_encode($expected, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' actual=' . json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * @param array{
 *     summary:array{
 *         table_insert_count:int,
 *         table_changed_count:int,
 *         table_delete_count:int,
 *         column_insert_count:int,
 *         column_update_count:int,
 *         column_delete_count:int
 *     }
 * } $preview
 * @param list<string> $errors
 */
function app_cli_mtool_self_loop_assert_live_import_steady(array $preview, array &$errors): void
{
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['table_insert_count'], 'live-schema table_insert_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['table_changed_count'], 'live-schema table_changed_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['table_delete_count'], 'live-schema table_delete_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['column_insert_count'], 'live-schema column_insert_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['column_update_count'], 'live-schema column_update_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['column_delete_count'], 'live-schema column_delete_count', $errors);
}

/**
 * @param array{
 *     summary:array{
 *         class_insert_count:int,
 *         class_update_count:int,
 *         field_insert_count:int,
 *         field_update_count:int,
 *         stale_class_count:int,
 *         stale_field_count:int
 *     }
 * } $preview
 * @param list<string> $errors
 */
function app_cli_mtool_self_loop_assert_data_class_steady(array $preview, array &$errors): void
{
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['class_insert_count'], 'data-class class_insert_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['class_update_count'], 'data-class class_update_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['field_insert_count'], 'data-class field_insert_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['field_update_count'], 'data-class field_update_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['stale_class_count'], 'data-class stale_class_count', $errors);
    app_cli_mtool_self_loop_assert_same(0, $preview['summary']['stale_field_count'], 'data-class stale_field_count', $errors);
}

/**
 * @param array{
 *     summary:array{
 *         total_candidate_entities:int,
 *         dbaccess_candidate_count:int,
 *         method_candidate_count:int
 *     }
 * } $summary
 * @param array{
 *     generation_summary:array<string,mixed>
 *     db_access_sync_summary:array{
 *         total_candidate_entities:int|null,
 *         dbaccess_candidate_count:int|null,
 *         method_candidate_count:int|null
 *     }|null
 * } $expectedReference
 * @param list<string> $errors
 */
function app_cli_mtool_self_loop_assert_db_access_sync_shape(
    array $summary,
    array $expectedReference,
    array &$errors,
): void
{
    $expectedSyncSummary = [
        'total_candidate_entities' => (int) ($expectedReference['generation_summary']['generated_dbaccess_count'] ?? 0),
        'dbaccess_candidate_count' => (int) ($expectedReference['generation_summary']['generated_dbaccess_count'] ?? 0),
        'method_candidate_count' => (int) ($expectedReference['generation_summary']['canonical_function_count'] ?? 0),
    ];

    if (is_array($expectedReference['db_access_sync_summary'] ?? null)) {
        foreach ($expectedSyncSummary as $key => $_value) {
            if (isset($expectedReference['db_access_sync_summary'][$key])) {
                $expectedSyncSummary[$key] = (int) $expectedReference['db_access_sync_summary'][$key];
            }
        }
    }

    app_cli_mtool_self_loop_assert_same($expectedSyncSummary['total_candidate_entities'], $summary['summary']['total_candidate_entities'], 'db-access total_candidate_entities', $errors);
    app_cli_mtool_self_loop_assert_same($expectedSyncSummary['dbaccess_candidate_count'], $summary['summary']['dbaccess_candidate_count'], 'db-access dbaccess_candidate_count', $errors);
    app_cli_mtool_self_loop_assert_same($expectedSyncSummary['method_candidate_count'], $summary['summary']['method_candidate_count'], 'db-access method_candidate_count', $errors);
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
function app_cli_mtool_self_loop_runtime_file_checks(
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

$parsed = app_cli_mtool_self_loop_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_mtool_self_loop_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_mtool_self_loop_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$projectKey = APP_CLI_MTOOL_SELF_LOOP_PROJECT_KEY;
$sourceOutputKey = APP_CLI_MTOOL_SELF_LOOP_SOURCE_OUTPUT_KEY;
$expectedReferenceResult = app_cli_mtool_self_loop_load_expected_reference($parsed['reference_path']);
if (!$expectedReferenceResult['ok'] || $expectedReferenceResult['item'] === null) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'expected_reference_path' => $parsed['reference_path'],
            'steps' => [],
            'assertion_errors' => [],
            'error' => $expectedReferenceResult['error'],
        ],
        false,
    );
    exit(1);
}
$expectedReference = $expectedReferenceResult['item'];
$steps = [
    'table_imports' => [],
    'live_preview_after_import' => null,
    'data_class_sync' => null,
    'data_class_preview_after_sync' => null,
    'db_access_sync' => null,
    'artifact' => null,
];
$assertionErrors = [];
$runtimeManifestPath = '';
$runtimeSummaryProjection = null;
$runtimeRoot = '';
$runtimeFileChecks = [];

app_cli_mtool_self_loop_assert_same(
    $projectKey,
    $expectedReference['project_key'],
    'reference project_key',
    $assertionErrors,
);
app_cli_mtool_self_loop_assert_same(
    $sourceOutputKey,
    $expectedReference['source_output_key'],
    'reference source_output_key',
    $assertionErrors,
);

foreach (APP_CLI_MTOOL_SELF_LOOP_IMPORT_SOURCES as $sourceKey) {
    $result = app_project_table_import_apply($app, $projectKey, $sourceKey);
    $steps['table_imports'][] = [
        'source_key' => $sourceKey,
        'ok' => $result['ok'],
        'summary' => $result['summary'],
        'errors' => $result['errors'],
        'error' => $result['error'],
    ];

    if (!$result['ok']) {
        app_cli_mtool_self_loop_write_json(
            [
                'ok' => false,
                'project_key' => $projectKey,
                'source_output_key' => $sourceOutputKey,
                'requested_by' => $parsed['requested_by'],
                'steps' => $steps,
                'assertion_errors' => [],
                'error' => 'table import に失敗しました: ' . $sourceKey,
            ],
            false,
        );
        exit(1);
    }
}

$livePreview = app_project_table_import_preview($app, $projectKey, 'live-schema');
$steps['live_preview_after_import'] = [
    'ok' => $livePreview['ok'],
    'summary' => $livePreview['summary'],
    'errors' => $livePreview['errors'],
    'error' => $livePreview['error'],
];
if (!$livePreview['ok']) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => [],
            'error' => 'live-schema preview の確認に失敗しました。',
        ],
        false,
    );
    exit(1);
}
app_cli_mtool_self_loop_assert_live_import_steady($livePreview, $assertionErrors);

$dataClassSync = app_project_data_class_sync_apply($app, $projectKey);
$steps['data_class_sync'] = [
    'ok' => $dataClassSync['ok'],
    'summary' => $dataClassSync['summary'],
    'errors' => $dataClassSync['errors'],
    'error' => $dataClassSync['error'],
];
if (!$dataClassSync['ok']) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'data-class sync に失敗しました。',
        ],
        false,
    );
    exit(1);
}

$dataClassPreview = app_project_data_class_sync_preview($app, $projectKey);
$steps['data_class_preview_after_sync'] = [
    'ok' => $dataClassPreview['ok'],
    'summary' => $dataClassPreview['summary'],
    'errors' => $dataClassPreview['errors'],
    'error' => $dataClassPreview['error'],
];
if (!$dataClassPreview['ok']) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'data-class preview の確認に失敗しました。',
        ],
        false,
    );
    exit(1);
}
app_cli_mtool_self_loop_assert_data_class_steady($dataClassPreview, $assertionErrors);

$dbAccessSync = app_project_db_access_sync_from_generated_catalog($app, $projectKey);
$steps['db_access_sync'] = [
    'ok' => $dbAccessSync['ok'],
    'summary' => $dbAccessSync['summary'],
    'errors' => $dbAccessSync['errors'],
    'error' => $dbAccessSync['error'],
];
if (!$dbAccessSync['ok']) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'db-access sync に失敗しました。',
        ],
        false,
    );
    exit(1);
}
app_cli_mtool_self_loop_assert_db_access_sync_shape($dbAccessSync, $expectedReference, $assertionErrors);

$sourceOutputResult = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
if (!$sourceOutputResult['ok'] || $sourceOutputResult['item'] === null) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => $sourceOutputResult['error'] !== ''
                ? $sourceOutputResult['error']
                : 'source output definition が見つかりません。',
        ],
        false,
    );
    exit(1);
}

$artifactResult = app_project_output_create_from_definition(
    $app,
    $projectKey,
    $sourceOutputResult['item'],
    $parsed['requested_by'],
);
if (!$artifactResult['ok'] || $artifactResult['artifact'] === null) {
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => $artifactResult['error'] !== ''
                ? $artifactResult['error']
                : 'source output 生成に失敗しました。',
        ],
        false,
    );
    exit(1);
}

$artifact = $artifactResult['artifact'];
$runtimeRoot = $artifact['bundle_root'] . '/' . $artifact['runtime_source_relative_path'];
$runtimeManifestPath = $runtimeRoot . '/_support/runtime-generation-manifest.json';
$runtimeManifest = app_project_output_read_manifest($runtimeManifestPath);
if (!is_array($runtimeManifest) || !isset($runtimeManifest['generation_summary']) || !is_array($runtimeManifest['generation_summary'])) {
    $steps['artifact'] = [
        'artifact_key' => $artifact['artifact_key'],
        'manifest_path' => $artifact['manifest_path'],
        'bundle_manifest_path' => $artifact['bundle_manifest_path'],
        'runtime_manifest_path' => $runtimeManifestPath,
    ];
    app_cli_mtool_self_loop_write_json(
        [
            'ok' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'requested_by' => $parsed['requested_by'],
            'steps' => $steps,
            'assertion_errors' => $assertionErrors,
            'error' => 'runtime-generation-manifest.json の読み込みに失敗しました。',
        ],
        false,
    );
    exit(1);
}

$runtimeSummaryProjection = app_cli_mtool_self_loop_generation_summary_projection($runtimeManifest['generation_summary']);
$runtimeManifestArtifactKey = (string) ($runtimeManifest['artifact_key'] ?? '');
app_cli_mtool_self_loop_assert_same(
    $artifact['artifact_key'],
    $runtimeManifestArtifactKey,
    'runtime artifact_key',
    $assertionErrors,
);
$runtimeFileChecks = app_cli_mtool_self_loop_runtime_file_checks(
    $runtimeRoot,
    $expectedReference['runtime_files'],
    $assertionErrors,
);
$steps['artifact'] = [
    'artifact_key' => $artifact['artifact_key'],
    'manifest_path' => $artifact['manifest_path'],
    'bundle_manifest_path' => $artifact['bundle_manifest_path'],
    'runtime_manifest_path' => $runtimeManifestPath,
    'runtime_manifest_artifact_key' => $runtimeManifestArtifactKey,
    'generation_summary' => $runtimeSummaryProjection,
    'runtime_file_checks' => $runtimeFileChecks,
];

foreach ($expectedReference['generation_summary'] as $key => $expectedValue) {
    $actualValue = $runtimeSummaryProjection[$key] ?? null;
    app_cli_mtool_self_loop_assert_same($expectedValue, $actualValue, 'runtime ' . $key, $assertionErrors);
}

$ok = $assertionErrors === [];
app_cli_mtool_self_loop_write_json(
    [
        'ok' => $ok,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'requested_by' => $parsed['requested_by'],
        'expected_reference_path' => $parsed['reference_path'],
        'steps' => $steps,
        'expected_generation_summary' => $expectedReference['generation_summary'],
        'expected_db_access_sync_summary' => $expectedReference['db_access_sync_summary'],
        'expected_runtime_files' => $expectedReference['runtime_files'],
        'assertion_errors' => $assertionErrors,
        'error' => $ok ? '' : 'MTOOL self-loop check failed.',
    ],
    $ok,
);

exit($ok ? 0 : 1);
