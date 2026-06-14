#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/domain_validation.php';
require_once __DIR__ . '/lib/project_language_resource_sync_service.php';

function app_cli_project_language_resource_sync_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/debug/language_resource/sync_project_language_resource_from_file_tree.php \
    --project-key=MTOOL \
    [--apply]

Options:
  --project-key=KEY    sync 対象 project key
  --apply              dry-run ではなく migration/debug 用 DB bridge table を更新
  --help               このヘルプを表示

Note:
  この script は current runtime/admin では使わない debug bridge 専用です。
  tableless な通常運用では不要で、canonical DB table が無い場合は preview warning または apply error を返します。
TEXT;
}

/**
 * @return list<string>
 */
function app_cli_project_language_resource_sync_debug_bridge_warnings(bool $apply): array
{
    $warnings = [
        'この script は migration/debug 用の DB bridge 専用です。current runtime/admin の source of truth は file tree です。',
    ];
    if ($apply) {
        $warnings[] = '--apply は deprecated な canonical DB table を意図的に残している検証環境でだけ使ってください。';
    }

    return $warnings;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     apply:bool,
 *     error:string
 * }
 */
function app_cli_project_language_resource_sync_parse_args(array $argv): array
{
    $projectKey = '';
    $apply = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'apply' => false,
                'error' => '',
            ];
        }

        if ($argument === '--apply') {
            $apply = true;
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'apply' => false,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'apply' => false,
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'apply' => $apply,
        'error' => '',
    ];
}

$parsed = app_cli_project_language_resource_sync_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_project_language_resource_sync_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_project_language_resource_sync_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$result = app_project_language_resource_sync_from_file_tree(
    $app,
    $parsed['project_key'],
    $parsed['apply'],
);
$warnings = array_values(array_unique(array_merge(
    app_cli_project_language_resource_sync_debug_bridge_warnings($parsed['apply']),
    is_array($result['warnings'] ?? null) ? $result['warnings'] : [],
)));
$exitCode = $result['ok'] ? 0 : 1;

fwrite(
    $exitCode === 0 ? STDOUT : STDERR,
    json_encode(
        [
            'ok' => $result['ok'],
            'mode' => 'debug-db-bridge',
            'summary' => $result['summary'],
            'warnings' => $warnings,
            'errors' => $result['errors'],
            'error' => $result['error'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($exitCode);
