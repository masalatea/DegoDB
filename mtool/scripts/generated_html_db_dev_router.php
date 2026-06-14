<?php

declare(strict_types=1);

function app_generated_html_db_dev_router_plain_error(string $message, int $statusCode = 500): void
{
    http_response_code($statusCode);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message . PHP_EOL;
}

function app_generated_html_db_dev_router_docroot(): string
{
    $configured = getenv('MTOOL_HTML_DB_DOCROOT');
    if (!is_string($configured) || trim($configured) === '') {
        app_generated_html_db_dev_router_plain_error('MTOOL_HTML_DB_DOCROOT is required.');
        exit(1);
    }

    $docroot = rtrim($configured, '/');
    if (!is_dir($docroot)) {
        app_generated_html_db_dev_router_plain_error('MTOOL_HTML_DB_DOCROOT is not a directory: ' . $docroot);
        exit(1);
    }

    $resolved = realpath($docroot);
    if ($resolved === false || !is_dir($resolved)) {
        app_generated_html_db_dev_router_plain_error('Failed to resolve MTOOL_HTML_DB_DOCROOT: ' . $docroot);
        exit(1);
    }

    return rtrim(str_replace('\\', '/', $resolved), '/');
}

function app_generated_html_db_dev_router_app_root(): string
{
    $configured = getenv('APP_APP_ROOT');
    if (is_string($configured) && trim($configured) !== '') {
        $candidate = rtrim($configured, '/');
        if (is_file($candidate . '/http.php')) {
            return $candidate;
        }
    }

    return dirname(__DIR__) . '/app';
}

function app_generated_html_db_dev_router_static_candidate(string $docroot, string $requestPath): ?string
{
    $normalizedPath = rawurldecode($requestPath);
    if ($normalizedPath === '/' || $normalizedPath === '') {
        $indexPath = $docroot . '/index.php';
        return is_file($indexPath) ? $indexPath : null;
    }

    $relativePath = ltrim($normalizedPath, '/');
    if ($relativePath === '' || str_contains($relativePath, "\0")) {
        return null;
    }

    $candidate = realpath($docroot . '/' . $relativePath);
    if ($candidate === false) {
        $directoryIndex = realpath($docroot . '/' . rtrim($relativePath, '/') . '/index.php');
        if ($directoryIndex === false) {
            return null;
        }

        $candidate = $directoryIndex;
    }

    $normalizedCandidate = str_replace('\\', '/', $candidate);
    $normalizedDocroot = rtrim(str_replace('\\', '/', $docroot), '/');
    if (!str_starts_with($normalizedCandidate, $normalizedDocroot . '/')) {
        return null;
    }

    return is_file($normalizedCandidate) ? $normalizedCandidate : null;
}

function app_generated_html_db_dev_router_static_content_type(string $path): string
{
    return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'html', 'htm' => 'text/html; charset=UTF-8',
        'txt', 'md' => 'text/plain; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        default => 'application/octet-stream',
    };
}

function app_generated_html_db_dev_router_serve_static_file(string $path): void
{
    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php') {
        require $path;
        return;
    }

    header('Content-Type: ' . app_generated_html_db_dev_router_static_content_type($path));
    readfile($path);
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url(is_string($requestUri) ? $requestUri : '/', PHP_URL_PATH);
if (!is_string($requestPath) || $requestPath === '') {
    $requestPath = '/';
}

$docroot = app_generated_html_db_dev_router_docroot();
$staticCandidate = app_generated_html_db_dev_router_static_candidate($docroot, $requestPath);
if (is_string($staticCandidate)) {
    app_generated_html_db_dev_router_serve_static_file($staticCandidate);
    return;
}

$appRoot = app_generated_html_db_dev_router_app_root();
if (!is_file($appRoot . '/http.php')) {
    app_generated_html_db_dev_router_plain_error('Current app http.php was not found: ' . $appRoot . '/http.php');
    exit(1);
}

require $appRoot . '/http.php';
app_run_http_request();
