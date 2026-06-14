<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database_source_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_published_single_proxy_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_swagger_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_data_class_sync_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_db_access_sync_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_source.php';
require_once dirname(__DIR__, 2) . '/mtool/app/source_output_repository.php';

use PHPUnit\Framework\TestCase;

final class ExternalDatabaseSourceOutputFlowContractTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupSourceKeys = [];

    /** @var list<string> */
    private array $cleanupPaths = [];

    protected function tearDown(): void
    {
        $app = app_bootstrap();

        foreach ($this->cleanupSourceKeys as $sourceKey) {
            $this->deleteSourceByKey($app, $sourceKey);
        }
        $this->cleanupSourceKeys = [];

        foreach ($this->cleanupPaths as $path) {
            try {
                app_project_output_delete_tree($path);
            } catch (Throwable) {
            }
        }
        $this->cleanupPaths = [];

        parent::tearDown();
    }

    public function testExternalNamedDatabaseSourceSupportsImportSyncAndOutputPublishFlow(): void
    {
        $app = app_bootstrap();
        $this->ensureMtoolCoreFlowSeed($app);

        $sourceKey = 'ext_flow_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $this->cleanupSourceKeys[] = $sourceKey;

        $createSourceResult = app_create_database_source($app, [
            'source_key' => $sourceKey,
            'label' => 'external flow lab db',
            'description' => 'lab_db を external named source として import/output flow に通す contract test です。',
            'host' => $app['lab_db']['host'],
            'port' => $app['lab_db']['port'],
            'database_name' => $app['lab_db']['name'],
            'user_name' => $app['lab_db']['user'],
            'password' => $app['lab_db']['password'],
            'supports_live_schema_import' => true,
            'supports_proxy_runtime_read' => true,
            'proxy_runtime_priority' => 150,
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($createSourceResult['ok'], $createSourceResult['error']);

        $namedSourceOptionKey = app_project_table_import_named_live_source_option_key($sourceKey);
        $importApplyResult = app_project_table_import_apply($app, 'MTOOL', $namedSourceOptionKey, 'lab_experiments');
        self::assertTrue($importApplyResult['ok'], $importApplyResult['error']);
        self::assertSame('lab_experiments', $importApplyResult['tables'][0]['name'] ?? '');
        self::assertContains(
            $importApplyResult['tables'][0]['status'] ?? '',
            ['same', 'changed', 'new'],
        );

        $dataClassSyncResult = app_project_data_class_sync_apply($app, 'MTOOL');
        self::assertTrue($dataClassSyncResult['ok'], $dataClassSyncResult['error']);
        self::assertGreaterThan(0, $dataClassSyncResult['summary']['class_insert_count']);

        $dbAccessSyncResult = app_project_db_access_sync_from_generated_catalog($app, 'MTOOL');
        self::assertTrue($dbAccessSyncResult['ok'], $dbAccessSyncResult['error']);
        self::assertGreaterThan(0, $dbAccessSyncResult['summary']['dbaccess_candidate_count']);
        self::assertGreaterThan(0, $dbAccessSyncResult['summary']['method_candidate_count']);

        $nonce = strtolower(substr(bin2hex(random_bytes(4)), 0, 8));
        $openApiOutput = $this->fetchSourceOutputDefinition($app, 'MTOOL', 'OPENAPI-JSON');
        $proxyOutput = $this->fetchSourceOutputDefinition($app, 'MTOOL', 'DBTABLE-PROXY-SERVER');

        $openApiDefinition = $this->withTemporaryOutputPaths($openApiOutput, $nonce . '-openapi');
        $proxyDefinition = $this->withTemporaryOutputPaths($proxyOutput, $nonce . '-proxy');

        $openApiArtifactResult = app_project_output_create_from_definition(
            $app,
            'MTOOL',
            $openApiDefinition,
            'phpunit-external-source-flow',
        );
        self::assertTrue($openApiArtifactResult['ok'], $openApiArtifactResult['error']);
        self::assertIsArray($openApiArtifactResult['artifact']);
        $this->cleanupPaths[] = $openApiArtifactResult['artifact']['artifact_dir'];

        $proxyArtifactResult = app_project_output_create_from_definition(
            $app,
            'MTOOL',
            $proxyDefinition,
            'phpunit-external-source-flow',
        );
        self::assertTrue($proxyArtifactResult['ok'], $proxyArtifactResult['error']);
        self::assertIsArray($proxyArtifactResult['artifact']);
        $this->cleanupPaths[] = $proxyArtifactResult['artifact']['artifact_dir'];

        $openApiPublishResult = app_project_output_publish_artifact(
            $app,
            $openApiArtifactResult['artifact'],
            $openApiDefinition,
        );
        self::assertTrue($openApiPublishResult['ok'], $openApiPublishResult['error']);
        self::assertIsArray($openApiPublishResult['published']);
        $this->cleanupPaths[] = $openApiPublishResult['published']['published_root'];

        $proxyPublishResult = app_project_output_publish_artifact(
            $app,
            $proxyArtifactResult['artifact'],
            $proxyDefinition,
        );
        self::assertTrue($proxyPublishResult['ok'], $proxyPublishResult['error']);
        self::assertIsArray($proxyPublishResult['published']);
        $this->cleanupPaths[] = $proxyPublishResult['published']['published_root'];

        $specResult = app_lab_swagger_resolve_spec($app, 'MTOOL', $openApiDefinition);
        self::assertTrue($specResult['ok'], $specResult['error']);
        self::assertSame('published-output', $specResult['spec_source']);
        self::assertArrayHasKey('/proxyserver-lab_experiments-Getlab_experimentsList.php', $specResult['spec']['paths'] ?? []);

        $proxyRuntimeRoot = $proxyPublishResult['published']['published_root'] ?? '';
        self::assertIsString($proxyRuntimeRoot);
        self::assertFileExists($proxyRuntimeRoot . '/proxyserver-lab_experiments-Getlab_experimentsList.php');
        self::assertFileExists($proxyRuntimeRoot . '/build-plan.json');
        self::assertSame(
            'lab_experiments',
            app_lab_published_single_proxy_resolve_source_name_from_build_plan(
                $proxyRuntimeRoot,
                'proxyserver-lab_experiments-Getlab_experimentsList.php',
            ),
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function fetchSourceOutputDefinition(array $app, string $projectKey, string $sourceOutputKey): array
    {
        $result = app_fetch_project_source_output_item($app, $projectKey, $sourceOutputKey);
        self::assertTrue($result['ok'], $result['error']);
        self::assertIsArray($result['item']);

        return $result['item'];
    }

    /**
     * @param array<string,mixed> $definition
     * @return array<string,mixed>
     */
    private function withTemporaryOutputPaths(array $definition, string $suffix): array
    {
        $relativeRoot = 'work/test-output/external-source-flow/' . $suffix;
        $definition['source_output_dir'] = $relativeRoot . '/published';
        $definition['source_temp_output_dir'] = $relativeRoot . '/temp';

        return $definition;
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

    private function ensureMtoolCoreFlowSeed(array $app): void
    {
        $pdo = app_database_source_repository_create_config_pdo($app);
        $seedPaths = [
            dirname(__DIR__, 2) . '/mtool/docker/mariadb/config-seed/002_seed.sql',
            dirname(__DIR__, 2) . '/mtool/docker/mariadb/config-seed/034_single_proxy_swagger_source_output_seed.sql',
        ];

        foreach ($seedPaths as $seedPath) {
            $sql = file_get_contents($seedPath);
            self::assertIsString($sql, 'failed to read seed: ' . $seedPath);
            $pdo->exec($sql);
        }
    }
}
