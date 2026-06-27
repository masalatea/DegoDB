SET @sample20_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE20'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample20_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample20_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample20_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample20_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample20_project_id;

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
    @sample20_project_id,
    'content_article',
    '',
    0,
    'Content publishing sample with public list/detail functions for published articles.',
    'manual',
    '',
    ''
);

SET @sample20_db_access_class_id = LAST_INSERT_ID();

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
    @sample20_db_access_class_id,
    'GetPublishedContentArticleList',
    10,
    '',
    'SELECTLIST',
    'ContentArticle',
    'content_article',
    '',
    0,
    'content_article.published_at desc, content_article.id desc',
    'List published articles for the public content site.',
    'argument',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetPublishedContentArticleList($limit)',
    10,
    'manual'
),
(
    @sample20_db_access_class_id,
    'GetPublishedContentArticle',
    20,
    '',
    'SELECTSINGLE',
    'ContentArticle',
    'content_article',
    '',
    0,
    '',
    'Get a single published article by slug for the public content site.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetPublishedContentArticle($param_ContentArticle_Slug_where)',
    20,
    'manual'
);

SET @sample20_get_article_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample20_db_access_class_id
      AND functions.function_name = 'GetPublishedContentArticleList'
);

SET @sample20_get_article_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample20_db_access_class_id
      AND functions.function_name = 'GetPublishedContentArticle'
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
(@sample20_get_article_list_function_id, 'content_article', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample20_get_article_list_function_id, 'content_article', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample20_get_article_list_function_id, 'content_article', '', 'slug', '', '', 'slug', 0, 30, 'manual'),
(@sample20_get_article_list_function_id, 'content_article', '', 'category_name', '', '', 'categoryName', 0, 40, 'manual'),
(@sample20_get_article_list_function_id, 'content_article', '', 'author_name', '', '', 'authorName', 0, 50, 'manual'),
(@sample20_get_article_list_function_id, 'content_article', '', 'published_at', '', '', 'publishedAt', 0, 60, 'manual'),
(@sample20_get_article_list_function_id, 'content_article', '', 'summary', '', '', 'summary', 0, 70, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'slug', '', '', 'slug', 0, 30, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'category_name', '', '', 'categoryName', 0, 40, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'author_name', '', '', 'authorName', 0, 50, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'published_at', '', '', 'publishedAt', 0, 60, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'summary', '', '', 'summary', 0, 70, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'body', '', '', 'body', 0, 80, 'manual');

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
(@sample20_get_article_list_function_id, 'content_article', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample20_get_article_function_id, 'content_article', '', 'slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual');

SET @sample20_project_id = NULL;
SET @sample20_db_access_class_id = NULL;
SET @sample20_get_article_list_function_id = NULL;
SET @sample20_get_article_function_id = NULL;
