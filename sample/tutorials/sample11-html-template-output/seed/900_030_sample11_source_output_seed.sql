SET @sample11_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE11'
);

DELETE FROM project_source_outputs
WHERE project_id = @sample11_project_id
  AND source_output_key = 'HTML-PAGE';

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
    @sample11_project_id,
    'HTML-PAGE',
    'Sample11 HTML Page Module',
    'php',
    'html',
    'Release',
    'catalog://html-module/SAMPLE11/HTML-PAGE',
    'work/source-outputs/SAMPLE11/HTML-PAGE',
    'work/staging/source-outputs/SAMPLE11/HTML-PAGE',
    '',
    '',
    'UTF-8',
    'mtool/html-source-outputs/SAMPLE11/HTML-PAGE',
    'html-module-catalog',
    'runtime',
    'tar.gz',
    10,
    'Publish a minimal curated HTML module tree for the sample11 HTML template tutorial. The source tree is mtool/reference/html-modules/sample11/HTML-PAGE/current.',
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

INSERT INTO project_html_source_bindings (
    project_id,
    legacy_project_source_output_pid,
    source_output_key,
    module_source_ref,
    refresh_policy,
    notes,
    source_of_truth
) VALUES (
    @sample11_project_id,
    110030,
    'HTML-PAGE',
    'catalog://html-module/SAMPLE11/HTML-PAGE',
    'follow-source-output',
    'Tutorial binding for the sample11 HTML module source output.',
    'manual'
)
ON DUPLICATE KEY UPDATE
    source_output_key = VALUES(source_output_key),
    module_source_ref = VALUES(module_source_ref),
    refresh_policy = VALUES(refresh_policy),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

SET @sample11_project_id = NULL;
