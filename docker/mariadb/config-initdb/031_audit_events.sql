CREATE TABLE IF NOT EXISTS audit_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_key VARCHAR(64) NOT NULL,
    actor_login_id VARCHAR(191) NOT NULL,
    actor_source VARCHAR(64) NOT NULL,
    project_key VARCHAR(64) NOT NULL,
    event_type VARCHAR(128) NOT NULL,
    target_type VARCHAR(128) NOT NULL,
    target_key VARCHAR(191) NOT NULL,
    result VARCHAR(32) NOT NULL,
    message TEXT NOT NULL,
    metadata_json TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_audit_events_event_key (event_key),
    KEY idx_audit_events_project_created (project_key, created_at),
    KEY idx_audit_events_actor_created (actor_login_id, created_at),
    KEY idx_audit_events_type_created (event_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
