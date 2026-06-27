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

DROP TABLE IF EXISTS task_card;

CREATE TABLE task_card (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'todo',
    assigned_to VARCHAR(100) NOT NULL DEFAULT '',
    priority INT NOT NULL DEFAULT 0,
    due_date DATE DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_task_card_status_due_date (status, due_date),
    KEY idx_task_card_assigned_to (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO task_card (
    title,
    body,
    status,
    assigned_to,
    priority,
    due_date,
    completed_at,
    updated_at
) VALUES
    ('Define first demo prompt', 'Turn the raw idea into a readable sample prompt and scope.', 'doing', 'Alice', 30, '2026-06-19', NULL, '2026-06-19 09:00:00'),
    ('Create TaskCard metadata', 'Seed table, DataClass, DBAccess, HTML, and OpenAPI output definitions.', 'todo', 'Bob', 20, '2026-06-20', NULL, '2026-06-19 09:30:00'),
    ('Publish reference outputs', 'Run the sample pack and capture actual generated outputs.', 'todo', 'Chris', 10, '2026-06-21', NULL, '2026-06-19 10:00:00'),
    ('Review demo feedback notes', 'Record any runtime or generator gaps discovered while making the demo.', 'done', 'Dana', 40, '2026-06-18', '2026-06-19 08:30:00', '2026-06-19 08:30:00');

SET @sample18_project_id = NULL;
