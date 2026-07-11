INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE32',
    'Sample 32 No-Code UI Test Lab',
    'sample32-no-code-ui-test-lab',
    'paused',
    'admin',
    'Dedicated no-code UI fixture lab for fast JSON and DOM contract tests.'
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
WHERE p.project_key = 'SAMPLE32'
ON DUPLICATE KEY UPDATE
    role_code = VALUES(role_code),
    can_administer = VALUES(can_administer);
