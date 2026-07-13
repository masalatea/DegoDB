<?php

declare(strict_types=1);

require_once __DIR__ . '/sso_app_user_design_guidance.php';

const APP_SSO_APP_USER_PROJECT_POLICY_VERSION = 'sso-app-user-project-policy-v1';

/**
 * Normalize and validate a non-secret project policy without side effects.
 *
 * @param array<string,mixed> $input
 * @return array{ok:bool,policy:array<string,mixed>,errors:list<string>,warnings:list<string>}
 */
function app_sso_app_user_project_policy_normalize(array $input): array
{
    $allowedFields = [
        'contract_version',
        'enabled',
        'auth_mode',
        'provisioning_mode',
        'provider_key',
        'sso_profile_fields',
        'application_profile_fields',
        'user_owned_data',
        'tenant_boundary',
        'lifecycle_custom_boundary',
        'schema_roles',
    ];
    $version = trim((string) ($input['contract_version'] ?? APP_SSO_APP_USER_PROJECT_POLICY_VERSION));
    $enabled = filter_var($input['enabled'] ?? false, FILTER_VALIDATE_BOOL);
    $authMode = strtolower(trim((string) ($input['auth_mode'] ?? '')));
    $provisioningMode = strtolower(trim((string) ($input['provisioning_mode'] ?? '')));
    $providerKey = strtolower(trim((string) ($input['provider_key'] ?? '')));
    $ssoProfileFields = app_sso_app_user_string_list($input['sso_profile_fields'] ?? []);
    $applicationProfileFields = app_sso_app_user_string_list($input['application_profile_fields'] ?? []);
    $userOwnedData = app_sso_app_user_string_list($input['user_owned_data'] ?? []);
    $tenantBoundary = trim((string) ($input['tenant_boundary'] ?? ''));
    $lifecycleBoundary = app_sso_app_user_string_list($input['lifecycle_custom_boundary'] ?? []);
    $schemaRolesInput = is_array($input['schema_roles'] ?? null) ? $input['schema_roles'] : [];
    $allowedSchemaRoles = ['application_user_table', 'external_identity_table', 'profile_table'];
    $unknownSchemaRoles = array_values(array_diff(array_map('strval', array_keys($schemaRolesInput)), $allowedSchemaRoles));
    $schemaRoles = [
        'application_user_table' => strtolower(trim((string) ($schemaRolesInput['application_user_table'] ?? 'app_user'))),
        'external_identity_table' => strtolower(trim((string) ($schemaRolesInput['external_identity_table'] ?? 'app_user_external_identity'))),
        'profile_table' => strtolower(trim((string) ($schemaRolesInput['profile_table'] ?? 'app_user_profile'))),
    ];

    $policy = [
        'contract_version' => $version,
        'enabled' => $enabled,
        'auth_mode' => $authMode,
        'provisioning_mode' => $provisioningMode,
        'provider_key' => $providerKey,
        'sso_profile_fields' => $ssoProfileFields,
        'application_profile_fields' => $applicationProfileFields,
        'user_owned_data' => $userOwnedData,
        'tenant_boundary' => $tenantBoundary,
        'lifecycle_custom_boundary' => $lifecycleBoundary,
        'schema_roles' => $schemaRoles,
    ];

    $errors = [];
    $warnings = [];
    $unknownFields = array_values(array_diff(array_map('strval', array_keys($input)), $allowedFields));
    if ($unknownFields !== []) {
        sort($unknownFields);
        $errors[] = 'unknown policy fields: ' . implode(', ', $unknownFields) . '.';
    }
    if ($unknownSchemaRoles !== []) {
        sort($unknownSchemaRoles);
        $errors[] = 'unknown schema_roles: ' . implode(', ', $unknownSchemaRoles) . '.';
    }
    foreach ($schemaRoles as $role => $tableName) {
        if ($tableName === '' || preg_match('/^[a-z_][a-z0-9_]{0,127}$/', $tableName) !== 1) {
            $errors[] = 'invalid schema role table name: ' . $role . '.';
        }
    }
    if ($version !== APP_SSO_APP_USER_PROJECT_POLICY_VERSION) {
        $errors[] = 'unsupported contract_version: ' . $version;
    }

    $forbidden = app_sso_app_user_forbidden_fields(array_merge($ssoProfileFields, $applicationProfileFields));
    if ($forbidden !== []) {
        $errors[] = 'forbidden profile fields: ' . implode(', ', $forbidden) . '.';
    }
    $overlap = array_values(array_intersect($ssoProfileFields, $applicationProfileFields));
    if ($overlap !== []) {
        $errors[] = 'profile field ownership overlaps: ' . implode(', ', $overlap) . '.';
    }
    if (in_array('email-auto-link', $lifecycleBoundary, true)) {
        $errors[] = 'email-based automatic identity linking is not supported.';
    }

    if ($enabled) {
        if (!in_array($authMode, ['oidc', 'sso'], true)) {
            $errors[] = 'enabled policy requires auth_mode oidc or sso.';
        }
        if (!in_array($provisioningMode, ['jit', 'invitation-only'], true)) {
            $errors[] = 'enabled policy requires provisioning_mode jit or invitation-only.';
        }
        if ($providerKey === '' || preg_match('/^[a-z][a-z0-9._-]{0,63}$/', $providerKey) !== 1) {
            $errors[] = 'enabled policy requires a valid provider_key.';
        }
        if (!array_key_exists('sso_profile_fields', $input)) {
            $warnings[] = 'sso_profile_fields is not explicitly declared; no SSO profile fields will be persisted.';
        }
        if (!array_key_exists('lifecycle_custom_boundary', $input)) {
            $warnings[] = 'lifecycle_custom_boundary is not explicitly recorded.';
        }
    } elseif ($authMode !== '' || $provisioningMode !== '' || $providerKey !== '' || $ssoProfileFields !== []) {
        $warnings[] = 'disabled policy retains configuration but does not enable validation or generation.';
    }

    return [
        'ok' => $errors === [],
        'policy' => $policy,
        'errors' => $errors,
        'warnings' => $warnings,
    ];
}

/** @param array<string,mixed> $policy */
function app_sso_app_user_project_policy_json(array $policy): string
{
    return json_encode(
        $policy,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
}
