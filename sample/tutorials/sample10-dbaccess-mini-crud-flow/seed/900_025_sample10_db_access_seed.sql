SET @sample10_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE10'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample10_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample10_project_id;

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
    @sample10_project_id,
    'SupportTicket',
    '',
    0,
    'Mini CRUD flow DBAccess sample for a single imported SupportTicket table.',
    'manual',
    '',
    ''
);

SET @sample10_db_access_class_id = LAST_INSERT_ID();

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
    @sample10_db_access_class_id,
    'GetSupportTicketList',
    10,
    '',
    'SELECTLIST',
    'SupportTicket',
    'SupportTicket',
    '',
    0,
    'SupportTicket.UpdatedAt desc, SupportTicket.Id desc',
    'List support tickets by status with newest updates first.',
    'argument',
    '',
    '',
    '',
    '',
    0,
    'public function GetSupportTicketList($param_SupportTicket_Status_where, $limit)',
    10,
    'manual'
),
(
    @sample10_db_access_class_id,
    'GetSupportTicket',
    20,
    '',
    'SELECTSINGLE',
    'SupportTicket',
    'SupportTicket',
    '',
    0,
    '',
    'Get a single support ticket by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function GetSupportTicket($param_SupportTicket_Id_where)',
    20,
    'manual'
),
(
    @sample10_db_access_class_id,
    'InsertSupportTicket',
    30,
    '',
    'INSERT',
    'SupportTicket',
    'SupportTicket',
    'classobject',
    0,
    '',
    'Insert a new support ticket row.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function InsertSupportTicket($SupportTicketObj)',
    30,
    'manual'
),
(
    @sample10_db_access_class_id,
    'UpdateSupportTicket',
    40,
    '',
    'UPDATE',
    'SupportTicket',
    'SupportTicket',
    'classobject',
    0,
    '',
    'Update a support ticket row by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function UpdateSupportTicket($SupportTicketObj)',
    40,
    'manual'
),
(
    @sample10_db_access_class_id,
    'DeleteSupportTicket',
    50,
    '',
    'DELETE',
    'SupportTicket',
    'SupportTicket',
    'classobject',
    0,
    '',
    'Delete a support ticket row by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function DeleteSupportTicket($SupportTicketObj)',
    50,
    'manual'
);

SET @sample10_get_support_ticket_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample10_db_access_class_id
      AND functions.function_name = 'GetSupportTicketList'
);

SET @sample10_get_support_ticket_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample10_db_access_class_id
      AND functions.function_name = 'GetSupportTicket'
);

SET @sample10_insert_support_ticket_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample10_db_access_class_id
      AND functions.function_name = 'InsertSupportTicket'
);

SET @sample10_update_support_ticket_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample10_db_access_class_id
      AND functions.function_name = 'UpdateSupportTicket'
);

SET @sample10_delete_support_ticket_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample10_db_access_class_id
      AND functions.function_name = 'DeleteSupportTicket'
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
(@sample10_get_support_ticket_list_function_id, 'SupportTicket', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample10_get_support_ticket_list_function_id, 'SupportTicket', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample10_get_support_ticket_list_function_id, 'SupportTicket', '', 'Status', '', '', 'Status', 0, 30, 'manual'),
(@sample10_get_support_ticket_list_function_id, 'SupportTicket', '', 'AssignedTo', '', '', 'AssignedTo', 0, 40, 'manual'),
(@sample10_get_support_ticket_list_function_id, 'SupportTicket', '', 'UpdatedAt', '', '', 'UpdatedAt', 0, 50, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'Status', '', '', 'Status', 0, 30, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'AssignedTo', '', '', 'AssignedTo', 0, 40, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'Body', '', '', 'Body', 0, 50, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'UpdatedAt', '', '', 'UpdatedAt', 0, 60, 'manual');

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
(@sample10_get_support_ticket_list_function_id, 'SupportTicket', '', 'Status', 'argument', '', '', '', '', '', '', '', '=', 10, 'manual'),
(@sample10_get_support_ticket_function_id, 'SupportTicket', '', 'Id', 'argument', '', '', '', '', '', '', '', '=', 10, 'manual');

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample10_insert_support_ticket_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample10_insert_support_ticket_function_id, 'Status', 'argument', '', '', 20, 'manual'),
(@sample10_insert_support_ticket_function_id, 'AssignedTo', 'argument', '', '', 30, 'manual'),
(@sample10_insert_support_ticket_function_id, 'Body', 'argument', '', '', 40, 'manual'),
(@sample10_insert_support_ticket_function_id, 'UpdatedAt', 'argument', '', '', 50, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample10_update_support_ticket_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample10_update_support_ticket_function_id, 'Status', 'argument', '', '', 20, 'manual'),
(@sample10_update_support_ticket_function_id, 'AssignedTo', 'argument', '', '', 30, 'manual'),
(@sample10_update_support_ticket_function_id, 'Body', 'argument', '', '', 40, 'manual'),
(@sample10_update_support_ticket_function_id, 'UpdatedAt', 'argument', '', '', 50, 'manual');

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
(@sample10_update_support_ticket_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample10_delete_support_ticket_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample10_project_id = NULL;
SET @sample10_db_access_class_id = NULL;
SET @sample10_get_support_ticket_list_function_id = NULL;
SET @sample10_get_support_ticket_function_id = NULL;
SET @sample10_insert_support_ticket_function_id = NULL;
SET @sample10_update_support_ticket_function_id = NULL;
SET @sample10_delete_support_ticket_function_id = NULL;
