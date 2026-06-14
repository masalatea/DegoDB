INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE09',
    'Sample 09 DBAccess Aggregate Report',
    'sample09-dbaccess-aggregate-report',
    'paused',
    'admin',
    '2 live table + 1 report model table の aggregate DBAccess PHP output を確認する tutorial sample project。'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    lifecycle_status = VALUES(lifecycle_status),
    owner_login_id = VALUES(owner_login_id),
    description = VALUES(description);

INSERT INTO project_memberships (
    project_id,
    login_id,
    role_code,
    can_administer
)
SELECT
    p.id,
    'admin',
    'owner',
    1
FROM projects AS p
WHERE p.project_key = 'SAMPLE09'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
