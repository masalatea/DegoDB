<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/experiment_repository.php';
require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     experiment_key:string,
 *     project_key:string,
 *     name:string,
 *     execution_status:string,
 *     runtime_target:string,
 *     executed_by:string,
 *     updated_at:string,
 *     notes:string
 * } $item
 * @return array{
 *     experiment_key:string,
 *     project_key:string,
 *     name:string,
 *     execution_status:string,
 *     runtime_target:string,
 *     notes:string
 * }
 */
function app_experiment_form_from_item(array $item): array
{
    return [
        'experiment_key' => $item['experiment_key'],
        'project_key' => $item['project_key'],
        'name' => $item['name'],
        'execution_status' => $item['execution_status'],
        'runtime_target' => $item['runtime_target'],
        'notes' => $item['notes'],
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string
 * } $request
 */
function app_render_experiment_list_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab') {
        app_render_forbidden_page($app, $request, 'この route は 実験用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['lab', 'admin'], $principal)) {
        app_render_forbidden_page($app, $request, 'experiments の参照と編集には lab または admin role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $createInput = app_experiment_form_defaults();
    $createErrors = [];
    $editInput = app_experiment_form_defaults();
    $editErrors = [];
    $pageErrors = [];

    $editExperimentKey = strtoupper(trim(app_query_param('edit')));
    $createdExperimentKey = app_query_param('created');
    $updatedExperimentKey = app_query_param('updated');
    $isEditing = $editExperimentKey !== '';

    if (app_request_method_is($request, 'POST')) {
        $intent = app_post_param('_intent', 'create');

        if ($intent === 'update') {
            $validation = app_validate_experiment_form([
                'experiment_key' => app_post_param('experiment_key'),
                'project_key' => app_post_param('project_key'),
                'name' => app_post_param('name'),
                'execution_status' => app_post_param('execution_status'),
                'runtime_target' => app_post_param('runtime_target'),
                'notes' => app_post_param('notes'),
            ]);

            $editInput = $validation['input'];
            $editExperimentKey = $editInput['experiment_key'];
            $isEditing = $editExperimentKey !== '';

            if (!app_verify_csrf_token(app_post_param('_csrf'))) {
                $editErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
            } else {
                $editErrors = $validation['errors'];

                if ($editErrors === []) {
                    $updateResult = app_update_lab_experiment($app, [
                        'experiment_key' => $editInput['experiment_key'],
                        'project_key' => $editInput['project_key'],
                        'name' => $editInput['name'],
                        'execution_status' => $editInput['execution_status'],
                        'runtime_target' => $editInput['runtime_target'],
                        'notes' => $editInput['notes'],
                    ]);

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/experiments?updated=' . rawurlencode($editInput['experiment_key']),
                        );
                        return;
                    }

                    $editErrors[] = $updateResult['error'];
                }
            }
        } elseif ($intent === 'create' || $intent === '') {
            $validation = app_validate_experiment_form([
                'experiment_key' => app_post_param('experiment_key'),
                'project_key' => app_post_param('project_key'),
                'name' => app_post_param('name'),
                'execution_status' => app_post_param('execution_status'),
                'runtime_target' => app_post_param('runtime_target'),
                'notes' => app_post_param('notes'),
            ]);

            $createInput = $validation['input'];

            if (!app_verify_csrf_token(app_post_param('_csrf'))) {
                $createErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
            } else {
                $createErrors = $validation['errors'];

                if ($createErrors === []) {
                    $createResult = app_insert_lab_experiment($app, [
                        'experiment_key' => $createInput['experiment_key'],
                        'project_key' => $createInput['project_key'],
                        'name' => $createInput['name'],
                        'execution_status' => $createInput['execution_status'],
                        'runtime_target' => $createInput['runtime_target'],
                        'executed_by' => $principal['id'],
                        'notes' => $createInput['notes'],
                    ]);

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/experiments?created=' . rawurlencode($createInput['experiment_key']),
                        );
                        return;
                    }

                    $createErrors[] = $createResult['error'];
                }
            }
        } else {
            $pageErrors[] = '不明な操作です。';
        }
    }

    if ($isEditing && !app_request_method_is($request, 'POST')) {
        $selectedExperiment = app_fetch_lab_experiment_by_key($app, $editExperimentKey);

        if (!$selectedExperiment['ok']) {
            $pageErrors[] = '編集対象の experiment 取得に失敗しました: ' . $selectedExperiment['error'];
            $isEditing = false;
        } elseif ($selectedExperiment['item'] === null) {
            $pageErrors[] = '指定された experiment は見つかりません。';
            $isEditing = false;
        } else {
            $editInput = app_experiment_form_from_item($selectedExperiment['item']);
        }
    }

    $catalog = app_fetch_lab_experiment_catalog($app);
    $csrfToken = app_csrf_token();
    $statusCode = ($createErrors !== [] || $editErrors !== []) ? 422 : 200;

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Experiments</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 72rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
        }
        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        input[readonly] {
            background: #e2e8f0;
        }
        textarea {
            min-height: 7rem;
            resize: vertical;
        }
        button {
            margin-top: 1.25rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            vertical-align: top;
            text-align: left;
        }
        .pill {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            background: #e2e8f0;
            font-size: 0.875rem;
        }
        .error {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }
        .success {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
        }
        .actions {
            white-space: nowrap;
        }
        .inline-link {
            display: inline-block;
            margin-top: 0.75rem;
        }
    </style>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?> 実験一覧</h1>
    <p><code>lab</code> 側で project に紐づく experiment record を管理する画面です。Project 本体の canonical metadata は <code>admin</code> 側で管理し、ここでは compare output 実行や review の文脈になる実験単位を整理します。</p>
    <ul>
        <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
        <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
    </ul>

    <p><a href="/dashboard">dashboard</a> / <a href="/health">health</a></p>

    <?php if ($createdExperimentKey !== ''): ?>
        <div class="success">experiment <code><?php echo app_h($createdExperimentKey); ?></code> を追加しました。</div>
    <?php endif; ?>

    <?php if ($updatedExperimentKey !== ''): ?>
        <div class="success">experiment <code><?php echo app_h($updatedExperimentKey); ?></code> を更新しました。</div>
    <?php endif; ?>

    <?php if ($pageErrors !== []): ?>
        <div class="error">
            <?php foreach ($pageErrors as $error): ?>
                <div><?php echo app_h($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($createErrors !== []): ?>
        <div class="error">
            <?php foreach ($createErrors as $error): ?>
                <div><?php echo app_h($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/experiments">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="_intent" value="create">

        <h2>Experiment 追加</h2>

        <label for="experiment_key">experiment key</label>
        <input
            id="experiment_key"
            name="experiment_key"
            value="<?php echo app_h($createInput['experiment_key']); ?>"
            placeholder="EXP-NEW-001"
        >

        <label for="project_key">project key</label>
        <input
            id="project_key"
            name="project_key"
            value="<?php echo app_h($createInput['project_key']); ?>"
            placeholder="MTOOL"
        >

        <label for="name">experiment name</label>
        <input
            id="name"
            name="name"
            value="<?php echo app_h($createInput['name']); ?>"
            placeholder="New Experiment"
        >

        <label for="execution_status">execution status</label>
        <select id="execution_status" name="execution_status">
            <?php foreach (app_allowed_experiment_statuses() as $status): ?>
                <option value="<?php echo app_h($status); ?>"<?php echo $createInput['execution_status'] === $status ? ' selected' : ''; ?>>
                    <?php echo app_h($status); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="runtime_target">runtime target</label>
        <select id="runtime_target" name="runtime_target">
            <?php foreach (app_allowed_runtime_targets() as $target): ?>
                <option value="<?php echo app_h($target); ?>"<?php echo $createInput['runtime_target'] === $target ? ' selected' : ''; ?>>
                    <?php echo app_h($target); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="notes">notes</label>
        <textarea
            id="notes"
            name="notes"
            placeholder="何を検証する experiment か"
        ><?php echo app_h($createInput['notes']); ?></textarea>

        <button type="submit">Experiment を追加</button>
    </form>

    <?php if ($isEditing): ?>
        <?php if ($editErrors !== []): ?>
            <div class="error">
                <?php foreach ($editErrors as $error): ?>
                    <div><?php echo app_h($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/experiments?edit=<?php echo rawurlencode($editExperimentKey); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="_intent" value="update">
            <input type="hidden" name="experiment_key" value="<?php echo app_h($editInput['experiment_key']); ?>">

            <h2>Experiment 編集</h2>
            <p>experiment key は識別子として固定し、ここでは紐づく project と実験内容を更新します。</p>

            <label for="edit_experiment_key">experiment key</label>
            <input
                id="edit_experiment_key"
                value="<?php echo app_h($editInput['experiment_key']); ?>"
                readonly
            >

            <label for="edit_project_key">project key</label>
            <input
                id="edit_project_key"
                name="project_key"
                value="<?php echo app_h($editInput['project_key']); ?>"
                placeholder="MTOOL"
            >

            <label for="edit_name">experiment name</label>
            <input
                id="edit_name"
                name="name"
                value="<?php echo app_h($editInput['name']); ?>"
                placeholder="New Experiment"
            >

            <label for="edit_execution_status">execution status</label>
            <select id="edit_execution_status" name="execution_status">
                <?php foreach (app_allowed_experiment_statuses() as $status): ?>
                    <option value="<?php echo app_h($status); ?>"<?php echo $editInput['execution_status'] === $status ? ' selected' : ''; ?>>
                        <?php echo app_h($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edit_runtime_target">runtime target</label>
            <select id="edit_runtime_target" name="runtime_target">
                <?php foreach (app_allowed_runtime_targets() as $target): ?>
                    <option value="<?php echo app_h($target); ?>"<?php echo $editInput['runtime_target'] === $target ? ' selected' : ''; ?>>
                        <?php echo app_h($target); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edit_notes">notes</label>
            <textarea
                id="edit_notes"
                name="notes"
                placeholder="何を検証する experiment か"
            ><?php echo app_h($editInput['notes']); ?></textarea>

            <button type="submit">Experiment を更新</button>
            <a class="inline-link" href="/experiments">編集を閉じる</a>
        </form>
    <?php endif; ?>

    <?php if (!$catalog['ok']): ?>
        <div class="error">lab_experiments の取得に失敗しました: <?php echo app_h($catalog['error']); ?></div>
    <?php elseif ($catalog['items'] === []): ?>
        <p>Experiment はまだ登録されていません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>experiment</th>
                <th>project</th>
                <th>status</th>
                <th>runtime</th>
                <th>executor</th>
                <th>updated</th>
                <th>notes</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($catalog['items'] as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo app_h($item['name']); ?></strong><br>
                        <code><?php echo app_h($item['experiment_key']); ?></code>
                    </td>
                    <td><code><?php echo app_h($item['project_key']); ?></code></td>
                    <td><span class="pill"><?php echo app_h($item['execution_status']); ?></span></td>
                    <td><code><?php echo app_h($item['runtime_target']); ?></code></td>
                    <td><code><?php echo app_h($item['executed_by'] !== '' ? $item['executed_by'] : 'unassigned'); ?></code></td>
                    <td><code><?php echo app_h($item['updated_at']); ?></code></td>
                    <td><?php echo nl2br(app_h($item['notes'])); ?></td>
                    <td class="actions">
                        <a href="/experiments?edit=<?php echo rawurlencode($item['experiment_key']); ?>">edit</a><br>
                        <a href="/runs/compare-output/<?php echo rawurlencode($item['project_key']); ?>">compare output</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
    <?php
}
