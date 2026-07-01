<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array{ok:bool,items:list<array<string,mixed>>,error:string}
 */
function app_pdo_fetch_managed_operation_snapshot(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'SELECT
                o.id AS operation_id,
                o.project_id,
                o.operation_key,
                o.contract_key,
                o.name AS operation_name,
                o.operation_type,
                o.status AS operation_status,
                o.storage_policy,
                o.permission_key,
                o.required_roles_json,
                o.required_scopes_json,
                o.required_claims_json,
                o.notes AS operation_notes,
                o.source_of_truth AS operation_source_of_truth,
                f.id AS field_id,
                f.field_physical_name,
                f.field_role,
                f.is_required,
                f.allow_client_write,
                f.notes AS field_notes,
                f.source_of_truth AS field_source_of_truth
             FROM project_managed_operations AS o
             LEFT JOIN project_managed_operation_fields AS f
                ON f.project_id = o.project_id
               AND f.managed_operation_id = o.id
             WHERE o.project_id = :project_id
             ORDER BY o.operation_key, f.field_role, f.field_physical_name'
        );
        $statement->execute([':project_id' => $projectId]);

        $items = [];
        $indexByOperationId = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $operationId = (string) ($row['operation_id'] ?? '');
            if ($operationId === '') {
                continue;
            }

            if (!isset($indexByOperationId[$operationId])) {
                $indexByOperationId[$operationId] = count($items);
                $items[] = app_managed_operation_pdo_operation_item_from_row($projectKey, $row);
            }

            $fieldId = (string) ($row['field_id'] ?? '');
            if ($fieldId === '') {
                continue;
            }

            $items[$indexByOperationId[$operationId]]['fields'][] = [
                'id' => $fieldId,
                'field_physical_name' => (string) ($row['field_physical_name'] ?? ''),
                'field_role' => (string) ($row['field_role'] ?? ''),
                'is_required' => ((int) ($row['is_required'] ?? 0)) === 1,
                'allow_client_write' => ((int) ($row['allow_client_write'] ?? 0)) === 1,
                'notes' => (string) ($row['field_notes'] ?? ''),
                'source_of_truth' => (string) ($row['field_source_of_truth'] ?? ''),
            ];
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
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_upsert_managed_operation(array $app, string $projectKey, array $input): array
{
    try {
        $operationKey = app_managed_operation_normalize_key((string) ($input['operation_key'] ?? ''));
        if ($operationKey === '') {
            throw new RuntimeException('operation key が空です。');
        }

        $contractKey = app_managed_operation_normalize_contract_key((string) ($input['contract_key'] ?? ''));
        if ($contractKey === '') {
            throw new RuntimeException('contract key が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_pdo_resolve_project_id($pdo, $projectKey);
        $existingId = app_managed_operation_pdo_find_operation_id($pdo, $projectId, $operationKey);
        $payload = [
            ':project_id' => $projectId,
            ':operation_key' => $operationKey,
            ':contract_key' => $contractKey,
            ':name' => trim((string) ($input['name'] ?? $operationKey)),
            ':operation_type' => app_managed_operation_normalize_type((string) ($input['operation_type'] ?? 'read')),
            ':status' => app_managed_operation_normalize_status((string) ($input['status'] ?? 'active')),
            ':storage_policy' => app_managed_operation_normalize_storage_policy((string) ($input['storage_policy'] ?? 'business-only')),
            ':permission_key' => strtolower(trim((string) ($input['permission_key'] ?? 'project.read'))),
            ':required_roles_json' => app_managed_operation_json_text(app_managed_operation_string_list($input['required_roles'] ?? [])),
            ':required_scopes_json' => app_managed_operation_json_text(app_managed_operation_string_list($input['required_scopes'] ?? [])),
            ':required_claims_json' => app_managed_operation_json_text(app_managed_operation_string_map($input['required_claims'] ?? [])),
            ':notes' => trim((string) ($input['notes'] ?? '')),
            ':source_of_truth' => trim((string) ($input['source_of_truth'] ?? 'manual')),
        ];

        if ($existingId > 0) {
            $payload[':id'] = $existingId;
            $executePayload = $payload;
            unset($executePayload[':operation_key']);
            $statement = $pdo->prepare(
                'UPDATE project_managed_operations
                 SET
                    contract_key = :contract_key,
                    name = :name,
                    operation_type = :operation_type,
                    status = :status,
                    storage_policy = :storage_policy,
                    permission_key = :permission_key,
                    required_roles_json = :required_roles_json,
                    required_scopes_json = :required_scopes_json,
                    required_claims_json = :required_claims_json,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                   AND project_id = :project_id'
            );
        } else {
            $executePayload = $payload;
            $statement = $pdo->prepare(
                'INSERT INTO project_managed_operations (
                    project_id,
                    operation_key,
                    contract_key,
                    name,
                    operation_type,
                    status,
                    storage_policy,
                    permission_key,
                    required_roles_json,
                    required_scopes_json,
                    required_claims_json,
                    notes,
                    source_of_truth
                ) VALUES (
                    :project_id,
                    :operation_key,
                    :contract_key,
                    :name,
                    :operation_type,
                    :status,
                    :storage_policy,
                    :permission_key,
                    :required_roles_json,
                    :required_scopes_json,
                    :required_claims_json,
                    :notes,
                    :source_of_truth
                )'
            );
        }

        $statement->execute($executePayload);

        return app_pdo_fetch_managed_operation_item($app, $projectKey, $operationKey);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array<string,mixed> $input
 * @return array{ok:bool,item:array<string,mixed>|null,error:string}
 */
function app_pdo_upsert_managed_operation_field(
    array $app,
    string $projectKey,
    string $operationKey,
    array $input,
): array {
    try {
        $normalizedOperationKey = app_managed_operation_normalize_key($operationKey);
        if ($normalizedOperationKey === '') {
            throw new RuntimeException('operation key が空です。');
        }

        $fieldPhysicalName = trim((string) ($input['field_physical_name'] ?? ''));
        if ($fieldPhysicalName === '') {
            throw new RuntimeException('field physical name が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_managed_operation_pdo_resolve_project_id($pdo, $projectKey);
        $operationId = app_managed_operation_pdo_find_operation_id($pdo, $projectId, $normalizedOperationKey);
        if ($operationId <= 0) {
            throw new RuntimeException('managed operation が見つかりません: ' . $normalizedOperationKey);
        }

        $existingId = app_managed_operation_pdo_find_field_id($pdo, $projectId, $operationId, $fieldPhysicalName);
        $payload = [
            ':project_id' => $projectId,
            ':managed_operation_id' => $operationId,
            ':field_physical_name' => $fieldPhysicalName,
            ':field_role' => app_managed_operation_normalize_field_role((string) ($input['field_role'] ?? 'input')),
            ':is_required' => (int) ((bool) ($input['is_required'] ?? false)),
            ':allow_client_write' => (int) ((bool) ($input['allow_client_write'] ?? false)),
            ':notes' => trim((string) ($input['notes'] ?? '')),
            ':source_of_truth' => trim((string) ($input['source_of_truth'] ?? 'manual')),
        ];

        if ($existingId > 0) {
            $payload[':id'] = $existingId;
            $executePayload = $payload;
            unset($executePayload[':managed_operation_id'], $executePayload[':field_physical_name']);
            $statement = $pdo->prepare(
                'UPDATE project_managed_operation_fields
                 SET
                    field_role = :field_role,
                    is_required = :is_required,
                    allow_client_write = :allow_client_write,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                   AND project_id = :project_id'
            );
        } else {
            $executePayload = $payload;
            $statement = $pdo->prepare(
                'INSERT INTO project_managed_operation_fields (
                    project_id,
                    managed_operation_id,
                    field_physical_name,
                    field_role,
                    is_required,
                    allow_client_write,
                    notes,
                    source_of_truth
                ) VALUES (
                    :project_id,
                    :managed_operation_id,
                    :field_physical_name,
                    :field_role,
                    :is_required,
                    :allow_client_write,
                    :notes,
                    :source_of_truth
                )'
            );
        }

        $statement->execute($executePayload);

        return app_pdo_fetch_managed_operation_item($app, $projectKey, $normalizedOperationKey);
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
function app_pdo_fetch_managed_operation_item(array $app, string $projectKey, string $operationKey): array
{
    $snapshot = app_pdo_fetch_managed_operation_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $snapshot['error'],
        ];
    }

    $normalizedOperationKey = app_managed_operation_normalize_key($operationKey);
    foreach ($snapshot['items'] as $item) {
        if ((string) ($item['operation_key'] ?? '') === $normalizedOperationKey) {
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
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function app_managed_operation_pdo_operation_item_from_row(string $projectKey, array $row): array
{
    return [
        'project_key' => $projectKey,
        'project_id' => (string) ($row['project_id'] ?? ''),
        'id' => (string) ($row['operation_id'] ?? ''),
        'operation_key' => (string) ($row['operation_key'] ?? ''),
        'contract_key' => (string) ($row['contract_key'] ?? ''),
        'name' => (string) ($row['operation_name'] ?? ''),
        'operation_type' => (string) ($row['operation_type'] ?? ''),
        'status' => (string) ($row['operation_status'] ?? ''),
        'storage_policy' => (string) ($row['storage_policy'] ?? ''),
        'permission_key' => (string) ($row['permission_key'] ?? ''),
        'required_roles' => app_managed_operation_json_list((string) ($row['required_roles_json'] ?? '[]')),
        'required_scopes' => app_managed_operation_json_list((string) ($row['required_scopes_json'] ?? '[]')),
        'required_claims' => app_managed_operation_json_map((string) ($row['required_claims_json'] ?? '{}')),
        'notes' => (string) ($row['operation_notes'] ?? ''),
        'source_of_truth' => (string) ($row['operation_source_of_truth'] ?? ''),
        'fields' => [],
    ];
}

function app_managed_operation_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
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

function app_managed_operation_pdo_find_operation_id(PDO $pdo, int $projectId, string $operationKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM project_managed_operations
         WHERE project_id = :project_id
           AND operation_key = :operation_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':operation_key' => $operationKey,
    ]);

    return (int) ($statement->fetchColumn() ?: 0);
}

function app_managed_operation_pdo_find_field_id(
    PDO $pdo,
    int $projectId,
    int $operationId,
    string $fieldPhysicalName,
): int {
    $statement = $pdo->prepare(
        'SELECT id
         FROM project_managed_operation_fields
         WHERE project_id = :project_id
           AND managed_operation_id = :managed_operation_id
           AND field_physical_name = :field_physical_name
         LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':managed_operation_id' => $operationId,
        ':field_physical_name' => $fieldPhysicalName,
    ]);

    return (int) ($statement->fetchColumn() ?: 0);
}

function app_managed_operation_normalize_key(string $value): string
{
    $normalized = strtolower(trim($value));
    return preg_match('/^[a-z][a-z0-9_]*$/', $normalized) === 1 ? $normalized : '';
}

function app_managed_operation_normalize_contract_key(string $value): string
{
    $normalized = strtolower(trim($value));
    return preg_match('/^[a-z][a-z0-9_]*$/', $normalized) === 1 ? $normalized : '';
}

function app_managed_operation_normalize_type(string $value): string
{
    $normalized = strtolower(trim($value));
    return in_array($normalized, ['list', 'read', 'create', 'update', 'delete'], true) ? $normalized : 'read';
}

function app_managed_operation_normalize_status(string $value): string
{
    $normalized = strtolower(trim($value));
    return in_array($normalized, ['draft', 'active', 'paused', 'archived'], true) ? $normalized : 'active';
}

function app_managed_operation_normalize_storage_policy(string $value): string
{
    $normalized = strtolower(trim($value));
    return in_array($normalized, ['business-only', 'allow-local-metadata-read'], true) ? $normalized : 'business-only';
}

function app_managed_operation_normalize_field_role(string $value): string
{
    $normalized = strtolower(trim($value));
    return in_array($normalized, ['key', 'input', 'output', 'filter'], true) ? $normalized : 'input';
}

function app_managed_operation_json_text(array $value): string
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        return '[]';
    }

    return $json;
}

/**
 * @return list<string>
 */
function app_managed_operation_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        if (is_string($item) && trim($item) !== '') {
            $items[] = strtolower(trim($item));
        }
    }

    return array_values(array_unique($items));
}

/**
 * @return array<string,string>
 */
function app_managed_operation_string_map(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $key => $item) {
        if (is_string($key) && $key !== '' && is_scalar($item)) {
            $items[$key] = (string) $item;
        }
    }

    ksort($items);
    return $items;
}

/**
 * @return list<string>
 */
function app_managed_operation_json_list(string $json): array
{
    $decoded = json_decode($json, true);
    return app_managed_operation_string_list($decoded);
}

/**
 * @return array<string,string>
 */
function app_managed_operation_json_map(string $json): array
{
    $decoded = json_decode($json, true);
    return app_managed_operation_string_map($decoded);
}
