<?php

declare(strict_types=1);

require_once __DIR__ . '/project_page_security_repository_pdo.php';

function app_fetch_project_page_security_policies(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_page_security_policies($app, $projectKey);
}

function app_fetch_project_page_security_policy(array $app, string $projectKey, int $policyId): array
{
    return app_pdo_fetch_project_page_security_policy($app, $projectKey, $policyId);
}

function app_create_project_page_security_policy(array $app, string $projectKey, array $input): array
{
    return app_pdo_create_project_page_security_policy($app, $projectKey, $input);
}

function app_update_project_page_security_policy(array $app, string $projectKey, int $policyId, array $input): array
{
    return app_pdo_update_project_page_security_policy($app, $projectKey, $policyId, $input);
}

function app_delete_project_page_security_policy(array $app, string $projectKey, int $policyId): array
{
    return app_pdo_delete_project_page_security_policy($app, $projectKey, $policyId);
}
