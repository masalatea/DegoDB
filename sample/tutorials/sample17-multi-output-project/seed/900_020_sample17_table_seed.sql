SET @sample17_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE17'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample17_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample17_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample17_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample17_project_id;

DROP TABLE IF EXISTS CapstoneTask;

CREATE TABLE CapstoneTask (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'open',
    OwnerName VARCHAR(100) NOT NULL DEFAULT '',
    Priority INT NOT NULL DEFAULT 0,
    DueDate DATE DEFAULT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    KEY idx_capstone_task_status_priority (Status, Priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO CapstoneTask (
    Title,
    Status,
    OwnerName,
    Priority,
    DueDate,
    UpdatedAt
) VALUES
    ('Publish data classes', 'open', 'Alice', 20, '2026-06-20', '2026-06-16 10:00:00'),
    ('Publish DBAccess layer', 'review', 'Bob', 30, '2026-06-21', '2026-06-16 11:30:00'),
    ('Publish HTML page', 'open', 'Chris', 10, NULL, '2026-06-16 12:00:00'),
    ('Publish OpenAPI artifact', 'done', 'Dana', 40, '2026-06-22', '2026-06-15 18:15:00');

SET @sample17_project_id = NULL;
