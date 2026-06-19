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

DROP TABLE IF EXISTS ArticlePublicSummary;
DROP TABLE IF EXISTS ArticleJsonModel;
DROP TABLE IF EXISTS JsonCategory;
DROP TABLE IF EXISTS JsonAuthor;

CREATE TABLE JsonAuthor (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_json_author_name (Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE JsonCategory (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_json_category_name (Name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ArticleJsonModel (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    JsonAuthorId BIGINT UNSIGNED NOT NULL,
    JsonCategoryId BIGINT UNSIGNED NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Slug VARCHAR(160) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME NULL,
    Body TEXT NOT NULL,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_article_json_model_slug (Slug),
    KEY idx_article_json_model_author_id (JsonAuthorId),
    KEY idx_article_json_model_category_id (JsonCategoryId),
    KEY idx_article_json_model_status_published_at (Status, PublishedAt),
    CONSTRAINT fk_article_json_model_author
        FOREIGN KEY (JsonAuthorId)
        REFERENCES JsonAuthor (Id),
    CONSTRAINT fk_article_json_model_category
        FOREIGN KEY (JsonCategoryId)
        REFERENCES JsonCategory (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ArticlePublicSummary (
    ArticleId BIGINT UNSIGNED NOT NULL,
    ArticleTitle VARCHAR(255) NOT NULL,
    ArticleSlug VARCHAR(160) NOT NULL,
    PublishedAt DATETIME NULL,
    AuthorName VARCHAR(255) NOT NULL,
    CategoryName VARCHAR(255) NOT NULL,
    PRIMARY KEY (ArticleId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO JsonAuthor (
    Name
) VALUES
    ('Sample Editor'),
    ('Draft Writer');

INSERT INTO JsonCategory (
    Name
) VALUES
    ('Guide'),
    ('Internal Note');

INSERT INTO ArticleJsonModel (
    JsonAuthorId,
    JsonCategoryId,
    Title,
    Slug,
    Status,
    PublishedAt,
    Body
) VALUES
    (1, 1, 'はじめての電子書籍CMS', 'first-ebook-cms', 'published', '2026-06-19 09:00:00', 'JSONから始めるCMSの例です。'),
    (2, 2, '公開前の設計メモ', 'draft-content-model-note', 'draft', NULL, '下書きは public API へ出さない。');

SET @sample19_project_id = NULL;
