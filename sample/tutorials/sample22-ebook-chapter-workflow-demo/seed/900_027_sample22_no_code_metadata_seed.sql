SET @sample22_project_id = (
    SELECT id FROM projects WHERE project_key = 'SAMPLE22'
);

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample22_project_id
  AND contracts.contract_key IN ('ebook_workflow_book', 'ebook_workflow_published_chapter');

DELETE FROM project_shared_contracts
WHERE project_id = @sample22_project_id
  AND contract_key IN ('ebook_workflow_book', 'ebook_workflow_published_chapter');

INSERT INTO project_shared_contracts (
    project_id, contract_key, data_class_physical_name, status,
    sync_role, no_code_role, app_persistence_role, notes, source_of_truth
) VALUES
(@sample22_project_id, 'ebook_workflow_book', 'ebook_workflow_book', 'active', 'server-copy', 'managed-screen', 'server-managed-copy', 'Read-only Sample22 book lookup source.', 'manual'),
(@sample22_project_id, 'ebook_workflow_published_chapter', 'ebook_workflow_published_chapter', 'active', 'server-copy', 'managed-screen', 'server-managed-copy', 'Read-only Sample22 published chapter relation consumer.', 'manual');

SET @sample22_book_contract_id = (
    SELECT id FROM project_shared_contracts
    WHERE project_id = @sample22_project_id AND contract_key = 'ebook_workflow_book'
);
SET @sample22_chapter_contract_id = (
    SELECT id FROM project_shared_contracts
    WHERE project_id = @sample22_project_id AND contract_key = 'ebook_workflow_published_chapter'
);

INSERT INTO project_shared_contract_fields (
    project_id, shared_contract_id, field_physical_name,
    sync_role, operation_role, no_code_role, app_persistence_role, notes, source_of_truth
) VALUES
(@sample22_project_id, @sample22_book_contract_id, 'id', 'server-copy', 'key', 'identifier', 'server-managed-copy', 'Book lookup key.', 'manual'),
(@sample22_project_id, @sample22_book_contract_id, 'title', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Book lookup label.', 'manual'),
(@sample22_project_id, @sample22_book_contract_id, 'slug', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Book public slug.', 'manual'),
(@sample22_project_id, @sample22_book_contract_id, 'status', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Book lifecycle status.', 'manual'),
(@sample22_project_id, @sample22_book_contract_id, 'published_at', 'server-copy', 'readonly', 'field', 'server-managed-copy', 'Book publication timestamp.', 'manual');

INSERT INTO project_shared_contract_fields (
    project_id, shared_contract_id, field_physical_name,
    sync_role, operation_role, no_code_role,
    relation_kind, relation_contract_key, relation_key_field, relation_label_field, relation_ui_role, relation_required,
    app_persistence_role, notes, source_of_truth
) VALUES
(@sample22_project_id, @sample22_chapter_contract_id, 'chapter_id', 'server-copy', 'key', 'identifier', '', '', '', '', '', 0, 'server-managed-copy', 'Published chapter key.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'book_id', 'server-copy', 'readonly', 'field', 'belongs_to', 'ebook_workflow_book', 'id', 'title', 'parent', 1, 'server-managed-copy', 'Explicit book parent and lookup boundary.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'book_slug', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Visible book context slug.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'chapter_title', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Published chapter title.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'chapter_slug', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Published chapter slug.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'status', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Published lifecycle status.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'spine_order', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'EPUB spine order.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'nav_label', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Navigation label.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'epub_resource_path', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'EPUB resource path.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'body_markdown', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Published Markdown body.', 'manual'),
(@sample22_project_id, @sample22_chapter_contract_id, 'published_at', 'server-copy', 'readonly', 'field', '', '', '', '', '', 0, 'server-managed-copy', 'Publication timestamp.', 'manual');

SET @sample22_book_contract_id = NULL;
SET @sample22_chapter_contract_id = NULL;
SET @sample22_project_id = NULL;
