CREATE TABLE IF NOT EXISTS project_compare_outputs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    compare_output_key VARCHAR(64) NOT NULL,
    name VARCHAR(191) NOT NULL,
    storage_base_path VARCHAR(512) NOT NULL DEFAULT '',
    output_file_path VARCHAR(512) NOT NULL DEFAULT '',
    output_file_type VARCHAR(32) NOT NULL DEFAULT 'Text',
    compare_path VARCHAR(512) NOT NULL DEFAULT '',
    compare_tool_file_path VARCHAR(512) NOT NULL DEFAULT '',
    compare_output_list_order INT UNSIGNED NOT NULL DEFAULT 100,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_compare_outputs_project_key (project_id, compare_output_key),
    KEY idx_project_compare_outputs_project_order (project_id, compare_output_list_order, compare_output_key),
    CONSTRAINT fk_project_compare_outputs_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_compare_output_additional_paths (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    compare_output_id BIGINT UNSIGNED NOT NULL,
    additional_path_key VARCHAR(64) NOT NULL,
    path_a_base_path VARCHAR(512) NOT NULL DEFAULT '',
    path_a VARCHAR(512) NOT NULL DEFAULT '',
    path_b_base_path VARCHAR(512) NOT NULL DEFAULT '',
    path_b VARCHAR(512) NOT NULL DEFAULT '',
    is_same_filename_only TINYINT(1) NOT NULL DEFAULT 0,
    additional_path_list_order INT UNSIGNED NOT NULL DEFAULT 100,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_compare_output_additional_paths_key (compare_output_id, additional_path_key),
    KEY idx_project_compare_output_additional_paths_order (compare_output_id, additional_path_list_order, additional_path_key),
    CONSTRAINT fk_project_compare_output_additional_paths_compare_output
        FOREIGN KEY (compare_output_id) REFERENCES project_compare_outputs(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
