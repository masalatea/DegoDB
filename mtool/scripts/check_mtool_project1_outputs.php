#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_mtool_project1_outputs_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_mtool_project1_outputs.php [--project-key=MTOOL] [--requested-by=NAME] [--publish]

Options:
  --project-key=KEY      Source output catalog を確認する project key (default: MTOOL)
  --requested-by=NAME    artifact manifest に残す実行者名 (default: project-output-check)
  --publish              生成直後の artifact を source_output_dir へ昇格する
  --help                 このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     requested_by:string,
 *     publish:bool,
 *     error:string
 * }
 */
function app_cli_mtool_project1_outputs_parse_args(array $argv): array
{
    $projectKey = 'MTOOL';
    $requestedBy = 'project-output-check';
    $publish = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => $projectKey,
                'requested_by' => $requestedBy,
                'publish' => $publish,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = app_project_output_normalize_requested_by(
                substr($argument, strlen('--requested-by=')),
            );
            continue;
        }

        if ($argument === '--publish') {
            $publish = true;
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'requested_by' => $requestedBy,
            'publish' => $publish,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'requested_by' => $requestedBy,
            'publish' => $publish,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'requested_by' => $requestedBy,
        'publish' => $publish,
        'error' => '',
    ];
}

/**
 * @param array<mixed> $payload
 */
function app_cli_mtool_project1_outputs_write_json(array $payload, bool $ok): void
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

$parsed = app_cli_mtool_project1_outputs_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_mtool_project1_outputs_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_mtool_project1_outputs_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$catalogResult = app_fetch_project_source_output_catalog($app, $parsed['project_key']);
if (!$catalogResult['ok']) {
    app_cli_mtool_project1_outputs_write_json([
        'project_key' => $parsed['project_key'],
        'error' => $catalogResult['error'],
    ], false);
    exit(1);
}

$items = $catalogResult['items'];
if ($items === []) {
    app_cli_mtool_project1_outputs_write_json([
        'project_key' => $parsed['project_key'],
        'error' => 'source output catalog が空です。',
    ], false);
    exit(1);
}

$results = [];
$successCount = 0;
$failureCount = 0;

foreach ($items as $sourceOutput) {
    $createResult = app_project_output_create_from_definition(
        $app,
        $parsed['project_key'],
        $sourceOutput,
        $parsed['requested_by'],
    );

    if ($createResult['ok'] && is_array($createResult['artifact'])) {
        $artifact = $createResult['artifact'];
        $published = null;
        if ($parsed['publish']) {
            $publishResult = app_project_output_publish_artifact(
                $app,
                $artifact,
                $sourceOutput,
            );
            if (!$publishResult['ok'] || !is_array($publishResult['published'])) {
                $results[] = [
                    'source_output_key' => $sourceOutput['source_output_key'],
                    'class_type' => $sourceOutput['class_type'],
                    'artifact_strategy' => $sourceOutput['artifact_strategy'],
                    'ok' => false,
                    'artifact_key' => $artifact['artifact_key'],
                    'manifest_path' => $artifact['manifest_path'],
                    'error' => $publishResult['error'],
                ];
                $failureCount++;
                continue;
            }

            $published = $publishResult['published'];
        }

        $results[] = [
            'source_output_key' => $sourceOutput['source_output_key'],
            'class_type' => $sourceOutput['class_type'],
            'artifact_strategy' => $sourceOutput['artifact_strategy'],
            'ok' => true,
            'artifact_key' => $artifact['artifact_key'],
            'source_file_count' => $artifact['source_file_count'],
            'source_total_bytes' => $artifact['source_total_bytes'],
            'archive_path' => $artifact['archive_path'],
            'manifest_path' => $artifact['manifest_path'],
            'published' => $published,
        ];
        $successCount++;
        continue;
    }

    $results[] = [
        'source_output_key' => $sourceOutput['source_output_key'],
        'class_type' => $sourceOutput['class_type'],
        'artifact_strategy' => $sourceOutput['artifact_strategy'],
        'ok' => false,
        'error' => $createResult['error'],
    ];
    $failureCount++;
}

$payload = [
    'project_key' => $parsed['project_key'],
    'checked_at' => date(DATE_ATOM),
    'publish' => $parsed['publish'],
    'definition_count' => count($items),
    'success_count' => $successCount,
    'failure_count' => $failureCount,
    'results' => $results,
];

app_cli_mtool_project1_outputs_write_json($payload, $failureCount === 0);
exit($failureCount === 0 ? 0 : 1);
