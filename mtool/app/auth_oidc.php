<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/audit_log_repository_pdo.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/request.php';

function app_require_composer_autoload(): void
{
    $autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
    if (!is_file($autoloadPath)) {
        throw new RuntimeException('Composer dependencies are not installed. Run `composer install`.');
    }

    require_once $autoloadPath;
}

function app_auth_oidc_callback_path(): string
{
    return '/auth/oidc/callback';
}

/**
 * @param array{
 *     auth:array{
 *         oidc:array{
 *             issuer:string,
 *             client_id:string,
 *             client_secret:string,
 *             redirect_uri:string,
 *             scopes:list<string>
 *         }
 *     }
 * } $app
 */
function app_auth_oidc_validate_config(array $app): string
{
    $oidc = $app['auth']['oidc'] ?? [];
    foreach (['issuer', 'client_id', 'client_secret', 'redirect_uri'] as $key) {
        if (trim((string) ($oidc[$key] ?? '')) === '') {
            return 'OIDC config is missing: APP_AUTH_OIDC_' . strtoupper($key);
        }
    }

    if (($oidc['scopes'] ?? []) === []) {
        return 'OIDC scope must include at least `openid`.';
    }

    if (!in_array('openid', $oidc['scopes'], true)) {
        return 'OIDC scope must include `openid`.';
    }

    return '';
}

/**
 * @return array<string,mixed>
 */
function app_auth_oidc_discovery(array $app): array
{
    $issuer = rtrim((string) ($app['auth']['oidc']['issuer'] ?? ''), '/');
    $discoveryUrl = $issuer . '/.well-known/openid-configuration';
    $decoded = app_auth_oidc_fetch_json($discoveryUrl);

    foreach (['authorization_endpoint', 'token_endpoint', 'jwks_uri', 'issuer'] as $key) {
        if (trim((string) ($decoded[$key] ?? '')) === '') {
            throw new RuntimeException('OIDC discovery document is missing: ' . $key);
        }
    }

    if (rtrim((string) $decoded['issuer'], '/') !== $issuer) {
        throw new RuntimeException('OIDC issuer mismatch.');
    }

    return $decoded;
}

/**
 * @return array<string,mixed>
 */
function app_auth_oidc_fetch_json(string $url): array
{
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('curl init failed.');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if (!is_string($raw) || $raw === '' || $status < 200 || $status >= 300) {
        throw new RuntimeException('OIDC JSON fetch failed: HTTP ' . $status . ($error !== '' ? ' ' . $error : ''));
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('OIDC JSON response is invalid.');
    }

    return $decoded;
}

/**
 * @return array<string,mixed>
 */
function app_auth_oidc_post_token(string $tokenEndpoint, array $fields): array
{
    $ch = curl_init($tokenEndpoint);
    if ($ch === false) {
        throw new RuntimeException('curl init failed.');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields, '', '&', PHP_QUERY_RFC3986),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if (!is_string($raw) || $raw === '' || $status < 200 || $status >= 300) {
        throw new RuntimeException('OIDC token exchange failed: HTTP ' . $status . ($error !== '' ? ' ' . $error : ''));
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('OIDC token response is invalid.');
    }

    return $decoded;
}

function app_auth_oidc_begin(array $app, array $request, string $redirectPath): void
{
    $configError = app_auth_oidc_validate_config($app);
    if ($configError !== '') {
        throw new RuntimeException($configError);
    }

    $discovery = app_auth_oidc_discovery($app);
    $authorizationRequest = app_auth_oidc_authorization_request($app, $redirectPath, $discovery);
    $_SESSION['app_oidc'] = [
        'state' => $authorizationRequest['state'],
        'nonce' => $authorizationRequest['nonce'],
        'redirect' => $authorizationRequest['redirect'],
    ];

    app_send_redirect_response($request, $authorizationRequest['authorization_url']);
}

/**
 * @return array{
 *     state:string,
 *     nonce:string,
 *     redirect:string,
 *     authorization_url:string
 * }
 */
function app_auth_oidc_authorization_request(array $app, string $redirectPath, array $discovery): array
{
    $state = bin2hex(random_bytes(16));
    $nonce = bin2hex(random_bytes(16));
    $redirect = app_normalize_local_path($redirectPath, app_auth_dashboard_path());
    $authUrl = (string) $discovery['authorization_endpoint'] . '?' . http_build_query([
        'response_type' => 'code',
        'client_id' => (string) $app['auth']['oidc']['client_id'],
        'redirect_uri' => (string) $app['auth']['oidc']['redirect_uri'],
        'scope' => implode(' ', $app['auth']['oidc']['scopes']),
        'state' => $state,
        'nonce' => $nonce,
    ], '', '&', PHP_QUERY_RFC3986);

    return [
        'state' => $state,
        'nonce' => $nonce,
        'redirect' => $redirect,
        'authorization_url' => $authUrl,
    ];
}

function app_auth_oidc_handle_callback(array $app, array $request): void
{
    $state = app_query_param('state');
    $code = app_query_param('code');
    $session = $_SESSION['app_oidc'] ?? null;
    if (!is_array($session) || !hash_equals((string) ($session['state'] ?? ''), $state) || $code === '') {
        unset($_SESSION['app_oidc']);
        throw new RuntimeException('OIDC callback state is invalid.');
    }

    $discovery = app_auth_oidc_discovery($app);
    $tokenResponse = app_auth_oidc_post_token((string) $discovery['token_endpoint'], [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => (string) $app['auth']['oidc']['redirect_uri'],
        'client_id' => (string) $app['auth']['oidc']['client_id'],
        'client_secret' => (string) $app['auth']['oidc']['client_secret'],
    ]);

    $idToken = trim((string) ($tokenResponse['id_token'] ?? ''));
    if ($idToken === '') {
        throw new RuntimeException('OIDC token response does not include id_token.');
    }

    $claims = app_auth_oidc_verify_id_token($app, (string) $discovery['jwks_uri'], $idToken, (string) $session['nonce']);
    $redirect = app_auth_oidc_complete_with_claims($app, $claims, (string) ($session['redirect'] ?? ''));
    unset($_SESSION['app_oidc']);
    app_send_redirect_response($request, $redirect);
}

/**
 * Stores an authenticated principal after OIDC protocol validation has already
 * verified state, nonce, issuer, and audience.
 *
 * @param array<string,mixed> $claims
 */
function app_auth_oidc_complete_with_claims(array $app, array $claims, string $redirectPath): string
{
    $principal = app_auth_oidc_principal_from_claims($app, $claims);
    app_auth_store_principal($principal);
    app_auth_oidc_audit_login($app, $principal);

    return app_normalize_local_path($redirectPath, app_auth_dashboard_path());
}

/**
 * @param array{id:string,roles:list<string>,project_roles?:array<string,list<string>>,auth_source:string,site:string} $principal
 * @return array{ok:bool,item:array<string,mixed>,error:string}
 */
function app_auth_oidc_audit_login(array $app, array $principal): array
{
    try {
        return app_pdo_audit_log_append($app, [
            'actor_login_id' => (string) $principal['id'],
            'actor_source' => (string) $principal['auth_source'],
            'project_key' => '',
            'event_type' => 'auth.oidc.login',
            'target_type' => 'session',
            'target_key' => (string) ($principal['site'] ?? ''),
            'result' => 'success',
            'message' => '',
            'metadata' => [
                'site_roles' => $principal['roles'],
                'project_role_keys' => array_keys($principal['project_roles'] ?? []),
            ],
        ]);
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array<string,mixed>
 */
function app_auth_oidc_verify_id_token(array $app, string $jwksUri, string $idToken, string $nonce): array
{
    app_require_composer_autoload();
    $jwks = app_auth_oidc_fetch_json($jwksUri);
    $decoded = \Firebase\JWT\JWT::decode($idToken, \Firebase\JWT\JWK::parseKeySet($jwks));
    $claims = json_decode(json_encode($decoded, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($claims)) {
        throw new RuntimeException('OIDC id_token claims are invalid.');
    }

    $issuer = rtrim((string) ($app['auth']['oidc']['issuer'] ?? ''), '/');
    if (rtrim((string) ($claims['iss'] ?? ''), '/') !== $issuer) {
        throw new RuntimeException('OIDC id_token issuer mismatch.');
    }

    $audience = $claims['aud'] ?? null;
    $clientId = (string) ($app['auth']['oidc']['client_id'] ?? '');
    $audiences = is_array($audience) ? $audience : [$audience];
    if (!in_array($clientId, $audiences, true)) {
        throw new RuntimeException('OIDC id_token audience mismatch.');
    }

    if (!hash_equals($nonce, (string) ($claims['nonce'] ?? ''))) {
        throw new RuntimeException('OIDC id_token nonce mismatch.');
    }

    return $claims;
}

/**
 * @param array<string,mixed> $claims
 * @return array{
 *     id:string,
 *     issuer:string,
 *     subject:string,
 *     display_name:string,
 *     email:string,
 *     roles:list<string>,
 *     project_roles:array<string,list<string>>,
 *     auth_source:string,
 *     site:string
 * }
 */
function app_auth_oidc_principal_from_claims(array $app, array $claims): array
{
    $id = trim((string) ($claims['sub'] ?? ''));
    if ($id === '') {
        throw new RuntimeException('OIDC id_token subject is missing.');
    }

    $displayName = trim((string) ($claims['name'] ?? $claims['preferred_username'] ?? $claims['email'] ?? $id));
    $issuer = rtrim(trim((string) ($claims['iss'] ?? $app['auth']['oidc']['issuer'] ?? '')), '/');
    $email = trim((string) ($claims['email'] ?? ''));
    $groups = app_auth_oidc_claim_values($claims[$app['auth']['oidc']['groups_claim']] ?? []);
    $roles = app_auth_oidc_roles_from_groups($app, $groups);
    $projectRoles = app_auth_oidc_project_roles_from_groups($app, $groups);

    return [
        'id' => $id,
        'issuer' => $issuer,
        'subject' => $id,
        'display_name' => $displayName !== '' ? $displayName : $id,
        'email' => $email,
        'roles' => $roles,
        'project_roles' => $projectRoles,
        'auth_source' => 'oidc',
        'site' => $app['site'],
    ];
}

/**
 * @return list<string>
 */
function app_auth_oidc_claim_values(mixed $value): array
{
    if (is_string($value)) {
        return $value === '' ? [] : [$value];
    }

    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        if (is_string($item) && trim($item) !== '') {
            $items[] = trim($item);
        }
    }

    return array_values(array_unique($items));
}

/**
 * @param list<string> $groups
 * @return list<string>
 */
function app_auth_oidc_roles_from_groups(array $app, array $groups): array
{
    $roles = [];
    $map = [
        'admin' => $app['auth']['oidc']['admin_groups'] ?? [],
        'config' => $app['auth']['oidc']['config_groups'] ?? [],
        'lab' => $app['auth']['oidc']['lab_groups'] ?? [],
    ];

    foreach ($map as $role => $allowedGroups) {
        foreach ($allowedGroups as $group) {
            if (in_array($group, $groups, true)) {
                $roles[] = $role;
                break;
            }
        }
    }

    if ($roles === []) {
        $roles = $app['auth']['oidc']['default_roles'] ?? [];
    }

    return array_values(array_unique(array_filter(
        $roles,
        static fn (string $role): bool => $role !== '',
    )));
}

/**
 * @param list<string> $groups
 * @return array<string,list<string>>
 */
function app_auth_oidc_project_roles_from_groups(array $app, array $groups): array
{
    $prefix = (string) ($app['auth']['oidc']['project_role_group_prefix'] ?? 'dego:project:');
    if ($prefix === '') {
        return [];
    }

    $projectRoles = [];
    foreach ($groups as $group) {
        if (!str_starts_with($group, $prefix)) {
            continue;
        }

        $tail = substr($group, strlen($prefix));
        $separatorPosition = strrpos($tail, ':');
        if ($separatorPosition === false) {
            continue;
        }

        $projectKey = app_auth_oidc_normalize_project_role_key(substr($tail, 0, $separatorPosition));
        $roleCode = strtolower(trim(substr($tail, $separatorPosition + 1)));
        if ($projectKey === '' || !in_array($roleCode, ['viewer', 'editor', 'publisher', 'admin'], true)) {
            continue;
        }

        $projectRoles[$projectKey] ??= [];
        $projectRoles[$projectKey][] = $roleCode;
    }

    foreach ($projectRoles as $projectKey => $roles) {
        $projectRoles[$projectKey] = array_values(array_unique($roles));
    }

    ksort($projectRoles);
    return $projectRoles;
}

function app_auth_oidc_normalize_project_role_key(string $projectKey): string
{
    $normalized = app_normalize_project_key($projectKey);

    return app_project_key_is_valid($normalized) ? $normalized : '';
}
