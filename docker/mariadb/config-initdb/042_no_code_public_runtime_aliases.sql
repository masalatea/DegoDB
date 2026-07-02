CREATE TABLE IF NOT EXISTS no_code_public_runtime_aliases (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT UNSIGNED NOT NULL,
    alias_key VARCHAR(64) NOT NULL,
    source_output_key VARCHAR(64) NOT NULL,
    candidate_revision_id BIGINT UNSIGNED NOT NULL,
    revision_id VARCHAR(64) NOT NULL,
    artifact_key VARCHAR(64) NOT NULL,
    selected_by VARCHAR(191) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_no_code_public_runtime_alias_project (project_id, alias_key),
    KEY idx_no_code_public_runtime_alias_candidate (candidate_revision_id),
    CONSTRAINT fk_no_code_public_runtime_alias_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_no_code_public_runtime_alias_candidate
        FOREIGN KEY (candidate_revision_id) REFERENCES no_code_publish_candidate_revisions(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
