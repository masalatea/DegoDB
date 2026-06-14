CREATE TABLE IF NOT EXISTS lab_experiments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    experiment_key VARCHAR(64) NOT NULL,
    project_key VARCHAR(64) NOT NULL,
    name VARCHAR(191) NOT NULL,
    execution_status VARCHAR(32) NOT NULL DEFAULT 'ready',
    runtime_target VARCHAR(64) NOT NULL DEFAULT 'local-docker',
    executed_by VARCHAR(128) DEFAULT NULL,
    notes TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lab_experiments_experiment_key (experiment_key),
    KEY idx_lab_experiments_project_key (project_key),
    KEY idx_lab_experiments_execution_status (execution_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
