CREATE TABLE IF NOT EXISTS project_custom_proxies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    custom_proxy_key VARCHAR(64) NOT NULL,
    basename VARCHAR(191) NOT NULL,
    name VARCHAR(191) NOT NULL,
    in_transaction TINYINT(1) NOT NULL DEFAULT 0,
    auth_type VARCHAR(64) NOT NULL DEFAULT '',
    single_get_function_name VARCHAR(191) NOT NULL DEFAULT '',
    continue_even_if_failed_to_insert TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_custom_proxies_project_key (project_id, custom_proxy_key),
    KEY idx_project_custom_proxies_project_name (project_id, basename, name, custom_proxy_key),
    CONSTRAINT fk_project_custom_proxies_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_custom_proxy_steps (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    custom_proxy_id BIGINT UNSIGNED NOT NULL,
    db_access_source_name VARCHAR(191) NOT NULL,
    db_access_function_name VARCHAR(191) NOT NULL,
    is_list TINYINT(1) NOT NULL DEFAULT 0,
    step_order INT UNSIGNED NOT NULL DEFAULT 100,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_project_custom_proxy_steps_order (custom_proxy_id, step_order, id),
    CONSTRAINT fk_project_custom_proxy_steps_custom_proxy
        FOREIGN KEY (custom_proxy_id) REFERENCES project_custom_proxies(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_custom_proxy_source_output_targets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    custom_proxy_id BIGINT UNSIGNED NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_custom_proxy_source_output_targets (custom_proxy_id, source_output_key),
    KEY idx_project_custom_proxy_source_output_targets_source_output (source_output_key),
    CONSTRAINT fk_project_custom_proxy_source_output_targets_custom_proxy
        FOREIGN KEY (custom_proxy_id) REFERENCES project_custom_proxies(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
