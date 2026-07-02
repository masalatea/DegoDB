<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit_log_repository_pdo.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/managed_operation_sync_outbox_repository_pdo.php';
require_once __DIR__ . '/no_code_operator_sync_inspection.php';
require_once __DIR__ . '/project_permission.php';
require_once __DIR__ . '/project_source_output_route_common.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

/**
 * @param array{
 *     site:string,
 *     site_name:string
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 */
function app_render_project_sync_outbox_detail_page(array $app, array $request): void
{
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'sync outbox の確認には admin または config role が必要です。');
        return;
    }

    if (!app_request_method_is($request, 'GET') && !app_request_method_is($request, 'POST')) {
        app_render_method_not_allowed_page($app, $request, ['GET', 'POST']);
        return;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return;
    }

    $dedupeKey = trim(app_route_param($request, 'dedupe_key'));
    if ($dedupeKey === '' || strlen($dedupeKey) > 128) {
        app_render_bad_request_page($app, $request, 'sync outbox dedupe key の形式が不正です。');
        return;
    }

    $permission = app_project_permission_can_with_audit(
        $app,
        $projectKey,
        $principal,
        'source_output.download',
        'sync_outbox',
        $dedupeKey,
    );
    if (!$permission['ok']) {
        app_render_internal_error_page($app, $request);
        return;
    }
    if (!$permission['allowed']) {
        app_render_forbidden_page($app, $request, 'sync outbox の確認には project publisher 以上の権限が必要です。');
        return;
    }

    $itemResult = app_pdo_fetch_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
    if (!$itemResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Sync Outbox Detail</title>
</head>
<body>
<main>
    <h1>Sync Outbox Detail</h1>
    <p>sync outbox item の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>dedupe key: <code><?php echo app_h($dedupeKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($itemResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return;
    }

    $item = $itemResult['item'];
    if ($item === null) {
        app_render_not_found_page($app, $request);
        return;
    }

    $retryEligibility = app_no_code_operator_sync_retry_eligibility($item);
    $errors = [];
    if (app_request_method_is($request, 'POST')) {
        if (!app_verify_csrf_token(app_post_param('_csrf'))) {
            $errors[] = 'フォームの有効期限が切れています。再読み込みしてやり直してください。';
        } elseif (trim(app_post_param('action')) !== 'retry-sync-outbox') {
            $errors[] = '未対応の操作です。';
        } elseif (!$retryEligibility['allowed']) {
            $errors[] = 'この sync outbox item は retry できません: ' . implode(' ', $retryEligibility['reasons']);
        } else {
            $retryResult = app_pdo_requeue_failed_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
            if ($retryResult['ok'] && $retryResult['item'] !== null) {
                $auditResult = app_pdo_audit_log_append(
                    $app,
                    app_project_sync_outbox_retry_audit_event_input(
                        $principal,
                        $projectKey,
                        $dedupeKey,
                        $item,
                        $retryResult['item'],
                    ),
                );
                app_send_redirect_response(
                    $request,
                    app_project_sync_outbox_detail_path($projectKey, $dedupeKey)
                        . '?retried=1&audit=' . ($auditResult['ok'] ? 'recorded' : 'failed'),
                );
                return;
            }

            $errors[] = $retryResult['error'];
        }
    }

    $retried = trim((string) ($_GET['retried'] ?? '')) === '1';
    $retryAuditState = trim((string) ($_GET['audit'] ?? ''));
    $retryAuditResult = app_pdo_audit_log_fetch_latest($app, [
        'project_key' => $projectKey,
        'event_type' => 'sync_outbox.retry_requeued',
        'target_type' => 'sync_outbox',
        'target_key' => $dedupeKey,
        'limit' => 3,
    ]);
    $retryAuditItems = $retryAuditResult['ok'] ? $retryAuditResult['items'] : [];
    $csrfToken = app_csrf_token();
    $intentJson = json_encode($item['intent'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($intentJson)) {
        $intentJson = '{}';
    }

    app_send_html_response_headers($request, 200);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Sync Outbox Detail</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 72rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code, pre {
            background: #edf2f7;
            border-radius: 6px;
        }
        code {
            padding: 0.1rem 0.3rem;
        }
        pre {
            padding: 1rem;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border-bottom: 1px solid #d7dde5;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        .breadcrumbs, .muted {
            color: #475569;
        }
        .notice {
            border: 1px solid #93c5fd;
            border-radius: 8px;
            background: #eff6ff;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
<main>
    <p class="breadcrumbs"><a href="/dashboard">dashboard</a> / <a href="/projects">projects</a> / <a href="/projects/<?php echo app_h(rawurlencode($projectKey)); ?>"><?php echo app_h($projectKey); ?></a> / <a href="<?php echo app_h(app_project_source_outputs_path($projectKey)); ?>">source-outputs</a> / sync outbox</p>

    <h1>Sync Outbox Detail</h1>
    <p class="muted">This page can requeue eligible failed items to <code>pending</code>. Inline processing, background scheduling, transport, and conflict resolution are intentionally out of scope for this slice.</p>

    <?php if ($retried): ?>
        <section class="notice">
            <h2>Retry Queued</h2>
            <p>The item was requeued for the existing processor. It was not processed inline by this page.</p>
            <ul>
                <li>current status: <code><?php echo app_h((string) ($item['status'] ?? '')); ?></code></li>
                <li>attempts before next processor claim: <code><?php echo app_h((string) ($item['attempts'] ?? '')); ?></code></li>
                <li>last error cleared: <code><?php echo app_h(trim((string) ($item['last_error'] ?? '')) === '' ? 'yes' : 'no'); ?></code></li>
                <li>audit trail: <code><?php echo app_h($retryAuditState === 'recorded' ? 'recorded' : ($retryAuditState === 'failed' ? 'failed' : 'not reported')); ?></code></li>
                <li>next step: existing processor can claim this item when it scans pending sync outbox work.</li>
            </ul>
        </section>
    <?php endif; ?>
    <?php if ($errors !== []): ?>
        <section>
            <h2>Errors</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo app_h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <h2>Retry Eligibility</h2>
    <ul>
        <li>state: <code><?php echo app_h($retryEligibility['state']); ?></code> <?php echo app_h($retryEligibility['label']); ?></li>
        <li>action: <code><?php echo app_h($retryEligibility['action_label']); ?></code></li>
        <li>allowed: <code><?php echo app_h($retryEligibility['allowed'] ? 'yes' : 'no'); ?></code></li>
    </ul>
    <?php if ($retryEligibility['reasons'] !== []): ?>
        <p class="muted">reasons: <code><?php echo app_h(implode(' ', $retryEligibility['reasons'])); ?></code></p>
    <?php endif; ?>
    <?php if ($retryEligibility['allowed']): ?>
        <form method="post">
            <input type="hidden" name="_csrf" value="<?php echo app_h($csrfToken); ?>">
            <input type="hidden" name="action" value="retry-sync-outbox">
            <button type="submit"><?php echo app_h($retryEligibility['action_label']); ?></button>
        </form>
    <?php endif; ?>

    <h2>Recent Retry Audit</h2>
    <?php if (!$retryAuditResult['ok']): ?>
        <p class="muted">retry audit events could not be loaded: <code><?php echo app_h($retryAuditResult['error']); ?></code></p>
    <?php elseif ($retryAuditItems === []): ?>
        <p class="muted">No retry audit event has been recorded for this sync outbox item yet.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>created_at</th>
                <th>actor</th>
                <th>result</th>
                <th>status</th>
                <th>attempts</th>
                <th>message</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($retryAuditItems as $auditItem): ?>
                <?php $metadata = is_array($auditItem['metadata'] ?? null) ? $auditItem['metadata'] : []; ?>
                <tr>
                    <td><code><?php echo app_h((string) ($auditItem['created_at'] ?? '')); ?></code></td>
                    <td><code><?php echo app_h((string) ($auditItem['actor_login_id'] ?? '')); ?></code></td>
                    <td><code><?php echo app_h((string) ($auditItem['result'] ?? '')); ?></code></td>
                    <td><code><?php echo app_h((string) ($metadata['status_before'] ?? '')); ?> -&gt; <?php echo app_h((string) ($metadata['status_after'] ?? '')); ?></code></td>
                    <td><code><?php echo app_h((string) ($metadata['attempts_before'] ?? '')); ?> -&gt; <?php echo app_h((string) ($metadata['attempts_after'] ?? '')); ?></code></td>
                    <td><?php echo app_h((string) ($auditItem['message'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <table>
        <tbody>
        <?php foreach ([
            'status',
            'attempts',
            'last_error',
            'operation_key',
            'operation_type',
            'contract_key',
            'origin',
            'target',
            'storage_mode',
            'dedupe_key',
            'created_at',
            'updated_at',
        ] as $field): ?>
            <tr>
                <th><?php echo app_h($field); ?></th>
                <td><code><?php echo app_h((string) ($item[$field] ?? '')); ?></code></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Intent Payload</h2>
    <pre><code><?php echo app_h($intentJson); ?></code></pre>
</main>
</body>
</html>
    <?php
}

/**
 * @param array{id?:string,auth_source?:string} $principal
 * @param array<string,mixed> $before
 * @param array<string,mixed> $after
 * @return array<string,mixed>
 */
function app_project_sync_outbox_retry_audit_event_input(
    array $principal,
    string $projectKey,
    string $dedupeKey,
    array $before,
    array $after,
): array {
    return [
        'actor_login_id' => (string) ($principal['id'] ?? ''),
        'actor_source' => (string) ($principal['auth_source'] ?? 'unknown'),
        'project_key' => $projectKey,
        'event_type' => 'sync_outbox.retry_requeued',
        'target_type' => 'sync_outbox',
        'target_key' => $dedupeKey,
        'result' => 'success',
        'message' => 'Failed sync outbox item was requeued for the existing processor.',
        'metadata' => [
            'status_before' => (string) ($before['status'] ?? ''),
            'status_after' => (string) ($after['status'] ?? ''),
            'attempts_before' => (int) ($before['attempts'] ?? 0),
            'attempts_after' => (int) ($after['attempts'] ?? 0),
            'last_error_before' => (string) ($before['last_error'] ?? ''),
            'last_error_after' => (string) ($after['last_error'] ?? ''),
            'operation_key' => (string) ($after['operation_key'] ?? $before['operation_key'] ?? ''),
            'operation_type' => (string) ($after['operation_type'] ?? $before['operation_type'] ?? ''),
            'contract_key' => (string) ($after['contract_key'] ?? $before['contract_key'] ?? ''),
        ],
    ];
}
