INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE22',
    'Sample 22 Ebook Chapter Workflow Demo',
    'sample22-ebook-chapter-workflow-demo',
    'paused',
    'admin',
    'ebook catalog に chapter workflow と EPUB spine/nav metadata を追加する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE22'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
