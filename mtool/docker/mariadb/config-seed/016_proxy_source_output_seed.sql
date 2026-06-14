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
    'DBIMPORT-PROXY-SERVER',
    'Mtool DB Import Proxy Server',
    'php',
    'DBaaSProxyServer',
    'Release',
    '',
    'work/source-outputs/MTOOL/DBIMPORT-PROXY-SERVER',
    'work/staging/source-outputs/MTOOL/DBIMPORT-PROXY-SERVER',
    'https://example.invalid/proxy_dbimport',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/DBIMPORT-PROXY-SERVER',
    'custom-proxy-server',
    'custom-proxy',
    'tar.gz',
    20,
    '旧 ProjectSourceOutput.PID=300 を取り込んだ seed。custom proxy build plan をもとに PHP proxy server artifact を生成する。',
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
    'DBIMPORT-PROXY-CLIENT',
    'Mtool DB Import Proxy Client',
    'cs',
    'DBaaSProxyClient',
    'Release',
    '',
    'work/source-outputs/MTOOL/DBIMPORT-PROXY-CLIENT',
    'work/staging/source-outputs/MTOOL/DBIMPORT-PROXY-CLIENT',
    'https://example.invalid/proxy_dbimport',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/DBIMPORT-PROXY-CLIENT',
    'custom-proxy-client',
    'custom-proxy',
    'tar.gz',
    30,
    '旧 ProjectSourceOutput.PID=301 を取り込んだ seed。custom proxy build plan をもとに C# proxy client artifact を生成する。legacy の TargetServerProjectSourceOutputPID=300 は proxy_base_url に反映する。',
    'bootstrap-default'
FROM projects AS p
WHERE p.project_key = 'MTOOL';

INSERT IGNORE INTO project_custom_proxy_source_output_targets (
    custom_proxy_id,
    source_output_key
)
SELECT
    cp.id,
    target.source_output_key
FROM project_custom_proxies AS cp
INNER JOIN projects AS p
    ON p.id = cp.project_id
INNER JOIN (
    SELECT 'DBIMPORT-PROXY-SERVER' AS source_output_key
    UNION ALL
    SELECT 'DBIMPORT-PROXY-CLIENT'
) AS target
WHERE p.project_key = 'MTOOL'
  AND cp.custom_proxy_key IN (
      'DB-IMPORT',
      'DB-GETTABLEDEFINITION',
      'DB-GETCOLUMNDEFINITION',
      'DB-GETPROJECTLIST'
  );

UPDATE project_custom_proxies AS cp
INNER JOIN projects AS p
    ON p.id = cp.project_id
SET cp.notes = CASE cp.custom_proxy_key
    WHEN 'DB-IMPORT' THEN '旧 daCustomProxy.PID=9 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ再マップ済み。'
    WHEN 'DB-GETTABLEDEFINITION' THEN '旧 daCustomProxy.PID=10 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ再マップ済み。'
    WHEN 'DB-GETCOLUMNDEFINITION' THEN '旧 daCustomProxy.PID=11 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ再マップ済み。'
    WHEN 'DB-GETPROJECTLIST' THEN '旧 daCustomProxy.PID=12 を取り込んだ seed。target source output は DBIMPORT-PROXY-SERVER / DBIMPORT-PROXY-CLIENT へ再マップ済み。'
    ELSE cp.notes
END
WHERE p.project_key = 'MTOOL'
  AND cp.custom_proxy_key IN (
      'DB-IMPORT',
      'DB-GETTABLEDEFINITION',
      'DB-GETCOLUMNDEFINITION',
      'DB-GETPROJECTLIST'
  );
