INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE03',
    'Sample 03 Dataclass Lookup And Helper',
    'sample03-dataclass-lookup-and-helper',
    'paused',
    'admin',
    'lookup / caption 向きの 2 table から canonical DataClass PHP output を確認し、helper は custom layer へ逃がす前提を共有する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE03'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
