<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_promotion_manifest.php';

const APP_FIREBIRD_SOURCE_INSPECTION_VERSION = 'firebird-source-inspection-v1';

/**
 * Normalize Firebird metadata/value-profile rows into the source-inspection
 * shape used by promotion planning.
 *
 * This first slice is intentionally pure: no PDO, no filesystem write, no
 * target connection, and no Firebird mutation.
 *
 * @param array<string,mixed> $metadata
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_firebird_source_inspection_normalize(array $metadata, array $options = []): array
{
    $blockers = [];
    if (app_sqlite_mysql_promotion_contains_secret($metadata) || app_sqlite_mysql_promotion_contains_secret($options)) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/');
    }

    $relations = app_firebird_source_inspection_relations($metadata['relations'] ?? [], $blockers);
    $columnsByTable = app_firebird_source_inspection_columns($metadata['fields'] ?? [], $relations, $blockers);
    $constraints = app_firebird_source_inspection_constraints($metadata['constraints'] ?? [], $relations, $blockers);
    $segments = app_firebird_source_inspection_index_segments($metadata['index_segments'] ?? [], $blockers);
    $foreignKeyRefs = app_firebird_source_inspection_ref_constraints($metadata['ref_constraints'] ?? [], $blockers);
    $profiles = app_firebird_source_inspection_value_profiles($metadata['value_profiles'] ?? [], $blockers);

    $tables = [];
    foreach ($relations as $firebirdName => $tableName) {
        $tableConstraints = $constraints[$firebirdName] ?? [];
        $keys = app_firebird_source_inspection_keys($tableConstraints, $segments);
        $foreignKeys = app_firebird_source_inspection_foreign_keys($tableConstraints, $constraints, $segments, $foreignKeyRefs, $blockers);
        $columns = [];
        foreach ($columnsByTable[$firebirdName] ?? [] as $column) {
            $columnName = $column['name'];
            $columns[] = [
                'name' => $columnName,
                'type' => $column['type'],
                'nullable' => $column['nullable'],
                'default' => $column['default'],
                'profile' => $profiles[$tableName][$columnName] ?? [],
            ];
        }
        if ($columns === []) $blockers[] = app_sqlite_mysql_promotion_issue('firebird_table_has_no_columns', '/tables/' . $tableName);
        $tables[] = [
            'name' => $tableName,
            'row_count' => max(0, (int) (($metadata['row_counts'][$tableName] ?? $metadata['row_counts'][$firebirdName] ?? 0))),
            'keys' => $keys,
            'foreign_keys' => $foreignKeys,
            'columns' => $columns,
        ];
    }
    usort($tables, static fn (array $a, array $b): int => (string) $a['name'] <=> (string) $b['name']);

    $blockers = app_sqlite_mysql_promotion_sorted_issues($blockers);
    $result = [
        'inspection_version' => APP_FIREBIRD_SOURCE_INSPECTION_VERSION,
        'ok' => $blockers === [],
        'stage' => 'source_inspection',
        'driver' => 'firebird',
        'mutation_performed' => false,
        'source_identity' => app_firebird_source_inspection_safe_identity((string) ($metadata['source_identity'] ?? $options['source_identity'] ?? 'firebird-source'), $blockers),
        'tables' => $tables,
        'blockers' => $blockers,
        'warnings' => [],
    ];
    $errors = app_firebird_source_inspection_contract_errors($result);
    if ($errors !== []) {
        $result['ok'] = false;
        foreach ($errors as $error) $result['blockers'][] = app_sqlite_mysql_promotion_issue('inspection_contract_invalid', '/' . $error);
        $result['blockers'] = app_sqlite_mysql_promotion_sorted_issues($result['blockers']);
    }
    return $result;
}

/** @param array<string,mixed> $inspection @return list<string> */
function app_firebird_source_inspection_contract_errors(array $inspection): array
{
    $errors = [];
    if (($inspection['inspection_version'] ?? '') !== APP_FIREBIRD_SOURCE_INSPECTION_VERSION) $errors[] = 'inspection_version';
    if (($inspection['stage'] ?? '') !== 'source_inspection') $errors[] = 'stage';
    if (($inspection['driver'] ?? '') !== 'firebird') $errors[] = 'driver';
    if (($inspection['mutation_performed'] ?? null) !== false) $errors[] = 'mutation_performed';
    foreach (['tables', 'blockers', 'warnings'] as $key) {
        if (!is_array($inspection[$key] ?? null)) $errors[] = $key;
    }
    if (($inspection['ok'] ?? null) !== (($inspection['blockers'] ?? []) === [])) $errors[] = 'ok_blocker_consistency';
    if (app_sqlite_mysql_promotion_contains_secret($inspection)) $errors[] = 'secret';
    return array_values(array_unique($errors));
}

/** @param mixed $items @param list<array{code:string,path:string}> $blockers @return array<string,string> */
function app_firebird_source_inspection_relations(mixed $items, array &$blockers): array
{
    $relations = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $raw = trim((string) ($item['relation_name'] ?? $item['name'] ?? ''));
        if ($raw === '') continue;
        $system = (int) ($item['system_flag'] ?? 0);
        if ($system !== 0) continue;
        $normalized = app_firebird_source_inspection_identifier($raw, '/relations/' . $raw, $blockers);
        if ($normalized !== '') $relations[strtoupper($raw)] = $normalized;
    }
    ksort($relations, SORT_STRING);
    if ($relations === []) $blockers[] = app_sqlite_mysql_promotion_issue('firebird_no_user_tables', '/relations');
    return $relations;
}

/** @param mixed $items @param array<string,string> $relations @param list<array{code:string,path:string}> $blockers @return array<string,list<array{name:string,type:string,nullable:bool,default:mixed,position:int}>> */
function app_firebird_source_inspection_columns(mixed $items, array $relations, array &$blockers): array
{
    $columns = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $relation = strtoupper(trim((string) ($item['relation_name'] ?? '')));
        if (!isset($relations[$relation])) continue;
        $raw = trim((string) ($item['field_name'] ?? $item['name'] ?? ''));
        if ($raw === '') continue;
        $name = app_firebird_source_inspection_identifier($raw, '/fields/' . $relation . '/' . $raw, $blockers);
        if ($name === '') continue;
        $columns[$relation][] = [
            'name' => $name,
            'type' => app_firebird_source_inspection_type($item),
            'nullable' => ((int) ($item['null_flag'] ?? 0)) !== 1,
            'default' => app_firebird_source_inspection_default($item['default_source'] ?? null),
            'position' => (int) ($item['field_position'] ?? $item['position'] ?? 0),
        ];
    }
    foreach ($columns as &$tableColumns) {
        usort($tableColumns, static fn (array $a, array $b): int => [$a['position'], $a['name']] <=> [$b['position'], $b['name']]);
    }
    unset($tableColumns);
    ksort($columns, SORT_STRING);
    return $columns;
}

/** @param mixed $items @param array<string,string> $relations @param list<array{code:string,path:string}> $blockers @return array<string,array<string,array{constraint_name:string,kind:string,index_name:string,relation_name:string}>> */
function app_firebird_source_inspection_constraints(mixed $items, array $relations, array &$blockers): array
{
    $constraints = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $relation = strtoupper(trim((string) ($item['relation_name'] ?? '')));
        if (!isset($relations[$relation])) continue;
        $constraint = strtoupper(trim((string) ($item['constraint_name'] ?? '')));
        $index = strtoupper(trim((string) ($item['index_name'] ?? '')));
        $type = strtoupper(trim((string) ($item['constraint_type'] ?? '')));
        $kind = match ($type) {
            'PRIMARY KEY' => 'primary',
            'UNIQUE' => 'unique',
            'FOREIGN KEY' => 'foreign',
            default => '',
        };
        if ($kind === '') continue;
        if ($constraint === '' || $index === '') {
            $blockers[] = app_sqlite_mysql_promotion_issue('firebird_unsupported_constraint', '/constraints/' . $constraint);
            continue;
        }
        $constraints[$relation][$constraint] = ['constraint_name' => strtolower($constraint), 'kind' => $kind, 'index_name' => $index, 'relation_name' => $relation];
    }
    ksort($constraints, SORT_STRING);
    return $constraints;
}

/** @param mixed $items @param list<array{code:string,path:string}> $blockers @return array<string,list<string>> */
function app_firebird_source_inspection_index_segments(mixed $items, array &$blockers): array
{
    $segments = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $index = strtoupper(trim((string) ($item['index_name'] ?? '')));
        $raw = trim((string) ($item['field_name'] ?? ''));
        if ($index === '' || $raw === '') continue;
        $name = app_firebird_source_inspection_identifier($raw, '/index_segments/' . $index . '/' . $raw, $blockers);
        if ($name === '') continue;
        $segments[$index][] = ['name' => $name, 'position' => (int) ($item['field_position'] ?? $item['position'] ?? 0)];
    }
    $result = [];
    foreach ($segments as $index => $fields) {
        usort($fields, static fn (array $a, array $b): int => [$a['position'], $a['name']] <=> [$b['position'], $b['name']]);
        $result[$index] = array_column($fields, 'name');
    }
    ksort($result, SORT_STRING);
    return $result;
}

/** @param mixed $items @param list<array{code:string,path:string}> $blockers @return array<string,string> */
function app_firebird_source_inspection_ref_constraints(mixed $items, array &$blockers): array
{
    $refs = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $constraint = strtoupper(trim((string) ($item['constraint_name'] ?? '')));
        $referenced = strtoupper(trim((string) ($item['referenced_constraint_name'] ?? $item['const_name_uq'] ?? '')));
        if ($constraint !== '' && $referenced !== '') $refs[$constraint] = $referenced;
    }
    ksort($refs, SORT_STRING);
    return $refs;
}

/** @param array<string,array{constraint_name:string,kind:string,index_name:string,relation_name:string}> $tableConstraints @param array<string,list<string>> $segments @return list<array{kind:string,name:string,columns:list<string>}> */
function app_firebird_source_inspection_keys(array $tableConstraints, array $segments): array
{
    $keys = [];
    foreach ($tableConstraints as $constraint) {
        if (!in_array($constraint['kind'], ['primary', 'unique'], true)) continue;
        $columns = $segments[$constraint['index_name']] ?? [];
        if ($columns !== []) $keys[] = ['kind' => $constraint['kind'], 'name' => $constraint['constraint_name'], 'columns' => $columns];
    }
    usort($keys, static fn (array $a, array $b): int => [$a['kind'], $a['name'], $a['columns']] <=> [$b['kind'], $b['name'], $b['columns']]);
    return $keys;
}

/** @param array<string,array{constraint_name:string,kind:string,index_name:string,relation_name:string}> $tableConstraints @param array<string,array<string,array{constraint_name:string,kind:string,index_name:string,relation_name:string}>> $constraints @param array<string,list<string>> $segments @param array<string,string> $refs @param list<array{code:string,path:string}> $blockers @return list<array{name:string,columns:list<string>,referenced_table:string,referenced_columns:list<string>}> */
function app_firebird_source_inspection_foreign_keys(array $tableConstraints, array $constraints, array $segments, array $refs, array &$blockers): array
{
    $byName = [];
    foreach ($constraints as $tableConstraintsByName) foreach ($tableConstraintsByName as $name => $constraint) $byName[$name] = $constraint;
    $foreignKeys = [];
    foreach ($tableConstraints as $name => $constraint) {
        if ($constraint['kind'] !== 'foreign') continue;
        $referencedName = $refs[strtoupper($name)] ?? '';
        $referenced = $byName[$referencedName] ?? null;
        if (!is_array($referenced)) {
            $blockers[] = app_sqlite_mysql_promotion_issue('firebird_foreign_key_reference_missing', '/foreign_keys/' . strtolower($name));
            continue;
        }
        $columns = $segments[$constraint['index_name']] ?? [];
        $referencedColumns = $segments[$referenced['index_name']] ?? [];
        if ($columns === [] || $referencedColumns === [] || count($columns) !== count($referencedColumns)) {
            $blockers[] = app_sqlite_mysql_promotion_issue('firebird_foreign_key_columns_missing', '/foreign_keys/' . strtolower($name));
            continue;
        }
        $foreignKeys[] = [
            'name' => strtolower($name),
            'columns' => $columns,
            'referenced_table' => strtolower($referenced['relation_name']),
            'referenced_columns' => $referencedColumns,
        ];
    }
    usort($foreignKeys, static fn (array $a, array $b): int => [$a['name'], $a['columns']] <=> [$b['name'], $b['columns']]);
    return $foreignKeys;
}

/** @param mixed $items @param list<array{code:string,path:string}> $blockers @return array<string,array<string,array<string,mixed>>> */
function app_firebird_source_inspection_value_profiles(mixed $items, array &$blockers): array
{
    $profiles = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $table = trim((string) ($item['table'] ?? $item['relation_name'] ?? ''));
        $column = trim((string) ($item['column'] ?? $item['field_name'] ?? ''));
        if ($table === '' || $column === '') continue;
        $tableName = app_firebird_source_inspection_identifier($table, '/value_profiles/' . $table, $blockers);
        $columnName = app_firebird_source_inspection_identifier($column, '/value_profiles/' . $table . '/' . $column, $blockers);
        if ($tableName === '' || $columnName === '') continue;
        $profile = $item['profile'] ?? [];
        $profiles[$tableName][$columnName] = is_array($profile) ? app_sqlite_mysql_promotion_profile($profile) : [];
    }
    ksort($profiles, SORT_STRING);
    return $profiles;
}

/** @param list<array{code:string,path:string}> $blockers */
function app_firebird_source_inspection_identifier(string $raw, string $path, array &$blockers): string
{
    $trimmed = trim($raw);
    if ($trimmed === '') return '';
    if ($trimmed !== strtoupper($trimmed)) {
        $blockers[] = app_sqlite_mysql_promotion_issue('firebird_quoted_or_case_sensitive_identifier_unsupported', $path);
        return '';
    }
    return strtolower($trimmed);
}

/** @param array<string,mixed> $field */
function app_firebird_source_inspection_type(array $field): string
{
    $typeName = strtoupper(trim((string) ($field['type_name'] ?? $field['type'] ?? '')));
    if ($typeName !== '') {
        if (in_array($typeName, ['VARCHAR', 'CHAR'], true)) return $typeName . '(' . max(1, (int) ($field['field_character_length'] ?? $field['character_length'] ?? $field['field_length'] ?? $field['length'] ?? 1)) . ')';
        if (in_array($typeName, ['DECIMAL', 'NUMERIC'], true)) {
            $precision = max(1, (int) ($field['field_precision'] ?? $field['precision'] ?? 18));
            $scale = abs((int) ($field['field_scale'] ?? $field['scale'] ?? 0));
            return $typeName . '(' . $precision . ',' . $scale . ')';
        }
        if ($typeName === 'BLOB') {
            $subType = (int) ($field['field_sub_type'] ?? $field['sub_type'] ?? 0);
            return $subType === 1 ? 'BLOB SUB_TYPE TEXT' : 'BLOB SUB_TYPE BINARY';
        }
        return $typeName;
    }
    return 'UNKNOWN';
}

function app_firebird_source_inspection_default(mixed $default): mixed
{
    if (!is_string($default)) return null;
    $trimmed = trim($default);
    if ($trimmed === '') return null;
    return preg_replace('/^DEFAULT\s+/i', '', $trimmed) ?? $trimmed;
}

/** @param list<array{code:string,path:string}> $blockers */
function app_firebird_source_inspection_safe_identity(string $identity, array &$blockers): string
{
    $trimmed = trim($identity);
    if ($trimmed === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $trimmed) === 1 || str_contains($trimmed, '@')) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/source_identity');
        return 'firebird-source';
    }
    return $trimmed;
}
