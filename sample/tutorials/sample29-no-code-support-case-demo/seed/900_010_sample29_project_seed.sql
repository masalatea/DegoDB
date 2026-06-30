INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE29',
    'Sample 29 No-Code Support Case Demo',
    'sample29-no-code-support-case-demo',
    'paused',
    'admin',
    'Second data-first no-code sample using support case read-model fields to exercise richer generated runtime behavior.'
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
WHERE p.project_key = 'SAMPLE29'
ON DUPLICATE KEY UPDATE
    role_code = VALUES(role_code),
    can_administer = VALUES(can_administer);
