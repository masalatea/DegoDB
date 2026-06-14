CREATE TABLE IF NOT EXISTS database_sources (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    source_key VARCHAR(64) NOT NULL,
    label VARCHAR(191) NOT NULL,
    description TEXT NOT NULL,
    host VARCHAR(191) NOT NULL,
    port VARCHAR(16) NOT NULL DEFAULT '3306',
    database_name VARCHAR(191) NOT NULL,
    user_name VARCHAR(191) NOT NULL,
    password TEXT NOT NULL,
    supports_live_schema_import TINYINT(1) NOT NULL DEFAULT 1,
    supports_proxy_runtime_read TINYINT(1) NOT NULL DEFAULT 0,
    proxy_runtime_priority INT UNSIGNED NOT NULL DEFAULT 1000,
    source_of_truth VARCHAR(64) NOT NULL DEFAULT 'manual',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_database_sources_source_key (source_key),
    KEY idx_database_sources_proxy_runtime (
        supports_proxy_runtime_read,
        proxy_runtime_priority,
        source_key
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
