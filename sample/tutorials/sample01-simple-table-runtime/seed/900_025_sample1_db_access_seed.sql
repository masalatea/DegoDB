SET @sample1_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE1'
);

DELETE classes
FROM project_db_access_classes AS classes
WHERE classes.project_id = @sample1_project_id
  AND classes.source_name = 'Article';

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
    @sample1_project_id,
    'Article',
    '',
    0,
    'Minimal CRUD DBAccess sample for a single imported Article table.',
    'manual',
    '',
    ''
)
ON DUPLICATE KEY UPDATE
    store_base_path = VALUES(store_base_path),
    is_autoload = VALUES(is_autoload),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth),
    last_detected_dbaccess_file = VALUES(last_detected_dbaccess_file),
    last_detected_data_file = VALUES(last_detected_data_file);

SET @sample1_db_access_class_id = (
    SELECT id
    FROM project_db_access_classes
    WHERE project_id = @sample1_project_id
      AND source_name = 'Article'
);

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
    @sample1_db_access_class_id,
    'GetArticleList',
    10,
    '',
    'SELECTLIST',
    'Article',
    'Article',
    '',
    0,
    'Article.Id',
    'List all articles.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function GetArticleList()',
    10,
    'manual'
),
(
    @sample1_db_access_class_id,
    'GetArticle',
    20,
    '',
    'SELECTSINGLE',
    'Article',
    'Article',
    '',
    0,
    '',
    'Get a single article by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function GetArticle($param_Article_Id_where)',
    20,
    'manual'
),
(
    @sample1_db_access_class_id,
    'InsertArticle',
    30,
    '',
    'INSERT',
    'Article',
    'Article',
    'classobject',
    0,
    '',
    'Insert a new article row.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function InsertArticle($ArticleObj)',
    30,
    'manual'
),
(
    @sample1_db_access_class_id,
    'UpdateArticle',
    40,
    '',
    'UPDATE',
    'Article',
    'Article',
    'classobject',
    0,
    '',
    'Update an article row by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function UpdateArticle($ArticleObj)',
    40,
    'manual'
),
(
    @sample1_db_access_class_id,
    'DeleteArticle',
    50,
    '',
    'DELETE',
    'Article',
    'Article',
    'classobject',
    0,
    '',
    'Delete an article row by primary key.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function DeleteArticle($ArticleObj)',
    50,
    'manual'
)
ON DUPLICATE KEY UPDATE
    function_list_order = VALUES(function_list_order),
    function_suffix = VALUES(function_suffix),
    action_type = VALUES(action_type),
    data_class_base_name = VALUES(data_class_base_name),
    target_table_name = VALUES(target_table_name),
    parameter_type = VALUES(parameter_type),
    select_by_distinct = VALUES(select_by_distinct),
    sort_order_columns = VALUES(sort_order_columns),
    memo = VALUES(memo),
    limit_parameter_type = VALUES(limit_parameter_type),
    limit_fixed_parameter = VALUES(limit_fixed_parameter),
    or_group_type = VALUES(or_group_type),
    single_proxy_auth_type = VALUES(single_proxy_auth_type),
    single_proxy_single_get_function_name = VALUES(single_proxy_single_get_function_name),
    is_blob_target = VALUES(is_blob_target),
    detected_signature = VALUES(detected_signature),
    detected_line = VALUES(detected_line),
    source_of_truth = VALUES(source_of_truth);

SET @sample1_get_article_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample1_db_access_class_id
      AND functions.function_name = 'GetArticleList'
);

SET @sample1_get_article_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample1_db_access_class_id
      AND functions.function_name = 'GetArticle'
);

SET @sample1_insert_article_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample1_db_access_class_id
      AND functions.function_name = 'InsertArticle'
);

SET @sample1_update_article_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample1_db_access_class_id
      AND functions.function_name = 'UpdateArticle'
);

SET @sample1_delete_article_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample1_db_access_class_id
      AND functions.function_name = 'DeleteArticle'
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
(@sample1_get_article_list_function_id, 'Article', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample1_get_article_list_function_id, 'Article', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample1_get_article_list_function_id, 'Article', '', 'Body', '', '', 'Body', 0, 30, 'manual'),
(@sample1_get_article_function_id, 'Article', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample1_get_article_function_id, 'Article', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample1_get_article_function_id, 'Article', '', 'Body', '', '', 'Body', 0, 30, 'manual')
ON DUPLICATE KEY UPDATE
    target_table_name = VALUES(target_table_name),
    target_table_alias_name = VALUES(target_table_alias_name),
    target_table_column_name = VALUES(target_table_column_name),
    target_table_column_prefix = VALUES(target_table_column_prefix),
    target_table_column_suffix = VALUES(target_table_column_suffix),
    store_class_field_name = VALUES(store_class_field_name),
    group_by_target = VALUES(group_by_target),
    field_list_order = VALUES(field_list_order),
    source_of_truth = VALUES(source_of_truth);

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
(@sample1_get_article_function_id, 'Article', '', 'Id', 'argument', '', '', '', '', '', '', '', '=', 10, 'manual')
ON DUPLICATE KEY UPDATE
    target_table_name = VALUES(target_table_name),
    target_table_alias_name = VALUES(target_table_alias_name),
    target_table_column_name = VALUES(target_table_column_name),
    parameter_type = VALUES(parameter_type),
    parameter_data_type = VALUES(parameter_data_type),
    fixed_parameter = VALUES(fixed_parameter),
    another_table_name = VALUES(another_table_name),
    another_table_alias_name = VALUES(another_table_alias_name),
    another_field_name = VALUES(another_field_name),
    join_type = VALUES(join_type),
    or_group = VALUES(or_group),
    relational_operator = VALUES(relational_operator),
    where_order = VALUES(where_order),
    source_of_truth = VALUES(source_of_truth);

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample1_insert_article_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample1_insert_article_function_id, 'Body', 'argument', '', '', 20, 'manual')
ON DUPLICATE KEY UPDATE
    target_table_column_name = VALUES(target_table_column_name),
    parameter_type = VALUES(parameter_type),
    parameter_data_type = VALUES(parameter_data_type),
    fixed_parameter = VALUES(fixed_parameter),
    field_list_order = VALUES(field_list_order),
    source_of_truth = VALUES(source_of_truth);

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample1_update_article_function_id, 'Title', 'argument', '', '', 10, 'manual'),
(@sample1_update_article_function_id, 'Body', 'argument', '', '', 20, 'manual')
ON DUPLICATE KEY UPDATE
    target_table_column_name = VALUES(target_table_column_name),
    parameter_type = VALUES(parameter_type),
    parameter_data_type = VALUES(parameter_data_type),
    fixed_parameter = VALUES(fixed_parameter),
    field_list_order = VALUES(field_list_order),
    source_of_truth = VALUES(source_of_truth);

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
(@sample1_update_article_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample1_delete_article_function_id, 'Id', 'argument', '', '', '', '=', 10, 'manual')
ON DUPLICATE KEY UPDATE
    target_table_column_name = VALUES(target_table_column_name),
    parameter_type = VALUES(parameter_type),
    parameter_data_type = VALUES(parameter_data_type),
    fixed_parameter = VALUES(fixed_parameter),
    or_group = VALUES(or_group),
    relational_operator = VALUES(relational_operator),
    where_order = VALUES(where_order),
    source_of_truth = VALUES(source_of_truth);

SET @sample1_get_article_list_function_id = NULL;
SET @sample1_get_article_function_id = NULL;
SET @sample1_insert_article_function_id = NULL;
SET @sample1_update_article_function_id = NULL;
SET @sample1_delete_article_function_id = NULL;
SET @sample1_db_access_class_id = NULL;
SET @sample1_project_id = NULL;
