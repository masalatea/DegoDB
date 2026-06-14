#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/language_resource_file_catalog.php';
require_once dirname(__DIR__) . '/app/legacy_language_resource_reference.php';

function app_cli_export_language_resource_file_tree_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_language_resource_file_tree.php \
    --project-key=MTOOL \
    [--output-root=mtool/resources] \
    [--overlay-seed=/path/to/legacy-overlay-seed.sql] \
    [--clean]
  php mtool/scripts/export_language_resource_file_tree.php \
    --all \
    [--clean]

Options:
  --project-key=KEY    file tree を作る project key
  --output-root=PATH   output root。省略時は MTOOL は mtool/resources、sample は sample/<category>/<pack>/resources
  --overlay-seed=PATH  source_output_key 解決に使う optional overlay seed
  --all                legacy reference がある全 project の file tree を再生成する
  --clean              output root を削除してから再生成
  --help               このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     output_root:string,
 *     overlay_seed_path:string,
 *     export_all:bool,
 *     clean:bool,
 *     error:string
 * }
 */
function app_cli_export_language_resource_file_tree_parse_args(array $argv): array
{
    $projectKey = '';
    $outputRoot = '';
    $overlaySeedPath = '';
    $exportAll = false;
    $clean = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'output_root' => '',
                'overlay_seed_path' => '',
                'export_all' => false,
                'clean' => false,
                'error' => '',
            ];
        }

        if ($argument === '--all') {
            $exportAll = true;
            continue;
        }

        if ($argument === '--clean') {
            $clean = true;
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--output-root=')) {
            $outputRoot = trim(substr($argument, strlen('--output-root=')));
            continue;
        }

        if (str_starts_with($argument, '--overlay-seed=')) {
            $overlaySeedPath = trim(substr($argument, strlen('--overlay-seed=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_root' => '',
            'overlay_seed_path' => '',
            'export_all' => false,
            'clean' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($exportAll && $projectKey !== '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_root' => '',
            'overlay_seed_path' => '',
            'export_all' => false,
            'clean' => false,
            'error' => '--all と --project-key は同時に指定できません。',
        ];
    }

    if ($exportAll) {
        if ($outputRoot !== '' || $overlaySeedPath !== '') {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'output_root' => '',
                'overlay_seed_path' => '',
                'export_all' => false,
                'clean' => false,
                'error' => '--all 指定時は --output-root / --overlay-seed を使えません。',
            ];
        }

        return [
            'ok' => true,
            'help' => false,
            'project_key' => '',
            'output_root' => '',
            'overlay_seed_path' => '',
            'export_all' => true,
            'clean' => $clean,
            'error' => '',
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_root' => '',
            'overlay_seed_path' => '',
            'export_all' => false,
            'clean' => false,
            'error' => '有効な --project-key=... または --all を指定してください。',
        ];
    }

    if ($outputRoot === '') {
        $outputRoot = app_language_resource_file_catalog_default_root($projectKey);
    }
    if ($overlaySeedPath === '') {
        $overlaySeedPath = app_language_resource_file_catalog_default_overlay_seed_path($projectKey);
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'output_root' => $outputRoot,
        'overlay_seed_path' => $overlaySeedPath,
        'export_all' => false,
        'clean' => $clean,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     output_root:string,
 *     files_written:int,
 *     summary:array<string,mixed>,
 *     normalization:array<string,mixed>,
 *     warnings:list<string>,
 *     resolved_source_output_key_count:int,
 *     error:string
 * }
 */
function app_cli_export_language_resource_file_tree_run_project(
    string $projectKey,
    string $outputRoot,
    string $overlaySeedPath,
    bool $clean,
): array {
    $referenceResult = app_load_legacy_language_resource_reference($projectKey);
    if (!$referenceResult['ok'] || !is_array($referenceResult['item'])) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'output_root' => $outputRoot,
            'files_written' => 0,
            'summary' => [],
            'normalization' => [],
            'warnings' => [],
            'resolved_source_output_key_count' => 0,
            'error' => $referenceResult['error'] !== ''
                ? $referenceResult['error']
                : 'legacy language resource reference を読み込めません。',
        ];
    }

    $sourceOutputMap = app_language_resource_file_catalog_source_output_map_for_project(
        $projectKey,
        $overlaySeedPath,
    );
    $tree = app_language_resource_file_catalog_build_from_reference($referenceResult['item'], $sourceOutputMap);
    $validation = app_language_resource_file_catalog_validate_tree($tree);
    if (!$validation['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'output_root' => $outputRoot,
            'files_written' => 0,
            'summary' => [],
            'normalization' => [],
            'warnings' => $validation['warnings'],
            'resolved_source_output_key_count' => count($sourceOutputMap),
            'error' => 'export 前 validation に失敗しました。',
        ];
    }

    $writeResult = app_language_resource_file_catalog_write_tree(
        $outputRoot,
        $tree,
        $clean,
    );
    if (!$writeResult['ok']) {
        return [
            'ok' => false,
            'project_key' => $projectKey,
            'output_root' => $outputRoot,
            'files_written' => 0,
            'summary' => [],
            'normalization' => [],
            'warnings' => [],
            'resolved_source_output_key_count' => count($sourceOutputMap),
            'error' => $writeResult['error'],
        ];
    }

    return [
        'ok' => true,
        'project_key' => $projectKey,
        'output_root' => $writeResult['root_path'],
        'files_written' => $writeResult['files_written'],
        'summary' => is_array($tree['manifest']['counts'] ?? null)
            ? $tree['manifest']['counts']
            : $validation['summary'],
        'normalization' => is_array($tree['manifest']['normalization'] ?? null)
            ? $tree['manifest']['normalization']
            : [],
        'warnings' => $validation['warnings'],
        'resolved_source_output_key_count' => count($sourceOutputMap),
        'error' => '',
    ];
}

/**
 * @param array{
 *     ok:bool,
 *     project_key:string,
 *     output_root:string,
 *     files_written:int,
 *     summary:array<string,mixed>,
 *     normalization:array<string,mixed>,
 *     warnings:list<string>,
 *     resolved_source_output_key_count:int,
 *     error:string
 * } $result
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     output_root:string,
 *     files_written:int,
 *     summary:array<string,mixed>,
 *     normalization:array<string,mixed>,
 *     warnings:list<string>,
 *     resolved_source_output_key_count:int,
 *     error:string
 * }
 */
function app_cli_export_language_resource_file_tree_compact_result(array $result): array
{
    $compacted = $result;
    $normalization = is_array($compacted['normalization'] ?? null) ? $compacted['normalization'] : [];
    if (is_array($normalization['dropped_rows'] ?? null)) {
        unset($normalization['dropped_rows']);
    }
    $compacted['normalization'] = $normalization;

    return $compacted;
}

$parsed = app_cli_export_language_resource_file_tree_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_export_language_resource_file_tree_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_export_language_resource_file_tree_usage() . PHP_EOL);
    exit(64);
}

if ($parsed['export_all']) {
    $projectResults = [];
    $okCount = 0;
    foreach (app_legacy_language_resource_reference_project_keys() as $projectKey) {
        $result = app_cli_export_language_resource_file_tree_run_project(
            $projectKey,
            app_language_resource_file_catalog_default_root($projectKey),
            app_language_resource_file_catalog_default_overlay_seed_path($projectKey),
            $parsed['clean'],
        );
        $projectResults[] = app_cli_export_language_resource_file_tree_compact_result($result);
        if ($result['ok']) {
            $okCount++;
        }
    }

    $report = [
        'ok' => count($projectResults) === $okCount,
        'mode' => 'all',
        'project_count' => count($projectResults),
        'ok_count' => $okCount,
        'error_project_count' => count($projectResults) - $okCount,
        'projects' => $projectResults,
    ];
    fwrite(
        $report['ok'] ? STDOUT : STDERR,
        json_encode(
            $report,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit($report['ok'] ? 0 : 1);
}

$result = app_cli_export_language_resource_file_tree_run_project(
    $parsed['project_key'],
    $parsed['output_root'],
    $parsed['overlay_seed_path'],
    $parsed['clean'],
);
fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
exit($result['ok'] ? 0 : 1);
