INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE25',
    'Sample 25 Ebook Editor Auth CMS Demo',
    'sample25-ebook-editor-auth-cms-demo',
    'paused',
    'admin',
    '編集者向けの章更新・公開 API を ProjectToken protected proxy として publish する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE25'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
