#!/usr/bin/env php
<?php

declare(strict_types=1);

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_sample18_task_board_http_smoke.php [options]

Options:
  --lab-base-url=URL       Lab base URL (default: http://127.0.0.1:${LAB_HTTP_PORT:-18272})
  --lab-user=USER          Lab login user (default: LAB_AUTH_STUB_USER or lab-local)
  --lab-password=PASSWORD  Lab login password (default: LAB_AUTH_STUB_PASSWORD or change-this-lab-password)
  --timeout=SECONDS        HTTP timeout seconds (default: 10)
  --pretty                 Pretty-print JSON result
  --help                   Show this help

TEXT;
}

function parse_args(array $argv): array
{
    $args = [
        'lab_base_url' => '',
        'lab_user' => getenv('LAB_AUTH_STUB_USER') ?: 'lab-local',
        'lab_password' => getenv('LAB_AUTH_STUB_PASSWORD') ?: 'change-this-lab-password',
        'timeout' => 10,
        'pretty' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            echo usage();
            exit(0);
        }
        if ($arg === '--pretty') {
            $args['pretty'] = true;
            continue;
        }
        if (!str_starts_with($arg, '--') || !str_contains($arg, '=')) {
            throw new InvalidArgumentException('unsupported argument: ' . $arg);
        }

        [$name, $value] = explode('=', substr($arg, 2), 2);
        match ($name) {
            'lab-base-url' => $args['lab_base_url'] = rtrim($value, '/'),
            'lab-user' => $args['lab_user'] = $value,
            'lab-password' => $args['lab_password'] = $value,
            'timeout' => $args['timeout'] = max(1, (int) $value),
            default => throw new InvalidArgumentException('unsupported option: --' . $name),
        };
    }

    if ($args['lab_base_url'] === '') {
        $labPort = getenv('LAB_HTTP_PORT') ?: '18272';
        $args['lab_base_url'] = 'http://127.0.0.1:' . $labPort;
    }

    return $args;
}

function absolute_url(string $baseUrl, string $path): string
{
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

function header_map(array $lines): array
{
    $headers = [];
    foreach ($lines as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }
        [$name, $value] = explode(':', $line, 2);
        $key = strtolower(trim($name));
        if ($key !== '') {
            $headers[$key][] = trim($value);
        }
    }

    return $headers;
}

function store_cookies(array &$cookies, array $headerLines): void
{
    foreach ($headerLines as $line) {
        if (stripos($line, 'Set-Cookie:') !== 0) {
            continue;
        }
        $pair = explode(';', trim(substr($line, strlen('Set-Cookie:'))), 2)[0] ?? '';
        if (!str_contains($pair, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $pair, 2);
        if (trim($name) !== '') {
            $cookies[trim($name)] = $value;
        }
    }
}

function http_status(array $headerLines): int
{
    foreach ($headerLines as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})\b#', $line, $matches) === 1) {
            return (int) $matches[1];
        }
    }

    return 0;
}

function request_once(array &$client, string $method, string $path, array $options = []): array
{
    $headers = [];
    foreach (($options['headers'] ?? []) as $name => $value) {
        $headers[] = trim((string) $name) . ': ' . (string) $value;
    }
    if ($client['cookies'] !== []) {
        $pairs = [];
        foreach ($client['cookies'] as $name => $value) {
            $pairs[] = $name . '=' . $value;
        }
        $headers[] = 'Cookie: ' . implode('; ', $pairs);
    }

    $body = $options['body'] ?? null;
    if ($body === null && array_key_exists('form_params', $options)) {
        $body = http_build_query($options['form_params'], '', '&', PHP_QUERY_RFC3986);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }

    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headers),
            'content' => $body ?? '',
            'timeout' => $client['timeout'],
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
        ],
    ]);

    $error = '';
    set_error_handler(static function (int $severity, string $message) use (&$error): bool {
        $error = $message;
        return true;
    });
    try {
        $responseBody = file_get_contents(absolute_url($client['base_url'], $path), false, $context);
    } finally {
        restore_error_handler();
    }

    $headerLines = [];
    $rawHeaders = function_exists('http_get_last_response_headers')
        ? http_get_last_response_headers()
        : ($http_response_header ?? []);
    if (is_array($rawHeaders)) {
        foreach ($rawHeaders as $line) {
            if (is_string($line) && trim($line) !== '') {
                $headerLines[] = $line;
            }
        }
    }
    store_cookies($client['cookies'], $headerLines);
    $headerMap = header_map($headerLines);

    return [
        'ok' => $error === '' || http_status($headerLines) !== 0,
        'status' => http_status($headerLines),
        'path' => $path,
        'body' => is_string($responseBody) ? $responseBody : '',
        'location' => (string) ($headerMap['location'][0] ?? ''),
        'error' => $error,
    ];
}

function request_follow(array &$client, string $method, string $path, array $options = []): array
{
    $currentMethod = strtoupper($method);
    $currentPath = $path;
    $currentOptions = $options;
    $redirects = [];

    for ($i = 0; $i <= 10; $i++) {
        $response = request_once($client, $currentMethod, $currentPath, $currentOptions);
        if (!in_array($response['status'], [301, 302, 303, 307, 308], true) || $response['location'] === '') {
            $response['final_path'] = $currentPath;
            $response['redirects'] = $redirects;
            return $response;
        }

        $redirects[] = [
            'from_path' => $currentPath,
            'status' => $response['status'],
            'location' => $response['location'],
        ];

        $location = $response['location'];
        if (preg_match('#\Ahttps?://#i', $location) === 1) {
            $pathPart = (string) parse_url($location, PHP_URL_PATH);
            $queryPart = (string) parse_url($location, PHP_URL_QUERY);
            $currentPath = $pathPart . ($queryPart !== '' ? '?' . $queryPart : '');
        } else {
            $currentPath = '/' . ltrim($location, '/');
        }

        if (in_array($response['status'], [301, 302, 303], true)) {
            $currentMethod = 'GET';
            $currentOptions = [];
        }
    }

    throw new RuntimeException('too many redirects');
}

function input_value(string $html, string $name): string
{
    $patterns = [
        '/<input\b[^>]*\bname="' . preg_quote($name, '/') . '"[^>]*\bvalue="([^"]*)"[^>]*>/iu',
        '/<input\b[^>]*\bvalue="([^"]*)"[^>]*\bname="' . preg_quote($name, '/') . '"[^>]*>/iu',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $html, $matches) === 1) {
            return html_entity_decode((string) ($matches[1] ?? ''), ENT_QUOTES, 'UTF-8');
        }
    }

    return '';
}

function ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function response_excerpt(array $response): string
{
    $body = trim((string) ($response['body'] ?? ''));
    return $body !== '' ? ' body=' . substr($body, 0, 400) : '';
}

function run_smoke(array $args): array
{
    $client = [
        'base_url' => $args['lab_base_url'],
        'timeout' => $args['timeout'],
        'cookies' => [],
    ];

    $health = request_once($client, 'GET', '/health');
    ensure($health['status'] === 200, 'lab health status is not 200: ' . $health['status'] . response_excerpt($health));

    $loginRedirectPath = '/samples/sample18-task-board';
    $loginPage = request_once($client, 'GET', '/login?redirect=' . rawurlencode($loginRedirectPath));
    ensure($loginPage['status'] === 200, 'login page status is not 200: ' . $loginPage['status'] . response_excerpt($loginPage));
    $loginCsrf = input_value($loginPage['body'], '_csrf');
    ensure($loginCsrf !== '', 'login CSRF token not found');

    $loginSubmit = request_follow($client, 'POST', '/login', [
        'form_params' => [
            '_csrf' => $loginCsrf,
            'redirect' => $loginRedirectPath,
            'username' => $args['lab_user'],
            'password' => $args['lab_password'],
        ],
    ]);
    ensure($loginSubmit['status'] === 200, 'login submit final status is not 200: ' . $loginSubmit['status'] . response_excerpt($loginSubmit));
    ensure($loginSubmit['final_path'] === $loginRedirectPath, 'login final path mismatch: ' . $loginSubmit['final_path']);
    ensure(str_contains($loginSubmit['body'], 'Sample18 Mini Task Board'), 'sample18 page title missing after login');

    $pageCsrf = input_value($loginSubmit['body'], '_csrf_token');
    ensure($pageCsrf !== '', 'sample18 form CSRF token not found');

    $title = 'HTTP smoke task ' . date('His');
    $create = request_follow($client, 'POST', '/samples/sample18-task-board', [
        'form_params' => [
            '_csrf_token' => $pageCsrf,
            'action' => 'create',
            'title' => $title,
            'body' => 'Created by sample18 HTTP smoke.',
            'assigned_to' => 'Smoke',
            'priority' => '15',
            'due_date' => date('Y-m-d'),
        ],
    ]);
    ensure($create['status'] === 200, 'create final status is not 200: ' . $create['status'] . response_excerpt($create));
    ensure(str_contains($create['body'], $title), 'created task title was not rendered');

    ensure(preg_match('/edit_id=(\d+)/', $create['body'], $matches) === 1, 'edit link was not rendered');
    $taskId = (string) ($matches[1] ?? '');
    $editPage = request_once($client, 'GET', '/samples/sample18-task-board?edit_id=' . rawurlencode($taskId));
    ensure($editPage['status'] === 200, 'edit page status is not 200: ' . $editPage['status'] . response_excerpt($editPage));
    ensure(str_contains($editPage['body'], 'Update Task'), 'update form was not rendered');
    $editCsrf = input_value($editPage['body'], '_csrf_token');
    ensure($editCsrf !== '', 'edit form CSRF token not found');

    $updatedTitle = $title . ' edited';
    $update = request_follow($client, 'POST', '/samples/sample18-task-board', [
        'form_params' => [
            '_csrf_token' => $editCsrf,
            'action' => 'update',
            'id' => $taskId,
            'title' => $updatedTitle,
            'body' => 'Updated by sample18 HTTP smoke.',
            'status' => 'doing',
            'assigned_to' => 'Smoke',
            'priority' => '20',
            'due_date' => date('Y-m-d'),
        ],
    ]);
    ensure($update['status'] === 200, 'update final status is not 200: ' . $update['status'] . response_excerpt($update));
    ensure(str_contains($update['body'], $updatedTitle), 'updated task title was not rendered');
    ensure(str_contains($update['body'], 'doing'), 'updated task status was not rendered');

    return [
        'ok' => true,
        'lab_base_url' => $args['lab_base_url'],
        'page_path' => '/samples/sample18-task-board',
        'checks' => [
            'health' => ['status' => $health['status']],
            'login' => ['status' => $loginSubmit['status'], 'final_path' => $loginSubmit['final_path']],
            'create' => ['status' => $create['status'], 'title' => $title],
            'update' => ['status' => $update['status'], 'title' => $updatedTitle],
        ],
    ];
}

try {
    $args = parse_args($argv);
    $result = run_smoke($args);
    echo json_encode($result, ($args['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
} catch (Throwable $throwable) {
    $pretty = in_array('--pretty', $argv, true);
    echo json_encode(
        ['ok' => false, 'error' => $throwable->getMessage()],
        ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ) . PHP_EOL;
    exit(1);
}
