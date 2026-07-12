SET @sample18_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE18'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample18_project_id
  AND operations.contract_key = 'task_card';

DELETE FROM project_managed_operations
WHERE project_id = @sample18_project_id
  AND contract_key = 'task_card';

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample18_project_id
  AND contracts.contract_key = 'task_card';

DELETE FROM project_shared_contracts
WHERE project_id = @sample18_project_id
  AND contract_key = 'task_card';

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
    @sample18_project_id,
    'task_card',
    'task_card',
    'active',
    'server-copy',
    'managed-screen',
    'server-managed-copy',
    'Sample18 task_card contract is the first existing sample UI no-code extraction target.',
    'manual'
);

SET @sample18_task_card_shared_contract_id = LAST_INSERT_ID();

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
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'id', 'server-copy', 'key', 'identifier', 'server-managed-copy', 'Primary key shown read-only in generated no-code screens.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'title', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable task title accepted by the curated sample18 mutation route.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'body', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable task body accepted by the curated sample18 mutation route.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'status', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly task status retained outside the generated create handoff.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'assigned_to', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable assignee accepted by the curated sample18 mutation route.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'priority', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable priority accepted by the curated sample18 mutation route.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'due_date', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable due date accepted by the curated sample18 mutation route.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'completed_at', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly completion timestamp for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'updated_at', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly update timestamp for first sample18 no-code extraction.', 'manual');

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
) VALUES
(@sample18_project_id, 'create_task_card', 'task_card', 'Create Task Card', 'create', 'active', 'business-only', 'project.edit', '["editor"]', '["task_card:write"]', '{}', 'Sample18 generated action metadata for the curated create boundary; generated execution remains disabled by policy.', 'manual'),
(@sample18_project_id, 'update_task_card', 'task_card', 'Update Task Card', 'update', 'active', 'business-only', 'project.edit', '["editor"]', '["task_card:write"]', '{}', 'Sample18 generated action metadata for the curated update boundary; generated execution remains disabled by policy.', 'manual'),
(@sample18_project_id, 'complete_task_card', 'task_card', 'Complete Task Card', 'update', 'active', 'business-only', 'project.edit', '["editor"]', '["task_card:write"]', '{}', 'Sample18 generated action metadata for the curated complete boundary; generated execution remains disabled by policy.', 'manual');

SET @sample18_create_task_card_operation_id = (
    SELECT id
    FROM project_managed_operations
    WHERE project_id = @sample18_project_id
      AND operation_key = 'create_task_card'
);

SET @sample18_update_task_card_operation_id = (
    SELECT id
    FROM project_managed_operations
    WHERE project_id = @sample18_project_id
      AND operation_key = 'update_task_card'
);

SET @sample18_complete_task_card_operation_id = (
    SELECT id
    FROM project_managed_operations
    WHERE project_id = @sample18_project_id
      AND operation_key = 'complete_task_card'
);

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
(@sample18_project_id, @sample18_create_task_card_operation_id, 'title', 'input', 1, 1, 'Required title input for sample18 create metadata.', 'manual'),
(@sample18_project_id, @sample18_create_task_card_operation_id, 'body', 'input', 0, 1, 'Optional body input for sample18 create metadata.', 'manual'),
(@sample18_project_id, @sample18_create_task_card_operation_id, 'assigned_to', 'input', 0, 1, 'Optional assignee input for sample18 create metadata.', 'manual'),
(@sample18_project_id, @sample18_create_task_card_operation_id, 'priority', 'input', 0, 1, 'Optional priority input for sample18 create metadata.', 'manual'),
(@sample18_project_id, @sample18_create_task_card_operation_id, 'due_date', 'input', 0, 1, 'Optional due date input for sample18 create metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'id', 'key', 1, 0, 'Primary key for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'title', 'input', 1, 1, 'Required title input for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'body', 'input', 0, 1, 'Optional body input for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'status', 'input', 0, 1, 'Optional status input for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'assigned_to', 'input', 0, 1, 'Optional assignee input for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'priority', 'input', 0, 1, 'Optional priority input for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_update_task_card_operation_id, 'due_date', 'input', 0, 1, 'Optional due date input for sample18 update metadata.', 'manual'),
(@sample18_project_id, @sample18_complete_task_card_operation_id, 'id', 'key', 1, 0, 'Primary key for sample18 complete metadata.', 'manual');

SET @sample18_project_id = NULL;
SET @sample18_task_card_shared_contract_id = NULL;
SET @sample18_create_task_card_operation_id = NULL;
SET @sample18_update_task_card_operation_id = NULL;
SET @sample18_complete_task_card_operation_id = NULL;
