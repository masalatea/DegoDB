CREATE TABLE IF NOT EXISTS no_code_publish_candidate_transition_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    candidate_revision_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    revision_id VARCHAR(64) NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    transition VARCHAR(32) NOT NULL,
    from_status VARCHAR(32) NOT NULL,
    to_status VARCHAR(32) NOT NULL,
    transition_reason TEXT NOT NULL,
    metadata_json MEDIUMTEXT NOT NULL,
    created_by VARCHAR(191) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_no_code_publish_candidate_transition_candidate (candidate_revision_id, created_at),
    KEY idx_no_code_publish_candidate_transition_project (project_id, source_output_key, revision_id),
    CONSTRAINT fk_no_code_publish_candidate_transition_candidate
        FOREIGN KEY (candidate_revision_id) REFERENCES no_code_publish_candidate_revisions(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_no_code_publish_candidate_transition_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
