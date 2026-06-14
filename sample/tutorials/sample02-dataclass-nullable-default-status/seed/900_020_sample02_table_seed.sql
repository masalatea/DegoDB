SET @sample02_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE02'
);

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample02_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample02_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample02_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample02_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample02_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample02_project_id;

DROP TABLE IF EXISTS Task;

CREATE TABLE Task (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'draft',
    SortOrder INT NOT NULL DEFAULT 0,
    IsPinned TINYINT(1) NOT NULL DEFAULT 0,
    PublishedAt DATETIME NULL,
    Note TEXT NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sample02_project_id = NULL;
