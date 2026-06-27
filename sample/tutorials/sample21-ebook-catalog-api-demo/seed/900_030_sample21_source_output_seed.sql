SET @sample21_project_id = (
    SELECT id
    FROM projects
    WHERE project_key = 'SAMPLE21'
);

DELETE FROM project_source_outputs
WHERE project_id = @sample21_project_id
  AND source_output_key IN (
      'DATACLASS-PHP',
      'DBACCESS-PHP',
      'OPENAPI-JSON'
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
    spec_visibility,
    notes,
    source_of_truth
) VALUES
(
    @sample21_project_id,
    'DATACLASS-PHP',
    'Sample21 Data Class (PHP)',
    'php',
    'DataClass',
    'Release',
    '',
    'work/source-outputs/SAMPLE21/DATACLASS-PHP',
    'work/staging/source-outputs/SAMPLE21/DATACLASS-PHP',
    '',
    '',
    'UTF-8',
    'mtool/dataclass-source-outputs/SAMPLE21/DATACLASS-PHP',
    'canonical-dataclass-php',
    'runtime',
    'tar.gz',
    10,
    'disabled',
    'Generate PHP data classes for the sample21 ebook catalog API demo.',
    'manual'
),
(
    @sample21_project_id,
    'DBACCESS-PHP',
    'Sample21 DBAccess (PHP)',
    'php',
    'DBAccess',
    'Release',
    '',
    'work/source-outputs/SAMPLE21/DBACCESS-PHP',
    'work/staging/source-outputs/SAMPLE21/DBACCESS-PHP',
    '',
    '',
    'UTF-8',
    'mtool/dbaccess-source-outputs/SAMPLE21/DBACCESS-PHP',
    'canonical-dbaccess-php',
    'runtime',
    'tar.gz',
    20,
    'disabled',
    'Generate PHP DBAccess classes for the sample21 ebook catalog API demo.',
    'manual'
),
(
    @sample21_project_id,
    'OPENAPI-JSON',
    'Sample21 OpenAPI JSON',
    'json',
    'OpenAPI',
    'Release',
    '',
    'work/source-outputs/SAMPLE21/OPENAPI-JSON',
    'work/staging/source-outputs/SAMPLE21/OPENAPI-JSON',
    'http://127.0.0.1:8082/runs/proxy/SAMPLE21/API-PROXY-SERVER',
    '',
    '',
    'mtool/openapi-source-outputs/SAMPLE21/OPENAPI-JSON',
    'openapi-json',
    'single-function-proxy',
    'tar.gz',
    30,
    'internal-only',
    'Generate OpenAPI JSON from ebook catalog read-function target metadata.',
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
    spec_visibility = VALUES(spec_visibility),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);

INSERT INTO project_db_access_function_source_output_targets (
    db_access_function_id,
    source_output_key
)
SELECT
    functions.id,
    'OPENAPI-JSON'
FROM project_db_access_functions AS functions
INNER JOIN project_db_access_classes AS classes
    ON classes.id = functions.db_access_class_id
WHERE classes.project_id = @sample21_project_id
  AND classes.source_name = 'ebook_catalog_item'
  AND functions.function_name IN (
      'GetPublicEbookCatalogList',
      'GetPublicEbookBook'
  )
ON DUPLICATE KEY UPDATE
    source_output_key = VALUES(source_output_key);

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
    @sample21_project_id,
    'AI-CONTEXT-MD',
    'Sample21 AI Context Markdown',
    'md',
    'AIContext',
    'Release',
    '',
    'work/source-outputs/SAMPLE21/AI-CONTEXT-MD',
    'work/staging/source-outputs/SAMPLE21/AI-CONTEXT-MD',
    '',
    '',
    'UTF-8',
    'mtool/ai-context-source-outputs/SAMPLE21/AI-CONTEXT-MD',
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
SET @sample21_project_id = NULL;
