<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_firebird_promotion_contract.php';
require_once __DIR__ . '/sqlite_mysql_export.php';

const APP_SQLITE_FIREBIRD_TARGET_SCHEMA_PLAN_VERSION = 'sqlite-firebird-target-schema-plan-v1';
const APP_SQLITE_FIREBIRD_EXPORT_VERSION = 'sqlite-firebird-export-v1';
const APP_SQLITE_FIREBIRD_IMPORT_REHEARSAL_VERSION = 'sqlite-firebird-import-rehearsal-v1';

/** @param array<string,mixed> $contract @return array<string,mixed> */
function app_sqlite_firebird_target_schema_plan(array $contract): array
{
    $errors = app_sqlite_firebird_promotion_contract_errors($contract);
    if (($contract['ok'] ?? false) !== true) $errors[] = 'promotion_contract_not_ready';

    $statements = [];
    $tables = [];
    foreach (is_array($contract['tables'] ?? null) ? $contract['tables'] : [] as $table) {
        if (!is_array($table)) continue;
        $name = trim((string) ($table['name'] ?? ''));
        if (!app_sqlite_firebird_identifier_valid($name)) {
            $errors[] = 'invalid_table_identifier:' . $name;
            continue;
        }

        $definitions = [];
        foreach (is_array($table['columns'] ?? null) ? $table['columns'] : [] as $column) {
            if (!is_array($column)) continue;
            $columnName = trim((string) ($column['name'] ?? ''));
            $type = strtoupper(trim((string) ($column['target_type'] ?? '')));
            if (!app_sqlite_firebird_identifier_valid($columnName)) {
                $errors[] = 'invalid_column_identifier:' . $name . '.' . $columnName;
                continue;
            }
            if (!app_sqlite_firebird_target_type_valid($type)) {
                $errors[] = 'invalid_target_type:' . $name . '.' . $columnName;
                continue;
            }

            $definition = app_sqlite_firebird_quote_identifier($columnName) . ' ' . $type;
            if (array_key_exists('default', $column) && $column['default'] !== null && !str_contains($type, 'BLOB')) {
                $default = app_sqlite_firebird_default_sql($column['default']);
                if ($default === null) $errors[] = 'unsupported_target_default:' . $name . '.' . $columnName;
                else $definition .= ' DEFAULT ' . $default;
            }
            $definition .= (($column['nullable'] ?? false) === true) ? '' : ' NOT NULL';
            $definitions[] = $definition;
        }

        foreach (is_array($table['keys'] ?? null) ? $table['keys'] : [] as $key) {
            if (!is_array($key)) continue;
            $kind = strtolower((string) ($key['kind'] ?? ''));
            $keyName = trim((string) ($key['name'] ?? ''));
            $columns = app_sqlite_firebird_identifier_list($key['columns'] ?? []);
            if ($columns === null || !in_array($kind, ['primary', 'unique'], true)) {
                $errors[] = 'invalid_target_key:' . $name;
                continue;
            }
            if ($kind === 'primary') $definitions[] = 'PRIMARY KEY (' . $columns . ')';
            elseif (!app_sqlite_firebird_identifier_valid($keyName)) $errors[] = 'invalid_target_key_identifier:' . $name;
            else $definitions[] = 'CONSTRAINT ' . app_sqlite_firebird_quote_identifier($keyName) . ' UNIQUE (' . $columns . ')';
        }

        foreach (is_array($table['foreign_keys'] ?? null) ? $table['foreign_keys'] : [] as $foreignKey) {
            if (!is_array($foreignKey)) continue;
            $constraintName = trim((string) ($foreignKey['name'] ?? ''));
            $referencedTable = trim((string) ($foreignKey['referenced_table'] ?? ''));
            $columns = app_sqlite_firebird_identifier_list($foreignKey['columns'] ?? []);
            $referencedColumns = app_sqlite_firebird_identifier_list($foreignKey['referenced_columns'] ?? []);
            if (!app_sqlite_firebird_identifier_valid($constraintName)
                || !app_sqlite_firebird_identifier_valid($referencedTable)
                || $columns === null || $referencedColumns === null) {
                $errors[] = 'invalid_target_foreign_key:' . $name;
                continue;
            }
            $definitions[] = 'CONSTRAINT ' . app_sqlite_firebird_quote_identifier($constraintName)
                . ' FOREIGN KEY (' . $columns . ') REFERENCES '
                . app_sqlite_firebird_quote_identifier($referencedTable) . ' (' . $referencedColumns . ')';
        }

        if ($definitions === []) $errors[] = 'target_table_has_no_definitions:' . $name;
        $statement = 'CREATE TABLE ' . app_sqlite_firebird_quote_identifier($name)
            . " (\n  " . implode(",\n  ", $definitions) . "\n)";
        $statements[] = $statement;
        $tables[] = ['name' => $name, 'statement_sha256' => hash('sha256', $statement)];
    }

    $errors = array_values(array_unique($errors));
    $digestInput = implode(";\n", $statements) . ($statements === [] ? '' : ";\n");
    return [
        'plan_version' => APP_SQLITE_FIREBIRD_TARGET_SCHEMA_PLAN_VERSION,
        'ok' => $errors === [],
        'stage' => 'target_schema_plan',
        'mutation_performed' => false,
        'promotion_contract_sha256' => app_sqlite_mysql_promotion_digest($contract),
        'schema_sha256' => hash('sha256', $digestInput),
        'requires_new_or_empty_target' => true,
        'requires_explicit_approval' => true,
        'tables' => $tables,
        'statements' => $statements,
        'errors' => $errors,
    ];
}

/** @param array<string,mixed> $contract @return array<string,mixed> */
function app_sqlite_firebird_export(PDO $pdo, array $contract, int $chunkSize = 500): array
{
    $errors = app_sqlite_firebird_promotion_contract_errors($contract);
    if (($contract['ok'] ?? false) !== true) $errors[] = 'promotion_contract_not_ready';
    if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') $errors[] = 'sqlite_source_required';
    if ($chunkSize < 1 || $chunkSize > 10000) $errors[] = 'invalid_chunk_size';
    if ($errors !== []) return app_sqlite_firebird_export_result(false, $errors, [], [], false);

    $ownsTransaction = !$pdo->inTransaction();
    $chunks = [];
    $tables = [];
    try {
        if ($ownsTransaction) $pdo->beginTransaction();
        foreach ($contract['tables'] as $tablePlan) {
            if (!is_array($tablePlan)) continue;
            $tableName = (string) ($tablePlan['name'] ?? '');
            $pk = array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : []));
            $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
            if (!app_sqlite_firebird_identifier_valid($tableName) || $pk === [] || $columns === []) throw new RuntimeException('invalid_export_table_plan:' . $tableName);
            $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
            foreach (array_merge($pk, $columnNames) as $identifier) {
                if (!app_sqlite_firebird_identifier_valid($identifier)) throw new RuntimeException('invalid_export_identifier:' . $tableName . '.' . $identifier);
            }
            $selectColumns = implode(', ', array_map('app_sqlite_firebird_quote_identifier', $columnNames));
            $order = implode(', ', array_map('app_sqlite_firebird_quote_identifier', $pk));
            $expected = (int) ($tablePlan['row_count'] ?? -1);
            $actualSourceCount = (int) $pdo->query('SELECT COUNT(*) FROM ' . app_sqlite_firebird_quote_identifier($tableName))->fetchColumn();
            if ($expected !== $actualSourceCount) throw new RuntimeException('source_row_count_mismatch:' . $tableName . ':expected=' . $expected . ':actual=' . $actualSourceCount);
            $statement = $pdo->query('SELECT ' . $selectColumns . ' FROM ' . app_sqlite_firebird_quote_identifier($tableName) . ' ORDER BY ' . $order);
            if (!$statement instanceof PDOStatement) throw new RuntimeException('source_select_failed:' . $tableName);

            $tableCount = 0;
            $chunkIndex = 0;
            $buffer = [];
            while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $encoded = [];
                foreach ($columns as $column) {
                    $name = (string) $column['name'];
                    $encoded[$name] = app_sqlite_firebird_export_convert_value($row[$name] ?? null, $column, $tableName . '.' . $name);
                }
                $buffer[] = $encoded;
                $tableCount++;
                if (count($buffer) === $chunkSize) {
                    $chunks[] = app_sqlite_firebird_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
                    $buffer = [];
                }
            }
            if ($buffer !== []) $chunks[] = app_sqlite_firebird_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
            $tables[] = ['name' => $tableName, 'source_row_count' => $actualSourceCount, 'exported_row_count' => $tableCount, 'chunk_count' => $chunkIndex];
        }
        if ($ownsTransaction) $pdo->commit();
        return app_sqlite_firebird_export_result(true, [], $tables, $chunks, false);
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        return app_sqlite_firebird_export_result(false, [$throwable->getMessage()], $tables, $chunks, false);
    }
}

/** @param list<string> $pk @param list<array<string,mixed>> $rows @return array<string,mixed> */
function app_sqlite_firebird_export_chunk(string $table, int $index, array $pk, array $rows): array
{
    $json = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $last = $rows[count($rows) - 1];
    $cursor = [];
    foreach ($pk as $column) $cursor[$column] = $last[$column] ?? null;
    return [
        'export_version' => APP_SQLITE_FIREBIRD_EXPORT_VERSION,
        'table' => $table,
        'chunk_index' => $index,
        'row_count' => count($rows),
        'rows_sha256' => hash('sha256', $json),
        'resume_after_primary_key' => $cursor,
        'rows' => $rows,
    ];
}

/** @param array<string,mixed> $column */
function app_sqlite_firebird_export_convert_value(mixed $value, array $column, string $path): mixed
{
    if ($value === null) return null;
    $type = strtoupper(trim((string) ($column['target_type'] ?? '')));
    $canonicalType = strtolower(trim((string) ($column['canonical_type'] ?? '')));
    if ($type === 'BIGINT') {
        if (is_int($value)) return (string) $value;
        if (is_string($value) && preg_match('/^-?(0|[1-9][0-9]*)$/', $value) === 1) return $value;
        throw new RuntimeException('integer_conversion_failed:' . $path);
    }
    if ($type === 'SMALLINT') {
        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') return (int) $value;
        throw new RuntimeException('boolean_conversion_failed:' . $path);
    }
    if (str_starts_with($type, 'DECIMAL(')) {
        $decimal = is_int($value) || is_float($value) ? (string) $value : $value;
        if (!is_string($decimal) || preg_match('/^-?(0|[1-9][0-9]*)(\.[0-9]+)?$/', $decimal) !== 1) throw new RuntimeException('decimal_conversion_failed:' . $path);
        return $decimal;
    }
    if ($type === 'TIMESTAMP') {
        if (!is_string($value) || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]{1,6})?$/', $value) !== 1) throw new RuntimeException('timestamp_conversion_failed:' . $path);
        return $value;
    }
    if (str_contains($type, 'BLOB SUB_TYPE BINARY')) {
        if (!is_string($value)) throw new RuntimeException('blob_conversion_failed:' . $path);
        return ['encoding' => 'base64', 'byte_length' => strlen($value), 'value' => base64_encode($value)];
    }
    if (str_contains($type, 'BLOB SUB_TYPE TEXT') && $canonicalType === 'json') {
        if (!is_string($value)) throw new RuntimeException('json_conversion_failed:' . $path);
        try { $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR); }
        catch (JsonException) { throw new RuntimeException('json_conversion_failed:' . $path); }
        return ['encoding' => 'json-text', 'value' => json_encode(app_sqlite_mysql_export_canonical_json_value($decoded), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)];
    }
    if (!is_string($value)) throw new RuntimeException('text_conversion_failed:' . $path);
    return $value;
}

/** @param array<string,mixed> $contract @param array<string,mixed> $schemaPlan @param array<string,mixed> $exportResult @return array<string,mixed> */
function app_sqlite_firebird_import_rehearsal_package(array $contract, array $schemaPlan, array $exportResult): array
{
    $errors = [];
    $contractDigest = app_sqlite_mysql_promotion_digest($contract);
    $schemaDigest = (string) ($schemaPlan['schema_sha256'] ?? '');
    if (app_sqlite_firebird_promotion_contract_errors($contract) !== [] || ($contract['ok'] ?? false) !== true) $errors[] = 'promotion_contract_not_ready';
    if (($schemaPlan['plan_version'] ?? '') !== APP_SQLITE_FIREBIRD_TARGET_SCHEMA_PLAN_VERSION || ($schemaPlan['ok'] ?? false) !== true || ($schemaPlan['mutation_performed'] ?? true) !== false) $errors[] = 'target_schema_plan_not_ready';
    if ((string) ($schemaPlan['promotion_contract_sha256'] ?? '') !== $contractDigest) $errors[] = 'target_schema_contract_digest_mismatch';
    if (($exportResult['ok'] ?? false) !== true || ($exportResult['mutation_performed'] ?? true) !== false) $errors[] = 'export_not_ready';
    $chunkSummary = app_sqlite_firebird_chunk_summary($exportResult);
    if (!$chunkSummary['ok']) $errors[] = 'export_chunk_contract_invalid';
    if ($chunkSummary['row_count'] !== app_sqlite_firebird_contract_row_count($contract)) $errors[] = 'export_row_count_mismatch';

    $completed = [];
    foreach (is_array($exportResult['chunks'] ?? null) ? $exportResult['chunks'] : [] as $chunk) {
        if (!is_array($chunk)) continue;
        $completed[(string) ($chunk['table'] ?? '') . ':' . (int) ($chunk['chunk_index'] ?? -1)] = (string) ($chunk['rows_sha256'] ?? '');
    }
    ksort($completed, SORT_STRING);
    $checkpoint = [
        'checkpoint_version' => APP_SQLITE_FIREBIRD_IMPORT_REHEARSAL_VERSION,
        'completed' => $completed,
        'mutation_performed' => false,
    ];

    $errors = array_values(array_unique($errors));
    $safe = [
        'rehearsal_version' => APP_SQLITE_FIREBIRD_IMPORT_REHEARSAL_VERSION,
        'rehearsal_ready' => $errors === [],
        'stage' => $errors === [] ? 'firebird_import_rehearsal_ready' : 'firebird_import_rehearsal_blocked',
        'mutation_performed' => false,
        'promotion_contract_sha256' => $contractDigest,
        'target_schema_sha256' => $schemaDigest,
        'export_summary' => [
            'table_count' => $chunkSummary['table_count'],
            'chunk_count' => $chunkSummary['chunk_count'],
            'row_count' => $chunkSummary['row_count'],
        ],
        'import_checkpoint_sha256' => app_sqlite_mysql_promotion_digest($checkpoint),
        'required_verification' => $contract['required_verification'] ?? [],
        'requires_explicit_local_profile_switch' => true,
        'non_goals' => $contract['non_goals'] ?? [],
        'errors' => $errors,
    ];
    $safe['rehearsal_sha256'] = app_sqlite_mysql_promotion_digest($safe);
    return $safe;
}

/** @param array<string,mixed> $exportResult @return array{ok:bool,table_count:int,chunk_count:int,row_count:int} */
function app_sqlite_firebird_chunk_summary(array $exportResult): array
{
    $ok = true;
    $tables = [];
    $chunkCount = 0;
    $rowCount = 0;
    foreach (is_array($exportResult['chunks'] ?? null) ? $exportResult['chunks'] : [] as $chunk) {
        if (!is_array($chunk) || ($chunk['export_version'] ?? '') !== APP_SQLITE_FIREBIRD_EXPORT_VERSION) {
            $ok = false;
            continue;
        }
        $table = (string) ($chunk['table'] ?? '');
        $rows = is_array($chunk['rows'] ?? null) ? $chunk['rows'] : [];
        $json = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if ($table === '' || (int) ($chunk['row_count'] ?? -1) !== count($rows) || !hash_equals((string) ($chunk['rows_sha256'] ?? ''), hash('sha256', $json))) $ok = false;
        $tables[$table] = true;
        $chunkCount++;
        $rowCount += count($rows);
    }
    return ['ok' => $ok, 'table_count' => count($tables), 'chunk_count' => $chunkCount, 'row_count' => $rowCount];
}

/** @param array<string,mixed> $contract */
function app_sqlite_firebird_contract_row_count(array $contract): int
{
    $count = 0;
    foreach (is_array($contract['tables'] ?? null) ? $contract['tables'] : [] as $table) {
        if (is_array($table)) $count += max(0, (int) ($table['row_count'] ?? 0));
    }
    return $count;
}

/** @param list<string> $errors @param list<array<string,mixed>> $tables @param list<array<string,mixed>> $chunks @return array<string,mixed> */
function app_sqlite_firebird_export_result(bool $ok, array $errors, array $tables, array $chunks, bool $mutation): array
{
    return ['export_version' => APP_SQLITE_FIREBIRD_EXPORT_VERSION, 'ok' => $ok, 'stage' => $ok ? 'export_ready' : 'export_failed', 'mutation_performed' => $mutation, 'tables' => $tables, 'chunks' => $chunks, 'errors' => array_values(array_unique($errors))];
}

function app_sqlite_firebird_identifier_valid(string $value): bool { return preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/', $value) === 1; }
function app_sqlite_firebird_quote_identifier(string $value): string { return '"' . $value . '"'; }
function app_sqlite_firebird_target_type_valid(string $value): bool
{
    return preg_match('/^(BIGINT|SMALLINT|DECIMAL\([1-9][0-9]?,[0-9]{1,2}\)|TIMESTAMP|BLOB SUB_TYPE (TEXT|BINARY)|VARCHAR\([1-9][0-9]{0,4}\)|CHAR\([1-9][0-9]{0,2}\))$/', $value) === 1;
}
function app_sqlite_firebird_identifier_list(mixed $values): ?string
{
    if (!is_array($values) || $values === []) return null;
    $result = [];
    foreach ($values as $value) { $name = (string) $value; if (!app_sqlite_firebird_identifier_valid($name)) return null; $result[] = app_sqlite_firebird_quote_identifier($name); }
    return implode(', ', $result);
}
function app_sqlite_firebird_default_sql(mixed $value): ?string
{
    if (is_int($value) || is_float($value)) return (string) $value;
    if (is_bool($value)) return $value ? '1' : '0';
    if (!is_string($value) || str_contains($value, "\0")) return null;
    if (strtoupper(trim($value)) === 'CURRENT_TIMESTAMP') return 'CURRENT_TIMESTAMP';
    return "'" . str_replace("'", "''", $value) . "'";
}
