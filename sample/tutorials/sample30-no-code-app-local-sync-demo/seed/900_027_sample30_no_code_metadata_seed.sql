SET @sample30_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE30'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample30_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample30_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample30_project_id
  AND contracts.contract_key = 'sync_task';

DELETE FROM project_shared_contracts
WHERE project_id = @sample30_project_id
  AND contract_key = 'sync_task';

INSERT INTO project_shared_contracts (
    project_id,
    contract_key,
    data_class_physical_name,
    status,
    sync_role,
    no_code_role,
    app_persistence_role,
    notes,
    source_of_truth
) VALUES (
    @sample30_project_id,
    'sync_task',
    'sync_task',
    'active',
    'local-copy',
    'managed-screen',
    'local-copy',
    'Sample30 sync_task contract connects no-code action intent to App-local sync handling.',
    'manual'
);

SET @sample30_sync_task_shared_contract_id = LAST_INSERT_ID();

INSERT INTO project_shared_contract_fields (
    project_id,
    shared_contract_id,
    field_physical_name,
    sync_role,
    operation_role,
    no_code_role,
    app_persistence_role,
    notes,
    source_of_truth
) VALUES
(@sample30_project_id, @sample30_sync_task_shared_contract_id, 'id', 'local-copy', 'key', 'identifier', 'local-copy', 'Primary key used by generated no-code and App-local sync handling.', 'manual'),
(@sample30_project_id, @sample30_sync_task_shared_contract_id, 'title', 'local-copy', 'readonly', 'field', 'local-copy', 'Read-only task title displayed by generated no-code screens.', 'manual'),
(@sample30_project_id, @sample30_sync_task_shared_contract_id, 'status', 'local-copy', 'editable', 'field', 'local-copy', 'Editable sync status field.', 'manual'),
(@sample30_project_id, @sample30_sync_task_shared_contract_id, 'note', 'local-copy', 'editable', 'field', 'local-copy', 'Editable note field updated through no-code sync handoff.', 'manual');

INSERT INTO project_managed_operations (
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
    @sample30_project_id,
    'update_sync_task',
    'sync_task',
    'Update Sync Task',
    'update',
    'active',
    'business-only',
    'project.edit',
    '["editor"]',
    '["sync_task:write"]',
    '{}',
    'Sample30 generated update operation for no-code to App-local sync handoff.',
    'manual'
);

SET @sample30_update_sync_task_operation_id = LAST_INSERT_ID();

INSERT INTO project_managed_operation_fields (
    project_id,
    managed_operation_id,
    field_physical_name,
    field_role,
    is_required,
    allow_client_write,
    notes,
    source_of_truth
) VALUES
(@sample30_project_id, @sample30_update_sync_task_operation_id, 'id', 'key', 1, 0, 'Primary key used by UpdateSyncTask.', 'manual'),
(@sample30_project_id, @sample30_update_sync_task_operation_id, 'status', 'input', 1, 1, 'Editable status input.', 'manual'),
(@sample30_project_id, @sample30_update_sync_task_operation_id, 'note', 'input', 1, 1, 'Editable note input.', 'manual');

SET @sample30_project_id = NULL;
SET @sample30_sync_task_shared_contract_id = NULL;
SET @sample30_update_sync_task_operation_id = NULL;
