CREATE TABLE IF NOT EXISTS project_identity_memberships (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    principal_source VARCHAR(64) NOT NULL,
    principal_subject VARCHAR(191) NOT NULL,
    role_code VARCHAR(64) NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_identity_memberships_role (
        project_id,
        principal_source,
        principal_subject,
        role_code
    ),
    KEY idx_project_identity_memberships_principal (principal_source, principal_subject),
    KEY idx_project_identity_memberships_project_role (project_id, role_code),
    CONSTRAINT fk_project_identity_memberships_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
