SET @sample31_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE31'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample31_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample31_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample31_project_id
  AND contracts.contract_key = 'inventory_request';

DELETE FROM project_shared_contracts
WHERE project_id = @sample31_project_id
  AND contract_key = 'inventory_request';

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
    @sample31_project_id,
    'inventory_request',
    'inventory_request',
    'active',
    'server-copy',
    'managed-screen',
    'server-managed-copy',
    'Sample31 inventory_request contract is a third data-first no-code domain sample.',
    'manual'
);

SET @sample31_inventory_request_shared_contract_id = LAST_INSERT_ID();

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
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'id', 'server-copy', 'key', 'identifier', 'server-managed-copy', 'Primary key shown read-only in generated no-code screens.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'request_number', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Read-only request number representing inventory workflow context.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'requester_name', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Read-only requester context field.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'warehouse_code', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Read-only warehouse context field.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'needed_by', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Read-only needed-by date for typed date runtime-data semantics.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'item_sku', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable item SKU input.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'quantity_needed', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable requested quantity input.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'status', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable status input.', 'manual'),
(@sample31_project_id, @sample31_inventory_request_shared_contract_id, 'fulfillment_note', 'server-copy', 'editable', 'field', 'server-managed-copy', 'Editable fulfillment note text.', 'manual');

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
    @sample31_project_id,
    'update_inventory_request',
    'inventory_request',
    'Update Inventory Request',
    'update',
    'active',
    'business-only',
    'project.edit',
    '["editor"]',
    '["inventory_request:write"]',
    '{}',
    'Sample31 generated update operation for the third data-first no-code sample.',
    'manual'
);

SET @sample31_update_inventory_request_operation_id = LAST_INSERT_ID();

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
(@sample31_project_id, @sample31_update_inventory_request_operation_id, 'id', 'key', 1, 0, 'Primary key used by UpdateInventoryRequest.', 'manual'),
(@sample31_project_id, @sample31_update_inventory_request_operation_id, 'item_sku', 'input', 1, 1, 'Editable item SKU input.', 'manual'),
(@sample31_project_id, @sample31_update_inventory_request_operation_id, 'quantity_needed', 'input', 1, 1, 'Editable requested quantity input.', 'manual'),
(@sample31_project_id, @sample31_update_inventory_request_operation_id, 'status', 'input', 1, 1, 'Editable status input.', 'manual'),
(@sample31_project_id, @sample31_update_inventory_request_operation_id, 'fulfillment_note', 'input', 1, 1, 'Editable fulfillment note input.', 'manual');

SET @sample31_project_id = NULL;
SET @sample31_inventory_request_shared_contract_id = NULL;
SET @sample31_update_inventory_request_operation_id = NULL;
