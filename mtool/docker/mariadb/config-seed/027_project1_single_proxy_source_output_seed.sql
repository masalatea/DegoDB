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
    'PAYPAL-PROXY-SERVER',
    'Mtool Paypal Proxy Server',
    'php',
    'DBaaSProxyServer',
    'Release',
    '',
    'work/source-outputs/MTOOL/PAYPAL-PROXY-SERVER',
    'work/staging/source-outputs/MTOOL/PAYPAL-PROXY-SERVER',
    '',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/PAYPAL-PROXY-SERVER',
    'single-proxy-server',
    'single-function-proxy',
    'tar.gz',
    40,
    '旧 ProjectSourceOutput.PID=28 (/proxy_paypal) を canonical key へ寄せた core seed。legacy simple proxy row のうち ApacheHostSetting を除く Project / PaypalSubscription function を direct single-proxy artifact として出力する。',
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
    'UPLOADER-PROXY-SERVER',
    'Mtool Uploader Proxy Server',
    'php',
    'DBaaSProxyServer',
    'Release',
    '',
    'work/source-outputs/MTOOL/UPLOADER-PROXY-SERVER',
    'work/staging/source-outputs/MTOOL/UPLOADER-PROXY-SERVER',
    '',
    '',
    '',
    'mtool/proxy-source-outputs/MTOOL/UPLOADER-PROXY-SERVER',
    'single-proxy-server',
    'single-function-proxy',
    'tar.gz',
    50,
    '旧 ProjectSourceOutput.PID=117 (/proxy_uploader) を canonical key へ寄せた core seed。legacy simple proxy row の DropboxUploadToken 1 件を direct single-proxy artifact として出力する。',
    'bootstrap-default'
FROM projects AS p
WHERE p.project_key = 'MTOOL';

INSERT IGNORE INTO project_db_access_function_source_output_targets (
    db_access_function_id,
    source_output_key
)
SELECT
    f.id,
    'PAYPAL-PROXY-SERVER'
FROM project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
WHERE p.project_key = 'MTOOL'
  AND (
      (c.source_name = 'Project' AND f.function_name IN (
          'GetProjectList',
          'GetProjectbyOwnerOrUserSecurityList',
          'GetProject',
          'InsertProject',
          'UpdateProject',
          'DeleteProject'
      ))
      OR (c.source_name = 'PaypalSubscription' AND f.function_name = 'GetActiveEikaiwaSubscriptionList')
  );

INSERT IGNORE INTO project_db_access_function_source_output_targets (
    db_access_function_id,
    source_output_key
)
SELECT
    f.id,
    'UPLOADER-PROXY-SERVER'
FROM project_db_access_functions AS f
INNER JOIN project_db_access_classes AS c
    ON c.id = f.db_access_class_id
INNER JOIN projects AS p
    ON p.id = c.project_id
WHERE p.project_key = 'MTOOL'
  AND c.source_name = 'DropboxUploadToken'
  AND f.function_name = 'GetDropboxUploadToken';
