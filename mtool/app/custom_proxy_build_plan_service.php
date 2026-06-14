<?php

declare(strict_types=1);

require_once __DIR__ . '/custom_proxy_repository.php';
require_once __DIR__ . '/custom_proxy_service.php';
require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/generated_catalog.php';

/**
 * @return array{
 *     resolved:bool,
 *     signature:string,
 *     line:int,
 *     resolution_error:string
 * }
 */
function app_custom_proxy_build_plan_resolve_step_reference(
    array $generatedCatalog,
    string $sourceName,
    string $functionName,
): array {
    $normalizedSourceName = trim($sourceName);
    $normalizedFunctionName = trim($functionName);

    if ($normalizedSourceName === '' || $normalizedFunctionName === '') {
        return [
            'resolved' => false,
            'signature' => '',
            'line' => 0,
            'resolution_error' => 'source/function が空です。',
        ];
    }

    $entity = app_generated_catalog_find_entity($generatedCatalog, $normalizedSourceName);
    if ($entity === null) {
        return [
            'resolved' => false,
            'signature' => '',
            'line' => 0,
            'resolution_error' => 'generated dbaccess entity が見つかりません。',
        ];
    }

    $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
    if ($dbaccessPath === '') {
        return [
            'resolved' => false,
            'signature' => '',
            'line' => 0,
            'resolution_error' => 'generated dbaccess file path がありません。',
        ];
    }

    $methodCatalog = app_generated_file_method_catalog($dbaccessPath);
    $method = app_generated_file_find_method($methodCatalog, $normalizedFunctionName);
    if ($method === null) {
        return [
            'resolved' => false,
            'signature' => '',
            'line' => 0,
            'resolution_error' => 'generated function が見つかりません。',
        ];
    }

    return [
        'resolved' => true,
        'signature' => (string) ($method['signature'] ?? ''),
        'line' => (int) ($method['line'] ?? 0),
        'resolution_error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     plan:array{
 *         project_key:string,
 *         source_output_key:string,
 *         custom_proxy_count:int,
 *         step_count:int,
 *         unresolved_step_count:int,
 *         generated_catalog_summary:array{
 *             total_entities:int,
 *             paired_count:int,
 *             data_only_count:int,
 *             dbaccess_only_count:int
 *         },
 *         items:list<array{
 *             custom_proxy_key:string,
 *             display_name:string,
 *             basename:string,
 *             name:string,
 *             in_transaction:bool,
 *             continue_even_if_failed_to_insert:bool,
 *             auth_policy:array{
 *                 raw_auth_type:string,
 *                 raw_auth_type_caption:string,
 *                 resolved_auth_type:string,
 *                 resolved_auth_type_caption:string,
 *                 strategy_caption:string,
 *                 summary:string,
 *                 is_valid:bool
 *             },
 *             target_source_output_keys:list<string>,
 *             step_count:int,
 *             unresolved_step_count:int,
 *             notes:string,
 *             updated_at:string,
 *             steps:list<array{
 *                 id:string,
 *                 step_order:string,
 *                 db_access_source_name:string,
 *                 db_access_function_name:string,
 *                 is_list:bool,
 *                 notes:string,
 *                 source_of_truth:string,
 *                 updated_at:string,
 *                 resolved:bool,
 *                 signature:string,
 *                 line:int,
 *                 resolution_error:string
 *             }>
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_custom_proxy_build_plan_for_source_output(
    array $app,
    string $projectKey,
    string $sourceOutputKey,
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);

    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'plan' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    if ($normalizedSourceOutputKey === '' || !app_source_output_key_is_valid($normalizedSourceOutputKey)) {
        return [
            'ok' => false,
            'plan' => null,
            'error' => 'source output key の形式が不正です。',
        ];
    }

    $catalogResult = app_fetch_project_custom_proxy_catalog($app, $normalizedProjectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'plan' => null,
            'error' => $catalogResult['error'],
        ];
    }

    $generatedCatalog = app_generated_entity_catalog($app);
    $items = [];
    $totalStepCount = 0;
    $totalUnresolvedStepCount = 0;

    foreach ($catalogResult['items'] as $customProxy) {
        $targetKeysResult = app_fetch_project_custom_proxy_target_keys(
            $app,
            $normalizedProjectKey,
            $customProxy['custom_proxy_key'],
        );
        if (!$targetKeysResult['ok']) {
            return [
                'ok' => false,
                'plan' => null,
                'error' => $targetKeysResult['error'],
            ];
        }

        $targetSourceOutputKeys = $targetKeysResult['items'];
        if (!in_array($normalizedSourceOutputKey, $targetSourceOutputKeys, true)) {
            continue;
        }

        $stepCatalogResult = app_fetch_project_custom_proxy_step_catalog(
            $app,
            $normalizedProjectKey,
            $customProxy['custom_proxy_key'],
        );
        if (!$stepCatalogResult['ok']) {
            return [
                'ok' => false,
                'plan' => null,
                'error' => $stepCatalogResult['error'],
            ];
        }

        $resolvedSteps = [];
        $unresolvedStepCount = 0;

        foreach ($stepCatalogResult['items'] as $step) {
            $resolution = app_custom_proxy_build_plan_resolve_step_reference(
                $generatedCatalog,
                $step['db_access_source_name'],
                $step['db_access_function_name'],
            );
            if (!$resolution['resolved']) {
                $unresolvedStepCount++;
                $totalUnresolvedStepCount++;
            }

            $resolvedSteps[] = [
                'id' => $step['id'],
                'step_order' => $step['step_order'],
                'db_access_source_name' => $step['db_access_source_name'],
                'db_access_function_name' => $step['db_access_function_name'],
                'is_list' => $step['is_list'] === '1',
                'notes' => $step['notes'],
                'source_of_truth' => $step['source_of_truth'],
                'updated_at' => $step['updated_at'],
                'resolved' => $resolution['resolved'],
                'signature' => $resolution['signature'],
                'line' => $resolution['line'],
                'resolution_error' => $resolution['resolution_error'],
            ];
        }

        $stepCount = count($resolvedSteps);
        $totalStepCount += $stepCount;
        $authPolicy = app_resolve_custom_proxy_auth_policy(
            $customProxy['auth_type'],
            $customProxy['single_get_function_name'],
        );

        $items[] = [
            'custom_proxy_key' => $customProxy['custom_proxy_key'],
            'display_name' => app_custom_proxy_display_name($customProxy['basename'], $customProxy['name']),
            'basename' => $customProxy['basename'],
            'name' => $customProxy['name'],
            'in_transaction' => $customProxy['in_transaction'] === '1',
            'continue_even_if_failed_to_insert' => $customProxy['continue_even_if_failed_to_insert'] === '1',
            'auth_policy' => $authPolicy,
            'target_source_output_keys' => $targetSourceOutputKeys,
            'step_count' => $stepCount,
            'unresolved_step_count' => $unresolvedStepCount,
            'notes' => $customProxy['notes'],
            'updated_at' => $customProxy['updated_at'],
            'steps' => $resolvedSteps,
        ];
    }

    return [
        'ok' => true,
        'plan' => [
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $normalizedSourceOutputKey,
            'custom_proxy_count' => count($items),
            'step_count' => $totalStepCount,
            'unresolved_step_count' => $totalUnresolvedStepCount,
            'generated_catalog_summary' => [
                'total_entities' => $generatedCatalog['total_entities'],
                'paired_count' => $generatedCatalog['paired_count'],
                'data_only_count' => $generatedCatalog['data_only_count'],
                'dbaccess_only_count' => $generatedCatalog['dbaccess_only_count'],
            ],
            'items' => $items,
        ],
        'error' => '',
    ];
}
