#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/runtime_reference_promotion.php';

function app_cli_promote_runtime_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/promote_runtime_reference.php [--project-key=MTOOL] [--source-output-key=RUNTIME-DBCLASSES] [--artifact-key=20260519-010720-0d539855] [--requested-by=cli]

Options:
  --project-key=KEY          promote 対象 project key (default: MTOOL)
  --source-output-key=KEY    promote 対象 source output key (default: RUNTIME-DBCLASSES)
  --artifact-key=KEY         promote する artifact key。省略時は対象 output の最新 artifact
  --requested-by=NAME        実行者名。出力 JSON に記録する (default: cli)
  --help                     このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     artifact_key:string,
 *     requested_by:string,
 *     error:string
 * }
 */
function app_cli_promote_runtime_reference_parse_args(array $argv): array
{
    $projectKey = app_runtime_reference_promotion_project_key();
    $sourceOutputKey = app_runtime_reference_promotion_source_output_key();
    $artifactKey = '';
    $requestedBy = 'cli';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => $projectKey,
                'source_output_key' => $sourceOutputKey,
                'artifact_key' => $artifactKey,
                'requested_by' => $requestedBy,
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

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = app_project_output_normalize_requested_by(substr($argument, strlen('--requested-by=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'requested_by' => $requestedBy,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'requested_by' => $requestedBy,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'requested_by' => $requestedBy,
            'error' => '有効な --source-output-key=... を指定してください。',
        ];
    }

    if ($artifactKey !== '' && !app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'source_output_key' => '',
            'artifact_key' => '',
            'requested_by' => $requestedBy,
            'error' => '有効な --artifact-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'artifact_key' => $artifactKey,
        'requested_by' => $requestedBy,
        'error' => '',
    ];
}

$parsed = app_cli_promote_runtime_reference_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_promote_runtime_reference_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_promote_runtime_reference_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
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

    $artifact = $artifactResult['item'];
} else {
    $artifactListResult = app_project_output_list($app, $parsed['project_key'], $parsed['source_output_key']);
    if (!$artifactListResult['ok']) {
        fwrite(STDERR, $artifactListResult['error'] . PHP_EOL);
        exit(1);
    }

    if ($artifactListResult['items'] === []) {
        fwrite(STDERR, 'promote 対象の artifact が見つかりません。先に create_project_output.php を実行してください。' . PHP_EOL);
        exit(1);
    }

    $artifact = $artifactListResult['items'][0];
}

if (!is_array($artifact)) {
    fwrite(STDERR, 'artifact の解決に失敗しました。' . PHP_EOL);
    exit(1);
}

if ($artifact['source_output_key'] !== $parsed['source_output_key']) {
    fwrite(STDERR, '指定した artifact は要求 source output key と一致しません。' . PHP_EOL);
    exit(1);
}

$promotionResult = app_runtime_reference_promote_artifact(
    $artifact,
    app_runtime_storage_runtime_dbclasses_root($app),
    $parsed['requested_by'],
    app_runtime_storage_runtime_reference_snapshots_root(
        $app,
        $parsed['project_key'],
        $parsed['source_output_key'],
        (string) $artifact['artifact_key'],
    ),
);
if (!$promotionResult['ok'] || !is_array($promotionResult['promoted'])) {
    fwrite(STDERR, $promotionResult['error'] . PHP_EOL);
    exit(1);
}

$promoted = $promotionResult['promoted'];
fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $promoted['project_key'],
            'source_output_key' => $promoted['source_output_key'],
            'artifact_key' => $promoted['artifact_key'],
            'requested_by' => $parsed['requested_by'],
            'runtime_source_relative_path' => $promoted['runtime_source_relative_path'],
            'source_root' => $promoted['source_root'],
            'target_root' => $promoted['target_root'],
            'snapshot_root' => $promoted['snapshot_root'],
            'snapshot_manifest_path' => $promoted['snapshot_manifest_path'],
            'promoted_file_count' => $promoted['file_count'],
            'promoted_total_bytes' => $promoted['total_bytes'],
            'promoted_at' => $promoted['promoted_at'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
