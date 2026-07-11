SET @sample32_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE32'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample32_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample32_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample32_project_id;

DELETE FROM project_shared_contracts
WHERE project_id = @sample32_project_id;

DELETE FROM project_source_outputs
WHERE project_id = @sample32_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample32_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample32_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample32_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample32_project_id;

DROP TABLE IF EXISTS no_code_lab_card;

CREATE TABLE no_code_lab_card (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(120) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    owner_name VARCHAR(120) NOT NULL,
    priority INT NOT NULL DEFAULT 0,
    due_on DATE DEFAULT NULL,
    notes TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO no_code_lab_card (
    id,
    title,
    status,
    owner_name,
    priority,
    due_on,
    notes
) VALUES (
    3201,
    'Fixture list card',
    'draft',
    'No Code Lab',
    10,
    '2026-07-20',
    'First fixture row for fast no-code UI contract checks.'
), (
    3202,
    'Fixture detail card',
    'ready',
    'Contract Runner',
    20,
    '2026-07-21',
    'Second fixture row used by detail and form preview assertions.'
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    status = VALUES(status),
    owner_name = VALUES(owner_name),
    priority = VALUES(priority),
    due_on = VALUES(due_on),
    notes = VALUES(notes);

SET @sample32_project_id = NULL;
