#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_table_import_service.php';

function app_cli_project_table_import_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/import_project_tables.php --project-key=MTOOL --source=live-schema

Options:
  --project-key=KEY    table import を行う project key
  --source=KEY         import source key (`live-schema`, `lab-live-schema`, `legacy-reference`, `legacy-reference-test-module`, `legacy-reference-build-run-state`)
  --table=NAME         特定 table の差分だけを import する
  --help               このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     source_key:string,
 *     table_name:string,
 *     error:string
 * }
 */
function app_cli_project_table_import_parse_args(array $argv): array
{
    $projectKey = '';
    $sourceKey = 'live-schema';
    $tableName = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'source_key' => 'live-schema',
                'table_name' => '',
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--source=')) {
            $sourceKey = trim(substr($argument, strlen('--source=')));
            continue;
        }
        if (str_starts_with($argument, '--table=')) {
            $tableName = trim(substr($argument, strlen('--table=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'source_key' => 'live-schema',
            'table_name' => '',
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'source_key' => 'live-schema',
            'table_name' => '',
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'source_key' => $sourceKey,
        'table_name' => $tableName,
        'error' => '',
    ];
}

$parsed = app_cli_project_table_import_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_project_table_import_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_project_table_import_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$result = app_project_table_import_apply(
    $app,
    $parsed['project_key'],
    $parsed['source_key'],
    $parsed['table_name'],
);
$exitCode = $result['ok'] ? 0 : 1;

fwrite(
    $exitCode === 0 ? STDOUT : STDERR,
    json_encode(
        [
            'ok' => $result['ok'],
            'summary' => $result['summary'],
            'tables' => $result['tables'],
            'errors' => $result['errors'],
            'error' => $result['error'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($exitCode);
