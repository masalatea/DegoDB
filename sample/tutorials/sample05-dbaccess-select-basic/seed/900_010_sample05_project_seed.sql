INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE05',
    'Sample 05 DBAccess Select Basic',
    'sample05-dbaccess-select-basic',
    'paused',
    'admin',
    '1 table + 1 db access class + 1 selectlist function で canonical DBAccess PHP output を確認する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE05'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
