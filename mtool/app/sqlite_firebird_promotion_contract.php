<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_promotion_manifest.php';

const APP_SQLITE_FIREBIRD_PROMOTION_CONTRACT_VERSION = 'sqlite-firebird-promotion-contract-v1';

/**
 * Build a deterministic, side-effect-free SQLite-to-Firebird promotion contract.
 *
 * The contract intentionally does not connect to SQLite or Firebird. Inputs are
 * inspected snapshots, and the output is a reviewable plan shape for the local
 * durable Firebird profile.
 *
 * @param array<string,mixed> $canonicalSnapshot
 * @param array<string,mixed> $sqliteInspection
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_sqlite_firebird_promotion_contract_build(
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

    $targetIdentity = trim((string) ($options['target_identity'] ?? 'firebird-local-profile'));
    if ($targetIdentity === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $targetIdentity) === 1 || str_contains($targetIdentity, '@')) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/target/identity');
        $targetIdentity = 'firebird-local-profile';
    }

    $canonicalTables = app_sqlite_mysql_promotion_tables($canonicalSnapshot['tables'] ?? []);
    $sourceTables = app_sqlite_mysql_promotion_tables($sqliteInspection['tables'] ?? []);
    if ($canonicalTables === []) $blockers[] = app_sqlite_mysql_promotion_issue('canonical_metadata_incomplete', '/tables');

    $loadOrder = app_sqlite_mysql_promotion_load_order($canonicalTables);
    if (!$loadOrder['ok']) $blockers[] = app_sqlite_mysql_promotion_issue('foreign_key_cycle_unsupported', '/tables');

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
        if ($primary === []) $blockers[] = app_sqlite_mysql_promotion_issue('stable_primary_key_missing', '/tables/' . $tableName . '/keys');

        $keys = app_sqlite_mysql_promotion_keys($table['keys'] ?? []);
        $foreignKeys = app_sqlite_mysql_promotion_foreign_keys($table['foreign_keys'] ?? []);
        if ($keys !== app_sqlite_mysql_promotion_keys($source['keys'] ?? [])
            || $foreignKeys !== app_sqlite_mysql_promotion_foreign_keys($source['foreign_keys'] ?? [])) {
            $blockers[] = app_sqlite_mysql_promotion_issue('sqlite_constraint_evidence_missing', '/tables/' . $tableName . '/constraints');
        }

        $columnPlans = [];
        foreach ($canonicalColumns as $columnName => $column) {
            $sourceColumn = $sourceColumns[$columnName] ?? [];
            $mapping = app_sqlite_firebird_promotion_column_mapping($column, $sourceColumn);
            foreach ($mapping['blockers'] as $code) $blockers[] = app_sqlite_mysql_promotion_issue($code, '/tables/' . $tableName . '/columns/' . $columnName);
            foreach ($mapping['warnings'] as $code) $warnings[] = app_sqlite_mysql_promotion_issue($code, '/tables/' . $tableName . '/columns/' . $columnName);
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
            'identity_strategy' => app_sqlite_firebird_promotion_identity_strategy($primary, $canonicalColumns),
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
    $contract = [
        'contract_version' => APP_SQLITE_FIREBIRD_PROMOTION_CONTRACT_VERSION,
        'ok' => $blockers === [],
        'stage' => 'preflight',
        'mutation_performed' => false,
        'source' => [
            'driver' => 'sqlite',
            'identity' => trim((string) ($sqliteInspection['source_identity'] ?? 'sqlite-source')),
            'snapshot_sha256' => app_sqlite_mysql_promotion_digest(app_sqlite_mysql_promotion_snapshot_for_digest($sqliteInspection)),
            'retain_after_promotion' => true,
        ],
        'target' => [
            'driver' => 'firebird',
            'profile' => 'local_durable_file',
            'identity' => $targetIdentity,
            'must_be_new_or_empty' => true,
            'requires_backup_restore_smoke' => true,
        ],
        'canonical_sha256' => app_sqlite_mysql_promotion_digest(app_sqlite_mysql_promotion_snapshot_for_digest($canonicalSnapshot)),
        'tables' => $tablePlans,
        'blockers' => $blockers,
        'warnings' => $warnings,
        'required_approvals' => ['source_backup', 'target_file_prepare', 'data_import_rehearsal', 'local_profile_switch'],
        'required_verification' => ['row_counts', 'primary_keys', 'row_digests', 'unique_keys', 'foreign_keys', 'json_values', 'blob_values', 'timestamp_values', 'next_ids', 'dbaccess_smoke', 'firebird_backup_restore_smoke'],
        'non_goals' => ['firebird_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover', 'automatic_source_delete'],
    ];

    $errors = app_sqlite_firebird_promotion_contract_errors($contract);
    if ($errors !== []) {
        $contract['ok'] = false;
        foreach ($errors as $error) $contract['blockers'][] = app_sqlite_mysql_promotion_issue('contract_invalid', '/' . $error);
        $contract['blockers'] = app_sqlite_mysql_promotion_sorted_issues($contract['blockers']);
    }
    return $contract;
}

/** @param array<string,mixed> $contract @return list<string> */
function app_sqlite_firebird_promotion_contract_errors(array $contract): array
{
    $errors = [];
    if (($contract['contract_version'] ?? '') !== APP_SQLITE_FIREBIRD_PROMOTION_CONTRACT_VERSION) $errors[] = 'contract_version';
    if (($contract['stage'] ?? '') !== 'preflight') $errors[] = 'stage';
    if (($contract['mutation_performed'] ?? null) !== false) $errors[] = 'mutation_performed';
    if (($contract['source']['driver'] ?? '') !== 'sqlite') $errors[] = 'source_driver';
    if (($contract['source']['retain_after_promotion'] ?? null) !== true) $errors[] = 'source_retention';
    if (($contract['target']['driver'] ?? '') !== 'firebird') $errors[] = 'target_driver';
    if (($contract['target']['profile'] ?? '') !== 'local_durable_file') $errors[] = 'target_profile';
    if (($contract['target']['must_be_new_or_empty'] ?? null) !== true) $errors[] = 'target_new_or_empty';
    if (($contract['target']['requires_backup_restore_smoke'] ?? null) !== true) $errors[] = 'backup_restore_smoke';
    foreach (['canonical_sha256', 'source.snapshot_sha256'] as $path) {
        $value = $path === 'canonical_sha256' ? ($contract['canonical_sha256'] ?? '') : ($contract['source']['snapshot_sha256'] ?? '');
        if (preg_match('/^[a-f0-9]{64}$/', (string) $value) !== 1) $errors[] = $path;
    }
    foreach (['tables', 'blockers', 'warnings', 'required_approvals', 'required_verification', 'non_goals'] as $key) {
        if (!is_array($contract[$key] ?? null)) $errors[] = $key;
    }
    if (($contract['ok'] ?? null) !== (($contract['blockers'] ?? []) === [])) $errors[] = 'ok_blocker_consistency';
    if (app_sqlite_mysql_promotion_contains_secret($contract)) $errors[] = 'secret';
    if (in_array('automatic_source_delete', $contract['non_goals'] ?? [], true) !== true) $errors[] = 'automatic_source_delete_non_goal';
    return array_values(array_unique($errors));
}

/** @param array<string,mixed> $canonical @param array<string,mixed> $source @return array{target_type:string,blockers:list<string>,warnings:list<string>} */
function app_sqlite_firebird_promotion_column_mapping(array $canonical, array $source): array
{
    $type = strtolower(trim((string) ($canonical['type'] ?? '')));
    $profile = app_sqlite_mysql_promotion_profile($source['profile'] ?? []);
    $blockers = [];
    $warnings = [];
    $target = trim((string) ($canonical['firebird_type'] ?? ''));
    if ($target === '') {
        if (preg_match('/^(int|integer|bigint)/', $type)) $target = 'BIGINT';
        elseif (preg_match('/^(bool|boolean)/', $type)) $target = 'SMALLINT';
        elseif (preg_match('/^(decimal|numeric)\s*\(/', $type)) $target = strtoupper($type);
        elseif (preg_match('/^(datetime|timestamp)/', $type)) $target = 'TIMESTAMP';
        elseif ($type === 'json') {
            $target = 'BLOB SUB_TYPE TEXT';
            $warnings[] = 'json_stored_as_text';
        } elseif (preg_match('/^(blob|binary|varbinary)/', $type)) $target = 'BLOB SUB_TYPE BINARY';
        elseif (preg_match('/^(varchar|char)\s*\(/', $type)) $target = strtoupper($type);
        elseif (in_array($type, ['text', 'string'], true)) {
            $target = 'BLOB SUB_TYPE TEXT';
            $warnings[] = 'text_columns_mapped_to_blob_sub_type_text';
        } else {
            $blockers[] = 'canonical_metadata_incomplete';
        }
    }

    $classes = array_values(array_unique(array_map('strtolower', is_array($profile['storage_classes'] ?? null) ? $profile['storage_classes'] : [])));
    $allowed = match (true) {
        str_starts_with($target, 'BIGINT'), str_starts_with($target, 'SMALLINT') => ['integer', 'null'],
        str_starts_with($target, 'DECIMAL'), str_starts_with($target, 'NUMERIC') => ['integer', 'real', 'text', 'null'],
        str_contains($target, 'BLOB SUB_TYPE BINARY') => ['blob', 'null'],
        default => ['text', 'null'],
    };
    if (array_diff($classes, $allowed) !== []) $blockers[] = 'sqlite_dynamic_type_violation';
    if ($type === 'json' && (($profile['invalid_json_count'] ?? 0) > 0)) $blockers[] = 'invalid_json_value';
    if (str_starts_with($target, 'SMALLINT') && (($profile['invalid_boolean_count'] ?? 0) > 0)) $blockers[] = 'sqlite_dynamic_type_violation';
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

/** @param list<string> $primary @param array<string,array<string,mixed>> $columns */
function app_sqlite_firebird_promotion_identity_strategy(array $primary, array $columns): string
{
    if (count($primary) !== 1) return 'none_or_composite_primary_key';
    $column = $columns[$primary[0]] ?? [];
    $type = strtolower(trim((string) ($column['type'] ?? '')));
    if (!preg_match('/^(int|integer|bigint)/', $type)) return 'none_non_integer_primary_key';
    return 'preserve_source_values_then_advance_firebird_identity_or_generator';
}
