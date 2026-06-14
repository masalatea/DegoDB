<?php

declare(strict_types=1);

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         project_pid:int,
 *         source_dump_path:string,
 *         generated_at:string,
 *         db_access_count:int,
 *         function_count:int,
 *         db_access_classes:list<array{
 *             project_pid:int,
 *             legacy_da_pid:int,
 *             source_name:string,
 *             store_base_path:string,
 *             is_autoload:string,
 *             last_modified_dt:string
 *         }>,
 *         functions:list<array{
 *             project_pid:int,
 *             legacy_da_pid:int,
 *             legacy_function_pid:int,
 *             source_name:string,
 *             function_name:string,
 *             action_type:string,
 *             function_list_order:int,
 *             single_proxy_single_get_function_pid:int
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_load_legacy_db_access_reference(string $projectKey): array
{
    $referencePath = app_legacy_db_access_reference_path($projectKey);
    if ($referencePath === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'この project に対応する legacy db access reference はまだありません。',
        ];
    }

    if (!is_file($referencePath)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy db access reference が見つかりません: ' . $referencePath,
        ];
    }

    $contents = file_get_contents($referencePath);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy db access reference を読み込めません。',
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy db access reference の JSON が不正です。',
        ];
    }

    $dbAccessClasses = $decoded['db_access_classes'] ?? null;
    $functions = $decoded['functions'] ?? null;
    if (!is_array($dbAccessClasses) || !is_array($functions)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy db access reference の db_access_classes / functions が不正です。',
        ];
    }

    $normalizedDbAccessClasses = [];
    foreach ($dbAccessClasses as $dbAccessClass) {
        if (!is_array($dbAccessClass)) {
            continue;
        }

        $legacyDaPid = (int) ($dbAccessClass['legacy_da_pid'] ?? 0);
        $sourceName = trim((string) ($dbAccessClass['source_name'] ?? ''));
        if ($legacyDaPid <= 0 || $sourceName === '') {
            continue;
        }

        $normalizedDbAccessClasses[] = [
            'project_pid' => (int) ($dbAccessClass['project_pid'] ?? 0),
            'legacy_da_pid' => $legacyDaPid,
            'source_name' => $sourceName,
            'store_base_path' => (string) ($dbAccessClass['store_base_path'] ?? ''),
            'is_autoload' => trim((string) ($dbAccessClass['is_autoload'] ?? '0')) === '1' ? '1' : '0',
            'last_modified_dt' => (string) ($dbAccessClass['last_modified_dt'] ?? ''),
        ];
    }

    $normalizedFunctions = [];
    foreach ($functions as $function) {
        if (!is_array($function)) {
            continue;
        }

        $legacyDaPid = (int) ($function['legacy_da_pid'] ?? 0);
        $legacyFunctionPid = (int) ($function['legacy_function_pid'] ?? 0);
        $sourceName = trim((string) ($function['source_name'] ?? ''));
        $functionName = trim((string) ($function['function_name'] ?? ''));
        if ($legacyDaPid <= 0 || $legacyFunctionPid <= 0 || $sourceName === '' || $functionName === '') {
            continue;
        }

        $normalizedFunctions[] = [
            'project_pid' => (int) ($function['project_pid'] ?? 0),
            'legacy_da_pid' => $legacyDaPid,
            'legacy_function_pid' => $legacyFunctionPid,
            'source_name' => $sourceName,
            'function_name' => $functionName,
            'action_type' => strtoupper(trim((string) ($function['action_type'] ?? ''))),
            'function_list_order' => (int) ($function['function_list_order'] ?? 0),
            'single_proxy_single_get_function_pid' => (int) ($function['single_proxy_single_get_function_pid'] ?? 0),
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'project_pid' => (int) ($decoded['project_pid'] ?? 0),
            'source_dump_path' => (string) ($decoded['source_dump_path'] ?? ''),
            'generated_at' => (string) ($decoded['generated_at'] ?? ''),
            'db_access_count' => (int) ($decoded['db_access_count'] ?? count($normalizedDbAccessClasses)),
            'function_count' => (int) ($decoded['function_count'] ?? count($normalizedFunctions)),
            'db_access_classes' => $normalizedDbAccessClasses,
            'functions' => $normalizedFunctions,
        ],
        'error' => '',
    ];
}

function app_legacy_db_access_reference_path(string $projectKey): string
{
    $normalizedProjectKey = strtoupper(trim($projectKey));
    if ($normalizedProjectKey === 'MTOOL') {
        return dirname(__DIR__) . '/reference/mtool-legacy-db-access-catalog.json';
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_legacy_db_access_reference_current_da_pid_map(string $projectKey): array
{
    $reference = app_load_legacy_db_access_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $map = [];
    foreach ($reference['item']['db_access_classes'] as $dbAccessClass) {
        $map[(string) $dbAccessClass['legacy_da_pid']] = $dbAccessClass['source_name'];
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
function app_legacy_db_access_reference_current_function_pid_map(string $projectKey): array
{
    $reference = app_load_legacy_db_access_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $map = [];
    foreach ($reference['item']['functions'] as $function) {
        $map[(string) $function['legacy_function_pid']] = [
            'source_name' => $function['source_name'],
            'function_name' => $function['function_name'],
        ];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}
