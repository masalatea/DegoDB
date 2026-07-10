SET @sample28_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE28'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample28_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample28_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample28_project_id
  AND contracts.contract_key = 'no_code_ticket';

DELETE FROM project_shared_contracts
WHERE project_id = @sample28_project_id
  AND contract_key = 'no_code_ticket';

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
    @sample28_project_id,
    'no_code_ticket',
    'no_code_ticket',
    'active',
    'server-copy',
    'managed-screen',
    'server-managed-copy',
    'Sample28 no_code_ticket contract is the first user-facing no-code data app screen.',
    'manual'
);

SET @sample28_no_code_ticket_shared_contract_id = LAST_INSERT_ID();

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
(@sample28_project_id, @sample28_no_code_ticket_shared_contract_id, 'id', 'server-copy', 'key', 'identifier', 'server-managed-copy', 'Primary key shown read-only in generated no-code screens.', 'manual'),
(@sample28_project_id, @sample28_no_code_ticket_shared_contract_id, 'title', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable title field for the generated update action.', 'manual'),
(@sample28_project_id, @sample28_no_code_ticket_shared_contract_id, 'status', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable status field for the generated update action.', 'manual'),
(@sample28_project_id, @sample28_no_code_ticket_shared_contract_id, 'priority', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable priority field for the generated update action.', 'manual'),
(@sample28_project_id, @sample28_no_code_ticket_shared_contract_id, 'body', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable body field for the generated update action.', 'manual');

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
    @sample28_project_id,
    'update_no_code_ticket',
    'no_code_ticket',
    'Update No-Code Ticket',
    'update',
    'active',
    'business-only',
    'project.edit',
    '["admin"]',
    '[]',
    '{}',
    'Sample28 generated update operation for the data-first no-code app MVP. Local stub admin may execute this tryout action through the runtime endpoint.',
    'manual'
);

SET @sample28_update_no_code_ticket_operation_id = LAST_INSERT_ID();

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
(@sample28_project_id, @sample28_update_no_code_ticket_operation_id, 'id', 'key', 1, 0, 'Primary key used by UpdateNoCodeTicket.', 'manual'),
(@sample28_project_id, @sample28_update_no_code_ticket_operation_id, 'title', 'input', 1, 1, 'Editable title input.', 'manual'),
(@sample28_project_id, @sample28_update_no_code_ticket_operation_id, 'status', 'input', 1, 1, 'Editable status input.', 'manual'),
(@sample28_project_id, @sample28_update_no_code_ticket_operation_id, 'priority', 'input', 1, 1, 'Editable priority input.', 'manual'),
(@sample28_project_id, @sample28_update_no_code_ticket_operation_id, 'body', 'input', 1, 1, 'Editable body input.', 'manual');

SET @sample28_project_id = NULL;
SET @sample28_no_code_ticket_shared_contract_id = NULL;
SET @sample28_update_no_code_ticket_operation_id = NULL;
