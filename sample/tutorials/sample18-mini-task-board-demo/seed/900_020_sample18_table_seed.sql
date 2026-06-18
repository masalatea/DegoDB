SET @sample18_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE18'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample18_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample18_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample18_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample18_project_id;

DROP TABLE IF EXISTS TaskCard;

CREATE TABLE TaskCard (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Body TEXT NOT NULL,
    Status VARCHAR(32) NOT NULL DEFAULT 'todo',
    AssignedTo VARCHAR(100) NOT NULL DEFAULT '',
    Priority INT NOT NULL DEFAULT 0,
    DueDate DATE DEFAULT NULL,
    CompletedAt DATETIME DEFAULT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id),
    KEY idx_task_card_status_due_date (Status, DueDate),
    KEY idx_task_card_assigned_to (AssignedTo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO TaskCard (
    Title,
    Body,
    Status,
    AssignedTo,
    Priority,
    DueDate,
    CompletedAt,
    UpdatedAt
) VALUES
    ('Define first demo prompt', 'Turn the raw idea into a readable sample prompt and scope.', 'doing', 'Alice', 30, '2026-06-19', NULL, '2026-06-19 09:00:00'),
    ('Create TaskCard metadata', 'Seed table, DataClass, DBAccess, HTML, and OpenAPI output definitions.', 'todo', 'Bob', 20, '2026-06-20', NULL, '2026-06-19 09:30:00'),
    ('Publish reference outputs', 'Run the sample pack and capture actual generated outputs.', 'todo', 'Chris', 10, '2026-06-21', NULL, '2026-06-19 10:00:00'),
    ('Review demo feedback notes', 'Record any runtime or generator gaps discovered while making the demo.', 'done', 'Dana', 40, '2026-06-18', '2026-06-19 08:30:00', '2026-06-19 08:30:00');

SET @sample18_project_id = NULL;

