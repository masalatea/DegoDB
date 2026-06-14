INSERT IGNORE INTO project_source_outputs (
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
)
SELECT
    p.id,
    'DBTABLE-PROXY-SERVER',
    'Mtool dbtable Proxy Server',
    'php',
    'DBaaSProxyServer',
    'Release',
    '',
    'work/source-outputs/MTOOL/DBTABLE-PROXY-SERVER',
    'work/staging/source-outputs/MTOOL/DBTABLE-PROXY-SERVER',
    'http://127.0.0.1:8082/runs/proxy/MTOOL/DBTABLE-PROXY-SERVER',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/DBTABLE-PROXY-SERVER',
    'single-proxy-server',
    'single-function-proxy',
    'tar.gz',
    41,
    'current generic single-function proxy server definition。dbtable smoke と imported canonical-bootstrap table の publish / relay / Swagger flow を同じ target で扱う。',
    'bootstrap-default'
FROM projects AS p
WHERE p.project_key = 'MTOOL';

INSERT IGNORE INTO project_source_outputs (
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
)
SELECT
    p.id,
    'OPENAPI-JSON',
    'Mtool OpenAPI JSON',
    'json',
    'OpenAPI',
    'Release',
    '',
    'work/source-outputs/MTOOL/OPENAPI-JSON',
    'work/staging/source-outputs/MTOOL/OPENAPI-JSON',
    'http://127.0.0.1:8082/runs/proxy/MTOOL/DBTABLE-PROXY-SERVER',
    '',
    '',
    'mtool/openapi-source-outputs/MTOOL/OPENAPI-JSON',
    'openapi-json',
    'single-function-proxy',
    'tar.gz',
    42,
    'current generic OpenAPI JSON definition。single-function proxy target assignment を最小 openapi.json へ落とし、Lab Swagger viewer の verified lane で使う。',
    'bootstrap-default'
FROM projects AS p
WHERE p.project_key = 'MTOOL';

INSERT IGNORE INTO project_db_access_function_source_output_targets (
    db_access_function_id,
    source_output_key
)
SELECT
    f.id,
    target.source_output_key
FROM project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
INNER JOIN (
    SELECT 'DBTABLE-PROXY-SERVER' AS source_output_key
    UNION ALL
    SELECT 'OPENAPI-JSON'
) AS target
WHERE p.project_key = 'MTOOL'
  AND c.source_name = 'dbtable'
  AND f.function_name IN (
      'GetdbtableList',
      'GetdbtableByName'
  );
