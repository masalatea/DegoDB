CREATE TABLE IF NOT EXISTS html_templates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    legacy_html_template_pid BIGINT UNSIGNED NOT NULL,
    target_type VARCHAR(64) NOT NULL,
    parent_legacy_html_template_pid BIGINT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL,
    program_language VARCHAR(32) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_html_templates_legacy_pid (
        legacy_html_template_pid
    ),
    KEY idx_html_templates_target_parent_name (
        target_type,
        parent_legacy_html_template_pid,
        name,
        legacy_html_template_pid
    ),
    KEY idx_html_templates_parent (
        parent_legacy_html_template_pid
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS html_template_parameters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    legacy_template_parameter_pid BIGINT UNSIGNED NOT NULL,
    legacy_html_template_pid BIGINT UNSIGNED NOT NULL,
    parameter_name VARCHAR(255) NOT NULL,
    target_value_type VARCHAR(32) NOT NULL,
    target_variable_or_class_object VARCHAR(255) NOT NULL,
    target_property_of_class_object VARCHAR(255) NOT NULL,
    another_template_pid BIGINT UNSIGNED NOT NULL DEFAULT 0,
    trim_last_space TINYINT(1) NOT NULL DEFAULT 0,
    trim_last_return TINYINT(1) NOT NULL DEFAULT 0,
    data_type VARCHAR(32) NOT NULL DEFAULT '',
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(32) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_html_template_parameters_legacy_pid (
        legacy_template_parameter_pid
    ),
    KEY idx_html_template_parameters_template_name (
        legacy_html_template_pid,
        parameter_name,
        legacy_template_parameter_pid
    ),
    KEY idx_html_template_parameters_another_template (
        another_template_pid
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
