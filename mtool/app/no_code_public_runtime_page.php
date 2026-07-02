<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/no_code_publish_candidate_repository_pdo.php';
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

function app_no_code_public_runtime_current_preview_path(string $projectKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/current/runtime-preview.html';
}

function app_no_code_public_runtime_alias_preview_path(string $projectKey, string $aliasKey): string
{
    return '/runs/no-code/'
        . rawurlencode(app_normalize_project_key($projectKey))
        . '/alias/'
        . rawurlencode(app_no_code_public_runtime_normalize_alias_key($aliasKey))
        . '/runtime-preview.html';
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
 * @param array{
 *     request_id:string
 * } $request
 */
function app_send_no_code_public_runtime_file_response(array $request, string $filePath, string $cacheControl): void
{
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Length: ' . (string) filesize($filePath));
    header('Cache-Control: ' . $cacheControl);
    header('X-Content-Type-Options: nosniff');
    header('X-Request-Id: ' . $request['request_id']);

    if (readfile($filePath) === false) {
        throw new RuntimeException('public runtime preview の送信に失敗しました。');
    }
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

    app_send_no_code_public_runtime_file_response($request, $previewPath, $cacheControl);
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
    )) {
        app_render_not_found_page($app, $request);
        return;
    }
}
