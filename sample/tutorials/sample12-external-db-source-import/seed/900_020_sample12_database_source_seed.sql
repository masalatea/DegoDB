DELETE FROM database_sources
WHERE source_key = 'sample12_lab';

INSERT INTO database_sources (
    source_key,
    label,
    description,
    host,
    port,
    database_name,
    user_name,
    password,
    supports_live_schema_import,
    supports_proxy_runtime_read,
    proxy_runtime_priority,
    source_of_truth
) VALUES (
    'sample12_lab',
    'Sample12 external lab DB',
    'Tutorial external named source backed by the sample db-lab schema. Used only for table import.',
    'db-lab',
    '3306',
    'lab_app',
    'lab_app',
    'sample12_lab_password',
    1,
    0,
    1200,
    'manual'
)
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    host = VALUES(host),
    port = VALUES(port),
    database_name = VALUES(database_name),
    user_name = VALUES(user_name),
    password = VALUES(password),
    supports_live_schema_import = VALUES(supports_live_schema_import),
    supports_proxy_runtime_read = VALUES(supports_proxy_runtime_read),
    proxy_runtime_priority = VALUES(proxy_runtime_priority),
    source_of_truth = VALUES(source_of_truth);
