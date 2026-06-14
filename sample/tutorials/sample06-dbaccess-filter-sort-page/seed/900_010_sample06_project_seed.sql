INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE06',
    'Sample 06 DBAccess Filter Sort Page',
    'sample06-dbaccess-filter-sort-page',
    'paused',
    'admin',
    '1 table + 1 filtered selectlist function で canonical DBAccess PHP output を確認する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE06'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
