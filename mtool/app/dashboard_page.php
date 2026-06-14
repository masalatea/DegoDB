<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';

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
function app_handle_logout_request(array $app, array $request): void
{
    if (!app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['POST']);
        return;
    }

    if (!app_verify_csrf_token(app_post_param('_csrf'))) {
        app_render_bad_request_page($app, $request, 'CSRF token が一致しません。');
        return;
    }

    app_auth_logout();
    app_send_redirect_response($request, app_auth_login_path());
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     site_role_summary:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string
 * } $request
 */
function app_render_dashboard_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    $databaseStatus = app_probe_database($app);
    $csrfToken = app_csrf_token();
    $primaryLinkPath = $app['site'] === 'admin' ? '/projects' : '/experiments';
    $primaryLinkLabel = $app['site'] === 'admin' ? 'projects' : 'experiments';

    app_send_html_response_headers($request);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Dashboard</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 48rem;
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
        .grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .panel {
            border: 1px solid #d7dde5;
            border-radius: 10px;
            padding: 1rem;
            background: #f8fafc;
        }
        button {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
        }
    </style>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?> ダッシュボード</h1>
    <p><?php echo app_h($app['site_role_summary']); ?></p>
    <?php if ($app['site'] === 'admin'): ?>
        <p>このサイトでは、外で決めた DB 構造を import した後の metadata 管理と、<code>Data Class</code>、<code>DB Access</code>、<code>Source Output</code> の設計・生成を進めます。project を開いたら、最初の起点は <code>/tables/import</code> です。</p>
    <?php else: ?>
        <p>このサイトでは、admin 側で定義した canonical metadata を参照しながら、experiment 管理、compare output 実行、review を進めます。</p>
    <?php endif; ?>

    <div class="grid">
        <section class="panel">
            <h2>認証状態</h2>
            <ul>
                <li>user id: <code><?php echo app_h($principal['id']); ?></code></li>
                <li>display name: <code><?php echo app_h($principal['display_name']); ?></code></li>
                <li>roles: <code><?php echo app_h(implode(', ', $principal['roles'])); ?></code></li>
                <li>source: <code><?php echo app_h($principal['auth_source']); ?></code></li>
            </ul>
        </section>

        <section class="panel">
            <h2>実行環境</h2>
            <ul>
                <li>site: <code><?php echo app_h($app['site']); ?></code></li>
                <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
                <li>db status: <code><?php echo app_h($databaseStatus['label']); ?></code></li>
                <li>db detail: <code><?php echo app_h($databaseStatus['detail']); ?></code></li>
            </ul>
        </section>

        <section class="panel">
            <h2>主な流れ</h2>
            <ul>
                <?php if ($app['site'] === 'admin'): ?>
                    <li><a href="/projects"><code>/projects</code></a> から project を選ぶ</li>
                    <li><a href="/settings/html-templates"><code>/settings/html-templates</code></a> で global HTML template metadata を管理する</li>
                    <li><a href="/settings/database-sources"><code>/settings/database-sources</code></a> で external named database source を管理する</li>
                    <li>project を開いたら最初に <code>/tables/import</code> で DB 設計情報の import 状態を確認する</li>
                    <li>外部 DB schema に差分がある場合は、<code>/tables/import</code> と <code>/data-classes/sync</code> を先にやり直す</li>
                    <li><code>/language-resources</code> は file canonical catalog の inspector を表示し、編集は current admin ではなく repo 配下 JSON の直接更新で行う</li>
                    <li>その後に <code>/db-access</code>、<code>/source-outputs</code> で metadata と生成を進める</li>
                <?php else: ?>
                    <li><code>/experiments</code> で experiment を管理する</li>
                    <li><code>/runs/compare-output/{project_key}</code> で compare output を実行する</li>
                    <li>生成された job を review する</li>
                <?php endif; ?>
            </ul>
        </section>
    </div>

    <p><a href="/">トップ</a> / <a href="<?php echo app_h($primaryLinkPath); ?>"><?php echo app_h($primaryLinkLabel); ?></a> / <a href="/health">health</a></p>

    <form method="post" action="<?php echo app_h(app_auth_logout_path()); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <button type="submit">ログアウト</button>
    </form>
</main>
</body>
</html>
    <?php
}
