<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/auth_foundation.php';

use PHPUnit\Framework\TestCase;

final class AuthFoundationContractTest extends TestCase
{
    public function testLegacyProjectUserPermissionBitsAreInventoryOnlyAndCollapseToPermissionKeys(): void
    {
        $inventory = app_auth_foundation_legacy_permission_unit_inventory();

        self::assertCount(16, $inventory);
        self::assertSame([
            'dbtoolRead',
            'dbtoolWrite',
            'htmlRead',
            'htmlWrite',
            'testtoolRead',
            'testtoolWrite',
            'spectoolRead',
            'spectoolWrite',
            'ReqRead',
            'ReqWrite',
            'ChatRead',
            'ChatWrite',
            'MinutesRead',
            'MinutesWrite',
            'UploadRead',
            'UploadWrite',
        ], array_column($inventory, 'legacy_field'));
        self::assertContains('project.read', array_column($inventory, 'permission_key'));
        self::assertContains('project.edit', array_column($inventory, 'permission_key'));
        self::assertContains('source_output.download', array_column($inventory, 'permission_key'));
        self::assertContains('source_output.publish', array_column($inventory, 'permission_key'));

        $requirements = app_auth_foundation_permission_requirements();
        foreach (array_column($inventory, 'legacy_field') as $legacyField) {
            self::assertArrayNotHasKey($legacyField, $requirements);
        }
    }

    public function testPrincipalNormalizationKeepsStableIdentityAndKnownRolesOnly(): void
    {
        $normalized = app_auth_foundation_normalize_principal([
            'id' => ' user-123 ',
            'display_name' => ' Test User ',
            'auth_source' => ' OIDC ',
            'site' => ' Admin ',
            'roles' => ['CONFIG', 'unknown', 'config', 'lab'],
            'project_roles' => [
                'sample-project' => ['Viewer', 'bad-role', 'publisher', 'viewer'],
                '' => ['admin'],
            ],
        ]);

        self::assertTrue($normalized['ok'], $normalized['error']);
        self::assertSame('user-123', $normalized['principal']['id']);
        self::assertSame('Test User', $normalized['principal']['display_name']);
        self::assertSame('oidc', $normalized['principal']['auth_source']);
        self::assertSame('admin', $normalized['principal']['site']);
        self::assertSame(['config', 'lab'], $normalized['principal']['site_roles']);
        self::assertSame([
            'SAMPLE-PROJECT' => ['viewer', 'publisher'],
        ], $normalized['principal']['project_roles']);
    }

    public function testAuthorizationEvaluatorRequiresAllPermissionKeys(): void
    {
        $principal = [
            'id' => 'editor-1',
            'display_name' => 'Editor',
            'auth_source' => 'oidc',
            'site' => 'admin',
            'roles' => [],
            'project_roles' => [
                'AUTH-PROJECT' => ['editor'],
            ],
        ];

        $readAndEdit = app_auth_foundation_evaluate_permissions($principal, ['project.read', 'project.edit'], 'AUTH-PROJECT');
        self::assertTrue($readAndEdit['ok'], $readAndEdit['error']);
        self::assertTrue($readAndEdit['allowed']);
        self::assertSame([], $readAndEdit['failed_permission_keys']);

        $publishToo = app_auth_foundation_evaluate_permissions($principal, ['project.read', 'source_output.publish'], 'AUTH-PROJECT');
        self::assertTrue($publishToo['ok'], $publishToo['error']);
        self::assertFalse($publishToo['allowed']);
        self::assertSame(['source_output.publish'], $publishToo['failed_permission_keys']);
    }

    public function testAuthorizationEvaluatorFailsClosedForUnknownOrUnscopedInput(): void
    {
        $principal = [
            'id' => 'publisher-1',
            'display_name' => 'Publisher',
            'auth_source' => 'oidc',
            'site' => 'admin',
            'roles' => [],
            'project_roles' => [
                'AUTH-PROJECT' => ['publisher'],
            ],
        ];

        $unknown = app_auth_foundation_evaluate_permissions($principal, ['project.read', 'legacy.dbtoolRead'], 'AUTH-PROJECT');
        self::assertFalse($unknown['ok']);
        self::assertFalse($unknown['allowed']);
        self::assertSame(['legacy.dbtoolread'], $unknown['failed_permission_keys']);
        self::assertStringContainsString('unknown permission key', $unknown['error']);

        $missingProjectScope = app_auth_foundation_evaluate_permissions($principal, ['project.read']);
        self::assertTrue($missingProjectScope['ok'], $missingProjectScope['error']);
        self::assertFalse($missingProjectScope['allowed']);
        self::assertSame(['project.read'], $missingProjectScope['failed_permission_keys']);

        $missingPrincipal = app_auth_foundation_evaluate_permissions([
            'display_name' => 'No ID',
            'auth_source' => 'oidc',
            'site' => 'admin',
        ], ['site.admin']);
        self::assertFalse($missingPrincipal['ok']);
        self::assertFalse($missingPrincipal['allowed']);
        self::assertStringContainsString('principal id is required', $missingPrincipal['error']);
    }

    public function testSiteAdminCanServeAsBreakGlassForProjectPermissions(): void
    {
        $decision = app_auth_foundation_evaluate_permissions([
            'id' => 'site-admin',
            'display_name' => 'Site Admin',
            'auth_source' => 'stub',
            'site' => 'admin',
            'roles' => ['admin'],
            'project_roles' => [],
        ], ['project.read', 'source_output.publish'], 'AUTH-PROJECT');

        self::assertTrue($decision['ok'], $decision['error']);
        self::assertTrue($decision['allowed']);
    }
}
