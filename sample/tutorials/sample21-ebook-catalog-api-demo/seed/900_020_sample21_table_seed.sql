SET @sample21_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE21'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample21_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample21_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample21_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample21_project_id;

DROP TABLE IF EXISTS EbookCatalogItem;
DROP TABLE IF EXISTS EbookBookGenre;
DROP TABLE IF EXISTS EbookBookAuthor;
DROP TABLE IF EXISTS EbookBook;
DROP TABLE IF EXISTS EbookGenre;
DROP TABLE IF EXISTS EbookAuthor;
DROP TABLE IF EXISTS EbookSeries;

CREATE TABLE EbookSeries (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(160) NOT NULL,
    Slug VARCHAR(160) NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_series_slug (Slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookAuthor (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(160) NOT NULL,
    Slug VARCHAR(160) NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_author_slug (Slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookGenre (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(120) NOT NULL,
    Slug VARCHAR(120) NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_genre_slug (Slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookBook (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookSeriesId BIGINT UNSIGNED NULL,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME DEFAULT NULL,
    Summary VARCHAR(600) NOT NULL DEFAULT '',
    EpubStatus VARCHAR(32) NOT NULL DEFAULT 'none',
    PrimaryEpubUrl VARCHAR(500) NOT NULL DEFAULT '',
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_book_slug (Slug),
    KEY idx_ebook_book_series_id (EbookSeriesId),
    KEY idx_ebook_book_status_published_at (Status, PublishedAt),
    CONSTRAINT fk_ebook_book_series
        FOREIGN KEY (EbookSeriesId)
        REFERENCES EbookSeries (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookBookAuthor (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookBookId BIGINT UNSIGNED NOT NULL,
    EbookAuthorId BIGINT UNSIGNED NOT NULL,
    DisplayOrder INT NOT NULL DEFAULT 1,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_book_author (EbookBookId, EbookAuthorId),
    KEY idx_ebook_book_author_author_id (EbookAuthorId),
    CONSTRAINT fk_ebook_book_author_book
        FOREIGN KEY (EbookBookId)
        REFERENCES EbookBook (Id),
    CONSTRAINT fk_ebook_book_author_author
        FOREIGN KEY (EbookAuthorId)
        REFERENCES EbookAuthor (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookBookGenre (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    EbookBookId BIGINT UNSIGNED NOT NULL,
    EbookGenreId BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ebook_book_genre (EbookBookId, EbookGenreId),
    KEY idx_ebook_book_genre_genre_id (EbookGenreId),
    CONSTRAINT fk_ebook_book_genre_book
        FOREIGN KEY (EbookBookId)
        REFERENCES EbookBook (Id),
    CONSTRAINT fk_ebook_book_genre_genre
        FOREIGN KEY (EbookGenreId)
        REFERENCES EbookGenre (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE EbookCatalogItem (
    BookId BIGINT UNSIGNED NOT NULL,
    BookTitle VARCHAR(255) NOT NULL,
    BookSlug VARCHAR(180) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    SeriesName VARCHAR(160) NOT NULL DEFAULT '',
    SeriesSlug VARCHAR(160) NOT NULL DEFAULT '',
    AuthorName VARCHAR(160) NOT NULL,
    AuthorSlug VARCHAR(160) NOT NULL,
    GenreName VARCHAR(120) NOT NULL,
    GenreSlug VARCHAR(120) NOT NULL,
    PublishedAt DATETIME DEFAULT NULL,
    Summary VARCHAR(600) NOT NULL DEFAULT '',
    EpubStatus VARCHAR(32) NOT NULL DEFAULT 'none',
    PrimaryEpubUrl VARCHAR(500) NOT NULL DEFAULT '',
    PRIMARY KEY (BookId),
    KEY idx_ebook_catalog_status_published_at (Status, PublishedAt),
    KEY idx_ebook_catalog_author_slug (AuthorSlug),
    KEY idx_ebook_catalog_genre_slug (GenreSlug),
    KEY idx_ebook_catalog_series_slug (SeriesSlug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO EbookSeries (Name, Slug) VALUES
    ('JSON First Guides', 'json-first-guides'),
    ('Reader Essentials', 'reader-essentials');

INSERT INTO EbookAuthor (Name, Slug) VALUES
    ('Sample Editor', 'sample-editor'),
    ('Catalog Writer', 'catalog-writer');

INSERT INTO EbookGenre (Name, Slug) VALUES
    ('Guide', 'guide'),
    ('Reference', 'reference');

INSERT INTO EbookBook (
    EbookSeriesId,
    Title,
    Slug,
    Status,
    PublishedAt,
    Summary,
    EpubStatus,
    PrimaryEpubUrl,
    UpdatedAt
) VALUES
    (
        1,
        'JSONから始める電子書籍CMS',
        'json-first-ebook-cms',
        'published',
        '2026-06-21 09:00:00',
        'JSONで考えた本棚を、AIがcatalog APIへ変換するサンプルです。',
        'planned',
        '',
        '2026-06-21 09:30:00'
    ),
    (
        2,
        '公開本棚APIの作り方',
        'public-ebook-catalog-api',
        'published',
        '2026-06-22 10:00:00',
        '公開中の本を著者、ジャンル、シリーズで絞り込むAPIの例です。',
        'available',
        '/assets/epub/json-first-mini-book/json-first-mini-book.epub',
        '2026-06-22 10:30:00'
    ),
    (
        NULL,
        '未公開の販売メモ',
        'draft-sales-note',
        'draft',
        NULL,
        '下書き本はpublic catalog APIには出しません。',
        'none',
        '',
        '2026-06-22 11:00:00'
    );

INSERT INTO EbookBookAuthor (EbookBookId, EbookAuthorId, DisplayOrder) VALUES
    (1, 1, 1),
    (2, 2, 1),
    (3, 1, 1);

INSERT INTO EbookBookGenre (EbookBookId, EbookGenreId) VALUES
    (1, 1),
    (2, 2),
    (3, 1);

INSERT INTO EbookCatalogItem (
    BookId,
    BookTitle,
    BookSlug,
    Status,
    SeriesName,
    SeriesSlug,
    AuthorName,
    AuthorSlug,
    GenreName,
    GenreSlug,
    PublishedAt,
    Summary,
    EpubStatus,
    PrimaryEpubUrl
) VALUES
    (
        1,
        'JSONから始める電子書籍CMS',
        'json-first-ebook-cms',
        'published',
        'JSON First Guides',
        'json-first-guides',
        'Sample Editor',
        'sample-editor',
        'Guide',
        'guide',
        '2026-06-21 09:00:00',
        'JSONで考えた本棚を、AIがcatalog APIへ変換するサンプルです。',
        'planned',
        ''
    ),
    (
        2,
        '公開本棚APIの作り方',
        'public-ebook-catalog-api',
        'published',
        'Reader Essentials',
        'reader-essentials',
        'Catalog Writer',
        'catalog-writer',
        'Reference',
        'reference',
        '2026-06-22 10:00:00',
        '公開中の本を著者、ジャンル、シリーズで絞り込むAPIの例です。',
        'available',
        '/assets/epub/json-first-mini-book/json-first-mini-book.epub'
    ),
    (
        3,
        '未公開の販売メモ',
        'draft-sales-note',
        'draft',
        '',
        '',
        'Sample Editor',
        'sample-editor',
        'Guide',
        'guide',
        NULL,
        '下書き本はpublic catalog APIには出しません。',
        'none',
        ''
    );

SET @sample21_project_id = NULL;
