<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_output_service.php';

function app_lab_swagger_path(string $projectKey, array $query = []): string
{
    $path = '/runs/swagger/' . rawurlencode(app_normalize_project_key($projectKey));
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

/**
 * @param array<string,mixed> $sourceOutput
 */
function app_lab_swagger_supports_source_output(array $sourceOutput): bool
{
    if (trim((string) ($sourceOutput['artifact_strategy'] ?? '')) !== 'openapi-json') {
        return false;
    }

    return app_source_output_effective_spec_visibility($sourceOutput) !== 'disabled';
}

/**
 * @param list<array<string,mixed>> $catalog
 * @return list<array<string,mixed>>
 */
function app_lab_swagger_supported_source_outputs(array $catalog): array
{
    return array_values(array_filter(
        $catalog,
        static fn (array $sourceOutput): bool => app_lab_swagger_supports_source_output($sourceOutput),
    ));
}

/**
 * @return list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     supports_proxy_runtime_read:bool
 * }>
 */
function app_lab_swagger_runtime_database_source_options(array $app): array
{
    $options = [];
    foreach (app_database_source_proxy_runtime_candidates($app) as $source) {
        $key = trim((string) ($source['key'] ?? ''));
        if ($key === '') {
            continue;
        }

        $options[] = [
            'key' => $key,
            'label' => trim((string) ($source['label'] ?? $key)),
            'description' => trim((string) ($source['description'] ?? '')),
            'proxy_runtime_priority' => (int) ($source['proxy_runtime_priority'] ?? 1000),
            'is_canonical_store' => (bool) ($source['is_canonical_store'] ?? false),
            'supports_proxy_runtime_read' => (bool) ($source['supports_proxy_runtime_read'] ?? false),
        ];
    }

    return $options;
}

/**
 * @param list<array{
 *     key:string,
 *     label:string,
 *     description:string,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     supports_proxy_runtime_read:bool
 * }> $options
 * @return array{
 *     selected_key:string,
 *     selected_source:array{
 *         key:string,
 *         label:string,
 *         description:string,
 *         proxy_runtime_priority:int,
 *         is_canonical_store:bool,
 *         supports_proxy_runtime_read:bool
 *     }|null,
 *     notice:string
 * }
 */
function app_lab_swagger_resolve_runtime_database_source_selection(
    array $app,
    string $requestedKey,
    array $options = [],
): array {
    $normalizedRequestedKey = trim($requestedKey);
    if ($normalizedRequestedKey === '') {
        return [
            'selected_key' => '',
            'selected_source' => null,
            'notice' => '',
        ];
    }

    $optionsByKey = [];
    foreach ($options !== [] ? $options : app_lab_swagger_runtime_database_source_options($app) as $option) {
        $optionKey = trim((string) ($option['key'] ?? ''));
        if ($optionKey === '') {
            continue;
        }

        $optionsByKey[$optionKey] = $option;
    }

    if (isset($optionsByKey[$normalizedRequestedKey])) {
        return [
            'selected_key' => $normalizedRequestedKey,
            'selected_source' => $optionsByKey[$normalizedRequestedKey],
            'notice' => '',
        ];
    }

    if (!app_database_source_exists($app, $normalizedRequestedKey)) {
        return [
            'selected_key' => '',
            'selected_source' => null,
            'notice' => '指定した `db_source_key` は database source catalog に見つかりません。auto-select に戻します。',
        ];
    }

    return [
        'selected_key' => '',
        'selected_source' => null,
        'notice' => '指定した `db_source_key` は proxy runtime candidate ではありません。`supports_proxy_runtime_read=1` の source を選ぶか auto-select に戻してください。',
    ];
}

function app_lab_swagger_pretty_json(mixed $value): string
{
    $json = json_encode(
        $value,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        return "{}\n";
    }

    return $json . PHP_EOL;
}

/**
 * JSON を associative array として decode すると空 object example が空配列へ潰れる。
 * viewer の request textarea では object/array の区別を維持する。
 *
 * @param array<string,mixed>|null $schema
 */
function app_lab_swagger_normalize_example_for_schema(mixed $example, ?array $schema = null): mixed
{
    if (!is_array($schema)) {
        return $example;
    }

    $schemaType = trim((string) ($schema['type'] ?? ''));
    $properties = is_array($schema['properties'] ?? null) ? $schema['properties'] : [];
    $hasObjectShape = $schemaType === 'object' || $properties !== [] || array_key_exists('additionalProperties', $schema);
    if ($hasObjectShape) {
        if (!is_array($example)) {
            return $example;
        }

        if ($example === []) {
            return (object) [];
        }

        $normalizedObject = [];
        foreach ($example as $propertyName => $propertyValue) {
            $propertySchema = is_string($propertyName) && is_array($properties[$propertyName] ?? null)
                ? $properties[$propertyName]
                : null;
            $normalizedObject[$propertyName] = app_lab_swagger_normalize_example_for_schema(
                $propertyValue,
                $propertySchema,
            );
        }

        return $normalizedObject;
    }

    if ($schemaType === 'array' && is_array($example)) {
        $itemSchema = is_array($schema['items'] ?? null) ? $schema['items'] : null;
        $normalizedArray = [];
        foreach ($example as $item) {
            $normalizedArray[] = app_lab_swagger_normalize_example_for_schema($item, $itemSchema);
        }

        return $normalizedArray;
    }

    return $example;
}

/**
 * @return array{
 *     ok:bool,
 *     spec:array<string,mixed>|null,
 *     spec_json:string,
 *     spec_path:string,
 *     spec_source:string,
 *     artifact:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_lab_swagger_read_spec_file(
    string $specPath,
    string $specSource,
    ?array $artifact = null,
): array {
    if (!is_file($specPath)) {
        return [
            'ok' => false,
            'spec' => null,
            'spec_json' => '',
            'spec_path' => $specPath,
            'spec_source' => $specSource,
            'artifact' => $artifact,
            'error' => 'spec file が見つかりません。',
        ];
    }

    $json = file_get_contents($specPath);
    if (!is_string($json) || $json === '') {
        return [
            'ok' => false,
            'spec' => null,
            'spec_json' => '',
            'spec_path' => $specPath,
            'spec_source' => $specSource,
            'artifact' => $artifact,
            'error' => 'spec file の読み込みに失敗しました。',
        ];
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
        return [
            'ok' => false,
            'spec' => null,
            'spec_json' => $json,
            'spec_path' => $specPath,
            'spec_source' => $specSource,
            'artifact' => $artifact,
            'error' => 'OpenAPI JSON の解析に失敗しました: ' . json_last_error_msg(),
        ];
    }

    return [
        'ok' => true,
        'spec' => $decoded,
        'spec_json' => $json,
        'spec_path' => $specPath,
        'spec_source' => $specSource,
        'artifact' => $artifact,
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $sourceOutput
 * @return array{
 *     ok:bool,
 *     spec:array<string,mixed>|null,
 *     spec_json:string,
 *     spec_path:string,
 *     spec_source:string,
 *     artifact:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_lab_swagger_resolve_spec(
    array $app,
    string $projectKey,
    array $sourceOutput,
    string $artifactKey = '',
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $sourceOutputKey = app_normalize_source_output_key((string) ($sourceOutput['source_output_key'] ?? ''));
    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'spec' => null,
            'spec_json' => '',
            'spec_path' => '',
            'spec_source' => '',
            'artifact' => null,
            'error' => 'project key の形式が不正です。',
        ];
    }
    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'spec' => null,
            'spec_json' => '',
            'spec_path' => '',
            'spec_source' => '',
            'artifact' => null,
            'error' => 'source output key の形式が不正です。',
        ];
    }

    $candidates = [];
    $normalizedArtifactKey = trim($artifactKey);
    if ($normalizedArtifactKey !== '') {
        if (!app_project_output_artifact_key_is_valid($normalizedArtifactKey)) {
            return [
                'ok' => false,
                'spec' => null,
                'spec_json' => '',
                'spec_path' => '',
                'spec_source' => '',
                'artifact' => null,
                'error' => 'artifact key の形式が不正です。',
            ];
        }

        $artifactResult = app_project_output_find($app, $normalizedProjectKey, $normalizedArtifactKey);
        if (!$artifactResult['ok']) {
            return [
                'ok' => false,
                'spec' => null,
                'spec_json' => '',
                'spec_path' => '',
                'spec_source' => '',
                'artifact' => null,
                'error' => $artifactResult['error'],
            ];
        }
        if (!is_array($artifactResult['item'] ?? null)) {
            return [
                'ok' => false,
                'spec' => null,
                'spec_json' => '',
                'spec_path' => '',
                'spec_source' => '',
                'artifact' => null,
                'error' => '指定した artifact が見つかりません。',
            ];
        }
        if (($artifactResult['item']['source_output_key'] ?? '') !== $sourceOutputKey) {
            return [
                'ok' => false,
                'spec' => null,
                'spec_json' => '',
                'spec_path' => '',
                'spec_source' => '',
                'artifact' => null,
                'error' => '指定した artifact は選択中 source output に属していません。',
            ];
        }

        $candidates[] = [
            'spec_path' => app_project_output_artifact_bundle_runtime_root($artifactResult['item']) . '/openapi.json',
            'spec_source' => 'artifact-bundle',
            'artifact' => $artifactResult['item'],
        ];
    } else {
        $sourceOutputDir = trim(str_replace('\\', '/', (string) ($sourceOutput['source_output_dir'] ?? '')));
        if ($sourceOutputDir !== '' && app_project_output_relative_path_is_safe($sourceOutputDir)) {
            $candidates[] = [
                'spec_path' => app_project_output_workspace_path_from_relative($sourceOutputDir) . '/openapi.json',
                'spec_source' => 'published-output',
                'artifact' => null,
            ];
        }

        $artifactListResult = app_project_output_list($app, $normalizedProjectKey, $sourceOutputKey);
        if (!$artifactListResult['ok']) {
            return [
                'ok' => false,
                'spec' => null,
                'spec_json' => '',
                'spec_path' => '',
                'spec_source' => '',
                'artifact' => null,
                'error' => $artifactListResult['error'],
            ];
        }

        $latestArtifact = $artifactListResult['items'][0] ?? null;
        if (is_array($latestArtifact)) {
            $candidates[] = [
                'spec_path' => app_project_output_artifact_bundle_runtime_root($latestArtifact) . '/openapi.json',
                'spec_source' => 'artifact-bundle',
                'artifact' => $latestArtifact,
            ];
        }
    }

    $errors = [];
    foreach ($candidates as $candidate) {
        $result = app_lab_swagger_read_spec_file(
            (string) ($candidate['spec_path'] ?? ''),
            (string) ($candidate['spec_source'] ?? ''),
            is_array($candidate['artifact'] ?? null) ? $candidate['artifact'] : null,
        );
        if ($result['ok']) {
            return $result;
        }

        if ($result['error'] !== '') {
            $errors[] = $result['spec_source'] . ': ' . $result['error'];
        }
    }

    return [
        'ok' => false,
        'spec' => null,
        'spec_json' => '',
        'spec_path' => '',
        'spec_source' => '',
        'artifact' => null,
        'error' => $errors !== []
            ? implode(' / ', $errors)
            : '利用可能な openapi.json がまだありません。artifact を生成してください。',
    ];
}

/**
 * @return array{
 *     required_fields:list<string>,
 *     optional_fields:list<string>,
 *     notice:string
 * }
 */
function app_lab_swagger_auth_helper_descriptor(string $authStrategy): array
{
    $normalizedStrategy = trim($authStrategy);

    return match ($normalizedStrategy) {
        'project-token' => [
            'required_fields' => ['TOKEN'],
            'optional_fields' => [],
            'notice' => 'この operation は `TOKEN` が必要です。endpoint 側で `MTOOL_PROXY_PROJECT_TOKEN` が設定されている必要があります。Auth Helper に project token を入れると、request body に自動差し込みできます。',
        ],
        'project-token-or-get-function' => [
            'required_fields' => [],
            'optional_fields' => ['TOKEN'],
            'notice' => 'この operation は `TOKEN` を入れると project token 認証を優先します。token path を使う時は endpoint 側で `MTOOL_PROXY_PROJECT_TOKEN` が設定されている必要があります。空欄のまま送る場合は endpoint 側 get-function 認証に依存します。',
        ],
        'no-security' => [
            'required_fields' => [],
            'optional_fields' => [],
            'notice' => 'この operation は `NoSecurity` です。認証なしで request を送れます。',
        ],
        'login-cookie-token' => [
            'required_fields' => ['LOGIN_COOKIE_TOKEN'],
            'optional_fields' => [],
            'notice' => 'この operation は `LOGIN_COOKIE_TOKEN` が必要です。Auth Helper に入れると、request body に自動差し込みできます。',
        ],
        'static-bearer' => [
            'required_fields' => ['Authorization: Bearer'],
            'optional_fields' => [],
            'notice' => 'この operation は `Authorization: Bearer <token>` が必要です。request body に token は入りません。',
        ],
        default => [
            'required_fields' => [],
            'optional_fields' => [],
            'notice' => '',
        ],
    };
}

/**
 * @param list<array{
 *     auth_strategy:string
 * }> $operations
 * @return array{
 *     auth_operation_count:int,
 *     project_token_required_count:int,
 *     project_token_optional_count:int,
 *     login_cookie_token_required_count:int,
 *     static_bearer_required_count:int,
 *     requires_auth_helper:bool
 * }
 */
function app_lab_swagger_auth_helper_summary(array $operations): array
{
    $summary = [
        'auth_operation_count' => 0,
        'project_token_required_count' => 0,
        'project_token_optional_count' => 0,
        'login_cookie_token_required_count' => 0,
        'static_bearer_required_count' => 0,
        'requires_auth_helper' => false,
    ];

    foreach ($operations as $operation) {
        $authStrategy = trim((string) ($operation['auth_strategy'] ?? ''));
        $descriptor = app_lab_swagger_auth_helper_descriptor($authStrategy);

        if ($descriptor['required_fields'] === [] && $descriptor['optional_fields'] === []) {
            continue;
        }

        $summary['auth_operation_count']++;
        $summary['requires_auth_helper'] = true;

        if ($authStrategy === 'project-token') {
            $summary['project_token_required_count']++;
            continue;
        }

        if ($authStrategy === 'project-token-or-get-function') {
            $summary['project_token_optional_count']++;
            continue;
        }

        if ($authStrategy === 'login-cookie-token') {
            $summary['login_cookie_token_required_count']++;
            continue;
        }

        if ($authStrategy === 'static-bearer') {
            $summary['static_bearer_required_count']++;
        }
    }

    return $summary;
}

/**
 * @param array<string,mixed> $spec
 * @return list<array{
 *     method:string,
 *     path:string,
 *     operation_id:string,
 *     summary:string,
 *     description:string,
 *     source_name:string,
 *     function_name:string,
 *     auth_strategy:string,
 *     auth_notice:string,
 *     auth_required_fields:list<string>,
 *     auth_optional_fields:list<string>,
 *     input_kind:string,
 *     response_mode:string,
 *     request_example_pretty:string,
 *     success_example_pretty:string
 * }>
 */
function app_lab_swagger_operation_catalog(array $spec): array
{
    $operations = [];
    $paths = $spec['paths'] ?? null;
    if (!is_array($paths)) {
        return [];
    }

    foreach ($paths as $path => $methods) {
        if (!is_string($path) || !is_array($methods)) {
            continue;
        }

        foreach ($methods as $method => $operation) {
            if (!is_string($method) || !is_array($operation)) {
                continue;
            }

            $normalizedMethod = strtolower(trim($method));
            if (!in_array($normalizedMethod, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                continue;
            }

            $requestContent = is_array($operation['requestBody']['content']['application/json'] ?? null)
                ? $operation['requestBody']['content']['application/json']
                : [];
            $successContent = is_array($operation['responses']['200']['content']['application/json'] ?? null)
                ? $operation['responses']['200']['content']['application/json']
                : [];
            $requestExample = app_lab_swagger_normalize_example_for_schema(
                $requestContent['example'] ?? (object) [],
                is_array($requestContent['schema'] ?? null) ? $requestContent['schema'] : null,
            );
            $successExample = app_lab_swagger_normalize_example_for_schema(
                $successContent['example'] ?? (object) [],
                is_array($successContent['schema'] ?? null) ? $successContent['schema'] : null,
            );
            $meta = is_array($operation['x-mtool'] ?? null) ? $operation['x-mtool'] : [];
            $authStrategy = trim((string) ($meta['auth_strategy'] ?? ''));
            $authDescriptor = app_lab_swagger_auth_helper_descriptor($authStrategy);

            $operations[] = [
                'method' => strtoupper($normalizedMethod),
                'path' => $path,
                'operation_id' => trim((string) ($operation['operationId'] ?? '')),
                'summary' => trim((string) ($operation['summary'] ?? '')),
                'description' => trim((string) ($operation['description'] ?? '')),
                'source_name' => trim((string) ($meta['source_name'] ?? '')),
                'function_name' => trim((string) ($meta['function_name'] ?? '')),
                'auth_strategy' => $authStrategy,
                'auth_notice' => $authDescriptor['notice'],
                'auth_required_fields' => $authDescriptor['required_fields'],
                'auth_optional_fields' => $authDescriptor['optional_fields'],
                'input_kind' => trim((string) ($meta['input_kind'] ?? '')),
                'response_mode' => trim((string) ($meta['response_mode'] ?? '')),
                'request_example_pretty' => app_lab_swagger_pretty_json($requestExample),
                'success_example_pretty' => app_lab_swagger_pretty_json($successExample),
            ];
        }
    }

    usort(
        $operations,
        static function (array $left, array $right): int {
            $leftKey = $left['path'] . "\n" . $left['method'] . "\n" . $left['operation_id'];
            $rightKey = $right['path'] . "\n" . $right['method'] . "\n" . $right['operation_id'];

            return strcmp($leftKey, $rightKey);
        },
    );

    return $operations;
}

/**
 * @param array<string,mixed> $spec
 */
function app_lab_swagger_default_base_url(array $spec): string
{
    $servers = $spec['servers'] ?? null;
    if (!is_array($servers)) {
        return '';
    }

    foreach ($servers as $server) {
        if (!is_array($server)) {
            continue;
        }

        $url = trim((string) ($server['url'] ?? ''));
        if ($url !== '') {
            return $url;
        }
    }

    return '';
}
