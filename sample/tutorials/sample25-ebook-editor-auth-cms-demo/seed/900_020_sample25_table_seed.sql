SET @sample25_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE25'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample25_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample25_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample25_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample25_project_id;

DROP TABLE IF EXISTS ebook_editor_chapter;
DROP TABLE IF EXISTS ebook_editor_book;

CREATE TABLE ebook_editor_book (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_editor_book_slug (slug),
    KEY idx_ebook_editor_book_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_editor_chapter (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_editor_book_id BIGINT UNSIGNED NOT NULL,
    chapter_title VARCHAR(255) NOT NULL,
    chapter_slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    spine_order INT NOT NULL DEFAULT 1,
    body_markdown TEXT NOT NULL,
    published_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_editor_chapter_slug_per_book (ebook_editor_book_id, chapter_slug),
    KEY idx_ebook_editor_chapter_book_status (ebook_editor_book_id, status, spine_order),
    CONSTRAINT fk_ebook_editor_chapter_book
        FOREIGN KEY (ebook_editor_book_id)
        REFERENCES ebook_editor_book (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ebook_editor_book (
    title,
    slug,
    status,
    published_at,
    updated_at
) VALUES
    ('JSONから始める電子書籍CMS', 'json-first-ebook-cms', 'published', '2026-06-21 09:00:00', '2026-06-21 09:05:00');

INSERT INTO ebook_editor_chapter (
    ebook_editor_book_id,
    chapter_title,
    chapter_slug,
    status,
    spine_order,
    body_markdown,
    published_at,
    updated_at
) VALUES
    (1, 'はじめに', 'intro', 'published', 1, 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00', '2026-06-21 09:15:00'),
    (1, '編集者向けAPI', 'editor-api', 'draft', 2, 'この章はtoken protected APIで更新・公開します。', NULL, '2026-06-21 09:30:00');

SET @sample25_project_id = NULL;
