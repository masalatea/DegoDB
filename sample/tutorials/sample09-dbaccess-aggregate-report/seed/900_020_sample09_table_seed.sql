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

DROP TABLE IF EXISTS SalesCategoryReport;
DROP TABLE IF EXISTS SalesRecord;
DROP TABLE IF EXISTS SalesCategory;

CREATE TABLE SalesCategory (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    IsActive TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE SalesRecord (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    SalesCategoryId BIGINT UNSIGNED NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'open',
    Amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (Id),
    KEY idx_sales_record_category_id (SalesCategoryId),
    CONSTRAINT fk_sales_record_category
        FOREIGN KEY (SalesCategoryId)
        REFERENCES SalesCategory (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE SalesCategoryReport (
    SalesCategoryId BIGINT UNSIGNED NOT NULL,
    SalesCategoryName VARCHAR(255) NOT NULL,
    ClosedSaleCount INT UNSIGNED NOT NULL,
    ClosedSaleTotalAmount DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (SalesCategoryId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO SalesCategory (
    Name,
    IsActive
) VALUES
    ('Hardware', 1),
    ('Software', 1),
    ('Legacy', 0);

INSERT INTO SalesRecord (
    SalesCategoryId,
    Title,
    Status,
    Amount
) VALUES
    (1, 'Keyboard refresh', 'closed', 120.00),
    (1, 'Mouse replacement', 'closed', 80.00),
    (2, 'License renewal', 'closed', 40.00),
    (2, 'Future subscription', 'open', 500.00),
    (3, 'Unsupported backlog', 'closed', 999.00);

SET @sample09_project_id = NULL;
