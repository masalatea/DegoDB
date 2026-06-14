CREATE TABLE IF NOT EXISTS project_db_access_function_source_output_targets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    db_access_function_id BIGINT UNSIGNED NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pdaf_source_output_targets (db_access_function_id, source_output_key),
    KEY idx_pdaf_source_output_key (source_output_key),
    CONSTRAINT fk_pdaf_source_output_targets_function
        FOREIGN KEY (db_access_function_id) REFERENCES project_db_access_functions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
