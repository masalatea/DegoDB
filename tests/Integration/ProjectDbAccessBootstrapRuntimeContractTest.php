<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_db_access_bootstrap_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_proxy_generator.php';

use PHPUnit\Framework\TestCase;

final class ProjectDbAccessBootstrapRuntimeContractTest extends TestCase
{
    public function testCanonicalBootstrapBodyLinesUseExecutablePhpConcatenation(): void
    {
        $table = [
            'name' => 'lab_experiments',
            'columns' => [
                [
                    'name' => 'id',
                    'datatype' => 'int',
                    'is_key' => 'PRI',
                    'extra' => 'auto_increment',
                    'column_list_order' => 1,
                ],
                [
                    'name' => 'name',
                    'datatype' => 'varchar',
                    'is_key' => '',
                    'extra' => '',
                    'column_list_order' => 2,
                ],
            ],
        ];

        $selectLines = app_project_db_access_bootstrap_select_body_lines(
            $table,
            $table['columns'],
            [$table['columns'][0]],
            false,
        );
        $insertLines = app_project_db_access_bootstrap_insert_body_lines(
            $table,
            [$table['columns'][1]],
        );

        $selectText = implode("\n", $selectLines);
        $insertText = implode("\n", $insertLines);

        self::assertStringContainsString(
            <<<'TEXT'
$last_sql_command_for_mtooldb = 'select `lab_experiments`.`id`, `lab_experiments`.`name` from `lab_experiments`' . ' where ' . '`lab_experiments`.`id` = ' . ($param_lab_experiments_id_where === null ? 'NULL' : '\'' . $mtooldb->real_escape_string((string) $param_lab_experiments_id_where) . '\'');
TEXT,
            $selectText,
        );
        self::assertStringContainsString(
            <<<'TEXT'
$last_sql_command_for_mtooldb = 'insert into `lab_experiments` (`name`) values(' . ($lab_experimentsObj->name === null ? 'NULL' : '\'' . $mtooldb->real_escape_string((string) ($lab_experimentsObj->name)) . '\'') . ')';
TEXT,
            $insertText,
        );
        self::assertStringNotContainsString('\\$param_lab_experiments_id_where', $selectText);
        self::assertStringNotContainsString('\\$lab_experimentsObj', $insertText);
    }

    public function testProxyRuntimeBundleTransformAllowsCanonicalBootstrapBaseClass(): void
    {
        $contents = <<<'PHP'
<?php
class lab_experimentsDBAccessBase
{
}
PHP;

        $rewritten = app_project_output_proxy_runtime_bundle_transform_file(
            'base/dbaccess-lab_experimentsBase.php',
            $contents,
        );

        self::assertSame($contents, $rewritten);
    }
}
