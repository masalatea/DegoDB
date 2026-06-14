<?php

declare(strict_types=1);

require_once __DIR__ . '/source_output_repository_pdo.php';

function app_fetch_project_source_output_catalog(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_source_output_catalog($app, $projectKey);
}

function app_fetch_project_source_output_item(array $app, string $projectKey, string $sourceOutputKey): array
{
    return app_pdo_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
}

function app_fetch_project_source_output_default_item(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_source_output_default_item($app, $projectKey);
}

function app_create_project_source_output(array $app, array $input): array
{
    return app_pdo_create_project_source_output($app, $input);
}

function app_update_project_source_output(array $app, array $input): array
{
    return app_pdo_update_project_source_output($app, $input);
}

function app_delete_project_source_output(array $app, array $input): array
{
    return app_pdo_delete_project_source_output($app, $input);
}

function app_reorder_project_source_outputs(array $app, array $input): array
{
    return app_pdo_reorder_project_source_outputs($app, $input);
}
