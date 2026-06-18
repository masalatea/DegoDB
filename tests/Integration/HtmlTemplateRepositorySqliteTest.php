<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/html_template_repository.php';

use PHPUnit\Framework\TestCase;

final class HtmlTemplateRepositorySqliteTest extends TestCase
{
    public function testHtmlTemplateAndParametersWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();

        $template = app_create_html_template($app, [
            'target_type' => 'HTML',
            'parent_html_template_pid' => 0,
            'name' => 'Page',
            'program_language' => 'html',
            'file_name' => 'page.html',
            'comment' => 'first',
        ]);
        self::assertTrue($template['ok'], $template['error']);
        self::assertSame('Page', $template['item']['name'] ?? '');
        $templatePid = (int) ($template['item']['legacy_html_template_pid'] ?? 0);
        self::assertGreaterThan(0, $templatePid);

        $templateUpdate = app_update_html_template($app, [
            'legacy_html_template_pid' => $templatePid,
            'target_type' => 'HTML',
            'parent_html_template_pid' => 0,
            'name' => 'Page Updated',
            'program_language' => 'html',
            'file_name' => 'page-updated.html',
            'comment' => 'updated',
        ]);
        self::assertTrue($templateUpdate['ok'], $templateUpdate['error']);
        self::assertSame('Page Updated', $templateUpdate['item']['name'] ?? '');

        $catalog = app_fetch_html_template_catalog($app);
        self::assertTrue($catalog['ok'], $catalog['error']);
        self::assertSame('Page Updated', app_html_template_find_catalog_item_by_pid($catalog['items'], $templatePid)['name'] ?? '');

        $parameter = app_create_html_template_parameter($app, [
            'legacy_html_template_pid' => $templatePid,
            'parameter_name' => 'title',
            'target_value_type' => 'fixed',
            'target_variable_or_class_object' => '',
            'target_property_of_class_object' => '',
            'another_template_pid' => 0,
            'trim_last_space' => 0,
            'trim_last_return' => 0,
            'data_type' => 'string',
        ]);
        self::assertTrue($parameter['ok'], $parameter['error']);
        self::assertSame('title', $parameter['item']['parameter_name'] ?? '');
        $parameterPid = (int) ($parameter['item']['legacy_template_parameter_pid'] ?? 0);
        self::assertGreaterThan(0, $parameterPid);

        $parameterUpdate = app_update_html_template_parameter($app, [
            'legacy_html_template_pid' => $templatePid,
            'legacy_template_parameter_pid' => $parameterPid,
            'parameter_name' => 'heading',
            'target_value_type' => 'fixed',
            'target_variable_or_class_object' => '',
            'target_property_of_class_object' => '',
            'another_template_pid' => 0,
            'trim_last_space' => 1,
            'trim_last_return' => 0,
            'data_type' => 'string',
        ]);
        self::assertTrue($parameterUpdate['ok'], $parameterUpdate['error']);
        self::assertSame('heading', $parameterUpdate['item']['parameter_name'] ?? '');

        $parameterCatalog = app_fetch_html_template_parameter_catalog($app, $templatePid);
        self::assertTrue($parameterCatalog['ok'], $parameterCatalog['error']);
        self::assertSame('heading', $parameterCatalog['items'][0]['parameter_name'] ?? '');

        self::assertTrue(app_delete_html_template_parameter($app, $templatePid, $parameterPid)['ok']);
        self::assertTrue(app_delete_html_template($app, $templatePid)['ok']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-html-template-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
