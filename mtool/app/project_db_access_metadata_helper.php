<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/table_metadata_repository.php';

/**
 * @return array{
 *     action:string,
 *     http_method:string,
 *     endpoint_slug:string,
 *     legacy_action_type:string,
 *     function_suffix_candidate:string
 * }
 */
function app_project_db_access_guess_function_profile(string $methodName): array
{
    $normalized = trim($methodName);
    $endpointSlug = strtolower(
        preg_replace('/[^a-z0-9]+/i', '-', $normalized) ?? $normalized,
    );
    $endpointSlug = trim($endpointSlug, '-');
    if ($endpointSlug === '') {
        $endpointSlug = 'function-preview';
    }

    $functionSuffixCandidate = $normalized;
    $action = 'custom';
    $httpMethod = 'POST';
    $legacyActionType = 'UNKNOWN';

    if (str_starts_with($normalized, 'Get')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = str_ends_with($normalized, 'List') ? 'SELECTLIST' : 'SELECTSINGLE';
        $functionSuffixCandidate = substr($normalized, 3) ?: $normalized;
    } elseif (str_starts_with($normalized, 'List')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = 'SELECTLIST';
        $functionSuffixCandidate = substr($normalized, 4) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Search')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = 'SELECTLIST';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Find')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = str_ends_with($normalized, 'List') ? 'SELECTLIST' : 'SELECTSINGLE';
        $functionSuffixCandidate = substr($normalized, 4) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Select')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = str_ends_with($normalized, 'List') ? 'SELECTLIST' : 'SELECTSINGLE';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Count')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = 'SELECTSINGLE';
        $functionSuffixCandidate = substr($normalized, 5) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Exists')) {
        $action = 'read';
        $httpMethod = 'GET';
        $legacyActionType = 'SELECTSINGLE';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Insert')) {
        $action = 'insert';
        $httpMethod = 'POST';
        $legacyActionType = 'INSERT';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Create')) {
        $action = 'insert';
        $httpMethod = 'POST';
        $legacyActionType = 'INSERT';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Add')) {
        $action = 'insert';
        $httpMethod = 'POST';
        $legacyActionType = 'INSERT';
        $functionSuffixCandidate = substr($normalized, 3) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Register')) {
        $action = 'insert';
        $httpMethod = 'POST';
        $legacyActionType = 'INSERT';
        $functionSuffixCandidate = substr($normalized, 8) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Update')) {
        $action = 'update';
        $httpMethod = 'PUT';
        $legacyActionType = 'UPDATE';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Save')) {
        $action = 'update';
        $httpMethod = 'PUT';
        $legacyActionType = 'UPDATE';
        $functionSuffixCandidate = substr($normalized, 4) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Set')) {
        $action = 'update';
        $httpMethod = 'PUT';
        $legacyActionType = 'UPDATE';
        $functionSuffixCandidate = substr($normalized, 3) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Delete')) {
        $action = 'delete';
        $httpMethod = 'DELETE';
        $legacyActionType = 'DELETE';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    } elseif (str_starts_with($normalized, 'Remove')) {
        $action = 'delete';
        $httpMethod = 'DELETE';
        $legacyActionType = 'DELETE';
        $functionSuffixCandidate = substr($normalized, 6) ?: $normalized;
    }

    if (str_ends_with($functionSuffixCandidate, 'List')) {
        $trimmed = substr($functionSuffixCandidate, 0, -4);
        if (is_string($trimmed) && $trimmed !== '') {
            $functionSuffixCandidate = $trimmed;
        }
    }

    $functionSuffixCandidate = trim($functionSuffixCandidate);
    if ($functionSuffixCandidate === '') {
        $functionSuffixCandidate = $normalized;
    }

    return [
        'action' => $action,
        'http_method' => $httpMethod,
        'endpoint_slug' => $endpointSlug,
        'legacy_action_type' => $legacyActionType,
        'function_suffix_candidate' => $functionSuffixCandidate,
    ];
}

/**
 * @param array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string
 * } $item
 * @return array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_class_form_from_item(array $item): array
{
    return [
        'source_name' => $item['source_name'],
        'store_base_path' => $item['store_base_path'],
        'is_autoload' => $item['is_autoload'],
        'notes' => $item['notes'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

/**
 * @param array{
 *     source_name:string
 * } $entity
 * @return array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_class_form_from_entity(array $entity): array
{
    $input = app_db_access_class_form_defaults();
    $input['source_name'] = $entity['source_name'];

    return $input;
}

/**
 * @param array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * } $item
 * @return array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_form_from_item(array $item): array
{
    return [
        'source_name' => $item['source_name'],
        'function_name' => $item['function_name'],
        'function_list_order' => $item['function_list_order'],
        'function_suffix' => $item['function_suffix'],
        'action_type' => $item['action_type'],
        'data_class_base_name' => $item['data_class_base_name'],
        'target_table_name' => $item['target_table_name'],
        'parameter_type' => $item['parameter_type'],
        'select_by_distinct' => $item['select_by_distinct'],
        'sort_order_columns' => $item['sort_order_columns'],
        'memo' => $item['memo'],
        'limit_parameter_type' => $item['limit_parameter_type'],
        'limit_fixed_parameter' => $item['limit_fixed_parameter'],
        'or_group_type' => $item['or_group_type'],
        'single_proxy_auth_type' => $item['single_proxy_auth_type'],
        'single_proxy_single_get_function_name' => $item['single_proxy_single_get_function_name'],
        'is_blob_target' => $item['is_blob_target'],
        'detected_signature' => $item['detected_signature'],
        'detected_line' => $item['detected_line'],
        'source_of_truth' => $item['source_of_truth'],
    ];
}

/**
 * @param array{
 *     source_name:string
 * } $entity
 * @param array{
 *     name:string,
 *     line:int,
 *     signature:string
 * } $method
 * @param array{
 *     action:string,
 *     http_method:string,
 *     endpoint_slug:string,
 *     legacy_action_type:string,
 *     function_suffix_candidate:string
 * } $functionProfile
 * @return array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * }
 */
function app_project_db_access_function_form_from_preview(array $entity, array $method, array $functionProfile): array
{
    $input = app_db_access_function_form_defaults();
    $input['source_name'] = $entity['source_name'];
    $input['function_name'] = $method['name'];
    $input['function_list_order'] = (string) $method['line'];
    $input['function_suffix'] = $functionProfile['function_suffix_candidate'];
    $input['action_type'] = $functionProfile['legacy_action_type'];
    $input['detected_signature'] = $method['signature'];
    $input['detected_line'] = (string) $method['line'];

    if (in_array($functionProfile['legacy_action_type'], ['SELECTSINGLE', 'SELECTLIST'], true)) {
        $input['data_class_base_name'] = $entity['source_name'];
    }

    if (in_array($functionProfile['legacy_action_type'], ['INSERT', 'UPDATE', 'DELETE'], true)) {
        $input['target_table_name'] = $entity['source_name'];
    }

    return $input;
}

function app_project_db_access_normalize_target_source_output_keys(
    array $requestedKeys,
    array $availableSourceOutputs,
): array {
    $allowed = [];
    foreach ($availableSourceOutputs as $sourceOutput) {
        if (!is_array($sourceOutput)) {
            continue;
        }

        if (!app_source_output_supports_single_function_proxy_targets($sourceOutput)) {
            continue;
        }

        $sourceOutputKey = (string) ($sourceOutput['source_output_key'] ?? '');
        if ($sourceOutputKey !== '') {
            $allowed[$sourceOutputKey] = true;
        }
    }

    $normalized = [];
    foreach ($requestedKeys as $requestedKey) {
        if (!is_string($requestedKey)) {
            continue;
        }

        $key = app_normalize_source_output_key($requestedKey);
        if ($key !== '' && isset($allowed[$key])) {
            $normalized[$key] = $key;
        }
    }

    return array_values($normalized);
}

function app_project_db_access_resolve_select_result_data_class_name(
    string $sourceName,
    ?array $functionItem = null,
): string {
    $resolvedName = '';
    if (is_array($functionItem)) {
        $resolvedName = trim((string) ($functionItem['data_class_base_name'] ?? ''));
    }
    if ($resolvedName === '') {
        $resolvedName = trim($sourceName);
    }

    return $resolvedName;
}

/**
 * @return list<string>
 */
function app_project_db_access_validate_canonical_table_column_reference(
    array $app,
    string $projectKey,
    string $tableName,
    string $columnName,
    string $tableLabel,
    string $columnLabel,
    bool $allowWildcardColumn = false,
): array {
    $normalizedTableName = trim($tableName);
    if ($normalizedTableName === '') {
        return [];
    }

    $tableResult = app_fetch_table_metadata_item($app, $projectKey, $normalizedTableName);
    if (!$tableResult['ok']) {
        return ['canonical dbtable の確認に失敗しました: ' . $tableResult['error']];
    }
    if ($tableResult['item'] === null) {
        return [$tableLabel . ' に指定した canonical table が見つかりません: ' . $normalizedTableName];
    }

    $normalizedColumnName = trim($columnName);
    if ($normalizedColumnName === '' || ($allowWildcardColumn && $normalizedColumnName === '*')) {
        return [];
    }

    $columnResult = app_fetch_table_metadata_column_item(
        $app,
        $projectKey,
        $normalizedTableName,
        $normalizedColumnName,
    );
    if (!$columnResult['ok']) {
        return ['canonical dbtablecolumns の確認に失敗しました: ' . $columnResult['error']];
    }
    if ($columnResult['item'] === null) {
        return [$columnLabel . ' に指定した canonical column が見つかりません: '
            . $normalizedTableName . '.' . $normalizedColumnName];
    }

    return [];
}

/**
 * @return list<string>
 */
function app_project_db_access_validate_canonical_data_class_field_reference(
    array $app,
    string $projectKey,
    string $dataClassName,
    string $fieldName,
    string $fieldLabel,
): array {
    $normalizedFieldName = trim($fieldName);
    if ($normalizedFieldName === '') {
        return [];
    }

    $normalizedDataClassName = trim($dataClassName);
    if ($normalizedDataClassName === '') {
        return [$fieldLabel . ' を確認するための canonical data class が未解決です。'];
    }

    $dataClassResult = app_fetch_data_class_metadata_item($app, $projectKey, $normalizedDataClassName);
    if (!$dataClassResult['ok']) {
        return ['canonical dataclass の確認に失敗しました: ' . $dataClassResult['error']];
    }
    if ($dataClassResult['item'] === null) {
        return [$fieldLabel . ' を確認するための canonical data class が見つかりません: '
            . $normalizedDataClassName];
    }

    $fieldResult = app_fetch_data_class_metadata_field_item(
        $app,
        $projectKey,
        $normalizedDataClassName,
        $normalizedFieldName,
    );
    if (!$fieldResult['ok']) {
        return ['canonical dataclassfields の確認に失敗しました: ' . $fieldResult['error']];
    }
    if ($fieldResult['item'] === null) {
        return [$fieldLabel . ' に指定した canonical data class field が見つかりません: '
            . $normalizedDataClassName . '.' . $normalizedFieldName];
    }

    return [];
}

/**
 * @param array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return list<string>
 */
function app_project_db_access_validate_select_where_metadata_refs(
    array $app,
    string $projectKey,
    array $input,
): array {
    $errors = app_project_db_access_validate_canonical_table_column_reference(
        $app,
        $projectKey,
        (string) ($input['target_table_name'] ?? ''),
        (string) ($input['target_table_column_name'] ?? ''),
        'Target Table Name',
        'Target Column Name',
    );

    if (trim((string) ($input['parameter_type'] ?? '')) !== 'anotherfield') {
        return $errors;
    }

    return array_merge(
        $errors,
        app_project_db_access_validate_canonical_table_column_reference(
            $app,
            $projectKey,
            (string) ($input['another_table_name'] ?? ''),
            (string) ($input['another_field_name'] ?? ''),
            'Another Table Name',
            'Another Field Name',
        ),
    );
}

/**
 * @param array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * } $input
 * @return list<string>
 */
function app_project_db_access_validate_select_target_field_metadata_refs(
    array $app,
    string $projectKey,
    string $resultDataClassName,
    array $input,
): array {
    $errors = app_project_db_access_validate_canonical_table_column_reference(
        $app,
        $projectKey,
        (string) ($input['target_table_name'] ?? ''),
        (string) ($input['target_table_column_name'] ?? ''),
        'Target Table Name',
        'Target Column Name',
        true,
    );

    if (trim((string) ($input['target_table_column_name'] ?? '')) === '*') {
        return $errors;
    }

    return array_merge(
        $errors,
        app_project_db_access_validate_canonical_data_class_field_reference(
            $app,
            $projectKey,
            $resultDataClassName,
            (string) ($input['store_class_field_name'] ?? ''),
            'Store Class Field Name',
        ),
    );
}

/**
 * @param array{
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return list<string>
 */
function app_project_db_access_validate_update_delete_where_metadata_refs(
    array $app,
    string $projectKey,
    string $targetTableName,
    array $input,
): array {
    return app_project_db_access_validate_canonical_table_column_reference(
        $app,
        $projectKey,
        $targetTableName,
        (string) ($input['target_table_column_name'] ?? ''),
        'function detail の target table',
        'Target Column Name',
    );
}
