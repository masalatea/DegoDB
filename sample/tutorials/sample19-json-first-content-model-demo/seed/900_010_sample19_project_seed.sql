INSERT INTO projects (
    project_key,
    name,
    slug,
    lifecycle_status,
    owner_login_id,
    description
) VALUES (
    'SAMPLE19',
    'Sample 19 JSON-first Content Model',
    'sample19-json-first-content-model-demo',
    'paused',
    'admin',
    'DB を知らないユーザーの JSON 入力を、AI が normalized DB / API 設計へ変換する見立ての tutorial sample project。'
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
WHERE p.project_key = 'SAMPLE19'
ON DUPLICATE KEY UPDATE
    can_administer = VALUES(can_administer);
