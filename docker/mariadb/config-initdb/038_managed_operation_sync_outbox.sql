CREATE TABLE IF NOT EXISTS project_managed_operation_sync_outbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    dedupe_key VARCHAR(64) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'pending',
    storage_mode VARCHAR(32) NOT NULL,
    origin_endpoint VARCHAR(32) NOT NULL,
    target_endpoint VARCHAR(32) NOT NULL,
    operation_key VARCHAR(191) NOT NULL,
    operation_type VARCHAR(32) NOT NULL,
    contract_key VARCHAR(191) NOT NULL,
    intent_json MEDIUMTEXT NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    last_error TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_project_managed_operation_sync_outbox_dedupe (project_id, dedupe_key),
    KEY idx_project_managed_operation_sync_outbox_status (project_id, status, updated_at),
    KEY idx_project_managed_operation_sync_outbox_operation (project_id, operation_key),
    CONSTRAINT fk_project_managed_operation_sync_outbox_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
