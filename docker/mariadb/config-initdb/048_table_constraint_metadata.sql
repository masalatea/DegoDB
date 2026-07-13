CREATE TABLE IF NOT EXISTS project_table_keys (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    table_pid BIGINT UNSIGNED NOT NULL,
    key_name VARCHAR(191) NOT NULL,
    key_kind VARCHAR(32) NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_table_keys_name (project_id, table_pid, key_name),
    CONSTRAINT fk_project_table_keys_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_keys_table FOREIGN KEY (table_pid) REFERENCES dbtable(PID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_table_key_columns (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    table_key_id BIGINT UNSIGNED NOT NULL,
    column_pid BIGINT UNSIGNED NOT NULL,
    ordinal_position INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_table_key_columns_ordinal (project_id, table_key_id, ordinal_position),
    UNIQUE KEY uq_project_table_key_columns_column (project_id, table_key_id, column_pid),
    CONSTRAINT fk_project_table_key_columns_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_key_columns_key FOREIGN KEY (table_key_id) REFERENCES project_table_keys(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_key_columns_column FOREIGN KEY (column_pid) REFERENCES dbtablecolumns(PID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_table_foreign_keys (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    table_pid BIGINT UNSIGNED NOT NULL,
    constraint_name VARCHAR(191) NOT NULL,
    referenced_table_pid BIGINT UNSIGNED NOT NULL,
    on_update_action VARCHAR(32) NOT NULL DEFAULT 'NO ACTION',
    on_delete_action VARCHAR(32) NOT NULL DEFAULT 'NO ACTION',
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_table_foreign_keys_name (project_id, table_pid, constraint_name),
    CONSTRAINT fk_project_table_foreign_keys_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_foreign_keys_table FOREIGN KEY (table_pid) REFERENCES dbtable(PID) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_foreign_keys_ref_table FOREIGN KEY (referenced_table_pid) REFERENCES dbtable(PID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_table_foreign_key_columns (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    foreign_key_id BIGINT UNSIGNED NOT NULL,
    column_pid BIGINT UNSIGNED NOT NULL,
    referenced_column_pid BIGINT UNSIGNED NOT NULL,
    ordinal_position INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_table_fk_columns_ordinal (project_id, foreign_key_id, ordinal_position),
    UNIQUE KEY uq_project_table_fk_columns_column (project_id, foreign_key_id, column_pid),
    CONSTRAINT fk_project_table_fk_columns_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_fk_columns_fk FOREIGN KEY (foreign_key_id) REFERENCES project_table_foreign_keys(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_fk_columns_column FOREIGN KEY (column_pid) REFERENCES dbtablecolumns(PID) ON DELETE CASCADE,
    CONSTRAINT fk_project_table_fk_columns_ref_column FOREIGN KEY (referenced_column_pid) REFERENCES dbtablecolumns(PID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
