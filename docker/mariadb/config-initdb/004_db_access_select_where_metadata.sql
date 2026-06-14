CREATE TABLE IF NOT EXISTS project_db_access_function_select_wheres (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    db_access_function_id BIGINT UNSIGNED NOT NULL,
    target_table_name VARCHAR(191) NOT NULL DEFAULT '',
    target_table_alias_name VARCHAR(191) NOT NULL DEFAULT '',
    target_table_column_name VARCHAR(191) NOT NULL DEFAULT '',
    parameter_type VARCHAR(32) NOT NULL DEFAULT '',
    parameter_data_type VARCHAR(32) NOT NULL DEFAULT '',
    fixed_parameter TEXT NOT NULL,
    another_table_name VARCHAR(191) NOT NULL DEFAULT '',
    another_table_alias_name VARCHAR(191) NOT NULL DEFAULT '',
    another_field_name VARCHAR(191) NOT NULL DEFAULT '',
    join_type VARCHAR(32) NOT NULL DEFAULT '',
    or_group VARCHAR(64) NOT NULL DEFAULT '',
    relational_operator VARCHAR(32) NOT NULL DEFAULT '=',
    where_order INT UNSIGNED NOT NULL DEFAULT 0,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_project_db_access_function_select_wheres_function_order (db_access_function_id, where_order, id),
    CONSTRAINT fk_project_db_access_function_select_wheres_function
        FOREIGN KEY (db_access_function_id) REFERENCES project_db_access_functions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
