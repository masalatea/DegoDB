INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE17',
    'Sample 17 Multi Output Project',
    'sample17-multi-output-project',
    'paused',
    'admin',
    '同じ project から DataClass、DBAccess、HTML、OpenAPI の複数 Source Output を publish する tutorial capstone。'
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
WHERE p.project_key = 'SAMPLE17'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
