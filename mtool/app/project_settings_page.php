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
 *     php_namespace:string,
 *     member_count:int,
 *     updated_at:string,
 *     description:string
 * } $item
 * @return array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     php_namespace:string,
 *     description:string
 * }
 */
function app_project_settings_form_from_item(array $item): array
{
    return [
        'project_key' => $item['project_key'],
        'name' => $item['name'],
        'slug' => $item['slug'],
        'lifecycle_status' => $item['lifecycle_status'],
        'php_namespace' => (string) ($item['php_namespace'] ?? ''),
        'description' => $item['description'],
    ];
}

/**
 * @return list<array{
 *     title:string,
 *     legacy_fields:string,
 *     summary:string
 * }>
 */
function app_project_settings_pending_groups(): array
{
    return [
        [
            'title' => 'Storage / Dropbox',
            'legacy_fields' => 'StorageType, DropboxBaseFolderPID',
            'summary' => '保存先種類と Dropbox base folder 選択は、canonical project setting の拡張として次段で追加します。',
        ],
        [
            'title' => 'DB Connection',
            'legacy_fields' => 'DBType, DBUserPID, SQLServerConnectionString, SettingDir, DBManagerURL',
            'summary' => '接続先 DB と管理 URL は、table import / metadata sync と整合する形で settings 配下へ移します。',
        ],
        [
            'title' => 'Proxy Access',
            'legacy_fields' => 'TokenForProxyAccess, proxy_header_of_access_control_allow_origin, proxy_header_of_access_control_allow_headers',
            'summary' => 'proxy token と CORS header は、proxy module と責務を分けつつここから参照できるように再整理します。',
        ],
        [
            'title' => 'Build / UI Options',
            'legacy_fields' => 'option_automatically_create_simple_proxy など各種 option_*',
            'summary' => '自動 proxy 生成、source output 表示、language resource 表示、compare output 利用可否などの option 群は feature flag 群として後続移植します。',
        ],
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_settings_page(array $app, array $request): void
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
        app_render_forbidden_page($app, $request, 'project settings の参照と更新には admin または config role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $project = app_fetch_project_by_key($app, $projectKey);
    if (!$project['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Settings</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>project settings の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($project['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    if ($project['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $item = $project['item'];
    $input = app_project_settings_form_from_item($item);
    $errors = [];
    $updated = app_query_param('updated') === '1';

    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } else {
            $postedProjectKey = app_normalize_project_key(app_post_param('project_key'));
            if ($postedProjectKey !== $projectKey) {
                $errors[] = '更新対象の project key が route と一致しません。';
            }

            $validation = app_validate_project_form([
                'project_key' => $projectKey,
                'name' => app_post_param('name'),
                'slug' => app_post_param('slug'),
                'lifecycle_status' => app_post_param('lifecycle_status'),
                'php_namespace' => app_post_param('php_namespace'),
                'description' => app_post_param('description'),
            ]);

            $input = $validation['input'];
            $errors = array_merge($errors, $validation['errors']);

            if ($errors === []) {
                $updateResult = app_update_project($app, [
                    'project_key' => $projectKey,
                    'name' => $input['name'],
                    'slug' => $input['slug'],
                    'lifecycle_status' => $input['lifecycle_status'],
                    'php_namespace' => $input['php_namespace'],
                    'description' => $input['description'],
                ]);

                if ($updateResult['ok']) {
                    app_send_redirect_response(
                        $request,
                        '/projects/' . rawurlencode($projectKey) . '/settings?updated=1',
                    );
                    return;
                }

                $errors[] = $updateResult['error'];
            }
        }
    }

    $pendingGroups = app_project_settings_pending_groups();
    $csrfToken = app_csrf_token();
    $statusCode = $errors === [] ? 200 : 422;

    app_send_html_response_headers($request, $statusCode);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Settings</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 78rem;
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
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card, .note-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #fefce8;
            border-color: #facc15;
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
            min-height: 8rem;
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
        .pending-list {
            margin-top: 1rem;
            display: grid;
            gap: 1rem;
        }
        .pending-item {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
            background: #ffffff;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo rawurlencode($projectKey); ?>"><code><?php echo app_h($projectKey); ?></code></a> / settings</p>

    <h1><?php echo app_h($item['name']); ?> Project 基本設定</h1>
    <p>project の基本 metadata を管理する画面です。まずは canonical な project 基本情報を独立 route に分離し、旧画面で一緒に扱っていた DB / Proxy / option 群は後続でこの settings 配下へ拡張します。</p>

    <div class="summary-grid">
        <section class="summary-card">
            <h2>Project 概要</h2>
            <ul>
                <li>project key: <code><?php echo app_h($item['project_key']); ?></code></li>
                <li>slug: <code><?php echo app_h($item['slug']); ?></code></li>
                <li>status: <code><?php echo app_h($item['lifecycle_status']); ?></code></li>
                <li>PHP namespace: <code><?php echo app_h($item['php_namespace'] !== '' ? $item['php_namespace'] : '(none)'); ?></code></li>
                <li>owner: <code><?php echo app_h($item['owner_login_id']); ?></code></li>
                <li>members: <code><?php echo app_h((string) $item['member_count']); ?></code></li>
                <li>updated: <code><?php echo app_h($item['updated_at']); ?></code></li>
            </ul>
        </section>

        <section class="summary-card">
            <h2>Runtime Context</h2>
            <ul>
                <li>site: <code><?php echo app_h($app['site']); ?></code></li>
                <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
                <li>login user: <code><?php echo app_h($principal['id']); ?></code></li>
                <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
                <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
            </ul>
        </section>

        <section class="note-card">
            <h2>現段階の範囲</h2>
            <p class="muted">ここで更新できるのは、`Project` の identity / slug / lifecycle / description です。旧画面で同居していた DB 接続、Storage、Proxy token、各種 option はまだ新スキーマへ移していません。</p>
        </section>
    </div>

    <?php if ($updated): ?>
        <div class="success">project <code><?php echo app_h($projectKey); ?></code> の基本設定を更新しました。</div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo app_h($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/projects/<?php echo rawurlencode($projectKey); ?>/settings">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="project_key" value="<?php echo app_h($projectKey); ?>">

        <h2>現在更新できる canonical fields</h2>

        <label for="project_key">project key</label>
        <input
            id="project_key"
            value="<?php echo app_h($projectKey); ?>"
            readonly
        >

        <label for="name">project name</label>
        <input
            id="name"
            name="name"
            value="<?php echo app_h($input['name']); ?>"
            placeholder="Project Name"
        >

        <label for="slug">slug</label>
        <input
            id="slug"
            name="slug"
            value="<?php echo app_h($input['slug']); ?>"
            placeholder="project-slug"
        >

        <label for="lifecycle_status">lifecycle status</label>
        <select id="lifecycle_status" name="lifecycle_status">
            <?php foreach (app_allowed_project_lifecycle_statuses() as $status): ?>
                <option value="<?php echo app_h($status); ?>"<?php echo $input['lifecycle_status'] === $status ? ' selected' : ''; ?>>
                    <?php echo app_h($status); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="php_namespace">PHP namespace</label>
        <input
            id="php_namespace"
            name="php_namespace"
            value="<?php echo app_h($input['php_namespace']); ?>"
            placeholder="App\\Generated"
        >

        <label for="description">description</label>
        <textarea
            id="description"
            name="description"
            placeholder="この project が何を管理するか"
        ><?php echo app_h($input['description']); ?></textarea>

        <button type="submit">Project 基本設定を更新</button>
    </form>

    <section>
        <h2>旧 `project_edit` から未移植の設定群</h2>
        <p class="muted">旧画面で一括更新していた設定は、新実装では責務ごとに分離して段階的に移植します。</p>

        <div class="pending-list">
            <?php foreach ($pendingGroups as $group): ?>
                <section class="pending-item">
                    <h3><?php echo app_h($group['title']); ?></h3>
                    <p><?php echo app_h($group['summary']); ?></p>
                    <p>legacy fields: <code><?php echo app_h($group['legacy_fields']); ?></code></p>
                </section>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
    <?php
}
