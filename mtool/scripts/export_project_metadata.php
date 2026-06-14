#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/project_metadata_bundle.php';

$parsed = app_cli_project_metadata_export_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_project_metadata_export_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_project_metadata_export_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$result = app_project_metadata_bundle_export($app, $parsed['project_key'], [
    'output_dir' => $parsed['output_dir'],
    'scope' => $parsed['scope'],
    'database_source_keys' => $parsed['database_sources'],
    'requested_by' => $parsed['requested_by'],
]);
$exitCode = $result['ok'] ? 0 : 1;

fwrite(
    $exitCode === 0 ? STDOUT : STDERR,
    json_encode(
        [
            'ok' => $result['ok'],
            'bundle_root' => $result['bundle_root'],
            'manifest' => $result['manifest'],
            'summary' => $result['summary'],
            'error' => $result['error'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($exitCode);
