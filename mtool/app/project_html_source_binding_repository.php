<?php

declare(strict_types=1);

require_once __DIR__ . '/project_html_source_binding_repository_pdo.php';

function app_fetch_project_html_source_bindings(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_html_source_bindings($app, $projectKey);
}

function app_fetch_project_html_source_binding(array $app, string $projectKey, int $legacyProjectSourceOutputPid): array
{
    return app_pdo_fetch_project_html_source_binding($app, $projectKey, $legacyProjectSourceOutputPid);
}

function app_upsert_project_html_source_binding(array $app, string $projectKey, array $input): array
{
    return app_pdo_upsert_project_html_source_binding($app, $projectKey, $input);
}

function app_delete_project_html_source_binding(array $app, string $projectKey, int $legacyProjectSourceOutputPid): array
{
    return app_pdo_delete_project_html_source_binding($app, $projectKey, $legacyProjectSourceOutputPid);
}
