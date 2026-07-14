<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';

use PHPUnit\Framework\TestCase;

final class ConfigStoreProfileTest extends TestCase
{
    /** @var array<string,string|null> */
    private array $savedEnv = [];

    protected function tearDown(): void
    {
        foreach ($this->savedEnv as $key => $value) {
            if ($value === null) {
                putenv($key);
                continue;
            }

            putenv($key . '=' . $value);
        }

        $this->savedEnv = [];

        parent::tearDown();
    }

    public function testEmptyDriverDefaultsToMysqlWithoutStoreDir(): void
    {
        self::assertSame('mysql', app_config_store_driver(''));

        $config = app_config_store_config(
            '',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
        );

        self::assertSame('mysql', $config['driver']);
        self::assertSame('mysql:host=db-config;port=3306;dbname=config_app;charset=utf8mb4', $config['dsn']);
    }

    public function testStoreDirCreatesSqliteConfigWithoutDriverDetails(): void
    {
        $config = app_config_store_config(
            '',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            'work/config-store',
        );

        self::assertSame('sqlite', $config['driver']);
        self::assertSame('', $config['host']);
        self::assertSame('', $config['user']);
        self::assertSame('/var/www/work/config-store/config.sqlite', $config['name']);
        self::assertSame('sqlite:' . $config['name'], $config['dsn']);
    }

    public function testStoreDirWithoutWorkPrefixResolvesUnderWorkRoot(): void
    {
        $config = app_config_store_config(
            '',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            'config-store',
        );

        self::assertSame('sqlite', $config['driver']);
        self::assertSame('/var/www/work/config-store/config.sqlite', $config['name']);
        self::assertSame('sqlite:' . $config['name'], $config['dsn']);
    }

    public function testHostWorkRelativeStoreDirResolvesToMountedWorkRootInsideScenario(): void
    {
        $config = app_config_store_config(
            '',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work/scenarios/01-mtool',
            'work/config-store',
        );

        self::assertSame('sqlite', $config['driver']);
        self::assertSame('/var/www/work/config-store/config.sqlite', $config['name']);
        self::assertSame('sqlite:' . $config['name'], $config['dsn']);
    }

    public function testExplicitSqliteStoreDirCanUseAbsoluteFolder(): void
    {
        $config = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            '/tmp/dego-config',
        );

        self::assertSame('/tmp/dego-config/config.sqlite', $config['name']);
        self::assertSame('sqlite:/tmp/dego-config/config.sqlite', $config['dsn']);
    }

    public function testExplicitFirebirdConfigStoreBuildsFirebirdDsn(): void
    {
        $config = app_config_store_config(
            'firebird',
            'user-db-firebird',
            '3050',
            '/var/lib/firebird/data/config_app.fdb',
            'config_app',
            'secret',
            '/var/www/work',
        );

        self::assertSame('firebird', $config['driver']);
        self::assertSame('user-db-firebird', $config['host']);
        self::assertSame('3050', $config['port']);
        self::assertSame('/var/lib/firebird/data/config_app.fdb', $config['name']);
        self::assertSame('config_app', $config['user']);
        self::assertSame(
            'firebird:dbname=user-db-firebird/3050:/var/lib/firebird/data/config_app.fdb;charset=UTF8',
            $config['dsn'],
        );
    }

    public function testExplicitFirebirdConfigStoreCanUseDsnOverride(): void
    {
        $this->setEnv(
            'APP_CONFIG_FIREBIRD_DSN',
            'firebird:dbname=custom-host/3051:/data/custom.fdb;charset=ISO8859_1',
        );

        $config = app_config_store_config(
            'fb',
            'ignored-host',
            '',
            'ignored.fdb',
            'config_app',
            'secret',
            '/var/www/work',
        );

        self::assertSame('firebird', $config['driver']);
        self::assertSame('3050', $config['port']);
        self::assertSame('firebird:dbname=custom-host/3051:/data/custom.fdb;charset=ISO8859_1', $config['dsn']);
    }

    public function testAdminDefaultDbFollowsSqliteConfigStoreWhenAppDbIsNotExplicit(): void
    {
        $this->setEnv('APP_SITE', 'admin');
        $this->setEnv('APP_WORK_ROOT', '/var/www/work');
        $this->setEnv('APP_CONFIG_STORE_DIR', 'work/config-store');
        foreach (['APP_DB_HOST', 'APP_DB_PORT', 'APP_DB_NAME', 'APP_DB_USER', 'APP_DB_PASSWORD'] as $key) {
            $this->clearEnv($key);
        }

        $app = app_load_config();

        self::assertSame('sqlite', $app['config_db']['driver']);
        self::assertSame($app['config_db'], $app['db']);
    }

    public function testAdminDefaultDbFollowsFirebirdConfigStoreWhenAppDbIsNotExplicit(): void
    {
        $this->setEnv('APP_SITE', 'admin');
        $this->setEnv('APP_CONFIG_STORE_DRIVER', 'firebird');
        $this->setEnv('APP_CONFIG_DB_HOST', 'user-db-firebird');
        $this->setEnv('APP_CONFIG_DB_NAME', '/var/lib/firebird/data/config_app.fdb');
        $this->setEnv('APP_CONFIG_DB_USER', 'config_app');
        $this->setEnv('APP_CONFIG_DB_PASSWORD', 'secret');
        $this->clearEnv('APP_CONFIG_DB_PORT');
        foreach (['APP_DB_HOST', 'APP_DB_PORT', 'APP_DB_NAME', 'APP_DB_USER', 'APP_DB_PASSWORD'] as $key) {
            $this->clearEnv($key);
        }

        $app = app_load_config();

        self::assertSame('firebird', $app['config_db']['driver']);
        self::assertSame('3050', $app['config_db']['port']);
        self::assertSame($app['config_db'], $app['db']);
    }

    public function testExplicitAdminAppDbDoesNotFollowSqliteConfigStore(): void
    {
        $this->setEnv('APP_SITE', 'admin');
        $this->setEnv('APP_WORK_ROOT', '/var/www/work');
        $this->setEnv('APP_CONFIG_STORE_DIR', 'work/config-store');
        $this->setEnv('APP_DB_HOST', 'external-db');
        $this->setEnv('APP_DB_PORT', '3306');
        $this->setEnv('APP_DB_NAME', 'config_app');
        $this->setEnv('APP_DB_USER', 'config_app');
        $this->setEnv('APP_DB_PASSWORD', 'secret');

        $app = app_load_config();

        self::assertSame('sqlite', $app['config_db']['driver']);
        self::assertSame('mysql', $app['db']['driver'] ?? 'mysql');
        self::assertSame('external-db', $app['db']['host']);
        self::assertNotSame($app['config_db']['dsn'], $app['db']['dsn']);
    }

    public function testStubAuthScopesCanBeConfiguredForLocalManagedOperationTryouts(): void
    {
        $this->setEnv('APP_SITE', 'admin');
        $this->setEnv('APP_AUTH_STUB_ROLES', 'admin,config,editor');
        $this->setEnv('APP_AUTH_STUB_SCOPES', 'support_case:write, task:write');

        $app = app_load_config();

        self::assertSame(['admin', 'config', 'editor'], $app['auth']['stub']['roles']);
        self::assertSame(['support_case:write', 'task:write'], $app['auth']['stub']['scopes']);
    }

    private function setEnv(string $key, string $value): void
    {
        if (!array_key_exists($key, $this->savedEnv)) {
            $current = getenv($key);
            $this->savedEnv[$key] = $current === false ? null : $current;
        }

        putenv($key . '=' . $value);
    }

    private function clearEnv(string $key): void
    {
        if (!array_key_exists($key, $this->savedEnv)) {
            $current = getenv($key);
            $this->savedEnv[$key] = $current === false ? null : $current;
        }

        putenv($key);
    }
}
