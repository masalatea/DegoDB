SET @sample10_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE10'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample10_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample10_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample10_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample10_project_id;

DROP TABLE IF EXISTS support_ticket;

CREATE TABLE support_ticket (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    assigned_to VARCHAR(100) NOT NULL DEFAULT '',
    body TEXT NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO support_ticket (
    title,
    status,
    assigned_to,
    body,
    updated_at
) VALUES
    ('Seed runtime sample', 'open', 'Alice', 'Prepare the sample10 tutorial pack and verify the minimal CRUD flow.', '2026-05-22 09:00:00'),
    ('Verify tutorial output', 'in_progress', 'Bob', 'Diff runtime output against the durable reference files after publish.', '2026-05-22 11:30:00'),
    ('Archive stale sample docs', 'done', '', 'Close redundant sample paths after the catalog cleanup is verified.', '2026-05-21 18:15:00');

SET @sample10_project_id = NULL;
