INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE20',
    'Sample 20 Content Publishing Demo',
    'sample20-content-publishing-demo',
    'paused',
    'admin',
    'JSON-first content model を、公開済み記事の一覧・詳細・HTML・OpenAPI へ具体化する tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE20'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
