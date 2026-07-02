CREATE TABLE IF NOT EXISTS no_code_publish_candidate_revisions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    revision_id VARCHAR(64) NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    artifact_key VARCHAR(64) NOT NULL,
    artifact_archive_path VARCHAR(512) NOT NULL DEFAULT '',
    artifact_checksum VARCHAR(128) NOT NULL DEFAULT '',
    readiness_state VARCHAR(32) NOT NULL,
    readiness_label VARCHAR(191) NOT NULL,
    screen_count INT UNSIGNED NOT NULL DEFAULT 0,
    action_count INT UNSIGNED NOT NULL DEFAULT 0,
    preview_files_ready TINYINT(1) NOT NULL DEFAULT 0,
    artifact_archive_exists TINYINT(1) NOT NULL DEFAULT 0,
    blocking_reasons_json MEDIUMTEXT NOT NULL,
    snapshot_json MEDIUMTEXT NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft_candidate',
    created_by VARCHAR(191) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_no_code_publish_candidate_revision (project_id, source_output_key, revision_id),
    KEY idx_no_code_publish_candidate_created (project_id, source_output_key, created_at),
    KEY idx_no_code_publish_candidate_artifact (project_id, source_output_key, artifact_key),
    CONSTRAINT fk_no_code_publish_candidate_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
