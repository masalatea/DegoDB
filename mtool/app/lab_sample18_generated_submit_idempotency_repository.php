<?php

declare(strict_types=1);

require_once __DIR__ . '/lab_sample18_generated_submit_idempotency_repository_pdo.php';

function app_lab_sample18_generated_submit_idempotency_create_or_reuse_record(array $app, array $input): array
{
    return app_pdo_lab_sample18_generated_submit_idempotency_create_or_reuse_record($app, $input);
}

function app_lab_sample18_generated_submit_idempotency_fetch_latest_records(array $app, array $filters = []): array
{
    return app_pdo_lab_sample18_generated_submit_idempotency_fetch_latest_records($app, $filters);
}
