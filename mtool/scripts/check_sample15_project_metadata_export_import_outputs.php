#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample15_project_metadata_export_import_check.php';

$previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

try {
    $result = app_sample15_bundle_run(
        app_bootstrap(),
        'sample15-check',
        app_sample15_bundle_default_reference_root(),
    );
} finally {
    if ($previousPolicy === false) {
        putenv('MTOOL_GENERATED_NAME_POLICY');
    } else {
        putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
    }
}

fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);
