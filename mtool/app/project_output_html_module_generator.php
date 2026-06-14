<?php

declare(strict_types=1);

require_once __DIR__ . '/project_output_html_module_catalog.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/project_html_source_binding_repository.php';
require_once __DIR__ . '/source_output_repository.php';
require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/custom_proxy_repository.php';
require_once __DIR__ . '/compare_output_repository.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/legacy_db_access_reference.php';
require_once __DIR__ . '/legacy_dbtable_reference.php';
require_once __DIR__ . '/legacy_dataclass_reference.php';
require_once __DIR__ . '/legacy_html_reference.php';
require_once __DIR__ . '/legacy_source_output_registry.php';
require_once __DIR__ . '/project_html_repository.php';
require_once __DIR__ . '/project_language_resource_catalog_loader.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/table_metadata_repository.php';

function app_project_output_html_module_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'html-module-catalog';
}

function app_project_output_html_module_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_html_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @return list<string>
 */
function app_project_output_html_module_generated_entry_wrapper_targets(
    string $projectKey,
    string $sourceOutputKey,
): array {
    if (
        app_normalize_project_key($projectKey) !== 'MTOOL'
        || app_normalize_source_output_key($sourceOutputKey) !== 'HTML-DB'
    ) {
        return [];
    }

    return [
        'project_source_output.php',
        'project_source_output_edit.php',
        'project_source_output_change_order.php',
        'dbtables.php',
        'dbtables_import.php',
        'dbtables_import_for_each.php',
        'dbtable_columns.php',
        'dbtable_edit.php',
        'dbtable_column_edit.php',
        'dataclasses.php',
        'dataclasses_sync.php',
        'dataclass_fields.php',
        'dataclass_edit.php',
        'dataclass_field_edit.php',
        'da.php',
        'da_edit.php',
        'da_funcs.php',
        'da_funcs_change_order.php',
        'da_source.php',
        'da_sync.php',
        'da_func_edit.php',
        'da_func_move.php',
        'da_func_source.php',
        'da_func_endpoint.php',
        'da_func_sort_order_edit.php',
        'da_func_select_where.php',
        'da_func_select_where_input_aid.php',
        'da_func_select_where_change_order.php',
        'da_func_select_where_edit.php',
        'da_func_select_target_fields.php',
        'da_func_select_target_field_edit.php',
        'da_func_select_having.php',
        'da_func_select_having_edit.php',
        'da_func_update_delete_where.php',
        'da_func_update_delete_where_input_aid.php',
        'da_func_update_delete_where_change_order.php',
        'da_func_update_delete_where_edit.php',
        'da_func_insert_target_fields.php',
        'da_func_insert_target_field_edit.php',
        'da_func_update_target_fields.php',
        'da_func_update_target_field_edit.php',
        'da_proxy_custom.php',
        'da_proxy_custom_edit.php',
        'da_proxy_custom_endpoint.php',
        'da_proxy_custom_func.php',
        'da_proxy_custom_func_change_order.php',
        'da_proxy_custom_func_edit.php',
        'da_edit_proxy_single_target.php',
        'da_funcs_edit_proxy_single_target.php',
        'da_funcs_edit_proxy_single_setting.php',
        'da_funcs_edit_proxy_single_setting_edit.php',
        'compare_output.php',
        'compare_output_edit.php',
        'compare_output_additional_path.php',
        'compare_output_additional_path_edit.php',
        'build_project.php',
        'build_project_for_each.php',
        'build_project_ajax.php',
        'build_project_ajax_check_if_completed.php',
        'endpoint_test_json_ajax.php',
        'endpoint_common_include.php',
        'endpoint_lib_include.php',
        'endpoint_test_json_client_include.php',
        'compare_output_do.php',
        'compare_output_do_ajax.php',
        'htmls.php',
        'html_edit.php',
        'html_parameters.php',
        'html_parameter_edit.php',
        'lang_res.php',
        'lang_res_list.php',
        'lang_res_edit.php',
        'lang_res_edit_include.php',
        'lang_res_group_edit.php',
        'lang_res_group_edit_include.php',
        'lang_res_move.php',
        'lang_res_move_include.php',
        'lang_res_assign_additional_group.php',
        'lang_res_assign_additional_group_include.php',
        'lang_res_auto_translate_ajax.php',
        'lang_res_check_project_source_output_setting_lib.php',
        'lang_res_select_resource_group_lib.php',
    ];
}

function app_project_output_html_module_extract_legacy_pid(string $text, string $entityName): int
{
    if (
        preg_match(
            '/\b' . preg_quote($entityName, '/') . '\.PID\s*=\s*(\d+)/u',
            $text,
            $matches,
        ) !== 1
    ) {
        return 0;
    }

    return (int) ($matches[1] ?? 0);
}

function app_project_output_html_module_legacy_project_pid(array $app, string $projectKey): int
{
    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if ($projectResult['ok'] && is_array($projectResult['item'])) {
        $legacyPid = app_project_output_html_module_extract_legacy_pid(
            (string) ($projectResult['item']['description'] ?? ''),
            'Project',
        );
        if ($legacyPid > 0) {
            return $legacyPid;
        }
    }

    return app_normalize_project_key($projectKey) === 'MTOOL' ? 1 : 0;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_mtool_legacy_source_output_pid_fallback_map(): array
{
    return app_legacy_project_source_output_key_fallback_map('MTOOL');
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_source_output_pid_map(array $app, string $projectKey): array
{
    $map = [];
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if ($catalogResult['ok']) {
        foreach ($catalogResult['items'] as $item) {
            $legacyPid = app_project_output_html_module_extract_legacy_pid(
                (string) ($item['notes'] ?? ''),
                'ProjectSourceOutput',
            );
            if ($legacyPid <= 0) {
                continue;
            }

            $map[(string) $legacyPid] = (string) ($item['source_output_key'] ?? '');
        }
    }

    $bindingResult = app_fetch_project_html_source_bindings($app, $projectKey);
    if ($bindingResult['ok']) {
        foreach ($bindingResult['items'] as $binding) {
            $legacyPid = (int) ($binding['legacy_project_source_output_pid'] ?? 0);
            $sourceOutputKey = app_normalize_source_output_key((string) ($binding['source_output_key'] ?? ''));
            if ($legacyPid <= 0 || $sourceOutputKey === '') {
                continue;
            }

            $map[(string) $legacyPid] = $sourceOutputKey;
        }
    }

    if (app_normalize_project_key($projectKey) === 'MTOOL') {
        foreach (app_project_output_html_module_mtool_legacy_source_output_pid_fallback_map() as $legacyPid => $sourceOutputKey) {
            if (!array_key_exists($legacyPid, $map)) {
                $map[$legacyPid] = $sourceOutputKey;
            }
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_mtool_legacy_custom_proxy_pid_fallback_map(): array
{
    return [
        '9' => 'DB-IMPORT',
        '10' => 'DB-GETTABLEDEFINITION',
        '11' => 'DB-GETCOLUMNDEFINITION',
        '12' => 'DB-GETPROJECTLIST',
    ];
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_custom_proxy_pid_map(array $app, string $projectKey): array
{
    $map = [];
    $catalogResult = app_fetch_project_custom_proxy_catalog($app, $projectKey);
    if ($catalogResult['ok']) {
        foreach ($catalogResult['items'] as $item) {
            $legacyPid = app_project_output_html_module_extract_legacy_pid(
                (string) ($item['notes'] ?? ''),
                'daCustomProxy',
            );
            $customProxyKey = app_normalize_custom_proxy_key((string) ($item['custom_proxy_key'] ?? ''));
            if ($legacyPid <= 0 || $customProxyKey === '') {
                continue;
            }

            $map[(string) $legacyPid] = $customProxyKey;
        }
    }

    if (app_normalize_project_key($projectKey) === 'MTOOL') {
        foreach (app_project_output_html_module_mtool_legacy_custom_proxy_pid_fallback_map() as $legacyPid => $customProxyKey) {
            if (!array_key_exists($legacyPid, $map)) {
                $map[$legacyPid] = $customProxyKey;
            }
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,array{custom_proxy_key:string,step_id:string}>
 */
function app_project_output_html_module_legacy_custom_proxy_step_pid_map(array $app, string $projectKey): array
{
    $map = [];
    $catalogResult = app_fetch_project_custom_proxy_catalog($app, $projectKey);
    if ($catalogResult['ok']) {
        foreach ($catalogResult['items'] as $item) {
            $customProxyKey = app_normalize_custom_proxy_key((string) ($item['custom_proxy_key'] ?? ''));
            if ($customProxyKey === '') {
                continue;
            }

            $stepCatalogResult = app_fetch_project_custom_proxy_step_catalog($app, $projectKey, $customProxyKey);
            if (!$stepCatalogResult['ok']) {
                continue;
            }

            foreach ($stepCatalogResult['items'] as $step) {
                $legacyPid = app_project_output_html_module_extract_legacy_pid(
                    (string) ($step['notes'] ?? ''),
                    'daCustomProxyFunc',
                );
                $stepId = trim((string) ($step['id'] ?? ''));
                if ($legacyPid <= 0 || $stepId === '') {
                    continue;
                }

                $map[(string) $legacyPid] = [
                    'custom_proxy_key' => $customProxyKey,
                    'step_id' => $stepId,
                ];
            }
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_mtool_legacy_compare_output_pid_fallback_map(): array
{
    return [
        '1' => 'MAIN',
        '2' => 'CLIENTCOMMON',
    ];
}

/**
 * @return array<string,array{
 *     compare_output_key:string,
 *     additional_path_key:string
 * }>
 */
function app_project_output_html_module_mtool_legacy_compare_output_additional_path_pid_fallback_map(): array
{
    return [
        '6' => [
            'compare_output_key' => 'CLIENTCOMMON',
            'additional_path_key' => 'PROXYCLIENT',
        ],
        '9' => [
            'compare_output_key' => 'CLIENTCOMMON',
            'additional_path_key' => 'LANGRESOURCE',
        ],
    ];
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_compare_output_pid_map(array $app, string $projectKey): array
{
    $map = [];
    $catalogResult = app_fetch_project_compare_output_catalog($app, $projectKey);
    if ($catalogResult['ok']) {
        foreach ($catalogResult['items'] as $item) {
            $legacyPid = app_project_output_html_module_extract_legacy_pid(
                (string) ($item['notes'] ?? ''),
                'CompareOutput',
            );
            $compareOutputKey = app_normalize_compare_output_key((string) ($item['compare_output_key'] ?? ''));
            if ($legacyPid <= 0 || $compareOutputKey === '') {
                continue;
            }

            $map[(string) $legacyPid] = $compareOutputKey;
        }
    }

    if (app_normalize_project_key($projectKey) === 'MTOOL') {
        foreach (app_project_output_html_module_mtool_legacy_compare_output_pid_fallback_map() as $legacyPid => $compareOutputKey) {
            if (!array_key_exists($legacyPid, $map)) {
                $map[$legacyPid] = $compareOutputKey;
            }
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,array{
 *     compare_output_key:string,
 *     additional_path_key:string
 * }>
 */
function app_project_output_html_module_legacy_compare_output_additional_path_pid_map(
    array $app,
    string $projectKey,
): array {
    $map = [];
    $catalogResult = app_fetch_project_compare_output_catalog($app, $projectKey);
    if ($catalogResult['ok']) {
        foreach ($catalogResult['items'] as $item) {
            $compareOutputKey = app_normalize_compare_output_key((string) ($item['compare_output_key'] ?? ''));
            if ($compareOutputKey === '') {
                continue;
            }

            $additionalPathCatalogResult = app_fetch_project_compare_output_additional_path_catalog(
                $app,
                $projectKey,
                $compareOutputKey,
            );
            if (!$additionalPathCatalogResult['ok']) {
                continue;
            }

            foreach ($additionalPathCatalogResult['items'] as $additionalPath) {
                $legacyPid = app_project_output_html_module_extract_legacy_pid(
                    (string) ($additionalPath['notes'] ?? ''),
                    'CompareOutputAdditionalPath',
                );
                $additionalPathKey = app_normalize_compare_output_additional_path_key(
                    (string) ($additionalPath['additional_path_key'] ?? ''),
                );
                if ($legacyPid <= 0 || $additionalPathKey === '') {
                    continue;
                }

                $map[(string) $legacyPid] = [
                    'compare_output_key' => $compareOutputKey,
                    'additional_path_key' => $additionalPathKey,
                ];
            }
        }
    }

    if (app_normalize_project_key($projectKey) === 'MTOOL') {
        foreach (
            app_project_output_html_module_mtool_legacy_compare_output_additional_path_pid_fallback_map()
            as $legacyPid => $additionalPathReference
        ) {
            if (!array_key_exists($legacyPid, $map)) {
                $map[$legacyPid] = $additionalPathReference;
            }
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_table_pid_map(array $app, string $projectKey): array
{
    $referenceMap = app_legacy_dbtable_reference_current_table_pid_map($projectKey);
    if ($referenceMap !== []) {
        return $referenceMap;
    }

    $map = [];
    $snapshotResult = app_fetch_table_metadata_snapshot($app, $projectKey);
    if (!$snapshotResult['ok']) {
        return $map;
    }

    foreach ($snapshotResult['items'] as $item) {
        $legacyPid = trim((string) ($item['pid'] ?? ''));
        $tableName = trim((string) ($item['name'] ?? ''));
        if ($legacyPid === '' || $tableName === '') {
            continue;
        }

        $map[$legacyPid] = $tableName;
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_data_class_pid_map(array $app, string $projectKey): array
{
    $referenceMap = app_legacy_dataclass_reference_current_data_class_pid_map($projectKey);
    if ($referenceMap === []) {
        return [];
    }

    $availableNames = [];
    $catalogResult = app_generated_entity_catalog($app);
    foreach ($catalogResult['entities'] as $entity) {
        $availableNames[strtolower((string) ($entity['source_name'] ?? ''))] = true;
    }

    $snapshotResult = app_fetch_data_class_metadata_snapshot($app, $projectKey);
    if ($snapshotResult['ok']) {
        foreach ($snapshotResult['items'] as $item) {
            $availableNames[strtolower((string) ($item['name'] ?? ''))] = true;
        }
    }

    $map = [];
    foreach ($referenceMap as $legacyPid => $dataClassName) {
        if (!isset($availableNames[strtolower($dataClassName)])) {
            continue;
        }

        $map[$legacyPid] = $dataClassName;
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,list<array{
 *     source_output_key:string,
 *     release_target_type:string
 * }>>
 */
function app_project_output_html_module_custom_proxy_target_source_output_map(
    array $app,
    string $projectKey,
): array {
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [];
    }

    $sourceOutputByKey = [];
    foreach ($catalogResult['items'] as $sourceOutput) {
        if (!is_array($sourceOutput) || !app_source_output_supports_custom_proxy_targets($sourceOutput)) {
            continue;
        }

        if ((string) ($sourceOutput['artifact_strategy'] ?? '') !== 'custom-proxy-server') {
            continue;
        }

        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $sourceOutputByKey[$sourceOutputKey] = $sourceOutput;
    }

    $customProxyCatalogResult = app_fetch_project_custom_proxy_catalog($app, $projectKey);
    if (!$customProxyCatalogResult['ok']) {
        return [];
    }

    $map = [];
    foreach ($customProxyCatalogResult['items'] as $customProxy) {
        $customProxyKey = app_normalize_custom_proxy_key((string) ($customProxy['custom_proxy_key'] ?? ''));
        if ($customProxyKey === '') {
            continue;
        }

        $targetKeysResult = app_fetch_project_custom_proxy_target_keys($app, $projectKey, $customProxyKey);
        if (!$targetKeysResult['ok']) {
            continue;
        }

        $items = [];
        foreach ($targetKeysResult['items'] as $sourceOutputKey) {
            $normalizedSourceOutputKey = app_normalize_source_output_key((string) $sourceOutputKey);
            if ($normalizedSourceOutputKey === '' || !isset($sourceOutputByKey[$normalizedSourceOutputKey])) {
                continue;
            }

            $items[] = [
                'source_output_key' => $normalizedSourceOutputKey,
                'release_target_type' => (string) ($sourceOutputByKey[$normalizedSourceOutputKey]['release_target_type'] ?? ''),
            ];
        }

        if ($items !== []) {
            $map[$customProxyKey] = $items;
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_language_resource_pid_map(array $app, string $projectKey): array
{
    $catalogResult = app_fetch_project_language_resource_catalog($app, $projectKey);
    if (!$catalogResult['ok'] || !is_array($catalogResult['item'])) {
        return [];
    }

    $resources = $catalogResult['item']['resources'] ?? null;
    if (!is_array($resources)) {
        return [];
    }

    $map = [];
    foreach ($resources as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        $resourceKey = trim((string) ($resource['resource_key'] ?? ''));
        if ($legacyResourcePid <= 0 || $resourceKey === '') {
            continue;
        }

        $map[(string) $legacyResourcePid] = $resourceKey;
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,array{
 *     legacy_resource_pid:int,
 *     resource_key:string
 * }>
 */
function app_project_output_html_module_legacy_language_resource_key_name_map(
    array $app,
    string $projectKey,
): array {
    $catalogResult = app_fetch_project_language_resource_catalog($app, $projectKey);
    if (!$catalogResult['ok'] || !is_array($catalogResult['item'])) {
        return [];
    }

    $resources = $catalogResult['item']['resources'] ?? null;
    if (!is_array($resources)) {
        return [];
    }

    $map = [];
    foreach ($resources as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $keyName = trim((string) ($resource['key_name'] ?? ''));
        $resourceKey = trim((string) ($resource['resource_key'] ?? ''));
        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        if ($keyName === '' || $resourceKey === '' || $legacyResourcePid <= 0) {
            continue;
        }

        if (!array_key_exists($keyName, $map)) {
            $map[$keyName] = [
                'legacy_resource_pid' => $legacyResourcePid,
                'resource_key' => $resourceKey,
            ];
        }
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array{
 *     project_key:string,
 *     legacy_project_pid:int,
 *     legacy_source_output_pid_map:array<string,string>,
 *     legacy_language_resource_pid_map:array<string,string>,
 *     legacy_language_resource_key_name_map:array<string,array{
 *         legacy_resource_pid:int,
 *         resource_key:string
 *     }>,
 *     legacy_custom_proxy_pid_map:array<string,string>,
 *     legacy_custom_proxy_step_pid_map:array<string,array{
 *         custom_proxy_key:string,
 *         step_id:string
 *     }>,
 *     custom_proxy_target_source_output_map:array<string,list<array{
 *         source_output_key:string,
 *         release_target_type:string
 *     }>>,
 *     legacy_compare_output_pid_map:array<string,string>,
 *     legacy_compare_output_additional_path_pid_map:array<string,array{
 *         compare_output_key:string,
 *         additional_path_key:string
 *     }>,
 *     legacy_table_pid_map:array<string,string>,
 *     legacy_data_class_pid_map:array<string,string>,
 *     legacy_html_pid_map:array<string,string>,
 *     legacy_db_access_pid_map:array<string,string>,
 *     legacy_db_access_function_pid_map:array<string,array{
 *         source_name:string,
 *         function_name:string
 *     }>
 * }
 */
function app_project_output_html_module_generated_entry_wrapper_context(array $app, string $projectKey): array
{
    return [
        'project_key' => app_normalize_project_key($projectKey),
        'legacy_project_pid' => app_project_output_html_module_legacy_project_pid($app, $projectKey),
        'legacy_source_output_pid_map' => app_project_output_html_module_legacy_source_output_pid_map($app, $projectKey),
        'legacy_language_resource_pid_map' => app_project_output_html_module_legacy_language_resource_pid_map($app, $projectKey),
        'legacy_language_resource_key_name_map'
            => app_project_output_html_module_legacy_language_resource_key_name_map($app, $projectKey),
        'legacy_custom_proxy_pid_map' => app_project_output_html_module_legacy_custom_proxy_pid_map($app, $projectKey),
        'legacy_custom_proxy_step_pid_map' => app_project_output_html_module_legacy_custom_proxy_step_pid_map($app, $projectKey),
        'custom_proxy_target_source_output_map'
            => app_project_output_html_module_custom_proxy_target_source_output_map($app, $projectKey),
        'legacy_compare_output_pid_map' => app_project_output_html_module_legacy_compare_output_pid_map($app, $projectKey),
        'legacy_compare_output_additional_path_pid_map'
            => app_project_output_html_module_legacy_compare_output_additional_path_pid_map($app, $projectKey),
        'legacy_table_pid_map' => app_project_output_html_module_legacy_table_pid_map($app, $projectKey),
        'legacy_data_class_pid_map' => app_project_output_html_module_legacy_data_class_pid_map($app, $projectKey),
        'legacy_html_pid_map' => app_project_output_html_module_legacy_html_pid_map($app, $projectKey),
        'legacy_db_access_pid_map' => app_project_output_html_module_legacy_db_access_pid_map($app, $projectKey),
        'legacy_db_access_function_pid_map' => app_project_output_html_module_legacy_db_access_function_pid_map($app, $projectKey),
    ];
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_html_pid_map(array $app, string $projectKey): array
{
    $reference = app_load_legacy_html_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $catalogResult = app_fetch_project_html_catalog(
        $app,
        $projectKey,
        (int) ($reference['item']['project_pid'] ?? 0),
    );
    if (!$catalogResult['ok']) {
        return app_legacy_html_reference_current_html_pid_map($projectKey);
    }

    $map = [];
    foreach ($catalogResult['items'] as $item) {
        $legacyHtmlPid = (int) ($item['legacy_html_pid'] ?? 0);
        $htmlKey = trim((string) ($item['html_key'] ?? ''));
        if ($legacyHtmlPid <= 0 || $htmlKey === '') {
            continue;
        }

        $map[(string) $legacyHtmlPid] = $htmlKey;
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,string>
 */
function app_project_output_html_module_legacy_db_access_pid_map(array $app, string $projectKey): array
{
    $referenceMap = app_legacy_db_access_reference_current_da_pid_map($projectKey);
    if ($referenceMap === []) {
        return [];
    }

    $availableSourceNames = [];
    $catalog = app_generated_entity_catalog($app);
    foreach ($catalog['entities'] as $entity) {
        $sourceName = trim((string) ($entity['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }

        $availableSourceNames[strtolower($sourceName)] = $sourceName;
    }

    $map = [];
    foreach ($referenceMap as $legacyDaPid => $sourceName) {
        $sourceNameKey = strtolower($sourceName);
        if (!array_key_exists($sourceNameKey, $availableSourceNames)) {
            continue;
        }

        $map[$legacyDaPid] = $availableSourceNames[$sourceNameKey];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,array{
 *     source_name:string,
 *     function_name:string
 * }>
 */
function app_project_output_html_module_legacy_db_access_function_pid_map(array $app, string $projectKey): array
{
    $referenceMap = app_legacy_db_access_reference_current_function_pid_map($projectKey);
    if ($referenceMap === []) {
        return [];
    }

    $availableMethodsBySource = [];
    $catalog = app_generated_entity_catalog($app);
    foreach ($catalog['entities'] as $entity) {
        $sourceName = trim((string) ($entity['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }

        $methodMap = [];
        foreach (app_generated_file_method_catalog((string) ($entity['dbaccess_path'] ?? '')) as $method) {
            $methodName = trim((string) ($method['name'] ?? ''));
            if ($methodName === '') {
                continue;
            }

            $methodMap[strtolower($methodName)] = $methodName;
        }

        $availableMethodsBySource[strtolower($sourceName)] = [
            'source_name' => $sourceName,
            'methods' => $methodMap,
        ];
    }

    $map = [];
    foreach ($referenceMap as $legacyFunctionPid => $functionReference) {
        $sourceName = trim((string) ($functionReference['source_name'] ?? ''));
        $functionName = trim((string) ($functionReference['function_name'] ?? ''));
        if ($sourceName === '' || $functionName === '') {
            continue;
        }

        $sourceNameKey = strtolower($sourceName);
        $functionNameKey = strtolower($functionName);
        if (!array_key_exists($sourceNameKey, $availableMethodsBySource)) {
            continue;
        }

        $sourceInfo = $availableMethodsBySource[$sourceNameKey];
        $availableMethods = $sourceInfo['methods'];
        if (!array_key_exists($functionNameKey, $availableMethods)) {
            continue;
        }

        $map[$legacyFunctionPid] = [
            'source_name' => $sourceInfo['source_name'],
            'function_name' => $availableMethods[$functionNameKey],
        ];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

function app_project_output_html_module_generated_source_output_list_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_source_output_list_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `project_source_output.php`.
// The legacy source-output list now reduces directly to the current route.
// Unsupported verbs and project mismatches also hand off there.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};

if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedTargetPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacySourceOutputPidMap
 */
function app_project_output_html_module_generated_source_output_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacySourceOutputPidMap,
): string {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_source_output_list_path($projectKey),
        true,
    );
    $exportedCreatePath = var_export(
        app_project_output_html_module_default_source_output_new_path($projectKey),
        true,
    );
    $exportedPidMap = var_export($legacySourceOutputPidMap, true);
    $exportedNormalizedProjectKey = var_export($normalizedProjectKey, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `project_source_output_edit.php`.
// Known legacy ProjectSourceOutputPID edit flows are bridged into the current source-output edit route.
// Blank add-flow GET/POST is handed off to the current advanced create route with bridge-prefilled values.

function app_html_db_source_output_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_source_output_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_source_output_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_source_output_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_source_output_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$sourceOutputListPath = {$exportedListPath};
\$sourceOutputCreatePath = {$exportedCreatePath};
\$legacySourceOutputPidMap = {$exportedPidMap};
\$legacySourceOutputPid = app_html_db_source_output_edit_wrapper_param(
    \$legacyParams,
    'ProjectSourceOutputPID',
);
\$sourceOutputKey = '';
\$targetPath = \$sourceOutputCreatePath;

if (\$legacySourceOutputPid !== '' && array_key_exists(\$legacySourceOutputPid, \$legacySourceOutputPidMap)) {
    \$sourceOutputKey = \$legacySourceOutputPidMap[\$legacySourceOutputPid];
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/source-outputs/'
        . rawurlencode(\$sourceOutputKey)
        . '/edit';
}

\$projectPid = app_html_db_source_output_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$targetPath);
        exit;
    }

    app_html_db_source_output_edit_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            'legacy ProjectPID が current project route と一致しません。current source output page からやり直してください。',
        ],
    );
}

if (\$requestMethod === 'POST') {
    \$bridgeErrors = [];
    if (\$legacySourceOutputPid !== '' && \$sourceOutputKey === '') {
        \$bridgeErrors[] = '更新対象の legacy source output pid は current route に解決できませんでした。';
    }

    \$update = app_html_db_source_output_edit_wrapper_param(\$_POST, 'UPDATE');
    \$delete = app_html_db_source_output_edit_wrapper_param(\$_POST, 'DELETE');
    if (\$update === '' && \$delete === '') {
        app_html_db_source_output_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            array_merge(
                \$bridgeErrors,
                [
                    'legacy source output edit POST に UPDATE / DELETE が無いため、current source output page へ handoff します。',
                ],
            ),
        );
    }

    \$appRoot = app_html_db_source_output_edit_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_source_output_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            array_merge(
                \$bridgeErrors,
                [
                    'current source output save を継続する shared bootstrap が見つかりません。current source output page から再実行してください。',
                ],
            ),
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$legacyProgramLanguage = app_html_db_source_output_edit_wrapper_param(\$_POST, 'ProgramLanguage');
    \$legacyClassType = app_html_db_source_output_edit_wrapper_param(\$_POST, 'ClassType');
    \$legacyReleaseTargetType = app_html_db_source_output_edit_wrapper_param(\$_POST, 'ReleaseTargetType');
    \$legacySourceTemplateDir = app_html_db_source_output_edit_wrapper_param(\$_POST, 'SourceTemplateDir');
    \$legacySourceOutputDir = app_html_db_source_output_edit_wrapper_param(\$_POST, 'SourceOutputDir');
    \$legacySourceTempOutputDir = app_html_db_source_output_edit_wrapper_param(\$_POST, 'SourceTempOutputDir');
    \$legacyProxyBaseUrl = app_html_db_source_output_edit_wrapper_param(\$_POST, 'ProxyBaseURL');
    \$legacyAutoloadFilenameSuffix = app_html_db_source_output_edit_wrapper_param(\$_POST, 'AutoloadFilenameSuffix');
    \$legacySourceTextCharCode = app_html_db_source_output_edit_wrapper_param(\$_POST, 'SourceTextCharCode');
    \$legacyOnlyFieldValues = [];
    foreach (
        [
            'CustomFileExtention',
            'DropboxBaseFolderPID',
            'UnitTestTemplateDir',
            'UnitTestOutputDir',
            'TargetServerProjectSourceOutputPID',
            'CSNameSpace',
            'JavaPackageName',
            'AutoLoadFilePathForPHP',
            'JavaFunctionType',
            'DotNetLanguageResourceType',
        ] as \$legacyOnlyFieldName
    ) {
        \$legacyOnlyFieldValue = app_html_db_source_output_edit_wrapper_param(\$_POST, \$legacyOnlyFieldName);
        if (\$legacyOnlyFieldValue === '') {
            continue;
        }

        \$legacyOnlyFieldValues[\$legacyOnlyFieldName] = \$legacyOnlyFieldValue;
    }
    \$legacyMetadataPostFields = [];
    if (\$legacySourceOutputPid !== '') {
        \$legacyMetadataPostFields['legacy_project_source_output_pid'] = \$legacySourceOutputPid;
    }
    foreach (\$legacyOnlyFieldValues as \$legacyOnlyFieldName => \$legacyOnlyFieldValue) {
        \$legacyMetadataPostFields['legacy_only_' . \$legacyOnlyFieldName] = \$legacyOnlyFieldValue;
    }

    if (\$legacySourceOutputPid === '') {
        \$legacyTargetServerProjectSourceOutputPid = app_html_db_source_output_edit_wrapper_param(
            \$_POST,
            'TargetServerProjectSourceOutputPID',
        );
        \$legacyTargetServerSourceOutputKey = '';
        if (\$legacyTargetServerProjectSourceOutputPid !== '') {
            if (array_key_exists(\$legacyTargetServerProjectSourceOutputPid, \$legacySourceOutputPidMap)) {
                \$legacyTargetServerSourceOutputKey = \$legacySourceOutputPidMap[\$legacyTargetServerProjectSourceOutputPid];
            } else {
                \$bridgeErrors[] = 'legacy TargetServerProjectSourceOutputPID は current source output に解決できませんでした。';
            }
        }

        \$legacyOnlyFieldNames = array_keys(\$legacyOnlyFieldValues);

        \$bridgeErrors[] = 'legacy source output add flow は current advanced create page へ handoff しました。source_output_key / name を補い、prefill 済み strategy / binding を確認して保存してください。';
        if (\$legacyOnlyFieldNames !== []) {
            \$bridgeErrors[] = 'まだ current schema へ移していない legacy-only fields があります: ' . implode(', ', \$legacyOnlyFieldNames);
        }

        \$_SERVER['REQUEST_METHOD'] = 'POST';
        \$_SERVER['REQUEST_URI'] = \$sourceOutputCreatePath;
        \$_SERVER['QUERY_STRING'] = '';
        \$_GET = [];
        \$_POST = [
            '_csrf' => app_csrf_token(),
            'action' => 'bridge-prefill-source-output',
            'source_output_key' => '',
            'name' => '',
            'program_language' => \$legacyProgramLanguage,
            'class_type' => \$legacyClassType,
            'release_target_type' => \$legacyReleaseTargetType,
            'source_template_dir' => \$legacySourceTemplateDir,
            'source_output_dir' => \$legacySourceOutputDir,
            'source_temp_output_dir' => \$legacySourceTempOutputDir,
            'proxy_base_url' => \$legacyProxyBaseUrl,
            'autoload_filename_suffix' => \$legacyAutoloadFilenameSuffix,
            'source_text_char_code' => \$legacySourceTextCharCode,
            'legacy_target_server_source_output_key' => \$legacyTargetServerSourceOutputKey,
            'legacy_source_output_dir' => \$legacySourceOutputDir,
            'legacy_source_template_dir' => \$legacySourceTemplateDir,
            'source_of_truth' => 'manual',
        ];
        foreach (\$legacyMetadataPostFields as \$legacyMetadataFieldName => \$legacyMetadataFieldValue) {
            if (\$legacyMetadataFieldValue === '') {
                continue;
            }

            \$_POST[\$legacyMetadataFieldName] = \$legacyMetadataFieldValue;
        }
        if (\$bridgeErrors !== []) {
            \$_POST['bridge_errors'] = \$bridgeErrors;
        }

        require_once \$appRoot . '/http.php';
        app_run_http_request();
        return;
    }

    if (\$sourceOutputKey === '') {
        app_html_db_source_output_edit_wrapper_redirect_with_bridge_errors(
            \$sourceOutputListPath,
            \$bridgeErrors,
        );
    }

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = \$targetPath;
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];

    \$_POST = [
        '_csrf' => app_csrf_token(),
        'action' => \$delete !== '' ? 'delete-source-output' : 'save-source-output',
        'source_output_key' => \$sourceOutputKey,
    ];

    if (\$delete === '') {
        \$_POST['program_language'] = \$legacyProgramLanguage;
        \$_POST['class_type'] = \$legacyClassType;
        \$_POST['release_target_type'] = \$legacyReleaseTargetType;
        \$_POST['source_template_dir'] = \$legacySourceTemplateDir;
        \$_POST['source_output_dir'] = \$legacySourceOutputDir;
        \$_POST['source_temp_output_dir'] = \$legacySourceTempOutputDir;
        \$_POST['proxy_base_url'] = \$legacyProxyBaseUrl;
        \$_POST['autoload_filename_suffix'] = \$legacyAutoloadFilenameSuffix;
        \$_POST['source_text_char_code'] = \$legacySourceTextCharCode;
        foreach (\$legacyMetadataPostFields as \$legacyMetadataFieldName => \$legacyMetadataFieldValue) {
            if (\$legacyMetadataFieldValue === '') {
                continue;
            }

            \$_POST[\$legacyMetadataFieldName] = \$legacyMetadataFieldValue;
        }
    }

    if (\$bridgeErrors !== []) {
        \$_POST['bridge_errors'] = \$bridgeErrors;
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_source_output_edit_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current source output page からやり直してください。',
        ],
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

function app_project_output_html_module_default_source_output_change_order_path(string $projectKey): string
{
    return app_project_output_html_module_default_source_output_list_path($projectKey) . '/change-order';
}

function app_project_output_html_module_default_table_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/tables';
}

function app_project_output_html_module_default_table_import_path(string $projectKey): string
{
    return app_project_output_html_module_default_table_list_path($projectKey) . '/import';
}

function app_project_output_html_module_default_data_class_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/data-classes';
}

function app_project_output_html_module_default_data_class_sync_path(string $projectKey): string
{
    return app_project_output_html_module_default_data_class_list_path($projectKey) . '/sync';
}

function app_project_output_html_module_default_html_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/html';
}

function app_project_output_html_module_default_html_detail_path(string $projectKey, string $htmlKey): string
{
    return app_project_output_html_module_default_html_list_path($projectKey)
        . '/' . rawurlencode($htmlKey);
}

function app_project_output_html_module_default_html_parameters_path(string $projectKey, string $htmlKey): string
{
    return app_project_output_html_module_default_html_detail_path($projectKey, $htmlKey) . '/parameters';
}

function app_project_output_html_module_default_language_resource_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/language-resources';
}

function app_project_output_html_module_default_language_resource_groups_path(string $projectKey): string
{
    return app_project_output_html_module_default_language_resource_list_path($projectKey) . '/groups';
}

function app_project_output_html_module_default_language_resource_detail_path(
    string $projectKey,
    string $resourceKey,
): string {
    return app_project_output_html_module_default_language_resource_list_path($projectKey)
        . '/' . rawurlencode($resourceKey);
}

function app_project_output_html_module_default_db_access_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/db-access';
}

function app_project_output_html_module_default_db_access_sync_path(string $projectKey): string
{
    return app_project_output_html_module_default_db_access_list_path($projectKey) . '/sync';
}

function app_project_output_html_module_default_custom_proxy_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/proxy/custom';
}

function app_project_output_html_module_default_single_proxy_path(
    string $projectKey,
    string $dbAccessKey = '',
): string {
    $path = '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/proxy/single';
    if ($dbAccessKey !== '') {
        $path .= '?db_access_key=' . rawurlencode($dbAccessKey);
    }

    return $path;
}

function app_project_output_html_module_default_custom_proxy_endpoint_path(
    string $projectKey,
    string $customProxyKey,
    string $sourceOutputKey = '',
): string {
    $path = '/projects/' . rawurlencode(app_normalize_project_key($projectKey))
        . '/proxy/custom/'
        . rawurlencode(app_normalize_custom_proxy_key($customProxyKey))
        . '/endpoint';
    if ($sourceOutputKey !== '') {
        $path .= '?source_output_key=' . rawurlencode($sourceOutputKey);
    }

    return $path;
}

function app_project_output_html_module_default_compare_output_settings_path(
    string $projectKey,
    string $compareOutputKey = '',
): string {
    $path = '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/compare-output-settings';
    if ($compareOutputKey !== '') {
        $path .= '?compare_output_key=' . rawurlencode($compareOutputKey);
    }

    return $path;
}

function app_project_output_html_module_default_compare_output_additional_paths_path(
    string $projectKey,
    string $compareOutputKey,
    string $additionalPathKey = '',
): string {
    $path = '/projects/' . rawurlencode(app_normalize_project_key($projectKey))
        . '/compare-output-settings/additional-paths'
        . '?compare_output_key=' . rawurlencode($compareOutputKey);
    if ($additionalPathKey !== '') {
        $path .= '&additional_path_key=' . rawurlencode($additionalPathKey);
    }

    return $path;
}

function app_project_output_html_module_default_compare_output_run_path(string $projectKey): string
{
    return '/runs/compare-output/' . rawurlencode(app_normalize_project_key($projectKey));
}

function app_project_output_html_module_default_endpoint_test_path(string $projectKey): string
{
    return '/runs/endpoints/' . rawurlencode(app_normalize_project_key($projectKey));
}

function app_project_output_html_module_default_build_run_path(
    string $projectKey,
    bool $detailedView = false,
): string {
    $path = '/runs/builds/' . rawurlencode(app_normalize_project_key($projectKey));
    if ($detailedView) {
        $path .= '?view=detailed';
    }

    return $path;
}

function app_project_output_html_module_generated_source_output_change_order_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacySourceOutputPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_source_output_change_order_path($projectKey),
        true,
    );
    $exportedPidMap = var_export($legacySourceOutputPidMap, true);
    $exportedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `project_source_output_change_order.php`.
// GET/HEAD requests redirect to the current change-order route.
// Legacy reorder/reset POST is translated into current `source_output_keys[]` / `source_output_orders[]` submissions.

function app_html_db_source_output_change_order_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_source_output_change_order_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_source_output_change_order_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_source_output_change_order_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$targetPath = {$exportedTargetPath};
\$projectPid = app_html_db_source_output_change_order_wrapper_param(
    \$requestMethod === 'POST' ? \$_POST : \$_GET,
    'ProjectPID',
);
\$expectedProjectPid = {$exportedLegacyProjectPid};

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$requestMethod !== 'POST') {
    app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current source output order page からやり直してください。',
        ],
    );
}

if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            'legacy ProjectPID が current project route と一致しません。current source output order page からやり直してください。',
        ],
    );
}

\$appRoot = app_html_db_source_output_change_order_wrapper_app_root();
if (\$appRoot === '') {
    app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            'current source output change-order を継続する shared bootstrap が見つかりません。current source output order page から再実行してください。',
        ],
    );
}

require_once \$appRoot . '/bootstrap.php';
require_once \$appRoot . '/session.php';
require_once \$appRoot . '/csrf.php';
require_once \$appRoot . '/source_output_repository.php';

\$app = app_bootstrap();
app_boot_session(\$app);

\$formAction = app_html_db_source_output_change_order_wrapper_param(\$_POST, 'doReset') !== ''
    ? 'reset'
    : 'save';
\$translatedPost = [
    '_csrf' => app_csrf_token(),
];

if (\$formAction === 'reset') {
    \$translatedPost['form_action'] = 'reset';
} else {
    \$catalogResult = app_fetch_project_source_output_catalog(\$app, {$exportedProjectKey});
    if (!\$catalogResult['ok']) {
        app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                trim((string) (\$catalogResult['error'] ?? '')) !== ''
                    ? trim((string) \$catalogResult['error'])
                    : 'current source output catalog の読み込みに失敗しました。',
            ],
        );
    }

    \$catalogItems = is_array(\$catalogResult['items'] ?? null) ? \$catalogResult['items'] : [];
    \$catalogKeys = [];
    foreach (\$catalogItems as \$item) {
        if (!is_array(\$item)) {
            continue;
        }

        \$sourceOutputKey = trim((string) (\$item['source_output_key'] ?? ''));
        if (\$sourceOutputKey === '') {
            continue;
        }

        \$catalogKeys[\$sourceOutputKey] = \$sourceOutputKey;
    }

    \$newSortOrder = app_html_db_source_output_change_order_wrapper_param(\$_POST, 'NewSortOrder');
    if (\$newSortOrder === '') {
        app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy source output change-order POST に NewSortOrder が無いため、current page へ handoff します。',
            ],
        );
    }

    \$orderedKeys = [];
    \$unknownLegacyPids = [];
    foreach (preg_split('/,+/', \$newSortOrder) ?: [] as \$rawLegacyPid) {
        \$legacyPid = trim((string) \$rawLegacyPid);
        if (\$legacyPid === '') {
            continue;
        }

        \$legacySourceOutputPidMap = {$exportedPidMap};
        if (!array_key_exists(\$legacyPid, \$legacySourceOutputPidMap)) {
            \$unknownLegacyPids[] = \$legacyPid;
            continue;
        }

        \$mappedKey = trim((string) \$legacySourceOutputPidMap[\$legacyPid]);
        if (\$mappedKey === '' || !array_key_exists(\$mappedKey, \$catalogKeys)) {
            \$unknownLegacyPids[] = \$legacyPid;
            continue;
        }

        \$orderedKeys[\$mappedKey] = \$mappedKey;
    }

    if (\$unknownLegacyPids !== []) {
        app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy source output order に current route へ解決できない PID が含まれています: '
                . implode(', ', \$unknownLegacyPids),
            ],
        );
    }

    foreach (\$catalogItems as \$item) {
        if (!is_array(\$item)) {
            continue;
        }

        \$sourceOutputKey = trim((string) (\$item['source_output_key'] ?? ''));
        if (\$sourceOutputKey === '' || array_key_exists(\$sourceOutputKey, \$orderedKeys)) {
            continue;
        }

        \$orderedKeys[\$sourceOutputKey] = \$sourceOutputKey;
    }

    if (\$orderedKeys === []) {
        app_html_db_source_output_change_order_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy source output order を current source output key へ変換できませんでした。',
            ],
        );
    }

    \$translatedPost['source_output_keys'] = [];
    \$translatedPost['source_output_orders'] = [];
    \$position = 10;
    foreach (array_values(\$orderedKeys) as \$sourceOutputKey) {
        \$translatedPost['source_output_keys'][] = \$sourceOutputKey;
        \$translatedPost['source_output_orders'][] = (string) \$position;
        \$position += 10;
    }
}

\$_SERVER['REQUEST_METHOD'] = 'POST';
\$_SERVER['REQUEST_URI'] = \$targetPath;
\$_SERVER['QUERY_STRING'] = '';
\$_GET = [];
\$_POST = \$translatedPost;

require_once \$appRoot . '/http.php';
app_run_http_request();
return;

PHP;
}

/**
 * @param array<string,string> $legacyTablePidMap
 */
function app_project_output_html_module_generated_tables_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyTablePidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_table_list_path($projectKey),
        true,
    );
    $exportedTablePidMap = var_export($legacyTablePidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dbtables.php`.
// The current system exposes table metadata under `/projects/{project_key}/tables`.
// When a legacy DBTablePID can be mapped to a canonical table name, redirect to its detail page.
// Unsupported verbs, project mismatches, and unknown filter targets reduce to the current table list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyTablePidMap = {$exportedTablePidMap};
\$filterTablePid = isset(\$legacyParams['filterdbtablePID']) ? trim((string) \$legacyParams['filterdbtablePID']) : '';

if (\$filterTablePid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$filterTablePid, \$legacyTablePidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/tables/'
        . rawurlencode(\$legacyTablePidMap[\$filterTablePid]);
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

function app_project_output_html_module_generated_tables_import_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedImportPath = var_export(
        app_project_output_html_module_default_table_import_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dbtables_import.php', true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dbtables_import.php`.
// The current system manages table metadata import from `/projects/{project_key}/tables/import`.
// Legacy bulk-import action parameters still fall back to `_legacy/`.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$doImportAllTable = isset(\$_GET['DoImportAllTable']) ? trim((string) \$_GET['DoImportAllTable']) : '';
if (\$doImportAllTable !== '') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedImportPath});
exit;

PHP;
}

function app_project_output_html_module_generated_tables_import_for_each_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedImportPath = var_export(
        app_project_output_html_module_default_table_import_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dbtables_import_for_each.php', true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dbtables_import_for_each.php`.
// Pure GET preview requests are redirected to the focused current import page.
// Legacy GET actions (`DoImport*`, `FieldName`, `IncludeOrder`) still fall back to `_legacy/`.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$fieldName = isset(\$_GET['FieldName']) ? trim((string) \$_GET['FieldName']) : '';
\$doImport = isset(\$_GET['DoImport']) ? trim((string) \$_GET['DoImport']) : '';
\$doImportAll = isset(\$_GET['DoImportAll']) ? trim((string) \$_GET['DoImportAll']) : '';
\$includeOrder = isset(\$_GET['IncludeOrder']) ? trim((string) \$_GET['IncludeOrder']) : '';
if (\$fieldName !== '' || \$doImport !== '' || \$doImportAll !== '' || \$includeOrder !== '') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$tableName = isset(\$_GET['TableName']) ? trim((string) \$_GET['TableName']) : '';
\$targetPath = {$exportedImportPath};
if (\$tableName !== '') {
    \$targetPath .= '?table=' . rawurlencode(\$tableName);
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyTablePidMap
 */
function app_project_output_html_module_generated_table_columns_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyTablePidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_table_list_path($projectKey),
        true,
    );
    $exportedTablePidMap = var_export($legacyTablePidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dbtable_columns.php`.
// Known legacy DBTablePID values are redirected to the current table columns route.
// Unsupported verbs, project mismatches, and unknown table IDs reduce to the current table list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyTablePidMap = {$exportedTablePidMap};
\$legacyTablePid = isset(\$legacyParams['DBTablePID']) ? trim((string) \$legacyParams['DBTablePID']) : '';

if (\$legacyTablePid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$legacyTablePid, \$legacyTablePidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/tables/'
        . rawurlencode(\$legacyTablePidMap[\$legacyTablePid])
        . '/columns';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyTablePidMap
 */
function app_project_output_html_module_generated_table_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyTablePidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_table_list_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dbtable_edit.php', true);
    $exportedTablePidMap = var_export($legacyTablePidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dbtable_edit.php`.
// Current table metadata now exposes a dedicated edit route, so redirect there when the legacy DBTablePID can be mapped.
// Legacy POST/save flows remain on `_legacy/`, but GET mismatches now reduce to the current table list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyTablePidMap = {$exportedTablePidMap};
\$legacyTablePid = isset(\$_GET['DBTablePID']) ? trim((string) \$_GET['DBTablePID']) : '';

if (\$legacyTablePid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$legacyTablePid, \$legacyTablePidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/tables/'
        . rawurlencode(\$legacyTablePidMap[\$legacyTablePid])
        . '/edit';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyTablePidMap
 */
function app_project_output_html_module_generated_table_column_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyTablePidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_table_list_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dbtable_column_edit.php', true);
    $exportedTablePidMap = var_export($legacyTablePidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dbtable_column_edit.php`.
// Current table metadata now exposes a dedicated new-column route, but legacy DBTableColumnPID values cannot be mapped 1:1.
// Legacy POST/save flows remain on `_legacy/`, while GET mismatches reduce to the current table list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyTablePidMap = {$exportedTablePidMap};
\$legacyTablePid = isset(\$_GET['DBTablePID']) ? trim((string) \$_GET['DBTablePID']) : '';
\$legacyTableColumnPid = isset(\$_GET['DBTableColumnPID']) ? trim((string) \$_GET['DBTableColumnPID']) : '';

if (\$legacyTablePid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$legacyTablePid, \$legacyTablePidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/tables/'
        . rawurlencode(\$legacyTablePidMap[\$legacyTablePid]);
    if (\$legacyTableColumnPid === '') {
        \$targetPath .= '/columns/new';
    } else {
        \$targetPath .= '/columns';
    }
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDataClassPidMap
 */
function app_project_output_html_module_generated_data_classes_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDataClassPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_data_class_list_path($projectKey),
        true,
    );
    $exportedDataClassPidMap = var_export($legacyDataClassPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dataclasses.php`.
// Known legacy DataClassPID values are redirected to the current data-class routes.
// Unsupported verbs, project mismatches, and unknown filter targets reduce to the current data-class list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyDataClassPidMap = {$exportedDataClassPidMap};
\$filterDataClassPid = isset(\$legacyParams['filterdataclassPID']) ? trim((string) \$legacyParams['filterdataclassPID']) : '';

if (\$filterDataClassPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$filterDataClassPid, \$legacyDataClassPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/data-classes/'
        . rawurlencode(\$legacyDataClassPidMap[\$filterDataClassPid]);
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

function app_project_output_html_module_generated_data_classes_sync_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedSyncPath = var_export(
        app_project_output_html_module_default_data_class_sync_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dataclasses_sync.php', true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dataclasses_sync.php`.
// Preview-only GET requests are redirected to the current sync page.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$doSyncAllClasses = isset(\$_GET['DoSyncAllClasses']) ? trim((string) \$_GET['DoSyncAllClasses']) : '';
if (\$doSyncAllClasses !== '') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedSyncPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDataClassPidMap
 */
function app_project_output_html_module_generated_data_class_fields_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDataClassPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_data_class_list_path($projectKey),
        true,
    );
    $exportedDataClassPidMap = var_export($legacyDataClassPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dataclass_fields.php`.
// Known legacy DataClassPID values are redirected to the current field list route.
// Unsupported verbs, project mismatches, and unknown class IDs reduce to the current data-class list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyDataClassPidMap = {$exportedDataClassPidMap};
\$legacyDataClassPid = isset(\$legacyParams['DataClassPID']) ? trim((string) \$legacyParams['DataClassPID']) : '';

if (\$legacyDataClassPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$legacyDataClassPid, \$legacyDataClassPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/data-classes/'
        . rawurlencode(\$legacyDataClassPidMap[\$legacyDataClassPid])
        . '/fields';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDataClassPidMap
 */
function app_project_output_html_module_generated_data_class_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDataClassPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_data_class_list_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dataclass_edit.php', true);
    $exportedDataClassPidMap = var_export($legacyDataClassPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dataclass_edit.php`.
// Known legacy DataClassPID values are redirected to the current edit route.
// Blank add flows and POST/save semantics remain on `_legacy/`, while invalid GET deep links reduce to the current data-class list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyDataClassPidMap = {$exportedDataClassPidMap};
\$legacyDataClassPid = isset(\$_GET['DataClassPID']) ? trim((string) \$_GET['DataClassPID']) : '';
\$dataClassName = isset(\$_GET['name']) ? trim((string) \$_GET['name']) : '';

if (\$legacyDataClassPid !== '' && array_key_exists(\$legacyDataClassPid, \$legacyDataClassPidMap)) {
    \$dataClassName = \$legacyDataClassPidMap[\$legacyDataClassPid];
}

if (\$dataClassName !== '') {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/data-classes/'
        . rawurlencode(\$dataClassName)
        . '/edit';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$legacyDataClassPid !== '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

require_once __DIR__ . '/' . {$exportedLegacyFallback};

PHP;
}

/**
 * @param array<string,string> $legacyDataClassPidMap
 */
function app_project_output_html_module_generated_data_class_field_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDataClassPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_data_class_list_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/dataclass_field_edit.php', true);
    $exportedDataClassPidMap = var_export($legacyDataClassPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `dataclass_field_edit.php`.
// Current data-class metadata now exposes a dedicated new-field route, but legacy DataClassFieldPID values are redirected to the field list.
// Legacy POST/save flows remain on `_legacy/`, while invalid GET deep links reduce to the current data-class list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyDataClassPidMap = {$exportedDataClassPidMap};
\$legacyDataClassPid = isset(\$_GET['DataClassPID']) ? trim((string) \$_GET['DataClassPID']) : '';
\$legacyDataClassFieldPid = isset(\$_GET['DataClassFieldPID']) ? trim((string) \$_GET['DataClassFieldPID']) : '';

if (\$legacyDataClassPid !== '' && array_key_exists(\$legacyDataClassPid, \$legacyDataClassPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/data-classes/'
        . rawurlencode(\$legacyDataClassPidMap[\$legacyDataClassPid]);
    if (\$legacyDataClassFieldPid === '') {
        \$targetPath .= '/fields/new';
    } else {
        \$targetPath .= '/fields';
    }
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyHtmlPidMap
 */
function app_project_output_html_module_generated_html_list_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyHtmlPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_html_list_path($projectKey),
        true,
    );
    $exportedHtmlPidMap = var_export($legacyHtmlPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `htmls.php`.
// Known legacy html filters are redirected to the current detail route.
// Unsupported verbs, project mismatches, and unknown filters reduce to the current html list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyHtmlPidMap = {$exportedHtmlPidMap};
\$filterHtmlPid = isset(\$legacyParams['filterhtmlPID']) ? trim((string) \$legacyParams['filterhtmlPID']) : '';

if (\$filterHtmlPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$filterHtmlPid, \$legacyHtmlPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/html/'
        . rawurlencode(\$legacyHtmlPidMap[\$filterHtmlPid]);
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyHtmlPidMap
 */
function app_project_output_html_module_generated_html_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyHtmlPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedHtmlListPath = var_export(
        app_project_output_html_module_default_html_list_path($projectKey),
        true,
    );
    $exportedHtmlPidMap = var_export($legacyHtmlPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `html_edit.php`.
// GET add/edit deep links land on the current html list/detail pages.
// Legacy create/update/delete POST is translated into current html route actions.

function app_html_db_html_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_html_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_html_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_html_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header('Location: ' . app_html_db_html_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors));
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$htmlListPath = {$exportedHtmlListPath};
\$legacyHtmlPidMap = {$exportedHtmlPidMap};
\$legacyHtmlPid = app_html_db_html_edit_wrapper_param(\$legacyParams, 'htmlPID');
\$htmlKey = '';
\$targetPath = \$htmlListPath . '?intent=create';

if (\$legacyHtmlPid !== '') {
    if (array_key_exists(\$legacyHtmlPid, \$legacyHtmlPidMap)) {
        \$htmlKey = \$legacyHtmlPidMap[\$legacyHtmlPid];
        \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
            . '/html/'
            . rawurlencode(\$htmlKey)
            . '?intent=edit';
    } else {
        \$targetPath = \$htmlListPath;
    }
}

\$projectPid = app_html_db_html_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . (\$htmlKey !== '' ? \$targetPath : \$htmlListPath));
        exit;
    }

    app_html_db_html_edit_wrapper_redirect_with_bridge_errors(
        \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
        [
            'legacy ProjectPID が current project route と一致しません。current html page からやり直してください。',
        ],
    );
}

if (\$requestMethod === 'POST') {
    \$bridgeErrors = [];
    if (\$legacyHtmlPid !== '' && \$htmlKey === '') {
        \$bridgeErrors[] = '更新対象の legacy html pid は current route に解決できませんでした。';
    }

    \$update = app_html_db_html_edit_wrapper_param(\$_POST, 'UPDATE');
    \$delete = app_html_db_html_edit_wrapper_param(\$_POST, 'DELETE');
    if (\$update === '' && \$delete === '') {
        app_html_db_html_edit_wrapper_redirect_with_bridge_errors(
            \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
            array_merge(
                \$bridgeErrors,
                [
                    'legacy html edit POST に UPDATE / DELETE が無いため、current html page へ handoff します。',
                ],
            ),
        );
    }

    if (\$delete !== '' && \$htmlKey === '') {
        app_html_db_html_edit_wrapper_redirect_with_bridge_errors(
            \$htmlListPath,
            array_merge(
                \$bridgeErrors,
                [
                    '削除対象の legacy html pid が指定されていません。',
                ],
            ),
        );
    }

    \$appRoot = app_html_db_html_edit_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_html_edit_wrapper_redirect_with_bridge_errors(
            \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
            array_merge(
                \$bridgeErrors,
                [
                    'current html save を継続する shared bootstrap が見つかりません。current html page から再実行してください。',
                ],
            ),
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];

    if (\$delete !== '') {
        \$_SERVER['REQUEST_URI'] = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
            . '/html/'
            . rawurlencode(\$htmlKey);
        \$_POST = [
            '_csrf' => app_csrf_token(),
            'action' => 'delete-html',
            'html_key' => \$htmlKey,
        ];
        if (\$bridgeErrors !== []) {
            \$_POST['bridge_errors'] = \$bridgeErrors;
        }

        require_once \$appRoot . '/http.php';
        app_run_http_request();
        return;
    }

    \$_POST = [
        '_csrf' => app_csrf_token(),
        'action' => \$htmlKey === '' ? 'create-html' : 'update-html',
        'name' => app_html_db_html_edit_wrapper_param(\$_POST, 'name'),
        'legacy_project_source_output_pid' => app_html_db_html_edit_wrapper_param(\$_POST, 'ProjectSourceOutputPID'),
        'legacy_html_template_pid' => app_html_db_html_edit_wrapper_param(\$_POST, 'htmlTemplatePID'),
    ];

    if (\$htmlKey === '') {
        \$_SERVER['REQUEST_URI'] = {$exportedHtmlListPath};
    } else {
        \$_SERVER['REQUEST_URI'] = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
            . '/html/'
            . rawurlencode(\$htmlKey);
        \$_POST['html_key'] = \$htmlKey;
    }

    if (\$bridgeErrors !== []) {
        \$_POST['bridge_errors'] = \$bridgeErrors;
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_html_edit_wrapper_redirect_with_bridge_errors(
        \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
        [
            '未対応の request method です。current html page からやり直してください。',
        ],
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyHtmlPidMap
 */
function app_project_output_html_module_generated_html_parameters_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyHtmlPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_html_list_path($projectKey),
        true,
    );
    $exportedHtmlPidMap = var_export($legacyHtmlPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `html_parameters.php`.
// Known legacy html parameter list requests are redirected to the current parameters route.
// Unsupported verbs, project mismatches, and unknown html IDs reduce to the current html list.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

\$legacyHtmlPidMap = {$exportedHtmlPidMap};
\$legacyHtmlPid = isset(\$legacyParams['htmlPID']) ? trim((string) \$legacyParams['htmlPID']) : '';

if (\$legacyHtmlPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$legacyHtmlPid, \$legacyHtmlPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/html/'
        . rawurlencode(\$legacyHtmlPidMap[\$legacyHtmlPid])
        . '/parameters';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedListPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyHtmlPidMap
 */
function app_project_output_html_module_generated_html_parameter_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyHtmlPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedHtmlListPath = var_export(
        app_project_output_html_module_default_html_list_path($projectKey),
        true,
    );
    $exportedHtmlPidMap = var_export($legacyHtmlPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `html_parameter_edit.php`.
// GET deep links land on the current parameter editor.
// Legacy create/update/delete POST is translated into current html parameter actions.

function app_html_db_html_parameter_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_html_parameter_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_html_parameter_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_html_parameter_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$htmlListPath = {$exportedHtmlListPath};
\$legacyHtmlPidMap = {$exportedHtmlPidMap};
\$legacyHtmlPid = app_html_db_html_parameter_edit_wrapper_param(\$legacyParams, 'htmlPID');
\$legacyHtmlParameterPid = app_html_db_html_parameter_edit_wrapper_param(\$legacyParams, 'htmlParameterPID');
\$htmlKey = '';
\$parameterPath = \$htmlListPath;
\$bridgeQuery = [];

if (\$legacyHtmlPid !== '' && array_key_exists(\$legacyHtmlPid, \$legacyHtmlPidMap)) {
    \$htmlKey = \$legacyHtmlPidMap[\$legacyHtmlPid];
    \$parameterPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/html/'
        . rawurlencode(\$htmlKey)
        . '/parameters';

    if (\$legacyHtmlParameterPid !== '') {
        \$bridgeQuery['intent'] = 'edit';
        \$bridgeQuery['parameter_pid'] = \$legacyHtmlParameterPid;
    } else {
        \$bridgeQuery['intent'] = 'create';
    }

    \$parameterName = app_html_db_html_parameter_edit_wrapper_param(\$legacyParams, 'ParameterName');
    if (\$parameterName !== '') {
        \$bridgeQuery['parameter_name'] = \$parameterName;
    }

    \$dataType = app_html_db_html_parameter_edit_wrapper_param(\$legacyParams, 'DataType');
    if (\$dataType !== '') {
        \$bridgeQuery['data_type'] = \$dataType;
    }
}

\$targetPath = \$parameterPath;
if (\$parameterPath !== \$htmlListPath && \$bridgeQuery !== []) {
    \$targetPath .= '?' . http_build_query(\$bridgeQuery, '', '&', PHP_QUERY_RFC3986);
}

\$projectPid = app_html_db_html_parameter_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . (\$htmlKey !== '' ? \$targetPath : \$htmlListPath));
        exit;
    }

    app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
        \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
        [
            'legacy ProjectPID が current project route と一致しません。current html parameter page からやり直してください。',
        ],
    );
}

if (\$requestMethod === 'POST') {
    \$bridgeErrors = [];
    if (\$legacyHtmlPid !== '' && \$htmlKey === '') {
        \$bridgeErrors[] = '更新対象の legacy html pid は current route に解決できませんでした。';
    }

    \$update = app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'UPDATE');
    \$delete = app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'DELETE');
    if (\$update === '' && \$delete === '') {
        app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
            \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
            array_merge(
                \$bridgeErrors,
                [
                    'legacy html parameter edit POST に UPDATE / DELETE が無いため、current parameters page へ handoff します。',
                ],
            ),
        );
    }

    if (\$htmlKey === '') {
        app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
            \$htmlListPath,
            array_merge(
                \$bridgeErrors,
                [
                    '更新対象の current html route を解決できませんでした。',
                ],
            ),
        );
    }

    if (\$delete !== '' && \$legacyHtmlParameterPid === '') {
        app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
            \$parameterPath,
            [
                '削除対象の htmlParameter PID が指定されていません。',
            ],
        );
    }

    \$appRoot = app_html_db_html_parameter_edit_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            array_merge(
                \$bridgeErrors,
                [
                    'current html parameter save を継続する shared bootstrap が見つかりません。current parameters page から再実行してください。',
                ],
            ),
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = \$parameterPath;
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];

    \$_POST = [
        '_csrf' => app_csrf_token(),
        'action' => \$delete !== ''
            ? 'delete-parameter'
            : (\$legacyHtmlParameterPid === '' ? 'create-parameter' : 'update-parameter'),
        'html_key' => \$htmlKey,
        'parameter_pid' => \$legacyHtmlParameterPid,
        'parameter_name' => app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'ParameterName'),
        'parameter_value' => app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'ParameterValue'),
        'data_type' => app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'DataType'),
        'data_class_pid' => app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'DataClassPID'),
        'da_pid' => app_html_db_html_parameter_edit_wrapper_param(\$_POST, 'DAPID'),
    ];

    if (\$bridgeErrors !== []) {
        \$_POST['bridge_errors'] = \$bridgeErrors;
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_html_parameter_edit_wrapper_redirect_with_bridge_errors(
        \$htmlKey !== '' ? \$targetPath : \$htmlListPath,
        [
            '未対応の request method です。current html parameter page からやり直してください。',
        ],
    );
}

header('Cache-Control: no-store');
header('Location: ' . (\$htmlKey !== '' ? \$targetPath : \$htmlListPath));
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 */
function app_project_output_html_module_generated_db_access_list_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_db_access_list_path($projectKey),
        true,
    );
    $exportedDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da.php`.
// Known legacy DAPID values are redirected to the current db-access routes.
// Unsupported verbs, project mismatches, and unknown filter targets reduce to the current list route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$legacyDbAccessPidMap = {$exportedDbAccessPidMap};
\$filterDaPid = isset(\$legacyParams['filterdaPID']) ? trim((string) \$legacyParams['filterdaPID']) : '';
\$targetPath = {$exportedListPath};
if (\$filterDaPid !== '' && array_key_exists(\$filterDaPid, \$legacyDbAccessPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/db-access/'
        . rawurlencode(\$legacyDbAccessPidMap[\$filterDaPid]);
}

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 */
function app_project_output_html_module_generated_db_access_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export('_legacy/da_edit.php', true);
    $exportedDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_edit.php`.
// Existing legacy DAPID edit flows are redirected to the current edit route.
// Blank add flows remain on `_legacy/`.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$legacyDbAccessPidMap = {$exportedDbAccessPidMap};
\$legacyDaPid = isset(\$_GET['DAPID']) ? trim((string) \$_GET['DAPID']) : '';

if (\$legacyDaPid !== '' && array_key_exists(\$legacyDaPid, \$legacyDbAccessPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/db-access/'
        . rawurlencode(\$legacyDbAccessPidMap[\$legacyDaPid])
        . '/edit';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

require_once __DIR__ . '/' . {$exportedLegacyFallback};

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_functions_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_db_access_list_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/da_funcs.php', true);
    $exportedDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_funcs.php`.
// Known legacy DAPID values are redirected to the current function list route.
// Known `filterdafuncPID` values are redirected to the current function detail route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$legacyDbAccessPidMap = {$exportedDbAccessPidMap};
\$legacyDbAccessFunctionPidMap = {$exportedDbAccessFunctionPidMap};
\$legacyDaPid = isset(\$_GET['DAPID']) ? trim((string) \$_GET['DAPID']) : '';
\$legacyFunctionPid = isset(\$_GET['filterdafuncPID']) ? trim((string) \$_GET['filterdafuncPID']) : '';

if (\$legacyDaPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (!array_key_exists(\$legacyDaPid, \$legacyDbAccessPidMap)) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$sourceName = \$legacyDbAccessPidMap[\$legacyDaPid];
\$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/db-access/'
    . rawurlencode(\$sourceName)
    . '/functions';

if (\$legacyFunctionPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (
    array_key_exists(\$legacyFunctionPid, \$legacyDbAccessFunctionPidMap)
    && \$legacyDbAccessFunctionPidMap[\$legacyFunctionPid]['source_name'] === \$sourceName
) {
    \$targetPath .= '/'
        . rawurlencode(\$legacyDbAccessFunctionPidMap[\$legacyFunctionPid]['function_name']);
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

require_once __DIR__ . '/' . {$exportedLegacyFallback};

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 */
function app_project_output_html_module_generated_db_access_functions_change_order_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_db_access_list_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/da_funcs_change_order.php', true);
    $exportedDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_funcs_change_order.php`.
// Preview-only GET requests are redirected to the current function change-order route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$newSortOrder = isset(\$_GET['NewSortOrder']) ? trim((string) \$_GET['NewSortOrder']) : '';
\$doReset = isset(\$_GET['doReset']) ? trim((string) \$_GET['doReset']) : '';
if (\$newSortOrder !== '' || \$doReset !== '') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$legacyDbAccessPidMap = {$exportedDbAccessPidMap};
\$legacyDaPid = isset(\$_GET['DAPID']) ? trim((string) \$_GET['DAPID']) : '';

if (\$legacyDaPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (array_key_exists(\$legacyDaPid, \$legacyDbAccessPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/db-access/'
        . rawurlencode(\$legacyDbAccessPidMap[\$legacyDaPid])
        . '/functions/change-order';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

require_once __DIR__ . '/' . {$exportedLegacyFallback};

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 */
function app_project_output_html_module_generated_db_access_source_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_db_access_list_path($projectKey),
        true,
    );
    $exportedDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_source.php`.
// Known legacy DAPID values are redirected to the current source preview route.
// Unsupported verbs, project mismatches, and unknown IDs reduce to the nearest current route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$legacyDbAccessPidMap = {$exportedDbAccessPidMap};
\$legacyDaPid = isset(\$legacyParams['PID']) ? trim((string) \$legacyParams['PID']) : '';
\$targetPath = {$exportedListPath};
if (\$legacyDaPid !== '' && array_key_exists(\$legacyDaPid, \$legacyDbAccessPidMap)) {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/db-access/'
        . rawurlencode(\$legacyDbAccessPidMap[\$legacyDaPid])
        . '/source';
}

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

function app_project_output_html_module_generated_db_access_sync_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedSyncPath = var_export(
        app_project_output_html_module_default_db_access_sync_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export('_legacy/da_sync.php', true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_sync.php`.
// Preview-only GET requests are redirected to the current sync page.
// Legacy per-data-class sync actions remain on `_legacy/`.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$_GET['ProjectPID']) ? trim((string) \$_GET['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$dataClassName = isset(\$_GET['DataClassName']) ? trim((string) \$_GET['DataClassName']) : '';
\$doSync = isset(\$_GET['DoSync']) ? trim((string) \$_GET['DoSync']) : '';
\$doSyncAll = isset(\$_GET['DoSyncAll']) ? trim((string) \$_GET['DoSyncAll']) : '';
if (\$dataClassName !== '' || \$doSync !== '' || \$doSyncAll !== '') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedSyncPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_route_prefix_text(
    string $projectKey,
    int $legacyProjectPid,
    string $legacyFallbackRelativePath,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
    bool $reduceInvalidGetToCurrent = false,
    bool $reduceUnsupportedVerbToCurrent = false,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedDbAccessListPath = var_export(
        '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/db-access',
        true,
    );
    $exportedDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);
    $exportedReduceInvalidGetToCurrent = var_export($reduceInvalidGetToCurrent, true);
    $exportedReduceUnsupportedVerbToCurrent = var_export($reduceUnsupportedVerbToCurrent, true);

    return <<<PHP
\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$dbAccessListPath = {$exportedDbAccessListPath};
\$legacyDbAccessPidMap = {$exportedDbAccessPidMap};
\$legacyDbAccessFunctionPidMap = {$exportedDbAccessFunctionPidMap};
\$legacyDaPid = isset(\$legacyParams['DAPID']) ? trim((string) \$legacyParams['DAPID']) : '';
\$legacyFunctionPid = isset(\$legacyParams['DAFuncPID']) ? trim((string) \$legacyParams['DAFuncPID']) : '';
\$currentFallbackPath = \$dbAccessListPath;

\$mappedDaSourceName = '';
if (\$legacyDaPid !== '' && array_key_exists(\$legacyDaPid, \$legacyDbAccessPidMap)) {
    \$mappedDaSourceName = trim((string) \$legacyDbAccessPidMap[\$legacyDaPid]);
    if (\$mappedDaSourceName !== '') {
        \$currentFallbackPath = \$dbAccessListPath
            . '/'
            . rawurlencode(\$mappedDaSourceName)
            . '/functions';
    }
}

\$sourceName = '';
\$functionName = '';
\$basePath = '';
if (\$legacyFunctionPid !== '' && array_key_exists(\$legacyFunctionPid, \$legacyDbAccessFunctionPidMap)) {
    \$functionReference = \$legacyDbAccessFunctionPidMap[\$legacyFunctionPid];
    \$sourceName = trim((string) (\$functionReference['source_name'] ?? ''));
    \$functionName = trim((string) (\$functionReference['function_name'] ?? ''));
    if (\$sourceName !== '' && \$functionName !== '') {
        \$basePath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
            . '/db-access/'
            . rawurlencode(\$sourceName)
            . '/functions/'
            . rawurlencode(\$functionName);
        \$currentFallbackPath = \$basePath;
    }
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    if ({$exportedReduceUnsupportedVerbToCurrent}) {
        header('Cache-Control: no-store');
        header('Location: ' . \$currentFallbackPath);
        exit;
    }

    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if ({$exportedReduceInvalidGetToCurrent}) {
        header('Cache-Control: no-store');
        header('Location: ' . \$currentFallbackPath);
        exit;
    }

    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

if (\$basePath === '') {
    if ({$exportedReduceInvalidGetToCurrent}) {
        header('Cache-Control: no-store');
        header('Location: ' . \$currentFallbackPath);
        exit;
    }

    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

if (\$legacyDaPid !== '') {
    if (
        !array_key_exists(\$legacyDaPid, \$legacyDbAccessPidMap)
        || \$legacyDbAccessPidMap[\$legacyDaPid] !== \$sourceName
    ) {
        if ({$exportedReduceInvalidGetToCurrent}) {
            header('Cache-Control: no-store');
            header('Location: ' . \$currentFallbackPath);
            exit;
        }

        require_once __DIR__ . '/' . {$exportedLegacyFallback};
        return;
    }
}

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
    string $relativePath,
    string $targetSuffix,
    string $description,
    bool $reduceInvalidGetToCurrent = false,
    bool $reduceUnsupportedVerbToCurrent = false,
): string {
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_db_access_function_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyDbAccessPidMap,
        $legacyDbAccessFunctionPidMap,
        $reduceInvalidGetToCurrent,
        $reduceUnsupportedVerbToCurrent,
    );
    $exportedTargetSuffix = var_export($targetSuffix, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// {$description}

{$prefix}
\$targetPath = \$basePath . {$exportedTargetSuffix};
header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_endpoint_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedDbAccessListPath = var_export(
        '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/db-access',
        true,
    );
    $exportedLegacyDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedLegacyDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_func_endpoint.php`.
// Legacy function endpoint previews are now current read-only endpoint pages.
// Unknown or mismatched legacy IDs are reduced to the nearest current list route instead of `_legacy/`.

function app_html_db_db_access_function_endpoint_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$dbAccessListPath = {$exportedDbAccessListPath};

\$projectPid = app_html_db_db_access_function_endpoint_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . \$dbAccessListPath);
    exit;
}

\$legacyDbAccessPidMap = {$exportedLegacyDbAccessPidMap};
\$legacyDbAccessFunctionPidMap = {$exportedLegacyDbAccessFunctionPidMap};
\$legacyDbAccessPid = app_html_db_db_access_function_endpoint_wrapper_param(\$legacyParams, 'DAPID');
\$targetPath = \$dbAccessListPath;

if (\$legacyDbAccessPid !== '' && array_key_exists(\$legacyDbAccessPid, \$legacyDbAccessPidMap)) {
    \$targetPath .= '/' . rawurlencode(\$legacyDbAccessPidMap[\$legacyDbAccessPid]) . '/functions';
}

\$legacyFunctionPid = app_html_db_db_access_function_endpoint_wrapper_param(\$legacyParams, 'DAFuncPID');
if (\$legacyFunctionPid !== '' && array_key_exists(\$legacyFunctionPid, \$legacyDbAccessFunctionPidMap)) {
    \$functionReference = \$legacyDbAccessFunctionPidMap[\$legacyFunctionPid];
    \$sourceName = trim((string) (\$functionReference['source_name'] ?? ''));
    \$functionName = trim((string) (\$functionReference['function_name'] ?? ''));
    if (\$sourceName !== '' && \$functionName !== '') {
        \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
            . '/db-access/'
            . rawurlencode(\$sourceName)
            . '/functions/'
            . rawurlencode(\$functionName)
            . '/endpoint';
    }
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_change_order_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
    string $relativePath,
    string $targetSuffix,
    bool $reduceInvalidGetToCurrent = false,
    bool $reduceUnsupportedVerbToCurrent = false,
): string {
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_db_access_function_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyDbAccessPidMap,
        $legacyDbAccessFunctionPidMap,
        $reduceInvalidGetToCurrent,
        $reduceUnsupportedVerbToCurrent,
    );
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedTargetSuffix = var_export($targetSuffix, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// Preview-only GET requests are redirected to the current change-order route.
// Legacy sort-order update actions remain on `_legacy/`.

{$prefix}
\$newSortOrder = isset(\$_GET['NewSortOrder']) ? trim((string) \$_GET['NewSortOrder']) : '';
\$doReset = isset(\$_GET['doReset']) ? trim((string) \$_GET['doReset']) : '';
if (\$newSortOrder !== '' || \$doReset !== '') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$targetPath = \$basePath . {$exportedTargetSuffix};
header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_list_or_new_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
    string $relativePath,
    string $routeSegment,
    string $description,
    bool $reduceInvalidGetToCurrent = false,
): string {
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_db_access_function_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyDbAccessPidMap,
        $legacyDbAccessFunctionPidMap,
        $reduceInvalidGetToCurrent,
        false,
    );
    $exportedRouteSegment = var_export($routeSegment, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// {$description}

{$prefix}
\$legacyItemPid = isset(\$_GET['PID']) ? trim((string) \$_GET['PID']) : '';
\$targetPath = \$basePath . '/' . {$exportedRouteSegment};
if (\$legacyItemPid === '') {
    \$targetPath .= '/new';
}
header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_select_where_input_aid_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_func_select_where_input_aid.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_db_access_function_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyDbAccessPidMap,
        $legacyDbAccessFunctionPidMap,
        true,
        false,
    );
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_func_select_where_input_aid.php`.
// The base input-aid preview is redirected to the current route.
// Interactive filter / candidate-selection state remains on `_legacy/`.

{$prefix}
\$showAllPattern = isset(\$_GET['ShowAllPattern']) ? trim((string) \$_GET['ShowAllPattern']) : '';
\$filterByTargetTableName = isset(\$_GET['FilterBytargetTableName']) ? trim((string) \$_GET['FilterBytargetTableName']) : '';
\$filterByTargetTableColumnName = isset(\$_GET['FilterBytargetTableColumnName']) ? trim((string) \$_GET['FilterBytargetTableColumnName']) : '';
\$filterByParameterType = isset(\$_GET['FilterByParameterType']) ? trim((string) \$_GET['FilterByParameterType']) : '';
\$filterByAnotherTableName = isset(\$_GET['FilterByAnotherTableName']) ? trim((string) \$_GET['FilterByAnotherTableName']) : '';
\$filterByAnotherFieldName = isset(\$_GET['FilterByAnotherFieldName']) ? trim((string) \$_GET['FilterByAnotherFieldName']) : '';
\$reverseFilterByTargetTableName = isset(\$_GET['ReverseFilterBytargetTableName']) ? trim((string) \$_GET['ReverseFilterBytargetTableName']) : '';
\$reverseFilterByTargetTableColumnName = isset(\$_GET['ReverseFilterBytargetTableColumnName']) ? trim((string) \$_GET['ReverseFilterBytargetTableColumnName']) : '';
\$reverseFilterByParameterType = isset(\$_GET['ReverseFilterByParameterType']) ? trim((string) \$_GET['ReverseFilterByParameterType']) : '';
\$reverseFilterByAnotherTableName = isset(\$_GET['ReverseFilterByAnotherTableName']) ? trim((string) \$_GET['ReverseFilterByAnotherTableName']) : '';
\$reverseFilterByAnotherFieldName = isset(\$_GET['ReverseFilterByAnotherFieldName']) ? trim((string) \$_GET['ReverseFilterByAnotherFieldName']) : '';
\$doNext = isset(\$_GET['DoNext']) ? trim((string) \$_GET['DoNext']) : '';
\$dbTableList = \$_GET['DBTableList'] ?? null;
\$selectTargetList = \$_GET['SelectTargetList'] ?? null;

if (
    \$showAllPattern !== ''
    || \$filterByTargetTableName !== ''
    || \$filterByTargetTableColumnName !== ''
    || \$filterByParameterType !== ''
    || \$filterByAnotherTableName !== ''
    || \$filterByAnotherFieldName !== ''
    || \$reverseFilterByTargetTableName !== ''
    || \$reverseFilterByTargetTableColumnName !== ''
    || \$reverseFilterByParameterType !== ''
    || \$reverseFilterByAnotherTableName !== ''
    || \$reverseFilterByAnotherFieldName !== ''
    || \$doNext !== ''
    || \$dbTableList !== null
    || \$selectTargetList !== null
) {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}

\$targetPath = \$basePath . '/select-where/input-aid';
header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_select_where_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_func_select_where_edit.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_db_access_function_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyDbAccessPidMap,
        $legacyDbAccessFunctionPidMap,
        true,
        false,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_func_select_where_edit.php`.
// New-row preview flows are redirected to the current `/new` route with compatible query defaults.
// Existing legacy item PID deep links are redirected to the current designer list until canonical item mapping is added.

{$prefix}
\$legacyItemPid = isset(\$_GET['PID']) ? trim((string) \$_GET['PID']) : '';
\$targetPath = \$basePath . '/select-where';

if (\$legacyItemPid === '') {
    \$targetPath .= '/new';

    \$query = [];
    \$targetTableName = isset(\$_GET['targetTableName']) ? trim((string) \$_GET['targetTableName']) : '';
    \$targetTableAliasName = isset(\$_GET['targetTableAliasName']) ? trim((string) \$_GET['targetTableAliasName']) : '';
    \$targetTableColumnName = isset(\$_GET['targetTableColumnName']) ? trim((string) \$_GET['targetTableColumnName']) : '';
    \$parameterType = isset(\$_GET['ParameterType']) ? trim((string) \$_GET['ParameterType']) : '';
    \$parameterDataType = isset(\$_GET['ParameterDataType']) ? trim((string) \$_GET['ParameterDataType']) : '';
    \$fixedParameter = isset(\$_GET['FixedParameter']) ? trim((string) \$_GET['FixedParameter']) : '';
    \$anotherTableName = isset(\$_GET['AnotherTableName']) ? trim((string) \$_GET['AnotherTableName']) : '';
    \$anotherTableAliasName = isset(\$_GET['AnotherTableAliasName']) ? trim((string) \$_GET['AnotherTableAliasName']) : '';
    \$anotherFieldName = isset(\$_GET['AnotherFieldName']) ? trim((string) \$_GET['AnotherFieldName']) : '';
    \$orGroup = isset(\$_GET['ORGroup']) ? trim((string) \$_GET['ORGroup']) : '';
    \$relationalOperator = isset(\$_GET['RelationalOperator']) ? trim((string) \$_GET['RelationalOperator']) : '';
    \$innerJoinType = isset(\$_GET['InnerJoinType']) ? trim((string) \$_GET['InnerJoinType']) : '';
    \$outerJoinType = isset(\$_GET['OuterJoinType']) ? trim((string) \$_GET['OuterJoinType']) : '';

    if (\$targetTableName !== '') {
        \$query['target_table_name'] = \$targetTableName;
    }
    if (\$targetTableAliasName !== '') {
        \$query['target_table_alias_name'] = \$targetTableAliasName;
    }
    if (\$targetTableColumnName !== '') {
        \$query['target_table_column_name'] = \$targetTableColumnName;
    }
    if (\$parameterType !== '') {
        \$query['parameter_type'] = \$parameterType;
    }
    if (\$parameterDataType !== '') {
        \$query['parameter_data_type'] = \$parameterDataType;
    }
    if (\$fixedParameter !== '') {
        \$query['fixed_parameter'] = \$fixedParameter;
    }
    if (\$anotherTableName !== '') {
        \$query['another_table_name'] = \$anotherTableName;
    }
    if (\$anotherTableAliasName !== '') {
        \$query['another_table_alias_name'] = \$anotherTableAliasName;
    }
    if (\$anotherFieldName !== '') {
        \$query['another_field_name'] = \$anotherFieldName;
    }
    if (\$orGroup !== '') {
        \$query['or_group'] = \$orGroup;
    }
    if (\$relationalOperator !== '') {
        \$query['relational_operator'] = \$relationalOperator;
    }

    \$joinType = '';
    if (\$parameterType === 'anotherfield') {
        \$joinType = \$outerJoinType;
    } elseif (\$parameterType === 'argument' || \$parameterType === 'fixed') {
        \$joinType = \$innerJoinType;
    }
    if (\$joinType !== '') {
        \$query['join_type'] = \$joinType;
    }

    if (\$query !== []) {
        \$targetPath .= '?' . http_build_query(\$query);
    }
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_db_access_function_update_delete_where_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_func_update_delete_where_edit.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_db_access_function_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyDbAccessPidMap,
        $legacyDbAccessFunctionPidMap,
        true,
        false,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_func_update_delete_where_edit.php`.
// New-row preview flows are redirected to the current `/new` route with compatible query defaults.
// Existing legacy item PID deep links are redirected to the current designer list until canonical item mapping is added.

{$prefix}
\$legacyItemPid = isset(\$_GET['PID']) ? trim((string) \$_GET['PID']) : '';
\$targetPath = \$basePath . '/update-delete-where';

if (\$legacyItemPid === '') {
    \$targetPath .= '/new';

    \$query = [];
    \$targetTableColumnName = isset(\$_GET['targetTableColumnName']) ? trim((string) \$_GET['targetTableColumnName']) : '';
    \$parameterType = isset(\$_GET['ParameterType']) ? trim((string) \$_GET['ParameterType']) : '';
    \$parameterDataType = isset(\$_GET['ParameterDataType']) ? trim((string) \$_GET['ParameterDataType']) : '';
    \$fixedParameter = isset(\$_GET['FixedParameter']) ? trim((string) \$_GET['FixedParameter']) : '';
    \$orGroup = isset(\$_GET['ORGroup']) ? trim((string) \$_GET['ORGroup']) : '';
    \$relationalOperator = isset(\$_GET['RelationalOperator']) ? trim((string) \$_GET['RelationalOperator']) : '';

    if (\$targetTableColumnName !== '') {
        \$query['target_table_column_name'] = \$targetTableColumnName;
    }
    if (\$parameterType !== '') {
        \$query['parameter_type'] = \$parameterType;
    }
    if (\$parameterDataType !== '') {
        \$query['parameter_data_type'] = \$parameterDataType;
    }
    if (\$fixedParameter !== '') {
        \$query['fixed_parameter'] = \$fixedParameter;
    }
    if (\$orGroup !== '') {
        \$query['or_group'] = \$orGroup;
    }
    if (\$relationalOperator !== '') {
        \$query['relational_operator'] = \$relationalOperator;
    }

    if (\$query !== []) {
        \$targetPath .= '?' . http_build_query(\$query);
    }
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 */
function app_project_output_html_module_generated_single_proxy_list_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_single_proxy_path($projectKey),
        true,
    );
    $exportedLegacyDbAccessPidMap = var_export($legacyDbAccessPidMap, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_edit_proxy_single_target.php`.
// GET/HEAD preview requests are redirected to the current project-scoped single proxy route.
// Unsupported verbs, project mismatches, and unknown filter targets reduce to the current list route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$targetPath = {$exportedTargetPath};
\$legacyDbAccessPidMap = {$exportedLegacyDbAccessPidMap};
\$legacyFilterDaPid = isset(\$legacyParams['filterdaPID']) ? trim((string) \$legacyParams['filterdaPID']) : '';
if (\$legacyFilterDaPid !== '' && array_key_exists(\$legacyFilterDaPid, \$legacyDbAccessPidMap)) {
    \$targetPath .= '?db_access_key=' . rawurlencode(\$legacyDbAccessPidMap[\$legacyFilterDaPid]);
}

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 */
function app_project_output_html_module_generated_single_proxy_db_access_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    string $relativePath,
    string $description,
    string $legacyDbAccessPidParamName = 'DAPID',
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_single_proxy_path($projectKey),
        true,
    );
    $exportedLegacyDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedLegacyDbAccessPidParamName = var_export($legacyDbAccessPidParamName, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// {$description}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$targetPath = {$exportedTargetPath};
\$legacyDbAccessPidMap = {$exportedLegacyDbAccessPidMap};
\$legacyDbAccessPidParamName = {$exportedLegacyDbAccessPidParamName};
\$legacyDbAccessPid = isset(\$legacyParams[\$legacyDbAccessPidParamName])
    ? trim((string) \$legacyParams[\$legacyDbAccessPidParamName])
    : '';
\$resolvedDbAccessKey = '';
if (\$legacyDbAccessPid !== '' && array_key_exists(\$legacyDbAccessPid, \$legacyDbAccessPidMap)) {
    \$resolvedDbAccessKey = \$legacyDbAccessPidMap[\$legacyDbAccessPid];
}

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$legacyDbAccessPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$resolvedDbAccessKey === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

\$targetPath .= '?db_access_key=' . rawurlencode(\$resolvedDbAccessKey);
header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,string> $legacySourceOutputPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_single_proxy_bulk_target_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacySourceOutputPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_funcs_edit_proxy_single_target.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_single_proxy_path($projectKey),
        true,
    );
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedLegacyDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedLegacySourceOutputPidMap = var_export($legacySourceOutputPidMap, true);
    $exportedLegacyDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_funcs_edit_proxy_single_target.php`.
// GET/HEAD preview requests are redirected to the current project-scoped single proxy route.
// Legacy bulk target update POST is translated into the current bulk-save payload.

function app_html_db_single_proxy_bulk_target_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_single_proxy_bulk_target_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_single_proxy_bulk_target_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_single_proxy_bulk_target_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_single_proxy_bulk_target_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$targetPath = {$exportedTargetPath};

\$projectPid = app_html_db_single_proxy_bulk_target_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$targetPath);
        exit;
    }

    app_html_db_single_proxy_bulk_target_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            'legacy ProjectPID が current project route と一致しません。current single proxy page からやり直してください。',
        ],
    );
}
\$legacyDbAccessPidMap = {$exportedLegacyDbAccessPidMap};
\$legacyDbAccessPid = app_html_db_single_proxy_bulk_target_wrapper_param(\$legacyParams, 'DAPID');
if (\$legacyDbAccessPid === '') {
    if (\$requestMethod === 'POST') {
        \$dbAccessKey = '';
    } else {
        header('Cache-Control: no-store');
        header('Location: ' . \$targetPath);
        exit;
    }
} else {
    \$dbAccessKey = \$legacyDbAccessPidMap[\$legacyDbAccessPid] ?? \$legacyDbAccessPid;
}

\$hasMappedDbAccessKey = \$legacyDbAccessPid !== '' && array_key_exists(\$legacyDbAccessPid, \$legacyDbAccessPidMap);
if (\$hasMappedDbAccessKey) {
    \$targetPath .= '?db_access_key=' . rawurlencode(\$dbAccessKey);
} elseif (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$requestMethod === 'POST') {
    if (\$legacyDbAccessPid === '') {
        app_html_db_single_proxy_bulk_target_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                '更新対象の legacy DAPID が指定されていません。',
            ],
        );
    }

    if (!\$hasMappedDbAccessKey) {
        app_html_db_single_proxy_bulk_target_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                '更新対象の legacy DAPID は current db access key に解決できませんでした。',
            ],
        );
    }

    \$legacySelections = \$_POST['IsTargetOfSimpleProxyWithProjectSourceOutputAndDAFuncPID'] ?? [];
    if (!is_array(\$legacySelections)) {
        \$legacySelections = [];
    }

    \$legacySourceOutputPidMap = {$exportedLegacySourceOutputPidMap};
    \$legacyDbAccessFunctionPidMap = {$exportedLegacyDbAccessFunctionPidMap};
    \$currentSelections = [];

    foreach (\$legacySelections as \$legacySelection) {
        if (!is_string(\$legacySelection) || !preg_match('/^(\\d+)-(\\d+)$/', \$legacySelection, \$matches)) {
            continue;
        }

        \$legacyFunctionPid = \$matches[1];
        \$legacySourceOutputPid = \$matches[2];
        if (
            !array_key_exists(\$legacyFunctionPid, \$legacyDbAccessFunctionPidMap)
            || !array_key_exists(\$legacySourceOutputPid, \$legacySourceOutputPidMap)
        ) {
            continue;
        }

        \$functionReference = \$legacyDbAccessFunctionPidMap[\$legacyFunctionPid];
        if (\$hasMappedDbAccessKey && (string) (\$functionReference['source_name'] ?? '') !== \$dbAccessKey) {
            continue;
        }

        \$functionName = trim((string) (\$functionReference['function_name'] ?? ''));
        \$sourceOutputKey = trim((string) \$legacySourceOutputPidMap[\$legacySourceOutputPid]);
        if (\$functionName === '' || \$sourceOutputKey === '') {
            continue;
        }

        if (!isset(\$currentSelections[\$functionName])) {
            \$currentSelections[\$functionName] = [];
        }
        \$currentSelections[\$functionName][\$sourceOutputKey] = \$sourceOutputKey;
    }

    foreach (\$currentSelections as \$functionName => \$selectedKeys) {
        \$currentSelections[\$functionName] = array_values(\$selectedKeys);
    }

    \$appRoot = app_html_db_single_proxy_bulk_target_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_single_proxy_bulk_target_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'current single proxy bulk target save を継続する shared bootstrap が見つかりません。current page から再実行してください。',
            ],
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = \$targetPath;
    \$_SERVER['QUERY_STRING'] = \$hasMappedDbAccessKey
        ? http_build_query(['db_access_key' => \$dbAccessKey], '', '&', PHP_QUERY_RFC3986)
        : '';
    \$_GET = \$hasMappedDbAccessKey ? ['db_access_key' => \$dbAccessKey] : [];
    \$_POST = [
        '_csrf' => app_csrf_token(),
        'db_access_key' => \$dbAccessKey,
        'source_output_keys_by_function' => \$currentSelections,
    ];

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_single_proxy_bulk_target_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current single proxy page からやり直してください。',
        ],
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyDbAccessPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_single_proxy_function_detail_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyDbAccessPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_funcs_edit_proxy_single_setting_edit.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedLegacyDbAccessPidMap = var_export($legacyDbAccessPidMap, true);
    $exportedLegacyDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_funcs_edit_proxy_single_setting_edit.php`.
// GET/HEAD preview requests are redirected to the current function detail route where auth/target editing now lives.
// Legacy POST update actions are translated into the current auth-only bridge path.

function app_html_db_single_proxy_function_detail_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_single_proxy_function_detail_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_single_proxy_function_detail_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_single_proxy_function_detail_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_single_proxy_function_detail_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$singleProxyListPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey}) . '/proxy/single';

\$projectPid = app_html_db_single_proxy_function_detail_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$singleProxyListPath);
        exit;
    }

    app_html_db_single_proxy_function_detail_wrapper_redirect_with_bridge_errors(
        \$singleProxyListPath,
        [
            'legacy ProjectPID が current project route と一致しません。current single proxy page からやり直してください。',
        ],
    );
}

\$legacyDbAccessPidMap = {$exportedLegacyDbAccessPidMap};
\$legacyDbAccessFunctionPidMap = {$exportedLegacyDbAccessFunctionPidMap};
\$legacyFunctionPid = app_html_db_single_proxy_function_detail_wrapper_param(\$legacyParams, 'DAFuncPID');
\$fallbackDbAccessPid = app_html_db_single_proxy_function_detail_wrapper_param(\$legacyParams, 'DAPID');
\$fallbackDbAccessKey = '';
if (\$fallbackDbAccessPid !== '' && array_key_exists(\$fallbackDbAccessPid, \$legacyDbAccessPidMap)) {
    \$fallbackDbAccessKey = \$legacyDbAccessPidMap[\$fallbackDbAccessPid];
}
\$fallbackTargetPath = \$fallbackDbAccessKey !== ''
    ? \$singleProxyListPath . '?db_access_key=' . rawurlencode(\$fallbackDbAccessKey)
    : \$singleProxyListPath;
if (\$legacyFunctionPid === '' || !array_key_exists(\$legacyFunctionPid, \$legacyDbAccessFunctionPidMap)) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$fallbackTargetPath);
        exit;
    }

    app_html_db_single_proxy_function_detail_wrapper_redirect_with_bridge_errors(
        \$fallbackTargetPath,
        [
            '更新対象の legacy DAFuncPID は current function route に解決できませんでした。',
        ],
    );
}

\$functionReference = \$legacyDbAccessFunctionPidMap[\$legacyFunctionPid];
\$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/db-access/'
    . rawurlencode(\$functionReference['source_name'])
    . '/functions/'
    . rawurlencode(\$functionReference['function_name']);

\$legacySingleGetFunctionPid = app_html_db_single_proxy_function_detail_wrapper_param(
    \$_POST,
    'SingleProxy_SingleGetFuncPID',
);
\$singleGetFunctionName = '';
\$bridgeErrors = [];
if (\$legacySingleGetFunctionPid !== '') {
    if (array_key_exists(\$legacySingleGetFunctionPid, \$legacyDbAccessFunctionPidMap)) {
        \$singleGetFunctionName = trim((string) (
            \$legacyDbAccessFunctionPidMap[\$legacySingleGetFunctionPid]['function_name'] ?? ''
        ));
    }

    if (\$singleGetFunctionName === '') {
        \$singleGetFunctionName = \$legacySingleGetFunctionPid;
        \$bridgeErrors[] = 'legacy SingleProxy_SingleGetFuncPID を current function name に解決できませんでした。';
    }
}

if (\$requestMethod === 'POST') {
    \$appRoot = app_html_db_single_proxy_function_detail_wrapper_app_root();
    if (\$appRoot === '') {
        \$bridgeErrors[] = 'current single proxy auth save を継続する shared bootstrap が見つかりません。current function detail page から再実行してください。';
        app_html_db_single_proxy_function_detail_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            \$bridgeErrors,
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = \$targetPath;
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];
    \$_POST = [
        '_csrf' => app_csrf_token(),
        'bridge_mode' => 'legacy-single-proxy-auth',
        'source_name' => \$functionReference['source_name'],
        'function_name' => \$functionReference['function_name'],
        'single_proxy_auth_type' => app_html_db_single_proxy_function_detail_wrapper_param(
            \$legacyParams,
            'SingleProxy_AuthType',
        ),
        'single_proxy_single_get_function_name' => \$singleGetFunctionName,
    ];
    if (\$bridgeErrors !== []) {
        \$_POST['bridge_errors'] = \$bridgeErrors;
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    \$bridgeErrors[] = '未対応の request method です。current function detail page からやり直してください。';
    app_html_db_single_proxy_function_detail_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        \$bridgeErrors,
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCustomProxyPidMap
 * @param array<string,list<array{source_output_key:string,release_target_type:string}>> $customProxyTargetSourceOutputMap
 */
function app_project_output_html_module_generated_custom_proxy_endpoint_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCustomProxyPidMap,
    array $customProxyTargetSourceOutputMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedCustomProxyListPath = var_export(
        app_project_output_html_module_default_custom_proxy_list_path($projectKey),
        true,
    );
    $exportedLegacyCustomProxyPidMap = var_export($legacyCustomProxyPidMap, true);
    $exportedTargetSourceOutputMap = var_export($customProxyTargetSourceOutputMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_proxy_custom_endpoint.php`.
// Known legacy custom proxy endpoint previews are redirected to the current endpoint preview route.
// Legacy preview semantics target custom proxy server outputs only.
// When ReleaseType maps to a server target source output, the current page is focused to that output.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$customProxyListPath = {$exportedCustomProxyListPath};
\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . \$customProxyListPath);
    exit;
}

\$legacyCustomProxyPidMap = {$exportedLegacyCustomProxyPidMap};
\$legacyCustomProxyPid = isset(\$legacyParams['DACustomProxyPID'])
    ? trim((string) \$legacyParams['DACustomProxyPID'])
    : '';
\$customProxyKey = \$legacyCustomProxyPidMap[\$legacyCustomProxyPid] ?? '';

if (\$customProxyKey === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$customProxyListPath);
    exit;
}

\$customProxyBasePath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/proxy/custom/'
    . rawurlencode(\$customProxyKey);
\$targetPath = \$customProxyBasePath . '/endpoint';
\$customProxyTargetSourceOutputMap = {$exportedTargetSourceOutputMap};
\$releaseType = isset(\$legacyParams['ReleaseType']) ? trim((string) \$legacyParams['ReleaseType']) : '';

if (\$releaseType !== '' && array_key_exists(\$customProxyKey, \$customProxyTargetSourceOutputMap)) {
    foreach (\$customProxyTargetSourceOutputMap[\$customProxyKey] as \$targetSourceOutput) {
        if (!is_array(\$targetSourceOutput)) {
            continue;
        }

        if (trim((string) (\$targetSourceOutput['release_target_type'] ?? '')) !== \$releaseType) {
            continue;
        }

        \$sourceOutputKey = trim((string) (\$targetSourceOutput['source_output_key'] ?? ''));
        if (\$sourceOutputKey === '') {
            continue;
        }

        \$targetPath .= '?source_output_key=' . rawurlencode(\$sourceOutputKey);
        break;
    }
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

function app_project_output_html_module_generated_endpoint_lib_include_wrapper_text(string $projectKey): string
{
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_endpoint_test_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility helper for `endpoint_lib_include.php`.
// The legacy endpoint helper bootstrap has been retired. Only tiny compatibility helpers remain.

if (!class_exists('EndpointTestResult')) {
    class EndpointTestResult
    {
        public \$Result = null;
        public \$_status = 'NG';
        public \$Message = '';
    }
}

if (!function_exists('output_json_parameter_by_adding_indent')) {
    function output_json_parameter_by_adding_indent(\$json_parameter, \$add_indent): void
    {
        \$lines = preg_split('/\\r\\n|\\r|\\n/', trim((string) \$json_parameter)) ?: [];
        if (\$lines === [] || (count(\$lines) === 1 && \$lines[0] === '')) {
            return;
        }

        foreach (\$lines as \$index => \$line) {
            if (\$index > 0) {
                print \$add_indent;
            }

            print htmlspecialchars((string) \$line, ENT_QUOTES, 'UTF-8');
            if (\$index < count(\$lines) - 1) {
                print "\\n";
            }
        }
    }
}

if (basename((string) (\$_SERVER['SCRIPT_FILENAME'] ?? '')) === 'endpoint_lib_include.php') {
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');
    \$targetPath = {$exportedTargetPath};
    \$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');
    echo '<section class="endpoint-helper-handoff">';
    echo '<h3>Endpoint Helper Moved</h3>';
    echo '<p>Use <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a> for current endpoint test runs.</p>';
    echo '</section>';
}

PHP;
}

function app_project_output_html_module_generated_endpoint_test_client_include_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_endpoint_test_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility helper for `endpoint_test_json_client_include.php`.
// The legacy inline test form now hands users off to the current endpoint test runner.

if (!function_exists('app_html_db_endpoint_test_client_helper_value')) {
    function app_html_db_endpoint_test_client_helper_value(\$subject, string \$field): string
    {
        if (is_array(\$subject)) {
            \$value = \$subject[\$field] ?? null;
        } elseif (is_object(\$subject)) {
            \$value = \$subject->{\$field} ?? null;
        } else {
            \$value = null;
        }

        if (is_string(\$value) || is_numeric(\$value)) {
            return trim((string) \$value);
        }

        return '';
    }
}

if (!function_exists('output_mtool_json_test_form')) {
    function output_mtool_json_test_form(\$form_url, \$ProjectPID, \$BuildSourceFuncCache, \$json_parameter): void
    {
        \$targetPath = {$exportedTargetPath};
        \$legacyProjectPid = {$exportedLegacyProjectPid};
        \$resolvedProjectPid = is_scalar(\$ProjectPID) ? trim((string) \$ProjectPID) : '';
        if (\$resolvedProjectPid === '') {
            \$resolvedProjectPid = \$legacyProjectPid;
        }

        \$proxyUrl = app_html_db_endpoint_test_client_helper_value(\$BuildSourceFuncCache, 'ProxyURL');
        \$requestJson = trim((string) \$json_parameter);
        \$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');
        \$escapedProxyUrl = htmlspecialchars(\$proxyUrl, ENT_QUOTES, 'UTF-8');
        \$escapedRequestJson = htmlspecialchars(\$requestJson, ENT_QUOTES, 'UTF-8');
        \$escapedProjectPid = htmlspecialchars(\$resolvedProjectPid, ENT_QUOTES, 'UTF-8');

        echo '<div class="endpoint-test-handoff">';
        echo '<p>Legacy inline JSON test has moved to the current runner. Open <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a>.</p>';
        if (\$proxyUrl !== '') {
            echo '<p>Legacy endpoint URL: <code>' . \$escapedProxyUrl . '</code></p>';
        }
        if (\$requestJson !== '') {
            echo '<details><summary>Legacy request JSON</summary><pre>' . \$escapedRequestJson . '</pre></details>';
        }
        echo '<details><summary>Legacy AJAX payload</summary><pre>';
        echo htmlspecialchars(json_encode([
            'ProjectPID' => \$resolvedProjectPid,
            'ProxyURL' => \$proxyUrl,
            'POST_JSON' => \$requestJson,
            'TargetRunner' => \$targetPath,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '', ENT_QUOTES, 'UTF-8');
        echo '</pre></details>';
        echo '<input type="hidden" name="ProjectPID" value="' . \$escapedProjectPid . '">';
        echo '</div>';
    }
}

if (basename((string) (\$_SERVER['SCRIPT_FILENAME'] ?? '')) === 'endpoint_test_json_client_include.php') {
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');
    \$targetPath = {$exportedTargetPath};
    \$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');
    echo '<section class="endpoint-helper-handoff">';
    echo '<h3>Endpoint Test Form Moved</h3>';
    echo '<p>Use <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a> for current endpoint test runs.</p>';
    echo '</section>';
}

PHP;
}

function app_project_output_html_module_generated_endpoint_common_include_wrapper_text(string $projectKey): string
{
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_endpoint_test_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility helper for `endpoint_common_include.php`.
// The full legacy endpoint sample block has moved to current endpoint preview / runner pages.

include_once __DIR__ . '/endpoint_test_json_client_include.php';

if (!function_exists('app_html_db_endpoint_common_helper_value')) {
    function app_html_db_endpoint_common_helper_value(\$subject, string \$field): string
    {
        if (is_array(\$subject)) {
            \$value = \$subject[\$field] ?? null;
        } elseif (is_object(\$subject)) {
            \$value = \$subject->{\$field} ?? null;
        } else {
            \$value = null;
        }

        if (is_string(\$value) || is_numeric(\$value)) {
            return trim((string) \$value);
        }

        return '';
    }
}

\$targetPath = {$exportedTargetPath};
\$buildSourceFuncCache = \$BuildSourceFuncCache ?? null;
\$targetProjectPid = \$TargetProjectPID ?? '';
\$proxyUrl = app_html_db_endpoint_common_helper_value(\$buildSourceFuncCache, 'ProxyURL');
\$proxyParameter = app_html_db_endpoint_common_helper_value(\$buildSourceFuncCache, 'ProxyParameterForJquery');
\$proxyResultFormat = app_html_db_endpoint_common_helper_value(\$buildSourceFuncCache, 'ProxyResultFormatForJquery');
\$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');

echo '<section class="endpoint-common-handoff">';
echo '<h4>Endpoint Preview Moved</h4>';
echo '<p>Detailed endpoint contract preview now lives on the current endpoint pages and runner.</p>';
echo '<p>Open <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a> for execution, or use the current endpoint preview routes from db-access/custom-proxy pages.</p>';

if (\$proxyUrl !== '') {
    echo '<h4>Legacy Endpoint URL</h4>';
    echo '<pre>' . htmlspecialchars(\$proxyUrl, ENT_QUOTES, 'UTF-8') . '</pre>';
}
if (\$proxyParameter !== '') {
    echo '<h4>Legacy Parameter</h4>';
    echo '<pre>' . htmlspecialchars(\$proxyParameter, ENT_QUOTES, 'UTF-8') . '</pre>';
}
if (\$proxyResultFormat !== '') {
    echo '<h4>Legacy Result Shape</h4>';
    echo '<pre>' . htmlspecialchars(\$proxyResultFormat, ENT_QUOTES, 'UTF-8') . '</pre>';
}

if (function_exists('output_mtool_json_test_form')) {
    echo '<h4>Legacy Test Payload</h4>';
    output_mtool_json_test_form((string) (\$_SERVER['SCRIPT_NAME'] ?? ''), \$targetProjectPid, \$buildSourceFuncCache, \$proxyParameter);
}

echo '</section>';

PHP;
}

function app_project_output_html_module_generated_custom_proxy_list_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_custom_proxy_list_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_proxy_custom.php`.
// The legacy custom-proxy list now lives under the current `/proxy/custom` route.
// Unsupported verbs and project mismatches reduce to the current list route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedTargetPath});
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCustomProxyPidMap
 */
function app_project_output_html_module_generated_custom_proxy_route_prefix_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCustomProxyPidMap,
    string $legacyCustomProxyPidParamName = 'daCustomProxyPID',
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedCustomProxyListPath = var_export(
        app_project_output_html_module_default_custom_proxy_list_path($projectKey),
        true,
    );
    $exportedCustomProxyPidMap = var_export($legacyCustomProxyPidMap, true);
    $exportedCustomProxyPidParamName = var_export($legacyCustomProxyPidParamName, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$customProxyListPath = {$exportedCustomProxyListPath};

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . \$customProxyListPath);
    exit;
}

\$legacyCustomProxyPidMap = {$exportedCustomProxyPidMap};
\$legacyCustomProxyPidParamName = {$exportedCustomProxyPidParamName};
\$legacyCustomProxyPid = isset(\$legacyParams[\$legacyCustomProxyPidParamName])
    ? trim((string) \$legacyParams[\$legacyCustomProxyPidParamName])
    : '';
\$customProxyKey = '';
if (\$legacyCustomProxyPid !== '' && array_key_exists(\$legacyCustomProxyPid, \$legacyCustomProxyPidMap)) {
    \$customProxyKey = \$legacyCustomProxyPidMap[\$legacyCustomProxyPid];
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    if (\$customProxyKey === '') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/proxy/custom/'
        . rawurlencode(\$customProxyKey)
        . '/functions';
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$legacyCustomProxyPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$customProxyListPath);
    exit;
}

if (!array_key_exists(\$legacyCustomProxyPid, \$legacyCustomProxyPidMap)) {
    header('Cache-Control: no-store');
    header('Location: ' . \$customProxyListPath);
    exit;
}

\$customProxyKey = \$legacyCustomProxyPidMap[\$legacyCustomProxyPid];
\$customProxyBasePath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/proxy/custom/'
    . rawurlencode(\$customProxyKey);
PHP;
}

/**
 * @param array<string,string> $legacyCustomProxyPidMap
 * @param array<string,string> $legacySourceOutputPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_custom_proxy_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCustomProxyPidMap,
    array $legacySourceOutputPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_proxy_custom_edit.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedCustomProxyListPath = var_export(
        app_project_output_html_module_default_custom_proxy_list_path($projectKey),
        true,
    );
    $exportedLegacyCustomProxyPidMap = var_export($legacyCustomProxyPidMap, true);
    $exportedLegacySourceOutputPidMap = var_export($legacySourceOutputPidMap, true);
    $exportedLegacyDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_proxy_custom_edit.php`.
// Blank add flows are redirected to the current custom-proxy list, where creation now lives.
// Legacy create/update/delete POST is translated into the current list/detail workflow.

function app_html_db_custom_proxy_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_custom_proxy_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_custom_proxy_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header('Location: ' . app_html_db_custom_proxy_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors));
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$customProxyListPath = {$exportedCustomProxyListPath};

\$projectPid = app_html_db_custom_proxy_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            'legacy ProjectPID が current project route と一致しません。current custom proxy page からやり直してください。',
        ],
    );
}
\$legacyCustomProxyPidMap = {$exportedLegacyCustomProxyPidMap};
\$legacyCustomProxyPid = app_html_db_custom_proxy_edit_wrapper_param(\$legacyParams, 'daCustomProxyPID');
\$customProxyKey = '';
\$targetPath = \$customProxyListPath;
\$bridgeErrors = [];

if (\$legacyCustomProxyPid !== '') {
    if (!array_key_exists(\$legacyCustomProxyPid, \$legacyCustomProxyPidMap)) {
        if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
            header('Cache-Control: no-store');
            header('Location: ' . \$customProxyListPath);
            exit;
        }
        \$bridgeErrors[] = '更新対象の legacy custom proxy pid は current route に解決できませんでした。';
    } else {
        \$customProxyKey = \$legacyCustomProxyPidMap[\$legacyCustomProxyPid];
        \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
            . '/proxy/custom/'
            . rawurlencode(\$customProxyKey);
    }
}

if (\$requestMethod === 'POST') {
    \$update = app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'UPDATE');
    \$delete = app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'DELETE');
    if (\$update === '' && \$delete === '') {
        app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            array_merge(
                \$bridgeErrors,
                [
                    'legacy custom proxy edit POST に UPDATE / DELETE が無いため、current page へ handoff します。',
                ],
            ),
        );
    }

    if (\$legacyCustomProxyPid !== '' && \$customProxyKey === '') {
        app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(
            \$customProxyListPath,
            \$bridgeErrors !== []
                ? \$bridgeErrors
                : ['更新対象の legacy custom proxy pid は current route に解決できませんでした。'],
        );
    }

    if (\$delete !== '' && \$customProxyKey === '') {
        \$bridgeErrors[] = '削除対象の legacy custom proxy pid が指定されていません。';
        app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(\$customProxyListPath, \$bridgeErrors);
    }

    \$singleGetFunctionName = '';
    if (\$delete === '') {
        \$legacyDbAccessFunctionPidMap = {$exportedLegacyDbAccessFunctionPidMap};
        \$legacySingleGetFunctionPid = app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'SingleGetFuncPID');
        if (\$legacySingleGetFunctionPid !== '') {
            if (array_key_exists(\$legacySingleGetFunctionPid, \$legacyDbAccessFunctionPidMap)) {
                \$singleGetFunctionName = trim((string) (
                    \$legacyDbAccessFunctionPidMap[\$legacySingleGetFunctionPid]['function_name'] ?? ''
                ));
            }

            if (\$singleGetFunctionName === '') {
                \$singleGetFunctionName = \$legacySingleGetFunctionPid;
                \$bridgeErrors[] = 'legacy SingleGetFuncPID を current function name に解決できませんでした。';
            }
        }
    }

    \$hasLegacyTargetList = array_key_exists('TargetProjectSourceOutputPIDList', \$_POST);
    \$currentTargetKeys = [];
    if (\$hasLegacyTargetList) {
        \$legacyTargetPidList = \$_POST['TargetProjectSourceOutputPIDList'] ?? [];
        if (!is_array(\$legacyTargetPidList)) {
            \$legacyTargetPidList = [];
        }

        \$legacySourceOutputPidMap = {$exportedLegacySourceOutputPidMap};
        foreach (\$legacyTargetPidList as \$legacyTargetPid) {
            if (!is_string(\$legacyTargetPid) && !is_int(\$legacyTargetPid)) {
                continue;
            }

            \$normalizedLegacyTargetPid = trim((string) \$legacyTargetPid);
            if (\$normalizedLegacyTargetPid === '') {
                continue;
            }

            if (!array_key_exists(\$normalizedLegacyTargetPid, \$legacySourceOutputPidMap)) {
                continue;
            }

            \$sourceOutputKey = trim((string) \$legacySourceOutputPidMap[\$normalizedLegacyTargetPid]);
            if (\$sourceOutputKey === '') {
                continue;
            }

            \$currentTargetKeys[\$sourceOutputKey] = \$sourceOutputKey;
        }
    }

    \$appRoot = app_html_db_custom_proxy_edit_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(
            \$delete !== '' || \$customProxyKey === '' ? \$customProxyListPath : \$targetPath,
            array_merge(
                \$bridgeErrors,
                [
                    'current custom proxy save を継続する shared bootstrap が見つかりません。current page から再実行してください。',
                ],
            ),
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];

    if (\$delete !== '') {
        \$_SERVER['REQUEST_URI'] = \$customProxyListPath;
        \$_POST = [
            '_csrf' => app_csrf_token(),
            'action' => 'delete',
            'custom_proxy_key' => \$customProxyKey,
        ];
        if (\$bridgeErrors !== []) {
            \$_POST['bridge_errors'] = \$bridgeErrors;
        }

        require_once \$appRoot . '/http.php';
        app_run_http_request();
        return;
    }

    \$_POST = [
        '_csrf' => app_csrf_token(),
        'basename' => app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'basename'),
        'name' => app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'name'),
        'in_transaction' => app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'InTransaction'),
        'auth_type' => app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'AuthType'),
        'single_get_function_name' => \$singleGetFunctionName,
        'continue_even_if_failed_to_insert'
            => app_html_db_custom_proxy_edit_wrapper_param(\$_POST, 'ContinueEvenIfFailedToInsert'),
    ];

    if (\$customProxyKey === '') {
        \$_SERVER['REQUEST_URI'] = \$customProxyListPath;
        \$_POST['action'] = 'create';
        \$_POST['bridge_mode'] = 'legacy-custom-proxy-create';
        \$_POST['custom_proxy_key'] = '';
    } else {
        \$_SERVER['REQUEST_URI'] = \$targetPath;
        \$_POST['bridge_mode'] = 'legacy-custom-proxy-edit';
        \$_POST['custom_proxy_key'] = \$customProxyKey;
    }

    if (\$hasLegacyTargetList) {
        \$_POST['source_output_keys'] = array_values(\$currentTargetKeys);
    }
    if (\$bridgeErrors !== []) {
        \$_POST['bridge_errors'] = \$bridgeErrors;
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_custom_proxy_edit_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        array_merge(
            \$bridgeErrors,
            [
                '未対応の request method です。current custom proxy page からやり直してください。',
            ],
        ),
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCustomProxyPidMap
 */
function app_project_output_html_module_generated_custom_proxy_functions_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCustomProxyPidMap,
    string $relativePath,
    string $description,
): string {
    $prefix = app_project_output_html_module_generated_custom_proxy_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyCustomProxyPidMap,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// {$description}

{$prefix}
\$targetPath = \$customProxyBasePath . '/functions';
header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCustomProxyPidMap
 * @param array<string,array{custom_proxy_key:string,step_id:string}> $legacyCustomProxyStepPidMap
 * @param array<string,array{source_name:string,function_name:string}> $legacyDbAccessFunctionPidMap
 */
function app_project_output_html_module_generated_custom_proxy_step_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCustomProxyPidMap,
    array $legacyCustomProxyStepPidMap,
    array $legacyDbAccessFunctionPidMap,
): string {
    $relativePath = 'da_proxy_custom_func_edit.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedCustomProxyListPath = var_export(
        app_project_output_html_module_default_custom_proxy_list_path($projectKey),
        true,
    );
    $exportedLegacyCustomProxyPidMap = var_export($legacyCustomProxyPidMap, true);
    $exportedLegacyCustomProxyStepPidMap = var_export($legacyCustomProxyStepPidMap, true);
    $exportedLegacyDbAccessFunctionPidMap = var_export($legacyDbAccessFunctionPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_proxy_custom_func_edit.php`.
// GET/HEAD deep links land on the current functions page.
// Legacy create/update/delete POST is translated into current inline step actions.

function app_html_db_custom_proxy_step_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_custom_proxy_step_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_custom_proxy_step_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_custom_proxy_step_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$customProxyListPath = {$exportedCustomProxyListPath};

\$projectPid = app_html_db_custom_proxy_step_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            'legacy ProjectPID が current project route と一致しません。current custom proxy functions page からやり直してください。',
        ],
    );
}
\$legacyCustomProxyPidMap = {$exportedLegacyCustomProxyPidMap};
\$legacyCustomProxyPid = app_html_db_custom_proxy_step_edit_wrapper_param(\$legacyParams, 'daCustomProxyPID');
if (\$legacyCustomProxyPid === '') {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            '更新対象の legacy custom proxy pid が指定されていません。',
        ],
    );
}

if (!array_key_exists(\$legacyCustomProxyPid, \$legacyCustomProxyPidMap)) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            '更新対象の legacy custom proxy pid は current route に解決できませんでした。',
        ],
    );
}

\$customProxyKey = \$legacyCustomProxyPidMap[\$legacyCustomProxyPid];
\$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/proxy/custom/'
    . rawurlencode(\$customProxyKey)
    . '/functions';

if (\$requestMethod === 'POST') {
    \$update = app_html_db_custom_proxy_step_edit_wrapper_param(\$_POST, 'UPDATE');
    \$delete = app_html_db_custom_proxy_step_edit_wrapper_param(\$_POST, 'DELETE');
    if (\$update === '' && \$delete === '') {
        app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy custom proxy step POST に UPDATE / DELETE が無いため、current functions page へ handoff します。',
            ],
        );
    }

    \$legacyStepPid = app_html_db_custom_proxy_step_edit_wrapper_param(\$_POST, 'daCustomProxyFuncPID');
    \$stepId = '';
    \$bridgeErrors = [];
    if (\$legacyStepPid !== '') {
        \$legacyCustomProxyStepPidMap = {$exportedLegacyCustomProxyStepPidMap};
        if (array_key_exists(\$legacyStepPid, \$legacyCustomProxyStepPidMap)) {
            \$stepReference = \$legacyCustomProxyStepPidMap[\$legacyStepPid];
            if ((string) (\$stepReference['custom_proxy_key'] ?? '') === \$customProxyKey) {
                \$stepId = trim((string) (\$stepReference['step_id'] ?? ''));
            }
        }

        if (\$stepId === '') {
            \$stepId = \$legacyStepPid;
        }
    }

    \$appRoot = app_html_db_custom_proxy_step_edit_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            array_merge(
                \$bridgeErrors,
                [
                    'current custom proxy step save を継続する shared bootstrap が見つかりません。current functions page から再実行してください。',
                ],
            ),
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = \$targetPath;
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];

    if (\$delete !== '') {
        if (\$stepId === '') {
            app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
                \$targetPath,
                [
                    \$legacyStepPid === ''
                        ? '削除対象の legacy custom proxy function pid が指定されていません。'
                        : '削除対象の legacy custom proxy function pid は current step id に解決できませんでした。',
                ],
            );
        }

        \$_POST = [
            '_csrf' => app_csrf_token(),
            'action' => 'delete-step',
            'custom_proxy_key' => \$customProxyKey,
            'step_id' => \$stepId,
        ];

        require_once \$appRoot . '/http.php';
        app_run_http_request();
        return;
    }

    \$legacyFunctionPid = app_html_db_custom_proxy_step_edit_wrapper_param(\$_POST, 'dafuncPID');
    \$legacyDbAccessFunctionPidMap = {$exportedLegacyDbAccessFunctionPidMap};
    \$dbAccessSourceName = '';
    \$dbAccessFunctionName = '';
    if (\$legacyFunctionPid !== '' && array_key_exists(\$legacyFunctionPid, \$legacyDbAccessFunctionPidMap)) {
        \$functionReference = \$legacyDbAccessFunctionPidMap[\$legacyFunctionPid];
        \$dbAccessSourceName = trim((string) (\$functionReference['source_name'] ?? ''));
        \$dbAccessFunctionName = trim((string) (\$functionReference['function_name'] ?? ''));
    }
    if (\$dbAccessSourceName === '' || \$dbAccessFunctionName === '') {
        \$dbAccessFunctionName = \$legacyFunctionPid;
        \$bridgeErrors[] = 'legacy dafuncPID を current db access / function に解決できませんでした。';
    }

    \$_POST = [
        '_csrf' => app_csrf_token(),
        'action' => \$stepId === '' ? 'create-step' : 'update-step',
        'custom_proxy_key' => \$customProxyKey,
        'db_access_source_name' => \$dbAccessSourceName,
        'db_access_function_name' => \$dbAccessFunctionName,
        'is_list' => app_html_db_custom_proxy_step_edit_wrapper_param(\$_POST, 'IsList'),
    ];
    if (\$stepId !== '') {
        \$_POST['step_id'] = \$stepId;
    }
    if (\$bridgeErrors !== []) {
        \$_POST['bridge_errors'] = \$bridgeErrors;
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_custom_proxy_step_edit_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        array_merge(
            \$bridgeErrors,
            [
                '未対応の request method です。current custom proxy functions page からやり直してください。',
            ],
        ),
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCustomProxyPidMap
 * @param array<string,array{custom_proxy_key:string,step_id:string}> $legacyCustomProxyStepPidMap
 */
function app_project_output_html_module_generated_custom_proxy_functions_change_order_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCustomProxyPidMap,
    array $legacyCustomProxyStepPidMap,
): string {
    $relativePath = 'da_proxy_custom_func_change_order.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedCustomProxyListPath = var_export(
        app_project_output_html_module_default_custom_proxy_list_path($projectKey),
        true,
    );
    $exportedLegacyCustomProxyPidMap = var_export($legacyCustomProxyPidMap, true);
    $exportedLegacyCustomProxyStepPidMap = var_export($legacyCustomProxyStepPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `da_proxy_custom_func_change_order.php`.
// Preview-only GET requests are redirected to the current functions page, where step order is edited inline.
// Legacy sort-order update actions are translated into current reorder/reset actions.

function app_html_db_custom_proxy_change_order_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_custom_proxy_change_order_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_custom_proxy_change_order_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_custom_proxy_change_order_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_custom_proxy_change_order_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$customProxyListPath = {$exportedCustomProxyListPath};

\$projectPid = app_html_db_custom_proxy_change_order_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_change_order_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            'legacy ProjectPID が current project route と一致しません。current custom proxy functions page からやり直してください。',
        ],
    );
}
\$legacyCustomProxyPidMap = {$exportedLegacyCustomProxyPidMap};
\$legacyCustomProxyPid = app_html_db_custom_proxy_change_order_wrapper_param(\$legacyParams, 'daCustomProxyPID');
if (\$legacyCustomProxyPid === '') {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_change_order_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            '更新対象の legacy custom proxy pid が指定されていません。',
        ],
    );
}

if (!array_key_exists(\$legacyCustomProxyPid, \$legacyCustomProxyPidMap)) {
    if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
        header('Cache-Control: no-store');
        header('Location: ' . \$customProxyListPath);
        exit;
    }

    app_html_db_custom_proxy_change_order_wrapper_redirect_with_bridge_errors(
        \$customProxyListPath,
        [
            '更新対象の legacy custom proxy pid は current route に解決できませんでした。',
        ],
    );
}

\$customProxyKey = \$legacyCustomProxyPidMap[\$legacyCustomProxyPid];
\$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/proxy/custom/'
    . rawurlencode(\$customProxyKey)
    . '/functions';

\$newSortOrder = app_html_db_custom_proxy_change_order_wrapper_param(\$legacyParams, 'NewSortOrder');
\$doReset = app_html_db_custom_proxy_change_order_wrapper_param(\$legacyParams, 'doReset');

if (\$newSortOrder !== '' || \$doReset !== '') {
    \$appRoot = app_html_db_custom_proxy_change_order_wrapper_app_root();
    if (\$appRoot === '') {
        app_html_db_custom_proxy_change_order_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'current custom proxy reorder を継続する shared bootstrap が見つかりません。current functions page から再実行してください。',
            ],
        );
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/session.php';
    require_once \$appRoot . '/csrf.php';

    \$app = app_bootstrap();
    app_boot_session(\$app);

    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = \$targetPath;
    \$_SERVER['QUERY_STRING'] = '';
    \$_GET = [];

    if (\$doReset !== '') {
        \$_POST = [
            '_csrf' => app_csrf_token(),
            'action' => 'reset-step-order',
            'custom_proxy_key' => \$customProxyKey,
        ];
    } else {
        \$legacyCustomProxyStepPidMap = {$exportedLegacyCustomProxyStepPidMap};
        \$legacyStepPidList = preg_split('/,+/', \$newSortOrder) ?: [];
        \$currentStepIdList = [];
        foreach (\$legacyStepPidList as \$legacyStepPid) {
            \$normalizedLegacyStepPid = trim((string) \$legacyStepPid);
            if (\$normalizedLegacyStepPid === '') {
                continue;
            }

            \$stepId = '';
            if (array_key_exists(\$normalizedLegacyStepPid, \$legacyCustomProxyStepPidMap)) {
                \$stepReference = \$legacyCustomProxyStepPidMap[\$normalizedLegacyStepPid];
                if ((string) (\$stepReference['custom_proxy_key'] ?? '') === \$customProxyKey) {
                    \$stepId = trim((string) (\$stepReference['step_id'] ?? ''));
                }
            }

            \$currentStepIdList[] = \$stepId !== '' ? \$stepId : \$normalizedLegacyStepPid;
        }

        \$_POST = [
            '_csrf' => app_csrf_token(),
            'action' => 'reorder-steps',
            'custom_proxy_key' => \$customProxyKey,
            'step_ids_csv' => implode(',', \$currentStepIdList),
        ];
    }

    require_once \$appRoot . '/http.php';
    app_run_http_request();
    return;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    app_html_db_custom_proxy_change_order_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current custom proxy functions page からやり直してください。',
        ],
    );
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCompareOutputPidMap
 */
function app_project_output_html_module_generated_compare_output_route_prefix_text(
    string $projectKey,
    int $legacyProjectPid,
    string $legacyFallbackRelativePath,
    array $legacyCompareOutputPidMap,
    bool $fallbackOnNonGet = false,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedLegacyFallback = var_export($legacyFallbackRelativePath, true);
    $exportedCompareOutputRootPath = var_export(
        app_project_output_html_module_default_compare_output_settings_path($projectKey),
        true,
    );
    $exportedCompareOutputPidMap = var_export($legacyCompareOutputPidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);
    $exportedFallbackOnNonGet = var_export($fallbackOnNonGet, true);

    return <<<PHP
\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ({$exportedFallbackOnNonGet} && \$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    require_once __DIR__ . '/' . {$exportedLegacyFallback};
    return;
}
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$compareOutputSettingsRootPath = {$exportedCompareOutputRootPath};

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . \$compareOutputSettingsRootPath);
    exit;
}

\$legacyCompareOutputPidMap = {$exportedCompareOutputPidMap};
\$legacyCompareOutputPid = isset(\$legacyParams['CompareOutputPID']) ? trim((string) \$legacyParams['CompareOutputPID']) : '';

if (\$legacyCompareOutputPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$compareOutputSettingsRootPath);
    exit;
}

if (!array_key_exists(\$legacyCompareOutputPid, \$legacyCompareOutputPidMap)) {
    header('Cache-Control: no-store');
    header('Location: ' . \$compareOutputSettingsRootPath);
    exit;
}

\$compareOutputKey = \$legacyCompareOutputPidMap[\$legacyCompareOutputPid];
\$compareOutputSettingsPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/compare-output-settings?compare_output_key='
    . rawurlencode(\$compareOutputKey);
\$compareOutputAdditionalPathsPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
    . '/compare-output-settings/additional-paths?compare_output_key='
    . rawurlencode(\$compareOutputKey);
PHP;
}

/**
 * @param array<string,string> $legacyCompareOutputPidMap
 */
function app_project_output_html_module_generated_compare_output_settings_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCompareOutputPidMap,
    string $relativePath,
    string $description,
    bool $fallbackOnNonGet = false,
): string {
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_compare_output_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyCompareOutputPidMap,
        $fallbackOnNonGet,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// {$description}

{$prefix}
header('Cache-Control: no-store');
header('Location: ' . \$compareOutputSettingsPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCompareOutputPidMap
 */
function app_project_output_html_module_generated_compare_output_additional_paths_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCompareOutputPidMap,
): string {
    $relativePath = 'compare_output_additional_path.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_compare_output_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyCompareOutputPidMap,
        false,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `compare_output_additional_path.php`.
// Known legacy `CompareOutputPID` values are redirected to the current additional-paths page.

{$prefix}
header('Cache-Control: no-store');
header('Location: ' . \$compareOutputAdditionalPathsPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyCompareOutputPidMap
 * @param array<string,array{compare_output_key:string,additional_path_key:string}> $legacyCompareOutputAdditionalPathPidMap
 */
function app_project_output_html_module_generated_compare_output_additional_path_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyCompareOutputPidMap,
    array $legacyCompareOutputAdditionalPathPidMap,
): string {
    $relativePath = 'compare_output_additional_path_edit.php';
    $legacyFallbackRelativePath = '_legacy/' . $relativePath;
    $prefix = app_project_output_html_module_generated_compare_output_route_prefix_text(
        $projectKey,
        $legacyProjectPid,
        $legacyFallbackRelativePath,
        $legacyCompareOutputPidMap,
        true,
    );
    $exportedAdditionalPathPidMap = var_export($legacyCompareOutputAdditionalPathPidMap, true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `compare_output_additional_path_edit.php`.
// Add-flow deep links are redirected to the current additional-paths page, where creation now lives inline.
// Existing legacy additional-path item PID deep links are redirected to the selected current row when a canonical key can be resolved.
// Mismatched legacy row bindings now reduce to the selected current additional-path list.

{$prefix}
\$legacyCompareOutputAdditionalPathPidMap = {$exportedAdditionalPathPidMap};
\$legacyAdditionalPathPid = isset(\$_GET['CompareOutputAdditionalPathPID'])
    ? trim((string) \$_GET['CompareOutputAdditionalPathPID'])
    : '';
\$targetPath = \$compareOutputAdditionalPathsPath;

if (\$legacyAdditionalPathPid === '') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (array_key_exists(\$legacyAdditionalPathPid, \$legacyCompareOutputAdditionalPathPidMap)) {
    \$additionalPathReference = \$legacyCompareOutputAdditionalPathPidMap[\$legacyAdditionalPathPid];
    if (\$additionalPathReference['compare_output_key'] === \$compareOutputKey) {
        \$targetPath .= '&additional_path_key=' . rawurlencode(\$additionalPathReference['additional_path_key']);
    }
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

function app_project_output_html_module_generated_compare_output_run_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_compare_output_run_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `compare_output_do.php`.
// The current compare-output run flow lives at `/runs/compare-output/{project_key}`.
// Unsupported verbs and project mismatches also reduce to the current run route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;

\$projectPid = isset(\$legacyParams['ProjectPID']) ? trim((string) \$legacyParams['ProjectPID']) : '';
\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

if (\$requestMethod !== 'GET' && \$requestMethod !== 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedTargetPath});
exit;

PHP;
}

function app_project_output_html_module_generated_build_run_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    bool $detailedView = false,
    string $relativePath = 'build_project.php',
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_build_run_path($projectKey, $detailedView),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// The current build run flow lives at `/runs/builds/{project_key}` and records a completed job manifest after `generate + publish`.
// Known legacy project requests are redirected there regardless of the original GET/POST entrypoint.

\$projectPid = '';
if (isset(\$_POST['ProjectPID'])) {
    \$projectPid = trim((string) \$_POST['ProjectPID']);
} elseif (isset(\$_GET['ProjectPID'])) {
    \$projectPid = trim((string) \$_GET['ProjectPID']);
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . {$exportedTargetPath});
exit;

PHP;
}

function app_project_output_html_module_generated_build_run_ajax_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_build_run_path($projectKey),
        true,
    );
    $exportedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `build_project_ajax.php`.
// The legacy incremental build AJAX worker is retired. Known project requests are handed off to the current build run route.
// GET/HEAD requests redirect there, while POST requests return an HTML notice for callers that still expect a progress fragment.
// Project mismatches are also reduced to the current handoff.

\$projectPid = '';
if (isset(\$_POST['ProjectPID'])) {
    \$projectPid = trim((string) \$_POST['ProjectPID']);
} elseif (isset(\$_GET['ProjectPID'])) {
    \$projectPid = trim((string) \$_GET['ProjectPID']);
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$projectPid = '';
}

\$targetPath = {$exportedTargetPath};
\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Content-Type: text/html; charset=UTF-8');

\$projectKey = {$exportedProjectKey};
\$escapedProjectKey = htmlspecialchars(\$projectKey, ENT_QUOTES, 'UTF-8');
\$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');

echo '<section class="build-ajax-handoff">';
echo '<h3>Build Run Moved</h3>';
echo '<p>Legacy build_project_ajax.php is no longer executed inside the generated artifact for project <code>' . \$escapedProjectKey . '</code>.</p>';
echo '<p>Open <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a> and run the build from the current screen.</p>';
echo '<p>The current flow records a completed job manifest instead of using BuildToken polling.</p>';
echo '</section>';

PHP;
}

function app_project_output_html_module_generated_build_run_ajax_check_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_build_run_path($projectKey),
        true,
    );
    $exportedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `build_project_ajax_check_if_completed.php`.
// The current build run flow is synchronous from the UI perspective, so legacy BuildToken polling now completes immediately.
// GET/HEAD requests redirect to the current build screen, while POST returns a compact JSON handoff payload.
// Project mismatches are also reduced to the current handoff.

\$projectPid = '';
if (isset(\$_POST['ProjectPID'])) {
    \$projectPid = trim((string) \$_POST['ProjectPID']);
} elseif (isset(\$_GET['ProjectPID'])) {
    \$projectPid = trim((string) \$_GET['ProjectPID']);
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$projectPid = '';
}

\$targetPath = {$exportedTargetPath};
\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Content-Type: application/json; charset=UTF-8');

echo json_encode(
    [
        'IsCompleted' => true,
        '_status' => 'OK',
        'Message' => 'Legacy BuildToken polling has moved to the current build run screen.',
        'ProjectKey' => {$exportedProjectKey},
        'RedirectPath' => \$targetPath,
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
);

PHP;
}

function app_project_output_html_module_generated_compare_output_run_ajax_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_compare_output_run_path($projectKey),
        true,
    );
    $exportedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `compare_output_do_ajax.php`.
// The legacy AJAX batch runner is retired. Known project requests are handed off to the current compare-output run route.
// GET/HEAD requests redirect there, while POST requests return a small HTML notice for callers that still expect an HTML fragment.
// Project mismatches are also reduced to the current handoff.

\$projectPid = '';
if (isset(\$_POST['ProjectPID'])) {
    \$projectPid = trim((string) \$_POST['ProjectPID']);
} elseif (isset(\$_GET['ProjectPID'])) {
    \$projectPid = trim((string) \$_GET['ProjectPID']);
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$projectPid = '';
}

\$targetPath = {$exportedTargetPath};
\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

header('Cache-Control: no-store');
header('Content-Type: text/html; charset=UTF-8');

\$projectKey = {$exportedProjectKey};
\$escapedProjectKey = htmlspecialchars(\$projectKey, ENT_QUOTES, 'UTF-8');
\$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');

echo '<section class="compare-output-ajax-handoff">';
echo '<h3>Compare Output Run Moved</h3>';
echo '<p>Legacy compare_output_do_ajax.php is no longer executed inside the generated artifact for project <code>' . \$escapedProjectKey . '</code>.</p>';
echo '<p>Open <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a> and run compare output from the current screen.</p>';
echo '<p>Template assets and ignore rules are now managed from the current admin compare-output settings screen.</p>';
echo '</section>';
exit;

PHP;
}

function app_project_output_html_module_generated_endpoint_test_ajax_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedProjectKey = var_export(app_normalize_project_key($projectKey), true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_endpoint_test_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `endpoint_test_json_ajax.php`.
// The current endpoint test runner lives at `/runs/endpoints/{project_key}`.
// GET/HEAD preview requests are redirected there.
// Known-project POST requests are executed by the current endpoint-test job service and rendered in the preserved legacy HTML fragment format.

function app_html_db_endpoint_test_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_endpoint_test_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_endpoint_test_wrapper_handoff_state(
    string \$path,
    string \$endpointUrl = '',
    string \$requestJson = ''
): array {
    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$normalizedEndpointUrl = trim(\$endpointUrl);
    if (\$normalizedEndpointUrl !== '') {
        \$query['endpoint_url'] = \$normalizedEndpointUrl;
    }

    \$normalizedRequestJson = trim(\$requestJson);
    \$requestJsonPrefilled = false;
    if (\$normalizedRequestJson !== '' && strlen(\$normalizedRequestJson) <= 2000) {
        \$query['request_json'] = \$normalizedRequestJson;
        \$requestJsonPrefilled = true;
    }

    \$resolvedPath = \$pathParts[0];
    if (\$query !== []) {
        \$resolvedPath .= '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
    }

    return [
        'path' => \$resolvedPath,
        'request_json_prefilled' => \$requestJsonPrefilled,
    ];
}

function app_html_db_endpoint_test_wrapper_render_handoff(
    string \$title,
    string \$message,
    string \$targetPath,
    bool \$requestJsonOmitted = false
): void {
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');

    \$escapedTitle = htmlspecialchars(\$title, ENT_QUOTES, 'UTF-8');
    \$escapedMessage = htmlspecialchars(\$message, ENT_QUOTES, 'UTF-8');
    \$escapedTargetPath = htmlspecialchars(\$targetPath, ENT_QUOTES, 'UTF-8');

    echo '<section class="endpoint-test-handoff">';
    echo '<h3>' . \$escapedTitle . '</h3>';
    echo '<p>' . \$escapedMessage . '</p>';
    echo '<p>Open <a href="' . \$escapedTargetPath . '">' . \$escapedTargetPath . '</a> to continue from the current endpoint test screen.</p>';
    if (\$requestJsonOmitted) {
        echo '<p>request JSON is not prefilled in the link because the legacy payload is too large for a safe query string handoff.</p>';
    }
    echo '</section>';
}

function app_html_db_endpoint_test_wrapper_render_notice(string \$message): void
{
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');

    echo '<h3>' . htmlspecialchars(\$message, ENT_QUOTES, 'UTF-8') . '</h3>';
}

function app_html_db_endpoint_test_wrapper_render_result(
    string \$status,
    string \$message,
    string \$responseBody = '',
    string \$responsePretty = ''
): void {
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=UTF-8');

    if (\$status === 'OK') {
        echo '<p>Original Result:</p>';
        echo '<pre>' . htmlspecialchars(\$responseBody, ENT_QUOTES, 'UTF-8') . '</pre>';

        if (\$responsePretty !== '') {
            echo '<p>Result with Format:</p>';
            echo '<pre>' . htmlspecialchars(\$responsePretty, ENT_QUOTES, 'UTF-8') . '</pre>';
        }

        return;
    }

    echo '<p>Result: ' . htmlspecialchars(\$status, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '<p>' . htmlspecialchars(\$message, ENT_QUOTES, 'UTF-8') . '</p>';
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$projectPid = app_html_db_endpoint_test_wrapper_param(\$_POST, 'ProjectPID');
if (\$projectPid === '') {
    \$projectPid = app_html_db_endpoint_test_wrapper_param(\$_GET, 'ProjectPID');
}
\$proxyUrl = app_html_db_endpoint_test_wrapper_param(\$_POST, 'ProxyURL');
if (\$proxyUrl === '') {
    \$proxyUrl = app_html_db_endpoint_test_wrapper_param(\$_GET, 'ProxyURL');
}
\$postJson = app_html_db_endpoint_test_wrapper_param(\$_POST, 'POST_JSON');
if (\$postJson === '') {
    \$postJson = app_html_db_endpoint_test_wrapper_param(\$_GET, 'POST_JSON');
}
\$handoffState = app_html_db_endpoint_test_wrapper_handoff_state(
    {$exportedTargetPath},
    \$proxyUrl,
    \$postJson,
);
\$targetPath = \$handoffState['path'];
\$requestJsonOmitted = trim(\$postJson) !== '' && \$handoffState['request_json_prefilled'] !== true;

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$requestMethod !== 'POST') {
    app_html_db_endpoint_test_wrapper_render_handoff(
        'Endpoint Test Moved',
        'This legacy endpoint only supports GET/HEAD redirect or POST bridge on the generated artifact.',
        \$targetPath,
        \$requestJsonOmitted,
    );
    return;
}

if (\$expectedProjectPid !== '' && \$projectPid === '') {
    app_html_db_endpoint_test_wrapper_render_handoff(
        'ProjectPID Required',
        'The legacy endpoint test POST did not include ProjectPID, so the generated artifact is handing the request off to the current route instead of invoking preserved legacy runtime.',
        \$targetPath,
        \$requestJsonOmitted,
    );
    return;
}

if (\$expectedProjectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    app_html_db_endpoint_test_wrapper_render_handoff(
        'Project Mismatch',
        'The legacy ProjectPID does not match this generated artifact. Re-run the endpoint test from the current project-scoped screen.',
        \$targetPath,
        \$requestJsonOmitted,
    );
    return;
}

\$appRoot = app_html_db_endpoint_test_wrapper_app_root();
if (\$appRoot === '') {
    app_html_db_endpoint_test_wrapper_render_handoff(
        'Endpoint Test Bootstrap Missing',
        'Current shared bootstrap was not found from this generated artifact, so endpoint execution cannot continue here.',
        \$targetPath,
        \$requestJsonOmitted,
    );
    return;
}

require_once \$appRoot . '/bootstrap.php';
require_once \$appRoot . '/session.php';
require_once \$appRoot . '/auth.php';
require_once \$appRoot . '/project_repository.php';
require_once \$appRoot . '/endpoint_test_job_service.php';

\$app = app_bootstrap();
app_boot_session(\$app);

\$principal = app_auth_principal();
if (\$principal === null) {
    app_html_db_endpoint_test_wrapper_render_notice('認証が必要です。');
    return;
}

if (!app_auth_has_any_role(['admin', 'config', 'lab'], \$principal)) {
    app_html_db_endpoint_test_wrapper_render_notice('endpoint test の実行権限がありません。');
    return;
}

\$projectKey = {$exportedProjectKey};
try {
    \$projectResult = app_fetch_project_by_key(\$app, \$projectKey);
    if (!\$projectResult['ok']) {
        app_html_db_endpoint_test_wrapper_render_result(
            'NG',
            \$projectResult['error'] !== '' ? \$projectResult['error'] : 'project の読み込みに失敗しました。',
        );
        return;
    }

    if (!is_array(\$projectResult['item'] ?? null)) {
        app_html_db_endpoint_test_wrapper_render_result(
            'NG',
            'project が current repository 上で見つかりません。',
        );
        return;
    }

    \$postJsonObject = json_decode(\$postJson);
    if (\$postJsonObject == null) {
        app_html_db_endpoint_test_wrapper_render_result('NG', 'Failed to decode JSON');
        return;
    }

    \$jobResult = app_endpoint_test_job_create(
        \$app,
        \$projectKey,
        [
            'endpoint_url' => \$proxyUrl,
            'request_json' => \$postJson,
        ],
        'legacy-endpoint-test-wrapper:' . \$principal['id'],
    );

    if (!\$jobResult['ok'] || !is_array(\$jobResult['job'] ?? null)) {
        app_html_db_endpoint_test_wrapper_render_result(
            'NG',
            trim((string) (\$jobResult['error'] ?? '')) !== ''
                ? trim((string) \$jobResult['error'])
                : 'endpoint test job の作成に失敗しました。',
        );
        return;
    }

    \$job = \$jobResult['job'];
    if ((string) (\$job['status'] ?? '') === 'completed') {
        \$legacyResponsePretty = '';
        \$responseJsonObject = json_decode((string) (\$job['response_body'] ?? ''));
        if (\$responseJsonObject != null) {
            \$legacyResponsePretty = json_encode(\$responseJsonObject, JSON_PRETTY_PRINT);
            if (!is_string(\$legacyResponsePretty)) {
                \$legacyResponsePretty = '';
            }
        }

        app_html_db_endpoint_test_wrapper_render_result(
            'OK',
            'Successfully Called',
            (string) (\$job['response_body'] ?? ''),
            \$legacyResponsePretty,
        );
        return;
    }

    \$httpCode = (int) (\$job['http_code'] ?? 0);
    \$message = \$httpCode > 0
        ? 'Error Occured while request. Http Code is: ' . \$httpCode
        : trim((string) (\$job['error_message'] ?? ''));
    if (\$message === '') {
        \$message = 'endpoint request に失敗しました。';
    }

    app_html_db_endpoint_test_wrapper_render_result('NG', \$message);
    return;
} catch (Throwable \$throwable) {
    app_html_db_endpoint_test_wrapper_render_result('NG', \$throwable->getMessage());
    return;
}

PHP;
}

function app_project_output_html_module_generated_lang_res_groups_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_language_resource_groups_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res.php`.
// The legacy language-resource group landing page reduces to the current groups summary route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$projectPid = '';
if (isset(\$legacyParams['ProjectPID']) && !is_array(\$legacyParams['ProjectPID'])) {
    \$projectPid = trim((string) \$legacyParams['ProjectPID']);
}
\$groupPid = '';
if (isset(\$legacyParams['PID']) && !is_array(\$legacyParams['PID'])) {
    \$groupPid = trim((string) \$legacyParams['PID']);
}

\$targetPath = {$exportedTargetPath};
if (\$groupPid !== '' && ctype_digit(\$groupPid) && (int) \$groupPid > 0) {
    \$targetPath .= '?group_pid=' . rawurlencode(\$groupPid);
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedTargetPath});
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

function app_project_output_html_module_generated_lang_res_list_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_language_resource_list_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res_list.php`.
// Legacy group-scoped list links now reduce to the current language-resource list route.

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$projectPid = '';
if (isset(\$legacyParams['ProjectPID']) && !is_array(\$legacyParams['ProjectPID'])) {
    \$projectPid = trim((string) \$legacyParams['ProjectPID']);
}
\$groupPid = '';
if (isset(\$legacyParams['LanguageResourceGroupPID']) && !is_array(\$legacyParams['LanguageResourceGroupPID'])) {
    \$groupPid = trim((string) \$legacyParams['LanguageResourceGroupPID']);
}

\$targetPath = {$exportedListPath};
if (\$groupPid !== '' && ctype_digit(\$groupPid) && (int) \$groupPid > 0) {
    \$targetPath .= '?group_pid=' . rawurlencode(\$groupPid);
}

\$expectedProjectPid = {$exportedLegacyProjectPid};
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

header('Cache-Control: no-store');
header('Location: ' . \$targetPath);
exit;

PHP;
}

/**
 * @param array<string,string> $legacyLanguageResourcePidMap
 * @param array<string,array{
 *     legacy_resource_pid:int,
 *     resource_key:string
 * }> $legacyLanguageResourceKeyNameMap
 */
function app_project_output_html_module_generated_lang_res_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyLanguageResourcePidMap,
    array $legacyLanguageResourceKeyNameMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_language_resource_list_path($projectKey),
        true,
    );
    $exportedPidMap = var_export($legacyLanguageResourcePidMap, true);
    $exportedKeyNameMap = var_export($legacyLanguageResourceKeyNameMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res_edit.php`.
// Legacy add/edit/delete/duplicate resource flow is translated into the current language-resource list/detail routes.

function app_html_db_lang_res_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_lang_res_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

/**
 * @return array{
 *     pid_map:array<string,string>,
 *     key_name_map:array<string,array{
 *         legacy_resource_pid:int,
 *         resource_key:string
 *     }>
 * }
 */
function app_html_db_lang_res_edit_wrapper_dynamic_maps(
    string \$appRoot,
    string \$projectKey,
    string \$legacyProjectPid
): array {
    \$empty = [
        'pid_map' => [],
        'key_name_map' => [],
    ];
    if (
        \$appRoot === ''
        || !is_file(\$appRoot . '/bootstrap.php')
        || !is_file(\$appRoot . '/project_language_resource_catalog_loader.php')
    ) {
        return \$empty;
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/project_language_resource_catalog_loader.php';

    \$app = app_bootstrap();
    \$catalogResult = app_fetch_project_language_resource_catalog(
        \$app,
        \$projectKey,
        (int) \$legacyProjectPid,
    );
    \$catalog = \$catalogResult['item'] ?? null;
    if (!\$catalogResult['ok'] || !is_array(\$catalog)) {
        return \$empty;
    }

    \$pidMap = [];
    \$keyNameMap = [];
    foreach (\$catalog['resources'] ?? [] as \$resource) {
        if (!is_array(\$resource)) {
            continue;
        }

        \$legacyResourcePid = (int) (\$resource['legacy_resource_pid'] ?? 0);
        \$resourceKey = trim((string) (\$resource['resource_key'] ?? ''));
        if (\$legacyResourcePid <= 0 || \$resourceKey === '') {
            continue;
        }

        \$pidMap[(string) \$legacyResourcePid] = \$resourceKey;
        \$keyName = trim((string) (\$resource['key_name'] ?? ''));
        if (\$keyName !== '') {
            \$keyNameMap[\$keyName] = [
                'legacy_resource_pid' => \$legacyResourcePid,
                'resource_key' => \$resourceKey,
            ];
        }
    }

    return [
        'pid_map' => \$pidMap,
        'key_name_map' => \$keyNameMap,
    ];
}

function app_html_db_lang_res_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header('Location: ' . app_html_db_lang_res_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors));
    exit;
}

/**
 * @return array<string,array{
 *     caption:string,
 *     caption_auto_translated:string
 * }>
 */
function app_html_db_lang_res_edit_wrapper_caption_inputs(array \$source): array
{
    \$items = [];
    foreach (\$source as \$name => \$rawValue) {
        if (!is_string(\$name) || preg_match('/^Caption(\d+)$/', \$name, \$matches) !== 1) {
            continue;
        }

        if (is_array(\$rawValue)) {
            continue;
        }

        \$legacyLanguagePid = (string) ((int) (\$matches[1] ?? 0));
        if ((int) \$legacyLanguagePid <= 0) {
            continue;
        }

        \$items[\$legacyLanguagePid] = [
            'caption' => trim((string) \$rawValue),
            'caption_auto_translated' => app_html_db_lang_res_edit_wrapper_param(
                \$source,
                'Caption' . \$legacyLanguagePid . 'AutoTranslated',
            ),
        ];
    }

    ksort(\$items, SORT_NATURAL);

    return \$items;
}

function app_html_db_lang_res_edit_wrapper_list_path(
    string \$basePath,
    string \$groupPid,
    string \$intent = '',
    string \$sourceResourceKey = ''
): string {
    \$query = [];
    if (\$groupPid !== '' && ctype_digit(\$groupPid) && (int) \$groupPid > 0) {
        \$query['group_pid'] = \$groupPid;
    }
    if (\$intent !== '') {
        \$query['intent'] = \$intent;
    }
    if (\$sourceResourceKey !== '') {
        \$query['source_resource_key'] = \$sourceResourceKey;
    }

    if (\$query === []) {
        return \$basePath;
    }

    return \$basePath . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$legacyLanguageResourcePidMap = {$exportedPidMap};
\$legacyLanguageResourceKeyNameMap = {$exportedKeyNameMap};
\$legacyGroupPid = app_html_db_lang_res_edit_wrapper_param(\$legacyParams, 'LanguageResourceGroupPID');
\$legacyResourcePid = app_html_db_lang_res_edit_wrapper_param(\$legacyParams, 'PID');
\$resolvedLegacyResourcePid = \$legacyResourcePid;
\$pidByKeyName = app_html_db_lang_res_edit_wrapper_param(\$legacyParams, 'PID_BY_KEYNAME');
\$duplicate = app_html_db_lang_res_edit_wrapper_param(\$legacyParams, 'duplicate') !== '';
\$legacySourceResourcePid = app_html_db_lang_res_edit_wrapper_param(\$legacyParams, 'SourceResourcePID');
\$projectPid = app_html_db_lang_res_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
\$appRoot = app_html_db_lang_res_edit_wrapper_app_root();
\$dynamicMaps = [
    'pid_map' => [],
    'key_name_map' => [],
];
if (
    (\$legacyResourcePid !== '' && !array_key_exists(\$legacyResourcePid, \$legacyLanguageResourcePidMap))
    || (\$pidByKeyName !== '' && !array_key_exists(\$pidByKeyName, \$legacyLanguageResourceKeyNameMap))
    || (\$legacySourceResourcePid !== '' && !array_key_exists(\$legacySourceResourcePid, \$legacyLanguageResourcePidMap))
) {
    \$dynamicMaps = app_html_db_lang_res_edit_wrapper_dynamic_maps(
        \$appRoot,
        {$exportedNormalizedProjectKey},
        \$expectedProjectPid,
    );
}
\$resourceKey = '';
if (\$legacyResourcePid !== '') {
    if (array_key_exists(\$legacyResourcePid, \$legacyLanguageResourcePidMap)) {
        \$resourceKey = \$legacyLanguageResourcePidMap[\$legacyResourcePid];
    } elseif (array_key_exists(\$legacyResourcePid, \$dynamicMaps['pid_map'])) {
        \$resourceKey = (string) (\$dynamicMaps['pid_map'][\$legacyResourcePid] ?? '');
    }
}
if (\$resourceKey === '' && \$pidByKeyName !== '') {
    if (array_key_exists(\$pidByKeyName, \$legacyLanguageResourceKeyNameMap)) {
        \$resourceKey = (string) (\$legacyLanguageResourceKeyNameMap[\$pidByKeyName]['resource_key'] ?? '');
        \$resolvedLegacyResourcePid = (string) ((int) (\$legacyLanguageResourceKeyNameMap[\$pidByKeyName]['legacy_resource_pid'] ?? 0));
    } elseif (array_key_exists(\$pidByKeyName, \$dynamicMaps['key_name_map'])) {
        \$resourceKey = (string) (\$dynamicMaps['key_name_map'][\$pidByKeyName]['resource_key'] ?? '');
        \$resolvedLegacyResourcePid = (string) ((int) (\$dynamicMaps['key_name_map'][\$pidByKeyName]['legacy_resource_pid'] ?? 0));
    }
}

\$sourceResourceKey = '';
if (\$legacySourceResourcePid !== '') {
    if (array_key_exists(\$legacySourceResourcePid, \$legacyLanguageResourcePidMap)) {
        \$sourceResourceKey = \$legacyLanguageResourcePidMap[\$legacySourceResourcePid];
    } elseif (array_key_exists(\$legacySourceResourcePid, \$dynamicMaps['pid_map'])) {
        \$sourceResourceKey = (string) (\$dynamicMaps['pid_map'][\$legacySourceResourcePid] ?? '');
    }
}

\$baseListPath = {$exportedListPath};
\$createListPath = app_html_db_lang_res_edit_wrapper_list_path(
    \$baseListPath,
    \$legacyGroupPid,
    (\$legacyGroupPid !== '' || \$duplicate) ? 'create' : '',
);
if (\$legacyGroupPid === '' && !\$duplicate) {
    \$createListPath = \$baseListPath;
}
if (\$duplicate) {
    \$targetPath = app_html_db_lang_res_edit_wrapper_list_path(
        \$baseListPath,
        \$legacyGroupPid,
        'duplicate',
        \$sourceResourceKey,
    );
} elseif (\$resourceKey !== '') {
    \$targetPath = '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/language-resources/'
        . rawurlencode(\$resourceKey);
} else {
    \$targetPath = \$createListPath;
}

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
        app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy ProjectPID が current project route と一致しません。current language resource page からやり直してください。',
            ],
        );
    }

    if (\$legacySourceResourcePid !== '' && \$sourceResourceKey === '') {
        app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
            \$baseListPath,
            [
                '指定された複製元の legacy resource pid は current route に解決できませんでした。',
            ],
        );
    }

    if ((\$legacyResourcePid !== '' || \$pidByKeyName !== '') && \$resourceKey === '' && !\$duplicate) {
        app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
            \$baseListPath,
            [
                '指定された legacy resource は current detail route に解決できませんでした。',
            ],
        );
    }

    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$requestMethod !== 'POST') {
    app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current language resource page からやり直してください。',
        ],
    );
}

\$update = app_html_db_lang_res_edit_wrapper_param(\$_POST, 'UPDATE');
\$delete = app_html_db_lang_res_edit_wrapper_param(\$_POST, 'DELETE');
\$bridgeErrors = [];
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$bridgeErrors[] = 'legacy ProjectPID が current project route と一致しません。';
}
if (\$update === '' && \$delete === '') {
    \$bridgeErrors[] = 'legacy resource edit POST に UPDATE / DELETE が無いため、current route へ handoff できませんでした。';
}
if (\$delete !== '' && \$resourceKey === '') {
    \$bridgeErrors[] = '削除対象の legacy resource は current detail route に解決できませんでした。';
}
if (\$update !== '' && (\$legacyResourcePid !== '' || \$pidByKeyName !== '') && \$resourceKey === '' && !\$duplicate) {
    \$bridgeErrors[] = '更新対象の legacy resource は current detail route に解決できませんでした。';
}
if (\$update !== '' && \$legacySourceResourcePid !== '' && \$sourceResourceKey === '' && \$resourceKey === '') {
    \$bridgeErrors[] = '指定された複製元の legacy resource pid は current route に解決できませんでした。';
}

if (\$bridgeErrors !== []) {
    app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
        \$resourceKey !== '' ? \$targetPath : \$baseListPath,
        \$bridgeErrors,
    );
}

\$bridgeErrors[] = 'LanguageResource の current route は read-only inspector に切り替わりました。保存・削除・複製は current admin では扱わず、repo 配下の JSON file を直接編集してください。';
app_html_db_lang_res_edit_wrapper_redirect_with_bridge_errors(
    \$resourceKey !== '' ? \$targetPath : \$baseListPath,
    \$bridgeErrors,
);

PHP;
}

function app_project_output_html_module_generated_lang_res_group_edit_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedTargetPath = var_export(
        app_project_output_html_module_default_language_resource_groups_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res_group_edit.php`.
// Legacy group add/edit/delete flow is translated into the current language-resource groups route.

function app_html_db_lang_res_group_edit_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_lang_res_group_edit_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

function app_html_db_lang_res_group_edit_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_lang_res_group_edit_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header(
        'Location: '
        . app_html_db_lang_res_group_edit_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors)
    );
    exit;
}

/**
 * @return list<string>
 */
function app_html_db_lang_res_group_edit_wrapper_pid_list(\$rawValue): array
{
    if (is_array(\$rawValue)) {
        \$rawItems = \$rawValue;
    } elseif (is_string(\$rawValue) || is_numeric(\$rawValue)) {
        \$rawItems = [\$rawValue];
    } else {
        return [];
    }

    \$items = [];
    foreach (\$rawItems as \$rawItem) {
        if (!is_string(\$rawItem) && !is_numeric(\$rawItem)) {
            continue;
        }

        \$normalized = trim((string) \$rawItem);
        if (\$normalized === '' || !ctype_digit(\$normalized) || (int) \$normalized <= 0) {
            continue;
        }

        \$items[\$normalized] = \$normalized;
    }

    return array_values(\$items);
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$legacyGroupPid = app_html_db_lang_res_group_edit_wrapper_param(\$legacyParams, 'PID');
\$projectPid = app_html_db_lang_res_group_edit_wrapper_param(\$legacyParams, 'ProjectPID');
\$targetPath = {$exportedTargetPath};
if (\$legacyGroupPid !== '' && ctype_digit(\$legacyGroupPid) && (int) \$legacyGroupPid > 0) {
    \$targetPath .= '?group_pid=' . rawurlencode(\$legacyGroupPid);
} elseif (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    \$targetPath .= '?intent=create';
}
\$expectedProjectPid = {$exportedLegacyProjectPid};

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
        app_html_db_lang_res_group_edit_wrapper_redirect_with_bridge_errors(
            {$exportedTargetPath},
            [
                'legacy ProjectPID が current project route と一致しません。current language resource groups page からやり直してください。',
            ],
        );
    }

    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$requestMethod !== 'POST') {
    app_html_db_lang_res_group_edit_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current language resource groups page からやり直してください。',
        ],
    );
}

\$update = app_html_db_lang_res_group_edit_wrapper_param(\$_POST, 'UPDATE');
\$delete = app_html_db_lang_res_group_edit_wrapper_param(\$_POST, 'DELETE');
\$bridgeErrors = [];
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$bridgeErrors[] = 'legacy ProjectPID が current project route と一致しません。';
}
if (\$update === '' && \$delete === '') {
    \$bridgeErrors[] = 'legacy group edit POST に UPDATE / DELETE が無いため、current route へ handoff できませんでした。';
}
if (\$update !== '' && \$legacyGroupPid !== '' && (!ctype_digit(\$legacyGroupPid) || (int) \$legacyGroupPid <= 0)) {
    \$bridgeErrors[] = '更新対象の legacy group pid が不正です。';
}
if (\$delete !== '' && (\$legacyGroupPid === '' || !ctype_digit(\$legacyGroupPid) || (int) \$legacyGroupPid <= 0)) {
    \$bridgeErrors[] = '削除対象の legacy group pid が不正です。';
}
if (\$bridgeErrors !== []) {
    app_html_db_lang_res_group_edit_wrapper_redirect_with_bridge_errors(\$targetPath, \$bridgeErrors);
}

\$bridgeErrors[] = 'LanguageResource group の current route は read-only inspector に切り替わりました。group 編集は current admin では扱わず、repo 配下の group.json を直接編集してください。';
app_html_db_lang_res_group_edit_wrapper_redirect_with_bridge_errors(\$targetPath, \$bridgeErrors);

PHP;
}

/**
 * @param array<string,string> $legacyLanguageResourcePidMap
 */
function app_project_output_html_module_generated_lang_res_move_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyLanguageResourcePidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_language_resource_list_path($projectKey),
        true,
    );
    $exportedPidMap = var_export($legacyLanguageResourcePidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res_move.php`.
// Legacy move preview and submit flow is translated into the current language-resource detail action.

function app_html_db_lang_res_move_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_lang_res_move_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_html_db_lang_res_move_wrapper_dynamic_pid_map(
    string \$appRoot,
    string \$projectKey,
    string \$legacyProjectPid
): array {
    if (
        \$appRoot === ''
        || !is_file(\$appRoot . '/bootstrap.php')
        || !is_file(\$appRoot . '/project_language_resource_catalog_loader.php')
    ) {
        return [];
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/project_language_resource_catalog_loader.php';

    \$app = app_bootstrap();
    \$catalogResult = app_fetch_project_language_resource_catalog(
        \$app,
        \$projectKey,
        (int) \$legacyProjectPid,
    );
    \$catalog = \$catalogResult['item'] ?? null;
    if (!\$catalogResult['ok'] || !is_array(\$catalog)) {
        return [];
    }

    \$pidMap = [];
    foreach (\$catalog['resources'] ?? [] as \$resource) {
        if (!is_array(\$resource)) {
            continue;
        }

        \$legacyResourcePid = (int) (\$resource['legacy_resource_pid'] ?? 0);
        \$resourceKey = trim((string) (\$resource['resource_key'] ?? ''));
        if (\$legacyResourcePid <= 0 || \$resourceKey === '') {
            continue;
        }

        \$pidMap[(string) \$legacyResourcePid] = \$resourceKey;
    }

    return \$pidMap;
}

function app_html_db_lang_res_move_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_lang_res_move_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header('Location: ' . app_html_db_lang_res_move_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors));
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$legacyLanguageResourcePidMap = {$exportedPidMap};
\$legacyResourcePid = app_html_db_lang_res_move_wrapper_param(\$legacyParams, 'PID');
\$projectPid = app_html_db_lang_res_move_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
\$appRoot = app_html_db_lang_res_move_wrapper_app_root();
\$dynamicPidMap = [];
if (\$legacyResourcePid !== '' && !array_key_exists(\$legacyResourcePid, \$legacyLanguageResourcePidMap)) {
    \$dynamicPidMap = app_html_db_lang_res_move_wrapper_dynamic_pid_map(
        \$appRoot,
        {$exportedNormalizedProjectKey},
        \$expectedProjectPid,
    );
}
\$resourceKey = '';
if (\$legacyResourcePid !== '') {
    if (array_key_exists(\$legacyResourcePid, \$legacyLanguageResourcePidMap)) {
        \$resourceKey = \$legacyLanguageResourcePidMap[\$legacyResourcePid];
    } elseif (array_key_exists(\$legacyResourcePid, \$dynamicPidMap)) {
        \$resourceKey = (string) (\$dynamicPidMap[\$legacyResourcePid] ?? '');
    }
}

\$targetPath = \$resourceKey === ''
    ? {$exportedListPath}
    : '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/language-resources/'
        . rawurlencode(\$resourceKey);

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
        app_html_db_lang_res_move_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy ProjectPID が current project route と一致しません。current language resource page からやり直してください。',
            ],
        );
    }

    if (\$legacyResourcePid !== '' && \$resourceKey === '') {
        app_html_db_lang_res_move_wrapper_redirect_with_bridge_errors(
            {$exportedListPath},
            [
                '指定された legacy resource pid は current detail route に解決できませんでした。',
            ],
        );
    }

    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$requestMethod !== 'POST') {
    app_html_db_lang_res_move_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current language resource page からやり直してください。',
        ],
    );
}

\$bridgeErrors = [];
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$bridgeErrors[] = 'legacy ProjectPID が current project route と一致しません。';
}
if (\$legacyResourcePid === '') {
    \$bridgeErrors[] = 'legacy PID が不足しています。';
} elseif (\$resourceKey === '') {
    \$bridgeErrors[] = '更新対象の legacy resource pid は current detail route に解決できませんでした。';
}

\$update = app_html_db_lang_res_move_wrapper_param(\$_POST, 'UPDATE');
if (\$update === '') {
    \$bridgeErrors[] = 'legacy move POST に UPDATE が無いため、current detail route へ handoff できませんでした。';
}

if (\$bridgeErrors !== []) {
    app_html_db_lang_res_move_wrapper_redirect_with_bridge_errors(\$targetPath, \$bridgeErrors);
}

\$bridgeErrors[] = 'LanguageResource move は current admin では扱いません。base group の変更は対象 resource.json を直接編集してください。';
app_html_db_lang_res_move_wrapper_redirect_with_bridge_errors(\$targetPath, \$bridgeErrors);

PHP;
}

/**
 * @param array<string,string> $legacyLanguageResourcePidMap
 */
function app_project_output_html_module_generated_lang_res_assign_additional_group_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
    array $legacyLanguageResourcePidMap,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_language_resource_list_path($projectKey),
        true,
    );
    $exportedPidMap = var_export($legacyLanguageResourcePidMap, true);
    $exportedNormalizedProjectKey = var_export(app_normalize_project_key($projectKey), true);

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res_assign_additional_group.php`.
// Legacy GET deep links now land on the current language-resource detail page.
// Legacy POST assignment updates are translated into the current detail action.

function app_html_db_lang_res_assign_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

/**
 * @return list<int>
 */
function app_html_db_lang_res_assign_wrapper_pid_list(\$rawValue): array
{
    if (is_array(\$rawValue)) {
        \$rawItems = \$rawValue;
    } elseif (is_string(\$rawValue) || is_numeric(\$rawValue)) {
        \$rawItems = [\$rawValue];
    } else {
        return [];
    }

    \$items = [];
    foreach (\$rawItems as \$rawItem) {
        if (!is_string(\$rawItem) && !is_numeric(\$rawItem)) {
            continue;
        }

        \$normalized = (int) trim((string) \$rawItem);
        if (\$normalized <= 0) {
            continue;
        }

        \$items[(string) \$normalized] = \$normalized;
    }

    return array_values(\$items);
}

function app_html_db_lang_res_assign_wrapper_app_root(): string
{
    \$configured = getenv('APP_APP_ROOT');
    if (is_string(\$configured) && trim(\$configured) !== '') {
        \$candidate = rtrim(\$configured, '/');
        if (is_file(\$candidate . '/bootstrap.php')) {
            return \$candidate;
        }
    }

    \$searchRoot = __DIR__;
    for (\$depth = 0; \$depth < 12; \$depth++) {
        foreach ([
            \$searchRoot . '/app/bootstrap.php' => \$searchRoot . '/app',
            \$searchRoot . '/mtool/app/bootstrap.php' => \$searchRoot . '/mtool/app',
            \$searchRoot . '/shared/bootstrap.php' => \$searchRoot . '/shared',
        ] as \$candidateBootstrap => \$candidateRoot) {
            if (is_file(\$candidateBootstrap)) {
                return \$candidateRoot;
            }
        }

        \$parent = dirname(\$searchRoot);
        if (\$parent === \$searchRoot) {
            break;
        }

        \$searchRoot = \$parent;
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_html_db_lang_res_assign_wrapper_dynamic_pid_map(
    string \$appRoot,
    string \$projectKey,
    string \$legacyProjectPid
): array {
    if (
        \$appRoot === ''
        || !is_file(\$appRoot . '/bootstrap.php')
        || !is_file(\$appRoot . '/project_language_resource_catalog_loader.php')
    ) {
        return [];
    }

    require_once \$appRoot . '/bootstrap.php';
    require_once \$appRoot . '/project_language_resource_catalog_loader.php';

    \$app = app_bootstrap();
    \$catalogResult = app_fetch_project_language_resource_catalog(
        \$app,
        \$projectKey,
        (int) \$legacyProjectPid,
    );
    \$catalog = \$catalogResult['item'] ?? null;
    if (!\$catalogResult['ok'] || !is_array(\$catalog)) {
        return [];
    }

    \$pidMap = [];
    foreach (\$catalog['resources'] ?? [] as \$resource) {
        if (!is_array(\$resource)) {
            continue;
        }

        \$legacyResourcePid = (int) (\$resource['legacy_resource_pid'] ?? 0);
        \$resourceKey = trim((string) (\$resource['resource_key'] ?? ''));
        if (\$legacyResourcePid <= 0 || \$resourceKey === '') {
            continue;
        }

        \$pidMap[(string) \$legacyResourcePid] = \$resourceKey;
    }

    return \$pidMap;
}

function app_html_db_lang_res_assign_wrapper_path_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): string {
    \$normalizedBridgeErrors = [];
    foreach (\$bridgeErrors as \$bridgeError) {
        if (!is_string(\$bridgeError) && !is_numeric(\$bridgeError)) {
            continue;
        }

        \$normalizedBridgeError = trim((string) \$bridgeError);
        if (\$normalizedBridgeError === '') {
            continue;
        }

        \$normalizedBridgeErrors[\$normalizedBridgeError] = \$normalizedBridgeError;
    }

    if (\$normalizedBridgeErrors === []) {
        return \$path;
    }

    \$pathParts = explode('?', \$path, 2);
    \$query = [];
    if (isset(\$pathParts[1]) && \$pathParts[1] !== '') {
        parse_str(\$pathParts[1], \$query);
        if (!is_array(\$query)) {
            \$query = [];
        }
    }

    \$query['bridge_errors'] = array_values(\$normalizedBridgeErrors);

    return \$pathParts[0] . '?' . http_build_query(\$query, '', '&', PHP_QUERY_RFC3986);
}

function app_html_db_lang_res_assign_wrapper_redirect_with_bridge_errors(
    string \$path,
    array \$bridgeErrors
): void {
    header('Cache-Control: no-store');
    header('Location: ' . app_html_db_lang_res_assign_wrapper_path_with_bridge_errors(\$path, \$bridgeErrors));
    exit;
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$legacyLanguageResourcePidMap = {$exportedPidMap};
\$baseGroupPid = app_html_db_lang_res_assign_wrapper_param(\$legacyParams, 'BaseLanguageResourceGroupPID');
\$listPath = {$exportedListPath};
if (\$baseGroupPid !== '' && ctype_digit(\$baseGroupPid) && (int) \$baseGroupPid > 0) {
    \$listPath .= '?group_pid=' . rawurlencode(\$baseGroupPid);
}

\$legacyResourcePid = app_html_db_lang_res_assign_wrapper_param(\$legacyParams, 'LanguageResourcePID');
\$projectPid = app_html_db_lang_res_assign_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};
\$appRoot = app_html_db_lang_res_assign_wrapper_app_root();
\$dynamicPidMap = [];
if (\$legacyResourcePid !== '' && !array_key_exists(\$legacyResourcePid, \$legacyLanguageResourcePidMap)) {
    \$dynamicPidMap = app_html_db_lang_res_assign_wrapper_dynamic_pid_map(
        \$appRoot,
        {$exportedNormalizedProjectKey},
        \$expectedProjectPid,
    );
}
\$resourceKey = '';
if (\$legacyResourcePid !== '') {
    if (array_key_exists(\$legacyResourcePid, \$legacyLanguageResourcePidMap)) {
        \$resourceKey = \$legacyLanguageResourcePidMap[\$legacyResourcePid];
    } elseif (array_key_exists(\$legacyResourcePid, \$dynamicPidMap)) {
        \$resourceKey = (string) (\$dynamicPidMap[\$legacyResourcePid] ?? '');
    }
}

\$targetPath = \$resourceKey === ''
    ? \$listPath
    : '/projects/' . rawurlencode({$exportedNormalizedProjectKey})
        . '/language-resources/'
        . rawurlencode(\$resourceKey);

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
        app_html_db_lang_res_assign_wrapper_redirect_with_bridge_errors(
            \$targetPath,
            [
                'legacy ProjectPID が current project route と一致しません。current language resource page からやり直してください。',
            ],
        );
    }

    if (\$legacyResourcePid !== '' && \$resourceKey === '') {
        app_html_db_lang_res_assign_wrapper_redirect_with_bridge_errors(
            \$listPath,
            [
                '指定された legacy resource pid は current detail route に解決できませんでした。',
            ],
        );
    }

    header('Cache-Control: no-store');
    header('Location: ' . \$targetPath);
    exit;
}

if (\$requestMethod !== 'POST') {
    app_html_db_lang_res_assign_wrapper_redirect_with_bridge_errors(
        \$targetPath,
        [
            '未対応の request method です。current language resource page からやり直してください。',
        ],
    );
}

\$bridgeErrors = [];
if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    \$bridgeErrors[] = 'legacy ProjectPID が current project route と一致しません。';
}
if (\$legacyResourcePid === '') {
    \$bridgeErrors[] = 'legacy LanguageResourcePID が不足しています。';
} elseif (\$resourceKey === '') {
    \$bridgeErrors[] = '更新対象の legacy resource pid は current detail route に解決できませんでした。';
}

\$update = app_html_db_lang_res_assign_wrapper_param(\$_POST, 'UPDATE');
if (\$update === '') {
    \$bridgeErrors[] = 'legacy additional-group assignment POST に UPDATE が無いため、current detail route へ handoff できませんでした。';
}

if (\$bridgeErrors !== []) {
    app_html_db_lang_res_assign_wrapper_redirect_with_bridge_errors(\$targetPath, \$bridgeErrors);
}

\$bridgeErrors[] = 'LanguageResource additional group 更新は current admin では扱いません。additional_group_keys は対象 resource.json を直接編集してください。';
app_html_db_lang_res_assign_wrapper_redirect_with_bridge_errors(\$targetPath, \$bridgeErrors);

PHP;
}

function app_project_output_html_module_generated_lang_res_auto_translate_ajax_wrapper_text(
    string $projectKey,
    int $legacyProjectPid,
): string {
    $legacyProjectPidText = $legacyProjectPid > 0 ? (string) $legacyProjectPid : '';
    $exportedLegacyProjectPid = var_export($legacyProjectPidText, true);
    $exportedListPath = var_export(
        app_project_output_html_module_default_language_resource_list_path($projectKey),
        true,
    );

    return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `lang_res_auto_translate_ajax.php`.
// Current admin does not expose LanguageResource auto-translate.
// Legacy AJAX callers receive a read-only bridge response that points them at the file workflow.

function app_html_db_lang_res_auto_translate_wrapper_param(array \$source, string \$name): string
{
    \$value = \$source[\$name] ?? null;
    if (is_array(\$value)) {
        return '';
    }

    if (is_string(\$value) || is_numeric(\$value)) {
        return trim((string) \$value);
    }

    return '';
}

function app_html_db_lang_res_auto_translate_wrapper_emit(array \$payload): void
{
    http_response_code(200);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode(\$payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

\$requestMethod = strtoupper((string) (\$_SERVER['REQUEST_METHOD'] ?? 'GET'));
\$legacyParams = \$requestMethod === 'POST' ? \$_POST : \$_GET;
\$projectPid = app_html_db_lang_res_auto_translate_wrapper_param(\$legacyParams, 'ProjectPID');
\$expectedProjectPid = {$exportedLegacyProjectPid};

if (\$requestMethod === 'GET' || \$requestMethod === 'HEAD') {
    header('Cache-Control: no-store');
    header('Location: ' . {$exportedListPath});
    exit;
}

if (\$requestMethod !== 'POST') {
    app_html_db_lang_res_auto_translate_wrapper_emit([
        'IsCompleted' => false,
        '_status' => 'NG',
        'Message' => 'POST のみ利用できます。',
        'Provider' => '',
        'ProviderCaption' => '',
    ]);
    return;
}

if (\$expectedProjectPid !== '' && \$projectPid !== '' && \$projectPid !== \$expectedProjectPid) {
    app_html_db_lang_res_auto_translate_wrapper_emit([
        'IsCompleted' => false,
        '_status' => 'NG',
        'Message' => 'legacy ProjectPID が current project route と一致しません。',
        'Provider' => '',
        'ProviderCaption' => '',
    ]);
    return;
}

app_html_db_lang_res_auto_translate_wrapper_emit([
    'IsCompleted' => false,
    '_status' => 'NG',
    'Message' => 'LanguageResource auto translate は current admin では扱いません。repo 配下の JSON file を直接編集し、必要な翻訳は AI workflow 側で行ってください。',
    'Provider' => '',
    'ProviderCaption' => '',
]);
return;

PHP;
}

function app_project_output_html_module_default_source_output_list_path(string $projectKey): string
{
    return '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/source-outputs';
}

function app_project_output_html_module_default_source_output_new_path(string $projectKey): string
{
    return app_project_output_html_module_default_source_output_list_path($projectKey) . '/new';
}

/**
 * @param array{
 *     project_key:string,
 *     legacy_project_pid:int,
 *     legacy_source_output_pid_map:array<string,string>,
 *     legacy_language_resource_pid_map:array<string,string>,
 *     legacy_language_resource_key_name_map:array<string,array{
 *         legacy_resource_pid:int,
 *         resource_key:string
 *     }>,
 *     legacy_custom_proxy_pid_map:array<string,string>,
 *     legacy_custom_proxy_step_pid_map:array<string,array{
 *         custom_proxy_key:string,
 *         step_id:string
 *     }>,
 *     custom_proxy_target_source_output_map:array<string,list<array{
 *         source_output_key:string,
 *         release_target_type:string
 *     }>>,
 *     legacy_compare_output_pid_map:array<string,string>,
 *     legacy_compare_output_additional_path_pid_map:array<string,array{
 *         compare_output_key:string,
 *         additional_path_key:string
 *     }>,
 *     legacy_table_pid_map:array<string,string>,
 *     legacy_data_class_pid_map:array<string,string>,
 *     legacy_html_pid_map:array<string,string>,
 *     legacy_db_access_pid_map:array<string,string>,
 *     legacy_db_access_function_pid_map:array<string,array{
 *         source_name:string,
 *         function_name:string
 *     }>
 * } $context
 */
function app_project_output_html_module_generated_entry_wrapper_text(string $relativePath, array $context): string
{
    return match ($relativePath) {
        'project_source_output.php' => app_project_output_html_module_generated_source_output_list_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'project_source_output_edit.php' => app_project_output_html_module_generated_source_output_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_source_output_pid_map'],
        ),
        'project_source_output_change_order.php' => app_project_output_html_module_generated_source_output_change_order_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_source_output_pid_map'],
        ),
        'dbtables.php' => app_project_output_html_module_generated_tables_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_table_pid_map'],
        ),
        'dbtables_import.php' => app_project_output_html_module_generated_tables_import_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'dbtables_import_for_each.php' => app_project_output_html_module_generated_tables_import_for_each_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'dbtable_columns.php' => app_project_output_html_module_generated_table_columns_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_table_pid_map'],
        ),
        'dbtable_edit.php' => app_project_output_html_module_generated_table_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_table_pid_map'],
        ),
        'dbtable_column_edit.php' => app_project_output_html_module_generated_table_column_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_table_pid_map'],
        ),
        'dataclasses.php' => app_project_output_html_module_generated_data_classes_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_data_class_pid_map'],
        ),
        'dataclasses_sync.php' => app_project_output_html_module_generated_data_classes_sync_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'dataclass_fields.php' => app_project_output_html_module_generated_data_class_fields_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_data_class_pid_map'],
        ),
        'dataclass_edit.php' => app_project_output_html_module_generated_data_class_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_data_class_pid_map'],
        ),
        'dataclass_field_edit.php' => app_project_output_html_module_generated_data_class_field_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_data_class_pid_map'],
        ),
        'htmls.php' => app_project_output_html_module_generated_html_list_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_html_pid_map'],
        ),
        'html_edit.php' => app_project_output_html_module_generated_html_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_html_pid_map'],
        ),
        'html_parameters.php' => app_project_output_html_module_generated_html_parameters_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_html_pid_map'],
        ),
        'html_parameter_edit.php' => app_project_output_html_module_generated_html_parameter_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_html_pid_map'],
        ),
        'da.php' => app_project_output_html_module_generated_db_access_list_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
        ),
        'da_edit.php' => app_project_output_html_module_generated_db_access_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
        ),
        'da_funcs.php' => app_project_output_html_module_generated_db_access_functions_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_funcs_change_order.php' => app_project_output_html_module_generated_db_access_functions_change_order_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
        ),
        'da_source.php' => app_project_output_html_module_generated_db_access_source_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
        ),
        'da_sync.php' => app_project_output_html_module_generated_db_access_sync_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'da_func_edit.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_edit.php',
            '',
            'Known legacy function edit flows are redirected to the current function detail route. Blank add flows remain on `_legacy/`.',
        ),
        'da_func_move.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_move.php',
            '/move',
            'Known legacy function move previews are redirected to the current move route.',
            true,
            false,
        ),
        'da_func_source.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_source.php',
            '/source',
            'Known legacy function source previews are redirected to the current source route.',
            true,
            true,
        ),
        'da_func_endpoint.php' => app_project_output_html_module_generated_db_access_function_endpoint_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_func_sort_order_edit.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_sort_order_edit.php',
            '',
            'Legacy sort-order editing is folded into the current function detail route.',
            true,
            false,
        ),
        'da_func_select_where.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_select_where.php',
            '/select-where',
            'Known legacy select-where previews are redirected to the current designer list route.',
            true,
            true,
        ),
        'da_func_select_where_input_aid.php' => app_project_output_html_module_generated_db_access_function_select_where_input_aid_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_func_select_where_change_order.php' => app_project_output_html_module_generated_db_access_function_change_order_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_select_where_change_order.php',
            '/select-where/change-order',
            true,
            true,
        ),
        'da_func_select_where_edit.php' => app_project_output_html_module_generated_db_access_function_select_where_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_func_select_target_fields.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_select_target_fields.php',
            '/select-target-fields',
            'Known legacy select-target-field previews are redirected to the current designer list route.',
            true,
            true,
        ),
        'da_func_select_target_field_edit.php' => app_project_output_html_module_generated_db_access_function_list_or_new_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_select_target_field_edit.php',
            'select-target-fields',
            'New-row flows are redirected to the current `/new` route. Existing legacy item PID deep links are redirected to the current designer list until canonical item mapping is added.',
            true,
        ),
        'da_func_select_having.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_select_having.php',
            '/select-having',
            'Known legacy select-having previews are redirected to the current designer list route.',
            true,
            true,
        ),
        'da_func_select_having_edit.php' => app_project_output_html_module_generated_db_access_function_list_or_new_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_select_having_edit.php',
            'select-having',
            'New-row flows are redirected to the current `/new` route. Existing legacy item PID deep links are redirected to the current designer list until canonical item mapping is added.',
            true,
        ),
        'da_func_update_delete_where.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_update_delete_where.php',
            '/update-delete-where',
            'Known legacy update/delete-where previews are redirected to the current designer list route.',
            true,
            true,
        ),
        'da_func_update_delete_where_input_aid.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_update_delete_where_input_aid.php',
            '/update-delete-where/input-aid',
            'Known legacy update/delete input-aid previews are redirected to the current input-aid route.',
            true,
            true,
        ),
        'da_func_update_delete_where_change_order.php' => app_project_output_html_module_generated_db_access_function_change_order_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_update_delete_where_change_order.php',
            '/update-delete-where/change-order',
            true,
            true,
        ),
        'da_func_update_delete_where_edit.php' => app_project_output_html_module_generated_db_access_function_update_delete_where_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_func_insert_target_fields.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_insert_target_fields.php',
            '/insert-target-fields',
            'Known legacy insert-target-field previews are redirected to the current designer list route.',
            true,
            true,
        ),
        'da_func_insert_target_field_edit.php' => app_project_output_html_module_generated_db_access_function_list_or_new_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_insert_target_field_edit.php',
            'insert-target-fields',
            'New-row flows are redirected to the current `/new` route. Existing legacy item PID deep links are redirected to the current designer list until canonical item mapping is added.',
            true,
        ),
        'da_func_update_target_fields.php' => app_project_output_html_module_generated_db_access_function_simple_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_update_target_fields.php',
            '/update-target-fields',
            'Known legacy update-target-field previews are redirected to the current designer list route.',
            true,
            true,
        ),
        'da_func_update_target_field_edit.php' => app_project_output_html_module_generated_db_access_function_list_or_new_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
            'da_func_update_target_field_edit.php',
            'update-target-fields',
            'New-row flows are redirected to the current `/new` route. Existing legacy item PID deep links are redirected to the current designer list until canonical item mapping is added.',
            true,
        ),
        'da_edit_proxy_single_target.php' => app_project_output_html_module_generated_single_proxy_list_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
        ),
        'da_funcs_edit_proxy_single_target.php' => app_project_output_html_module_generated_single_proxy_bulk_target_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_source_output_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_funcs_edit_proxy_single_setting.php' => app_project_output_html_module_generated_single_proxy_db_access_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            'da_funcs_edit_proxy_single_setting.php',
            'GET/HEAD preview requests are redirected to the current project-scoped single proxy route. Function-level auth editing now happens from current function detail pages.',
        ),
        'da_funcs_edit_proxy_single_setting_edit.php' => app_project_output_html_module_generated_single_proxy_function_detail_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_db_access_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_proxy_custom.php' => app_project_output_html_module_generated_custom_proxy_list_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'da_proxy_custom_edit.php' => app_project_output_html_module_generated_custom_proxy_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_custom_proxy_pid_map'],
            $context['legacy_source_output_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_proxy_custom_func.php' => app_project_output_html_module_generated_custom_proxy_functions_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_custom_proxy_pid_map'],
            'da_proxy_custom_func.php',
            'Known legacy custom-proxy function list previews are redirected to the current functions route.',
        ),
        'da_proxy_custom_func_change_order.php' => app_project_output_html_module_generated_custom_proxy_functions_change_order_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_custom_proxy_pid_map'],
            $context['legacy_custom_proxy_step_pid_map'],
        ),
        'da_proxy_custom_func_edit.php' => app_project_output_html_module_generated_custom_proxy_step_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_custom_proxy_pid_map'],
            $context['legacy_custom_proxy_step_pid_map'],
            $context['legacy_db_access_function_pid_map'],
        ),
        'da_proxy_custom_endpoint.php' => app_project_output_html_module_generated_custom_proxy_endpoint_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_custom_proxy_pid_map'],
            $context['custom_proxy_target_source_output_map'],
        ),
        'compare_output.php' => app_project_output_html_module_generated_compare_output_settings_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_compare_output_pid_map'],
            'compare_output.php',
            'Known legacy compare-output list deep links are redirected to the current settings page.',
        ),
        'compare_output_edit.php' => app_project_output_html_module_generated_compare_output_settings_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_compare_output_pid_map'],
            'compare_output_edit.php',
            'Add-flow links land on the current settings page, and existing legacy item deep links select the matching current compare-output row when possible.',
            true,
        ),
        'compare_output_additional_path.php' => app_project_output_html_module_generated_compare_output_additional_paths_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_compare_output_pid_map'],
        ),
        'compare_output_additional_path_edit.php' => app_project_output_html_module_generated_compare_output_additional_path_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_compare_output_pid_map'],
            $context['legacy_compare_output_additional_path_pid_map'],
        ),
        'build_project.php' => app_project_output_html_module_generated_build_run_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'build_project_for_each.php' => app_project_output_html_module_generated_build_run_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            true,
            'build_project_for_each.php',
        ),
        'build_project_ajax.php' => app_project_output_html_module_generated_build_run_ajax_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'build_project_ajax_check_if_completed.php' => app_project_output_html_module_generated_build_run_ajax_check_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'endpoint_test_json_ajax.php' => app_project_output_html_module_generated_endpoint_test_ajax_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'endpoint_common_include.php' => app_project_output_html_module_generated_endpoint_common_include_wrapper_text(
            $context['project_key'],
        ),
        'endpoint_lib_include.php' => app_project_output_html_module_generated_endpoint_lib_include_wrapper_text(
            $context['project_key'],
        ),
        'endpoint_test_json_client_include.php' => app_project_output_html_module_generated_endpoint_test_client_include_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'compare_output_do.php' => app_project_output_html_module_generated_compare_output_run_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'compare_output_do_ajax.php' => app_project_output_html_module_generated_compare_output_run_ajax_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'lang_res.php' => app_project_output_html_module_generated_lang_res_groups_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'lang_res_list.php' => app_project_output_html_module_generated_lang_res_list_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'lang_res_edit.php' => app_project_output_html_module_generated_lang_res_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_language_resource_pid_map'],
            $context['legacy_language_resource_key_name_map'],
        ),
        'lang_res_group_edit.php' => app_project_output_html_module_generated_lang_res_group_edit_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        'lang_res_move.php' => app_project_output_html_module_generated_lang_res_move_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_language_resource_pid_map'],
        ),
        'lang_res_assign_additional_group.php' => app_project_output_html_module_generated_lang_res_assign_additional_group_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
            $context['legacy_language_resource_pid_map'],
        ),
        'lang_res_auto_translate_ajax.php' => app_project_output_html_module_generated_lang_res_auto_translate_ajax_wrapper_text(
            $context['project_key'],
            $context['legacy_project_pid'],
        ),
        default => (function () use ($relativePath): string {
            $legacyRelativePath = '_legacy/' . ltrim(str_replace('\\', '/', $relativePath), '/');
            $exportedLegacyRelativePath = var_export($legacyRelativePath, true);

            return <<<PHP
<?php

declare(strict_types=1);

// Generated compatibility entry wrapper for `{$relativePath}`.
// The legacy implementation is preserved under `_legacy/` until the current route redirect is ready.

require_once __DIR__ . '/' . {$exportedLegacyRelativePath};

PHP;
        })(),
    };
}

/**
 * @param array{
 *     source_output_key:string
 * } $definition
 */
function app_project_output_apply_html_module_generated_entry_wrappers(
    array $app,
    string $runtimeSourceRoot,
    string $projectKey,
    array $definition,
): void {
    $sourceOutputKey = (string) ($definition['source_output_key'] ?? '');
    $context = app_project_output_html_module_generated_entry_wrapper_context($app, $projectKey);
    foreach (app_project_output_html_module_generated_entry_wrapper_targets($projectKey, $sourceOutputKey) as $relativePath) {
        $sourcePath = $runtimeSourceRoot . '/' . $relativePath;
        if (!is_file($sourcePath)) {
            throw new RuntimeException('html module entry wrapper 対象ファイルが見つかりません: ' . $relativePath);
        }

        $legacyPath = $runtimeSourceRoot . '/_legacy/' . $relativePath;
        app_project_output_ensure_directory(dirname($legacyPath));
        if (!copy($sourcePath, $legacyPath)) {
            throw new RuntimeException('html module legacy fallback の退避に失敗しました: ' . $relativePath);
        }

        app_project_output_write_text_file(
            $sourcePath,
            app_project_output_html_module_generated_entry_wrapper_text($relativePath, $context) . PHP_EOL,
        );
    }
}

/**
 * @param array{
 *     source_output_key:string,
 *     source_template_dir:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{
 *             relative_path:string,
 *             size:int
 *         }>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_html_module_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_html_module_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の html module artifact strategy です。',
        ];
    }

    $sourceRootResult = app_project_output_html_module_resolve_source_root(
        (string) ($definition['source_template_dir'] ?? ''),
    );
    if (!$sourceRootResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $sourceRootResult['error'],
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_html_module_default_runtime_source_relative_path(
            $projectKey,
            (string) ($definition['source_output_key'] ?? ''),
        );
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $scanResult = app_project_output_scan_tree($sourceRootResult['source_root']);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_copy_tree(
            $sourceRootResult['source_root'],
            $runtimeSourceRoot,
            $scanResult['files'],
        );
        app_project_output_apply_html_module_generated_entry_wrappers(
            $app,
            $runtimeSourceRoot,
            $projectKey,
            $definition,
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'html module source tree の staging に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $stagedScanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$stagedScanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $stagedScanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $stagedScanResult,
        'error' => '',
    ];
}
