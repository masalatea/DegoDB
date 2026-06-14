<?php

declare(strict_types=1);

require_once __DIR__ . '/compare_output_repository_pdo.php';

function app_fetch_project_compare_output_catalog(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_compare_output_catalog($app, $projectKey);
}

function app_fetch_project_compare_output_item(array $app, string $projectKey, string $compareOutputKey): array
{
    return app_pdo_fetch_project_compare_output_item($app, $projectKey, $compareOutputKey);
}

function app_create_project_compare_output(array $app, array $input): array
{
    return app_pdo_create_project_compare_output($app, $input);
}

function app_update_project_compare_output(array $app, array $input): array
{
    return app_pdo_update_project_compare_output($app, $input);
}

function app_delete_project_compare_output(array $app, string $projectKey, string $compareOutputKey): array
{
    return app_pdo_delete_project_compare_output($app, $projectKey, $compareOutputKey);
}

function app_fetch_project_compare_output_additional_path_catalog(
    array $app,
    string $projectKey,
    string $compareOutputKey,
): array {
    return app_pdo_fetch_project_compare_output_additional_path_catalog($app, $projectKey, $compareOutputKey);
}

function app_fetch_project_compare_output_additional_path_item(
    array $app,
    string $projectKey,
    string $compareOutputKey,
    string $additionalPathKey,
): array {
    return app_pdo_fetch_project_compare_output_additional_path_item(
        $app,
        $projectKey,
        $compareOutputKey,
        $additionalPathKey,
    );
}

function app_create_project_compare_output_additional_path(array $app, array $input): array
{
    return app_pdo_create_project_compare_output_additional_path($app, $input);
}

function app_update_project_compare_output_additional_path(array $app, array $input): array
{
    return app_pdo_update_project_compare_output_additional_path($app, $input);
}

function app_delete_project_compare_output_additional_path(
    array $app,
    string $projectKey,
    string $compareOutputKey,
    string $additionalPathKey,
): array {
    return app_pdo_delete_project_compare_output_additional_path(
        $app,
        $projectKey,
        $compareOutputKey,
        $additionalPathKey,
    );
}
