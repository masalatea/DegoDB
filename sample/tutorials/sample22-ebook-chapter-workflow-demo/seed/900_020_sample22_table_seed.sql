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

DROP TABLE IF EXISTS EbookWorkflowPublishedChapter;
DROP TABLE IF EXISTS EbookWorkflowChapter;
DROP TABLE IF EXISTS EbookWorkflowBook;

CREATE TABLE EbookWorkflowBook (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME DEFAULT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_book_slug (Slug),
    KEY idx_ebook_book_status_published_at (Status, PublishedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookWorkflowChapter (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookWorkflowBookId BIGINT UNSIGNED NOT NULL,
    ChapterTitle VARCHAR(255) NOT NULL,
    ChapterSlug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    SpineOrder INT NOT NULL DEFAULT 1,
    NavLabel VARCHAR(255) NOT NULL DEFAULT '',
    EpubResourcePath VARCHAR(500) NOT NULL DEFAULT '',
    BodyMarkdown TEXT NOT NULL,
    PublishedAt DATETIME DEFAULT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_chapter_slug_per_book (EbookWorkflowBookId, ChapterSlug),
    KEY idx_ebook_chapter_book_status_spine (EbookWorkflowBookId, Status, SpineOrder),
    CONSTRAINT fk_ebook_chapter_book
        FOREIGN KEY (EbookWorkflowBookId)
        REFERENCES EbookWorkflowBook (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookWorkflowPublishedChapter (
    ChapterId BIGINT UNSIGNED NOT NULL,
    BookId BIGINT UNSIGNED NOT NULL,
    BookSlug VARCHAR(180) NOT NULL,
    ChapterTitle VARCHAR(255) NOT NULL,
    ChapterSlug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'published',
    SpineOrder INT NOT NULL DEFAULT 1,
    NavLabel VARCHAR(255) NOT NULL DEFAULT '',
    EpubResourcePath VARCHAR(500) NOT NULL DEFAULT '',
    BodyMarkdown TEXT NOT NULL,
    PublishedAt DATETIME DEFAULT NULL,
    PRIMARY KEY (ChapterId),
    KEY idx_published_chapter_book_spine (BookSlug, Status, SpineOrder)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO EbookWorkflowBook (
    Title,
    Slug,
    Status,
    PublishedAt
) VALUES
    ('JSONから始める電子書籍CMS', 'json-first-ebook-cms', 'published', '2026-06-21 09:00:00'),
    ('未公開の販売メモ', 'draft-sales-note', 'draft', NULL);

INSERT INTO EbookWorkflowChapter (
    EbookWorkflowBookId,
    ChapterTitle,
    ChapterSlug,
    Status,
    SpineOrder,
    NavLabel,
    EpubResourcePath,
    BodyMarkdown,
    PublishedAt,
    UpdatedAt
) VALUES
    (1, 'はじめに', 'intro', 'published', 1, 'はじめに', 'OEBPS/chapter1.xhtml', 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00', '2026-06-21 09:15:00'),
    (1, '公開APIを考える', 'public-api', 'published', 2, '公開APIを考える', 'OEBPS/chapter2.xhtml', '読者向けには公開済み章だけをspine順で返します。', '2026-06-21 09:20:00', '2026-06-21 09:25:00'),
    (1, '編集メモ', 'editor-note', 'draft', 3, '編集メモ', 'OEBPS/chapter3.xhtml', '下書き章はpublic APIに出しません。', NULL, '2026-06-21 09:30:00');

INSERT INTO EbookWorkflowPublishedChapter (
    ChapterId,
    BookId,
    BookSlug,
    ChapterTitle,
    ChapterSlug,
    Status,
    SpineOrder,
    NavLabel,
    EpubResourcePath,
    BodyMarkdown,
    PublishedAt
) VALUES
    (1, 1, 'json-first-ebook-cms', 'はじめに', 'intro', 'published', 1, 'はじめに', 'OEBPS/chapter1.xhtml', 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00'),
    (2, 1, 'json-first-ebook-cms', '公開APIを考える', 'public-api', 'published', 2, '公開APIを考える', 'OEBPS/chapter2.xhtml', '読者向けには公開済み章だけをspine順で返します。', '2026-06-21 09:20:00');

SET @sample22_project_id = NULL;
