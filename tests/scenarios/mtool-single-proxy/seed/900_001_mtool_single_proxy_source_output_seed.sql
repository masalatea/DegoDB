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
    'SAMPLE-SINGLE-PROXY-SERVER',
    'Mtool Sample Single Proxy Server',
    'php',
    'DBaaSProxyServer',
    'Release',
    '',
    'work/source-outputs/MTOOL/SAMPLE-SINGLE-PROXY-SERVER',
    'work/staging/source-outputs/MTOOL/SAMPLE-SINGLE-PROXY-SERVER',
    '',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/SAMPLE-SINGLE-PROXY-SERVER',
    'single-proxy-server',
    'single-function-proxy',
    'tar.gz',
    80,
    'single-function proxy artifact strategy を end-to-end で検証する sample/test definition。Project 1 の core source output seed とは別管理にする。',
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
    'SAMPLE-SINGLE-PROXY-CLIENT',
    'Mtool Sample Single Proxy Client',
    'cs',
    'DBaaSProxyClient',
    'Release',
    '',
    'work/source-outputs/MTOOL/SAMPLE-SINGLE-PROXY-CLIENT',
    'work/staging/source-outputs/MTOOL/SAMPLE-SINGLE-PROXY-CLIENT',
    '',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/SAMPLE-SINGLE-PROXY-CLIENT',
    'single-proxy-client',
    'single-function-proxy',
    'tar.gz',
    90,
    'single-function proxy artifact strategy を end-to-end で検証する sample/test definition。Project 1 の core source output seed とは別管理にする。',
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
    SELECT 'SAMPLE-SINGLE-PROXY-SERVER' AS source_output_key
    UNION ALL
    SELECT 'SAMPLE-SINGLE-PROXY-CLIENT'
) AS target
WHERE p.project_key = 'MTOOL'
  AND (
      (c.source_name = 'dbtable' AND f.function_name IN ('GetdbtableList', 'Insertdbtable'))
      OR (c.source_name = 'Project' AND f.function_name = 'GetProjectbyOwnerOrUserSecurityList')
  );
