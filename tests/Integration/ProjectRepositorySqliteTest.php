<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class ProjectRepositorySqliteTest extends TestCase
{
    public function testProjectRepositoryCanInsertFetchAndUpdateWithSqliteConfigStore(): void
    {
        $storeDir = sys_get_temp_dir() . '/dego-project-repository-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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

        $insert = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_TEST',
            'name' => 'SQLite Test',
            'slug' => 'sqlite-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'created on sqlite config store',
        ]);
        self::assertTrue($insert['ok'], $insert['error']);

        $item = app_pdo_fetch_project_by_key($app, 'SQLITE_TEST');
        self::assertTrue($item['ok'], $item['error']);
        self::assertSame('SQLite Test', $item['item']['name'] ?? '');
        self::assertSame(1, $item['item']['member_count'] ?? 0);

        $update = app_pdo_update_project($app, [
            'project_key' => 'SQLITE_TEST',
            'name' => 'SQLite Test Updated',
            'slug' => 'sqlite-test-updated',
            'lifecycle_status' => 'active',
            'description' => 'updated on sqlite config store',
        ]);
        self::assertTrue($update['ok'], $update['error']);

        $updated = app_pdo_fetch_project_by_key($app, 'SQLITE_TEST');
        self::assertTrue($updated['ok'], $updated['error']);
        self::assertSame('SQLite Test Updated', $updated['item']['name'] ?? '');
        self::assertSame('sqlite-test-updated', $updated['item']['slug'] ?? '');
    }
}
