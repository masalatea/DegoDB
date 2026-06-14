CREATE TABLE IF NOT EXISTS project_html_source_bindings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    legacy_project_source_output_pid INT UNSIGNED NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    module_source_ref VARCHAR(512) NOT NULL DEFAULT '',
    refresh_policy VARCHAR(64) NOT NULL DEFAULT 'follow-source-output',
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_html_source_bindings_legacy_pid (
        project_id,
        legacy_project_source_output_pid
    ),
    KEY idx_project_html_source_bindings_source_output_key (
        project_id,
        source_output_key
    ),
    CONSTRAINT fk_project_html_source_bindings_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
