#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/runtime_reference_promotion.php';

function app_cli_restore_runtime_reference_snapshot_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/restore_runtime_reference_snapshot.php --artifact-key=20260520-022959-3e593819 [--project-key=MTOOL] [--source-output-key=RUNTIME-DBCLASSES] [--requested-by=cli]

Options:
  --artifact-key=KEY         restore する durable snapshot の artifact key
  --project-key=KEY          対象 project key (default: MTOOL)
  --source-output-key=KEY    対象 source output key (default: RUNTIME-DBCLASSES)
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
function app_cli_restore_runtime_reference_snapshot_parse_args(array $argv): array
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

    if ($artifactKey === '' || !app_project_output_artifact_key_is_valid($artifactKey)) {
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

$parsed = app_cli_restore_runtime_reference_snapshot_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_restore_runtime_reference_snapshot_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_restore_runtime_reference_snapshot_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$restoreResult = app_runtime_reference_restore_snapshot(
    $parsed['project_key'],
    $parsed['source_output_key'],
    $parsed['artifact_key'],
    app_runtime_storage_runtime_dbclasses_root($app),
    $parsed['requested_by'],
    app_runtime_storage_runtime_reference_snapshots_root(
        $app,
        $parsed['project_key'],
        $parsed['source_output_key'],
        $parsed['artifact_key'],
    ),
);
if (!$restoreResult['ok'] || !is_array($restoreResult['restored'])) {
    fwrite(STDERR, $restoreResult['error'] . PHP_EOL);
    exit(1);
}

$restored = $restoreResult['restored'];
fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $restored['project_key'],
            'source_output_key' => $restored['source_output_key'],
            'artifact_key' => $restored['artifact_key'],
            'requested_by' => $restored['requested_by'],
            'runtime_source_relative_path' => $restored['runtime_source_relative_path'],
            'source_root' => $restored['source_root'],
            'snapshot_root' => $restored['snapshot_root'],
            'snapshot_manifest_path' => $restored['snapshot_manifest_path'],
            'target_root' => $restored['target_root'],
            'restored_file_count' => $restored['file_count'],
            'restored_total_bytes' => $restored['total_bytes'],
            'restored_at' => $restored['restored_at'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit(0);
