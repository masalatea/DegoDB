SET @sample14_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE14'
);

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample14_project_id
  AND classes.source_name = 'sample14_transaction_item';

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample14_project_id
  AND classes.source_name = 'sample14_transaction_item';

DELETE FROM project_db_access_classes
WHERE project_id = @sample14_project_id
  AND source_name = 'sample14_transaction_item';

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
    @sample14_project_id,
    'sample14_transaction_item',
    '',
    0,
    'DBAccess used by the Sample14 Transaction Full custom proxy.',
    'manual',
    '',
    ''
);

SET @sample14_db_access_class_id = LAST_INSERT_ID();

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
    @sample14_db_access_class_id,
    'InsertSample14TransactionItem',
    10,
    '',
    'INSERT',
    'Sample14TransactionItem',
    'sample14_transaction_item',
    'classobject',
    0,
    '',
    'Insert one row as a step in the Sample14 Transaction Full proxy.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function InsertSample14TransactionItem($Sample14TransactionItemObj)',
    10,
    'manual'
);

SET @sample14_insert_function_id = LAST_INSERT_ID();

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample14_insert_function_id, 'transaction_key', 'argument', '', '', 10, 'manual'),
(@sample14_insert_function_id, 'step_name', 'argument', '', '', 20, 'manual');

SET @sample14_project_id = NULL;
SET @sample14_db_access_class_id = NULL;
SET @sample14_insert_function_id = NULL;
