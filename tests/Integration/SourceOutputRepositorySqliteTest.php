<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/source_output_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class SourceOutputRepositorySqliteTest extends TestCase
{
    public function testSourceOutputRepositoryCrudWorksWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_SO_TEST',
            'name' => 'SQLite Source Output Test',
            'slug' => 'sqlite-source-output-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'source output sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $create = app_pdo_create_project_source_output($app, $this->sourceOutputInput([
            'project_key' => 'SQLITE_SO_TEST',
            'source_output_key' => 'DBACCESS-PHP',
            'name' => 'DB Access PHP',
        ]));
        self::assertTrue($create['ok'], $create['error']);

        $item = app_pdo_fetch_project_source_output_item($app, 'SQLITE_SO_TEST', 'DBACCESS-PHP');
        self::assertTrue($item['ok'], $item['error']);
        self::assertSame('DB Access PHP', $item['item']['name'] ?? '');

        $implicitAiContext = app_pdo_fetch_project_source_output_item($app, 'SQLITE_SO_TEST', 'AI-CONTEXT-MD');
        self::assertTrue($implicitAiContext['ok'], $implicitAiContext['error']);
        self::assertSame('implicit-default', $implicitAiContext['item']['source_of_truth'] ?? '');
        self::assertSame(
            'mtool/ai-context-source-outputs/SQLITE_SO_TEST/AI-CONTEXT-MD',
            $implicitAiContext['item']['runtime_source_relative_path'] ?? '',
        );

        $update = app_pdo_update_project_source_output($app, $this->sourceOutputInput([
            'project_key' => 'SQLITE_SO_TEST',
            'source_output_key' => 'DBACCESS-PHP',
            'name' => 'DB Access PHP Updated',
            'source_output_list_order' => '20',
        ]));
        self::assertTrue($update['ok'], $update['error']);

        $catalog = app_pdo_fetch_project_source_output_catalog($app, 'SQLITE_SO_TEST');
        self::assertTrue($catalog['ok'], $catalog['error']);
        self::assertCount(2, $catalog['items']);
        self::assertSame('DB Access PHP Updated', $catalog['items'][0]['name']);
        self::assertSame('AI-CONTEXT-MD', $catalog['items'][1]['source_output_key']);

        $delete = app_pdo_delete_project_source_output($app, [
            'project_key' => 'SQLITE_SO_TEST',
            'source_output_key' => 'DBACCESS-PHP',
        ]);
        self::assertTrue($delete['ok'], $delete['error']);

        $catalogAfterDelete = app_pdo_fetch_project_source_output_catalog($app, 'SQLITE_SO_TEST');
        self::assertTrue($catalogAfterDelete['ok'], $catalogAfterDelete['error']);
        self::assertCount(1, $catalogAfterDelete['items']);
        self::assertSame('AI-CONTEXT-MD', $catalogAfterDelete['items'][0]['source_output_key']);
    }

    public function testImplicitAiContextDefaultCanMaterializeOnUpdate(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_AI_CONTEXT_DEFAULT_TEST',
            'name' => 'SQLite AI Context Default Test',
            'slug' => 'sqlite-ai-context-default-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'implicit AI context source output smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $implicit = app_pdo_fetch_project_source_output_item(
            $app,
            'SQLITE_AI_CONTEXT_DEFAULT_TEST',
            'AI-CONTEXT-MD',
        );
        self::assertTrue($implicit['ok'], $implicit['error']);
        self::assertSame('implicit-default', $implicit['item']['source_of_truth'] ?? '');

        $input = $this->sourceOutputInput([
            'project_key' => 'SQLITE_AI_CONTEXT_DEFAULT_TEST',
            'source_output_key' => 'AI-CONTEXT-MD',
            'name' => 'Materialized AI Context',
            'program_language' => 'md',
            'class_type' => 'AIContext',
            'runtime_source_relative_path' => 'mtool/ai-context-source-outputs/SQLITE_AI_CONTEXT_DEFAULT_TEST/AI-CONTEXT-MD',
            'artifact_strategy' => 'ai-context-md',
            'target_binding_type' => 'runtime',
            'source_output_list_order' => '90',
            'source_of_truth' => 'manual',
        ]);
        $update = app_pdo_update_project_source_output($app, $input);
        self::assertTrue($update['ok'], $update['error']);

        $materialized = app_pdo_fetch_project_source_output_item(
            $app,
            'SQLITE_AI_CONTEXT_DEFAULT_TEST',
            'AI-CONTEXT-MD',
        );
        self::assertTrue($materialized['ok'], $materialized['error']);
        self::assertSame('Materialized AI Context', $materialized['item']['name'] ?? '');
        self::assertSame('manual', $materialized['item']['source_of_truth'] ?? '');
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-source-output-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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

    /**
     * @param array<string,string> $overrides
     * @return array<string,string>
     */
    private function sourceOutputInput(array $overrides): array
    {
        return array_merge([
            'project_key' => 'SQLITE_SO_TEST',
            'source_output_key' => 'DBACCESS-PHP',
            'name' => 'DB Access PHP',
            'program_language' => 'php',
            'class_type' => 'DBAccess',
            'release_target_type' => 'Release',
            'source_template_dir' => '',
            'source_output_dir' => '',
            'source_temp_output_dir' => '',
            'proxy_base_url' => '',
            'autoload_filename_suffix' => '',
            'source_text_char_code' => 'UTF-8',
            'runtime_source_relative_path' => 'mtool/dbclasses',
            'artifact_strategy' => 'generated-bootstrap-dbclasses',
            'target_binding_type' => '',
            'spec_visibility' => 'internal-only',
            'output_archive_format' => 'tar.gz',
            'source_output_list_order' => '10',
            'notes' => '',
            'source_of_truth' => 'manual',
        ], $overrides);
    }
}
