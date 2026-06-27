SET @sample21_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE21'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample21_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample21_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample21_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample21_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample21_project_id;

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
    @sample21_project_id,
    'ebook_catalog_item',
    '',
    0,
    'Ebook catalog API sample with public list/detail functions over the catalog read model.',
    'manual',
    '',
    ''
);

SET @sample21_db_access_class_id = LAST_INSERT_ID();

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
    @sample21_db_access_class_id,
    'GetPublicEbookCatalogList',
    10,
    '',
    'SELECTLIST',
    'EbookCatalogItem',
    'ebook_catalog_item',
    '',
    1,
    'ebook_catalog_item.published_at desc, ebook_catalog_item.book_id desc',
    'List public ebook catalog items by author, genre, series, and title pattern.',
    'argument',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetPublicEbookCatalogList($param_EbookCatalogItem_AuthorSlug_where, $param_EbookCatalogItem_GenreSlug_where, $param_EbookCatalogItem_SeriesSlug_where, $param_EbookCatalogItem_BookTitle_where, $limit)',
    10,
    'manual'
),
(
    @sample21_db_access_class_id,
    'GetPublicEbookBook',
    20,
    '',
    'SELECTSINGLE',
    'EbookCatalogItem',
    'ebook_catalog_item',
    '',
    1,
    '',
    'Get one public ebook catalog item by book slug.',
    '',
    '',
    '',
    'NoSecurity',
    '',
    0,
    'public function GetPublicEbookBook($param_EbookCatalogItem_BookSlug_where)',
    20,
    'manual'
);

SET @sample21_catalog_list_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample21_db_access_class_id
      AND functions.function_name = 'GetPublicEbookCatalogList'
);

SET @sample21_book_detail_function_id = (
    SELECT functions.id
    FROM project_db_access_functions AS functions
    WHERE functions.db_access_class_id = @sample21_db_access_class_id
      AND functions.function_name = 'GetPublicEbookBook'
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
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'book_id', '', '', 'bookId', 0, 10, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'book_title', '', '', 'bookTitle', 0, 20, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'book_slug', '', '', 'bookSlug', 0, 30, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'series_name', '', '', 'seriesName', 0, 40, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'author_name', '', '', 'authorName', 0, 50, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'genre_name', '', '', 'genreName', 0, 60, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'published_at', '', '', 'publishedAt', 0, 70, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'summary', '', '', 'summary', 0, 80, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'epub_status', '', '', 'epubStatus', 0, 90, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'primary_epub_url', '', '', 'primaryEpubUrl', 0, 100, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'book_id', '', '', 'bookId', 0, 10, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'book_title', '', '', 'bookTitle', 0, 20, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'book_slug', '', '', 'bookSlug', 0, 30, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'series_name', '', '', 'seriesName', 0, 40, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'author_name', '', '', 'authorName', 0, 50, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'genre_name', '', '', 'genreName', 0, 60, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'published_at', '', '', 'publishedAt', 0, 70, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'summary', '', '', 'summary', 0, 80, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'epub_status', '', '', 'epubStatus', 0, 90, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'primary_epub_url', '', '', 'primaryEpubUrl', 0, 100, 'manual');

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
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'author_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'genre_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 30, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'series_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 40, 'manual'),
(@sample21_catalog_list_function_id, 'ebook_catalog_item', '', 'book_title', 'argument', 'varchar', '', '', '', '', '', '', 'LIKE', 50, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample21_book_detail_function_id, 'ebook_catalog_item', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual');

SET @sample21_project_id = NULL;
SET @sample21_db_access_class_id = NULL;
SET @sample21_catalog_list_function_id = NULL;
SET @sample21_book_detail_function_id = NULL;
