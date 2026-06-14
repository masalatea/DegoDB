UPDATE project_source_outputs AS so
INNER JOIN projects AS p
    ON p.id = so.project_id
SET
    so.source_output_dir = CASE
        WHEN so.source_output_dir = ''
            OR so.source_output_dir LIKE 'published/source-outputs/%'
            OR so.source_output_dir LIKE 'work/source-outputs/%'
            OR so.source_output_dir LIKE 'sample/source-outputs/%'
        THEN CONCAT('work/source-outputs/', p.project_key, '/', so.source_output_key)
        ELSE so.source_output_dir
    END,
    so.source_temp_output_dir = CASE
        WHEN so.source_temp_output_dir = ''
            OR so.source_temp_output_dir LIKE 'published/source-outputs/%'
            OR so.source_temp_output_dir LIKE 'work/staging/source-outputs/%'
        THEN CONCAT('work/staging/source-outputs/', p.project_key, '/', so.source_output_key)
        ELSE so.source_temp_output_dir
    END,
    so.source_template_dir = CASE
        WHEN so.source_template_dir LIKE 'shared/reference/%'
        THEN CONCAT('mtool/reference/', SUBSTRING(so.source_template_dir, CHAR_LENGTH('shared/reference/') + 1))
        WHEN so.source_template_dir LIKE 'mtool/shared/reference/%'
        THEN CONCAT('mtool/reference/', SUBSTRING(so.source_template_dir, CHAR_LENGTH('mtool/shared/reference/') + 1))
        ELSE so.source_template_dir
    END
WHERE
    so.source_output_dir = ''
    OR so.source_output_dir LIKE 'published/source-outputs/%'
    OR so.source_output_dir LIKE 'work/source-outputs/%'
    OR so.source_output_dir LIKE 'sample/source-outputs/%'
    OR so.source_temp_output_dir = ''
    OR so.source_temp_output_dir LIKE 'published/source-outputs/%'
    OR so.source_temp_output_dir LIKE 'work/staging/source-outputs/%'
    OR so.source_template_dir LIKE 'shared/reference/%'
    OR so.source_template_dir LIKE 'mtool/shared/reference/%';
