SET @sample19_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE19'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample19_project_id;

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
    @sample19_project_id,
    'ArticleJsonModel',
    '',
    0,
    'JSON-first content model sample. Nested JSON author/category is normalized and joined into a public summary DTO.',
    'manual',
    '',
    ''
);

SET @sample19_db_access_class_id = LAST_INSERT_ID();

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
    @sample19_db_access_class_id,
    'GetPublishedArticlePublicSummaryList',
    10,
    '',
    'SELECTLIST',
    'ArticlePublicSummary',
    'ArticleJsonModel',
    '',
    0,
    'ArticleJsonModel.PublishedAt desc, ArticleJsonModel.Id desc',
    'Public read model derived from the user JSON: published article rows joined with normalized author and category rows.',
    '',
    '',
    '',
    '',
    '',
    0,
    'public function GetPublishedArticlePublicSummaryList()',
    10,
    'manual'
);

SET @sample19_get_summary_list_function_id = LAST_INSERT_ID();

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
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'Id', '', '', 'ArticleId', 0, 10, 'manual'),
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'Title', '', '', 'ArticleTitle', 0, 20, 'manual'),
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'Slug', '', '', 'ArticleSlug', 0, 30, 'manual'),
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'PublishedAt', '', '', 'PublishedAt', 0, 40, 'manual'),
(@sample19_get_summary_list_function_id, 'JsonAuthor', '', 'Name', '', '', 'AuthorName', 0, 50, 'manual'),
(@sample19_get_summary_list_function_id, 'JsonCategory', '', 'Name', '', '', 'CategoryName', 0, 60, 'manual');

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
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'JsonAuthorId', 'anotherfield', '', '', 'JsonAuthor', '', 'Id', 'inner', '', '=', 10, 'manual'),
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'JsonCategoryId', 'anotherfield', '', '', 'JsonCategory', '', 'Id', 'inner', '', '=', 20, 'manual'),
(@sample19_get_summary_list_function_id, 'ArticleJsonModel', '', 'Status', 'fixed', '', 'published', '', '', '', '', '', '=', 30, 'manual');

SET @sample19_project_id = NULL;
SET @sample19_db_access_class_id = NULL;
SET @sample19_get_summary_list_function_id = NULL;
