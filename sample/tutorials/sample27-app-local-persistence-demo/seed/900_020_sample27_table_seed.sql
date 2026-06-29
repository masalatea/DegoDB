SET @sample27_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE27'
);

DELETE fields
FROM project_shared_contract_fields AS fields
INNER JOIN project_shared_contracts AS contracts
    ON contracts.id = fields.shared_contract_id
WHERE contracts.project_id = @sample27_project_id;

DELETE FROM project_shared_contracts
WHERE project_id = @sample27_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample27_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample27_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample27_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample27_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample27_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample27_project_id;

DROP TABLE IF EXISTS app_local_task;

CREATE TABLE app_local_task (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    sort_order INT NOT NULL DEFAULT 0,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    published_at DATETIME NULL,
    note TEXT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO app_local_task (
    id,
    title,
    status,
    sort_order,
    is_pinned,
    published_at,
    note
) VALUES (
    1001,
    'Server task for App-local persistence',
    'draft',
    10,
    0,
    NULL,
    'server read fixture for sample27'
)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    status = VALUES(status),
    sort_order = VALUES(sort_order),
    is_pinned = VALUES(is_pinned),
    published_at = VALUES(published_at),
    note = VALUES(note);

SET @sample27_project_id = NULL;
