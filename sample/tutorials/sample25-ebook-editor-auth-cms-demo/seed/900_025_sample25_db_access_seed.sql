SET @sample25_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE25'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample25_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample25_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample25_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample25_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample25_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample25_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample25_project_id;

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
    @sample25_project_id,
    'ebook_editor_chapter',
    '',
    0,
    'Editor CMS API sample with ProjectToken protected chapter update and publish functions.',
    'manual',
    '',
    ''
);

SET @sample25_db_access_class_id = LAST_INSERT_ID();

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
(@sample25_db_access_class_id, 'GetEditorEbookChapter', 10, '', 'SELECTSINGLE', 'EbookEditorChapter', 'ebook_editor_chapter', '', 0, '', 'Get one chapter for editor preview.', '', '', '', 'ProjectToken', '', 0, 'public function GetEditorEbookChapter($param_EbookEditorChapter_Id_where)', 10, 'manual'),
(@sample25_db_access_class_id, 'UpdateEditorEbookChapterDraft', 20, '', 'UPDATE', 'EbookEditorChapter', 'ebook_editor_chapter', 'classobject', 0, '', 'Update editable draft fields for one chapter.', '', '', '', 'ProjectToken', '', 0, 'public function UpdateEditorEbookChapterDraft($EbookEditorChapterObj)', 20, 'manual'),
(@sample25_db_access_class_id, 'PublishEditorEbookChapter', 30, '', 'UPDATE', 'EbookEditorChapter', 'ebook_editor_chapter', 'classobject', 0, '', 'Mark one chapter as published.', '', '', '', 'ProjectToken', '', 0, 'public function PublishEditorEbookChapter($EbookEditorChapterObj)', 30, 'manual');

SET @sample25_get_chapter_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample25_db_access_class_id AND function_name = 'GetEditorEbookChapter');
SET @sample25_update_chapter_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample25_db_access_class_id AND function_name = 'UpdateEditorEbookChapterDraft');
SET @sample25_publish_chapter_id = (SELECT id FROM project_db_access_functions WHERE db_access_class_id = @sample25_db_access_class_id AND function_name = 'PublishEditorEbookChapter');

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
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'id', '', '', 'id', 0, 10, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'ebook_editor_book_id', '', '', 'ebookEditorBookId', 0, 20, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'chapter_title', '', '', 'chapterTitle', 0, 30, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'chapter_slug', '', '', 'chapterSlug', 0, 40, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'status', '', '', 'status', 0, 50, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'spine_order', '', '', 'spineOrder', 0, 60, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'body_markdown', '', '', 'bodyMarkdown', 0, 70, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'published_at', '', '', 'publishedAt', 0, 80, 'manual'),
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'updated_at', '', '', 'updatedAt', 0, 90, 'manual');

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
(@sample25_get_chapter_id, 'ebook_editor_chapter', '', 'id', 'argument', 'int', '', '', '', '', '', '', '=', 10, 'manual');

INSERT INTO project_db_access_function_update_target_fields (
    db_access_function_id,
    target_table_column_name,
    parameter_type,
    parameter_data_type,
    fixed_parameter,
    field_list_order,
    source_of_truth
) VALUES
(@sample25_update_chapter_id, 'chapter_title', 'argument', '', '', 10, 'manual'),
(@sample25_update_chapter_id, 'chapter_slug', 'argument', '', '', 20, 'manual'),
(@sample25_update_chapter_id, 'spine_order', 'argument', '', '', 30, 'manual'),
(@sample25_update_chapter_id, 'body_markdown', 'argument', '', '', 40, 'manual'),
(@sample25_update_chapter_id, 'updated_at', 'fixed', 'raw', 'NOW()', 50, 'manual'),
(@sample25_publish_chapter_id, 'status', 'fixed', 'varchar', 'published', 10, 'manual'),
(@sample25_publish_chapter_id, 'published_at', 'fixed', 'raw', 'NOW()', 20, 'manual'),
(@sample25_publish_chapter_id, 'updated_at', 'fixed', 'raw', 'NOW()', 30, 'manual');

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
(@sample25_update_chapter_id, 'id', 'argument', '', '', '', '=', 10, 'manual'),
(@sample25_publish_chapter_id, 'id', 'argument', '', '', '', '=', 10, 'manual');

SET @sample25_project_id = NULL;
SET @sample25_db_access_class_id = NULL;
SET @sample25_get_chapter_id = NULL;
SET @sample25_update_chapter_id = NULL;
SET @sample25_publish_chapter_id = NULL;
