SET @sample08_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE08'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample08_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample08_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample08_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample08_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample08_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample08_project_id;

DROP TABLE IF EXISTS blog_post_author_summary;
DROP TABLE IF EXISTS blog_post;
DROP TABLE IF EXISTS blog_author;

CREATE TABLE blog_author (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_post (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blog_author_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PRIMARY KEY (id),
    KEY idx_blog_post_author_id (blog_author_id),
    CONSTRAINT fk_blog_post_author
        FOREIGN KEY (blog_author_id)
        REFERENCES blog_author (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_post_author_summary (
    blog_post_id BIGINT UNSIGNED NOT NULL,
    blog_post_title VARCHAR(255) NOT NULL,
    blog_author_id BIGINT UNSIGNED NOT NULL,
    blog_author_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (blog_post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO blog_author (
    name,
    is_active
) VALUES
    ('Alice Editor', 1),
    ('Bob Archived', 0),
    ('Carol Writer', 1);

INSERT INTO blog_post (
    blog_author_id,
    title,
    status
) VALUES
    (1, 'Canonical Join Tutorial', 'published'),
    (2, 'Inactive Author Should Not Appear', 'published'),
    (3, 'Draft Posts Stay Hidden', 'draft'),
    (3, 'Roadmap Checkpoint', 'published');

SET @sample08_project_id = NULL;
