SET @sample18_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE18'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample18_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample18_project_id;

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
    @sample18_project_id,
    'task_card',
    '',
    0,
    'Instruction-driven mini task board demo DBAccess class.',
    'manual',
    '',
    ''
);

SET @sample18_db_access_class_id = LAST_INSERT_ID();

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
    @sample18_db_access_class_id,
    'GetTaskCardList',
    10,
    '',
    'SELECTLIST',
    'TaskCard',
    'task_card',
    '',
    0,
    'task_card.due_date asc, task_card.priority desc, task_card.id asc',
    'List task cards by status for the mini task board demo.',
    'argument',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetTaskCardList($param_TaskCard_Status_where, $limit)',
    10,
    'manual'
),
(
    @sample18_db_access_class_id,
    'GetTaskCard',
    20,
    '',
    'SELECTSINGLE',
    'TaskCard',
    'task_card',
    '',
    0,
    '',
    'Get a single task card by primary key.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetTaskCard($param_TaskCard_Id_where)',
    20,
    'manual'
),
(
    @sample18_db_access_class_id,
    'InsertTaskCard',
    30,
    '',
    'INSERT',
    'TaskCard',
    'task_card',
    'classobject',
    0,
    '',
    'Create a task card from a class object.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function InsertTaskCard($TaskCardObj)',
    30,
    'manual'
),
(
    @sample18_db_access_class_id,
    'UpdateTaskCard',
    40,
    '',
    'UPDATE',
    'TaskCard',
    'task_card',
    'classobject',
    0,
    '',
    'Update a task card by primary key.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function UpdateTaskCard($TaskCardObj)',
    40,
    'manual'
),
(
    @sample18_db_access_class_id,
    'CompleteTaskCard',
    50,
    '',
    'UPDATE',
    'TaskCard',
    'task_card',
    'classobject',
    0,
    '',
    'Mark a task card as complete by primary key.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function CompleteTaskCard($TaskCardObj)',
    50,
    'manual'
);

SET @sample18_get_task_card_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample18_db_access_class_id
      AND functions.function_name = 'GetTaskCardList'
);

SET @sample18_get_task_card_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample18_db_access_class_id
      AND functions.function_name = 'GetTaskCard'
);

SET @sample18_insert_task_card_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample18_db_access_class_id
      AND functions.function_name = 'InsertTaskCard'
);

SET @sample18_update_task_card_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample18_db_access_class_id
      AND functions.function_name = 'UpdateTaskCard'
);

SET @sample18_complete_task_card_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample18_db_access_class_id
      AND functions.function_name = 'CompleteTaskCard'
);

INSERT INTO project_db_access_function_select_target_fields (
    db_access_function_id,
    target_table_name,
    target_table_alias_name,
    target_table_column_name,
    target_table_column_prefix,
    target_table_column_suffix,
    store_class_field_name,
    group_by_target,
    field_list_order,
    source_of_truth
) VALUES
(@sample18_get_task_card_list_function_id, 'task_card', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample18_get_task_card_list_function_id, 'task_card', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample18_get_task_card_list_function_id, 'task_card', '', 'status', '', '', 'status', 0, 30, 'manual'),
(@sample18_get_task_card_list_function_id, 'task_card', '', 'assigned_to', '', '', 'assignedTo', 0, 40, 'manual'),
(@sample18_get_task_card_list_function_id, 'task_card', '', 'priority', '', '', 'priority', 0, 50, 'manual'),
(@sample18_get_task_card_list_function_id, 'task_card', '', 'due_date', '', '', 'dueDate', 0, 60, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'body', '', '', 'body', 0, 30, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'status', '', '', 'status', 0, 40, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'assigned_to', '', '', 'assignedTo', 0, 50, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'priority', '', '', 'priority', 0, 60, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'due_date', '', '', 'dueDate', 0, 70, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'completed_at', '', '', 'completedAt', 0, 80, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'updated_at', '', '', 'updatedAt', 0, 90, 'manual');

INSERT INTO project_db_access_function_select_wheres (
    db_access_function_id,
    target_table_name,
    target_table_alias_name,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    another_table_name,
    another_table_alias_name,
    another_field_name,
    join_type,
    or_group,
    relational_operator,
    where_order,
    source_of_truth
) VALUES
(@sample18_get_task_card_list_function_id, 'task_card', '', 'status', 'argument', 'varchar', '', '', '', '', '', '', '=', 10, 'manual'),
(@sample18_get_task_card_function_id, 'task_card', '', 'id', 'argument', 'int', '', '', '', '', '', '', '=', 10, 'manual');

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample18_insert_task_card_function_id, 'title', 'argument', '', '', 10, 'manual'),
(@sample18_insert_task_card_function_id, 'body', 'argument', '', '', 20, 'manual'),
(@sample18_insert_task_card_function_id, 'status', 'argument', '', '', 30, 'manual'),
(@sample18_insert_task_card_function_id, 'assigned_to', 'argument', '', '', 40, 'manual'),
(@sample18_insert_task_card_function_id, 'priority', 'argument', '', '', 50, 'manual'),
(@sample18_insert_task_card_function_id, 'due_date', 'argument', '', '', 60, 'manual'),
(@sample18_insert_task_card_function_id, 'updated_at', 'argument', '', '', 70, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample18_update_task_card_function_id, 'title', 'argument', '', '', 10, 'manual'),
(@sample18_update_task_card_function_id, 'body', 'argument', '', '', 20, 'manual'),
(@sample18_update_task_card_function_id, 'status', 'argument', '', '', 30, 'manual'),
(@sample18_update_task_card_function_id, 'assigned_to', 'argument', '', '', 40, 'manual'),
(@sample18_update_task_card_function_id, 'priority', 'argument', '', '', 50, 'manual'),
(@sample18_update_task_card_function_id, 'due_date', 'argument', '', '', 60, 'manual'),
(@sample18_update_task_card_function_id, 'completed_at', 'argument', '', '', 70, 'manual'),
(@sample18_update_task_card_function_id, 'updated_at', 'argument', '', '', 80, 'manual'),
(@sample18_complete_task_card_function_id, 'status', 'fixed', '', 'done', 10, 'manual'),
(@sample18_complete_task_card_function_id, 'completed_at', 'argument', '', '', 20, 'manual'),
(@sample18_complete_task_card_function_id, 'updated_at', 'argument', '', '', 30, 'manual');

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
(@sample18_update_task_card_function_id, 'id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample18_complete_task_card_function_id, 'id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample18_project_id = NULL;
SET @sample18_db_access_class_id = NULL;
SET @sample18_get_task_card_list_function_id = NULL;
SET @sample18_get_task_card_function_id = NULL;
SET @sample18_insert_task_card_function_id = NULL;
SET @sample18_update_task_card_function_id = NULL;
SET @sample18_complete_task_card_function_id = NULL;
