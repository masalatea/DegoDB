<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/project_permission.php';

/**
 * @param array<string,mixed> $app
 * @param array{
 *     project_key:string,
 *     source_output_key:string,
 *     operation_key:string,
 *     custom_operations:list<array<string,mixed>>,
 *     principal?:array<string,mixed>|null,
 *     csrf_valid?:bool,
 *     source_output?:array<string,mixed>|null,
 *     artifact_key?:string,
 *     current_artifact_key?:string
 * } $request
 * @return array{
 *     ok:bool,
 *     allowed:bool,
 *     result:string,
 *     status_code:int,
 *     failure_code:string,
 *     operation:array<string,mixed>|null,
 *     plan:array<string,mixed>|null,
 *     audit_event:array<string,mixed>,
 *     error:string
 * }
 */
function app_no_code_custom_operation_dispatch_preflight(array $app, array $request): array
{
    $projectKey = strtoupper(trim((string) ($request['project_key'] ?? '')));
    $sourceOutputKey = trim((string) ($request['source_output_key'] ?? ''));
    $operationKey = trim((string) ($request['operation_key'] ?? ''));
    $operation = app_no_code_custom_operation_dispatch_find_operation(
        is_array($request['custom_operations'] ?? null) ? $request['custom_operations'] : [],
        $operationKey,
    );

    if ($projectKey === '' || $sourceOutputKey === '' || $operationKey === '') {
        return app_no_code_custom_operation_dispatch_result($request, null, 'invalid', 400, 'invalid_request');
    }

    if ($operation === null) {
        return app_no_code_custom_operation_dispatch_result($request, null, 'invalid', 404, 'unknown_operation');
    }

    $principal = $request['principal'] ?? null;
    if (!is_array($principal)) {
        return app_no_code_custom_operation_dispatch_result($request, $operation, 'unauthorized', 401, 'unauthenticated');
    }

    if (!app_no_code_custom_operation_dispatch_auth_guard_allows($operation, $principal)) {
        return app_no_code_custom_operation_dispatch_result($request, $operation, 'unauthorized', 403, 'auth_guard');
    }

    if ((bool) ($operation['csrf_required'] ?? true) && !((bool) ($request['csrf_valid'] ?? false))) {
        return app_no_code_custom_operation_dispatch_result($request, $operation, 'blocked', 400, 'missing_csrf');
    }

    $sourceOutput = $request['source_output'] ?? null;
    if (!is_array($sourceOutput)) {
        return app_no_code_custom_operation_dispatch_result($request, $operation, 'invalid', 404, 'missing_source_output');
    }

    if (trim((string) ($sourceOutput['source_output_key'] ?? '')) !== $sourceOutputKey) {
        return app_no_code_custom_operation_dispatch_result($request, $operation, 'invalid', 404, 'source_output_mismatch');
    }

    $policyKey = trim((string) ($operation['policy_key'] ?? ''));
    if ($policyKey !== '') {
        $policy = app_project_permission_can($app, $projectKey, $principal, $policyKey);
        if (!$policy['ok']) {
            return app_no_code_custom_operation_dispatch_result($request, $operation, 'invalid', 500, 'policy_error');
        }
        if (!$policy['allowed']) {
            return app_no_code_custom_operation_dispatch_result($request, $operation, 'unauthorized', 403, 'policy_denied');
        }
    }

    if ((string) ($operation['availability'] ?? 'deferred') !== 'available') {
        return app_no_code_custom_operation_dispatch_result($request, $operation, 'blocked', 409, 'deferred_availability');
    }

    if ((string) ($operation['target'] ?? '') === 'artifact') {
        $artifactKey = trim((string) ($request['artifact_key'] ?? ''));
        $currentArtifactKey = trim((string) ($request['current_artifact_key'] ?? ''));
        if ($currentArtifactKey === '') {
            return app_no_code_custom_operation_dispatch_result($request, $operation, 'blocked', 409, 'missing_artifact');
        }
        if ($artifactKey === '' || $artifactKey !== $currentArtifactKey) {
            return app_no_code_custom_operation_dispatch_result($request, $operation, 'stale', 409, 'stale_artifact');
        }
    }

    return app_no_code_custom_operation_dispatch_result($request, $operation, 'accepted_plan', 202, '');
}

/**
 * @param list<array<string,mixed>> $operations
 * @return array<string,mixed>|null
 */
function app_no_code_custom_operation_dispatch_find_operation(array $operations, string $operationKey): ?array
{
    foreach ($operations as $operation) {
        if (trim((string) ($operation['operation_key'] ?? '')) === $operationKey) {
            return $operation;
        }
    }

    return null;
}

/**
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $principal
 */
function app_no_code_custom_operation_dispatch_auth_guard_allows(array $operation, array $principal): bool
{
    $routeBoundary = is_array($operation['route_boundary'] ?? null) ? $operation['route_boundary'] : [];
    $authGuard = trim((string) ($routeBoundary['auth_guard'] ?? ''));
    if ($authGuard === '' || $authGuard === 'mtool_operator_admin') {
        return app_auth_has_any_role(['admin', 'config'], $principal);
    }

    if ($authGuard === 'web_lab_login') {
        return trim((string) ($principal['id'] ?? '')) !== '';
    }

    return false;
}

/**
 * @param array<string,mixed> $request
 * @param array<string,mixed>|null $operation
 * @return array{
 *     ok:bool,
 *     allowed:bool,
 *     result:string,
 *     status_code:int,
 *     failure_code:string,
 *     operation:array<string,mixed>|null,
 *     plan:array<string,mixed>|null,
 *     audit_event:array<string,mixed>,
 *     error:string
 * }
 */
function app_no_code_custom_operation_dispatch_result(
    array $request,
    ?array $operation,
    string $result,
    int $statusCode,
    string $failureCode,
): array {
    $allowed = $result === 'accepted_plan';
    $projectKey = strtoupper(trim((string) ($request['project_key'] ?? '')));
    $sourceOutputKey = trim((string) ($request['source_output_key'] ?? ''));
    $operationKey = trim((string) ($request['operation_key'] ?? ''));

    $plan = null;
    if ($allowed && $operation !== null) {
        $plan = [
            'execution_mode' => 'plan-only',
            'operation_key' => (string) ($operation['operation_key'] ?? $operationKey),
            'adapter_handoff' => (string) ($operation['adapter_handoff'] ?? ''),
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'artifact_key' => trim((string) ($request['artifact_key'] ?? '')),
        ];
    }

    return [
        'ok' => true,
        'allowed' => $allowed,
        'result' => $result,
        'status_code' => $statusCode,
        'failure_code' => $failureCode,
        'operation' => $operation,
        'plan' => $plan,
        'audit_event' => app_no_code_custom_operation_dispatch_audit_event($request, $operation, $result, $failureCode),
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $request
 * @param array<string,mixed>|null $operation
 * @return array<string,mixed>
 */
function app_no_code_custom_operation_dispatch_audit_event(
    array $request,
    ?array $operation,
    string $result,
    string $failureCode,
): array {
    $principal = is_array($request['principal'] ?? null) ? $request['principal'] : [];
    $projectKey = strtoupper(trim((string) ($request['project_key'] ?? '')));
    $sourceOutputKey = trim((string) ($request['source_output_key'] ?? ''));
    $operationKey = trim((string) ($operation['operation_key'] ?? $request['operation_key'] ?? ''));

    return [
        'actor_login_id' => (string) ($principal['id'] ?? ''),
        'actor_source' => (string) ($principal['auth_source'] ?? 'unknown'),
        'project_key' => $projectKey,
        'event_type' => (string) ($operation['audit_event'] ?? 'mtool.custom_operation.dispatch'),
        'target_type' => (string) ($operation['target'] ?? 'source_output'),
        'target_key' => $sourceOutputKey,
        'result' => $result,
        'message' => $failureCode,
        'metadata' => [
            'operation_key' => $operationKey,
            'source_output_key' => $sourceOutputKey,
            'artifact_key' => trim((string) ($request['artifact_key'] ?? '')),
            'adapter_handoff' => (string) ($operation['adapter_handoff'] ?? ''),
            'policy_key' => (string) ($operation['policy_key'] ?? ''),
            'failure_code' => $failureCode,
        ],
    ];
}
