<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/sql_dialect.php';

function app_lab_sample18_task_board_path(): string
{
    return '/samples/sample18-task-board';
}

function app_lab_sample18_task_board_is_available(PDO $pdo): bool
{
    if (!app_sql_table_exists($pdo, 'projects')) {
        return false;
    }

    $statement = $pdo->prepare("SELECT 1 FROM projects WHERE project_key = 'SAMPLE18' LIMIT 1");
    $statement->execute();

    return $statement->fetchColumn() !== false;
}

function app_lab_sample18_task_board_create_schema(PDO $pdo): void
{
    if (app_sql_table_exists($pdo, 'TaskCard')) {
        return;
    }

    if (app_sql_dialect_from_pdo($pdo) === 'sqlite') {
        $pdo->exec(
            "CREATE TABLE TaskCard (
                Id INTEGER PRIMARY KEY AUTOINCREMENT,
                Title TEXT NOT NULL,
                Body TEXT NOT NULL,
                Status TEXT NOT NULL DEFAULT 'todo',
                AssignedTo TEXT NOT NULL DEFAULT '',
                Priority INTEGER NOT NULL DEFAULT 0,
                DueDate TEXT DEFAULT NULL,
                CompletedAt TEXT DEFAULT NULL,
                UpdatedAt TEXT NOT NULL
            )",
        );
        return;
    }

    $pdo->exec(
        "CREATE TABLE TaskCard (
            Id BIGINT NOT NULL AUTO_INCREMENT,
            Title VARCHAR(255) NOT NULL,
            Body TEXT NOT NULL,
            Status VARCHAR(32) NOT NULL DEFAULT 'todo',
            AssignedTo VARCHAR(100) NOT NULL DEFAULT '',
            Priority INT NOT NULL DEFAULT 0,
            DueDate DATE DEFAULT NULL,
            CompletedAt DATETIME DEFAULT NULL,
            UpdatedAt DATETIME NOT NULL,
            PRIMARY KEY (Id)
        )",
    );
}

/**
 * @return list<array<string,mixed>>
 */
function app_lab_sample18_task_board_fetch_rows(PDO $pdo, string $status): array
{
    $sql = 'SELECT Id, Title, Body, Status, AssignedTo, Priority, DueDate, CompletedAt, UpdatedAt FROM TaskCard';
    $params = [];
    if ($status !== '') {
        $sql .= ' WHERE Status = :status';
        $params[':status'] = $status;
    }
    $sql .= ' ORDER BY CASE WHEN DueDate IS NULL THEN 1 ELSE 0 END, DueDate ASC, Priority DESC, Id ASC';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * @return array<string,mixed>|null
 */
function app_lab_sample18_task_board_fetch_row(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare('SELECT Id, Title, Body, Status, AssignedTo, Priority, DueDate, CompletedAt, UpdatedAt FROM TaskCard WHERE Id = :id');
    $statement->execute([':id' => $id]);
    $row = $statement->fetch();

    return is_array($row) ? $row : null;
}

function app_lab_sample18_task_board_redirect(array $request, string $message = ''): void
{
    $location = app_lab_sample18_task_board_path();
    if ($message !== '') {
        $location .= '?message=' . rawurlencode($message);
    }
    app_send_redirect_response($request, $location);
}

function app_lab_sample18_task_board_handle_post(PDO $pdo, array $request): void
{
    if (!app_verify_csrf_token(app_post_param('_csrf_token'))) {
        app_send_html_response_headers($request, 400);
        echo '<!DOCTYPE html><meta charset="utf-8"><p>CSRF token is invalid.</p>';
        return;
    }

    $action = app_post_param('action');
    $now = date('Y-m-d H:i:s');

    if ($action === 'create') {
        $title = trim(app_post_param('title'));
        if ($title === '') {
            app_lab_sample18_task_board_redirect($request, 'Title is required.');
            return;
        }

        $priority = max(0, min(100, (int) app_post_param('priority', '10')));
        $dueDate = trim(app_post_param('due_date'));
        if ($dueDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) !== 1) {
            $dueDate = '';
        }

        $statement = $pdo->prepare(
            'INSERT INTO TaskCard (Title, Body, Status, AssignedTo, Priority, DueDate, CompletedAt, UpdatedAt)
             VALUES (:title, :body, :status, :assigned_to, :priority, :due_date, NULL, :updated_at)',
        );
        $statement->execute([
            ':title' => $title,
            ':body' => trim(app_post_param('body')),
            ':status' => 'todo',
            ':assigned_to' => trim(app_post_param('assigned_to')),
            ':priority' => $priority,
            ':due_date' => $dueDate !== '' ? $dueDate : null,
            ':updated_at' => $now,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task created.');
        return;
    }

    $id = (int) app_post_param('id', '0');
    if ($id <= 0) {
        app_lab_sample18_task_board_redirect($request, 'Task id is invalid.');
        return;
    }

    if ($action === 'update') {
        $title = trim(app_post_param('title'));
        if ($title === '') {
            app_lab_sample18_task_board_redirect($request, 'Title is required.');
            return;
        }

        $status = trim(app_post_param('status'));
        if (!in_array($status, ['todo', 'doing', 'done'], true)) {
            $status = 'todo';
        }

        $priority = max(0, min(100, (int) app_post_param('priority', '10')));
        $dueDate = trim(app_post_param('due_date'));
        if ($dueDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) !== 1) {
            $dueDate = '';
        }

        $completedAt = $status === 'done' ? $now : null;
        $statement = $pdo->prepare(
            'UPDATE TaskCard
             SET Title = :title,
                 Body = :body,
                 Status = :status,
                 AssignedTo = :assigned_to,
                 Priority = :priority,
                 DueDate = :due_date,
                 CompletedAt = :completed_at,
                 UpdatedAt = :updated_at
             WHERE Id = :id',
        );
        $statement->execute([
            ':title' => $title,
            ':body' => trim(app_post_param('body')),
            ':status' => $status,
            ':assigned_to' => trim(app_post_param('assigned_to')),
            ':priority' => $priority,
            ':due_date' => $dueDate !== '' ? $dueDate : null,
            ':completed_at' => $completedAt,
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task updated.');
        return;
    }

    if ($action === 'complete') {
        $statement = $pdo->prepare(
            "UPDATE TaskCard
             SET Status = 'done', CompletedAt = :completed_at, UpdatedAt = :updated_at
             WHERE Id = :id",
        );
        $statement->execute([
            ':completed_at' => $now,
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task completed.');
        return;
    }

    if ($action === 'reopen') {
        $statement = $pdo->prepare(
            "UPDATE TaskCard
             SET Status = 'todo', CompletedAt = NULL, UpdatedAt = :updated_at
             WHERE Id = :id",
        );
        $statement->execute([
            ':updated_at' => $now,
            ':id' => $id,
        ]);

        app_lab_sample18_task_board_redirect($request, 'Task reopened.');
        return;
    }

    if ($action === 'delete') {
        $statement = $pdo->prepare('DELETE FROM TaskCard WHERE Id = :id');
        $statement->execute([':id' => $id]);

        app_lab_sample18_task_board_redirect($request, 'Task deleted.');
        return;
    }

    app_lab_sample18_task_board_redirect($request, 'Unknown action.');
}

function app_render_lab_sample18_task_board_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab' && $app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 実験用サイト または 設定変更用サイト でのみ利用します。');
        return;
    }

    $pdo = app_create_config_pdo($app);
    if (!app_lab_sample18_task_board_is_available($pdo)) {
        app_render_not_found_page($app, $request);
        return;
    }

    app_lab_sample18_task_board_create_schema($pdo);

    if (app_request_method_is($request, 'POST')) {
        app_lab_sample18_task_board_handle_post($pdo, $request);
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $status = trim(app_query_param('status'));
    if (!in_array($status, ['', 'todo', 'doing', 'done'], true)) {
        $status = '';
    }

    $rows = app_lab_sample18_task_board_fetch_rows($pdo, $status);
    $editId = (int) app_query_param('edit_id', '0');
    $editRow = $editId > 0 ? app_lab_sample18_task_board_fetch_row($pdo, $editId) : null;
    $csrfToken = app_csrf_token();
    $message = trim(app_query_param('message'));

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sample18 Mini Task Board</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #202124;
            --muted: #68707a;
            --line: #d8dee6;
            --panel: #f7f9fb;
            --accent: #146c94;
            --danger: #a33a2a;
            --done: #2c7a4b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            color: var(--ink);
            font-family: Arial, sans-serif;
            margin: 0;
            background: #ffffff;
        }

        main {
            margin: 0 auto;
            max-width: 1120px;
            padding: 28px 20px 48px;
        }

        header {
            border-bottom: 1px solid var(--line);
            margin-bottom: 22px;
            padding-bottom: 18px;
        }

        h1 {
            font-size: 1.8rem;
            line-height: 1.2;
            margin: 0 0 8px;
        }

        p {
            line-height: 1.55;
        }

        .muted {
            color: var(--muted);
        }

        .notice {
            background: #eef7f3;
            border: 1px solid #b8d8c7;
            color: #1f5f3d;
            margin: 0 0 18px;
            padding: 10px 12px;
        }

        .toolbar,
        form.create {
            align-items: end;
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
            margin: 18px 0;
        }

        form.create {
            background: var(--panel);
            border: 1px solid var(--line);
            padding: 14px;
        }

        form.create.editing {
            background: #fff8e6;
            border-color: #d7bd75;
        }

        label {
            display: grid;
            gap: 5px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        input,
        select,
        textarea,
        button,
        .button {
            border: 1px solid var(--line);
            font: inherit;
            min-height: 36px;
            padding: 7px 9px;
        }

        textarea {
            min-height: 36px;
            resize: vertical;
        }

        button,
        .button {
            background: #ffffff;
            color: var(--ink);
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            text-decoration: none;
            white-space: nowrap;
        }

        button.primary {
            background: var(--accent);
            border-color: var(--accent);
            color: #ffffff;
        }

        button.danger {
            border-color: #d6aaa3;
            color: var(--danger);
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border-bottom: 1px solid var(--line);
            padding: 10px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: var(--panel);
            font-size: 0.82rem;
        }

        .status {
            border: 1px solid var(--line);
            display: inline-block;
            font-size: 0.8rem;
            min-width: 64px;
            padding: 3px 7px;
            text-align: center;
        }

        .status.done {
            border-color: #9ccbaa;
            color: var(--done);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .actions form {
            margin: 0;
        }

        @media (max-width: 780px) {
            .toolbar,
            form.create {
                grid-template-columns: 1fr;
            }

            table,
            thead,
            tbody,
            tr,
            th,
            td {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                border: 1px solid var(--line);
                margin-bottom: 10px;
            }

            td {
                border-bottom: 0;
            }
        }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Sample18 Mini Task Board</h1>
        <p class="muted">A tiny running UI for the instruction-driven sample. It reads and writes the sample <code>TaskCard</code> table from the config store.</p>
    </header>

    <?php if ($message !== ''): ?>
        <p class="notice"><?php echo app_h($message); ?></p>
    <?php endif; ?>

    <form class="toolbar" method="get" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
        <label>
            Status filter
            <select name="status">
                <option value=""<?php echo $status === '' ? ' selected' : ''; ?>>all</option>
                <option value="todo"<?php echo $status === 'todo' ? ' selected' : ''; ?>>todo</option>
                <option value="doing"<?php echo $status === 'doing' ? ' selected' : ''; ?>>doing</option>
                <option value="done"<?php echo $status === 'done' ? ' selected' : ''; ?>>done</option>
            </select>
        </label>
        <button type="submit">Apply</button>
        <a class="button" href="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">Reset</a>
    </form>

    <form class="create <?php echo $editRow !== null ? 'editing' : ''; ?>" method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
        <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="action" value="<?php echo $editRow !== null ? 'update' : 'create'; ?>">
        <?php if ($editRow !== null): ?>
            <input type="hidden" name="id" value="<?php echo app_h((string) ($editRow['Id'] ?? '')); ?>">
        <?php endif; ?>
        <label>
            Title
            <input name="title" required maxlength="255" placeholder="Write release note" value="<?php echo app_h((string) ($editRow['Title'] ?? '')); ?>">
        </label>
        <label>
            Body
            <textarea name="body" placeholder="Short task memo"><?php echo app_h((string) ($editRow['Body'] ?? '')); ?></textarea>
        </label>
        <?php if ($editRow !== null): ?>
            <?php $editStatus = (string) ($editRow['Status'] ?? 'todo'); ?>
            <label>
                Status
                <select name="status">
                    <option value="todo"<?php echo $editStatus === 'todo' ? ' selected' : ''; ?>>todo</option>
                    <option value="doing"<?php echo $editStatus === 'doing' ? ' selected' : ''; ?>>doing</option>
                    <option value="done"<?php echo $editStatus === 'done' ? ' selected' : ''; ?>>done</option>
                </select>
            </label>
        <?php endif; ?>
        <label>
            Assigned to
            <input name="assigned_to" maxlength="100" placeholder="Alice" value="<?php echo app_h((string) ($editRow['AssignedTo'] ?? '')); ?>">
        </label>
        <label>
            Priority
            <input name="priority" type="number" min="0" max="100" value="<?php echo app_h((string) ($editRow['Priority'] ?? '10')); ?>">
        </label>
        <label>
            Due date
            <input name="due_date" type="date" value="<?php echo app_h((string) ($editRow['DueDate'] ?? '')); ?>">
        </label>
        <button class="primary" type="submit"><?php echo $editRow !== null ? 'Update Task' : 'Add Task'; ?></button>
        <?php if ($editRow !== null): ?>
            <a class="button" href="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">Cancel</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
        <tr>
            <th>Task</th>
            <th>Status</th>
            <th>Assigned</th>
            <th>Priority</th>
            <th>Due</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <?php $rowStatus = (string) ($row['Status'] ?? ''); ?>
            <tr>
                <td>
                    <strong><?php echo app_h((string) ($row['Title'] ?? '')); ?></strong>
                    <div class="muted"><?php echo app_h((string) ($row['Body'] ?? '')); ?></div>
                </td>
                <td><span class="status <?php echo $rowStatus === 'done' ? 'done' : ''; ?>"><?php echo app_h($rowStatus); ?></span></td>
                <td><?php echo app_h((string) ($row['AssignedTo'] ?? '')); ?></td>
                <td><?php echo app_h((string) ($row['Priority'] ?? '')); ?></td>
                <td><?php echo app_h((string) ($row['DueDate'] ?? '')); ?></td>
                <td>
                    <div class="actions">
                        <a class="button" href="<?php echo app_h(app_lab_sample18_task_board_path() . '?edit_id=' . rawurlencode((string) ($row['Id'] ?? ''))); ?>">Edit</a>
                        <?php if ($rowStatus === 'done'): ?>
                            <form method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
                                <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="reopen">
                                <input type="hidden" name="id" value="<?php echo app_h((string) ($row['Id'] ?? '')); ?>">
                                <button type="submit">Reopen</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
                                <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="id" value="<?php echo app_h((string) ($row['Id'] ?? '')); ?>">
                                <button type="submit">Complete</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?php echo app_h(app_lab_sample18_task_board_path()); ?>">
                            <input type="hidden" name="_csrf_token" value="<?php echo app_h($csrfToken); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo app_h((string) ($row['Id'] ?? '')); ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
    <?php
}
