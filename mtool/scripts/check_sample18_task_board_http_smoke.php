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

function json_response(array $response): array
{
    $decoded = json_decode((string) ($response['body'] ?? ''), true);
    ensure(is_array($decoded), 'response body is not JSON:' . response_excerpt($response));

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

    $generatedSubmitPath = '/samples/sample18-task-board/no-code/generated-submit';

    $generatedGet = request_once($client, 'GET', $generatedSubmitPath);
    ensure($generatedGet['status'] === 405, 'generated submit GET status is not 405: ' . $generatedGet['status'] . response_excerpt($generatedGet));
    $generatedGetJson = json_response($generatedGet);
    ensure(($generatedGetJson['failure_code'] ?? '') === 'method_not_allowed', 'generated submit GET failure_code mismatch');
    ensure(($generatedGetJson['mutation_enabled'] ?? true) === false, 'generated submit GET mutation flag was enabled');

    $generatedBlocked = request_once($client, 'POST', $generatedSubmitPath, [
        'form_params' => [
            '_csrf_token' => $pageCsrf,
            'operation_key' => 'create_task_card',
            'title' => 'Generated blocked smoke',
            'body' => 'Generated submit is intentionally blocked.',
            'assigned_to' => 'Smoke',
            'priority' => '12',
            'due_date' => date('Y-m-d'),
            'client_only' => 'ignored',
        ],
    ]);
    ensure($generatedBlocked['status'] === 409, 'generated submit blocked status is not 409: ' . $generatedBlocked['status'] . response_excerpt($generatedBlocked));
    $generatedBlockedJson = json_response($generatedBlocked);
    ensure(($generatedBlockedJson['result'] ?? '') === 'blocked', 'generated submit blocked result mismatch');
    ensure(($generatedBlockedJson['failure_code'] ?? '') === 'generated_submit_disabled', 'generated submit blocked failure_code mismatch');
    ensure(($generatedBlockedJson['operation_key'] ?? '') === 'create_task_card', 'generated submit blocked operation_key mismatch');
    ensure(($generatedBlockedJson['curated_route_action'] ?? '') === 'create', 'generated submit blocked curated action mismatch');
    ensure(($generatedBlockedJson['db_access_function'] ?? '') === 'InsertTaskCard', 'generated submit blocked DBAccess function mismatch');
    ensure(($generatedBlockedJson['dispatcher_result']['dispatch_state'] ?? '') === 'dry_run', 'generated submit dispatcher dry-run state mismatch');
    ensure(($generatedBlockedJson['dispatcher_result']['executed'] ?? true) === false, 'generated submit dispatcher unexpectedly executed');
    ensure(($generatedBlockedJson['dispatcher_result']['mutation_enabled'] ?? true) === false, 'generated submit dispatcher mutation flag was enabled');
    ensure(($generatedBlockedJson['dispatcher_result']['bound_fields']['Title'] ?? '') === 'Generated blocked smoke', 'generated submit dispatcher bound title mismatch');
    ensure(str_starts_with((string) ($generatedBlockedJson['dedupe_key_preview'] ?? ''), 'sample18.generated_submit.create_task_card.'), 'generated submit dedupe key preview mismatch');
    ensure(strlen((string) ($generatedBlockedJson['payload_fingerprint'] ?? '')) === 64, 'generated submit payload fingerprint mismatch');
    ensure(($generatedBlockedJson['audit_event_preview']['event_type'] ?? '') === 'sample18.generated_submit.requested', 'generated submit audit event type mismatch');
    ensure(($generatedBlockedJson['audit_event_preview']['result'] ?? '') === 'blocked', 'generated submit audit result mismatch');
    ensure(($generatedBlockedJson['audit_event_preview']['metadata']['dedupe_key'] ?? '') === ($generatedBlockedJson['dedupe_key_preview'] ?? ''), 'generated submit audit dedupe key mismatch');
    ensure(($generatedBlockedJson['mutation_enabled'] ?? true) === false, 'generated submit blocked mutation flag was enabled');
    ensure(
        in_array('client_only', $generatedBlockedJson['ignored_input_fields'] ?? [], true),
        'generated submit blocked ignored_input_fields did not include client_only',
    );

    $generatedMissingCsrf = request_once($client, 'POST', $generatedSubmitPath, [
        'form_params' => [
            'operation_key' => 'create_task_card',
            'title' => 'Generated missing CSRF smoke',
        ],
    ]);
    ensure($generatedMissingCsrf['status'] === 403, 'generated submit missing CSRF status is not 403: ' . $generatedMissingCsrf['status'] . response_excerpt($generatedMissingCsrf));
    $generatedMissingCsrfJson = json_response($generatedMissingCsrf);
    ensure(($generatedMissingCsrfJson['failure_code'] ?? '') === 'missing_csrf', 'generated submit missing CSRF failure_code mismatch');
    ensure(($generatedMissingCsrfJson['errors'] ?? []) === ['csrf.missing'], 'generated submit missing CSRF errors mismatch');
    ensure(($generatedMissingCsrfJson['mutation_enabled'] ?? true) === false, 'generated submit missing CSRF mutation flag was enabled');

    $generatedInvalidCsrf = request_once($client, 'POST', $generatedSubmitPath, [
        'form_params' => [
            '_csrf_token' => 'wrong-token',
            'operation_key' => 'create_task_card',
            'title' => 'Generated invalid CSRF smoke',
        ],
    ]);
    ensure($generatedInvalidCsrf['status'] === 403, 'generated submit invalid CSRF status is not 403: ' . $generatedInvalidCsrf['status'] . response_excerpt($generatedInvalidCsrf));
    $generatedInvalidCsrfJson = json_response($generatedInvalidCsrf);
    ensure(($generatedInvalidCsrfJson['failure_code'] ?? '') === 'invalid_csrf', 'generated submit invalid CSRF failure_code mismatch');
    ensure(($generatedInvalidCsrfJson['errors'] ?? []) === ['csrf.invalid'], 'generated submit invalid CSRF errors mismatch');
    ensure(($generatedInvalidCsrfJson['mutation_enabled'] ?? true) === false, 'generated submit invalid CSRF mutation flag was enabled');

    $generatedInvalid = request_once($client, 'POST', $generatedSubmitPath, [
        'form_params' => [
            '_csrf_token' => $pageCsrf,
            'operation_key' => 'update_task_card',
            'id' => '0',
            'title' => '',
        ],
    ]);
    ensure($generatedInvalid['status'] === 422, 'generated submit invalid status is not 422: ' . $generatedInvalid['status'] . response_excerpt($generatedInvalid));
    $generatedInvalidJson = json_response($generatedInvalid);
    ensure(($generatedInvalidJson['failure_code'] ?? '') === 'validation_error', 'generated submit invalid failure_code mismatch');
    ensure(($generatedInvalidJson['errors'] ?? []) === ['id.invalid', 'title.required'], 'generated submit invalid errors mismatch');
    ensure(($generatedInvalidJson['mutation_enabled'] ?? true) === false, 'generated submit invalid mutation flag was enabled');

    $generatedUnknown = request_once($client, 'POST', $generatedSubmitPath, [
        'form_params' => [
            '_csrf_token' => $pageCsrf,
            'operation_key' => 'delete_task_card',
            'id' => $taskId,
        ],
    ]);
    ensure($generatedUnknown['status'] === 404, 'generated submit unknown status is not 404: ' . $generatedUnknown['status'] . response_excerpt($generatedUnknown));
    $generatedUnknownJson = json_response($generatedUnknown);
    ensure(($generatedUnknownJson['failure_code'] ?? '') === 'unknown_operation', 'generated submit unknown failure_code mismatch');
    ensure(($generatedUnknownJson['errors'] ?? []) === ['operation.unknown'], 'generated submit unknown errors mismatch');
    ensure(($generatedUnknownJson['mutation_enabled'] ?? true) === false, 'generated submit unknown mutation flag was enabled');

    return [
        'ok' => true,
        'lab_base_url' => $args['lab_base_url'],
        'page_path' => '/samples/sample18-task-board',
        'checks' => [
            'health' => ['status' => $health['status']],
            'login' => ['status' => $loginSubmit['status'], 'final_path' => $loginSubmit['final_path']],
            'create' => ['status' => $create['status'], 'title' => $title],
            'update' => ['status' => $update['status'], 'title' => $updatedTitle],
            'generated_submit' => [
                'get' => ['status' => $generatedGet['status'], 'failure_code' => $generatedGetJson['failure_code'] ?? ''],
                'blocked' => ['status' => $generatedBlocked['status'], 'failure_code' => $generatedBlockedJson['failure_code'] ?? ''],
                'missing_csrf' => ['status' => $generatedMissingCsrf['status'], 'failure_code' => $generatedMissingCsrfJson['failure_code'] ?? ''],
                'invalid_csrf' => ['status' => $generatedInvalidCsrf['status'], 'failure_code' => $generatedInvalidCsrfJson['failure_code'] ?? ''],
                'invalid' => ['status' => $generatedInvalid['status'], 'failure_code' => $generatedInvalidJson['failure_code'] ?? ''],
                'unknown' => ['status' => $generatedUnknown['status'], 'failure_code' => $generatedUnknownJson['failure_code'] ?? ''],
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
    $pretty = in_array('--pretty', $argv, true);
    echo json_encode(
        ['ok' => false, 'error' => $throwable->getMessage()],
        ($pretty ? JSON_PRETTY_PRINT : 0) | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ) . PHP_EOL;
    exit(1);
}
