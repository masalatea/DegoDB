<?php

declare(strict_types=1);

/**
 * @return array{
 *     request_id:string,
 *     method:string,
 *     uri:string,
 *     path:string,
 *     query_string:string,
 *     host:string,
 *     scheme:string,
 *     remote_addr:string,
 *     user_agent:string
 * }
 */
function app_request_context(): array
{
    $uri = app_server_value('REQUEST_URI', '/');
    $path = parse_url($uri, PHP_URL_PATH);

    return [
        'request_id' => app_request_id(),
        'method' => strtoupper(app_server_value('REQUEST_METHOD', 'GET')),
        'uri' => $uri,
        'path' => is_string($path) && $path !== '' ? $path : '/',
        'query_string' => app_server_value('QUERY_STRING', ''),
        'host' => app_server_value('HTTP_HOST', 'localhost'),
        'scheme' => app_request_scheme(),
        'remote_addr' => app_server_value('REMOTE_ADDR', ''),
        'user_agent' => app_server_value('HTTP_USER_AGENT', ''),
    ];
}

function app_server_value(string $name, string $default): string
{
    $value = $_SERVER[$name] ?? null;
    if (!is_string($value) || $value === '') {
        return $default;
    }

    return $value;
}

function app_request_scheme(): string
{
    $https = app_server_value('HTTPS', '');
    if ($https !== '' && strtolower($https) !== 'off') {
        return 'https';
    }

    $forwardedProto = app_server_value('HTTP_X_FORWARDED_PROTO', '');
    if ($forwardedProto !== '') {
        return strtolower($forwardedProto);
    }

    return 'http';
}

function app_request_id(): string
{
    $forwarded = app_server_value('HTTP_X_REQUEST_ID', '');
    if ($forwarded !== '') {
        return $forwarded;
    }

    return bin2hex(random_bytes(8));
}

/**
 * @param array{
 *     method:string
 * } $request
 */
function app_request_method_is(array $request, string $method): bool
{
    return $request['method'] === strtoupper($method);
}

function app_query_param(string $name, string $default = ''): string
{
    $value = $_GET[$name] ?? null;
    if (!is_string($value) || $value === '') {
        return $default;
    }

    return $value;
}

function app_post_param(string $name, string $default = ''): string
{
    $value = $_POST[$name] ?? null;
    if (!is_string($value) || $value === '') {
        return $default;
    }

    return $value;
}

function app_post_array_param(string $name): array
{
    $value = $_POST[$name] ?? null;
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        if (is_string($item) || is_numeric($item)) {
            $items[] = (string) $item;
        }
    }

    return $items;
}

/**
 * @param array{
 *     path:string,
 *     query_string:string
 * } $request
 */
function app_request_path_with_query(array $request): string
{
    if ($request['query_string'] === '') {
        return $request['path'];
    }

    return $request['path'] . '?' . $request['query_string'];
}

function app_normalize_local_path(string $value, string $default): string
{
    $trimmed = trim($value);
    if ($trimmed === '') {
        return $default;
    }

    if ($trimmed[0] !== '/') {
        return $default;
    }

    if (str_starts_with($trimmed, '//') || str_contains($trimmed, '://')) {
        return $default;
    }

    return $trimmed;
}
