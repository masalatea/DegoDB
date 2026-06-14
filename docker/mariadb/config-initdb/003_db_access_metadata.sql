CREATE TABLE IF NOT EXISTS project_db_access_classes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    source_name VARCHAR(191) NOT NULL,
    store_base_path VARCHAR(512) NOT NULL DEFAULT '',
    is_autoload TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'preview-bootstrap',
    last_detected_dbaccess_file VARCHAR(191) NOT NULL DEFAULT '',
    last_detected_data_file VARCHAR(191) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_db_access_classes_project_source (project_id, source_name),
    KEY idx_project_db_access_classes_project_id (project_id),
    CONSTRAINT fk_project_db_access_classes_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_db_access_functions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    db_access_class_id BIGINT UNSIGNED NOT NULL,
    function_name VARCHAR(191) NOT NULL,
    function_list_order INT UNSIGNED NOT NULL DEFAULT 0,
    function_suffix VARCHAR(191) NOT NULL DEFAULT '',
    action_type VARCHAR(32) NOT NULL DEFAULT '',
    data_class_base_name VARCHAR(191) NOT NULL DEFAULT '',
    target_table_name VARCHAR(191) NOT NULL DEFAULT '',
    parameter_type VARCHAR(64) NOT NULL DEFAULT '',
    select_by_distinct TINYINT(1) NOT NULL DEFAULT 0,
    sort_order_columns VARCHAR(512) NOT NULL DEFAULT '',
    memo TEXT NOT NULL,
    limit_parameter_type VARCHAR(64) NOT NULL DEFAULT '',
    limit_fixed_parameter VARCHAR(191) NOT NULL DEFAULT '',
    or_group_type VARCHAR(64) NOT NULL DEFAULT '',
    single_proxy_auth_type VARCHAR(64) NOT NULL DEFAULT '',
    single_proxy_single_get_function_name VARCHAR(191) NOT NULL DEFAULT '',
    is_blob_target TINYINT(1) NOT NULL DEFAULT 0,
    detected_signature VARCHAR(512) NOT NULL DEFAULT '',
    detected_line INT UNSIGNED NOT NULL DEFAULT 0,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'preview-bootstrap',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_db_access_functions_class_function (db_access_class_id, function_name),
    KEY idx_project_db_access_functions_class_id (db_access_class_id),
    KEY idx_project_db_access_functions_class_order (db_access_class_id, function_list_order, function_name),
    CONSTRAINT fk_project_db_access_functions_class
        FOREIGN KEY (db_access_class_id) REFERENCES project_db_access_classes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
