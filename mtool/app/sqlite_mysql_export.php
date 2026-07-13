<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_promotion_manifest.php';

const APP_SQLITE_MYSQL_EXPORT_VERSION = 'sqlite-mysql-export-v1';

/**
 * Read a frozen SQLite source in deterministic PK order and emit bounded
 * chunks. The optional consumer owns persistence; this function writes none.
 *
 * @param array<string,mixed> $manifest
 * @param null|callable(array<string,mixed>):void $consumer
 * @return array<string,mixed>
 */
function app_sqlite_mysql_export(PDO $pdo, array $manifest, int $chunkSize = 500, ?callable $consumer = null, array $resumeAfter = []): array
{
    $errors = app_sqlite_mysql_promotion_manifest_contract_errors($manifest);
    if (($manifest['ok'] ?? false) !== true) $errors[] = 'promotion_manifest_not_ready';
    if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') $errors[] = 'sqlite_source_required';
    if ($chunkSize < 1 || $chunkSize > 10000) $errors[] = 'invalid_chunk_size';
    if ($errors !== []) return app_sqlite_mysql_export_result(false, $errors, [], [], false);

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
            if (!app_sqlite_mysql_export_identifier_valid($tableName) || $pk === [] || $columns === []) {
                throw new RuntimeException('invalid_export_table_plan:' . $tableName);
            }
            $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
            foreach (array_merge($pk, $columnNames) as $identifier) {
                if (!app_sqlite_mysql_export_identifier_valid($identifier)) throw new RuntimeException('invalid_export_identifier:' . $tableName . '.' . $identifier);
            }
            $selectColumns = implode(', ', array_map('app_sqlite_mysql_export_quote_identifier', $columnNames));
            $order = implode(', ', array_map('app_sqlite_mysql_export_quote_identifier', $pk));
            $expected = (int) ($tablePlan['row_count'] ?? -1);
            $actualSourceCount = (int) $pdo->query('SELECT COUNT(*) FROM ' . app_sqlite_mysql_export_quote_identifier($tableName))->fetchColumn();
            if ($expected !== $actualSourceCount) throw new RuntimeException('source_row_count_mismatch:' . $tableName . ':expected=' . $expected . ':actual=' . $actualSourceCount);
            $cursor = is_array($resumeAfter[$tableName] ?? null) ? $resumeAfter[$tableName] : [];
            $sql = 'SELECT ' . $selectColumns . ' FROM ' . app_sqlite_mysql_export_quote_identifier($tableName);
            $params = [];
            if ($cursor !== []) {
                $cursorValues = [];
                foreach ($pk as $column) {
                    if (!array_key_exists($column, $cursor)) throw new RuntimeException('invalid_resume_cursor:' . $tableName . '.' . $column);
                    $cursorValues[] = $cursor[$column];
                }
                $sql .= ' WHERE (' . $order . ') > (' . implode(', ', array_fill(0, count($pk), '?')) . ')';
                $params = $cursorValues;
            }
            $sql .= ' ORDER BY ' . $order;
            $statement = $pdo->prepare($sql);
            $statement->execute($params);
            $tableCount = 0;
            $chunkIndex = 0;
            $buffer = [];
            while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $encoded = [];
                foreach ($columns as $column) {
                    $name = (string) $column['name'];
                    $encoded[$name] = app_sqlite_mysql_export_convert_value($row[$name] ?? null, (string) ($column['target_type'] ?? ''), $tableName . '.' . $name);
                }
                $buffer[] = $encoded;
                $tableCount++;
                if (count($buffer) === $chunkSize) {
                    $chunk = app_sqlite_mysql_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
                    $consumer === null ? $chunks[] = $chunk : $consumer($chunk);
                    $buffer = [];
                }
            }
            if ($buffer !== []) {
                $chunk = app_sqlite_mysql_export_chunk($tableName, $chunkIndex++, $pk, $buffer);
                $consumer === null ? $chunks[] = $chunk : $consumer($chunk);
            }
            $tables[] = ['name' => $tableName, 'source_row_count' => $actualSourceCount, 'exported_row_count' => $tableCount, 'chunk_count' => $chunkIndex, 'resumed' => $cursor !== []];
        }
        if ($ownsTransaction) $pdo->commit();
        return app_sqlite_mysql_export_result(true, [], $tables, $chunks, false);
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        return app_sqlite_mysql_export_result(false, [$throwable->getMessage()], $tables, $chunks, false);
    }
}

/** @param list<string> $pk @param list<array<string,mixed>> $rows @return array<string,mixed> */
function app_sqlite_mysql_export_chunk(string $table, int $index, array $pk, array $rows): array
{
    $json = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $last = $rows[count($rows) - 1];
    $cursor = [];
    foreach ($pk as $column) $cursor[$column] = $last[$column] ?? null;
    return [
        'export_version' => APP_SQLITE_MYSQL_EXPORT_VERSION,
        'table' => $table,
        'chunk_index' => $index,
        'row_count' => count($rows),
        'rows_sha256' => hash('sha256', $json),
        'resume_after_primary_key' => $cursor,
        'rows' => $rows,
    ];
}

function app_sqlite_mysql_export_convert_value(mixed $value, string $targetType, string $path): mixed
{
    if ($value === null) return null;
    $type = strtoupper(trim($targetType));
    if ($type === 'BIGINT') {
        if (is_int($value)) return (string) $value;
        if (is_string($value) && preg_match('/^-?(0|[1-9][0-9]*)$/', $value) === 1) return $value;
        throw new RuntimeException('integer_conversion_failed:' . $path);
    }
    if ($type === 'TINYINT(1)') {
        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') return (int) $value;
        throw new RuntimeException('boolean_conversion_failed:' . $path);
    }
    if (str_starts_with($type, 'DECIMAL(')) {
        $decimal = is_int($value) || is_float($value) ? (string) $value : $value;
        if (!is_string($decimal) || preg_match('/^-?(0|[1-9][0-9]*)(\.[0-9]+)?$/', $decimal) !== 1) throw new RuntimeException('decimal_conversion_failed:' . $path);
        return $decimal;
    }
    if ($type === 'JSON') {
        if (!is_string($value)) throw new RuntimeException('json_conversion_failed:' . $path);
        try { $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR); }
        catch (JsonException) { throw new RuntimeException('json_conversion_failed:' . $path); }
        return ['encoding' => 'json', 'value' => app_sqlite_mysql_export_canonical_json_value($decoded)];
    }
    if (str_contains($type, 'BLOB') || str_contains($type, 'BINARY')) {
        if (!is_string($value)) throw new RuntimeException('blob_conversion_failed:' . $path);
        return ['encoding' => 'base64', 'byte_length' => strlen($value), 'value' => base64_encode($value)];
    }
    if (str_starts_with($type, 'DATETIME')) {
        if (!is_string($value) || preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]{1,6})?$/', $value) !== 1) throw new RuntimeException('timestamp_conversion_failed:' . $path);
        return $value;
    }
    if (!is_string($value)) throw new RuntimeException('text_conversion_failed:' . $path);
    return $value;
}

function app_sqlite_mysql_export_canonical_json_value(mixed $value): mixed
{
    if (!is_array($value)) return $value;
    if (array_is_list($value)) return array_map('app_sqlite_mysql_export_canonical_json_value', $value);
    ksort($value, SORT_STRING);
    foreach ($value as $key => $child) $value[$key] = app_sqlite_mysql_export_canonical_json_value($child);
    return $value;
}

function app_sqlite_mysql_export_identifier_valid(string $value): bool { return preg_match('/^[A-Za-z_][A-Za-z0-9_]{0,63}$/', $value) === 1; }
function app_sqlite_mysql_export_quote_identifier(string $value): string { return '"' . $value . '"'; }

/** @param list<string> $errors @param list<array<string,mixed>> $tables @param list<array<string,mixed>> $chunks @return array<string,mixed> */
function app_sqlite_mysql_export_result(bool $ok, array $errors, array $tables, array $chunks, bool $mutation): array
{
    return ['export_version' => APP_SQLITE_MYSQL_EXPORT_VERSION, 'ok' => $ok, 'stage' => $ok ? 'export_ready' : 'export_failed', 'mutation_performed' => $mutation, 'tables' => $tables, 'chunks' => $chunks, 'errors' => array_values(array_unique($errors))];
}
