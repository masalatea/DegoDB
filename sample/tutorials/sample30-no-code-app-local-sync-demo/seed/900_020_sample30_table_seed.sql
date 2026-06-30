SET @sample30_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE30'
);

DELETE FROM project_managed_operation_sync_outbox
WHERE project_id = @sample30_project_id;

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample30_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample30_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample30_project_id;

DELETE FROM project_shared_contracts
WHERE project_id = @sample30_project_id;

DELETE FROM project_source_outputs
WHERE project_id = @sample30_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample30_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample30_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample30_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample30_project_id;

DROP TABLE IF EXISTS sync_task;

CREATE TABLE sync_task (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    note TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sync_task (
    id,
    title,
    status,
    note
) VALUES (
    3001,
    'App-local sync no-code task',
    'draft',
    'Before no-code sync handoff.'
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    status = VALUES(status),
    note = VALUES(note);

SET @sample30_project_id = NULL;
