<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit_log_repository_pdo.php';
require_once __DIR__ . '/project_identity_membership_repository.php';
require_once __DIR__ . '/project_membership_repository.php';

const APP_PROJECT_PERMISSION_ROLE_ORDER = [
    'viewer' => 10,
    'editor' => 20,
    'publisher' => 30,
    'admin' => 40,
];

const APP_PROJECT_PERMISSION_REQUIREMENTS = [
    'project.read' => 'viewer',
    'project.edit' => 'editor',
    'source_output.review' => 'publisher',
    'source_output.publish' => 'publisher',
    'source_output.publish_request' => 'publisher',
    'source_output.download' => 'publisher',
    'db_source.manage' => 'admin',
    'secret.manage' => 'admin',
    'project.admin' => 'admin',
];

/**
 * @param array{
 *     id:string,
 *     roles:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string
 * } $principal
 * @return array{ok:bool, roles:list<string>, source:string, error:string}
 */
function app_project_permission_roles_for_principal(array $app, string $projectKey, array $principal): array
{
    if (app_auth_has_role('admin', $principal)) {
        return [
            'ok' => true,
            'roles' => ['admin'],
            'source' => 'site-role',
            'error' => '',
        ];
    }

    $claimRoles = app_project_permission_claim_roles_for_principal($projectKey, $principal);
    if ($claimRoles !== []) {
        return [
            'ok' => true,
            'roles' => $claimRoles,
            'source' => 'external-claims',
            'error' => '',
        ];
    }

    $identityRoles = app_fetch_project_identity_roles_for_principal($app, $projectKey, $principal);
    if (!$identityRoles['ok']) {
        return [
            'ok' => false,
            'roles' => [],
            'source' => 'identity-membership',
            'error' => $identityRoles['error'],
        ];
    }
    if ($identityRoles['roles'] !== []) {
        return [
            'ok' => true,
            'roles' => $identityRoles['roles'],
            'source' => 'identity-membership',
            'error' => '',
        ];
    }

    $legacyRole = app_project_permission_legacy_role_for_principal($app, $projectKey, $principal);
    if ($legacyRole['role'] !== '') {
        return [
            'ok' => true,
            'roles' => [$legacyRole['role']],
            'source' => 'legacy-membership',
            'error' => '',
        ];
    }

    return [
        'ok' => true,
        'roles' => [],
        'source' => 'none',
        'error' => '',
    ];
}

/**
 * @param array{
 *     id:string,
 *     roles:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string
 * } $principal
 * @return array{ok:bool, allowed:bool, roles:list<string>, source:string, error:string}
 */
function app_project_permission_can(
    array $app,
    string $projectKey,
    array $principal,
    string $capability,
): array {
    $requiredRole = APP_PROJECT_PERMISSION_REQUIREMENTS[$capability] ?? '';
    if ($requiredRole === '') {
        return [
            'ok' => false,
            'allowed' => false,
            'roles' => [],
            'source' => 'policy',
            'error' => 'unknown project capability: ' . $capability,
        ];
    }

    $roles = app_project_permission_roles_for_principal($app, $projectKey, $principal);
    if (!$roles['ok']) {
        return [
            'ok' => false,
            'allowed' => false,
            'roles' => [],
            'source' => $roles['source'],
            'error' => $roles['error'],
        ];
    }

    return [
        'ok' => true,
        'allowed' => app_project_permission_roles_allow($roles['roles'], $requiredRole),
        'roles' => $roles['roles'],
        'source' => $roles['source'],
        'error' => '',
    ];
}

/**
 * @param array{
 *     id:string,
 *     roles:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string
 * } $principal
 * @return array{ok:bool, allowed:bool, roles:list<string>, source:string, error:string}
 */
function app_project_permission_can_with_audit(
    array $app,
    string $projectKey,
    array $principal,
    string $capability,
    string $targetType = '',
    string $targetKey = '',
): array {
    $decision = app_project_permission_can($app, $projectKey, $principal, $capability);
    app_project_permission_audit_decision($app, $projectKey, $principal, $capability, $decision, $targetType, $targetKey);

    return $decision;
}

/**
 * @param array{id:string,auth_source:string} $principal
 * @param array{ok:bool,allowed:bool,roles:list<string>,source:string,error:string} $decision
 * @return array{ok:bool,item:array<string,mixed>,error:string}
 */
function app_project_permission_audit_decision(
    array $app,
    string $projectKey,
    array $principal,
    string $capability,
    array $decision,
    string $targetType = '',
    string $targetKey = '',
): array {
    $result = !$decision['ok'] ? 'error' : ($decision['allowed'] ? 'success' : 'denied');

    return app_pdo_audit_log_append($app, [
        'actor_login_id' => (string) ($principal['id'] ?? ''),
        'actor_source' => (string) ($principal['auth_source'] ?? 'unknown'),
        'project_key' => $projectKey,
        'event_type' => 'project.permission.decision',
        'target_type' => $targetType !== '' ? $targetType : 'project',
        'target_key' => $targetKey !== '' ? $targetKey : $projectKey,
        'result' => $result,
        'message' => $decision['error'],
        'metadata' => [
            'capability' => $capability,
            'allowed' => (bool) $decision['allowed'],
            'roles' => $decision['roles'],
            'role_source' => $decision['source'],
        ],
    ]);
}

/**
 * @param list<string> $roles
 */
function app_project_permission_roles_allow(array $roles, string $requiredRole): bool
{
    $requiredRank = APP_PROJECT_PERMISSION_ROLE_ORDER[$requiredRole] ?? 0;
    if ($requiredRank <= 0) {
        return false;
    }

    foreach ($roles as $role) {
        $rank = APP_PROJECT_PERMISSION_ROLE_ORDER[$role] ?? 0;
        if ($rank >= $requiredRank) {
            return true;
        }
    }

    return false;
}

/**
 * @param array{project_roles?:array<string,list<string>>} $principal
 * @return list<string>
 */
function app_project_permission_claim_roles_for_principal(string $projectKey, array $principal): array
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    if ($normalizedProjectKey === '' || !is_array($principal['project_roles'] ?? null)) {
        return [];
    }

    $roles = $principal['project_roles'][$normalizedProjectKey] ?? [];
    if (!is_array($roles)) {
        return [];
    }

    $normalizedRoles = [];
    foreach ($roles as $role) {
        if (!is_string($role)) {
            continue;
        }

        $normalizedRole = strtolower(trim($role));
        if (array_key_exists($normalizedRole, APP_PROJECT_PERMISSION_ROLE_ORDER)) {
            $normalizedRoles[] = $normalizedRole;
        }
    }

    return array_values(array_unique($normalizedRoles));
}

/**
 * @param array{id:string} $principal
 * @return array{role:string}
 */
function app_project_permission_legacy_role_for_principal(array $app, string $projectKey, array $principal): array
{
    $summary = app_fetch_project_membership_summary($app, $projectKey);
    if (!$summary['ok'] || !is_array($summary['item'])) {
        return ['role' => ''];
    }

    $principalId = trim((string) ($principal['id'] ?? ''));
    if ($principalId === '') {
        return ['role' => ''];
    }

    $candidates = [];
    if (is_array($summary['item']['owner'] ?? null)) {
        $candidates[] = $summary['item']['owner'];
    }
    foreach (($summary['item']['members'] ?? []) as $member) {
        if (is_array($member)) {
            $candidates[] = $member;
        }
    }

    foreach ($candidates as $candidate) {
        if ((string) ($candidate['login_id'] ?? '') !== $principalId) {
            continue;
        }

        $roleCode = (string) ($candidate['role_code'] ?? '');
        if ($roleCode === 'owner' || $roleCode === 'admin' || (bool) ($candidate['can_administer'] ?? false)) {
            return ['role' => 'admin'];
        }

        return ['role' => 'editor'];
    }

    return ['role' => ''];
}
