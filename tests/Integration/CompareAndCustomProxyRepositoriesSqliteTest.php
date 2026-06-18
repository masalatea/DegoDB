<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/source_output_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/compare_output_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/custom_proxy_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class CompareAndCustomProxyRepositoriesSqliteTest extends TestCase
{
    public function testCompareOutputMetadataWorksWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->createProject($app, 'SQLITE_COMPARE_TEST');

        $compareInput = [
            'project_key' => 'SQLITE_COMPARE_TEST',
            'compare_output_key' => 'MAIN',
            'name' => 'Main Compare',
            'storage_base_path' => 'work/compare',
            'output_file_path' => 'actual',
            'output_file_type' => 'folder',
            'compare_path' => 'expected',
            'compare_tool_file_path' => '',
            'compare_output_list_order' => '10',
            'notes' => '',
            'source_of_truth' => 'manual',
        ];
        $compare = app_pdo_create_project_compare_output($app, $compareInput);
        self::assertTrue($compare['ok'], $compare['error']);

        $compareInput['name'] = 'Main Compare Updated';
        $compareUpdate = app_pdo_update_project_compare_output($app, $compareInput);
        self::assertTrue($compareUpdate['ok'], $compareUpdate['error']);

        $compareCatalog = app_pdo_fetch_project_compare_output_catalog($app, 'SQLITE_COMPARE_TEST');
        self::assertTrue($compareCatalog['ok'], $compareCatalog['error']);
        self::assertSame('Main Compare Updated', $compareCatalog['items'][0]['name'] ?? '');

        $pathInput = [
            'project_key' => 'SQLITE_COMPARE_TEST',
            'compare_output_key' => 'MAIN',
            'additional_path_key' => 'EXTRA',
            'path_a_base_path' => 'work/a',
            'path_a' => 'src',
            'path_b_base_path' => 'work/b',
            'path_b' => 'src',
            'is_same_filename_only' => '0',
            'additional_path_list_order' => '10',
            'notes' => '',
            'source_of_truth' => 'manual',
        ];
        $path = app_pdo_create_project_compare_output_additional_path($app, $pathInput);
        self::assertTrue($path['ok'], $path['error']);

        $pathInput['is_same_filename_only'] = '1';
        $pathUpdate = app_pdo_update_project_compare_output_additional_path($app, $pathInput);
        self::assertTrue($pathUpdate['ok'], $pathUpdate['error']);

        $pathCatalog = app_pdo_fetch_project_compare_output_additional_path_catalog(
            $app,
            'SQLITE_COMPARE_TEST',
            'MAIN',
        );
        self::assertTrue($pathCatalog['ok'], $pathCatalog['error']);
        self::assertSame('1', $pathCatalog['items'][0]['is_same_filename_only'] ?? '');

        self::assertTrue(app_pdo_delete_project_compare_output_additional_path($app, 'SQLITE_COMPARE_TEST', 'MAIN', 'EXTRA')['ok']);
        self::assertTrue(app_pdo_delete_project_compare_output($app, 'SQLITE_COMPARE_TEST', 'MAIN')['ok']);
    }

    public function testCustomProxyMetadataWorksWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->createProject($app, 'SQLITE_CUSTOM_PROXY_TEST');

        $sourceOutput = app_pdo_create_project_source_output($app, $this->sourceOutputInput('SQLITE_CUSTOM_PROXY_TEST'));
        self::assertTrue($sourceOutput['ok'], $sourceOutput['error']);

        $proxyInput = [
            'project_key' => 'SQLITE_CUSTOM_PROXY_TEST',
            'custom_proxy_key' => 'USER-CUSTOM',
            'basename' => 'UserProxy',
            'name' => 'User Custom',
            'in_transaction' => '1',
            'auth_type' => 'ProjectToken',
            'single_get_function_name' => '',
            'continue_even_if_failed_to_insert' => '0',
            'notes' => '',
            'source_of_truth' => 'manual',
        ];
        $proxy = app_pdo_create_project_custom_proxy($app, $proxyInput);
        self::assertTrue($proxy['ok'], $proxy['error']);

        $proxyInput['name'] = 'User Custom Updated';
        $proxyUpdate = app_pdo_update_project_custom_proxy($app, $proxyInput);
        self::assertTrue($proxyUpdate['ok'], $proxyUpdate['error']);

        $targetReplace = app_pdo_replace_project_custom_proxy_target_keys(
            $app,
            'SQLITE_CUSTOM_PROXY_TEST',
            'USER-CUSTOM',
            ['CUSTOM-PROXY-SERVER'],
        );
        self::assertTrue($targetReplace['ok'], $targetReplace['error']);
        $targetKeys = app_pdo_fetch_project_custom_proxy_target_keys($app, 'SQLITE_CUSTOM_PROXY_TEST', 'USER-CUSTOM');
        self::assertTrue($targetKeys['ok'], $targetKeys['error']);
        self::assertSame(['CUSTOM-PROXY-SERVER'], $targetKeys['items']);

        $stepInput = [
            'project_key' => 'SQLITE_CUSTOM_PROXY_TEST',
            'custom_proxy_key' => 'USER-CUSTOM',
            'db_access_source_name' => 'UserDbAccess',
            'db_access_function_name' => 'selectUser',
            'is_list' => '1',
            'step_order' => '10',
            'notes' => '',
            'source_of_truth' => 'manual',
        ];
        $step = app_pdo_create_project_custom_proxy_step($app, $stepInput);
        self::assertTrue($step['ok'], $step['error']);
        $stepCatalog = app_pdo_fetch_project_custom_proxy_step_catalog($app, 'SQLITE_CUSTOM_PROXY_TEST', 'USER-CUSTOM');
        self::assertTrue($stepCatalog['ok'], $stepCatalog['error']);
        self::assertSame('selectUser', $stepCatalog['items'][0]['db_access_function_name'] ?? '');

        $stepInput['step_id'] = $stepCatalog['items'][0]['id'];
        $stepInput['db_access_function_name'] = 'updateUser';
        $stepUpdate = app_pdo_update_project_custom_proxy_step($app, $stepInput);
        self::assertTrue($stepUpdate['ok'], $stepUpdate['error']);

        $reset = app_pdo_reset_project_custom_proxy_step_order($app, 'SQLITE_CUSTOM_PROXY_TEST', 'USER-CUSTOM');
        self::assertTrue($reset['ok'], $reset['error']);

        $proxyCatalog = app_pdo_fetch_project_custom_proxy_catalog($app, 'SQLITE_CUSTOM_PROXY_TEST');
        self::assertTrue($proxyCatalog['ok'], $proxyCatalog['error']);
        self::assertSame('User Custom Updated', $proxyCatalog['items'][0]['name'] ?? '');
        self::assertSame(1, $proxyCatalog['items'][0]['step_count'] ?? 0);
        self::assertSame(1, $proxyCatalog['items'][0]['target_count'] ?? 0);

        self::assertTrue(app_pdo_delete_project_custom_proxy_step($app, 'SQLITE_CUSTOM_PROXY_TEST', 'USER-CUSTOM', $stepInput['step_id'])['ok']);
        self::assertTrue(app_pdo_delete_project_custom_proxy($app, 'SQLITE_CUSTOM_PROXY_TEST', 'USER-CUSTOM')['ok']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-compare-custom-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
            'generated' => [
                'dbclasses_root' => '',
            ],
        ];
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        return $app;
    }

    /**
     * @param array<string,mixed> $app
     */
    private function createProject(array $app, string $projectKey): void
    {
        $project = app_pdo_insert_project($app, [
            'project_key' => $projectKey,
            'name' => $projectKey,
            'slug' => strtolower(str_replace('_', '-', $projectKey)),
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'sqlite repository smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);
    }

    /**
     * @return array<string,string>
     */
    private function sourceOutputInput(string $projectKey): array
    {
        return [
            'project_key' => $projectKey,
            'source_output_key' => 'CUSTOM-PROXY-SERVER',
            'name' => 'Custom Proxy Server',
            'program_language' => 'php',
            'class_type' => 'CustomProxy',
            'release_target_type' => 'Release',
            'source_template_dir' => '',
            'source_output_dir' => '',
            'source_temp_output_dir' => '',
            'proxy_base_url' => '',
            'autoload_filename_suffix' => '',
            'source_text_char_code' => 'UTF-8',
            'runtime_source_relative_path' => 'mtool/custom-proxy',
            'artifact_strategy' => 'generated-custom-proxy',
            'target_binding_type' => '',
            'spec_visibility' => 'internal-only',
            'output_archive_format' => 'tar.gz',
            'source_output_list_order' => '10',
            'notes' => '',
            'source_of_truth' => 'manual',
        ];
    }
}
