DROP TABLE IF EXISTS external_article;

CREATE TABLE external_article (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    body TEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_external_article_slug (slug),
    KEY idx_external_article_status_published (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO external_article (
    title,
    slug,
    status,
    published_at,
    body
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
