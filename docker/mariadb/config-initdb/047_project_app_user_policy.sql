CREATE TABLE IF NOT EXISTS project_app_user_policies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    contract_version VARCHAR(64) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    policy_json MEDIUMTEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_app_user_policies_project (project_id),
    CONSTRAINT fk_project_app_user_policies_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
