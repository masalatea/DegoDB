<?php

declare(strict_types=1);

require_once __DIR__ . '/db_access_endpoint_policy.php';
require_once __DIR__ . '/db_access_repository.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/project_db_access_bootstrap_service.php';
require_once __DIR__ . '/project_db_access_metadata_helper.php';

/**
 * @return array{
 *     resolved:bool,
 *     signature:string,
 *     line:int,
 *     resolution_error:string
 * }
 */
function app_single_proxy_build_plan_resolve_function_reference(
    array $app,
    string $projectKey,
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
        $canonicalFunctionResult = app_fetch_db_access_function_metadata(
            $app,
            $projectKey,
            $normalizedSourceName,
            $normalizedFunctionName,
        );
        if ($canonicalFunctionResult['ok'] && is_array($canonicalFunctionResult['item'] ?? null)) {
            $detectedSignature = trim((string) ($canonicalFunctionResult['item']['detected_signature'] ?? ''));
            if ($detectedSignature !== '') {
                return [
                    'resolved' => true,
                    'signature' => $detectedSignature,
                    'line' => (int) ($canonicalFunctionResult['item']['detected_line'] ?? 0),
                    'resolution_error' => '',
                ];
            }
        }

        $candidateEntityResult = app_project_db_access_bootstrap_candidate_entity(
            $app,
            $projectKey,
            $normalizedSourceName,
        );
        if ($candidateEntityResult['ok'] && is_array($candidateEntityResult['entity'] ?? null)) {
            foreach (($candidateEntityResult['entity']['method_catalog'] ?? []) as $method) {
                if (
                    is_array($method)
                    && strcasecmp((string) ($method['name'] ?? ''), $normalizedFunctionName) === 0
                ) {
                    return [
                        'resolved' => true,
                        'signature' => trim((string) ($method['signature'] ?? '')),
                        'line' => (int) ($method['line'] ?? 0),
                        'resolution_error' => '',
                    ];
                }
            }
        }

        return [
            'resolved' => false,
            'signature' => '',
            'line' => 0,
            'resolution_error' => 'generated / canonical dbaccess entity が見つかりません。',
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
 *         function_count:int,
 *         unresolved_function_count:int,
 *         unresolved_auth_count:int,
 *         generated_catalog_summary:array{
 *             total_entities:int,
 *             paired_count:int,
 *             data_only_count:int,
 *             dbaccess_only_count:int
 *         },
 *         items:list<array{
 *             source_name:string,
 *             function_name:string,
 *             display_name:string,
 *             endpoint_slug:string,
 *             function_list_order:string,
 *             action_type:string,
 *             auth_policy:array{
 *                 raw_auth_type:string,
 *                 raw_auth_type_caption:string,
 *                 resolved_auth_type:string,
 *                 resolved_auth_type_caption:string,
 *                 strategy_caption:string,
 *                 summary:string,
 *                 is_valid:bool
 *             },
 *             source_of_truth:string,
 *             function_updated_at:string,
 *             target_updated_at:string,
 *             resolved:bool,
 *             signature:string,
 *             line:int,
 *             resolution_error:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_single_proxy_build_plan_for_source_output(
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

    $targetCatalogResult = app_fetch_source_output_db_access_function_target_catalog(
        $app,
        $normalizedProjectKey,
        $normalizedSourceOutputKey,
    );
    if (!$targetCatalogResult['ok']) {
        return [
            'ok' => false,
            'plan' => null,
            'error' => $targetCatalogResult['error'],
        ];
    }

    $generatedCatalog = app_generated_entity_catalog($app);
    $items = [];
    $unresolvedFunctionCount = 0;
    $unresolvedAuthCount = 0;

    foreach ($targetCatalogResult['items'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $sourceName = trim((string) ($item['source_name'] ?? ''));
        $functionName = trim((string) ($item['function_name'] ?? ''));
        $resolution = app_single_proxy_build_plan_resolve_function_reference(
            $app,
            $normalizedProjectKey,
            $generatedCatalog,
            $sourceName,
            $functionName,
        );
        if (!$resolution['resolved']) {
            $unresolvedFunctionCount++;
        }

        $authPolicy = app_resolve_db_access_single_proxy_auth_policy(
            (string) ($item['single_proxy_auth_type'] ?? ''),
            (string) ($item['single_proxy_single_get_function_name'] ?? ''),
        );
        if (!$authPolicy['is_valid']) {
            $unresolvedAuthCount++;
        }

        $functionProfile = app_project_db_access_guess_function_profile($functionName);
        $actionType = strtoupper(trim((string) ($item['action_type'] ?? '')));
        if ($actionType === '') {
            $actionType = $functionProfile['legacy_action_type'];
        }

        $items[] = [
            'source_name' => $sourceName,
            'function_name' => $functionName,
            'display_name' => $sourceName !== '' ? ($sourceName . '.' . $functionName) : $functionName,
            'endpoint_slug' => $functionProfile['endpoint_slug'],
            'function_list_order' => (string) ($item['function_list_order'] ?? ''),
            'action_type' => $actionType,
            'auth_policy' => $authPolicy,
            'source_of_truth' => (string) ($item['source_of_truth'] ?? ''),
            'function_updated_at' => (string) ($item['function_updated_at'] ?? ''),
            'target_updated_at' => (string) ($item['target_updated_at'] ?? ''),
            'resolved' => $resolution['resolved'],
            'signature' => $resolution['signature'],
            'line' => $resolution['line'],
            'resolution_error' => $resolution['resolution_error'],
        ];
    }

    return [
        'ok' => true,
        'plan' => [
            'project_key' => $normalizedProjectKey,
            'source_output_key' => $normalizedSourceOutputKey,
            'function_count' => count($items),
            'unresolved_function_count' => $unresolvedFunctionCount,
            'unresolved_auth_count' => $unresolvedAuthCount,
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
