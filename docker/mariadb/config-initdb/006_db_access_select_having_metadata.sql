CREATE TABLE IF NOT EXISTS project_db_access_function_select_havings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    db_access_function_id BIGINT UNSIGNED NOT NULL,
    left_target_prefix VARCHAR(191) NOT NULL DEFAULT '',
    left_target_field_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
    left_target_suffix VARCHAR(191) NOT NULL DEFAULT '',
    relational_operator VARCHAR(32) NOT NULL DEFAULT '=',
    right_target_prefix VARCHAR(191) NOT NULL DEFAULT '',
    right_parameter_type VARCHAR(32) NOT NULL DEFAULT 'argument',
    right_parameter_data_type VARCHAR(32) NOT NULL DEFAULT '',
    right_fixed_parameter TEXT NOT NULL,
    right_target_field_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
    right_target_suffix VARCHAR(191) NOT NULL DEFAULT '',
    having_order INT UNSIGNED NOT NULL DEFAULT 0,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pdash_func_order (
        db_access_function_id,
        having_order,
        id
    ),
    CONSTRAINT fk_pdash_function
        FOREIGN KEY (db_access_function_id) REFERENCES project_db_access_functions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
