#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/project_output_html_module_catalog.php';
require_once dirname(__DIR__) . '/app/project_output_legacy_source_generator.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_show_html_module_catalog_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/show_html_module_catalog.php [--project-key=MTOOL]

Options:
  --project-key=KEY    html source output catalog を確認する project key (default: MTOOL)
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
function app_cli_show_html_module_catalog_parse_args(array $argv): array
{
    $projectKey = 'MTOOL';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => $projectKey,
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
            'project_key' => $projectKey,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
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

$parsed = app_cli_show_html_module_catalog_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_show_html_module_catalog_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_show_html_module_catalog_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$catalogResult = app_fetch_project_source_output_catalog($app, $parsed['project_key']);
if (!$catalogResult['ok']) {
    fwrite(STDERR, $catalogResult['error'] . PHP_EOL);
    exit(1);
}

$items = [];
$resolvedKindCounts = [];
foreach ($catalogResult['items'] as $sourceOutput) {
    if (($sourceOutput['class_type'] ?? '') !== 'html') {
        continue;
    }

    $resolvedSource = app_project_output_legacy_source_resolve_root((string) ($sourceOutput['source_template_dir'] ?? ''));
    $resolvedKind = $resolvedSource['ok']
        ? ($resolvedSource['source_kind'] !== '' ? $resolvedSource['source_kind'] : 'unknown')
        : 'resolve-error';
    if (!array_key_exists($resolvedKind, $resolvedKindCounts)) {
        $resolvedKindCounts[$resolvedKind] = 0;
    }
    $resolvedKindCounts[$resolvedKind]++;

    $items[] = [
        'source_output_key' => $sourceOutput['source_output_key'],
        'source_template_dir' => $sourceOutput['source_template_dir'],
        'resolved' => [
            'ok' => $resolvedSource['ok'],
            'source_kind' => $resolvedSource['source_kind'],
            'source_kind_caption' => $resolvedSource['source_kind'] === 'direct-path'
                ? 'Direct Path'
                : app_project_output_html_module_source_kind_caption($resolvedSource['source_kind']),
            'source_root_relative_path' => $resolvedSource['source_root_relative_path'],
            'error' => $resolvedSource['error'],
        ],
    ];
}

fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $parsed['project_key'],
            'html_source_output_count' => count($items),
            'resolved_kind_counts' => $resolvedKindCounts,
            'items' => $items,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
