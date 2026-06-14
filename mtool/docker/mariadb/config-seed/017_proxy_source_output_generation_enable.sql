UPDATE project_source_outputs AS so
INNER JOIN projects AS p
    ON p.id = so.project_id
SET so.proxy_base_url = 'https://example.invalid/proxy_dbimport',
    so.runtime_source_relative_path = 'mtool/proxy-source-outputs/MTOOL/DBIMPORT-PROXY-SERVER',
    so.artifact_strategy = 'custom-proxy-server',
    so.target_binding_type = 'custom-proxy',
    so.output_archive_format = 'tar.gz',
    so.notes = '旧 ProjectSourceOutput.PID=300 を取り込んだ seed。custom proxy build plan をもとに PHP proxy server artifact を生成する。'
WHERE p.project_key = 'MTOOL'
  AND so.source_output_key = 'DBIMPORT-PROXY-SERVER';

UPDATE project_source_outputs AS so
INNER JOIN projects AS p
    ON p.id = so.project_id
SET so.proxy_base_url = 'https://example.invalid/proxy_dbimport',
    so.runtime_source_relative_path = 'mtool/proxy-source-outputs/MTOOL/DBIMPORT-PROXY-CLIENT',
    so.artifact_strategy = 'custom-proxy-client',
    so.target_binding_type = 'custom-proxy',
    so.output_archive_format = 'tar.gz',
    so.notes = '旧 ProjectSourceOutput.PID=301 を取り込んだ seed。custom proxy build plan をもとに C# proxy client artifact を生成する。legacy の TargetServerProjectSourceOutputPID=300 は proxy_base_url に反映する。'
WHERE p.project_key = 'MTOOL'
  AND so.source_output_key = 'DBIMPORT-PROXY-CLIENT';
