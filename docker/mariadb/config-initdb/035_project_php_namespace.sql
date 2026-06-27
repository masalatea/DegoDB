ALTER TABLE projects
    ADD COLUMN IF NOT EXISTS php_namespace VARCHAR(191) NOT NULL DEFAULT ''
    AFTER owner_login_id;

