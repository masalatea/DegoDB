INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE24',
    'Sample 24 Ebook Public Reader Site Demo',
    'sample24-ebook-public-reader-site-demo',
    'paused',
    'admin',
    '公開中の本、章、EPUB download metadata を reader site / app API 向けに publish する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE24'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
