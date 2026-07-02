SET @sample28_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE28'
);

DELETE FROM project_source_outputs
WHERE project_id = @sample28_project_id
  AND source_output_key = 'NO-CODE-RUNTIME';

DELETE FROM project_source_outputs
WHERE project_id = @sample28_project_id
  AND source_output_key = 'NO-CODE-REACT-BRIDGE';

DELETE FROM project_source_outputs
WHERE project_id = @sample28_project_id
  AND source_output_key = 'NO-CODE-JSON-FORMS-PROBE';

DELETE FROM project_source_outputs
WHERE project_id = @sample28_project_id
  AND source_output_key = 'AI-CONTEXT-MD';

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
    @sample28_project_id,
    'NO-CODE-RUNTIME',
    'Sample28 No-Code Runtime JSON',
    'json',
    'NoCodeRuntime',
    'Release',
    '',
    'work/source-outputs/SAMPLE28/NO-CODE-RUNTIME',
    'work/staging/source-outputs/SAMPLE28/NO-CODE-RUNTIME',
    '',
    '',
    'UTF-8',
    'mtool/no-code-runtime-source-outputs/SAMPLE28/NO-CODE-RUNTIME',
    'no-code-runtime-json',
    'runtime',
    'tar.gz',
    70,
    'Generate no-code screen definition and runtime preview JSON from sample28 shared contract and managed operation metadata.',
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
    @sample28_project_id,
    'NO-CODE-JSON-FORMS-PROBE',
    'Sample28 No-Code JSON Forms Probe',
    'json',
    'NoCodeJsonFormsProbe',
    'Release',
    '',
    'work/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE',
    'work/staging/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE',
    '',
    '',
    'UTF-8',
    'mtool/no-code-json-forms-probe-source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE',
    'no-code-json-forms-probe',
    'runtime',
    'tar.gz',
    80,
    'Generate comparison JSON Schema and UI Schema artifacts from sample28 no-code form metadata without bundling a schema-form runtime UI.',
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
    @sample28_project_id,
    'NO-CODE-REACT-BRIDGE',
    'Sample28 No-Code React Bridge',
    'typescript',
    'NoCodeReactBridge',
    'Release',
    '',
    'work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE',
    'work/staging/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE',
    '',
    '',
    'UTF-8',
    'mtool/no-code-react-bridge-source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE',
    'no-code-react-bridge',
    'runtime',
    'tar.gz',
    75,
    'Generate a React + TypeScript bridge scaffold from sample28 no-code runtime metadata while keeping Mtool ownership at the JSON/action-intent boundary.',
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
    @sample28_project_id,
    'AI-CONTEXT-MD',
    'Sample28 AI Context Markdown',
    'md',
    'AIContext',
    'Release',
    '',
    'work/source-outputs/SAMPLE28/AI-CONTEXT-MD',
    'work/staging/source-outputs/SAMPLE28/AI-CONTEXT-MD',
    '',
    '',
    'UTF-8',
    'mtool/ai-context-source-outputs/SAMPLE28/AI-CONTEXT-MD',
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

SET @sample28_project_id = NULL;
