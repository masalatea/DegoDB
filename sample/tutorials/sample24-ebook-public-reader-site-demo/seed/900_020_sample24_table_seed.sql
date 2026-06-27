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

DROP TABLE IF EXISTS ebook_reader_media_delivery;
DROP TABLE IF EXISTS ebook_reader_chapter;
DROP TABLE IF EXISTS ebook_reader_book;

CREATE TABLE ebook_reader_book (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    author_name VARCHAR(160) NOT NULL DEFAULT '',
    genre_name VARCHAR(120) NOT NULL DEFAULT '',
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    summary VARCHAR(500) NOT NULL DEFAULT '',
    published_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_reader_book_slug (slug),
    KEY idx_ebook_reader_book_status_published_at (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_reader_chapter (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_reader_book_id BIGINT UNSIGNED NOT NULL,
    book_slug VARCHAR(180) NOT NULL,
    chapter_title VARCHAR(255) NOT NULL,
    chapter_slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    spine_order INT NOT NULL DEFAULT 1,
    body_markdown TEXT NOT NULL,
    published_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_reader_chapter_slug_per_book (ebook_reader_book_id, chapter_slug),
    KEY idx_ebook_reader_chapter_book_status_spine (book_slug, status, spine_order),
    CONSTRAINT fk_ebook_reader_chapter_book
        FOREIGN KEY (ebook_reader_book_id)
        REFERENCES ebook_reader_book (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_reader_media_delivery (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_reader_book_id BIGINT UNSIGNED NOT NULL,
    book_slug VARCHAR(180) NOT NULL,
    asset_slug VARCHAR(180) NOT NULL,
    asset_kind VARCHAR(32) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    public_url VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    sha256 VARCHAR(64) NOT NULL,
    version_label VARCHAR(80) NOT NULL DEFAULT '',
    status VARCHAR(32) NOT NULL DEFAULT 'published',
    PRIMARY KEY (id),
    KEY idx_ebook_reader_media_book_kind (book_slug, asset_kind, status),
    CONSTRAINT fk_ebook_reader_media_book
        FOREIGN KEY (ebook_reader_book_id)
        REFERENCES ebook_reader_book (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ebook_reader_book (
    title,
    slug,
    author_name,
    genre_name,
    status,
    summary,
    published_at
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

INSERT INTO ebook_reader_chapter (
    ebook_reader_book_id,
    book_slug,
    chapter_title,
    chapter_slug,
    status,
    spine_order,
    body_markdown,
    published_at
) VALUES
    (1, 'json-first-ebook-cms', 'はじめに', 'intro', 'published', 1, 'JSONから始める電子書籍CMSの導入章です。', '2026-06-21 09:10:00'),
    (1, 'json-first-ebook-cms', '公開APIを考える', 'public-api', 'published', 2, '読者向けには公開済み章だけをspine順で返します。', '2026-06-21 09:20:00'),
    (1, 'json-first-ebook-cms', '編集メモ', 'editor-note', 'draft', 3, '下書き章はpublic readerには出しません。', NULL);

INSERT INTO ebook_reader_media_delivery (
    ebook_reader_book_id,
    book_slug,
    asset_slug,
    asset_kind,
    display_name,
    public_url,
    mime_type,
    file_size_bytes,
    sha256,
    version_label,
    status
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
