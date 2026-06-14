<?php

declare(strict_types=1);

function app_csrf_token(): string
{
    app_assert_active_session();

    $token = $_SESSION['_csrf_token'] ?? null;
    if (!is_string($token) || $token === '') {
        $token = bin2hex(random_bytes(16));
        $_SESSION['_csrf_token'] = $token;
    }

    return $token;
}

function app_verify_csrf_token(string $submittedToken): bool
{
    app_assert_active_session();

    $sessionToken = $_SESSION['_csrf_token'] ?? null;

    return is_string($sessionToken)
        && $sessionToken !== ''
        && $submittedToken !== ''
        && hash_equals($sessionToken, $submittedToken);
}

function app_assert_active_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('Session is not active.');
    }
}
