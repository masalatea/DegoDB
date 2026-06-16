DROP TABLE IF EXISTS ExternalArticle;

CREATE TABLE ExternalArticle (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(191) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME DEFAULT NULL,
    Body TEXT NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_external_article_slug (Slug),
    KEY idx_external_article_status_published (Status, PublishedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ExternalArticle (
    Title,
    Slug,
    Status,
    PublishedAt,
    Body
) VALUES
    (
        'External source first row',
        'external-source-first-row',
        'published',
        '2026-06-16 09:00:00',
        'This row lives in db-lab and is imported through named-live-schema:sample12_lab.'
    ),
    (
        'Draft from external DB',
        'draft-from-external-db',
        'draft',
        NULL,
        'This draft row confirms nullable datetime import from an external named source.'
    );
