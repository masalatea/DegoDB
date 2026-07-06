INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE31',
    'Sample 31 No-Code Inventory Request Demo',
    'sample31-no-code-inventory-request-demo',
    'paused',
    'admin',
    'Third data-first no-code sample using inventory request fields to exercise generated runtime behavior beyond ticket and support case domains.'
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
WHERE p.project_key = 'SAMPLE31'
ON DUPLICATE KEY UPDATE
    role_code = VALUES(role_code),
    can_administer = VALUES(can_administer);
