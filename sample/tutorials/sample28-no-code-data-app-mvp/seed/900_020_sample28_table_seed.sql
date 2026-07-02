SET @sample28_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE28'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample28_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample28_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample28_project_id;

DELETE FROM project_shared_contracts
WHERE project_id = @sample28_project_id;

DELETE FROM project_source_outputs
WHERE project_id = @sample28_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample28_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample28_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample28_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample28_project_id;

DROP TABLE IF EXISTS no_code_ticket;

CREATE TABLE no_code_ticket (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    priority INT NOT NULL DEFAULT 0,
    body TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO no_code_ticket (
    id,
    title,
    status,
    priority,
    body
) VALUES (
    1001,
    'First no-code app ticket',
    'open',
    10,
    'This row is the first sample28 data-first no-code app fixture.'
), (
    1002,
    'Review generated customer fields',
    'triage',
    20,
    'Confirm imported fields before exposing the generated no-code preview to operators.'
), (
    1003,
    'Prepare approval handoff',
    'ready',
    30,
    'Use the publish candidate workflow to review and approve the generated runtime.'
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    status = VALUES(status),
    priority = VALUES(priority),
    body = VALUES(body);

SET @sample28_project_id = NULL;
