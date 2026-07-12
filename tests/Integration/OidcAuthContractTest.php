<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/auth_oidc.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_user_identity.php';

use PHPUnit\Framework\TestCase;

final class OidcAuthContractTest extends TestCase
{
    public function testOidcConfigAndAuthorizationRequestAreStable(): void
    {
        $app = $this->oidcApp();
        self::assertSame('', app_auth_oidc_validate_config($app));

        $request = app_auth_oidc_authorization_request($app, '/projects/MTOOL', [
            'authorization_endpoint' => 'https://idp.example.test/oauth2/v1/authorize',
        ]);

        self::assertSame('/projects/MTOOL', $request['redirect']);
        self::assertNotSame('', $request['state']);
        self::assertNotSame('', $request['nonce']);

        $query = [];
        parse_str((string) parse_url($request['authorization_url'], PHP_URL_QUERY), $query);
        self::assertSame('code', $query['response_type'] ?? '');
        self::assertSame('client-web', $query['client_id'] ?? '');
        self::assertSame('https://app.example.test/auth/oidc/callback', $query['redirect_uri'] ?? '');
        self::assertSame('openid profile email', $query['scope'] ?? '');
        self::assertSame($request['state'], $query['state'] ?? '');
        self::assertSame($request['nonce'], $query['nonce'] ?? '');
    }

    public function testOidcClaimsProducePrincipalWithMappedRoles(): void
    {
        $principal = app_auth_oidc_principal_from_claims($this->oidcApp(), [
            'iss' => 'https://idp.example.test',
            'sub' => 'user-123',
            'name' => 'Editor User',
            'email' => 'editor@example.test',
            'groups' => [
                'dego-editor',
                'dego-publisher',
                'dego:project:SSO-MEMBERSHIP:publisher',
                'dego:project:reporting-team:viewer',
            ],
        ]);

        self::assertSame('user-123', $principal['id']);
        self::assertSame('https://idp.example.test', $principal['issuer']);
        self::assertSame('user-123', $principal['subject']);
        self::assertSame('Editor User', $principal['display_name']);
        self::assertSame('editor@example.test', $principal['email']);
        self::assertSame('oidc', $principal['auth_source']);
        self::assertSame(['config', 'lab'], $principal['roles']);
        self::assertSame([
            'REPORTING-TEAM' => ['viewer'],
            'SSO-MEMBERSHIP' => ['publisher'],
        ], $principal['project_roles']);

        $identity = app_local_user_identity_from_principal($principal, [
            'device_id' => 'browser-device-1',
            'profile_cached_at' => '2026-07-13T00:00:00+00:00',
            'last_authenticated_at' => '2026-07-13T00:00:00+00:00',
        ]);
        self::assertTrue($identity['ok'], $identity['error']);
        self::assertSame('https://idp.example.test', $identity['identity']['issuer'] ?? '');
        self::assertSame('user-123', $identity['identity']['subject'] ?? '');
        self::assertSame('editor@example.test', $identity['identity']['email'] ?? '');
        self::assertSame('browser-device-1', $identity['identity']['device_id'] ?? '');
    }

    /**
     * @return array<string,mixed>
     */
    private function oidcApp(): array
    {
        return [
            'site' => 'admin',
            'auth' => [
                'mode' => 'oidc',
                'oidc' => [
                    'issuer' => 'https://idp.example.test',
                    'client_id' => 'client-web',
                    'client_secret' => 'secret',
                    'redirect_uri' => 'https://app.example.test/auth/oidc/callback',
                    'scopes' => ['openid', 'profile', 'email'],
                    'groups_claim' => 'groups',
                    'admin_groups' => ['dego-admin'],
                    'config_groups' => ['dego-editor'],
                    'lab_groups' => ['dego-publisher'],
                    'project_role_group_prefix' => 'dego:project:',
                    'default_roles' => ['lab'],
                ],
            ],
        ];
    }
}
