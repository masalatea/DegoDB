#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample17_multi_output_project_check.php';

$app = app_bootstrap();
$result = app_sample17_multi_output_run(
    $app,
    'sample17-check',
    app_sample17_multi_output_default_reference_root(),
);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($result['ok'] ? 0 : 1);
