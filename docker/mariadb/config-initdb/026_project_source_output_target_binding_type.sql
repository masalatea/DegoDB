ALTER TABLE project_source_outputs
    ADD COLUMN IF NOT EXISTS target_binding_type VARCHAR(64) NOT NULL DEFAULT ''
    AFTER artifact_strategy;
