#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/language_resource_file_catalog.php';

function app_cli_validate_language_resource_file_tree_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/validate_language_resource_file_tree.php \
    --project-key=MTOOL \
    [--root=mtool/resources]
  php mtool/scripts/validate_language_resource_file_tree.php \
    --all

Options:
  --project-key=KEY    validate する project key
  --root=PATH          validate 対象 root。省略時は project key ごとの既定 root
  --all                既知 project 全件を validate する
  --help               このヘルプを表示
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     root_path:string,
 *     validate_all:bool,
 *     error:string
 * }
 */
function app_cli_validate_language_resource_file_tree_parse_args(array $argv): array
{
    $projectKey = '';
    $rootPath = '';
    $validateAll = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'root_path' => '',
                'validate_all' => false,
                'error' => '',
            ];
        }

        if ($argument === '--all') {
            $validateAll = true;
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--root=')) {
            $rootPath = trim(substr($argument, strlen('--root=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'root_path' => '',
            'validate_all' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($validateAll && $projectKey !== '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'root_path' => '',
            'validate_all' => false,
            'error' => '--all と --project-key は同時に指定できません。',
        ];
    }

    if ($validateAll) {
        if ($rootPath !== '') {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'root_path' => '',
                'validate_all' => false,
                'error' => '--all 指定時は --root を使えません。',
            ];
        }

        return [
            'ok' => true,
            'help' => false,
            'project_key' => '',
            'root_path' => '',
            'validate_all' => true,
            'error' => '',
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'root_path' => '',
            'validate_all' => false,
            'error' => '有効な --project-key=... または --all を指定してください。',
        ];
    }

    if ($rootPath === '') {
        $rootPath = app_language_resource_file_catalog_default_root($projectKey);
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'root_path' => $rootPath,
        'validate_all' => false,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     root_path:string,
 *     manifest_counts:array<string,mixed>,
 *     actual_counts:array<string,mixed>,
 *     normalization:array<string,mixed>,
 *     errors:list<string>,
 *     warnings:list<string>
 * }
 */
function app_cli_validate_language_resource_file_tree_report(
    string $projectKey,
    string $rootPath,
    bool $includeDroppedRows = true,
): array
{
    $loaded = app_language_resource_file_catalog_load_catalog($rootPath);
    $manifestCounts = is_array($loaded['manifest']['counts'] ?? null) ? $loaded['manifest']['counts'] : [];
    $countErrors = [];
    $normalization = is_array($loaded['manifest']['normalization'] ?? null)
        ? $loaded['manifest']['normalization']
        : [];
    if (!$includeDroppedRows && is_array($normalization['dropped_rows'] ?? null)) {
        unset($normalization['dropped_rows']);
    }

    $manifestProjectKey = trim((string) ($loaded['manifest']['project_key'] ?? ''));
    if ($manifestProjectKey !== '' && $manifestProjectKey !== $projectKey) {
        $countErrors[] = 'manifest.project_key と指定した project key が一致しません。';
    }

    return [
        'ok' => $loaded['ok'] && $countErrors === [],
        'project_key' => $projectKey,
        'root_path' => $rootPath,
        'manifest_counts' => $manifestCounts,
        'actual_counts' => $loaded['actual_counts'],
        'normalization' => $normalization,
        'errors' => array_values(array_unique(array_merge($loaded['errors'], $countErrors))),
        'warnings' => $loaded['warnings'],
    ];
}

$parsed = app_cli_validate_language_resource_file_tree_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_validate_language_resource_file_tree_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_validate_language_resource_file_tree_usage() . PHP_EOL);
    exit(64);
}

if ($parsed['validate_all']) {
    $knownProjects = app_language_resource_file_catalog_known_projects();
    $projectReports = [];
    $okCount = 0;
    $warningProjectCount = 0;
    foreach ($knownProjects as $project) {
        $projectKey = (string) ($project['project_key'] ?? '');
        $rootPath = (string) ($project['root_path'] ?? '');
        if ($projectKey === '' || $rootPath === '') {
            continue;
        }

        $report = app_cli_validate_language_resource_file_tree_report($projectKey, $rootPath, false);
        $projectReports[] = $report;
        if ($report['ok']) {
            $okCount++;
        }
        if ((is_array($report['warnings'] ?? null) ? $report['warnings'] : []) !== []) {
            $warningProjectCount++;
        }
    }

    $allOk = count($projectReports) === $okCount;
    $report = [
        'ok' => $allOk,
        'mode' => 'all',
        'summary' => [
            'project_count' => count($projectReports),
            'ok_count' => $okCount,
            'warning_project_count' => $warningProjectCount,
            'error_project_count' => count($projectReports) - $okCount,
        ],
        'errors' => [],
        'projects' => $projectReports,
    ];

    $stream = $report['ok'] ? STDOUT : STDERR;
    fwrite(
        $stream,
        json_encode(
            $report,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );

    exit($report['ok'] ? 0 : 1);
}

$report = app_cli_validate_language_resource_file_tree_report(
    $parsed['project_key'],
    $parsed['root_path'],
);
$report['mode'] = 'single';

$stream = $report['ok'] ? STDOUT : STDERR;
fwrite(
    $stream,
    json_encode(
        $report,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($report['ok'] ? 0 : 1);
