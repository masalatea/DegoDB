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

DROP TABLE IF EXISTS article;

CREATE TABLE article (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sample1_project_id = NULL;
