CREATE TABLE IF NOT EXISTS sample18_generated_submit_idempotency_records (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    dedupe_key VARCHAR(191) NOT NULL,
    project_key VARCHAR(64) NOT NULL,
    operation_key VARCHAR(128) NOT NULL,
    payload_fingerprint VARCHAR(64) NOT NULL,
    result VARCHAR(32) NOT NULL,
    failure_code VARCHAR(128) NOT NULL,
    first_audit_event_key VARCHAR(64) NOT NULL,
    duplicate_count INT UNSIGNED NOT NULL DEFAULT 0,
    metadata_json TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sample18_submit_idempotency_dedupe (dedupe_key),
    KEY idx_sample18_submit_idempotency_project_op (project_key, operation_key, created_at),
    KEY idx_sample18_submit_idempotency_fingerprint (payload_fingerprint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
