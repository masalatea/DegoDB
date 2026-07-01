CREATE TABLE IF NOT EXISTS project_shared_contracts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    contract_key VARCHAR(191) NOT NULL,
    data_class_physical_name VARCHAR(191) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    sync_role VARCHAR(64) NOT NULL DEFAULT '',
    no_code_role VARCHAR(64) NOT NULL DEFAULT '',
    app_persistence_role VARCHAR(64) NOT NULL DEFAULT '',
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_shared_contracts_key (project_id, contract_key),
    KEY idx_project_shared_contracts_data_class (project_id, data_class_physical_name),
    CONSTRAINT fk_project_shared_contracts_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_shared_contract_fields (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    shared_contract_id BIGINT UNSIGNED NOT NULL,
    field_physical_name VARCHAR(191) NOT NULL,
    sync_role VARCHAR(64) NOT NULL DEFAULT '',
    operation_role VARCHAR(64) NOT NULL DEFAULT '',
    no_code_role VARCHAR(64) NOT NULL DEFAULT '',
    app_persistence_role VARCHAR(64) NOT NULL DEFAULT '',
    notes TEXT NOT NULL,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_shared_contract_fields_name (project_id, shared_contract_id, field_physical_name),
    KEY idx_project_shared_contract_fields_contract (project_id, shared_contract_id),
    CONSTRAINT fk_project_shared_contract_fields_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_project_shared_contract_fields_contract
        FOREIGN KEY (shared_contract_id) REFERENCES project_shared_contracts(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
