SET @sample2_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE2'
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
    @sample2_project_id,
    'DBACCESS-CS-SQLSERVER',
    'SQL Server Test DBAccess (C#)',
    'cs',
    'DBAccess',
    'Release',
    '',
    'work/source-outputs/SAMPLE2/DBACCESS-CS-SQLSERVER',
    'work/staging/source-outputs/SAMPLE2/DBACCESS-CS-SQLSERVER',
    '',
    '',
    '',
    '',
    'metadata-only',
    'metadata-only',
    'none',
    10,
    'Derived from legacy ProjectSourceOutput PID 5. Original SourceTemplateDir=/Dev Lib/settings/dbclasses_template_system_default/php, SourceOutputDir=/WpfApplication1 - test3 - SQL Server/WpfApplication1/CSTest, UnitTestTemplateDir=/Dev Lib/settings/unit_test_template_system_default/php, UnitTestOutputDir=/C# Test/mtool-test/CS unit test.',
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

SET @sample2_project_id = NULL;
