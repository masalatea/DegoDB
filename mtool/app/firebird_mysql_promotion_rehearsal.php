<?php

declare(strict_types=1);

require_once __DIR__ . '/firebird_mysql_promotion_manifest.php';
require_once __DIR__ . '/sqlite_mysql_export.php';
require_once __DIR__ . '/sqlite_mysql_import.php';
require_once __DIR__ . '/sqlite_mysql_target_schema.php';
require_once __DIR__ . '/sqlite_mysql_verification.php';

const APP_FIREBIRD_MYSQL_TARGET_SCHEMA_PLAN_VERSION = 'firebird-mysql-target-schema-plan-v1';
const APP_FIREBIRD_MYSQL_EXPORT_VERSION = 'firebird-mysql-export-v1';
const APP_FIREBIRD_MYSQL_IMPORT_CHECKPOINT_VERSION = 'firebird-mysql-import-checkpoint-v1';
const APP_FIREBIRD_MYSQL_VERIFICATION_VERSION = 'firebird-mysql-promotion-verification-v1';
const APP_FIREBIRD_MYSQL_REHEARSAL_PACKAGE_VERSION = 'firebird-mysql-rehearsal-package-v1';

/** @param array<string,mixed> $manifest @param array<string,mixed> $options @return array<string,mixed> */
function app_firebird_mysql_target_schema_plan(array $manifest, array $options = []): array
{
    $errors = app_firebird_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    $charset = strtolower(trim((string) ($options['charset'] ?? 'utf8mb4')));
    $collation = strtolower(trim((string) ($options['collation'] ?? 'utf8mb4_bin')));
    if ($charset !== 'utf8mb4') $errors[] = 'unsupported_target_charset';
    if (!in_array($collation, ['utf8mb4_bin', 'utf8mb4_0900_bin'], true)) $errors[] = 'unsupported_target_collation';

    $statements = [];
    $tables = [];
    foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $table) {
        if (!is_array($table)) continue;
        $name = trim((string) ($table['name'] ?? ''));
        if (!app_sqlite_mysql_target_identifier_valid($name)) {
            $errors[] = 'invalid_table_identifier:' . $name;
            continue;
        }
        $definitions = [];
        foreach (is_array($table['columns'] ?? null) ? $table['columns'] : [] as $column) {
            if (!is_array($column)) continue;
            $columnName = trim((string) ($column['name'] ?? ''));
            $type = strtoupper(trim((string) ($column['target_type'] ?? '')));
            if (!app_sqlite_mysql_target_identifier_valid($columnName)) {
                $errors[] = 'invalid_column_identifier:' . $name . '.' . $columnName;
                continue;
            }
            if (!app_sqlite_mysql_target_type_valid($type)) {
                $errors[] = 'invalid_target_type:' . $name . '.' . $columnName;
                continue;
            }
            $definition = app_sqlite_mysql_target_quote_identifier($columnName) . ' ' . $type;
            $definition .= (($column['nullable'] ?? false) === true) ? ' NULL' : ' NOT NULL';
            if (array_key_exists('default', $column) && $column['default'] !== null && !in_array($type, ['JSON', 'TEXT', 'LONGBLOB'], true)) {
                $default = app_sqlite_mysql_target_default_sql($column['default']);
                if ($default === null) $errors[] = 'unsupported_target_default:' . $name . '.' . $columnName;
                else $definition .= ' DEFAULT ' . $default;
            }
            $definitions[] = $definition;
        }
        foreach (is_array($table['keys'] ?? null) ? $table['keys'] : [] as $key) {
            if (!is_array($key)) continue;
            $kind = strtolower((string) ($key['kind'] ?? ''));
            $keyName = trim((string) ($key['name'] ?? ''));
            $columns = app_sqlite_mysql_target_identifier_list($key['columns'] ?? []);
            if ($columns === null || !in_array($kind, ['primary', 'unique'], true)) {
                $errors[] = 'invalid_target_key:' . $name;
                continue;
            }
            if ($kind === 'primary') $definitions[] = 'PRIMARY KEY (' . $columns . ')';
            elseif (!app_sqlite_mysql_target_identifier_valid($keyName)) $errors[] = 'invalid_target_key_identifier:' . $name;
            else $definitions[] = 'CONSTRAINT ' . app_sqlite_mysql_target_quote_identifier($keyName) . ' UNIQUE (' . $columns . ')';
        }
        foreach (is_array($table['foreign_keys'] ?? null) ? $table['foreign_keys'] : [] as $foreignKey) {
            if (!is_array($foreignKey)) continue;
            $constraintName = trim((string) ($foreignKey['name'] ?? ''));
            $referencedTable = trim((string) ($foreignKey['referenced_table'] ?? ''));
            $columns = app_sqlite_mysql_target_identifier_list($foreignKey['columns'] ?? []);
            $referencedColumns = app_sqlite_mysql_target_identifier_list($foreignKey['referenced_columns'] ?? []);
            if (!app_sqlite_mysql_target_identifier_valid($constraintName)
                || !app_sqlite_mysql_target_identifier_valid($referencedTable)
                || $columns === null || $referencedColumns === null) {
                $errors[] = 'invalid_target_foreign_key:' . $name;
                continue;
            }
            $definitions[] = 'CONSTRAINT ' . app_sqlite_mysql_target_quote_identifier($constraintName)
                . ' FOREIGN KEY (' . $columns . ') REFERENCES '
                . app_sqlite_mysql_target_quote_identifier($referencedTable) . ' (' . $referencedColumns . ')';
        }
        if ($definitions === []) $errors[] = 'target_table_has_no_definitions:' . $name;
        $statement = 'CREATE TABLE ' . app_sqlite_mysql_target_quote_identifier($name)
            . " (\n  " . implode(",\n  ", $definitions) . "\n) ENGINE=InnoDB DEFAULT CHARACTER SET {$charset} COLLATE {$collation}";
        $statements[] = $statement;
        $tables[] = ['name' => $name, 'statement_sha256' => hash('sha256', $statement)];
    }
    $errors = array_values(array_unique($errors));
    $digestInput = implode(";\n", $statements) . ($statements === [] ? '' : ";\n");
    return [
        'plan_version' => APP_FIREBIRD_MYSQL_TARGET_SCHEMA_PLAN_VERSION,
        'ok' => $errors === [],
        'stage' => 'target_schema_plan',
        'mutation_performed' => false,
        'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
        'schema_sha256' => hash('sha256', $digestInput),
        'charset' => $charset,
        'collation' => $collation,
        'requires_empty_target' => true,
        'requires_explicit_approval' => true,
        'tables' => $tables,
        'statements' => $statements,
        'errors' => $errors,
    ];
}

/** @param array<string,mixed> $manifest @param array<string,list<array<string,mixed>>> $sourceRows @return array<string,mixed> */
function app_firebird_mysql_export_from_rows(array $manifest, array $sourceRows, int $chunkSize = 500): array
{
    $errors = app_firebird_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if ($chunkSize < 1 || $chunkSize > 10000) $errors[] = 'invalid_chunk_size';
    if ($errors !== []) return app_firebird_mysql_export_result(false, $errors, [], [], false);

    $chunks = [];
    $tables = [];
    try {
        foreach ($manifest['tables'] as $tablePlan) {
            if (!is_array($tablePlan)) continue;
            $tableName = (string) ($tablePlan['name'] ?? '');
            $pk = array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : []));
            $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
            if (!app_sqlite_mysql_export_identifier_valid($tableName) || $pk === [] || $columns === []) throw new RuntimeException('invalid_export_table_plan:' . $tableName);
            $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
            foreach (array_merge($pk, $columnNames) as $identifier) {
                if (!app_sqlite_mysql_export_identifier_valid($identifier)) throw new RuntimeException('invalid_export_identifier:' . $tableName . '.' . $identifier);
            }
            $rows = array_values(is_array($sourceRows[$tableName] ?? null) ? $sourceRows[$tableName] : []);
            usort($rows, static function (array $a, array $b) use ($pk): int {
                foreach ($pk as $column) {
                    $cmp = ((string) ($a[$column] ?? '')) <=> ((string) ($b[$column] ?? ''));
                    if ($cmp !== 0) return $cmp;
                }
                return 0;
            });
            $expected = (int) ($tablePlan['row_count'] ?? -1);
            if ($expected !== count($rows)) throw new RuntimeException('source_row_count_mismatch:' . $tableName . ':expected=' . $expected . ':actual=' . count($rows));
            $tableCount = 0;
            $chunkIndex = 0;
            $buffer = [];
            foreach ($rows as $row) {
                $encoded = [];
                foreach ($columns as $column) {
                    $name = (string) $column['name'];
                    $encoded[$name] = app_firebird_mysql_export_convert_value($row[$name] ?? null, (string) ($column['target_type'] ?? ''), $tableName . '.' . $name);
                }
                $buffer[] = $encoded;
                $tableCount++;
                if (count($buffer) === $chunkSize) {
                    $chunks[] = app_firebird_mysql_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
                    $buffer = [];
                }
            }
            if ($buffer !== []) $chunks[] = app_firebird_mysql_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
            $tables[] = ['name' => $tableName, 'source_row_count' => count($rows), 'exported_row_count' => $tableCount, 'chunk_count' => $chunkIndex];
        }
        return app_firebird_mysql_export_result(true, [], $tables, $chunks, false);
    } catch (Throwable $throwable) {
        return app_firebird_mysql_export_result(false, [$throwable->getMessage()], $tables, $chunks, false);
    }
}

/**
 * Read a Firebird source in deterministic PK order and emit MySQL/MariaDB
 * promotion chunks. This function performs no Firebird mutation and opens no
 * target connection.
 *
 * @param array<string,mixed> $manifest
 * @param null|callable(array<string,mixed>):void $consumer
 * @param array<string,array<string,mixed>> $resumeAfter
 * @return array<string,mixed>
 */
function app_firebird_mysql_export(PDO $pdo, array $manifest, int $chunkSize = 500, ?callable $consumer = null, array $resumeAfter = []): array
{
    $errors = app_firebird_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'firebird') $errors[] = 'firebird_source_required';
    if ($chunkSize < 1 || $chunkSize > 10000) $errors[] = 'invalid_chunk_size';
    if ($errors !== []) return app_firebird_mysql_export_result(false, $errors, [], [], false);

    $ownsTransaction = !$pdo->inTransaction();
    $chunks = [];
    $tables = [];
    try {
        if ($ownsTransaction) $pdo->beginTransaction();
        foreach ($manifest['tables'] as $tablePlan) {
            if (!is_array($tablePlan)) continue;
            $tableName = (string) ($tablePlan['name'] ?? '');
            $pk = array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : []));
            $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
            if (!app_sqlite_mysql_export_identifier_valid($tableName) || $pk === [] || $columns === []) throw new RuntimeException('invalid_export_table_plan:' . $tableName);
            $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
            foreach (array_merge($pk, $columnNames) as $identifier) {
                if (!app_sqlite_mysql_export_identifier_valid($identifier)) throw new RuntimeException('invalid_export_identifier:' . $tableName . '.' . $identifier);
            }

            $quotedTable = app_firebird_mysql_export_quote_identifier($tableName);
            $selectColumns = implode(', ', array_map('app_firebird_mysql_export_quote_identifier', $columnNames));
            $order = implode(', ', array_map('app_firebird_mysql_export_quote_identifier', $pk));
            $expected = (int) ($tablePlan['row_count'] ?? -1);
            $actualSourceCount = (int) $pdo->query('SELECT COUNT(*) FROM ' . $quotedTable)->fetchColumn();
            if ($expected !== $actualSourceCount) throw new RuntimeException('source_row_count_mismatch:' . $tableName . ':expected=' . $expected . ':actual=' . $actualSourceCount);

            $cursor = is_array($resumeAfter[$tableName] ?? null) ? $resumeAfter[$tableName] : [];
            $sql = 'SELECT ' . $selectColumns . ' FROM ' . $quotedTable;
            $params = [];
            if ($cursor !== []) {
                [$where, $params] = app_firebird_mysql_export_resume_where($pk, $cursor, $tableName);
                $sql .= ' WHERE ' . $where;
            }
            $sql .= ' ORDER BY ' . $order;

            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            $tableCount = 0;
            $chunkIndex = 0;
            $buffer = [];
            while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                if (!is_array($row)) continue;
                $encoded = [];
                foreach ($columns as $column) {
                    $name = (string) $column['name'];
                    $encoded[$name] = app_firebird_mysql_export_convert_value(
                        app_firebird_mysql_export_row_value($row, $name),
                        (string) ($column['target_type'] ?? ''),
                        $tableName . '.' . $name,
                    );
                }
                $buffer[] = $encoded;
                $tableCount++;
                if (count($buffer) === $chunkSize) {
                    $chunk = app_firebird_mysql_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
                    $consumer === null ? $chunks[] = $chunk : $consumer($chunk);
                    $buffer = [];
                }
            }
            if ($buffer !== []) {
                $chunk = app_firebird_mysql_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
                $consumer === null ? $chunks[] = $chunk : $consumer($chunk);
            }
            $tables[] = [
                'name' => $tableName,
                'source_row_count' => $actualSourceCount,
                'exported_row_count' => $tableCount,
                'chunk_count' => $chunkIndex,
                'resumed' => $cursor !== [],
            ];
        }
        if ($ownsTransaction) $pdo->commit();
        return app_firebird_mysql_export_result(true, [], $tables, $chunks, false);
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        return app_firebird_mysql_export_result(false, [$throwable->getMessage()], $tables, $chunks, false);
    }
}

/** @param list<string> $pk @param list<array<string,mixed>> $rows @return array<string,mixed> */
function app_firebird_mysql_export_chunk(string $table, int $index, array $pk, array $rows): array
{
    $json = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $last = $rows[count($rows) - 1];
    $cursor = [];
    foreach ($pk as $column) $cursor[$column] = $last[$column] ?? null;
    return [
        'export_version' => APP_FIREBIRD_MYSQL_EXPORT_VERSION,
        'table' => $table,
        'chunk_index' => $index,
        'row_count' => count($rows),
        'rows_sha256' => hash('sha256', $json),
        'resume_after_primary_key' => $cursor,
        'rows' => $rows,
    ];
}

function app_firebird_mysql_export_convert_value(mixed $value, string $targetType, string $path): mixed
{
    if ($value === null) return null;
    if (is_resource($value)) {
        $contents = stream_get_contents($value);
        if (!is_string($contents)) throw new RuntimeException('stream_conversion_failed:' . $path);
        $value = $contents;
    }
    if ($value instanceof DateTimeInterface) {
        $value = $value->format('Y-m-d H:i:s.u');
    }
    if (is_bool($value)) {
        $value = $value ? 1 : 0;
    }
    return app_sqlite_mysql_export_convert_value($value, $targetType, $path);
}

function app_firebird_mysql_export_quote_identifier(string $value): string
{
    if (!app_sqlite_mysql_export_identifier_valid($value)) throw new RuntimeException('invalid_export_identifier:' . $value);
    return '"' . $value . '"';
}

/** @param array<string,mixed> $row */
function app_firebird_mysql_export_row_value(array $row, string $name): mixed
{
    if (array_key_exists($name, $row)) return $row[$name];
    $upper = strtoupper($name);
    if (array_key_exists($upper, $row)) return $row[$upper];
    $lower = strtolower($name);
    if (array_key_exists($lower, $row)) return $row[$lower];
    return null;
}

/**
 * @param list<string> $pk
 * @param array<string,mixed> $cursor
 * @return array{0:string,1:list<mixed>}
 */
function app_firebird_mysql_export_resume_where(array $pk, array $cursor, string $tableName): array
{
    $parts = [];
    $params = [];
    foreach ($pk as $index => $column) {
        if (!array_key_exists($column, $cursor)) throw new RuntimeException('invalid_resume_cursor:' . $tableName . '.' . $column);
        $equals = [];
        for ($i = 0; $i < $index; $i++) {
            $equals[] = app_firebird_mysql_export_quote_identifier($pk[$i]) . ' = ?';
            $params[] = $cursor[$pk[$i]];
        }
        $parts[] = ($equals === [] ? '' : '(' . implode(' AND ', $equals) . ' AND ')
            . app_firebird_mysql_export_quote_identifier($column) . ' > ?'
            . ($equals === [] ? '' : ')');
        $params[] = $cursor[$column];
    }
    return ['(' . implode(' OR ', $parts) . ')', $params];
}

/** @param array<string,mixed> $manifest @param array<string,mixed> $chunk @param array<string,mixed> $checkpoint @return array<string,mixed> */
function app_firebird_mysql_import_chunk(PDO $pdo, array $manifest, array $chunk, array $checkpoint = [], bool $approved = false): array
{
    if (!$approved) return app_sqlite_mysql_import_result(false, 'approval', 'explicit_approval_required', [], false);
    if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') return app_sqlite_mysql_import_result(false, 'preflight', 'mysql_target_required', [], false);
    if (app_firebird_mysql_promotion_manifest_contract_errors($manifest) !== [] || ($manifest['ok'] ?? false) !== true) return app_sqlite_mysql_import_result(false, 'preflight', 'promotion_manifest_not_ready', [], false);
    if (($chunk['export_version'] ?? '') !== APP_FIREBIRD_MYSQL_EXPORT_VERSION) return app_sqlite_mysql_import_result(false, 'preflight', 'invalid_export_version', [], false);

    $tableName = (string) ($chunk['table'] ?? '');
    $tablePlan = null;
    foreach ($manifest['tables'] as $candidate) {
        if (is_array($candidate) && ($candidate['name'] ?? '') === $tableName) $tablePlan = $candidate;
    }
    if (!is_array($tablePlan)) return app_sqlite_mysql_import_result(false, 'preflight', 'unknown_export_table', [], false);

    $rows = is_array($chunk['rows'] ?? null) ? $chunk['rows'] : [];
    $rowJson = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $digest = hash('sha256', $rowJson);
    if (!hash_equals((string) ($chunk['rows_sha256'] ?? ''), $digest) || (int) ($chunk['row_count'] ?? -1) !== count($rows)) {
        return app_sqlite_mysql_import_result(false, 'preflight', 'chunk_integrity_failed', [], false);
    }

    $checkpointKey = $tableName . ':' . (int) ($chunk['chunk_index'] ?? -1);
    $completed = is_array($checkpoint['completed'] ?? null) ? $checkpoint['completed'] : [];
    if (isset($completed[$checkpointKey])) {
        if (!hash_equals((string) $completed[$checkpointKey], $digest)) return app_sqlite_mysql_import_result(false, 'checkpoint', 'checkpoint_digest_mismatch', $checkpoint, false);
        return app_sqlite_mysql_import_result(true, 'already_committed', '', $checkpoint, false);
    }

    $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
    $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
    if ($columns === [] || array_filter($columnNames, static fn (string $name): bool => !app_sqlite_mysql_target_identifier_valid($name)) !== []) {
        return app_sqlite_mysql_import_result(false, 'preflight', 'invalid_table_contract', [], false);
    }
    $sql = 'INSERT INTO ' . app_sqlite_mysql_target_quote_identifier($tableName)
        . ' (' . implode(', ', array_map('app_sqlite_mysql_target_quote_identifier', $columnNames)) . ') VALUES ('
        . implode(', ', array_fill(0, count($columnNames), '?')) . ')';

    try {
        $pdo->beginTransaction();
        $statement = $pdo->prepare($sql);
        foreach ($rows as $row) {
            if (!is_array($row) || array_keys($row) !== $columnNames) throw new RuntimeException('chunk_row_shape_mismatch');
            $values = [];
            foreach ($columns as $column) $values[] = app_sqlite_mysql_import_decode_value($row[(string) $column['name']], (string) ($column['target_type'] ?? ''));
            $statement->execute($values);
        }
        $pdo->commit();
        $completed[$checkpointKey] = $digest;
        ksort($completed, SORT_STRING);
        $next = [
            'checkpoint_version' => APP_FIREBIRD_MYSQL_IMPORT_CHECKPOINT_VERSION,
            'completed' => $completed,
            'last_table' => $tableName,
            'last_chunk_index' => (int) $chunk['chunk_index'],
            'resume_after_primary_key' => $chunk['resume_after_primary_key'] ?? [],
        ];
        return app_sqlite_mysql_import_result(true, 'chunk_committed', '', $next, count($rows) > 0);
    } catch (Throwable $throwable) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return app_sqlite_mysql_import_result(false, 'chunk_rolled_back', $throwable->getMessage(), $checkpoint, false);
    }
}

/** @param array<string,mixed> $manifest @param array<string,mixed> $exportResult @return array<string,mixed> */
function app_firebird_mysql_verification_source_evidence_from_export(array $manifest, array $exportResult): array
{
    $errors = app_firebird_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if (($exportResult['export_version'] ?? '') !== APP_FIREBIRD_MYSQL_EXPORT_VERSION || ($exportResult['ok'] ?? false) !== true) $errors[] = 'export_not_ready';
    if (($exportResult['mutation_performed'] ?? true) !== false) $errors[] = 'export_mutation_performed';
    if ($errors !== []) return app_sqlite_mysql_verification_collect_result(false, 'firebird', [], $errors);

    $chunksByTable = [];
    foreach (is_array($exportResult['chunks'] ?? null) ? $exportResult['chunks'] : [] as $chunk) {
        if (is_array($chunk)) $chunksByTable[(string) ($chunk['table'] ?? '')][] = $chunk;
    }
    foreach ($chunksByTable as &$chunks) {
        usort($chunks, static fn (array $a, array $b): int => (int) ($a['chunk_index'] ?? 0) <=> (int) ($b['chunk_index'] ?? 0));
    }
    unset($chunks);

    $tables = [];
    try {
        foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $tablePlan) {
            if (!is_array($tablePlan)) continue;
            $tables[] = app_firebird_mysql_verification_source_table_from_export($tablePlan, $chunksByTable[(string) ($tablePlan['name'] ?? '')] ?? []);
        }
        return app_sqlite_mysql_verification_collect_result(true, 'firebird', $tables, []);
    } catch (Throwable $throwable) {
        return app_sqlite_mysql_verification_collect_result(false, 'firebird', $tables, [$throwable->getMessage()]);
    }
}

/** @param array<string,mixed> $tablePlan @param list<array<string,mixed>> $chunks @return array<string,mixed> */
function app_firebird_mysql_verification_source_table_from_export(array $tablePlan, array $chunks): array
{
    $tableName = (string) ($tablePlan['name'] ?? '');
    $pk = array_values(array_map('strval', is_array($tablePlan['primary_key'] ?? null) ? $tablePlan['primary_key'] : []));
    $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
    if (!app_sqlite_mysql_target_identifier_valid($tableName) || $pk === [] || $columns === []) throw new RuntimeException('invalid_verification_table_plan:' . $tableName);

    $pkHash = hash_init('sha256');
    $rowHash = hash_init('sha256');
    $valueHashes = app_sqlite_mysql_verification_value_hashes($columns);
    $scanned = 0;
    $maxPrimary = null;
    foreach ($chunks as $chunk) {
        if (($chunk['export_version'] ?? '') !== APP_FIREBIRD_MYSQL_EXPORT_VERSION) throw new RuntimeException('invalid_export_chunk_version:' . $tableName);
        $rows = is_array($chunk['rows'] ?? null) ? $chunk['rows'] : [];
        $json = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (!hash_equals((string) ($chunk['rows_sha256'] ?? ''), hash('sha256', $json)) || (int) ($chunk['row_count'] ?? -1) !== count($rows)) {
            throw new RuntimeException('export_chunk_integrity_failed:' . $tableName);
        }
        foreach ($rows as $row) {
            if (!is_array($row)) throw new RuntimeException('export_row_shape_invalid:' . $tableName);
            $encoded = [];
            foreach ($columns as $column) {
                $name = (string) ($column['name'] ?? '');
                $encoded[$name] = app_firebird_mysql_verification_source_value($row[$name] ?? null, (string) ($column['target_type'] ?? ''), $tableName . '.' . $name);
            }
            $pkTuple = [];
            foreach ($pk as $column) $pkTuple[$column] = $encoded[$column] ?? null;
            hash_update($pkHash, json_encode($pkTuple, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
            hash_update($rowHash, json_encode($encoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n");
            app_sqlite_mysql_verification_update_value_hashes($valueHashes, $pkTuple, $encoded);
            if (count($pk) === 1) {
                $value = (string) ($pkTuple[$pk[0]] ?? '');
                if ($value !== '' && ($maxPrimary === null || app_sqlite_mysql_verification_decimal_compare($value, $maxPrimary) > 0)) $maxPrimary = $value;
            }
            $scanned++;
        }
    }
    $expected = max(0, (int) ($tablePlan['row_count'] ?? 0));
    if ($scanned !== $expected) throw new RuntimeException('export_row_count_mismatch:' . $tableName . ':expected=' . $expected . ':actual=' . $scanned);

    return [
        'name' => $tableName,
        'row_count' => $scanned,
        'count_start' => $scanned,
        'count_end' => $scanned,
        'stable_count' => true,
        'primary_key_sha256' => hash_final($pkHash),
        'rows_sha256' => hash_final($rowHash),
        'json_values' => app_sqlite_mysql_verification_finalize_value_hash($valueHashes, 'json_values'),
        'blob_values' => app_sqlite_mysql_verification_finalize_value_hash($valueHashes, 'blob_values'),
        'timestamp_values' => app_sqlite_mysql_verification_finalize_value_hash($valueHashes, 'timestamp_values'),
        'nullability' => app_firebird_mysql_verification_expected_nullability($tablePlan),
        'unique_keys' => app_firebird_mysql_verification_expected_unique_keys($tablePlan),
        'foreign_keys' => app_firebird_mysql_verification_expected_foreign_keys($tablePlan),
        'foreign_key_violation_count' => 0,
        'next_id' => count($pk) === 1
            ? app_sqlite_mysql_verification_next_id_result($pk, true, $maxPrimary, $maxPrimary === null ? null : app_sqlite_mysql_verification_decimal_increment($maxPrimary), 'none', null, true, '')
            : app_sqlite_mysql_verification_next_id_result($pk, false, null, null, 'none', null, true, 'composite_primary_key'),
    ];
}

function app_firebird_mysql_verification_source_value(mixed $value, string $targetType, string $path): mixed
{
    if ($value === null) return null;
    $type = strtoupper(trim($targetType));
    if ($type === 'JSON') {
        if (!is_array($value) || ($value['encoding'] ?? '') !== 'json' || !array_key_exists('value', $value)) throw new RuntimeException('invalid_json_envelope:' . $path);
        return ['encoding' => 'json', 'value' => app_sqlite_mysql_export_canonical_json_value($value['value'])];
    }
    if (str_contains($type, 'BLOB') || str_contains($type, 'BINARY')) {
        if (!is_array($value) || ($value['encoding'] ?? '') !== 'base64') throw new RuntimeException('invalid_blob_envelope:' . $path);
        $decoded = base64_decode((string) ($value['value'] ?? ''), true);
        if (!is_string($decoded) || strlen($decoded) !== (int) ($value['byte_length'] ?? -1)) throw new RuntimeException('invalid_blob_envelope:' . $path);
        return ['encoding' => 'sha256', 'byte_length' => strlen($decoded), 'sha256' => hash('sha256', $decoded)];
    }
    if (is_array($value) || is_object($value)) throw new RuntimeException('invalid_scalar_value:' . $path);
    return $value;
}

/** @param array<string,mixed> $tablePlan @return list<array{name:string,nullable:bool}> */
function app_firebird_mysql_verification_expected_nullability(array $tablePlan): array
{
    $items = [];
    foreach (is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [] as $column) {
        if (is_array($column)) $items[] = ['name' => (string) ($column['name'] ?? ''), 'nullable' => ($column['nullable'] ?? false) === true];
    }
    usort($items, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);
    return $items;
}

/** @param array<string,mixed> $tablePlan @return list<array{kind:string,columns:list<string>}> */
function app_firebird_mysql_verification_expected_unique_keys(array $tablePlan): array
{
    $keys = [];
    foreach (is_array($tablePlan['keys'] ?? null) ? $tablePlan['keys'] : [] as $key) {
        if (!is_array($key)) continue;
        $kind = strtolower((string) ($key['kind'] ?? ''));
        if (in_array($kind, ['primary', 'unique'], true)) $keys[] = ['kind' => $kind, 'columns' => array_values(array_map('strval', is_array($key['columns'] ?? null) ? $key['columns'] : []))];
    }
    return app_sqlite_mysql_verification_sort_shapes($keys);
}

/** @param array<string,mixed> $tablePlan @return list<array{columns:list<string>,referenced_table:string,referenced_columns:list<string>}> */
function app_firebird_mysql_verification_expected_foreign_keys(array $tablePlan): array
{
    $foreignKeys = [];
    foreach (is_array($tablePlan['foreign_keys'] ?? null) ? $tablePlan['foreign_keys'] : [] as $foreignKey) {
        if (!is_array($foreignKey)) continue;
        $foreignKeys[] = [
            'columns' => array_values(array_map('strval', is_array($foreignKey['columns'] ?? null) ? $foreignKey['columns'] : [])),
            'referenced_table' => (string) ($foreignKey['referenced_table'] ?? ''),
            'referenced_columns' => array_values(array_map('strval', is_array($foreignKey['referenced_columns'] ?? null) ? $foreignKey['referenced_columns'] : [])),
        ];
    }
    return app_sqlite_mysql_verification_sort_shapes($foreignKeys);
}

/** @param array<string,mixed> $manifest @return array<string,mixed> */
function app_firebird_mysql_verification_collect_mysql_target(PDO $pdo, array $manifest): array
{
    $errors = app_firebird_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') $errors[] = 'mysql_target_required';
    if ($errors !== []) return app_sqlite_mysql_verification_collect_result(false, (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME), [], $errors);

    $ownsTransaction = !$pdo->inTransaction();
    $tables = [];
    try {
        if ($ownsTransaction) $pdo->beginTransaction();
        foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $tablePlan) {
            if (is_array($tablePlan)) $tables[] = app_sqlite_mysql_verification_collect_table($pdo, 'mysql', $tablePlan);
        }
        if ($ownsTransaction) $pdo->commit();
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        return app_sqlite_mysql_verification_collect_result(false, 'mysql', $tables, [$throwable->getMessage()]);
    }
    $unstableTables = array_values(array_filter($tables, static fn (array $table): bool => ($table['stable_count'] ?? false) !== true));
    return app_sqlite_mysql_verification_collect_result($unstableTables === [], 'mysql', $tables, $unstableTables === [] ? [] : ['verification_scan_count_changed']);
}

/** @param array<string,mixed> $context @param list<array<string,mixed>> $checks @return array<string,mixed> */
function app_firebird_mysql_verification_artifact(array $context, array $checks): array
{
    $normalized = [];
    $blockers = [];
    foreach ($checks as $check) {
        if (!is_array($check)) continue;
        $key = trim((string) ($check['check_key'] ?? ''));
        if ($key === '' || isset($normalized[$key])) {
            $blockers[] = ['code' => 'invalid_or_duplicate_check', 'check_key' => $key];
            continue;
        }
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
    $safeContext = [
        'promotion_manifest_sha256' => (string) ($context['promotion_manifest_sha256'] ?? ''),
        'target_schema_sha256' => (string) ($context['target_schema_sha256'] ?? ''),
        'import_checkpoint_sha256' => (string) ($context['import_checkpoint_sha256'] ?? ''),
    ];
    foreach ($safeContext as $key => $value) {
        if (preg_match('/^[a-f0-9]{64}$/', $value) !== 1) $blockers[] = ['code' => 'invalid_context_digest', 'check_key' => $key];
    }
    return [
        'verification_version' => APP_FIREBIRD_MYSQL_VERIFICATION_VERSION,
        'cutover_ready' => $blockers === [],
        'mutation_performed' => false,
        'context' => $safeContext,
        'checks' => array_values($normalized),
        'blockers' => $blockers,
    ];
}

/** @param array<string,mixed> $context @param array<string,mixed> $sourceEvidence @param array<string,mixed> $targetEvidence @param array<string,mixed> $manifest @param array<string,mixed> $dbaccessSmoke @return array<string,mixed> */
function app_firebird_mysql_verification_build_artifact(array $context, array $sourceEvidence, array $targetEvidence, array $manifest, array $dbaccessSmoke): array
{
    return app_firebird_mysql_verification_artifact(
        $context,
        app_sqlite_mysql_verification_checks($sourceEvidence, $targetEvidence, $manifest, $dbaccessSmoke),
    );
}

/** @param array<string,mixed> $manifest @param array<string,mixed> $schemaPlan @param array<string,mixed> $exportResult @return array<string,mixed> */
function app_firebird_mysql_promotion_rehearsal_package(array $manifest, array $schemaPlan, array $exportResult): array
{
    $errors = [];
    $manifestDigest = app_sqlite_mysql_promotion_digest($manifest);
    if (app_firebird_mysql_promotion_manifest_contract_errors($manifest) !== [] || ($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if (($schemaPlan['plan_version'] ?? '') !== APP_FIREBIRD_MYSQL_TARGET_SCHEMA_PLAN_VERSION || ($schemaPlan['ok'] ?? false) !== true || ($schemaPlan['mutation_performed'] ?? true) !== false) $errors[] = 'target_schema_plan_not_ready';
    if ((string) ($schemaPlan['promotion_manifest_sha256'] ?? '') !== $manifestDigest) $errors[] = 'target_schema_manifest_digest_mismatch';
    if (($exportResult['export_version'] ?? '') !== APP_FIREBIRD_MYSQL_EXPORT_VERSION || ($exportResult['ok'] ?? false) !== true || ($exportResult['mutation_performed'] ?? true) !== false) $errors[] = 'export_not_ready';
    $chunkSummary = app_firebird_mysql_chunk_summary($exportResult);
    if (!$chunkSummary['ok']) $errors[] = 'export_chunk_contract_invalid';
    if ($chunkSummary['row_count'] !== app_firebird_mysql_manifest_row_count($manifest)) $errors[] = 'export_row_count_mismatch';

    $errors = array_values(array_unique($errors));
    $package = [
        'package_version' => APP_FIREBIRD_MYSQL_REHEARSAL_PACKAGE_VERSION,
        'rehearsal_ready' => $errors === [],
        'stage' => $errors === [] ? 'firebird_mysql_rehearsal_ready' : 'firebird_mysql_rehearsal_blocked',
        'mutation_performed' => false,
        'promotion_manifest_sha256' => $manifestDigest,
        'target_schema_sha256' => (string) ($schemaPlan['schema_sha256'] ?? ''),
        'export_summary' => [
            'table_count' => $chunkSummary['table_count'],
            'chunk_count' => $chunkSummary['chunk_count'],
            'row_count' => $chunkSummary['row_count'],
        ],
        'required_verification' => $manifest['required_verification'] ?? [],
        'requires_explicit_cutover' => true,
        'non_goals' => $manifest['non_goals'] ?? [],
        'errors' => $errors,
    ];
    $package['rehearsal_package_sha256'] = app_sqlite_mysql_promotion_digest($package);
    return $package;
}

/** @param array<string,mixed> $exportResult @return array{ok:bool,table_count:int,chunk_count:int,row_count:int} */
function app_firebird_mysql_chunk_summary(array $exportResult): array
{
    $ok = true;
    $tables = [];
    $chunkCount = 0;
    $rowCount = 0;
    foreach (is_array($exportResult['chunks'] ?? null) ? $exportResult['chunks'] : [] as $chunk) {
        if (!is_array($chunk) || ($chunk['export_version'] ?? '') !== APP_FIREBIRD_MYSQL_EXPORT_VERSION) {
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

/** @param array<string,mixed> $manifest */
function app_firebird_mysql_manifest_row_count(array $manifest): int
{
    $count = 0;
    foreach (is_array($manifest['tables'] ?? null) ? $manifest['tables'] : [] as $table) {
        if (is_array($table)) $count += max(0, (int) ($table['row_count'] ?? 0));
    }
    return $count;
}

/** @param list<string> $errors @param list<array<string,mixed>> $tables @param list<array<string,mixed>> $chunks @return array<string,mixed> */
function app_firebird_mysql_export_result(bool $ok, array $errors, array $tables, array $chunks, bool $mutation): array
{
    return ['export_version' => APP_FIREBIRD_MYSQL_EXPORT_VERSION, 'ok' => $ok, 'stage' => $ok ? 'export_ready' : 'export_failed', 'mutation_performed' => $mutation, 'tables' => $tables, 'chunks' => $chunks, 'errors' => array_values(array_unique($errors))];
}
