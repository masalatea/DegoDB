<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_source.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_service.php';

use PHPUnit\Framework\TestCase;

final class LabDbIngressContractTest extends TestCase
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

    public function testLabLiveSchemaSourceIsAvailableAsNamedLiveSource(): void
    {
        $sourceDefinition = app_project_table_import_live_source_definition('lab-live-schema');

        self::assertNotNull($sourceDefinition);
        self::assertSame('lab live schema', $sourceDefinition['label']);
        self::assertSame('lab_db', $sourceDefinition['database_source_key']);
        self::assertTrue($sourceDefinition['apply_supported']);

        $sourceKeys = array_map(
            static fn (array $option): string => (string) ($option['key'] ?? ''),
            app_project_table_import_source_options('MTOOL'),
        );

        self::assertContains('live-schema', $sourceKeys);
        self::assertContains('lab-live-schema', $sourceKeys);
    }

    public function testAppLoadConfigKeepsDedicatedLabDbConnection(): void
    {
        $this->setEnv('APP_SITE', 'admin');
        $this->setEnv('APP_DB_HOST', 'config-db-host');
        $this->setEnv('APP_DB_PORT', '3306');
        $this->setEnv('APP_DB_NAME', 'config_app');
        $this->setEnv('APP_DB_USER', 'config_user');
        $this->setEnv('APP_DB_PASSWORD', 'config_password');
        $this->setEnv('APP_CONFIG_DB_HOST', 'config-db-host');
        $this->setEnv('APP_CONFIG_DB_PORT', '3306');
        $this->setEnv('APP_CONFIG_DB_NAME', 'config_app');
        $this->setEnv('APP_CONFIG_DB_USER', 'config_user');
        $this->setEnv('APP_CONFIG_DB_PASSWORD', 'config_password');
        $this->setEnv('APP_LAB_DB_HOST', 'lab-db-host');
        $this->setEnv('APP_LAB_DB_PORT', '43062');
        $this->setEnv('APP_LAB_DB_NAME', 'lab_schema');
        $this->setEnv('APP_LAB_DB_USER', 'lab_user');
        $this->setEnv('APP_LAB_DB_PASSWORD', 'lab_password');

        $config = app_load_config();

        self::assertSame('config-db-host', $config['db']['host']);
        self::assertSame('lab-db-host', $config['lab_db']['host']);
        self::assertSame('43062', $config['lab_db']['port']);
        self::assertSame('lab_schema', $config['lab_db']['name']);
        self::assertSame('lab_user', $config['lab_db']['user']);
        self::assertSame('lab_password', $config['lab_db']['password']);
        self::assertStringContainsString('mysql:host=lab-db-host;port=43062;dbname=lab_schema', $config['lab_db']['dsn']);
        self::assertSame(['db', 'config_db', 'lab_db'], array_keys($config['database_sources']));
        self::assertSame('site default db', $config['database_sources']['db']['label']);
        self::assertSame('config db', $config['database_sources']['config_db']['label']);
        self::assertSame('lab db', $config['database_sources']['lab_db']['label']);
        self::assertTrue($config['database_sources']['db']['supports_live_schema_import']);
        self::assertFalse($config['database_sources']['db']['supports_proxy_runtime_read']);
        self::assertTrue($config['database_sources']['config_db']['is_canonical_store']);
        self::assertTrue($config['database_sources']['lab_db']['supports_proxy_runtime_read']);
        self::assertSame('lab-db-host', $config['database_sources']['lab_db']['host']);
        self::assertSame('lab_schema', $config['database_sources']['lab_db']['name']);
    }

    public function testDevStackKeepsLabDbUiAndIngressContract(): void
    {
        $compose = $this->readRepoFile('compose.yaml');
        $makefile = $this->readRepoFile('Makefile');
        $envExample = $this->readRepoFile('.env.example');
        $accessScript = $this->readRepoFile('mtool/scripts/show_compose_access_urls.sh');
        $readme = $this->readRepoFile('README.md');
        $commonTasks = $this->readRepoFile('docs/common-tasks.md');

        self::assertStringContainsString('lab-db-ui:', $compose);
        self::assertStringContainsString('profiles: ["lab-db-ui"]', $compose);
        self::assertStringContainsString('ADMINER_DEFAULT_SERVER: db-lab', $compose);
        self::assertStringContainsString('APP_LAB_DB_HOST: db-lab', $compose);

        self::assertStringContainsString('LAB_DB_UI_HTTP_PORT=', $envExample);
        self::assertStringContainsString('lab-db-ui', $accessScript);
        self::assertStringContainsString('DB UI:', $accessScript);
        self::assertStringContainsString('COMPOSE_PROFILES=$(LAB_DB_UI_PROFILE) $(COMPOSE_LOCAL) up -d lab-db-ui', $makefile);

        self::assertStringContainsString('lab-db-ui', $readme);
        self::assertStringContainsString('lab-live-schema', $readme);
        self::assertStringContainsString('lab-live-schema', $commonTasks);
    }

    public function testLabLiveSchemaPreviewUsesActualLabTablesForManagedScope(): void
    {
        $app = app_bootstrap();

        $sourceResult = app_project_table_import_source_resolve($app, 'MTOOL', 'lab-live-schema');

        self::assertTrue($sourceResult['ok'], $sourceResult['error']);
        self::assertSame('lab_app', $sourceResult['source_schema_name']);
        self::assertSame(['lab_experiments'], $sourceResult['managed_target_table_names']);
        self::assertSame(['lab_experiments'], array_map(
            static fn (array $table): string => (string) ($table['name'] ?? ''),
            $sourceResult['tables'],
        ));
        self::assertSame(
            ['lab_experiments'],
            array_map(
                static fn (array $table): string => (string) ($table['name'] ?? ''),
                app_project_table_import_managed_source_tables($sourceResult),
            ),
        );
        self::assertSame([], app_project_table_import_preview_canonical_tables([], $sourceResult));
    }

    private function setEnv(string $key, string $value): void
    {
        if (!array_key_exists($key, $this->savedEnv)) {
            $current = getenv($key);
            $this->savedEnv[$key] = $current === false ? null : $current;
        }

        putenv($key . '=' . $value);
    }

    private function readRepoFile(string $relativePath): string
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . $relativePath;
        self::assertFileExists($absolutePath, 'missing file: ' . $relativePath);

        $content = file_get_contents($absolutePath);
        self::assertIsString($content, 'failed to read: ' . $relativePath);

        return $content;
    }
}
