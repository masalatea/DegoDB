#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/custom_proxy_build_plan_service.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/single_proxy_build_plan_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_source_output_build_plan_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/show_source_output_build_plan.php --project-key=MTOOL --source-output-key=DBIMPORT-PROXY-SERVER

Options:
  --project-key=KEY          build plan を確認する project key
  --source-output-key=KEY    対象 source output key
  --help                     このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     help:bool,
 *     error:string
 * }
 */
function app_cli_source_output_build_plan_parse_args(array $argv): array
{
    $projectKey = '';
    $sourceOutputKey = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'project_key' => '',
                'source_output_key' => '',
                'help' => true,
                'error' => '',
            ];
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
            'project_key' => '',
            'source_output_key' => '',
            'help' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'help' => false,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'help' => false,
            'error' => '有効な --source-output-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'help' => false,
        'error' => '',
    ];
}

$parsed = app_cli_source_output_build_plan_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_source_output_build_plan_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_source_output_build_plan_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$sourceOutputResult = app_fetch_project_source_output_item(
    $app,
    $parsed['project_key'],
    $parsed['source_output_key'],
);
if (!$sourceOutputResult['ok']) {
    fwrite(STDERR, $sourceOutputResult['error'] . PHP_EOL);
    exit(1);
}

if ($sourceOutputResult['item'] === null) {
    fwrite(
        STDERR,
        'source output definition が見つかりません: '
        . $parsed['project_key'] . '/' . $parsed['source_output_key'] . PHP_EOL,
    );
    exit(1);
}

$bindingScope = app_source_output_target_binding_scope($sourceOutputResult['item']);
$payload = [
    'project_key' => $parsed['project_key'],
    'source_output' => $sourceOutputResult['item'],
    'binding_scope' => $bindingScope,
];

if ($bindingScope === 'custom-proxy') {
    $planResult = app_custom_proxy_build_plan_for_source_output(
        $app,
        $parsed['project_key'],
        $parsed['source_output_key'],
    );
    if (!$planResult['ok'] || $planResult['plan'] === null) {
        fwrite(STDERR, $planResult['error'] . PHP_EOL);
        exit(1);
    }

    $payload['plan_type'] = 'custom-proxy';
    $payload['custom_proxy_build_plan'] = $planResult['plan'];
} elseif ($bindingScope === 'single-function-proxy') {
    $planResult = app_single_proxy_build_plan_for_source_output(
        $app,
        $parsed['project_key'],
        $parsed['source_output_key'],
    );
    if (!$planResult['ok'] || $planResult['plan'] === null) {
        fwrite(STDERR, $planResult['error'] . PHP_EOL);
        exit(1);
    }

    $payload['plan_type'] = 'single-function-proxy';
    $payload['single_proxy_build_plan'] = $planResult['plan'];
} else {
    $payload['plan_type'] = 'none';
    $payload['message'] = 'この source output は proxy target binding scope を持ちません。';
}

fwrite(
    STDOUT,
    json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
