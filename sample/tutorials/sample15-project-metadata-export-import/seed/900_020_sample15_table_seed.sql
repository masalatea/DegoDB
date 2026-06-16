SET @sample15_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE15'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample15_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample15_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample15_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample15_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample15_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample15_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample15_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample15_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample15_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample15_project_id;

DROP TABLE IF EXISTS BundleNote;

CREATE TABLE BundleNote (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Body TEXT NOT NULL,
    UpdatedAt DATETIME NOT NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO BundleNote (
    Title,
    Body,
    UpdatedAt
) VALUES
    ('Bundle Export', 'Capture project-core metadata as a transportable bundle.', '2026-06-16 09:00:00'),
    ('Bundle Import', 'Preview before applying the bundle to a target project.', '2026-06-16 09:10:00');

SET @sample15_project_id = NULL;
