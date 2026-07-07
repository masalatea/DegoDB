ALTER TABLE project_shared_contracts
    ADD COLUMN IF NOT EXISTS usage_intent VARCHAR(64) NOT NULL DEFAULT ''
    AFTER status;

ALTER TABLE project_shared_contracts
    ADD COLUMN IF NOT EXISTS view_variant_preference VARCHAR(64) NOT NULL DEFAULT ''
    AFTER usage_intent;

