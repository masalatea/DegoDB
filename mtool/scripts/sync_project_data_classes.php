#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_data_class_sync_service.php';

function app_cli_project_data_class_sync_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/sync_project_data_classes.php --project-key=MTOOL

Options:
  --project-key=KEY    data class sync を行う project key
  --help               このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     error:string
 * }
 */
function app_cli_project_data_class_sync_parse_args(array $argv): array
{
    $projectKey = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'error' => '',
    ];
}

$parsed = app_cli_project_data_class_sync_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_project_data_class_sync_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_project_data_class_sync_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$result = app_project_data_class_sync_apply($app, $parsed['project_key']);
$exitCode = $result['ok'] ? 0 : 1;

fwrite(
    $exitCode === 0 ? STDOUT : STDERR,
    json_encode(
        [
            'ok' => $result['ok'],
            'summary' => $result['summary'],
            'classes' => $result['classes'],
            'errors' => $result['errors'],
            'error' => $result['error'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($exitCode);
