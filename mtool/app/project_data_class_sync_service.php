<?php

declare(strict_types=1);

require_once __DIR__ . '/data_class_repository.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/table_metadata_repository.php';

function app_project_data_class_sync_preflight(array $app, string $projectKey): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'error' => 'project key の形式が不正です。',
        ];
    }

    $tableSnapshot = app_fetch_table_metadata_snapshot($app, $normalizedProjectKey);
    if (!$tableSnapshot['ok']) {
        return [
            'ok' => false,
            'error' => 'table metadata の読み込みに失敗しました: ' . $tableSnapshot['error'],
        ];
    }

    if ($tableSnapshot['items'] === []) {
        return [
            'ok' => false,
            'error' => '先に /tables/import で canonical table metadata を作成してください。',
        ];
    }

    return [
        'ok' => true,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         table_count:int,
 *         canonical_data_class_count:int,
 *         class_insert_count:int,
 *         class_update_count:int,
 *         class_same_count:int,
 *         field_insert_count:int,
 *         field_update_count:int,
 *         field_same_count:int,
 *         stale_class_count:int,
 *         stale_field_count:int
 *     },
 *     classes:list<array{
 *         name:string,
 *         status:string,
 *         table_field_count:int,
 *         canonical_field_count:int,
 *         field_insert_count:int,
 *         field_update_count:int,
 *         field_same_count:int,
 *         stale_field_count:int
 *     }>,
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_data_class_sync_preview(array $app, string $projectKey): array
{
    $preflight = app_project_data_class_sync_preflight($app, $projectKey);
    if (!$preflight['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_data_class_sync_empty_summary($projectKey),
            'classes' => [],
            'errors' => [$preflight['error']],
            'error' => $preflight['error'],
        ];
    }

    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $tableSnapshot = app_fetch_table_metadata_snapshot($app, $normalizedProjectKey);
    if (!$tableSnapshot['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_data_class_sync_empty_summary($normalizedProjectKey),
            'classes' => [],
            'errors' => [$tableSnapshot['error']],
            'error' => $tableSnapshot['error'],
        ];
    }

    $dataClassSnapshot = app_fetch_data_class_metadata_snapshot($app, $normalizedProjectKey);
    if (!$dataClassSnapshot['ok']) {
        return [
            'ok' => false,
            'summary' => app_project_data_class_sync_empty_summary($normalizedProjectKey),
            'classes' => [],
            'errors' => [$dataClassSnapshot['error']],
            'error' => $dataClassSnapshot['error'],
        ];
    }

    return app_project_data_class_sync_build_plan(
        $normalizedProjectKey,
        $tableSnapshot['items'],
        $dataClassSnapshot['items'],
    );
}

/**
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         table_count:int,
 *         canonical_data_class_count:int,
 *         class_insert_count:int,
 *         class_update_count:int,
 *         class_same_count:int,
 *         field_insert_count:int,
 *         field_update_count:int,
 *         field_same_count:int,
 *         stale_class_count:int,
 *         stale_field_count:int
 *     },
 *     classes:list<array{
 *         name:string,
 *         status:string,
 *         table_field_count:int,
 *         canonical_field_count:int,
 *         field_insert_count:int,
 *         field_update_count:int,
 *         field_same_count:int,
 *         stale_field_count:int
 *     }>,
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_data_class_sync_apply(array $app, string $projectKey): array
{
    $preview = app_project_data_class_sync_preview($app, $projectKey);
    if (!$preview['ok']) {
        return $preview;
    }

    $normalizedProjectKey = $preview['summary']['project_key'];
    $tableSnapshot = app_fetch_table_metadata_snapshot($app, $normalizedProjectKey);
    if (!$tableSnapshot['ok']) {
        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'classes' => $preview['classes'],
            'errors' => [$tableSnapshot['error']],
            'error' => $tableSnapshot['error'],
        ];
    }

    $dataClassSnapshot = app_fetch_data_class_metadata_snapshot($app, $normalizedProjectKey);
    if (!$dataClassSnapshot['ok']) {
        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'classes' => $preview['classes'],
            'errors' => [$dataClassSnapshot['error']],
            'error' => $dataClassSnapshot['error'],
        ];
    }

    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $normalizedProjectKey);
        $dataClassByName = app_project_data_class_sync_data_class_by_name($dataClassSnapshot['items']);

        $pdo->beginTransaction();

        foreach ($tableSnapshot['items'] as $table) {
            $dataClass = $dataClassByName[$table['name']] ?? null;
            $dataClassPid = $dataClass['pid'] ?? '';
            $dataClassPhysicalName = (string) ($table['physical_name'] ?? $table['name']);
            $touched = false;

            if ($dataClassPid === '') {
                $insertClass = $pdo->prepare(
                    'INSERT INTO dataclass (
                        ProjectPID,
                        name,
                        physical_name,
                        StoreBasePath,
                        IsAutoload,
                        InheritParentDataClassName
                    ) VALUES (
                        :project_id,
                        :name,
                        :physical_name,
                        :store_base_path,
                        :is_autoload,
                        :inherit_parent_data_class_name
                    )'
                );
                $insertClass->execute([
                    ':project_id' => $projectId,
                    ':name' => $table['name'],
                    ':physical_name' => $dataClassPhysicalName,
                    ':store_base_path' => '',
                    ':is_autoload' => 1,
                    ':inherit_parent_data_class_name' => '',
                ]);
                $dataClassPid = (string) $pdo->lastInsertId();
                $touched = true;
                $dataClass = [
                    'pid' => $dataClassPid,
                    'fields' => [],
                ];
            } elseif ((string) ($dataClass['physical_name'] ?? $dataClass['name']) !== $dataClassPhysicalName) {
                $updateClass = $pdo->prepare(
                    'UPDATE dataclass
                     SET
                        physical_name = :physical_name,
                        LastModifiedDT = CURRENT_TIMESTAMP
                     WHERE PID = :pid
                       AND ProjectPID = :project_id'
                );
                $updateClass->execute([
                    ':physical_name' => $dataClassPhysicalName,
                    ':pid' => (int) $dataClassPid,
                    ':project_id' => $projectId,
                ]);
                $touched = true;
            }

            $existingFieldsByName = [];
            foreach (($dataClass['fields'] ?? []) as $field) {
                $existingFieldsByName[$field['name']] = $field;
            }

            foreach ($table['columns'] as $column) {
                $existingField = $existingFieldsByName[$column['name']] ?? null;
                $targetDatatype = app_project_data_class_sync_general_datatype($column['datatype']);
                $fieldOrder = (int) $column['column_list_order'];
                if ($existingField === null) {
                    $insertField = $pdo->prepare(
                        'INSERT INTO dataclassfields (
                            ProjectPID,
                            dataclassPID,
                            name,
                            physical_name,
                            datatype,
                            FieldListOrder,
                            RefDataClassName,
                            RefDataClassFieldName
                        ) VALUES (
                            :project_id,
                            :dataclass_pid,
                            :name,
                            :physical_name,
                            :datatype,
                            :field_list_order,
                            :ref_data_class_name,
                            :ref_data_class_field_name
                        )'
                    );
                    $insertField->execute([
                        ':project_id' => $projectId,
                        ':dataclass_pid' => (int) $dataClassPid,
                        ':name' => $column['name'],
                        ':physical_name' => (string) ($column['physical_name'] ?? $column['name']),
                        ':datatype' => $targetDatatype,
                        ':field_list_order' => $fieldOrder,
                        ':ref_data_class_name' => '',
                        ':ref_data_class_field_name' => '',
                    ]);
                    $touched = true;
                    continue;
                }

                if (
                    $existingField['datatype'] !== $targetDatatype
                    || $existingField['field_list_order'] !== $fieldOrder
                    || (string) ($existingField['physical_name'] ?? $existingField['name']) !== (string) ($column['physical_name'] ?? $column['name'])
                ) {
                    $updateField = $pdo->prepare(
                        'UPDATE dataclassfields
                         SET
                            physical_name = :physical_name,
                            datatype = :datatype,
                            FieldListOrder = :field_list_order,
                            RefDataClassName = :ref_data_class_name,
                            RefDataClassFieldName = :ref_data_class_field_name
                         WHERE PID = :pid
                           AND ProjectPID = :project_id'
                    );
                    $updateField->execute([
                        ':physical_name' => (string) ($column['physical_name'] ?? $column['name']),
                        ':datatype' => $targetDatatype,
                        ':field_list_order' => $fieldOrder,
                        ':ref_data_class_name' => $existingField['ref_data_class_name'],
                        ':ref_data_class_field_name' => $existingField['ref_data_class_field_name'],
                        ':pid' => (int) $existingField['pid'],
                        ':project_id' => $projectId,
                    ]);
                    $touched = true;
                }
            }

            if ($touched) {
                $touchClass = $pdo->prepare(
                    'UPDATE dataclass
                     SET LastModifiedDT = CURRENT_TIMESTAMP
                     WHERE PID = :pid
                       AND ProjectPID = :project_id'
                );
                $touchClass->execute([
                    ':pid' => (int) $dataClassPid,
                    ':project_id' => $projectId,
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $throwable) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'summary' => $preview['summary'],
            'classes' => $preview['classes'],
            'errors' => [$throwable->getMessage()],
            'error' => $throwable->getMessage(),
        ];
    }

    return [
        'ok' => true,
        'summary' => $preview['summary'],
        'classes' => $preview['classes'],
        'errors' => [],
        'error' => '',
    ];
}

function app_project_data_class_sync_build_plan(
    string $projectKey,
    array $tables,
    array $dataClasses,
): array {
    $summary = app_project_data_class_sync_empty_summary($projectKey);
    $summary['table_count'] = count($tables);
    $summary['canonical_data_class_count'] = count($dataClasses);
    $dataClassByName = app_project_data_class_sync_data_class_by_name($dataClasses);
    $tableNames = [];
    $classes = [];

    foreach ($tables as $table) {
        $tableNames[$table['name']] = true;
        $existingClass = $dataClassByName[$table['name']] ?? null;
        $existingFieldsByName = [];
        if ($existingClass !== null) {
            foreach ($existingClass['fields'] as $field) {
                $existingFieldsByName[$field['name']] = $field;
            }
        }

        $fieldInsertCount = 0;
        $fieldUpdateCount = 0;
        $fieldSameCount = 0;
        $staleFieldCount = 0;

        foreach ($table['columns'] as $column) {
            $existingField = $existingFieldsByName[$column['name']] ?? null;
            $targetDatatype = app_project_data_class_sync_general_datatype($column['datatype']);
            $targetOrder = (int) $column['column_list_order'];

            if ($existingField === null) {
                $fieldInsertCount++;
                continue;
            }

            if ($existingField['datatype'] === $targetDatatype && $existingField['field_list_order'] === $targetOrder) {
                $fieldSameCount++;
            } else {
                $fieldUpdateCount++;
            }
        }

        if ($existingClass !== null) {
            foreach ($existingClass['fields'] as $existingField) {
                if (!app_project_data_class_sync_table_has_column($table, $existingField['name'])) {
                    $staleFieldCount++;
                }
            }
        }

        $status = 'same';
        if ($existingClass === null) {
            $status = 'new';
            $summary['class_insert_count']++;
        } elseif ($fieldInsertCount > 0 || $fieldUpdateCount > 0) {
            $status = 'changed';
            $summary['class_update_count']++;
        } else {
            $summary['class_same_count']++;
        }

        $summary['field_insert_count'] += $fieldInsertCount;
        $summary['field_update_count'] += $fieldUpdateCount;
        $summary['field_same_count'] += $fieldSameCount;
        $summary['stale_field_count'] += $staleFieldCount;

        $classes[] = [
            'name' => $table['name'],
            'status' => $status,
            'table_field_count' => count($table['columns']),
            'canonical_field_count' => $existingClass !== null ? count($existingClass['fields']) : 0,
            'field_insert_count' => $fieldInsertCount,
            'field_update_count' => $fieldUpdateCount,
            'field_same_count' => $fieldSameCount,
            'stale_field_count' => $staleFieldCount,
        ];
    }

    foreach ($dataClasses as $dataClass) {
        if (array_key_exists($dataClass['name'], $tableNames)) {
            continue;
        }

        $summary['stale_class_count']++;
    }

    usort(
        $classes,
        static fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']),
    );

    return [
        'ok' => true,
        'summary' => $summary,
        'classes' => $classes,
        'errors' => [],
        'error' => '',
    ];
}

function app_project_data_class_sync_empty_summary(string $projectKey): array
{
    return [
        'project_key' => app_normalize_project_key($projectKey),
        'table_count' => 0,
        'canonical_data_class_count' => 0,
        'class_insert_count' => 0,
        'class_update_count' => 0,
        'class_same_count' => 0,
        'field_insert_count' => 0,
        'field_update_count' => 0,
        'field_same_count' => 0,
        'stale_class_count' => 0,
        'stale_field_count' => 0,
    ];
}

function app_project_data_class_sync_data_class_by_name(array $dataClasses): array
{
    $result = [];
    foreach ($dataClasses as $dataClass) {
        $result[$dataClass['name']] = $dataClass;
    }

    return $result;
}

function app_project_data_class_sync_table_has_column(array $table, string $columnName): bool
{
    foreach ($table['columns'] as $column) {
        if ($column['name'] === $columnName) {
            return true;
        }
    }

    return false;
}

function app_project_data_class_sync_general_datatype(string $databaseDatatype): string
{
    $normalized = strtolower(trim($databaseDatatype));
    if ($normalized === '') {
        return 'object';
    }

    return match (true) {
        preg_match('/^tinyint\(1\)$/', $normalized) === 1 => 'bool',
        preg_match('/^(bigint|int|smallint|mediumint|tinyint)/', $normalized) === 1 => 'int',
        preg_match('/^(decimal|numeric|double|float|real)/', $normalized) === 1 => 'float',
        preg_match('/^(date)$/', $normalized) === 1 => 'date',
        preg_match('/^(datetime|timestamp)/', $normalized) === 1 => 'datetime',
        preg_match('/^(time)/', $normalized) === 1 => 'time',
        preg_match('/^(char|varchar|text|tinytext|mediumtext|longtext|enum|set)/', $normalized) === 1 => 'string',
        preg_match('/^(json)/', $normalized) === 1 => 'object',
        preg_match('/^(blob|tinyblob|mediumblob|longblob|binary|varbinary)/', $normalized) === 1 => 'blob',
        default => 'object',
    };
}
