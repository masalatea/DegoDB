<?php

declare(strict_types=1);

require_once __DIR__ . '/project_host_assignment_repository_pdo.php';

function app_fetch_project_host_assignments(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_host_assignments($app, $projectKey);
}

function app_fetch_project_host_assignment(array $app, string $projectKey, int $assignmentId): array
{
    return app_pdo_fetch_project_host_assignment($app, $projectKey, $assignmentId);
}

function app_create_project_host_assignment(array $app, string $projectKey, array $input): array
{
    return app_pdo_create_project_host_assignment($app, $projectKey, $input);
}

function app_update_project_host_assignment(array $app, string $projectKey, int $assignmentId, array $input): array
{
    return app_pdo_update_project_host_assignment($app, $projectKey, $assignmentId, $input);
}

function app_delete_project_host_assignment(array $app, string $projectKey, int $assignmentId): array
{
    return app_pdo_delete_project_host_assignment($app, $projectKey, $assignmentId);
}
