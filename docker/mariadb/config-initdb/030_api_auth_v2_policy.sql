ALTER TABLE project_db_access_functions
    ADD COLUMN IF NOT EXISTS auth_policy_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER single_proxy_single_get_function_name;

ALTER TABLE project_db_access_functions
    ADD COLUMN IF NOT EXISTS auth_policy_json TEXT NOT NULL DEFAULT '' AFTER auth_policy_version;

ALTER TABLE project_custom_proxies
    ADD COLUMN IF NOT EXISTS auth_policy_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER single_get_function_name;

ALTER TABLE project_custom_proxies
    ADD COLUMN IF NOT EXISTS auth_policy_json TEXT NOT NULL DEFAULT '' AFTER auth_policy_version;
