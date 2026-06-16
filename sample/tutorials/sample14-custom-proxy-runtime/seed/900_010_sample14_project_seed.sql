INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE14',
    'Sample 14 Custom Proxy Runtime',
    'sample14-custom-proxy-runtime',
    'paused',
    'admin',
    'custom proxy metadata から PHP proxy server artifact を publish する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE14'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
