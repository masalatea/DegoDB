<?php

declare(strict_types=1);

const APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION = 'sqlite-mysql-promotion-manifest-v1';

/**
 * Build a deterministic, review-only promotion manifest. Inputs are already
 * inspected snapshots; this function opens no database and writes no file.
 *
 * @param array<string,mixed> $canonicalSnapshot
 * @param array<string,mixed> $sqliteInspection
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_sqlite_mysql_promotion_manifest_build(
    array $canonicalSnapshot,
    array $sqliteInspection,
    array $options = [],
): array {
    $blockers = [];
    $warnings = [];
    if (app_sqlite_mysql_promotion_contains_secret($canonicalSnapshot)
        || app_sqlite_mysql_promotion_contains_secret($sqliteInspection)
        || app_sqlite_mysql_promotion_contains_secret($options)) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/');
    }
    $targetIdentity = trim((string) ($options['target_identity'] ?? 'mysql-target'));
    if ($targetIdentity === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $targetIdentity) === 1 || str_contains($targetIdentity, '@')) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/target/identity');
        $targetIdentity = 'mysql-target';
    }

    $canonicalTables = app_sqlite_mysql_promotion_tables($canonicalSnapshot['tables'] ?? []);
    $sourceTables = app_sqlite_mysql_promotion_tables($sqliteInspection['tables'] ?? []);
    if ($canonicalTables === []) {
        $blockers[] = app_sqlite_mysql_promotion_issue('canonical_metadata_incomplete', '/tables');
    }

    $loadOrder = app_sqlite_mysql_promotion_load_order($canonicalTables);
    if (!$loadOrder['ok']) {
        $blockers[] = app_sqlite_mysql_promotion_issue('foreign_key_cycle_unsupported', '/tables');
    }

    $tablePlans = [];
    foreach ($loadOrder['tables'] as $tableName) {
        $table = $canonicalTables[$tableName];
        $source = $sourceTables[$tableName] ?? null;
        if (!is_array($source)) {
            $blockers[] = app_sqlite_mysql_promotion_issue('source_schema_mismatch', '/tables/' . $tableName);
            continue;
        }
        $canonicalColumns = $table['columns'];
        $sourceColumns = $source['columns'];
        if (array_keys($canonicalColumns) !== array_keys($sourceColumns)) {
            $blockers[] = app_sqlite_mysql_promotion_issue('source_schema_mismatch', '/tables/' . $tableName . '/columns');
        }

        $primary = app_sqlite_mysql_promotion_primary_columns($table);
        if ($primary === []) {
            $blockers[] = app_sqlite_mysql_promotion_issue('stable_primary_key_missing', '/tables/' . $tableName . '/keys');
        }
        $keys = app_sqlite_mysql_promotion_keys($table['keys'] ?? []);
        $foreignKeys = app_sqlite_mysql_promotion_foreign_keys($table['foreign_keys'] ?? []);
        if ($keys !== app_sqlite_mysql_promotion_keys($source['keys'] ?? [])
            || $foreignKeys !== app_sqlite_mysql_promotion_foreign_keys($source['foreign_keys'] ?? [])) {
            $blockers[] = app_sqlite_mysql_promotion_issue('sqlite_constraint_evidence_missing', '/tables/' . $tableName . '/constraints');
        }

        $columnPlans = [];
        foreach ($canonicalColumns as $columnName => $column) {
            $sourceColumn = $sourceColumns[$columnName] ?? [];
            $mapping = app_sqlite_mysql_promotion_column_mapping($column, $sourceColumn);
            foreach ($mapping['blockers'] as $code) {
                $blockers[] = app_sqlite_mysql_promotion_issue($code, '/tables/' . $tableName . '/columns/' . $columnName);
            }
            foreach ($mapping['warnings'] as $code) {
                $warnings[] = app_sqlite_mysql_promotion_issue($code, '/tables/' . $tableName . '/columns/' . $columnName);
            }
            $columnPlans[] = [
                'name' => $columnName,
                'source_declared_type' => (string) ($sourceColumn['type'] ?? ''),
                'canonical_type' => (string) ($column['type'] ?? ''),
                'target_type' => $mapping['target_type'],
                'nullable' => (bool) ($column['nullable'] ?? false),
                'default' => $column['default'] ?? null,
                'profile' => app_sqlite_mysql_promotion_profile($sourceColumn['profile'] ?? []),
            ];
        }
        $tablePlans[] = [
            'name' => $tableName,
            'row_count' => max(0, (int) ($source['row_count'] ?? 0)),
            'primary_key' => $primary,
            'keys' => $keys,
            'foreign_keys' => $foreignKeys,
            'columns' => $columnPlans,
        ];
    }
    foreach (array_diff(array_keys($sourceTables), array_keys($canonicalTables)) as $extraTable) {
        $blockers[] = app_sqlite_mysql_promotion_issue('source_schema_mismatch', '/tables/' . $extraTable);
    }

    $blockers = app_sqlite_mysql_promotion_sorted_issues($blockers);
    $warnings = app_sqlite_mysql_promotion_sorted_issues($warnings);
    $manifest = [
        'manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION,
        'ok' => $blockers === [],
        'stage' => 'preflight',
        'mutation_performed' => false,
        'source' => [
            'driver' => 'sqlite',
            'identity' => trim((string) ($sqliteInspection['source_identity'] ?? 'sqlite-source')),
            'snapshot_sha256' => app_sqlite_mysql_promotion_digest(app_sqlite_mysql_promotion_snapshot_for_digest($sqliteInspection)),
        ],
        'target' => [
            'driver' => 'mysql',
            'identity' => $targetIdentity,
            'must_be_empty' => true,
        ],
        'canonical_sha256' => app_sqlite_mysql_promotion_digest(app_sqlite_mysql_promotion_snapshot_for_digest($canonicalSnapshot)),
        'tables' => $tablePlans,
        'blockers' => $blockers,
        'warnings' => $warnings,
        'required_approvals' => ['target_schema_prepare', 'data_import', 'cutover'],
        'required_verification' => ['row_counts', 'primary_keys', 'row_digests', 'unique_keys', 'foreign_keys', 'values', 'next_ids', 'dbaccess_smoke'],
        'non_goals' => ['mysql_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover'],
    ];
    $errors = app_sqlite_mysql_promotion_manifest_contract_errors($manifest);
    if ($errors !== []) {
        $manifest['ok'] = false;
        foreach ($errors as $error) {
            $manifest['blockers'][] = app_sqlite_mysql_promotion_issue('manifest_contract_invalid', '/' . $error);
        }
        $manifest['blockers'] = app_sqlite_mysql_promotion_sorted_issues($manifest['blockers']);
    }
    return $manifest;
}

/** @param array<string,mixed> $manifest @return list<string> */
function app_sqlite_mysql_promotion_manifest_contract_errors(array $manifest): array
{
    $errors = [];
    if (($manifest['manifest_version'] ?? '') !== APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION) $errors[] = 'manifest_version';
    if (($manifest['stage'] ?? '') !== 'preflight') $errors[] = 'stage';
    if (($manifest['mutation_performed'] ?? null) !== false) $errors[] = 'mutation_performed';
    if (($manifest['source']['driver'] ?? '') !== 'sqlite') $errors[] = 'source_driver';
    if (($manifest['target']['driver'] ?? '') !== 'mysql') $errors[] = 'target_driver';
    foreach (['canonical_sha256', 'source.snapshot_sha256'] as $path) {
        $value = $path === 'canonical_sha256' ? ($manifest['canonical_sha256'] ?? '') : ($manifest['source']['snapshot_sha256'] ?? '');
        if (preg_match('/^[a-f0-9]{64}$/', (string) $value) !== 1) $errors[] = $path;
    }
    foreach (['tables', 'blockers', 'warnings', 'required_approvals', 'required_verification', 'non_goals'] as $key) {
        if (!is_array($manifest[$key] ?? null)) $errors[] = $key;
    }
    if (($manifest['ok'] ?? null) !== (($manifest['blockers'] ?? []) === [])) $errors[] = 'ok_blocker_consistency';
    if (app_sqlite_mysql_promotion_contains_secret($manifest)) $errors[] = 'secret';
    return array_values(array_unique($errors));
}

/** @return array<string,array<string,mixed>> */
function app_sqlite_mysql_promotion_tables(mixed $items): array
{
    $tables = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $name = trim((string) ($item['name'] ?? ''));
        if ($name === '') continue;
        $columns = [];
        foreach (is_array($item['columns'] ?? null) ? $item['columns'] : [] as $column) {
            if (!is_array($column)) continue;
            $columnName = trim((string) ($column['name'] ?? ''));
            if ($columnName !== '') $columns[$columnName] = $column;
        }
        ksort($columns, SORT_STRING);
        $item['columns'] = $columns;
        $tables[$name] = $item;
    }
    ksort($tables, SORT_STRING);
    return $tables;
}

/** @param array<string,mixed> $table @return list<string> */
function app_sqlite_mysql_promotion_primary_columns(array $table): array
{
    foreach (is_array($table['keys'] ?? null) ? $table['keys'] : [] as $key) {
        if (!is_array($key) || strtolower((string) ($key['kind'] ?? '')) !== 'primary') continue;
        $columns = array_values(array_filter(array_map('strval', is_array($key['columns'] ?? null) ? $key['columns'] : [])));
        sort($columns, SORT_STRING);
        return $columns;
    }
    return [];
}

/** @return list<array{kind:string,name:string,columns:list<string>}> */
function app_sqlite_mysql_promotion_keys(mixed $items): array
{
    $keys = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $kind = strtolower(trim((string) ($item['kind'] ?? $item['key_kind'] ?? '')));
        $columns = array_values(array_filter(array_map('strval', is_array($item['columns'] ?? null) ? $item['columns'] : [])));
        $name = trim((string) ($item['name'] ?? $item['key_name'] ?? ''));
        if ($kind !== '' && $columns !== []) $keys[] = ['kind' => $kind, 'name' => $name, 'columns' => $columns];
    }
    usort($keys, static fn (array $a, array $b): int => [$a['kind'], $a['name'], $a['columns']] <=> [$b['kind'], $b['name'], $b['columns']]);
    return $keys;
}

/** @return list<array{name:string,columns:list<string>,referenced_table:string,referenced_columns:list<string>}> */
function app_sqlite_mysql_promotion_foreign_keys(mixed $items): array
{
    $foreignKeys = [];
    foreach (is_array($items) ? $items : [] as $item) {
        if (!is_array($item)) continue;
        $columns = array_values(array_filter(array_map('strval', is_array($item['columns'] ?? null) ? $item['columns'] : [])));
        $referencedColumns = array_values(array_filter(array_map('strval', is_array($item['referenced_columns'] ?? null) ? $item['referenced_columns'] : [])));
        $referencedTable = trim((string) ($item['referenced_table'] ?? $item['referenced_table_name'] ?? ''));
        if ($columns === [] || $referencedTable === '' || count($columns) !== count($referencedColumns)) continue;
        $foreignKeys[] = [
            'name' => trim((string) ($item['name'] ?? $item['constraint_name'] ?? '')),
            'columns' => $columns,
            'referenced_table' => $referencedTable,
            'referenced_columns' => $referencedColumns,
        ];
    }
    usort($foreignKeys, static fn (array $a, array $b): int => [$a['name'], $a['columns']] <=> [$b['name'], $b['columns']]);
    return $foreignKeys;
}

/** @param array<string,array<string,mixed>> $tables @return array{ok:bool,tables:list<string>} */
function app_sqlite_mysql_promotion_load_order(array $tables): array
{
    $remaining = [];
    foreach ($tables as $name => $table) {
        $dependencies = [];
        foreach (app_sqlite_mysql_promotion_foreign_keys($table['foreign_keys'] ?? []) as $foreignKey) {
            if ($foreignKey['referenced_table'] !== $name && isset($tables[$foreignKey['referenced_table']])) {
                $dependencies[$foreignKey['referenced_table']] = true;
            }
        }
        $remaining[$name] = $dependencies;
    }
    $ordered = [];
    while ($remaining !== []) {
        $ready = [];
        foreach ($remaining as $name => $dependencies) {
            if (array_diff_key($dependencies, array_fill_keys(array_keys($remaining), true)) === $dependencies) $ready[] = $name;
        }
        sort($ready, SORT_STRING);
        if ($ready === []) {
            $rest = array_keys($remaining);
            sort($rest, SORT_STRING);
            return ['ok' => false, 'tables' => array_merge($ordered, $rest)];
        }
        foreach ($ready as $name) {
            $ordered[] = $name;
            unset($remaining[$name]);
        }
    }
    return ['ok' => true, 'tables' => $ordered];
}

/** @param array<string,mixed> $canonical @param array<string,mixed> $source @return array{target_type:string,blockers:list<string>,warnings:list<string>} */
function app_sqlite_mysql_promotion_column_mapping(array $canonical, array $source): array
{
    $type = strtolower(trim((string) ($canonical['type'] ?? '')));
    $profile = app_sqlite_mysql_promotion_profile($source['profile'] ?? []);
    $blockers = [];
    $warnings = [];
    $target = trim((string) ($canonical['mysql_type'] ?? ''));
    if ($target === '') {
        if (preg_match('/^(int|integer|bigint)/', $type)) $target = 'BIGINT';
        elseif (preg_match('/^(bool|boolean)/', $type)) $target = 'TINYINT(1)';
        elseif (preg_match('/^(decimal|numeric)\s*\(/', $type)) $target = strtoupper($type);
        elseif (preg_match('/^(datetime|timestamp)/', $type)) $target = 'DATETIME(6)';
        elseif ($type === 'json') $target = 'JSON';
        elseif (preg_match('/^(blob|binary|varbinary)/', $type)) $target = 'LONGBLOB';
        elseif (preg_match('/^(varchar|char)\s*\(/', $type)) $target = strtoupper($type);
        elseif (in_array($type, ['text', 'string'], true)) $target = 'TEXT';
        else $blockers[] = 'canonical_metadata_incomplete';
    }
    $classes = array_values(array_unique(array_map('strtolower', is_array($profile['storage_classes'] ?? null) ? $profile['storage_classes'] : [])));
    $allowed = match (true) {
        str_starts_with($target, 'BIGINT'), str_starts_with($target, 'TINYINT') => ['integer', 'null'],
        str_starts_with($target, 'DECIMAL') => ['integer', 'real', 'text', 'null'],
        str_contains($target, 'BLOB'), str_contains($target, 'BINARY') => ['blob', 'null'],
        default => ['text', 'null'],
    };
    if (array_diff($classes, $allowed) !== []) $blockers[] = 'sqlite_dynamic_type_violation';
    if ($target === 'JSON' && (($profile['invalid_json_count'] ?? 0) > 0)) $blockers[] = 'invalid_json_value';
    if (str_starts_with($target, 'TINYINT') && (($profile['invalid_boolean_count'] ?? 0) > 0)) $blockers[] = 'sqlite_dynamic_type_violation';
    if (($profile['integer_range_violation_count'] ?? 0) > 0) $blockers[] = 'integer_range_violation';
    if (($profile['decimal_precision_violation_count'] ?? 0) > 0) $blockers[] = 'decimal_precision_violation';
    if (($profile['text_encoding_or_length_violation_count'] ?? 0) > 0) $blockers[] = 'text_encoding_or_length_violation';
    if (($profile['ambiguous_timestamp_count'] ?? 0) > 0) $blockers[] = 'ambiguous_timestamp_value';
    if (($profile['trailing_space_count'] ?? 0) > 0) $warnings[] = 'trailing_space_semantics_risk';
    $default = $canonical['default'] ?? null;
    if (is_string($default) && preg_match('/\([^)]*\)|datetime\s*\(/i', $default) === 1 && strtoupper(trim($default)) !== 'CURRENT_TIMESTAMP') {
        $blockers[] = 'unsupported_default_expression';
    }
    return ['target_type' => $target, 'blockers' => array_values(array_unique($blockers)), 'warnings' => array_values(array_unique($warnings))];
}

/** @return array<string,mixed> */
function app_sqlite_mysql_promotion_profile(mixed $profile): array
{
    if (!is_array($profile)) return [];
    ksort($profile, SORT_STRING);
    return $profile;
}

/** @return array{code:string,path:string} */
function app_sqlite_mysql_promotion_issue(string $code, string $path): array
{
    return ['code' => $code, 'path' => $path];
}

/** @param list<array{code:string,path:string}> $issues @return list<array{code:string,path:string}> */
function app_sqlite_mysql_promotion_sorted_issues(array $issues): array
{
    $unique = [];
    foreach ($issues as $issue) $unique[$issue['code'] . "\n" . $issue['path']] = $issue;
    ksort($unique, SORT_STRING);
    return array_values($unique);
}

function app_sqlite_mysql_promotion_digest(array $value): string
{
    $normalize = static function (mixed $item) use (&$normalize): mixed {
        if (!is_array($item)) return $item;
        if (array_is_list($item)) return array_map($normalize, $item);
        ksort($item, SORT_STRING);
        foreach ($item as $key => $child) $item[$key] = $normalize($child);
        return $item;
    };
    return hash('sha256', json_encode($normalize($value), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

/** @param array<string,mixed> $snapshot @return array<string,mixed> */
function app_sqlite_mysql_promotion_snapshot_for_digest(array $snapshot): array
{
    if (array_key_exists('tables', $snapshot)) {
        $snapshot['tables'] = array_values(app_sqlite_mysql_promotion_tables($snapshot['tables']));
    }
    return $snapshot;
}

function app_sqlite_mysql_promotion_contains_secret(mixed $value, string $key = ''): bool
{
    if (preg_match('/(^|_)(password|passwd|secret|token|credential|dsn)($|_)/i', $key) === 1) return true;
    if (!is_array($value)) return false;
    foreach ($value as $childKey => $child) {
        if (app_sqlite_mysql_promotion_contains_secret($child, (string) $childKey)) return true;
    }
    return false;
}
