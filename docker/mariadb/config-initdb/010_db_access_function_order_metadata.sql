ALTER TABLE project_db_access_functions
    ADD COLUMN IF NOT EXISTS function_list_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER function_name;

UPDATE project_db_access_functions
SET function_list_order = CASE
    WHEN function_list_order > 0 THEN function_list_order
    WHEN detected_line > 0 THEN detected_line
    ELSE id
END
WHERE function_list_order = 0;
