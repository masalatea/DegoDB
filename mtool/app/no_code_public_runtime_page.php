<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/managed_operation_sync_outbox_repository_pdo.php';
require_once __DIR__ . '/no_code_managed_operation_bridge.php';
require_once __DIR__ . '/no_code_publish_candidate_repository_pdo.php';
require_once __DIR__ . '/no_code_runtime.php';
require_once __DIR__ . '/project_output_service.php';
require_once __DIR__ . '/response.php';

function app_no_code_public_runtime_preview_path(string $projectKey, string $artifactKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode($artifactKey)
        . '/runtime-preview.html';
}

function app_no_code_public_runtime_execution_path(string $projectKey, string $artifactKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/'
        . rawurlencode($artifactKey)
        . '/execute.json';
}

function app_no_code_public_runtime_current_preview_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/runtime-preview.html';
}

function app_no_code_public_runtime_current_execution_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/execute.json';
}

function app_no_code_public_runtime_alias_preview_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/runtime-preview.html';
}

function app_no_code_public_runtime_alias_execution_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/execute.json';
}

function app_no_code_public_runtime_artifact_cache_control(): string
{
    return 'public, max-age=31536000, immutable';
}

function app_no_code_public_runtime_current_cache_control(): string
{
    return 'no-store';
}

/**
 * @param array<string,mixed> $candidate
 * @return array<string,string>
 */
function app_no_code_public_runtime_execution_binding(string $projectKey, array $candidate): array
{
    $binding = [
        'csrf_token' => app_csrf_token(),
        'project_key' => app_normalize_project_key($projectKey),
        'artifact_key' => (string) ($candidate['artifact_key'] ?? ''),
        'source_output_key' => APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY,
    ];

    $revisionId = trim((string) ($candidate['revision_id'] ?? ''));
    if ($revisionId !== '') {
        $binding['revision_id'] = $revisionId;
    }

    return $binding;
}

/**
 * @param array<string,mixed> $candidate
 * @return array{ok:bool,definition:array<string,mixed>,error:string}
 */
function app_no_code_public_runtime_candidate_screen_definition(
    array $app,
    string $projectKey,
    array $candidate,
): array {
    $artifactKey = (string) ($candidate['artifact_key'] ?? '');
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact binding does not match',
        ];
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact was not found',
        ];
    }

    $artifact = $artifactResult['item'];
    if ($artifact['source_output_key'] !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact binding does not match',
        ];
    }

    try {
        $runtimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution artifact was not found',
        ];
    }

    $definitionPath = $runtimeRoot . '/screen-definition.json';
    if (!is_file($definitionPath)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution screen definition is missing',
        ];
    }

    $definition = json_decode((string) file_get_contents($definitionPath), true);
    if (!is_array($definition)) {
        return [
            'ok' => false,
            'definition' => [],
            'error' => 'runtime execution screen definition is invalid',
        ];
    }

    return [
        'ok' => true,
        'definition' => $definition,
        'error' => '',
    ];
}

/**
 * @return callable(array<string,mixed>):array<string,mixed>
 */
function app_no_code_public_runtime_dispatcher(array $app): callable
{
    return app_no_code_managed_operation_dispatcher(
        [
            'origin' => 'public-runtime',
            'target' => 'server',
        ],
        static fn (array $intent): array => app_pdo_enqueue_managed_operation_sync_intent($app, $intent),
    );
}

/**
 * @param array<string,mixed> $candidate
 * @param array<string,mixed> $post
 * @param array<string,mixed>|null $principal
 * @param callable(array<string,mixed>):array<string,mixed> $dispatcher
 * @return array{status_code:int,payload:array<string,mixed>}
 */
function app_no_code_public_runtime_execution_response_for_candidate(
    array $app,
    string $projectKey,
    array $candidate,
    string $requestMethod,
    array $post,
    ?array $principal,
    callable $dispatcher,
): array {
    $definitionResult = app_no_code_public_runtime_candidate_screen_definition($app, $projectKey, $candidate);
    if (!$definitionResult['ok']) {
        return app_no_code_runtime_execution_endpoint_response(
            app_no_code_runtime_execution_response_error($definitionResult['error']),
        );
    }

    $definition = $definitionResult['definition'];
    if ($principal !== null) {
        $policyDefinitionResult = app_no_code_screen_definition_from_project($app, $projectKey, $principal);
        if (!$policyDefinitionResult['ok']) {
            return app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error($policyDefinitionResult['error']),
            );
        }
        $definition = app_no_code_runtime_definition_with_action_policy_overlay(
            $definition,
            $policyDefinitionResult['definition'],
        );
    }

    $execution = app_no_code_runtime_execute_request_from_post(
        $definition,
        $requestMethod,
        $post,
        app_no_code_public_runtime_execution_binding($projectKey, $candidate),
        $dispatcher,
    );

    return app_no_code_runtime_execution_endpoint_response($execution);
}

/**
 * @param array{
 *     request_id:string
 * } $request
 */
/**
 * @param array<string,mixed>|null $executionBinding
 */
function app_send_no_code_public_runtime_file_response(
    array $request,
    string $filePath,
    string $cacheControl,
    ?array $executionBinding = null,
): void
{
    $body = null;
    if ($executionBinding !== null) {
        $html = (string) file_get_contents($filePath);
        $body = app_no_code_public_runtime_preview_html_with_execution_binding($html, $executionBinding);
    }

    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Length: ' . (string) ($body !== null ? strlen($body) : filesize($filePath)));
    header('Cache-Control: ' . $cacheControl);
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);

    if ($body !== null) {
        echo $body;
        return;
    }

    if (readfile($filePath) === false) {
        throw new RuntimeException('public runtime preview の送信に失敗しました。');
    }
}

/**
 * @param array<string,mixed> $executionBinding
 */
function app_no_code_public_runtime_preview_html_with_execution_binding(string $html, array $executionBinding): string
{
    $script = '<script type="application/json" id="no-code-runtime-execution-binding">'
        . app_no_code_runtime_json_script_text($executionBinding)
        . '</script>';

    if (str_contains($html, '<script>')) {
        return str_replace('<script>', $script . "\n<script>", $html);
    }

    if (str_contains($html, '</body>')) {
        return str_replace('</body>', $script . "\n</body>", $html);
    }

    return $html . "\n" . $script . "\n";
}

/**
 * @param array<string,mixed> $candidate
 * @return array<string,string>
 */
function app_no_code_public_runtime_preview_execution_binding(
    string $projectKey,
    array $candidate,
    string $executionPath,
): array {
    $binding = app_no_code_public_runtime_execution_binding($projectKey, $candidate);
    $binding['execution_url'] = $executionPath;
    return $binding;
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string
 * } $request
 * @param array<string,mixed> $candidate
 */
function app_send_no_code_public_runtime_candidate_preview_response(
    array $app,
    array $request,
    string $projectKey,
    array $candidate,
    string $cacheControl,
    ?string $executionPath = null,
): bool {
    $artifactKey = (string) ($candidate['artifact_key'] ?? '');
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        return false;
    }

    $artifactResult = app_project_output_find($app, $projectKey, $artifactKey);
    if (!$artifactResult['ok'] || $artifactResult['item'] === null) {
        return false;
    }

    $artifact = $artifactResult['item'];
    if ($artifact['source_output_key'] !== APP_NO_CODE_OPERATOR_SOURCE_OUTPUT_KEY) {
        return false;
    }

    try {
        $runtimeRoot = app_project_output_artifact_bundle_runtime_root($artifact);
    } catch (Throwable) {
        return false;
    }

    $previewPath = $runtimeRoot . '/runtime-preview.html';
    if (!is_file($previewPath)) {
        return false;
    }

    $executionBinding = $executionPath !== null
        ? app_no_code_public_runtime_preview_execution_binding($projectKey, $candidate, $executionPath)
        : null;
    app_send_no_code_public_runtime_file_response($request, $previewPath, $cacheControl, $executionBinding);
    return true;
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $artifactKey = trim(app_route_param($request, 'artifact_key'));
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        app_render_bad_request_page($app, $request, 'artifact key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_artifact($app, $projectKey, $artifactKey);
    if (!$candidateResult['ok']) {
        app_render_not_found_page($app, $request);
        return;
    }
    if ($candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_artifact_cache_control(),
        null,
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $artifactKey = trim(app_route_param($request, 'artifact_key'));
    if (!app_project_output_artifact_key_is_valid($artifactKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_artifact($app, $projectKey, $artifactKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_execution_page(array $app, array $request): void
{
    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution project binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution alias binding does not match'),
            )['payload'],
            409,
        );
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_send_json_response(
            $request,
            app_no_code_runtime_execution_endpoint_response(
                app_no_code_runtime_execution_response_error('runtime execution artifact was not found'),
            )['payload'],
            422,
        );
        return;
    }

    $response = app_no_code_public_runtime_execution_response_for_candidate(
        $app,
        $projectKey,
        $candidateResult['item'],
        $request['method'],
        $_POST,
        app_auth_principal(),
        app_no_code_public_runtime_dispatcher($app),
    );

    app_send_json_response($request, $response['payload'], $response['status_code']);
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_current_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_current_approved_no_code_publish_candidate($app, $projectKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_current_cache_control(),
        app_no_code_public_runtime_current_execution_path($projectKey),
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}

/**
 * @param array{
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_no_code_public_runtime_alias_preview_page(array $app, array $request): void
{
    if (!app_request_method_is($request, 'GET')) {
        app_render_method_not_allowed_page($app, $request, ['GET']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $aliasKey = app_no_code_public_runtime_normalize_alias_key(app_route_param($request, 'alias_key'));
    if (!app_no_code_public_runtime_alias_key_is_valid($aliasKey)) {
        app_render_bad_request_page($app, $request, 'alias key の形式が不正です。');
        return;
    }

    $candidateResult = app_pdo_find_approved_no_code_publish_candidate_for_alias($app, $projectKey, $aliasKey);
    if (!$candidateResult['ok'] || $candidateResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    if (!app_send_no_code_public_runtime_candidate_preview_response(
        $app,
        $request,
        $projectKey,
        $candidateResult['item'],
        app_no_code_public_runtime_current_cache_control(),
        app_no_code_public_runtime_alias_execution_path($projectKey, $aliasKey),
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}
