#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/project_metadata_bundle.php';

$parsed = app_cli_project_metadata_import_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_project_metadata_import_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_project_metadata_import_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$options = [
    'target_project_key' => $parsed['target_project_key'],
    'database_source_secrets_path' => $parsed['database_source_secrets_path'],
    'requested_by' => $parsed['requested_by'],
];
$result = $parsed['mode'] === 'apply'
    ? app_project_metadata_bundle_import_apply($app, $parsed['bundle_path'], $options)
    : app_project_metadata_bundle_import_preview($app, $parsed['bundle_path'], $options);
$exitCode = $result['ok'] ? 0 : 1;

fwrite(
    $exitCode === 0 ? STDOUT : STDERR,
    json_encode(
        [
            'ok' => $result['ok'],
            'mode' => $parsed['mode'],
            'bundle_root' => $result['bundle_root'],
            'manifest' => $result['manifest'],
            'summary' => $result['summary'],
            'warnings' => $result['warnings'],
            'error' => $result['error'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($exitCode);
