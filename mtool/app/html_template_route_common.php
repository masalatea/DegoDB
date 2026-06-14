<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/html_template_repository.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

function app_html_templates_path(): string
{
    return '/settings/html-templates';
}

function app_html_template_detail_path(int $legacyTemplatePid): string
{
    return app_html_templates_path() . '/' . rawurlencode((string) $legacyTemplatePid);
}

function app_html_template_parameters_path(int $legacyTemplatePid): string
{
    return app_html_template_detail_path($legacyTemplatePid) . '/parameters';
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
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     }
 * }|null
 */
function app_html_template_route_bootstrap(array $app, array $request, array $allowedMethods = ['GET']): ?array
{
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
        app_render_forbidden_page($app, $request, 'HTML template settings の参照には admin または config role が必要です。');
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

    if (!in_array(strtoupper((string) $request['method']), $normalizedAllowedMethods, true)) {
        app_render_method_not_allowed_page($app, $request, $normalizedAllowedMethods);
        return null;
    }

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
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
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     },
 *     template:array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }
 * }|null
 */
function app_html_template_item_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    $bootstrap = app_html_template_route_bootstrap($app, $request, $allowedMethods);
    if ($bootstrap === null) {
        return null;
    }

    $legacyTemplatePid = (int) trim(app_route_param($request, 'legacy_template_pid'));
    if ($legacyTemplatePid <= 0) {
        app_render_bad_request_page($app, $request, 'html template PID の形式が不正です。');
        return null;
    }

    $templateResult = app_fetch_html_template_by_pid($app, $legacyTemplatePid);
    if (!$templateResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - HTML Template Settings</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>HTML template metadata の読み込みに失敗しました。</p>
    <ul>
        <li>legacy template PID: <code><?php echo app_h((string) $legacyTemplatePid); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($templateResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($templateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    return $bootstrap + [
        'template' => $templateResult['item'],
    ];
}
