#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample15_project_metadata_export_import_check.php';

$result = app_sample15_bundle_run(
    app_bootstrap(),
    'sample15-check',
    app_sample15_bundle_default_reference_root(),
);

fwrite(
    $result['ok'] ? STDOUT : STDERR,
    json_encode(
        $result,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($result['ok'] ? 0 : 1);
