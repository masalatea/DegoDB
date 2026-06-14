<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/project_host_assignment_repository.php';
require_once __DIR__ . '/project_security_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

/**
 * @return array{
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string
 * }
 */
function app_project_host_assignments_default_form_input(): array
{
    return [
        'apache_setting_name' => '',
        'server_local_name' => '',
        'virtual_host_name' => '',
        'template_name' => '',
        'notes' => '',
    ];
}

/**
 * @return array{
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string
 * }
 */
function app_project_host_assignments_form_input_from_post(): array
{
    return [
        'apache_setting_name' => trim(app_post_param('apache_setting_name')),
        'server_local_name' => trim(app_post_param('server_local_name')),
        'virtual_host_name' => trim(app_post_param('virtual_host_name')),
        'template_name' => trim(app_post_param('template_name')),
        'notes' => trim(app_post_param('notes')),
    ];
}

/**
 * @param array{
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string
 * } $input
 * @return array{
 *     input:array{
 *         apache_setting_name:string,
 *         server_local_name:string,
 *         virtual_host_name:string,
 *         template_name:string,
 *         notes:string
 *     },
 *     errors:list<string>
 * }
 */
function app_project_host_assignments_validate_form_input(array $input): array
{
    $normalized = [
        'apache_setting_name' => trim($input['apache_setting_name']),
        'server_local_name' => trim($input['server_local_name']),
        'virtual_host_name' => trim($input['virtual_host_name']),
        'template_name' => trim($input['template_name']),
        'notes' => trim($input['notes']),
    ];
    $errors = [];

    foreach ([
        'apache_setting_name' => 128,
        'server_local_name' => 128,
        'virtual_host_name' => 191,
        'template_name' => 128,
    ] as $field => $maxLength) {
        if ($normalized[$field] === '') {
            $errors[] = str_replace('_', ' ', $field) . ' は必須です。';
            continue;
        }

        if (mb_strlen($normalized[$field]) > $maxLength) {
            $errors[] = str_replace('_', ' ', $field) . ' は ' . (string) $maxLength . ' 文字以内にしてください。';
        }
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param list<array{
 *     id:int,
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string,
 *     source_of_truth:string
 * }> $items
 * @return array<int,array{
 *     id:int,
 *     apache_setting_name:string,
 *     server_local_name:string,
 *     virtual_host_name:string,
 *     template_name:string,
 *     notes:string,
 *     source_of_truth:string
 * }>
 */
function app_project_host_assignments_index_by_id(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $indexed[(int) ($item['id'] ?? 0)] = $item;
    }

    return $indexed;
}

function app_render_project_host_assignments_page(array $app, array $request): void
{
    $context = app_project_security_route_bootstrap($app, $request, ['GET', 'POST']);
    if ($context === null) {
        return;
    }

    $projectKey = $context['project_key'];
    $project = $context['project'];
    $assignmentResult = app_fetch_project_host_assignments($app, $projectKey);
    if (!$assignmentResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Host Assignments</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>host assignment の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($assignmentResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    $assignments = $assignmentResult['items'];
    $assignmentsById = app_project_host_assignments_index_by_id($assignments);
    $selectedAssignmentId = max(0, (int) app_query_param('assignment_id', '0'));
    $selectedAssignment = $assignmentsById[$selectedAssignmentId] ?? null;
    $formMode = $selectedAssignment === null ? 'create' : 'edit';
    $formInput = $selectedAssignment === null
        ? app_project_host_assignments_default_form_input()
        : [
            'apache_setting_name' => $selectedAssignment['apache_setting_name'],
            'server_local_name' => $selectedAssignment['server_local_name'],
            'virtual_host_name' => $selectedAssignment['virtual_host_name'],
            'template_name' => $selectedAssignment['template_name'],
            'notes' => $selectedAssignment['notes'],
        ];
    $errors = [];
    $updated = app_query_param('updated') === '1';
    $created = app_query_param('created') === '1';
    $deleted = app_query_param('deleted') === '1';

    if (app_request_method_is($request, 'POST')) {
        $updated = false;
        $created = false;
        $deleted = false;
        $formMode = app_post_param('assignment_action') === 'update' ? 'edit' : 'create';
        $selectedAssignmentId = max(0, (int) app_post_param('assignment_id'));

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $postedProjectKey = app_normalize_project_key(app_post_param('project_key'));
            if ($postedProjectKey !== $projectKey) {
                $errors[] = '更新対象の project key が route と一致しません。';
            }

            $action = app_post_param('assignment_action');
            if ($action === 'delete') {
                if ($selectedAssignmentId <= 0) {
                    $errors[] = '削除対象の host assignment を選択してください。';
                } else {
                    $deleteResult = app_delete_project_host_assignment($app, $projectKey, $selectedAssignmentId);
                    if ($deleteResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            app_project_host_assignments_path($projectKey) . '?deleted=1',
                        );
                        return;
                    }

                    $errors[] = $deleteResult['error'];
                }
            } else {
                $formInput = app_project_host_assignments_form_input_from_post();
                $validation = app_project_host_assignments_validate_form_input($formInput);
                $formInput = $validation['input'];
                $errors = array_merge($errors, $validation['errors']);

                if ($errors === []) {
                    if ($action === 'update') {
                        if ($selectedAssignmentId <= 0) {
                            $errors[] = '更新対象の host assignment を選択してください。';
                        } else {
                            $saveResult = app_update_project_host_assignment(
                                $app,
                                $projectKey,
                                $selectedAssignmentId,
                                $formInput,
                            );
                            if ($saveResult['ok']) {
                                app_send_redirect_response(
                                    $request,
                                    app_project_host_assignments_path($projectKey)
                                    . '?assignment_id=' . rawurlencode((string) $selectedAssignmentId)
                                    . '&updated=1',
                                );
                                return;
                            }

                            $errors[] = $saveResult['error'];
                        }
                    } else {
                        $saveResult = app_create_project_host_assignment($app, $projectKey, $formInput);
                        if ($saveResult['ok']) {
                            app_send_redirect_response(
                                $request,
                                app_project_host_assignments_path($projectKey)
                                . '?assignment_id=' . rawurlencode((string) $saveResult['assignment_id'])
                                . '&created=1',
                            );
                            return;
                        }

                        $errors[] = $saveResult['error'];
                    }
                }
            }
        }
    }

    $statusCode = $errors === [] ? 200 : 422;
    $csrfToken = app_csrf_token();

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Host Assignments</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 84rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card,
        .note-card,
        .message {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .message-success {
            background: #dcfce7;
            border-color: #86efac;
        }
        .message-error {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        .module-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .module-table th,
        .module-table td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .edit-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            background: #f8fafc;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
        label {
            display: block;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        input[type="text"],
        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.55rem 0.7rem;
            font: inherit;
            background: #ffffff;
        }
        textarea {
            min-height: 6rem;
            resize: vertical;
        }
        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        button {
            border: 1px solid #0f172a;
            border-radius: 999px;
            padding: 0.7rem 1.2rem;
            font: inherit;
            background: #0f172a;
            color: #ffffff;
            cursor: pointer;
        }
        .button-secondary {
            background: #ffffff;
            color: #0f172a;
        }
        .button-danger {
            background: #7f1d1d;
            border-color: #7f1d1d;
        }
        .button-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #0f172a;
            border-radius: 999px;
            padding: 0.7rem 1.2rem;
            text-decoration: none;
            color: #0f172a;
            background: #ffffff;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><?php echo app_h($projectKey); ?></a> / host-assignments</p>

    <h1><?php echo app_h($project['name']); ?> Host Assignments</h1>
    <p>旧 `project_host_assignment*.php` の visible 4 列を、まずは current canonical row として保持します。infra setting への最終 split は後段ですが、Phase 1 ではこの landing zone で host assignment 自体は編集できます。</p>

    <?php if ($created): ?>
        <div class="message message-success">
            <strong>created</strong><br>
            host assignment を追加しました。
        </div>
    <?php endif; ?>

    <?php if ($updated): ?>
        <div class="message message-success">
            <strong>updated</strong><br>
            host assignment を保存しました。
        </div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <div class="message message-success">
            <strong>deleted</strong><br>
            host assignment を削除しました。
        </div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="message message-error">
            <strong>save failed</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Current Counts</h2>
            <ul>
                <li>assignments: <code><?php echo app_h((string) count($assignments)); ?></code></li>
                <li>selected assignment: <code><?php echo app_h($selectedAssignment === null ? 'new' : (string) $selectedAssignment['id']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>Current Interpretation</h2>
            <ul>
                <li>旧画面の `Apache Setting / Host / Virtual Host / Template` を current row で保持します。</li>
                <li>Phase 1 では feature coverage を優先し、project 配下で直接編集します。</li>
                <li>後段で必要になれば `Server` / `ApacheSetting` / `ApacheHostSettingTemplate` などの infra catalog へ split します。</li>
            </ul>
        </section>
    </div>

    <section>
        <h2>Current Assignments</h2>
        <?php if ($assignments === []): ?>
            <p class="muted">host assignment はまだありません。</p>
        <?php else: ?>
            <table class="module-table">
                <thead>
                <tr>
                    <th>Apache Setting</th>
                    <th>Host</th>
                    <th>Virtual Host</th>
                    <th>Template</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo app_h($assignment['apache_setting_name']); ?></td>
                        <td><?php echo app_h($assignment['server_local_name']); ?></td>
                        <td><code><?php echo app_h($assignment['virtual_host_name']); ?></code></td>
                        <td><?php echo app_h($assignment['template_name']); ?></td>
                        <td>
                            <a href="<?php echo app_h(app_project_host_assignments_path($projectKey) . '?assignment_id=' . rawurlencode((string) $assignment['id'])); ?>">edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="edit-card">
        <h2><?php echo $formMode === 'edit' ? 'Edit Assignment' : 'Create Assignment'; ?></h2>
        <p class="muted">現在は denormalized row ですが、機能移植の source of truth としてはここを使います。</p>

        <form method="post" action="<?php echo app_h(app_project_host_assignments_path($projectKey)); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="project_key" value="<?php echo app_h($projectKey); ?>">
            <input type="hidden" name="assignment_id" value="<?php echo app_h((string) $selectedAssignmentId); ?>">
            <input type="hidden" name="assignment_action" value="<?php echo app_h($formMode === 'edit' ? 'update' : 'create'); ?>">

            <div class="form-grid">
                <div>
                    <label for="apache_setting_name">Apache Setting</label>
                    <input
                        id="apache_setting_name"
                        name="apache_setting_name"
                        type="text"
                        value="<?php echo app_h($formInput['apache_setting_name']); ?>"
                        placeholder="Apache Main"
                    >
                </div>

                <div>
                    <label for="server_local_name">Host</label>
                    <input
                        id="server_local_name"
                        name="server_local_name"
                        type="text"
                        value="<?php echo app_h($formInput['server_local_name']); ?>"
                        placeholder="app01"
                    >
                </div>

                <div>
                    <label for="virtual_host_name">Virtual Host</label>
                    <input
                        id="virtual_host_name"
                        name="virtual_host_name"
                        type="text"
                        value="<?php echo app_h($formInput['virtual_host_name']); ?>"
                        placeholder="example.local"
                    >
                </div>

                <div>
                    <label for="template_name">Template</label>
                    <input
                        id="template_name"
                        name="template_name"
                        type="text"
                        value="<?php echo app_h($formInput['template_name']); ?>"
                        placeholder="default-vhost"
                    >
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <label for="notes">notes</label>
                <textarea id="notes" name="notes" placeholder="migration note / infra split memo"><?php echo app_h($formInput['notes']); ?></textarea>
            </div>

            <div class="button-row">
                <button type="submit"><?php echo $formMode === 'edit' ? 'Save Assignment' : 'Create Assignment'; ?></button>
                <?php if ($formMode === 'edit'): ?>
                    <a class="button-link" href="<?php echo app_h(app_project_host_assignments_path($projectKey)); ?>">New Assignment</a>
                    <button
                        type="submit"
                        class="button-danger"
                        onclick="this.form.assignment_action.value='delete'; return confirm('この host assignment を削除しますか。');"
                    >Delete Assignment</button>
                <?php endif; ?>
            </div>
        </form>
    </section>
</main>
</body>
</html>
    <?php
}
