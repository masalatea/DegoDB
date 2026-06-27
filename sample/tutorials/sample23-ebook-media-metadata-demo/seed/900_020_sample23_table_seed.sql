SET @sample23_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE23'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample23_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample23_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample23_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample23_project_id;

DROP TABLE IF EXISTS ebook_media_delivery;
DROP TABLE IF EXISTS ebook_media_book_asset;
DROP TABLE IF EXISTS ebook_media_asset;
DROP TABLE IF EXISTS ebook_media_book;

CREATE TABLE ebook_media_book (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_media_book_slug (slug),
    KEY idx_ebook_media_book_status_published_at (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_media_asset (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    asset_slug VARCHAR(180) NOT NULL,
    asset_kind VARCHAR(32) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    public_url VARCHAR(500) NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    sha256 VARCHAR(64) NOT NULL,
    version_label VARCHAR(80) NOT NULL DEFAULT '',
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_media_asset_slug (asset_slug),
    KEY idx_ebook_media_asset_kind_status (asset_kind, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_media_book_asset (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_media_book_id BIGINT UNSIGNED NOT NULL,
    ebook_media_asset_id BIGINT UNSIGNED NOT NULL,
    display_role VARCHAR(32) NOT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    is_primary_asset TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_media_book_asset_role (ebook_media_book_id, display_role, ebook_media_asset_id),
    KEY idx_ebook_media_book_asset_book_sort (ebook_media_book_id, sort_order),
    CONSTRAINT fk_ebook_media_book_asset_book
        FOREIGN KEY (ebook_media_book_id)
        REFERENCES ebook_media_book (id),
    CONSTRAINT fk_ebook_media_book_asset_asset
        FOREIGN KEY (ebook_media_asset_id)
        REFERENCES ebook_media_asset (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_media_delivery (
    delivery_id BIGINT UNSIGNED NOT NULL,
    book_id BIGINT UNSIGNED NOT NULL,
    book_slug VARCHAR(180) NOT NULL,
    asset_id BIGINT UNSIGNED NOT NULL,
    asset_slug VARCHAR(180) NOT NULL,
    asset_kind VARCHAR(32) NOT NULL,
    display_role VARCHAR(32) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    public_url VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    sha256 VARCHAR(64) NOT NULL,
    version_label VARCHAR(80) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 1,
    is_primary_asset TINYINT(1) NOT NULL DEFAULT 0,
    status VARCHAR(32) NOT NULL DEFAULT 'published',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (delivery_id),
    KEY idx_ebook_media_delivery_book_role (book_slug, display_role, sort_order),
    KEY idx_ebook_media_delivery_asset_slug (asset_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ebook_media_book (
    title,
    slug,
    status,
    published_at
) VALUES
    ('JSONから始める電子書籍CMS', 'json-first-ebook-cms', 'published', '2026-06-21 09:00:00'),
    ('未公開の販売メモ', 'draft-sales-note', 'draft', NULL);

INSERT INTO ebook_media_asset (
    asset_slug,
    asset_kind,
    display_name,
    public_url,
    storage_path,
    mime_type,
    file_size_bytes,
    sha256,
    version_label,
    status,
    updated_at
) VALUES
    (
        'json-first-mini-book-epub',
        'epub',
        'JSON First Mini Book EPUB',
        '/assets/epub/json-first-mini-book/json-first-mini-book.epub',
        'sample/_assets/epub/json-first-mini-book/json-first-mini-book.epub',
        'application/epub+zip',
        3125,
        '6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea',
        'sample-fixture-v1',
        'published',
        '2026-06-19 09:00:00'
    ),
    (
        'json-first-mini-book-cover',
        'cover-image',
        'JSON First Mini Book Cover',
        '/assets/cover/json-first-mini-book-cover.png',
        'sample/_assets/cover/json-first-mini-book-cover.png',
        'image/png',
        0,
        '',
        'placeholder',
        'draft',
        '2026-06-19 09:05:00'
    );

INSERT INTO ebook_media_book_asset (
    ebook_media_book_id,
    ebook_media_asset_id,
    display_role,
    sort_order,
    is_primary_asset
) VALUES
    (1, 1, 'download', 1, 1),
    (1, 2, 'cover', 2, 0);

INSERT INTO ebook_media_delivery (
    delivery_id,
    book_id,
    book_slug,
    asset_id,
    asset_slug,
    asset_kind,
    display_role,
    display_name,
    public_url,
    mime_type,
    file_size_bytes,
    sha256,
    version_label,
    sort_order,
    is_primary_asset,
    status,
    updated_at
) VALUES
    (
        1,
        1,
        'json-first-ebook-cms',
        1,
        'json-first-mini-book-epub',
        'epub',
        'download',
        'JSON First Mini Book EPUB',
        '/assets/epub/json-first-mini-book/json-first-mini-book.epub',
        'application/epub+zip',
        3125,
        '6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea',
        'sample-fixture-v1',
        1,
        1,
        'published',
        '2026-06-19 09:00:00'
    );

SET @sample23_project_id = NULL;
