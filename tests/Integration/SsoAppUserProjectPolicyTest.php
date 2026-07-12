<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_project_policy_repository.php';

final class SsoAppUserProjectPolicyTest extends TestCase
{
    public function testAbsentPolicyIsCompatibleAndDoesNotOptIn(): void
    {
        [$app, $projectKey] = $this->project();
        $result = app_fetch_sso_app_user_project_policy($app, $projectKey);

        self::assertTrue($result['ok'], $result['error']);
        self::assertNull($result['item']);
    }

    public function testEnabledPolicyRoundTripsAsNormalizedNonSecretContract(): void
    {
        [$app, $projectKey] = $this->project();
        $saved = app_upsert_sso_app_user_project_policy($app, $projectKey, [
            'enabled' => true,
            'auth_mode' => 'OIDC',
            'provisioning_mode' => 'jit',
            'provider_key' => 'PRIMARY-OIDC',
            'sso_profile_fields' => ['Email', 'display_name', 'email'],
            'application_profile_fields' => ['nickname'],
            'user_owned_data' => ['saved_item'],
            'tenant_boundary' => '',
            'lifecycle_custom_boundary' => ['identity-link', 'retention'],
        ], 'test');

        self::assertTrue($saved['ok'], $saved['error']);
        self::assertSame([], $saved['warnings']);
        self::assertSame(APP_SSO_APP_USER_PROJECT_POLICY_VERSION, $saved['item']['policy']['contract_version']);
        self::assertSame('oidc', $saved['item']['policy']['auth_mode']);
        self::assertSame('primary-oidc', $saved['item']['policy']['provider_key']);
        self::assertSame(['email', 'display_name'], $saved['item']['policy']['sso_profile_fields']);
        self::assertSame('test', $saved['item']['source_of_truth']);

        $updated = app_upsert_sso_app_user_project_policy($app, $projectKey, [
            'enabled' => false,
            'auth_mode' => 'oidc',
            'provisioning_mode' => 'jit',
            'provider_key' => 'primary-oidc',
            'sso_profile_fields' => ['email'],
            'application_profile_fields' => [],
            'user_owned_data' => [],
            'lifecycle_custom_boundary' => [],
        ]);
        self::assertTrue($updated['ok'], $updated['error']);
        self::assertFalse($updated['item']['policy']['enabled']);
        self::assertCount(1, $updated['warnings']);
    }

    public function testUnsafeOrIncompleteEnabledPolicyFailsWithoutPersistence(): void
    {
        [$app, $projectKey] = $this->project();
        $result = app_upsert_sso_app_user_project_policy($app, $projectKey, [
            'enabled' => true,
            'auth_mode' => 'oidc',
            'provisioning_mode' => '',
            'provider_key' => '',
            'sso_profile_fields' => ['email', 'refresh_token'],
            'application_profile_fields' => ['email'],
            'lifecycle_custom_boundary' => ['email-auto-link'],
            'client_secret' => 'must-not-be-accepted',
        ]);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('forbidden profile fields', $result['error']);
        self::assertStringContainsString('ownership overlaps', $result['error']);
        self::assertStringContainsString('email-based automatic identity linking', $result['error']);
        self::assertStringContainsString('provisioning_mode', $result['error']);
        self::assertStringContainsString('provider_key', $result['error']);
        self::assertStringContainsString('unknown policy fields: client_secret', $result['error']);

        $fetched = app_fetch_sso_app_user_project_policy($app, $projectKey);
        self::assertTrue($fetched['ok'], $fetched['error']);
        self::assertNull($fetched['item']);
    }

    /** @return array{0:array<string,mixed>,1:string} */
    private function project(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-sso-app-user-policy-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            $storeDir,
        );
        $app = ['site' => 'admin', 'db' => $configDb, 'config_db' => $configDb];
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $projectKey = 'SSO-POLICY-' . strtoupper(bin2hex(random_bytes(3)));
        $project = app_pdo_insert_project($app, [
            'project_key' => $projectKey,
            'name' => 'SSO Policy',
            'slug' => strtolower($projectKey),
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'SSO app-user project policy test',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        return [$app, $projectKey];
    }
}
