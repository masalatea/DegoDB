#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample18_mini_task_board_demo_check.php';

$app = app_bootstrap();
$result = app_sample18_mini_task_board_demo_run(
    $app,
    'sample18-check',
    app_sample18_mini_task_board_demo_default_reference_root(),
);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($result['ok'] ? 0 : 1);
