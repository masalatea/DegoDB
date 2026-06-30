SET @sample30_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE30'
);

DELETE FROM project_source_outputs
WHERE project_id = @sample30_project_id
  AND source_output_key IN ('APP-LOCAL-PERSISTENCE', 'NO-CODE-RUNTIME', 'AI-CONTEXT-MD');

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
    @sample30_project_id,
    'APP-LOCAL-PERSISTENCE',
    'Sample30 App-local Persistence PHP',
    'php',
    'AppLocalPersistence',
    'Release',
    '',
    'work/source-outputs/SAMPLE30/APP-LOCAL-PERSISTENCE',
    'work/staging/source-outputs/SAMPLE30/APP-LOCAL-PERSISTENCE',
    '',
    '',
    'UTF-8',
    'mtool/app-local-persistence-source-outputs/SAMPLE30/APP-LOCAL-PERSISTENCE',
    'app-local-persistence-php',
    'runtime',
    'tar.gz',
    60,
    'Generate App-local SQLite schema and PHP persistence wrappers from the sample30 shared contract manifest.',
    'manual'
),
(
    @sample30_project_id,
    'NO-CODE-RUNTIME',
    'Sample30 No-Code Runtime JSON',
    'json',
    'NoCodeRuntime',
    'Release',
    '',
    'work/source-outputs/SAMPLE30/NO-CODE-RUNTIME',
    'work/staging/source-outputs/SAMPLE30/NO-CODE-RUNTIME',
    '',
    '',
    'UTF-8',
    'mtool/no-code-runtime-source-outputs/SAMPLE30/NO-CODE-RUNTIME',
    'no-code-runtime-json',
    'runtime',
    'tar.gz',
    70,
    'Generate no-code screen definition and runtime preview JSON from sample30 shared contract and managed operation metadata.',
    'manual'
),
(
    @sample30_project_id,
    'AI-CONTEXT-MD',
    'Sample30 AI Context Markdown',
    'md',
    'AIContext',
    'Release',
    '',
    'work/source-outputs/SAMPLE30/AI-CONTEXT-MD',
    'work/staging/source-outputs/SAMPLE30/AI-CONTEXT-MD',
    '',
    '',
    'UTF-8',
    'mtool/ai-context-source-outputs/SAMPLE30/AI-CONTEXT-MD',
    'ai-context-md',
    'runtime',
    'tar.gz',
    90,
    'Generate AI-readable Markdown and JSON context from canonical project metadata. Authored by DegoDB/Mtool generator code; AI is reader/consumer only.',
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

SET @sample30_project_id = NULL;
