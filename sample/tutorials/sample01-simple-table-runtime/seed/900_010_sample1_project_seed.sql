INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE1',
    'Sample 1 Simple Table',
    'sample01-simple-table-runtime',
    'paused',
    'admin',
    '1 table の live schema import から canonical dataclass PHP output までを通す最小 sample project。'
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
WHERE p.project_key = 'SAMPLE1'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
