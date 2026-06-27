SET @sample05_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE05'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample05_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample05_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample05_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample05_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample05_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample05_project_id;

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
    @sample05_project_id,
    'notice',
    '',
    0,
    'Minimal select-only DBAccess sample for a single imported Notice table.',
    'manual',
    '',
    ''
);

SET @sample05_db_access_class_id = LAST_INSERT_ID();

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
) VALUES (
    @sample05_db_access_class_id,
    'GetNoticeList',
    10,
    '',
    'SELECTLIST',
    'Notice',
    'notice',
    '',
    0,
    'notice.sort_order, notice.id',
    'List notices in configured display order.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function GetNoticeList()',
    10,
    'manual'
);

SET @sample05_get_notice_list_function_id = LAST_INSERT_ID();

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
(@sample05_get_notice_list_function_id, 'notice', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample05_get_notice_list_function_id, 'notice', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample05_get_notice_list_function_id, 'notice', '', 'body', '', '', 'body', 0, 30, 'manual'),
(@sample05_get_notice_list_function_id, 'notice', '', 'sort_order', '', '', 'sortOrder', 0, 40, 'manual');

SET @sample05_project_id = NULL;
SET @sample05_db_access_class_id = NULL;
SET @sample05_get_notice_list_function_id = NULL;
