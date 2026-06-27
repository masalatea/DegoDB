SET @sample09_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE09'
);

DELETE targets
FROM project_db_access_function_source_output_targets AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE targets
FROM project_db_access_function_select_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE wheres
FROM project_db_access_function_select_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE havings
FROM project_db_access_function_select_havings AS havings
INNER JOIN project_db_access_functions AS functions
    ON functions.id = havings.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE targets
FROM project_db_access_function_insert_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE targets
FROM project_db_access_function_update_target_fields AS targets
INNER JOIN project_db_access_functions AS functions
    ON functions.id = targets.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE wheres
FROM project_db_access_function_update_delete_wheres AS wheres
INNER JOIN project_db_access_functions AS functions
    ON functions.id = wheres.db_access_function_id
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE functions
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample09_project_id;

DELETE FROM project_db_access_classes
WHERE project_id = @sample09_project_id;

DELETE FROM dataclassfields
WHERE ProjectPID = @sample09_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample09_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample09_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample09_project_id;

DROP TABLE IF EXISTS sales_category_report;
DROP TABLE IF EXISTS sales_record;
DROP TABLE IF EXISTS sales_category;

CREATE TABLE sales_category (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales_record (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sales_category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id),
    KEY idx_sales_record_category_id (sales_category_id),
    CONSTRAINT fk_sales_record_category
        FOREIGN KEY (sales_category_id)
        REFERENCES sales_category (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales_category_report (
    sales_category_id BIGINT UNSIGNED NOT NULL,
    sales_category_name VARCHAR(255) NOT NULL,
    closed_sale_count INT UNSIGNED NOT NULL,
    closed_sale_total_amount DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (sales_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sales_category (
    name,
    is_active
) VALUES
    ('Hardware', 1),
    ('Software', 1),
    ('Legacy', 0);

INSERT INTO sales_record (
    sales_category_id,
    title,
    status,
    amount
) VALUES
    (1, 'Keyboard refresh', 'closed', 120.00),
    (1, 'Mouse replacement', 'closed', 80.00),
    (2, 'License renewal', 'closed', 40.00),
    (2, 'Future subscription', 'open', 500.00),
    (3, 'Unsupported backlog', 'closed', 999.00);

SET @sample09_project_id = NULL;
