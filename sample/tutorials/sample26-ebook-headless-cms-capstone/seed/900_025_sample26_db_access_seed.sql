SET @sample26_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE26'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample26_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample26_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample26_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample26_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample26_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample26_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample26_project_id;

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
    @sample26_project_id,
    'EbookCmsBook',
    '',
    0,
    'Capstone DBAccess class that exposes public reader/app APIs and ProjectToken editor chapter APIs.',
    'manual',
    '',
    ''
);

SET @sample26_db_access_class_id = LAST_INSERT_ID();

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
(@sample26_db_access_class_id, 'GetPublicEbookCmsBookList', 10, '', 'SELECTLIST', 'EbookCmsBook', 'EbookCmsBook', '', 0, 'EbookCmsBook.PublishedAt desc, EbookCmsBook.Id desc', 'List published books for site and app clients.', 'argument', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookCmsBookList($limit)', 10, 'manual'),
(@sample26_db_access_class_id, 'GetPublicEbookCmsBook', 20, '', 'SELECTSINGLE', 'EbookCmsBook', 'EbookCmsBook', '', 0, '', 'Get one published book detail by slug.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookCmsBook($param_EbookCmsBook_Slug_where)', 20, 'manual'),
(@sample26_db_access_class_id, 'GetPublicEbookCmsChapterList', 30, '', 'SELECTLIST', 'EbookCmsChapter', 'EbookCmsChapter', '', 0, 'EbookCmsChapter.SpineOrder asc, EbookCmsChapter.Id asc', 'List published chapters for one book.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookCmsChapterList($param_EbookCmsChapter_BookSlug_where)', 30, 'manual'),
(@sample26_db_access_class_id, 'GetPublicEbookCmsChapter', 40, '', 'SELECTSINGLE', 'EbookCmsChapter', 'EbookCmsChapter', '', 0, '', 'Get one published chapter by book slug and chapter slug.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookCmsChapter($param_EbookCmsChapter_BookSlug_where, $param_EbookCmsChapter_ChapterSlug_where)', 40, 'manual'),
(@sample26_db_access_class_id, 'GetPublicEbookCmsEpubDeliveryList', 50, '', 'SELECTLIST', 'EbookCmsBook', 'EbookCmsBook', '', 0, 'EbookCmsBook.Id asc', 'List EPUB delivery metadata for one published book.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublicEbookCmsEpubDeliveryList($param_EbookCmsBook_Slug_where)', 50, 'manual'),
(@sample26_db_access_class_id, 'GetEditorEbookCmsChapter', 60, '', 'SELECTSINGLE', 'EbookCmsChapter', 'EbookCmsChapter', '', 0, '', 'Get one chapter for editor preview.', '', '', '', 'ProjectToken', '', 0, 'public function GetEditorEbookCmsChapter($param_EbookCmsChapter_Id_where)', 60, 'manual'),
(@sample26_db_access_class_id, 'UpdateEditorEbookCmsChapterDraft', 70, '', 'UPDATE', 'EbookCmsChapter', 'EbookCmsChapter', 'classobject', 0, '', 'Update editable draft fields for one chapter.', '', '', '', 'ProjectToken', '', 0, 'public function UpdateEditorEbookCmsChapterDraft($EbookCmsChapterObj)', 70, 'manual'),
(@sample26_db_access_class_id, 'PublishEditorEbookCmsChapter', 80, '', 'UPDATE', 'EbookCmsChapter', 'EbookCmsChapter', 'classobject', 0, '', 'Mark one chapter as published.', '', '', '', 'ProjectToken', '', 0, 'public function PublishEditorEbookCmsChapter($EbookCmsChapterObj)', 80, 'manual');

SET @sample26_book_list_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'GetPublicEbookCmsBookList');
SET @sample26_book_detail_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'GetPublicEbookCmsBook');
SET @sample26_chapter_list_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'GetPublicEbookCmsChapterList');
SET @sample26_chapter_detail_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'GetPublicEbookCmsChapter');
SET @sample26_epub_list_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'GetPublicEbookCmsEpubDeliveryList');
SET @sample26_editor_get_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'GetEditorEbookCmsChapter');
SET @sample26_editor_update_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'UpdateEditorEbookCmsChapterDraft');
SET @sample26_editor_publish_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample26_db_access_class_id AND function_name = 'PublishEditorEbookCmsChapter');

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
(@sample26_book_list_id, 'EbookCmsBook', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample26_book_list_id, 'EbookCmsBook', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample26_book_list_id, 'EbookCmsBook', '', 'Slug', '', '', 'Slug', 0, 30, 'manual'),
(@sample26_book_list_id, 'EbookCmsBook', '', 'AuthorName', '', '', 'AuthorName', 0, 40, 'manual'),
(@sample26_book_list_id, 'EbookCmsBook', '', 'GenreName', '', '', 'GenreName', 0, 50, 'manual'),
(@sample26_book_list_id, 'EbookCmsBook', '', 'CoverImageUrl', '', '', 'CoverImageUrl', 0, 60, 'manual'),
(@sample26_book_list_id, 'EbookCmsBook', '', 'Summary', '', '', 'Summary', 0, 70, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'Title', '', '', 'Title', 0, 20, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'Slug', '', '', 'Slug', 0, 30, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'AuthorName', '', '', 'AuthorName', 0, 40, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'GenreName', '', '', 'GenreName', 0, 50, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'CoverImageUrl', '', '', 'CoverImageUrl', 0, 60, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'Summary', '', '', 'Summary', 0, 70, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'BookSlug', '', '', 'BookSlug', 0, 20, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'ChapterTitle', '', '', 'ChapterTitle', 0, 30, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'ChapterSlug', '', '', 'ChapterSlug', 0, 40, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'SpineOrder', '', '', 'SpineOrder', 0, 50, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'BookSlug', '', '', 'BookSlug', 0, 20, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'ChapterTitle', '', '', 'ChapterTitle', 0, 30, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'ChapterSlug', '', '', 'ChapterSlug', 0, 40, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'SpineOrder', '', '', 'SpineOrder', 0, 50, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'BodyMarkdown', '', '', 'BodyMarkdown', 0, 60, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'Slug', '', '', 'Slug', 0, 20, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'Title', '', '', 'Title', 0, 30, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'EpubDownloadUrl', '', '', 'EpubDownloadUrl', 0, 40, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'EpubMimeType', '', '', 'EpubMimeType', 0, 50, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'EpubSha256', '', '', 'EpubSha256', 0, 60, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'Id', '', '', 'Id', 0, 10, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'EbookCmsBookId', '', '', 'EbookCmsBookId', 0, 20, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'BookSlug', '', '', 'BookSlug', 0, 30, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'ChapterTitle', '', '', 'ChapterTitle', 0, 40, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'ChapterSlug', '', '', 'ChapterSlug', 0, 50, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'Status', '', '', 'Status', 0, 60, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'SpineOrder', '', '', 'SpineOrder', 0, 70, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'BodyMarkdown', '', '', 'BodyMarkdown', 0, 80, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'PublishedAt', '', '', 'PublishedAt', 0, 90, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'UpdatedAt', '', '', 'UpdatedAt', 0, 100, 'manual');

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
(@sample26_book_list_id, 'EbookCmsBook', '', 'Status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'Status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample26_book_detail_id, 'EbookCmsBook', '', 'Slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'Status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample26_chapter_list_id, 'EbookCmsChapter', '', 'BookSlug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'Status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'BookSlug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample26_chapter_detail_id, 'EbookCmsChapter', '', 'ChapterSlug', 'argument', 'varchar', '', '', '', '', '', '', '=', 30, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'Status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample26_epub_list_id, 'EbookCmsBook', '', 'Slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample26_editor_get_id, 'EbookCmsChapter', '', 'Id', 'argument', 'int', '', '', '', '', '', '', '=', 10, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample26_editor_update_id, 'ChapterTitle', 'argument', '', '', 10, 'manual'),
(@sample26_editor_update_id, 'ChapterSlug', 'argument', '', '', 20, 'manual'),
(@sample26_editor_update_id, 'SpineOrder', 'argument', '', '', 30, 'manual'),
(@sample26_editor_update_id, 'BodyMarkdown', 'argument', '', '', 40, 'manual'),
(@sample26_editor_update_id, 'UpdatedAt', 'fixed', 'raw', 'NOW()', 50, 'manual'),
(@sample26_editor_publish_id, 'Status', 'fixed', 'varchar', 'published', 10, 'manual'),
(@sample26_editor_publish_id, 'PublishedAt', 'fixed', 'raw', 'NOW()', 20, 'manual'),
(@sample26_editor_publish_id, 'UpdatedAt', 'fixed', 'raw', 'NOW()', 30, 'manual');

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
(@sample26_editor_update_id, 'Id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample26_editor_publish_id, 'Id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample26_project_id = NULL;
SET @sample26_db_access_class_id = NULL;
SET @sample26_book_list_id = NULL;
SET @sample26_book_detail_id = NULL;
SET @sample26_chapter_list_id = NULL;
SET @sample26_chapter_detail_id = NULL;
SET @sample26_epub_list_id = NULL;
SET @sample26_editor_get_id = NULL;
SET @sample26_editor_update_id = NULL;
SET @sample26_editor_publish_id = NULL;
