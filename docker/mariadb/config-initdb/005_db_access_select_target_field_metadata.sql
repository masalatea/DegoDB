CREATE TABLE IF NOT EXISTS project_db_access_function_select_target_fields (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    db_access_function_id BIGINT UNSIGNED NOT NULL,
    target_table_name VARCHAR(191) NOT NULL DEFAULT '',
    target_table_alias_name VARCHAR(191) NOT NULL DEFAULT '',
    target_table_column_name VARCHAR(191) NOT NULL DEFAULT '',
    target_table_column_prefix VARCHAR(191) NOT NULL DEFAULT '',
    target_table_column_suffix VARCHAR(191) NOT NULL DEFAULT '',
    store_class_field_name VARCHAR(191) NOT NULL DEFAULT '',
    group_by_target TINYINT(1) NOT NULL DEFAULT 0,
    field_list_order INT UNSIGNED NOT NULL DEFAULT 0,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pdastf_func_order (
        db_access_function_id,
        field_list_order,
        id
    ),
    CONSTRAINT fk_pdastf_function
        FOREIGN KEY (db_access_function_id) REFERENCES project_db_access_functions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
