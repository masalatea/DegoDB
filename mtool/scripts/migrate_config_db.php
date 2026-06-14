#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/config_db_bootstrap.php';

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     sql_dir:string,
 *     requested_by:string,
 *     error:string
 * }
 */
function app_cli_migrate_config_db_parse_args(array $argv): array
{
    $sqlDir = '';
    $requestedBy = 'cli';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'sql_dir' => '',
                'requested_by' => $requestedBy,
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--sql-dir=')) {
            $sqlDir = trim(substr($argument, strlen('--sql-dir=')));
            continue;
        }
        if (str_starts_with($argument, '--requested-by=')) {
            $requestedBy = trim(substr($argument, strlen('--requested-by=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'sql_dir' => '',
            'requested_by' => $requestedBy,
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'sql_dir' => $sqlDir,
        'requested_by' => $requestedBy,
        'error' => '',
    ];
}

function app_cli_migrate_config_db_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/migrate_config_db.php

Options:
  --sql-dir=PATH        current config-initdb directory。default: docker/mariadb/config-initdb
  --requested-by=NAME   summary に残す caller 名。default: cli
  --help                このヘルプを表示
TEXT;
}

$parsed = app_cli_migrate_config_db_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_migrate_config_db_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_migrate_config_db_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$result = app_config_db_bootstrap_apply($app, [
    'sql_dir' => $parsed['sql_dir'],
]);
$exitCode = $result['ok'] ? 0 : 1;

fwrite(
    $exitCode === 0 ? STDOUT : STDERR,
    json_encode(
        [
            'ok' => $result['ok'],
            'requested_by' => $parsed['requested_by'],
            'target' => $result['target'],
            'summary' => $result['summary'],
            'applied_files' => $result['applied_files'],
            'missing_tables' => $result['missing_tables'],
            'missing_columns' => $result['missing_columns'],
            'unexpected_legacy_columns' => $result['unexpected_legacy_columns'],
            'warnings' => $result['warnings'],
            'error' => $result['error'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($exitCode);
