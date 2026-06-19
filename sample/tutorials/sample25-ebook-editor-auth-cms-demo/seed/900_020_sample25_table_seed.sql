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

DROP TABLE IF EXISTS EbookEditorChapter;
DROP TABLE IF EXISTS EbookEditorBook;

CREATE TABLE EbookEditorBook (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME DEFAULT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_editor_book_slug (Slug),
    KEY idx_ebook_editor_book_status (Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookEditorChapter (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookEditorBookId BIGINT UNSIGNED NOT NULL,
    ChapterTitle VARCHAR(255) NOT NULL,
    ChapterSlug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    SpineOrder INT NOT NULL DEFAULT 1,
    BodyMarkdown TEXT NOT NULL,
    PublishedAt DATETIME DEFAULT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_editor_chapter_slug_per_book (EbookEditorBookId, ChapterSlug),
    KEY idx_ebook_editor_chapter_book_status (EbookEditorBookId, Status, SpineOrder),
    CONSTRAINT fk_ebook_editor_chapter_book
        FOREIGN KEY (EbookEditorBookId)
        REFERENCES EbookEditorBook (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO EbookEditorBook (
    Title,
    Slug,
    Status,
    PublishedAt,
    UpdatedAt
) VALUES
    ('JSONから始める電子書籍CMS', 'json-first-ebook-cms', 'published', '2026-06-21 09:00:00', '2026-06-21 09:05:00');

INSERT INTO EbookEditorChapter (
    EbookEditorBookId,
    ChapterTitle,
    ChapterSlug,
    Status,
    SpineOrder,
    BodyMarkdown,
    PublishedAt,
    UpdatedAt
) VALUES
    (1, 'はじめに', 'intro', 'published', 1, 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00', '2026-06-21 09:15:00'),
    (1, '編集者向けAPI', 'editor-api', 'draft', 2, 'この章はtoken protected APIで更新・公開します。', NULL, '2026-06-21 09:30:00');

SET @sample25_project_id = NULL;
