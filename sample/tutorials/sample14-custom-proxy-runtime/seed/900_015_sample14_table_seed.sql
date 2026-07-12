SET @sample14_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE14'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample14_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample14_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample14_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample14_project_id;

DROP TABLE IF EXISTS sample14_transaction_item;

CREATE TABLE sample14_transaction_item (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_key VARCHAR(100) NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sample14_transaction_item_key (transaction_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sample14_project_id = NULL;
