<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @param array<string,mixed> $intent
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_enqueue_managed_operation_sync_intent(array $app, array $intent): array
{
    try {
        $projectKey = (string) ($intent['project_key'] ?? '');
        $dedupeKey = (string) ($intent['dedupe_key'] ?? '');
        if ($projectKey === '' || $dedupeKey === '') {
            throw new RuntimeException('managed operation sync intent requires project_key and dedupe_key.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_sync_outbox_resolve_project_id($pdo, $projectKey);
        $intentJson = app_managed_operation_sync_outbox_json_text($intent);
        $existingId = app_managed_operation_sync_outbox_find_id($pdo, $projectId, $dedupeKey);

        if ($existingId > 0) {
            $statement = $pdo->prepare(
                'UPDATE project_managed_operation_sync_outbox
                 SET
                    intent_json = :intent_json,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                   AND project_id = :project_id'
            );
            $statement->execute([
                ':intent_json' => $intentJson,
                ':id' => $existingId,
                ':project_id' => $projectId,
            ]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO project_managed_operation_sync_outbox (
                    project_id,
                    dedupe_key,
                    status,
                    storage_mode,
                    origin_endpoint,
                    target_endpoint,
                    operation_key,
                    operation_type,
                    contract_key,
                    intent_json,
                    last_error
                ) VALUES (
                    :project_id,
                    :dedupe_key,
                    :status,
                    :storage_mode,
                    :origin_endpoint,
                    :target_endpoint,
                    :operation_key,
                    :operation_type,
                    :contract_key,
                    :intent_json,
                    :last_error
                )'
            );
            $statement->execute([
                ':project_id' => $projectId,
                ':dedupe_key' => $dedupeKey,
                ':status' => app_managed_operation_sync_outbox_status((string) ($intent['status'] ?? 'pending')),
                ':storage_mode' => (string) ($intent['storage_mode'] ?? ''),
                ':origin_endpoint' => (string) ($intent['origin'] ?? ''),
                ':target_endpoint' => (string) ($intent['target'] ?? ''),
                ':operation_key' => (string) ($intent['operation_key'] ?? ''),
                ':operation_type' => (string) ($intent['operation_type'] ?? ''),
                ':contract_key' => (string) ($intent['contract_key'] ?? ''),
                ':intent_json' => $intentJson,
                ':last_error' => '',
            ]);
        }

        return app_pdo_fetch_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_fetch_managed_operation_sync_outbox_item(array $app, string $projectKey, string $dedupeKey): array
{
    $catalog = app_pdo_fetch_managed_operation_sync_outbox_catalog($app, $projectKey);
    if (!$catalog['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $catalog['error'],
        ];
    }

    foreach ($catalog['items'] as $item) {
        if ((string) ($item['dedupe_key'] ?? '') === $dedupeKey) {
            return [
                'ok' => true,
                'item' => $item,
                'error' => '',
            ];
        }
    }

    return [
        'ok' => true,
        'item' => null,
        'error' => '',
    ];
}

/**
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_fetch_managed_operation_sync_outbox_catalog(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_sync_outbox_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'SELECT
                id,
                dedupe_key,
                status,
                storage_mode,
                origin_endpoint,
                target_endpoint,
                operation_key,
                operation_type,
                contract_key,
                intent_json,
                attempts,
                last_error,
                created_at,
                updated_at
             FROM project_managed_operation_sync_outbox
             WHERE project_id = :project_id
             ORDER BY id'
        );
        $statement->execute([':project_id' => $projectId]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_managed_operation_sync_outbox_item_from_row($projectKey, $row);
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
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_fetch_next_pending_managed_operation_sync_outbox_item(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_sync_outbox_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'SELECT
                id,
                dedupe_key,
                status,
                storage_mode,
                origin_endpoint,
                target_endpoint,
                operation_key,
                operation_type,
                contract_key,
                intent_json,
                attempts,
                last_error,
                created_at,
                updated_at
             FROM project_managed_operation_sync_outbox
             WHERE project_id = :project_id
               AND status = :status
             ORDER BY attempts ASC, id ASC
             LIMIT 1'
        );
        $statement->execute([
            ':project_id' => $projectId,
            ':status' => 'pending',
        ]);
        $row = $statement->fetch();
        if (!is_array($row)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_managed_operation_sync_outbox_item_from_row($projectKey, $row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,claimed:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_claim_managed_operation_sync_outbox_item(
    array $app,
    string $projectKey,
    string $dedupeKey,
): array {
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_sync_outbox_resolve_project_id($pdo, $projectKey);
        $existingId = app_managed_operation_sync_outbox_find_id($pdo, $projectId, $dedupeKey);
        if ($existingId <= 0) {
            throw new RuntimeException('managed operation sync outbox item was not found.');
        }

        $statement = $pdo->prepare(
            'UPDATE project_managed_operation_sync_outbox
             SET
                status = :running_status,
                attempts = attempts + 1,
                last_error = :last_error,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND project_id = :project_id
               AND status = :pending_status'
        );
        $statement->execute([
            ':running_status' => 'running',
            ':last_error' => '',
            ':id' => $existingId,
            ':project_id' => $projectId,
            ':pending_status' => 'pending',
        ]);

        $item = app_pdo_fetch_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
        if (!$item['ok']) {
            return [
                'ok' => false,
                'claimed' => false,
                'item' => null,
                'error' => $item['error'],
            ];
        }

        return [
            'ok' => true,
            'claimed' => $statement->rowCount() === 1,
            'item' => $item['item'],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'claimed' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_mark_managed_operation_sync_outbox_running(
    array $app,
    string $projectKey,
    string $dedupeKey,
): array {
    return app_pdo_update_managed_operation_sync_outbox_status(
        $app,
        $projectKey,
        $dedupeKey,
        'running',
        '',
        true,
    );
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_mark_managed_operation_sync_outbox_done(
    array $app,
    string $projectKey,
    string $dedupeKey,
): array {
    return app_pdo_update_managed_operation_sync_outbox_status(
        $app,
        $projectKey,
        $dedupeKey,
        'done',
        '',
        false,
    );
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_mark_managed_operation_sync_outbox_failed(
    array $app,
    string $projectKey,
    string $dedupeKey,
    string $lastError,
): array {
    return app_pdo_update_managed_operation_sync_outbox_status(
        $app,
        $projectKey,
        $dedupeKey,
        'failed',
        $lastError,
        false,
    );
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_requeue_failed_managed_operation_sync_outbox_item(
    array $app,
    string $projectKey,
    string $dedupeKey,
): array {
    try {
        $current = app_pdo_fetch_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
        if (!$current['ok']) {
            throw new RuntimeException($current['error']);
        }
        if ($current['item'] === null) {
            throw new RuntimeException('managed operation sync outbox item was not found.');
        }
        if ((string) ($current['item']['status'] ?? '') !== 'failed') {
            throw new RuntimeException('only failed managed operation sync outbox items can be requeued.');
        }

        return app_pdo_update_managed_operation_sync_outbox_status(
            $app,
            $projectKey,
            $dedupeKey,
            'pending',
            '',
            false,
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_update_managed_operation_sync_outbox_status(
    array $app,
    string $projectKey,
    string $dedupeKey,
    string $status,
    string $lastError = '',
    bool $incrementAttempts = false,
): array {
    try {
        $normalizedStatus = app_managed_operation_sync_outbox_status($status);
        if ($normalizedStatus !== $status) {
            throw new RuntimeException('managed operation sync outbox status is invalid.');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_sync_outbox_resolve_project_id($pdo, $projectKey);
        $existingId = app_managed_operation_sync_outbox_find_id($pdo, $projectId, $dedupeKey);
        if ($existingId <= 0) {
            throw new RuntimeException('managed operation sync outbox item was not found.');
        }

        $statement = $pdo->prepare(
            'UPDATE project_managed_operation_sync_outbox
             SET
                status = :status,
                attempts = attempts + :attempt_increment,
                last_error = :last_error,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND project_id = :project_id'
        );
        $statement->execute([
            ':status' => $normalizedStatus,
            ':attempt_increment' => $incrementAttempts ? 1 : 0,
            ':last_error' => trim($lastError),
            ':id' => $existingId,
            ':project_id' => $projectId,
        ]);

        return app_pdo_fetch_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_managed_operation_sync_outbox_item_from_row(string $projectKey, array $row): array
{
    return [
        'project_key' => $projectKey,
        'id' => (string) ($row['id'] ?? ''),
        'dedupe_key' => (string) ($row['dedupe_key'] ?? ''),
        'status' => (string) ($row['status'] ?? ''),
        'storage_mode' => (string) ($row['storage_mode'] ?? ''),
        'origin' => (string) ($row['origin_endpoint'] ?? ''),
        'target' => (string) ($row['target_endpoint'] ?? ''),
        'operation_key' => (string) ($row['operation_key'] ?? ''),
        'operation_type' => (string) ($row['operation_type'] ?? ''),
        'contract_key' => (string) ($row['contract_key'] ?? ''),
        'intent' => app_managed_operation_sync_outbox_json_array((string) ($row['intent_json'] ?? '{}')),
        'attempts' => (int) ($row['attempts'] ?? 0),
        'last_error' => (string) ($row['last_error'] ?? ''),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'updated_at' => (string) ($row['updated_at'] ?? ''),
    ];
}

function app_managed_operation_sync_outbox_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM projects
         WHERE project_key = :project_key
         LIMIT 1'
    );
    $statement->execute([':project_key' => $projectKey]);

    $projectId = $statement->fetchColumn();
    if ($projectId === false) {
        throw new RuntimeException('project が見つかりません: ' . $projectKey);
    }

    return (int) $projectId;
}

function app_managed_operation_sync_outbox_find_id(PDO $pdo, int $projectId, string $dedupeKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM project_managed_operation_sync_outbox
         WHERE project_id = :project_id
           AND dedupe_key = :dedupe_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':dedupe_key' => $dedupeKey,
    ]);

    return (int) ($statement->fetchColumn() ?: 0);
}

function app_managed_operation_sync_outbox_status(string $status): string
{
    $normalized = strtolower(trim($status));
    return in_array($normalized, ['pending', 'running', 'done', 'failed'], true) ? $normalized : 'pending';
}

/**
 * @param array<string,mixed> $intent
 */
function app_managed_operation_sync_outbox_json_text(array $intent): string
{
    $json = json_encode($intent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('managed operation sync intent JSON generation failed.');
    }

    return $json;
}

/**
 * @return array<string,mixed>
 */
function app_managed_operation_sync_outbox_json_array(string $json): array
{
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}
