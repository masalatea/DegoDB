INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE30',
    'Sample 30 No-Code App-local Sync Demo',
    'sample30-no-code-app-local-sync-demo',
    'paused',
    'admin',
    'First sync-backed no-code demo connecting generated action intent to managed operation outbox and App-local SQLite handling.'
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
WHERE p.project_key = 'SAMPLE30'
ON DUPLICATE KEY UPDATE
    role_code = VALUES(role_code),
    can_administer = VALUES(can_administer);
