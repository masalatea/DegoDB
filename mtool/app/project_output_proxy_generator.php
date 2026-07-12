<?php

declare(strict_types=1);

require_once __DIR__ . '/custom_proxy_build_plan_service.php';
require_once __DIR__ . '/generated_name.php';
require_once __DIR__ . '/project_db_access_bootstrap_service.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/single_proxy_build_plan_service.php';

function app_project_output_proxy_strategy_is_supported(string $strategy): bool
{
    return in_array($strategy, [
        'single-proxy-server',
        'single-proxy-client',
        'custom-proxy-server',
        'custom-proxy-client',
    ], true);
}

function app_project_output_proxy_strategy_target_binding_scope(string $strategy): string
{
    return match ($strategy) {
        'single-proxy-server', 'single-proxy-client' => 'single-function-proxy',
        'custom-proxy-server', 'custom-proxy-client' => 'custom-proxy',
        default => '',
    };
}

function app_project_output_proxy_strategy_transport(string $strategy): string
{
    return match ($strategy) {
        'single-proxy-server', 'custom-proxy-server' => 'server',
        'single-proxy-client', 'custom-proxy-client' => 'client',
        default => '',
    };
}

function app_project_output_proxy_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_proxy_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

function app_project_output_bundle_root_depth_from_runtime_source_relative_path(
    string $runtimeSourceRelativePath,
): int {
    $normalized = app_runtime_storage_relative_path($runtimeSourceRelativePath);
    if ($normalized === '') {
        return 1;
    }

    return max(1, count(explode('/', $normalized)));
}

function app_project_output_prepare_proxy_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_proxy_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の proxy artifact strategy です。',
        ];
    }

    $bindingScope = app_project_output_proxy_strategy_target_binding_scope($strategy);
    $transport = app_project_output_proxy_strategy_transport($strategy);
    $contextResult = $bindingScope === 'single-function-proxy'
        ? app_project_output_single_proxy_build_context($app, $projectKey, $definition)
        : app_project_output_proxy_build_context($app, $projectKey, $definition);
    if (!$contextResult['ok'] || $contextResult['context'] === null) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $contextResult['error'],
        ];
    }

    $context = $contextResult['context'];
    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_proxy_default_runtime_source_relative_path(
            $projectKey,
            (string) $definition['source_output_key'],
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

    $context['runtime_source_relative_path'] = $runtimeSourceRelativePath;
    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    if ($bindingScope === 'single-function-proxy') {
        $emittedFileResult = $transport === 'server'
            ? app_project_output_single_proxy_build_server_emitted_files($context)
            : app_project_output_single_proxy_build_client_emitted_files($context);
    } else {
        $emittedFileResult = $transport === 'server'
            ? app_project_output_proxy_build_server_emitted_files($context)
            : app_project_output_proxy_build_client_emitted_files($context);
    }
    if (!$emittedFileResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $emittedFileResult['error'],
        ];
    }

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($emittedFileResult['files'] as $file) {
            app_project_output_write_text_file(
                $runtimeSourceRoot . '/' . $file['relative_path'],
                $file['contents'],
            );
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'proxy staging tree の作成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}

function app_project_output_proxy_build_context(array $app, string $projectKey, array $definition): array
{
    if (!app_source_output_supports_custom_proxy_targets($definition)) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'この source output は custom proxy target 用の binding scope ではありません。',
        ];
    }

    $sourceOutputKey = app_normalize_source_output_key((string) ($definition['source_output_key'] ?? ''));
    $planResult = app_custom_proxy_build_plan_for_source_output($app, $projectKey, $sourceOutputKey);
    if (!$planResult['ok'] || $planResult['plan'] === null) {
        return [
            'ok' => false,
            'context' => null,
            'error' => $planResult['error'] !== ''
                ? $planResult['error']
                : 'custom proxy build plan を取得できませんでした。',
        ];
    }

    $plan = $planResult['plan'];
    if ($plan['items'] === []) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'この source output を target にする custom proxy がありません。',
        ];
    }

    if ($plan['unresolved_step_count'] > 0) {
        return [
            'ok' => false,
            'context' => null,
            'error' => '未解決の custom proxy step があるため artifact を生成できません。'
                . ' unresolved=' . $plan['unresolved_step_count'],
        ];
    }

    $sourceEntities = [];
    foreach (app_project_output_proxy_unique_source_names($plan['items']) as $sourceName) {
        $entityResult = app_project_output_proxy_load_source_entity($app, $sourceName, $projectKey);
        if (!$entityResult['ok'] || $entityResult['entity'] === null) {
            return [
                'ok' => false,
                'context' => null,
                'error' => $entityResult['error'],
            ];
        }

        $sourceEntities[$sourceName] = $entityResult['entity'];
    }

    $commonBasename = app_project_output_proxy_common_basename($plan['items']);
    $clientPrefix = app_project_output_proxy_client_prefix($commonBasename);
    $clientNamespace = app_project_output_proxy_client_namespace($sourceOutputKey, $commonBasename);
    $proxyItems = [];

    foreach ($plan['items'] as $item) {
        if (!$item['auth_policy']['is_valid']) {
            return [
                'ok' => false,
                'context' => null,
                'error' => 'auth policy が未解決の custom proxy があるため artifact を生成できません: '
                    . $item['custom_proxy_key'],
            ];
        }

        $proxyItems[] = app_project_output_proxy_enrich_item(
            $item,
            $sourceEntities,
            $clientPrefix,
        );
    }

    return [
        'ok' => true,
        'context' => [
            'project_key' => app_normalize_project_key($projectKey),
            'source_output_key' => $sourceOutputKey,
            'definition' => $definition,
            'plan' => $plan,
            'source_entities' => $sourceEntities,
            'common_basename' => $commonBasename,
            'client_prefix' => $clientPrefix,
            'client_namespace' => $clientNamespace,
            'proxy_items' => $proxyItems,
        ],
        'error' => '',
    ];
}

function app_project_output_single_proxy_build_context(array $app, string $projectKey, array $definition): array
{
    if (!app_source_output_supports_single_function_proxy_targets($definition)) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'この source output は single-function proxy target 用の binding scope ではありません。',
        ];
    }

    $sourceOutputKey = app_normalize_source_output_key((string) ($definition['source_output_key'] ?? ''));
    $planResult = app_single_proxy_build_plan_for_source_output($app, $projectKey, $sourceOutputKey);
    if (!$planResult['ok'] || $planResult['plan'] === null) {
        return [
            'ok' => false,
            'context' => null,
            'error' => $planResult['error'] !== ''
                ? $planResult['error']
                : 'single-function proxy build plan を取得できませんでした。',
        ];
    }

    $plan = $planResult['plan'];
    if ($plan['items'] === []) {
        return [
            'ok' => false,
            'context' => null,
            'error' => 'この source output を target にする single-function proxy がありません。',
        ];
    }

    if ($plan['unresolved_function_count'] > 0) {
        return [
            'ok' => false,
            'context' => null,
            'error' => '未解決の single-function proxy function があるため artifact を生成できません。'
                . ' unresolved=' . $plan['unresolved_function_count'],
        ];
    }

    if ($plan['unresolved_auth_count'] > 0) {
        return [
            'ok' => false,
            'context' => null,
            'error' => '未解決の single-function proxy auth policy があるため artifact を生成できません。'
                . ' unresolved=' . $plan['unresolved_auth_count'],
        ];
    }

    $sourceEntities = [];
    foreach (app_project_output_single_proxy_unique_source_names($plan['items']) as $sourceName) {
        $entityResult = app_project_output_proxy_load_source_entity($app, $sourceName, $projectKey);
        if (!$entityResult['ok'] || $entityResult['entity'] === null) {
            return [
                'ok' => false,
                'context' => null,
                'error' => $entityResult['error'],
            ];
        }

        $sourceEntities[$sourceName] = $entityResult['entity'];
    }

    $clientPrefix = app_project_output_single_proxy_client_prefix($sourceOutputKey);
    $clientNamespace = app_project_output_single_proxy_client_namespace($sourceOutputKey);
    $proxyItems = [];

    foreach ($plan['items'] as $item) {
        $sourceName = (string) ($item['source_name'] ?? '');
        if ($sourceName === '' || !isset($sourceEntities[$sourceName])) {
            return [
                'ok' => false,
                'context' => null,
                'error' => 'single-function proxy source entity が見つかりません: ' . $sourceName,
            ];
        }

        $proxyItems[] = app_project_output_single_proxy_enrich_item(
            $item,
            $sourceEntities[$sourceName],
        );
    }

    return [
        'ok' => true,
        'context' => [
            'project_key' => app_normalize_project_key($projectKey),
            'source_output_key' => $sourceOutputKey,
            'definition' => $definition,
            'plan' => $plan,
            'source_entities' => $sourceEntities,
            'client_prefix' => $clientPrefix,
            'client_namespace' => $clientNamespace,
            'proxy_items' => $proxyItems,
        ],
        'error' => '',
    ];
}

function app_project_output_proxy_unique_source_names(array $planItems): array
{
    $values = [];
    foreach ($planItems as $item) {
        foreach ($item['steps'] as $step) {
            $sourceName = trim((string) ($step['db_access_source_name'] ?? ''));
            if ($sourceName !== '') {
                $values[$sourceName] = $sourceName;
            }
        }
    }

    return array_values($values);
}

function app_project_output_single_proxy_unique_source_names(array $planItems): array
{
    $values = [];
    foreach ($planItems as $item) {
        $sourceName = trim((string) ($item['source_name'] ?? ''));
        if ($sourceName !== '') {
            $values[$sourceName] = $sourceName;
        }
    }

    return array_values($values);
}

function app_project_output_proxy_load_source_entity(array $app, string $sourceName, string $projectKey = ''): array
{
    $generatedCatalog = app_generated_entity_catalog($app);
    $entity = app_generated_catalog_find_entity($generatedCatalog, $sourceName);
    if ($entity === null && app_generated_name_policy_uses_physical_logical_names()) {
        $outputSourceName = app_generated_name_map_for_physical_name($sourceName, 'class')['generated_name'];
        if ($outputSourceName !== $sourceName) {
            $entity = app_generated_catalog_find_entity($generatedCatalog, $outputSourceName);
        }
    }
    if ($entity !== null) {
        $dataPath = (string) ($entity['data_path'] ?? '');
        $dbaccessPath = (string) ($entity['dbaccess_path'] ?? '');
        if ($dataPath !== '' && is_file($dataPath) && $dbaccessPath !== '' && is_file($dbaccessPath)) {
            $dataClasses = app_generated_file_class_names($dataPath);
            $dbaccessClasses = app_generated_file_class_names($dbaccessPath);
            $dataProperties = app_generated_file_property_names($dataPath);

            return [
                'ok' => true,
                'entity' => [
                    'source_name' => $sourceName,
                    'data_path' => $dataPath,
                    'dbaccess_path' => $dbaccessPath,
                    'data_file' => basename($dataPath),
                    'dbaccess_file' => basename($dbaccessPath),
                    'data_class' => $dataClasses[0] ?? ($sourceName . 'Data'),
                    'data_list_class' => ($dataClasses[0] ?? ($sourceName . 'Data')) . 'List',
                    'dbaccess_class' => $dbaccessClasses[0] ?? ($sourceName . 'DBAccess'),
                    'data_properties' => $dataProperties,
                ],
                'error' => '',
            ];
        }
    }

    if ($projectKey === '') {
        return [
            'ok' => false,
            'entity' => null,
            'error' => 'generated source entity が見つからず、canonical fallback に必要な project key もありません: ' . $sourceName,
        ];
    }

    $fallbackEntityResult = app_project_db_access_bootstrap_materialize_runtime_entity(
        $app,
        $projectKey,
        $sourceName,
    );
    if (!$fallbackEntityResult['ok'] || !is_array($fallbackEntityResult['entity'] ?? null)) {
        return [
            'ok' => false,
            'entity' => null,
            'error' => $fallbackEntityResult['error'] !== ''
                ? $fallbackEntityResult['error']
                : ('generated / canonical source entity が見つかりません: ' . $sourceName),
        ];
    }

    return [
        'ok' => true,
        'entity' => $fallbackEntityResult['entity'],
        'error' => '',
    ];
}

function app_project_output_proxy_common_basename(array $planItems): string
{
    $values = [];
    foreach ($planItems as $item) {
        $basename = trim((string) ($item['basename'] ?? ''));
        if ($basename !== '') {
            $values[$basename] = $basename;
        }
    }

    if (count($values) === 1) {
        return array_values($values)[0];
    }

    return 'CustomProxy';
}

function app_project_output_proxy_client_prefix(string $commonBasename): string
{
    $basename = trim($commonBasename);
    if ($basename === '') {
        return 'CustomProxy';
    }

    return $basename . 'Custom';
}

function app_project_output_proxy_client_namespace(string $sourceOutputKey, string $commonBasename): string
{
    $baseKey = preg_replace('/-PROXY-(SERVER|CLIENT)$/i', '', $sourceOutputKey);
    if (!is_string($baseKey)) {
        $baseKey = $sourceOutputKey;
    }
    $baseKey = trim($baseKey, '-');

    if ($commonBasename !== '' && str_starts_with(strtoupper($baseKey), strtoupper($commonBasename))) {
        $suffix = substr($baseKey, strlen($commonBasename));
        if (is_string($suffix)) {
            $candidate = $commonBasename . app_project_output_proxy_pascalize_key($suffix);
            if ($candidate !== '') {
                return $candidate;
            }
        }
    }

    $fallback = app_project_output_proxy_pascalize_key($baseKey);
    return $fallback !== '' ? $fallback : 'GeneratedProxyClient';
}

function app_project_output_single_proxy_client_prefix(string $sourceOutputKey): string
{
    $baseKey = preg_replace('/-PROXY-(SERVER|CLIENT)$/i', '', $sourceOutputKey);
    if (!is_string($baseKey)) {
        $baseKey = $sourceOutputKey;
    }

    $prefix = app_project_output_proxy_pascalize_key(trim($baseKey, '-'));
    if ($prefix === '') {
        return 'Single';
    }

    if (str_ends_with($prefix, 'Single')) {
        return $prefix;
    }

    return $prefix . 'Single';
}

function app_project_output_single_proxy_client_namespace(string $sourceOutputKey): string
{
    $prefix = app_project_output_single_proxy_client_prefix($sourceOutputKey);
    return $prefix !== '' ? $prefix : 'GeneratedSingleProxyClient';
}

function app_project_output_proxy_pascalize_key(string $value): string
{
    $parts = preg_split('/[^A-Za-z0-9]+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($parts)) {
        return '';
    }

    $normalized = '';
    foreach ($parts as $part) {
        if (!is_string($part)) {
            continue;
        }
        $normalized .= app_project_output_proxy_pascalize_segment($part);
    }

    return $normalized;
}

function app_project_output_proxy_pascalize_segment(string $value): string
{
    $normalized = preg_replace('/[^A-Za-z0-9]+/', '', trim($value));
    if (!is_string($normalized) || $normalized === '') {
        return '';
    }

    if (strtoupper($normalized) === $normalized) {
        if (strlen($normalized) <= 2) {
            return $normalized;
        }

        return strtoupper(substr($normalized, 0, 1)) . strtolower(substr($normalized, 1));
    }

    return strtoupper(substr($normalized, 0, 1)) . substr($normalized, 1);
}

function app_project_output_single_proxy_action_type(string $actionType, string $functionName): string
{
    $normalized = strtoupper(trim($actionType));
    if ($normalized === 'SELECTSINGLE') {
        return 'select-single';
    }
    if ($normalized === 'SELECTLIST') {
        return 'select-list';
    }
    if ($normalized === 'INSERT') {
        return 'insert';
    }
    if ($normalized === 'UPDATE') {
        return 'update';
    }
    if ($normalized === 'DELETE') {
        return 'delete';
    }

    return app_project_output_proxy_step_action($functionName);
}

function app_project_output_single_proxy_output_source_name(array $item): string
{
    $sourceName = trim((string) ($item['source_name'] ?? ''));
    if (!app_generated_name_policy_uses_physical_logical_names()) {
        return $sourceName;
    }

    $physicalName = trim((string) ($item['physical_name'] ?? $sourceName));
    return app_generated_name_map_for_physical_name($physicalName, 'class')['generated_name'];
}

function app_project_output_single_proxy_output_function_name(array $item): string
{
    $functionName = trim((string) ($item['function_name'] ?? ''));
    if ($functionName === '' || !app_generated_name_policy_uses_physical_logical_names()) {
        return $functionName;
    }

    $sourceName = trim((string) ($item['source_name'] ?? ''));
    $physicalName = trim((string) ($item['physical_name'] ?? $sourceName));
    $generatedSourceName = app_generated_name_map_for_physical_name($physicalName, 'class')['generated_name'];
    foreach (array_unique([$sourceName, $physicalName]) as $candidate) {
        if ($candidate !== '' && str_contains($functionName, $candidate)) {
            return str_replace($candidate, $generatedSourceName, $functionName);
        }
    }

    return $functionName;
}

function app_project_output_single_proxy_output_endpoint_filename(array $item): string
{
    return 'proxyserver-'
        . app_project_output_single_proxy_output_source_name($item)
        . '-'
        . app_project_output_single_proxy_output_function_name($item)
        . '.php';
}

function app_project_output_single_proxy_enrich_item(array $item, array $entity): array
{
    $outputSourceName = app_project_output_single_proxy_output_source_name($item);
    $outputFunctionName = app_project_output_single_proxy_output_function_name($item);
    $itemStem = preg_replace(
        '/[^A-Za-z0-9_]+/',
        '',
        $outputSourceName . $outputFunctionName,
    );
    if (!is_string($itemStem) || $itemStem === '') {
        $itemStem = 'GeneratedSingleProxy';
    }

    $parameterNames = app_project_output_proxy_parse_method_parameters((string) ($item['signature'] ?? ''));
    $action = app_project_output_single_proxy_action_type(
        (string) ($item['action_type'] ?? ''),
        (string) ($item['function_name'] ?? ''),
    );
    $inputKind = count($parameterNames) === 1 && str_ends_with($parameterNames[0], 'Obj')
        ? 'object'
        : 'scalar';
    $parameterSchemas = app_project_output_proxy_parameter_schemas_from_single_proxy_item(
        $parameterNames,
        $item,
    );

    $responseKey = '';
    $responseMode = 'none';
    $responsePropertyType = '';
    if ($action === 'insert') {
        $responseKey = 'InsertID';
        $responseMode = 'insert-id-single';
        $responsePropertyType = 'long?';
    } elseif ($action === 'select-list') {
        $responseKey = 'Result';
        $responseMode = 'direct-result';
        $responsePropertyType = $entity['data_list_class'];
    } elseif ($action === 'select-single') {
        $responseKey = 'Result';
        $responseMode = 'direct-result';
        $responsePropertyType = $entity['data_class'];
    }

    return [
        'source_name' => (string) ($item['source_name'] ?? ''),
        'function_name' => (string) ($item['function_name'] ?? ''),
        'display_name' => (string) ($item['display_name'] ?? ''),
        'auth_policy' => $item['auth_policy'],
        'in_transaction' => false,
        'continue_even_if_failed_to_insert' => false,
        'endpoint_filename' => app_project_output_single_proxy_output_endpoint_filename($item),
        'handler_class' => $itemStem . 'ProxyHandler',
        'base_handler_class' => $itemStem . 'ProxyHandlerBase',
        'request_class' => $itemStem . 'RequestParams',
        'result_class' => $itemStem . 'ProxyResult',
        'client_method' => $itemStem . 'Async',
        'response_property_type' => $responsePropertyType,
        'steps' => [
            [
                'step_no' => 1,
                'step_order' => (string) ($item['function_list_order'] ?? '1'),
                'request_key' => '',
                'source_name' => (string) ($item['source_name'] ?? ''),
                'function_name' => (string) ($item['function_name'] ?? ''),
                'signature' => (string) ($item['signature'] ?? ''),
                'line' => (int) ($item['line'] ?? 0),
                'is_list' => false,
                'action' => $action,
                'parameter_names' => $parameterNames,
                'parameter_schemas' => $parameterSchemas,
                'input_kind' => $inputKind,
                'object_param_name' => $inputKind === 'object' ? ($parameterNames[0] ?? '') : '',
                'object_class' => $inputKind === 'object' ? $entity['data_class'] : '',
                'data_class' => $entity['data_class'],
                'data_list_class' => $entity['data_list_class'],
                'dbaccess_class' => $entity['dbaccess_class'],
                'request_class' => $itemStem . 'RequestParams',
                'result_class' => '',
                'result_data_type' => '',
                'response_key' => $responseKey,
                'response_mode' => $responseMode,
                'overall_request_property_type' => $itemStem . 'RequestParams',
                'overall_result_property_type' => $responsePropertyType,
            ],
        ],
    ];
}

/**
 * @param list<string> $parameterNames
 * @return array<string,array<string,string>>
 */
function app_project_output_proxy_parameter_schemas_from_single_proxy_item(array $parameterNames, array $item): array
{
    $schemas = [];
    $sourceName = trim((string) ($item['source_name'] ?? ''));
    $selectWheres = array_values(array_filter(
        is_array($item['select_wheres'] ?? null) ? $item['select_wheres'] : [],
        static fn (mixed $row): bool => is_array($row),
    ));
    $whereByParameterName = [];
    foreach ($selectWheres as $where) {
        $columnName = trim((string) ($where['target_table_column_name'] ?? ''));
        if ($sourceName === '' || $columnName === '') {
            continue;
        }

        $sourceCandidates = [$sourceName];
        $sourceNameMap = app_generated_name_map_for_physical_name($sourceName, 'class');
        $sourceCandidates[] = $sourceNameMap['generated_name'];

        $columnCandidates = [$columnName];
        $columnNameMap = app_generated_name_map_for_physical_name($columnName, 'class');
        $columnCandidates[] = $columnNameMap['generated_name'];

        foreach (array_unique($sourceCandidates) as $sourceCandidate) {
            foreach (array_unique($columnCandidates) as $columnCandidate) {
                $whereByParameterName['param_' . $sourceCandidate . '_' . $columnCandidate . '_where'] = $where;
            }
        }
    }

    foreach ($parameterNames as $index => $parameterName) {
        $normalizedParameterName = trim($parameterName);
        if ($normalizedParameterName === '') {
            continue;
        }

        $metadata = [
            'parameter_name' => $normalizedParameterName,
            'datatype' => '',
            'source' => 'name-inference',
            'target_table_name' => '',
            'target_table_column_name' => '',
        ];

        if ($normalizedParameterName === 'limit') {
            $metadata['datatype'] = 'int';
            $metadata['source'] = trim((string) ($item['limit_parameter_type'] ?? '')) !== ''
                ? 'limit-parameter'
                : 'limit-name';
            $schemas[$normalizedParameterName] = $metadata;
            continue;
        }

        $where = $whereByParameterName[$normalizedParameterName] ?? ($selectWheres[$index] ?? null);
        if (is_array($where)) {
            $parameterDataType = trim((string) ($where['parameter_data_type'] ?? ''));
            $targetColumnName = trim((string) ($where['target_table_column_name'] ?? ''));
            $metadata['datatype'] = $parameterDataType;
            $metadata['source'] = $parameterDataType !== ''
                ? 'select-where-parameter-data-type'
                : 'select-where-column-name';
            $metadata['target_table_name'] = trim((string) ($where['target_table_name'] ?? ''));
            $metadata['target_table_column_name'] = $targetColumnName;
        }

        $schemas[$normalizedParameterName] = $metadata;
    }

    return $schemas;
}

function app_project_output_proxy_enrich_item(array $item, array $sourceEntities, string $clientPrefix): array
{
    $handlerStem = preg_replace('/[^A-Za-z0-9_]+/', '', $item['basename'] . $item['name']);
    if (!is_string($handlerStem) || $handlerStem === '') {
        $handlerStem = 'GeneratedProxy';
    }

    $steps = [];
    $stepNo = 0;
    foreach ($item['steps'] as $step) {
        $stepNo++;
        $sourceName = (string) $step['db_access_source_name'];
        $steps[] = app_project_output_proxy_enrich_step(
            $step,
            $sourceEntities[$sourceName],
            $clientPrefix,
            $stepNo,
        );
    }

    return [
        'custom_proxy_key' => $item['custom_proxy_key'],
        'display_name' => $item['display_name'],
        'basename' => $item['basename'],
        'name' => $item['name'],
        'in_transaction' => $item['in_transaction'],
        'continue_even_if_failed_to_insert' => $item['continue_even_if_failed_to_insert'],
        'auth_policy' => $item['auth_policy'],
        'endpoint_filename' => 'proxyserver-' . $item['basename'] . '-' . $item['name'] . '.php',
        'handler_class' => $handlerStem . 'ProxyHandler',
        'base_handler_class' => $handlerStem . 'ProxyHandlerBase',
        'overall_request_class' => $clientPrefix . 'Proxy' . $item['name'] . 'RequestParams',
        'overall_result_class' => $clientPrefix . 'Proxy' . $item['name'] . 'ProxyResult',
        'steps' => $steps,
    ];
}

function app_project_output_proxy_enrich_step(array $step, array $entity, string $clientPrefix, int $stepNo): array
{
    $parameterNames = app_project_output_proxy_parse_method_parameters((string) ($step['signature'] ?? ''));
    $action = app_project_output_proxy_step_action((string) ($step['db_access_function_name'] ?? ''));
    $inputKind = count($parameterNames) === 1 && str_ends_with($parameterNames[0], 'Obj')
        ? 'object'
        : 'scalar';
    $stepClassStem = $clientPrefix
        . 'Step'
        . $stepNo
        . $step['db_access_source_name']
        . 'Proxy'
        . $step['db_access_function_name'];

    $responseKey = '';
    $responseMode = 'none';
    $overallResultPropertyType = '';
    $resultClass = '';
    $resultDataType = '';

    if ($action === 'insert') {
        $responseKey = 'insert_id' . $stepNo;
        $responseMode = $step['is_list'] ? 'insert-id-list' : 'insert-id-single';
        $overallResultPropertyType = $step['is_list'] ? 'List<long>' : 'long?';
    } elseif ($action === 'select-list' || $action === 'select-single') {
        $responseKey = 'Result' . $stepNo;
        $responseMode = $step['is_list'] ? 'step-result-list' : 'step-result-single';
        $resultClass = $stepClassStem . 'ResultParams';
        $resultDataType = $action === 'select-list'
            ? $entity['data_list_class']
            : $entity['data_class'];
        $overallResultPropertyType = $step['is_list']
            ? 'List<' . $resultClass . '>'
            : $resultClass;
    }

    return [
        'step_no' => $stepNo,
        'step_order' => $step['step_order'],
        'request_key' => 'step' . $stepNo,
        'source_name' => $step['db_access_source_name'],
        'function_name' => $step['db_access_function_name'],
        'signature' => $step['signature'],
        'line' => $step['line'],
        'is_list' => $step['is_list'],
        'action' => $action,
        'parameter_names' => $parameterNames,
        'input_kind' => $inputKind,
        'object_param_name' => $inputKind === 'object' ? ($parameterNames[0] ?? '') : '',
        'object_class' => $inputKind === 'object' ? $entity['data_class'] : '',
        'data_class' => $entity['data_class'],
        'data_list_class' => $entity['data_list_class'],
        'dbaccess_class' => $entity['dbaccess_class'],
        'request_class' => $stepClassStem . 'RequestParams',
        'result_class' => $resultClass,
        'result_data_type' => $resultDataType,
        'response_key' => $responseKey,
        'response_mode' => $responseMode,
        'overall_request_property_type' => $step['is_list']
            ? 'List<' . $stepClassStem . 'RequestParams>'
            : ($stepClassStem . 'RequestParams'),
        'overall_result_property_type' => $overallResultPropertyType,
    ];
}

function app_project_output_proxy_parse_method_parameters(string $signature): array
{
    if (!preg_match('/\((.*)\)/', $signature, $matches)) {
        return [];
    }

    $inner = trim((string) ($matches[1] ?? ''));
    if ($inner === '') {
        return [];
    }

    $parts = explode(',', $inner);
    $parameters = [];

    foreach ($parts as $part) {
        $candidate = trim($part);
        if ($candidate === '') {
            continue;
        }

        $candidate = preg_replace('/=.*/', '', $candidate);
        if (!is_string($candidate)) {
            continue;
        }

        if (preg_match('/\$([A-Za-z0-9_]+)/', $candidate, $parameterMatches)) {
            $parameters[] = trim((string) $parameterMatches[1]);
        }
    }

    return $parameters;
}

function app_project_output_proxy_step_action(string $functionName): string
{
    if (str_starts_with($functionName, 'Insert')) {
        return 'insert';
    }

    if (str_starts_with($functionName, 'Update')) {
        return 'update';
    }

    if (str_starts_with($functionName, 'Delete')) {
        return 'delete';
    }

    if (str_starts_with($functionName, 'Get') && str_ends_with($functionName, 'List')) {
        return 'select-list';
    }

    if (str_starts_with($functionName, 'Get')) {
        return 'select-single';
    }

    return 'unknown';
}

/**
 * @param array{
 *     source_entities:list<array{
 *         data_path:string,
 *         dbaccess_path:string
 *     }>
 * } $context
 * @return array{
 *     ok:bool,
 *     files:array<string,string>,
 *     error:string
 * }
 */
function app_project_output_proxy_runtime_bundle_files(array $context): array
{
    $runtimeRoots = [];

    foreach ($context['source_entities'] as $entity) {
        foreach (['data_path', 'dbaccess_path'] as $field) {
            $path = trim((string) ($entity[$field] ?? ''));
            if ($path === '' || !is_file($path)) {
                return [
                    'ok' => false,
                    'files' => [],
                    'error' => 'generated runtime file が不足しています: ' . $field,
                ];
            }

            $runtimeRoots[] = str_replace('\\', '/', dirname($path));
        }
    }

    $runtimeRoots = array_values(array_unique(array_filter($runtimeRoots, static fn (string $value): bool => $value !== '')));
    if ($runtimeRoots === []) {
        return [
            'ok' => true,
            'files' => [],
            'error' => '',
        ];
    }

    $files = [];

    foreach ($runtimeRoots as $runtimeRoot) {
        if (!is_dir($runtimeRoot)) {
            return [
                'ok' => false,
                'files' => [],
                'error' => 'proxy runtime bundle root が見つかりません。',
            ];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($runtimeRoot, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
                continue;
            }

            $absolutePath = str_replace('\\', '/', $fileInfo->getPathname());
            if (!str_starts_with($absolutePath, $runtimeRoot . '/')) {
                continue;
            }

            $relativePath = substr($absolutePath, strlen($runtimeRoot) + 1);
            if (!is_string($relativePath) || $relativePath === '') {
                continue;
            }

            $contents = file_get_contents($absolutePath);
            if (!is_string($contents)) {
                return [
                    'ok' => false,
                    'files' => [],
                    'error' => 'runtime bundle file の読み込みに失敗しました: ' . $relativePath,
                ];
            }

            $transformedContents = app_project_output_proxy_runtime_bundle_transform_file(
                str_replace('\\', '/', $relativePath),
                $contents,
            );
            $bundleRelativePath = '_support/runtime_dbclasses/' . str_replace('\\', '/', $relativePath);
            if (isset($files[$bundleRelativePath]) && $files[$bundleRelativePath] !== $transformedContents) {
                return [
                    'ok' => false,
                    'files' => [],
                    'error' => 'proxy runtime bundle file が複数 root で衝突しました: ' . $bundleRelativePath,
                ];
            }

            $files[$bundleRelativePath] = $transformedContents;
        }
    }

    return [
        'ok' => true,
        'files' => $files,
        'error' => '',
    ];
}

function app_project_output_proxy_runtime_bundle_transform_file(string $relativePath, string $contents): string
{
    if (preg_match('#^base/dbaccess-[^/]+\.php$#', $relativePath) === 1) {
        $rewritten = preg_replace(
            '/^require_once __DIR__ \. \'\/\.\.\/_support\/legacy-dbaccess\/[^\']+\.php\';\s*$/m',
            '',
            $contents,
        );
        if (!is_string($rewritten)) {
            throw new RuntimeException('proxy runtime dbaccess base の legacy require 変換に失敗しました: ' . $relativePath);
        }

        $rewritten = preg_replace_callback(
            '/^(\s*)class\s+([A-Za-z0-9_]+Base)\s+extends\s+[A-Za-z0-9_]+Legacy\b/m',
            static fn (array $matches): string => $matches[1] . 'class ' . $matches[2],
            $rewritten,
            1,
            $count,
        );
        if (!is_string($rewritten)) {
            throw new RuntimeException('proxy runtime dbaccess base class 変換に失敗しました: ' . $relativePath);
        }

        if ($count === 0 && preg_match('/^(\s*)class\s+[A-Za-z0-9_]+Base\b/m', $rewritten) === 1) {
            return $rewritten;
        }

        if ($count !== 1) {
            throw new RuntimeException('proxy runtime dbaccess base class 変換に失敗しました: ' . $relativePath);
        }

        return $rewritten;
    }

    if (preg_match('#^_support/legacy-dbaccess/[^/]+\.php$#', $relativePath) !== 1) {
        return $contents;
    }

    $rewritten = preg_replace(
        '/^require_once __DIR__ \. \'\/(?:_runtime_loader\.php|base\/[^\']+\.php)\'\s*;\s*$/m',
        '',
        $contents,
    );
    if (!is_string($rewritten)) {
        throw new RuntimeException('legacy dbaccess support file の require 行変換に失敗しました: ' . $relativePath);
    }

    $rewritten = preg_replace_callback(
        '/^(\s*)class\s+([A-Za-z0-9_]+)\b/m',
        static function (array $matches): string {
            $className = $matches[2];
            if (!str_ends_with($className, 'Legacy')) {
                $className .= 'Legacy';
            }

            return $matches[1] . 'class ' . $className;
        },
        $rewritten,
        1,
        $count,
    );
    if (!is_string($rewritten) || $count !== 1) {
        throw new RuntimeException('legacy dbaccess support class 変換に失敗しました: ' . $relativePath);
    }

    return $rewritten;
}

function app_project_output_proxy_build_server_emitted_files(array $context): array
{
    $files = [];
    $files['README.md'] = app_project_output_proxy_server_readme_text($context) . PHP_EOL;
    $files['build-plan.json'] = app_project_output_proxy_json_text($context['plan']);
    $files['_support/runtime_dbclasses/autoload_proxy_runtime.php'] = app_project_output_proxy_server_autoload_text($context) . PHP_EOL;
    $files['_support/custom_proxy_runtime.php'] = app_project_output_proxy_server_runtime_text() . PHP_EOL;
    $files['_support/custom_proxy_loader.php'] = app_project_output_proxy_server_loader_text(
        app_project_output_custom_layer_relative_path($context['project_key'], $context['source_output_key']),
        (string) ($context['runtime_source_relative_path'] ?? ''),
    ) . PHP_EOL;

    $runtimeBundleResult = app_project_output_proxy_runtime_bundle_files($context);
    if (!$runtimeBundleResult['ok']) {
        return [
            'ok' => false,
            'files' => [],
            'error' => $runtimeBundleResult['error'],
        ];
    }
    foreach ($runtimeBundleResult['files'] as $relativePath => $contents) {
        $files[$relativePath] = $contents;
    }

    foreach ($context['proxy_items'] as $item) {
        $handlerRelativePath = 'handlers/' . $item['handler_class'] . '.php';
        $files['_base/' . $handlerRelativePath] = app_project_output_proxy_server_handler_base_text($item) . PHP_EOL;
        $files['_wrappers/' . $handlerRelativePath] = app_project_output_proxy_server_handler_wrapper_text($item) . PHP_EOL;
        $files[$item['endpoint_filename']] = app_project_output_proxy_server_entrypoint_text($item) . PHP_EOL;
    }

    return [
        'ok' => true,
        'files' => app_project_output_proxy_file_list($files),
        'error' => '',
    ];
}

function app_project_output_proxy_server_readme_text(array $context): string
{
    $lines = [
        '# Custom Proxy Server Artifact',
        '',
        'Generated from `'
            . $context['project_key']
            . '/'
            . $context['source_output_key']
            . '`.',
        '',
        'This bundle contains:',
        '- PHP endpoint entrypoints for the targeted custom proxies',
        '- minimal copied `data-*` / `dbaccess-*` runtime files for referenced sources',
        '- generated base handler classes and default wrapper classes',
        '',
        'Environment variables:',
        '- `MTOOL_PROXY_DB_HOST`',
        '- `MTOOL_PROXY_DB_PORT`',
        '- `MTOOL_PROXY_DB_USER`',
        '- `MTOOL_PROXY_DB_PASSWORD`',
        '- `MTOOL_PROXY_DB_NAME`',
        '- `MTOOL_PROXY_PROJECT_TOKEN`',
        '- `MTOOL_PROXY_CORS_ALLOW_ORIGIN`',
        '- `MTOOL_PROXY_CORS_ALLOW_HEADERS`',
        '',
        'Custom hook points:',
        '- `'
            . app_project_output_custom_layer_relative_path($context['project_key'], $context['source_output_key'])
            . '/bootstrap.php`',
        '- wrapper handler methods `authorizeByGetFunction()` / `authorizeByLoginCookieToken()` when the auth strategy requires them',
    ];

    foreach ($context['proxy_items'] as $item) {
        $lines[] = '- `'
            . app_project_output_custom_layer_relative_path($context['project_key'], $context['source_output_key'])
            . '/handlers/'
            . $item['handler_class']
            . '.php`';
    }

    return implode("\n", $lines);
}

function app_project_output_proxy_server_autoload_text(array $context): string
{
    $requireLines = [];
    foreach ($context['source_entities'] as $entity) {
        $requireLines[] = "require_once __DIR__ . '/" . $entity['data_file'] . "';";
    }
    foreach ($context['source_entities'] as $entity) {
        $requireLines[] = "require_once __DIR__ . '/" . $entity['dbaccess_file'] . "';";
    }

    $requires = implode("\n", $requireLines);

    return <<<PHP
<?php

declare(strict_types=1);

\$mtooldb = null;
\$last_sql_command_for_mtooldb = '';
\$time_for_reconnect_mtooldb_if_necessary = time();

require_once __DIR__ . '/_support/mtool_runtime_db.php';
{$requires}
PHP;
}

function app_project_output_proxy_server_runtime_text(
    string $endpointBaseClass = 'MtoolGeneratedCustomProxyEndpointBase',
): string
{
    $template = <<<'PHP'
<?php

declare(strict_types=1);

abstract class __ENDPOINT_BASE_CLASS__
{
    final public function handle(): void
    {
        $this->sendCorsHeaders();

        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'POST'));
        if ($method === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        try {
            $payload = $this->decodeRequestPayload();
            $this->beforeHandle($payload);
            $this->authorizeRequest($payload);

            $response = [
                '_status' => 'OK',
                'Message' => $this->proxyDisplayName() . ' completed.',
            ];

            $this->withOptionalTransaction(function () use ($payload, &$response): void {
                foreach ($this->stepDefinitions() as $step) {
                    $this->handleStep($step, $payload, $response);
                }
            });

            $this->afterHandle($payload, $response);
            $this->sendJson(200, $response);
        } catch (Throwable $throwable) {
            $this->onException($throwable);
            $this->sendJson(500, [
                '_status' => 'NG',
                'Message' => $throwable->getMessage(),
            ]);
        }
    }

    abstract protected function proxyDisplayName(): string;

    abstract protected function stepDefinitions(): array;

    protected function usesTransaction(): bool
    {
        return false;
    }

    protected function continueEvenIfFailedToInsert(): bool
    {
        return false;
    }

    protected function authStrategy(): string
    {
        return 'manual';
    }

    protected function authPolicy(): array
    {
        return [];
    }

    protected function expectedProjectToken(): string
    {
        return getenv('MTOOL_PROXY_PROJECT_TOKEN') ?: '';
    }

    protected function expectedStaticBearerToken(): string
    {
        return getenv('DEGODB_PROXY_BEARER_TOKEN') ?: (getenv('MTOOL_PROXY_BEARER_TOKEN') ?: '');
    }

    protected function authorizationHeader(): string
    {
        return $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';
    }

    protected function singleGetFunctionName(): string
    {
        return '';
    }

    protected function authorizeByGetFunction(array $payload, string $singleGetFunctionName): bool
    {
        return false;
    }

    protected function authorizeByLoginCookieToken(string $loginCookieToken, array $payload): bool
    {
        return false;
    }

    protected function beforeHandle(array $payload): void
    {
    }

    protected function afterHandle(array $payload, array &$response): void
    {
    }

    protected function onException(Throwable $throwable): void
    {
    }

    private function decodeRequestPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('request body は JSON object である必要があります。');
        }

        return $decoded;
    }

    private function authorizeRequest(array $payload): void
    {
        $strategy = $this->authStrategy();
        $projectTokenAttempted = false;
        $projectTokenFailureReason = '';
        if ($strategy === 'no-security') {
            return;
        }

        if ($strategy === 'manual') {
            return;
        }

        if ($strategy === 'static-bearer') {
            $header = trim($this->authorizationHeader());
            if ($header === '') {
                throw new RuntimeException('Authorization bearer header が必要です。');
            }
            if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
                throw new RuntimeException('Authorization header は Bearer token 形式である必要があります。');
            }

            $suppliedToken = trim((string) ($matches[1] ?? ''));
            if ($suppliedToken === '') {
                throw new RuntimeException('Bearer token は空でない string である必要があります。');
            }

            $expectedToken = $this->expectedStaticBearerToken();
            if ($expectedToken === '') {
                throw new RuntimeException('DEGODB_PROXY_BEARER_TOKEN が未設定です。');
            }

            if (!hash_equals($expectedToken, $suppliedToken)) {
                throw new RuntimeException('Bearer token が一致しません。');
            }

            return;
        }

        if ($strategy === 'oidc-jwt-bearer') {
            $this->authorizeOidcJwtBearer();
            return;
        }

        if ($strategy === 'project-token' || $strategy === 'project-token-or-get-function') {
            if (!array_key_exists('TOKEN', $payload)) {
                if ($strategy === 'project-token') {
                    throw new RuntimeException('TOKEN が必要です。');
                }
            } else {
                $projectTokenAttempted = true;
                if (!is_string($payload['TOKEN']) || trim($payload['TOKEN']) === '') {
                    throw new RuntimeException('TOKEN は空でない string である必要があります。');
                }

                $expectedToken = $this->expectedProjectToken();
                if ($expectedToken === '') {
                    $projectTokenFailureReason = 'MTOOL_PROXY_PROJECT_TOKEN が未設定です。';
                    if ($strategy === 'project-token') {
                        throw new RuntimeException($projectTokenFailureReason);
                    }
                } elseif ($payload['TOKEN'] === $expectedToken) {
                    return;
                } else {
                    $projectTokenFailureReason = 'TOKEN が一致しません。';
                    if ($strategy === 'project-token') {
                        throw new RuntimeException($projectTokenFailureReason);
                    }
                }
            }
        }

        if ($strategy === 'get-function' || $strategy === 'project-token-or-get-function') {
            $singleGetFunctionName = trim($this->singleGetFunctionName());
            if ($singleGetFunctionName === '') {
                if ($strategy === 'project-token-or-get-function' && $projectTokenAttempted && $projectTokenFailureReason !== '') {
                    throw new RuntimeException($projectTokenFailureReason . ' get-function 用 single get function name が必要です。');
                }
                throw new RuntimeException('single get function name が必要です。');
            }

            if ($this->authorizeByGetFunction($payload, $singleGetFunctionName)) {
                return;
            }

            if ($strategy === 'project-token-or-get-function' && $projectTokenAttempted) {
                if ($projectTokenFailureReason !== '') {
                    throw new RuntimeException($projectTokenFailureReason . ' get-function 認証にも失敗しました。');
                }

                throw new RuntimeException('TOKEN も get-function も認証に失敗しました。');
            }

            throw new RuntimeException('get-function 認証に失敗しました。');
        }

        if ($strategy === 'login-cookie-token') {
            if (!array_key_exists('LOGIN_COOKIE_TOKEN', $payload)) {
                throw new RuntimeException('LOGIN_COOKIE_TOKEN が必要です。');
            }

            if (!is_string($payload['LOGIN_COOKIE_TOKEN']) || trim($payload['LOGIN_COOKIE_TOKEN']) === '') {
                throw new RuntimeException('LOGIN_COOKIE_TOKEN は空でない string である必要があります。');
            }

            if ($this->authorizeByLoginCookieToken($payload['LOGIN_COOKIE_TOKEN'], $payload)) {
                return;
            }

            throw new RuntimeException('LOGIN_COOKIE_TOKEN 認証に失敗しました。');
        }

        throw new RuntimeException('未対応の auth strategy です。');
    }

    private function authorizeOidcJwtBearer(): void
    {
        $header = trim($this->authorizationHeader());
        if ($header === '') {
            throw new RuntimeException('Authorization bearer header が必要です。');
        }
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            throw new RuntimeException('Authorization header は Bearer token 形式である必要があります。');
        }

        $jwt = trim((string) ($matches[1] ?? ''));
        if ($jwt === '') {
            throw new RuntimeException('Bearer token は空でない string である必要があります。');
        }

        $policy = $this->authPolicy();
        $issuer = rtrim((string) ($policy['issuer'] ?? ''), '/');
        $audience = (string) ($policy['audience'] ?? '');
        if ($issuer === '' || $audience === '') {
            throw new RuntimeException('oidc-jwt-bearer policy には issuer と audience が必要です。');
        }

        $this->requireJwtRuntime();
        $jwks = $this->loadOidcJwks($policy);
        $decoded = \Firebase\JWT\JWT::decode($jwt, \Firebase\JWT\JWK::parseKeySet($jwks));
        $claims = json_decode(json_encode($decoded, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($claims)) {
            throw new RuntimeException('OIDC JWT claims が object ではありません。');
        }

        if (rtrim((string) ($claims['iss'] ?? ''), '/') !== $issuer) {
            throw new RuntimeException('OIDC JWT issuer が一致しません。');
        }

        $tokenAudience = $claims['aud'] ?? null;
        $audiences = is_array($tokenAudience) ? $tokenAudience : [$tokenAudience];
        if (!in_array($audience, $audiences, true)) {
            throw new RuntimeException('OIDC JWT audience が一致しません。');
        }

        $requiredClaims = $policy['required_claims'] ?? [];
        if (!is_array($requiredClaims)) {
            throw new RuntimeException('oidc-jwt-bearer required_claims は object である必要があります。');
        }
        foreach ($requiredClaims as $claimName => $expectedValue) {
            $claimName = (string) $claimName;
            if (!array_key_exists($claimName, $claims)) {
                throw new RuntimeException('OIDC JWT required claim が不足しています: ' . $claimName);
            }
            if (!$this->oidcClaimMatches($claims[$claimName], $expectedValue)) {
                throw new RuntimeException('OIDC JWT required claim が一致しません: ' . $claimName);
            }
        }
    }

    private function requireJwtRuntime(): void
    {
        if (class_exists(\Firebase\JWT\JWT::class) && class_exists(\Firebase\JWT\JWK::class)) {
            return;
        }

        $candidates = [
            getcwd() . '/vendor/autoload.php',
            dirname(__DIR__, 2) . '/vendor/autoload.php',
            dirname(__DIR__, 3) . '/vendor/autoload.php',
            dirname(__DIR__, 4) . '/vendor/autoload.php',
        ];
        foreach ($candidates as $autoloadPath) {
            if (is_file($autoloadPath)) {
                require_once $autoloadPath;
                break;
            }
        }

        if (!class_exists(\Firebase\JWT\JWT::class) || !class_exists(\Firebase\JWT\JWK::class)) {
            throw new RuntimeException('OIDC JWT 検証に必要な Composer dependencies が見つかりません。');
        }
    }

    /**
     * @param array<string,mixed> $policy
     * @return array<string,mixed>
     */
    private function loadOidcJwks(array $policy): array
    {
        $jwksJsonEnv = trim((string) ($policy['jwks_json_env'] ?? ''));
        if ($jwksJsonEnv !== '') {
            $jwksJson = getenv($jwksJsonEnv);
            if (!is_string($jwksJson) || trim($jwksJson) === '') {
                throw new RuntimeException('OIDC JWKS env が未設定です: ' . $jwksJsonEnv);
            }

            return $this->decodeOidcJson($jwksJson, 'OIDC JWKS env');
        }

        $jwksUri = trim((string) ($policy['jwks_uri'] ?? ''));
        if ($jwksUri === '') {
            $discoveryUrl = trim((string) ($policy['discovery_url'] ?? ''));
            if ($discoveryUrl === '') {
                throw new RuntimeException('oidc-jwt-bearer policy には discovery_url、jwks_uri、jwks_json_env のいずれかが必要です。');
            }
            $discovery = $this->fetchOidcJson($discoveryUrl, 'OIDC discovery');
            $jwksUri = trim((string) ($discovery['jwks_uri'] ?? ''));
            if ($jwksUri === '') {
                throw new RuntimeException('OIDC discovery に jwks_uri がありません。');
            }
        }

        return $this->fetchOidcJson($jwksUri, 'OIDC JWKS');
    }

    /**
     * @return array<string,mixed>
     */
    private function fetchOidcJson(string $url, string $label): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);
        $json = @file_get_contents($url, false, $context);
        if (!is_string($json) || trim($json) === '') {
            throw new RuntimeException($label . ' を取得できません。');
        }

        return $this->decodeOidcJson($json, $label);
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeOidcJson(string $json, string $label): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException($label . ' は JSON object である必要があります。');
        }

        return $decoded;
    }

    private function oidcClaimMatches(mixed $actualValue, mixed $expectedValue): bool
    {
        if (is_array($actualValue) && !is_array($expectedValue)) {
            return in_array($expectedValue, $actualValue, true);
        }
        if (is_string($actualValue) && is_string($expectedValue)) {
            $parts = preg_split('/\s+/', trim($actualValue));
            if (is_array($parts) && in_array($expectedValue, $parts, true)) {
                return true;
            }
        }

        return $actualValue === $expectedValue;
    }

    private function withOptionalTransaction(callable $callback): void
    {
        if (!$this->usesTransaction()) {
            $callback();
            return;
        }

        connect_mtooldb_if_not_yet();
        global $mtooldb;

        if (!is_object($mtooldb)
            || !method_exists($mtooldb, 'beginTransaction')
            || !method_exists($mtooldb, 'commit')
            || !method_exists($mtooldb, 'rollBack')
            || !method_exists($mtooldb, 'inTransaction')) {
            throw new RuntimeException('transaction に必要な DB connection がありません。');
        }

        if (!$mtooldb->beginTransaction()) {
            throw new RuntimeException(
                'transaction を開始できません: ' . (string) ($mtooldb->error ?? ''),
            );
        }

        try {
            $callback();
            if (!$mtooldb->commit()) {
                throw new RuntimeException(
                    'transaction を commit できません: ' . (string) ($mtooldb->error ?? ''),
                );
            }
        } catch (Throwable $throwable) {
            if ($mtooldb->inTransaction() && !$mtooldb->rollBack()) {
                throw new RuntimeException(
                    'transaction を rollback できません: ' . (string) ($mtooldb->error ?? ''),
                    0,
                    $throwable,
                );
            }

            throw $throwable;
        }
    }

    private function handleStep(array $step, array $payload, array &$response): void
    {
        $requestKey = (string) ($step['request_key'] ?? '');
        $responseMode = (string) ($step['response_mode'] ?? 'none');
        $responseKey = (string) ($step['response_key'] ?? '');

        if ($requestKey === '') {
            $stepInput = $payload;
            unset($stepInput['TOKEN'], $stepInput['LOGIN_COOKIE_TOKEN']);
        } else {
            $stepInput = $payload[$requestKey] ?? ($step['is_list'] ? [] : []);
        }

        if (!is_array($stepInput)) {
            $inputLabel = $requestKey !== '' ? $requestKey : 'request payload';
            throw new RuntimeException($inputLabel . ' は object または array である必要があります。');
        }

        if ($step['is_list']) {
            $responseItems = [];
            $insertIds = [];

            foreach ($stepInput as $item) {
                if (!is_array($item)) {
                    throw new RuntimeException($requestKey . ' の各要素は object である必要があります。');
                }

                $result = $this->executeStep($step, $item);
                if ($responseMode === 'insert-id-list') {
                    $insertIds[] = $this->lastInsertIdOrNull();
                } elseif ($responseMode === 'step-result-list') {
                    $responseItems[] = [
                        'Result' => $this->normalizeValue($result),
                    ];
                }
            }

            if ($responseKey !== '') {
                if ($responseMode === 'insert-id-list') {
                    $response[$responseKey] = $insertIds;
                } elseif ($responseMode === 'step-result-list') {
                    $response[$responseKey] = $responseItems;
                }
            }

            return;
        }

        $result = $this->executeStep($step, $stepInput);
        if ($responseKey === '') {
            return;
        }

        if ($responseMode === 'insert-id-single') {
            $response[$responseKey] = $this->lastInsertIdOrNull();
            return;
        }

        if ($responseMode === 'direct-result') {
            $response[$responseKey] = $this->normalizeValue($result);
            return;
        }

        if ($responseMode === 'step-result-single') {
            $response[$responseKey] = [
                'Result' => $this->normalizeValue($result),
            ];
        }
    }

    private function executeStep(array $step, array $stepInput)
    {
        $dbaccessClass = (string) ($step['dbaccess_class'] ?? '');
        $functionName = (string) ($step['function_name'] ?? '');
        if ($dbaccessClass === '' || $functionName === '') {
            throw new RuntimeException('dbaccess step 定義が不正です。');
        }

        if (!class_exists($dbaccessClass)) {
            throw new RuntimeException('dbaccess class が見つかりません: ' . $dbaccessClass);
        }

        $instance = new $dbaccessClass();
        if (!method_exists($instance, $functionName)) {
            throw new RuntimeException('dbaccess function が見つかりません: ' . $functionName);
        }

        $arguments = [];
        if (($step['input_kind'] ?? '') === 'object') {
            $paramName = (string) ($step['object_param_name'] ?? '');
            $objectClass = (string) ($step['object_class'] ?? '');
            $objectPayload = $stepInput[$paramName] ?? [];
            if (!is_array($objectPayload)) {
                throw new RuntimeException($paramName . ' は object である必要があります。');
            }

            $arguments[] = $this->hydrateDataObject($objectClass, $objectPayload);
        } else {
            foreach ((array) ($step['parameter_names'] ?? []) as $paramName) {
                $arguments[] = $stepInput[(string) $paramName] ?? null;
            }
        }

        $result = $instance->$functionName(...$arguments);
        if ($result === false) {
            $action = (string) ($step['action'] ?? '');
            if ($action === 'insert' && $this->continueEvenIfFailedToInsert()) {
                return null;
            }

            throw new RuntimeException('step failed: ' . $functionName);
        }

        return $result;
    }

    private function hydrateDataObject(string $className, array $payload): object
    {
        if ($className === '' || !class_exists($className)) {
            throw new RuntimeException('data class が見つかりません: ' . $className);
        }

        $object = new $className();
        foreach ($payload as $key => $value) {
            if (!is_string($key) || !property_exists($object, $key)) {
                continue;
            }

            $object->$key = $value;
        }

        return $object;
    }

    private function normalizeValue($value)
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            return $this->normalizeValue(get_object_vars($value));
        }

        return $value;
    }

    private function lastInsertIdOrNull(): ?int
    {
        global $mtooldb;

        if (is_object($mtooldb) && method_exists($mtooldb, 'lastInsertId')) {
            return $mtooldb->lastInsertId();
        }

        if ($mtooldb instanceof mysqli) {
            $insertId = $mtooldb->insert_id;

            return is_numeric($insertId) ? (int) $insertId : null;
        }

        return null;
    }

    private function sendCorsHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: ' . (getenv('MTOOL_PROXY_CORS_ALLOW_ORIGIN') ?: '*'));
        header('Access-Control-Allow-Headers: ' . (getenv('MTOOL_PROXY_CORS_ALLOW_HEADERS') ?: 'Origin, X-Requested-With, Content-Type, Accept'));
    }

    private function sendJson(int $statusCode, array $payload): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        echo is_string($json) ? ($json . PHP_EOL) : "{}\n";
    }
}
PHP;

    return str_replace('__ENDPOINT_BASE_CLASS__', $endpointBaseClass, $template);
}

function app_project_output_proxy_server_loader_text(
    string $customLayerRelativePath,
    string $runtimeSourceRelativePath,
    string $runtimeFilename = 'custom_proxy_runtime.php',
    string $helperPrefix = 'mtool_generated_custom_proxy',
): string
{
    $exportedCustomLayerPath = var_export($customLayerRelativePath, true);
    $bundleRootDepth = app_project_output_bundle_root_depth_from_runtime_source_relative_path(
        $runtimeSourceRelativePath,
    );
    $exportedBundleRootDepth = var_export($bundleRootDepth, true);
    $exportedRuntimeFilename = var_export($runtimeFilename, true);
    $bundleRootFunction = $helperPrefix . '_bundle_root';
    $customLayerRootFunction = $helperPrefix . '_custom_layer_root';
    $loadBootstrapFunction = $helperPrefix . '_load_custom_bootstrap';
    $runFunction = $helperPrefix . '_run';

    return <<<PHP
<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_dbclasses/autoload_proxy_runtime.php';
require_once __DIR__ . '/' . {$exportedRuntimeFilename};

function {$bundleRootFunction}(string \$runtimeSourceRoot): string
{
    return dirname(\$runtimeSourceRoot, {$exportedBundleRootDepth});
}

function {$customLayerRootFunction}(string \$runtimeSourceRoot): string
{
    return {$bundleRootFunction}(\$runtimeSourceRoot) . '/' . {$exportedCustomLayerPath};
}

function {$loadBootstrapFunction}(string \$runtimeSourceRoot): void
{
    static \$loaded = false;
    if (\$loaded) {
        return;
    }

    \$loaded = true;
    \$bootstrapPath = {$customLayerRootFunction}(\$runtimeSourceRoot) . '/bootstrap.php';
    if (is_file(\$bootstrapPath)) {
        require_once \$bootstrapPath;
    }
}

function {$runFunction}(
    string \$runtimeSourceRoot,
    string \$handlerRelativePath,
    string \$baseClassName,
    string \$wrapperClassName,
): void {
    \$basePath = \$runtimeSourceRoot . '/_base/' . \$handlerRelativePath;
    \$defaultWrapperPath = \$runtimeSourceRoot . '/_wrappers/' . \$handlerRelativePath;
    \$customWrapperPath = {$customLayerRootFunction}(\$runtimeSourceRoot) . '/' . \$handlerRelativePath;

    if (!is_file(\$basePath)) {
        throw new RuntimeException('Missing base handler: ' . \$handlerRelativePath);
    }

    require_once \$basePath;
    {$loadBootstrapFunction}(\$runtimeSourceRoot);

    if (is_file(\$customWrapperPath)) {
        require_once \$customWrapperPath;
    } else {
        if (!is_file(\$defaultWrapperPath)) {
            throw new RuntimeException('Missing wrapper handler: ' . \$handlerRelativePath);
        }
        require_once \$defaultWrapperPath;
    }

    if (!class_exists(\$wrapperClassName)) {
        throw new RuntimeException('Wrapper class が見つかりません: ' . \$wrapperClassName);
    }

    \$handler = new \$wrapperClassName();
    if (!(\$handler instanceof \$baseClassName)) {
        throw new RuntimeException('Wrapper class が base handler を継承していません: ' . \$wrapperClassName);
    }

    \$handler->handle();
}
PHP;
}

function app_project_output_proxy_server_handler_base_text(
    array $item,
    string $endpointBaseClass = 'MtoolGeneratedCustomProxyEndpointBase',
): string
{
    $stepBlocks = [];
    foreach ($item['steps'] as $step) {
        $stepBlocks[] = app_project_output_proxy_php_array_block([
            'step_no' => $step['step_no'],
            'request_key' => $step['request_key'],
            'is_list' => $step['is_list'],
            'source_name' => $step['source_name'],
            'dbaccess_class' => $step['dbaccess_class'],
            'function_name' => $step['function_name'],
            'action' => $step['action'],
            'input_kind' => $step['input_kind'],
            'object_param_name' => $step['object_param_name'],
            'object_class' => $step['object_class'],
            'parameter_names' => $step['parameter_names'],
            'response_key' => $step['response_key'],
            'response_mode' => $step['response_mode'],
        ], 3);
    }

    $stepsSource = implode(",\n", $stepBlocks);
    $displayName = var_export($item['display_name'], true);
    $authStrategy = var_export($item['auth_policy']['strategy_key'], true);
    $authPolicy = app_project_output_proxy_php_array_block($item['auth_policy']['policy'] ?? [], 2);
    $singleGetFunctionName = var_export($item['auth_policy']['single_get_function_name'], true);
    $usesTransaction = $item['in_transaction'] ? 'true' : 'false';
    $continueEvenIfFailedToInsert = $item['continue_even_if_failed_to_insert'] ? 'true' : 'false';

    return <<<PHP
<?php

declare(strict_types=1);

class {$item['base_handler_class']} extends {$endpointBaseClass}
{
    protected function proxyDisplayName(): string
    {
        return {$displayName};
    }

    protected function usesTransaction(): bool
    {
        return {$usesTransaction};
    }

    protected function continueEvenIfFailedToInsert(): bool
    {
        return {$continueEvenIfFailedToInsert};
    }

    protected function authStrategy(): string
    {
        return {$authStrategy};
    }

    protected function authPolicy(): array
    {
        return {$authPolicy};
    }

    protected function singleGetFunctionName(): string
    {
        return {$singleGetFunctionName};
    }

    protected function stepDefinitions(): array
    {
        return [
{$stepsSource}
        ];
    }
}
PHP;
}

function app_project_output_proxy_server_handler_wrapper_text(array $item): string
{
    return <<<PHP
<?php

declare(strict_types=1);

// Extend this wrapper to add project-specific hooks such as:
// - authorizeByGetFunction()
// - authorizeByLoginCookieToken()
// - beforeHandle() / afterHandle()

class {$item['handler_class']} extends {$item['base_handler_class']}
{
}
PHP;
}

function app_project_output_proxy_server_entrypoint_text(
    array $item,
    string $loaderFilename = '_support/custom_proxy_loader.php',
    string $runnerFunction = 'mtool_generated_custom_proxy_run',
): string
{
    $handlerRelativePath = var_export('handlers/' . $item['handler_class'] . '.php', true);
    $baseClass = var_export($item['base_handler_class'], true);
    $wrapperClass = var_export($item['handler_class'], true);
    $exportedLoaderFilename = var_export($loaderFilename, true);

    return <<<PHP
<?php

declare(strict_types=1);

require_once __DIR__ . '/' . {$exportedLoaderFilename};

{$runnerFunction}(
    __DIR__,
    {$handlerRelativePath},
    {$baseClass},
    {$wrapperClass},
);
PHP;
}

function app_project_output_single_proxy_build_server_emitted_files(array $context): array
{
    $files = [];
    $files['README.md'] = app_project_output_single_proxy_server_readme_text($context) . PHP_EOL;
    $files['build-plan.json'] = app_project_output_proxy_json_text($context['plan']);
    $files['_support/runtime_dbclasses/autoload_proxy_runtime.php'] = app_project_output_proxy_server_autoload_text($context) . PHP_EOL;
    $files['_support/single_proxy_runtime.php'] = app_project_output_proxy_server_runtime_text(
        'MtoolGeneratedSingleProxyEndpointBase',
    ) . PHP_EOL;
    $files['_support/single_proxy_loader.php'] = app_project_output_proxy_server_loader_text(
        app_project_output_custom_layer_relative_path($context['project_key'], $context['source_output_key']),
        (string) ($context['runtime_source_relative_path'] ?? ''),
        'single_proxy_runtime.php',
        'mtool_generated_single_proxy',
    ) . PHP_EOL;

    $runtimeBundleResult = app_project_output_proxy_runtime_bundle_files($context);
    if (!$runtimeBundleResult['ok']) {
        return [
            'ok' => false,
            'files' => [],
            'error' => $runtimeBundleResult['error'],
        ];
    }
    foreach ($runtimeBundleResult['files'] as $relativePath => $contents) {
        $files[$relativePath] = $contents;
    }

    foreach ($context['proxy_items'] as $item) {
        $handlerRelativePath = 'handlers/' . $item['handler_class'] . '.php';
        $files['_base/' . $handlerRelativePath] = app_project_output_proxy_server_handler_base_text(
            $item,
            'MtoolGeneratedSingleProxyEndpointBase',
        ) . PHP_EOL;
        $files['_wrappers/' . $handlerRelativePath] = app_project_output_proxy_server_handler_wrapper_text($item) . PHP_EOL;
        $files[$item['endpoint_filename']] = app_project_output_proxy_server_entrypoint_text(
            $item,
            '_support/single_proxy_loader.php',
            'mtool_generated_single_proxy_run',
        ) . PHP_EOL;
    }

    return [
        'ok' => true,
        'files' => app_project_output_proxy_file_list($files),
        'error' => '',
    ];
}

function app_project_output_single_proxy_server_readme_text(array $context): string
{
    $lines = [
        '# Single Function Proxy Server Artifact',
        '',
        'Generated from `'
            . $context['project_key']
            . '/'
            . $context['source_output_key']
            . '`.',
        '',
        'This bundle contains:',
        '- PHP endpoint entrypoints for the targeted single-function proxies',
        '- minimal copied `data-*` / `dbaccess-*` runtime files for referenced sources',
        '- generated base handler classes and default wrapper classes',
        '',
        'Request / response shape:',
        '- request payloads stay function-local and direct',
        '- auth fields (`TOKEN`, `LOGIN_COOKIE_TOKEN`) stay top-level when required',
        '- select results return top-level `Result`; insert returns top-level `InsertID`',
        '',
        'Environment variables:',
        '- `MTOOL_PROXY_DB_HOST`',
        '- `MTOOL_PROXY_DB_PORT`',
        '- `MTOOL_PROXY_DB_USER`',
        '- `MTOOL_PROXY_DB_PASSWORD`',
        '- `MTOOL_PROXY_DB_NAME`',
        '- `MTOOL_PROXY_PROJECT_TOKEN`',
        '- `MTOOL_PROXY_CORS_ALLOW_ORIGIN`',
        '- `MTOOL_PROXY_CORS_ALLOW_HEADERS`',
        '',
        'Custom hook points:',
        '- `'
            . app_project_output_custom_layer_relative_path($context['project_key'], $context['source_output_key'])
            . '/bootstrap.php`',
        '- wrapper handler methods `authorizeByGetFunction()` / `authorizeByLoginCookieToken()` when the auth strategy requires them',
    ];

    foreach ($context['proxy_items'] as $item) {
        $lines[] = '- `'
            . app_project_output_custom_layer_relative_path($context['project_key'], $context['source_output_key'])
            . '/handlers/'
            . $item['handler_class']
            . '.php`';
    }

    return implode("\n", $lines);
}

function app_project_output_proxy_build_client_emitted_files(array $context): array
{
    $files = [];
    $files['README.md'] = app_project_output_proxy_client_readme_text($context) . PHP_EOL;
    $files['build-plan.json'] = app_project_output_proxy_json_text($context['plan']);
    $files[$context['client_prefix'] . 'ProxyClientBase.cs'] = app_project_output_proxy_client_base_text($context) . PHP_EOL;
    $files[$context['client_prefix'] . 'ProxyClient.cs'] = app_project_output_proxy_client_wrapper_text($context) . PHP_EOL;

    foreach ($context['source_entities'] as $entity) {
        $files[$entity['data_class'] . '.cs'] = app_project_output_proxy_client_data_class_text(
            $entity,
            $context['client_namespace'],
        ) . PHP_EOL;
        $files[$entity['data_list_class'] . '.cs'] = app_project_output_proxy_client_data_list_class_text(
            $entity,
            $context['client_namespace'],
        ) . PHP_EOL;
    }

    foreach ($context['proxy_items'] as $item) {
        $files[$item['overall_request_class'] . '.cs'] = app_project_output_proxy_client_request_class_text(
            $item,
            $context['client_namespace'],
        ) . PHP_EOL;
        $files[$item['overall_result_class'] . '.cs'] = app_project_output_proxy_client_result_class_text(
            $item,
            $context['client_namespace'],
        ) . PHP_EOL;

        foreach ($item['steps'] as $step) {
            $files[$step['request_class'] . '.cs'] = app_project_output_proxy_client_step_request_class_text(
                $step,
                $context['client_namespace'],
            ) . PHP_EOL;

            if ($step['result_class'] !== '') {
                $files[$step['result_class'] . '.cs'] = app_project_output_proxy_client_step_result_class_text(
                    $step,
                    $context['client_namespace'],
                ) . PHP_EOL;
            }
        }
    }

    return [
        'ok' => true,
        'files' => app_project_output_proxy_file_list($files),
        'error' => '',
    ];
}

function app_project_output_single_proxy_build_client_emitted_files(array $context): array
{
    $files = [];
    $files['README.md'] = app_project_output_single_proxy_client_readme_text($context) . PHP_EOL;
    $files['build-plan.json'] = app_project_output_proxy_json_text($context['plan']);
    $files[$context['client_prefix'] . 'ProxyClientBase.cs'] = app_project_output_single_proxy_client_base_text($context) . PHP_EOL;
    $files[$context['client_prefix'] . 'ProxyClient.cs'] = app_project_output_single_proxy_client_wrapper_text($context) . PHP_EOL;

    foreach ($context['source_entities'] as $entity) {
        $files[$entity['data_class'] . '.cs'] = app_project_output_proxy_client_data_class_text(
            $entity,
            $context['client_namespace'],
        ) . PHP_EOL;
        $files[$entity['data_list_class'] . '.cs'] = app_project_output_proxy_client_data_list_class_text(
            $entity,
            $context['client_namespace'],
        ) . PHP_EOL;
    }

    foreach ($context['proxy_items'] as $item) {
        $files[$item['request_class'] . '.cs'] = app_project_output_single_proxy_client_request_class_text(
            $item,
            $context['client_namespace'],
        ) . PHP_EOL;
        $files[$item['result_class'] . '.cs'] = app_project_output_single_proxy_client_result_class_text(
            $item,
            $context['client_namespace'],
        ) . PHP_EOL;
    }

    return [
        'ok' => true,
        'files' => app_project_output_proxy_file_list($files),
        'error' => '',
    ];
}

function app_project_output_single_proxy_client_readme_text(array $context): string
{
    $baseUrl = trim((string) ($context['definition']['proxy_base_url'] ?? ''));
    $customLayerRelativePath = app_project_output_custom_layer_relative_path(
        $context['project_key'],
        $context['source_output_key'],
    );

    return implode("\n", [
        '# Single Function Proxy Client Artifact',
        '',
        'Generated from `'
            . $context['project_key']
            . '/'
            . $context['source_output_key']
            . '`.',
        '',
        'Primary client class:',
        '- `' . $context['client_prefix'] . 'ProxyClient`',
        '',
        'Namespace:',
        '- `' . $context['client_namespace'] . '`',
        '',
        'Default base URL:',
        '- `' . ($baseUrl !== '' ? $baseUrl : '(blank)') . '`',
        '',
        'Custom layer workspace:',
        '- `' . $customLayerRelativePath . '`',
        '',
        'The generated client keeps DTO and request/result classes direct per function.',
        'Project-specific behavior should be added by extending the wrapper client class or by composing collaborators around it.',
    ]);
}

function app_project_output_single_proxy_client_base_text(array $context): string
{
    $baseUrl = app_project_output_proxy_cs_string((string) ($context['definition']['proxy_base_url'] ?? ''));
    $methodBlocks = [];

    foreach ($context['proxy_items'] as $item) {
        $endpointFilename = app_project_output_proxy_cs_string($item['endpoint_filename']);
        $methodBlocks[] = <<<CS
        public Task<{$item['result_class']}?> {$item['client_method']}(
            {$item['request_class']} request,
            CancellationToken cancellationToken = default
        )
        {
            return PostAsJsonAsync<{$item['request_class']}, {$item['result_class']}>(
                {$endpointFilename},
                request,
                cancellationToken
            );
        }
CS;
    }

    $methods = implode("\n\n", $methodBlocks);

    return <<<CS
#nullable enable
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading;
using System.Threading.Tasks;

namespace {$context['client_namespace']}
{
    public abstract class {$context['client_prefix']}ProxyClientBase
    {
        protected HttpClient HttpClient { get; }

        protected JsonSerializerOptions JsonOptions { get; } = new JsonSerializerOptions
        {
            PropertyNameCaseInsensitive = false,
            WriteIndented = true
        };

        public string BaseUrl { get; set; } = {$baseUrl};

        protected {$context['client_prefix']}ProxyClientBase(HttpClient? httpClient = null)
        {
            HttpClient = httpClient ?? new HttpClient();
        }

        protected async Task<TResponse?> PostAsJsonAsync<TRequest, TResponse>(
            string endpointFilename,
            TRequest request,
            CancellationToken cancellationToken = default
        )
        {
            var requestUrl = string.IsNullOrWhiteSpace(BaseUrl)
                ? endpointFilename
                : BaseUrl.TrimEnd('/') + "/" + endpointFilename;

            var json = JsonSerializer.Serialize(request, JsonOptions);
            using var content = new StringContent(json, Encoding.UTF8, "application/json");
            using var response = await HttpClient.PostAsync(requestUrl, content, cancellationToken).ConfigureAwait(false);
            var body = await response.Content.ReadAsStringAsync(cancellationToken).ConfigureAwait(false);

            if (string.IsNullOrWhiteSpace(body))
            {
                return default;
            }

            response.EnsureSuccessStatusCode();
            return JsonSerializer.Deserialize<TResponse>(body, JsonOptions);
        }

{$methods}
    }
}
CS;
}

function app_project_output_single_proxy_client_wrapper_text(array $context): string
{
    return <<<CS
#nullable enable
using System.Net.Http;

namespace {$context['client_namespace']}
{
    public class {$context['client_prefix']}ProxyClient : {$context['client_prefix']}ProxyClientBase
    {
        public {$context['client_prefix']}ProxyClient(HttpClient? httpClient = null)
            : base(httpClient)
        {
        }
    }
}
CS;
}

function app_project_output_proxy_client_readme_text(array $context): string
{
    $baseUrl = trim((string) ($context['definition']['proxy_base_url'] ?? ''));
    $customLayerRelativePath = app_project_output_custom_layer_relative_path(
        $context['project_key'],
        $context['source_output_key'],
    );

    return implode("\n", [
        '# Custom Proxy Client Artifact',
        '',
        'Generated from `'
            . $context['project_key']
            . '/'
            . $context['source_output_key']
            . '`.',
        '',
        'Primary client class:',
        '- `' . $context['client_prefix'] . 'ProxyClient`',
        '',
        'Namespace:',
        '- `' . $context['client_namespace'] . '`',
        '',
        'Default base URL:',
        '- `' . ($baseUrl !== '' ? $baseUrl : '(blank)') . '`',
        '',
        'Custom layer workspace:',
        '- `' . $customLayerRelativePath . '`',
        '',
        'The generated client keeps DTO and request/result classes thin.',
        'Project-specific behavior should be added by extending the wrapper client class or by composing collaborators around it.',
    ]);
}

function app_project_output_proxy_client_base_text(array $context): string
{
    $baseUrl = app_project_output_proxy_cs_string((string) ($context['definition']['proxy_base_url'] ?? ''));
    $methodBlocks = [];

    foreach ($context['proxy_items'] as $item) {
        $endpointFilename = app_project_output_proxy_cs_string($item['endpoint_filename']);
        $methodBlocks[] = <<<CS
        public Task<{$item['overall_result_class']}?> {$item['name']}Async(
            {$item['overall_request_class']} request,
            CancellationToken cancellationToken = default
        )
        {
            return PostAsJsonAsync<{$item['overall_request_class']}, {$item['overall_result_class']}>(
                {$endpointFilename},
                request,
                cancellationToken
            );
        }
CS;
    }

    $methods = implode("\n\n", $methodBlocks);

    return <<<CS
#nullable enable
using System;
using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading;
using System.Threading.Tasks;

namespace {$context['client_namespace']}
{
    public abstract class {$context['client_prefix']}ProxyClientBase
    {
        protected HttpClient HttpClient { get; }

        protected JsonSerializerOptions JsonOptions { get; } = new JsonSerializerOptions
        {
            PropertyNameCaseInsensitive = false,
            WriteIndented = true
        };

        public string BaseUrl { get; set; } = {$baseUrl};

        protected {$context['client_prefix']}ProxyClientBase(HttpClient? httpClient = null)
        {
            HttpClient = httpClient ?? new HttpClient();
        }

        protected async Task<TResponse?> PostAsJsonAsync<TRequest, TResponse>(
            string endpointFilename,
            TRequest request,
            CancellationToken cancellationToken = default
        )
        {
            var requestUrl = string.IsNullOrWhiteSpace(BaseUrl)
                ? endpointFilename
                : BaseUrl.TrimEnd('/') + "/" + endpointFilename;

            var json = JsonSerializer.Serialize(request, JsonOptions);
            using var content = new StringContent(json, Encoding.UTF8, "application/json");
            using var response = await HttpClient.PostAsync(requestUrl, content, cancellationToken).ConfigureAwait(false);
            var body = await response.Content.ReadAsStringAsync(cancellationToken).ConfigureAwait(false);

            if (string.IsNullOrWhiteSpace(body))
            {
                return default;
            }

            response.EnsureSuccessStatusCode();
            return JsonSerializer.Deserialize<TResponse>(body, JsonOptions);
        }

{$methods}
    }
}
CS;
}

function app_project_output_proxy_client_wrapper_text(array $context): string
{
    return <<<CS
#nullable enable
using System.Net.Http;

namespace {$context['client_namespace']}
{
    public class {$context['client_prefix']}ProxyClient : {$context['client_prefix']}ProxyClientBase
    {
        public {$context['client_prefix']}ProxyClient(HttpClient? httpClient = null)
            : base(httpClient)
        {
        }
    }
}
CS;
}

function app_project_output_proxy_client_data_class_text(array $entity, string $namespace): string
{
    $propertyLines = [];
    foreach ($entity['data_properties'] as $property) {
        $propertyLines[] = '        public object? ' . $property . ' { get; set; }';
    }

    $properties = $propertyLines === [] ? "        public object? Value { get; set; }\n" : implode("\n", $propertyLines) . "\n";

    return <<<CS
#nullable enable

namespace {$namespace}
{
    public class {$entity['data_class']}
    {
{$properties}    }
}
CS;
}

function app_project_output_proxy_client_data_list_class_text(array $entity, string $namespace): string
{
    return <<<CS
#nullable enable
using System.Collections.Generic;

namespace {$namespace}
{
    public class {$entity['data_list_class']} : List<{$entity['data_class']}>
    {
    }
}
CS;
}

function app_project_output_proxy_client_request_class_text(array $item, string $namespace): string
{
    $propertyLines = app_project_output_proxy_client_auth_property_lines($item);

    foreach ($item['steps'] as $step) {
        $propertyLines[] = '        public '
            . $step['overall_request_property_type']
            . '? step'
            . $step['step_no']
            . ' { get; set; }';
    }

    $properties = implode("\n", $propertyLines) . "\n";

    return <<<CS
#nullable enable
using System.Collections.Generic;

namespace {$namespace}
{
    public class {$item['overall_request_class']}
    {
{$properties}    }
}
CS;
}

function app_project_output_proxy_client_auth_property_lines(array $item): array
{
    $strategy = (string) ($item['auth_policy']['strategy_key'] ?? '');
    $propertyLines = [];

    if (in_array($strategy, ['project-token', 'project-token-or-get-function'], true)) {
        $propertyLines[] = '        public string? TOKEN { get; set; }';
    }

    if ($strategy === 'login-cookie-token') {
        $propertyLines[] = '        public string? LOGIN_COOKIE_TOKEN { get; set; }';
    }

    return $propertyLines;
}

function app_project_output_single_proxy_client_request_class_text(array $item, string $namespace): string
{
    $propertyLines = app_project_output_proxy_client_auth_property_lines($item);
    $step = $item['steps'][0] ?? null;

    if (is_array($step)) {
        if ($step['input_kind'] === 'object') {
            $propertyLines[] = '        public '
                . $step['object_class']
                . '? '
                . $step['object_param_name']
                . ' { get; set; }';
        } else {
            foreach ($step['parameter_names'] as $parameterName) {
                $propertyLines[] = '        public object? ' . $parameterName . ' { get; set; }';
            }
        }
    }

    $properties = $propertyLines === [] ? '' : (implode("\n", $propertyLines) . "\n");

    return <<<CS
#nullable enable

namespace {$namespace}
{
    public class {$item['request_class']}
    {
{$properties}    }
}
CS;
}

function app_project_output_single_proxy_client_result_class_text(array $item, string $namespace): string
{
    $propertyLines = [
        '        public string? _status { get; set; }',
        '        public string? Message { get; set; }',
    ];

    $step = $item['steps'][0] ?? null;
    if (
        is_array($step)
        && $step['response_key'] !== ''
        && $item['response_property_type'] !== ''
    ) {
        $propertyLines[] = '        public '
            . $item['response_property_type']
            . '? '
            . $step['response_key']
            . ' { get; set; }';
    }

    $properties = implode("\n", $propertyLines) . "\n";

    return <<<CS
#nullable enable

namespace {$namespace}
{
    public class {$item['result_class']}
    {
{$properties}    }
}
CS;
}

function app_project_output_proxy_client_result_class_text(array $item, string $namespace): string
{
    $propertyLines = [
        '        public string? _status { get; set; }',
        '        public string? Message { get; set; }',
    ];

    foreach ($item['steps'] as $step) {
        if ($step['response_key'] === '' || $step['overall_result_property_type'] === '') {
            continue;
        }

        $propertyLines[] = '        public '
            . $step['overall_result_property_type']
            . '? '
            . $step['response_key']
            . ' { get; set; }';
    }

    $properties = implode("\n", $propertyLines) . "\n";

    return <<<CS
#nullable enable
using System.Collections.Generic;

namespace {$namespace}
{
    public class {$item['overall_result_class']}
    {
{$properties}    }
}
CS;
}

function app_project_output_proxy_client_step_request_class_text(array $step, string $namespace): string
{
    $propertyLines = [];
    if ($step['input_kind'] === 'object') {
        $propertyLines[] = '        public '
            . $step['object_class']
            . '? '
            . $step['object_param_name']
            . ' { get; set; }';
    } else {
        foreach ($step['parameter_names'] as $parameterName) {
            $propertyLines[] = '        public object? ' . $parameterName . ' { get; set; }';
        }
    }

    if ($propertyLines === []) {
        $propertyLines[] = '        public object? Value { get; set; }';
    }

    $properties = implode("\n", $propertyLines) . "\n";

    return <<<CS
#nullable enable

namespace {$namespace}
{
    public class {$step['request_class']}
    {
{$properties}    }
}
CS;
}

function app_project_output_proxy_client_step_result_class_text(array $step, string $namespace): string
{
    return <<<CS
#nullable enable

namespace {$namespace}
{
    public class {$step['result_class']}
    {
        public {$step['result_data_type']}? Result { get; set; }
    }
}
CS;
}

function app_project_output_proxy_cs_string(string $value): string
{
    $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return is_string($encoded) ? $encoded : '""';
}

function app_project_output_proxy_php_array_block(array $value, int $indentLevel): string
{
    $indent = str_repeat('    ', $indentLevel);
    $innerIndent = $indent . '    ';
    $lines = [$indent . '['];

    foreach ($value as $key => $item) {
        $exportedValue = app_project_output_proxy_php_value($item, $indentLevel + 1);
        $separator = str_starts_with($exportedValue, "\n") ? ' =>' : ' => ';
        $lines[] = $innerIndent . var_export((string) $key, true) . $separator . $exportedValue . ',';
    }

    $lines[] = $indent . ']';

    return implode("\n", $lines);
}

function app_project_output_proxy_php_value($value, int $indentLevel): string
{
    if (is_array($value)) {
        if ($value === []) {
            return '[]';
        }

        return "\n" . app_project_output_proxy_php_array_block($value, $indentLevel + 1);
    }

    return var_export($value, true);
}

function app_project_output_proxy_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );

    return is_string($json) ? ($json . PHP_EOL) : "{}\n";
}

function app_project_output_proxy_file_list(array $files): array
{
    ksort($files);

    $items = [];
    foreach ($files as $relativePath => $contents) {
        $items[] = [
            'relative_path' => (string) $relativePath,
            'contents' => (string) $contents,
        ];
    }

    return $items;
}
