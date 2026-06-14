<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     method:string,
 *     path:string,
 *     query_string:string,
 *     request_id:string
 * } $request
 */
function app_handle_before_dispatch(array $app, array $request, string $routeName): bool
{
    if (!app_route_requires_auth($routeName)) {
        return false;
    }

    if (app_auth_is_authenticated()) {
        return false;
    }

    app_send_redirect_response(
        $request,
        app_auth_login_path() . '?redirect=' . rawurlencode(app_auth_current_target($request)),
    );

    return true;
}

/**
 * @param array{
 *     site:string
 * } $app
 * @param array{
 *     request_id:string
 * } $request
 */
function app_handle_unexpected_http_error(array $app, array $request, Throwable $throwable): void
{
    error_log(sprintf(
        '[app][%s][%s] %s in %s:%d',
        $app['site'],
        $request['request_id'],
        $throwable->getMessage(),
        $throwable->getFile(),
        $throwable->getLine(),
    ));

    app_render_internal_error_page($app, $request);
}
