<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository_pdo.php';

function app_fetch_data_class_metadata_snapshot(array $app, string $projectKey): array
{
    return app_pdo_fetch_data_class_metadata_snapshot($app, $projectKey);
}

function app_fetch_data_class_metadata_item(array $app, string $projectKey, string $dataClassName): array
{
    return app_pdo_fetch_data_class_metadata_item($app, $projectKey, $dataClassName);
}

function app_fetch_data_class_metadata_field_item(
    array $app,
    string $projectKey,
    string $dataClassName,
    string $fieldName,
): array {
    return app_pdo_fetch_data_class_metadata_field_item($app, $projectKey, $dataClassName, $fieldName);
}

function app_create_data_class_metadata_item(array $app, string $projectKey, array $input): array
{
    return app_pdo_create_data_class_metadata_item($app, $projectKey, $input);
}

function app_update_data_class_metadata_item(
    array $app,
    string $projectKey,
    string $dataClassPid,
    array $input,
): array {
    return app_pdo_update_data_class_metadata_item($app, $projectKey, $dataClassPid, $input);
}

function app_delete_data_class_metadata_item(array $app, string $projectKey, string $dataClassPid): array
{
    return app_pdo_delete_data_class_metadata_item($app, $projectKey, $dataClassPid);
}

function app_create_data_class_metadata_field(
    array $app,
    string $projectKey,
    string $dataClassPid,
    array $input,
): array {
    return app_pdo_create_data_class_metadata_field($app, $projectKey, $dataClassPid, $input);
}

function app_update_data_class_metadata_field(
    array $app,
    string $projectKey,
    string $fieldPid,
    array $input,
): array {
    return app_pdo_update_data_class_metadata_field($app, $projectKey, $fieldPid, $input);
}

function app_delete_data_class_metadata_field(array $app, string $projectKey, string $fieldPid): array
{
    return app_pdo_delete_data_class_metadata_field($app, $projectKey, $fieldPid);
}
