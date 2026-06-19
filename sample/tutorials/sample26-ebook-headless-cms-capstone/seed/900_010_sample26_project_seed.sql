INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE26',
    'Sample 26 Ebook Headless CMS Capstone',
    'sample26-ebook-headless-cms-capstone',
    'paused',
    'admin',
    '電子書籍 site / app 向け public API、reader HTML、ProjectToken protected editor API、metadata bundle を 1 project から publish する tutorial capstone project。'
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
WHERE p.project_key = 'SAMPLE26'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
