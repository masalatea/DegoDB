SET @sample03_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE03'
);

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample03_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample03_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample03_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample03_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample03_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample03_project_id;

DROP TABLE IF EXISTS task_priority;
DROP TABLE IF EXISTS task_status;

CREATE TABLE task_status (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    status_key VARCHAR(40) NOT NULL,
    name VARCHAR(100) NOT NULL,
    caption VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_closed TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_task_status_status_key (status_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_priority (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    priority_key VARCHAR(40) NOT NULL,
    name VARCHAR(100) NOT NULL,
    caption VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    weight INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_task_priority_priority_key (priority_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO task_status (
    status_key,
    name,
    caption,
    sort_order,
    is_closed
) VALUES
    ('draft', 'Draft', 'Draft', 10, 0),
    ('ready', 'Ready', 'Ready', 20, 0),
    ('done', 'Done', 'Done', 30, 1);

INSERT INTO task_priority (
    priority_key,
    name,
    caption,
    sort_order,
    weight
) VALUES
    ('low', 'Low', 'Low', 10, 10),
    ('normal', 'Normal', 'Normal', 20, 20),
    ('high', 'High', 'High', 30, 30);

SET @sample03_project_id = NULL;
