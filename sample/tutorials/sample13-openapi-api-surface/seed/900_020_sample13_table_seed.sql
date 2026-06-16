SET @sample13_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE13'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample13_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample13_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample13_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample13_project_id;

DROP TABLE IF EXISTS ApiTask;

CREATE TABLE ApiTask (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'open',
    OwnerName VARCHAR(100) NOT NULL DEFAULT '',
    DueDate DATE DEFAULT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    KEY idx_api_task_status_due_date (Status, DueDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ApiTask (
    Title,
    Status,
    OwnerName,
    DueDate,
    UpdatedAt
) VALUES
    ('Expose list endpoint', 'open', 'Alice', '2026-06-20', '2026-06-16 10:00:00'),
    ('Review OpenAPI artifact', 'review', 'Bob', '2026-06-21', '2026-06-16 11:30:00'),
    ('Publish API surface docs', 'done', 'Chris', NULL, '2026-06-15 18:15:00');

SET @sample13_project_id = NULL;
