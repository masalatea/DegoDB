<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/single_proxy_build_plan_service.php';
require_once __DIR__ . '/source_output_repository.php';

function app_lab_endpoint_test_path(string $projectKey, array $query = []): string
{
    $path = '/runs/endpoints/' . rawurlencode(app_normalize_project_key($projectKey));
    $normalizedQuery = [];
    foreach ($query as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            continue;
        }

        $normalizedQuery[$key] = $stringValue;
    }

    if ($normalizedQuery !== []) {
        $path .= '?' . http_build_query($normalizedQuery, '', '&', PHP_QUERY_RFC3986);
    }

    return $path;
}

function app_lab_endpoint_test_job_api_path(string $jobKey): string
{
    return '/api/runs/endpoints/' . rawurlencode($jobKey);
}

function app_endpoint_test_job_storage_root(array $app, string $projectKey = ''): string
{
    return app_runtime_storage_endpoint_test_jobs_root($app, $projectKey);
}

function app_endpoint_test_job_key_is_valid(string $jobKey): bool
{
    return preg_match('/^[0-9]{8}-[0-9]{6}-[a-f0-9]{8}$/', $jobKey) === 1;
}

function app_endpoint_test_new_job_key(): string
{
    return date('Ymd-His') . '-' . bin2hex(random_bytes(4));
}

function app_endpoint_test_normalize_requested_by(string $requestedBy): string
{
    $normalized = preg_replace('/\s+/', ' ', trim($requestedBy));
    if (!is_string($normalized) || $normalized === '') {
        return 'system';
    }

    if (strlen($normalized) > 128) {
        return substr($normalized, 0, 128);
    }

    return $normalized;
}

function app_endpoint_test_ensure_directory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('directory を作成できませんでした: ' . $directory);
    }
}

function app_endpoint_test_delete_tree(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            $pathname = $fileInfo->getPathname();
            if ($fileInfo->isDir()) {
                @rmdir($pathname);
                continue;
            }

            @unlink($pathname);
        }
    } catch (Throwable $throwable) {
        // best effort cleanup
    }

    @rmdir($path);
}

/**
 * @param array<mixed> $payload
 */
function app_endpoint_test_write_json_file(string $path, array $payload): void
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );

    if (!is_string($json) || $json === '') {
        throw new RuntimeException('endpoint test job manifest JSON の生成に失敗しました。');
    }

    app_endpoint_test_ensure_directory(dirname($path));
    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('endpoint test job manifest の保存に失敗しました: ' . $path);
    }
}

/**
 * @return array{
 *     ok:bool,
 *     pretty_json:string,
 *     error:string
 * }
 */
function app_endpoint_test_validate_request_json(string $requestJson): array
{
    $trimmed = trim($requestJson);
    if ($trimmed === '') {
        return [
            'ok' => false,
            'pretty_json' => '',
            'error' => 'request JSON を入力してください。',
        ];
    }

    $decoded = json_decode($trimmed, true);
    if (!is_array($decoded) && !is_object($decoded) && $decoded !== null && !is_scalar($decoded)) {
        return [
            'ok' => false,
            'pretty_json' => '',
            'error' => 'request JSON の解析に失敗しました。',
        ];
    }

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'ok' => false,
            'pretty_json' => '',
            'error' => 'request JSON の解析に失敗しました: ' . json_last_error_msg(),
        ];
    }

    $prettyJson = json_encode(
        $decoded,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($prettyJson) || $prettyJson === '') {
        $prettyJson = $trimmed;
    }

    return [
        'ok' => true,
        'pretty_json' => $prettyJson,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     endpoint_url:string,
 *     error:string
 * }
 */
function app_endpoint_test_resolve_endpoint_url(
    string $endpointUrl,
    string $baseUrl = '',
    string $endpointFilename = '',
): array {
    $normalizedEndpointUrl = trim($endpointUrl);
    if ($normalizedEndpointUrl === '') {
        $normalizedBaseUrl = trim($baseUrl);
        $normalizedEndpointFilename = trim($endpointFilename);
        if ($normalizedBaseUrl === '' || $normalizedEndpointFilename === '') {
            return [
                'ok' => false,
                'endpoint_url' => '',
                'error' => 'endpoint URL か base URL + endpoint filename のどちらかが必要です。',
            ];
        }

        $normalizedEndpointUrl = rtrim($normalizedBaseUrl, '/') . '/' . ltrim($normalizedEndpointFilename, '/');
    }

    if (!preg_match('#^https?://#i', $normalizedEndpointUrl)) {
        return [
            'ok' => false,
            'endpoint_url' => '',
            'error' => 'endpoint URL は http:// または https:// で始めてください。',
        ];
    }

    $parts = parse_url($normalizedEndpointUrl);
    if (!is_array($parts) || !is_string($parts['host'] ?? null) || trim($parts['host']) === '') {
        return [
            'ok' => false,
            'endpoint_url' => '',
            'error' => 'endpoint URL の形式が不正です。',
        ];
    }

    return [
        'ok' => true,
        'endpoint_url' => $normalizedEndpointUrl,
        'error' => '',
    ];
}

function app_endpoint_test_endpoint_filename(string $dbAccessKey, string $functionKey): string
{
    return 'proxyserver-' . trim($dbAccessKey) . '-' . trim($functionKey) . '.php';
}

/**
 * @return array{
 *     response_pretty:string,
 *     response_json_valid:bool
 * }
 */
function app_endpoint_test_decode_response_body(string $responseBody): array
{
    $responsePretty = '';
    $responseJsonValid = false;
    if ($responseBody !== '') {
        $decoded = json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $responseJsonValid = true;
            $responsePretty = json_encode(
                $decoded,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            );
            if (!is_string($responsePretty)) {
                $responsePretty = '';
            }
        }
    }

    return [
        'response_pretty' => $responsePretty,
        'response_json_valid' => $responseJsonValid,
    ];
}

/**
 * @param list<string> $headers
 */
function app_endpoint_test_http_code_from_response_headers(array $headers): int
{
    foreach ($headers as $header) {
        if (!is_string($header)) {
            continue;
        }

        if (preg_match('#^HTTP/\S+\s+(\d{3})\b#', $header, $matches) === 1) {
            return (int) ($matches[1] ?? 0);
        }
    }

    return 0;
}

/**
 * @return array{
 *     ok:bool,
 *     http_code:int,
 *     response_body:string,
 *     response_pretty:string,
 *     response_json_valid:bool,
 *     error:string
 * }
 */
function app_endpoint_test_execute_request(string $endpointUrl, string $requestJson): array
{
    if (function_exists('curl_init')) {
        $curl = curl_init();
        if ($curl === false) {
            return [
                'ok' => false,
                'http_code' => 0,
                'response_body' => '',
                'response_pretty' => '',
                'response_json_valid' => false,
                'error' => 'curl 初期化に失敗しました。',
            ];
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $endpointUrl,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Matsuesoft-SQL-Output: Yes',
            ],
            CURLOPT_POSTFIELDS => $requestJson,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);

        $responseBody = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if (!is_string($responseBody)) {
            $responseBody = '';
        }

        $decodedResponse = app_endpoint_test_decode_response_body($responseBody);

        if ($curlError !== '') {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'response_body' => $responseBody,
                'response_pretty' => $decodedResponse['response_pretty'],
                'response_json_valid' => $decodedResponse['response_json_valid'],
                'error' => 'endpoint request に失敗しました: ' . $curlError,
            ];
        }

        if ($httpCode !== 200) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'response_body' => $responseBody,
                'response_pretty' => $decodedResponse['response_pretty'],
                'response_json_valid' => $decodedResponse['response_json_valid'],
                'error' => 'HTTP status が 200 ではありません: ' . $httpCode,
            ];
        }

        return [
            'ok' => true,
            'http_code' => $httpCode,
            'response_body' => $responseBody,
            'response_pretty' => $decodedResponse['response_pretty'],
            'response_json_valid' => $decodedResponse['response_json_valid'],
            'error' => '',
        ];
    }

    $streamError = '';
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", [
                'Content-Type: application/json',
                'Accept: application/json',
                'Matsuesoft-SQL-Output: Yes',
            ]),
            'content' => $requestJson,
            'timeout' => 30,
            'follow_location' => 1,
            'max_redirects' => 3,
            'ignore_errors' => true,
        ],
    ]);

    $previousErrorHandler = set_error_handler(
        static function (int $severity, string $message) use (&$streamError): bool {
            $streamError = $message;
            return true;
        },
    );

    try {
        $responseBody = file_get_contents($endpointUrl, false, $context);
    } finally {
        restore_error_handler();
    }

    if (!is_string($responseBody)) {
        $responseBody = '';
    }

    $responseHeaders = [];
    if (isset($http_response_header) && is_array($http_response_header)) {
        $responseHeaders = array_values(array_filter(
            $http_response_header,
            static fn (mixed $header): bool => is_string($header),
        ));
    }

    $httpCode = app_endpoint_test_http_code_from_response_headers($responseHeaders);
    $decodedResponse = app_endpoint_test_decode_response_body($responseBody);

    if ($streamError !== '' && $httpCode === 0) {
        return [
            'ok' => false,
            'http_code' => 0,
            'response_body' => $responseBody,
            'response_pretty' => $decodedResponse['response_pretty'],
            'response_json_valid' => $decodedResponse['response_json_valid'],
            'error' => 'endpoint request に失敗しました: ' . $streamError,
        ];
    }

    if ($httpCode !== 200) {
        return [
            'ok' => false,
            'http_code' => $httpCode,
            'response_body' => $responseBody,
            'response_pretty' => $decodedResponse['response_pretty'],
            'response_json_valid' => $decodedResponse['response_json_valid'],
            'error' => 'HTTP status が 200 ではありません: ' . $httpCode,
        ];
    }

    return [
        'ok' => true,
        'http_code' => $httpCode,
        'response_body' => $responseBody,
        'response_pretty' => $decodedResponse['response_pretty'],
        'response_json_valid' => $decodedResponse['response_json_valid'],
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     source_outputs:list<array<string,mixed>>,
 *     items:list<array{
 *         source_output_key:string,
 *         source_output_name:string,
 *         release_target_type:string,
 *         proxy_base_url:string,
 *         source_name:string,
 *         function_name:string,
 *         display_name:string,
 *         endpoint_slug:string,
 *         endpoint_filename:string,
 *         function_list_order:string,
 *         action_type:string,
 *         resolved:bool,
 *         signature:string,
 *         line:int,
 *         resolution_error:string,
 *         auth_policy:array{
 *             raw_auth_type:string,
 *             raw_auth_type_caption:string,
 *             resolved_auth_type:string,
 *             resolved_auth_type_caption:string,
 *             strategy_caption:string,
 *             summary:string,
 *             is_valid:bool
 *         }
 *     }>,
 *     error:string
 * }
 */
function app_endpoint_test_single_proxy_candidate_catalog(array $app, string $projectKey): array
{
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'source_outputs' => [],
            'items' => [],
            'error' => $catalogResult['error'],
        ];
    }

    $sourceOutputs = array_values(array_filter(
        $catalogResult['items'],
        static function (array $sourceOutput): bool {
            return app_source_output_target_binding_scope($sourceOutput) === 'single-function-proxy'
                && in_array((string) ($sourceOutput['artifact_strategy'] ?? ''), ['single-proxy-server'], true);
        },
    ));

    $items = [];
    foreach ($sourceOutputs as $sourceOutput) {
        $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
        if ($sourceOutputKey === '') {
            continue;
        }

        $buildPlanResult = app_single_proxy_build_plan_for_source_output($app, $projectKey, $sourceOutputKey);
        if (!$buildPlanResult['ok'] || !is_array($buildPlanResult['plan'])) {
            return [
                'ok' => false,
                'source_outputs' => $sourceOutputs,
                'items' => [],
                'error' => $buildPlanResult['error'],
            ];
        }

        foreach ($buildPlanResult['plan']['items'] as $item) {
            $items[] = [
                'source_output_key' => $sourceOutputKey,
                'source_output_name' => (string) ($sourceOutput['name'] ?? $sourceOutputKey),
                'release_target_type' => (string) ($sourceOutput['release_target_type'] ?? ''),
                'proxy_base_url' => (string) ($sourceOutput['proxy_base_url'] ?? ''),
                'source_name' => (string) ($item['source_name'] ?? ''),
                'function_name' => (string) ($item['function_name'] ?? ''),
                'display_name' => (string) ($item['display_name'] ?? ''),
                'endpoint_slug' => (string) ($item['endpoint_slug'] ?? ''),
                'endpoint_filename' => app_endpoint_test_endpoint_filename(
                    (string) ($item['source_name'] ?? ''),
                    (string) ($item['function_name'] ?? ''),
                ),
                'function_list_order' => (string) ($item['function_list_order'] ?? ''),
                'action_type' => (string) ($item['action_type'] ?? ''),
                'resolved' => (bool) ($item['resolved'] ?? false),
                'signature' => (string) ($item['signature'] ?? ''),
                'line' => (int) ($item['line'] ?? 0),
                'resolution_error' => (string) ($item['resolution_error'] ?? ''),
                'auth_policy' => is_array($item['auth_policy'] ?? null) ? $item['auth_policy'] : [
                    'raw_auth_type' => '',
                    'raw_auth_type_caption' => '',
                    'resolved_auth_type' => '',
                    'resolved_auth_type_caption' => '',
                    'strategy_caption' => '',
                    'summary' => '',
                    'is_valid' => false,
                ],
            ];
        }
    }

    usort(
        $items,
        static function (array $left, array $right): int {
            $leftKey = $left['source_output_key'] . "\n" . $left['function_list_order'] . "\n" . $left['display_name'];
            $rightKey = $right['source_output_key'] . "\n" . $right['function_list_order'] . "\n" . $right['display_name'];

            return strcmp($leftKey, $rightKey);
        },
    );

    return [
        'ok' => true,
        'source_outputs' => $sourceOutputs,
        'items' => $items,
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     source_output_name:string,
 *     release_target_type:string,
 *     proxy_base_url:string,
 *     source_name:string,
 *     function_name:string,
 *     display_name:string,
 *     endpoint_slug:string,
 *     endpoint_filename:string,
 *     function_list_order:string,
 *     action_type:string,
 *     resolved:bool,
 *     signature:string,
 *     line:int,
 *     resolution_error:string,
 *     auth_policy:array{
 *         raw_auth_type:string,
 *         raw_auth_type_caption:string,
 *         resolved_auth_type:string,
 *         resolved_auth_type_caption:string,
 *         strategy_caption:string,
 *         summary:string,
 *         is_valid:bool
 *     }
 * }> $candidates
 * @return array<string,mixed>|null
 */
function app_endpoint_test_find_candidate(
    array $candidates,
    string $sourceOutputKey,
    string $dbAccessKey,
    string $functionKey,
): ?array {
    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);
    $normalizedDbAccessKey = trim($dbAccessKey);
    $normalizedFunctionKey = trim($functionKey);
    foreach ($candidates as $candidate) {
        if (
            $candidate['source_output_key'] === $normalizedSourceOutputKey
            && $candidate['source_name'] === $normalizedDbAccessKey
            && $candidate['function_name'] === $normalizedFunctionKey
        ) {
            return $candidate;
        }
    }

    return null;
}

/**
 * @param array{
 *     endpoint_label:string,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_name:string,
 *     function_name:string,
 *     endpoint_filename:string,
 *     endpoint_url:string,
 *     base_url:string,
 *     request_json:string,
 *     request_json_pretty:string,
 *     http_code:int,
 *     status:string,
 *     error_message:string,
 *     response_json_valid:bool,
 *     request_snapshot_relative_path:string,
 *     response_snapshot_relative_path:string,
 *     response_pretty_snapshot_relative_path:string,
 *     request_bytes:int,
 *     response_bytes:int,
 *     created_at:string,
 *     requested_by:string
 * } $item
 * @return array<mixed>
 */
function app_endpoint_test_job_manifest_from_item(array $item, string $jobKey, string $projectKey): array
{
    return [
        'schema_version' => 1,
        'artifact_type' => 'endpoint-test-job',
        'job_key' => $jobKey,
        'project_key' => $projectKey,
        'endpoint_label' => $item['endpoint_label'],
        'source_output_key' => $item['source_output_key'],
        'source_output_name' => $item['source_output_name'],
        'source_name' => $item['source_name'],
        'function_name' => $item['function_name'],
        'endpoint_filename' => $item['endpoint_filename'],
        'endpoint_url' => $item['endpoint_url'],
        'base_url' => $item['base_url'],
        'request_json' => $item['request_json'],
        'request_json_pretty' => $item['request_json_pretty'],
        'http_code' => $item['http_code'],
        'status' => $item['status'],
        'error_message' => $item['error_message'],
        'response_json_valid' => $item['response_json_valid'],
        'request_snapshot_relative_path' => $item['request_snapshot_relative_path'],
        'response_snapshot_relative_path' => $item['response_snapshot_relative_path'],
        'response_pretty_snapshot_relative_path' => $item['response_pretty_snapshot_relative_path'],
        'request_bytes' => $item['request_bytes'],
        'response_bytes' => $item['response_bytes'],
        'created_at' => $item['created_at'],
        'requested_by' => $item['requested_by'],
    ];
}

/**
 * @param array{
 *     source_output_key?:string,
 *     source_output_name?:string,
 *     source_name?:string,
 *     function_name?:string,
 *     endpoint_filename?:string,
 *     endpoint_url:string,
 *     base_url?:string,
 *     request_json:string,
 *     request_json_pretty:string,
 *     endpoint_label?:string
 * } $requestDefinition
 * @return array{
 *     ok:bool,
 *     job:array{
 *         job_key:string,
 *         project_key:string,
 *         endpoint_label:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_name:string,
 *         function_name:string,
 *         endpoint_filename:string,
 *         endpoint_url:string,
 *         base_url:string,
 *         request_json:string,
 *         request_json_pretty:string,
 *         request_body:string,
 *         response_body:string,
 *         response_pretty:string,
 *         http_code:int,
 *         status:string,
 *         error_message:string,
 *         response_json_valid:bool,
 *         request_snapshot_relative_path:string,
 *         response_snapshot_relative_path:string,
 *         response_pretty_snapshot_relative_path:string,
 *         request_snapshot_path:string,
 *         response_snapshot_path:string,
 *         response_pretty_snapshot_path:string,
 *         request_bytes:int,
 *         response_bytes:int,
 *         created_at:string,
 *         requested_by:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_endpoint_test_job_create(
    array $app,
    string $projectKey,
    array $requestDefinition,
    string $requestedBy = 'system',
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'job' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }

    $validatedRequestJson = app_endpoint_test_validate_request_json($requestDefinition['request_json'] ?? '');
    if (!$validatedRequestJson['ok']) {
        return [
            'ok' => false,
            'job' => null,
            'error' => $validatedRequestJson['error'],
        ];
    }

    $endpointUrlResult = app_endpoint_test_resolve_endpoint_url(
        (string) ($requestDefinition['endpoint_url'] ?? ''),
        (string) ($requestDefinition['base_url'] ?? ''),
        (string) ($requestDefinition['endpoint_filename'] ?? ''),
    );
    if (!$endpointUrlResult['ok']) {
        return [
            'ok' => false,
            'job' => null,
            'error' => $endpointUrlResult['error'],
        ];
    }

    $executionResult = app_endpoint_test_execute_request(
        $endpointUrlResult['endpoint_url'],
        $validatedRequestJson['pretty_json'],
    );

    $jobKey = app_endpoint_test_new_job_key();
    $jobDir = app_endpoint_test_job_storage_root($app, $normalizedProjectKey) . '/' . $jobKey;
    $manifestPath = $jobDir . '/manifest.json';
    $requestSnapshotRelativePath = 'request/request.json';
    $responseSnapshotRelativePath = 'response/response.txt';
    $responsePrettySnapshotRelativePath = 'response/response.pretty.json';
    $requestSnapshotPath = $jobDir . '/' . $requestSnapshotRelativePath;
    $responseSnapshotPath = $jobDir . '/' . $responseSnapshotRelativePath;
    $responsePrettySnapshotPath = $jobDir . '/' . $responsePrettySnapshotRelativePath;
    $status = $executionResult['ok'] ? 'completed' : 'failed';

    $sourceOutputKey = app_normalize_source_output_key((string) ($requestDefinition['source_output_key'] ?? ''));
    if ($sourceOutputKey !== '' && !app_source_output_key_is_valid($sourceOutputKey)) {
        $sourceOutputKey = '';
    }

    $sourceOutputName = trim((string) ($requestDefinition['source_output_name'] ?? ''));
    if ($sourceOutputName === '') {
        $sourceOutputName = $sourceOutputKey;
    }

    $sourceName = trim((string) ($requestDefinition['source_name'] ?? ''));
    $functionName = trim((string) ($requestDefinition['function_name'] ?? ''));
    $endpointFilename = trim((string) ($requestDefinition['endpoint_filename'] ?? ''));
    $endpointLabel = trim((string) ($requestDefinition['endpoint_label'] ?? ''));
    if ($endpointLabel === '') {
        if ($sourceName !== '' && $functionName !== '') {
            $endpointLabel = $sourceName . '.' . $functionName;
        } elseif ($endpointFilename !== '') {
            $endpointLabel = $endpointFilename;
        } else {
            $endpointLabel = $endpointUrlResult['endpoint_url'];
        }
    }

    $createdAt = date(DATE_ATOM);
    $normalizedRequestedBy = app_endpoint_test_normalize_requested_by($requestedBy);
    $responsePretty = $executionResult['response_json_valid']
        ? $executionResult['response_pretty']
        : '';

    try {
        app_endpoint_test_ensure_directory(dirname($requestSnapshotPath));
        app_endpoint_test_ensure_directory(dirname($responseSnapshotPath));

        if (file_put_contents($requestSnapshotPath, $validatedRequestJson['pretty_json'] . PHP_EOL) === false) {
            throw new RuntimeException('request snapshot の保存に失敗しました。');
        }
        if (file_put_contents($responseSnapshotPath, $executionResult['response_body']) === false) {
            throw new RuntimeException('response snapshot の保存に失敗しました。');
        }
        if ($responsePretty !== '') {
            if (file_put_contents($responsePrettySnapshotPath, $responsePretty . PHP_EOL) === false) {
                throw new RuntimeException('formatted response snapshot の保存に失敗しました。');
            }
        }

        $job = [
            'job_key' => $jobKey,
            'project_key' => $normalizedProjectKey,
            'endpoint_label' => $endpointLabel,
            'source_output_key' => $sourceOutputKey,
            'source_output_name' => $sourceOutputName,
            'source_name' => $sourceName,
            'function_name' => $functionName,
            'endpoint_filename' => $endpointFilename,
            'endpoint_url' => $endpointUrlResult['endpoint_url'],
            'base_url' => trim((string) ($requestDefinition['base_url'] ?? '')),
            'request_json' => (string) ($requestDefinition['request_json'] ?? ''),
            'request_json_pretty' => $validatedRequestJson['pretty_json'],
            'request_body' => $validatedRequestJson['pretty_json'],
            'response_body' => $executionResult['response_body'],
            'response_pretty' => $responsePretty,
            'http_code' => $executionResult['http_code'],
            'status' => $status,
            'error_message' => $executionResult['error'],
            'response_json_valid' => $executionResult['response_json_valid'],
            'request_snapshot_relative_path' => $requestSnapshotRelativePath,
            'response_snapshot_relative_path' => $responseSnapshotRelativePath,
            'response_pretty_snapshot_relative_path' => $responsePretty !== '' ? $responsePrettySnapshotRelativePath : '',
            'request_snapshot_path' => $requestSnapshotPath,
            'response_snapshot_path' => $responseSnapshotPath,
            'response_pretty_snapshot_path' => $responsePretty !== '' ? $responsePrettySnapshotPath : '',
            'request_bytes' => is_file($requestSnapshotPath) ? (int) filesize($requestSnapshotPath) : 0,
            'response_bytes' => is_file($responseSnapshotPath) ? (int) filesize($responseSnapshotPath) : 0,
            'created_at' => $createdAt,
            'requested_by' => $normalizedRequestedBy,
            'job_dir' => $jobDir,
            'manifest_path' => $manifestPath,
        ];

        app_endpoint_test_write_json_file(
            $manifestPath,
            app_endpoint_test_job_manifest_from_item($job, $jobKey, $normalizedProjectKey),
        );

        return [
            'ok' => true,
            'job' => $job,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        app_endpoint_test_delete_tree($jobDir);

        return [
            'ok' => false,
            'job' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array<mixed>|null
 */
function app_endpoint_test_job_read_manifest(string $manifestPath): ?array
{
    if (!is_file($manifestPath)) {
        return null;
    }

    $json = file_get_contents($manifestPath);
    if (!is_string($json) || $json === '') {
        return null;
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return null;
    }

    return $decoded;
}

/**
 * @param array<mixed> $manifest
 * @return array{
 *     job_key:string,
 *     project_key:string,
 *     endpoint_label:string,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_name:string,
 *     function_name:string,
 *     endpoint_filename:string,
 *     endpoint_url:string,
 *     base_url:string,
 *     request_json:string,
 *     request_json_pretty:string,
 *     request_body:string,
 *     response_body:string,
 *     response_pretty:string,
 *     http_code:int,
 *     status:string,
 *     error_message:string,
 *     response_json_valid:bool,
 *     request_snapshot_relative_path:string,
 *     response_snapshot_relative_path:string,
 *     response_pretty_snapshot_relative_path:string,
 *     request_snapshot_path:string,
 *     response_snapshot_path:string,
 *     response_pretty_snapshot_path:string,
 *     request_bytes:int,
 *     response_bytes:int,
 *     created_at:string,
 *     requested_by:string,
 *     job_dir:string,
 *     manifest_path:string
 * }|null
 */
function app_endpoint_test_job_item_from_manifest(string $jobDir, array $manifest): ?array
{
    $jobKey = $manifest['job_key'] ?? null;
    $projectKey = $manifest['project_key'] ?? null;
    $endpointLabel = $manifest['endpoint_label'] ?? null;
    $sourceOutputKey = $manifest['source_output_key'] ?? null;
    $sourceOutputName = $manifest['source_output_name'] ?? null;
    $sourceName = $manifest['source_name'] ?? null;
    $functionName = $manifest['function_name'] ?? null;
    $endpointFilename = $manifest['endpoint_filename'] ?? null;
    $endpointUrl = $manifest['endpoint_url'] ?? null;
    $baseUrl = $manifest['base_url'] ?? null;
    $requestJson = $manifest['request_json'] ?? null;
    $requestJsonPretty = $manifest['request_json_pretty'] ?? null;
    $httpCode = $manifest['http_code'] ?? null;
    $status = $manifest['status'] ?? null;
    $errorMessage = $manifest['error_message'] ?? null;
    $responseJsonValid = $manifest['response_json_valid'] ?? null;
    $requestSnapshotRelativePath = $manifest['request_snapshot_relative_path'] ?? null;
    $responseSnapshotRelativePath = $manifest['response_snapshot_relative_path'] ?? null;
    $responsePrettySnapshotRelativePath = $manifest['response_pretty_snapshot_relative_path'] ?? null;
    $requestBytes = $manifest['request_bytes'] ?? null;
    $responseBytes = $manifest['response_bytes'] ?? null;
    $createdAt = $manifest['created_at'] ?? null;
    $requestedBy = $manifest['requested_by'] ?? null;

    if (
        !is_string($jobKey) || !app_endpoint_test_job_key_is_valid($jobKey)
        || !is_string($projectKey) || !app_project_key_is_valid($projectKey)
        || !is_string($endpointLabel) || $endpointLabel === ''
        || !is_string($sourceOutputKey)
        || !is_string($sourceOutputName)
        || !is_string($sourceName)
        || !is_string($functionName)
        || !is_string($endpointFilename)
        || !is_string($endpointUrl) || $endpointUrl === ''
        || !is_string($baseUrl)
        || !is_string($requestJson)
        || !is_string($requestJsonPretty)
        || !is_int($httpCode)
        || !is_string($status) || $status === ''
        || !is_string($errorMessage)
        || !is_bool($responseJsonValid)
        || !is_string($requestSnapshotRelativePath) || $requestSnapshotRelativePath === ''
        || !is_string($responseSnapshotRelativePath) || $responseSnapshotRelativePath === ''
        || !is_string($responsePrettySnapshotRelativePath)
        || !is_int($requestBytes)
        || !is_int($responseBytes)
        || !is_string($createdAt) || $createdAt === ''
        || !is_string($requestedBy) || $requestedBy === ''
    ) {
        return null;
    }

    $requestSnapshotPath = $jobDir . '/' . $requestSnapshotRelativePath;
    $responseSnapshotPath = $jobDir . '/' . $responseSnapshotRelativePath;
    $responsePrettySnapshotPath = $responsePrettySnapshotRelativePath !== ''
        ? ($jobDir . '/' . $responsePrettySnapshotRelativePath)
        : '';
    $requestBody = '';
    $responseBody = '';
    $responsePretty = '';

    if (is_file($requestSnapshotPath)) {
        $snapshot = file_get_contents($requestSnapshotPath);
        if (is_string($snapshot)) {
            $requestBody = $snapshot;
        }
    }
    if (is_file($responseSnapshotPath)) {
        $snapshot = file_get_contents($responseSnapshotPath);
        if (is_string($snapshot)) {
            $responseBody = $snapshot;
        }
    }
    if ($responsePrettySnapshotPath !== '' && is_file($responsePrettySnapshotPath)) {
        $snapshot = file_get_contents($responsePrettySnapshotPath);
        if (is_string($snapshot)) {
            $responsePretty = $snapshot;
        }
    }

    return [
        'job_key' => $jobKey,
        'project_key' => $projectKey,
        'endpoint_label' => $endpointLabel,
        'source_output_key' => $sourceOutputKey,
        'source_output_name' => $sourceOutputName,
        'source_name' => $sourceName,
        'function_name' => $functionName,
        'endpoint_filename' => $endpointFilename,
        'endpoint_url' => $endpointUrl,
        'base_url' => $baseUrl,
        'request_json' => $requestJson,
        'request_json_pretty' => $requestJsonPretty,
        'request_body' => $requestBody,
        'response_body' => $responseBody,
        'response_pretty' => $responsePretty,
        'http_code' => $httpCode,
        'status' => $status,
        'error_message' => $errorMessage,
        'response_json_valid' => $responseJsonValid,
        'request_snapshot_relative_path' => $requestSnapshotRelativePath,
        'response_snapshot_relative_path' => $responseSnapshotRelativePath,
        'response_pretty_snapshot_relative_path' => $responsePrettySnapshotRelativePath,
        'request_snapshot_path' => $requestSnapshotPath,
        'response_snapshot_path' => $responseSnapshotPath,
        'response_pretty_snapshot_path' => $responsePrettySnapshotPath,
        'request_bytes' => $requestBytes,
        'response_bytes' => $responseBytes,
        'created_at' => $createdAt,
        'requested_by' => $requestedBy,
        'job_dir' => $jobDir,
        'manifest_path' => $jobDir . '/manifest.json',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         job_key:string,
 *         project_key:string,
 *         endpoint_label:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_name:string,
 *         function_name:string,
 *         endpoint_filename:string,
 *         endpoint_url:string,
 *         base_url:string,
 *         request_json:string,
 *         request_json_pretty:string,
 *         request_body:string,
 *         response_body:string,
 *         response_pretty:string,
 *         http_code:int,
 *         status:string,
 *         error_message:string,
 *         response_json_valid:bool,
 *         request_snapshot_relative_path:string,
 *         response_snapshot_relative_path:string,
 *         response_pretty_snapshot_relative_path:string,
 *         request_snapshot_path:string,
 *         response_snapshot_path:string,
 *         response_pretty_snapshot_path:string,
 *         request_bytes:int,
 *         response_bytes:int,
 *         created_at:string,
 *         requested_by:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }|null,
 *     error:string
 * }
 */
function app_endpoint_test_job_find(array $app, string $jobKey): array
{
    if (!app_endpoint_test_job_key_is_valid($jobKey)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'job key の形式が不正です。',
        ];
    }

    $storageRoot = app_endpoint_test_job_storage_root($app);
    if (!is_dir($storageRoot)) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    $projectEntries = scandir($storageRoot);
    if ($projectEntries === false) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'endpoint test job storage の読み込みに失敗しました。',
        ];
    }

    foreach ($projectEntries as $projectEntry) {
        if ($projectEntry === '.' || $projectEntry === '..') {
            continue;
        }

        $projectDir = $storageRoot . '/' . $projectEntry;
        if (!is_dir($projectDir)) {
            continue;
        }

        $jobDir = $projectDir . '/' . $jobKey;
        if (!is_dir($jobDir)) {
            continue;
        }

        $manifest = app_endpoint_test_job_read_manifest($jobDir . '/manifest.json');
        if ($manifest === null) {
            continue;
        }

        $item = app_endpoint_test_job_item_from_manifest($jobDir, $manifest);
        if ($item === null) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'endpoint test job manifest の形式が不正です。',
            ];
        }

        if ($item['job_key'] !== $jobKey) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'endpoint test job manifest と要求 path が一致しません。',
            ];
        }

        return [
            'ok' => true,
            'item' => $item,
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'item' => null,
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         job_key:string,
 *         project_key:string,
 *         endpoint_label:string,
 *         source_output_key:string,
 *         source_output_name:string,
 *         source_name:string,
 *         function_name:string,
 *         endpoint_filename:string,
 *         endpoint_url:string,
 *         base_url:string,
 *         request_json:string,
 *         request_json_pretty:string,
 *         request_body:string,
 *         response_body:string,
 *         response_pretty:string,
 *         http_code:int,
 *         status:string,
 *         error_message:string,
 *         response_json_valid:bool,
 *         request_snapshot_relative_path:string,
 *         response_snapshot_relative_path:string,
 *         response_pretty_snapshot_relative_path:string,
 *         request_snapshot_path:string,
 *         response_snapshot_path:string,
 *         response_pretty_snapshot_path:string,
 *         request_bytes:int,
 *         response_bytes:int,
 *         created_at:string,
 *         requested_by:string,
 *         job_dir:string,
 *         manifest_path:string
 *     }>,
 *     error:string
 * }
 */
function app_endpoint_test_job_list(array $app, ?string $projectKey = null, int $limit = 20): array
{
    $storageRoot = app_endpoint_test_job_storage_root($app);
    if (!is_dir($storageRoot)) {
        return [
            'ok' => true,
            'items' => [],
            'error' => '',
        ];
    }

    $projectDirs = [];
    if ($projectKey !== null && trim($projectKey) !== '') {
        $normalizedProjectKey = app_normalize_project_key($projectKey);
        if (!app_project_key_is_valid($normalizedProjectKey)) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'project key の形式が不正です。',
            ];
        }

        $projectDir = app_endpoint_test_job_storage_root($app, $normalizedProjectKey);
        if (!is_dir($projectDir)) {
            return [
                'ok' => true,
                'items' => [],
                'error' => '',
            ];
        }

        $projectDirs[] = $projectDir;
    } else {
        $entries = scandir($storageRoot);
        if ($entries === false) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'endpoint test job storage の読み込みに失敗しました。',
            ];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $projectDir = $storageRoot . '/' . $entry;
            if (is_dir($projectDir)) {
                $projectDirs[] = $projectDir;
            }
        }
    }

    $items = [];
    foreach ($projectDirs as $projectDir) {
        $entries = scandir($projectDir);
        if ($entries === false) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'endpoint test project storage の読み込みに失敗しました。',
            ];
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $jobDir = $projectDir . '/' . $entry;
            if (!is_dir($jobDir)) {
                continue;
            }

            $manifest = app_endpoint_test_job_read_manifest($jobDir . '/manifest.json');
            if ($manifest === null) {
                continue;
            }

            $item = app_endpoint_test_job_item_from_manifest($jobDir, $manifest);
            if ($item === null) {
                continue;
            }

            $items[] = $item;
        }
    }

    usort(
        $items,
        static fn (array $left, array $right): int => strcmp($right['job_key'], $left['job_key']),
    );

    if ($limit > 0 && count($items) > $limit) {
        $items = array_slice($items, 0, $limit);
    }

    return [
        'ok' => true,
        'items' => $items,
        'error' => '',
    ];
}
