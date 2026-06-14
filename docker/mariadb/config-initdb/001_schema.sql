CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_key VARCHAR(64) NOT NULL,
    name VARCHAR(191) NOT NULL,
    slug VARCHAR(191) NOT NULL,
    lifecycle_status VARCHAR(32) NOT NULL DEFAULT 'draft',
    owner_login_id VARCHAR(128) NOT NULL,
    description TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_project_key (project_key),
    UNIQUE KEY uq_projects_slug (slug),
    KEY idx_projects_lifecycle_status (lifecycle_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_memberships (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    login_id VARCHAR(128) NOT NULL,
    role_code VARCHAR(64) NOT NULL,
    can_administer TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_memberships_role (project_id, login_id, role_code),
    KEY idx_project_memberships_login_id (login_id),
    CONSTRAINT fk_project_memberships_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_page_security_policies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    server_name VARCHAR(128) NOT NULL,
    script_name VARCHAR(255) NOT NULL,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_page_security_policy_scope (project_id, server_name, script_name),
    KEY idx_project_page_security_policies_project_id (project_id),
    CONSTRAINT fk_project_page_security_policies_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_page_security_policy_capabilities (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    page_security_policy_id BIGINT UNSIGNED NOT NULL,
    security_type VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_page_security_policy_capability (page_security_policy_id, security_type),
    KEY idx_project_page_security_policy_capabilities_security_type (security_type),
    CONSTRAINT fk_project_page_security_policy_capabilities_policy
        FOREIGN KEY (page_security_policy_id) REFERENCES project_page_security_policies(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_host_assignments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    apache_setting_name VARCHAR(128) NOT NULL,
    server_local_name VARCHAR(128) NOT NULL,
    virtual_host_name VARCHAR(191) NOT NULL,
    template_name VARCHAR(128) NOT NULL,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_host_assignments_identity (
        project_id,
        apache_setting_name,
        server_local_name,
        virtual_host_name,
        template_name
    ),
    KEY idx_project_host_assignments_project_id (project_id),
    CONSTRAINT fk_project_host_assignments_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
