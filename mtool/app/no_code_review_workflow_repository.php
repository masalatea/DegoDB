<?php

declare(strict_types=1);

require_once __DIR__ . '/no_code_review_workflow_repository_pdo.php';

function app_no_code_review_workflow_create_or_reuse_request(array $app, array $input): array
{
    return app_pdo_no_code_review_workflow_create_or_reuse_request($app, $input);
}

function app_no_code_review_workflow_fetch_latest_requests(array $app, array $filters = []): array
{
    return app_pdo_no_code_review_workflow_fetch_latest_requests($app, $filters);
}
