<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/audit_log_repository.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/lab_sample18_generated_submit_idempotency_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/sql_dialect.php';

function app_lab_sample18_task_board_path(): string
{
    return '/samples/sample18-task-board';
}

function app_lab_sample18_task_board_generated_submit_path(): string
{
    return app_lab_sample18_task_board_path() . '/no-code/generated-submit';
}

function app_lab_sample18_task_board_is_available(PDO $pdo): bool
{
    if (!app_sql_table_exists($pdo, 'projects')) {
        return false;
    }

    $statement = $pdo->prepare("SELECT 1 FROM projects WHERE project_key = 'SAMPLE18' LIMIT 1");
    $statement->execute();

    return $statement->fetchColumn() !== false;
}

function app_lab_sample18_task_board_create_schema(PDO $pdo): void
{
    if (app_sql_table_exists($pdo, 'TaskCard')) {
        return;
    }

    if (app_sql_dialect_from_pdo($pdo) === 'sqlite') {
        $pdo->exec(
            "CREATE TABLE TaskCard (
                Id INTEGER PRIMARY KEY AUTOINCREMENT,
                Title TEXT NOT NULL,
                Body TEXT NOT NULL,
                Status TEXT NOT NULL DEFAULT 'todo',
                AssignedTo TEXT NOT NULL DEFAULT '',
                Priority INTEGER NOT NULL DEFAULT 0,
                DueDate TEXT DEFAULT NULL,
                CompletedAt TEXT DEFAULT NULL,
                UpdatedAt TEXT NOT NULL
            )",
        );
        return;
    }

    $pdo->exec(
        "CREATE TABLE TaskCard (
            Id BIGINT NOT NULL AUTO_INCREMENT,
            Title VARCHAR(255) NOT NULL,
            Body TEXT NOT NULL,
            Status VARCHAR(32) NOT NULL DEFAULT 'todo',
            AssignedTo VARCHAR(100) NOT NULL DEFAULT '',
            Priority INT NOT NULL DEFAULT 0,
            DueDate DATE DEFAULT NULL,
            CompletedAt DATETIME DEFAULT NULL,
            UpdatedAt DATETIME NOT NULL,
            PRIMARY KEY (Id)
        )",
    );
}

/**
 * @return list<array<string,mixed>>
 */
function app_lab_sample18_task_board_fetch_rows(PDO $pdo, string $status): array
{
    $sql = 'SELECT Id, Title, Body, Status, AssignedTo, Priority, DueDate, CompletedAt, UpdatedAt FROM TaskCard';
    $params = [];
    if ($status !== '') {
        $sql .= ' WHERE Status = :status';
        $params[':status'] = $status;
    }
    $sql .= ' ORDER BY CASE WHEN DueDate IS NULL THEN 1 ELSE 0 END, DueDate ASC, Priority DESC, Id ASC';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * @return array<string,mixed>|null
 */
function app_lab_sample18_task_board_fetch_row(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare('SELECT Id, Title, Body, Status, AssignedTo, Priority, DueDate, CompletedAt, UpdatedAt FROM TaskCard WHERE Id = :id');
    $statement->execute([':id' => $id]);
    $row = $statement->fetch();

    return is_array($row) ? $row : null;
}

function app_lab_sample18_task_board_redirect(array $request, string $message = ''): void
{
    $location = app_lab_sample18_task_board_path();
    if ($message !== '') {
        $location .= '?message=' . rawurlencode($message);
    }
    app_send_redirect_response($request, $location);
}

/**
 * @return array<string,array<string,mixed>>
 */
function app_lab_sample18_task_board_generated_submit_contracts(): array
{
    return [
        'create_task_card' => [
            'operation_key' => 'create_task_card',
            'curated_route_action' => 'create',
            'db_access_function' => 'InsertTaskCard',
            'key_fields' => [],
            'required_client_fields' => ['title'],
            'optional_client_fields' => ['body', 'assigned_to', 'priority', 'due_date'],
            'fixed_fields' => ['status' => 'todo'],
            'server_managed_fields' => ['completed_at', 'updated_at'],
        ],
        'update_task_card' => [
            'operation_key' => 'update_task_card',
            'curated_route_action' => 'update',
            'db_access_function' => 'UpdateTaskCard',
            'key_fields' => ['id'],
            'required_client_fields' => ['title'],
            'optional_client_fields' => ['body', 'status', 'assigned_to', 'priority', 'due_date'],
            'derived_fields' => ['completed_at'],
            'server_managed_fields' => ['updated_at'],
        ],
        'complete_task_card' => [
            'operation_key' => 'complete_task_card',
            'curated_route_action' => 'complete',
            'db_access_function' => 'CompleteTaskCard',
            'key_fields' => ['id'],
            'required_client_fields' => [],
            'optional_client_fields' => [],
            'fixed_fields' => ['status' => 'done'],
            'server_managed_fields' => ['completed_at', 'updated_at'],
        ],
    ];
}

/**
 * @param array<string,mixed> $input
 * @return array{
 *     ok:bool,
 *     operation_key:string,
 *     curated_route_action:string,
 *     db_access_function:string,
 *     payload:array<string,mixed>,
 *     ignored_input_fields:list<string>,
 *     errors:list<string>,
 *     failure_code:string
 * }
 */
function app_lab_sample18_task_board_normalize_generated_submit_request(
    string $operationKey,
    array $input,
    string $now,
): array {
    $contracts = app_lab_sample18_task_board_generated_submit_contracts();
    $contract = $contracts[$operationKey] ?? null;
    if (!is_array($contract)) {
        return app_lab_sample18_task_board_generated_submit_request_result(
            false,
            $operationKey,
            '',
            '',
            [],
            array_keys($input),
            ['operation.unknown'],
            'unknown_operation',
        );
    }

    $errors = [];
    $payload = [];
    if (in_array('id', $contract['key_fields'] ?? [], true)) {
        $id = (int) ($input['id'] ?? 0);
        if ($id <= 0) {
            $errors[] = 'id.invalid';
        } else {
            $payload['id'] = $id;
        }
    }

    if (in_array('title', $contract['required_client_fields'] ?? [], true)) {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            $errors[] = 'title.required';
        }
        $payload['title'] = $title;
    }

    if (in_array('body', $contract['optional_client_fields'] ?? [], true)) {
        $payload['body'] = trim((string) ($input['body'] ?? ''));
    }
    if (in_array('assigned_to', $contract['optional_client_fields'] ?? [], true)) {
        $payload['assigned_to'] = trim((string) ($input['assigned_to'] ?? ''));
    }
    if (in_array('priority', $contract['optional_client_fields'] ?? [], true)) {
        $payload['priority'] = max(0, min(100, (int) ($input['priority'] ?? 10)));
    }
    if (in_array('due_date', $contract['optional_client_fields'] ?? [], true)) {
        $payload['due_date'] = app_lab_sample18_task_board_normalize_due_date((string) ($input['due_date'] ?? ''));
    }
    if (in_array('status', $contract['optional_client_fields'] ?? [], true)) {
        $payload['status'] = app_lab_sample18_task_board_normalize_status((string) ($input['status'] ?? ''));
    }

    foreach (($contract['fixed_fields'] ?? []) as $field => $value) {
        if (is_string($field)) {
            $payload[$field] = $value;
        }
    }
    if (in_array('completed_at', $contract['derived_fields'] ?? [], true)) {
        $payload['completed_at'] = ($payload['status'] ?? '') === 'done' ? $now : null;
    }
    foreach (($contract['server_managed_fields'] ?? []) as $field) {
        if ($field === 'completed_at') {
            $payload[$field] = ($payload['status'] ?? '') === 'done' ? $now : null;
            continue;
        }
        if ($field === 'updated_at') {
            $payload[$field] = $now;
        }
    }

    $allowedInputFields = array_merge(
        $contract['key_fields'] ?? [],
        $contract['required_client_fields'] ?? [],
        $contract['optional_client_fields'] ?? [],
    );
    $ignoredInputFields = array_values(array_diff(array_keys($input), $allowedInputFields));
    sort($ignoredInputFields);
    ksort($payload);

    return app_lab_sample18_task_board_generated_submit_request_result(
        $errors === [],
        (string) ($contract['operation_key'] ?? $operationKey),
        (string) ($contract['curated_route_action'] ?? ''),
        (string) ($contract['db_access_function'] ?? ''),
        $payload,
        $ignoredInputFields,
        $errors,
        $errors === [] ? '' : 'validation_error',
    );
}

function app_lab_sample18_task_board_normalize_due_date(string $value): ?string
{
    $normalized = trim($value);

    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized) === 1 ? $normalized : null;
}

function app_lab_sample18_task_board_normalize_status(string $value): string
{
    $normalized = trim($value);

    return in_array($normalized, ['todo', 'doing', 'done'], true) ? $normalized : 'todo';
}

/**
 * @param array<string,mixed> $payload
 * @param list<string> $ignoredInputFields
 * @param list<string> $errors
 * @return array{
 *     ok:bool,
 *     operation_key:string,
 *     curated_route_action:string,
 *     db_access_function:string,
 *     payload:array<string,mixed>,
 *     ignored_input_fields:list<string>,
 *     errors:list<string>,
 *     failure_code:string
 * }
 */
function app_lab_sample18_task_board_generated_submit_request_result(
    bool $ok,
    string $operationKey,
    string $curatedRouteAction,
    string $dbAccessFunction,
    array $payload,
    array $ignoredInputFields,
    array $errors,
    string $failureCode,
): array {
    return [
        'ok' => $ok,
        'operation_key' => $operationKey,
        'curated_route_action' => $curatedRouteAction,
        'db_access_function' => $dbAccessFunction,
        'payload' => $payload,
        'ignored_input_fields' => $ignoredInputFields,
        'errors' => $errors,
        'failure_code' => $failureCode,
    ];
}

function app_lab_sample18_task_board_generated_submit_db_access_field_name(string $fieldName): string
{
    $map = [
        'id' => 'Id',
        'title' => 'Title',
        'body' => 'Body',
        'status' => 'Status',
        'assigned_to' => 'AssignedTo',
        'priority' => 'Priority',
        'due_date' => 'DueDate',
        'completed_at' => 'CompletedAt',
        'updated_at' => 'UpdatedAt',
    ];

    return $map[$fieldName] ?? $fieldName;
}

/**
 * @param array{
 *     ok:bool,
 *     operation_key:string,
 *     curated_route_action:string,
 *     db_access_function:string,
 *     payload:array<string,mixed>,
 *     ignored_input_fields:list<string>,
 *     errors:list<string>,
 *     failure_code:string
 * } $normalized
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_dispatcher_dry_run(array $normalized): array
{
    if (!($normalized['ok'] ?? false)) {
        return [
            'ok' => false,
            'dispatch_state' => 'not_ready',
            'executed' => false,
            'mutation_enabled' => false,
            'failure_code' => (string) ($normalized['failure_code'] ?? 'validation_error'),
        ];
    }

    $boundFields = [];
    foreach (($normalized['payload'] ?? []) as $fieldName => $value) {
        if (!is_string($fieldName)) {
            continue;
        }
        $boundFields[app_lab_sample18_task_board_generated_submit_db_access_field_name($fieldName)] = $value;
    }

    return [
        'ok' => true,
        'dispatch_state' => 'dry_run',
        'executed' => false,
        'mutation_enabled' => false,
        'operation_key' => (string) ($normalized['operation_key'] ?? ''),
        'curated_route_action' => (string) ($normalized['curated_route_action'] ?? ''),
        'db_access_class' => 'TaskCardDBAccess',
        'db_access_function' => (string) ($normalized['db_access_function'] ?? ''),
        'data_object' => 'TaskCardData',
        'method_arguments' => [
            'TaskCardObj' => $boundFields,
        ],
        'bound_fields' => $boundFields,
    ];
}

/**
 * @param array<string,mixed> $value
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_canonical_array(array $value): array
{
    ksort($value);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = app_lab_sample18_task_board_generated_submit_canonical_array($item);
        }
    }

    return $value;
}

/**
 * @param array<string,mixed> $dispatcherResult
 */
function app_lab_sample18_task_board_generated_submit_payload_fingerprint(array $dispatcherResult): string
{
    $payload = [
        'route_version' => 'sample18-generated-submit-v1',
        'operation_key' => (string) ($dispatcherResult['operation_key'] ?? ''),
        'bound_fields' => is_array($dispatcherResult['bound_fields'] ?? null) ? $dispatcherResult['bound_fields'] : [],
    ];
    $json = json_encode(app_lab_sample18_task_board_generated_submit_canonical_array($payload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return hash('sha256', is_string($json) ? $json : '');
}

/**
 * @param array<string,mixed> $dispatcherResult
 */
function app_lab_sample18_task_board_generated_submit_dedupe_key_preview(array $dispatcherResult): string
{
    $operationKey = (string) ($dispatcherResult['operation_key'] ?? '');
    if ($operationKey === '' || !($dispatcherResult['ok'] ?? false)) {
        return '';
    }

    return 'sample18.generated_submit.' . $operationKey . '.'
        . substr(app_lab_sample18_task_board_generated_submit_payload_fingerprint($dispatcherResult), 0, 32);
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
    array $normalized,
    array $dispatcherResult,
    string $result,
    string $failureCode,
): array {
    if (!($normalized['ok'] ?? false) || !($dispatcherResult['ok'] ?? false)) {
        return [
            'dedupe_key_preview' => '',
            'payload_fingerprint' => '',
            'audit_event_preview' => [],
        ];
    }

    $fingerprint = app_lab_sample18_task_board_generated_submit_payload_fingerprint($dispatcherResult);
    $dedupeKey = app_lab_sample18_task_board_generated_submit_dedupe_key_preview($dispatcherResult);
    $metadata = [
        'operation_key' => (string) ($normalized['operation_key'] ?? ''),
        'curated_route_action' => (string) ($normalized['curated_route_action'] ?? ''),
        'db_access_function' => (string) ($normalized['db_access_function'] ?? ''),
        'dispatch_state' => (string) ($dispatcherResult['dispatch_state'] ?? ''),
        'mutation_enabled' => (bool) ($dispatcherResult['mutation_enabled'] ?? false),
        'executed' => (bool) ($dispatcherResult['executed'] ?? false),
        'failure_code' => $failureCode,
        'dedupe_key' => $dedupeKey,
        'payload_fingerprint' => $fingerprint,
        'ignored_input_fields' => is_array($normalized['ignored_input_fields'] ?? null) ? $normalized['ignored_input_fields'] : [],
        'normalized_payload' => is_array($normalized['payload'] ?? null) ? $normalized['payload'] : [],
        'dispatcher_bound_fields' => is_array($dispatcherResult['bound_fields'] ?? null) ? $dispatcherResult['bound_fields'] : [],
    ];

    return [
        'dedupe_key_preview' => $dedupeKey,
        'payload_fingerprint' => $fingerprint,
        'audit_event_preview' => [
            'actor_login_id' => '',
            'actor_source' => 'web_lab_login',
            'project_key' => 'SAMPLE18',
            'event_type' => 'sample18.generated_submit.requested',
            'target_type' => 'sample18_task_card',
            'target_key' => $dedupeKey,
            'result' => $result,
            'message' => $failureCode,
            'metadata' => $metadata,
        ],
    ];
}

/**
 * @param array<string,mixed> $auditEvent
 * @param array<string,mixed> $principal
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_audit_event_with_actor(
    array $auditEvent,
    array $principal,
): array {
    if ($auditEvent === []) {
        return [];
    }

    if (trim((string) ($auditEvent['actor_login_id'] ?? '')) === '') {
        $auditEvent['actor_login_id'] = trim((string) ($principal['id'] ?? ''));
    }
    if (trim((string) ($auditEvent['actor_source'] ?? '')) === '') {
        $auditEvent['actor_source'] = trim((string) ($principal['auth_source'] ?? 'unknown'));
    }

    return $auditEvent;
}

/**
 * @param array<string,mixed>|null $app
 * @param array<string,mixed> $auditEvent
 * @return array{ok:bool,skipped:bool,status:string,item:array<string,mixed>,error:string,reason:string}
 */
function app_lab_sample18_task_board_generated_submit_append_audit_event(?array $app, array $auditEvent): array
{
    if ($app === null) {
        return [
            'ok' => true,
            'skipped' => true,
            'status' => 'skipped',
            'item' => [],
            'error' => '',
            'reason' => 'no_app',
        ];
    }

    if ($auditEvent === []) {
        return [
            'ok' => true,
            'skipped' => true,
            'status' => 'skipped',
            'item' => [],
            'error' => '',
            'reason' => 'no_audit_event',
        ];
    }

    if (trim((string) ($auditEvent['actor_login_id'] ?? '')) === '') {
        return [
            'ok' => true,
            'skipped' => true,
            'status' => 'skipped',
            'item' => [],
            'error' => '',
            'reason' => 'missing_actor',
        ];
    }

    $append = app_audit_log_append($app, $auditEvent);

    return [
        'ok' => (bool) ($append['ok'] ?? false),
        'skipped' => false,
        'status' => ($append['ok'] ?? false) ? 'appended' : 'failed',
        'item' => is_array($append['item'] ?? null) ? $append['item'] : [],
        'error' => (string) ($append['error'] ?? ''),
        'reason' => '',
    ];
}

/**
 * @param array<string,mixed>|null $app
 * @param array<string,mixed> $executionUpdatePlan
 * @param array<string,mixed> $executionGuard
 * @param array<string,mixed> $principal
 * @param array<string,mixed> $metadata
 * @return array{ok:bool,skipped:bool,status:string,item:array<string,mixed>,error:string,reason:string}
 */
function app_lab_sample18_task_board_generated_submit_append_execution_audit_event(
    ?array $app,
    array $executionUpdatePlan,
    array $executionGuard,
    array $principal,
    string $executionStatus,
    string $executionResultCode,
    string $transactionStatus,
    array $metadata = [],
): array {
    if (!in_array($executionStatus, ['executed', 'failed', 'rolled_back'], true)) {
        return [
            'ok' => false,
            'skipped' => false,
            'status' => 'failed',
            'item' => [],
            'error' => 'sample18 generated submit execution audit status is not supported: ' . $executionStatus,
            'reason' => 'invalid_execution_status',
        ];
    }
    if (trim($executionResultCode) === '') {
        return [
            'ok' => false,
            'skipped' => false,
            'status' => 'failed',
            'item' => [],
            'error' => 'sample18 generated submit execution audit result code is required.',
            'reason' => 'missing_execution_result_code',
        ];
    }
    if (!in_array($transactionStatus, ['committed', 'rolled_back', 'not_opened'], true)) {
        return [
            'ok' => false,
            'skipped' => false,
            'status' => 'failed',
            'item' => [],
            'error' => 'sample18 generated submit execution audit transaction status is not supported: ' . $transactionStatus,
            'reason' => 'invalid_transaction_status',
        ];
    }
    if (($executionGuard['status'] ?? '') !== 'allowed' || !($executionGuard['ready'] ?? false)) {
        return [
            'ok' => false,
            'skipped' => false,
            'status' => 'failed',
            'item' => [],
            'error' => 'sample18 generated submit execution guard is not ready.',
            'reason' => 'execution_guard_not_ready',
        ];
    }

    $dedupeKey = (string) ($executionGuard['dedupe_key'] ?? '');
    if ($dedupeKey === '') {
        return [
            'ok' => false,
            'skipped' => false,
            'status' => 'failed',
            'item' => [],
            'error' => 'sample18 generated submit execution audit dedupe key is required.',
            'reason' => 'dedupe_key_missing',
        ];
    }
    $requestAuditEventKey = (string) ($executionGuard['request_audit_event_key'] ?? '');
    if ($requestAuditEventKey === '') {
        return [
            'ok' => false,
            'skipped' => false,
            'status' => 'failed',
            'item' => [],
            'error' => 'sample18 generated submit execution audit request audit event key is required.',
            'reason' => 'request_audit_event_key_missing',
        ];
    }

    $plannedAudit = is_array($executionUpdatePlan['execution_audit_update'] ?? null)
        ? $executionUpdatePlan['execution_audit_update']
        : [];
    $eventMetadata = [
        'request_audit_event_key' => $requestAuditEventKey,
        'dedupe_key' => $dedupeKey,
        'operation_key' => (string) ($executionGuard['operation_key'] ?? ''),
        'db_access_class' => (string) ($executionGuard['db_access_class'] ?? ''),
        'db_access_function' => (string) ($executionGuard['db_access_function'] ?? ''),
        'execution_status' => $executionStatus,
        'execution_result_code' => $executionResultCode,
        'transaction_status' => $transactionStatus,
        'planned_transaction_status' => (string) ($plannedAudit['transaction_status'] ?? ''),
        'details' => $metadata,
    ];

    return app_lab_sample18_task_board_generated_submit_append_audit_event($app, [
        'actor_login_id' => trim((string) ($principal['id'] ?? '')),
        'actor_source' => trim((string) ($principal['auth_source'] ?? 'unknown')),
        'project_key' => 'SAMPLE18',
        'event_type' => 'sample18.generated_submit.executed',
        'target_type' => 'sample18_task_card',
        'target_key' => $dedupeKey,
        'result' => $executionStatus,
        'message' => $executionResultCode,
        'metadata' => $eventMetadata,
    ]);
}

/**
 * @return array{ok:bool,status:string,created:bool,dedupe_key:string,item:array<string,mixed>,error:string,reason:string}
 */
function app_lab_sample18_task_board_generated_submit_idempotency_skipped(
    string $reason,
    string $dedupeKey = '',
): array {
    return [
        'ok' => true,
        'status' => 'skipped',
        'created' => false,
        'dedupe_key' => $dedupeKey,
        'item' => [],
        'error' => '',
        'reason' => $reason,
    ];
}

/**
 * @param array<string,mixed>|null $app
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @param array<string,mixed> $idempotencyAuditPreview
 * @param array<string,mixed> $auditAppend
 * @return array{ok:bool,status:string,created:bool,dedupe_key:string,item:array<string,mixed>,error:string,reason:string}
 */
function app_lab_sample18_task_board_generated_submit_apply_idempotency(
    ?array $app,
    array $normalized,
    array $dispatcherResult,
    array $idempotencyAuditPreview,
    array $auditAppend,
): array {
    $dedupeKey = trim((string) ($idempotencyAuditPreview['dedupe_key_preview'] ?? ''));
    if ($app === null) {
        return app_lab_sample18_task_board_generated_submit_idempotency_skipped('no_app', $dedupeKey);
    }

    if ($dedupeKey === '') {
        return app_lab_sample18_task_board_generated_submit_idempotency_skipped('no_dedupe_key', '');
    }

    $auditItem = is_array($auditAppend['item'] ?? null) ? $auditAppend['item'] : [];
    $append = app_lab_sample18_generated_submit_idempotency_create_or_reuse_record($app, [
        'dedupe_key' => $dedupeKey,
        'project_key' => 'SAMPLE18',
        'operation_key' => (string) ($normalized['operation_key'] ?? ''),
        'payload_fingerprint' => (string) ($idempotencyAuditPreview['payload_fingerprint'] ?? ''),
        'result' => 'blocked',
        'failure_code' => 'generated_submit_disabled',
        'first_audit_event_key' => (string) ($auditItem['event_key'] ?? ''),
        'metadata' => [
            'audit_append_status' => (string) ($auditAppend['status'] ?? ''),
            'audit_append_ok' => (bool) ($auditAppend['ok'] ?? false),
            'operation_key' => (string) ($normalized['operation_key'] ?? ''),
            'curated_route_action' => (string) ($normalized['curated_route_action'] ?? ''),
            'db_access_function' => (string) ($normalized['db_access_function'] ?? ''),
            'ignored_input_fields' => is_array($normalized['ignored_input_fields'] ?? null)
                ? $normalized['ignored_input_fields']
                : [],
            'normalized_payload' => is_array($normalized['payload'] ?? null) ? $normalized['payload'] : [],
            'dispatcher_bound_fields' => is_array($dispatcherResult['bound_fields'] ?? null)
                ? $dispatcherResult['bound_fields']
                : [],
            'dispatch_state' => (string) ($dispatcherResult['dispatch_state'] ?? ''),
            'mutation_enabled' => (bool) ($dispatcherResult['mutation_enabled'] ?? false),
            'executed' => (bool) ($dispatcherResult['executed'] ?? false),
        ],
    ]);

    return [
        'ok' => (bool) ($append['ok'] ?? false),
        'status' => (string) ($append['result'] ?? (($append['ok'] ?? false) ? 'recorded' : 'failed')),
        'created' => (bool) ($append['created'] ?? false),
        'dedupe_key' => $dedupeKey,
        'item' => is_array($append['item'] ?? null) ? $append['item'] : [],
        'error' => (string) ($append['error'] ?? ''),
        'reason' => '',
    ];
}

function app_lab_sample18_task_board_generated_submit_mutation_enablement_flag(array $app): bool
{
    if (array_key_exists('sample18_generated_submit_mutation_enabled', $app)) {
        return (bool) $app['sample18_generated_submit_mutation_enabled'];
    }

    return trim((string) getenv('MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED')) === '1';
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @param array<string,mixed> $auditAppend
 * @param array<string,mixed> $idempotency
 * @return array{status:string,ready:bool,mutation_enabled:bool,executed:bool,reasons:list<string>}
 */
function app_lab_sample18_task_board_generated_submit_mutation_gate(
    array $app,
    array $normalized,
    array $dispatcherResult,
    array $auditAppend,
    array $idempotency,
): array {
    $reasons = [];
    $failed = false;

    if (!app_lab_sample18_task_board_generated_submit_mutation_enablement_flag($app)) {
        $reasons[] = 'enablement_flag_disabled';
    }
    if (!($normalized['ok'] ?? false)) {
        $reasons[] = 'request_not_valid';
    }
    if (!($dispatcherResult['ok'] ?? false)) {
        $reasons[] = 'dispatcher_not_ready';
    }
    if (($dispatcherResult['executed'] ?? false) || ($dispatcherResult['mutation_enabled'] ?? false)) {
        $reasons[] = 'dispatcher_not_dry_run';
    }

    $auditStatus = (string) ($auditAppend['status'] ?? '');
    if ($auditStatus !== 'appended') {
        $reasons[] = $auditStatus === 'failed' ? 'audit_append_failed' : 'audit_append_not_appended';
        $failed = $failed || $auditStatus === 'failed';
    }

    $idempotencyStatus = (string) ($idempotency['status'] ?? '');
    if ($idempotencyStatus !== 'recorded' || !($idempotency['created'] ?? false)) {
        if ($idempotencyStatus === 'duplicate') {
            $reasons[] = 'duplicate_generated_submit';
        } elseif ($idempotencyStatus === 'failed') {
            $reasons[] = 'idempotency_failed';
            $failed = true;
        } elseif ($idempotencyStatus === 'skipped') {
            $reasons[] = 'idempotency_skipped';
        } else {
            $reasons[] = 'idempotency_not_recorded';
        }
    }

    if ($reasons === []) {
        return [
            'status' => 'ready',
            'ready' => true,
            'mutation_enabled' => false,
            'executed' => false,
            'reasons' => [],
        ];
    }

    return [
        'status' => $failed ? 'failed' : (in_array('enablement_flag_disabled', $reasons, true) ? 'disabled' : 'blocked'),
        'ready' => false,
        'mutation_enabled' => false,
        'executed' => false,
        'reasons' => array_values(array_unique($reasons)),
    ];
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @param array{status:string,ready:bool,mutation_enabled:bool,executed:bool,reasons:list<string>} $mutationGate
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
    array $normalized,
    array $dispatcherResult,
    array $mutationGate,
): array {
    $reasons = [];
    $failed = false;

    if (!($normalized['ok'] ?? false)) {
        $reasons[] = 'request_not_valid';
    }
    if (!($dispatcherResult['ok'] ?? false)) {
        $reasons[] = 'dispatcher_not_ready';
    }
    if (($dispatcherResult['executed'] ?? false) || ($dispatcherResult['mutation_enabled'] ?? false)) {
        $reasons[] = 'dispatcher_not_dry_run';
        $failed = true;
    }
    if (($mutationGate['status'] ?? '') !== 'ready' || !($mutationGate['ready'] ?? false)) {
        $reasons[] = 'mutation_gate_not_ready';
        foreach (($mutationGate['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
        $failed = $failed || (string) ($mutationGate['status'] ?? '') === 'failed';
    }

    $contracts = app_lab_sample18_task_board_generated_submit_contracts();
    $operationKey = (string) ($normalized['operation_key'] ?? '');
    $expectedFunction = (string) ($contracts[$operationKey]['db_access_function'] ?? '');
    $dbAccessFunction = (string) ($dispatcherResult['db_access_function'] ?? '');
    if ($operationKey === '' || $expectedFunction === '' || $dbAccessFunction !== $expectedFunction) {
        $reasons[] = 'db_access_function_not_allowlisted';
        $failed = true;
    }

    return [
        'status' => $reasons === [] ? 'planned' : ($failed ? 'failed' : 'blocked'),
        'ready' => $reasons === [],
        'mutation_enabled' => false,
        'executed' => false,
        'operation_key' => $operationKey,
        'curated_route_action' => (string) ($normalized['curated_route_action'] ?? ''),
        'db_access_class' => 'TaskCardDBAccess',
        'db_access_function' => $dbAccessFunction,
        'data_object' => 'TaskCardData',
        'method_arguments' => is_array($dispatcherResult['method_arguments'] ?? null)
            ? $dispatcherResult['method_arguments']
            : [],
        'transaction' => 'not_opened',
        'reasons' => array_values(array_unique($reasons)),
    ];
}

/**
 * @param array<string,mixed> $executionPlan
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_transaction_plan(array $executionPlan): array
{
    $reasons = [];
    $failed = false;

    if (($executionPlan['status'] ?? '') !== 'planned' || !($executionPlan['ready'] ?? false)) {
        $reasons[] = 'execution_plan_not_ready';
        foreach (($executionPlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
        $failed = (string) ($executionPlan['status'] ?? '') === 'failed';
    }
    if (($executionPlan['executed'] ?? false) || ($executionPlan['mutation_enabled'] ?? false)) {
        $reasons[] = 'execution_plan_not_metadata_only';
        $failed = true;
    }

    $planned = $reasons === [];

    return [
        'status' => $planned ? 'planned' : ($failed ? 'failed' : 'blocked'),
        'ready' => $planned,
        'transaction' => $planned ? 'planned_not_opened' : 'not_opened',
        'db_handle' => 'sample18_application_db',
        'audit_store' => 'config_db_audit_log',
        'idempotency_store' => 'config_db_idempotency',
        'will_execute' => false,
        'will_update_audit' => false,
        'will_update_idempotency' => false,
        'rollback_policy' => [
            'on_dbaccess_exception' => 'rollback',
            'on_unexpected_result' => 'rollback',
            'on_post_execution_update_failure' => 'rollback',
        ],
        'post_execution_audit_update' => [
            'status' => 'planned_not_written',
            'event_type' => 'sample18.generated_submit.executed',
            'source_event_key' => '',
            'db_access_class' => (string) ($executionPlan['db_access_class'] ?? ''),
            'db_access_function' => (string) ($executionPlan['db_access_function'] ?? ''),
            'transaction' => $planned ? 'planned_not_opened' : 'not_opened',
        ],
        'post_execution_idempotency_update' => [
            'status' => 'planned_not_written',
            'execution_status' => 'planned',
            'transaction' => $planned ? 'planned_not_opened' : 'not_opened',
        ],
        'reasons' => array_values(array_unique($reasons)),
    ];
}

/**
 * @param array<string,mixed> $transactionPlan
 * @param array<string,mixed> $auditAppend
 * @param array<string,mixed> $idempotency
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_execution_update_plan(
    array $transactionPlan,
    array $auditAppend,
    array $idempotency,
): array {
    $reasons = [];
    $failed = false;

    if (($transactionPlan['status'] ?? '') !== 'planned' || !($transactionPlan['ready'] ?? false)) {
        $reasons[] = 'transaction_plan_not_ready';
        foreach (($transactionPlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
        $failed = (string) ($transactionPlan['status'] ?? '') === 'failed';
    }
    if (
        ($transactionPlan['will_execute'] ?? false)
        || ($transactionPlan['will_update_audit'] ?? false)
        || ($transactionPlan['will_update_idempotency'] ?? false)
    ) {
        $reasons[] = 'transaction_plan_not_metadata_only';
        $failed = true;
    }

    $auditItem = is_array($auditAppend['item'] ?? null) ? $auditAppend['item'] : [];
    $idempotencyItem = is_array($idempotency['item'] ?? null) ? $idempotency['item'] : [];
    $dedupeKey = (string) ($idempotency['dedupe_key'] ?? ($idempotencyItem['dedupe_key'] ?? ''));
    if ($dedupeKey === '') {
        $reasons[] = 'dedupe_key_missing';
    }

    $planned = $reasons === [];

    return [
        'status' => $planned ? 'planned' : ($failed ? 'failed' : 'blocked'),
        'ready' => $planned,
        'will_write_audit' => false,
        'will_update_idempotency' => false,
        'will_execute' => false,
        'audit_store' => (string) ($transactionPlan['audit_store'] ?? 'config_db_audit_log'),
        'idempotency_store' => (string) ($transactionPlan['idempotency_store'] ?? 'config_db_idempotency'),
        'execution_audit_update' => [
            'status' => 'planned_not_written',
            'event_type' => 'sample18.generated_submit.executed',
            'target_key' => $dedupeKey,
            'request_audit_event_key' => (string) ($auditItem['event_key'] ?? ''),
            'result' => 'executed',
            'transaction_status' => 'planned_not_opened',
            'metadata' => [
                'dedupe_key' => $dedupeKey,
                'db_access_class' => (string) (($transactionPlan['post_execution_audit_update']['db_access_class'] ?? '')),
                'db_access_function' => (string) (($transactionPlan['post_execution_audit_update']['db_access_function'] ?? '')),
            ],
        ],
        'idempotency_execution_update' => [
            'status' => 'planned_not_written',
            'dedupe_key' => $dedupeKey,
            'execution_status' => 'planned',
            'execution_result_code' => 'planned_not_executed',
            'transaction_status' => 'planned_not_opened',
        ],
        'reasons' => array_values(array_unique($reasons)),
    ];
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $auditAppend
 * @param array<string,mixed> $idempotency
 * @param array<string,mixed> $mutationGate
 * @param array<string,mixed> $executionPlan
 * @param array<string,mixed> $transactionPlan
 * @param array<string,mixed> $executionUpdatePlan
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_execution_guard(
    array $normalized,
    array $auditAppend,
    array $idempotency,
    array $mutationGate,
    array $executionPlan,
    array $transactionPlan,
    array $executionUpdatePlan,
): array {
    $reasons = [];
    $failed = false;

    if (!($normalized['ok'] ?? false)) {
        $reasons[] = 'request_not_ready';
        $failed = true;
    }
    if (($auditAppend['status'] ?? '') !== 'appended') {
        $reasons[] = 'audit_append_not_ready';
        $failed = (string) ($auditAppend['status'] ?? '') === 'failed';
    }
    if (($idempotency['status'] ?? '') !== 'recorded' || !($idempotency['created'] ?? false)) {
        $reasons[] = 'idempotency_not_ready';
        if (($idempotency['status'] ?? '') === 'duplicate') {
            $reasons[] = 'duplicate_generated_submit';
        }
        $failed = $failed || (string) ($idempotency['status'] ?? '') === 'failed';
    }

    foreach ([
        'mutation_gate' => $mutationGate,
        'execution_plan' => $executionPlan,
        'transaction_plan' => $transactionPlan,
        'execution_update_plan' => $executionUpdatePlan,
    ] as $name => $plan) {
        $expectedStatus = $name === 'mutation_gate' ? 'ready' : 'planned';
        if (($plan['status'] ?? '') !== $expectedStatus || !($plan['ready'] ?? false)) {
            $reasons[] = $name . '_not_ready';
            foreach (($plan['reasons'] ?? []) as $reason) {
                if (is_string($reason) && $reason !== '') {
                    $reasons[] = $reason;
                }
            }
            $failed = $failed || (string) ($plan['status'] ?? '') === 'failed';
        }
    }

    if (
        ($executionPlan['mutation_enabled'] ?? false)
        || ($executionPlan['executed'] ?? false)
        || ($transactionPlan['will_execute'] ?? false)
        || ($transactionPlan['will_update_audit'] ?? false)
        || ($transactionPlan['will_update_idempotency'] ?? false)
        || ($executionUpdatePlan['will_execute'] ?? false)
        || ($executionUpdatePlan['will_write_audit'] ?? false)
        || ($executionUpdatePlan['will_update_idempotency'] ?? false)
    ) {
        $reasons[] = 'execution_metadata_not_metadata_only';
        $failed = true;
    }

    $operationKey = (string) ($normalized['operation_key'] ?? '');
    $contracts = app_lab_sample18_task_board_generated_submit_contracts();
    $contract = is_array($contracts[$operationKey] ?? null) ? $contracts[$operationKey] : [];
    $expectedFunction = (string) ($contract['db_access_function'] ?? '');
    $dbAccessClass = (string) ($executionPlan['db_access_class'] ?? '');
    $dbAccessFunction = (string) ($executionPlan['db_access_function'] ?? '');
    if ($dbAccessClass !== 'TaskCardDBAccess' || $dbAccessFunction === '' || $dbAccessFunction !== $expectedFunction) {
        $reasons[] = 'dbaccess_not_allowlisted';
        $failed = true;
    }

    $dbHandle = (string) ($transactionPlan['db_handle'] ?? '');
    if ($dbHandle !== 'sample18_application_db') {
        $reasons[] = 'db_handle_not_allowlisted';
        $failed = true;
    }

    $dedupeKey = (string) ($executionUpdatePlan['idempotency_execution_update']['dedupe_key'] ?? '');
    if ($dedupeKey === '') {
        $reasons[] = 'dedupe_key_missing';
    }
    $requestAuditEventKey = (string) ($executionUpdatePlan['execution_audit_update']['request_audit_event_key'] ?? '');
    if ($requestAuditEventKey === '') {
        $reasons[] = 'request_audit_event_key_missing';
    }

    $allowed = $reasons === [];

    return [
        'status' => $allowed ? 'allowed' : ($failed ? 'failed' : 'blocked'),
        'ready' => $allowed,
        'will_open_transaction' => false,
        'will_call_dbaccess' => false,
        'will_write_execution_audit' => false,
        'will_update_idempotency_execution' => false,
        'db_handle' => $dbHandle,
        'db_access_class' => $dbAccessClass,
        'db_access_function' => $dbAccessFunction,
        'operation_key' => $operationKey,
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'reasons' => array_values(array_unique($reasons)),
    ];
}

/**
 * @param array<string,mixed> $executionGuard
 * @param array<string,mixed> $executionUpdatePlan
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
    array $executionGuard,
    array $executionUpdatePlan,
    bool $executorEnabled,
): array {
    $reasons = [];
    $failed = false;

    if (!$executorEnabled) {
        $reasons[] = 'executor_feature_flag_disabled';
    }
    if (($executionGuard['status'] ?? '') !== 'allowed' || !($executionGuard['ready'] ?? false)) {
        $reasons[] = 'execution_guard_not_ready';
        foreach (($executionGuard['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
        $failed = (string) ($executionGuard['status'] ?? '') === 'failed';
    }
    if (($executionUpdatePlan['status'] ?? '') !== 'planned' || !($executionUpdatePlan['ready'] ?? false)) {
        $reasons[] = 'execution_update_plan_not_ready';
        foreach (($executionUpdatePlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
        $failed = $failed || (string) ($executionUpdatePlan['status'] ?? '') === 'failed';
    }

    if (
        ($executionGuard['will_open_transaction'] ?? false)
        || ($executionGuard['will_call_dbaccess'] ?? false)
        || ($executionGuard['will_write_execution_audit'] ?? false)
        || ($executionGuard['will_update_idempotency_execution'] ?? false)
        || ($executionUpdatePlan['will_execute'] ?? false)
        || ($executionUpdatePlan['will_write_audit'] ?? false)
        || ($executionUpdatePlan['will_update_idempotency'] ?? false)
    ) {
        $reasons[] = 'coordination_metadata_not_dry_run';
        $failed = true;
    }

    if ((string) ($executionGuard['dedupe_key'] ?? '') === '') {
        $reasons[] = 'dedupe_key_missing';
    }
    if ((string) ($executionGuard['request_audit_event_key'] ?? '') === '') {
        $reasons[] = 'request_audit_event_key_missing';
    }

    $planned = $reasons === [];

    return [
        'status' => $planned ? 'planned' : ($failed ? 'failed' : 'blocked'),
        'ready' => $planned,
        'will_open_transaction' => false,
        'will_call_dbaccess' => false,
        'will_write_execution_audit' => false,
        'will_update_idempotency_execution' => false,
        'app_db_transaction_boundary' => [
            'db_handle' => (string) ($executionGuard['db_handle'] ?? ''),
            'transaction_scope' => 'sample18_application_db_only',
            'cross_store_atomic' => false,
        ],
        'config_db_persistence_boundary' => [
            'audit_store' => (string) ($executionUpdatePlan['audit_store'] ?? 'config_db_audit_log'),
            'idempotency_store' => (string) ($executionUpdatePlan['idempotency_store'] ?? 'config_db_idempotency'),
            'cross_store_atomic' => false,
        ],
        'ordered_steps' => [
            ['step' => 'recheck_execution_guard', 'status' => 'planned_not_run'],
            ['step' => 'open_app_db_transaction', 'status' => 'planned_not_opened'],
            ['step' => 'call_dbaccess', 'status' => 'planned_not_called'],
            ['step' => 'classify_dbaccess_result', 'status' => 'planned_not_run'],
            ['step' => 'finish_app_db_transaction', 'status' => 'planned_not_run'],
            ['step' => 'append_execution_audit', 'status' => 'planned_not_written'],
            ['step' => 'update_idempotency_execution_outcome', 'status' => 'planned_not_written'],
        ],
        'operation_key' => (string) ($executionGuard['operation_key'] ?? ''),
        'dedupe_key' => (string) ($executionGuard['dedupe_key'] ?? ''),
        'request_audit_event_key' => (string) ($executionGuard['request_audit_event_key'] ?? ''),
        'reasons' => array_values(array_unique($reasons)),
    ];
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @param array<string,mixed> $executionGuard
 * @param array<string,mixed> $executorCoordinationPlan
 * @param callable(array<string,mixed>):mixed $dbaccessInvoker
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
    array $normalized,
    array $dispatcherResult,
    array $executionGuard,
    array $executorCoordinationPlan,
    bool $executorEnabled,
    callable $dbaccessInvoker,
): array {
    $operationKey = (string) ($normalized['operation_key'] ?? '');
    $contracts = app_lab_sample18_task_board_generated_submit_contracts();
    $expectedFunction = (string) ($contracts[$operationKey]['db_access_function'] ?? '');
    $dbAccessClass = (string) ($dispatcherResult['db_access_class'] ?? '');
    $dbAccessFunction = (string) ($dispatcherResult['db_access_function'] ?? '');
    $dataObject = (string) ($dispatcherResult['data_object'] ?? '');
    $methodArguments = is_array($dispatcherResult['method_arguments'] ?? null)
        ? $dispatcherResult['method_arguments']
        : [];
    $taskCardObj = is_array($methodArguments['TaskCardObj'] ?? null) ? $methodArguments['TaskCardObj'] : null;
    $dedupeKey = (string) ($executionGuard['dedupe_key'] ?? ($executorCoordinationPlan['dedupe_key'] ?? ''));
    $requestAuditEventKey = (string) ($executionGuard['request_audit_event_key'] ?? ($executorCoordinationPlan['request_audit_event_key'] ?? ''));

    $base = [
        'status' => 'skipped',
        'executed' => false,
        'invoked' => false,
        'db_access_class' => $dbAccessClass,
        'db_access_function' => $dbAccessFunction,
        'data_object' => $dataObject,
        'operation_key' => $operationKey,
        'result_code' => 'not_executed',
        'failure_code' => '',
        'error' => '',
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'reasons' => [],
    ];

    $reasons = [];
    if (!$executorEnabled) {
        $reasons[] = 'executor_feature_flag_disabled';
    }
    if (!($normalized['ok'] ?? false)) {
        $reasons[] = 'request_not_valid';
    }
    if (!($dispatcherResult['ok'] ?? false)) {
        $reasons[] = 'dispatcher_not_ready';
    }
    if (($executionGuard['status'] ?? '') !== 'allowed' || !($executionGuard['ready'] ?? false)) {
        $reasons[] = 'execution_guard_not_ready';
        foreach (($executionGuard['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
    }
    if (($executorCoordinationPlan['status'] ?? '') !== 'planned' || !($executorCoordinationPlan['ready'] ?? false)) {
        $reasons[] = 'executor_coordination_plan_not_ready';
        foreach (($executorCoordinationPlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
    }
    if ($operationKey === '' || !is_array($contracts[$operationKey] ?? null)) {
        $reasons[] = 'operation_not_allowlisted';
    }
    if ($expectedFunction === '' || $dbAccessClass !== 'TaskCardDBAccess' || $dbAccessFunction !== $expectedFunction) {
        $reasons[] = 'dbaccess_not_allowlisted';
    }
    if ($dataObject !== 'TaskCardData') {
        $reasons[] = 'data_object_not_allowlisted';
    }
    if ($taskCardObj === null) {
        $reasons[] = 'task_card_payload_missing';
    }
    if ($dedupeKey === '') {
        $reasons[] = 'dedupe_key_missing';
    }
    if ($requestAuditEventKey === '') {
        $reasons[] = 'request_audit_event_key_missing';
    }

    if ($reasons !== []) {
        $reasons = array_values(array_unique($reasons));
        $base['failure_code'] = $reasons[0];
        $base['reasons'] = $reasons;

        return $base;
    }

    $call = [
        'operation_key' => $operationKey,
        'db_access_class' => $dbAccessClass,
        'db_access_function' => $dbAccessFunction,
        'data_object' => $dataObject,
        'method_arguments' => $methodArguments,
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
    ];

    try {
        $result = $dbaccessInvoker($call);
    } catch (Throwable $throwable) {
        $base['status'] = 'failed';
        $base['invoked'] = true;
        $base['result_code'] = 'dbaccess_exception';
        $base['failure_code'] = 'dbaccess_exception';
        $base['error'] = $throwable->getMessage();
        $base['reasons'] = ['dbaccess_exception'];

        return $base;
    }

    if (!is_array($result)) {
        $base['status'] = 'failed';
        $base['invoked'] = true;
        $base['result_code'] = 'dbaccess_malformed_result';
        $base['failure_code'] = 'dbaccess_malformed_result';
        $base['reasons'] = ['dbaccess_malformed_result'];

        return $base;
    }

    $ok = (bool) ($result['ok'] ?? false);
    $base['invoked'] = true;
    $base['result_code'] = (string) ($result['result_code'] ?? ($ok ? 'dbaccess_executed' : 'dbaccess_failed'));
    if (array_key_exists('rows_affected', $result)) {
        $base['rows_affected'] = (int) $result['rows_affected'];
    }
    if (array_key_exists('insert_id', $result)) {
        $base['insert_id'] = (int) $result['insert_id'];
    }

    if (!$ok) {
        $base['status'] = 'failed';
        $base['failure_code'] = (string) ($result['failure_code'] ?? 'dbaccess_failed');
        $base['error'] = (string) ($result['error'] ?? '');
        $base['reasons'] = [$base['failure_code']];

        return $base;
    }

    $base['status'] = 'executed';
    $base['executed'] = true;

    return $base;
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @param array<string,mixed> $executionGuard
 * @param array<string,mixed> $executorCoordinationPlan
 * @param callable(array<string,mixed>):mixed $beginTransaction
 * @param callable(array<string,mixed>):mixed $commitTransaction
 * @param callable(array<string,mixed>):mixed $rollbackTransaction
 * @param callable(array<string,mixed>):mixed $dbaccessInvoker
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_transaction_adapter(
    array $normalized,
    array $dispatcherResult,
    array $executionGuard,
    array $executorCoordinationPlan,
    bool $executorEnabled,
    callable $beginTransaction,
    callable $commitTransaction,
    callable $rollbackTransaction,
    callable $dbaccessInvoker,
): array {
    $operationKey = (string) ($normalized['operation_key'] ?? '');
    $dedupeKey = (string) ($executionGuard['dedupe_key'] ?? ($executorCoordinationPlan['dedupe_key'] ?? ''));
    $requestAuditEventKey = (string) ($executionGuard['request_audit_event_key'] ?? ($executorCoordinationPlan['request_audit_event_key'] ?? ''));
    $base = [
        'status' => 'failed',
        'success' => false,
        'executed' => false,
        'transaction_status' => 'not_started',
        'dbaccess_status' => 'not_called',
        'recording_status' => 'planned_not_written',
        'rolled_back' => false,
        'recovery_required' => false,
        'recovery_reason' => '',
        'failure_code' => '',
        'error' => '',
        'operation_key' => $operationKey,
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'dbaccess_result' => [],
        'reasons' => [],
    ];

    $reasons = [];
    if (!$executorEnabled) {
        $reasons[] = 'executor_feature_flag_disabled';
    }
    if (($executionGuard['status'] ?? '') !== 'allowed' || !($executionGuard['ready'] ?? false)) {
        $reasons[] = 'execution_guard_not_ready';
        foreach (($executionGuard['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
    }
    if (($executorCoordinationPlan['status'] ?? '') !== 'planned' || !($executorCoordinationPlan['ready'] ?? false)) {
        $reasons[] = 'executor_coordination_plan_not_ready';
        foreach (($executorCoordinationPlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $reasons[] = $reason;
            }
        }
    }
    if ($dedupeKey === '') {
        $reasons[] = 'dedupe_key_missing';
    }
    if ($requestAuditEventKey === '') {
        $reasons[] = 'request_audit_event_key_missing';
    }

    if ($reasons !== []) {
        $reasons = array_values(array_unique($reasons));
        $base['failure_code'] = $reasons[0];
        $base['reasons'] = $reasons;

        return $base;
    }

    $context = [
        'operation_key' => $operationKey,
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'transaction_scope' => (string) ($executorCoordinationPlan['app_db_transaction_boundary']['transaction_scope'] ?? ''),
        'db_handle' => (string) ($executorCoordinationPlan['app_db_transaction_boundary']['db_handle'] ?? ''),
    ];

    try {
        $begin = $beginTransaction($context);
    } catch (Throwable $throwable) {
        $base['transaction_status'] = 'begin_failed';
        $base['failure_code'] = 'transaction_begin_exception';
        $base['error'] = $throwable->getMessage();
        $base['reasons'] = ['transaction_begin_exception'];

        return $base;
    }
    if (!is_array($begin) || !($begin['ok'] ?? false)) {
        $base['transaction_status'] = 'begin_failed';
        $base['failure_code'] = (string) (is_array($begin) ? ($begin['failure_code'] ?? 'transaction_begin_failed') : 'transaction_begin_failed');
        $base['error'] = (string) (is_array($begin) ? ($begin['error'] ?? '') : '');
        $base['reasons'] = [$base['failure_code']];

        return $base;
    }

    $base['transaction_status'] = 'begun';
    $dbaccess = app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter(
        $normalized,
        $dispatcherResult,
        $executionGuard,
        $executorCoordinationPlan,
        $executorEnabled,
        $dbaccessInvoker,
    );
    $base['dbaccess_result'] = $dbaccess;

    if (($dbaccess['status'] ?? '') !== 'executed' || !($dbaccess['executed'] ?? false)) {
        $base['dbaccess_status'] = ($dbaccess['status'] ?? '') === 'skipped' ? 'not_called' : 'failed';
        $base['failure_code'] = (string) ($dbaccess['failure_code'] ?? 'dbaccess_failed');
        $base['error'] = (string) ($dbaccess['error'] ?? '');
        $base['reasons'] = array_values(array_unique(array_merge(
            [$base['failure_code']],
            array_filter($dbaccess['reasons'] ?? [], 'is_string'),
        )));

        try {
            $rollback = $rollbackTransaction($context + ['failure_code' => $base['failure_code']]);
        } catch (Throwable $throwable) {
            $base['transaction_status'] = 'rollback_failed';
            $base['failure_code'] = 'transaction_rollback_exception';
            $base['error'] = $throwable->getMessage();
            $base['reasons'][] = 'transaction_rollback_exception';

            return $base;
        }
        if (!is_array($rollback) || !($rollback['ok'] ?? false)) {
            $base['transaction_status'] = 'rollback_failed';
            $base['failure_code'] = (string) (is_array($rollback) ? ($rollback['failure_code'] ?? 'transaction_rollback_failed') : 'transaction_rollback_failed');
            $base['error'] = (string) (is_array($rollback) ? ($rollback['error'] ?? '') : '');
            $base['reasons'][] = $base['failure_code'];

            return $base;
        }

        $base['transaction_status'] = 'rolled_back';
        $base['rolled_back'] = true;

        return $base;
    }

    $base['dbaccess_status'] = 'executed';
    try {
        $commit = $commitTransaction($context + ['dbaccess_result' => $dbaccess]);
    } catch (Throwable $throwable) {
        $base['transaction_status'] = 'commit_failed';
        $base['failure_code'] = 'transaction_commit_exception';
        $base['error'] = $throwable->getMessage();
        $base['recovery_required'] = true;
        $base['recovery_reason'] = 'commit_status_unknown';
        $base['reasons'] = ['transaction_commit_exception'];

        return $base;
    }
    if (!is_array($commit) || !($commit['ok'] ?? false)) {
        $base['transaction_status'] = 'commit_failed';
        $base['failure_code'] = (string) (is_array($commit) ? ($commit['failure_code'] ?? 'transaction_commit_failed') : 'transaction_commit_failed');
        $base['error'] = (string) (is_array($commit) ? ($commit['error'] ?? '') : '');
        $base['recovery_required'] = true;
        $base['recovery_reason'] = 'commit_status_unknown';
        $base['reasons'] = [$base['failure_code']];

        return $base;
    }

    $base['status'] = 'executed';
    $base['success'] = true;
    $base['executed'] = true;
    $base['transaction_status'] = 'committed';
    $base['failure_code'] = '';
    $base['reasons'] = [];

    return $base;
}

/**
 * @param array<string,mixed> $transactionResult
 * @param array<string,mixed> $executionUpdatePlan
 * @param array<string,mixed> $executionGuard
 * @param callable(array<string,mixed>):mixed $executionAuditRecorder
 * @param callable(array<string,mixed>):mixed $idempotencyOutcomeRecorder
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
    array $transactionResult,
    array $executionUpdatePlan,
    array $executionGuard,
    callable $executionAuditRecorder,
    callable $idempotencyOutcomeRecorder,
): array {
    $dedupeKey = (string) ($transactionResult['dedupe_key'] ?? ($executionGuard['dedupe_key'] ?? ''));
    $requestAuditEventKey = (string) ($transactionResult['request_audit_event_key'] ?? ($executionGuard['request_audit_event_key'] ?? ''));
    $base = [
        'status' => 'failed',
        'success' => false,
        'recording_status' => 'skipped',
        'execution_audit_status' => 'not_started',
        'idempotency_update_status' => 'not_started',
        'transaction_status' => (string) ($transactionResult['transaction_status'] ?? ''),
        'dbaccess_status' => (string) ($transactionResult['dbaccess_status'] ?? ''),
        'recovery_required' => false,
        'recovery_reason' => '',
        'failure_code' => '',
        'error' => '',
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'execution_audit_result' => [],
        'idempotency_update_result' => [],
        'reasons' => [],
    ];

    $preconditionReasons = [];
    if (($transactionResult['status'] ?? '') !== 'executed' || !($transactionResult['success'] ?? false)) {
        $preconditionReasons[] = 'transaction_result_not_successful';
    }
    if (($transactionResult['transaction_status'] ?? '') !== 'committed') {
        $preconditionReasons[] = 'transaction_not_committed';
    }
    if (($transactionResult['dbaccess_status'] ?? '') !== 'executed') {
        $preconditionReasons[] = 'dbaccess_not_executed';
    }
    if (($executionUpdatePlan['status'] ?? '') !== 'planned' || !($executionUpdatePlan['ready'] ?? false)) {
        $preconditionReasons[] = 'execution_update_plan_not_ready';
        foreach (($executionUpdatePlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $preconditionReasons[] = $reason;
            }
        }
    }
    if (($executionGuard['status'] ?? '') !== 'allowed' || !($executionGuard['ready'] ?? false)) {
        $preconditionReasons[] = 'execution_guard_not_ready';
        foreach (($executionGuard['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $preconditionReasons[] = $reason;
            }
        }
    }
    if ($dedupeKey === '') {
        $preconditionReasons[] = 'dedupe_key_missing';
    }
    if ($requestAuditEventKey === '') {
        $preconditionReasons[] = 'request_audit_event_key_missing';
    }

    if ($preconditionReasons !== []) {
        $preconditionReasons = array_values(array_unique($preconditionReasons));
        $base['failure_code'] = $preconditionReasons[0];
        $base['reasons'] = $preconditionReasons;

        return $base;
    }

    $context = [
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'transaction_status' => 'committed',
        'dbaccess_status' => 'executed',
        'operation_key' => (string) ($transactionResult['operation_key'] ?? ($executionGuard['operation_key'] ?? '')),
    ];

    try {
        $audit = $executionAuditRecorder($context);
    } catch (Throwable $throwable) {
        $base['recording_status'] = 'failed';
        $base['execution_audit_status'] = 'failed';
        $base['failure_code'] = 'execution_audit_exception';
        $base['error'] = $throwable->getMessage();
        $base['recovery_required'] = true;
        $base['recovery_reason'] = 'post_commit_recording_failed';
        $base['reasons'] = ['execution_audit_exception'];

        return $base;
    }
    $base['execution_audit_result'] = is_array($audit) ? $audit : [];
    if (!is_array($audit) || !($audit['ok'] ?? false)) {
        $base['recording_status'] = 'failed';
        $base['execution_audit_status'] = 'failed';
        $base['failure_code'] = (string) (is_array($audit) ? ($audit['failure_code'] ?? 'execution_audit_failed') : 'execution_audit_failed');
        $base['error'] = (string) (is_array($audit) ? ($audit['error'] ?? '') : '');
        $base['recovery_required'] = true;
        $base['recovery_reason'] = 'post_commit_recording_failed';
        $base['reasons'] = [$base['failure_code']];

        return $base;
    }
    $base['execution_audit_status'] = 'recorded';

    try {
        $idempotency = $idempotencyOutcomeRecorder($context + ['execution_audit_result' => $audit]);
    } catch (Throwable $throwable) {
        $base['recording_status'] = 'failed';
        $base['idempotency_update_status'] = 'failed';
        $base['failure_code'] = 'idempotency_update_exception';
        $base['error'] = $throwable->getMessage();
        $base['recovery_required'] = true;
        $base['recovery_reason'] = 'post_commit_recording_failed';
        $base['reasons'] = ['idempotency_update_exception'];

        return $base;
    }
    $base['idempotency_update_result'] = is_array($idempotency) ? $idempotency : [];
    if (!is_array($idempotency) || !($idempotency['ok'] ?? false)) {
        $base['recording_status'] = 'failed';
        $base['idempotency_update_status'] = 'failed';
        $base['failure_code'] = (string) (is_array($idempotency) ? ($idempotency['failure_code'] ?? 'idempotency_update_failed') : 'idempotency_update_failed');
        $base['error'] = (string) (is_array($idempotency) ? ($idempotency['error'] ?? '') : '');
        $base['recovery_required'] = true;
        $base['recovery_reason'] = 'post_commit_recording_failed';
        $base['reasons'] = [$base['failure_code']];

        return $base;
    }

    $base['status'] = 'recorded';
    $base['success'] = true;
    $base['recording_status'] = 'recorded';
    $base['idempotency_update_status'] = 'recorded';
    $base['failure_code'] = '';
    $base['reasons'] = [];

    return $base;
}

/**
 * @param array<string,mixed> $normalized
 * @param array<string,mixed> $dispatcherResult
 * @param array<string,mixed> $executionGuard
 * @param array<string,mixed> $executorCoordinationPlan
 * @param array<string,mixed> $executionUpdatePlan
 * @param callable(array<string,mixed>):mixed $beginTransaction
 * @param callable(array<string,mixed>):mixed $commitTransaction
 * @param callable(array<string,mixed>):mixed $rollbackTransaction
 * @param callable(array<string,mixed>):mixed $dbaccessInvoker
 * @param callable(array<string,mixed>):mixed $executionAuditRecorder
 * @param callable(array<string,mixed>):mixed $idempotencyOutcomeRecorder
 * @return array<string,mixed>
 */
function app_lab_sample18_task_board_generated_submit_route_execution_plan(
    array $normalized,
    array $dispatcherResult,
    array $executionGuard,
    array $executorCoordinationPlan,
    array $executionUpdatePlan,
    bool $executorEnabled,
    callable $beginTransaction,
    callable $commitTransaction,
    callable $rollbackTransaction,
    callable $dbaccessInvoker,
    callable $executionAuditRecorder,
    callable $idempotencyOutcomeRecorder,
): array {
    $dedupeKey = (string) ($executionGuard['dedupe_key'] ?? ($executorCoordinationPlan['dedupe_key'] ?? ''));
    $requestAuditEventKey = (string) ($executionGuard['request_audit_event_key'] ?? ($executorCoordinationPlan['request_audit_event_key'] ?? ''));
    $base = [
        'ok' => false,
        'accepted' => false,
        'result' => 'blocked',
        'success' => false,
        'execution_status' => 'not_executed',
        'failure_code' => '',
        'recovery_required' => false,
        'dedupe_key' => $dedupeKey,
        'request_audit_event_key' => $requestAuditEventKey,
        'transaction_result' => [],
        'post_commit_recording' => [],
        'reasons' => [],
    ];

    $preconditionReasons = [];
    if (!$executorEnabled) {
        $preconditionReasons[] = 'executor_feature_flag_disabled';
    }
    if (($executionGuard['status'] ?? '') !== 'allowed' || !($executionGuard['ready'] ?? false)) {
        $preconditionReasons[] = 'execution_guard_not_ready';
        foreach (($executionGuard['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $preconditionReasons[] = $reason;
            }
        }
    }
    if (($executorCoordinationPlan['status'] ?? '') !== 'planned' || !($executorCoordinationPlan['ready'] ?? false)) {
        $preconditionReasons[] = 'executor_coordination_plan_not_ready';
        foreach (($executorCoordinationPlan['reasons'] ?? []) as $reason) {
            if (is_string($reason) && $reason !== '') {
                $preconditionReasons[] = $reason;
            }
        }
    }

    if ($preconditionReasons !== []) {
        $preconditionReasons = array_values(array_unique($preconditionReasons));
        $base['failure_code'] = $preconditionReasons[0];
        $base['reasons'] = $preconditionReasons;

        return $base;
    }

    $transaction = app_lab_sample18_task_board_generated_submit_transaction_adapter(
        $normalized,
        $dispatcherResult,
        $executionGuard,
        $executorCoordinationPlan,
        $executorEnabled,
        $beginTransaction,
        $commitTransaction,
        $rollbackTransaction,
        $dbaccessInvoker,
    );
    $base['transaction_result'] = $transaction;
    if (($transaction['status'] ?? '') !== 'executed' || !($transaction['success'] ?? false)) {
        $base['result'] = 'failed';
        $base['execution_status'] = 'failed';
        $base['failure_code'] = (string) ($transaction['failure_code'] ?? 'execution_transaction_failed');
        $base['recovery_required'] = (bool) ($transaction['recovery_required'] ?? false);
        $base['reasons'] = array_values(array_unique(array_merge(
            [$base['failure_code']],
            array_filter($transaction['reasons'] ?? [], 'is_string'),
        )));

        return $base;
    }

    $recording = app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter(
        $transaction,
        $executionUpdatePlan,
        $executionGuard,
        $executionAuditRecorder,
        $idempotencyOutcomeRecorder,
    );
    $base['post_commit_recording'] = $recording;
    if (($recording['status'] ?? '') !== 'recorded' || !($recording['success'] ?? false)) {
        $base['result'] = 'failed';
        $base['execution_status'] = 'failed';
        $base['failure_code'] = (string) ($recording['failure_code'] ?? 'post_commit_recording_failed');
        $base['recovery_required'] = (bool) ($recording['recovery_required'] ?? false);
        $base['reasons'] = array_values(array_unique(array_merge(
            [$base['failure_code']],
            array_filter($recording['reasons'] ?? [], 'is_string'),
        )));

        return $base;
    }

    $base['ok'] = true;
    $base['accepted'] = true;
    $base['result'] = 'executed';
    $base['success'] = true;
    $base['execution_status'] = 'executed';
    $base['failure_code'] = '';
    $base['reasons'] = [];

    return $base;
}

/**
 * @param array<string,mixed> $post
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_lab_sample18_task_board_generated_submit_blocked_response(
    string $requestMethod,
    array $post,
    string $now,
    string $csrfGuardResult = 'valid',
    ?array $app = null,
    array $principal = [],
): array {
    if (strtoupper($requestMethod) !== 'POST') {
        return [
            'status_code' => 405,
            'payload' => [
                'ok' => false,
                'accepted' => false,
                'result' => 'invalid',
                'failure_code' => 'method_not_allowed',
                'allowed_methods' => ['POST'],
                'mutation_enabled' => false,
            ],
        ];
    }

    if ($csrfGuardResult !== 'valid') {
        return [
            'status_code' => 403,
            'payload' => [
                'ok' => false,
                'accepted' => false,
                'result' => 'invalid',
                'failure_code' => $csrfGuardResult === 'missing' ? 'missing_csrf' : 'invalid_csrf',
                'errors' => [$csrfGuardResult === 'missing' ? 'csrf.missing' : 'csrf.invalid'],
                'mutation_enabled' => false,
            ],
        ];
    }

    $operationKey = trim((string) ($post['operation_key'] ?? ''));
    $input = $post;
    unset($input['operation_key'], $input['_csrf_token']);
    $normalized = app_lab_sample18_task_board_normalize_generated_submit_request($operationKey, $input, $now);
    if (!$normalized['ok']) {
        $statusCode = $normalized['failure_code'] === 'unknown_operation' ? 404 : 422;
        return [
            'status_code' => $statusCode,
            'payload' => [
                'ok' => false,
                'accepted' => false,
                'result' => 'invalid',
                'failure_code' => $normalized['failure_code'],
                'operation_key' => $normalized['operation_key'],
                'errors' => $normalized['errors'],
                'normalized_payload' => $normalized['payload'],
                'ignored_input_fields' => $normalized['ignored_input_fields'],
                'mutation_enabled' => false,
            ],
        ];
    }

    $dispatcherResult = app_lab_sample18_task_board_generated_submit_dispatcher_dry_run($normalized);
    $idempotencyAuditPreview = app_lab_sample18_task_board_generated_submit_idempotency_audit_preview(
        $normalized,
        $dispatcherResult,
        'blocked',
        'generated_submit_disabled',
    );
    $auditEvent = app_lab_sample18_task_board_generated_submit_audit_event_with_actor(
        is_array($idempotencyAuditPreview['audit_event_preview'] ?? null)
            ? $idempotencyAuditPreview['audit_event_preview']
            : [],
        $principal,
    );
    $auditAppend = app_lab_sample18_task_board_generated_submit_append_audit_event($app, $auditEvent);
    $idempotency = app_lab_sample18_task_board_generated_submit_apply_idempotency(
        $app,
        $normalized,
        $dispatcherResult,
        $idempotencyAuditPreview,
        $auditAppend,
    );
    $mutationGate = app_lab_sample18_task_board_generated_submit_mutation_gate(
        $app ?? [],
        $normalized,
        $dispatcherResult,
        $auditAppend,
        $idempotency,
    );
    $dbaccessExecutionPlan = app_lab_sample18_task_board_generated_submit_dbaccess_execution_plan(
        $normalized,
        $dispatcherResult,
        $mutationGate,
    );
    $transactionPlan = app_lab_sample18_task_board_generated_submit_transaction_plan($dbaccessExecutionPlan);
    $executionUpdatePlan = app_lab_sample18_task_board_generated_submit_execution_update_plan(
        $transactionPlan,
        $auditAppend,
        $idempotency,
    );
    $executionGuard = app_lab_sample18_task_board_generated_submit_execution_guard(
        $normalized,
        $auditAppend,
        $idempotency,
        $mutationGate,
        $dbaccessExecutionPlan,
        $transactionPlan,
        $executionUpdatePlan,
    );
    $executorCoordinationPlan = app_lab_sample18_task_board_generated_submit_executor_coordination_plan(
        $executionGuard,
        $executionUpdatePlan,
        false,
    );

    return [
        'status_code' => 409,
        'payload' => [
            'ok' => false,
            'accepted' => false,
            'result' => 'blocked',
            'failure_code' => 'generated_submit_disabled',
            'operation_key' => $normalized['operation_key'],
            'curated_route_action' => $normalized['curated_route_action'],
            'db_access_function' => $normalized['db_access_function'],
            'normalized_payload' => $normalized['payload'],
            'ignored_input_fields' => $normalized['ignored_input_fields'],
            'dispatcher_result' => $dispatcherResult,
            'dedupe_key_preview' => $idempotencyAuditPreview['dedupe_key_preview'],
            'payload_fingerprint' => $idempotencyAuditPreview['payload_fingerprint'],
            'audit_event_preview' => $auditEvent,
            'audit_append' => $auditAppend,
            'idempotency' => $idempotency,
            'mutation_gate' => $mutationGate,
            'dbaccess_execution_plan' => $dbaccessExecutionPlan,
            'transaction_plan' => $transactionPlan,
            'execution_update_plan' => $executionUpdatePlan,
            'execution_guard' => $executionGuard,
            'executor_coordination_plan' => $executorCoordinationPlan,
            'mutation_enabled' => false,
        ],
    ];
}

/**
 * @param array{request_id:string,method:string} $request
 */
function app_render_lab_sample18_task_board_generated_submit_page(array $app, array $request): void
{
    $submittedCsrfToken = trim((string) ($_POST['_csrf_token'] ?? ''));
    $csrfGuardResult = 'valid';
    if (strtoupper($request['method']) === 'POST') {
        if ($submittedCsrfToken === '') {
            $csrfGuardResult = 'missing';
        } elseif (!app_verify_csrf_token($submittedCsrfToken)) {
            $csrfGuardResult = 'invalid';
        }
    }

    $response = app_lab_sample18_task_board_generated_submit_blocked_response(
        $request['method'],
        $_POST,
        date('Y-m-d H:i:s'),
        $csrfGuardResult,
        $app,
        app_auth_principal() ?? [],
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

function app_lab_sample18_task_board_handle_post(PDO $pdo, array $request): void
{
    if (!app_verify_csrf_token(app_post_param('_csrf_token'))) {
        app_send_html_response_headers($request, 400);
        echo '<!DOCTYPE html><meta charset="utf-8"><p>CSRF token is invalid.</p>';
        return;
    }

    $action = app_post_param('action');
    $now = date('Y-m-d H:i:s');

    if ($action === 'create') {
        $title = trim(app_post_param('title'));
        if ($title === '') {
            app_lab_sample18_task_board_redirect($request, 'Title is required.');
            return;
        }

        $priority = max(0, min(100, (int) app_post_param('priority', '10')));
        $dueDate = trim(app_post_param('due_date'));
        if ($dueDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) !== 1) {
            $dueDate = '';
        }

        $statement = $pdo->prepare(
            'INSERT INTO TaskCard (Title, Body, Status, AssignedTo, Priority, DueDate, CompletedAt, UpdatedAt)
             VALUES (:title, :body, :status, :assigned_to, :priority, :due_date, NULL, :updated_at)',
        );
        $statement->execute([
            ':title' => $title,
            ':body' => trim(app_post_param('body')),
            ':status' => 'todo',
            ':assigned_to' => trim(app_post_param('assigned_to')),
            ':priority' => $priority,
            ':due_date' => $dueDate !== '' ? $dueDate : null,
            ':updated_at' => $now,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task created.');
        return;
    }

    $id = (int) app_post_param('id', '0');
    if ($id <= 0) {
        app_lab_sample18_task_board_redirect($request, 'Task id is invalid.');
        return;
    }

    if ($action === 'update') {
        $title = trim(app_post_param('title'));
        if ($title === '') {
            app_lab_sample18_task_board_redirect($request, 'Title is required.');
            return;
        }

        $status = trim(app_post_param('status'));
        if (!in_array($status, ['todo', 'doing', 'done'], true)) {
            $status = 'todo';
        }

        $priority = max(0, min(100, (int) app_post_param('priority', '10')));
        $dueDate = trim(app_post_param('due_date'));
        if ($dueDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) !== 1) {
            $dueDate = '';
        }

        $completedAt = $status === 'done' ? $now : null;
        $statement = $pdo->prepare(
            'UPDATE TaskCard
             SET Title = :title,
                 Body = :body,
                 Status = :status,
                 AssignedTo = :assigned_to,
                 Priority = :priority,
                 DueDate = :due_date,
                 CompletedAt = :completed_at,
                 UpdatedAt = :updated_at
             WHERE Id = :id',
        );
        $statement->execute([
            ':title' => $title,
            ':body' => trim(app_post_param('body')),
            ':status' => $status,
            ':assigned_to' => trim(app_post_param('assigned_to')),
            ':priority' => $priority,
            ':due_date' => $dueDate !== '' ? $dueDate : null,
            ':completed_at' => $completedAt,
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task updated.');
        return;
    }

    if ($action === 'complete') {
        $statement = $pdo->prepare(
            "UPDATE TaskCard
             SET Status = 'done', CompletedAt = :completed_at, UpdatedAt = :updated_at
             WHERE Id = :id",
        );
        $statement->execute([
            ':completed_at' => $now,
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task completed.');
        return;
    }

    if ($action === 'reopen') {
        $statement = $pdo->prepare(
            "UPDATE TaskCard
             SET Status = 'todo', CompletedAt = NULL, UpdatedAt = :updated_at
             WHERE Id = :id",
        );
        $statement->execute([
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task reopened.');
        return;
    }

    if ($action === 'delete') {
        $statement = $pdo->prepare('DELETE FROM TaskCard WHERE Id = :id');
        $statement->execute([':id' => $id]);

        app_lab_sample18_task_board_redirect($request, 'Task deleted.');
        return;
    }

    app_lab_sample18_task_board_redirect($request, 'Unknown action.');
}

function app_render_lab_sample18_task_board_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab' && $app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 実験用サイト または 設定変更用サイト でのみ利用します。');
        return;
    }

    $pdo = app_create_config_pdo($app);
    if (!app_lab_sample18_task_board_is_available($pdo)) {
        app_render_not_found_page($app, $request);
        return;
    }

    app_lab_sample18_task_board_create_schema($pdo);

    if (app_request_method_is($request, 'POST')) {
        app_lab_sample18_task_board_handle_post($pdo, $request);
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $status = trim(app_query_param('status'));
    if (!in_array($status, ['', 'todo', 'doing', 'done'], true)) {
        $status = '';
    }

    $rows = app_lab_sample18_task_board_fetch_rows($pdo, $status);
    $editId = (int) app_query_param('edit_id', '0');
    $editRow = $editId > 0 ? app_lab_sample18_task_board_fetch_row($pdo, $editId) : null;
    $csrfToken = app_csrf_token();
    $message = trim(app_query_param('message'));

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sample18 Mini Task Board</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #202124;
            --muted: #68707a;
            --line: #d8dee6;
            --panel: #f7f9fb;
            --accent: #146c94;
            --danger: #a33a2a;
            --done: #2c7a4b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: var(--ink);
            font-family: Arial, sans-serif;
            margin: 0;
            background: #ffffff;
        }

        main {
            margin: 0 auto;
            max-width: 1120px;
            padding: 28px 20px 48px;
        }

        header {
            border-bottom: 1px solid var(--line);
            margin-bottom: 22px;
            padding-bottom: 18px;
        }

        h1 {
            font-size: 1.8rem;
            line-height: 1.2;
            margin: 0 0 8px;
        }

        p {
            line-height: 1.55;
        }

        .muted {
            color: var(--muted);
        }

        .notice {
            background: #eef7f3;
            border: 1px solid #b8d8c7;
            color: #1f5f3d;
            margin: 0 0 18px;
            padding: 10px 12px;
        }

        .toolbar,
        form.create {
            align-items: end;
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
            margin: 18px 0;
        }

        form.create {
            background: var(--panel);
            border: 1px solid var(--line);
            padding: 14px;
        }

        form.create.editing {
            background: #fff8e6;
            border-color: #d7bd75;
        }

        label {
            display: grid;
            gap: 5px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        input,
        select,
        textarea,
        button,
        .button {
            border: 1px solid var(--line);
            font: inherit;
            min-height: 36px;
            padding: 7px 9px;
        }

        textarea {
            min-height: 36px;
            resize: vertical;
        }

        button,
        .button {
            background: #ffffff;
            color: var(--ink);
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            text-decoration: none;
            white-space: nowrap;
        }

        button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #ffffff;
        }

        button.danger {
            border-color: #d6aaa3;
            color: var(--danger);
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border-bottom: 1px solid var(--line);
            padding: 10px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: var(--panel);
            font-size: 0.82rem;
        }

        .status {
            border: 1px solid var(--line);
            display: inline-block;
            font-size: 0.8rem;
            min-width: 64px;
            padding: 3px 7px;
            text-align: center;
        }

        .status.done {
            border-color: #9ccbaa;
            color: var(--done);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .actions form {
            margin: 0;
        }

        @media (max-width: 780px) {
            .toolbar,
            form.create {
                grid-template-columns: 1fr;
            }

            table,
            thead,
            tbody,
            tr,
            th,
            td {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                border: 1px solid var(--line);
                margin-bottom: 10px;
            }

            td {
                border-bottom: 0;
            }
        }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Sample18 Mini Task Board</h1>
        <p class="muted">A tiny running UI for the instruction-driven sample. It reads and writes the sample <code>TaskCard</code> table from the config store.</p>
    </header>

    <?php if ($message !== ''): ?>
        <p class="notice"><?php echo app_h($message); ?></p>
    <?php endif; ?>

    <form class="toolbar" method="get" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
        <label>
            Status filter
            <select name="status">
                <option value=""<?php echo $status === '' ? ' selected' : ''; ?>>all</option>
                <option value="todo"<?php echo $status === 'todo' ? ' selected' : ''; ?>>todo</option>
                <option value="doing"<?php echo $status === 'doing' ? ' selected' : ''; ?>>doing</option>
                <option value="done"<?php echo $status === 'done' ? ' selected' : ''; ?>>done</option>
            </select>
        </label>
        <button type="submit">Apply</button>
        <a class="button" href="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">Reset</a>
    </form>

    <form class="create <?php echo $editRow !== null ? 'editing' : ''; ?>" method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
        <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="action" value="<?php echo $editRow !== null ? 'update' : 'create'; ?>">
        <?php if ($editRow !== null): ?>
            <input type="hidden" name="id" value="<?php echo app_h((string) ($editRow['Id'] ?? '')); ?>">
        <?php endif; ?>
        <label>
            Title
            <input name="title" required maxlength="255" placeholder="Write release note" value="<?php echo app_h((string) ($editRow['Title'] ?? '')); ?>">
        </label>
        <label>
            Body
            <textarea name="body" placeholder="Short task memo"><?php echo app_h((string) ($editRow['Body'] ?? '')); ?></textarea>
        </label>
        <?php if ($editRow !== null): ?>
            <?php $editStatus = (string) ($editRow['Status'] ?? 'todo'); ?>
            <label>
                Status
                <select name="status">
                    <option value="todo"<?php echo $editStatus === 'todo' ? ' selected' : ''; ?>>todo</option>
                    <option value="doing"<?php echo $editStatus === 'doing' ? ' selected' : ''; ?>>doing</option>
                    <option value="done"<?php echo $editStatus === 'done' ? ' selected' : ''; ?>>done</option>
                </select>
            </label>
        <?php endif; ?>
        <label>
            Assigned to
            <input name="assigned_to" maxlength="100" placeholder="Alice" value="<?php echo app_h((string) ($editRow['AssignedTo'] ?? '')); ?>">
        </label>
        <label>
            Priority
            <input name="priority" type="number" min="0" max="100" value="<?php echo app_h((string) ($editRow['Priority'] ?? '10')); ?>">
        </label>
        <label>
            Due date
            <input name="due_date" type="date" value="<?php echo app_h((string) ($editRow['DueDate'] ?? '')); ?>">
        </label>
        <button class="primary" type="submit"><?php echo $editRow !== null ? 'Update Task' : 'Add Task'; ?></button>
        <?php if ($editRow !== null): ?>
            <a class="button" href="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">Cancel</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
        <tr>
            <th>Task</th>
            <th>Status</th>
            <th>Assigned</th>
            <th>Priority</th>
            <th>Due</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <?php $rowStatus = (string) ($row['Status'] ?? ''); ?>
            <tr>
                <td>
                    <strong><?php echo app_h((string) ($row['Title'] ?? '')); ?></strong>
                    <div class="muted"><?php echo app_h((string) ($row['Body'] ?? '')); ?></div>
                </td>
                <td><span class="status <?php echo $rowStatus === 'done' ? 'done' : ''; ?>"><?php echo app_h($rowStatus); ?></span></td>
                <td><?php echo app_h((string) ($row['AssignedTo'] ?? '')); ?></td>
                <td><?php echo app_h((string) ($row['Priority'] ?? '')); ?></td>
                <td><?php echo app_h((string) ($row['DueDate'] ?? '')); ?></td>
                <td>
                    <div class="actions">
                        <a class="button" href="<?php echo app_h(app_lab_sample18_task_board_path() . '?edit_id=' . rawurlencode((string) ($row['Id'] ?? ''))); ?>">Edit</a>
                        <?php if ($rowStatus === 'done'): ?>
                            <form method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
                                <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="reopen">
                                <input type="hidden" name="id" value="<?php echo app_h((string) ($row['Id'] ?? '')); ?>">
                                <button type="submit">Reopen</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
                                <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="id" value="<?php echo app_h((string) ($row['Id'] ?? '')); ?>">
                                <button type="submit">Complete</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
                            <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo app_h((string) ($row['Id'] ?? '')); ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
    <?php
}
