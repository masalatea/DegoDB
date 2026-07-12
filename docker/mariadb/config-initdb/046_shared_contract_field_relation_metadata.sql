ALTER TABLE project_shared_contract_fields
    ADD COLUMN IF NOT EXISTS relation_kind VARCHAR(32) NOT NULL DEFAULT ''
    AFTER no_code_role;

ALTER TABLE project_shared_contract_fields
    ADD COLUMN IF NOT EXISTS relation_contract_key VARCHAR(191) NOT NULL DEFAULT ''
    AFTER relation_kind;

ALTER TABLE project_shared_contract_fields
    ADD COLUMN IF NOT EXISTS relation_key_field VARCHAR(191) NOT NULL DEFAULT ''
    AFTER relation_contract_key;

ALTER TABLE project_shared_contract_fields
    ADD COLUMN IF NOT EXISTS relation_label_field VARCHAR(191) NOT NULL DEFAULT ''
    AFTER relation_key_field;

ALTER TABLE project_shared_contract_fields
    ADD COLUMN IF NOT EXISTS relation_ui_role VARCHAR(32) NOT NULL DEFAULT ''
    AFTER relation_label_field;

ALTER TABLE project_shared_contract_fields
    ADD COLUMN IF NOT EXISTS relation_required TINYINT(1) NOT NULL DEFAULT 0
    AFTER relation_ui_role;
