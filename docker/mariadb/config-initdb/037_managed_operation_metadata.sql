CREATE TABLE IF NOT EXISTS project_managed_operations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    operation_key VARCHAR(191) NOT NULL,
    contract_key VARCHAR(191) NOT NULL,
    name VARCHAR(255) NOT NULL,
    operation_type VARCHAR(32) NOT NULL DEFAULT 'read',
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    storage_policy VARCHAR(64) NOT NULL DEFAULT 'business-only',
    permission_key VARCHAR(128) NOT NULL DEFAULT 'project.read',
    required_roles_json TEXT NOT NULL,
    required_scopes_json TEXT NOT NULL,
    required_claims_json TEXT NOT NULL,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_managed_operations_key (project_id, operation_key),
    KEY idx_project_managed_operations_contract (project_id, contract_key),
    CONSTRAINT fk_project_managed_operations_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_managed_operation_fields (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    managed_operation_id BIGINT UNSIGNED NOT NULL,
    field_physical_name VARCHAR(191) NOT NULL,
    field_role VARCHAR(32) NOT NULL DEFAULT 'input',
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    allow_client_write TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_managed_operation_fields_name (project_id, managed_operation_id, field_physical_name),
    KEY idx_project_managed_operation_fields_operation (project_id, managed_operation_id),
    CONSTRAINT fk_project_managed_operation_fields_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_project_managed_operation_fields_operation
        FOREIGN KEY (managed_operation_id) REFERENCES project_managed_operations(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
