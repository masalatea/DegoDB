SET @sample1_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE1'
);

DELETE FROM dataclassfields
WHERE ProjectPID = @sample1_project_id;

DELETE FROM dataclass
WHERE ProjectPID = @sample1_project_id;

DELETE FROM dbtablecolumns
WHERE ProjectPID = @sample1_project_id;

DELETE FROM dbtable
WHERE ProjectPID = @sample1_project_id;

DROP TABLE IF EXISTS Article;

CREATE TABLE Article (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Body TEXT NOT NULL,
    PRIMARY KEY (Id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sample1_project_id = NULL;
