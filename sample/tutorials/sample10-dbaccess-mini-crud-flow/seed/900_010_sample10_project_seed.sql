INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    php_namespace,
    description
) VALUES (
    'SAMPLE10',
    'Sample 10 DBAccess Mini CRUD Flow',
    'sample10-dbaccess-mini-crud-flow',
    'paused',
    'admin',
    'DegoDB\\Sample\\Sample10',
    '1 table + list/detail/create/update/delete で canonical DBAccess PHP output を確認する tutorial sample project。'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    slug = VALUES(slug),
    lifecycle_status = VALUES(lifecycle_status),
    owner_login_id = VALUES(owner_login_id),
    php_namespace = VALUES(php_namespace),
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
WHERE p.project_key = 'SAMPLE10'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
