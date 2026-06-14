<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

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
 *         column_count:int,
 *         columns:list<array{
 *             project_pid:string,
 *             dbtable_pid:string,
 *             pid:string,
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int,
 *             memo:string
 *         }>
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_table_metadata_snapshot(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'SELECT
                t.ProjectPID AS project_pid,
                t.PID AS table_pid,
                t.name AS table_name,
                c.ProjectPID AS column_project_pid,
                c.dbtablePID AS column_table_pid,
                c.PID AS column_pid,
                c.name AS column_name,
                c.datatype AS column_datatype,
                c.IsNull AS column_is_null,
                c.IsKey AS column_is_key,
                c.IsDefault AS column_is_default,
                c.Extra AS column_extra,
                c.ColumnListOrder AS column_list_order,
                c.memo AS column_memo
            FROM dbtable AS t
            LEFT JOIN dbtablecolumns AS c
                ON c.ProjectPID = t.ProjectPID
               AND c.dbtablePID = t.PID
            WHERE t.ProjectPID = :project_id
            ORDER BY t.name, c.ColumnListOrder, c.PID'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);

        $rows = $statement->fetchAll();
        $items = [];
        $indexByTableName = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $tableName = (string) ($row['table_name'] ?? '');
            if ($tableName === '') {
                continue;
            }

            if (!array_key_exists($tableName, $indexByTableName)) {
                $indexByTableName[$tableName] = count($items);
                $items[] = [
                    'project_pid' => (string) ($row['project_pid'] ?? ''),
                    'pid' => (string) ($row['table_pid'] ?? ''),
                    'name' => $tableName,
                    'column_count' => 0,
                    'columns' => [],
                ];
            }

            $tableIndex = $indexByTableName[$tableName];
            $columnPid = (string) ($row['column_pid'] ?? '');
            if ($columnPid === '') {
                continue;
            }

            $items[$tableIndex]['columns'][] = [
                'project_pid' => (string) ($row['column_project_pid'] ?? ''),
                'dbtable_pid' => (string) ($row['column_table_pid'] ?? ''),
                'pid' => $columnPid,
                'name' => (string) ($row['column_name'] ?? ''),
                'datatype' => (string) ($row['column_datatype'] ?? ''),
                'is_null' => (string) ($row['column_is_null'] ?? ''),
                'is_key' => (string) ($row['column_is_key'] ?? ''),
                'is_default' => (string) ($row['column_is_default'] ?? ''),
                'extra' => (string) ($row['column_extra'] ?? ''),
                'column_list_order' => (int) ($row['column_list_order'] ?? 0),
                'memo' => (string) ($row['column_memo'] ?? ''),
            ];
            $items[$tableIndex]['column_count'] = count($items[$tableIndex]['columns']);
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
 *         column_count:int,
 *         columns:list<array{
 *             project_pid:string,
 *             dbtable_pid:string,
 *             pid:string,
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int,
 *             memo:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_table_metadata_item(array $app, string $projectKey, string $tableName): array
{
    $snapshot = app_pdo_fetch_table_metadata_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $snapshot['error'],
        ];
    }

    foreach ($snapshot['items'] as $item) {
        if (strcasecmp($item['name'], $tableName) === 0) {
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
 *         dbtable_pid:string,
 *         pid:string,
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int,
 *         memo:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_table_metadata_column_item(
    array $app,
    string $projectKey,
    string $tableName,
    string $columnName,
): array {
    $tableItem = app_pdo_fetch_table_metadata_item($app, $projectKey, $tableName);
    if (!$tableItem['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $tableItem['error'],
        ];
    }

    if ($tableItem['item'] === null) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    foreach ($tableItem['item']['columns'] as $column) {
        if (strcasecmp($column['name'], $columnName) === 0) {
            return [
                'ok' => true,
                'item' => $column,
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
 *         pid:string,
 *         name:string,
 *         column_count:int,
 *         columns:list<array{
 *             project_pid:string,
 *             dbtable_pid:string,
 *             pid:string,
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int,
 *             memo:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_create_table_metadata_item(array $app, string $projectKey, string $tableName): array
{
    try {
        $normalizedTableName = trim($tableName);
        if ($normalizedTableName === '') {
            throw new RuntimeException('table name が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'INSERT INTO dbtable (ProjectPID, name)
             VALUES (:project_id, :name)'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':name' => $normalizedTableName,
        ]);

        return app_pdo_fetch_table_metadata_item($app, $projectKey, $normalizedTableName);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         pid:string,
 *         name:string,
 *         column_count:int,
 *         columns:list<array{
 *             project_pid:string,
 *             dbtable_pid:string,
 *             pid:string,
 *             name:string,
 *             datatype:string,
 *             is_null:string,
 *             is_key:string,
 *             is_default:string,
 *             extra:string,
 *             column_list_order:int,
 *             memo:string
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_update_table_metadata_item(
    array $app,
    string $projectKey,
    string $tablePid,
    string $tableName,
): array {
    try {
        $normalizedTableName = trim($tableName);
        $normalizedTablePid = (int) trim($tablePid);
        if ($normalizedTableName === '') {
            throw new RuntimeException('table name が空です。');
        }
        if ($normalizedTablePid <= 0) {
            throw new RuntimeException('table PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $check = $pdo->prepare(
            'SELECT PID
             FROM dbtable
             WHERE PID = :pid
               AND ProjectPID = :project_id
             LIMIT 1'
        );
        $check->execute([
            ':pid' => $normalizedTablePid,
            ':project_id' => $projectId,
        ]);
        if ($check->fetchColumn() === false) {
            throw new RuntimeException('更新対象の table metadata が見つかりません。');
        }

        $statement = $pdo->prepare(
            'UPDATE dbtable
             SET name = :name
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $statement->execute([
            ':name' => $normalizedTableName,
            ':pid' => $normalizedTablePid,
            ':project_id' => $projectId,
        ]);

        return app_pdo_fetch_table_metadata_item($app, $projectKey, $normalizedTableName);
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
function app_pdo_delete_table_metadata_item(array $app, string $projectKey, string $tablePid): array
{
    try {
        $normalizedTablePid = (int) trim($tablePid);
        if ($normalizedTablePid <= 0) {
            throw new RuntimeException('table PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'DELETE FROM dbtable
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $statement->execute([
            ':pid' => $normalizedTablePid,
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
 *     is_null:string,
 *     is_key:string,
 *     is_default:string,
 *     extra:string,
 *     memo:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         dbtable_pid:string,
 *         pid:string,
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int,
 *         memo:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_create_table_metadata_column(
    array $app,
    string $projectKey,
    string $tablePid,
    array $input,
): array {
    try {
        $normalizedTablePid = (int) trim($tablePid);
        if ($normalizedTablePid <= 0) {
            throw new RuntimeException('table PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $tableStatement = $pdo->prepare(
            'SELECT name
             FROM dbtable
             WHERE PID = :pid
               AND ProjectPID = :project_id
             LIMIT 1'
        );
        $tableStatement->execute([
            ':pid' => $normalizedTablePid,
            ':project_id' => $projectId,
        ]);
        $tableName = $tableStatement->fetchColumn();
        if ($tableName === false || !is_string($tableName) || $tableName === '') {
            throw new RuntimeException('column 追加対象の table metadata が見つかりません。');
        }

        $orderStatement = $pdo->prepare(
            'SELECT COALESCE(MAX(ColumnListOrder), 0)
             FROM dbtablecolumns
             WHERE ProjectPID = :project_id
               AND dbtablePID = :dbtable_pid'
        );
        $orderStatement->execute([
            ':project_id' => $projectId,
            ':dbtable_pid' => $normalizedTablePid,
        ]);
        $maxOrder = (int) ($orderStatement->fetchColumn() ?: 0);

        $insert = $pdo->prepare(
            'INSERT INTO dbtablecolumns (
                ProjectPID,
                dbtablePID,
                name,
                datatype,
                IsNull,
                IsKey,
                IsDefault,
                Extra,
                ColumnListOrder,
                memo
            ) VALUES (
                :project_id,
                :dbtable_pid,
                :name,
                :datatype,
                :is_null,
                :is_key,
                :is_default,
                :extra,
                :column_list_order,
                :memo
            )'
        );
        $insert->execute([
            ':project_id' => $projectId,
            ':dbtable_pid' => $normalizedTablePid,
            ':name' => trim((string) ($input['name'] ?? '')),
            ':datatype' => trim((string) ($input['datatype'] ?? '')),
            ':is_null' => trim((string) ($input['is_null'] ?? '')),
            ':is_key' => trim((string) ($input['is_key'] ?? '')),
            ':is_default' => trim((string) ($input['is_default'] ?? '')),
            ':extra' => trim((string) ($input['extra'] ?? '')),
            ':column_list_order' => $maxOrder + 1,
            ':memo' => trim((string) ($input['memo'] ?? '')),
        ]);

        return app_pdo_fetch_table_metadata_column_item(
            $app,
            $projectKey,
            $tableName,
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
 *     is_null:string,
 *     is_key:string,
 *     is_default:string,
 *     extra:string,
 *     memo:string
 * } $input
 * @return array{
 *     ok:bool,
 *     item:array{
 *         project_pid:string,
 *         dbtable_pid:string,
 *         pid:string,
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         column_list_order:int,
 *         memo:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_update_table_metadata_column(
    array $app,
    string $projectKey,
    string $columnPid,
    array $input,
): array {
    try {
        $normalizedColumnPid = (int) trim($columnPid);
        if ($normalizedColumnPid <= 0) {
            throw new RuntimeException('column PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $columnStatement = $pdo->prepare(
            'SELECT
                c.dbtablePID AS dbtable_pid,
                t.name AS table_name,
                c.ColumnListOrder AS column_list_order
             FROM dbtablecolumns AS c
             INNER JOIN dbtable AS t
                 ON t.ProjectPID = c.ProjectPID
                AND t.PID = c.dbtablePID
             WHERE c.PID = :pid
               AND c.ProjectPID = :project_id
             LIMIT 1'
        );
        $columnStatement->execute([
            ':pid' => $normalizedColumnPid,
            ':project_id' => $projectId,
        ]);
        $row = $columnStatement->fetch();
        if (!is_array($row)) {
            throw new RuntimeException('更新対象の column metadata が見つかりません。');
        }

        $update = $pdo->prepare(
            'UPDATE dbtablecolumns
             SET
                name = :name,
                datatype = :datatype,
                IsNull = :is_null,
                IsKey = :is_key,
                IsDefault = :is_default,
                Extra = :extra,
                memo = :memo
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $update->execute([
            ':name' => trim((string) ($input['name'] ?? '')),
            ':datatype' => trim((string) ($input['datatype'] ?? '')),
            ':is_null' => trim((string) ($input['is_null'] ?? '')),
            ':is_key' => trim((string) ($input['is_key'] ?? '')),
            ':is_default' => trim((string) ($input['is_default'] ?? '')),
            ':extra' => trim((string) ($input['extra'] ?? '')),
            ':memo' => trim((string) ($input['memo'] ?? '')),
            ':pid' => $normalizedColumnPid,
            ':project_id' => $projectId,
        ]);

        return app_pdo_fetch_table_metadata_column_item(
            $app,
            $projectKey,
            (string) ($row['table_name'] ?? ''),
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
function app_pdo_delete_table_metadata_column(array $app, string $projectKey, string $columnPid): array
{
    try {
        $normalizedColumnPid = (int) trim($columnPid);
        if ($normalizedColumnPid <= 0) {
            throw new RuntimeException('column PID の形式が不正です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_table_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'DELETE FROM dbtablecolumns
             WHERE PID = :pid
               AND ProjectPID = :project_id'
        );
        $statement->execute([
            ':pid' => $normalizedColumnPid,
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

function app_table_metadata_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
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
