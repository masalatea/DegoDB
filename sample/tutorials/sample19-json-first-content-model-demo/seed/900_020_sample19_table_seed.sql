SET @sample19_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE19'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample19_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample19_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample19_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample19_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample19_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample19_project_id;

DROP TABLE IF EXISTS article_public_summary;
DROP TABLE IF EXISTS article_json_model;
DROP TABLE IF EXISTS json_category;
DROP TABLE IF EXISTS json_author;

CREATE TABLE json_author (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_json_author_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE json_category (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_json_category_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_json_model (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    json_author_id BIGINT UNSIGNED NOT NULL,
    json_category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(160) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    body TEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_article_json_model_slug (slug),
    KEY idx_article_json_model_author_id (json_author_id),
    KEY idx_article_json_model_category_id (json_category_id),
    KEY idx_article_json_model_status_published_at (status, published_at),
    CONSTRAINT fk_article_json_model_author
        FOREIGN KEY (json_author_id)
        REFERENCES json_author (id),
    CONSTRAINT fk_article_json_model_category
        FOREIGN KEY (json_category_id)
        REFERENCES json_category (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_public_summary (
    article_id BIGINT UNSIGNED NOT NULL,
    article_title VARCHAR(255) NOT NULL,
    article_slug VARCHAR(160) NOT NULL,
    published_at DATETIME NULL,
    author_name VARCHAR(255) NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO json_author (
    name
) VALUES
    ('Sample Editor'),
    ('Draft Writer');

INSERT INTO json_category (
    name
) VALUES
    ('Guide'),
    ('Internal Note');

INSERT INTO article_json_model (
    json_author_id,
    json_category_id,
    title,
    slug,
    status,
    published_at,
    body
) VALUES
    (1, 1, 'はじめての電子書籍CMS', 'first-ebook-cms', 'published', '2026-06-19 09:00:00', 'JSONから始めるCMSの例です。'),
    (2, 2, '公開前の設計メモ', 'draft-content-model-note', 'draft', NULL, '下書きは public API へ出さない。');

SET @sample19_project_id = NULL;
