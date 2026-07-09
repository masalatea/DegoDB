<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sql_dialect.php';

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,created:bool,result:string,item:array<string,mixed>,error:string}
 */
function app_pdo_no_code_review_workflow_create_or_reuse_request(array $app, array $input): array
{
    try {
        $request = app_no_code_review_workflow_normalize_input($input);
        $pdo = app_create_config_pdo($app);
        $existing = app_pdo_no_code_review_workflow_fetch_open_request($pdo, $request);
        if ($existing !== []) {
            return [
                'ok' => true,
                'created' => false,
                'result' => 'duplicate',
                'item' => $existing,
                'error' => '',
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO no_code_review_requests (
                review_request_key,
                project_key,
                source_output_key,
                artifact_key,
                operation_key,
                adapter_handoff,
                status,
                requested_by,
                requested_at,
                source_output_dir,
                policy_key,
                audit_event,
                metadata_json
            ) VALUES (
                :review_request_key,
                :project_key,
                :source_output_key,
                :artifact_key,
                :operation_key,
                :adapter_handoff,
                :status,
                :requested_by,
                CURRENT_TIMESTAMP,
                :source_output_dir,
                :policy_key,
                :audit_event,
                :metadata_json
            )'
        );
        $statement->execute([
            ':review_request_key' => $request['review_request_key'],
            ':project_key' => $request['project_key'],
            ':source_output_key' => $request['source_output_key'],
            ':artifact_key' => $request['artifact_key'],
            ':operation_key' => $request['operation_key'],
            ':adapter_handoff' => $request['adapter_handoff'],
            ':status' => $request['status'],
            ':requested_by' => $request['requested_by'],
            ':source_output_dir' => $request['source_output_dir'],
            ':policy_key' => $request['policy_key'],
            ':audit_event' => $request['audit_event'],
            ':metadata_json' => $request['metadata_json'],
        ]);

        return [
            'ok' => true,
            'created' => true,
            'result' => 'accepted',
            'item' => app_pdo_no_code_review_workflow_fetch_by_request_key($pdo, $request['review_request_key']),
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
function app_pdo_no_code_review_workflow_fetch_latest_requests(array $app, array $filters = []): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_pdo($pdo);
        $requestedAtSelect = app_sql_datetime_select_expr($dialect, 'requested_at', 'requested_at');
        $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
        $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'updated_at', 'updated_at');
        $limit = max(1, min(500, (int) ($filters['limit'] ?? 100)));
        $where = [];
        $params = [];

        foreach (['project_key', 'source_output_key', 'artifact_key', 'operation_key', 'status', 'requested_by'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $where[] = $field . ' = :' . $field;
            $params[':' . $field] = $value;
        }

        $statement = $pdo->prepare(
            'SELECT
                review_request_key,
                project_key,
                source_output_key,
                artifact_key,
                operation_key,
                adapter_handoff,
                status,
                requested_by,
                ' . $requestedAtSelect . ',
                source_output_dir,
                policy_key,
                audit_event,
                metadata_json,
                ' . $createdAtSelect . ',
                ' . $updatedAtSelect . '
            FROM no_code_review_requests'
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY created_at DESC, id DESC LIMIT ' . $limit
        );
        $statement->execute($params);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_no_code_review_workflow_item_from_row($row);
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
 * @return list<string>
 */
function app_no_code_review_workflow_open_statuses(): array
{
    return ['requested', 'in_review'];
}

/**
 * @param array<string,mixed> $input
 * @return array<string,string>
 */
function app_no_code_review_workflow_normalize_input(array $input): array
{
    $requestKey = trim((string) ($input['review_request_key'] ?? ''));
    if ($requestKey === '') {
        $requestKey = 'review_' . date('YmdHis') . '_' . bin2hex(random_bytes(6));
    }

    $status = app_no_code_review_workflow_normalize_optional_string($input, 'status', 'requested');
    $openStatuses = app_no_code_review_workflow_open_statuses();
    $closedStatuses = ['accepted', 'rejected', 'cancelled', 'superseded'];
    if (!in_array($status, array_merge($openStatuses, $closedStatuses), true)) {
        throw new InvalidArgumentException('review workflow status is not supported: ' . $status);
    }

    $auditEvent = $input['audit_event'] ?? [];
    if (!is_array($auditEvent)) {
        throw new InvalidArgumentException('review workflow audit_event must be an array.');
    }

    $metadata = $input['metadata'] ?? [];
    if (!is_array($metadata)) {
        throw new InvalidArgumentException('review workflow metadata must be an array.');
    }

    return [
        'review_request_key' => $requestKey,
        'project_key' => app_no_code_review_workflow_normalize_required_string($input, 'project_key'),
        'source_output_key' => app_no_code_review_workflow_normalize_required_string($input, 'source_output_key'),
        'artifact_key' => app_no_code_review_workflow_normalize_required_string($input, 'artifact_key'),
        'operation_key' => app_no_code_review_workflow_normalize_optional_string($input, 'operation_key', 'review_source_output_artifact'),
        'adapter_handoff' => app_no_code_review_workflow_normalize_optional_string($input, 'adapter_handoff', 'mtool_source_output_review'),
        'status' => $status,
        'requested_by' => app_no_code_review_workflow_normalize_required_string($input, 'requested_by'),
        'source_output_dir' => app_no_code_review_workflow_normalize_optional_string($input, 'source_output_dir', ''),
        'policy_key' => app_no_code_review_workflow_normalize_optional_string($input, 'policy_key', 'source_output.review'),
        'audit_event' => json_encode($auditEvent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        'metadata_json' => json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    ];
}

/**
 * @param array<string,string> $request
 * @return array<string,mixed>
 */
function app_pdo_no_code_review_workflow_fetch_open_request(PDO $pdo, array $request): array
{
    $dialect = app_sql_dialect_from_pdo($pdo);
    $requestedAtSelect = app_sql_datetime_select_expr($dialect, 'requested_at', 'requested_at');
    $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
    $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'updated_at', 'updated_at');
    $statement = $pdo->prepare(
        'SELECT
            review_request_key,
            project_key,
            source_output_key,
            artifact_key,
            operation_key,
            adapter_handoff,
            status,
            requested_by,
            ' . $requestedAtSelect . ',
            source_output_dir,
            policy_key,
            audit_event,
            metadata_json,
            ' . $createdAtSelect . ',
            ' . $updatedAtSelect . '
        FROM no_code_review_requests
        WHERE project_key = :project_key
          AND source_output_key = :source_output_key
          AND artifact_key = :artifact_key
          AND operation_key = :operation_key
          AND status IN (\'requested\', \'in_review\')
        ORDER BY created_at DESC, id DESC
        LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $request['project_key'],
        ':source_output_key' => $request['source_output_key'],
        ':artifact_key' => $request['artifact_key'],
        ':operation_key' => $request['operation_key'],
    ]);
    $row = $statement->fetch();

    return is_array($row) ? app_no_code_review_workflow_item_from_row($row) : [];
}

/**
 * @return array<string,mixed>
 */
function app_pdo_no_code_review_workflow_fetch_by_request_key(PDO $pdo, string $requestKey): array
{
    $dialect = app_sql_dialect_from_pdo($pdo);
    $requestedAtSelect = app_sql_datetime_select_expr($dialect, 'requested_at', 'requested_at');
    $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
    $updatedAtSelect = app_sql_datetime_select_expr($dialect, 'updated_at', 'updated_at');
    $statement = $pdo->prepare(
        'SELECT
            review_request_key,
            project_key,
            source_output_key,
            artifact_key,
            operation_key,
            adapter_handoff,
            status,
            requested_by,
            ' . $requestedAtSelect . ',
            source_output_dir,
            policy_key,
            audit_event,
            metadata_json,
            ' . $createdAtSelect . ',
            ' . $updatedAtSelect . '
        FROM no_code_review_requests
        WHERE review_request_key = :review_request_key
        LIMIT 1'
    );
    $statement->execute([
        ':review_request_key' => $requestKey,
    ]);
    $row = $statement->fetch();

    return is_array($row) ? app_no_code_review_workflow_item_from_row($row) : [];
}

/**
 * @param array<string,mixed> $input
 */
function app_no_code_review_workflow_normalize_required_string(array $input, string $field): string
{
    $value = trim((string) ($input[$field] ?? ''));
    if ($value === '') {
        throw new InvalidArgumentException('review workflow field is required: ' . $field);
    }

    return $value;
}

/**
 * @param array<string,mixed> $input
 */
function app_no_code_review_workflow_normalize_optional_string(array $input, string $field, string $default): string
{
    $value = trim((string) ($input[$field] ?? ''));
    return $value === '' ? $default : $value;
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_no_code_review_workflow_item_from_row(array $row): array
{
    $auditEvent = json_decode((string) ($row['audit_event'] ?? '{}'), true);
    if (!is_array($auditEvent)) {
        $auditEvent = [];
    }

    $metadata = json_decode((string) ($row['metadata_json'] ?? '{}'), true);
    if (!is_array($metadata)) {
        $metadata = [];
    }

    return [
        'review_request_key' => (string) ($row['review_request_key'] ?? ''),
        'project_key' => (string) ($row['project_key'] ?? ''),
        'source_output_key' => (string) ($row['source_output_key'] ?? ''),
        'artifact_key' => (string) ($row['artifact_key'] ?? ''),
        'operation_key' => (string) ($row['operation_key'] ?? ''),
        'adapter_handoff' => (string) ($row['adapter_handoff'] ?? ''),
        'status' => (string) ($row['status'] ?? ''),
        'requested_by' => (string) ($row['requested_by'] ?? ''),
        'requested_at' => (string) ($row['requested_at'] ?? ''),
        'source_output_dir' => (string) ($row['source_output_dir'] ?? ''),
        'policy_key' => (string) ($row['policy_key'] ?? ''),
        'audit_event' => $auditEvent,
        'metadata' => $metadata,
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}
