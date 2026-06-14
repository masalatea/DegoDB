#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/runtime_reference_status.php';

function app_cli_runtime_reference_status_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/show_runtime_reference_status.php [--project-key=MTOOL] [--source-output-key=RUNTIME-DBCLASSES] [--require-current]

Options:
  --project-key=KEY          対象 project key (default: MTOOL)
  --source-output-key=KEY    対象 source output key (default: RUNTIME-DBCLASSES)
  --require-current          status が up-to-date 以外なら exit 1 にする
  --help                     この help を表示する
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     require_current:bool,
 *     error:string
 * }
 */
function app_cli_runtime_reference_status_parse_args(array $argv): array
{
    $projectKey = app_runtime_reference_status_default_project_key();
    $sourceOutputKey = app_runtime_reference_status_default_source_output_key();
    $requireCurrent = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => $projectKey,
                'source_output_key' => $sourceOutputKey,
                'require_current' => $requireCurrent,
                'error' => '',
            ];
        }

        if ($argument === '--require-current') {
            $requireCurrent = true;
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--source-output-key=')) {
            $sourceOutputKey = app_normalize_source_output_key(substr($argument, strlen('--source-output-key=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'require_current' => $requireCurrent,
            'error' => 'Unknown option: ' . $argument,
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'require_current' => $requireCurrent,
        'error' => '',
    ];
}

$parsed = app_cli_runtime_reference_status_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_runtime_reference_status_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_runtime_reference_status_usage() . PHP_EOL);
    exit(2);
}

$app = app_bootstrap();
$status = app_runtime_reference_status(
    $app,
    $parsed['project_key'],
    $parsed['source_output_key'],
);
if (!$status['ok']) {
    fwrite(STDERR, $status['error'] . PHP_EOL);
    exit(1);
}

$encoded = json_encode(
    $status,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
);
if (!is_string($encoded) || $encoded === '') {
    fwrite(STDERR, "status encode に失敗しました\n");
    exit(1);
}

fwrite(STDOUT, $encoded . PHP_EOL);
if ($parsed['require_current'] && $status['status'] !== 'up-to-date') {
    exit(1);
}

exit(0);
