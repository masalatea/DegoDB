SET @sample04_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE04'
);

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample04_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample04_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample04_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample04_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample04_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample04_project_id;

DROP TABLE IF EXISTS post_comment;
DROP TABLE IF EXISTS post;

CREATE TABLE post (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE post_comment (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_post_comment_post_id (post_id),
    CONSTRAINT fk_post_comment_post
        FOREIGN KEY (post_id)
        REFERENCES post (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO post (
    title,
    status,
    published_at
) VALUES (
    'Welcome',
    'published',
    '2026-05-22 09:00:00'
);

INSERT INTO post_comment (
    post_id,
    author_name,
    body,
    sort_order
) VALUES
    (1, 'Alice', 'First comment', 10),
    (1, 'Bob', 'Follow-up comment', 20);

SET @sample04_project_id = NULL;
