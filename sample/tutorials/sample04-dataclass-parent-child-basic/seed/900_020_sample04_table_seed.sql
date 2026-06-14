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

DROP TABLE IF EXISTS PostComment;
DROP TABLE IF EXISTS Post;

CREATE TABLE Post (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    PublishedAt DATETIME NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PostComment (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    PostId BIGINT UNSIGNED NOT NULL,
    AuthorName VARCHAR(100) NOT NULL,
    Body TEXT NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    KEY idx_postcomment_postid (PostId),
    CONSTRAINT fk_postcomment_post
        FOREIGN KEY (PostId)
        REFERENCES Post (Id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO Post (
    Title,
    Status,
    PublishedAt
) VALUES (
    'Welcome',
    'published',
    '2026-05-22 09:00:00'
);

INSERT INTO PostComment (
    PostId,
    AuthorName,
    Body,
    SortOrder
) VALUES
    (1, 'Alice', 'First comment', 10),
    (1, 'Bob', 'Follow-up comment', 20);

SET @sample04_project_id = NULL;
