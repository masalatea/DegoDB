<?php

declare(strict_types=1);

/**
 * @param array{
 *     session:array{
 *         name:string
 *     }
 * } $app
 */
function app_boot_session(array $app): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');

    session_name($app['session']['name']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => app_session_cookie_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function app_session_cookie_secure(): bool
{
    $https = $_SERVER['HTTPS'] ?? null;
    if (is_string($https) && $https !== '' && strtolower($https) !== 'off') {
        return true;
    }

    $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;

    return is_string($forwardedProto) && strtolower($forwardedProto) === 'https';
}
