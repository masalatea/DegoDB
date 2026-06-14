DROP INDEX IF EXISTS idx_project_db_access_function_select_wheres_function_legacy_pid
    ON project_db_access_function_select_wheres;
ALTER TABLE project_db_access_function_select_wheres
    DROP COLUMN IF EXISTS legacy_source_pid;

DROP INDEX IF EXISTS idx_pdastf_func_legacy_pid
    ON project_db_access_function_select_target_fields;
ALTER TABLE project_db_access_function_select_target_fields
    DROP COLUMN IF EXISTS legacy_source_pid;

DROP INDEX IF EXISTS idx_pdash_func_legacy_pid
    ON project_db_access_function_select_havings;
ALTER TABLE project_db_access_function_select_havings
    DROP COLUMN IF EXISTS legacy_source_pid;

DROP INDEX IF EXISTS idx_pdaudw_func_legacy_pid
    ON project_db_access_function_update_delete_wheres;
ALTER TABLE project_db_access_function_update_delete_wheres
    DROP COLUMN IF EXISTS legacy_source_pid;

DROP INDEX IF EXISTS idx_pdaitf_func_legacy_pid
    ON project_db_access_function_insert_target_fields;
ALTER TABLE project_db_access_function_insert_target_fields
    DROP COLUMN IF EXISTS legacy_source_pid;

DROP INDEX IF EXISTS idx_pdautf_func_legacy_pid
    ON project_db_access_function_update_target_fields;
ALTER TABLE project_db_access_function_update_target_fields
    DROP COLUMN IF EXISTS legacy_source_pid;
