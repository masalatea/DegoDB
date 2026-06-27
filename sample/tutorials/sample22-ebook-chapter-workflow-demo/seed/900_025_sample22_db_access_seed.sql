SET @sample22_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE22'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample22_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample22_project_id;

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
    @sample22_project_id,
    'ebook_workflow_chapter',
    '',
    0,
    'Ebook chapter workflow sample with public read and minimal editor write functions.',
    'manual',
    '',
    ''
);

SET @sample22_db_access_class_id = LAST_INSERT_ID();

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
(@sample22_db_access_class_id, 'GetPublishedEbookWorkflowChapterList', 10, '', 'SELECTLIST', 'EbookWorkflowPublishedChapter', 'ebook_workflow_published_chapter', '', 0, 'ebook_workflow_published_chapter.spine_order asc, ebook_workflow_published_chapter.chapter_id asc', 'List published chapters for a book in EPUB spine order.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublishedEbookWorkflowChapterList($param_EbookWorkflowPublishedChapter_BookSlug_where)', 10, 'manual'),
(@sample22_db_access_class_id, 'GetPublishedEbookWorkflowChapter', 20, '', 'SELECTSINGLE', 'EbookWorkflowPublishedChapter', 'ebook_workflow_published_chapter', '', 0, '', 'Get one published chapter by book slug and chapter slug.', '', '', '', 'NoSecurity', '', 0, 'public function GetPublishedEbookWorkflowChapter($param_EbookWorkflowPublishedChapter_BookSlug_where, $param_EbookWorkflowPublishedChapter_ChapterSlug_where)', 20, 'manual'),
(@sample22_db_access_class_id, 'InsertEbookWorkflowChapter', 30, '', 'INSERT', 'EbookWorkflowChapter', 'ebook_workflow_chapter', 'classobject', 0, '', 'Create a chapter draft.', '', '', '', 'NoSecurity', '', 0, 'public function InsertEbookWorkflowChapter($EbookWorkflowChapterObj)', 30, 'manual'),
(@sample22_db_access_class_id, 'UpdateEbookWorkflowChapterDraft', 40, '', 'UPDATE', 'EbookWorkflowChapter', 'ebook_workflow_chapter', 'classobject', 0, '', 'Update editable chapter draft fields.', '', '', '', 'NoSecurity', '', 0, 'public function UpdateEbookWorkflowChapterDraft($EbookWorkflowChapterObj)', 40, 'manual'),
(@sample22_db_access_class_id, 'UpdateEbookWorkflowChapterOrder', 50, '', 'UPDATE', 'EbookWorkflowChapter', 'ebook_workflow_chapter', 'classobject', 0, '', 'Update spine/nav order metadata.', '', '', '', 'NoSecurity', '', 0, 'public function UpdateEbookWorkflowChapterOrder($EbookWorkflowChapterObj)', 50, 'manual'),
(@sample22_db_access_class_id, 'PublishEbookWorkflowChapter', 60, '', 'UPDATE', 'EbookWorkflowChapter', 'ebook_workflow_chapter', 'classobject', 0, '', 'Mark a chapter as published.', '', '', '', 'NoSecurity', '', 0, 'public function PublishEbookWorkflowChapter($EbookWorkflowChapterObj)', 60, 'manual');

SET @sample22_list_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample22_db_access_class_id AND function_name = 'GetPublishedEbookWorkflowChapterList');
SET @sample22_detail_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample22_db_access_class_id AND function_name = 'GetPublishedEbookWorkflowChapter');
SET @sample22_insert_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample22_db_access_class_id AND function_name = 'InsertEbookWorkflowChapter');
SET @sample22_update_draft_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample22_db_access_class_id AND function_name = 'UpdateEbookWorkflowChapterDraft');
SET @sample22_update_order_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample22_db_access_class_id AND function_name = 'UpdateEbookWorkflowChapterOrder');
SET @sample22_publish_function_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample22_db_access_class_id AND function_name = 'PublishEbookWorkflowChapter');

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
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'chapter_id', '', '', 'chapterId', 0, 10, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'book_id', '', '', 'bookId', 0, 20, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'book_slug', '', '', 'bookSlug', 0, 30, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'chapter_title', '', '', 'chapterTitle', 0, 40, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'chapter_slug', '', '', 'chapterSlug', 0, 50, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'spine_order', '', '', 'spineOrder', 0, 60, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'nav_label', '', '', 'navLabel', 0, 70, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'epub_resource_path', '', '', 'epubResourcePath', 0, 80, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'chapter_id', '', '', 'chapterId', 0, 10, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'book_id', '', '', 'bookId', 0, 20, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'book_slug', '', '', 'bookSlug', 0, 30, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'chapter_title', '', '', 'chapterTitle', 0, 40, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'chapter_slug', '', '', 'chapterSlug', 0, 50, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'spine_order', '', '', 'spineOrder', 0, 60, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'nav_label', '', '', 'navLabel', 0, 70, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'epub_resource_path', '', '', 'epubResourcePath', 0, 80, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'body_markdown', '', '', 'bodyMarkdown', 0, 90, 'manual');

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
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample22_list_function_id, 'ebook_workflow_published_chapter', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'status', 'fixed', '', 'published', '', '', '', '', '', '=', 10, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'book_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 20, 'manual'),
(@sample22_detail_function_id, 'ebook_workflow_published_chapter', '', 'chapter_slug', 'argument', 'varchar', '', '', '', '', '', '', '=', 30, 'manual');

INSERT INTO project_db_access_function_insert_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample22_insert_function_id, 'ebook_workflow_book_id', 'argument', '', '', 10, 'manual'),
(@sample22_insert_function_id, 'chapter_title', 'argument', '', '', 20, 'manual'),
(@sample22_insert_function_id, 'chapter_slug', 'argument', '', '', 30, 'manual'),
(@sample22_insert_function_id, 'status', 'fixed', 'varchar', 'draft', 40, 'manual'),
(@sample22_insert_function_id, 'spine_order', 'argument', '', '', 50, 'manual'),
(@sample22_insert_function_id, 'nav_label', 'argument', '', '', 60, 'manual'),
(@sample22_insert_function_id, 'epub_resource_path', 'argument', '', '', 70, 'manual'),
(@sample22_insert_function_id, 'body_markdown', 'argument', '', '', 80, 'manual'),
(@sample22_insert_function_id, 'updated_at', 'fixed', 'raw', 'NOW()', 90, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample22_update_draft_function_id, 'chapter_title', 'argument', '', '', 10, 'manual'),
(@sample22_update_draft_function_id, 'nav_label', 'argument', '', '', 20, 'manual'),
(@sample22_update_draft_function_id, 'epub_resource_path', 'argument', '', '', 30, 'manual'),
(@sample22_update_draft_function_id, 'body_markdown', 'argument', '', '', 40, 'manual'),
(@sample22_update_draft_function_id, 'updated_at', 'fixed', 'raw', 'NOW()', 50, 'manual'),
(@sample22_update_order_function_id, 'spine_order', 'argument', '', '', 10, 'manual'),
(@sample22_update_order_function_id, 'nav_label', 'argument', '', '', 20, 'manual'),
(@sample22_update_order_function_id, 'epub_resource_path', 'argument', '', '', 30, 'manual'),
(@sample22_update_order_function_id, 'updated_at', 'fixed', 'raw', 'NOW()', 40, 'manual'),
(@sample22_publish_function_id, 'status', 'fixed', 'varchar', 'published', 10, 'manual'),
(@sample22_publish_function_id, 'published_at', 'fixed', 'raw', 'NOW()', 20, 'manual'),
(@sample22_publish_function_id, 'updated_at', 'fixed', 'raw', 'NOW()', 30, 'manual');

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
(@sample22_update_draft_function_id, 'id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample22_update_order_function_id, 'id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample22_publish_function_id, 'id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample22_project_id = NULL;
SET @sample22_db_access_class_id = NULL;
SET @sample22_list_function_id = NULL;
SET @sample22_detail_function_id = NULL;
SET @sample22_insert_function_id = NULL;
SET @sample22_update_draft_function_id = NULL;
SET @sample22_update_order_function_id = NULL;
SET @sample22_publish_function_id = NULL;
