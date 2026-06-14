INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE4',
    'Sample 4 Whiteboard',
    'sample53-runtime-whiteboard',
    'paused',
    'admin',
    'legacy `Project.PID = 12` (`Whiteboard`) を一般化した collaboration sample project。collaboration-style metadata を見るための durable sample pack。'
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
WHERE p.project_key = 'SAMPLE4'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
