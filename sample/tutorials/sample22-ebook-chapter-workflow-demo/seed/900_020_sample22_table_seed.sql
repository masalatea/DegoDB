SET @sample22_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE22'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample22_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample22_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample22_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample22_project_id;

DROP TABLE IF EXISTS ebook_workflow_published_chapter;
DROP TABLE IF EXISTS ebook_workflow_chapter;
DROP TABLE IF EXISTS ebook_workflow_book;

CREATE TABLE ebook_workflow_book (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_book_slug (slug),
    KEY idx_ebook_book_status_published_at (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_workflow_chapter (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_workflow_book_id BIGINT UNSIGNED NOT NULL,
    chapter_title VARCHAR(255) NOT NULL,
    chapter_slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    spine_order INT NOT NULL DEFAULT 1,
    nav_label VARCHAR(255) NOT NULL DEFAULT '',
    epub_resource_path VARCHAR(500) NOT NULL DEFAULT '',
    body_markdown TEXT NOT NULL,
    published_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_chapter_slug_per_book (ebook_workflow_book_id, chapter_slug),
    KEY idx_ebook_chapter_book_status_spine (ebook_workflow_book_id, status, spine_order),
    CONSTRAINT fk_ebook_chapter_book
        FOREIGN KEY (ebook_workflow_book_id)
        REFERENCES ebook_workflow_book (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_workflow_published_chapter (
    chapter_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    book_slug VARCHAR(180) NOT NULL,
    chapter_title VARCHAR(255) NOT NULL,
    chapter_slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'published',
    spine_order INT NOT NULL DEFAULT 1,
    nav_label VARCHAR(255) NOT NULL DEFAULT '',
    epub_resource_path VARCHAR(500) NOT NULL DEFAULT '',
    body_markdown TEXT NOT NULL,
    published_at DATETIME DEFAULT NULL,
    PRIMARY KEY (chapter_id),
    KEY idx_published_chapter_book_spine (book_slug, status, spine_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ebook_workflow_book (
    title,
    slug,
    status,
    published_at
) VALUES
    ('JSONから始める電子書籍CMS', 'json-first-ebook-cms', 'published', '2026-06-21 09:00:00'),
    ('未公開の販売メモ', 'draft-sales-note', 'draft', NULL);

INSERT INTO ebook_workflow_chapter (
    ebook_workflow_book_id,
    chapter_title,
    chapter_slug,
    status,
    spine_order,
    nav_label,
    epub_resource_path,
    body_markdown,
    published_at,
    updated_at
) VALUES
    (1, 'はじめに', 'intro', 'published', 1, 'はじめに', 'OEBPS/chapter1.xhtml', 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00', '2026-06-21 09:15:00'),
    (1, '公開APIを考える', 'public-api', 'published', 2, '公開APIを考える', 'OEBPS/chapter2.xhtml', '読者向けには公開済み章だけをspine順で返します。', '2026-06-21 09:20:00', '2026-06-21 09:25:00'),
    (1, '編集メモ', 'editor-note', 'draft', 3, '編集メモ', 'OEBPS/chapter3.xhtml', '下書き章はpublic APIに出しません。', NULL, '2026-06-21 09:30:00');

INSERT INTO ebook_workflow_published_chapter (
    chapter_id,
    book_id,
    book_slug,
    chapter_title,
    chapter_slug,
    status,
    spine_order,
    nav_label,
    epub_resource_path,
    body_markdown,
    published_at
) VALUES
    (1, 1, 'json-first-ebook-cms', 'はじめに', 'intro', 'published', 1, 'はじめに', 'OEBPS/chapter1.xhtml', 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00'),
    (2, 1, 'json-first-ebook-cms', '公開APIを考える', 'public-api', 'published', 2, '公開APIを考える', 'OEBPS/chapter2.xhtml', '読者向けには公開済み章だけをspine順で返します。', '2026-06-21 09:20:00');

SET @sample22_project_id = NULL;
