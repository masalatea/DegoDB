<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';

use PHPUnit\Framework\TestCase;

final class ConfigDbBootstrapSqliteTest extends TestCase
{
    public function testSqliteConfigStoreCanBeBootstrappedFromCurrentInitdb(): void
    {
        $storeDir = sys_get_temp_dir() . '/dego-config-store-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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

        $result = app_config_db_bootstrap_apply($app);

        self::assertTrue($result['ok'], $result['error']);
        self::assertTrue($result['summary']['schema_current']);
        self::assertSame([], $result['missing_tables']);
        self::assertSame([], $result['missing_columns']);
        self::assertSame([], $result['unexpected_legacy_columns']);
        self::assertFileExists($configDb['name']);
    }
}
