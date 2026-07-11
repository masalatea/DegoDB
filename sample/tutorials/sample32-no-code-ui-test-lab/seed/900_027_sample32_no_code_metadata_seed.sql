SET @sample32_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE32'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample32_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample32_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample32_project_id
  AND contracts.contract_key = 'no_code_lab_card';

DELETE FROM project_shared_contracts
WHERE project_id = @sample32_project_id
  AND contract_key = 'no_code_lab_card';

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
    @sample32_project_id,
    'no_code_lab_card',
    'no_code_lab_card',
    'active',
    'server-copy',
    'managed-screen',
    'server-managed-copy',
    'Sample32 no_code_lab_card contract is a dedicated no-code UI test lab fixture.',
    'manual'
);

SET @sample32_no_code_lab_card_shared_contract_id = LAST_INSERT_ID();

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
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'id', 'server-copy', 'key', 'identifier', 'server-managed-copy', 'Primary key shown read-only in generated no-code screens.', 'manual'),
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'title', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable title input for the first no-code UI lab fixture.', 'manual'),
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'status', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable status input for disabled action fixture checks.', 'manual'),
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'owner_name', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable owner input.', 'manual'),
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'priority', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable priority number input.', 'manual'),
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'due_on', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable due date input.', 'manual'),
(@sample32_project_id, @sample32_no_code_lab_card_shared_contract_id, 'notes', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable notes text input.', 'manual');

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
    @sample32_project_id,
    'archive_no_code_lab_card',
    'no_code_lab_card',
    'Archive Lab Card',
    'update',
    'active',
    'business-only',
    'project.edit',
    '["editor"]',
    '["no_code_lab_card:write"]',
    '{}',
    'Sample32 disabled action fixture for the dedicated no-code UI contract test lab.',
    'manual'
);

SET @sample32_archive_no_code_lab_card_operation_id = LAST_INSERT_ID();

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
(@sample32_project_id, @sample32_archive_no_code_lab_card_operation_id, 'id', 'key', 1, 0, 'Primary key used by ArchiveLabCard.', 'manual'),
(@sample32_project_id, @sample32_archive_no_code_lab_card_operation_id, 'status', 'input', 1, 1, 'Disabled action fixture status input.', 'manual'),
(@sample32_project_id, @sample32_archive_no_code_lab_card_operation_id, 'notes', 'input', 0, 1, 'Disabled action fixture notes input.', 'manual');

SET @sample32_project_id = NULL;
SET @sample32_no_code_lab_card_shared_contract_id = NULL;
SET @sample32_archive_no_code_lab_card_operation_id = NULL;
