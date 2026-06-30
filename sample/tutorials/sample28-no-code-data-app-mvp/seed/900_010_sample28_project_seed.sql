INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE28',
    'Sample 28 No-Code Data App MVP',
    'sample28-no-code-data-app-mvp',
    'paused',
    'admin',
    'User-facing data-first no-code app MVP sample proving generated list/detail/form and managed operation metadata.'
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
WHERE p.project_key = 'SAMPLE28'
ON DUPLICATE KEY UPDATE
    role_code = VALUES(role_code),
    can_administer = VALUES(can_administer);
