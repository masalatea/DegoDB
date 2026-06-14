<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/project_db_access_bootstrap_service.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';
require_once __DIR__ . '/project_scope_policy.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_db_access_sync_source_of_truth(): string
{
    return 'sync-bootstrap';
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_project_db_access_sync_preflight(array $app, string $projectKey): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'error' => 'project key の形式が不正です。',
        ];
    }

    if (!app_project_is_primary_self_loop_target($normalizedProjectKey)) {
        return [
            'ok' => false,
            'error' => '現在の bootstrap dbclasses は Project 1 (MTOOL) を基準にしているため、DB Access sync の主導線は MTOOL のみ対応です。その他 project は test/reference data として扱います。',
        ];
    }

    $candidateCatalogResult = app_project_db_access_bootstrap_candidate_catalog($app, $normalizedProjectKey);
    if (!$candidateCatalogResult['ok']) {
        return [
            'ok' => false,
            'error' => $candidateCatalogResult['error'],
        ];
    }

    if ($candidateCatalogResult['items'] === []) {
        return [
            'ok' => false,
            'error' => 'DB Access sync に使える generated / canonical candidate がありません。先に import と data class sync を確認してください。',
        ];
    }

    return [
        'ok' => true,
        'error' => '',
    ];
}

/**
 * @param array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string,
 *     last_detected_dbaccess_file:string,
 *     last_detected_data_file:string,
 *     updated_at:string
 * }|null $existingItem
 * @param array{
 *     source_name:string,
 *     data_file:string,
 *     dbaccess_file:string,
 *     data_path:string,
 *     dbaccess_path:string,
 *     has_data_file:bool,
 *     has_dbaccess_file:bool
 * } $entity
 * @return array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string,
 *     last_detected_dbaccess_file:string,
 *     last_detected_data_file:string
 * }
 */
function app_project_db_access_sync_class_input(string $projectKey, array $entity, ?array $existingItem): array
{
    $input = app_project_db_access_class_form_from_entity($entity);
    $input['source_of_truth'] = app_project_db_access_sync_source_of_truth();

    if ($existingItem !== null && in_array($existingItem['source_of_truth'], ['manual', 'seed-legacy'], true)) {
        $input = app_project_db_access_class_form_from_item($existingItem);
    }

    return [
        'project_key' => $projectKey,
        'source_name' => $entity['source_name'],
        'store_base_path' => $input['store_base_path'],
        'is_autoload' => $input['is_autoload'],
        'notes' => $input['notes'],
        'source_of_truth' => $input['source_of_truth'],
        'last_detected_dbaccess_file' => $entity['dbaccess_file'],
        'last_detected_data_file' => $entity['data_file'],
    ];
}

/**
 * @param array{
 *     source_of_truth?:string
 * }|null $existingItem
 */
function app_project_db_access_sync_preserves_existing_function_input(?array $existingItem): bool
{
    if (!is_array($existingItem)) {
        return false;
    }

    return in_array(
        trim((string) ($existingItem['source_of_truth'] ?? '')),
        ['manual', 'seed-legacy'],
        true,
    );
}

/**
 * @param array{
 *     source_name?:string,
 *     source_kind?:string
 * } $entity
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
 * } $input
 * @param array{
 *     action:string,
 *     http_method:string,
 *     endpoint_slug:string,
 *     legacy_action_type:string,
 *     function_suffix_candidate:string
 * } $functionProfile
 * @param array{
 *     source_of_truth?:string
 * }|null $existingItem
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
function app_project_db_access_sync_apply_canonical_bootstrap_defaults(
    array $input,
    array $entity,
    array $functionProfile,
    ?array $existingItem,
): array {
    if (trim((string) ($entity['source_kind'] ?? '')) !== 'canonical-bootstrap') {
        return $input;
    }

    if (app_project_db_access_sync_preserves_existing_function_input($existingItem)) {
        return $input;
    }

    $actionType = strtoupper(trim((string) ($input['action_type'] !== ''
        ? $input['action_type']
        : ($functionProfile['legacy_action_type'] ?? ''))));

    if ($input['single_proxy_auth_type'] === '') {
        $input['single_proxy_auth_type'] = 'NoSecurity';
    }

    if ($input['parameter_type'] === '' && in_array($actionType, ['INSERT', 'UPDATE'], true)) {
        $input['parameter_type'] = 'classobject';
    }

    return $input;
}

/**
 * @return list<string>
 */
function app_project_db_access_sync_default_target_source_output_key_candidates(): array
{
    return [
        'DBTABLE-PROXY-SERVER',
        'OPENAPI-JSON',
    ];
}

/**
 * @param array{
 *     source_kind?:string
 * } $entity
 * @param array{
 *     source_of_truth?:string
 * }|null $existingItem
 * @param list<array<string,mixed>> $availableSourceOutputs
 * @param list<string> $existingTargetKeys
 * @return list<string>
 */
function app_project_db_access_sync_resolved_target_source_output_keys(
    array $entity,
    ?array $existingItem,
    array $availableSourceOutputs,
    array $existingTargetKeys,
): array {
    $normalizedExistingTargetKeys = app_project_db_access_normalize_target_source_output_keys(
        $existingTargetKeys,
        $availableSourceOutputs,
    );
    if ($normalizedExistingTargetKeys !== []) {
        return $normalizedExistingTargetKeys;
    }

    if ($existingItem !== null) {
        return $normalizedExistingTargetKeys;
    }

    if (trim((string) ($entity['source_kind'] ?? '')) !== 'canonical-bootstrap') {
        return $normalizedExistingTargetKeys;
    }

    return app_project_db_access_normalize_target_source_output_keys(
        app_project_db_access_sync_default_target_source_output_key_candidates(),
        $availableSourceOutputs,
    );
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
 *     source_of_truth:string,
 *     updated_at:string
 * }|null $existingItem
 * @param array{
 *     source_name:string,
 *     data_file:string,
 *     dbaccess_file:string,
 *     data_path:string,
 *     dbaccess_path:string,
 *     has_data_file:bool,
 *     has_dbaccess_file:bool
 * } $entity
 * @param array{
 *     name:string,
 *     line:int,
 *     end_line:int,
 *     signature:string
 * } $method
 * @return array{
 *     project_key:string,
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
 *     source_of_truth:string,
 *     last_detected_dbaccess_file:string,
 *     last_detected_data_file:string
 * }
 */
function app_project_db_access_sync_function_input(
    string $projectKey,
    array $entity,
    array $method,
    ?array $existingItem,
): array {
    $functionProfile = app_project_db_access_guess_function_profile($method['name']);
    $input = app_project_db_access_function_form_from_preview($entity, $method, $functionProfile);
    $input['source_of_truth'] = app_project_db_access_sync_source_of_truth();

    if ($existingItem !== null) {
        if (app_project_db_access_sync_preserves_existing_function_input($existingItem)) {
            $input = app_project_db_access_function_form_from_item($existingItem);
        } elseif ($existingItem['function_list_order'] !== '' && $existingItem['function_list_order'] !== '0') {
            $input['function_list_order'] = $existingItem['function_list_order'];
        }
    }

    $input = app_project_db_access_sync_apply_canonical_bootstrap_defaults(
        $input,
        $entity,
        $functionProfile,
        $existingItem,
    );

    $input['source_name'] = $entity['source_name'];
    $input['function_name'] = $method['name'];
    $input['detected_signature'] = $method['signature'];
    $input['detected_line'] = (string) $method['line'];

    return [
        'project_key' => $projectKey,
        'source_name' => $input['source_name'],
        'function_name' => $input['function_name'],
        'function_list_order' => $input['function_list_order'],
        'function_suffix' => $input['function_suffix'],
        'action_type' => $input['action_type'],
        'data_class_base_name' => $input['data_class_base_name'],
        'target_table_name' => $input['target_table_name'],
        'parameter_type' => $input['parameter_type'],
        'select_by_distinct' => $input['select_by_distinct'],
        'sort_order_columns' => $input['sort_order_columns'],
        'memo' => $input['memo'],
        'limit_parameter_type' => $input['limit_parameter_type'],
        'limit_fixed_parameter' => $input['limit_fixed_parameter'],
        'or_group_type' => $input['or_group_type'],
        'single_proxy_auth_type' => $input['single_proxy_auth_type'],
        'single_proxy_single_get_function_name' => $input['single_proxy_single_get_function_name'],
        'is_blob_target' => $input['is_blob_target'],
        'detected_signature' => $input['detected_signature'],
        'detected_line' => $input['detected_line'],
        'source_of_truth' => $input['source_of_truth'],
        'last_detected_dbaccess_file' => $entity['dbaccess_file'],
        'last_detected_data_file' => $entity['data_file'],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         total_candidate_entities:int,
 *         dbaccess_candidate_count:int,
 *         method_candidate_count:int,
 *         class_inserted_count:int,
 *         class_updated_count:int,
 *         function_inserted_count:int,
 *         function_updated_count:int,
 *         stale_class_count:int,
 *         stale_function_count:int,
 *         stale_function_pruned_count:int
 *     },
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_db_access_sync_from_generated_catalog(array $app, string $projectKey): array
{
    $preflight = app_project_db_access_sync_preflight($app, $projectKey);
    if (!$preflight['ok']) {
        return [
            'ok' => false,
            'summary' => [
                'project_key' => app_normalize_project_key($projectKey),
                'total_candidate_entities' => 0,
                'dbaccess_candidate_count' => 0,
                'method_candidate_count' => 0,
                'class_inserted_count' => 0,
                'class_updated_count' => 0,
                'function_inserted_count' => 0,
                'function_updated_count' => 0,
                'stale_class_count' => 0,
                'stale_function_count' => 0,
                'stale_function_pruned_count' => 0,
            ],
            'errors' => [$preflight['error']],
            'error' => $preflight['error'],
        ];
    }

    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $candidateCatalogResult = app_project_db_access_bootstrap_candidate_catalog($app, $normalizedProjectKey);
    if (!$candidateCatalogResult['ok']) {
        return [
            'ok' => false,
            'summary' => [
                'project_key' => $normalizedProjectKey,
                'total_candidate_entities' => 0,
                'dbaccess_candidate_count' => 0,
                'method_candidate_count' => 0,
                'class_inserted_count' => 0,
                'class_updated_count' => 0,
                'function_inserted_count' => 0,
                'function_updated_count' => 0,
                'stale_class_count' => 0,
                'stale_function_count' => 0,
                'stale_function_pruned_count' => 0,
            ],
            'errors' => [$candidateCatalogResult['error']],
            'error' => $candidateCatalogResult['error'],
        ];
    }

    $catalog = $candidateCatalogResult['items'];
    $sourceOutputCatalogResult = app_fetch_project_source_output_catalog($app, $normalizedProjectKey);
    $singleProxySourceOutputs = [];
    if ($sourceOutputCatalogResult['ok']) {
        $singleProxySourceOutputs = array_values(array_filter(
            $sourceOutputCatalogResult['items'],
            static fn (array $sourceOutput): bool => app_source_output_supports_single_function_proxy_targets($sourceOutput),
        ));
    } else {
        $errors[] = 'source output catalog の読み込みに失敗しました: ' . $sourceOutputCatalogResult['error'];
    }

    $existingClassCatalog = app_fetch_db_access_class_metadata_catalog($app, $normalizedProjectKey);
    if (!$existingClassCatalog['ok']) {
        return [
            'ok' => false,
            'summary' => [
                'project_key' => $normalizedProjectKey,
                'total_candidate_entities' => count($catalog),
                'dbaccess_candidate_count' => 0,
                'method_candidate_count' => 0,
                'class_inserted_count' => 0,
                'class_updated_count' => 0,
                'function_inserted_count' => 0,
                'function_updated_count' => 0,
                'stale_class_count' => 0,
                'stale_function_count' => 0,
                'stale_function_pruned_count' => 0,
            ],
            'errors' => [$existingClassCatalog['error']],
            'error' => $existingClassCatalog['error'],
        ];
    }

    $existingClassBySource = [];
    foreach ($existingClassCatalog['items'] as $item) {
        $existingClassBySource[$item['source_name']] = $item;
    }

    $errors = [];
    $dbaccessCandidateCount = 0;
    $methodCandidateCount = 0;
    $classInsertedCount = 0;
    $classUpdatedCount = 0;
    $functionInsertedCount = 0;
    $functionUpdatedCount = 0;
    $staleFunctionCount = 0;
    $staleFunctionPrunedCount = 0;
    $processedSourceNames = [];

    foreach ($catalog as $entity) {
        if (!is_array($entity)) {
            continue;
        }

        $dbaccessCandidateCount++;
        $processedSourceNames[] = $entity['source_name'];

        $existingClassItem = $existingClassBySource[$entity['source_name']] ?? null;
        $classInput = app_project_db_access_sync_class_input($normalizedProjectKey, $entity, $existingClassItem);
        $classUpsertResult = app_upsert_db_access_class_metadata($app, $classInput);
        if (!$classUpsertResult['ok']) {
            $errors[] = $entity['source_name'] . ': class sync に失敗しました: ' . $classUpsertResult['error'];
            continue;
        }

        if ($existingClassItem === null) {
            $classInsertedCount++;
        } else {
            $classUpdatedCount++;
        }

        $methodCatalog = array_values(array_filter(
            $entity['method_catalog'] ?? [],
            static fn (mixed $method): bool => is_array($method),
        ));
        $methodCandidateCount += count($methodCatalog);
        $generatedMethodNames = [];

        $existingFunctionCatalog = app_fetch_db_access_function_metadata_catalog(
            $app,
            $normalizedProjectKey,
            $entity['source_name'],
        );
        if (!$existingFunctionCatalog['ok']) {
            $errors[] = $entity['source_name'] . ': function catalog の読み込みに失敗しました: ' . $existingFunctionCatalog['error'];
            continue;
        }

        $existingFunctionByName = [];
        foreach ($existingFunctionCatalog['items'] as $item) {
            $existingFunctionByName[$item['function_name']] = $item;
        }

        foreach ($methodCatalog as $method) {
            $generatedMethodNames[] = $method['name'];

            $existingFunctionResult = app_fetch_db_access_function_metadata(
                $app,
                $normalizedProjectKey,
                $entity['source_name'],
                $method['name'],
            );
            if (!$existingFunctionResult['ok']) {
                $errors[] = $entity['source_name'] . '.' . $method['name']
                    . ': function detail の読み込みに失敗しました: ' . $existingFunctionResult['error'];
                continue;
            }

            $functionInput = app_project_db_access_sync_function_input(
                $normalizedProjectKey,
                $entity,
                $method,
                $existingFunctionResult['item'],
            );
            $functionUpsertResult = app_upsert_db_access_function_metadata($app, $functionInput);
            if (!$functionUpsertResult['ok']) {
                $errors[] = $entity['source_name'] . '.' . $method['name']
                    . ': function sync に失敗しました: ' . $functionUpsertResult['error'];
                continue;
            }

            if ($existingFunctionResult['item'] === null) {
                $functionInsertedCount++;
            } else {
                $functionUpdatedCount++;
            }

            $existingTargetKeysResult = app_fetch_db_access_function_source_output_target_keys(
                $app,
                $normalizedProjectKey,
                $entity['source_name'],
                $method['name'],
            );
            if (!$existingTargetKeysResult['ok']) {
                $errors[] = $entity['source_name'] . '.' . $method['name']
                    . ': function target catalog の読み込みに失敗しました: ' . $existingTargetKeysResult['error'];
                continue;
            }

            $resolvedTargetKeys = app_project_db_access_sync_resolved_target_source_output_keys(
                $entity,
                $existingFunctionResult['item'],
                $singleProxySourceOutputs,
                $existingTargetKeysResult['items'],
            );
            if ($resolvedTargetKeys !== $existingTargetKeysResult['items']) {
                $replaceTargetsResult = app_replace_db_access_function_source_output_target_keys(
                    $app,
                    $normalizedProjectKey,
                    $entity['source_name'],
                    $method['name'],
                    $resolvedTargetKeys,
                );
                if (!$replaceTargetsResult['ok']) {
                    $errors[] = $entity['source_name'] . '.' . $method['name']
                        . ': function target sync に失敗しました: ' . $replaceTargetsResult['error'];
                    continue;
                }
            }
        }

        foreach (array_keys($existingFunctionByName) as $functionName) {
            if (!in_array($functionName, $generatedMethodNames, true)) {
                $existingFunctionItem = $existingFunctionByName[$functionName];
                if (($existingFunctionItem['source_of_truth'] ?? '') === app_project_db_access_sync_source_of_truth()) {
                    $deleteResult = app_delete_db_access_function_metadata(
                        $app,
                        $normalizedProjectKey,
                        $entity['source_name'],
                        $functionName,
                    );
                    if (!$deleteResult['ok']) {
                        $errors[] = $entity['source_name'] . '.' . $functionName
                            . ': stale sync-bootstrap function の削除に失敗しました: ' . $deleteResult['error'];
                        $staleFunctionCount++;
                        continue;
                    }

                    $staleFunctionPrunedCount++;
                    continue;
                }

                $staleFunctionCount++;
            }
        }
    }

    $staleClassCount = 0;
    foreach (array_keys($existingClassBySource) as $sourceName) {
        if (!in_array($sourceName, $processedSourceNames, true)) {
            $staleClassCount++;
        }
    }

    $summary = [
        'project_key' => $normalizedProjectKey,
        'total_candidate_entities' => count($catalog),
        'dbaccess_candidate_count' => $dbaccessCandidateCount,
        'method_candidate_count' => $methodCandidateCount,
        'class_inserted_count' => $classInsertedCount,
        'class_updated_count' => $classUpdatedCount,
        'function_inserted_count' => $functionInsertedCount,
        'function_updated_count' => $functionUpdatedCount,
        'stale_class_count' => $staleClassCount,
        'stale_function_count' => $staleFunctionCount,
        'stale_function_pruned_count' => $staleFunctionPrunedCount,
    ];

    return [
        'ok' => $errors === [],
        'summary' => $summary,
        'errors' => $errors,
        'error' => $errors === [] ? '' : implode("\n", $errors),
    ];
}
