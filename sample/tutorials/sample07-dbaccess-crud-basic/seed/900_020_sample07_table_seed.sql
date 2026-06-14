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

DROP TABLE IF EXISTS TodoItem;

CREATE TABLE TodoItem (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'open',
    Body TEXT NOT NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO TodoItem (
    Title,
    Status,
    Body
) VALUES
    ('Prepare onboarding checklist', 'open', 'Create the first generated CRUD sample.'),
    ('Verify generated DB access output', 'done', 'Compare runtime output against durable reference files.');

SET @sample07_project_id = NULL;
