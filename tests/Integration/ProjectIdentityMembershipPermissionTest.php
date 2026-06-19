<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/audit_log_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_identity_membership_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_membership_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_permission.php';

use PHPUnit\Framework\TestCase;

final class ProjectIdentityMembershipPermissionTest extends TestCase
{
    public function testIdentityMembershipDrivesProjectPermissionsAheadOfLegacyMembership(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SSO-MEMBERSHIP',
            'name' => 'SSO Membership',
            'slug' => 'sso-membership',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'legacy-owner@example.test',
            'description' => 'identity membership permission smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $legacy = app_replace_project_memberships($app, 'SSO-MEMBERSHIP', [
            [
                'login_id' => 'oidc-user-1',
                'role_code' => 'member',
            ],
        ]);
        self::assertTrue($legacy['ok'], $legacy['error']);

        $replace = app_replace_project_identity_memberships($app, 'SSO-MEMBERSHIP', [
            [
                'principal_source' => 'oidc',
                'principal_subject' => 'oidc-user-1',
                'role_code' => 'publisher',
                'source_of_truth' => 'test',
            ],
            [
                'principal_source' => 'oidc',
                'principal_subject' => 'oidc-user-2',
                'role_code' => 'viewer',
                'source_of_truth' => 'test',
            ],
        ]);
        self::assertTrue($replace['ok'], $replace['error']);

        $items = app_fetch_project_identity_memberships($app, 'SSO-MEMBERSHIP');
        self::assertTrue($items['ok'], $items['error']);
        self::assertSame(['oidc-user-1', 'oidc-user-2'], array_values(array_unique(array_map(
            static fn (array $item): string => (string) $item['principal_subject'],
            $items['items'],
        ))));

        $publisher = [
            'id' => 'oidc-user-1',
            'display_name' => 'Publisher',
            'roles' => [],
            'auth_source' => 'oidc',
            'site' => 'admin',
        ];
        $publishPermission = app_project_permission_can($app, 'SSO-MEMBERSHIP', $publisher, 'source_output.publish');
        self::assertTrue($publishPermission['ok'], $publishPermission['error']);
        self::assertTrue($publishPermission['allowed']);
        self::assertSame(['publisher'], $publishPermission['roles']);
        self::assertSame('identity-membership', $publishPermission['source']);

        $viewer = [
            'id' => 'oidc-user-2',
            'display_name' => 'Viewer',
            'roles' => [],
            'auth_source' => 'oidc',
            'site' => 'admin',
        ];
        $editPermission = app_project_permission_can($app, 'SSO-MEMBERSHIP', $viewer, 'project.edit');
        self::assertTrue($editPermission['ok'], $editPermission['error']);
        self::assertFalse($editPermission['allowed']);
        self::assertSame(['viewer'], $editPermission['roles']);
    }

    public function testExternalProjectClaimsDrivePermissionsAheadOfLocalOverride(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'CLAIM-FIRST',
            'name' => 'Claim First',
            'slug' => 'claim-first',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'legacy-owner@example.test',
            'description' => 'external claim permission smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $replace = app_replace_project_identity_memberships($app, 'CLAIM-FIRST', [
            [
                'principal_source' => 'oidc',
                'principal_subject' => 'oidc-user-claims',
                'role_code' => 'viewer',
                'source_of_truth' => 'local-override',
            ],
        ]);
        self::assertTrue($replace['ok'], $replace['error']);

        $principal = [
            'id' => 'oidc-user-claims',
            'display_name' => 'Claims User',
            'roles' => [],
            'project_roles' => [
                'CLAIM-FIRST' => ['publisher'],
            ],
            'auth_source' => 'oidc',
            'site' => 'admin',
        ];
        $publishPermission = app_project_permission_can($app, 'CLAIM-FIRST', $principal, 'source_output.publish');
        self::assertTrue($publishPermission['ok'], $publishPermission['error']);
        self::assertTrue($publishPermission['allowed']);
        self::assertSame(['publisher'], $publishPermission['roles']);
        self::assertSame('external-claims', $publishPermission['source']);
    }

    public function testAuditedPermissionDecisionRecordsOidcPrincipalAndCapability(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'AUDIT-SSO',
            'name' => 'Audit SSO',
            'slug' => 'audit-sso',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'legacy-owner@example.test',
            'description' => 'sso audit permission smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $principal = [
            'id' => 'oidc-audited-user',
            'display_name' => 'Audited User',
            'roles' => [],
            'project_roles' => [
                'AUDIT-SSO' => ['publisher'],
            ],
            'auth_source' => 'oidc',
            'site' => 'admin',
        ];
        $permission = app_project_permission_can_with_audit(
            $app,
            'AUDIT-SSO',
            $principal,
            'source_output.publish',
            'source_output',
            'OPENAPI-JSON',
        );
        self::assertTrue($permission['ok'], $permission['error']);
        self::assertTrue($permission['allowed']);

        $latest = app_pdo_audit_log_fetch_latest($app, [
            'event_type' => 'project.permission.decision',
            'limit' => 1,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        $event = $latest['items'][0];
        self::assertSame('oidc-audited-user', $event['actor_login_id']);
        self::assertSame('oidc', $event['actor_source']);
        self::assertSame('AUDIT-SSO', $event['project_key']);
        self::assertSame('source_output', $event['target_type']);
        self::assertSame('OPENAPI-JSON', $event['target_key']);
        self::assertSame('success', $event['result']);
        self::assertSame('source_output.publish', $event['metadata']['capability'] ?? '');
        self::assertSame('external-claims', $event['metadata']['role_source'] ?? '');
    }

    public function testLegacyMembershipRemainsCompatibilityFallbackOnly(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'LEGACY-MEMBER',
            'name' => 'Legacy Member',
            'slug' => 'legacy-member',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'legacy membership fallback smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $legacy = app_replace_project_memberships($app, 'LEGACY-MEMBER', [
            [
                'login_id' => 'legacy-editor@example.test',
                'role_code' => 'member',
            ],
        ]);
        self::assertTrue($legacy['ok'], $legacy['error']);

        $principal = [
            'id' => 'legacy-editor@example.test',
            'display_name' => 'Legacy Editor',
            'roles' => [],
            'auth_source' => 'stub',
            'site' => 'admin',
        ];
        $editPermission = app_project_permission_can($app, 'LEGACY-MEMBER', $principal, 'project.edit');
        self::assertTrue($editPermission['ok'], $editPermission['error']);
        self::assertTrue($editPermission['allowed']);
        self::assertSame(['editor'], $editPermission['roles']);
        self::assertSame('legacy-membership', $editPermission['source']);

        $publishPermission = app_project_permission_can($app, 'LEGACY-MEMBER', $principal, 'source_output.publish');
        self::assertTrue($publishPermission['ok'], $publishPermission['error']);
        self::assertFalse($publishPermission['allowed']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-project-identity-membership-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
        $app = [
            'site' => 'admin',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        return $app;
    }
}
