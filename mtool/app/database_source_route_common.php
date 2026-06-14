<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/response.php';

function app_database_sources_path(): string
{
    return '/settings/database-sources';
}

/**
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string
 * } $request
 * @param list<string> $allowedMethods
 * @return array{
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     }
 * }|null
 */
function app_database_source_route_bootstrap(array $app, array $request, array $allowedMethods = ['GET']): ?array
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
        app_render_forbidden_page($app, $request, 'database source settings の参照には admin または config role が必要です。');
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
        'principal' => $principal,
    ];
}
