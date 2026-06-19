INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE23',
    'Sample 23 Ebook Media Metadata Demo',
    'sample23-ebook-media-metadata-demo',
    'paused',
    'admin',
    '同梱 EPUB fixture の URL、MIME type、file size、checksum を media metadata として扱う tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE23'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
