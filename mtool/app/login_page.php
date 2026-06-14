<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     auth:array{
 *         mode:string,
 *         stub:array{
 *             username:string
 *         }
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string
 * } $request
 */
function app_handle_login_request(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $redirectPath = app_auth_requested_path($request, app_auth_dashboard_path());
    if (app_auth_is_authenticated()) {
        app_send_redirect_response($request, $redirectPath);
        return;
    }

    $statusCode = 200;
    $errorMessage = '';
    $username = '';

    if (app_request_method_is($request, 'POST')) {
        $username = trim(app_post_param('username'));

        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $statusCode = 400;
            $errorMessage = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (!app_auth_attempt_login($app, $username, app_post_param('password'))) {
            $statusCode = 401;
            $errorMessage = 'ユーザー名またはパスワードが一致しません。';
        } else {
            app_send_redirect_response($request, $redirectPath);
            return;
        }
    }

    app_render_login_page($app, $request, $redirectPath, $username, $errorMessage, $statusCode);
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     auth:array{
 *         mode:string,
 *         stub:array{
 *             username:string
 *         }
 *     }
 * } $app
 * @param array{
 *     request_id:string
 * } $request
 */
function app_render_login_page(
    array $app,
    array $request,
    string $redirectPath,
    string $username,
    string $errorMessage,
    int $statusCode = 200,
): void {
    app_send_html_response_headers($request, $statusCode);

    $csrfToken = app_csrf_token();
    $envPrefix = strtoupper($app['site']);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Login</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 40rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        label {
            display: block;
            font-weight: 600;
            margin-top: 1rem;
        }
        input {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }
        button {
            margin-top: 1.25rem;
            padding: 0.75rem 1rem;
            border: 0;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        .error {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }
        .muted {
            color: #475569;
        }
    </style>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?> ログイン</h1>
    <p><?php echo app_h(app_auth_mode_summary($app)); ?> の段階です。</p>
    <?php if ($app['site'] === 'admin'): ?>
        <p>ログイン後は <code>/projects</code> を起点に、metadata 管理と生成設定へ進みます。</p>
    <?php else: ?>
        <p>ログイン後は <code>/experiments</code> や compare output 実行画面へ進み、admin 側 definition を参照した実験と review を行います。</p>
    <?php endif; ?>
    <ul>
        <li>遷移先: <code><?php echo app_h($redirectPath); ?></code></li>
        <li>ユーザー名設定: <code><?php echo app_h($envPrefix . '_AUTH_STUB_USER'); ?></code></li>
        <li>パスワード設定: <code><?php echo app_h($envPrefix . '_AUTH_STUB_PASSWORD'); ?></code></li>
        <li>既定ユーザー名: <code><?php echo app_h($app['auth']['stub']['username']); ?></code></li>
    </ul>

    <?php if ($errorMessage !== ''): ?>
        <div class="error"><?php echo app_h($errorMessage); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo app_h(app_auth_login_path()); ?>">
        <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
        <input type="hidden" name="redirect" value="<?php echo app_h($redirectPath); ?>">

        <label for="username">ユーザー名</label>
        <input id="username" name="username" value="<?php echo app_h($username); ?>" autocomplete="username">

        <label for="password">パスワード</label>
        <input id="password" name="password" type="password" autocomplete="current-password">

        <button type="submit">ログイン</button>
    </form>

    <p class="muted">request id: <code><?php echo app_h($request['request_id']); ?></code></p>
    <p><a href="/">トップへ戻る</a></p>
</main>
</body>
</html>
    <?php
}
