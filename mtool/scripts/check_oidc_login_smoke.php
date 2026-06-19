<?php

declare(strict_types=1);

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_oidc_login_smoke.php [options]

Options:
  --app-port=PORT       Mtool app port (default: 18311)
  --idp-port=PORT       Mock OIDC provider port (default: 18312)
  --timeout=SECONDS     HTTP timeout seconds (default: 10)
  --pretty              Pretty-print JSON result
  --help                Show this help

TEXT;
}

function parse_args(array $argv): array
{
    $args = [
        'app_port' => 18311,
        'idp_port' => 18312,
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
            'app-port' => $args['app_port'] = max(1, (int) $value),
            'idp-port' => $args['idp_port'] = max(1, (int) $value),
            'timeout' => $args['timeout'] = max(1, (int) $value),
            default => throw new InvalidArgumentException('unsupported option: --' . $name),
        };
    }

    if ($args['app_port'] === $args['idp_port']) {
        throw new InvalidArgumentException('app port and idp port must differ.');
    }

    return $args;
}

function repo_root(): string
{
    return dirname(__DIR__, 2);
}

function ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function base64url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function write_app_router(string $path): void
{
    $indexPath = repo_root() . '/mtool/admin/public/index.php';
    $quotedIndexPath = var_export($indexPath, true);
    $source = <<<PHP
<?php
\$path = parse_url(\$_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
\$file = __DIR__ . \$path;
if (\$path !== '/' && is_file(\$file)) {
    return false;
}
require {$quotedIndexPath};
PHP;

    file_put_contents($path, $source);
}

function create_temp_dir(): string
{
    $dir = sys_get_temp_dir() . '/dego-oidc-smoke-' . getmypid() . '-' . bin2hex(random_bytes(4));
    ensure(mkdir($dir, 0777, true), 'failed to create temp dir: ' . $dir);
    return $dir;
}

function create_key_pair(string $dir): array
{
    $config = [
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    $resource = openssl_pkey_new($config);
    ensure($resource !== false, 'failed to create RSA key pair.');

    $privateKey = '';
    ensure(openssl_pkey_export($resource, $privateKey), 'failed to export private key.');
    $details = openssl_pkey_get_details($resource);
    ensure(is_array($details) && is_string($details['key'] ?? null), 'failed to export public key.');

    $privatePath = $dir . '/mock-oidc-private.pem';
    $publicPath = $dir . '/mock-oidc-public.pem';
    file_put_contents($privatePath, $privateKey);
    file_put_contents($publicPath, $details['key']);

    return [
        'private_path' => $privatePath,
        'public_path' => $publicPath,
        'key_id' => 'mock-key-' . base64url(random_bytes(6)),
    ];
}

function start_server(string $name, array $command, array $env, string $cwd, string $logPath): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['file', $logPath, 'a'],
        2 => ['file', $logPath, 'a'],
    ];
    $process = proc_open($command, $descriptorSpec, $pipes, $cwd, $env);
    if (!is_resource($process)) {
        throw new RuntimeException('failed to start ' . $name);
    }
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }

    return [
        'name' => $name,
        'process' => $process,
        'log_path' => $logPath,
    ];
}

function stop_server(array $server): void
{
    $process = $server['process'] ?? null;
    if (is_resource($process)) {
        proc_terminate($process);
        proc_close($process);
    }
}

function log_excerpt(string $path): string
{
    if (!is_file($path)) {
        return '';
    }

    $raw = file_get_contents($path);
    if (!is_string($raw)) {
        return '';
    }

    return substr($raw, -3000);
}

function wait_for_http(string $url, int $timeoutSeconds, string $logPath): void
{
    $deadline = microtime(true) + $timeoutSeconds;
    do {
        $headers = @get_headers($url);
        if (is_array($headers) && isset($headers[0]) && preg_match('/\s(200|302)\s/', (string) $headers[0]) === 1) {
            return;
        }
        usleep(100000);
    } while (microtime(true) < $deadline);

    throw new RuntimeException('server did not become ready: ' . $url . "\n" . log_excerpt($logPath));
}

function header_map(array $lines): array
{
    $headers = [];
    foreach ($lines as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }
        [$name, $value] = explode(':', $line, 2);
        $headers[strtolower(trim($name))][] = trim($value);
    }

    return $headers;
}

function store_cookies(array &$cookies, array $headerLines, string $host): void
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
            $cookies[$host][$name] = $value;
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

function request_once(array &$client, string $method, string $url): array
{
    $host = parse_url($url, PHP_URL_HOST) . ':' . parse_url($url, PHP_URL_PORT);
    $headerLines = [];
    if (($client['cookies'][$host] ?? []) !== []) {
        $pairs = [];
        foreach ($client['cookies'][$host] as $name => $value) {
            $pairs[] = $name . '=' . $value;
        }
        $headerLines[] = 'Cookie: ' . implode('; ', $pairs);
    }

    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headerLines),
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
        $body = file_get_contents($url, false, $context);
    } finally {
        restore_error_handler();
    }

    $rawHeaders = function_exists('http_get_last_response_headers')
        ? http_get_last_response_headers()
        : ($http_response_header ?? []);
    $responseHeaders = [];
    if (is_array($rawHeaders)) {
        foreach ($rawHeaders as $line) {
            if (is_string($line) && trim($line) !== '') {
                $responseHeaders[] = $line;
            }
        }
    }

    store_cookies($client['cookies'], $responseHeaders, $host);
    $headers = header_map($responseHeaders);

    return [
        'ok' => $error === '' || http_status($responseHeaders) !== 0,
        'status' => http_status($responseHeaders),
        'url' => $url,
        'headers' => $headers,
        'location' => (string) ($headers['location'][0] ?? ''),
        'body' => is_string($body) ? $body : '',
        'error' => $error,
    ];
}

function absolute_location(string $currentUrl, string $location): string
{
    if (preg_match('#\Ahttps?://#i', $location) === 1) {
        return $location;
    }

    $scheme = parse_url($currentUrl, PHP_URL_SCHEME) ?: 'http';
    $host = parse_url($currentUrl, PHP_URL_HOST) ?: '127.0.0.1';
    $port = parse_url($currentUrl, PHP_URL_PORT);

    return $scheme . '://' . $host . ($port !== null ? ':' . $port : '') . '/' . ltrim($location, '/');
}

function request_follow(array &$client, string $url): array
{
    $redirects = [];
    for ($i = 0; $i < 10; $i++) {
        $response = request_once($client, 'GET', $url);
        if (!in_array($response['status'], [301, 302, 303, 307, 308], true) || $response['location'] === '') {
            $response['redirects'] = $redirects;
            return $response;
        }

        $redirects[] = [
            'status' => $response['status'],
            'from' => $url,
            'location' => $response['location'],
        ];
        $url = absolute_location($url, $response['location']);
    }

    throw new RuntimeException('too many redirects');
}

function session_file_payload(string $sessionDir, array $cookies): string
{
    $sessionId = (string) (($cookies['127.0.0.1:18311']['MTOOL_OIDC_SMOKE_SESSID'] ?? '') ?: '');
    if ($sessionId === '') {
        foreach ($cookies as $cookieSet) {
            if (isset($cookieSet['MTOOL_OIDC_SMOKE_SESSID'])) {
                $sessionId = (string) $cookieSet['MTOOL_OIDC_SMOKE_SESSID'];
                break;
            }
        }
    }
    ensure($sessionId !== '', 'OIDC smoke session cookie was not set.');

    $path = rtrim($sessionDir, '/') . '/sess_' . preg_replace('/[^A-Za-z0-9,-]/', '', $sessionId);
    ensure(is_file($path), 'OIDC smoke session file was not found: ' . $path);
    $payload = file_get_contents($path);
    ensure(is_string($payload) && $payload !== '', 'OIDC smoke session file is empty.');

    return $payload;
}

function run_smoke(array $args): array
{
    $root = repo_root();
    $tmp = create_temp_dir();
    $sessionDir = $tmp . '/sessions';
    $configStoreDir = $tmp . '/config-store';
    mkdir($sessionDir, 0777, true);
    mkdir($configStoreDir, 0777, true);
    $keys = create_key_pair($tmp);
    $appRouter = $tmp . '/app-router.php';
    write_app_router($appRouter);

    $appBaseUrl = 'http://127.0.0.1:' . $args['app_port'];
    $idpBaseUrl = 'http://127.0.0.1:' . $args['idp_port'];
    $servers = [];

    try {
        $servers[] = start_server(
            'mock-oidc',
            [
                PHP_BINARY,
                '-S',
                '127.0.0.1:' . $args['idp_port'],
                $root . '/mtool/scripts/mock_oidc_provider_router.php',
            ],
            array_merge($_ENV, [
                'MOCK_OIDC_ISSUER' => $idpBaseUrl,
                'MOCK_OIDC_PRIVATE_KEY_PATH' => $keys['private_path'],
                'MOCK_OIDC_PUBLIC_KEY_PATH' => $keys['public_path'],
                'MOCK_OIDC_KEY_ID' => $keys['key_id'],
                'MOCK_OIDC_GROUPS' => 'dego-config,dego:project:CLAIM-FIRST:publisher',
            ]),
            $root,
            $tmp . '/mock-oidc.log',
        );
        wait_for_http($idpBaseUrl . '/.well-known/openid-configuration', $args['timeout'], $tmp . '/mock-oidc.log');

        $servers[] = start_server(
            'mtool-app',
            [
                PHP_BINARY,
                '-d',
                'session.save_path=' . $sessionDir,
                '-S',
                '127.0.0.1:' . $args['app_port'],
                '-t',
                $root . '/mtool/admin/public',
                $appRouter,
            ],
            array_merge($_ENV, [
                'APP_APP_ROOT' => $root . '/mtool/app',
                'APP_SITE' => 'admin',
                'APP_SITE_NAME' => 'OIDC Smoke Admin',
                'APP_SESSION_NAME' => 'MTOOL_OIDC_SMOKE_SESSID',
                'APP_AUTH_MODE' => 'oidc',
                'APP_AUTH_OIDC_ISSUER' => $idpBaseUrl,
                'APP_AUTH_OIDC_CLIENT_ID' => 'mtool-oidc-smoke',
                'APP_AUTH_OIDC_CLIENT_SECRET' => 'mock-secret',
                'APP_AUTH_OIDC_REDIRECT_URI' => $appBaseUrl . '/auth/oidc/callback',
                'APP_AUTH_OIDC_SCOPES' => 'openid,profile,email',
                'APP_AUTH_OIDC_GROUPS_CLAIM' => 'groups',
                'APP_AUTH_OIDC_CONFIG_GROUPS' => 'dego-config',
                'APP_AUTH_OIDC_PROJECT_ROLE_GROUP_PREFIX' => 'dego:project:',
                'APP_AUTH_OIDC_DEFAULT_ROLES' => '',
                'APP_CONFIG_STORE_DRIVER' => 'sqlite',
                'APP_CONFIG_STORE_DIR' => $configStoreDir,
                'APP_WORK_ROOT' => $tmp . '/work',
                'APP_PROJECT_REPOSITORY_DRIVER' => 'pdo',
                'APP_EXPERIMENT_REPOSITORY_DRIVER' => 'pdo',
            ]),
            $root,
            $tmp . '/mtool-app.log',
        );
        wait_for_http($appBaseUrl . '/health', $args['timeout'], $tmp . '/mtool-app.log');

        $client = [
            'timeout' => $args['timeout'],
            'cookies' => [],
        ];
        $loginUrl = $appBaseUrl . '/login?redirect=' . rawurlencode('/projects');
        $response = request_follow($client, $loginUrl);
        ensure($response['status'] === 200, 'OIDC login final status is not 200: ' . $response['status']);
        ensure(str_contains($response['url'], '/projects'), 'OIDC login did not return to /projects: ' . $response['url']);
        ensure(str_contains($response['body'], 'OIDC Smoke User') || str_contains($response['body'], 'Projects') || str_contains($response['body'], 'プロジェクト'), 'OIDC login final page did not look authenticated.');

        $sessionPayload = session_file_payload($sessionDir, $client['cookies']);
        ensure(str_contains($sessionPayload, 'app_principal'), 'session does not contain app_principal.');
        ensure(str_contains($sessionPayload, 'OIDC Smoke User'), 'session does not contain OIDC display name.');
        ensure(str_contains($sessionPayload, 'project_roles'), 'session does not contain project_roles.');
        ensure(str_contains($sessionPayload, 'CLAIM-FIRST'), 'session does not contain CLAIM-FIRST project role.');
        ensure(str_contains($sessionPayload, 'publisher'), 'session does not contain publisher project role.');

        return [
            'ok' => true,
            'app_base_url' => $appBaseUrl,
            'idp_base_url' => $idpBaseUrl,
            'final_url' => $response['url'],
            'redirect_count' => count($response['redirects']),
            'checks' => [
                'mock_discovery' => 'ok',
                'oidc_redirect_callback' => 'ok',
                'authenticated_projects_page' => 'ok',
                'session_principal_project_roles' => 'ok',
            ],
        ];
    } finally {
        foreach (array_reverse($servers) as $server) {
            stop_server($server);
        }
    }
}

try {
    $args = parse_args($argv);
    $result = run_smoke($args);
    echo json_encode($result, ($args['pretty'] ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
} catch (Throwable $throwable) {
    $pretty = in_array('--pretty', $argv, true);
    echo json_encode([
        'ok' => false,
        'error' => $throwable->getMessage(),
    ], ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(1);
}
