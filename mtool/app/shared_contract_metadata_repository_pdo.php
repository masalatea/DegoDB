<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

/**
 * @return array{
 *     ok:bool,
 *     items:list<array<string,mixed>>,
 *     error:string
 * }
 */
function app_pdo_fetch_shared_contract_metadata_snapshot(array $app, string $projectKey): array
{
    try {
        $pdo = app_create_config_pdo($app);
        $projectId = app_shared_contract_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $statement = $pdo->prepare(
            'SELECT
                c.id AS contract_id,
                c.project_id,
                c.contract_key,
                c.data_class_physical_name,
                c.status AS contract_status,
                c.usage_intent AS contract_usage_intent,
                c.view_variant_preference AS contract_view_variant_preference,
                c.sync_role AS contract_sync_role,
                c.no_code_role AS contract_no_code_role,
                c.app_persistence_role AS contract_app_persistence_role,
                c.notes AS contract_notes,
                c.source_of_truth AS contract_source_of_truth,
                f.id AS field_id,
                f.field_physical_name,
                f.sync_role AS field_sync_role,
                f.operation_role AS field_operation_role,
                f.no_code_role AS field_no_code_role,
                f.app_persistence_role AS field_app_persistence_role,
                f.notes AS field_notes,
                f.source_of_truth AS field_source_of_truth
             FROM project_shared_contracts AS c
             LEFT JOIN project_shared_contract_fields AS f
                ON f.project_id = c.project_id
               AND f.shared_contract_id = c.id
             WHERE c.project_id = :project_id
             ORDER BY c.contract_key, f.field_physical_name'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);

        $items = [];
        $indexByContractId = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $contractId = (string) ($row['contract_id'] ?? '');
            if ($contractId === '') {
                continue;
            }

            if (!isset($indexByContractId[$contractId])) {
                $indexByContractId[$contractId] = count($items);
                $items[] = [
                    'project_id' => (string) ($row['project_id'] ?? ''),
                    'id' => $contractId,
                    'contract_key' => (string) ($row['contract_key'] ?? ''),
                    'data_class_physical_name' => (string) ($row['data_class_physical_name'] ?? ''),
                    'status' => (string) ($row['contract_status'] ?? ''),
                    'usage_intent' => (string) ($row['contract_usage_intent'] ?? ''),
                    'view_variant_preference' => (string) ($row['contract_view_variant_preference'] ?? ''),
                    'sync_role' => (string) ($row['contract_sync_role'] ?? ''),
                    'no_code_role' => (string) ($row['contract_no_code_role'] ?? ''),
                    'app_persistence_role' => (string) ($row['contract_app_persistence_role'] ?? ''),
                    'notes' => (string) ($row['contract_notes'] ?? ''),
                    'source_of_truth' => (string) ($row['contract_source_of_truth'] ?? ''),
                    'fields' => [],
                ];
            }

            $fieldId = (string) ($row['field_id'] ?? '');
            if ($fieldId === '') {
                continue;
            }

            $items[$indexByContractId[$contractId]]['fields'][] = [
                'id' => $fieldId,
                'field_physical_name' => (string) ($row['field_physical_name'] ?? ''),
                'sync_role' => (string) ($row['field_sync_role'] ?? ''),
                'operation_role' => (string) ($row['field_operation_role'] ?? ''),
                'no_code_role' => (string) ($row['field_no_code_role'] ?? ''),
                'app_persistence_role' => (string) ($row['field_app_persistence_role'] ?? ''),
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
function app_pdo_upsert_shared_contract_metadata(array $app, string $projectKey, array $input): array
{
    try {
        $contractKey = trim((string) ($input['contract_key'] ?? ''));
        if ($contractKey === '') {
            throw new RuntimeException('contract key が空です。');
        }

        $dataClassPhysicalName = trim((string) ($input['data_class_physical_name'] ?? $contractKey));
        if ($dataClassPhysicalName === '') {
            throw new RuntimeException('data class physical name が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_shared_contract_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $existingId = app_shared_contract_metadata_pdo_find_contract_id($pdo, $projectId, $contractKey);
        if ($existingId > 0) {
            $statement = $pdo->prepare(
                'UPDATE project_shared_contracts
                 SET
                    data_class_physical_name = :data_class_physical_name,
                    status = :status,
                    usage_intent = :usage_intent,
                    view_variant_preference = :view_variant_preference,
                    sync_role = :sync_role,
                    no_code_role = :no_code_role,
                    app_persistence_role = :app_persistence_role,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                   AND project_id = :project_id'
            );
            $statement->execute([
                ':data_class_physical_name' => $dataClassPhysicalName,
                ':status' => trim((string) ($input['status'] ?? 'active')),
                ':usage_intent' => trim((string) ($input['usage_intent'] ?? '')),
                ':view_variant_preference' => trim((string) ($input['view_variant_preference'] ?? '')),
                ':sync_role' => trim((string) ($input['sync_role'] ?? '')),
                ':no_code_role' => trim((string) ($input['no_code_role'] ?? '')),
                ':app_persistence_role' => trim((string) ($input['app_persistence_role'] ?? '')),
                ':notes' => trim((string) ($input['notes'] ?? '')),
                ':source_of_truth' => trim((string) ($input['source_of_truth'] ?? 'manual')),
                ':id' => $existingId,
                ':project_id' => $projectId,
            ]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO project_shared_contracts (
                    project_id,
                    contract_key,
                    data_class_physical_name,
                    status,
                    usage_intent,
                    view_variant_preference,
                    sync_role,
                    no_code_role,
                    app_persistence_role,
                    notes,
                    source_of_truth
                ) VALUES (
                    :project_id,
                    :contract_key,
                    :data_class_physical_name,
                    :status,
                    :usage_intent,
                    :view_variant_preference,
                    :sync_role,
                    :no_code_role,
                    :app_persistence_role,
                    :notes,
                    :source_of_truth
                )'
            );
            $statement->execute([
                ':project_id' => $projectId,
                ':contract_key' => $contractKey,
                ':data_class_physical_name' => $dataClassPhysicalName,
                ':status' => trim((string) ($input['status'] ?? 'active')),
                ':usage_intent' => trim((string) ($input['usage_intent'] ?? '')),
                ':view_variant_preference' => trim((string) ($input['view_variant_preference'] ?? '')),
                ':sync_role' => trim((string) ($input['sync_role'] ?? '')),
                ':no_code_role' => trim((string) ($input['no_code_role'] ?? '')),
                ':app_persistence_role' => trim((string) ($input['app_persistence_role'] ?? '')),
                ':notes' => trim((string) ($input['notes'] ?? '')),
                ':source_of_truth' => trim((string) ($input['source_of_truth'] ?? 'manual')),
            ]);
        }

        return app_pdo_fetch_shared_contract_metadata_item($app, $projectKey, $contractKey);
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
function app_pdo_upsert_shared_contract_field_metadata(
    array $app,
    string $projectKey,
    string $contractKey,
    array $input,
): array {
    try {
        $fieldPhysicalName = trim((string) ($input['field_physical_name'] ?? ''));
        if ($fieldPhysicalName === '') {
            throw new RuntimeException('field physical name が空です。');
        }

        $pdo = app_create_config_pdo($app);
        $projectId = app_shared_contract_metadata_pdo_resolve_project_id($pdo, $projectKey);
        $contractId = app_shared_contract_metadata_pdo_find_contract_id($pdo, $projectId, $contractKey);
        if ($contractId <= 0) {
            throw new RuntimeException('shared contract metadata が見つかりません: ' . $contractKey);
        }

        $existingId = app_shared_contract_metadata_pdo_find_field_id($pdo, $projectId, $contractId, $fieldPhysicalName);
        if ($existingId > 0) {
            $statement = $pdo->prepare(
                'UPDATE project_shared_contract_fields
                 SET
                    sync_role = :sync_role,
                    operation_role = :operation_role,
                    no_code_role = :no_code_role,
                    app_persistence_role = :app_persistence_role,
                    notes = :notes,
                    source_of_truth = :source_of_truth,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id
                   AND project_id = :project_id'
            );
            $statement->execute([
                ':sync_role' => trim((string) ($input['sync_role'] ?? '')),
                ':operation_role' => trim((string) ($input['operation_role'] ?? '')),
                ':no_code_role' => trim((string) ($input['no_code_role'] ?? '')),
                ':app_persistence_role' => trim((string) ($input['app_persistence_role'] ?? '')),
                ':notes' => trim((string) ($input['notes'] ?? '')),
                ':source_of_truth' => trim((string) ($input['source_of_truth'] ?? 'manual')),
                ':id' => $existingId,
                ':project_id' => $projectId,
            ]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO project_shared_contract_fields (
                    project_id,
                    shared_contract_id,
                    field_physical_name,
                    sync_role,
                    operation_role,
                    no_code_role,
                    app_persistence_role,
                    notes,
                    source_of_truth
                ) VALUES (
                    :project_id,
                    :shared_contract_id,
                    :field_physical_name,
                    :sync_role,
                    :operation_role,
                    :no_code_role,
                    :app_persistence_role,
                    :notes,
                    :source_of_truth
                )'
            );
            $statement->execute([
                ':project_id' => $projectId,
                ':shared_contract_id' => $contractId,
                ':field_physical_name' => $fieldPhysicalName,
                ':sync_role' => trim((string) ($input['sync_role'] ?? '')),
                ':operation_role' => trim((string) ($input['operation_role'] ?? '')),
                ':no_code_role' => trim((string) ($input['no_code_role'] ?? '')),
                ':app_persistence_role' => trim((string) ($input['app_persistence_role'] ?? '')),
                ':notes' => trim((string) ($input['notes'] ?? '')),
                ':source_of_truth' => trim((string) ($input['source_of_truth'] ?? 'manual')),
            ]);
        }

        return app_pdo_fetch_shared_contract_metadata_item($app, $projectKey, $contractKey);
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
function app_pdo_fetch_shared_contract_metadata_item(array $app, string $projectKey, string $contractKey): array
{
    $snapshot = app_pdo_fetch_shared_contract_metadata_snapshot($app, $projectKey);
    if (!$snapshot['ok']) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $snapshot['error'],
        ];
    }

    foreach ($snapshot['items'] as $item) {
        if ((string) ($item['contract_key'] ?? '') === $contractKey) {
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

function app_shared_contract_metadata_pdo_resolve_project_id(PDO $pdo, string $projectKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM projects
         WHERE project_key = :project_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_key' => $projectKey,
    ]);

    $projectId = $statement->fetchColumn();
    if ($projectId === false) {
        throw new RuntimeException('project が見つかりません: ' . $projectKey);
    }

    return (int) $projectId;
}

function app_shared_contract_metadata_pdo_find_contract_id(PDO $pdo, int $projectId, string $contractKey): int
{
    $statement = $pdo->prepare(
        'SELECT id
         FROM project_shared_contracts
         WHERE project_id = :project_id
           AND contract_key = :contract_key
         LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':contract_key' => $contractKey,
    ]);

    return (int) ($statement->fetchColumn() ?: 0);
}

function app_shared_contract_metadata_pdo_find_field_id(
    PDO $pdo,
    int $projectId,
    int $contractId,
    string $fieldPhysicalName,
): int {
    $statement = $pdo->prepare(
        'SELECT id
         FROM project_shared_contract_fields
         WHERE project_id = :project_id
           AND shared_contract_id = :shared_contract_id
           AND field_physical_name = :field_physical_name
         LIMIT 1'
    );
    $statement->execute([
        ':project_id' => $projectId,
        ':shared_contract_id' => $contractId,
        ':field_physical_name' => $fieldPhysicalName,
    ]);

    return (int) ($statement->fetchColumn() ?: 0);
}
