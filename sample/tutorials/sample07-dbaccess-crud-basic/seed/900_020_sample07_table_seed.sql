SET @sample07_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE07'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample07_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample07_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample07_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample07_project_id;

DROP TABLE IF EXISTS todo_item;

CREATE TABLE todo_item (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    body TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO todo_item (
    title,
    status,
    body
) VALUES
    ('Prepare onboarding checklist', 'open', 'Create the first generated CRUD sample.'),
    ('Verify generated DB access output', 'done', 'Compare runtime output against durable reference files.');

SET @sample07_project_id = NULL;
