INSERT INTO projects (
    project_key,
    name,
    is_archived,
    created_at
) VALUES
    ('proj-alpha', 'Alpha Launch', 0, '2026-06-21 09:00:00'),
    ('proj-archive', 'Archived Cleanup', 1, '2026-06-20 09:00:00');

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
) VALUES
    (
        'task-alpha-1',
        1,
        'Prepare launch checklist',
        'open',
        2,
        'Ask legal to review wording.',
        '2026-06-25',
        '2026-06-21 09:10:00',
        '2026-06-21 09:10:00'
    ),
    (
        'task-alpha-2',
        1,
        'Publish internal dashboard',
        'done',
        1,
        NULL,
        '2026-06-22',
        '2026-06-21 09:15:00',
        '2026-06-21 10:00:00'
    ),
    (
        'task-archive-1',
        2,
        'Remove old labels',
        'open',
        0,
        NULL,
        NULL,
        '2026-06-20 09:30:00',
        '2026-06-20 09:30:00'
    );
