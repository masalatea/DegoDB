<?php

declare(strict_types=1);

require_once __DIR__ . '/legacy_table_schema_reference.php';

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_key:string,
 *         project_pid:int,
 *         source_dump_path:string,
 *         generated_at:string,
 *         table_count:int,
 *         tables:list<array{
 *             project_pid:int,
 *             legacy_table_pid:int,
 *             name:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_load_legacy_dbtable_reference(string $projectKey): array
{
    $referencePath = app_legacy_dbtable_reference_path($projectKey);
    if ($referencePath === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'この project に対応する legacy dbtable reference はまだありません。',
        ];
    }

    if (!is_file($referencePath)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dbtable reference が見つかりません: ' . $referencePath,
        ];
    }

    $contents = file_get_contents($referencePath);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dbtable reference を読み込めません。',
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dbtable reference の JSON が不正です。',
        ];
    }

    $tables = $decoded['tables'] ?? null;
    if (!is_array($tables)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dbtable reference の tables が不正です。',
        ];
    }

    $normalizedTables = [];
    foreach ($tables as $table) {
        if (!is_array($table)) {
            continue;
        }

        $legacyTablePid = (int) ($table['legacy_table_pid'] ?? 0);
        $tableName = trim((string) ($table['name'] ?? ''));
        if ($legacyTablePid <= 0 || $tableName === '') {
            continue;
        }

        $normalizedTables[] = [
            'project_pid' => (int) ($table['project_pid'] ?? 0),
            'legacy_table_pid' => $legacyTablePid,
            'name' => $tableName,
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'project_pid' => (int) ($decoded['project_pid'] ?? 0),
            'source_dump_path' => (string) ($decoded['source_dump_path'] ?? ''),
            'generated_at' => (string) ($decoded['generated_at'] ?? ''),
            'table_count' => (int) ($decoded['table_count'] ?? count($normalizedTables)),
            'tables' => $normalizedTables,
        ],
        'error' => '',
    ];
}

function app_legacy_dbtable_reference_path(string $projectKey): string
{
    $normalizedProjectKey = strtoupper(trim($projectKey));
    if ($normalizedProjectKey === 'MTOOL') {
        return dirname(__DIR__) . '/reference/mtool-legacy-dbtable-catalog.json';
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_legacy_dbtable_reference_current_table_pid_map(string $projectKey): array
{
    $reference = app_load_legacy_dbtable_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $legacyToCurrentTableNameMap = [];
    foreach (app_mtool_self_host_legacy_table_alias_map() as $currentTableName => $legacyTableName) {
        $legacyToCurrentTableNameMap[$legacyTableName] = $currentTableName;
    }

    $map = [];
    foreach ($reference['item']['tables'] as $table) {
        $legacyTableName = $table['name'];
        if (!array_key_exists($legacyTableName, $legacyToCurrentTableNameMap)) {
            continue;
        }

        $map[(string) $table['legacy_table_pid']] = $legacyToCurrentTableNameMap[$legacyTableName];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}
