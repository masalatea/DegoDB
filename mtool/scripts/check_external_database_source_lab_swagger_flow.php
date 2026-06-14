#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/database_source_repository.php';
require_once dirname(__DIR__) . '/app/project_data_class_sync_service.php';
require_once dirname(__DIR__) . '/app/project_db_access_sync_service.php';
require_once dirname(__DIR__) . '/app/project_output_service.php';
require_once dirname(__DIR__) . '/app/project_table_import_source.php';
require_once dirname(__DIR__) . '/app/source_output_repository.php';

function app_cli_external_source_smoke_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_external_database_source_lab_swagger_flow.php [options]

Options:
  --project-key=KEY              project key (default: MTOOL)
  --table-name=NAME              imported table name (default: lab_experiments)
  --source-key=KEY               external named source key (default: generated temporary key)
  --source-label=LABEL           external source label
  --source-description=TEXT      external source description
  --source-host=HOST             external source host for admin/lab containers (default: db-lab)
  --source-port=PORT             external source port (default: 3306)
  --source-db-name=NAME          external source database name (default: .env LAB_DB_NAME)
  --source-db-user=USER          external source database user (default: .env LAB_DB_USER)
  --source-db-password=PASS      external source database password (default: .env LAB_DB_PASSWORD)
  --proxy-runtime-priority=N     external source runtime priority (default: 150)
  --admin-host=HOST              admin site host (default: 127.0.0.1)
  --admin-port=PORT              admin site port (default: 8081)
  --lab-host=HOST                lab site host (default: 127.0.0.1)
  --lab-port=PORT                lab site port (default: 8082)
  --admin-user=USER              admin stub username (default: .env ADMIN_AUTH_STUB_USER)
  --admin-password=PASS          admin stub password (default: .env ADMIN_AUTH_STUB_PASSWORD)
  --lab-user=USER                lab stub username (default: .env LAB_AUTH_STUB_USER)
  --lab-password=PASS            lab stub password (default: .env LAB_AUTH_STUB_PASSWORD)
  --config-db-host-port=PORT     host-side config DB port for direct app service calls
  --lab-db-host-port=PORT        host-side lab DB port for direct app service calls
  --config-db-name=NAME          host-side config DB name
  --config-db-user=USER          host-side config DB user
  --config-db-password=PASS      host-side config DB password
  --lab-db-name=NAME             host-side lab DB name
  --lab-db-user=USER             host-side lab DB user
  --lab-db-password=PASS         host-side lab DB password
  --http-timeout=SECONDS         HTTP timeout seconds (default: 10)
  --keep-source                  keep the created external source instead of deleting it
  --help                         show this help
TEXT;
}

function app_cli_external_source_smoke_repo_root(): string
{
    return dirname(__DIR__, 2);
}

/**
 * @return array<string,string>
 */
function app_cli_external_source_smoke_env_defaults(): array
{
    $envPath = app_cli_external_source_smoke_repo_root() . '/.env';
    if (!is_file($envPath)) {
        return [];
    }

    $parsed = parse_ini_file($envPath, false, INI_SCANNER_RAW);
    if (!is_array($parsed)) {
        return [];
    }

    $defaults = [];
    foreach ($parsed as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }

        $defaults[$key] = (string) $value;
    }

    return $defaults;
}

/**
 * @param list<string> $argv
 * @param array<string,string> $defaults
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     table_name:string,
 *     source_key:string,
 *     source_label:string,
 *     source_description:string,
 *     source_host:string,
 *     source_port:string,
 *     source_db_name:string,
 *     source_db_user:string,
 *     source_db_password:string,
 *     proxy_runtime_priority:int,
 *     admin_host:string,
 *     admin_port:int,
 *     lab_host:string,
 *     lab_port:int,
 *     admin_user:string,
 *     admin_password:string,
 *     lab_user:string,
 *     lab_password:string,
 *     config_db_host_port:string,
 *     lab_db_host_port:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string,
 *     lab_db_name:string,
 *     lab_db_user:string,
 *     lab_db_password:string,
 *     http_timeout_seconds:int,
 *     keep_source:bool,
 *     error:string
 * }
 */
function app_cli_external_source_smoke_parse_args(array $argv, array $defaults): array
{
    $parsed = [
        'project_key' => 'MTOOL',
        'table_name' => 'lab_experiments',
        'source_key' => '',
        'source_label' => 'external ui smoke lab db',
        'source_description' => 'temporary external named source for admin/lab smoke',
        'source_host' => 'db-lab',
        'source_port' => '3306',
        'source_db_name' => $defaults['LAB_DB_NAME'] ?? 'lab_app',
        'source_db_user' => $defaults['LAB_DB_USER'] ?? 'lab_app',
        'source_db_password' => $defaults['LAB_DB_PASSWORD'] ?? '',
        'proxy_runtime_priority' => 150,
        'admin_host' => '127.0.0.1',
        'admin_port' => (int) ($defaults['ADMIN_HTTP_PORT'] ?? '8081'),
        'lab_host' => '127.0.0.1',
        'lab_port' => (int) ($defaults['LAB_HTTP_PORT'] ?? '8082'),
        'admin_user' => $defaults['ADMIN_AUTH_STUB_USER'] ?? 'admin',
        'admin_password' => $defaults['ADMIN_AUTH_STUB_PASSWORD'] ?? '',
        'lab_user' => $defaults['LAB_AUTH_STUB_USER'] ?? 'lab-user',
        'lab_password' => $defaults['LAB_AUTH_STUB_PASSWORD'] ?? '',
        'config_db_host_port' => $defaults['CONFIG_DB_HOST_PORT'] ?? '33061',
        'lab_db_host_port' => $defaults['LAB_DB_HOST_PORT'] ?? '33062',
        'config_db_name' => $defaults['CONFIG_DB_NAME'] ?? 'config_app',
        'config_db_user' => $defaults['CONFIG_DB_USER'] ?? 'config_app',
        'config_db_password' => $defaults['CONFIG_DB_PASSWORD'] ?? '',
        'lab_db_name' => $defaults['LAB_DB_NAME'] ?? 'lab_app',
        'lab_db_user' => $defaults['LAB_DB_USER'] ?? 'lab_app',
        'lab_db_password' => $defaults['LAB_DB_PASSWORD'] ?? '',
        'http_timeout_seconds' => 10,
        'keep_source' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'table_name' => '',
                'source_key' => '',
                'source_label' => '',
                'source_description' => '',
                'source_host' => '',
                'source_port' => '',
                'source_db_name' => '',
                'source_db_user' => '',
                'source_db_password' => '',
                'proxy_runtime_priority' => 0,
                'admin_host' => '',
                'admin_port' => 0,
                'lab_host' => '',
                'lab_port' => 0,
                'admin_user' => '',
                'admin_password' => '',
                'lab_user' => '',
                'lab_password' => '',
                'config_db_host_port' => '',
                'lab_db_host_port' => '',
                'config_db_name' => '',
                'config_db_user' => '',
                'config_db_password' => '',
                'lab_db_name' => '',
                'lab_db_user' => '',
                'lab_db_password' => '',
                'http_timeout_seconds' => 0,
                'keep_source' => false,
                'error' => '',
            ];
        }

        if ($argument === '--keep-source') {
            $parsed['keep_source'] = true;
            continue;
        }

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return array_merge($parsed, [
                'ok' => false,
                'help' => false,
                'error' => '不明な引数です: ' . $argument,
            ]);
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        $name = trim($name);
        $value = (string) $value;

        switch ($name) {
            case 'project-key':
                $parsed['project_key'] = trim($value);
                break;
            case 'table-name':
                $parsed['table_name'] = trim($value);
                break;
            case 'source-key':
                $parsed['source_key'] = trim($value);
                break;
            case 'source-label':
                $parsed['source_label'] = trim($value);
                break;
            case 'source-description':
                $parsed['source_description'] = trim($value);
                break;
            case 'source-host':
                $parsed['source_host'] = trim($value);
                break;
            case 'source-port':
                $parsed['source_port'] = trim($value);
                break;
            case 'source-db-name':
                $parsed['source_db_name'] = trim($value);
                break;
            case 'source-db-user':
                $parsed['source_db_user'] = trim($value);
                break;
            case 'source-db-password':
                $parsed['source_db_password'] = $value;
                break;
            case 'proxy-runtime-priority':
                $parsed['proxy_runtime_priority'] = (int) $value;
                break;
            case 'admin-host':
                $parsed['admin_host'] = trim($value);
                break;
            case 'admin-port':
                $parsed['admin_port'] = (int) $value;
                break;
            case 'lab-host':
                $parsed['lab_host'] = trim($value);
                break;
            case 'lab-port':
                $parsed['lab_port'] = (int) $value;
                break;
            case 'admin-user':
                $parsed['admin_user'] = trim($value);
                break;
            case 'admin-password':
                $parsed['admin_password'] = $value;
                break;
            case 'lab-user':
                $parsed['lab_user'] = trim($value);
                break;
            case 'lab-password':
                $parsed['lab_password'] = $value;
                break;
            case 'config-db-host-port':
                $parsed['config_db_host_port'] = trim($value);
                break;
            case 'lab-db-host-port':
                $parsed['lab_db_host_port'] = trim($value);
                break;
            case 'config-db-name':
                $parsed['config_db_name'] = trim($value);
                break;
            case 'config-db-user':
                $parsed['config_db_user'] = trim($value);
                break;
            case 'config-db-password':
                $parsed['config_db_password'] = $value;
                break;
            case 'lab-db-name':
                $parsed['lab_db_name'] = trim($value);
                break;
            case 'lab-db-user':
                $parsed['lab_db_user'] = trim($value);
                break;
            case 'lab-db-password':
                $parsed['lab_db_password'] = $value;
                break;
            case 'http-timeout':
                $parsed['http_timeout_seconds'] = (int) $value;
                break;
            default:
                return array_merge($parsed, [
                    'ok' => false,
                    'help' => false,
                    'error' => '不明な引数です: --' . $name,
                ]);
        }
    }

    if ($parsed['source_key'] === '') {
        $parsed['source_key'] = 'ext_smoke_' . date('mdHis') . strtolower(substr(bin2hex(random_bytes(2)), 0, 4));
    }

    if ($parsed['project_key'] === '') {
        return array_merge($parsed, ['ok' => false, 'help' => false, 'error' => 'project key は必須です。']);
    }
    if ($parsed['table_name'] === '') {
        return array_merge($parsed, ['ok' => false, 'help' => false, 'error' => 'table name は必須です。']);
    }
    if (preg_match('/^[a-z][a-z0-9_]*$/', $parsed['source_key']) !== 1) {
        return array_merge($parsed, ['ok' => false, 'help' => false, 'error' => 'source key は lower_snake_case で指定してください。']);
    }
    foreach ([
        'source_label' => $parsed['source_label'],
        'source_host' => $parsed['source_host'],
        'source_port' => $parsed['source_port'],
        'source_db_name' => $parsed['source_db_name'],
        'source_db_user' => $parsed['source_db_user'],
        'admin_host' => $parsed['admin_host'],
        'lab_host' => $parsed['lab_host'],
        'admin_user' => $parsed['admin_user'],
        'admin_password' => $parsed['admin_password'],
        'lab_user' => $parsed['lab_user'],
        'lab_password' => $parsed['lab_password'],
        'config_db_host_port' => $parsed['config_db_host_port'],
        'lab_db_host_port' => $parsed['lab_db_host_port'],
        'config_db_name' => $parsed['config_db_name'],
        'config_db_user' => $parsed['config_db_user'],
        'lab_db_name' => $parsed['lab_db_name'],
        'lab_db_user' => $parsed['lab_db_user'],
    ] as $label => $value) {
        if (!is_string($value) || trim($value) === '') {
            return array_merge($parsed, ['ok' => false, 'help' => false, 'error' => $label . ' は必須です。']);
        }
    }
    foreach (['admin_port', 'lab_port', 'http_timeout_seconds', 'proxy_runtime_priority'] as $field) {
        if ((int) $parsed[$field] < 1) {
            return array_merge($parsed, ['ok' => false, 'help' => false, 'error' => $field . ' は 1 以上で指定してください。']);
        }
    }

    return array_merge($parsed, [
        'ok' => true,
        'help' => false,
        'error' => '',
    ]);
}

function app_cli_external_source_smoke_apply_host_runtime_env(array $parsed): void
{
    $overrides = [
        'APP_SITE' => 'admin',
        'APP_DB_HOST' => '127.0.0.1',
        'APP_DB_PORT' => $parsed['config_db_host_port'],
        'APP_DB_NAME' => $parsed['config_db_name'],
        'APP_DB_USER' => $parsed['config_db_user'],
        'APP_DB_PASSWORD' => $parsed['config_db_password'],
        'APP_CONFIG_DB_HOST' => '127.0.0.1',
        'APP_CONFIG_DB_PORT' => $parsed['config_db_host_port'],
        'APP_CONFIG_DB_NAME' => $parsed['config_db_name'],
        'APP_CONFIG_DB_USER' => $parsed['config_db_user'],
        'APP_CONFIG_DB_PASSWORD' => $parsed['config_db_password'],
        'APP_LAB_DB_HOST' => '127.0.0.1',
        'APP_LAB_DB_PORT' => $parsed['lab_db_host_port'],
        'APP_LAB_DB_NAME' => $parsed['lab_db_name'],
        'APP_LAB_DB_USER' => $parsed['lab_db_user'],
        'APP_LAB_DB_PASSWORD' => $parsed['lab_db_password'],
    ];

    foreach ($overrides as $key => $value) {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function app_cli_external_source_smoke_write_json(array $payload, bool $ok): void
{
    $stream = $ok ? STDOUT : STDERR;
    fwrite(
        $stream,
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
}

function app_cli_external_source_smoke_ensure(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * @return array{
 *     base_url:string,
 *     timeout_seconds:int,
 *     cookies:array<string,string>
 * }
 */
function app_cli_external_source_smoke_http_client(string $host, int $port, int $timeoutSeconds): array
{
    return [
        'base_url' => 'http://' . $host . ':' . $port,
        'timeout_seconds' => $timeoutSeconds,
        'cookies' => [],
    ];
}

/**
 * @param list<string> $headerLines
 * @return array<string,list<string>>
 */
function app_cli_external_source_smoke_header_map(array $headerLines): array
{
    $headers = [];
    foreach ($headerLines as $line) {
        if (!is_string($line) || !str_contains($line, ':')) {
            continue;
        }

        [$name, $value] = explode(':', $line, 2);
        $normalizedName = strtolower(trim($name));
        if ($normalizedName === '') {
            continue;
        }

        if (!array_key_exists($normalizedName, $headers)) {
            $headers[$normalizedName] = [];
        }

        $headers[$normalizedName][] = trim($value);
    }

    return $headers;
}

/**
 * @param list<string> $headerLines
 */
function app_cli_external_source_smoke_http_status(array $headerLines): int
{
    foreach ($headerLines as $line) {
        if (!is_string($line)) {
            continue;
        }

        if (preg_match('#^HTTP/\S+\s+(\d{3})\b#', $line, $matches) === 1) {
            return (int) ($matches[1] ?? 0);
        }
    }

    return 0;
}

/**
 * @param list<string> $headerLines
 * @param array<string,string> $cookies
 */
function app_cli_external_source_smoke_store_cookies(array &$cookies, array $headerLines): void
{
    foreach ($headerLines as $line) {
        if (!is_string($line) || stripos($line, 'Set-Cookie:') !== 0) {
            continue;
        }

        $cookieValue = trim(substr($line, strlen('Set-Cookie:')));
        if ($cookieValue === '') {
            continue;
        }

        $cookiePair = explode(';', $cookieValue, 2)[0] ?? '';
        if ($cookiePair === '' || !str_contains($cookiePair, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $cookiePair, 2);
        $name = trim($name);
        if ($name === '') {
            continue;
        }

        $cookies[$name] = trim($value);
    }
}

function app_cli_external_source_smoke_absolute_url(array $client, string $path): string
{
    if (preg_match('#\Ahttps?://#i', $path) === 1) {
        return $path;
    }

    return rtrim($client['base_url'], '/') . '/' . ltrim($path, '/');
}

/**
 * @param array{
 *     headers?:array<string,string>,
 *     form_params?:array<string,mixed>,
 *     body?:string
 * } $options
 * @return array{
 *     ok:bool,
 *     status:int,
 *     url:string,
 *     path:string,
 *     headers:array<string,list<string>>,
 *     header_lines:list<string>,
 *     body:string,
 *     location:string,
 *     error:string
 * }
 */
function app_cli_external_source_smoke_http_request_once(array &$client, string $method, string $path, array $options = []): array
{
    $url = app_cli_external_source_smoke_absolute_url($client, $path);
    $headerLines = [];

    foreach (($options['headers'] ?? []) as $name => $value) {
        if (!is_string($name) || !is_string($value) || trim($name) === '') {
            continue;
        }

        $headerLines[] = trim($name) . ': ' . $value;
    }

    if ($client['cookies'] !== []) {
        $cookiePairs = [];
        foreach ($client['cookies'] as $cookieName => $cookieValue) {
            $cookiePairs[] = $cookieName . '=' . $cookieValue;
        }
        $headerLines[] = 'Cookie: ' . implode('; ', $cookiePairs);
    }

    $body = $options['body'] ?? null;
    if ($body === null && array_key_exists('form_params', $options)) {
        $body = http_build_query(
            $options['form_params'],
            '',
            '&',
            PHP_QUERY_RFC3986,
        );

        $hasContentType = false;
        foreach ($headerLines as $headerLine) {
            if (stripos($headerLine, 'Content-Type:') === 0) {
                $hasContentType = true;
                break;
            }
        }
        if (!$hasContentType) {
            $headerLines[] = 'Content-Type: application/x-www-form-urlencoded';
        }
    }

    if (function_exists('curl_init')) {
        $curl = curl_init();
        if ($curl === false) {
            return [
                'ok' => false,
                'status' => 0,
                'url' => $url,
                'path' => $path,
                'headers' => [],
                'header_lines' => [],
                'body' => '',
                'location' => '',
                'error' => 'curl 初期化に失敗しました。',
            ];
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => $client['timeout_seconds'],
            CURLOPT_HTTPHEADER => $headerLines,
        ]);
        if ($body !== null && strtoupper($method) !== 'GET' && strtoupper($method) !== 'HEAD') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $rawResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!is_string($rawResponse)) {
            $rawResponse = '';
        }

        $headerText = substr($rawResponse, 0, $headerSize);
        $responseBody = substr($rawResponse, $headerSize);
        if (!is_string($headerText)) {
            $headerText = '';
        }
        if (!is_string($responseBody)) {
            $responseBody = '';
        }

        $headerBlocks = preg_split("/\r\n\r\n|\n\n|\r\r/", trim($headerText));
        if (!is_array($headerBlocks) || $headerBlocks === []) {
            $headerBlocks = [$headerText];
        }

        $allHeaderLines = [];
        foreach ($headerBlocks as $headerBlock) {
            foreach (preg_split("/\r\n|\n|\r/", (string) $headerBlock) ?: [] as $headerLine) {
                if (is_string($headerLine) && trim($headerLine) !== '') {
                    $allHeaderLines[] = $headerLine;
                }
            }
        }

        $finalHeaderLines = [];
        $finalHeaderBlock = (string) end($headerBlocks);
        foreach (preg_split("/\r\n|\n|\r/", $finalHeaderBlock) ?: [] as $headerLine) {
            if (is_string($headerLine) && trim($headerLine) !== '') {
                $finalHeaderLines[] = $headerLine;
            }
        }

        app_cli_external_source_smoke_store_cookies($client['cookies'], $allHeaderLines);
        $headers = app_cli_external_source_smoke_header_map($finalHeaderLines);
        $location = (string) (($headers['location'][0] ?? ''));

        if ($curlError !== '') {
            return [
                'ok' => false,
                'status' => $status,
                'url' => $url,
                'path' => $path,
                'headers' => $headers,
                'header_lines' => $finalHeaderLines,
                'body' => $responseBody,
                'location' => $location,
                'error' => $curlError,
            ];
        }

        return [
            'ok' => true,
            'status' => $status,
            'url' => $url,
            'path' => $path,
            'headers' => $headers,
            'header_lines' => $finalHeaderLines,
            'body' => $responseBody,
            'location' => $location,
            'error' => '',
        ];
    }

    $streamError = '';
    $context = stream_context_create([
        'http' => [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headerLines),
            'content' => $body ?? '',
            'timeout' => $client['timeout_seconds'],
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
        ],
    ]);

    set_error_handler(static function (int $severity, string $message) use (&$streamError): bool {
        $streamError = $message;
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

    $responseHeaderLines = [];
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $responseHeaderLine) {
            if (is_string($responseHeaderLine) && trim($responseHeaderLine) !== '') {
                $responseHeaderLines[] = $responseHeaderLine;
            }
        }
    }

    app_cli_external_source_smoke_store_cookies($client['cookies'], $responseHeaderLines);
    $headers = app_cli_external_source_smoke_header_map($responseHeaderLines);
    $location = (string) (($headers['location'][0] ?? ''));
    $status = app_cli_external_source_smoke_http_status($responseHeaderLines);

    if ($streamError !== '' && $status === 0) {
        return [
            'ok' => false,
            'status' => 0,
            'url' => $url,
            'path' => $path,
            'headers' => $headers,
            'header_lines' => $responseHeaderLines,
            'body' => $responseBody,
            'location' => $location,
            'error' => $streamError,
        ];
    }

    return [
        'ok' => true,
        'status' => $status,
        'url' => $url,
        'path' => $path,
        'headers' => $headers,
        'header_lines' => $responseHeaderLines,
        'body' => $responseBody,
        'location' => $location,
        'error' => '',
    ];
}

/**
 * @param array{
 *     headers?:array<string,string>,
 *     form_params?:array<string,mixed>,
 *     body?:string,
 *     follow_redirects?:bool,
 *     max_redirects?:int
 * } $options
 * @return array{
 *     ok:bool,
 *     status:int,
 *     url:string,
 *     path:string,
 *     final_path:string,
 *     headers:array<string,list<string>>,
 *     header_lines:list<string>,
 *     body:string,
 *     location:string,
 *     redirects:list<array{
 *         from_path:string,
 *         status:int,
 *         location:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_external_source_smoke_http_request(array &$client, string $method, string $path, array $options = []): array
{
    $followRedirects = (bool) ($options['follow_redirects'] ?? false);
    $maxRedirects = max(0, (int) ($options['max_redirects'] ?? 10));

    $currentMethod = strtoupper($method);
    $currentPath = $path;
    $currentOptions = $options;
    unset($currentOptions['follow_redirects'], $currentOptions['max_redirects']);

    $redirects = [];
    while (true) {
        $response = app_cli_external_source_smoke_http_request_once($client, $currentMethod, $currentPath, $currentOptions);
        if (!$response['ok']) {
            return [
                'ok' => false,
                'status' => $response['status'],
                'url' => $response['url'],
                'path' => $currentPath,
                'final_path' => $currentPath,
                'headers' => $response['headers'],
                'header_lines' => $response['header_lines'],
                'body' => $response['body'],
                'location' => $response['location'],
                'redirects' => $redirects,
                'error' => $response['error'],
            ];
        }

        if (
            !$followRedirects
            || !in_array($response['status'], [301, 302, 303, 307, 308], true)
            || trim($response['location']) === ''
        ) {
            return [
                'ok' => true,
                'status' => $response['status'],
                'url' => $response['url'],
                'path' => $currentPath,
                'final_path' => $currentPath,
                'headers' => $response['headers'],
                'header_lines' => $response['header_lines'],
                'body' => $response['body'],
                'location' => $response['location'],
                'redirects' => $redirects,
                'error' => '',
            ];
        }

        if (count($redirects) >= $maxRedirects) {
            return [
                'ok' => false,
                'status' => $response['status'],
                'url' => $response['url'],
                'path' => $currentPath,
                'final_path' => $currentPath,
                'headers' => $response['headers'],
                'header_lines' => $response['header_lines'],
                'body' => $response['body'],
                'location' => $response['location'],
                'redirects' => $redirects,
                'error' => 'redirect が多すぎます。',
            ];
        }

        $redirects[] = [
            'from_path' => $currentPath,
            'status' => $response['status'],
            'location' => $response['location'],
        ];

        $location = $response['location'];
        if (preg_match('#\Ahttps?://#i', $location) === 1) {
            $redirectPath = (string) parse_url($location, PHP_URL_PATH);
            $redirectQuery = (string) parse_url($location, PHP_URL_QUERY);
            $currentPath = $redirectPath . ($redirectQuery !== '' ? '?' . $redirectQuery : '');
        } else {
            $currentPath = $location;
        }

        if (!str_starts_with($currentPath, '/')) {
            $currentPath = '/' . ltrim($currentPath, '/');
        }

        if (in_array($response['status'], [301, 302, 303], true) && !in_array($currentMethod, ['GET', 'HEAD'], true)) {
            $currentMethod = 'GET';
            $currentOptions = [];
        } elseif (in_array($response['status'], [307, 308], true)) {
            $currentMethod = strtoupper($method);
        } else {
            $currentMethod = 'GET';
            $currentOptions = [];
        }
    }
}

function app_cli_external_source_smoke_extract_input_value(string $html, string $name): string
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

function app_cli_external_source_smoke_query_value(string $path, string $name): string
{
    $query = (string) parse_url('http://local' . $path, PHP_URL_QUERY);
    if ($query === '') {
        return '';
    }

    parse_str($query, $queryParams);
    $value = $queryParams[$name] ?? null;
    if (is_array($value)) {
        return '';
    }

    return is_string($value) || is_numeric($value) ? trim((string) $value) : '';
}

function app_cli_external_source_smoke_assert_response_ok(array $response, string $label, int $expectedStatus = 200): void
{
    app_cli_external_source_smoke_ensure($response['ok'], $label . ' に失敗しました: ' . $response['error']);
    app_cli_external_source_smoke_ensure(
        $response['status'] === $expectedStatus,
        $label . ' の HTTP status が ' . $expectedStatus . ' ではありません: ' . $response['status'],
    );
}

/**
 * @return array{id:int,source_key:string,label:string,description:string}|null
 */
function app_cli_external_source_smoke_find_source(array $catalog, string $sourceKey): ?array
{
    foreach ($catalog as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (trim((string) ($item['source_key'] ?? '')) !== $sourceKey) {
            continue;
        }

        return [
            'id' => (int) ($item['id'] ?? 0),
            'source_key' => (string) ($item['source_key'] ?? ''),
            'label' => (string) ($item['label'] ?? ''),
            'description' => (string) ($item['description'] ?? ''),
        ];
    }

    return null;
}

/**
 * @param array<string,mixed> $sourceOutput
 * @return array{artifact_key:string,published_root:string}
 */
function app_cli_external_source_smoke_publish_source_output(
    array $app,
    string $projectKey,
    array $sourceOutput,
    string $requestedBy,
): array {
    $artifactResult = app_project_output_create_from_definition(
        $app,
        $projectKey,
        $sourceOutput,
        $requestedBy,
    );
    app_cli_external_source_smoke_ensure(
        (bool) $artifactResult['ok'],
        'source output artifact 作成に失敗しました: ' . (string) ($artifactResult['error'] ?? ''),
    );
    app_cli_external_source_smoke_ensure(
        is_array($artifactResult['artifact'] ?? null),
        'source output artifact が返りませんでした。',
    );

    $publishResult = app_project_output_publish_artifact(
        $app,
        $artifactResult['artifact'],
        $sourceOutput,
    );
    app_cli_external_source_smoke_ensure(
        (bool) $publishResult['ok'],
        'source output publish に失敗しました: ' . (string) ($publishResult['error'] ?? ''),
    );
    app_cli_external_source_smoke_ensure(
        is_array($publishResult['published'] ?? null),
        'source output published 情報が返りませんでした。',
    );

    return [
        'artifact_key' => (string) ($artifactResult['artifact']['artifact_key'] ?? ''),
        'published_root' => (string) ($publishResult['published']['published_root'] ?? ''),
    ];
}

$defaults = app_cli_external_source_smoke_env_defaults();
$parsed = app_cli_external_source_smoke_parse_args($argv, $defaults);
if (!$parsed['ok']) {
    app_cli_external_source_smoke_write_json([
        'ok' => false,
        'error' => $parsed['error'],
        'usage' => app_cli_external_source_smoke_usage(),
    ], false);
    exit(1);
}

if ($parsed['help']) {
    fwrite(STDOUT, app_cli_external_source_smoke_usage() . PHP_EOL);
    exit(0);
}

app_cli_external_source_smoke_apply_host_runtime_env($parsed);

$checks = [];
$sourceId = 0;
$cleanup = [
    'source_deleted' => false,
    'source_kept' => false,
];
$resultPayload = [];
$exitCode = 0;

try {
    $adminClient = app_cli_external_source_smoke_http_client(
        $parsed['admin_host'],
        $parsed['admin_port'],
        $parsed['http_timeout_seconds'],
    );
    $labClient = app_cli_external_source_smoke_http_client(
        $parsed['lab_host'],
        $parsed['lab_port'],
        $parsed['http_timeout_seconds'],
    );

    $adminHealth = app_cli_external_source_smoke_http_request($adminClient, 'GET', '/health');
    app_cli_external_source_smoke_assert_response_ok($adminHealth, 'admin health');
    $checks[] = [
        'name' => 'admin_health',
        'status' => $adminHealth['status'],
    ];

    $labHealth = app_cli_external_source_smoke_http_request($labClient, 'GET', '/health');
    app_cli_external_source_smoke_assert_response_ok($labHealth, 'lab health');
    $checks[] = [
        'name' => 'lab_health',
        'status' => $labHealth['status'],
    ];

    $adminRedirectPath = '/settings/database-sources';
    $adminLoginPage = app_cli_external_source_smoke_http_request(
        $adminClient,
        'GET',
        '/login?redirect=' . rawurlencode($adminRedirectPath),
        ['follow_redirects' => false],
    );
    app_cli_external_source_smoke_assert_response_ok($adminLoginPage, 'admin login page');
    $adminCsrf = app_cli_external_source_smoke_extract_input_value($adminLoginPage['body'], '_csrf');
    app_cli_external_source_smoke_ensure($adminCsrf !== '', 'admin login form の CSRF token を取得できませんでした。');

    $adminLoginSubmit = app_cli_external_source_smoke_http_request(
        $adminClient,
        'POST',
        '/login',
        [
            'follow_redirects' => true,
            'form_params' => [
                '_csrf' => $adminCsrf,
                'redirect' => $adminRedirectPath,
                'username' => $parsed['admin_user'],
                'password' => $parsed['admin_password'],
            ],
        ],
    );
    app_cli_external_source_smoke_assert_response_ok($adminLoginSubmit, 'admin login submit');
    app_cli_external_source_smoke_ensure(
        $adminLoginSubmit['final_path'] === $adminRedirectPath,
        'admin login 後の landing path が想定外です: ' . $adminLoginSubmit['final_path'],
    );
    app_cli_external_source_smoke_ensure(
        str_contains($adminLoginSubmit['body'], 'Database Sources'),
        'admin login 後の landing page が database sources page ではありません。',
    );
    $checks[] = [
        'name' => 'admin_login',
        'final_path' => $adminLoginSubmit['final_path'],
        'redirect_count' => count($adminLoginSubmit['redirects']),
    ];

    $createCsrf = app_cli_external_source_smoke_extract_input_value($adminLoginSubmit['body'], '_csrf');
    app_cli_external_source_smoke_ensure($createCsrf !== '', 'database sources page の CSRF token を取得できませんでした。');

    $createSubmit = app_cli_external_source_smoke_http_request(
        $adminClient,
        'POST',
        '/settings/database-sources',
        [
            'follow_redirects' => true,
            'form_params' => [
                '_csrf' => $createCsrf,
                'source_id' => '',
                'source_of_truth' => 'manual',
                'source_key' => $parsed['source_key'],
                'label' => $parsed['source_label'],
                'description' => $parsed['source_description'],
                'host' => $parsed['source_host'],
                'port' => $parsed['source_port'],
                'database_name' => $parsed['source_db_name'],
                'user_name' => $parsed['source_db_user'],
                'password' => $parsed['source_db_password'],
                'proxy_runtime_priority' => (string) $parsed['proxy_runtime_priority'],
                'supports_live_schema_import' => '1',
                'supports_proxy_runtime_read' => '1',
                'source_action' => 'create',
            ],
        ],
    );
    app_cli_external_source_smoke_assert_response_ok($createSubmit, 'database source create');
    app_cli_external_source_smoke_ensure(
        str_contains($createSubmit['body'], 'database source を作成しました。'),
        'database source create 後の success notice が見つかりません。',
    );
    app_cli_external_source_smoke_ensure(
        str_contains($createSubmit['body'], $parsed['source_key']),
        '作成した source key が database sources page に見つかりません。',
    );

    $sourceId = (int) app_cli_external_source_smoke_query_value($createSubmit['final_path'], 'source_id');
    if ($sourceId < 1) {
        $app = app_bootstrap();
        $catalogResult = app_fetch_database_sources($app);
        app_cli_external_source_smoke_ensure((bool) $catalogResult['ok'], 'database source catalog の取得に失敗しました。');
        $catalogItem = app_cli_external_source_smoke_find_source($catalogResult['items'] ?? [], $parsed['source_key']);
        app_cli_external_source_smoke_ensure($catalogItem !== null, '作成した database source を catalog から見つけられませんでした。');
        $sourceId = $catalogItem['id'];
    }

    $checks[] = [
        'name' => 'create_external_source',
        'source_id' => $sourceId,
        'final_path' => $createSubmit['final_path'],
    ];

    $app = app_bootstrap();
    $catalogResult = app_fetch_database_sources($app);
    app_cli_external_source_smoke_ensure((bool) $catalogResult['ok'], 'host-side app から database source catalog を取得できませんでした。');
    $catalogItem = app_cli_external_source_smoke_find_source($catalogResult['items'] ?? [], $parsed['source_key']);
    app_cli_external_source_smoke_ensure($catalogItem !== null, 'host-side app catalog に external source が merge されていません。');

    $sourceOptionKey = app_project_table_import_named_live_source_option_key($parsed['source_key']);
    $sourceOptions = app_project_table_import_source_options($parsed['project_key'], $app);
    $sourceOptionFound = false;
    foreach ($sourceOptions as $sourceOption) {
        if (($sourceOption['key'] ?? '') === $sourceOptionKey) {
            $sourceOptionFound = true;
            break;
        }
    }
    app_cli_external_source_smoke_ensure($sourceOptionFound, 'named-live-schema option が host-side source option catalog に現れません。');
    $checks[] = [
        'name' => 'host_catalog_merge',
        'source_option_key' => $sourceOptionKey,
        'source_id' => $sourceId,
    ];

    $importPath = '/projects/' . rawurlencode($parsed['project_key'])
        . '/tables/import?source=' . rawurlencode($sourceOptionKey)
        . '&table=' . rawurlencode($parsed['table_name']);
    $importPreview = app_cli_external_source_smoke_http_request(
        $adminClient,
        'GET',
        $importPath,
        ['follow_redirects' => true],
    );
    app_cli_external_source_smoke_assert_response_ok($importPreview, 'table import preview');
    app_cli_external_source_smoke_ensure(
        str_contains($importPreview['body'], 'named live schema / ' . $parsed['source_label']),
        'table import preview に external source label が見つかりません。',
    );
    app_cli_external_source_smoke_ensure(
        str_contains($importPreview['body'], $parsed['table_name']),
        'table import preview に target table が見つかりません。',
    );
    $importCsrf = app_cli_external_source_smoke_extract_input_value($importPreview['body'], '_csrf');
    app_cli_external_source_smoke_ensure($importCsrf !== '', 'table import preview の CSRF token を取得できませんでした。');
    $checks[] = [
        'name' => 'table_import_preview',
        'final_path' => $importPreview['final_path'],
    ];

    $importApply = app_cli_external_source_smoke_http_request(
        $adminClient,
        'POST',
        $importPath,
        [
            'follow_redirects' => true,
            'form_params' => [
                '_csrf' => $importCsrf,
                'source_key' => $sourceOptionKey,
                'table_name' => $parsed['table_name'],
            ],
        ],
    );
    app_cli_external_source_smoke_assert_response_ok($importApply, 'table import apply');
    app_cli_external_source_smoke_ensure(
        str_contains($importApply['body'], 'Last Import Result'),
        'table import apply 後の result section が見つかりません。',
    );
    $checks[] = [
        'name' => 'table_import_apply',
        'final_path' => $importApply['final_path'],
    ];

    $dataClassSync = app_project_data_class_sync_apply($app, $parsed['project_key']);
    app_cli_external_source_smoke_ensure(
        (bool) $dataClassSync['ok'],
        'data class sync に失敗しました: ' . (string) ($dataClassSync['error'] ?? ''),
    );
    $checks[] = [
        'name' => 'data_class_sync',
        'summary' => $dataClassSync['summary'] ?? [],
    ];

    $dbAccessSync = app_project_db_access_sync_from_generated_catalog($app, $parsed['project_key']);
    app_cli_external_source_smoke_ensure(
        (bool) $dbAccessSync['ok'],
        'db access sync に失敗しました: ' . (string) ($dbAccessSync['error'] ?? ''),
    );
    $checks[] = [
        'name' => 'db_access_sync',
        'summary' => $dbAccessSync['summary'] ?? [],
    ];

    $openApiSourceOutput = app_fetch_project_source_output_item($app, $parsed['project_key'], 'OPENAPI-JSON');
    app_cli_external_source_smoke_ensure(
        (bool) $openApiSourceOutput['ok'] && is_array($openApiSourceOutput['item'] ?? null),
        'OPENAPI-JSON source output definition を取得できませんでした。',
    );
    $proxySourceOutput = app_fetch_project_source_output_item($app, $parsed['project_key'], 'DBTABLE-PROXY-SERVER');
    app_cli_external_source_smoke_ensure(
        (bool) $proxySourceOutput['ok'] && is_array($proxySourceOutput['item'] ?? null),
        'DBTABLE-PROXY-SERVER source output definition を取得できませんでした。',
    );

    $requestedBy = 'check_external_database_source_lab_swagger_flow.php';
    $publishedOpenApi = app_cli_external_source_smoke_publish_source_output(
        $app,
        $parsed['project_key'],
        $openApiSourceOutput['item'],
        $requestedBy,
    );
    $publishedProxy = app_cli_external_source_smoke_publish_source_output(
        $app,
        $parsed['project_key'],
        $proxySourceOutput['item'],
        $requestedBy,
    );

    $openApiPath = rtrim($publishedOpenApi['published_root'], '/') . '/openapi.json';
    app_cli_external_source_smoke_ensure(is_file($openApiPath), 'published openapi.json が見つかりません。');
    $openApiJson = file_get_contents($openApiPath);
    app_cli_external_source_smoke_ensure(is_string($openApiJson) && $openApiJson !== '', 'published openapi.json の読み込みに失敗しました。');
    $openApiDecoded = json_decode($openApiJson, true);
    app_cli_external_source_smoke_ensure(is_array($openApiDecoded), 'published openapi.json の JSON decode に失敗しました。');
    app_cli_external_source_smoke_ensure(
        array_key_exists('/proxyserver-lab_experiments-Getlab_experimentsList.php', $openApiDecoded['paths'] ?? []),
        'published openapi.json に lab_experiments list path がありません。',
    );
    $checks[] = [
        'name' => 'publish_openapi',
        'artifact_key' => $publishedOpenApi['artifact_key'],
        'published_root' => $publishedOpenApi['published_root'],
    ];

    $buildPlanPath = rtrim($publishedProxy['published_root'], '/') . '/build-plan.json';
    app_cli_external_source_smoke_ensure(is_file($buildPlanPath), 'published proxy build-plan.json が見つかりません。');
    $buildPlanJson = file_get_contents($buildPlanPath);
    app_cli_external_source_smoke_ensure(is_string($buildPlanJson) && $buildPlanJson !== '', 'published proxy build-plan の読み込みに失敗しました。');
    $buildPlanDecoded = json_decode($buildPlanJson, true);
    app_cli_external_source_smoke_ensure(is_array($buildPlanDecoded), 'published proxy build-plan の JSON decode に失敗しました。');
    $buildPlanItemFound = false;
    foreach (($buildPlanDecoded['items'] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (
            ($item['source_name'] ?? '') === 'lab_experiments'
            && ($item['function_name'] ?? '') === 'Getlab_experimentsList'
        ) {
            $buildPlanItemFound = true;
            break;
        }
    }
    app_cli_external_source_smoke_ensure($buildPlanItemFound, 'published proxy build-plan に lab_experiments list endpoint がありません。');
    $proxyEntrypointPath = rtrim($publishedProxy['published_root'], '/') . '/proxyserver-lab_experiments-Getlab_experimentsList.php';
    app_cli_external_source_smoke_ensure(is_file($proxyEntrypointPath), 'published proxy entrypoint file が見つかりません。');
    $checks[] = [
        'name' => 'publish_proxy',
        'artifact_key' => $publishedProxy['artifact_key'],
        'published_root' => $publishedProxy['published_root'],
        'entrypoint' => $proxyEntrypointPath,
    ];

    $swaggerPath = '/runs/swagger/' . rawurlencode($parsed['project_key'])
        . '?source_output_key=OPENAPI-JSON'
        . '&db_source_key=' . rawurlencode($parsed['source_key']);
    $labLoginPage = app_cli_external_source_smoke_http_request(
        $labClient,
        'GET',
        '/login?redirect=' . rawurlencode($swaggerPath),
        ['follow_redirects' => false],
    );
    app_cli_external_source_smoke_assert_response_ok($labLoginPage, 'lab login page');
    $labCsrf = app_cli_external_source_smoke_extract_input_value($labLoginPage['body'], '_csrf');
    app_cli_external_source_smoke_ensure($labCsrf !== '', 'lab login form の CSRF token を取得できませんでした。');

    $labLoginSubmit = app_cli_external_source_smoke_http_request(
        $labClient,
        'POST',
        '/login',
        [
            'follow_redirects' => true,
            'form_params' => [
                '_csrf' => $labCsrf,
                'redirect' => $swaggerPath,
                'username' => $parsed['lab_user'],
                'password' => $parsed['lab_password'],
            ],
        ],
    );
    app_cli_external_source_smoke_assert_response_ok($labLoginSubmit, 'lab login submit');
    app_cli_external_source_smoke_ensure(
        $labLoginSubmit['final_path'] === $swaggerPath,
        'lab login 後の landing path が想定外です: ' . $labLoginSubmit['final_path'],
    );
    app_cli_external_source_smoke_ensure(
        str_contains($labLoginSubmit['body'], 'Try It Out'),
        'lab swagger page に Try It Out が見つかりません。',
    );
    app_cli_external_source_smoke_ensure(
        str_contains($labLoginSubmit['body'], 'lab_experiments.Getlab_experimentsList'),
        'lab swagger page に lab_experiments.Getlab_experimentsList が見つかりません。',
    );
    app_cli_external_source_smoke_ensure(
        str_contains($labLoginSubmit['body'], 'name="db_source_key"'),
        'lab swagger page に db_source_key selector が見つかりません。',
    );
    app_cli_external_source_smoke_ensure(
        str_contains($labLoginSubmit['body'], 'value="' . $parsed['source_key'] . '" selected'),
        'lab swagger page で external db_source_key が選択状態になっていません。',
    );
    $checks[] = [
        'name' => 'lab_login_and_swagger',
        'final_path' => $labLoginSubmit['final_path'],
        'redirect_count' => count($labLoginSubmit['redirects']),
    ];

    $proxyPath = '/runs/proxy/' . rawurlencode($parsed['project_key'])
        . '/DBTABLE-PROXY-SERVER/proxyserver-lab_experiments-Getlab_experimentsList.php'
        . '?db_source_key=' . rawurlencode($parsed['source_key']);
    $proxyResponse = app_cli_external_source_smoke_http_request(
        $labClient,
        'GET',
        $proxyPath,
        [
            'follow_redirects' => true,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ],
    );
    app_cli_external_source_smoke_assert_response_ok($proxyResponse, 'lab proxy route');
    $proxyDecoded = json_decode($proxyResponse['body'], true);
    app_cli_external_source_smoke_ensure(is_array($proxyDecoded), 'lab proxy route response の JSON decode に失敗しました。');
    app_cli_external_source_smoke_ensure(
        ($proxyDecoded['_status'] ?? '') === 'OK',
        'lab proxy route response の status が OK ではありません。',
    );
    $resultRows = $proxyDecoded['Result'] ?? null;
    app_cli_external_source_smoke_ensure(is_array($resultRows), 'lab proxy route response に Result array がありません。');
    app_cli_external_source_smoke_ensure(count($resultRows) >= 2, 'lab proxy route response の row 数が不足しています。');
    $resultNames = array_values(array_filter(array_map(
        static fn (array $row): string => trim((string) ($row['name'] ?? '')),
        array_values(array_filter($resultRows, 'is_array')),
    )));
    app_cli_external_source_smoke_ensure(
        in_array('Bootstrap Health Check', $resultNames, true),
        'lab proxy route response に期待した row name が見つかりません。',
    );
    $checks[] = [
        'name' => 'lab_proxy_route',
        'path' => $proxyPath,
        'row_count' => count($resultRows),
        'row_names' => $resultNames,
    ];

    $resultPayload = [
        'ok' => true,
        'project_key' => $parsed['project_key'],
        'table_name' => $parsed['table_name'],
        'source_key' => $parsed['source_key'],
        'source_id' => $sourceId,
        'checks' => $checks,
    ];
} catch (Throwable $throwable) {
    $resultPayload = [
        'ok' => false,
        'error' => $throwable->getMessage(),
        'project_key' => $parsed['project_key'],
        'table_name' => $parsed['table_name'],
        'source_key' => $parsed['source_key'],
        'source_id' => $sourceId,
        'checks' => $checks,
    ];
    $exitCode = 1;
} finally {
    if ($sourceId > 0) {
        if ($parsed['keep_source']) {
            $cleanup['source_kept'] = true;
        } else {
            try {
                $app = app_bootstrap();
                $deleteResult = app_delete_database_source($app, $sourceId);
                $cleanup['source_deleted'] = (bool) ($deleteResult['ok'] ?? false);
            } catch (Throwable) {
                $cleanup['source_deleted'] = false;
            }
        }
    }
}

if ($resultPayload === []) {
    $resultPayload = [
        'ok' => false,
        'error' => '結果 payload を組み立てられませんでした。',
        'project_key' => $parsed['project_key'],
        'table_name' => $parsed['table_name'],
        'source_key' => $parsed['source_key'],
        'source_id' => $sourceId,
        'checks' => $checks,
    ];
    $exitCode = 1;
}

$resultPayload['cleanup'] = $cleanup;
app_cli_external_source_smoke_write_json($resultPayload, $exitCode === 0);
exit($exitCode);
