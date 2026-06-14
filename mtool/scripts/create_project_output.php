#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_project_output_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/create_project_output.php --project-key=MTOOL [--source-output-key=RUNTIME-DBCLASSES] [--requested-by=codex] [--publish]

Options:
  --project-key=KEY          Source output を作る project key
  --source-output-key=KEY    DB 上の canonical definition を指定して作る
  --requested-by=NAME        manifest に残す実行者名
  --publish                  生成直後の artifact を source_output_dir へ昇格する
  --help                     このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     requested_by:string,
 *     publish:bool,
 *     help:bool,
 *     error:string
 * }
 */
function app_cli_project_output_parse_args(array $argv): array
{
    $projectKey = '';
    $sourceOutputKey = '';
    $requestedBy = 'cli';
    $publish = false;
    $sourceOutputKeyProvided = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'project_key' => '',
                'source_output_key' => '',
                'requested_by' => $requestedBy,
                'publish' => $publish,
                'help' => true,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--source-output-key=')) {
            $sourceOutputKeyProvided = true;
            $sourceOutputKey = app_normalize_source_output_key(substr($argument, strlen('--source-output-key=')));
            continue;
        }

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = app_project_output_normalize_requested_by(substr($argument, strlen('--requested-by=')));
            continue;
        }

        if ($argument === '--publish') {
            $publish = true;
            continue;
        }

        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'requested_by' => $requestedBy,
            'publish' => $publish,
            'help' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'requested_by' => $requestedBy,
            'publish' => $publish,
            'help' => false,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if ($sourceOutputKeyProvided && ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey))) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'requested_by' => $requestedBy,
            'publish' => $publish,
            'help' => false,
            'error' => '有効な --source-output-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'requested_by' => $requestedBy,
        'publish' => $publish,
        'help' => false,
        'error' => '',
    ];
}

$parsed = app_cli_project_output_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_project_output_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_project_output_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$sourceOutputItem = null;
if ($parsed['source_output_key'] !== '') {
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

    $sourceOutputItem = $sourceOutputResult['item'];
    $result = app_project_output_create_from_definition(
        $app,
        $parsed['project_key'],
        $sourceOutputItem,
        $parsed['requested_by'],
    );
} else {
    $sourceOutputItem = app_project_output_local_default_source_output($parsed['project_key']);
    $result = app_project_output_create($app, $parsed['project_key'], $parsed['requested_by']);
}

if (!$result['ok'] || $result['artifact'] === null) {
    fwrite(STDERR, $result['error'] . PHP_EOL);
    exit(1);
}

$artifact = $result['artifact'];
$published = null;
if ($parsed['publish']) {
    $publishResult = app_project_output_publish_artifact($app, $artifact, $sourceOutputItem);
    if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
        fwrite(STDERR, $publishResult['error'] . PHP_EOL);
        exit(1);
    }

    $published = $publishResult['published'];
}

fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $artifact['project_key'],
            'source_output_key' => $artifact['source_output_key'],
            'source_output_name' => $artifact['source_output_name'],
            'source_output_program_language' => $artifact['source_output_program_language'],
            'source_output_class_type' => $artifact['source_output_class_type'],
            'source_output_release_target_type' => $artifact['source_output_release_target_type'],
            'artifact_key' => $artifact['artifact_key'],
            'created_at' => $artifact['created_at'],
            'requested_by' => $artifact['requested_by'],
            'archive_format' => $artifact['archive_format'],
            'archive_filename' => $artifact['archive_filename'],
            'archive_path' => $artifact['archive_path'],
            'archive_size' => $artifact['archive_size'],
            'source_file_count' => $artifact['source_file_count'],
            'source_total_bytes' => $artifact['source_total_bytes'],
            'customization_model' => $artifact['customization_model'],
            'custom_layer_relative_path' => $artifact['custom_layer_relative_path'],
            'custom_layer_source' => $artifact['custom_layer_source'],
            'custom_layer_file_count' => $artifact['custom_layer_file_count'],
            'custom_layer_total_bytes' => $artifact['custom_layer_total_bytes'],
            'manifest_path' => $artifact['manifest_path'],
            'bundle_manifest_path' => $artifact['bundle_manifest_path'],
            'published' => $published,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
