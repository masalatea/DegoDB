<?php

declare(strict_types=1);

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/audit_log_repository.php';
require_once __DIR__ . '/no_code_custom_operation_dispatch.php';
require_once __DIR__ . '/no_code_mtool_dogfooding_probe.php';
require_once __DIR__ . '/project_source_output_route_common.php';

function app_handle_project_source_output_operation_request(array $app, array $request): void
{
    $bootstrap = app_project_source_output_item_route_bootstrap($app, $request, ['POST']);
    if ($bootstrap === null) {
        return;
    }

    $operationKey = app_project_source_output_operation_key_from_route_slug(
        app_route_param($request, 'operation_slug'),
    );
    if ($operationKey !== 'review_source_output_artifact') {
        app_render_not_found_page($app, $request);
        return;
    }

    $customOperations = app_project_source_output_operation_custom_operations(
        $bootstrap['project_key'],
        $bootstrap['principal'],
    );

    $result = app_no_code_custom_operation_dispatch_preflight($app, [
        'project_key' => $bootstrap['project_key'],
        'source_output_key' => $bootstrap['source_output_key'],
        'operation_key' => $operationKey,
        'custom_operations' => $customOperations,
        'principal' => $bootstrap['principal'],
        'csrf_valid' => app_verify_csrf_token(app_post_param('_csrf')),
        'source_output' => $bootstrap['source_output'],
        'artifact_key' => app_post_param('artifact_key'),
        'current_artifact_key' => app_post_param('current_artifact_key'),
    ]);
    $result['audit_append'] = app_project_source_output_operation_append_audit_event($app, $result);

    app_render_project_source_output_operation_result_page($app, $request, $result);
}

function app_project_source_output_operation_key_from_route_slug(string $operationSlug): string
{
    return str_replace('-', '_', strtolower(trim(rawurldecode($operationSlug))));
}

/**
 * @param array<string,mixed> $principal
 * @return list<array<string,mixed>>
 */
function app_project_source_output_operation_custom_operations(string $projectKey, array $principal): array
{
    if (strtoupper(trim($projectKey)) !== 'MTOOL') {
        return [];
    }

    $definition = app_no_code_mtool_dogfooding_probe_screen_definition($principal);
    if (!$definition['ok']) {
        return [];
    }

    $contract = $definition['definition']['contracts'][0] ?? [];
    if (!is_array($contract) || !is_array($contract['custom_operations'] ?? null)) {
        return [];
    }

    return $contract['custom_operations'];
}

/**
 * @param array<string,mixed> $result
 * @return array{ok:bool,skipped:bool,item:array<string,mixed>,error:string}
 */
function app_project_source_output_operation_append_audit_event(array $app, array $result): array
{
    $auditEvent = is_array($result['audit_event'] ?? null) ? $result['audit_event'] : [];
    if ($auditEvent === []) {
        return [
            'ok' => true,
            'skipped' => true,
            'item' => [],
            'error' => '',
        ];
    }

    $append = app_audit_log_append($app, $auditEvent);

    return [
        'ok' => $append['ok'],
        'skipped' => false,
        'item' => is_array($append['item'] ?? null) ? $append['item'] : [],
        'error' => (string) ($append['error'] ?? ''),
    ];
}

/**
 * @param array<string,mixed> $result
 */
function app_render_project_source_output_operation_result_page(array $app, array $request, array $result): void
{
    app_send_html_response_headers($request, (int) ($result['status_code'] ?? 500));
    $auditEvent = is_array($result['audit_event'] ?? null) ? $result['audit_event'] : [];
    $metadata = is_array($auditEvent['metadata'] ?? null) ? $auditEvent['metadata'] : [];
    $auditAppend = is_array($result['audit_append'] ?? null) ? $result['audit_append'] : [];
    $auditAppendStatus = app_project_source_output_operation_audit_append_status($auditAppend);
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Source Output Operation</title>
</head>
<body>
<main data-source-output-operation-result="<?php echo app_h((string) ($result['result'] ?? '')); ?>">
    <h1>Source Output Operation</h1>
    <p>Custom operation dispatch guard result.</p>
    <ul>
        <li>result: <code><?php echo app_h((string) ($result['result'] ?? '')); ?></code></li>
        <li>failure: <code><?php echo app_h((string) ($result['failure_code'] ?? '')); ?></code></li>
        <li>operation: <code><?php echo app_h((string) ($metadata['operation_key'] ?? '')); ?></code></li>
        <li>source output: <code><?php echo app_h((string) ($metadata['source_output_key'] ?? '')); ?></code></li>
        <li>audit event: <code><?php echo app_h((string) ($auditEvent['event_type'] ?? '')); ?></code></li>
        <li>audit append: <code><?php echo app_h($auditAppendStatus); ?></code></li>
    </ul>
    <p>No mutation has been executed.</p>
</main>
</body>
</html>
    <?php
}

/**
 * @param array<string,mixed> $auditAppend
 */
function app_project_source_output_operation_audit_append_status(array $auditAppend): string
{
    if ($auditAppend === [] || (bool) ($auditAppend['skipped'] ?? false)) {
        return 'skipped';
    }

    return (bool) ($auditAppend['ok'] ?? false) ? 'recorded' : 'failed';
}
