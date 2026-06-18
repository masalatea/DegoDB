<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_html_source_binding_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class HtmlSourceBindingRepositorySqliteTest extends TestCase
{
    public function testHtmlSourceBindingUpsertFetchAndDeleteWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_HTML_BINDING_TEST',
            'name' => 'SQLite HTML Binding Test',
            'slug' => 'sqlite-html-binding-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'html binding sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $input = [
            'legacy_project_source_output_pid' => 101,
            'source_output_key' => 'HTML-MODULE',
            'module_source_ref' => 'projects/SQLITE_HTML_BINDING_TEST/html',
            'refresh_policy' => 'follow-source-output',
            'notes' => 'first',
            'source_of_truth' => 'manual',
        ];
        $insert = app_pdo_upsert_project_html_source_binding($app, 'SQLITE_HTML_BINDING_TEST', $input);
        self::assertTrue($insert['ok'], $insert['error']);

        $item = app_pdo_fetch_project_html_source_binding($app, 'SQLITE_HTML_BINDING_TEST', 101);
        self::assertTrue($item['ok'], $item['error']);
        self::assertSame('first', $item['item']['notes'] ?? '');

        $input['notes'] = 'updated';
        $input['module_source_ref'] = 'projects/SQLITE_HTML_BINDING_TEST/html-updated';
        $update = app_pdo_upsert_project_html_source_binding($app, 'SQLITE_HTML_BINDING_TEST', $input);
        self::assertTrue($update['ok'], $update['error']);

        $catalog = app_pdo_fetch_project_html_source_bindings($app, 'SQLITE_HTML_BINDING_TEST');
        self::assertTrue($catalog['ok'], $catalog['error']);
        self::assertSame('updated', $catalog['items'][0]['notes'] ?? '');
        self::assertSame('projects/SQLITE_HTML_BINDING_TEST/html-updated', $catalog['items'][0]['module_source_ref'] ?? '');

        $delete = app_pdo_delete_project_html_source_binding($app, 'SQLITE_HTML_BINDING_TEST', 101);
        self::assertTrue($delete['ok'], $delete['error']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-html-binding-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
}
