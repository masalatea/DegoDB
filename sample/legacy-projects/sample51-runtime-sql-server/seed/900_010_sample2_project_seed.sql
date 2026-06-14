INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE2',
    'Sample 2 SQL Server',
    'sample51-runtime-sql-server',
    'paused',
    'admin',
    'legacy `Project.PID = 9` (`Test for SQL Server`) を一般化した SQL Server sample project。Project 1 = MTOOL の次段で扱う durable sample pack。'
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
WHERE p.project_key = 'SAMPLE2'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
