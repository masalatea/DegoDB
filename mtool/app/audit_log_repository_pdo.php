<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sql_dialect.php';

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>,error:string}
 */
function app_pdo_audit_log_append(array $app, array $input): array
{
    try {
        $event = app_audit_log_normalize_input($input);
        $pdo = app_create_config_pdo($app);
        $statement = $pdo->prepare(
            'INSERT INTO audit_events (
                event_key,
                actor_login_id,
                actor_source,
                project_key,
                event_type,
                target_type,
                target_key,
                result,
                message,
                metadata_json
            ) VALUES (
                :event_key,
                :actor_login_id,
                :actor_source,
                :project_key,
                :event_type,
                :target_type,
                :target_key,
                :result,
                :message,
                :metadata_json
            )'
        );
        $statement->execute([
            ':event_key' => $event['event_key'],
            ':actor_login_id' => $event['actor_login_id'],
            ':actor_source' => $event['actor_source'],
            ':project_key' => $event['project_key'],
            ':event_type' => $event['event_type'],
            ':target_type' => $event['target_type'],
            ':target_key' => $event['target_key'],
            ':result' => $event['result'],
            ':message' => $event['message'],
            ':metadata_json' => $event['metadata_json'],
        ]);

        return [
            'ok' => true,
            'item' => app_pdo_audit_log_fetch_by_event_key($pdo, $event['event_key']),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $filters
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_audit_log_fetch_latest(array $app, array $filters = []): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $dialect = app_sql_dialect_from_pdo($pdo);
        $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
        $limit = max(1, min(500, (int) ($filters['limit'] ?? 100)));
        $where = [];
        $params = [];

        foreach (['project_key', 'actor_login_id', 'event_type', 'target_type', 'target_key', 'result'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }

            $where[] = $field . ' = :' . $field;
            $params[':' . $field] = $value;
        }

        $statement = $pdo->prepare(
            'SELECT
                event_key,
                actor_login_id,
                actor_source,
                project_key,
                event_type,
                target_type,
                target_key,
                result,
                message,
                metadata_json,
                ' . $createdAtSelect . '
            FROM audit_events'
            . ($where === [] ? '' : ' WHERE ' . implode(' AND ', $where))
            . ' ORDER BY created_at DESC, id DESC ' . app_sql_limit_clause($dialect, $limit)
        );
        $statement->execute($params);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $items[] = app_audit_log_item_from_row($row);
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
 * @return array<string,string>
 */
function app_audit_log_normalize_input(array $input): array
{
    $eventKey = trim((string) ($input['event_key'] ?? ''));
    if ($eventKey === '') {
        $eventKey = 'audit_' . date('YmdHis') . '_' . bin2hex(random_bytes(6));
    }

    $event = [
        'event_key' => $eventKey,
        'actor_login_id' => app_audit_log_normalize_required_string($input, 'actor_login_id'),
        'actor_source' => app_audit_log_normalize_optional_string($input, 'actor_source', 'unknown'),
        'project_key' => app_audit_log_normalize_optional_string($input, 'project_key', ''),
        'event_type' => app_audit_log_normalize_required_string($input, 'event_type'),
        'target_type' => app_audit_log_normalize_optional_string($input, 'target_type', ''),
        'target_key' => app_audit_log_normalize_optional_string($input, 'target_key', ''),
        'result' => app_audit_log_normalize_optional_string($input, 'result', 'success'),
        'message' => app_audit_log_normalize_optional_string($input, 'message', ''),
    ];

    $metadata = $input['metadata'] ?? [];
    if (!is_array($metadata)) {
        throw new InvalidArgumentException('audit metadata must be an array.');
    }

    $event['metadata_json'] = json_encode(
        app_audit_log_sanitize_metadata($metadata),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    return $event;
}

/**
 * @param array<string,mixed> $input
 */
function app_audit_log_normalize_required_string(array $input, string $field): string
{
    $value = trim((string) ($input[$field] ?? ''));
    if ($value === '') {
        throw new InvalidArgumentException('audit field is required: ' . $field);
    }

    return $value;
}

/**
 * @param array<string,mixed> $input
 */
function app_audit_log_normalize_optional_string(array $input, string $field, string $default): string
{
    $value = trim((string) ($input[$field] ?? ''));
    return $value === '' ? $default : $value;
}

/**
 * @param array<string,mixed> $metadata
 * @return array<string,mixed>
 */
function app_audit_log_sanitize_metadata(array $metadata): array
{
    $sanitized = [];
    foreach ($metadata as $key => $value) {
        $normalizedKey = strtolower((string) $key);
        if (preg_match('/password|passwd|secret|token|credential/', $normalizedKey) === 1) {
            $sanitized[(string) $key] = '[redacted]';
            continue;
        }

        if (is_array($value)) {
            $sanitized[(string) $key] = app_audit_log_sanitize_metadata($value);
            continue;
        }

        $sanitized[(string) $key] = $value;
    }

    return $sanitized;
}

/**
 * @return array<string,mixed>
 */
function app_pdo_audit_log_fetch_by_event_key(PDO $pdo, string $eventKey): array
{
    $dialect = app_sql_dialect_from_pdo($pdo);
    $createdAtSelect = app_sql_datetime_select_expr($dialect, 'created_at', 'created_at');
    $statement = $pdo->prepare(
        'SELECT
            event_key,
            actor_login_id,
            actor_source,
            project_key,
            event_type,
            target_type,
            target_key,
            result,
            message,
            metadata_json,
            ' . $createdAtSelect . '
        FROM audit_events
        WHERE event_key = :event_key
        ' . app_sql_limit_clause($dialect, 1)
    );
    $statement->execute([
        ':event_key' => $eventKey,
    ]);
    $row = $statement->fetch();

    return is_array($row) ? app_audit_log_item_from_row($row) : [];
}

/**
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_audit_log_item_from_row(array $row): array
{
    $row = app_sql_normalize_row_keys($row);
    $metadata = json_decode((string) ($row['metadata_json'] ?? '{}'), true);
    if (!is_array($metadata)) {
        $metadata = [];
    }

    return [
        'event_key' => (string) ($row['event_key'] ?? ''),
        'actor_login_id' => (string) ($row['actor_login_id'] ?? ''),
        'actor_source' => (string) ($row['actor_source'] ?? ''),
        'project_key' => (string) ($row['project_key'] ?? ''),
        'event_type' => (string) ($row['event_type'] ?? ''),
        'target_type' => (string) ($row['target_type'] ?? ''),
        'target_key' => (string) ($row['target_key'] ?? ''),
        'result' => (string) ($row['result'] ?? ''),
        'message' => (string) ($row['message'] ?? ''),
        'metadata' => $metadata,
        'created_at' => (string) ($row['created_at'] ?? ''),
    ];
}
