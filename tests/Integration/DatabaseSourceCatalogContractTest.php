<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database_source_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_published_single_proxy_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_source.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class DatabaseSourceCatalogContractTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupSourceKeys = [];

    protected function tearDown(): void
    {
        $app = app_bootstrap();

        foreach (array_reverse($this->cleanupSourceKeys) as $sourceKey) {
            $this->deleteSourceByKey($app, $sourceKey);
        }

        $this->cleanupSourceKeys = [];

        parent::tearDown();
    }

    public function testSettingsRouteAndDashboardExposeDatabaseSourceSettings(): void
    {
        $route = app_route_match([
            'path' => '/settings/database-sources',
        ]);

        self::assertSame('database_sources', $route['name']);
        self::assertSame([], $route['params']);

        $dashboard = $this->readRepoFile('mtool/app/dashboard_page.php');
        $http = $this->readRepoFile('mtool/app/http.php');
        $router = $this->readRepoFile('mtool/app/router.php');
        self::assertStringContainsString('/settings/database-sources', $dashboard);
        self::assertStringContainsString("require_once __DIR__ . '/database_sources_page.php';", $http);
        self::assertStringContainsString("case 'database_sources':", $http);
        self::assertStringContainsString("#^/settings/database-sources/?$#", $router);
    }

    public function testPersistedDatabaseSourceCrudMergesIntoCatalogAndImportFlow(): void
    {
        $app = app_bootstrap();
        $pdo = app_database_source_repository_create_config_pdo($app);
        self::assertTrue(
            app_database_source_pdo_table_exists($pdo, 'database_sources'),
            'database_sources table is missing. Recreate config DB with current initdb files.',
        );

        $sourceKey = 'ext_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $this->cleanupSourceKeys[] = $sourceKey;

        $payload = [
            'source_key' => $sourceKey,
            'label' => 'external lab db',
            'description' => 'lab_db と同じ接続先を external source として登録する contract test です。',
            'host' => $app['lab_db']['host'],
            'port' => $app['lab_db']['port'],
            'database_name' => $app['lab_db']['name'],
            'user_name' => $app['lab_db']['user'],
            'password' => $app['lab_db']['password'],
            'supports_live_schema_import' => true,
            'supports_proxy_runtime_read' => false,
            'proxy_runtime_priority' => 150,
            'source_of_truth' => 'manual',
        ];

        $createResult = app_create_database_source($app, $payload);
        self::assertTrue($createResult['ok'], $createResult['error']);
        self::assertGreaterThan(0, $createResult['source_id']);

        $itemResult = app_fetch_database_source($app, $createResult['source_id']);
        self::assertTrue($itemResult['ok'], $itemResult['error']);
        self::assertNotNull($itemResult['item']);
        self::assertSame($sourceKey, $itemResult['item']['source_key']);
        self::assertSame($app['lab_db']['name'], $itemResult['item']['name']);
        self::assertFalse($itemResult['item']['supports_proxy_runtime_read']);

        $catalog = app_database_source_catalog($app);
        self::assertArrayHasKey($sourceKey, $catalog);
        self::assertSame('manual', $catalog[$sourceKey]['source_of_truth']);
        self::assertSame($app['lab_db']['host'], $catalog[$sourceKey]['host']);
        self::assertSame($app['lab_db']['name'], $catalog[$sourceKey]['name']);
        self::assertSame(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $app['lab_db']['host'],
                $app['lab_db']['port'],
                $app['lab_db']['name'],
            ),
            $catalog[$sourceKey]['dsn'],
        );

        $sourceOptionKey = app_project_table_import_named_live_source_option_key($sourceKey);
        $sourceOptions = app_project_table_import_source_options('MTOOL', $app);
        self::assertContains(
            $sourceOptionKey,
            array_map(
                static fn (array $option): string => (string) ($option['key'] ?? ''),
                $sourceOptions,
            ),
        );

        $sourceDefinition = app_project_table_import_live_source_definition($sourceOptionKey, $app);
        self::assertNotNull($sourceDefinition);
        self::assertSame($sourceKey, $sourceDefinition['database_source_key']);

        $preflight = app_project_table_import_preflight($app, 'MTOOL', $sourceOptionKey);
        self::assertTrue($preflight['ok'], $preflight['error']);

        $resolvedSource = app_project_table_import_source_resolve($app, 'MTOOL', $sourceOptionKey);
        self::assertTrue($resolvedSource['ok'], $resolvedSource['error']);
        self::assertSame($app['lab_db']['name'], $resolvedSource['source_schema_name']);
        self::assertContains(
            'lab_experiments',
            array_map(
                static fn (array $table): string => (string) ($table['name'] ?? ''),
                $resolvedSource['tables'],
            ),
        );

        $updateResult = app_update_database_source($app, $createResult['source_id'], array_merge($payload, [
            'label' => 'external lab runtime',
            'description' => 'proxy runtime read を有効化した contract test です。',
            'supports_proxy_runtime_read' => true,
            'proxy_runtime_priority' => 150,
        ]));
        self::assertTrue($updateResult['ok'], $updateResult['error']);

        $updatedItemResult = app_fetch_database_source($app, $createResult['source_id']);
        self::assertTrue($updatedItemResult['ok'], $updatedItemResult['error']);
        self::assertNotNull($updatedItemResult['item']);
        self::assertSame('external lab runtime', $updatedItemResult['item']['label']);
        self::assertTrue($updatedItemResult['item']['supports_proxy_runtime_read']);

        $runtimeCandidates = app_lab_published_single_proxy_runtime_database_source_key_candidates($app);
        $labPosition = array_search('lab_db', $runtimeCandidates, true);
        $externalPosition = array_search($sourceKey, $runtimeCandidates, true);
        $configPosition = array_search('config_db', $runtimeCandidates, true);
        self::assertIsInt($labPosition);
        self::assertIsInt($externalPosition);
        self::assertIsInt($configPosition);
        self::assertGreaterThan($labPosition, $externalPosition);
        self::assertLessThan($configPosition, $externalPosition);

        $deleteResult = app_delete_database_source($app, $createResult['source_id']);
        self::assertTrue($deleteResult['ok'], $deleteResult['error']);

        $catalogAfterDelete = app_database_source_catalog($app);
        self::assertArrayNotHasKey($sourceKey, $catalogAfterDelete);
        self::assertNotContains(
            $sourceOptionKey,
            array_map(
                static fn (array $option): string => (string) ($option['key'] ?? ''),
                app_project_table_import_source_options('MTOOL', $app),
            ),
        );
    }

    private function readRepoFile(string $relativePath): string
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . $relativePath;
        self::assertFileExists($absolutePath, 'missing file: ' . $relativePath);

        $content = file_get_contents($absolutePath);
        self::assertIsString($content, 'failed to read: ' . $relativePath);

        return $content;
    }

    private function deleteSourceByKey(array $app, string $sourceKey): void
    {
        try {
            $pdo = app_database_source_repository_create_config_pdo($app);
            if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
                return;
            }

            $statement = $pdo->prepare('DELETE FROM database_sources WHERE source_key = :source_key');
            $statement->execute([
                ':source_key' => $sourceKey,
            ]);
        } catch (Throwable) {
        }
    }
}
