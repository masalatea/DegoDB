<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sql_dialect.php';

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,created:bool,result:string,item:array<string,mixed>,error:string}
 */
function app_pdo_lab_sample18_generated_submit_idempotency_create_or_reuse_record(
    array $app,
    array $input,
): array {
    try {
        $record = app_lab_sample18_generated_submit_idempotency_normalize_input($input);
        $pdo = app_create_config_pdo($app);
        $existing = app_pdo_lab_sample18_generated_submit_idempotency_fetch_by_dedupe_key(
            $pdo,
            $record['dedupe_key'],
        );
        if ($existing !== []) {
            $statement = $pdo->prepare(
                'UPDATE sample18_generated_submit_idempotency_records
                 SET duplicate_count = duplicate_count + 1,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE dedupe_key = :dedupe_key',
            );
            $statement->execute([
                ':dedupe_key' => $record['dedupe_key'],
            ]);

            return [
                'ok' => true,
                'created' => false,
                'result' => 'duplicate',
                'item' => app_pdo_lab_sample18_generated_submit_idempotency_fetch_by_dedupe_key(
                    $pdo,
                    $record['dedupe_key'],
                ),
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO sample18_generated_submit_idempotency_records (
                dedupe_key,
                project_key,
                operation_key,
                payload_fingerprint,
                result,
                failure_code,
                first_audit_event_key,
                duplicate_count,
                metadata_json
            ) VALUES (
                :dedupe_key,
                :project_key,
                :operation_key,
                :payload_fingerprint,
                :result,
                :failure_code,
                :first_audit_event_key,
                0,
                :metadata_json
            )',
        );
        $statement->execute([
            ':dedupe_key' => $record['dedupe_key'],
            ':project_key' => $record['project_key'],
            ':operation_key' => $record['operation_key'],
            ':payload_fingerprint' => $record['payload_fingerprint'],
            ':result' => $record['result'],
            ':failure_code' => $record['failure_code'],
            ':first_audit_event_key' => $record['first_audit_event_key'],
            ':metadata_json' => $record['metadata_json'],
        ]);

        return [
            'ok' => true,
            'created' => true,
            'result' => 'recorded',
            'item' => app_pdo_lab_sample18_generated_submit_idempotency_fetch_by_dedupe_key(
                $pdo,
                $record['dedupe_key'],
            ),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'created' => false,
            'result' => 'failed',
            'item' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $filters
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_lab_sample18_generated_submit_idempotency_fetch_latest_records(
    array $app,
    array $filters = [],
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_pdo($pdo);
        $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'updated_at', 'updated_at');
        $limit = max(1, min(500, (int) ($filters['limit'] ?? 100)));
        $where = [];
        $params = [];

        foreach (['dedupe_key', 'project_key', 'operation_key', 'payload_fingerprint', 'result', 'failure_code'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $where[] = $field . ' = :' . $field;
            $params[':' . $field] = $value;
        }

        $statement = $pdo->prepare(
            'SELECT
                dedupe_key,
                project_key,
                operation_key,
                payload_fingerprint,
                result,
                failure_code,
                first_audit_event_key,
                duplicate_count,
                metadata_json,
                ' . $createdAtSelect . ',
                ' . $updatedAtSelect . '
            FROM sample18_generated_submit_idempotency_records'
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY created_at DESC, id DESC LIMIT ' . $limit,
        );
        $statement->execute($params);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_lab_sample18_generated_submit_idempotency_item_from_row($row);
            }
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,updated:bool,result:string,item:array<string,mixed>,error:string}
 */
function app_pdo_lab_sample18_generated_submit_idempotency_update_execution_outcome(
    array $app,
    array $input,
): array {
    try {
        $update = app_lab_sample18_generated_submit_idempotency_normalize_execution_outcome_input($input);
        $pdo = app_create_config_pdo($app);
        $existing = app_pdo_lab_sample18_generated_submit_idempotency_fetch_by_dedupe_key(
            $pdo,
            $update['dedupe_key'],
        );
        if ($existing === []) {
            throw new InvalidArgumentException('sample18 generated submit idempotency record was not found.');
        }
        if ((int) ($existing['duplicate_count'] ?? 0) > 0) {
            throw new InvalidArgumentException('sample18 generated submit idempotency duplicate replay cannot update execution outcome.');
        }

        $metadata = is_array($existing['metadata'] ?? null) ? $existing['metadata'] : [];
        $existingExecution = is_array($metadata['execution'] ?? null) ? $metadata['execution'] : [];
        $metadata['execution'] = array_merge($existingExecution, [
            'execution_status' => $update['execution_status'],
            'execution_result_code' => $update['execution_result_code'],
            'transaction_status' => $update['transaction_status'],
            'execution_audit_event_key' => $update['execution_audit_event_key'],
            'details' => $update['metadata'],
        ]);

        $statement = $pdo->prepare(
            'UPDATE sample18_generated_submit_idempotency_records
             SET result = :result,
                 failure_code = :failure_code,
                 metadata_json = :metadata_json,
                 updated_at = CURRENT_TIMESTAMP
             WHERE dedupe_key = :dedupe_key',
        );
        $statement->execute([
            ':dedupe_key' => $update['dedupe_key'],
            ':result' => $update['result'],
            ':failure_code' => $update['failure_code'],
            ':metadata_json' => json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);

        return [
            'ok' => true,
            'updated' => true,
            'result' => 'updated',
            'item' => app_pdo_lab_sample18_generated_submit_idempotency_fetch_by_dedupe_key(
                $pdo,
                $update['dedupe_key'],
            ),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'updated' => false,
            'result' => 'failed',
            'item' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array<string,string>
 */
function app_lab_sample18_generated_submit_idempotency_normalize_input(array $input): array
{
    $metadata = $input['metadata'] ?? [];
    if (!is_array($metadata)) {
        throw new InvalidArgumentException('sample18 generated submit idempotency metadata must be an array.');
    }

    $result = app_lab_sample18_generated_submit_idempotency_normalize_optional_string($input, 'result', 'blocked');
    if ($result !== 'blocked') {
        throw new InvalidArgumentException('sample18 generated submit idempotency result is not supported: ' . $result);
    }

    return [
        'dedupe_key' => app_lab_sample18_generated_submit_idempotency_normalize_required_string($input, 'dedupe_key'),
        'project_key' => app_lab_sample18_generated_submit_idempotency_normalize_optional_string($input, 'project_key', 'SAMPLE18'),
        'operation_key' => app_lab_sample18_generated_submit_idempotency_normalize_required_string($input, 'operation_key'),
        'payload_fingerprint' => app_lab_sample18_generated_submit_idempotency_normalize_required_string(
            $input,
            'payload_fingerprint',
        ),
        'result' => $result,
        'failure_code' => app_lab_sample18_generated_submit_idempotency_normalize_optional_string(
            $input,
            'failure_code',
            'generated_submit_disabled',
        ),
        'first_audit_event_key' => app_lab_sample18_generated_submit_idempotency_normalize_optional_string(
            $input,
            'first_audit_event_key',
            '',
        ),
        'metadata_json' => json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    ];
}

/**
 * @param array<string,mixed> $input
 * @return array<string,mixed>
 */
function app_lab_sample18_generated_submit_idempotency_normalize_execution_outcome_input(array $input): array
{
    $metadata = $input['metadata'] ?? [];
    if (!is_array($metadata)) {
        throw new InvalidArgumentException('sample18 generated submit idempotency execution metadata must be an array.');
    }

    $executionStatus = app_lab_sample18_generated_submit_idempotency_normalize_required_string(
        $input,
        'execution_status',
    );
    if (!in_array($executionStatus, ['executed', 'failed', 'rolled_back'], true)) {
        throw new InvalidArgumentException('sample18 generated submit idempotency execution status is not supported: ' . $executionStatus);
    }
    $transactionStatus = app_lab_sample18_generated_submit_idempotency_normalize_required_string(
        $input,
        'transaction_status',
    );
    if (!in_array($transactionStatus, ['committed', 'rolled_back', 'not_opened'], true)) {
        throw new InvalidArgumentException('sample18 generated submit idempotency transaction status is not supported: ' . $transactionStatus);
    }

    $defaultFailureCode = $executionStatus === 'executed' ? '' : 'generated_submit_execution_' . $executionStatus;

    return [
        'dedupe_key' => app_lab_sample18_generated_submit_idempotency_normalize_required_string($input, 'dedupe_key'),
        'result' => app_lab_sample18_generated_submit_idempotency_normalize_execution_result($executionStatus),
        'failure_code' => app_lab_sample18_generated_submit_idempotency_normalize_optional_string(
            $input,
            'failure_code',
            $defaultFailureCode,
        ),
        'execution_status' => $executionStatus,
        'execution_result_code' => app_lab_sample18_generated_submit_idempotency_normalize_required_string(
            $input,
            'execution_result_code',
        ),
        'transaction_status' => $transactionStatus,
        'execution_audit_event_key' => app_lab_sample18_generated_submit_idempotency_normalize_optional_string(
            $input,
            'execution_audit_event_key',
            '',
        ),
        'metadata' => $metadata,
    ];
}

function app_lab_sample18_generated_submit_idempotency_normalize_execution_result(string $executionStatus): string
{
    if ($executionStatus === 'executed') {
        return 'executed';
    }

    return $executionStatus;
}

/**
 * @return array<string,mixed>
 */
function app_pdo_lab_sample18_generated_submit_idempotency_fetch_by_dedupe_key(
    PDO $pdo,
    string $dedupeKey,
): array {
    $dialect = app_sql_dialect_from_pdo($pdo);
    $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
    $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'updated_at', 'updated_at');
    $statement = $pdo->prepare(
        'SELECT
            dedupe_key,
            project_key,
            operation_key,
            payload_fingerprint,
            result,
            failure_code,
            first_audit_event_key,
            duplicate_count,
            metadata_json,
            ' . $createdAtSelect . ',
            ' . $updatedAtSelect . '
        FROM sample18_generated_submit_idempotency_records
        WHERE dedupe_key = :dedupe_key
        LIMIT 1',
    );
    $statement->execute([
        ':dedupe_key' => $dedupeKey,
    ]);
    $row = $statement->fetch();

    return is_array($row) ? app_lab_sample18_generated_submit_idempotency_item_from_row($row) : [];
}

/**
 * @param array<string,mixed> $input
 */
function app_lab_sample18_generated_submit_idempotency_normalize_required_string(array $input, string $field): string
{
    $value = trim((string) ($input[$field] ?? ''));
    if ($value === '') {
        throw new InvalidArgumentException('sample18 generated submit idempotency field is required: ' . $field);
    }

    return $value;
}

/**
 * @param array<string,mixed> $input
 */
function app_lab_sample18_generated_submit_idempotency_normalize_optional_string(
    array $input,
    string $field,
    string $default,
): string {
    $value = trim((string) ($input[$field] ?? ''));
    return $value === '' ? $default : $value;
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_lab_sample18_generated_submit_idempotency_item_from_row(array $row): array
{
    $metadata = json_decode((string) ($row['metadata_json'] ?? '{}'), true);
    if (!is_array($metadata)) {
        $metadata = [];
    }

    return [
        'dedupe_key' => (string) ($row['dedupe_key'] ?? ''),
        'project_key' => (string) ($row['project_key'] ?? ''),
        'operation_key' => (string) ($row['operation_key'] ?? ''),
        'payload_fingerprint' => (string) ($row['payload_fingerprint'] ?? ''),
        'result' => (string) ($row['result'] ?? ''),
        'failure_code' => (string) ($row['failure_code'] ?? ''),
        'first_audit_event_key' => (string) ($row['first_audit_event_key'] ?? ''),
        'duplicate_count' => (int) ($row['duplicate_count'] ?? 0),
        'metadata' => $metadata,
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}
