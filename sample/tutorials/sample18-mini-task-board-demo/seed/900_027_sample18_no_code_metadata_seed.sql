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
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'title', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly task title for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'body', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly task body for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'status', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly task status for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'assigned_to', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly assignee for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'priority', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly priority for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'due_date', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly due date for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'completed_at', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly completion timestamp for first sample18 no-code extraction.', 'manual'),
(@sample18_project_id, @sample18_task_card_shared_contract_id, 'updated_at', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Readonly update timestamp for first sample18 no-code extraction.', 'manual');

SET @sample18_project_id = NULL;
SET @sample18_task_card_shared_contract_id = NULL;
