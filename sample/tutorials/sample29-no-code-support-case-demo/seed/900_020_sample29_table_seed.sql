SET @sample29_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE29'
);

DELETE fields
FROM project_managed_operation_fields AS fields
INNER JOIN project_managed_operations AS operations
    ON operations.id = fields.managed_operation_id
WHERE operations.project_id = @sample29_project_id;

DELETE FROM project_managed_operations
WHERE project_id = @sample29_project_id;

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample29_project_id;

DELETE FROM project_shared_contracts
WHERE project_id = @sample29_project_id;

DELETE FROM project_source_outputs
WHERE project_id = @sample29_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample29_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample29_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample29_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample29_project_id;

DROP TABLE IF EXISTS support_case;

CREATE TABLE support_case (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    case_number VARCHAR(40) NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_tier VARCHAR(40) NOT NULL DEFAULT 'standard',
    subject VARCHAR(255) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    severity VARCHAR(40) NOT NULL DEFAULT 'medium',
    next_action TEXT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_support_case_number (case_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO support_case (
    id,
    case_number,
    customer_name,
    customer_tier,
    subject,
    status,
    severity,
    next_action
) VALUES (
    2001,
    'CASE-2026-0001',
    'Northwind Field Team',
    'enterprise',
    'Onboarding data import review',
    'triage',
    'high',
    'Confirm imported customer fields and prepare a generated follow-up workflow.'
)
ON DUPLICATE KEY UPDATE
    case_number = VALUES(case_number),
    customer_name = VALUES(customer_name),
    customer_tier = VALUES(customer_tier),
    subject = VALUES(subject),
    status = VALUES(status),
    severity = VALUES(severity),
    next_action = VALUES(next_action);

SET @sample29_project_id = NULL;
