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
    'TaskCard',
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
    'TaskCard',
    '',
    0,
    'TaskCard.DueDate asc, TaskCard.Priority desc, TaskCard.Id asc',
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
    'TaskCard',
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
    'TaskCard',
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
    'TaskCard',
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
    'TaskCard',
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
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'Status', '', '', 'Status', 0, 30, 'manual'),
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'AssignedTo', '', '', 'AssignedTo', 0, 40, 'manual'),
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'Priority', '', '', 'Priority', 0, 50, 'manual'),
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'DueDate', '', '', 'DueDate', 0, 60, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'Body', '', '', 'Body', 0, 30, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'Status', '', '', 'Status', 0, 40, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'AssignedTo', '', '', 'AssignedTo', 0, 50, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'Priority', '', '', 'Priority', 0, 60, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'DueDate', '', '', 'DueDate', 0, 70, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'CompletedAt', '', '', 'CompletedAt', 0, 80, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'UpdatedAt', '', '', 'UpdatedAt', 0, 90, 'manual');

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
(@sample18_get_task_card_list_function_id, 'TaskCard', '', 'Status', 'argument', 'varchar', '', '', '', '', '', '', '=', 10, 'manual'),
(@sample18_get_task_card_function_id, 'TaskCard', '', 'Id', 'argument', 'int', '', '', '', '', '', '', '=', 10, 'manual');

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample18_insert_task_card_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample18_insert_task_card_function_id, 'Body', 'argument', '', '', 20, 'manual'),
(@sample18_insert_task_card_function_id, 'Status', 'argument', '', '', 30, 'manual'),
(@sample18_insert_task_card_function_id, 'AssignedTo', 'argument', '', '', 40, 'manual'),
(@sample18_insert_task_card_function_id, 'Priority', 'argument', '', '', 50, 'manual'),
(@sample18_insert_task_card_function_id, 'DueDate', 'argument', '', '', 60, 'manual'),
(@sample18_insert_task_card_function_id, 'UpdatedAt', 'argument', '', '', 70, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample18_update_task_card_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample18_update_task_card_function_id, 'Body', 'argument', '', '', 20, 'manual'),
(@sample18_update_task_card_function_id, 'Status', 'argument', '', '', 30, 'manual'),
(@sample18_update_task_card_function_id, 'AssignedTo', 'argument', '', '', 40, 'manual'),
(@sample18_update_task_card_function_id, 'Priority', 'argument', '', '', 50, 'manual'),
(@sample18_update_task_card_function_id, 'DueDate', 'argument', '', '', 60, 'manual'),
(@sample18_update_task_card_function_id, 'CompletedAt', 'argument', '', '', 70, 'manual'),
(@sample18_update_task_card_function_id, 'UpdatedAt', 'argument', '', '', 80, 'manual'),
(@sample18_complete_task_card_function_id, 'Status', 'fixed', '', 'done', 10, 'manual'),
(@sample18_complete_task_card_function_id, 'CompletedAt', 'argument', '', '', 20, 'manual'),
(@sample18_complete_task_card_function_id, 'UpdatedAt', 'argument', '', '', 30, 'manual');

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
(@sample18_update_task_card_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample18_complete_task_card_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample18_project_id = NULL;
SET @sample18_db_access_class_id = NULL;
SET @sample18_get_task_card_list_function_id = NULL;
SET @sample18_get_task_card_function_id = NULL;
SET @sample18_insert_task_card_function_id = NULL;
SET @sample18_update_task_card_function_id = NULL;
SET @sample18_complete_task_card_function_id = NULL;

