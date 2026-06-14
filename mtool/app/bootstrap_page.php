<?php

declare(strict_types=1);

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/auth.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     site_role_summary:string,
 *     db:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 * @param array{
 *     ok:bool,
 *     label:string,
 *     detail:string
 * } $databaseStatus
 * @param array{
 *     request_id:string,
 *     method:string,
 *     uri:string,
 *     path:string,
 *     query_string:string,
 *     host:string,
 *     scheme:string,
 *     remote_addr:string,
 *     user_agent:string
 * } $request
 */
function app_render_bootstrap_page(array $app, array $databaseStatus, array $request): void
{
    app_send_html_response_headers($request);
    $principal = app_auth_principal();
    $dashboardPath = app_auth_dashboard_path();
    $loginPath = app_auth_login_path();
    $primaryLinkPath = $app['site'] === 'admin' ? '/projects' : '/experiments';
    $primaryLinkLabel = $app['site'] === 'admin' ? 'projects' : 'experiments';

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?></title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
        }
        code {
            background: #f2f2f2;
            padding: 0.1rem 0.3rem;
        }
        .actions a {
            margin-right: 0.75rem;
        }
    </style>
</head>
<body>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p><?php echo app_h($app['site_role_summary']); ?></p>
    <?php if ($app['site'] === 'admin'): ?>
        <p>この入口から login して <code>/projects</code> へ進み、DB import 後の metadata 管理と <code>Data Class</code>、<code>DB Access</code>、<code>Source Output</code> の設計・生成を行います。</p>
    <?php else: ?>
        <p>この入口から login して <code>/experiments</code> や compare output 実行画面へ進み、admin 側で定義した canonical metadata を使った実験と review を行います。</p>
    <?php endif; ?>
    <ul>
        <li>site: <code><?php echo app_h($app['site']); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>request path: <code><?php echo app_h($request['path']); ?></code></li>
        <li>session name: <code><?php echo app_h($app['session']['name']); ?></code></li>
        <li>auth mode: <code><?php echo app_h($app['auth']['mode']); ?></code></li>
        <li>db host: <code><?php echo app_h($app['db']['host']); ?></code></li>
        <li>db port: <code><?php echo app_h($app['db']['port']); ?></code></li>
        <li>db name: <code><?php echo app_h($app['db']['name']); ?></code></li>
        <li>db status: <code><?php echo app_h($databaseStatus['label']); ?></code></li>
        <li>db detail: <code><?php echo app_h($databaseStatus['detail']); ?></code></li>
    </ul>
    <?php if ($principal !== null): ?>
        <p>現在のログイン: <code><?php echo app_h($principal['display_name']); ?></code></p>
    <?php else: ?>
        <p>現在は未ログインです。</p>
    <?php endif; ?>
    <p class="actions">
        <a href="<?php echo app_h($dashboardPath); ?>">dashboard</a>
        <a href="<?php echo app_h($primaryLinkPath); ?>"><?php echo app_h($primaryLinkLabel); ?></a>
        <a href="<?php echo app_h($loginPath); ?>">login</a>
        <a href="/health">health</a>
    </p>
</body>
</html>
    <?php
}
