SET @sample16_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE16'
);

DELETE FROM project_source_outputs
WHERE project_id = @sample16_project_id
  AND source_output_key = 'AUTH-PROXY-SERVER';

INSERT INTO project_source_outputs (
    project_id,
    source_output_key,
    name,
    program_language,
    class_type,
    release_target_type,
    source_template_dir,
    source_output_dir,
    source_temp_output_dir,
    proxy_base_url,
    autoload_filename_suffix,
    source_text_char_code,
    runtime_source_relative_path,
    artifact_strategy,
    target_binding_type,
    output_archive_format,
    source_output_list_order,
    notes,
    source_of_truth
) VALUES (
    @sample16_project_id,
    'AUTH-PROXY-SERVER',
    'Sample16 Authenticated Single Proxy Server',
    'php',
    'ProxyServer',
    'Release',
    '',
    'work/source-outputs/SAMPLE16/AUTH-PROXY-SERVER',
    'work/staging/source-outputs/SAMPLE16/AUTH-PROXY-SERVER',
    '',
    '',
    'UTF-8',
    'mtool/proxy-source-outputs/SAMPLE16/AUTH-PROXY-SERVER',
    'single-proxy-server',
    'single-function-proxy',
    'tar.gz',
    40,
    'Generate a static bearer authenticated single proxy server endpoint from AuthTask.GetAuthTask.',
    'manual'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    program_language = VALUES(program_language),
    class_type = VALUES(class_type),
    release_target_type = VALUES(release_target_type),
    source_template_dir = VALUES(source_template_dir),
    source_output_dir = VALUES(source_output_dir),
    source_temp_output_dir = VALUES(source_temp_output_dir),
    proxy_base_url = VALUES(proxy_base_url),
    autoload_filename_suffix = VALUES(autoload_filename_suffix),
    source_text_char_code = VALUES(source_text_char_code),
    runtime_source_relative_path = VALUES(runtime_source_relative_path),
    artifact_strategy = VALUES(artifact_strategy),
    target_binding_type = VALUES(target_binding_type),
    output_archive_format = VALUES(output_archive_format),
    source_output_list_order = VALUES(source_output_list_order),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

INSERT INTO project_db_access_function_source_output_targets (
    db_access_function_id,
    source_output_key
)
SELECT
    functions.id,
    'AUTH-PROXY-SERVER'
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample16_project_id
  AND classes.source_name = 'AuthTask'
  AND functions.function_name = 'GetAuthTask'
ON DUPLICATE KEY UPDATE
    source_output_key = VALUES(source_output_key);

SET @sample16_project_id = NULL;
