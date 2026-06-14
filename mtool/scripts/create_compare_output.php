#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/compare_output_job_service.php';
require_once dirname(__DIR__) . '/app/compare_output_repository.php';
require_once dirname(__DIR__) . '/app/compare_output_service.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';

function app_cli_compare_output_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/create_compare_output.php --project-key=MTOOL --compare-output-key=MAIN [--requested-by=codex]

Options:
  --project-key=KEY           Compare Output を作る project key
  --compare-output-key=KEY    DB 上の canonical compare output definition を指定する
  --requested-by=NAME         出力作成者名
  --help                      このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     compare_output_key:string,
 *     requested_by:string,
 *     help:bool,
 *     error:string
 * }
 */
function app_cli_compare_output_parse_args(array $argv): array
{
    $projectKey = '';
    $compareOutputKey = '';
    $requestedBy = 'cli';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'project_key' => '',
                'compare_output_key' => '',
                'requested_by' => $requestedBy,
                'help' => true,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--compare-output-key=')) {
            $compareOutputKey = app_normalize_compare_output_key(substr($argument, strlen('--compare-output-key=')));
            continue;
        }

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = trim(substr($argument, strlen('--requested-by=')));
            continue;
        }

        return [
            'ok' => false,
            'project_key' => '',
            'compare_output_key' => '',
            'requested_by' => $requestedBy,
            'help' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'compare_output_key' => '',
            'requested_by' => $requestedBy,
            'help' => false,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if ($compareOutputKey === '' || !app_compare_output_key_is_valid($compareOutputKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'compare_output_key' => '',
            'requested_by' => $requestedBy,
            'help' => false,
            'error' => '有効な --compare-output-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'project_key' => $projectKey,
        'compare_output_key' => $compareOutputKey,
        'requested_by' => $requestedBy,
        'help' => false,
        'error' => '',
    ];
}

$parsed = app_cli_compare_output_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_compare_output_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_compare_output_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$compareOutputResult = app_fetch_project_compare_output_item(
    $app,
    $parsed['project_key'],
    $parsed['compare_output_key'],
);
if (!$compareOutputResult['ok']) {
    fwrite(STDERR, $compareOutputResult['error'] . PHP_EOL);
    exit(1);
}

if ($compareOutputResult['item'] === null) {
    fwrite(
        STDERR,
        'compare output definition が見つかりません: '
        . $parsed['project_key'] . '/' . $parsed['compare_output_key'] . PHP_EOL,
    );
    exit(1);
}

$additionalPathResult = app_fetch_project_compare_output_additional_path_catalog(
    $app,
    $parsed['project_key'],
    $parsed['compare_output_key'],
);
if (!$additionalPathResult['ok']) {
    fwrite(STDERR, $additionalPathResult['error'] . PHP_EOL);
    exit(1);
}

$result = app_compare_output_job_create(
    $app,
    $parsed['project_key'],
    $compareOutputResult['item'],
    $additionalPathResult['items'],
    $parsed['requested_by'],
);

if (!$result['ok'] || $result['output'] === null || $result['job'] === null) {
    fwrite(STDERR, $result['error'] . PHP_EOL);
    exit(1);
}

$output = $result['output'];
$job = $result['job'];
fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $output['project_key'],
            'compare_output_key' => $output['compare_output_key'],
            'compare_output_name' => $output['compare_output_name'],
            'output_file_type' => $output['output_file_type'],
            'requested_by' => $output['requested_by'],
            'created_at' => $output['created_at'],
            'resolved_storage_base_path' => $output['resolved_storage_base_path'],
            'compare_root_absolute_path' => $output['compare_root_absolute_path'],
            'output_file_absolute_path' => $output['output_file_absolute_path'],
            'deviation_pair_count' => $output['deviation_pair_count'],
            'checked_pair_count' => $output['checked_pair_count'],
            'output_bytes' => $output['output_bytes'],
            'job_key' => $job['job_key'],
            'job_manifest_path' => $job['manifest_path'],
            'job_snapshot_path' => $job['output_snapshot_path'],
            'job_route' => app_lab_compare_output_job_path($job['job_key']),
            'job_api_route' => app_lab_compare_output_job_api_path($job['job_key']),
            'warning_count' => count($output['warnings']),
            'warnings' => $output['warnings'],
            'pairs' => $output['pairs'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
