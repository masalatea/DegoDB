<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/response.php';

function app_project_security_path(string $projectKey): string
{
    return '/projects/' . rawurlencode($projectKey) . '/security';
}

function app_project_security_users_path(string $projectKey): string
{
    return app_project_security_path($projectKey) . '/users';
}

function app_project_security_pages_path(string $projectKey): string
{
    return app_project_security_path($projectKey) . '/pages';
}

function app_project_host_assignments_path(string $projectKey): string
{
    return '/projects/' . rawurlencode($projectKey) . '/host-assignments';
}

/**
 * @return list<array{
 *     category:string,
 *     read_code:string,
 *     write_code:string
 * }>
 */
function app_project_security_legacy_permission_groups(): array
{
    return [
        ['category' => 'Chat', 'read_code' => 'CHATREAD', 'write_code' => 'CHATWRITE'],
        ['category' => 'Requirement Tool', 'read_code' => 'REQREAD', 'write_code' => 'REQWRITE'],
        ['category' => 'Spec Tool', 'read_code' => 'SPECTOOLREAD', 'write_code' => 'SPECTOOLWRITE'],
        ['category' => 'DB Tool', 'read_code' => 'DBTOOLREAD', 'write_code' => 'DBTOOLWRITE'],
        ['category' => 'HTML', 'read_code' => 'HTMLREAD', 'write_code' => 'HTMLWRITE'],
        ['category' => 'Test Tool', 'read_code' => 'TESTTOOLREAD', 'write_code' => 'TESTTOOLWRITE'],
        ['category' => 'Minutes Tool', 'read_code' => 'MINUTESREAD', 'write_code' => 'MINUTESWRITE'],
        ['category' => 'Upload Tool', 'read_code' => 'UPLOADREAD', 'write_code' => 'UPLOADWRITE'],
    ];
}

/**
 * @return list<string>
 */
function app_project_security_legacy_permission_codes(): array
{
    $codes = [];
    foreach (app_project_security_legacy_permission_groups() as $group) {
        $codes[] = $group['read_code'];
        $codes[] = $group['write_code'];
    }

    return $codes;
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
 *     project:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     },
 *     project_key:string
 * }|null
 */
function app_project_security_route_bootstrap(array $app, array $request, array $allowedMethods = ['GET']): ?array
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
        app_render_forbidden_page($app, $request, 'security / host assignment の参照には admin または config role が必要です。');
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
    <title><?php echo app_h($app['site_name']); ?> - Project Security</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>security / host assignment の読み込みに失敗しました。</p>
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

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
        'project' => $project['item'],
        'project_key' => $projectKey,
    ];
}
