<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/project_permission.php';

const APP_AUTH_FOUNDATION_SITE_ROLE_ORDER = [
    'lab' => 10,
    'config' => 20,
    'admin' => 30,
];

const APP_AUTH_FOUNDATION_SITE_PERMISSION_REQUIREMENTS = [
    'site.lab' => 'lab',
    'site.config' => 'config',
    'site.admin' => 'admin',
];

/**
 * @return list<array{
 *     legacy_field:string,
 *     category:string,
 *     action:string,
 *     permission_key:string,
 *     disposition:string
 * }>
 */
function app_auth_foundation_legacy_permission_unit_inventory(): array
{
    return [
        ['legacy_field' => 'dbtoolRead', 'category' => 'dbtool', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'dbtoolWrite', 'category' => 'dbtool', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'htmlRead', 'category' => 'html', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'htmlWrite', 'category' => 'html', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'testtoolRead', 'category' => 'testtool', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'testtoolWrite', 'category' => 'testtool', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'spectoolRead', 'category' => 'spectool', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'spectoolWrite', 'category' => 'spectool', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'ReqRead', 'category' => 'requirement', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'ReqWrite', 'category' => 'requirement', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'ChatRead', 'category' => 'chat', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'ChatWrite', 'category' => 'chat', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'MinutesRead', 'category' => 'minutes', 'action' => 'read', 'permission_key' => 'project.read', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'MinutesWrite', 'category' => 'minutes', 'action' => 'write', 'permission_key' => 'project.edit', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'UploadRead', 'category' => 'upload', 'action' => 'read', 'permission_key' => 'source_output.download', 'disposition' => 'collapse-to-rbac'],
        ['legacy_field' => 'UploadWrite', 'category' => 'upload', 'action' => 'write', 'permission_key' => 'source_output.publish', 'disposition' => 'collapse-to-rbac'],
    ];
}

/**
 * @return array<string,string>
 */
function app_auth_foundation_permission_requirements(): array
{
    return APP_AUTH_FOUNDATION_SITE_PERMISSION_REQUIREMENTS + APP_PROJECT_PERMISSION_REQUIREMENTS;
}

/**
 * @param array<string,mixed> $rawPrincipal
 * @return array{
 *     ok:bool,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         auth_source:string,
 *         site:string,
 *         site_roles:list<string>,
 *         project_roles:array<string,list<string>>
 *     },
 *     error:string
 * }
 */
function app_auth_foundation_normalize_principal(array $rawPrincipal): array
{
    $id = trim((string) ($rawPrincipal['id'] ?? ''));
    $displayName = trim((string) ($rawPrincipal['display_name'] ?? $id));
    $authSource = strtolower(trim((string) ($rawPrincipal['auth_source'] ?? '')));
    $site = strtolower(trim((string) ($rawPrincipal['site'] ?? '')));

    if ($id === '') {
        return app_auth_foundation_principal_error('principal id is required');
    }
    if ($authSource === '') {
        return app_auth_foundation_principal_error('principal auth_source is required');
    }
    if ($site === '') {
        return app_auth_foundation_principal_error('principal site is required');
    }

    return [
        'ok' => true,
        'principal' => [
            'id' => $id,
            'display_name' => $displayName !== '' ? $displayName : $id,
            'auth_source' => $authSource,
            'site' => $site,
            'site_roles' => app_auth_foundation_normalize_site_roles($rawPrincipal['roles'] ?? []),
            'project_roles' => app_auth_foundation_normalize_project_roles($rawPrincipal['project_roles'] ?? []),
        ],
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $rawPrincipal
 * @param list<string> $permissionKeys
 * @return array{
 *     ok:bool,
 *     allowed:bool,
 *     permission_keys:list<string>,
 *     failed_permission_keys:list<string>,
 *     error:string
 * }
 */
function app_auth_foundation_evaluate_permissions(array $rawPrincipal, array $permissionKeys, string $projectKey = ''): array
{
    $normalized = app_auth_foundation_normalize_principal($rawPrincipal);
    if (!$normalized['ok']) {
        return app_auth_foundation_permission_decision(false, [], [], $normalized['error']);
    }

    $keys = app_auth_foundation_normalize_permission_keys($permissionKeys);
    if ($keys === []) {
        return app_auth_foundation_permission_decision(false, [], [], 'at least one permission key is required');
    }

    $requirements = app_auth_foundation_permission_requirements();
    $failed = [];
    foreach ($keys as $permissionKey) {
        if (!array_key_exists($permissionKey, $requirements)) {
            return app_auth_foundation_permission_decision(false, $keys, [$permissionKey], 'unknown permission key: ' . $permissionKey);
        }

        if (!app_auth_foundation_permission_allows($normalized['principal'], $permissionKey, $requirements[$permissionKey], $projectKey)) {
            $failed[] = $permissionKey;
        }
    }

    return app_auth_foundation_permission_decision($failed === [], $keys, $failed, '');
}

/**
 * @return array{
 *     ok:bool,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         auth_source:string,
 *         site:string,
 *         site_roles:list<string>,
 *         project_roles:array<string,list<string>>
 *     },
 *     error:string
 * }
 */
function app_auth_foundation_principal_error(string $error): array
{
    return [
        'ok' => false,
        'principal' => [
            'id' => '',
            'display_name' => '',
            'auth_source' => '',
            'site' => '',
            'site_roles' => [],
            'project_roles' => [],
        ],
        'error' => $error,
    ];
}

/**
 * @param mixed $roles
 * @return list<string>
 */
function app_auth_foundation_normalize_site_roles(mixed $roles): array
{
    if (!is_array($roles)) {
        return [];
    }

    $normalized = [];
    foreach ($roles as $role) {
        if (!is_string($role)) {
            continue;
        }

        $role = strtolower(trim($role));
        if (array_key_exists($role, APP_AUTH_FOUNDATION_SITE_ROLE_ORDER)) {
            $normalized[] = $role;
        }
    }

    return array_values(array_unique($normalized));
}

/**
 * @param mixed $projectRoles
 * @return array<string,list<string>>
 */
function app_auth_foundation_normalize_project_roles(mixed $projectRoles): array
{
    if (!is_array($projectRoles)) {
        return [];
    }

    $normalized = [];
    foreach ($projectRoles as $projectKey => $roles) {
        if (!is_string($projectKey) || !is_array($roles)) {
            continue;
        }

        $normalizedProjectKey = app_normalize_project_key($projectKey);
        if ($normalizedProjectKey === '') {
            continue;
        }

        foreach ($roles as $role) {
            if (!is_string($role)) {
                continue;
            }

            $role = strtolower(trim($role));
            if (array_key_exists($role, APP_PROJECT_PERMISSION_ROLE_ORDER)) {
                $normalized[$normalizedProjectKey][] = $role;
            }
        }
    }

    foreach ($normalized as $projectKey => $roles) {
        $normalized[$projectKey] = array_values(array_unique($roles));
    }

    return $normalized;
}

/**
 * @param list<string> $permissionKeys
 * @return list<string>
 */
function app_auth_foundation_normalize_permission_keys(array $permissionKeys): array
{
    $normalized = [];
    foreach ($permissionKeys as $permissionKey) {
        if (!is_string($permissionKey)) {
            continue;
        }

        $permissionKey = strtolower(trim($permissionKey));
        if ($permissionKey !== '') {
            $normalized[] = $permissionKey;
        }
    }

    return array_values(array_unique($normalized));
}

/**
 * @param array{
 *     site_roles:list<string>,
 *     project_roles:array<string,list<string>>
 * } $principal
 */
function app_auth_foundation_permission_allows(array $principal, string $permissionKey, string $requiredRole, string $projectKey): bool
{
    if (str_starts_with($permissionKey, 'site.')) {
        return app_auth_foundation_site_roles_allow($principal['site_roles'], $requiredRole);
    }

    if (app_auth_foundation_site_roles_allow($principal['site_roles'], 'admin')) {
        return true;
    }

    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '') {
        return false;
    }

    $projectRoles = $principal['project_roles'][$normalizedProjectKey] ?? [];

    return app_project_permission_roles_allow($projectRoles, $requiredRole);
}

/**
 * @param list<string> $roles
 */
function app_auth_foundation_site_roles_allow(array $roles, string $requiredRole): bool
{
    $requiredRank = APP_AUTH_FOUNDATION_SITE_ROLE_ORDER[$requiredRole] ?? 0;
    if ($requiredRank <= 0) {
        return false;
    }

    foreach ($roles as $role) {
        $rank = APP_AUTH_FOUNDATION_SITE_ROLE_ORDER[$role] ?? 0;
        if ($rank >= $requiredRank) {
            return true;
        }
    }

    return false;
}

/**
 * @param list<string> $permissionKeys
 * @param list<string> $failedPermissionKeys
 * @return array{
 *     ok:bool,
 *     allowed:bool,
 *     permission_keys:list<string>,
 *     failed_permission_keys:list<string>,
 *     error:string
 * }
 */
function app_auth_foundation_permission_decision(
    bool $allowed,
    array $permissionKeys,
    array $failedPermissionKeys,
    string $error,
): array {
    return [
        'ok' => $error === '',
        'allowed' => $allowed && $error === '',
        'permission_keys' => $permissionKeys,
        'failed_permission_keys' => $failedPermissionKeys,
        'error' => $error,
    ];
}
