INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE8',
    'Sample 8 Misc',
    'sample56-runtime-misc-proxy',
    'paused',
    'admin',
    'legacy `Project.PID = 16` (`Mtool Work`) を一般化した misc sample project。tooling / proxy mix の再現確認に使う durable sample pack。'
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
WHERE p.project_key = 'SAMPLE8'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
