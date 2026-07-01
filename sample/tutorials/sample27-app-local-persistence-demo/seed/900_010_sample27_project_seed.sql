INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE27',
    'Sample 27 App-local Persistence Demo',
    'sample27-app-local-persistence-demo',
    'paused',
    'admin',
    'shared contract から App-local SQLite schema / DBAccess helper を通し、server read -> DTO -> app save -> app read を確認する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE27'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
