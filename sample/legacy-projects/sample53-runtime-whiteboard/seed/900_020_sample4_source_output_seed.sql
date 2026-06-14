SET @sample4_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE4'
);

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
    @sample4_project_id,
    'DBACCESS-PHP',
    'Whiteboard DBAccess (PHP)',
    'php',
    'DBAccess',
    'Release',
    '',
    'work/source-outputs/SAMPLE4/DBACCESS-PHP',
    'work/staging/source-outputs/SAMPLE4/DBACCESS-PHP',
    '',
    'whiteboard',
    '',
    '',
    'metadata-only',
    'metadata-only',
    'none',
    10,
    'Derived from legacy ProjectSourceOutput PID 4. Original SourceOutputDir=/Common Lib/nodejs_lib/dbclasses and legacy autoload suffix=whiteboard.',
    'bootstrap-default'
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

SET @sample4_project_id = NULL;
