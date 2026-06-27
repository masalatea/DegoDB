SET @sample20_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE20'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample20_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample20_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample20_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample20_project_id;

DROP TABLE IF EXISTS content_article;

CREATE TABLE content_article (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    category_name VARCHAR(120) NOT NULL DEFAULT '',
    author_name VARCHAR(120) NOT NULL DEFAULT '',
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    summary VARCHAR(500) NOT NULL DEFAULT '',
    body TEXT NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_content_article_slug (slug),
    KEY idx_content_article_status_published_at (status, published_at),
    KEY idx_content_article_category (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO content_article (
    title,
    slug,
    category_name,
    author_name,
    status,
    published_at,
    summary,
    body,
    updated_at
) VALUES
    (
        'JSONから始めるCMS',
        'json-first-cms',
        'Guide',
        'Sample Editor',
        'published',
        '2026-06-19 09:00:00',
        'DBを知らないユーザーがJSONから始めるCMSサンプルです。',
        'JSONで見立てた内容を、AIが管理しやすいDB/API構造へ変換します。',
        '2026-06-19 09:30:00'
    ),
    (
        '公開済み記事の読み方',
        'published-article-reader',
        'Guide',
        'Sample Editor',
        'published',
        '2026-06-20 10:00:00',
        '公開済み記事だけを一覧と詳細に出すサンプルです。',
        '下書き記事はpublic APIやHTML readerには出さず、公開済みだけを読者に見せます。',
        '2026-06-20 10:30:00'
    ),
    (
        '編集メモ',
        'draft-editor-note',
        'Internal',
        'Sample Editor',
        'draft',
        NULL,
        'この行はpublic listには出しません。',
        '下書きは後続のeditor workflow sampleで扱います。',
        '2026-06-20 11:00:00'
    );

SET @sample20_project_id = NULL;
