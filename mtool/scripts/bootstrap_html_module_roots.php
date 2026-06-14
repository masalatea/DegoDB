#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/project_output_html_module_catalog.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';

function app_cli_bootstrap_html_module_roots_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/bootstrap_html_module_roots.php [--project-key=MTOOL] [--source-output-key=HTML-DB] [--force]

Options:
  --project-key=KEY        fallback source の project key (default: MTOOL)
  --source-output-key=KEY  1 module だけ bootstrap する
  --force                  既存 current root を作り直す
  --help                   このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     force:bool,
 *     error:string
 * }
 */
function app_cli_bootstrap_html_module_roots_parse_args(array $argv): array
{
    $projectKey = 'MTOOL';
    $sourceOutputKey = '';
    $force = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => $projectKey,
                'source_output_key' => $sourceOutputKey,
                'force' => $force,
                'error' => '',
            ];
        }

        if ($argument === '--force') {
            $force = true;
            continue;
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
            'help' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'force' => $force,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'force' => $force,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    if ($sourceOutputKey !== '' && !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'force' => $force,
            'error' => '有効な --source-output-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'force' => $force,
        'error' => '',
    ];
}

/**
 * @return list<string>
 */
function app_cli_bootstrap_html_module_roots_collect_legacy_keys(string $relativeRoot): array
{
    $repoRoot = app_project_output_html_module_catalog_repo_root();
    $root = $repoRoot . '/' . ltrim($relativeRoot, '/');
    if (!is_dir($root)) {
        return [];
    }

    $entries = scandir($root);
    if ($entries === false) {
        throw new RuntimeException('legacy root の読み込みに失敗しました: ' . $root);
    }

    $keys = [];
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (!app_source_output_key_is_valid($entry)) {
            continue;
        }

        if (!is_dir($root . '/' . $entry)) {
            continue;
        }

        $keys[] = $entry;
    }

    sort($keys);

    return $keys;
}

/**
 * @return list<string>
 */
function app_cli_bootstrap_html_module_roots_discover_fallback_keys(string $projectKey): array
{
    $projectSlug = strtolower(app_normalize_project_key($projectKey));
    $keys = array_values(array_unique(array_merge(
        app_cli_bootstrap_html_module_roots_collect_legacy_keys(
            app_runtime_storage_mtool_reference_relative_path(
                'legacy-source-snapshots/' . $projectSlug . '/html'
            )
        ),
        app_cli_bootstrap_html_module_roots_collect_legacy_keys(
            app_runtime_storage_mtool_reference_relative_path(
                'legacy-source-placeholders/' . $projectSlug . '/html'
            )
        ),
    )));
    sort($keys);

    if ($keys === []) {
        throw new RuntimeException('fallback source root が見つかりません: ' . $projectSlug);
    }

    return $keys;
}

/**
 * @return array{
 *     ok:bool,
 *     source_root:string,
 *     source_relative_path:string,
 *     source_kind:string,
 *     error:string
 * }
 */
function app_cli_bootstrap_html_module_roots_resolve_bootstrap_source(string $projectKey, string $sourceOutputKey): array
{
    $repoRoot = app_project_output_html_module_catalog_repo_root();
    foreach (app_project_output_html_module_source_candidates($projectKey, $sourceOutputKey) as $candidate) {
        if ($candidate['source_kind'] === 'canonical-html-module') {
            continue;
        }

        $candidateRoot = $repoRoot . '/' . $candidate['relative_path'];
        $resolved = realpath($candidateRoot);
        if (!is_string($resolved) || $resolved === '') {
            continue;
        }

        $normalizedResolved = str_replace('\\', '/', $resolved);
        if (!is_dir($normalizedResolved)) {
            continue;
        }

        if ($normalizedResolved !== $repoRoot && !str_starts_with($normalizedResolved, $repoRoot . '/')) {
            continue;
        }

        return [
            'ok' => true,
            'source_root' => $normalizedResolved,
            'source_relative_path' => $candidate['relative_path'],
            'source_kind' => $candidate['source_kind'],
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'source_root' => '',
        'source_relative_path' => '',
        'source_kind' => '',
        'error' => 'bootstrap source root が見つかりません。',
    ];
}

$parsed = app_cli_bootstrap_html_module_roots_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_bootstrap_html_module_roots_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_bootstrap_html_module_roots_usage() . PHP_EOL);
    exit(64);
}

try {
    $sourceOutputKeys = $parsed['source_output_key'] !== ''
        ? [$parsed['source_output_key']]
        : app_cli_bootstrap_html_module_roots_discover_fallback_keys($parsed['project_key']);
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}

$projectSlug = strtolower(app_normalize_project_key($parsed['project_key']));
$repoRoot = app_project_output_html_module_catalog_repo_root();
$items = [];
$createdCount = 0;
$skippedCount = 0;
$failureCount = 0;

foreach ($sourceOutputKeys as $sourceOutputKey) {
    $canonicalRelativePath = app_runtime_storage_mtool_reference_relative_path(
        'html-modules/'
        . $projectSlug
        . '/'
        . $sourceOutputKey
        . '/current'
    );
    $canonicalRoot = $repoRoot . '/' . $canonicalRelativePath;
    $bootstrapSource = app_cli_bootstrap_html_module_roots_resolve_bootstrap_source($parsed['project_key'], $sourceOutputKey);

    if (!$bootstrapSource['ok']) {
        $items[] = [
            'source_output_key' => $sourceOutputKey,
            'ok' => false,
            'status' => 'missing-source',
            'bootstrap_source_kind' => '',
            'source_relative_path' => '',
            'canonical_relative_path' => $canonicalRelativePath,
            'error' => $bootstrapSource['error'],
        ];
        $failureCount++;
        continue;
    }

    $bootstrapScan = app_project_output_scan_tree($bootstrapSource['source_root']);
    if (!$bootstrapScan['ok']) {
        $items[] = [
            'source_output_key' => $sourceOutputKey,
            'ok' => false,
            'status' => 'scan-failed',
            'bootstrap_source_kind' => $bootstrapSource['source_kind'],
            'source_relative_path' => $bootstrapSource['source_relative_path'],
            'canonical_relative_path' => $canonicalRelativePath,
            'error' => $bootstrapScan['error'],
        ];
        $failureCount++;
        continue;
    }

    if (is_dir($canonicalRoot) && !$parsed['force']) {
        $canonicalScan = app_project_output_scan_tree($canonicalRoot);
        $items[] = [
            'source_output_key' => $sourceOutputKey,
            'ok' => true,
            'status' => 'skipped-existing',
            'bootstrap_source_kind' => $bootstrapSource['source_kind'],
            'source_relative_path' => $bootstrapSource['source_relative_path'],
            'canonical_relative_path' => $canonicalRelativePath,
            'file_count' => $canonicalScan['ok'] ? count($canonicalScan['files']) : 0,
            'total_bytes' => $canonicalScan['ok'] ? $canonicalScan['total_bytes'] : 0,
        ];
        $skippedCount++;
        continue;
    }

    try {
        app_project_output_delete_tree($canonicalRoot);
        app_project_output_copy_tree($bootstrapSource['source_root'], $canonicalRoot, $bootstrapScan['files']);

        $canonicalScan = app_project_output_scan_tree($canonicalRoot);
        $items[] = [
            'source_output_key' => $sourceOutputKey,
            'ok' => true,
            'status' => 'bootstrapped',
            'bootstrap_source_kind' => $bootstrapSource['source_kind'],
            'source_relative_path' => $bootstrapSource['source_relative_path'],
            'canonical_relative_path' => $canonicalRelativePath,
            'file_count' => $canonicalScan['ok'] ? count($canonicalScan['files']) : count($bootstrapScan['files']),
            'total_bytes' => $canonicalScan['ok'] ? $canonicalScan['total_bytes'] : $bootstrapScan['total_bytes'],
        ];
        $createdCount++;
    } catch (Throwable $throwable) {
        $items[] = [
            'source_output_key' => $sourceOutputKey,
            'ok' => false,
            'status' => 'copy-failed',
            'bootstrap_source_kind' => $bootstrapSource['source_kind'],
            'source_relative_path' => $bootstrapSource['source_relative_path'],
            'canonical_relative_path' => $canonicalRelativePath,
            'error' => $throwable->getMessage(),
        ];
        $failureCount++;
    }
}

fwrite(
    STDOUT,
    json_encode(
        [
            'project_key' => $parsed['project_key'],
            'force' => $parsed['force'],
            'requested_source_output_key' => $parsed['source_output_key'],
            'module_count' => count($sourceOutputKeys),
            'created_count' => $createdCount,
            'skipped_count' => $skippedCount,
            'failure_count' => $failureCount,
            'items' => $items,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($failureCount === 0 ? 0 : 1);
