<?php

declare(strict_types=1);

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_sample13_openapi_swagger_http_smoke.php [options]

Options:
  --lab-base-url=URL       Lab base URL (default: http://127.0.0.1:${LAB_HTTP_PORT:-18252})
  --lab-user=USER          Lab login user (default: LAB_AUTH_STUB_USER or lab-local)
  --lab-password=PASSWORD  Lab login password (default: LAB_AUTH_STUB_PASSWORD or change-this-lab-password)
  --db-source-key=KEY      Runtime db source key query parameter (default: config_db)
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
        'db_source_key' => 'config_db',
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
            'db-source-key' => $args['db_source_key'] = trim($value),
            'timeout' => $args['timeout'] = max(1, (int) $value),
            default => throw new InvalidArgumentException('unsupported option: --' . $name),
        };
    }

    if ($args['lab_base_url'] === '') {
        $labPort = getenv('LAB_HTTP_PORT') ?: '18252';
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
        $name = trim($name);
        if ($name !== '') {
            $cookies[$name] = $value;
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
    $headerLines = [];
    foreach (($options['headers'] ?? []) as $name => $value) {
        $headerLines[] = trim((string) $name) . ': ' . (string) $value;
    }
    if ($client['cookies'] !== []) {
        $pairs = [];
        foreach ($client['cookies'] as $name => $value) {
            $pairs[] = $name . '=' . $value;
        }
        $headerLines[] = 'Cookie: ' . implode('; ', $pairs);
    }

    $body = $options['body'] ?? null;
    if ($body === null && array_key_exists('form_params', $options)) {
        $body = http_build_query($options['form_params'], '', '&', PHP_QUERY_RFC3986);
        $headerLines[] = 'Content-Type: application/x-www-form-urlencoded';
    }

    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headerLines),
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

    $rawResponseHeaders = function_exists('http_get_last_response_headers')
        ? http_get_last_response_headers()
        : ($http_response_header ?? []);
    $responseHeaders = [];
    if (is_array($rawResponseHeaders)) {
        foreach ($rawResponseHeaders as $line) {
            if (is_string($line) && trim($line) !== '') {
                $responseHeaders[] = $line;
            }
        }
    }

    store_cookies($client['cookies'], $responseHeaders);
    $headers = header_map($responseHeaders);

    return [
        'ok' => $error === '' || http_status($responseHeaders) !== 0,
        'status' => http_status($responseHeaders),
        'path' => $path,
        'headers' => $headers,
        'body' => is_string($responseBody) ? $responseBody : '',
        'location' => (string) ($headers['location'][0] ?? ''),
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
    return $body === '' ? '' : ' body=' . substr($body, 0, 500);
}

function assert_body_contains(string $body, string $needle, string $label): void
{
    ensure(str_contains($body, $needle), $label . ' not found: ' . $needle);
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

    $swaggerPath = '/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON';
    if ((string) $args['db_source_key'] !== '') {
        $swaggerPath .= '&db_source_key=' . rawurlencode((string) $args['db_source_key']);
    }

    $loginRedirectPath = $swaggerPath;
    $loginPage = request_once($client, 'GET', '/login?redirect=' . rawurlencode($loginRedirectPath));
    ensure($loginPage['status'] === 200, 'login page status is not 200: ' . $loginPage['status'] . response_excerpt($loginPage));

    $csrf = input_value($loginPage['body'], '_csrf');
    ensure($csrf !== '', 'login CSRF token not found');

    $loginSubmit = request_follow($client, 'POST', '/login', [
        'form_params' => [
            '_csrf' => $csrf,
            'redirect' => $loginRedirectPath,
            'username' => $args['lab_user'],
            'password' => $args['lab_password'],
        ],
    ]);
    ensure($loginSubmit['status'] === 200, 'login submit final status is not 200: ' . $loginSubmit['status'] . response_excerpt($loginSubmit));
    ensure($loginSubmit['final_path'] === $loginRedirectPath, 'login final path mismatch: ' . $loginSubmit['final_path']);

    $viewer = request_once($client, 'GET', $swaggerPath);
    ensure($viewer['status'] === 200, 'swagger viewer status is not 200: ' . $viewer['status'] . response_excerpt($viewer));

    $body = $viewer['body'];
    assert_body_contains($body, 'Sample13 OpenAPI JSON', 'source output title');
    assert_body_contains($body, 'source: <code>published-output</code>', 'published spec source');
    assert_body_contains($body, 'operations: <code>2</code>', 'operation count');
    assert_body_contains($body, 'source/function: <code>ApiTask.GetApiTask</code>', 'GetApiTask operation');
    assert_body_contains($body, 'source/function: <code>ApiTask.GetApiTaskList</code>', 'GetApiTaskList operation');
    assert_body_contains($body, 'name="db_source_key"', 'db source selector');
    assert_body_contains($body, '<option value="config_db" selected>', 'selected config_db runtime source');
    assert_body_contains($body, 'Raw Spec', 'raw spec section');
    assert_body_contains($body, '&quot;openapi&quot;: &quot;3.0.3&quot;', 'openapi version in raw spec');

    $proxyPath = '/runs/proxy/SAMPLE13/API-PROXY-SERVER/proxyserver-ApiTask-GetApiTask.php';
    if ((string) $args['db_source_key'] !== '') {
        $proxyPath .= '?db_source_key=' . rawurlencode((string) $args['db_source_key']);
    }
    $proxy = request_once($client, 'POST', $proxyPath, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode(['param_ApiTask_Id_where' => 1], JSON_UNESCAPED_SLASHES),
    ]);
    ensure($proxy['status'] === 200, 'proxy endpoint status is not 200: ' . $proxy['status'] . response_excerpt($proxy));
    $proxyPayload = json_decode($proxy['body'], true);
    ensure(is_array($proxyPayload), 'proxy endpoint response is not JSON: ' . substr($proxy['body'], 0, 300));
    ensure(($proxyPayload['_status'] ?? '') === 'OK', 'proxy endpoint payload status is not OK: ' . substr($proxy['body'], 0, 300));
    ensure(is_array($proxyPayload['Result'] ?? null), 'proxy endpoint Result is missing: ' . substr($proxy['body'], 0, 300));
    ensure((string) ($proxyPayload['Result']['Title'] ?? '') !== '', 'proxy endpoint Result.Title is empty');

    return [
        'ok' => true,
        'lab_base_url' => $args['lab_base_url'],
        'swagger_path' => $swaggerPath,
        'proxy_path' => $proxyPath,
        'checks' => [
            'health' => ['status' => $health['status']],
            'login' => ['status' => $loginSubmit['status'], 'final_path' => $loginSubmit['final_path']],
            'viewer' => ['status' => $viewer['status'], 'expected_operations' => 2],
            'proxy' => [
                'status' => $proxy['status'],
                'payload_status' => $proxyPayload['_status'] ?? '',
                'result_keys' => array_keys($proxyPayload['Result']),
            ],
        ],
    ];
}

try {
    $args = parse_args($argv);
    echo json_encode(
        run_smoke($args),
        ($args['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ) . PHP_EOL;
    exit(0);
} catch (Throwable $throwable) {
    $pretty = in_array('--pretty', $argv, true);
    echo json_encode(
        ['ok' => false, 'error' => $throwable->getMessage()],
        ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ) . PHP_EOL;
    exit(1);
}
