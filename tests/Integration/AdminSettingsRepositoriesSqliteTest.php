<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_membership_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_page_security_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_host_assignment_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class AdminSettingsRepositoriesSqliteTest extends TestCase
{
    public function testMembershipSecurityAndHostAssignmentRepositoriesWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_ADMIN_TEST',
            'name' => 'SQLite Admin Test',
            'slug' => 'sqlite-admin-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'admin settings sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $replaceMembers = app_pdo_replace_project_memberships($app, 'SQLITE_ADMIN_TEST', [
            [
                'login_id' => 'admin@example.test',
                'role_code' => 'admin',
            ],
            [
                'login_id' => 'member@example.test',
                'role_code' => 'member',
            ],
        ]);
        self::assertTrue($replaceMembers['ok'], $replaceMembers['error']);

        $summary = app_pdo_fetch_project_membership_summary($app, 'SQLITE_ADMIN_TEST');
        self::assertTrue($summary['ok'], $summary['error']);
        self::assertSame(3, $summary['item']['unique_user_count'] ?? 0);
        self::assertSame(2, $summary['item']['admin_user_count'] ?? 0);

        $policy = app_pdo_create_project_page_security_policy($app, 'SQLITE_ADMIN_TEST', [
            'server_name' => 'admin.local',
            'script_name' => '/settings',
            'security_types' => ['LoginCookieToken', 'ProjectToken'],
            'notes' => 'sqlite security smoke',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($policy['ok'], $policy['error']);
        self::assertGreaterThan(0, $policy['policy_id']);

        $policyItem = app_pdo_fetch_project_page_security_policy($app, 'SQLITE_ADMIN_TEST', $policy['policy_id']);
        self::assertTrue($policyItem['ok'], $policyItem['error']);
        self::assertSame(['LoginCookieToken', 'ProjectToken'], $policyItem['item']['security_types'] ?? []);

        $policyUpdate = app_pdo_update_project_page_security_policy($app, 'SQLITE_ADMIN_TEST', $policy['policy_id'], [
            'server_name' => 'admin.local',
            'script_name' => '/settings',
            'security_types' => ['ProjectToken'],
            'notes' => 'updated sqlite security smoke',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($policyUpdate['ok'], $policyUpdate['error']);

        $policyCatalog = app_pdo_fetch_project_page_security_policies($app, 'SQLITE_ADMIN_TEST');
        self::assertTrue($policyCatalog['ok'], $policyCatalog['error']);
        self::assertSame(['ProjectToken'], $policyCatalog['items'][0]['security_types'] ?? []);

        $assignment = app_pdo_create_project_host_assignment($app, 'SQLITE_ADMIN_TEST', [
            'apache_setting_name' => 'local',
            'server_local_name' => 'admin',
            'virtual_host_name' => 'admin.local',
            'template_name' => 'default',
            'notes' => 'sqlite host smoke',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($assignment['ok'], $assignment['error']);
        self::assertGreaterThan(0, $assignment['assignment_id']);

        $assignmentUpdate = app_pdo_update_project_host_assignment($app, 'SQLITE_ADMIN_TEST', $assignment['assignment_id'], [
            'apache_setting_name' => 'local',
            'server_local_name' => 'admin',
            'virtual_host_name' => 'admin-updated.local',
            'template_name' => 'default',
            'notes' => 'updated sqlite host smoke',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($assignmentUpdate['ok'], $assignmentUpdate['error']);

        $assignments = app_pdo_fetch_project_host_assignments($app, 'SQLITE_ADMIN_TEST');
        self::assertTrue($assignments['ok'], $assignments['error']);
        self::assertSame('admin-updated.local', $assignments['items'][0]['virtual_host_name'] ?? '');

        self::assertTrue(app_pdo_delete_project_host_assignment($app, 'SQLITE_ADMIN_TEST', $assignment['assignment_id'])['ok']);
        self::assertTrue(app_pdo_delete_project_page_security_policy($app, 'SQLITE_ADMIN_TEST', $policy['policy_id'])['ok']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-admin-settings-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
