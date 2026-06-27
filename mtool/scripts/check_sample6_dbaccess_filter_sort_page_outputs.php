#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample6_dbaccess_filter_sort_page_output_check.php';

function app_cli_sample6_default_reference_root(): string
{
    return app_sample6_dbaccess_filter_sort_page_default_reference_root();
}

function app_cli_sample6_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample6_dbaccess_filter_sort_page_outputs.php [--requested-by=NAME] [--reference-root=PATH]

Options:
  --requested-by=NAME   artifact manifest に残す実行者名 (default: sample6-output-check)
  --reference-root=PATH expected output reference root (default: sample/tutorials/sample06-dbaccess-filter-sort-page/reference)
  --help                このヘルプを表示
TEXT;
}

function app_cli_sample6_parse_args(array $argv): array
{
    $requestedBy = 'sample6-output-check';
    $referenceRoot = app_cli_sample6_default_reference_root();

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'requested_by' => $requestedBy,
                'reference_root' => $referenceRoot,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = app_project_output_normalize_requested_by(
                substr($argument, strlen('--requested-by=')),
            );
            continue;
        }

        if (str_starts_with($argument, '--reference-root=')) {
            $referenceRoot = trim(substr($argument, strlen('--reference-root=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'requested_by' => $requestedBy,
            'reference_root' => $referenceRoot,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'requested_by' => $requestedBy,
        'reference_root' => $referenceRoot,
        'error' => '',
    ];
}

function app_cli_sample6_write_json(array $payload, bool $ok): void
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

$parsed = app_cli_sample6_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_sample6_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_sample6_usage() . PHP_EOL);
    exit(64);
}

$previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

try {
    $app = app_bootstrap();
    $result = app_sample6_dbaccess_filter_sort_page_run(
        $app,
        $parsed['requested_by'],
        $parsed['reference_root'],
    );
} finally {
    if ($previousPolicy === false) {
        putenv('MTOOL_GENERATED_NAME_POLICY');
    } else {
        putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
    }
}

app_cli_sample6_write_json($result, $result['ok']);

exit($result['ok'] ? 0 : 1);
