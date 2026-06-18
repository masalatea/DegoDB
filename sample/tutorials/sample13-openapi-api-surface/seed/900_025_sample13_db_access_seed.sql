SET @sample13_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE13'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample13_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample13_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample13_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample13_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample13_project_id;

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
    @sample13_project_id,
    'ApiTask',
    '',
    0,
    'OpenAPI API surface sample using two read functions for one ApiTask table.',
    'manual',
    '',
    ''
);

SET @sample13_db_access_class_id = LAST_INSERT_ID();

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
    @sample13_db_access_class_id,
    'GetApiTaskList',
    10,
    '',
    'SELECTLIST',
    'ApiTask',
    'ApiTask',
    '',
    0,
    'ApiTask.DueDate asc, ApiTask.Id asc',
    'List API tasks by status for OpenAPI surface tutorial.',
    'argument',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetApiTaskList($param_ApiTask_Status_where, $limit)',
    10,
    'manual'
),
(
    @sample13_db_access_class_id,
    'GetApiTask',
    20,
    '',
    'SELECTSINGLE',
    'ApiTask',
    'ApiTask',
    '',
    0,
    '',
    'Get a single API task by primary key for OpenAPI surface tutorial.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetApiTask($param_ApiTask_Id_where)',
    20,
    'manual'
);

SET @sample13_get_api_task_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample13_db_access_class_id
      AND functions.function_name = 'GetApiTaskList'
);

SET @sample13_get_api_task_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample13_db_access_class_id
      AND functions.function_name = 'GetApiTask'
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
(@sample13_get_api_task_list_function_id, 'ApiTask', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample13_get_api_task_list_function_id, 'ApiTask', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample13_get_api_task_list_function_id, 'ApiTask', '', 'Status', '', '', 'Status', 0, 30, 'manual'),
(@sample13_get_api_task_list_function_id, 'ApiTask', '', 'OwnerName', '', '', 'OwnerName', 0, 40, 'manual'),
(@sample13_get_api_task_list_function_id, 'ApiTask', '', 'DueDate', '', '', 'DueDate', 0, 50, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'Status', '', '', 'Status', 0, 30, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'OwnerName', '', '', 'OwnerName', 0, 40, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'DueDate', '', '', 'DueDate', 0, 50, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'UpdatedAt', '', '', 'UpdatedAt', 0, 60, 'manual');

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
(@sample13_get_api_task_list_function_id, 'ApiTask', '', 'Status', 'argument', 'varchar', '', '', '', '', '', '', '=', 10, 'manual'),
(@sample13_get_api_task_function_id, 'ApiTask', '', 'Id', 'argument', 'int', '', '', '', '', '', '', '=', 10, 'manual');

SET @sample13_project_id = NULL;
SET @sample13_db_access_class_id = NULL;
SET @sample13_get_api_task_list_function_id = NULL;
SET @sample13_get_api_task_function_id = NULL;
