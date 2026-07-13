<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/table_constraint_metadata.php';

/** @return array{ok:bool,snapshot:array<string,mixed>,error:string} */
function app_fetch_project_table_constraints(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_table_constraint_metadata_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            throw new RuntimeException('project not found.');
        }
        $keys = [];
        $keyIndexes = [];
        $statement = $pdo->prepare(
            'SELECT k.id, k.table_pid, k.key_name, k.key_kind, k.source_of_truth,
                    c.column_pid, c.ordinal_position
             FROM project_table_keys k
             LEFT JOIN project_table_key_columns c ON c.table_key_id = k.id AND c.project_id = k.project_id
             WHERE k.project_id = :project_id
             ORDER BY k.table_pid, k.key_name, c.ordinal_position',
        );
        $statement->execute([':project_id' => $projectId]);
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) $row['id'];
            if (!isset($keyIndexes[$id])) {
                $keyIndexes[$id] = count($keys);
                $keys[] = [
                    'table_pid' => (int) $row['table_pid'],
                    'key_name' => (string) $row['key_name'],
                    'key_kind' => (string) $row['key_kind'],
                    'source_of_truth' => (string) $row['source_of_truth'],
                    'columns' => [],
                ];
            }
            if ($row['column_pid'] !== null) {
                $keys[$keyIndexes[$id]]['columns'][] = [
                    'column_pid' => (int) $row['column_pid'],
                    'ordinal_position' => (int) $row['ordinal_position'],
                ];
            }
        }

        $foreignKeys = [];
        $fkIndexes = [];
        $statement = $pdo->prepare(
            'SELECT f.id, f.table_pid, f.constraint_name, f.referenced_table_pid,
                    f.on_update_action, f.on_delete_action, f.source_of_truth,
                    c.column_pid, c.referenced_column_pid, c.ordinal_position
             FROM project_table_foreign_keys f
             LEFT JOIN project_table_foreign_key_columns c ON c.foreign_key_id = f.id AND c.project_id = f.project_id
             WHERE f.project_id = :project_id
             ORDER BY f.table_pid, f.constraint_name, c.ordinal_position',
        );
        $statement->execute([':project_id' => $projectId]);
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) $row['id'];
            if (!isset($fkIndexes[$id])) {
                $fkIndexes[$id] = count($foreignKeys);
                $foreignKeys[] = [
                    'table_pid' => (int) $row['table_pid'],
                    'constraint_name' => (string) $row['constraint_name'],
                    'referenced_table_pid' => (int) $row['referenced_table_pid'],
                    'on_update_action' => (string) $row['on_update_action'],
                    'on_delete_action' => (string) $row['on_delete_action'],
                    'source_of_truth' => (string) $row['source_of_truth'],
                    'columns' => [],
                ];
            }
            if ($row['column_pid'] !== null) {
                $foreignKeys[$fkIndexes[$id]]['columns'][] = [
                    'column_pid' => (int) $row['column_pid'],
                    'referenced_column_pid' => (int) $row['referenced_column_pid'],
                    'ordinal_position' => (int) $row['ordinal_position'],
                ];
            }
        }

        return ['ok' => true, 'snapshot' => ['keys' => $keys, 'foreign_keys' => $foreignKeys], 'error' => ''];
    } catch (Throwable $throwable) {
        return ['ok' => false, 'snapshot' => ['keys' => [], 'foreign_keys' => []], 'error' => $throwable->getMessage()];
    }
}

/** @return array{ok:bool,snapshot:array<string,mixed>,error:string} */
function app_replace_project_table_constraints(array $app, string $projectKey, array $input): array
{
    $pdo = null;
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_table_constraint_metadata_project_id($pdo, $projectKey);
        if ($projectId <= 0) {
            throw new RuntimeException('project not found.');
        }
        [$columnTableByPid, $projectTablePids] = app_table_constraint_metadata_catalogs($pdo, $projectId);
        $normalized = app_table_constraint_metadata_normalize($input, $columnTableByPid, $projectTablePids);
        if (!$normalized['ok']) {
            throw new InvalidArgumentException(implode(' ', $normalized['errors']));
        }

        $pdo->beginTransaction();
        foreach (['project_table_foreign_keys', 'project_table_keys'] as $table) {
            $delete = $pdo->prepare('DELETE FROM ' . $table . ' WHERE project_id = :project_id');
            $delete->execute([':project_id' => $projectId]);
        }
        foreach ($normalized['snapshot']['keys'] as $key) {
            $insert = $pdo->prepare(
                'INSERT INTO project_table_keys (project_id, table_pid, key_name, key_kind, source_of_truth)
                 VALUES (:project_id, :table_pid, :key_name, :key_kind, :source_of_truth)',
            );
            $insert->execute([
                ':project_id' => $projectId,
                ':table_pid' => $key['table_pid'],
                ':key_name' => $key['key_name'],
                ':key_kind' => $key['key_kind'],
                ':source_of_truth' => $key['source_of_truth'],
            ]);
            $keyId = (int) $pdo->lastInsertId();
            foreach ($key['columns'] as $column) {
                $child = $pdo->prepare(
                    'INSERT INTO project_table_key_columns
                     (project_id, table_key_id, column_pid, ordinal_position)
                     VALUES (:project_id, :table_key_id, :column_pid, :ordinal_position)',
                );
                $child->execute([
                    ':project_id' => $projectId,
                    ':table_key_id' => $keyId,
                    ':column_pid' => $column['column_pid'],
                    ':ordinal_position' => $column['ordinal_position'],
                ]);
            }
        }
        foreach ($normalized['snapshot']['foreign_keys'] as $foreignKey) {
            $insert = $pdo->prepare(
                'INSERT INTO project_table_foreign_keys
                 (project_id, table_pid, constraint_name, referenced_table_pid, on_update_action, on_delete_action, source_of_truth)
                 VALUES (:project_id, :table_pid, :constraint_name, :referenced_table_pid, :on_update_action, :on_delete_action, :source_of_truth)',
            );
            $insert->execute([
                ':project_id' => $projectId,
                ':table_pid' => $foreignKey['table_pid'],
                ':constraint_name' => $foreignKey['constraint_name'],
                ':referenced_table_pid' => $foreignKey['referenced_table_pid'],
                ':on_update_action' => $foreignKey['on_update_action'],
                ':on_delete_action' => $foreignKey['on_delete_action'],
                ':source_of_truth' => $foreignKey['source_of_truth'],
            ]);
            $foreignKeyId = (int) $pdo->lastInsertId();
            foreach ($foreignKey['columns'] as $column) {
                $child = $pdo->prepare(
                    'INSERT INTO project_table_foreign_key_columns
                     (project_id, foreign_key_id, column_pid, referenced_column_pid, ordinal_position)
                     VALUES (:project_id, :foreign_key_id, :column_pid, :referenced_column_pid, :ordinal_position)',
                );
                $child->execute([
                    ':project_id' => $projectId,
                    ':foreign_key_id' => $foreignKeyId,
                    ':column_pid' => $column['column_pid'],
                    ':referenced_column_pid' => $column['referenced_column_pid'],
                    ':ordinal_position' => $column['ordinal_position'],
                ]);
            }
        }
        $pdo->commit();
        return app_fetch_project_table_constraints($app, $projectKey);
    } catch (Throwable $throwable) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['ok' => false, 'snapshot' => ['keys' => [], 'foreign_keys' => []], 'error' => $throwable->getMessage()];
    }
}

function app_table_constraint_metadata_project_id(PDO $pdo, string $projectKey): int
{
    $normalized = app_normalize_project_key($projectKey);
    $statement = $pdo->prepare('SELECT id FROM projects WHERE project_key = :project_key LIMIT 1');
    $statement->execute([':project_key' => $normalized]);
    return (int) ($statement->fetchColumn() ?: 0);
}

/** @return array{0:array<int,int>,1:array<int,bool>} */
function app_table_constraint_metadata_catalogs(PDO $pdo, int $projectId): array
{
    $tables = [];
    $statement = $pdo->prepare('SELECT PID FROM dbtable WHERE ProjectPID = :project_id');
    $statement->execute([':project_id' => $projectId]);
    foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $pid) {
        $tables[(int) $pid] = true;
    }
    $columns = [];
    $statement = $pdo->prepare('SELECT PID, dbtablePID FROM dbtablecolumns WHERE ProjectPID = :project_id');
    $statement->execute([':project_id' => $projectId]);
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $columns[(int) $row['PID']] = (int) $row['dbtablePID'];
    }
    return [$columns, $tables];
}

/**
 * Replace project constraints from a PID-free physical-name snapshot using the caller's transaction.
 *
 * @param array{keys?:array,foreign_keys?:array} $portable
 */
function app_replace_project_table_constraints_portable_pdo(PDO $pdo, int $projectId, array $portable): void
{
    $tablePids = [];
    $columnPids = [];
    $columnTableByPid = [];
    $projectTablePids = [];
    $statement = $pdo->prepare(
        'SELECT t.PID AS table_pid, t.physical_name AS table_name,
                c.PID AS column_pid, c.physical_name AS column_name
         FROM dbtable t
         LEFT JOIN dbtablecolumns c ON c.dbtablePID = t.PID AND c.ProjectPID = t.ProjectPID
         WHERE t.ProjectPID = :project_id',
    );
    $statement->execute([':project_id' => $projectId]);
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $tablePid = (int) $row['table_pid'];
        $tableName = strtolower((string) $row['table_name']);
        $tablePids[$tableName] = $tablePid;
        $projectTablePids[$tablePid] = true;
        if ($row['column_pid'] !== null) {
            $columnPid = (int) $row['column_pid'];
            $columnPids[$tableName . "\n" . strtolower((string) $row['column_name'])] = $columnPid;
            $columnTableByPid[$columnPid] = $tablePid;
        }
    }

    $resolved = ['keys' => [], 'foreign_keys' => []];
    foreach (is_array($portable['keys'] ?? null) ? $portable['keys'] : [] as $key) {
        $tableName = strtolower(trim((string) ($key['table_name'] ?? '')));
        $columns = [];
        foreach (is_array($key['columns'] ?? null) ? $key['columns'] : [] as $position => $column) {
            $columnName = is_array($column) ? (string) ($column['column_name'] ?? '') : (string) $column;
            $columns[] = [
                'column_pid' => $columnPids[$tableName . "\n" . strtolower($columnName)] ?? 0,
                'ordinal_position' => is_array($column) ? (int) ($column['ordinal_position'] ?? ($position + 1)) : $position + 1,
            ];
        }
        $resolved['keys'][] = [
            'table_pid' => $tablePids[$tableName] ?? 0,
            'key_name' => $key['key_name'] ?? '',
            'key_kind' => $key['key_kind'] ?? '',
            'source_of_truth' => $key['source_of_truth'] ?? 'live-schema',
            'columns' => $columns,
        ];
    }
    foreach (is_array($portable['foreign_keys'] ?? null) ? $portable['foreign_keys'] : [] as $foreignKey) {
        $tableName = strtolower(trim((string) ($foreignKey['table_name'] ?? '')));
        $referencedTableName = strtolower(trim((string) ($foreignKey['referenced_table_name'] ?? '')));
        $columns = [];
        foreach (is_array($foreignKey['columns'] ?? null) ? $foreignKey['columns'] : [] as $position => $column) {
            $columns[] = [
                'column_pid' => $columnPids[$tableName . "\n" . strtolower((string) ($column['column_name'] ?? ''))] ?? 0,
                'referenced_column_pid' => $columnPids[$referencedTableName . "\n" . strtolower((string) ($column['referenced_column_name'] ?? ''))] ?? 0,
                'ordinal_position' => (int) ($column['ordinal_position'] ?? ($position + 1)),
            ];
        }
        $resolved['foreign_keys'][] = [
            'table_pid' => $tablePids[$tableName] ?? 0,
            'constraint_name' => $foreignKey['constraint_name'] ?? '',
            'referenced_table_pid' => $tablePids[$referencedTableName] ?? 0,
            'on_update_action' => $foreignKey['on_update_action'] ?? 'NO ACTION',
            'on_delete_action' => $foreignKey['on_delete_action'] ?? 'NO ACTION',
            'source_of_truth' => $foreignKey['source_of_truth'] ?? 'live-schema',
            'columns' => $columns,
        ];
    }

    $normalized = app_table_constraint_metadata_normalize($resolved, $columnTableByPid, $projectTablePids);
    if (!$normalized['ok']) {
        throw new RuntimeException('portable table constraints are invalid: ' . implode(' ', $normalized['errors']));
    }
    app_replace_project_table_constraints_pdo($pdo, $projectId, $normalized['snapshot']);
}

function app_replace_project_table_constraints_pdo(PDO $pdo, int $projectId, array $snapshot): void
{
    foreach (['project_table_foreign_keys', 'project_table_keys'] as $table) {
        $delete = $pdo->prepare('DELETE FROM ' . $table . ' WHERE project_id = :project_id');
        $delete->execute([':project_id' => $projectId]);
    }
    foreach ($snapshot['keys'] as $key) {
        $insert = $pdo->prepare('INSERT INTO project_table_keys (project_id, table_pid, key_name, key_kind, source_of_truth) VALUES (:p,:t,:n,:k,:s)');
        $insert->execute([':p'=>$projectId, ':t'=>$key['table_pid'], ':n'=>$key['key_name'], ':k'=>$key['key_kind'], ':s'=>$key['source_of_truth']]);
        $id = (int) $pdo->lastInsertId();
        foreach ($key['columns'] as $column) {
            $child = $pdo->prepare('INSERT INTO project_table_key_columns (project_id,table_key_id,column_pid,ordinal_position) VALUES (:p,:i,:c,:o)');
            $child->execute([':p'=>$projectId, ':i'=>$id, ':c'=>$column['column_pid'], ':o'=>$column['ordinal_position']]);
        }
    }
    foreach ($snapshot['foreign_keys'] as $foreignKey) {
        $insert = $pdo->prepare('INSERT INTO project_table_foreign_keys (project_id,table_pid,constraint_name,referenced_table_pid,on_update_action,on_delete_action,source_of_truth) VALUES (:p,:t,:n,:r,:u,:d,:s)');
        $insert->execute([':p'=>$projectId, ':t'=>$foreignKey['table_pid'], ':n'=>$foreignKey['constraint_name'], ':r'=>$foreignKey['referenced_table_pid'], ':u'=>$foreignKey['on_update_action'], ':d'=>$foreignKey['on_delete_action'], ':s'=>$foreignKey['source_of_truth']]);
        $id = (int) $pdo->lastInsertId();
        foreach ($foreignKey['columns'] as $column) {
            $child = $pdo->prepare('INSERT INTO project_table_foreign_key_columns (project_id,foreign_key_id,column_pid,referenced_column_pid,ordinal_position) VALUES (:p,:i,:c,:r,:o)');
            $child->execute([':p'=>$projectId, ':i'=>$id, ':c'=>$column['column_pid'], ':r'=>$column['referenced_column_pid'], ':o'=>$column['ordinal_position']]);
        }
    }
}
