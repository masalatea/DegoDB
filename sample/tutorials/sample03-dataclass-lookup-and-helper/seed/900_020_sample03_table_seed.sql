SET @sample03_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE03'
);

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample03_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample03_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample03_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample03_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample03_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample03_project_id;

DROP TABLE IF EXISTS TaskPriority;
DROP TABLE IF EXISTS TaskStatus;

CREATE TABLE TaskStatus (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    StatusKey VARCHAR(40) NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Caption VARCHAR(100) NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    IsClosed TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_taskstatus_statuskey (StatusKey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE TaskPriority (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    PriorityKey VARCHAR(40) NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Caption VARCHAR(100) NOT NULL,
    SortOrder INT NOT NULL DEFAULT 0,
    Weight INT NOT NULL DEFAULT 0,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_taskpriority_prioritykey (PriorityKey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO TaskStatus (
    StatusKey,
    Name,
    Caption,
    SortOrder,
    IsClosed
) VALUES
    ('draft', 'Draft', 'Draft', 10, 0),
    ('ready', 'Ready', 'Ready', 20, 0),
    ('done', 'Done', 'Done', 30, 1);

INSERT INTO TaskPriority (
    PriorityKey,
    Name,
    Caption,
    SortOrder,
    Weight
) VALUES
    ('low', 'Low', 'Low', 10, 10),
    ('normal', 'Normal', 'Normal', 20, 20),
    ('high', 'High', 'High', 30, 30);

SET @sample03_project_id = NULL;
