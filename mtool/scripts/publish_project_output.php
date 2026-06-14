#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_publish_project_output_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/publish_project_output.php --project-key=MTOOL --source-output-key=RUNTIME-DBCLASSES [--artifact-key=20260512-123456-abcdef12]

Options:
  --project-key=KEY          publish 対象の project key
  --source-output-key=KEY    publish 対象の source output key
  --artifact-key=KEY         publish する artifact key。省略時は対象 output の最新 artifact
  --help                     このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     artifact_key:string,
 *     help:bool,
 *     error:string
 * }
 */
function app_cli_publish_project_output_parse_args(array $argv): array
{
    $projectKey = '';
    $sourceOutputKey = '';
    $artifactKey = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'project_key' => '',
                'source_output_key' => '',
                'artifact_key' => '',
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

        if (str_starts_with($argument, '--artifact-key=')) {
            $artifactKey = trim(substr($argument, strlen('--artifact-key=')));
            continue;
        }

        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'help' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'help' => false,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'help' => false,
            'error' => '有効な --source-output-key=... を指定してください。',
        ];
    }

    if ($artifactKey !== '' && !app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'help' => false,
            'error' => '有効な --artifact-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'artifact_key' => $artifactKey,
        'help' => false,
        'error' => '',
    ];
}

$parsed = app_cli_publish_project_output_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_publish_project_output_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_publish_project_output_usage() . PHP_EOL);
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

if (!is_array($sourceOutputResult['item'])) {
    fwrite(
        STDERR,
        'source output definition が見つかりません: '
        . $parsed['project_key'] . '/' . $parsed['source_output_key'] . PHP_EOL,
    );
    exit(1);
}

$artifact = null;
if ($parsed['artifact_key'] !== '') {
    $artifactResult = app_project_output_find($app, $parsed['project_key'], $parsed['artifact_key']);
    if (!$artifactResult['ok']) {
        fwrite(STDERR, $artifactResult['error'] . PHP_EOL);
        exit(1);
    }

    if (!is_array($artifactResult['item'])) {
        fwrite(STDERR, '指定した artifact が見つかりません。' . PHP_EOL);
        exit(1);
    }

    if ($artifactResult['item']['source_output_key'] !== $parsed['source_output_key']) {
        fwrite(STDERR, '指定した artifact は要求 source output key と一致しません。' . PHP_EOL);
        exit(1);
    }

    $artifact = $artifactResult['item'];
} else {
    $artifactListResult = app_project_output_list($app, $parsed['project_key'], $parsed['source_output_key']);
    if (!$artifactListResult['ok']) {
        fwrite(STDERR, $artifactListResult['error'] . PHP_EOL);
        exit(1);
    }

    if ($artifactListResult['items'] === []) {
        fwrite(STDERR, 'publish 対象の artifact が見つかりません。先に create_project_output.php を実行してください。' . PHP_EOL);
        exit(1);
    }

    $artifact = $artifactListResult['items'][0];
}

$publishResult = app_project_output_publish_artifact($app, $artifact, $sourceOutputResult['item']);
if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
    fwrite(STDERR, $publishResult['error'] . PHP_EOL);
    exit(1);
}

$published = $publishResult['published'];
fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $published['project_key'],
            'source_output_key' => $published['source_output_key'],
            'artifact_key' => $published['artifact_key'],
            'source_output_dir' => $published['source_output_dir'],
            'published_root' => $published['published_root'],
            'published_file_count' => $published['published_file_count'],
            'published_total_bytes' => $published['published_total_bytes'],
            'published_at' => $published['published_at'],
            'manifest_path' => $artifact['manifest_path'],
            'archive_path' => $artifact['archive_path'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
