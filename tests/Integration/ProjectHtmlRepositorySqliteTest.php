<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_html_repository.php';

use PHPUnit\Framework\TestCase;

final class ProjectHtmlRepositorySqliteTest extends TestCase
{
    public function testProjectHtmlAndParametersWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_PROJECT_HTML_TEST',
            'name' => 'SQLite Project HTML Test',
            'slug' => 'sqlite-project-html-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'project html sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $created = app_create_project_html($app, 'SQLITE_PROJECT_HTML_TEST', [
            'project_pid' => 0,
            'name' => 'Users',
            'legacy_project_source_output_pid' => 0,
            'legacy_html_template_pid' => 0,
        ]);
        self::assertTrue($created['ok'], $created['error']);
        self::assertSame('Users', $created['item']['name'] ?? '');
        $htmlPid = (int) ($created['item']['legacy_html_pid'] ?? 0);
        self::assertGreaterThan(0, $htmlPid);

        $updated = app_update_project_html($app, 'SQLITE_PROJECT_HTML_TEST', [
            'project_pid' => 0,
            'legacy_html_pid' => $htmlPid,
            'name' => 'Users Updated',
            'legacy_project_source_output_pid' => 0,
            'legacy_html_template_pid' => 0,
        ]);
        self::assertTrue($updated['ok'], $updated['error']);
        self::assertSame('Users Updated', $updated['item']['name'] ?? '');

        $catalog = app_fetch_project_html_catalog($app, 'SQLITE_PROJECT_HTML_TEST', 0);
        self::assertTrue($catalog['ok'], $catalog['error']);
        self::assertSame('Users Updated', $catalog['items'][0]['name'] ?? '');

        $parameter = app_create_project_html_parameter($app, 'SQLITE_PROJECT_HTML_TEST', [
            'project_pid' => 0,
            'legacy_html_pid' => $htmlPid,
            'parameter_name' => 'title',
            'parameter_value' => 'Users',
        ]);
        self::assertTrue($parameter['ok'], $parameter['error']);
        self::assertSame('Users', $parameter['item']['parameter_value'] ?? '');
        $parameterPid = (int) ($parameter['item']['legacy_parameter_pid'] ?? 0);
        self::assertGreaterThan(0, $parameterPid);

        $parameterUpdate = app_update_project_html_parameter($app, 'SQLITE_PROJECT_HTML_TEST', [
            'project_pid' => 0,
            'legacy_html_pid' => $htmlPid,
            'legacy_parameter_pid' => $parameterPid,
            'parameter_name' => 'title',
            'parameter_value' => 'Users Updated',
        ]);
        self::assertTrue($parameterUpdate['ok'], $parameterUpdate['error']);
        self::assertSame('Users Updated', $parameterUpdate['item']['parameter_value'] ?? '');

        $parameterCatalog = app_fetch_project_html_parameter_catalog(
            $app,
            'SQLITE_PROJECT_HTML_TEST',
            0,
            $htmlPid,
        );
        self::assertTrue($parameterCatalog['ok'], $parameterCatalog['error']);
        self::assertSame('Users Updated', $parameterCatalog['items'][0]['parameter_value'] ?? '');

        self::assertTrue(app_delete_project_html_parameter($app, 'SQLITE_PROJECT_HTML_TEST', 0, $htmlPid, $parameterPid)['ok']);
        self::assertTrue(app_delete_project_html($app, 'SQLITE_PROJECT_HTML_TEST', 0, $htmlPid)['ok']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-project-html-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
