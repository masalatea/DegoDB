<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     owner_login_id:string,
 *     member_count:int,
 *     updated_at:string,
 *     description:string
 * } $item
 * @return array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     description:string
 * }
 */
function app_project_form_from_item(array $item): array
{
    return [
        'project_key' => $item['project_key'],
        'name' => $item['name'],
        'slug' => $item['slug'],
        'lifecycle_status' => $item['lifecycle_status'],
        'description' => $item['description'],
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
function app_render_project_list_page(array $app, array $request): void
{
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'projects の参照と編集には admin または config role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $legacyEditProjectKey = app_normalize_project_key(app_query_param('edit'));
    if ($legacyEditProjectKey !== '' && app_request_method_is($request, 'GET')) {
        if (!app_project_key_is_valid($legacyEditProjectKey)) {
            app_render_bad_request_page($app, $request, 'edit query の project key 形式が不正です。');
            return;
        }

        app_send_redirect_response(
            $request,
            '/projects/' . rawurlencode($legacyEditProjectKey) . '/settings',
        );
        return;
    }

    $createInput = app_project_form_defaults();
    $createErrors = [];
    $editInput = app_project_form_defaults();
    $editErrors = [];
    $pageErrors = [];

    $editProjectKey = strtoupper(trim(app_query_param('edit')));
    $createdProjectKey = app_query_param('created');
    $updatedProjectKey = app_query_param('updated');
    $isEditing = $editProjectKey !== '';

    if (app_request_method_is($request, 'POST')) {
        $intent = app_post_param('_intent', 'create');

        if ($intent === 'update') {
            $validation = app_validate_project_form([
                'project_key' => app_post_param('project_key'),
                'name' => app_post_param('name'),
                'slug' => app_post_param('slug'),
                'lifecycle_status' => app_post_param('lifecycle_status'),
                'description' => app_post_param('description'),
            ]);

            $editInput = $validation['input'];
            $editProjectKey = $editInput['project_key'];
            $isEditing = $editProjectKey !== '';

            if (!app_verify_csrf_token(app_post_param('_csrf'))) {
                $editErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
            } else {
                $editErrors = $validation['errors'];

                if ($editErrors === []) {
                    $updateResult = app_update_project($app, [
                        'project_key' => $editInput['project_key'],
                        'name' => $editInput['name'],
                        'slug' => $editInput['slug'],
                        'lifecycle_status' => $editInput['lifecycle_status'],
                        'description' => $editInput['description'],
                    ]);

                    if ($updateResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects?updated=' . rawurlencode($editInput['project_key']),
                        );
                        return;
                    }

                    $editErrors[] = $updateResult['error'];
                }
            }
        } elseif ($intent === 'create' || $intent === '') {
            $validation = app_validate_project_form([
                'project_key' => app_post_param('project_key'),
                'name' => app_post_param('name'),
                'slug' => app_post_param('slug'),
                'lifecycle_status' => app_post_param('lifecycle_status'),
                'description' => app_post_param('description'),
            ]);

            $createInput = $validation['input'];

            if (!app_verify_csrf_token(app_post_param('_csrf'))) {
                $createErrors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
            } else {
                $createErrors = $validation['errors'];

                if ($createErrors === []) {
                    $createResult = app_insert_project($app, [
                        'project_key' => $createInput['project_key'],
                        'name' => $createInput['name'],
                        'slug' => $createInput['slug'],
                        'lifecycle_status' => $createInput['lifecycle_status'],
                        'owner_login_id' => $principal['id'],
                        'description' => $createInput['description'],
                    ]);

                    if ($createResult['ok']) {
                        app_send_redirect_response(
                            $request,
                            '/projects?created=' . rawurlencode($createInput['project_key']),
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
        $selectedProject = app_fetch_project_by_key($app, $editProjectKey);

        if (!$selectedProject['ok']) {
            $pageErrors[] = '編集対象の project 取得に失敗しました: ' . $selectedProject['error'];
            $isEditing = false;
        } elseif ($selectedProject['item'] === null) {
            $pageErrors[] = '指定された project は見つかりません。';
            $isEditing = false;
        } else {
            $editInput = app_project_form_from_item($selectedProject['item']);
        }
    }

    $catalog = app_fetch_project_catalog($app);
    $csrfToken = app_csrf_token();
    $statusCode = ($createErrors !== [] || $editErrors !== []) ? 422 : 200;

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Projects</title>
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
    <h1><?php echo app_h($app['site_name']); ?> Project 一覧</h1>
    <p>旧実装の中心集約である `Project` を、新実装側では canonical な設定定義として切り出しています。現段階では admin/config role を持つユーザーだけが追加と更新を行えます。</p>
    <ul>
        <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
        <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
    </ul>

    <p><a href="/dashboard">dashboard</a> / <a href="/health">health</a></p>

    <?php if ($createdProjectKey !== ''): ?>
        <div class="success">project <code><?php echo app_h($createdProjectKey); ?></code> を追加しました。</div>
    <?php endif; ?>

    <?php if ($updatedProjectKey !== ''): ?>
        <div class="success">project <code><?php echo app_h($updatedProjectKey); ?></code> を更新しました。</div>
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

    <form method="post" action="/projects">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="_intent" value="create">

        <h2>Project 追加</h2>

        <label for="project_key">project key</label>
        <input
            id="project_key"
            name="project_key"
            value="<?php echo app_h($createInput['project_key']); ?>"
            placeholder="MTOOL-NEW-001"
        >

        <label for="name">project name</label>
        <input
            id="name"
            name="name"
            value="<?php echo app_h($createInput['name']); ?>"
            placeholder="New Project"
        >

        <label for="slug">slug</label>
        <input
            id="slug"
            name="slug"
            value="<?php echo app_h($createInput['slug']); ?>"
            placeholder="new-project"
        >

        <label for="lifecycle_status">lifecycle status</label>
        <select id="lifecycle_status" name="lifecycle_status">
            <?php foreach (app_allowed_project_lifecycle_statuses() as $status): ?>
                <option value="<?php echo app_h($status); ?>"<?php echo $createInput['lifecycle_status'] === $status ? ' selected' : ''; ?>>
                    <?php echo app_h($status); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="description">description</label>
        <textarea
            id="description"
            name="description"
            placeholder="この project が何を管理するか"
        ><?php echo app_h($createInput['description']); ?></textarea>

        <button type="submit">Project を追加</button>
    </form>

    <?php if ($isEditing): ?>
        <?php if ($editErrors !== []): ?>
            <div class="error">
                <?php foreach ($editErrors as $error): ?>
                    <div><?php echo app_h($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/projects?edit=<?php echo rawurlencode($editProjectKey); ?>">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="_intent" value="update">
            <input type="hidden" name="project_key" value="<?php echo app_h($editInput['project_key']); ?>">

            <h2>Project 編集</h2>
            <p>project key は識別子として固定し、ここでは設定内容のみ更新します。</p>

            <label for="edit_project_key">project key</label>
            <input
                id="edit_project_key"
                value="<?php echo app_h($editInput['project_key']); ?>"
                readonly
            >

            <label for="edit_name">project name</label>
            <input
                id="edit_name"
                name="name"
                value="<?php echo app_h($editInput['name']); ?>"
                placeholder="New Project"
            >

            <label for="edit_slug">slug</label>
            <input
                id="edit_slug"
                name="slug"
                value="<?php echo app_h($editInput['slug']); ?>"
                placeholder="new-project"
            >

            <label for="edit_lifecycle_status">lifecycle status</label>
            <select id="edit_lifecycle_status" name="lifecycle_status">
                <?php foreach (app_allowed_project_lifecycle_statuses() as $status): ?>
                    <option value="<?php echo app_h($status); ?>"<?php echo $editInput['lifecycle_status'] === $status ? ' selected' : ''; ?>>
                        <?php echo app_h($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="edit_description">description</label>
            <textarea
                id="edit_description"
                name="description"
                placeholder="この project が何を管理するか"
            ><?php echo app_h($editInput['description']); ?></textarea>

            <button type="submit">Project を更新</button>
            <a class="inline-link" href="/projects">編集を閉じる</a>
        </form>
    <?php endif; ?>

    <?php if (!$catalog['ok']): ?>
        <div class="error">projects の取得に失敗しました: <?php echo app_h($catalog['error']); ?></div>
    <?php elseif ($catalog['items'] === []): ?>
        <p>Project はまだ登録されていません。</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>project</th>
                <th>status</th>
                <th>owner</th>
                <th>members</th>
                <th>updated</th>
                <th>description</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($catalog['items'] as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo app_h($item['name']); ?></strong><br>
                        <code><?php echo app_h($item['project_key']); ?></code><br>
                        <small>slug: <code><?php echo app_h($item['slug']); ?></code></small>
                    </td>
                    <td><span class="pill"><?php echo app_h($item['lifecycle_status']); ?></span></td>
                    <td><code><?php echo app_h($item['owner_login_id']); ?></code></td>
                    <td><?php echo app_h((string) $item['member_count']); ?></td>
                    <td><code><?php echo app_h($item['updated_at']); ?></code></td>
                    <td><?php echo nl2br(app_h($item['description'])); ?></td>
                    <td class="actions">
                        <a href="/projects/<?php echo rawurlencode($item['project_key']); ?>">open</a><br>
                        <a href="/projects/<?php echo rawurlencode($item['project_key']); ?>/settings">settings</a>
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
