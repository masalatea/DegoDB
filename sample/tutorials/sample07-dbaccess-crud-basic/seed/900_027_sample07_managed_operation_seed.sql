SET @sample07_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE07'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample07_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample07_project_id;

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
    @sample07_project_id,
    'update_todo_item',
    'todo_item',
    'Update Todo Item',
    'update',
    'active',
    'business-only',
    'project.edit',
    '["editor"]',
    '["todo_item:write"]',
    '{}',
    'Sample07 managed operation that binds to generated UpdateTodoItem DBAccess method.',
    'manual'
);

SET @sample07_update_todo_item_operation_id = LAST_INSERT_ID();

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
(@sample07_project_id, @sample07_update_todo_item_operation_id, 'id', 'key', 1, 0, 'Primary key used by UpdateTodoItem.', 'manual'),
(@sample07_project_id, @sample07_update_todo_item_operation_id, 'title', 'input', 1, 1, 'Editable title input.', 'manual'),
(@sample07_project_id, @sample07_update_todo_item_operation_id, 'status', 'input', 1, 1, 'Editable status input.', 'manual'),
(@sample07_project_id, @sample07_update_todo_item_operation_id, 'body', 'input', 1, 1, 'Editable body input.', 'manual');

SET @sample07_project_id = NULL;
SET @sample07_update_todo_item_operation_id = NULL;
