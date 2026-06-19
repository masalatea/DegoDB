<?php

declare(strict_types=1);

require_once __DIR__ . '/audit_log_repository_pdo.php';

function app_audit_log_append(array $app, array $input): array
{
    return app_pdo_audit_log_append($app, $input);
}

function app_audit_log_fetch_latest(array $app, array $filters = []): array
{
    return app_pdo_audit_log_fetch_latest($app, $filters);
}
