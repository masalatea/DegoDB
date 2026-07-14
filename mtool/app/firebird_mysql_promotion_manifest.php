<?php

declare(strict_types=1);

require_once __DIR__ . '/firebird_source_inspection.php';

const APP_FIREBIRD_MYSQL_PROMOTION_MANIFEST_VERSION = 'firebird-mysql-promotion-manifest-v1';

/**
 * Build a deterministic, review-only Firebird -> MySQL/MariaDB promotion
 * manifest. Inputs are normalized snapshots; this function opens no database
 * and writes no file.
 *
 * @param array<string,mixed> $canonicalSnapshot
 * @param array<string,mixed> $firebirdInspection
 * @param array<string,mixed> $options
 * @return array<string,mixed>
 */
function app_firebird_mysql_promotion_manifest_build(
    array $canonicalSnapshot,
    array $firebirdInspection,
    array $options = [],
): array {
    $blockers = [];
    $warnings = [];
    if (app_sqlite_mysql_promotion_contains_secret($canonicalSnapshot)
        || app_sqlite_mysql_promotion_contains_secret($firebirdInspection)
        || app_sqlite_mysql_promotion_contains_secret($options)) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/');
    }
    if (app_firebird_source_inspection_contract_errors($firebirdInspection) !== [] || ($firebirdInspection['ok'] ?? false) !== true) {
        $blockers[] = app_sqlite_mysql_promotion_issue('firebird_source_inspection_not_ready', '/source');
    }

    $targetIdentity = trim((string) ($options['target_identity'] ?? 'mysql-target'));
    if ($targetIdentity === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $targetIdentity) === 1 || str_contains($targetIdentity, '@')) {
        $blockers[] = app_sqlite_mysql_promotion_issue('secret_in_artifact', '/target/identity');
        $targetIdentity = 'mysql-target';
    }

    $canonicalTables = app_sqlite_mysql_promotion_tables($canonicalSnapshot['tables'] ?? []);
    $sourceTables = app_sqlite_mysql_promotion_tables($firebirdInspection['tables'] ?? []);
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
            $blockers[] = app_sqlite_mysql_promotion_issue('firebird_constraint_evidence_missing', '/tables/' . $tableName . '/constraints');
        }

        $columnPlans = [];
        foreach ($canonicalColumns as $columnName => $column) {
            $sourceColumn = $sourceColumns[$columnName] ?? [];
            $mapping = app_sqlite_mysql_promotion_column_mapping($column, $sourceColumn);
            foreach ($mapping['blockers'] as $code) {
                $blockers[] = app_sqlite_mysql_promotion_issue(
                    $code === 'sqlite_dynamic_type_violation' ? 'firebird_value_profile_violation' : $code,
                    '/tables/' . $tableName . '/columns/' . $columnName,
                );
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
        'manifest_version' => APP_FIREBIRD_MYSQL_PROMOTION_MANIFEST_VERSION,
        'ok' => $blockers === [],
        'stage' => 'preflight',
        'mutation_performed' => false,
        'source' => [
            'driver' => 'firebird',
            'identity' => trim((string) ($firebirdInspection['source_identity'] ?? 'firebird-source')),
            'snapshot_sha256' => app_sqlite_mysql_promotion_digest(app_sqlite_mysql_promotion_snapshot_for_digest($firebirdInspection)),
            'requires_source_backup' => true,
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
        'required_approvals' => ['source_backup', 'target_schema_prepare', 'data_import', 'cutover'],
        'required_verification' => ['row_counts', 'primary_keys', 'row_digests', 'unique_keys', 'foreign_keys', 'values', 'next_ids', 'dbaccess_smoke', 'firebird_backup_restore_smoke'],
        'non_goals' => ['mysql_to_firebird', 'firebird_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover'],
    ];
    $errors = app_firebird_mysql_promotion_manifest_contract_errors($manifest);
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
function app_firebird_mysql_promotion_manifest_contract_errors(array $manifest): array
{
    $errors = [];
    if (($manifest['manifest_version'] ?? '') !== APP_FIREBIRD_MYSQL_PROMOTION_MANIFEST_VERSION) $errors[] = 'manifest_version';
    if (($manifest['stage'] ?? '') !== 'preflight') $errors[] = 'stage';
    if (($manifest['mutation_performed'] ?? null) !== false) $errors[] = 'mutation_performed';
    if (($manifest['source']['driver'] ?? '') !== 'firebird') $errors[] = 'source_driver';
    if (($manifest['target']['driver'] ?? '') !== 'mysql') $errors[] = 'target_driver';
    if (($manifest['source']['requires_source_backup'] ?? null) !== true) $errors[] = 'source_backup';
    foreach (['canonical_sha256', 'source.snapshot_sha256'] as $path) {
        $value = $path === 'canonical_sha256' ? ($manifest['canonical_sha256'] ?? '') : ($manifest['source']['snapshot_sha256'] ?? '');
        if (preg_match('/^[a-f0-9]{64}$/', (string) $value) !== 1) $errors[] = $path;
    }
    foreach (['tables', 'blockers', 'warnings', 'required_approvals', 'required_verification', 'non_goals'] as $key) {
        if (!is_array($manifest[$key] ?? null)) $errors[] = $key;
    }
    if (!in_array('firebird_backup_restore_smoke', $manifest['required_verification'] ?? [], true)) $errors[] = 'firebird_backup_restore_smoke';
    if (!in_array('firebird_to_sqlite', $manifest['non_goals'] ?? [], true)) $errors[] = 'firebird_to_sqlite_non_goal';
    if (($manifest['ok'] ?? null) !== (($manifest['blockers'] ?? []) === [])) $errors[] = 'ok_blocker_consistency';
    if (app_sqlite_mysql_promotion_contains_secret($manifest)) $errors[] = 'secret';
    return array_values(array_unique($errors));
}
