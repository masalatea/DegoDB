CREATE TABLE IF NOT EXISTS project_db_access_function_insert_target_fields (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    db_access_function_id BIGINT UNSIGNED NOT NULL,
    target_table_column_name VARCHAR(191) NOT NULL DEFAULT '',
    parameter_type VARCHAR(32) NOT NULL DEFAULT 'argument',
    parameter_data_type VARCHAR(32) NOT NULL DEFAULT '',
    fixed_parameter TEXT NOT NULL,
    field_list_order INT UNSIGNED NOT NULL DEFAULT 0,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pdaitf_func_order (
        db_access_function_id,
        field_list_order,
        id
    ),
    CONSTRAINT fk_pdaitf_function
        FOREIGN KEY (db_access_function_id) REFERENCES project_db_access_functions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
