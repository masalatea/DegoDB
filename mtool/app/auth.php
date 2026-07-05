<?php

declare(strict_types=1);

require_once __DIR__ . '/request.php';

/**
 * @return array{
 *     id:string,
 *     display_name:string,
 *     roles:list<string>,
 *     scopes?:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string,
 *     site:string
 * }|null
 */
function app_auth_principal(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }

    $principal = $_SESSION['app_principal'] ?? null;
    if (!is_array($principal)) {
        return null;
    }

    $id = $principal['id'] ?? null;
    $displayName = $principal['display_name'] ?? null;
    $roles = $principal['roles'] ?? null;
    $scopes = $principal['scopes'] ?? [];
    $projectRoles = $principal['project_roles'] ?? [];
    $authSource = $principal['auth_source'] ?? null;
    $site = $principal['site'] ?? null;

    if (
        !is_string($id) || $id === ''
        || !is_string($displayName) || $displayName === ''
        || !is_array($roles)
        || !is_string($authSource) || $authSource === ''
        || !is_string($site) || $site === ''
    ) {
        return null;
    }

    $normalizedRoles = [];
    foreach ($roles as $role) {
        if (is_string($role) && $role !== '') {
            $normalizedRoles[] = $role;
        }
    }

    $normalizedScopes = [];
    if (is_array($scopes)) {
        foreach ($scopes as $scope) {
            if (is_string($scope) && $scope !== '') {
                $normalizedScopes[] = $scope;
            }
        }
    }

    $normalizedProjectRoles = [];
    if (is_array($projectRoles)) {
        foreach ($projectRoles as $projectKey => $projectRoleList) {
            if (!is_string($projectKey) || !is_array($projectRoleList)) {
                continue;
            }

            $normalizedRoleList = [];
            foreach ($projectRoleList as $projectRole) {
                if (is_string($projectRole) && $projectRole !== '') {
                    $normalizedRoleList[] = $projectRole;
                }
            }
            if ($normalizedRoleList !== []) {
                $normalizedProjectRoles[$projectKey] = array_values(array_unique($normalizedRoleList));
            }
        }
    }

    return [
        'id' => $id,
        'display_name' => $displayName,
        'roles' => $normalizedRoles,
        'scopes' => array_values(array_unique($normalizedScopes)),
        'project_roles' => $normalizedProjectRoles,
        'auth_source' => $authSource,
        'site' => $site,
    ];
}

function app_auth_is_authenticated(): bool
{
    return app_auth_principal() !== null;
}

/**
 * @param array{
 *     id:string,
 *     display_name:string,
 *     roles:list<string>,
 *     scopes?:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string,
 *     site:string
 * }|null $principal
 */
function app_auth_has_role(string $requiredRole, ?array $principal = null): bool
{
    $principal ??= app_auth_principal();
    if ($principal === null) {
        return false;
    }

    return in_array($requiredRole, $principal['roles'], true);
}

/**
 * @param list<string> $requiredRoles
 * @param array{
 *     id:string,
 *     display_name:string,
 *     roles:list<string>,
 *     scopes?:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string,
 *     site:string
 * }|null $principal
 */
function app_auth_has_any_role(array $requiredRoles, ?array $principal = null): bool
{
    $principal ??= app_auth_principal();
    if ($principal === null) {
        return false;
    }

    foreach ($requiredRoles as $requiredRole) {
        if (in_array($requiredRole, $principal['roles'], true)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array{
 *     site:string,
 *     auth:array{
 *         mode:string,
 *         stub:array{
 *             username:string,
 *             password:string,
 *             display_name:string,
 *             roles:list<string>,
 *             scopes?:list<string>
 *         }
 *     }
 * } $app
 */
function app_auth_attempt_login(array $app, string $username, string $password): bool
{
    return match ($app['auth']['mode']) {
        'stub' => app_auth_attempt_stub_login($app, $username, $password),
        'oidc' => false,
        default => false,
    };
}

/**
 * @param array{
 *     site:string,
 *     auth:array{
 *         stub:array{
 *             username:string,
 *             password:string,
 *             display_name:string,
 *             roles:list<string>,
 *             scopes?:list<string>
 *         }
 *     }
 * } $app
 */
function app_auth_attempt_stub_login(array $app, string $username, string $password): bool
{
    $expectedUsername = $app['auth']['stub']['username'];
    $expectedPassword = $app['auth']['stub']['password'];

    if ($expectedUsername === '' || $expectedPassword === '') {
        return false;
    }

    if (!hash_equals($expectedUsername, trim($username))) {
        return false;
    }

    if (!hash_equals($expectedPassword, $password)) {
        return false;
    }

    app_auth_store_principal([
        'id' => $expectedUsername,
        'display_name' => $app['auth']['stub']['display_name'],
        'roles' => $app['auth']['stub']['roles'],
        'scopes' => is_array($app['auth']['stub']['scopes'] ?? null) ? $app['auth']['stub']['scopes'] : [],
        'auth_source' => 'stub',
        'site' => $app['site'],
    ]);

    return true;
}

/**
 * @param array{
 *     id:string,
 *     display_name:string,
 *     roles:list<string>,
 *     scopes?:list<string>,
 *     project_roles?:array<string,list<string>>,
 *     auth_source:string,
 *     site:string
 * } $principal
 */
function app_auth_store_principal(array $principal): void
{
    app_assert_active_session();

    $_SESSION['app_principal'] = $principal;
    session_regenerate_id(true);
}

function app_auth_logout(): void
{
    app_assert_active_session();

    unset($_SESSION['app_principal']);
    session_regenerate_id(true);
}

/**
 * @param array{
 *     auth:array{
 *         mode:string
 *     }
 * } $app
 */
function app_auth_mode_summary(array $app): string
{
    return match ($app['auth']['mode']) {
        'stub' => 'ローカル Docker 用のスタブ認証',
        'oidc' => 'OpenID Connect 認証',
        default => '未定義の認証モード',
    };
}

function app_auth_login_path(): string
{
    return '/login';
}

function app_auth_dashboard_path(): string
{
    return '/dashboard';
}

function app_auth_logout_path(): string
{
    return '/logout';
}

/**
 * @param array{
 *     method:string,
 *     path:string,
 *     query_string:string
 * } $request
 */
function app_auth_current_target(array $request): string
{
    return app_normalize_local_path(app_request_path_with_query($request), app_auth_dashboard_path());
}

/**
 * @param array{
 *     method:string
 * } $request
 */
function app_auth_requested_path(array $request, string $defaultPath): string
{
    if (app_request_method_is($request, 'POST')) {
        return app_normalize_local_path(
            app_post_param('redirect', $defaultPath),
            $defaultPath,
        );
    }

    return app_normalize_local_path(
        app_query_param('redirect', $defaultPath),
        $defaultPath,
    );
}
