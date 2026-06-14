<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/endpoint_test_job_service.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

function app_render_lab_endpoint_test_job_api_page(array $app, array $request): void
{
    if ($app['site'] !== 'lab' && $app['site'] !== 'admin') {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'この route は 実験用サイト または 設定変更用サイト でのみ利用します。',
        ], 403);
        return;
    }

    if (!app_request_method_is($request, 'GET')) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'GET のみ利用できます。',
        ], 405);
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => '認証が必要です。',
        ], 401);
        return;
    }

    if (!app_auth_has_any_role(['lab', 'admin'], $principal)) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'endpoint test job の閲覧には lab または admin role が必要です。',
        ], 403);
        return;
    }

    $jobKey = trim(app_route_param($request, 'job_key'));
    if (!app_endpoint_test_job_key_is_valid($jobKey)) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'job key の形式が不正です。',
        ], 400);
        return;
    }

    $jobResult = app_endpoint_test_job_find($app, $jobKey);
    if (!$jobResult['ok']) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => $jobResult['error'],
        ], 500);
        return;
    }

    if ($jobResult['item'] === null) {
        app_send_json_response($request, [
            'ok' => false,
            'error' => 'endpoint test job が見つかりません。',
        ], 404);
        return;
    }

    app_send_json_response($request, [
        'ok' => true,
        'job' => $jobResult['item'],
    ], 200);
}
