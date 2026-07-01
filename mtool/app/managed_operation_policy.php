<?php

declare(strict_types=1);

require_once __DIR__ . '/auth_foundation.php';

/**
 * @param array<string,mixed> $principal
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $contract
 * @return array{
 *     ok:bool,
 *     allowed:bool,
 *     failed_checks:list<string>,
 *     error:string
 * }
 */
function app_managed_operation_policy_evaluate(array $principal, array $operation, array $contract): array
{
    $failed = [];

    if ((string) ($operation['status'] ?? '') !== 'active') {
        $failed[] = 'operation.status';
    }

    $projectKey = (string) ($operation['project_key'] ?? '');
    $permissionKey = (string) ($operation['permission_key'] ?? '');
    $permission = app_auth_foundation_evaluate_permissions($principal, [$permissionKey], $projectKey);
    if (!$permission['ok'] || !$permission['allowed']) {
        $failed[] = 'permission_key:' . $permissionKey;
    }

    foreach (app_managed_operation_policy_required_roles($operation) as $role) {
        if (!app_managed_operation_policy_principal_has_role($principal, $projectKey, $role)) {
            $failed[] = 'required_role:' . $role;
        }
    }

    foreach (app_managed_operation_policy_required_scopes($operation) as $scope) {
        if (!app_managed_operation_policy_principal_has_scope($principal, $scope)) {
            $failed[] = 'required_scope:' . $scope;
        }
    }

    foreach (app_managed_operation_policy_required_claims($operation) as $claimKey => $claimValue) {
        if (app_managed_operation_policy_principal_claim($principal, $claimKey) !== $claimValue) {
            $failed[] = 'required_claim:' . $claimKey;
        }
    }

    $storageFailures = app_managed_operation_policy_storage_failures($operation, $contract);
    $failed = array_merge($failed, $storageFailures);

    return [
        'ok' => true,
        'allowed' => $failed === [],
        'failed_checks' => $failed,
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $operation
 * @param array<string,mixed> $contract
 * @return list<string>
 */
function app_managed_operation_policy_storage_failures(array $operation, array $contract): array
{
    $failures = [];
    $contractKey = (string) ($contract['contract_key'] ?? '');
    if ($contractKey === '' || (string) ($operation['contract_key'] ?? '') !== $contractKey) {
        return ['contract_key'];
    }

    $fieldsByPhysicalName = [];
    foreach (($contract['fields'] ?? []) as $field) {
        if (is_array($field)) {
            $fieldsByPhysicalName[(string) ($field['physical_name'] ?? '')] = $field;
        }
    }

    $storagePolicy = (string) ($operation['storage_policy'] ?? 'business-only');
    foreach (($operation['fields'] ?? []) as $operationField) {
        if (!is_array($operationField)) {
            continue;
        }

        $physicalName = (string) ($operationField['field_physical_name'] ?? '');
        $fieldRole = (string) ($operationField['field_role'] ?? '');
        $contractField = $fieldsByPhysicalName[$physicalName] ?? null;
        if (!is_array($contractField)) {
            $failures[] = 'field.missing:' . $physicalName;
            continue;
        }

        if ($storagePolicy === 'business-only' && (string) ($contractField['storage_role'] ?? '') !== 'business') {
            $failures[] = 'field.storage_role:' . $physicalName;
        }

        if ($fieldRole === 'key' && !((bool) ($contractField['is_key'] ?? false))) {
            $failures[] = 'field.key_role:' . $physicalName;
        }

        if ((bool) ($operationField['allow_client_write'] ?? false)) {
            $metadata = is_array($contractField['contract_metadata'] ?? null)
                ? $contractField['contract_metadata']
                : [];
            if ((string) ($metadata['operation_role'] ?? '') !== 'editable') {
                $failures[] = 'field.operation_role:' . $physicalName;
            }
        }
    }

    return array_values(array_unique($failures));
}

/**
 * @param array<string,mixed> $operation
 * @return list<string>
 */
function app_managed_operation_policy_required_roles(array $operation): array
{
    return app_managed_operation_policy_string_list($operation['required_roles'] ?? []);
}

/**
 * @param array<string,mixed> $operation
 * @return list<string>
 */
function app_managed_operation_policy_required_scopes(array $operation): array
{
    return app_managed_operation_policy_string_list($operation['required_scopes'] ?? []);
}

/**
 * @param array<string,mixed> $operation
 * @return array<string,string>
 */
function app_managed_operation_policy_required_claims(array $operation): array
{
    $claims = $operation['required_claims'] ?? [];
    if (!is_array($claims)) {
        return [];
    }

    $normalized = [];
    foreach ($claims as $key => $value) {
        if (is_string($key) && $key !== '' && is_scalar($value)) {
            $normalized[$key] = (string) $value;
        }
    }

    return $normalized;
}

function app_managed_operation_policy_principal_has_role(array $principal, string $projectKey, string $role): bool
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $roles = [];

    if (is_array($principal['roles'] ?? null)) {
        $roles = array_merge($roles, app_managed_operation_policy_string_list($principal['roles']));
    }

    if ($normalizedProjectKey !== '' && is_array($principal['project_roles'][$normalizedProjectKey] ?? null)) {
        $roles = array_merge($roles, app_managed_operation_policy_string_list($principal['project_roles'][$normalizedProjectKey]));
    }

    return in_array(strtolower(trim($role)), array_values(array_unique($roles)), true);
}

function app_managed_operation_policy_principal_has_scope(array $principal, string $scope): bool
{
    if (!is_array($principal['scopes'] ?? null)) {
        return false;
    }

    return in_array(strtolower(trim($scope)), app_managed_operation_policy_string_list($principal['scopes']), true);
}

function app_managed_operation_policy_principal_claim(array $principal, string $claimKey): string
{
    if (!is_array($principal['claims'] ?? null)) {
        return '';
    }

    $value = $principal['claims'][$claimKey] ?? '';
    return is_scalar($value) ? (string) $value : '';
}

/**
 * @return list<string>
 */
function app_managed_operation_policy_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        if (is_string($item) && trim($item) !== '') {
            $items[] = strtolower(trim($item));
        }
    }

    return array_values(array_unique($items));
}
