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

DROP TABLE IF EXISTS ebook_catalog_item;
DROP TABLE IF EXISTS ebook_book_genre;
DROP TABLE IF EXISTS ebook_book_author;
DROP TABLE IF EXISTS ebook_book;
DROP TABLE IF EXISTS ebook_genre;
DROP TABLE IF EXISTS ebook_author;
DROP TABLE IF EXISTS ebook_series;

CREATE TABLE ebook_series (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_series_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_author (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_author_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_genre (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_genre_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_book (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_series_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    summary VARCHAR(600) NOT NULL DEFAULT '',
    epub_status VARCHAR(32) NOT NULL DEFAULT 'none',
    primary_epub_url VARCHAR(500) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_book_slug (slug),
    KEY idx_ebook_book_series_id (ebook_series_id),
    KEY idx_ebook_book_status_published_at (status, published_at),
    CONSTRAINT fk_ebook_book_series
        FOREIGN KEY (ebook_series_id)
        REFERENCES ebook_series (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_book_author (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_book_id BIGINT UNSIGNED NOT NULL,
    ebook_author_id BIGINT UNSIGNED NOT NULL,
    display_order INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_book_author (ebook_book_id, ebook_author_id),
    KEY idx_ebook_book_author_author_id (ebook_author_id),
    CONSTRAINT fk_ebook_book_author_book
        FOREIGN KEY (ebook_book_id)
        REFERENCES ebook_book (id),
    CONSTRAINT fk_ebook_book_author_author
        FOREIGN KEY (ebook_author_id)
        REFERENCES ebook_author (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_book_genre (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ebook_book_id BIGINT UNSIGNED NOT NULL,
    ebook_genre_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ebook_book_genre (ebook_book_id, ebook_genre_id),
    KEY idx_ebook_book_genre_genre_id (ebook_genre_id),
    CONSTRAINT fk_ebook_book_genre_book
        FOREIGN KEY (ebook_book_id)
        REFERENCES ebook_book (id),
    CONSTRAINT fk_ebook_book_genre_genre
        FOREIGN KEY (ebook_genre_id)
        REFERENCES ebook_genre (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ebook_catalog_item (
    book_id BIGINT UNSIGNED NOT NULL,
    book_title VARCHAR(255) NOT NULL,
    book_slug VARCHAR(180) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    series_name VARCHAR(160) NOT NULL DEFAULT '',
    series_slug VARCHAR(160) NOT NULL DEFAULT '',
    author_name VARCHAR(160) NOT NULL,
    author_slug VARCHAR(160) NOT NULL,
    genre_name VARCHAR(120) NOT NULL,
    genre_slug VARCHAR(120) NOT NULL,
    published_at DATETIME DEFAULT NULL,
    summary VARCHAR(600) NOT NULL DEFAULT '',
    epub_status VARCHAR(32) NOT NULL DEFAULT 'none',
    primary_epub_url VARCHAR(500) NOT NULL DEFAULT '',
    PRIMARY KEY (book_id),
    KEY idx_ebook_catalog_status_published_at (status, published_at),
    KEY idx_ebook_catalog_author_slug (author_slug),
    KEY idx_ebook_catalog_genre_slug (genre_slug),
    KEY idx_ebook_catalog_series_slug (series_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ebook_series (name, slug) VALUES
    ('JSON First Guides', 'json-first-guides'),
    ('Reader Essentials', 'reader-essentials');

INSERT INTO ebook_author (name, slug) VALUES
    ('Sample Editor', 'sample-editor'),
    ('Catalog Writer', 'catalog-writer');

INSERT INTO ebook_genre (name, slug) VALUES
    ('Guide', 'guide'),
    ('Reference', 'reference');

INSERT INTO ebook_book (
    ebook_series_id,
    title,
    slug,
    status,
    published_at,
    summary,
    epub_status,
    primary_epub_url,
    updated_at
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

INSERT INTO ebook_book_author (ebook_book_id, ebook_author_id, display_order) VALUES
    (1, 1, 1),
    (2, 2, 1),
    (3, 1, 1);

INSERT INTO ebook_book_genre (ebook_book_id, ebook_genre_id) VALUES
    (1, 1),
    (2, 2),
    (3, 1);

INSERT INTO ebook_catalog_item (
    book_id,
    book_title,
    book_slug,
    status,
    series_name,
    series_slug,
    author_name,
    author_slug,
    genre_name,
    genre_slug,
    published_at,
    summary,
    epub_status,
    primary_epub_url
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
