<?php

declare(strict_types=1);

require_once __DIR__ . '/table_metadata_repository_pdo.php';

function app_fetch_table_metadata_snapshot(array $app, string $projectKey): array
{
    return app_pdo_fetch_table_metadata_snapshot($app, $projectKey);
}

function app_fetch_table_metadata_item(array $app, string $projectKey, string $tableName): array
{
    return app_pdo_fetch_table_metadata_item($app, $projectKey, $tableName);
}

function app_fetch_table_metadata_column_item(
    array $app,
    string $projectKey,
    string $tableName,
    string $columnName,
): array {
    return app_pdo_fetch_table_metadata_column_item($app, $projectKey, $tableName, $columnName);
}

function app_create_table_metadata_item(array $app, string $projectKey, string $tableName): array
{
    return app_pdo_create_table_metadata_item($app, $projectKey, $tableName);
}

function app_update_table_metadata_item(
    array $app,
    string $projectKey,
    string $tablePid,
    string $tableName,
): array {
    return app_pdo_update_table_metadata_item($app, $projectKey, $tablePid, $tableName);
}

function app_delete_table_metadata_item(array $app, string $projectKey, string $tablePid): array
{
    return app_pdo_delete_table_metadata_item($app, $projectKey, $tablePid);
}

function app_create_table_metadata_column(
    array $app,
    string $projectKey,
    string $tablePid,
    array $input,
): array {
    return app_pdo_create_table_metadata_column($app, $projectKey, $tablePid, $input);
}

function app_update_table_metadata_column(
    array $app,
    string $projectKey,
    string $columnPid,
    array $input,
): array {
    return app_pdo_update_table_metadata_column($app, $projectKey, $columnPid, $input);
}

function app_delete_table_metadata_column(array $app, string $projectKey, string $columnPid): array
{
    return app_pdo_delete_table_metadata_column($app, $projectKey, $columnPid);
}
