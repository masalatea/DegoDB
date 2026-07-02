CREATE TABLE IF NOT EXISTS no_code_public_runtime_alias_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    alias_key VARCHAR(64) NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    candidate_revision_id BIGINT UNSIGNED NULL,
    revision_id VARCHAR(64) NOT NULL,
    artifact_key VARCHAR(64) NOT NULL,
    event_type VARCHAR(32) NOT NULL,
    created_by VARCHAR(191) NOT NULL,
    metadata_json MEDIUMTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_no_code_public_runtime_alias_event_project (project_id, source_output_key, alias_key, created_at),
    KEY idx_no_code_public_runtime_alias_event_candidate (candidate_revision_id),
    CONSTRAINT fk_no_code_public_runtime_alias_event_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_no_code_public_runtime_alias_event_candidate
        FOREIGN KEY (candidate_revision_id) REFERENCES no_code_publish_candidate_revisions(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
