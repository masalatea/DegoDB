INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES
    (
        'MTOOL',
        'Mtool',
        'mtool',
        'active',
        'admin',
        '旧 `Project.PID = 1` の `Mtool` を基準にした canonical project seed です。Mtool 本体と `mtool_lib/dbclasses` bootstrap の受け皿として扱います。'
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
WHERE p.project_key = 'MTOOL'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
