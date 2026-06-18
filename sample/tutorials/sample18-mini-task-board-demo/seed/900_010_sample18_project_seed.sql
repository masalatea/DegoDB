INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE18',
    'Sample 18 Mini Task Board Demo',
    'sample18-mini-task-board-demo',
    'paused',
    'admin',
    '仮想ユーザー prompt から作る instruction-driven demo。TaskCard の DataClass、DBAccess、HTML、OpenAPI を publish する。'
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
WHERE p.project_key = 'SAMPLE18'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);

