<?php

declare(strict_types=1);

$appRoot = getenv('APP_APP_ROOT') ?: '/var/www/mtool/app';

require_once rtrim($appRoot, '/') . '/http.php';

app_run_http_request();
