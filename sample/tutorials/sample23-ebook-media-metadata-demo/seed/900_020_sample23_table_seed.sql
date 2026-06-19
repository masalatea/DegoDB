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

DROP TABLE IF EXISTS EbookMediaDelivery;
DROP TABLE IF EXISTS EbookMediaBookAsset;
DROP TABLE IF EXISTS EbookMediaAsset;
DROP TABLE IF EXISTS EbookMediaBook;

CREATE TABLE EbookMediaBook (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME DEFAULT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_media_book_slug (Slug),
    KEY idx_ebook_media_book_status_published_at (Status, PublishedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookMediaAsset (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    AssetSlug VARCHAR(180) NOT NULL,
    AssetKind VARCHAR(32) NOT NULL,
    DisplayName VARCHAR(255) NOT NULL,
    PublicUrl VARCHAR(500) NOT NULL,
    StoragePath VARCHAR(500) NOT NULL,
    MimeType VARCHAR(120) NOT NULL,
    FileSizeBytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    Sha256 VARCHAR(64) NOT NULL,
    VersionLabel VARCHAR(80) NOT NULL DEFAULT '',
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_media_asset_slug (AssetSlug),
    KEY idx_ebook_media_asset_kind_status (AssetKind, Status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookMediaBookAsset (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookMediaBookId BIGINT UNSIGNED NOT NULL,
    EbookMediaAssetId BIGINT UNSIGNED NOT NULL,
    DisplayRole VARCHAR(32) NOT NULL,
    SortOrder INT NOT NULL DEFAULT 1,
    IsPrimaryAsset TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_media_book_asset_role (EbookMediaBookId, DisplayRole, EbookMediaAssetId),
    KEY idx_ebook_media_book_asset_book_sort (EbookMediaBookId, SortOrder),
    CONSTRAINT fk_ebook_media_book_asset_book
        FOREIGN KEY (EbookMediaBookId)
        REFERENCES EbookMediaBook (Id),
    CONSTRAINT fk_ebook_media_book_asset_asset
        FOREIGN KEY (EbookMediaAssetId)
        REFERENCES EbookMediaAsset (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookMediaDelivery (
    DeliveryId BIGINT UNSIGNED NOT NULL,
    BookId BIGINT UNSIGNED NOT NULL,
    BookSlug VARCHAR(180) NOT NULL,
    AssetId BIGINT UNSIGNED NOT NULL,
    AssetSlug VARCHAR(180) NOT NULL,
    AssetKind VARCHAR(32) NOT NULL,
    DisplayRole VARCHAR(32) NOT NULL,
    DisplayName VARCHAR(255) NOT NULL,
    PublicUrl VARCHAR(500) NOT NULL,
    MimeType VARCHAR(120) NOT NULL,
    FileSizeBytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    Sha256 VARCHAR(64) NOT NULL,
    VersionLabel VARCHAR(80) NOT NULL DEFAULT '',
    SortOrder INT NOT NULL DEFAULT 1,
    IsPrimaryAsset TINYINT(1) NOT NULL DEFAULT 0,
    Status VARCHAR(32) NOT NULL DEFAULT 'published',
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (DeliveryId),
    KEY idx_ebook_media_delivery_book_role (BookSlug, DisplayRole, SortOrder),
    KEY idx_ebook_media_delivery_asset_slug (AssetSlug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO EbookMediaBook (
    Title,
    Slug,
    Status,
    PublishedAt
) VALUES
    ('JSONから始める電子書籍CMS', 'json-first-ebook-cms', 'published', '2026-06-21 09:00:00'),
    ('未公開の販売メモ', 'draft-sales-note', 'draft', NULL);

INSERT INTO EbookMediaAsset (
    AssetSlug,
    AssetKind,
    DisplayName,
    PublicUrl,
    StoragePath,
    MimeType,
    FileSizeBytes,
    Sha256,
    VersionLabel,
    Status,
    UpdatedAt
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

INSERT INTO EbookMediaBookAsset (
    EbookMediaBookId,
    EbookMediaAssetId,
    DisplayRole,
    SortOrder,
    IsPrimaryAsset
) VALUES
    (1, 1, 'download', 1, 1),
    (1, 2, 'cover', 2, 0);

INSERT INTO EbookMediaDelivery (
    DeliveryId,
    BookId,
    BookSlug,
    AssetId,
    AssetSlug,
    AssetKind,
    DisplayRole,
    DisplayName,
    PublicUrl,
    MimeType,
    FileSizeBytes,
    Sha256,
    VersionLabel,
    SortOrder,
    IsPrimaryAsset,
    Status,
    UpdatedAt
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
