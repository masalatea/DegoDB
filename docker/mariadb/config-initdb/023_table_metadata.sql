CREATE TABLE IF NOT EXISTS dbtable (
    ProjectPID BIGINT UNSIGNED NOT NULL,
    PID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (PID),
    UNIQUE KEY uq_dbtable_project_name (ProjectPID, name),
    KEY idx_dbtable_name (name),
    CONSTRAINT fk_dbtable_project
        FOREIGN KEY (ProjectPID) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dbtablecolumns (
    ProjectPID BIGINT UNSIGNED NOT NULL,
    dbtablePID BIGINT UNSIGNED NOT NULL,
    PID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    datatype VARCHAR(255) NOT NULL,
    IsNull VARCHAR(255) NOT NULL,
    IsKey VARCHAR(255) NOT NULL,
    IsDefault VARCHAR(255) NOT NULL,
    Extra VARCHAR(255) NOT NULL,
    ColumnListOrder INT NOT NULL DEFAULT 99999999,
    memo VARCHAR(255) NOT NULL,
    PRIMARY KEY (PID),
    UNIQUE KEY uq_dbtablecolumns_project_table_name (ProjectPID, dbtablePID, name),
    KEY idx_dbtablecolumns_project_table (ProjectPID, dbtablePID),
    KEY idx_dbtablecolumns_project_table_order (ProjectPID, dbtablePID, ColumnListOrder, PID),
    CONSTRAINT fk_dbtablecolumns_project
        FOREIGN KEY (ProjectPID) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_dbtablecolumns_table
        FOREIGN KEY (dbtablePID) REFERENCES dbtable(PID)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
