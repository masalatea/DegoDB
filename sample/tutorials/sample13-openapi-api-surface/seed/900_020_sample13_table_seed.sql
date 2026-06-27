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

DROP TABLE IF EXISTS api_task;

CREATE TABLE api_task (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'open',
    owner_name VARCHAR(100) NOT NULL DEFAULT '',
    due_date DATE DEFAULT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_api_task_status_due_date (status, due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO api_task (
    title,
    status,
    owner_name,
    due_date,
    updated_at
) VALUES
    ('Expose list endpoint', 'open', 'Alice', '2026-06-20', '2026-06-16 10:00:00'),
    ('Review OpenAPI artifact', 'review', 'Bob', '2026-06-21', '2026-06-16 11:30:00'),
    ('Publish API surface docs', 'done', 'Chris', NULL, '2026-06-15 18:15:00');

SET @sample13_project_id = NULL;
