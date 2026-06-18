<?php

declare(strict_types=1);

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_sample16_authenticated_proxy_http_smoke.php [options]

Options:
  --lab-base-url=URL       Lab base URL (default: http://127.0.0.1:${LAB_HTTP_PORT:-18252})
  --lab-user=USER          Lab login user (default: LAB_AUTH_STUB_USER or lab-local)
  --lab-password=PASSWORD  Lab login password (default: LAB_AUTH_STUB_PASSWORD or change-this-lab-password)
  --proxy-token=TOKEN      Generated proxy project token (default: MTOOL_PROXY_PROJECT_TOKEN or sample16-project-token)
  --task-id=ID             AuthTask Id to fetch (default: 1)
  --db-source-key=KEY      Optional runtime db source key query parameter
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
        'proxy_token' => getenv('MTOOL_PROXY_PROJECT_TOKEN') ?: 'sample16-project-token',
        'task_id' => 1,
        'db_source_key' => '',
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
            'proxy-token' => $args['proxy_token'] = $value,
            'task-id' => $args['task_id'] = max(1, (int) $value),
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
        if ($key === '') {
            continue;
        }
        $headers[$key][] = trim($value);
    }

    return $headers;
}

function store_cookies(array &$cookies, array $headerLines): void
{
    foreach ($headerLines as $line) {
        if (stripos($line, 'Set-Cookie:') !== 0) {
            continue;
        }
        $cookie = trim(substr($line, strlen('Set-Cookie:')));
        $pair = explode(';', $cookie, 2)[0] ?? '';
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
    $url = absolute_url($client['base_url'], $path);
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
        $responseBody = file_get_contents($url, false, $context);
    } finally {
        restore_error_handler();
    }

    if (!is_string($responseBody)) {
        $responseBody = '';
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
        'url' => $url,
        'headers' => $headers,
        'body' => $responseBody,
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
    if ($body === '') {
        return '';
    }

    return ' body=' . substr($body, 0, 500);
}

function decode_json_body(array $response, string $label): array
{
    $decoded = json_decode($response['body'], true);
    ensure(is_array($decoded), $label . ' response is not JSON: ' . substr($response['body'], 0, 300));
    return $decoded;
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

    $proxyPath = '/runs/proxy/SAMPLE16/AUTH-PROXY-SERVER/proxyserver-AuthTask-GetAuthTask.php';
    $dbSourceKey = (string) $args['db_source_key'];
    if ($dbSourceKey !== '') {
        $proxyPath .= '?db_source_key=' . rawurlencode($dbSourceKey);
    }

    $loginRedirectPath = '/dashboard';
    $loginPath = '/login?redirect=' . rawurlencode($loginRedirectPath);
    $loginPage = request_once($client, 'GET', $loginPath);
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

    $missingToken = request_once($client, 'POST', $proxyPath, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode(['param_AuthTask_Id_where' => $args['task_id']], JSON_UNESCAPED_SLASHES),
    ]);
    $missingPayload = decode_json_body($missingToken, 'missing token');
    ensure($missingToken['status'] === 500, 'missing token status is not 500: ' . $missingToken['status'] . response_excerpt($missingToken));
    ensure(($missingPayload['_status'] ?? '') === 'NG', 'missing token did not fail closed');

    $wrongToken = request_once($client, 'POST', $proxyPath, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode([
            'TOKEN' => 'wrong-token',
            'param_AuthTask_Id_where' => $args['task_id'],
        ], JSON_UNESCAPED_SLASHES),
    ]);
    $wrongPayload = decode_json_body($wrongToken, 'wrong token');
    ensure($wrongToken['status'] === 500, 'wrong token status is not 500: ' . $wrongToken['status'] . response_excerpt($wrongToken));
    ensure(($wrongPayload['_status'] ?? '') === 'NG', 'wrong token did not fail closed');

    $matchingToken = request_once($client, 'POST', $proxyPath, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode([
            'TOKEN' => $args['proxy_token'],
            'param_AuthTask_Id_where' => $args['task_id'],
        ], JSON_UNESCAPED_SLASHES),
    ]);
    $matchingPayload = decode_json_body($matchingToken, 'matching token');
    ensure($matchingToken['status'] === 200, 'matching token status is not 200: ' . $matchingToken['status'] . response_excerpt($matchingToken));
    ensure(($matchingPayload['_status'] ?? '') === 'OK', 'matching token did not return OK' . response_excerpt($matchingToken));
    ensure(isset($matchingPayload['Result']) && is_array($matchingPayload['Result']), 'matching token result missing');

    return [
        'ok' => true,
        'lab_base_url' => $args['lab_base_url'],
        'proxy_path' => $proxyPath,
        'checks' => [
            'health' => ['status' => $health['status']],
            'login' => ['status' => $loginSubmit['status'], 'final_path' => $loginSubmit['final_path']],
            'missing_token' => ['status' => $missingToken['status'], 'payload_status' => $missingPayload['_status'] ?? ''],
            'wrong_token' => ['status' => $wrongToken['status'], 'payload_status' => $wrongPayload['_status'] ?? ''],
            'matching_token' => [
                'status' => $matchingToken['status'],
                'payload_status' => $matchingPayload['_status'] ?? '',
                'result_keys' => array_keys($matchingPayload['Result']),
            ],
        ],
    ];
}

try {
    $args = parse_args($argv);
    $result = run_smoke($args);
    echo json_encode($result, ($args['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
} catch (Throwable $throwable) {
    $payload = [
        'ok' => false,
        'error' => $throwable->getMessage(),
    ];
    $pretty = in_array('--pretty', $argv, true);
    echo json_encode($payload, ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(1);
}
