<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/table_metadata_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/data_class_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class SchemaMetadataRepositoriesSqliteTest extends TestCase
{
    public function testTableAndDataClassMetadataRepositoriesWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_SCHEMA_TEST',
            'name' => 'SQLite Schema Test',
            'slug' => 'sqlite-schema-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'schema metadata sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $table = app_pdo_create_table_metadata_item($app, 'SQLITE_SCHEMA_TEST', 'users');
        self::assertTrue($table['ok'], $table['error']);
        self::assertSame('users', $table['item']['name'] ?? '');

        $column = app_pdo_create_table_metadata_column($app, 'SQLITE_SCHEMA_TEST', (string) ($table['item']['pid'] ?? ''), [
            'name' => 'id',
            'datatype' => 'int',
            'is_null' => 'NO',
            'is_key' => 'PRI',
            'is_default' => '',
            'extra' => 'auto_increment',
            'memo' => 'primary key',
        ]);
        self::assertTrue($column['ok'], $column['error']);
        self::assertSame('id', $column['item']['name'] ?? '');

        $columnUpdate = app_pdo_update_table_metadata_column($app, 'SQLITE_SCHEMA_TEST', (string) ($column['item']['pid'] ?? ''), [
            'name' => 'user_id',
            'physical_name' => 'user_id',
            'datatype' => 'int',
            'is_null' => 'NO',
            'is_key' => 'PRI',
            'is_default' => '',
            'extra' => 'auto_increment',
            'memo' => 'updated primary key',
        ]);
        self::assertTrue($columnUpdate['ok'], $columnUpdate['error']);
        self::assertSame('user_id', $columnUpdate['item']['name'] ?? '');

        $tableSnapshot = app_pdo_fetch_table_metadata_snapshot($app, 'SQLITE_SCHEMA_TEST');
        self::assertTrue($tableSnapshot['ok'], $tableSnapshot['error']);
        self::assertSame(1, $tableSnapshot['items'][0]['column_count'] ?? 0);
        self::assertSame('users', $tableSnapshot['items'][0]['physical_name'] ?? '');
        self::assertSame('Users', $tableSnapshot['items'][0]['logical_name'] ?? '');
        self::assertSame('Users', $tableSnapshot['items'][0]['generated_name'] ?? '');
        self::assertSame('user_id', $tableSnapshot['items'][0]['columns'][0]['physical_name'] ?? '');
        self::assertSame('UserId', $tableSnapshot['items'][0]['columns'][0]['logical_name'] ?? '');
        self::assertSame('userId', $tableSnapshot['items'][0]['columns'][0]['generated_name'] ?? '');

        $dataClass = app_pdo_create_data_class_metadata_item($app, 'SQLITE_SCHEMA_TEST', [
            'name' => 'User',
            'physical_name' => 'user',
            'store_base_path' => 'src/Data',
            'is_autoload' => '1',
            'inherit_parent_data_class_name' => '',
        ]);
        self::assertTrue($dataClass['ok'], $dataClass['error']);
        self::assertSame('User', $dataClass['item']['name'] ?? '');

        $field = app_pdo_create_data_class_metadata_field($app, 'SQLITE_SCHEMA_TEST', (string) ($dataClass['item']['pid'] ?? ''), [
            'name' => 'id',
            'datatype' => 'int',
            'ref_data_class_name' => '',
            'ref_data_class_field_name' => '',
        ]);
        self::assertTrue($field['ok'], $field['error']);
        self::assertSame('id', $field['item']['name'] ?? '');

        $fieldUpdate = app_pdo_update_data_class_metadata_field($app, 'SQLITE_SCHEMA_TEST', (string) ($field['item']['pid'] ?? ''), [
            'name' => 'userId',
            'physical_name' => 'user_id',
            'datatype' => 'int',
            'ref_data_class_name' => '',
            'ref_data_class_field_name' => '',
        ]);
        self::assertTrue($fieldUpdate['ok'], $fieldUpdate['error']);
        self::assertSame('userId', $fieldUpdate['item']['name'] ?? '');

        $dataClassSnapshot = app_pdo_fetch_data_class_metadata_snapshot($app, 'SQLITE_SCHEMA_TEST');
        self::assertTrue($dataClassSnapshot['ok'], $dataClassSnapshot['error']);
        self::assertSame(1, $dataClassSnapshot['items'][0]['field_count'] ?? 0);
        self::assertSame('user', $dataClassSnapshot['items'][0]['physical_name'] ?? '');
        self::assertSame('User', $dataClassSnapshot['items'][0]['logical_name'] ?? '');
        self::assertSame('User', $dataClassSnapshot['items'][0]['generated_name'] ?? '');
        self::assertSame('user_id', $dataClassSnapshot['items'][0]['fields'][0]['physical_name'] ?? '');
        self::assertSame('UserId', $dataClassSnapshot['items'][0]['fields'][0]['logical_name'] ?? '');
        self::assertSame('userId', $dataClassSnapshot['items'][0]['fields'][0]['generated_name'] ?? '');

        self::assertTrue(app_pdo_delete_data_class_metadata_field($app, 'SQLITE_SCHEMA_TEST', (string) ($fieldUpdate['item']['pid'] ?? ''))['ok']);
        self::assertTrue(app_pdo_delete_data_class_metadata_item($app, 'SQLITE_SCHEMA_TEST', (string) ($dataClass['item']['pid'] ?? ''))['ok']);
        self::assertTrue(app_pdo_delete_table_metadata_column($app, 'SQLITE_SCHEMA_TEST', (string) ($columnUpdate['item']['pid'] ?? ''))['ok']);
        self::assertTrue(app_pdo_delete_table_metadata_item($app, 'SQLITE_SCHEMA_TEST', (string) ($table['item']['pid'] ?? ''))['ok']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-schema-metadata-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
}
