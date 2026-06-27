SET @sample17_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE17'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample17_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample17_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample17_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample17_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample17_project_id;

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
    @sample17_project_id,
    'capstone_task',
    '',
    0,
    'Multi-output capstone sample using two read functions for one CapstoneTask table.',
    'manual',
    '',
    ''
);

SET @sample17_db_access_class_id = LAST_INSERT_ID();

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
    @sample17_db_access_class_id,
    'GetCapstoneTaskList',
    10,
    '',
    'SELECTLIST',
    'CapstoneTask',
    'capstone_task',
    '',
    0,
    'capstone_task.priority desc, capstone_task.id asc',
    'List capstone tasks by status for multi-output tutorial.',
    'argument',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetCapstoneTaskList($param_CapstoneTask_Status_where, $limit)',
    10,
    'manual'
),
(
    @sample17_db_access_class_id,
    'GetCapstoneTask',
    20,
    '',
    'SELECTSINGLE',
    'CapstoneTask',
    'capstone_task',
    '',
    0,
    '',
    'Get a single capstone task by primary key for multi-output tutorial.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetCapstoneTask($param_CapstoneTask_Id_where)',
    20,
    'manual'
);

SET @sample17_get_task_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample17_db_access_class_id
      AND functions.function_name = 'GetCapstoneTaskList'
);

SET @sample17_get_task_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample17_db_access_class_id
      AND functions.function_name = 'GetCapstoneTask'
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
(@sample17_get_task_list_function_id, 'capstone_task', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample17_get_task_list_function_id, 'capstone_task', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample17_get_task_list_function_id, 'capstone_task', '', 'status', '', '', 'status', 0, 30, 'manual'),
(@sample17_get_task_list_function_id, 'capstone_task', '', 'owner_name', '', '', 'ownerName', 0, 40, 'manual'),
(@sample17_get_task_list_function_id, 'capstone_task', '', 'priority', '', '', 'priority', 0, 50, 'manual'),
(@sample17_get_task_list_function_id, 'capstone_task', '', 'due_date', '', '', 'dueDate', 0, 60, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'status', '', '', 'status', 0, 30, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'owner_name', '', '', 'ownerName', 0, 40, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'priority', '', '', 'priority', 0, 50, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'due_date', '', '', 'dueDate', 0, 60, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'updated_at', '', '', 'updatedAt', 0, 70, 'manual');

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
(@sample17_get_task_list_function_id, 'capstone_task', '', 'status', 'argument', 'varchar', '', '', '', '', '', '', '=', 10, 'manual'),
(@sample17_get_task_function_id, 'capstone_task', '', 'id', 'argument', 'int', '', '', '', '', '', '', '=', 10, 'manual');

SET @sample17_project_id = NULL;
SET @sample17_db_access_class_id = NULL;
SET @sample17_get_task_list_function_id = NULL;
SET @sample17_get_task_function_id = NULL;
