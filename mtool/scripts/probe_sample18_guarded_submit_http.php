#!/usr/bin/env php
<?php

declare(strict_types=1);

define('MTOOL_SAMPLE18_HTTP_SMOKE_LIBRARY_ONLY', true);
require_once __DIR__ . '/check_sample18_task_board_http_smoke.php';

$expected = (string) ($argv[1] ?? '');
$title = (string) ($argv[2] ?? '');
if (!in_array($expected, ['committed', 'rolled_back'], true) || $title === '') {
    fwrite(STDERR, "Usage: php mtool/scripts/probe_sample18_guarded_submit_http.php committed|rolled_back TITLE\n");
    exit(2);
}

$args = parse_args(array_merge([$argv[0]], array_slice($argv, 3)));
$client = [
    'base_url' => $args['lab_base_url'],
    'timeout' => $args['timeout'],
    'cookies' => [],
];

try {
    $loginRedirectPath = '/samples/sample18-task-board';
    $loginPage = request_once($client, 'GET', '/login?redirect=' . rawurlencode($loginRedirectPath));
    ensure($loginPage['status'] === 200, 'login page was not available');
    $loginCsrf = input_value($loginPage['body'], '_csrf');
    ensure($loginCsrf !== '', 'login CSRF token was not found');
    $login = request_follow($client, 'POST', '/login', [
        'form_params' => [
            '_csrf' => $loginCsrf,
            'username' => $args['lab_user'],
            'password' => $args['lab_password'],
            'redirect' => $loginRedirectPath,
        ],
    ]);
    ensure($login['status'] === 200, 'login failed');
    $csrf = input_value($login['body'], '_csrf_token');
    ensure($csrf !== '', 'task board CSRF token was not found');

    $operationKey = (string) (getenv('SAMPLE18_TRANSACTION_SMOKE_OPERATION_KEY') ?: 'create_task_card');
    $formParams = [
        '_csrf_token' => $csrf,
        'operation_key' => $operationKey,
    ];
    if ($operationKey === 'complete_task_card') {
        $taskId = (string) getenv('SAMPLE18_TRANSACTION_SMOKE_TASK_ID');
        ensure($taskId !== '' && ctype_digit($taskId), 'complete_task_card probe requires SAMPLE18_TRANSACTION_SMOKE_TASK_ID');
        $formParams['id'] = $taskId;
    } else {
        ensure($operationKey === 'create_task_card', 'unsupported generated submit smoke operation: ' . $operationKey);
        $formParams += [
            'title' => $title,
            'body' => 'Sample18 isolated guarded transaction smoke.',
            'assigned_to' => 'Transaction Smoke',
            'priority' => '17',
            'due_date' => date('Y-m-d'),
        ];
    }

    $response = request_once($client, 'POST', '/samples/sample18-task-board/no-code/generated-submit', [
        'form_params' => $formParams,
    ]);
    $payload = json_response($response);
    $transaction = is_array($payload['transaction_result'] ?? null) ? $payload['transaction_result'] : [];
    ensure(($transaction['transaction_status'] ?? '') === $expected, 'transaction status mismatch: ' . json_encode($payload));
    if ($expected === 'committed') {
        ensure($response['status'] === 200, 'committed response status was not 200');
        ensure(($payload['result'] ?? '') === 'executed', 'committed response was not executed');
        ensure(
            (($payload['route_execution']['recovery_required'] ?? true) === false),
            'committed response unexpectedly requires recovery: ' . json_encode($payload),
        );
    } else {
        ensure($response['status'] === 500, 'rolled-back response status was not 500');
        ensure(($transaction['rolled_back'] ?? false) === true, 'rolled-back response did not report rolled_back=true');
        ensure(($payload['post_commit_recording'] ?? []) === [], 'rollback unexpectedly performed post-commit recording');
    }

    echo json_encode([
        'ok' => true,
        'expected' => $expected,
        'operation_key' => $operationKey,
        'title' => $title,
        'http_status' => $response['status'],
        'result' => $payload['result'] ?? '',
        'transaction_status' => $transaction['transaction_status'] ?? '',
        'rolled_back' => $transaction['rolled_back'] ?? false,
    ], JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $throwable) {
    echo json_encode(['ok' => false, 'error' => $throwable->getMessage()], JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(1);
}
