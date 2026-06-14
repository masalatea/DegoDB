<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/custom_proxy_repository.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/generated_catalog.php';
require_once __DIR__ . '/generated_runtime.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_custom_proxies_path(string $projectKey): string
{
    return '/projects/' . rawurlencode($projectKey) . '/proxy/custom';
}

function app_project_custom_proxy_detail_path(string $projectKey, string $customProxyKey): string
{
    return app_project_custom_proxies_path($projectKey) . '/' . rawurlencode($customProxyKey);
}

function app_project_custom_proxy_functions_path(string $projectKey, string $customProxyKey): string
{
    return app_project_custom_proxy_detail_path($projectKey, $customProxyKey) . '/functions';
}

function app_project_custom_proxy_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return null;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return null;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'custom proxy の参照には admin または config role が必要です。');
        return null;
    }

    $normalizedAllowedMethods = array_values(
        array_filter(
            array_map(
                static fn (string $method): string => strtoupper(trim($method)),
                $allowedMethods,
            ),
            static fn (string $method): bool => $method !== '',
        ),
    );
    if ($normalizedAllowedMethods === []) {
        $normalizedAllowedMethods = ['GET'];
    }

    if (!in_array(strtoupper($request['method']), $normalizedAllowedMethods, true)) {
        app_render_method_not_allowed_page($app, $request, $normalizedAllowedMethods);
        return null;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return null;
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
    <title><?php echo app_h($app['site_name']); ?> - Project Custom Proxy</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>custom proxy の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($project['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($project['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $sourceOutputCatalog = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$sourceOutputCatalog['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Custom Proxy</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>source output catalog の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($sourceOutputCatalog['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
        'project' => $project['item'],
        'project_key' => $projectKey,
        'generated_runtime' => app_generated_runtime_summary($app),
        'generated_catalog' => app_generated_entity_catalog($app),
        'source_output_catalog' => $sourceOutputCatalog['items'],
    ];
}

function app_project_custom_proxy_item_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    $bootstrap = app_project_custom_proxy_route_bootstrap($app, $request, $allowedMethods);
    if ($bootstrap === null) {
        return null;
    }

    $customProxyKey = app_normalize_custom_proxy_key(app_route_param($request, 'custom_proxy_key'));
    if ($customProxyKey === '' || !app_custom_proxy_key_is_valid($customProxyKey)) {
        app_render_bad_request_page($app, $request, 'custom proxy key の形式が不正です。');
        return null;
    }

    $itemResult = app_fetch_project_custom_proxy_item($app, $bootstrap['project_key'], $customProxyKey);
    if (!$itemResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Project Custom Proxy</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>custom proxy detail の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($bootstrap['project_key']); ?></code></li>
        <li>custom proxy key: <code><?php echo app_h($customProxyKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($itemResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($itemResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $bootstrap['custom_proxy_key'] = $customProxyKey;
    $bootstrap['custom_proxy'] = $itemResult['item'];

    return $bootstrap;
}
