ALTER TABLE project_source_outputs
    ADD COLUMN IF NOT EXISTS spec_visibility VARCHAR(64) NOT NULL DEFAULT 'internal-only'
    AFTER target_binding_type;
