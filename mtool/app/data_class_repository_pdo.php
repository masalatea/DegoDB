<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/generated_name.php';

/**
 * @param array{
 *     config_db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         project_pid:string,
 *         pid:string,
 *         name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         inherit_parent_data_class_name:string,
 *         last_modified_dt:string,
 *         field_count:int,
 *         fields:list<array{
 *             project_pid:string,
 *             dataclass_pid:string,
 *             pid:string,
 *             name:string,
 *             datatype:string,
 *             field_list_order:int,
 *             ref_data_class_name:string,
 *             ref_data_class_field_name:string
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_data_class_metadata_snapshot(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_db_config(app_database_config($app, 'config_db'));
        $lastModifiedSelect = app_sql_datetime_select_expr($dialect, 'd.LastModifiedDT', 'last_modified_dt');
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'SELECT
                d.ProjectPID AS project_pid,
                d.PID AS dataclass_pid,
                d.name AS dataclass_name,
                d.physical_name AS dataclass_physical_name,
                d.StoreBasePath AS store_base_path,
                d.IsAutoload AS is_autoload,
                d.InheritParentDataClassName AS inherit_parent_data_class_name,
                ' . $lastModifiedSelect . ',
                f.ProjectPID AS field_project_pid,
                f.dataclassPID AS field_dataclass_pid,
                f.PID AS field_pid,
                f.name AS field_name,
                f.physical_name AS field_physical_name,
                f.datatype AS field_datatype,
                f.FieldListOrder AS field_list_order,
                f.RefDataClassName AS ref_data_class_name,
                f.RefDataClassFieldName AS ref_data_class_field_name
            FROM dataclass AS d
            LEFT JOIN dataclassfields AS f
                ON f.ProjectPID = d.ProjectPID
               AND f.dataclassPID = d.PID
            WHERE d.ProjectPID = :project_id
            ORDER BY d.name, f.FieldListOrder, f.PID'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        $indexByDataClassName = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $dataClassName = (string) ($row['dataclass_name'] ?? '');
            if ($dataClassName === '') {
                continue;
            }
            $dataClassPhysicalName = (string) ($row['dataclass_physical_name'] ?? '');
            if ($dataClassPhysicalName === '') {
                $dataClassPhysicalName = $dataClassName;
            }

            if (!array_key_exists($dataClassName, $indexByDataClassName)) {
                $dataClassNameMap = app_generated_name_map_for_physical_name($dataClassPhysicalName, 'class');
                $indexByDataClassName[$dataClassName] = count($items);
                $items[] = [
                    'project_pid' => (string) ($row['project_pid'] ?? ''),
                    'pid' => (string) ($row['dataclass_pid'] ?? ''),
                    'name' => $dataClassName,
                    'physical_name' => $dataClassNameMap['physical_name'],
                    'logical_name' => $dataClassNameMap['logical_name'],
                    'generated_name' => $dataClassNameMap['generated_name'],
                    'store_base_path' => (string) ($row['store_base_path'] ?? ''),
                    'is_autoload' => ((int) ($row['is_autoload'] ?? 0)) === 1 ? '1' : '0',
                    'inherit_parent_data_class_name' => (string) ($row['inherit_parent_data_class_name'] ?? ''),
                    'last_modified_dt' => (string) ($row['last_modified_dt'] ?? ''),
                    'field_count' => 0,
                    'fields' => [],
                ];
            }

            $dataClassIndex = $indexByDataClassName[$dataClassName];
            $fieldPid = (string) ($row['field_pid'] ?? '');
            if ($fieldPid === '') {
                continue;
            }

            $fieldName = (string) ($row['field_name'] ?? '');
            $fieldPhysicalName = (string) ($row['field_physical_name'] ?? '');
            if ($fieldPhysicalName === '') {
                $fieldPhysicalName = $fieldName;
            }
            $fieldNameMap = app_generated_name_map_for_physical_name($fieldPhysicalName, 'php-property');
            $items[$dataClassIndex]['fields'][] = [
                'project_pid' => (string) ($row['field_project_pid'] ?? ''),
                'dataclass_pid' => (string) ($row['field_dataclass_pid'] ?? ''),
                'pid' => $fieldPid,
                'name' => $fieldName,
                'physical_name' => $fieldNameMap['physical_name'],
                'logical_name' => $fieldNameMap['logical_name'],
                'generated_name' => $fieldNameMap['generated_name'],
                'datatype' => (string) ($row['field_datatype'] ?? ''),
                'field_list_order' => (int) ($row['field_list_order'] ?? 0),
                'ref_data_class_name' => (string) ($row['ref_data_class_name'] ?? ''),
                'ref_data_class_field_name' => (string) ($row['ref_data_class_field_name'] ?? ''),
            ];
            $items[$dataClassIndex]['field_count'] = count($items[$dataClassIndex]['fields']);
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     config_db:array{
 *         dsn:string,
 *         user:string,
 *         password:string
 *     }
 * } $app
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         pid:string,
 *         name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         inherit_parent_data_class_name:string,
 *         last_modified_dt:string,
 *         field_count:int,
 *         fields:list<array{
 *             project_pid:string,
 *             dataclass_pid:string,
 *             pid:string,
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
function app_pdo_fetch_data_class_metadata_item(array $app, string $projectKey, string $dataClassName): array
{
    $snapshot = app_pdo_fetch_data_class_metadata_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $snapshot['error'],
        ];
    }

    foreach ($snapshot['items'] as $item) {
        if (strcasecmp($item['name'], $dataClassName) === 0) {
            return [
                'ok' => true,
                'item' => $item,
                'error' => '',
            ];
        }
    }

    return [
        'ok' => true,
        'item' => null,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         dataclass_pid:string,
 *         pid:string,
 *         name:string,
 *         datatype:string,
 *         field_list_order:int,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_data_class_metadata_field_item(
    array $app,
    string $projectKey,
    string $dataClassName,
    string $fieldName,
): array {
    $dataClassItem = app_pdo_fetch_data_class_metadata_item($app, $projectKey, $dataClassName);
    if (!$dataClassItem['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $dataClassItem['error'],
        ];
    }

    if ($dataClassItem['item'] === null) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    foreach ($dataClassItem['item']['fields'] as $field) {
        if (strcasecmp($field['name'], $fieldName) === 0) {
            return [
                'ok' => true,
                'item' => $field,
                'error' => '',
            ];
        }
    }

    return [
        'ok' => true,
        'item' => null,
        'error' => '',
    ];
}

/**
 * @param array{
 *     name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     inherit_parent_data_class_name:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         pid:string,
 *         name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         inherit_parent_data_class_name:string,
 *         last_modified_dt:string,
 *         field_count:int,
 *         fields:list<array{
 *             project_pid:string,
 *             dataclass_pid:string,
 *             pid:string,
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
function app_pdo_create_data_class_metadata_item(array $app, string $projectKey, array $input): array
{
    try {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('data class name が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
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
        $statement->execute([
            ':project_id' => $projectId,
            ':name' => $name,
            ':physical_name' => trim((string) ($input['physical_name'] ?? $name)),
            ':store_base_path' => trim((string) ($input['store_base_path'] ?? '')),
            ':is_autoload' => trim((string) ($input['is_autoload'] ?? '0')) === '1' ? 1 : 0,
            ':inherit_parent_data_class_name' => trim((string) ($input['inherit_parent_data_class_name'] ?? '')),
        ]);

        return app_pdo_fetch_data_class_metadata_item($app, $projectKey, $name);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     inherit_parent_data_class_name:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         pid:string,
 *         name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         inherit_parent_data_class_name:string,
 *         last_modified_dt:string,
 *         field_count:int,
 *         fields:list<array{
 *             project_pid:string,
 *             dataclass_pid:string,
 *             pid:string,
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
function app_pdo_update_data_class_metadata_item(
    array $app,
    string $projectKey,
    string $dataClassPid,
    array $input,
): array {
    try {
        $normalizedDataClassPid = (int) trim($dataClassPid);
        $name = trim((string) ($input['name'] ?? ''));
        if ($normalizedDataClassPid <= 0) {
            throw new RuntimeException('data class PID の形式が不正です。');
        }
        if ($name === '') {
            throw new RuntimeException('data class name が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        app_data_class_pdo_assert_item_exists($pdo, $projectId, $normalizedDataClassPid);

        $statement = $pdo->prepare(
            'UPDATE dataclass
             SET
                name = :name,
                physical_name = :physical_name,
                StoreBasePath = :store_base_path,
                IsAutoload = :is_autoload,
                InheritParentDataClassName = :inherit_parent_data_class_name,
                LastModifiedDT = CURRENT_TIMESTAMP
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $statement->execute([
            ':name' => $name,
            ':physical_name' => trim((string) ($input['physical_name'] ?? $name)),
            ':store_base_path' => trim((string) ($input['store_base_path'] ?? '')),
            ':is_autoload' => trim((string) ($input['is_autoload'] ?? '0')) === '1' ? 1 : 0,
            ':inherit_parent_data_class_name' => trim((string) ($input['inherit_parent_data_class_name'] ?? '')),
            ':pid' => $normalizedDataClassPid,
            ':project_id' => $projectId,
        ]);

        return app_pdo_fetch_data_class_metadata_item($app, $projectKey, $name);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,error:string}
 */
function app_pdo_delete_data_class_metadata_item(array $app, string $projectKey, string $dataClassPid): array
{
    try {
        $normalizedDataClassPid = (int) trim($dataClassPid);
        if ($normalizedDataClassPid <= 0) {
            throw new RuntimeException('data class PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'DELETE FROM dataclass
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $statement->execute([
            ':pid' => $normalizedDataClassPid,
            ':project_id' => $projectId,
        ]);

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     name:string,
 *     datatype:string,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         dataclass_pid:string,
 *         pid:string,
 *         name:string,
 *         datatype:string,
 *         field_list_order:int,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_create_data_class_metadata_field(
    array $app,
    string $projectKey,
    string $dataClassPid,
    array $input,
): array {
    try {
        $normalizedDataClassPid = (int) trim($dataClassPid);
        if ($normalizedDataClassPid <= 0) {
            throw new RuntimeException('data class PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        $dataClassName = app_data_class_pdo_fetch_name_by_pid($pdo, $projectId, $normalizedDataClassPid);
        if ($dataClassName === '') {
            throw new RuntimeException('field 追加対象の data class metadata が見つかりません。');
        }

        $orderStatement = $pdo->prepare(
            'SELECT COALESCE(MAX(FieldListOrder), 0)
             FROM dataclassfields
             WHERE ProjectPID = :project_id
               AND dataclassPID = :dataclass_pid'
        );
        $orderStatement->execute([
            ':project_id' => $projectId,
            ':dataclass_pid' => $normalizedDataClassPid,
        ]);
        $maxOrder = (int) ($orderStatement->fetchColumn() ?: 0);

        $insert = $pdo->prepare(
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
        $insert->execute([
            ':project_id' => $projectId,
            ':dataclass_pid' => $normalizedDataClassPid,
            ':name' => trim((string) ($input['name'] ?? '')),
            ':physical_name' => trim((string) ($input['physical_name'] ?? $input['name'] ?? '')),
            ':datatype' => trim((string) ($input['datatype'] ?? '')),
            ':field_list_order' => $maxOrder + 1,
            ':ref_data_class_name' => trim((string) ($input['ref_data_class_name'] ?? '')),
            ':ref_data_class_field_name' => trim((string) ($input['ref_data_class_field_name'] ?? '')),
        ]);

        app_data_class_pdo_touch_last_modified($pdo, $projectId, $normalizedDataClassPid);

        return app_pdo_fetch_data_class_metadata_field_item(
            $app,
            $projectKey,
            $dataClassName,
            trim((string) ($input['name'] ?? '')),
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     name:string,
 *     datatype:string,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         dataclass_pid:string,
 *         pid:string,
 *         name:string,
 *         datatype:string,
 *         field_list_order:int,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_update_data_class_metadata_field(
    array $app,
    string $projectKey,
    string $fieldPid,
    array $input,
): array {
    try {
        $normalizedFieldPid = (int) trim($fieldPid);
        if ($normalizedFieldPid <= 0) {
            throw new RuntimeException('data class field PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        $row = app_data_class_pdo_fetch_field_parent_row($pdo, $projectId, $normalizedFieldPid);
        if ($row === null) {
            throw new RuntimeException('更新対象の data class field metadata が見つかりません。');
        }

        $update = $pdo->prepare(
            'UPDATE dataclassfields
             SET
                name = :name,
                physical_name = :physical_name,
                datatype = :datatype,
                RefDataClassName = :ref_data_class_name,
                RefDataClassFieldName = :ref_data_class_field_name
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $update->execute([
            ':name' => trim((string) ($input['name'] ?? '')),
            ':physical_name' => trim((string) ($input['physical_name'] ?? $input['name'] ?? '')),
            ':datatype' => trim((string) ($input['datatype'] ?? '')),
            ':ref_data_class_name' => trim((string) ($input['ref_data_class_name'] ?? '')),
            ':ref_data_class_field_name' => trim((string) ($input['ref_data_class_field_name'] ?? '')),
            ':pid' => $normalizedFieldPid,
            ':project_id' => $projectId,
        ]);

        app_data_class_pdo_touch_last_modified($pdo, $projectId, (int) $row['dataclass_pid']);

        return app_pdo_fetch_data_class_metadata_field_item(
            $app,
            $projectKey,
            (string) ($row['data_class_name'] ?? ''),
            trim((string) ($input['name'] ?? '')),
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,error:string}
 */
function app_pdo_delete_data_class_metadata_field(array $app, string $projectKey, string $fieldPid): array
{
    try {
        $normalizedFieldPid = (int) trim($fieldPid);
        if ($normalizedFieldPid <= 0) {
            throw new RuntimeException('data class field PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_data_class_pdo_resolve_project_id($pdo, $projectKey);
        $row = app_data_class_pdo_fetch_field_parent_row($pdo, $projectId, $normalizedFieldPid);
        if ($row === null) {
            throw new RuntimeException('削除対象の data class field metadata が見つかりません。');
        }

        $statement = $pdo->prepare(
            'DELETE FROM dataclassfields
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $statement->execute([
            ':pid' => $normalizedFieldPid,
            ':project_id' => $projectId,
        ]);

        app_data_class_pdo_touch_last_modified($pdo, $projectId, (int) $row['dataclass_pid']);

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

function app_data_class_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM projects
         WHERE project_key = :project_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
    ]);

    $projectId = $statement->fetchColumn();
    if ($projectId === false) {
        throw new RuntimeException('project が見つかりません: ' . $projectKey);
    }

    return (int) $projectId;
}

function app_data_class_pdo_assert_item_exists(PDO $pdo, int $projectId, int $dataClassPid): void
{
    $statement = $pdo->prepare(
        'SELECT PID
         FROM dataclass
         WHERE PID = :pid
           AND ProjectPID = :project_id
         LIMIT 1'
    );
    $statement->execute([
        ':pid' => $dataClassPid,
        ':project_id' => $projectId,
    ]);

    if ($statement->fetchColumn() === false) {
        throw new RuntimeException('更新対象の data class metadata が見つかりません。');
    }
}

function app_data_class_pdo_fetch_name_by_pid(PDO $pdo, int $projectId, int $dataClassPid): string
{
    $statement = $pdo->prepare(
        'SELECT name
         FROM dataclass
         WHERE PID = :pid
           AND ProjectPID = :project_id
         LIMIT 1'
    );
    $statement->execute([
        ':pid' => $dataClassPid,
        ':project_id' => $projectId,
    ]);

    $name = $statement->fetchColumn();

    return is_string($name) ? $name : '';
}

/**
 * @return array{
 *     dataclass_pid:string,
 *     data_class_name:string
 * }|null
 */
function app_data_class_pdo_fetch_field_parent_row(PDO $pdo, int $projectId, int $fieldPid): ?array
{
    $statement = $pdo->prepare(
        'SELECT
            f.dataclassPID AS dataclass_pid,
            d.name AS data_class_name
         FROM dataclassfields AS f
         INNER JOIN dataclass AS d
             ON d.ProjectPID = f.ProjectPID
            AND d.PID = f.dataclassPID
         WHERE f.PID = :pid
           AND f.ProjectPID = :project_id
         LIMIT 1'
    );
    $statement->execute([
        ':pid' => $fieldPid,
        ':project_id' => $projectId,
    ]);

    $row = $statement->fetch();
    if (!is_array($row)) {
        return null;
    }

    return [
        'dataclass_pid' => (string) ($row['dataclass_pid'] ?? ''),
        'data_class_name' => (string) ($row['data_class_name'] ?? ''),
    ];
}

function app_data_class_pdo_touch_last_modified(PDO $pdo, int $projectId, int $dataClassPid): void
{
    $statement = $pdo->prepare(
        'UPDATE dataclass
         SET LastModifiedDT = CURRENT_TIMESTAMP
         WHERE PID = :pid
           AND ProjectPID = :project_id'
    );
    $statement->execute([
        ':pid' => $dataClassPid,
        ':project_id' => $projectId,
    ]);
}
