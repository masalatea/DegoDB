<?php

declare(strict_types=1);

require_once __DIR__ . '/project_membership_repository_pdo.php';

function app_fetch_project_membership_summary(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_membership_summary($app, $projectKey);
}

function app_replace_project_memberships(array $app, string $projectKey, array $members): array
{
    return app_pdo_replace_project_memberships($app, $projectKey, $members);
}
