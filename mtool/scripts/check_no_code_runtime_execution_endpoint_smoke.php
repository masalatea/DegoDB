#!/usr/bin/env php
<?php

declare(strict_types=1);

function usage(): string
{
    return <<<'TEXT'
usage: php mtool/scripts/check_no_code_runtime_execution_endpoint_smoke.php [options]

Options:
  --base-url=URL          Admin base URL (default: http://127.0.0.1:${ADMIN_HTTP_PORT:-18291})
  --profile=PROFILE       Smoke payload profile: sample28, sample29, or sample31 (default: sample28)
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
        'profile' => 'sample28',
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
            'profile' => $args['profile'] = $value,
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
    if (!in_array($args['profile'], ['sample28', 'sample29', 'sample31'], true)) {
        throw new InvalidArgumentException('unsupported --profile: ' . $args['profile']);
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
        'headers' => $headerMap,
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

function runtime_data_path(string $previewPath): string
{
    if (!str_ends_with($previewPath, '/runtime-preview.html')) {
        throw new RuntimeException('preview path cannot be converted to runtime data path: ' . $previewPath);
    }

    return substr($previewPath, 0, -strlen('/runtime-preview.html')) . '/runtime-data.json';
}

function runtime_data_read_model_field_type(array $payload, string $contractKey, string $fieldKey): string
{
    $readModel = is_array($payload['read_model'] ?? null) ? $payload['read_model'] : [];
    $contracts = is_array($readModel['contracts'] ?? null) ? $readModel['contracts'] : [];
    $contract = is_array($contracts[$contractKey] ?? null) ? $contracts[$contractKey] : [];
    $fields = is_array($contract['fields'] ?? null) ? $contract['fields'] : [];
    $field = is_array($fields[$fieldKey] ?? null) ? $fields[$fieldKey] : [];

    return (string) ($field['type'] ?? '');
}

function smoke_profile(string $profile): array
{
    $profiles = [
        'sample28' => [
            'project_key' => 'SAMPLE28',
            'contract_key' => 'no_code_ticket',
            'action_key' => 'update_no_code_ticket',
            'operation_key' => 'update_no_code_ticket',
            'key_field' => 'id',
            'key_value' => '1001',
            'selected_key_value' => '1002',
            'search_query' => 'Review generated customer fields',
            'filter_field' => 'status',
            'filter_value' => 'triage',
            'second_filter_field' => 'priority',
            'second_filter_value' => '20',
            'numeric_filter_field' => 'priority',
            'numeric_filter_operator' => 'gt',
            'numeric_filter_value' => '10',
            'numeric_filter_first_key' => '1002',
            'sort_field' => 'status',
            'sort_direction' => 'desc',
            'sort_first_key' => '1002',
            'numeric_sort_field' => 'priority',
            'numeric_sort_asc_first_key' => '1001',
            'numeric_sort_desc_first_key' => '1003',
            'field_types' => [
                'id' => 'integer',
                'status' => 'string',
                'priority' => 'integer',
            ],
            'input' => [
                'id' => '1001',
                'title' => 'First no-code app ticket',
                'status' => 'open',
                'priority' => '10',
                'body' => 'Generated sample28 direct endpoint smoke payload',
            ],
        ],
        'sample29' => [
            'project_key' => 'SAMPLE29',
            'contract_key' => 'support_case',
            'action_key' => 'update_support_case',
            'operation_key' => 'update_support_case',
            'key_field' => 'id',
            'key_value' => '2001',
            'selected_key_value' => '2002',
            'search_query' => 'Generated workflow',
            'filter_field' => 'status',
            'filter_value' => 'open',
            'second_filter_field' => 'severity',
            'second_filter_value' => 'medium',
            'numeric_filter_field' => 'id',
            'numeric_filter_operator' => 'gt',
            'numeric_filter_value' => '2001',
            'numeric_filter_first_key' => '2002',
            'sort_field' => 'status',
            'sort_direction' => 'asc',
            'sort_first_key' => '2002',
            'numeric_sort_field' => 'id',
            'numeric_sort_asc_first_key' => '2001',
            'numeric_sort_desc_first_key' => '2002',
            'field_types' => [
                'id' => 'integer',
                'status' => 'string',
                'severity' => 'string',
            ],
            'input' => [
                'id' => '2001',
                'subject' => 'Billing export follow-up',
                'status' => 'open',
                'severity' => 'medium',
                'next_action' => 'Generated sample29 direct endpoint smoke payload',
            ],
        ],
        'sample31' => [
            'project_key' => 'SAMPLE31',
            'contract_key' => 'inventory_request',
            'action_key' => 'update_inventory_request',
            'operation_key' => 'update_inventory_request',
            'key_field' => 'id',
            'key_value' => '3101',
            'selected_key_value' => '3102',
            'search_query' => 'SKU-CABLE-99',
            'filter_field' => 'status',
            'filter_value' => 'review',
            'second_filter_field' => 'quantity_needed',
            'second_filter_value' => '24',
            'numeric_filter_field' => 'quantity_needed',
            'numeric_filter_operator' => 'gte',
            'numeric_filter_value' => '24',
            'numeric_filter_first_key' => '3102',
            'sort_field' => 'status',
            'sort_direction' => 'desc',
            'sort_first_key' => '3102',
            'numeric_sort_field' => 'quantity_needed',
            'numeric_sort_asc_first_key' => '3101',
            'numeric_sort_desc_first_key' => '3102',
            'datetime_filter_field' => 'needed_by',
            'datetime_filter_operator' => 'gte',
            'datetime_filter_value' => '2026-07-15',
            'datetime_filter_first_key' => '3102',
            'datetime_sort_field' => 'needed_by',
            'datetime_sort_asc_first_key' => '3101',
            'datetime_sort_desc_first_key' => '3102',
            'datetime_invalid_value' => '2026-99-99',
            'field_types' => [
                'id' => 'integer',
                'status' => 'string',
                'quantity_needed' => 'integer',
                'needed_by' => 'date',
            ],
            'input' => [
                'id' => '3101',
                'item_sku' => 'SKU-BOARD-84',
                'quantity_needed' => '18',
                'status' => 'allocated',
                'fulfillment_note' => 'Generated sample31 direct endpoint smoke payload',
            ],
        ],
    ];

    return $profiles[$profile];
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

function endpoint_smoke(array &$client, array $profile, string $label, string $previewPath, string $expectedUrlFragment): array
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
            'action_key' => $profile['action_key'],
            'input' => $profile['input'],
        ],
    ]);

    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' execution response was not JSON');
    ensure($response['status'] === 200, $label . ' execution did not return 200');
    ensure(($payload['ok'] ?? null) === true, $label . ' execution ok flag mismatch');
    ensure(($payload['executed'] ?? null) === true, $label . ' execution executed flag mismatch');
    ensure(($payload['error'] ?? '') === '', $label . ' execution error mismatch');
    ensure(is_array($payload['request'] ?? null) && ($payload['request']['ok'] ?? null) === true, $label . ' request contract did not pass');
    ensure(($payload['request']['action_key'] ?? '') === $profile['action_key'], $label . ' request action key mismatch');
    ensure(($payload['request']['binding']['project_key'] ?? '') === $profile['project_key'], $label . ' request project binding mismatch');
    ensure(($payload['request']['binding']['artifact_key'] ?? '') === (string) $binding['artifact_key'], $label . ' request artifact binding mismatch');
    ensure(($payload['intent']['operation_key'] ?? '') === $profile['operation_key'], $label . ' intent operation key mismatch');
    $keyField = (string) $profile['key_field'];
    ensure((string) ($payload['intent']['payload']['key'][$keyField] ?? '') === $profile['key_value'], $label . ' intent key mismatch');
    ensure(($payload['result']['sync_intent']['intent_version'] ?? '') === 'managed-operation-sync-intent-v0', $label . ' sync intent version mismatch');
    ensure(($payload['result']['sync_intent']['origin'] ?? '') === 'public-runtime', $label . ' sync intent origin mismatch');
    ensure(($payload['result']['sync_intent']['target'] ?? '') === 'server', $label . ' sync intent target mismatch');
    ensure(($payload['result']['executor_result']['ok'] ?? null) === true, $label . ' executor result ok flag mismatch');
    ensure(($payload['result']['executor_result']['item']['status'] ?? '') === 'pending', $label . ' outbox status mismatch');
    ensure((string) ($payload['result']['executor_result']['item']['id'] ?? '') !== '', $label . ' outbox item id missing');
    ensure((string) ($payload['result']['executor_result']['item']['dedupe_key'] ?? '') !== '', $label . ' outbox item dedupe key missing');
    ensure(($payload['result']['executor_result']['item']['operation_key'] ?? '') === $profile['operation_key'], $label . ' outbox item operation key mismatch');

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

function runtime_data_smoke(array &$client, array $profile, string $label, string $previewPath, string $selectionKind, string $selectedKey = ''): array
{
    $path = runtime_data_path($previewPath);
    if ($selectedKey !== '') {
        $path .= '?selected_key=' . rawurlencode($selectedKey);
    }
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' runtime data response was not JSON');
    ensure(($payload['contract_version'] ?? '') === 'no-code-runtime-data-v0', $label . ' runtime data contract mismatch');
    ensure(($payload['runtime_preview_version'] ?? '') === 'no-code-runtime-v0', $label . ' runtime data runtime version mismatch');
    ensure(strtolower((string) ($response['headers']['cache-control'][0] ?? '')) === 'no-store', $label . ' runtime data cache-control mismatch');

    if ($response['status'] !== 200) {
        ensure(($payload['ok'] ?? null) === false, $label . ' runtime data fail-closed ok flag mismatch');
        ensure((string) ($payload['error'] ?? '') !== '', $label . ' runtime data fail-closed error missing');

        return [
            'label' => $label,
            'status' => $response['status'],
            'data_url' => $path,
            'contract_version' => (string) ($payload['contract_version'] ?? ''),
            'fail_closed' => true,
            'error' => (string) ($payload['error'] ?? ''),
        ];
    }

    ensure(($payload['ok'] ?? null) === true, $label . ' runtime data ok flag mismatch');
    ensure(($payload['project_key'] ?? '') === $profile['project_key'], $label . ' runtime data project mismatch');
    ensure(($payload['selection']['kind'] ?? '') === $selectionKind, $label . ' runtime data selection kind mismatch');
    ensure((string) ($payload['selection']['artifact_key'] ?? '') !== '', $label . ' runtime data artifact key missing');
    ensure(($payload['query']['selected_key'] ?? '') === $selectedKey, $label . ' runtime data selected key query mismatch');
    ensure(($payload['screen_definition_version'] ?? '') === 'no-code-screen-definition-v0', $label . ' runtime data screen definition version mismatch');
    ensure(($payload['error'] ?? '') === '', $label . ' runtime data error mismatch');

    $contractKey = (string) $profile['contract_key'];
    $keyFieldType = runtime_data_read_model_field_type($payload, $contractKey, (string) $profile['key_field']);
    $filterFieldType = runtime_data_read_model_field_type($payload, $contractKey, (string) $profile['filter_field']);
    $sortFieldType = runtime_data_read_model_field_type($payload, $contractKey, (string) $profile['sort_field']);
    ensure($keyFieldType !== '', $label . ' runtime data read model key field type missing');
    ensure($filterFieldType !== '', $label . ' runtime data read model filter field type missing');
    ensure($sortFieldType !== '', $label . ' runtime data read model sort field type missing');
    foreach (($profile['field_types'] ?? []) as $fieldKey => $expectedType) {
        ensure(
            runtime_data_read_model_field_type($payload, $contractKey, (string) $fieldKey) === (string) $expectedType,
            $label . ' runtime data read model field type mismatch: ' . (string) $fieldKey,
        );
    }

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    ensure(count($screens) >= 3, $label . ' runtime data screen count mismatch');
    $listScreen = null;
    $detailScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
        }
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'detail') {
            $detailScreen = $screen;
        }
    }
    ensure(is_array($listScreen), $label . ' runtime data list screen missing');
    ensure(is_array($detailScreen), $label . ' runtime data detail screen missing');
    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure($rows !== [], $label . ' runtime data list rows missing');
    $keyField = (string) $profile['key_field'];
    $firstRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($firstRowKey !== '', $label . ' runtime data first row key missing');
    $expectedSelectedKey = $selectedKey !== '' ? $selectedKey : $firstRowKey;

    $listMetadata = is_array($listScreen['metadata'] ?? null) ? $listScreen['metadata'] : [];
    ensure(($listMetadata['row_count'] ?? null) === count($rows), $label . ' runtime data list row count metadata mismatch');
    ensure(($listMetadata['freshness'] ?? '') === 'live-read', $label . ' runtime data freshness metadata mismatch');
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    $selectionBasis = is_array($detailMetadata['selection_basis'] ?? null) ? $detailMetadata['selection_basis'] : [];
    $expectedSelectionBasis = $selectedKey !== '' ? 'explicit-selected-key' : 'default-first-row';
    ensure(
        ($selectionBasis['kind'] ?? '') === $expectedSelectionBasis,
        $label . ' runtime data selection basis metadata mismatch',
    );
    ensure(
        ($detailMetadata['selected_key']['field_key'] ?? '') === $keyField
            && (string) ($detailMetadata['selected_key']['display_value'] ?? '') === $expectedSelectedKey,
        $label . ' runtime data selected key metadata mismatch',
    );
    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    ensure(
        (string) ($detailItem[$keyField]['display_value'] ?? '') === $expectedSelectedKey,
        $label . ' runtime data detail selected item mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'contract_version' => (string) ($payload['contract_version'] ?? ''),
        'selection_kind' => (string) ($payload['selection']['kind'] ?? ''),
        'artifact_key' => (string) ($payload['selection']['artifact_key'] ?? ''),
        'screen_count' => count($screens),
        'key_field_type' => $keyFieldType,
        'filter_field_type' => $filterFieldType,
        'sort_field_type' => $sortFieldType,
        'first_row_key' => $firstRowKey,
        'row_count_metadata' => (int) ($listMetadata['row_count'] ?? 0),
        'selected_key' => (string) ($detailMetadata['selected_key']['display_value'] ?? ''),
        'selection_basis' => (string) ($selectionBasis['kind'] ?? ''),
        'query_selected_key' => (string) ($payload['query']['selected_key'] ?? ''),
    ];
}

function runtime_data_missing_selected_key_smoke(array &$client, string $label, string $previewPath): array
{
    $path = runtime_data_path($previewPath) . '?selected_key=missing-runtime-data-key';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' missing selected key response was not JSON');
    ensure($response['status'] === 422, $label . ' missing selected key did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' missing selected key ok flag mismatch');
    ensure(str_contains((string) ($payload['error'] ?? ''), 'selected key'), $label . ' missing selected key error mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_paginated_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $path = runtime_data_path($previewPath) . '?page=2&page_size=1';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' paginated runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' paginated runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' paginated runtime data ok flag mismatch');
    ensure(($payload['query']['page'] ?? '') === '2', $label . ' paginated runtime data page query mismatch');
    ensure(($payload['query']['page_size'] ?? '') === '1', $label . ' paginated runtime data page_size query mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    $detailScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
        }
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'detail') {
            $detailScreen = $screen;
        }
    }
    ensure(is_array($listScreen), $label . ' paginated runtime data list screen missing');
    ensure(is_array($detailScreen), $label . ' paginated runtime data detail screen missing');

    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) === 1, $label . ' paginated runtime data row count mismatch');
    $keyField = (string) $profile['key_field'];
    $pageRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($pageRowKey === (string) $profile['selected_key_value'], $label . ' paginated runtime data list row key mismatch');

    $listMetadata = is_array($listScreen['metadata'] ?? null) ? $listScreen['metadata'] : [];
    $pagination = is_array($listMetadata['pagination'] ?? null) ? $listMetadata['pagination'] : [];
    ensure(($listMetadata['row_count'] ?? null) === 1, $label . ' paginated runtime data row_count metadata mismatch');
    ensure(($pagination['page'] ?? null) === 2, $label . ' paginated runtime data page metadata mismatch');
    ensure(($pagination['page_size'] ?? null) === 1, $label . ' paginated runtime data page_size metadata mismatch');
    ensure(($pagination['total_rows'] ?? null) >= 2, $label . ' paginated runtime data total_rows metadata mismatch');
    ensure(($pagination['page_count'] ?? null) >= 2, $label . ' paginated runtime data page_count metadata mismatch');
    ensure(($pagination['has_previous_page'] ?? null) === true, $label . ' paginated runtime data previous metadata mismatch');

    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    $selectionBasis = is_array($detailMetadata['selection_basis'] ?? null) ? $detailMetadata['selection_basis'] : [];
    ensure(($selectionBasis['kind'] ?? '') === 'default-first-row', $label . ' paginated runtime data selection basis mismatch');
    ensure(
        (string) ($detailItem[$keyField]['display_value'] ?? '') === (string) $profile['key_value'],
        $label . ' paginated runtime data detail default selection mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'page_row_key' => $pageRowKey,
        'detail_selected_key' => (string) ($detailItem[$keyField]['display_value'] ?? ''),
        'selection_basis' => (string) ($selectionBasis['kind'] ?? ''),
        'pagination' => $pagination,
    ];
}

function runtime_data_search_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $searchQuery = (string) $profile['search_query'];
    $path = runtime_data_path($previewPath) . '?q=' . rawurlencode($searchQuery);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' searched runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' searched runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' searched runtime data ok flag mismatch');
    ensure(($payload['query']['q'] ?? '') === $searchQuery, $label . ' searched runtime data query mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    $detailScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
        }
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'detail') {
            $detailScreen = $screen;
        }
    }
    ensure(is_array($listScreen), $label . ' searched runtime data list screen missing');
    ensure(is_array($detailScreen), $label . ' searched runtime data detail screen missing');

    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) === 1, $label . ' searched runtime data row count mismatch');
    $keyField = (string) $profile['key_field'];
    $searchRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($searchRowKey === (string) $profile['selected_key_value'], $label . ' searched runtime data list row key mismatch');

    $listMetadata = is_array($listScreen['metadata'] ?? null) ? $listScreen['metadata'] : [];
    ensure(($listMetadata['row_count'] ?? null) === 1, $label . ' searched runtime data row_count metadata mismatch');

    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    $selectionBasis = is_array($detailMetadata['selection_basis'] ?? null) ? $detailMetadata['selection_basis'] : [];
    ensure(($selectionBasis['kind'] ?? '') === 'query-result-first-row', $label . ' searched runtime data selection basis mismatch');
    ensure(
        (string) ($detailItem[$keyField]['display_value'] ?? '') === (string) $profile['selected_key_value'],
        $label . ' searched runtime data detail selection mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'query' => $searchQuery,
        'row_key' => $searchRowKey,
        'detail_selected_key' => (string) ($detailItem[$keyField]['display_value'] ?? ''),
        'selection_basis' => (string) ($selectionBasis['kind'] ?? ''),
    ];
}

function runtime_data_filter_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $filterField = (string) $profile['filter_field'];
    $filterValue = (string) $profile['filter_value'];
    $path = runtime_data_path($previewPath) . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' filtered runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' filtered runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' filtered runtime data ok flag mismatch');
    ensure(($payload['query']['filter'][$filterField] ?? '') === $filterValue, $label . ' filtered runtime data query mismatch');
    ensure(($payload['query']['filter_op'][$filterField] ?? '') === 'contains', $label . ' filtered runtime data default operator mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    $detailScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
        }
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'detail') {
            $detailScreen = $screen;
        }
    }
    ensure(is_array($listScreen), $label . ' filtered runtime data list screen missing');
    ensure(is_array($detailScreen), $label . ' filtered runtime data detail screen missing');

    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) === 1, $label . ' filtered runtime data row count mismatch');
    $keyField = (string) $profile['key_field'];
    $filterRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($filterRowKey === (string) $profile['selected_key_value'], $label . ' filtered runtime data list row key mismatch');
    ensure((string) ($rows[0][$filterField]['display_value'] ?? '') === $filterValue, $label . ' filtered runtime data field value mismatch');

    $listMetadata = is_array($listScreen['metadata'] ?? null) ? $listScreen['metadata'] : [];
    ensure(($listMetadata['row_count'] ?? null) === 1, $label . ' filtered runtime data row_count metadata mismatch');

    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    $selectionBasis = is_array($detailMetadata['selection_basis'] ?? null) ? $detailMetadata['selection_basis'] : [];
    ensure(($selectionBasis['kind'] ?? '') === 'query-result-first-row', $label . ' filtered runtime data selection basis mismatch');
    ensure(
        (string) ($detailItem[$keyField]['display_value'] ?? '') === (string) $profile['selected_key_value'],
        $label . ' filtered runtime data detail selection mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'filter_field' => $filterField,
        'filter_value' => $filterValue,
        'filter_operator' => (string) ($payload['query']['filter_op'][$filterField] ?? ''),
        'row_key' => $filterRowKey,
        'detail_selected_key' => (string) ($detailItem[$keyField]['display_value'] ?? ''),
        'selection_basis' => (string) ($selectionBasis['kind'] ?? ''),
    ];
}

function runtime_data_filter_operator_eq_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $filterField = (string) $profile['filter_field'];
    $filterValue = (string) $profile['filter_value'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter_op[' . rawurlencode($filterField) . ']=eq';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' eq-filter runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' eq-filter runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' eq-filter runtime data ok flag mismatch');
    ensure(($payload['query']['filter'][$filterField] ?? '') === $filterValue, $label . ' eq-filter runtime data query mismatch');
    ensure(($payload['query']['filter_op'][$filterField] ?? '') === 'eq', $label . ' eq-filter runtime data operator mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
            break;
        }
    }
    ensure(is_array($listScreen), $label . ' eq-filter runtime data list screen missing');

    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) === 1, $label . ' eq-filter runtime data row count mismatch');
    $keyField = (string) $profile['key_field'];
    $filterRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($filterRowKey === (string) $profile['selected_key_value'], $label . ' eq-filter runtime data list row key mismatch');
    ensure((string) ($rows[0][$filterField]['display_value'] ?? '') === $filterValue, $label . ' eq-filter runtime data field value mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'filter_field' => $filterField,
        'filter_value' => $filterValue,
        'filter_operator' => 'eq',
        'row_key' => $filterRowKey,
    ];
}

function runtime_data_numeric_filter_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $filterField = (string) $profile['numeric_filter_field'];
    $filterValue = (string) $profile['numeric_filter_value'];
    $filterOperator = (string) $profile['numeric_filter_operator'];
    $expectedFirstKey = (string) $profile['numeric_filter_first_key'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter_op[' . rawurlencode($filterField) . ']=' . rawurlencode($filterOperator);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' numeric-filter runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' numeric-filter runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' numeric-filter runtime data ok flag mismatch');
    ensure(($payload['query']['filter'][$filterField] ?? '') === $filterValue, $label . ' numeric-filter runtime data query mismatch');
    ensure(($payload['query']['filter_op'][$filterField] ?? '') === $filterOperator, $label . ' numeric-filter runtime data operator mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
            break;
        }
    }
    ensure(is_array($listScreen), $label . ' numeric-filter runtime data list screen missing');
    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure($rows !== [], $label . ' numeric-filter runtime data list rows missing');
    $keyField = (string) $profile['key_field'];
    $filterRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($filterRowKey === $expectedFirstKey, $label . ' numeric-filter runtime data first row mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'filter_field' => $filterField,
        'filter_value' => $filterValue,
        'filter_operator' => $filterOperator,
        'row_key' => $filterRowKey,
    ];
}

function runtime_data_non_numeric_filter_operator_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $filterField = (string) $profile['filter_field'];
    $filterValue = (string) $profile['filter_value'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter_op[' . rawurlencode($filterField) . ']=gt';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' non-numeric-filter-operator runtime data response was not JSON');
    ensure($response['status'] === 422, $label . ' non-numeric-filter-operator runtime data did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' non-numeric-filter-operator runtime data ok flag mismatch');
    ensure(str_contains((string) ($payload['error'] ?? ''), 'numeric or date/time field'), $label . ' non-numeric-filter-operator runtime data error mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_datetime_filter_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    if (!isset($profile['datetime_filter_field'])) {
        return [
            'label' => $label,
            'skipped' => true,
        ];
    }

    $filterField = (string) $profile['datetime_filter_field'];
    $filterValue = (string) $profile['datetime_filter_value'];
    $filterOperator = (string) $profile['datetime_filter_operator'];
    $expectedFirstKey = (string) $profile['datetime_filter_first_key'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter_op[' . rawurlencode($filterField) . ']=' . rawurlencode($filterOperator);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' datetime-filter runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' datetime-filter runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' datetime-filter runtime data ok flag mismatch');
    ensure(($payload['query']['filter'][$filterField] ?? '') === $filterValue, $label . ' datetime-filter runtime data query mismatch');
    ensure(($payload['query']['filter_op'][$filterField] ?? '') === $filterOperator, $label . ' datetime-filter runtime data operator mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
            break;
        }
    }
    ensure(is_array($listScreen), $label . ' datetime-filter runtime data list screen missing');
    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure($rows !== [], $label . ' datetime-filter runtime data list rows missing');
    $keyField = (string) $profile['key_field'];
    $filterRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($filterRowKey === $expectedFirstKey, $label . ' datetime-filter runtime data first row mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'filter_field' => $filterField,
        'filter_value' => $filterValue,
        'filter_operator' => $filterOperator,
        'row_key' => $filterRowKey,
    ];
}

function runtime_data_invalid_datetime_filter_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    if (!isset($profile['datetime_filter_field'])) {
        return [
            'label' => $label,
            'skipped' => true,
        ];
    }

    $filterField = (string) $profile['datetime_filter_field'];
    $filterValue = (string) $profile['datetime_invalid_value'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter_op[' . rawurlencode($filterField) . ']=gte';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' invalid datetime-filter runtime data response was not JSON');
    ensure($response['status'] === 422, $label . ' invalid datetime-filter runtime data did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' invalid datetime-filter runtime data ok flag mismatch');
    ensure(str_contains((string) ($payload['error'] ?? ''), 'date/time'), $label . ' invalid datetime-filter runtime data error mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_multi_filter_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $filterField = (string) $profile['filter_field'];
    $filterValue = (string) $profile['filter_value'];
    $secondFilterField = (string) $profile['second_filter_field'];
    $secondFilterValue = (string) $profile['second_filter_value'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter[' . rawurlencode($secondFilterField) . ']=' . rawurlencode($secondFilterValue);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' multi-filter runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' multi-filter runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' multi-filter runtime data ok flag mismatch');
    ensure(($payload['query']['filter'][$filterField] ?? '') === $filterValue, $label . ' multi-filter runtime data first query mismatch');
    ensure(($payload['query']['filter'][$secondFilterField] ?? '') === $secondFilterValue, $label . ' multi-filter runtime data second query mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    $detailScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
        }
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'detail') {
            $detailScreen = $screen;
        }
    }
    ensure(is_array($listScreen), $label . ' multi-filter runtime data list screen missing');
    ensure(is_array($detailScreen), $label . ' multi-filter runtime data detail screen missing');

    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) === 1, $label . ' multi-filter runtime data row count mismatch');
    $keyField = (string) $profile['key_field'];
    $filterRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($filterRowKey === (string) $profile['selected_key_value'], $label . ' multi-filter runtime data list row key mismatch');
    ensure((string) ($rows[0][$filterField]['display_value'] ?? '') === $filterValue, $label . ' multi-filter first field value mismatch');
    ensure((string) ($rows[0][$secondFilterField]['display_value'] ?? '') === $secondFilterValue, $label . ' multi-filter second field value mismatch');

    $listMetadata = is_array($listScreen['metadata'] ?? null) ? $listScreen['metadata'] : [];
    ensure(($listMetadata['row_count'] ?? null) === 1, $label . ' multi-filter runtime data row_count metadata mismatch');

    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    $selectionBasis = is_array($detailMetadata['selection_basis'] ?? null) ? $detailMetadata['selection_basis'] : [];
    ensure(($selectionBasis['kind'] ?? '') === 'query-result-first-row', $label . ' multi-filter runtime data selection basis mismatch');
    ensure(
        (string) ($detailItem[$keyField]['display_value'] ?? '') === (string) $profile['selected_key_value'],
        $label . ' multi-filter runtime data detail selection mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'filter_field' => $filterField,
        'filter_value' => $filterValue,
        'second_filter_field' => $secondFilterField,
        'second_filter_value' => $secondFilterValue,
        'row_key' => $filterRowKey,
        'detail_selected_key' => (string) ($detailItem[$keyField]['display_value'] ?? ''),
        'selection_basis' => (string) ($selectionBasis['kind'] ?? ''),
    ];
}

function runtime_data_too_many_filters_smoke(array &$client, string $label, string $previewPath): array
{
    $filters = [];
    for ($index = 1; $index <= 9; $index++) {
        $filters[] = 'filter[f' . $index . ']=value' . $index;
    }
    $path = runtime_data_path($previewPath) . '?' . implode('&', $filters);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' too-many-filters runtime data response was not JSON');
    ensure($response['status'] === 422, $label . ' too-many-filters runtime data did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' too-many-filters runtime data ok flag mismatch');
    ensure(
        (string) ($payload['error'] ?? '') === 'runtime data filter query accepts 8 fields or less.',
        $label . ' too-many-filters runtime data error mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_invalid_filter_operator_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $filterField = (string) $profile['filter_field'];
    $filterValue = (string) $profile['filter_value'];
    $path = runtime_data_path($previewPath)
        . '?filter[' . rawurlencode($filterField) . ']=' . rawurlencode($filterValue)
        . '&filter_op[' . rawurlencode($filterField) . ']=sideways';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' invalid filter operator runtime data response was not JSON');
    ensure($response['status'] === 422, $label . ' invalid filter operator runtime data did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' invalid filter operator runtime data ok flag mismatch');
    ensure(
        (string) ($payload['error'] ?? '') === 'runtime data filter operator must be contains, eq, gt, gte, lt, or lte.',
        $label . ' invalid filter operator runtime data error mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_sort_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $sortField = (string) $profile['sort_field'];
    $sortDirection = (string) $profile['sort_direction'];
    $expectedFirstKey = (string) $profile['sort_first_key'];
    $path = runtime_data_path($previewPath) . '?sort[' . rawurlencode($sortField) . ']=' . rawurlencode($sortDirection);
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' sorted runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' sorted runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' sorted runtime data ok flag mismatch');
    ensure(($payload['query']['sort'][$sortField] ?? '') === $sortDirection, $label . ' sorted runtime data query mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    $detailScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
        }
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'detail') {
            $detailScreen = $screen;
        }
    }
    ensure(is_array($listScreen), $label . ' sorted runtime data list screen missing');
    ensure(is_array($detailScreen), $label . ' sorted runtime data detail screen missing');

    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) >= 2, $label . ' sorted runtime data row count mismatch');
    $keyField = (string) $profile['key_field'];
    $sortRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($sortRowKey === $expectedFirstKey, $label . ' sorted runtime data first row key mismatch');

    $detailItem = is_array($detailScreen['data']['item'] ?? null) ? $detailScreen['data']['item'] : [];
    $detailMetadata = is_array($detailScreen['metadata'] ?? null) ? $detailScreen['metadata'] : [];
    $selectionBasis = is_array($detailMetadata['selection_basis'] ?? null) ? $detailMetadata['selection_basis'] : [];
    ensure(($selectionBasis['kind'] ?? '') === 'query-result-first-row', $label . ' sorted runtime data selection basis mismatch');
    ensure(
        (string) ($detailItem[$keyField]['display_value'] ?? '') === $expectedFirstKey,
        $label . ' sorted runtime data detail default selection mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'sort_field' => $sortField,
        'sort_direction' => $sortDirection,
        'first_row_key' => $sortRowKey,
        'detail_selected_key' => (string) ($detailItem[$keyField]['display_value'] ?? ''),
        'selection_basis' => (string) ($selectionBasis['kind'] ?? ''),
    ];
}

function runtime_data_numeric_sort_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $sortField = (string) $profile['numeric_sort_field'];
    $keyField = (string) $profile['key_field'];
    $results = [];
    foreach (['asc', 'desc'] as $sortDirection) {
        $expectedFirstKey = (string) $profile['numeric_sort_' . $sortDirection . '_first_key'];
        $path = runtime_data_path($previewPath) . '?sort[' . rawurlencode($sortField) . ']=' . $sortDirection;
        $response = request_once($client, 'GET', $path);
        $payload = json_decode($response['body'], true);
        ensure(is_array($payload), $label . ' numeric-sort runtime data response was not JSON');
        ensure($response['status'] === 200, $label . ' numeric-sort runtime data did not succeed');
        ensure(($payload['ok'] ?? null) === true, $label . ' numeric-sort runtime data ok flag mismatch');
        ensure(($payload['query']['sort'][$sortField] ?? '') === $sortDirection, $label . ' numeric-sort runtime data query mismatch');

        $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
        $listScreen = null;
        foreach ($screens as $screen) {
            if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
                $listScreen = $screen;
                break;
            }
        }
        ensure(is_array($listScreen), $label . ' numeric-sort runtime data list screen missing');
        $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
        ensure(count($rows) >= 2, $label . ' numeric-sort runtime data row count mismatch');
        $sortRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
        ensure($sortRowKey === $expectedFirstKey, $label . ' numeric-sort runtime data first row key mismatch');

        $results[$sortDirection] = [
            'data_url' => $path,
            'first_row_key' => $sortRowKey,
        ];
    }

    return [
        'label' => $label,
        'status' => 200,
        'sort_field' => $sortField,
        'asc' => $results['asc'],
        'desc' => $results['desc'],
    ];
}

function runtime_data_datetime_sort_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    if (!isset($profile['datetime_sort_field'])) {
        return [
            'label' => $label,
            'skipped' => true,
        ];
    }

    $sortField = (string) $profile['datetime_sort_field'];
    $keyField = (string) $profile['key_field'];
    $results = [];
    foreach (['asc', 'desc'] as $sortDirection) {
        $expectedFirstKey = (string) $profile['datetime_sort_' . $sortDirection . '_first_key'];
        $path = runtime_data_path($previewPath) . '?sort[' . rawurlencode($sortField) . ']=' . $sortDirection;
        $response = request_once($client, 'GET', $path);
        $payload = json_decode($response['body'], true);
        ensure(is_array($payload), $label . ' datetime-sort runtime data response was not JSON');
        ensure($response['status'] === 200, $label . ' datetime-sort runtime data did not succeed');
        ensure(($payload['ok'] ?? null) === true, $label . ' datetime-sort runtime data ok flag mismatch');
        ensure(($payload['query']['sort'][$sortField] ?? '') === $sortDirection, $label . ' datetime-sort runtime data query mismatch');

        $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
        $listScreen = null;
        foreach ($screens as $screen) {
            if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
                $listScreen = $screen;
                break;
            }
        }
        ensure(is_array($listScreen), $label . ' datetime-sort runtime data list screen missing');
        $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
        ensure(count($rows) >= 2, $label . ' datetime-sort runtime data row count mismatch');
        $sortRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
        ensure($sortRowKey === $expectedFirstKey, $label . ' datetime-sort runtime data first row key mismatch');

        $results[$sortDirection] = [
            'data_url' => $path,
            'first_row_key' => $sortRowKey,
        ];
    }

    return [
        'label' => $label,
        'status' => 200,
        'sort_field' => $sortField,
        'asc' => $results['asc'],
        'desc' => $results['desc'],
    ];
}

function runtime_data_multi_sort_smoke(array &$client, array $profile, string $label, string $previewPath): array
{
    $sortField = (string) $profile['sort_field'];
    $sortDirection = (string) $profile['sort_direction'];
    $keyField = (string) $profile['key_field'];
    $expectedFirstKey = (string) $profile['sort_first_key'];
    $path = runtime_data_path($previewPath)
        . '?sort[' . rawurlencode($sortField) . ']=' . rawurlencode($sortDirection)
        . '&sort[' . rawurlencode($keyField) . ']=desc';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' multi-sort runtime data response was not JSON');
    ensure($response['status'] === 200, $label . ' multi-sort runtime data did not succeed');
    ensure(($payload['ok'] ?? null) === true, $label . ' multi-sort runtime data ok flag mismatch');
    ensure(($payload['query']['sort'][$sortField] ?? '') === $sortDirection, $label . ' multi-sort primary query mismatch');
    ensure(($payload['query']['sort'][$keyField] ?? '') === 'desc', $label . ' multi-sort secondary query mismatch');

    $screens = is_array($payload['screens'] ?? null) ? $payload['screens'] : [];
    $listScreen = null;
    foreach ($screens as $screen) {
        if (is_array($screen) && (string) ($screen['screen_type'] ?? '') === 'list') {
            $listScreen = $screen;
            break;
        }
    }
    ensure(is_array($listScreen), $label . ' multi-sort runtime data list screen missing');
    $rows = is_array($listScreen['data']['rows'] ?? null) ? $listScreen['data']['rows'] : [];
    ensure(count($rows) >= 2, $label . ' multi-sort runtime data row count mismatch');
    $sortRowKey = (string) ($rows[0][$keyField]['display_value'] ?? '');
    ensure($sortRowKey === $expectedFirstKey, $label . ' multi-sort runtime data first row key mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'sort_field' => $sortField,
        'sort_direction' => $sortDirection,
        'second_sort_field' => $keyField,
        'second_sort_direction' => 'desc',
        'first_row_key' => $sortRowKey,
    ];
}

function runtime_data_invalid_sort_smoke(array &$client, string $label, string $previewPath): array
{
    $path = runtime_data_path($previewPath) . '?sort[status]=sideways';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' invalid sort response was not JSON');
    ensure($response['status'] === 422, $label . ' invalid sort did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' invalid sort ok flag mismatch');
    ensure(str_contains((string) ($payload['error'] ?? ''), 'sort'), $label . ' invalid sort error mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_too_many_sorts_smoke(array &$client, string $label, string $previewPath): array
{
    $path = runtime_data_path($previewPath) . '?sort[f1]=asc&sort[f2]=asc&sort[f3]=asc&sort[f4]=asc';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' too-many-sort response was not JSON');
    ensure($response['status'] === 422, $label . ' too-many-sort did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' too-many-sort ok flag mismatch');
    ensure(
        (string) ($payload['error'] ?? '') === 'runtime data sort query accepts 3 fields or less.',
        $label . ' too-many-sort error mismatch',
    );

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
    ];
}

function runtime_data_invalid_pagination_smoke(array &$client, string $label, string $previewPath): array
{
    $path = runtime_data_path($previewPath) . '?page=0&page_size=1';
    $response = request_once($client, 'GET', $path);
    $payload = json_decode($response['body'], true);
    ensure(is_array($payload), $label . ' invalid pagination response was not JSON');
    ensure($response['status'] === 422, $label . ' invalid pagination did not fail closed');
    ensure(($payload['ok'] ?? null) === false, $label . ' invalid pagination ok flag mismatch');
    ensure(str_contains((string) ($payload['error'] ?? ''), 'page'), $label . ' invalid pagination error mismatch');

    return [
        'label' => $label,
        'status' => $response['status'],
        'data_url' => $path,
        'fail_closed' => true,
        'error' => (string) ($payload['error'] ?? ''),
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
    $profile = smoke_profile($args['profile']);
    $results = [
        runtime_data_smoke($client, $profile, 'current', $args['current_path'], 'current'),
        runtime_data_smoke($client, $profile, 'alias', $args['alias_path'], 'alias'),
        runtime_data_smoke($client, $profile, 'current-selected', $args['current_path'], 'current', $profile['selected_key_value']),
        runtime_data_missing_selected_key_smoke($client, 'current-selected-missing', $args['current_path']),
        runtime_data_paginated_smoke($client, $profile, 'current-paginated', $args['current_path']),
        runtime_data_search_smoke($client, $profile, 'current-search', $args['current_path']),
        runtime_data_filter_smoke($client, $profile, 'current-filter', $args['current_path']),
        runtime_data_filter_operator_eq_smoke($client, $profile, 'current-filter-eq', $args['current_path']),
        runtime_data_numeric_filter_smoke($client, $profile, 'current-filter-numeric', $args['current_path']),
        runtime_data_datetime_filter_smoke($client, $profile, 'current-filter-datetime', $args['current_path']),
        runtime_data_multi_filter_smoke($client, $profile, 'current-multi-filter', $args['current_path']),
        runtime_data_too_many_filters_smoke($client, 'current-too-many-filters', $args['current_path']),
        runtime_data_invalid_filter_operator_smoke($client, $profile, 'current-filter-operator-invalid', $args['current_path']),
        runtime_data_non_numeric_filter_operator_smoke($client, $profile, 'current-filter-operator-non-numeric-field', $args['current_path']),
        runtime_data_invalid_datetime_filter_smoke($client, $profile, 'current-filter-datetime-invalid', $args['current_path']),
        runtime_data_sort_smoke($client, $profile, 'current-sort', $args['current_path']),
        runtime_data_numeric_sort_smoke($client, $profile, 'current-sort-numeric', $args['current_path']),
        runtime_data_datetime_sort_smoke($client, $profile, 'current-sort-datetime', $args['current_path']),
        runtime_data_multi_sort_smoke($client, $profile, 'current-multi-sort', $args['current_path']),
        runtime_data_invalid_sort_smoke($client, 'current-sort-invalid', $args['current_path']),
        runtime_data_too_many_sorts_smoke($client, 'current-too-many-sorts', $args['current_path']),
        runtime_data_invalid_pagination_smoke($client, 'current-pagination-invalid', $args['current_path']),
        endpoint_smoke($client, $profile, 'current', $args['current_path'], '/current/execute.json'),
        endpoint_smoke($client, $profile, 'alias', $args['alias_path'], '/alias/'),
    ];

    $jsonFlags = JSON_UNESCAPED_SLASHES | ($args['pretty'] ? JSON_PRETTY_PRINT : 0);
    echo json_encode(['ok' => true, 'results' => $results], $jsonFlags) . PHP_EOL;
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
