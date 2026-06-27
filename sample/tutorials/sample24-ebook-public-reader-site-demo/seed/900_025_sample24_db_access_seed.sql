SET @sample24_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE24'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample24_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample24_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample24_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample24_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample24_project_id;

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
    @sample24_project_id,
    'ebook_reader_book',
    '',
    0,
    'Public ebook reader site sample with read-only book, chapter, and EPUB delivery endpoints.',
    'manual',
    '',
    ''
);

SET @sample24_db_access_class_id = LAST_INSERT_ID();

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
(@sample24_db_access_class_id, 'GetPublicEbookReaderBookList', 10, '', 'SELECTLIST', 'EbookReaderBook', 'ebook_reader_book', '', 0, 'ebook_reader_book.published_at desc, ebook_reader_book.id desc', 'List published books for the reader top/list page.', 'argument', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookReaderBookList($limit)', 10, 'manual'),
(@sample24_db_access_class_id, 'GetPublicEbookReaderBook', 20, '', 'SELECTSINGLE', 'EbookReaderBook', 'ebook_reader_book', '', 0, '', 'Get one published book detail by slug.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookReaderBook($param_EbookReaderBook_Slug_where)', 20, 'manual'),
(@sample24_db_access_class_id, 'GetPublicEbookReaderChapterList', 30, '', 'SELECTLIST', 'EbookReaderChapter', 'ebook_reader_chapter', '', 0, 'ebook_reader_chapter.spine_order asc, ebook_reader_chapter.id asc', 'List published chapters for a book.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookReaderChapterList($param_EbookReaderChapter_BookSlug_where)', 30, 'manual'),
(@sample24_db_access_class_id, 'GetPublicEbookReaderChapter', 40, '', 'SELECTSINGLE', 'EbookReaderChapter', 'ebook_reader_chapter', '', 0, '', 'Get one published chapter by book slug and chapter slug.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookReaderChapter($param_EbookReaderChapter_BookSlug_where, $param_EbookReaderChapter_ChapterSlug_where)', 40, 'manual'),
(@sample24_db_access_class_id, 'GetPublicEbookReaderMediaDeliveryList', 50, '', 'SELECTLIST', 'EbookReaderMediaDelivery', 'ebook_reader_media_delivery', '', 0, 'ebook_reader_media_delivery.id asc', 'List published EPUB delivery metadata for a book.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookReaderMediaDeliveryList($param_EbookReaderMediaDelivery_BookSlug_where)', 50, 'manual');

SET @sample24_book_list_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample24_db_access_class_id AND function_name = 'GetPublicEbookReaderBookList');
SET @sample24_book_detail_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample24_db_access_class_id AND function_name = 'GetPublicEbookReaderBook');
SET @sample24_chapter_list_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample24_db_access_class_id AND function_name = 'GetPublicEbookReaderChapterList');
SET @sample24_chapter_detail_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample24_db_access_class_id AND function_name = 'GetPublicEbookReaderChapter');
SET @sample24_media_list_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample24_db_access_class_id AND function_name = 'GetPublicEbookReaderMediaDeliveryList');

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
(@sample24_book_list_id, 'ebook_reader_book', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample24_book_list_id, 'ebook_reader_book', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample24_book_list_id, 'ebook_reader_book', '', 'slug', '', '', 'slug', 0, 30, 'manual'),
(@sample24_book_list_id, 'ebook_reader_book', '', 'author_name', '', '', 'authorName', 0, 40, 'manual'),
(@sample24_book_list_id, 'ebook_reader_book', '', 'genre_name', '', '', 'genreName', 0, 50, 'manual'),
(@sample24_book_list_id, 'ebook_reader_book', '', 'summary', '', '', 'summary', 0, 60, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'title', '', '', 'title', 0, 20, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'slug', '', '', 'slug', 0, 30, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'author_name', '', '', 'authorName', 0, 40, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'genre_name', '', '', 'genreName', 0, 50, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'summary', '', '', 'summary', 0, 60, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'book_slug', '', '', 'bookSlug', 0, 20, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'chapter_title', '', '', 'chapterTitle', 0, 30, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'chapter_slug', '', '', 'chapterSlug', 0, 40, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'spine_order', '', '', 'spineOrder', 0, 50, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'book_slug', '', '', 'bookSlug', 0, 20, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'chapter_title', '', '', 'chapterTitle', 0, 30, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'chapter_slug', '', '', 'chapterSlug', 0, 40, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'spine_order', '', '', 'spineOrder', 0, 50, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'body_markdown', '', '', 'bodyMarkdown', 0, 60, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'book_slug', '', '', 'bookSlug', 0, 20, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'asset_slug', '', '', 'assetSlug', 0, 30, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'asset_kind', '', '', 'assetKind', 0, 40, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'display_name', '', '', 'displayName', 0, 50, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'public_url', '', '', 'publicUrl', 0, 60, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'mime_type', '', '', 'mimeType', 0, 70, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'file_size_bytes', '', '', 'fileSizeBytes', 0, 80, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'sha256', '', '', 'sha256', 0, 90, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'version_label', '', '', 'versionLabel', 0, 100, 'manual');

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
(@sample24_book_list_id, 'ebook_reader_book', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample24_book_detail_id, 'ebook_reader_book', '', 'slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample24_chapter_list_id, 'ebook_reader_chapter', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample24_chapter_detail_id, 'ebook_reader_chapter', '', 'chapter_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 30, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample24_media_list_id, 'ebook_reader_media_delivery', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual');

SET @sample24_project_id = NULL;
SET @sample24_db_access_class_id = NULL;
SET @sample24_book_list_id = NULL;
SET @sample24_book_detail_id = NULL;
SET @sample24_chapter_list_id = NULL;
SET @sample24_chapter_detail_id = NULL;
SET @sample24_media_list_id = NULL;
