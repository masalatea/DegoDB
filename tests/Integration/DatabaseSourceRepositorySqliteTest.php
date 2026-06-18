<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database_source_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class DatabaseSourceRepositorySqliteTest extends TestCase
{
    public function testDatabaseSourceRepositoryCrudWorksWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();

        $create = app_pdo_create_database_source($app, [
            'source_key' => 'external-test',
            'label' => 'External Test',
            'description' => 'sqlite repository smoke',
            'host' => 'db.example.test',
            'port' => '3306',
            'database_name' => 'app_db',
            'user_name' => 'app_user',
            'password' => 'secret',
            'supports_live_schema_import' => true,
            'supports_proxy_runtime_read' => false,
            'proxy_runtime_priority' => 100,
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($create['ok'], $create['error']);
        self::assertGreaterThan(0, $create['source_id']);

        $item = app_pdo_fetch_database_source_item($app, $create['source_id']);
        self::assertTrue($item['ok'], $item['error']);
        self::assertSame('external-test', $item['item']['source_key'] ?? '');

        $update = app_pdo_update_database_source($app, $create['source_id'], [
            'source_key' => 'external-test-updated',
            'label' => 'External Test Updated',
            'description' => 'updated sqlite repository smoke',
            'host' => 'db2.example.test',
            'port' => '3307',
            'database_name' => 'app_db2',
            'user_name' => 'app_user2',
            'password' => 'secret2',
            'supports_live_schema_import' => false,
            'supports_proxy_runtime_read' => true,
            'proxy_runtime_priority' => 50,
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($update['ok'], $update['error']);

        $catalog = app_pdo_fetch_database_source_catalog($app);
        self::assertTrue($catalog['ok'], $catalog['error']);
        self::assertCount(1, $catalog['items']);
        self::assertSame('external-test-updated', $catalog['items'][0]['source_key']);

        $delete = app_pdo_delete_database_source($app, $create['source_id']);
        self::assertTrue($delete['ok'], $delete['error']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-database-source-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
