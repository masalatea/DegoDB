.read ../schema/schema.sql
.read ../seed.sql

SELECT
    project_key,
    name
FROM projects
WHERE is_archived = 0
ORDER BY project_key;

SELECT
    p.project_key,
    t.task_key,
    t.title,
    t.status,
    t.priority,
    t.due_date
FROM tasks t
JOIN projects p
    ON p.id = t.project_id
WHERE p.project_key = 'proj-alpha'
ORDER BY t.priority DESC, t.task_key;

INSERT INTO tasks (
    task_key,
    project_id,
    title,
    status,
    priority,
    internal_note,
    due_date,
    created_at,
    updated_at
) VALUES (
    'task-alpha-3',
    (SELECT id FROM projects WHERE project_key = 'proj-alpha'),
    'Confirm generated API contract',
    'open',
    1,
    NULL,
    NULL,
    '2026-06-21 11:00:00',
    '2026-06-21 11:00:00'
);

UPDATE tasks
SET
    status = 'done',
    updated_at = '2026-06-21 11:30:00'
WHERE task_key = 'task-alpha-3';

SELECT
    task_key,
    title,
    status,
    updated_at
FROM tasks
WHERE task_key = 'task-alpha-3';
