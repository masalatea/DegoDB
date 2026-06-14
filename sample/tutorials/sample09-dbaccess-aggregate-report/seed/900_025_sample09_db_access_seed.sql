SET @sample09_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE09'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample09_project_id;

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
    @sample09_project_id,
    'SalesRecord',
    '',
    0,
    'Aggregate report DBAccess sample for SalesRecord + SalesCategory tables.',
    'manual',
    '',
    ''
);

SET @sample09_db_access_class_id = LAST_INSERT_ID();

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
    @sample09_db_access_class_id,
    'GetClosedSalesCategoryReportList',
    10,
    '',
    'SELECTLIST',
    'SalesCategoryReport',
    'SalesRecord',
    '',
    0,
    'sum(SalesRecord.Amount) desc, SalesRecord.SalesCategoryId asc',
    'Join SalesRecord and SalesCategory rows into a grouped sales report DTO with count, sum, and having filters.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function GetClosedSalesCategoryReportList()',
    10,
    'manual'
);

SET @sample09_get_report_list_function_id = LAST_INSERT_ID();

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
(@sample09_get_report_list_function_id, 'SalesRecord', '', 'SalesCategoryId', '', '', 'SalesCategoryId', 1, 10, 'manual'),
(@sample09_get_report_list_function_id, 'SalesCategory', '', 'Name', '', '', 'SalesCategoryName', 1, 20, 'manual'),
(@sample09_get_report_list_function_id, 'SalesRecord', '', 'Id', 'count(', ')', 'ClosedSaleCount', 0, 30, 'manual'),
(@sample09_get_report_list_function_id, 'SalesRecord', '', 'Amount', 'sum(', ')', 'ClosedSaleTotalAmount', 0, 40, 'manual');

SET @sample09_sales_category_id_field_id = (
    SELECT id
    FROM project_db_access_function_select_target_fields
    WHERE db_access_function_id = @sample09_get_report_list_function_id
      AND store_class_field_name = 'SalesCategoryId'
);

SET @sample09_sales_category_name_field_id = (
    SELECT id
    FROM project_db_access_function_select_target_fields
    WHERE db_access_function_id = @sample09_get_report_list_function_id
      AND store_class_field_name = 'SalesCategoryName'
);

SET @sample09_closed_sale_count_field_id = (
    SELECT id
    FROM project_db_access_function_select_target_fields
    WHERE db_access_function_id = @sample09_get_report_list_function_id
      AND store_class_field_name = 'ClosedSaleCount'
);

SET @sample09_closed_sale_total_amount_field_id = (
    SELECT id
    FROM project_db_access_function_select_target_fields
    WHERE db_access_function_id = @sample09_get_report_list_function_id
      AND store_class_field_name = 'ClosedSaleTotalAmount'
);

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
(@sample09_get_report_list_function_id, 'SalesRecord', '', 'SalesCategoryId', 'anotherfield', '', '', 'SalesCategory', '', 'Id', 'inner', '', '=', 10, 'manual'),
(@sample09_get_report_list_function_id, 'SalesRecord', '', 'Status', 'fixed', '', 'closed', '', '', '', '', '', '=', 20, 'manual'),
(@sample09_get_report_list_function_id, 'SalesCategory', '', 'IsActive', 'fixed', 'raw', '1', '', '', '', '', '', '=', 30, 'manual');

INSERT INTO project_db_access_function_select_havings (
    db_access_function_id,
    left_target_prefix,
    left_target_field_id,
    left_target_suffix,
    relational_operator,
    right_target_prefix,
    right_parameter_type,
    right_parameter_data_type,
    right_fixed_parameter,
    right_target_field_id,
    right_target_suffix,
    having_order,
    source_of_truth
) VALUES
(@sample09_get_report_list_function_id, '', @sample09_closed_sale_count_field_id, '', '>=', '', 'fixed', 'raw', '2', 0, '', 10, 'manual'),
(@sample09_get_report_list_function_id, '', @sample09_closed_sale_total_amount_field_id, '', '>=', '', 'fixed', 'raw', '100', 0, '', 20, 'manual');

SET @sample09_project_id = NULL;
SET @sample09_db_access_class_id = NULL;
SET @sample09_get_report_list_function_id = NULL;
SET @sample09_sales_category_id_field_id = NULL;
SET @sample09_sales_category_name_field_id = NULL;
SET @sample09_closed_sale_count_field_id = NULL;
SET @sample09_closed_sale_total_amount_field_id = NULL;
