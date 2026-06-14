SET @sample07_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE07'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample07_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample07_project_id;

INSERT INTO project_db_access_classes (
    project_id,
    source_name,
    store_base_path,
    is_autoload,
    notes,
    source_of_truth,
    last_detected_dbaccess_file,
    last_detected_data_file
) VALUES (
    @sample07_project_id,
    'TodoItem',
    '',
    0,
    'Basic CRUD DBAccess sample for a single imported TodoItem table.',
    'manual',
    '',
    ''
);

SET @sample07_db_access_class_id = LAST_INSERT_ID();

INSERT INTO project_db_access_functions (
    db_access_class_id,
    function_name,
    function_list_order,
    function_suffix,
    action_type,
    data_class_base_name,
    target_table_name,
    parameter_type,
    select_by_distinct,
    sort_order_columns,
    memo,
    limit_parameter_type,
    limit_fixed_parameter,
    or_group_type,
    single_proxy_auth_type,
    single_proxy_single_get_function_name,
    is_blob_target,
    detected_signature,
    detected_line,
    source_of_truth
) VALUES
(
    @sample07_db_access_class_id,
    'InsertTodoItem',
    10,
    '',
    'INSERT',
    'TodoItem',
    'TodoItem',
    'classobject',
    0,
    '',
    'Insert a new TodoItem row.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function InsertTodoItem($TodoItemObj)',
    10,
    'manual'
),
(
    @sample07_db_access_class_id,
    'UpdateTodoItem',
    20,
    '',
    'UPDATE',
    'TodoItem',
    'TodoItem',
    'classobject',
    0,
    '',
    'Update a TodoItem row by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function UpdateTodoItem($TodoItemObj)',
    20,
    'manual'
),
(
    @sample07_db_access_class_id,
    'DeleteTodoItem',
    30,
    '',
    'DELETE',
    'TodoItem',
    'TodoItem',
    'classobject',
    0,
    '',
    'Delete a TodoItem row by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function DeleteTodoItem($TodoItemObj)',
    30,
    'manual'
);

SET @sample07_insert_todo_item_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample07_db_access_class_id
      AND functions.function_name = 'InsertTodoItem'
);

SET @sample07_update_todo_item_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample07_db_access_class_id
      AND functions.function_name = 'UpdateTodoItem'
);

SET @sample07_delete_todo_item_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample07_db_access_class_id
      AND functions.function_name = 'DeleteTodoItem'
);

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample07_insert_todo_item_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample07_insert_todo_item_function_id, 'Status', 'argument', '', '', 20, 'manual'),
(@sample07_insert_todo_item_function_id, 'Body', 'argument', '', '', 30, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample07_update_todo_item_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample07_update_todo_item_function_id, 'Status', 'argument', '', '', 20, 'manual'),
(@sample07_update_todo_item_function_id, 'Body', 'argument', '', '', 30, 'manual');

INSERT INTO project_db_access_function_update_delete_wheres (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    or_group,
    relational_operator,
    where_order,
    source_of_truth
) VALUES
(@sample07_update_todo_item_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample07_delete_todo_item_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample07_project_id = NULL;
SET @sample07_db_access_class_id = NULL;
SET @sample07_insert_todo_item_function_id = NULL;
SET @sample07_update_todo_item_function_id = NULL;
SET @sample07_delete_todo_item_function_id = NULL;
