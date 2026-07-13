<?php

declare(strict_types=1);

require_once __DIR__ . '/sqlite_mysql_export.php';
require_once __DIR__ . '/sqlite_mysql_target_schema.php';

const APP_SQLITE_MYSQL_IMPORT_CHECKPOINT_VERSION = 'sqlite-mysql-import-checkpoint-v1';

/** @param array<string,mixed> $manifest @param array<string,mixed> $chunk @param array<string,mixed> $checkpoint @return array<string,mixed> */
function app_sqlite_mysql_import_chunk(PDO $pdo, array $manifest, array $chunk, array $checkpoint = [], bool $approved = false): array
{
    if (!$approved) return app_sqlite_mysql_import_result(false, 'approval', 'explicit_approval_required', [], false);
    if ((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'mysql') return app_sqlite_mysql_import_result(false, 'preflight', 'mysql_target_required', [], false);
    if (app_sqlite_mysql_promotion_manifest_contract_errors($manifest) !== [] || ($manifest['ok'] ?? false) !== true) return app_sqlite_mysql_import_result(false, 'preflight', 'promotion_manifest_not_ready', [], false);
    if (($chunk['export_version'] ?? '') !== APP_SQLITE_MYSQL_EXPORT_VERSION) return app_sqlite_mysql_import_result(false, 'preflight', 'invalid_export_version', [], false);
    $tableName = (string) ($chunk['table'] ?? '');
    $tablePlan = null;
    foreach ($manifest['tables'] as $candidate) if (is_array($candidate) && ($candidate['name'] ?? '') === $tableName) $tablePlan = $candidate;
    if (!is_array($tablePlan)) return app_sqlite_mysql_import_result(false, 'preflight', 'unknown_export_table', [], false);
    $rows = is_array($chunk['rows'] ?? null) ? $chunk['rows'] : [];
    $rowJson = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $digest = hash('sha256', $rowJson);
    if (!hash_equals((string) ($chunk['rows_sha256'] ?? ''), $digest) || (int) ($chunk['row_count'] ?? -1) !== count($rows)) return app_sqlite_mysql_import_result(false, 'preflight', 'chunk_integrity_failed', [], false);
    $checkpointKey = $tableName . ':' . (int) ($chunk['chunk_index'] ?? -1);
    $completed = is_array($checkpoint['completed'] ?? null) ? $checkpoint['completed'] : [];
    if (isset($completed[$checkpointKey])) {
        if (!hash_equals((string) $completed[$checkpointKey], $digest)) return app_sqlite_mysql_import_result(false, 'checkpoint', 'checkpoint_digest_mismatch', $checkpoint, false);
        return app_sqlite_mysql_import_result(true, 'already_committed', '', $checkpoint, false);
    }
    $columns = array_values(array_filter(is_array($tablePlan['columns'] ?? null) ? $tablePlan['columns'] : [], 'is_array'));
    $columnNames = array_map(static fn (array $column): string => (string) ($column['name'] ?? ''), $columns);
    if ($columns === [] || array_filter($columnNames, static fn (string $name): bool => !app_sqlite_mysql_target_identifier_valid($name)) !== []) return app_sqlite_mysql_import_result(false, 'preflight', 'invalid_table_contract', [], false);
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
        $next = ['checkpoint_version' => APP_SQLITE_MYSQL_IMPORT_CHECKPOINT_VERSION, 'completed' => $completed, 'last_table' => $tableName, 'last_chunk_index' => (int) $chunk['chunk_index'], 'resume_after_primary_key' => $chunk['resume_after_primary_key'] ?? []];
        return app_sqlite_mysql_import_result(true, 'chunk_committed', '', $next, count($rows) > 0);
    } catch (Throwable $throwable) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return app_sqlite_mysql_import_result(false, 'chunk_rolled_back', $throwable->getMessage(), $checkpoint, false);
    }
}

function app_sqlite_mysql_import_decode_value(mixed $value, string $targetType): mixed
{
    if ($value === null) return null;
    $type = strtoupper(trim($targetType));
    if ($type === 'JSON') {
        if (!is_array($value) || ($value['encoding'] ?? '') !== 'json' || !array_key_exists('value', $value)) throw new RuntimeException('invalid_json_envelope');
        return json_encode($value['value'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    if (str_contains($type, 'BLOB') || str_contains($type, 'BINARY')) {
        if (!is_array($value) || ($value['encoding'] ?? '') !== 'base64') throw new RuntimeException('invalid_blob_envelope');
        $decoded = base64_decode((string) ($value['value'] ?? ''), true);
        if ($decoded === false || strlen($decoded) !== (int) ($value['byte_length'] ?? -1)) throw new RuntimeException('invalid_blob_envelope');
        return $decoded;
    }
    if (is_array($value) || is_object($value)) throw new RuntimeException('invalid_scalar_value');
    return $value;
}

/** @return array<string,mixed> */
function app_sqlite_mysql_import_result(bool $ok, string $stage, string $error, array $checkpoint, bool $mutation): array
{
    return ['ok' => $ok, 'stage' => $stage, 'error' => $error, 'checkpoint' => $checkpoint, 'mutation_performed' => $mutation];
}
