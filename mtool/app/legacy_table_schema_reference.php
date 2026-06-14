<?php

declare(strict_types=1);

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         source_schema_name:string,
 *         table_count:int,
 *         column_count:int,
 *         generated_at:string,
 *         tables:list<array{
 *             name:string,
 *             column_count:int,
 *             columns:list<array{
 *                 name:string,
 *                 datatype:string,
 *                 is_null:string,
 *                 is_key:string,
 *                 is_default:string,
 *                 extra:string,
 *                 column_list_order:int
 *             }>
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_load_legacy_table_schema_reference(string $projectKey): array
{
    $referencePath = app_legacy_table_schema_reference_path($projectKey);
    if ($referencePath === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'この project に対応する legacy table schema reference はまだありません。',
        ];
    }

    if (!is_file($referencePath)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy table schema reference が見つかりません: ' . $referencePath,
        ];
    }

    $contents = file_get_contents($referencePath);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy table schema reference を読み込めません。',
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy table schema reference の JSON が不正です。',
        ];
    }

    $tables = $decoded['tables'] ?? null;
    if (!is_array($tables)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy table schema reference の tables が不正です。',
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'source_schema_name' => (string) ($decoded['source_schema_name'] ?? ''),
            'table_count' => (int) ($decoded['table_count'] ?? 0),
            'column_count' => (int) ($decoded['column_count'] ?? 0),
            'generated_at' => (string) ($decoded['generated_at'] ?? ''),
            'tables' => $tables,
        ],
        'error' => '',
    ];
}

function app_legacy_table_schema_reference_path(string $projectKey): string
{
    $normalizedProjectKey = strtoupper(trim($projectKey));
    if ($normalizedProjectKey === 'MTOOL') {
        return dirname(__DIR__) . '/reference/mtool-legacy-table-schema.json';
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_mtool_self_host_legacy_table_alias_map(): array
{
    return [
        'projects' => 'Project',
        'project_memberships' => 'ProjectUser',
        'project_source_outputs' => 'ProjectSourceOutput',
        'project_compare_outputs' => 'CompareOutput',
        'project_compare_output_additional_paths' => 'CompareOutputAdditionalPath',
        'project_db_access_classes' => 'da',
        'project_db_access_functions' => 'dafunc',
        'project_db_access_function_select_wheres' => 'dafuncselectwhere',
        'project_db_access_function_select_target_fields' => 'dafuncselecttargetfields',
        'project_db_access_function_select_havings' => 'dafuncselecthaving',
        'project_db_access_function_insert_target_fields' => 'dafuncinserttargetfields',
        'project_db_access_function_update_target_fields' => 'dafuncupdatetargetfields',
        'project_db_access_function_update_delete_wheres' => 'dafuncupdatedeletewhere',
        'project_db_access_function_source_output_targets' => 'dafuncSimpleProxySourceOutputTarget',
        'project_custom_proxies' => 'daCustomProxy',
        'project_custom_proxy_steps' => 'daCustomProxyFunc',
        'project_custom_proxy_source_output_targets' => 'daCustomProxySourceOutputTarget',
        'dbtable' => 'dbtable',
        'dbtablecolumns' => 'dbtablecolumns',
        'dataclass' => 'dataclass',
        'dataclassfields' => 'dataclassfields',
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     source_schema_name:string,
 *     table_count:int,
 *     column_count:int,
 *     generated_at:string,
 *     tables:list<array{
 *         name:string,
 *         column_count:int,
 *         columns:list<array{
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int
 *         }>
 *     }>
 * } $reference
 * @return array{
 *     mapped_scope_table_count:int,
 *     mapped_reference_table_count:int,
 *     remaining_reference_table_count:int,
 *     mapped_pairs:list<array{current_table_name:string,legacy_table_name:string}>,
 *     remaining_reference_table_names:list<string>
 * }
 */
function app_mtool_self_host_legacy_scope_summary(array $reference): array
{
    $aliasMap = app_mtool_self_host_legacy_table_alias_map();
    $referenceNames = [];
    foreach ($reference['tables'] as $table) {
        $referenceNames[(string) ($table['name'] ?? '')] = true;
    }

    $mappedPairs = [];
    $mappedLegacyNames = [];
    foreach ($aliasMap as $currentTableName => $legacyTableName) {
        if (!isset($referenceNames[$legacyTableName])) {
            continue;
        }

        $mappedPairs[] = [
            'current_table_name' => $currentTableName,
            'legacy_table_name' => $legacyTableName,
        ];
        $mappedLegacyNames[$legacyTableName] = true;
    }

    usort(
        $mappedPairs,
        static fn (array $left, array $right): int => strcasecmp($left['current_table_name'], $right['current_table_name']),
    );

    $remainingReferenceTableNames = [];
    foreach (array_keys($referenceNames) as $referenceName) {
        if (isset($mappedLegacyNames[$referenceName])) {
            continue;
        }
        $remainingReferenceTableNames[] = $referenceName;
    }

    sort($remainingReferenceTableNames, SORT_NATURAL | SORT_FLAG_CASE);

    return [
        'mapped_scope_table_count' => count($aliasMap),
        'mapped_reference_table_count' => count($mappedPairs),
        'remaining_reference_table_count' => count($remainingReferenceTableNames),
        'mapped_pairs' => $mappedPairs,
        'remaining_reference_table_names' => $remainingReferenceTableNames,
    ];
}
