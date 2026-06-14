<?php

declare(strict_types=1);

require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     path:string
 * } $request
 */
function app_render_not_found_page(array $app, array $request): void
{
    app_send_html_response_headers($request, 404);

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - 404</title>
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
    </style>
</head>
<body>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>指定したページは見つかりません。</p>
    <ul>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>request path: <code><?php echo app_h($request['path']); ?></code></li>
    </ul>
</body>
</html>
    <?php
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     path:string
 * } $request
 */
function app_render_bad_request_page(array $app, array $request, string $message): void
{
    app_send_html_response_headers($request, 400);

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - 400</title>
</head>
<body>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p><?php echo app_h($message); ?></p>
    <ul>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>request path: <code><?php echo app_h($request['path']); ?></code></li>
    </ul>
</body>
</html>
    <?php
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     path:string
 * } $request
 * @param list<string> $allowedMethods
 */
function app_render_method_not_allowed_page(array $app, array $request, array $allowedMethods): void
{
    header('Allow: ' . implode(', ', $allowedMethods));
    app_send_html_response_headers($request, 405);

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - 405</title>
</head>
<body>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>許可されていない HTTP method です。</p>
    <ul>
        <li>allowed: <code><?php echo app_h(implode(', ', $allowedMethods)); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>request path: <code><?php echo app_h($request['path']); ?></code></li>
    </ul>
</body>
</html>
    <?php
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     path:string
 * } $request
 */
function app_render_forbidden_page(array $app, array $request, string $message): void
{
    app_send_html_response_headers($request, 403);

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - 403</title>
</head>
<body>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p><?php echo app_h($message); ?></p>
    <ul>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>request path: <code><?php echo app_h($request['path']); ?></code></li>
    </ul>
</body>
</html>
    <?php
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     path:string
 * } $request
 */
function app_render_internal_error_page(array $app, array $request): void
{
    app_send_html_response_headers($request, 500);

    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - 500</title>
</head>
<body>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>内部エラーが発生しました。</p>
    <ul>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>request path: <code><?php echo app_h($request['path']); ?></code></li>
    </ul>
</body>
</html>
    <?php
}
