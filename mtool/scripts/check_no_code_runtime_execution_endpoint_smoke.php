#!/usr/bin/env php
<?php

declare(strict_types=1);

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php [options]

Options:
  --base-url=URL          Admin base URL (default: http://127.0.0.1:${ADMIN_HTTP_PORT:-18291})
  --current-path=PATH     Current runtime preview path
  --alias-path=PATH       Alias runtime preview path
  --admin-user=USER       Admin login user (default: ADMIN_AUTH_STUB_USER or admin-local)
  --admin-password=PASS   Admin login password (default: ADMIN_AUTH_STUB_PASSWORD or change-this-admin-password)
  --timeout=SECONDS       HTTP timeout seconds (default: 10)
  --pretty                Pretty-print JSON result
  --help                  Show this help

TEXT;
}

function parse_args(array $argv): array
{
    $args = [
        'base_url' => '',
        'current_path' => '',
        'alias_path' => '',
        'admin_user' => getenv('ADMIN_AUTH_STUB_USER') ?: 'admin-local',
        'admin_password' => getenv('ADMIN_AUTH_STUB_PASSWORD') ?: 'change-this-admin-password',
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
            'base-url' => $args['base_url'] = rtrim($value, '/'),
            'current-path' => $args['current_path'] = $value,
            'alias-path' => $args['alias_path'] = $value,
            'admin-user' => $args['admin_user'] = $value,
            'admin-password' => $args['admin_password'] = $value,
            'timeout' => $args['timeout'] = max(1, (int) $value),
            default => throw new InvalidArgumentException('unsupported option: --' . $name),
        };
    }

    if ($args['base_url'] === '') {
        $adminPort = getenv('ADMIN_HTTP_PORT') ?: '18291';
        $args['base_url'] = 'http://127.0.0.1:' . $adminPort;
    }
    if ($args['current_path'] === '' || $args['alias_path'] === '') {
        throw new InvalidArgumentException('--current-path and --alias-path are required');
    }

    return $args;
}

function ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
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

function runtime_execution_binding(string $html): array
{
    if (preg_match('/<script\b[^>]*\bid="no-code-runtime-execution-binding"[^>]*>(.*?)<\/script>/isu', $html, $matches) !== 1) {
        throw new RuntimeException('execution binding script was not found');
    }

    $binding = json_decode(html_entity_decode(trim((string) $matches[1]), ENT_QUOTES, 'UTF-8'), true);
    if (!is_array($binding)) {
        throw new RuntimeException('execution binding script is not valid JSON');
    }

    return $binding;
}

function login_admin(array &$client, string $username, string $password): void
{
    $login = request_once($client, 'GET', '/login?redirect=%2Fdashboard');
    ensure($login['status'] === 200, 'login page did not return 200');
    $csrf = input_value($login['body'], '_csrf');
    ensure($csrf !== '', 'login csrf token was not found');

    $submit = request_once($client, 'POST', '/login', [
        'form_params' => [
            '_csrf' => $csrf,
            'redirect' => '/dashboard',
            'username' => $username,
            'password' => $password,
        ],
    ]);
    ensure(in_array($submit['status'], [302, 303], true), 'login submit did not redirect');

    $dashboard = request_once($client, 'GET', '/dashboard');
    ensure($dashboard['status'] === 200, 'dashboard did not return 200 after login');
}

function endpoint_smoke(array &$client, string $label, string $previewPath, string $expectedUrlFragment): array
{
    $preview = request_once($client, 'GET', $previewPath);
    ensure($preview['status'] === 200, $label . ' preview did not return 200');

    $binding = runtime_execution_binding($preview['body']);
    foreach (['csrf_token', 'project_key', 'artifact_key', 'execution_url'] as $key) {
        ensure((string) ($binding[$key] ?? '') !== '', $label . ' binding missing ' . $key);
    }
    ensure(str_contains((string) $binding['execution_url'], $expectedUrlFragment), $label . ' execution URL mismatch');

    $response = request_once($client, 'POST', (string) $binding['execution_url'], [
        'form_params' => [
            '_csrf' => (string) $binding['csrf_token'],
            'project_key' => (string) $binding['project_key'],
            'artifact_key' => (string) $binding['artifact_key'],
            'action_key' => 'update_no_code_ticket',
            'input' => [
                'id' => '1001',
                'title' => 'First no-code app ticket',
                'status' => 'open',
                'priority' => '10',
                'body' => 'Generated sample28 direct endpoint smoke payload',
            ],
        ],
    ]);

    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' execution response was not JSON');
    ensure($response['status'] === 200, $label . ' execution did not return 200');
    ensure(($payload['ok'] ?? null) === true, $label . ' execution ok flag mismatch');
    ensure(($payload['executed'] ?? null) === true, $label . ' execution executed flag mismatch');
    ensure(($payload['error'] ?? '') === '', $label . ' execution error mismatch');
    ensure(is_array($payload['request'] ?? null) && ($payload['request']['ok'] ?? null) === true, $label . ' request contract did not pass');
    ensure(($payload['request']['action_key'] ?? '') === 'update_no_code_ticket', $label . ' request action key mismatch');
    ensure(($payload['request']['binding']['project_key'] ?? '') === 'SAMPLE28', $label . ' request project binding mismatch');
    ensure(($payload['request']['binding']['artifact_key'] ?? '') === (string) $binding['artifact_key'], $label . ' request artifact binding mismatch');
    ensure(($payload['intent']['operation_key'] ?? '') === 'update_no_code_ticket', $label . ' intent operation key mismatch');
    ensure((string) ($payload['intent']['payload']['key']['id'] ?? '') === '1001', $label . ' intent key mismatch');
    ensure(($payload['result']['sync_intent']['intent_version'] ?? '') === 'managed-operation-sync-intent-v0', $label . ' sync intent version mismatch');
    ensure(($payload['result']['sync_intent']['origin'] ?? '') === 'public-runtime', $label . ' sync intent origin mismatch');
    ensure(($payload['result']['sync_intent']['target'] ?? '') === 'server', $label . ' sync intent target mismatch');
    ensure(($payload['result']['executor_result']['ok'] ?? null) === true, $label . ' executor result ok flag mismatch');
    ensure(($payload['result']['executor_result']['item']['status'] ?? '') === 'pending', $label . ' outbox status mismatch');
    ensure((string) ($payload['result']['executor_result']['item']['id'] ?? '') !== '', $label . ' outbox item id missing');
    ensure((string) ($payload['result']['executor_result']['item']['dedupe_key'] ?? '') !== '', $label . ' outbox item dedupe key missing');
    ensure(($payload['result']['executor_result']['item']['operation_key'] ?? '') === 'update_no_code_ticket', $label . ' outbox item operation key mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'execution_url' => (string) $binding['execution_url'],
        'artifact_key' => (string) $binding['artifact_key'],
        'ok' => $payload['ok'],
        'executed' => $payload['executed'],
        'error' => $payload['error'],
        'request_ok' => $payload['request']['ok'],
        'dispatcher_ok' => $payload['result']['ok'] ?? null,
        'dispatcher_executed' => $payload['result']['executed'] ?? null,
        'sync_intent' => $payload['result']['sync_intent']['intent_version'] ?? '',
        'outbox_status' => $payload['result']['executor_result']['item']['status'] ?? '',
        'outbox_id' => $payload['result']['executor_result']['item']['id'] ?? '',
        'outbox_dedupe_key' => $payload['result']['executor_result']['item']['dedupe_key'] ?? '',
        'outbox_operation_key' => $payload['result']['executor_result']['item']['operation_key'] ?? '',
    ];
}

try {
    $args = parse_args($argv);
    $client = [
        'base_url' => $args['base_url'],
        'timeout' => $args['timeout'],
        'cookies' => [],
    ];

    login_admin($client, $args['admin_user'], $args['admin_password']);
    $results = [
        endpoint_smoke($client, 'current', $args['current_path'], '/current/execute.json'),
        endpoint_smoke($client, 'alias', $args['alias_path'], '/alias/'),
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | ($args['pretty'] ? JSON_PRETTY_PRINT : 0);
    echo json_encode(['ok' => true, 'results' => $results], $jsonFlags) . PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
