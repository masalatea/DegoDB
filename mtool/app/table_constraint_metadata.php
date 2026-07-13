<?php

declare(strict_types=1);

/**
 * @param array<string,mixed> $input
 * @param array<int,int> $columnTableByPid
 * @param array<int,bool> $projectTablePids
 * @return array{ok:bool,snapshot:array<string,mixed>,errors:list<string>}
 */
function app_table_constraint_metadata_normalize(
    array $input,
    array $columnTableByPid,
    array $projectTablePids,
): array {
    $errors = [];
    $keys = [];
    $keyNames = [];
    $primaryTables = [];
    foreach (is_array($input['keys'] ?? null) ? $input['keys'] : [] as $index => $item) {
        if (!is_array($item)) {
            $errors[] = 'keys[' . $index . '] must be an object.';
            continue;
        }
        $tablePid = (int) ($item['table_pid'] ?? 0);
        $keyName = trim((string) ($item['key_name'] ?? ''));
        $keyKind = strtolower(trim((string) ($item['key_kind'] ?? '')));
        $source = trim((string) ($item['source_of_truth'] ?? 'manual')) ?: 'manual';
        if (!isset($projectTablePids[$tablePid])) {
            $errors[] = 'key table_pid is outside the project: ' . $tablePid . '.';
        }
        if ($keyName === '') {
            $errors[] = 'key_name is required at keys[' . $index . '].';
        }
        if (!in_array($keyKind, ['primary', 'unique', 'index'], true)) {
            $errors[] = 'unsupported key_kind at keys[' . $index . '].';
        }
        $identity = $tablePid . "\n" . strtolower($keyName);
        if (isset($keyNames[$identity])) {
            $errors[] = 'duplicate key name: ' . $keyName . '.';
        }
        $keyNames[$identity] = true;
        if ($keyKind === 'primary') {
            if (isset($primaryTables[$tablePid])) {
                $errors[] = 'multiple primary keys for table_pid ' . $tablePid . '.';
            }
            $primaryTables[$tablePid] = true;
        }
        $columns = app_table_constraint_metadata_normalize_key_columns(
            $item['columns'] ?? [],
            $tablePid,
            $columnTableByPid,
            'keys[' . $index . ']',
            $errors,
        );
        $keys[] = [
            'table_pid' => $tablePid,
            'key_name' => $keyName,
            'key_kind' => $keyKind,
            'source_of_truth' => $source,
            'columns' => $columns,
        ];
    }

    $foreignKeys = [];
    $foreignKeyNames = [];
    foreach (is_array($input['foreign_keys'] ?? null) ? $input['foreign_keys'] : [] as $index => $item) {
        if (!is_array($item)) {
            $errors[] = 'foreign_keys[' . $index . '] must be an object.';
            continue;
        }
        $tablePid = (int) ($item['table_pid'] ?? 0);
        $referencedTablePid = (int) ($item['referenced_table_pid'] ?? 0);
        $name = trim((string) ($item['constraint_name'] ?? ''));
        if (!isset($projectTablePids[$tablePid]) || !isset($projectTablePids[$referencedTablePid])) {
            $errors[] = 'foreign key table reference is outside the project at foreign_keys[' . $index . '].';
        }
        if ($name === '') {
            $errors[] = 'constraint_name is required at foreign_keys[' . $index . '].';
        }
        $identity = $tablePid . "\n" . strtolower($name);
        if (isset($foreignKeyNames[$identity])) {
            $errors[] = 'duplicate foreign key name: ' . $name . '.';
        }
        $foreignKeyNames[$identity] = true;
        $onUpdate = app_table_constraint_metadata_action((string) ($item['on_update_action'] ?? 'NO ACTION'));
        $onDelete = app_table_constraint_metadata_action((string) ($item['on_delete_action'] ?? 'NO ACTION'));
        if ($onUpdate === '' || $onDelete === '') {
            $errors[] = 'unsupported foreign key action at foreign_keys[' . $index . '].';
        }
        $columns = app_table_constraint_metadata_normalize_fk_columns(
            $item['columns'] ?? [],
            $tablePid,
            $referencedTablePid,
            $columnTableByPid,
            'foreign_keys[' . $index . ']',
            $errors,
        );
        $foreignKeys[] = [
            'table_pid' => $tablePid,
            'constraint_name' => $name,
            'referenced_table_pid' => $referencedTablePid,
            'on_update_action' => $onUpdate,
            'on_delete_action' => $onDelete,
            'source_of_truth' => trim((string) ($item['source_of_truth'] ?? 'manual')) ?: 'manual',
            'columns' => $columns,
        ];
    }

    return ['ok' => $errors === [], 'snapshot' => ['keys' => $keys, 'foreign_keys' => $foreignKeys], 'errors' => $errors];
}

function app_table_constraint_metadata_normalize_key_columns(
    mixed $items,
    int $tablePid,
    array $columnTableByPid,
    string $path,
    array &$errors,
): array {
    if (!is_array($items) || $items === []) {
        $errors[] = $path . '.columns must not be empty.';
        return [];
    }
    $normalized = [];
    $seen = [];
    foreach ($items as $index => $item) {
        $columnPid = is_array($item) ? (int) ($item['column_pid'] ?? 0) : 0;
        if (($columnTableByPid[$columnPid] ?? 0) !== $tablePid) {
            $errors[] = $path . '.columns[' . $index . '] does not belong to table_pid ' . $tablePid . '.';
        }
        if (isset($seen[$columnPid])) {
            $errors[] = $path . ' contains duplicate column_pid ' . $columnPid . '.';
        }
        $seen[$columnPid] = true;
        $normalized[] = ['column_pid' => $columnPid, 'ordinal_position' => $index + 1];
    }
    return $normalized;
}

function app_table_constraint_metadata_normalize_fk_columns(
    mixed $items,
    int $tablePid,
    int $referencedTablePid,
    array $columnTableByPid,
    string $path,
    array &$errors,
): array {
    if (!is_array($items) || $items === []) {
        $errors[] = $path . '.columns must not be empty.';
        return [];
    }
    $normalized = [];
    $seen = [];
    foreach ($items as $index => $item) {
        $columnPid = is_array($item) ? (int) ($item['column_pid'] ?? 0) : 0;
        $referencedColumnPid = is_array($item) ? (int) ($item['referenced_column_pid'] ?? 0) : 0;
        if (($columnTableByPid[$columnPid] ?? 0) !== $tablePid) {
            $errors[] = $path . '.columns[' . $index . '] source column is outside the declared table.';
        }
        if (($columnTableByPid[$referencedColumnPid] ?? 0) !== $referencedTablePid) {
            $errors[] = $path . '.columns[' . $index . '] referenced column is outside the referenced table.';
        }
        if (isset($seen[$columnPid])) {
            $errors[] = $path . ' contains duplicate source column_pid ' . $columnPid . '.';
        }
        $seen[$columnPid] = true;
        $normalized[] = [
            'column_pid' => $columnPid,
            'referenced_column_pid' => $referencedColumnPid,
            'ordinal_position' => $index + 1,
        ];
    }
    return $normalized;
}

function app_table_constraint_metadata_action(string $value): string
{
    $normalized = strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? $value));
    return in_array($normalized, ['NO ACTION', 'RESTRICT', 'CASCADE', 'SET NULL', 'SET DEFAULT'], true)
        ? $normalized
        : '';
}
