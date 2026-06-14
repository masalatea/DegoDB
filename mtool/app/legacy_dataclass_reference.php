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
 *         data_class_count:int,
 *         field_count:int,
 *         data_classes:list<array{
 *             project_pid:int,
 *             legacy_data_class_pid:int,
 *             name:string,
 *             store_base_path:string,
 *             is_autoload:string,
 *             inherit_parent_data_class_name:string,
 *             last_modified_dt:string
 *         }>,
 *         fields:list<array{
 *             project_pid:int,
 *             legacy_data_class_pid:int,
 *             legacy_field_pid:int,
 *             data_class_name:string,
 *             name:string,
 *             datatype:string,
 *             field_list_order:int,
 *             ref_data_class_name:string,
 *             ref_data_class_field_name:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_load_legacy_dataclass_reference(string $projectKey): array
{
    $referencePath = app_legacy_dataclass_reference_path($projectKey);
    if ($referencePath === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'この project に対応する legacy dataclass reference はまだありません。',
        ];
    }

    if (!is_file($referencePath)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dataclass reference が見つかりません: ' . $referencePath,
        ];
    }

    $contents = file_get_contents($referencePath);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dataclass reference を読み込めません。',
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dataclass reference の JSON が不正です。',
        ];
    }

    $dataClasses = $decoded['data_classes'] ?? null;
    $fields = $decoded['fields'] ?? null;
    if (!is_array($dataClasses) || !is_array($fields)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy dataclass reference の data_classes / fields が不正です。',
        ];
    }

    $normalizedDataClasses = [];
    foreach ($dataClasses as $dataClass) {
        if (!is_array($dataClass)) {
            continue;
        }

        $legacyDataClassPid = (int) ($dataClass['legacy_data_class_pid'] ?? 0);
        $name = trim((string) ($dataClass['name'] ?? ''));
        if ($legacyDataClassPid <= 0 || $name === '') {
            continue;
        }

        $normalizedDataClasses[] = [
            'project_pid' => (int) ($dataClass['project_pid'] ?? 0),
            'legacy_data_class_pid' => $legacyDataClassPid,
            'name' => $name,
            'store_base_path' => (string) ($dataClass['store_base_path'] ?? ''),
            'is_autoload' => trim((string) ($dataClass['is_autoload'] ?? '0')) === '1' ? '1' : '0',
            'inherit_parent_data_class_name' => (string) ($dataClass['inherit_parent_data_class_name'] ?? ''),
            'last_modified_dt' => (string) ($dataClass['last_modified_dt'] ?? ''),
        ];
    }

    $normalizedFields = [];
    foreach ($fields as $field) {
        if (!is_array($field)) {
            continue;
        }

        $legacyFieldPid = (int) ($field['legacy_field_pid'] ?? 0);
        $legacyDataClassPid = (int) ($field['legacy_data_class_pid'] ?? 0);
        $dataClassName = trim((string) ($field['data_class_name'] ?? ''));
        $name = trim((string) ($field['name'] ?? ''));
        if ($legacyFieldPid <= 0 || $legacyDataClassPid <= 0 || $dataClassName === '' || $name === '') {
            continue;
        }

        $normalizedFields[] = [
            'project_pid' => (int) ($field['project_pid'] ?? 0),
            'legacy_data_class_pid' => $legacyDataClassPid,
            'legacy_field_pid' => $legacyFieldPid,
            'data_class_name' => $dataClassName,
            'name' => $name,
            'datatype' => (string) ($field['datatype'] ?? ''),
            'field_list_order' => (int) ($field['field_list_order'] ?? 0),
            'ref_data_class_name' => (string) ($field['ref_data_class_name'] ?? ''),
            'ref_data_class_field_name' => (string) ($field['ref_data_class_field_name'] ?? ''),
        ];
    }

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? ''),
            'project_pid' => (int) ($decoded['project_pid'] ?? 0),
            'source_dump_path' => (string) ($decoded['source_dump_path'] ?? ''),
            'generated_at' => (string) ($decoded['generated_at'] ?? ''),
            'data_class_count' => (int) ($decoded['data_class_count'] ?? count($normalizedDataClasses)),
            'field_count' => (int) ($decoded['field_count'] ?? count($normalizedFields)),
            'data_classes' => $normalizedDataClasses,
            'fields' => $normalizedFields,
        ],
        'error' => '',
    ];
}

function app_legacy_dataclass_reference_path(string $projectKey): string
{
    $normalizedProjectKey = strtoupper(trim($projectKey));
    if ($normalizedProjectKey === 'MTOOL') {
        return dirname(__DIR__) . '/reference/mtool-legacy-dataclass-catalog.json';
    }

    return '';
}

/**
 * @return array<string,string>
 */
function app_legacy_dataclass_reference_current_data_class_pid_map(string $projectKey): array
{
    $reference = app_load_legacy_dataclass_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $map = [];
    foreach ($reference['item']['data_classes'] as $dataClass) {
        $map[(string) $dataClass['legacy_data_class_pid']] = $dataClass['name'];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array<string,array{
 *     data_class_name:string,
 *     field_name:string
 * }>
 */
function app_legacy_dataclass_reference_current_field_pid_map(string $projectKey): array
{
    $reference = app_load_legacy_dataclass_reference($projectKey);
    if (!$reference['ok'] || $reference['item'] === null) {
        return [];
    }

    $map = [];
    foreach ($reference['item']['fields'] as $field) {
        $map[(string) $field['legacy_field_pid']] = [
            'data_class_name' => $field['data_class_name'],
            'field_name' => $field['name'],
        ];
    }

    ksort($map, SORT_NATURAL);

    return $map;
}
