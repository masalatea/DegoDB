<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository_pdo.php';

function app_fetch_db_access_class_metadata(array $app, string $projectKey, string $sourceName): array
{
    return app_pdo_fetch_db_access_class_metadata($app, $projectKey, $sourceName);
}

function app_fetch_db_access_class_metadata_catalog(array $app, string $projectKey): array
{
    return app_pdo_fetch_db_access_class_metadata_catalog($app, $projectKey);
}

function app_upsert_db_access_class_metadata(array $app, array $input): array
{
    return app_pdo_upsert_db_access_class_metadata($app, $input);
}

function app_fetch_db_access_function_metadata(array $app, string $projectKey, string $sourceName, string $functionName): array
{
    return app_pdo_fetch_db_access_function_metadata($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_metadata_catalog(array $app, string $projectKey, string $sourceName): array
{
    return app_pdo_fetch_db_access_function_metadata_catalog($app, $projectKey, $sourceName);
}

function app_upsert_db_access_function_metadata(array $app, array $input): array
{
    return app_pdo_upsert_db_access_function_metadata($app, $input);
}

function app_delete_db_access_function_metadata(array $app, string $projectKey, string $sourceName, string $functionName): array
{
    return app_pdo_delete_db_access_function_metadata($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_source_output_target_keys(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_source_output_target_keys(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
    );
}

function app_replace_db_access_function_source_output_target_keys(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    array $sourceOutputKeys,
): array {
    return app_pdo_replace_db_access_function_source_output_target_keys(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $sourceOutputKeys,
    );
}

function app_fetch_source_output_db_access_function_target_catalog(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
): array {
    return app_pdo_fetch_source_output_db_access_function_target_catalog(
        $app,
        $projectKey,
        $sourceOutputKey,
    );
}

function app_reorder_db_access_functions(array $app, array $input): array
{
    return app_pdo_reorder_db_access_functions($app, $input);
}

function app_move_db_access_function(array $app, array $input): array
{
    return app_pdo_move_db_access_function($app, $input);
}

function app_fetch_db_access_function_select_where_catalog(array $app, string $projectKey, string $sourceName, string $functionName): array
{
    return app_pdo_fetch_db_access_function_select_where_catalog($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_select_where_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectWhereId,
): array {
    return app_pdo_fetch_db_access_function_select_where_item($app, $projectKey, $sourceName, $functionName, $selectWhereId);
}

function app_create_db_access_function_select_where(array $app, array $input): array
{
    return app_pdo_create_db_access_function_select_where($app, $input);
}

function app_update_db_access_function_select_where(array $app, array $input): array
{
    return app_pdo_update_db_access_function_select_where($app, $input);
}

function app_delete_db_access_function_select_where(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectWhereId,
): array {
    return app_pdo_delete_db_access_function_select_where($app, $projectKey, $sourceName, $functionName, $selectWhereId);
}

function app_reorder_db_access_function_select_where(array $app, array $input): array
{
    return app_pdo_reorder_db_access_function_select_where($app, $input);
}

function app_fetch_db_access_function_select_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_select_target_field_catalog($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_select_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectTargetFieldId,
): array {
    return app_pdo_fetch_db_access_function_select_target_field_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $selectTargetFieldId,
    );
}

function app_create_db_access_function_select_target_field(array $app, array $input): array
{
    return app_pdo_create_db_access_function_select_target_field($app, $input);
}

function app_update_db_access_function_select_target_field(array $app, array $input): array
{
    return app_pdo_update_db_access_function_select_target_field($app, $input);
}

function app_delete_db_access_function_select_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectTargetFieldId,
): array {
    return app_pdo_delete_db_access_function_select_target_field(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $selectTargetFieldId,
    );
}

function app_fetch_db_access_function_select_having_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_select_having_catalog($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_select_having_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectHavingId,
): array {
    return app_pdo_fetch_db_access_function_select_having_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $selectHavingId,
    );
}

function app_create_db_access_function_select_having(array $app, array $input): array
{
    return app_pdo_create_db_access_function_select_having($app, $input);
}

function app_update_db_access_function_select_having(array $app, array $input): array
{
    return app_pdo_update_db_access_function_select_having($app, $input);
}

function app_delete_db_access_function_select_having(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $selectHavingId,
): array {
    return app_pdo_delete_db_access_function_select_having(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $selectHavingId,
    );
}

function app_fetch_db_access_function_update_delete_where_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_update_delete_where_catalog($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_update_delete_where_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateDeleteWhereId,
): array {
    return app_pdo_fetch_db_access_function_update_delete_where_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $updateDeleteWhereId,
    );
}

function app_create_db_access_function_update_delete_where(array $app, array $input): array
{
    return app_pdo_create_db_access_function_update_delete_where($app, $input);
}

function app_update_db_access_function_update_delete_where(array $app, array $input): array
{
    return app_pdo_update_db_access_function_update_delete_where($app, $input);
}

function app_delete_db_access_function_update_delete_where(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateDeleteWhereId,
): array {
    return app_pdo_delete_db_access_function_update_delete_where(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $updateDeleteWhereId,
    );
}

function app_reorder_db_access_function_update_delete_where(array $app, array $input): array
{
    return app_pdo_reorder_db_access_function_update_delete_where($app, $input);
}

function app_fetch_db_access_function_insert_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_insert_target_field_catalog($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_insert_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $insertTargetFieldId,
): array {
    return app_pdo_fetch_db_access_function_insert_target_field_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $insertTargetFieldId,
    );
}

function app_create_db_access_function_insert_target_field(array $app, array $input): array
{
    return app_pdo_create_db_access_function_insert_target_field($app, $input);
}

function app_update_db_access_function_insert_target_field(array $app, array $input): array
{
    return app_pdo_update_db_access_function_insert_target_field($app, $input);
}

function app_delete_db_access_function_insert_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $insertTargetFieldId,
): array {
    return app_pdo_delete_db_access_function_insert_target_field(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $insertTargetFieldId,
    );
}

function app_fetch_db_access_function_update_target_field_catalog(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
): array {
    return app_pdo_fetch_db_access_function_update_target_field_catalog($app, $projectKey, $sourceName, $functionName);
}

function app_fetch_db_access_function_update_target_field_item(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateTargetFieldId,
): array {
    return app_pdo_fetch_db_access_function_update_target_field_item(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $updateTargetFieldId,
    );
}

function app_create_db_access_function_update_target_field(array $app, array $input): array
{
    return app_pdo_create_db_access_function_update_target_field($app, $input);
}

function app_update_db_access_function_update_target_field(array $app, array $input): array
{
    return app_pdo_update_db_access_function_update_target_field($app, $input);
}

function app_delete_db_access_function_update_target_field(
    array $app,
    string $projectKey,
    string $sourceName,
    string $functionName,
    string $updateTargetFieldId,
): array {
    return app_pdo_delete_db_access_function_update_target_field(
        $app,
        $projectKey,
        $sourceName,
        $functionName,
        $updateTargetFieldId,
    );
}
