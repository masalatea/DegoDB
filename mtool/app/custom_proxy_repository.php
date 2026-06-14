<?php

declare(strict_types=1);

require_once __DIR__ . '/custom_proxy_repository_pdo.php';

function app_fetch_project_custom_proxy_catalog(array $app, string $projectKey): array
{
    return app_pdo_fetch_project_custom_proxy_catalog($app, $projectKey);
}

function app_fetch_project_custom_proxy_item(array $app, string $projectKey, string $customProxyKey): array
{
    return app_pdo_fetch_project_custom_proxy_item($app, $projectKey, $customProxyKey);
}

function app_create_project_custom_proxy(array $app, array $input): array
{
    return app_pdo_create_project_custom_proxy($app, $input);
}

function app_update_project_custom_proxy(array $app, array $input): array
{
    return app_pdo_update_project_custom_proxy($app, $input);
}

function app_delete_project_custom_proxy(array $app, string $projectKey, string $customProxyKey): array
{
    return app_pdo_delete_project_custom_proxy($app, $projectKey, $customProxyKey);
}

function app_fetch_project_custom_proxy_target_keys(array $app, string $projectKey, string $customProxyKey): array
{
    return app_pdo_fetch_project_custom_proxy_target_keys($app, $projectKey, $customProxyKey);
}

function app_replace_project_custom_proxy_target_keys(
    array $app,
    string $projectKey,
    string $customProxyKey,
    array $sourceOutputKeys,
): array {
    return app_pdo_replace_project_custom_proxy_target_keys($app, $projectKey, $customProxyKey, $sourceOutputKeys);
}

function app_fetch_project_custom_proxy_step_catalog(array $app, string $projectKey, string $customProxyKey): array
{
    return app_pdo_fetch_project_custom_proxy_step_catalog($app, $projectKey, $customProxyKey);
}

function app_create_project_custom_proxy_step(array $app, array $input): array
{
    return app_pdo_create_project_custom_proxy_step($app, $input);
}

function app_update_project_custom_proxy_step(array $app, array $input): array
{
    return app_pdo_update_project_custom_proxy_step($app, $input);
}

function app_delete_project_custom_proxy_step(
    array $app,
    string $projectKey,
    string $customProxyKey,
    string $stepId,
): array {
    return app_pdo_delete_project_custom_proxy_step($app, $projectKey, $customProxyKey, $stepId);
}

function app_reorder_project_custom_proxy_steps(
    array $app,
    string $projectKey,
    string $customProxyKey,
    array $stepIds,
): array {
    return app_pdo_reorder_project_custom_proxy_steps($app, $projectKey, $customProxyKey, $stepIds);
}

function app_reset_project_custom_proxy_step_order(
    array $app,
    string $projectKey,
    string $customProxyKey,
): array {
    return app_pdo_reset_project_custom_proxy_step_order($app, $projectKey, $customProxyKey);
}
