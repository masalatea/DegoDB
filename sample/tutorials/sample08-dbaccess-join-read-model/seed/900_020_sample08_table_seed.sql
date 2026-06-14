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

DROP TABLE IF EXISTS BlogPostAuthorSummary;
DROP TABLE IF EXISTS BlogPost;
DROP TABLE IF EXISTS BlogAuthor;

CREATE TABLE BlogAuthor (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    IsActive TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE BlogPost (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    BlogAuthorId BIGINT UNSIGNED NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PRIMARY KEY (Id),
    KEY idx_blog_post_author_id (BlogAuthorId),
    CONSTRAINT fk_blog_post_author
        FOREIGN KEY (BlogAuthorId)
        REFERENCES BlogAuthor (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE BlogPostAuthorSummary (
    BlogPostId BIGINT UNSIGNED NOT NULL,
    BlogPostTitle VARCHAR(255) NOT NULL,
    BlogAuthorId BIGINT UNSIGNED NOT NULL,
    BlogAuthorName VARCHAR(255) NOT NULL,
    PRIMARY KEY (BlogPostId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO BlogAuthor (
    Name,
    IsActive
) VALUES
    ('Alice Editor', 1),
    ('Bob Archived', 0),
    ('Carol Writer', 1);

INSERT INTO BlogPost (
    BlogAuthorId,
    Title,
    Status
) VALUES
    (1, 'Canonical Join Tutorial', 'published'),
    (2, 'Inactive Author Should Not Appear', 'published'),
    (3, 'Draft Posts Stay Hidden', 'draft'),
    (3, 'Roadmap Checkpoint', 'published');

SET @sample08_project_id = NULL;
