CREATE TABLE IF NOT EXISTS project_html_definitions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    legacy_html_pid BIGINT UNSIGNED NOT NULL,
    html_key VARCHAR(191) NOT NULL,
    name VARCHAR(255) NOT NULL,
    legacy_project_source_output_pid BIGINT UNSIGNED NOT NULL DEFAULT 0,
    legacy_html_template_pid BIGINT UNSIGNED NOT NULL DEFAULT 0,
    html_list_order INT UNSIGNED NOT NULL DEFAULT 0,
    last_modified_dt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_html_definitions_project_legacy_pid (
        project_id,
        legacy_html_pid
    ),
    UNIQUE KEY uq_project_html_definitions_project_html_key (
        project_id,
        html_key
    ),
    KEY idx_project_html_definitions_project_order (
        project_id,
        html_list_order,
        legacy_html_pid
    ),
    KEY idx_project_html_definitions_project_source_output (
        project_id,
        legacy_project_source_output_pid
    ),
    CONSTRAINT fk_project_html_definitions_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_html_parameters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    project_html_definition_id BIGINT UNSIGNED NOT NULL,
    legacy_parameter_pid BIGINT UNSIGNED NOT NULL,
    parameter_name VARCHAR(255) NOT NULL,
    parameter_value MEDIUMTEXT NOT NULL,
    parameter_list_order INT UNSIGNED NOT NULL DEFAULT 0,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_html_parameters_project_legacy_pid (
        project_id,
        legacy_parameter_pid
    ),
    KEY idx_project_html_parameters_definition_order (
        project_html_definition_id,
        parameter_list_order,
        legacy_parameter_pid
    ),
    KEY idx_project_html_parameters_definition_name (
        project_html_definition_id,
        parameter_name
    ),
    KEY idx_project_html_parameters_project_definition (
        project_id,
        project_html_definition_id
    ),
    CONSTRAINT fk_project_html_parameters_definition
        FOREIGN KEY (project_html_definition_id) REFERENCES project_html_definitions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
