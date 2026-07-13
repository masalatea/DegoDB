<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_promotion_manifest.php';
require_once __DIR__ . '/sqlite_mysql_export.php';
require_once __DIR__ . '/sqlite_mysql_target_schema.php';

const APP_SQLITE_MYSQL_VERIFICATION_VERSION = 'sqlite-mysql-promotion-verification-v1';
const APP_SQLITE_MYSQL_DBACCESS_SMOKE_VERSION = 'sqlite-mysql-dbaccess-smoke-v1';
const APP_SQLITE_MYSQL_VERIFICATION_REQUIRED = ['row_count', 'primary_key_set', 'row_values', 'nullability', 'unique_keys', 'foreign_keys', 'json_values', 'blob_values', 'timestamp_values', 'next_ids', 'dbaccess_smoke'];

/** @param array<string,mixed> $context @param list<array<string,mixed>> $checks @return array<string,mixed> */
function app_sqlite_mysql_verification_artifact(array $context, array $checks): array
{
    $normalized = [];
    $blockers = [];
    foreach ($checks as $check) {
        if (!is_array($check)) continue;
        $key = trim((string) ($check['check_key'] ?? ''));
        if ($key === '' || isset($normalized[$key])) { $blockers[] = ['code' => 'invalid_or_duplicate_check', 'check_key' => $key]; continue; }
        $status = strtolower(trim((string) ($check['status'] ?? 'missing')));
        if (!in_array($status, ['passed', 'failed', 'missing', 'skipped', 'unsupported', 'warning'], true)) $status = 'missing';
        $normalized[$key] = ['check_key' => $key, 'required' => in_array($key, APP_SQLITE_MYSQL_VERIFICATION_REQUIRED, true), 'status' => $status, 'source' => $check['source'] ?? null, 'target' => $check['target'] ?? null, 'failure_code' => trim((string) ($check['failure_code'] ?? ''))];
    }
    foreach (APP_SQLITE_MYSQL_VERIFICATION_REQUIRED as $key) {
        if (!isset($normalized[$key])) $normalized[$key] = ['check_key' => $key, 'required' => true, 'status' => 'missing', 'source' => null, 'target' => null, 'failure_code' => 'required_check_missing'];
        if ($normalized[$key]['status'] !== 'passed') $blockers[] = ['code' => $normalized[$key]['failure_code'] !== '' ? $normalized[$key]['failure_code'] : 'required_check_not_passed', 'check_key' => $key, 'status' => $normalized[$key]['status']];
    }
    ksort($normalized, SORT_STRING);
    usort($blockers, static fn (array $a, array $b): int => [$a['check_key'] ?? '', $a['code']] <=> [$b['check_key'] ?? '', $b['code']]);
    $safeContext = ['promotion_manifest_sha256' => (string) ($context['promotion_manifest_sha256'] ?? ''), 'target_schema_sha256' => (string) ($context['target_schema_sha256'] ?? ''), 'import_checkpoint_sha256' => (string) ($context['import_checkpoint_sha256'] ?? '')];
    foreach ($safeContext as $key => $value) if (preg_match('/^[a-f0-9]{64}$/', $value) !== 1) $blockers[] = ['code' => 'invalid_context_digest', 'check_key' => $key];
    return ['verification_version' => APP_SQLITE_MYSQL_VERIFICATION_VERSION, 'cutover_ready' => $blockers === [], 'mutation_performed' => false, 'context' => $safeContext, 'checks' => array_values($normalized), 'blockers' => $blockers];
}

/** @param array<string,mixed> $manifest @return array<string,mixed> */
function app_sqlite_mysql_verification_collect_database(PDO $pdo, array $manifest): array
{
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $errors = app_sqlite_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if (!in_array($driver, ['sqlite', 'mysql'], true)) $errors[] = 'sqlite_or_mysql_required';
    if ($errors !== []) return app_sqlite_mysql_verification_collect_result(false, $driver, [], $errors);

    $ownsTransaction = !$pdo->inTransaction();
    $tables = [];
    try {
        if ($ownsTransaction) $pdo->beginTransaction();
        foreach ($manifest['tables'] as $tablePlan) {
            if (!is_array($tablePlan)) continue;
            $tables[] = app_sqlite_mysql_verification_collect_table($pdo, $driver, $tablePlan);
        }
        if ($ownsTransaction) $pdo->commit();
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        return app_sqlite_mysql_verification_collect_result(false, $driver, $tables, [$throwable->getMessage()]);
    }

    $unstableTables = array_values(array_filter($tables, static fn (array $table): bool => ($table['stable_count'] ?? false) !== true));
    return app_sqlite_mysql_verification_collect_result($unstableTables === [], $driver, $tables, $unstableTables === [] ? [] : ['verification_scan_count_changed']);
}

/** @param array<string,mixed> $tablePlan @return array<string,mixed> */
function app_sqlite_mysql_verification_collect_table(PDO $pdo, string $driver, array $tablePlan): array
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    $pk = array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : []));
    $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
    if (!app_sqlite_mysql_target_identifier_valid($tableName) || $pk === [] || $columns === []) {
        throw new RuntimeException('invalid_verification_table_plan:' . $tableName);
    }
    $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
    foreach (array_merge($pk, $columnNames) as $identifier) {
        if (!app_sqlite_mysql_target_identifier_valid($identifier)) throw new RuntimeException('invalid_verification_identifier:' . $tableName . '.' . $identifier);
    }

    $quote = $driver === 'mysql' ? 'app_sqlite_mysql_target_quote_identifier' : 'app_sqlite_mysql_export_quote_identifier';
    $quotedTable = $quote($tableName);
    $selectColumns = implode(', ', array_map($quote, $columnNames));
    $order = implode(', ', array_map($quote, $pk));
    $countStart = (int) $pdo->query('SELECT COUNT(*) FROM ' . $quotedTable)->fetchColumn();
    $statement = $pdo->query('SELECT ' . $selectColumns . ' FROM ' . $quotedTable . ' ORDER BY ' . $order);
    if (!$statement instanceof PDOStatement) throw new RuntimeException('verification_select_failed:' . $tableName);

    $pkHash = hash_init('sha256');
    $rowHash = hash_init('sha256');
    $valueHashes = app_sqlite_mysql_verification_value_hashes($columns);
    $scanned = 0;
    while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
        $encoded = [];
        foreach ($columns as $column) {
            $name = (string) $column['name'];
            $encoded[$name] = app_sqlite_mysql_verification_convert_value($row[$name] ?? null, (string) ($column['target_type'] ?? ''), $tableName . '.' . $name);
        }
        $pkTuple = [];
        foreach ($pk as $column) $pkTuple[$column] = $encoded[$column] ?? null;
        hash_update($pkHash, json_encode($pkTuple, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
        hash_update($rowHash, json_encode($encoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
        app_sqlite_mysql_verification_update_value_hashes($valueHashes, $pkTuple, $encoded);
        $scanned++;
    }
    $countEnd = (int) $pdo->query('SELECT COUNT(*) FROM ' . $quotedTable)->fetchColumn();

    return [
        'name' => $tableName,
        'row_count' => $scanned,
        'count_start' => $countStart,
        'count_end' => $countEnd,
        'stable_count' => $countStart === $countEnd && $countStart === $scanned,
        'primary_key_sha256' => hash_final($pkHash),
        'rows_sha256' => hash_final($rowHash),
        'json_values' => app_sqlite_mysql_verification_finalize_value_hash($valueHashes, 'json_values'),
        'blob_values' => app_sqlite_mysql_verification_finalize_value_hash($valueHashes, 'blob_values'),
        'timestamp_values' => app_sqlite_mysql_verification_finalize_value_hash($valueHashes, 'timestamp_values'),
        'nullability' => app_sqlite_mysql_verification_collect_nullability($pdo, $driver, $tablePlan),
        'unique_keys' => app_sqlite_mysql_verification_collect_unique_keys($pdo, $driver, $tablePlan),
        'foreign_keys' => app_sqlite_mysql_verification_collect_foreign_keys($pdo, $driver, $tablePlan),
        'foreign_key_violation_count' => app_sqlite_mysql_verification_foreign_key_violation_count($pdo, $driver, $tablePlan),
        'next_id' => app_sqlite_mysql_verification_collect_next_id($pdo, $driver, $tablePlan),
    ];
}

function app_sqlite_mysql_verification_convert_value(mixed $value, string $targetType, string $path): mixed
{
    if ($value === null) return null;
    $type = strtoupper(trim($targetType));
    if (str_contains($type, 'BLOB') || str_contains($type, 'BINARY')) {
        if (!is_string($value)) throw new RuntimeException('blob_conversion_failed:' . $path);
        return ['encoding' => 'sha256', 'byte_length' => strlen($value), 'sha256' => hash('sha256', $value)];
    }
    return app_sqlite_mysql_export_convert_value($value, $targetType, $path);
}

/** @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @return list<array<string,mixed>> */
function app_sqlite_mysql_verification_compare_core(array $sourceEvidence, array $targetEvidence): array
{
    $sourceTables = app_sqlite_mysql_verification_tables_by_name($sourceEvidence['tables'] ?? []);
    $targetTables = app_sqlite_mysql_verification_tables_by_name($targetEvidence['tables'] ?? []);
    $tableNames = array_values(array_unique(array_merge(array_keys($sourceTables), array_keys($targetTables))));
    sort($tableNames, SORT_STRING);

    return [
        app_sqlite_mysql_verification_compare_digest_check('row_count', $sourceEvidence, $targetEvidence, $tableNames, 'row_count', 'row_count_mismatch'),
        app_sqlite_mysql_verification_compare_digest_check('primary_key_set', $sourceEvidence, $targetEvidence, $tableNames, 'primary_key_sha256', 'primary_key_set_mismatch'),
        app_sqlite_mysql_verification_compare_digest_check('row_values', $sourceEvidence, $targetEvidence, $tableNames, 'rows_sha256', 'row_values_mismatch'),
    ];
}

/** @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @param array<string,mixed> $manifest @return list<array<string,mixed>> */
function app_sqlite_mysql_verification_compare_schema(array $sourceEvidence, array $targetEvidence, array $manifest): array
{
    $expected = app_sqlite_mysql_verification_expected_schema_summary($manifest);
    return [
        app_sqlite_mysql_verification_compare_expected_check('nullability', $sourceEvidence, $targetEvidence, $expected, 'nullability', 'nullability_mismatch'),
        app_sqlite_mysql_verification_compare_expected_check('unique_keys', $sourceEvidence, $targetEvidence, $expected, 'unique_keys', 'unique_keys_mismatch'),
        app_sqlite_mysql_verification_compare_expected_check('foreign_keys', $sourceEvidence, $targetEvidence, $expected, 'foreign_keys', 'foreign_keys_mismatch'),
    ];
}

/** @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @return list<array<string,mixed>> */
function app_sqlite_mysql_verification_compare_value_classes(array $sourceEvidence, array $targetEvidence): array
{
    $sourceTables = app_sqlite_mysql_verification_tables_by_name($sourceEvidence['tables'] ?? []);
    $targetTables = app_sqlite_mysql_verification_tables_by_name($targetEvidence['tables'] ?? []);
    $tableNames = array_values(array_unique(array_merge(array_keys($sourceTables), array_keys($targetTables))));
    sort($tableNames, SORT_STRING);

    return [
        app_sqlite_mysql_verification_compare_digest_check('json_values', $sourceEvidence, $targetEvidence, $tableNames, 'json_values', 'json_values_mismatch'),
        app_sqlite_mysql_verification_compare_digest_check('blob_values', $sourceEvidence, $targetEvidence, $tableNames, 'blob_values', 'blob_values_mismatch'),
        app_sqlite_mysql_verification_compare_digest_check('timestamp_values', $sourceEvidence, $targetEvidence, $tableNames, 'timestamp_values', 'timestamp_values_mismatch'),
    ];
}

/** @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @return list<array<string,mixed>> */
function app_sqlite_mysql_verification_compare_next_ids(array $sourceEvidence, array $targetEvidence): array
{
    $source = app_sqlite_mysql_verification_next_id_summary($sourceEvidence);
    $target = app_sqlite_mysql_verification_next_id_summary($targetEvidence);
    $ok = ($sourceEvidence['ok'] ?? false) === true
        && ($targetEvidence['ok'] ?? false) === true
        && ($source['digest_sha256'] ?? '') === ($target['digest_sha256'] ?? '')
        && app_sqlite_mysql_verification_next_id_sequences_safe($source)
        && app_sqlite_mysql_verification_next_id_sequences_safe($target);
    return [[
        'check_key' => 'next_ids',
        'status' => $ok ? 'passed' : 'failed',
        'source' => $source,
        'target' => $target,
        'failure_code' => $ok ? '' : 'next_ids_mismatch',
    ]];
}

/** @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @param array<string,mixed> $manifest @param array<string,mixed> $dbaccessSmoke @return list<array<string,mixed>> */
function app_sqlite_mysql_verification_checks(array $sourceEvidence, array $targetEvidence, array $manifest, array $dbaccessSmoke): array
{
    return array_merge(
        app_sqlite_mysql_verification_compare_core($sourceEvidence, $targetEvidence),
        app_sqlite_mysql_verification_compare_schema($sourceEvidence, $targetEvidence, $manifest),
        app_sqlite_mysql_verification_compare_value_classes($sourceEvidence, $targetEvidence),
        app_sqlite_mysql_verification_compare_next_ids($sourceEvidence, $targetEvidence),
        [app_sqlite_mysql_verification_dbaccess_smoke_check($dbaccessSmoke)],
    );
}

/** @param array<string,mixed> $context @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @param array<string,mixed> $manifest @param array<string,mixed> $dbaccessSmoke @return array<string,mixed> */
function app_sqlite_mysql_verification_build_artifact(array $context, array $sourceEvidence, array $targetEvidence, array $manifest, array $dbaccessSmoke): array
{
    return app_sqlite_mysql_verification_artifact($context, app_sqlite_mysql_verification_checks($sourceEvidence, $targetEvidence, $manifest, $dbaccessSmoke));
}

/** @param array<string,mixed> $smoke @return array<string,mixed> */
function app_sqlite_mysql_verification_dbaccess_smoke_check(array $smoke): array
{
    $operations = is_array($smoke['operations'] ?? null) ? $smoke['operations'] : [];
    $safeOperations = [];
    $allPassed = $operations !== [];
    foreach ($operations as $operation) {
        if (!is_array($operation)) {
            $allPassed = false;
            continue;
        }
        $status = strtolower((string) ($operation['status'] ?? ''));
        $safeOperations[] = [
            'name' => (string) ($operation['name'] ?? ''),
            'status' => $status,
            'row_count' => array_key_exists('row_count', $operation) ? (int) $operation['row_count'] : null,
            'result_sha256' => (string) ($operation['result_sha256'] ?? ''),
        ];
        if ($status !== 'passed' || preg_match('/^[a-f0-9]{64}$/', (string) ($operation['result_sha256'] ?? '')) !== 1) $allPassed = false;
    }
    usort($safeOperations, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    $source = [
        'smoke_version' => (string) ($smoke['smoke_version'] ?? ''),
        'target_driver' => (string) ($smoke['target_driver'] ?? ''),
        'mutation_performed' => ($smoke['mutation_performed'] ?? true) === true,
        'operations' => $safeOperations,
        'digest_sha256' => app_sqlite_mysql_verification_hash($safeOperations),
    ];
    $ok = ($smoke['smoke_version'] ?? '') === APP_SQLITE_MYSQL_DBACCESS_SMOKE_VERSION
        && ($smoke['ok'] ?? false) === true
        && ($smoke['target_driver'] ?? '') === 'mysql'
        && ($smoke['mutation_performed'] ?? true) === false
        && $allPassed
        && !app_sqlite_mysql_promotion_contains_secret($smoke);
    return [
        'check_key' => 'dbaccess_smoke',
        'status' => $ok ? 'passed' : 'failed',
        'source' => $source,
        'target' => null,
        'failure_code' => $ok ? '' : 'dbaccess_smoke_failed',
    ];
}

/** @param list<array<string,mixed>> $operations @return array<string,mixed> */
function app_sqlite_mysql_verification_dbaccess_smoke_artifact(array $operations, bool $ok = true): array
{
    $safeOperations = [];
    foreach ($operations as $operation) {
        if (!is_array($operation)) continue;
        $name = (string) ($operation['name'] ?? '');
        $rows = $operation['rows'] ?? [];
        $safeOperations[] = [
            'name' => $name,
            'status' => strtolower((string) ($operation['status'] ?? 'passed')),
            'row_count' => is_array($rows) ? count($rows) : (int) ($operation['row_count'] ?? 0),
            'result_sha256' => app_sqlite_mysql_verification_hash($rows),
        ];
    }
    usort($safeOperations, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    return [
        'smoke_version' => APP_SQLITE_MYSQL_DBACCESS_SMOKE_VERSION,
        'ok' => $ok,
        'target_driver' => 'mysql',
        'mutation_performed' => false,
        'operations' => $safeOperations,
    ];
}

/** @param list<string> $tableNames @return array<string,mixed> */
function app_sqlite_mysql_verification_compare_digest_check(string $checkKey, array $sourceEvidence, array $targetEvidence, array $tableNames, string $field, string $failureCode): array
{
    $source = app_sqlite_mysql_verification_digest_summary($sourceEvidence, $tableNames, $field);
    $target = app_sqlite_mysql_verification_digest_summary($targetEvidence, $tableNames, $field);
    $ok = ($sourceEvidence['ok'] ?? false) === true
        && ($targetEvidence['ok'] ?? false) === true
        && ($source['digest_sha256'] ?? '') === ($target['digest_sha256'] ?? '')
        && ($source['tables'] ?? []) === ($target['tables'] ?? [])
        && app_sqlite_mysql_verification_summary_stable($source)
        && app_sqlite_mysql_verification_summary_stable($target);
    return [
        'check_key' => $checkKey,
        'status' => $ok ? 'passed' : 'failed',
        'source' => $source,
        'target' => $target,
        'failure_code' => $ok ? '' : $failureCode,
    ];
}

/** @param array<string,mixed> $expected @return array<string,mixed> */
function app_sqlite_mysql_verification_compare_expected_check(string $checkKey, array $sourceEvidence, array $targetEvidence, array $expected, string $field, string $failureCode): array
{
    $source = app_sqlite_mysql_verification_schema_summary($sourceEvidence, $field);
    $target = app_sqlite_mysql_verification_schema_summary($targetEvidence, $field);
    $expectedPart = ['digest_sha256' => (string) ($expected[$field . '_sha256'] ?? ''), 'tables' => $expected[$field] ?? []];
    $ok = ($sourceEvidence['ok'] ?? false) === true
        && ($targetEvidence['ok'] ?? false) === true
        && ($source['digest_sha256'] ?? '') === ($expectedPart['digest_sha256'] ?? '')
        && ($target['digest_sha256'] ?? '') === ($expectedPart['digest_sha256'] ?? '')
        && ($source['tables'] ?? []) === ($expectedPart['tables'] ?? [])
        && ($target['tables'] ?? []) === ($expectedPart['tables'] ?? []);
    if ($checkKey === 'foreign_keys') {
        $ok = $ok
            && app_sqlite_mysql_verification_schema_fk_violations($sourceEvidence) === 0
            && app_sqlite_mysql_verification_schema_fk_violations($targetEvidence) === 0;
    }
    return [
        'check_key' => $checkKey,
        'status' => $ok ? 'passed' : 'failed',
        'source' => $source,
        'target' => $target,
        'expected' => $expectedPart,
        'failure_code' => $ok ? '' : $failureCode,
    ];
}

/** @param mixed $tables @return array<string,array<string,mixed>> */
function app_sqlite_mysql_verification_tables_by_name(mixed $tables): array
{
    $result = [];
    if (!is_array($tables)) return $result;
    foreach ($tables as $table) {
        if (is_array($table)) $result[(string) ($table['name'] ?? '')] = $table;
    }
    ksort($result, SORT_STRING);
    return $result;
}

/** @param list<string> $tableNames @return array<string,mixed> */
function app_sqlite_mysql_verification_digest_summary(array $evidence, array $tableNames, string $field): array
{
    $tables = app_sqlite_mysql_verification_tables_by_name($evidence['tables'] ?? []);
    $hash = hash_init('sha256');
    $items = [];
    foreach ($tableNames as $tableName) {
        $value = $tables[$tableName][$field] ?? null;
        $stable = (bool) ($tables[$tableName]['stable_count'] ?? false);
        $item = ['name' => $tableName, $field => $value, 'stable_count' => $stable];
        $items[] = $item;
        hash_update($hash, json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
    }
    return ['driver' => (string) ($evidence['driver'] ?? ''), 'digest_sha256' => hash_final($hash), 'tables' => $items];
}

/** @param array<string,mixed> $evidence @return array<string,mixed> */
function app_sqlite_mysql_verification_schema_summary(array $evidence, string $field): array
{
    $tables = [];
    foreach (app_sqlite_mysql_verification_tables_by_name($evidence['tables'] ?? []) as $tableName => $table) {
        $tables[] = ['name' => $tableName, $field => $table[$field] ?? []];
    }
    return [
        'driver' => (string) ($evidence['driver'] ?? ''),
        'digest_sha256' => app_sqlite_mysql_verification_hash($tables),
        'tables' => $tables,
        'foreign_key_violation_count' => $field === 'foreign_keys' ? app_sqlite_mysql_verification_schema_fk_violations($evidence) : null,
    ];
}

/** @param array<string,mixed> $evidence @return array<string,mixed> */
function app_sqlite_mysql_verification_next_id_summary(array $evidence): array
{
    $tables = [];
    foreach (app_sqlite_mysql_verification_tables_by_name($evidence['tables'] ?? []) as $tableName => $table) {
        $next = is_array($table['next_id'] ?? null) ? $table['next_id'] : [];
        $tables[] = [
            'name' => $tableName,
            'supported' => ($next['supported'] ?? false) === true,
            'primary_key' => $next['primary_key'] ?? [],
            'max_primary_key' => $next['max_primary_key'] ?? null,
            'required_next_id' => $next['required_next_id'] ?? null,
            'db_sequence_owner' => (string) ($next['db_sequence_owner'] ?? 'none'),
            'db_next_id' => $next['db_next_id'] ?? null,
            'sequence_safe' => ($next['sequence_safe'] ?? false) === true,
        ];
    }
    return ['driver' => (string) ($evidence['driver'] ?? ''), 'digest_sha256' => app_sqlite_mysql_verification_hash($tables), 'tables' => $tables];
}

/** @param array<string,mixed> $manifest @return array<string,mixed> */
function app_sqlite_mysql_verification_expected_schema_summary(array $manifest): array
{
    $nullability = [];
    $uniqueKeys = [];
    $foreignKeys = [];
    foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $table) {
        if (!is_array($table)) continue;
        $tableName = (string) ($table['name'] ?? '');
        $nullable = [];
        foreach (is_array($table['columns'] ?? null) ? $table['columns'] : [] as $column) {
            if (!is_array($column)) continue;
            $nullable[] = ['name' => (string) ($column['name'] ?? ''), 'nullable' => ($column['nullable'] ?? false) === true];
        }
        usort($nullable, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
        $nullability[] = ['name' => $tableName, 'nullability' => $nullable];

        $keys = [];
        foreach (is_array($table['keys'] ?? null) ? $table['keys'] : [] as $key) {
            if (!is_array($key)) continue;
            $kind = strtolower((string) ($key['kind'] ?? ''));
            if (!in_array($kind, ['primary', 'unique'], true)) continue;
            $keys[] = ['kind' => $kind, 'columns' => array_values(array_map('strval', is_array($key['columns'] ?? null) ? $key['columns'] : []))];
        }
        $uniqueKeys[] = ['name' => $tableName, 'unique_keys' => app_sqlite_mysql_verification_sort_shapes($keys)];

        $fks = [];
        foreach (is_array($table['foreign_keys'] ?? null) ? $table['foreign_keys'] : [] as $foreignKey) {
            if (!is_array($foreignKey)) continue;
            $fks[] = [
                'columns' => array_values(array_map('strval', is_array($foreignKey['columns'] ?? null) ? $foreignKey['columns'] : [])),
                'referenced_table' => (string) ($foreignKey['referenced_table'] ?? ''),
                'referenced_columns' => array_values(array_map('strval', is_array($foreignKey['referenced_columns'] ?? null) ? $foreignKey['referenced_columns'] : [])),
            ];
        }
        $foreignKeys[] = ['name' => $tableName, 'foreign_keys' => app_sqlite_mysql_verification_sort_shapes($fks)];
    }
    usort($nullability, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    usort($uniqueKeys, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    usort($foreignKeys, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    return [
        'nullability' => $nullability,
        'nullability_sha256' => app_sqlite_mysql_verification_hash($nullability),
        'unique_keys' => $uniqueKeys,
        'unique_keys_sha256' => app_sqlite_mysql_verification_hash($uniqueKeys),
        'foreign_keys' => $foreignKeys,
        'foreign_keys_sha256' => app_sqlite_mysql_verification_hash($foreignKeys),
    ];
}

/** @param array<string,mixed> $summary */
function app_sqlite_mysql_verification_summary_stable(array $summary): bool
{
    foreach (is_array($summary['tables'] ?? null) ? $summary['tables'] : [] as $table) {
        if (!is_array($table) || ($table['stable_count'] ?? false) !== true) return false;
    }
    return true;
}

/** @param array<string,mixed> $tablePlan @return list<array{name:string,nullable:bool}> */
function app_sqlite_mysql_verification_collect_nullability(PDO $pdo, string $driver, array $tablePlan): array
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    $primary = array_flip(array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : [])));
    if ($driver === 'sqlite') {
        $rows = $pdo->query('PRAGMA table_info(' . app_sqlite_mysql_export_quote_identifier($tableName) . ')')->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $name = (string) ($row['name'] ?? '');
            $result[] = ['name' => $name, 'nullable' => !isset($primary[$name]) && (int) ($row['notnull'] ?? 0) === 0];
        }
        usort($result, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
        return $result;
    }
    $statement = $pdo->prepare('SELECT COLUMN_NAME, IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION');
    $statement->execute([$tableName]);
    $result = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = (string) ($row['COLUMN_NAME'] ?? '');
        $result[] = ['name' => $name, 'nullable' => strtoupper((string) ($row['IS_NULLABLE'] ?? 'YES')) === 'YES'];
    }
    usort($result, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    return $result;
}

/** @param array<string,mixed> $tablePlan @return list<array{kind:string,columns:list<string>}> */
function app_sqlite_mysql_verification_collect_unique_keys(PDO $pdo, string $driver, array $tablePlan): array
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    if ($driver === 'sqlite') {
        $keys = [];
        $primary = app_sqlite_mysql_verification_sqlite_primary_key_columns($pdo, $tableName);
        if ($primary !== []) $keys[] = ['kind' => 'primary', 'columns' => $primary];
        $indexes = $pdo->query('PRAGMA index_list(' . app_sqlite_mysql_export_quote_identifier($tableName) . ')')->fetchAll(PDO::FETCH_ASSOC);
        foreach (is_array($indexes) ? $indexes : [] as $index) {
            if ((int) ($index['unique'] ?? 0) !== 1 || (string) ($index['origin'] ?? '') === 'pk') continue;
            $columns = app_sqlite_mysql_verification_sqlite_index_columns($pdo, (string) ($index['name'] ?? ''));
            if ($columns !== []) $keys[] = ['kind' => 'unique', 'columns' => $columns];
        }
        return app_sqlite_mysql_verification_sort_shapes($keys);
    }
    $statement = $pdo->prepare('SELECT INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND NON_UNIQUE = 0 ORDER BY INDEX_NAME, SEQ_IN_INDEX');
    $statement->execute([$tableName]);
    $grouped = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = (string) ($row['INDEX_NAME'] ?? '');
        $grouped[$name][] = (string) ($row['COLUMN_NAME'] ?? '');
    }
    $keys = [];
    foreach ($grouped as $name => $columns) $keys[] = ['kind' => strtoupper($name) === 'PRIMARY' ? 'primary' : 'unique', 'columns' => array_values($columns)];
    return app_sqlite_mysql_verification_sort_shapes($keys);
}

/** @param array<string,mixed> $tablePlan @return list<array{columns:list<string>,referenced_table:string,referenced_columns:list<string>}> */
function app_sqlite_mysql_verification_collect_foreign_keys(PDO $pdo, string $driver, array $tablePlan): array
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    if ($driver === 'sqlite') {
        $rows = $pdo->query('PRAGMA foreign_key_list(' . app_sqlite_mysql_export_quote_identifier($tableName) . ')')->fetchAll(PDO::FETCH_ASSOC);
        $grouped = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $id = (string) ($row['id'] ?? '');
            $grouped[$id]['referenced_table'] = (string) ($row['table'] ?? '');
            $grouped[$id]['columns'][(int) ($row['seq'] ?? 0)] = (string) ($row['from'] ?? '');
            $grouped[$id]['referenced_columns'][(int) ($row['seq'] ?? 0)] = (string) ($row['to'] ?? '');
        }
        $result = [];
        foreach ($grouped as $foreignKey) {
            ksort($foreignKey['columns'], SORT_NUMERIC);
            ksort($foreignKey['referenced_columns'], SORT_NUMERIC);
            $result[] = ['columns' => array_values($foreignKey['columns']), 'referenced_table' => $foreignKey['referenced_table'], 'referenced_columns' => array_values($foreignKey['referenced_columns'])];
        }
        return app_sqlite_mysql_verification_sort_shapes($result);
    }
    $statement = $pdo->prepare('SELECT CONSTRAINT_NAME, ORDINAL_POSITION, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY CONSTRAINT_NAME, ORDINAL_POSITION');
    $statement->execute([$tableName]);
    $grouped = [];
    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = (string) ($row['CONSTRAINT_NAME'] ?? '');
        $grouped[$name]['referenced_table'] = (string) ($row['REFERENCED_TABLE_NAME'] ?? '');
        $grouped[$name]['columns'][] = (string) ($row['COLUMN_NAME'] ?? '');
        $grouped[$name]['referenced_columns'][] = (string) ($row['REFERENCED_COLUMN_NAME'] ?? '');
    }
    $result = [];
    foreach ($grouped as $foreignKey) $result[] = ['columns' => array_values($foreignKey['columns'] ?? []), 'referenced_table' => $foreignKey['referenced_table'], 'referenced_columns' => array_values($foreignKey['referenced_columns'] ?? [])];
    return app_sqlite_mysql_verification_sort_shapes($result);
}

/** @param array<string,mixed> $tablePlan */
function app_sqlite_mysql_verification_foreign_key_violation_count(PDO $pdo, string $driver, array $tablePlan): int
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    $quote = $driver === 'mysql' ? 'app_sqlite_mysql_target_quote_identifier' : 'app_sqlite_mysql_export_quote_identifier';
    $count = 0;
    foreach (is_array($tablePlan['foreign_keys'] ?? null) ? $tablePlan['foreign_keys'] : [] as $foreignKey) {
        if (!is_array($foreignKey)) continue;
        $columns = array_values(array_map('strval', is_array($foreignKey['columns'] ?? null) ? $foreignKey['columns'] : []));
        $referencedColumns = array_values(array_map('strval', is_array($foreignKey['referenced_columns'] ?? null) ? $foreignKey['referenced_columns'] : []));
        $referencedTable = (string) ($foreignKey['referenced_table'] ?? '');
        if ($columns === [] || count($columns) !== count($referencedColumns)) continue;
        $join = [];
        $notNull = [];
        foreach ($columns as $index => $column) {
            $join[] = 'c.' . $quote($column) . ' = p.' . $quote($referencedColumns[$index]);
            $notNull[] = 'c.' . $quote($column) . ' IS NOT NULL';
        }
        $sql = 'SELECT COUNT(*) FROM ' . $quote($tableName) . ' c LEFT JOIN ' . $quote($referencedTable) . ' p ON ' . implode(' AND ', $join)
            . ' WHERE ' . implode(' AND ', $notNull) . ' AND p.' . $quote($referencedColumns[0]) . ' IS NULL';
        $count += (int) $pdo->query($sql)->fetchColumn();
    }
    return $count;
}

/** @return list<string> */
function app_sqlite_mysql_verification_sqlite_primary_key_columns(PDO $pdo, string $tableName): array
{
    $rows = $pdo->query('PRAGMA table_info(' . app_sqlite_mysql_export_quote_identifier($tableName) . ')')->fetchAll(PDO::FETCH_ASSOC);
    $columns = [];
    foreach (is_array($rows) ? $rows : [] as $row) {
        $pk = (int) ($row['pk'] ?? 0);
        if ($pk > 0) $columns[$pk] = (string) ($row['name'] ?? '');
    }
    ksort($columns, SORT_NUMERIC);
    return array_values($columns);
}

/** @return list<string> */
function app_sqlite_mysql_verification_sqlite_index_columns(PDO $pdo, string $indexName): array
{
    if (!app_sqlite_mysql_target_identifier_valid($indexName)) return [];
    $rows = $pdo->query('PRAGMA index_info(' . app_sqlite_mysql_export_quote_identifier($indexName) . ')')->fetchAll(PDO::FETCH_ASSOC);
    $columns = [];
    foreach (is_array($rows) ? $rows : [] as $row) $columns[(int) ($row['seqno'] ?? 0)] = (string) ($row['name'] ?? '');
    ksort($columns, SORT_NUMERIC);
    return array_values($columns);
}

/** @param list<array<string,mixed>> $shapes @return list<array<string,mixed>> */
function app_sqlite_mysql_verification_sort_shapes(array $shapes): array
{
    usort($shapes, static function (array $a, array $b): int {
        return json_encode($a, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            <=> json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    });
    return $shapes;
}

/** @param mixed $value */
function app_sqlite_mysql_verification_hash($value): string
{
    return hash('sha256', json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
}

/** @param array<string,mixed> $evidence */
function app_sqlite_mysql_verification_schema_fk_violations(array $evidence): int
{
    $count = 0;
    foreach (app_sqlite_mysql_verification_tables_by_name($evidence['tables'] ?? []) as $table) $count += (int) ($table['foreign_key_violation_count'] ?? 0);
    return $count;
}

/** @param array<string,mixed> $tablePlan @return array<string,mixed> */
function app_sqlite_mysql_verification_collect_next_id(PDO $pdo, string $driver, array $tablePlan): array
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    $primary = array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : []));
    if (count($primary) !== 1) return app_sqlite_mysql_verification_next_id_result($primary, false, null, null, 'none', null, true, 'composite_primary_key');
    $primaryColumn = $primary[0];
    $columnPlan = null;
    foreach (is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [] as $column) {
        if (is_array($column) && (string) ($column['name'] ?? '') === $primaryColumn) $columnPlan = $column;
    }
    $type = strtoupper(trim((string) ($columnPlan['target_type'] ?? '')));
    if (!in_array($type, ['BIGINT', 'TINYINT(1)'], true)) return app_sqlite_mysql_verification_next_id_result($primary, false, null, null, 'none', null, true, 'non_integer_primary_key');

    $quote = $driver === 'mysql' ? 'app_sqlite_mysql_target_quote_identifier' : 'app_sqlite_mysql_export_quote_identifier';
    $max = $pdo->query('SELECT MAX(' . $quote($primaryColumn) . ') FROM ' . $quote($tableName))->fetchColumn();
    $maxString = $max === null || $max === false ? null : (string) $max;
    $required = $maxString === null ? '1' : app_sqlite_mysql_verification_decimal_increment($maxString);
    $owner = 'none';
    $dbNext = null;
    if ($driver === 'sqlite') {
        $sequence = app_sqlite_mysql_verification_sqlite_sequence_value($pdo, $tableName);
        if ($sequence !== null) {
            $owner = 'sqlite_sequence';
            $dbNext = app_sqlite_mysql_verification_decimal_increment($sequence);
        }
    } else {
        $auto = app_sqlite_mysql_verification_mysql_auto_increment($pdo, $tableName);
        if (($auto['column'] ?? '') === $primaryColumn) {
            $owner = 'mysql_auto_increment';
            $dbNext = $auto['next_id'];
        }
    }
    $sequenceSafe = $dbNext === null || app_sqlite_mysql_verification_decimal_compare((string) $dbNext, (string) $required) >= 0;
    return app_sqlite_mysql_verification_next_id_result($primary, true, $maxString, $required, $owner, $dbNext, $sequenceSafe, '');
}

/** @param list<string> $primary @return array<string,mixed> */
function app_sqlite_mysql_verification_next_id_result(array $primary, bool $supported, ?string $max, ?string $required, string $owner, ?string $dbNext, bool $safe, string $reason): array
{
    return [
        'supported' => $supported,
        'primary_key' => $primary,
        'max_primary_key' => $max,
        'required_next_id' => $required,
        'db_sequence_owner' => $owner,
        'db_next_id' => $dbNext,
        'sequence_safe' => $safe,
        'unsupported_reason' => $reason,
    ];
}

function app_sqlite_mysql_verification_sqlite_sequence_value(PDO $pdo, string $tableName): ?string
{
    $exists = $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = 'sqlite_sequence'")->fetchColumn();
    if ((int) $exists !== 1) return null;
    $statement = $pdo->prepare('SELECT seq FROM sqlite_sequence WHERE name = ?');
    $statement->execute([$tableName]);
    $value = $statement->fetchColumn();
    return $value === false || $value === null ? null : (string) $value;
}

/** @return array{column:string,next_id:?string} */
function app_sqlite_mysql_verification_mysql_auto_increment(PDO $pdo, string $tableName): array
{
    $statement = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND EXTRA LIKE '%auto_increment%' ORDER BY ORDINAL_POSITION LIMIT 1");
    $statement->execute([$tableName]);
    $column = $statement->fetchColumn();
    if ($column === false || $column === null) return ['column' => '', 'next_id' => null];
    $next = $pdo->prepare('SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $next->execute([$tableName]);
    $value = $next->fetchColumn();
    return ['column' => (string) $column, 'next_id' => $value === false || $value === null ? null : (string) $value];
}

function app_sqlite_mysql_verification_next_id_sequences_safe(array $summary): bool
{
    foreach (is_array($summary['tables'] ?? null) ? $summary['tables'] : [] as $table) {
        if (!is_array($table) || ($table['supported'] ?? false) !== true || ($table['sequence_safe'] ?? false) !== true) return false;
    }
    return true;
}

function app_sqlite_mysql_verification_decimal_increment(string $value): string
{
    if (preg_match('/^-?(0|[1-9][0-9]*)$/', $value) !== 1) throw new RuntimeException('invalid_integer_value:' . $value);
    if (str_starts_with($value, '-')) {
        $absolute = substr($value, 1);
        if ($absolute === '1') return '0';
        return '-' . app_sqlite_mysql_verification_decimal_decrement_positive($absolute);
    }
    return app_sqlite_mysql_verification_decimal_increment_positive($value);
}

function app_sqlite_mysql_verification_decimal_increment_positive(string $value): string
{
    $carry = 1;
    $digits = str_split($value);
    for ($index = count($digits) - 1; $index >= 0; $index--) {
        $next = (int) $digits[$index] + $carry;
        $digits[$index] = (string) ($next % 10);
        $carry = intdiv($next, 10);
        if ($carry === 0) break;
    }
    if ($carry > 0) array_unshift($digits, (string) $carry);
    return ltrim(implode('', $digits), '0') ?: '0';
}

function app_sqlite_mysql_verification_decimal_decrement_positive(string $value): string
{
    if ($value === '0') return '-1';
    $digits = str_split($value);
    for ($index = count($digits) - 1; $index >= 0; $index--) {
        if ($digits[$index] !== '0') {
            $digits[$index] = (string) ((int) $digits[$index] - 1);
            break;
        }
        $digits[$index] = '9';
    }
    return ltrim(implode('', $digits), '0') ?: '0';
}

function app_sqlite_mysql_verification_decimal_compare(string $left, string $right): int
{
    if (preg_match('/^-?(0|[1-9][0-9]*)$/', $left) !== 1 || preg_match('/^-?(0|[1-9][0-9]*)$/', $right) !== 1) throw new RuntimeException('invalid_integer_compare');
    $leftNegative = str_starts_with($left, '-');
    $rightNegative = str_starts_with($right, '-');
    if ($leftNegative !== $rightNegative) return $leftNegative ? -1 : 1;
    $leftAbs = ltrim($leftNegative ? substr($left, 1) : $left, '0') ?: '0';
    $rightAbs = ltrim($rightNegative ? substr($right, 1) : $right, '0') ?: '0';
    $cmp = strlen($leftAbs) <=> strlen($rightAbs);
    if ($cmp === 0) $cmp = $leftAbs <=> $rightAbs;
    return $leftNegative ? -$cmp : $cmp;
}

/** @param list<array<string,mixed>> $columns @return array<string,array{columns:list<string>,hash:HashContext}> */
function app_sqlite_mysql_verification_value_hashes(array $columns): array
{
    $classes = [
        'json_values' => ['columns' => [], 'hash' => hash_init('sha256')],
        'blob_values' => ['columns' => [], 'hash' => hash_init('sha256')],
        'timestamp_values' => ['columns' => [], 'hash' => hash_init('sha256')],
    ];
    foreach ($columns as $column) {
        $name = (string) ($column['name'] ?? '');
        $type = strtoupper(trim((string) ($column['target_type'] ?? '')));
        if ($type === 'JSON') $classes['json_values']['columns'][] = $name;
        if (str_contains($type, 'BLOB') || str_contains($type, 'BINARY')) $classes['blob_values']['columns'][] = $name;
        if (str_starts_with($type, 'DATETIME')) $classes['timestamp_values']['columns'][] = $name;
    }
    foreach ($classes as &$class) sort($class['columns'], SORT_STRING);
    unset($class);
    return $classes;
}

/** @param array<string,array{columns:list<string>,hash:HashContext}> $classes @param array<string,mixed> $pkTuple @param array<string,mixed> $encoded */
function app_sqlite_mysql_verification_update_value_hashes(array &$classes, array $pkTuple, array $encoded): void
{
    foreach ($classes as &$class) {
        $values = [];
        foreach ($class['columns'] as $columnName) $values[$columnName] = $encoded[$columnName] ?? null;
        hash_update($class['hash'], json_encode(['pk' => $pkTuple, 'values' => $values], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
    }
    unset($class);
}

/** @param array<string,array{columns:list<string>,hash:HashContext}> $classes @return array{columns:list<string>,digest_sha256:string} */
function app_sqlite_mysql_verification_finalize_value_hash(array &$classes, string $key): array
{
    return ['columns' => $classes[$key]['columns'] ?? [], 'digest_sha256' => hash_final($classes[$key]['hash'])];
}

/** @param list<array<string,mixed>> $tables @param list<string> $errors @return array<string,mixed> */
function app_sqlite_mysql_verification_collect_result(bool $ok, string $driver, array $tables, array $errors): array
{
    return ['ok' => $ok, 'driver' => $driver, 'mutation_performed' => false, 'tables' => $tables, 'errors' => array_values(array_unique($errors))];
}
