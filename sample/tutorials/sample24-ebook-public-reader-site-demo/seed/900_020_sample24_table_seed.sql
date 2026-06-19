SET @sample24_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE24'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample24_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample24_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample24_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample24_project_id;

DROP TABLE IF EXISTS EbookReaderMediaDelivery;
DROP TABLE IF EXISTS EbookReaderChapter;
DROP TABLE IF EXISTS EbookReaderBook;

CREATE TABLE EbookReaderBook (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(180) NOT NULL,
    AuthorName VARCHAR(160) NOT NULL DEFAULT '',
    GenreName VARCHAR(120) NOT NULL DEFAULT '',
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    Summary VARCHAR(500) NOT NULL DEFAULT '',
    PublishedAt DATETIME DEFAULT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_reader_book_slug (Slug),
    KEY idx_ebook_reader_book_status_published_at (Status, PublishedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookReaderChapter (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookReaderBookId BIGINT UNSIGNED NOT NULL,
    BookSlug VARCHAR(180) NOT NULL,
    ChapterTitle VARCHAR(255) NOT NULL,
    ChapterSlug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    SpineOrder INT NOT NULL DEFAULT 1,
    BodyMarkdown TEXT NOT NULL,
    PublishedAt DATETIME DEFAULT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_reader_chapter_slug_per_book (EbookReaderBookId, ChapterSlug),
    KEY idx_ebook_reader_chapter_book_status_spine (BookSlug, Status, SpineOrder),
    CONSTRAINT fk_ebook_reader_chapter_book
        FOREIGN KEY (EbookReaderBookId)
        REFERENCES EbookReaderBook (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookReaderMediaDelivery (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookReaderBookId BIGINT UNSIGNED NOT NULL,
    BookSlug VARCHAR(180) NOT NULL,
    AssetSlug VARCHAR(180) NOT NULL,
    AssetKind VARCHAR(32) NOT NULL,
    DisplayName VARCHAR(255) NOT NULL,
    PublicUrl VARCHAR(500) NOT NULL,
    MimeType VARCHAR(120) NOT NULL,
    FileSizeBytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    Sha256 VARCHAR(64) NOT NULL,
    VersionLabel VARCHAR(80) NOT NULL DEFAULT '',
    Status VARCHAR(32) NOT NULL DEFAULT 'published',
    PRIMARY KEY (Id),
    KEY idx_ebook_reader_media_book_kind (BookSlug, AssetKind, Status),
    CONSTRAINT fk_ebook_reader_media_book
        FOREIGN KEY (EbookReaderBookId)
        REFERENCES EbookReaderBook (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO EbookReaderBook (
    Title,
    Slug,
    AuthorName,
    GenreName,
    Status,
    Summary,
    PublishedAt
) VALUES
    (
        'JSONから始める電子書籍CMS',
        'json-first-ebook-cms',
        'Sample Editor',
        'Guide',
        'published',
        'JSONを入口に、電子書籍サイトとapp向けAPIを組み立てる小さなreader sampleです。',
        '2026-06-21 09:00:00'
    ),
    (
        '未公開の販売メモ',
        'draft-sales-note',
        'Sample Editor',
        'Internal',
        'draft',
        'この本はpublic readerには出しません。',
        NULL
    );

INSERT INTO EbookReaderChapter (
    EbookReaderBookId,
    BookSlug,
    ChapterTitle,
    ChapterSlug,
    Status,
    SpineOrder,
    BodyMarkdown,
    PublishedAt
) VALUES
    (1, 'json-first-ebook-cms', 'はじめに', 'intro', 'published', 1, 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00'),
    (1, 'json-first-ebook-cms', '公開APIを考える', 'public-api', 'published', 2, '読者向けには公開済み章だけをspine順で返します。', '2026-06-21 09:20:00'),
    (1, 'json-first-ebook-cms', '編集メモ', 'editor-note', 'draft', 3, '下書き章はpublic readerには出しません。', NULL);

INSERT INTO EbookReaderMediaDelivery (
    EbookReaderBookId,
    BookSlug,
    AssetSlug,
    AssetKind,
    DisplayName,
    PublicUrl,
    MimeType,
    FileSizeBytes,
    Sha256,
    VersionLabel,
    Status
) VALUES
    (
        1,
        'json-first-ebook-cms',
        'json-first-mini-book-epub',
        'epub',
        'JSON First Mini Book EPUB',
        '/assets/epub/json-first-mini-book/json-first-mini-book.epub',
        'application/epub+zip',
        3125,
        '6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea',
        'sample-fixture-v1',
        'published'
    );

SET @sample24_project_id = NULL;
