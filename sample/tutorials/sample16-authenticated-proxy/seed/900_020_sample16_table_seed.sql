SET @sample16_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE16'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample16_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample16_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample16_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample16_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample16_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample16_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample16_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample16_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample16_project_id;

DROP TABLE IF EXISTS AuthTask;

CREATE TABLE AuthTask (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(32) NOT NULL,
    OwnerName VARCHAR(128) NOT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO AuthTask (
    Title,
    Status,
    OwnerName,
    UpdatedAt
) VALUES
    ('Token protected task', 'open', 'Admin', '2026-06-16 10:00:00'),
    ('Closed token task', 'closed', 'Admin', '2026-06-16 10:05:00');

SET @sample16_project_id = NULL;
