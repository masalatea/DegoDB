<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/source_output_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/db_access_repository_pdo.php';

use PHPUnit\Framework\TestCase;

final class DbAccessRepositorySqliteTest extends TestCase
{
    public function testDbAccessClassFunctionAndSourceOutputTargetsWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_DBACCESS_TEST',
            'name' => 'SQLite DB Access Test',
            'slug' => 'sqlite-dbaccess-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'db access sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $sourceOutput = app_pdo_create_project_source_output($app, $this->sourceOutputInput());
        self::assertTrue($sourceOutput['ok'], $sourceOutput['error']);

        $class = app_pdo_upsert_db_access_class_metadata($app, [
            'project_key' => 'SQLITE_DBACCESS_TEST',
            'source_name' => 'UserDbAccess',
            'store_base_path' => 'src/Db',
            'is_autoload' => '1',
            'notes' => 'sqlite class smoke',
            'source_of_truth' => 'manual',
            'last_detected_dbaccess_file' => 'UserDbAccess.php',
            'last_detected_data_file' => 'UserData.php',
        ]);
        self::assertTrue($class['ok'], $class['error']);

        $classItem = app_pdo_fetch_db_access_class_metadata($app, 'SQLITE_DBACCESS_TEST', 'UserDbAccess');
        self::assertTrue($classItem['ok'], $classItem['error']);
        self::assertSame('src/Db', $classItem['item']['store_base_path'] ?? '');

        $function = app_pdo_upsert_db_access_function_metadata($app, $this->functionInput([
            'project_key' => 'SQLITE_DBACCESS_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'selectUser',
            'last_detected_dbaccess_file' => 'UserDbAccess.php',
            'last_detected_data_file' => 'UserData.php',
        ]));
        self::assertTrue($function['ok'], $function['error']);

        $functionItem = app_pdo_fetch_db_access_function_metadata(
            $app,
            'SQLITE_DBACCESS_TEST',
            'UserDbAccess',
            'selectUser',
        );
        self::assertTrue($functionItem['ok'], $functionItem['error']);
        self::assertSame('SELECT', $functionItem['item']['action_type'] ?? '');

        $targetReplace = app_pdo_replace_db_access_function_source_output_target_keys(
            $app,
            'SQLITE_DBACCESS_TEST',
            'UserDbAccess',
            'selectUser',
            ['DBACCESS-PHP'],
        );
        self::assertTrue($targetReplace['ok'], $targetReplace['error']);

        $targetKeys = app_pdo_fetch_db_access_function_source_output_target_keys(
            $app,
            'SQLITE_DBACCESS_TEST',
            'UserDbAccess',
            'selectUser',
        );
        self::assertTrue($targetKeys['ok'], $targetKeys['error']);
        self::assertSame(['DBACCESS-PHP'], $targetKeys['items']);

        $targetCatalog = app_pdo_fetch_source_output_db_access_function_target_catalog(
            $app,
            'SQLITE_DBACCESS_TEST',
            'DBACCESS-PHP',
        );
        self::assertTrue($targetCatalog['ok'], $targetCatalog['error']);
        self::assertSame('selectUser', $targetCatalog['items'][0]['function_name'] ?? '');

        $delete = app_pdo_delete_db_access_function_metadata(
            $app,
            'SQLITE_DBACCESS_TEST',
            'UserDbAccess',
            'selectUser',
        );
        self::assertTrue($delete['ok'], $delete['error']);
    }

    public function testDbAccessSelectDetailsWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_SELECT_TEST',
            'name' => 'SQLite Select Test',
            'slug' => 'sqlite-select-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'select detail sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $function = app_pdo_upsert_db_access_function_metadata($app, $this->functionInput([
            'project_key' => 'SQLITE_SELECT_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'selectUser',
            'last_detected_dbaccess_file' => 'UserDbAccess.php',
            'last_detected_data_file' => 'UserData.php',
        ]));
        self::assertTrue($function['ok'], $function['error']);

        $whereInput = [
            'project_key' => 'SQLITE_SELECT_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'selectUser',
            'target_table_name' => 'users',
            'target_table_alias_name' => 'u',
            'target_table_column_name' => 'id',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'int',
            'fixed_parameter' => '',
            'another_table_name' => '',
            'another_table_alias_name' => '',
            'another_field_name' => '',
            'join_type' => '',
            'or_group' => '',
            'relational_operator' => '=',
            'where_order' => '10',
            'source_of_truth' => 'manual',
        ];
        $where = app_pdo_create_db_access_function_select_where($app, $whereInput);
        self::assertTrue($where['ok'], $where['error']);
        $whereInput['select_where_id'] = $where['item_id'];
        $whereInput['target_table_column_name'] = 'user_id';
        self::assertTrue(app_pdo_update_db_access_function_select_where($app, $whereInput)['ok']);
        $whereCatalog = app_pdo_fetch_db_access_function_select_where_catalog(
            $app,
            'SQLITE_SELECT_TEST',
            'UserDbAccess',
            'selectUser',
        );
        self::assertTrue($whereCatalog['ok'], $whereCatalog['error']);
        self::assertSame('user_id', $whereCatalog['items'][0]['target_table_column_name'] ?? '');

        $targetInput = [
            'project_key' => 'SQLITE_SELECT_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'selectUser',
            'target_table_name' => 'users',
            'target_table_alias_name' => 'u',
            'target_table_column_name' => 'name',
            'target_table_column_prefix' => '',
            'target_table_column_suffix' => '',
            'store_class_field_name' => 'name',
            'group_by_target' => '0',
            'field_list_order' => '10',
            'source_of_truth' => 'manual',
        ];
        $target = app_pdo_create_db_access_function_select_target_field($app, $targetInput);
        self::assertTrue($target['ok'], $target['error']);
        $targetInput['select_target_field_id'] = $target['item_id'];
        $targetInput['store_class_field_name'] = 'userName';
        self::assertTrue(app_pdo_update_db_access_function_select_target_field($app, $targetInput)['ok']);
        $targetCatalog = app_pdo_fetch_db_access_function_select_target_field_catalog(
            $app,
            'SQLITE_SELECT_TEST',
            'UserDbAccess',
            'selectUser',
        );
        self::assertTrue($targetCatalog['ok'], $targetCatalog['error']);
        self::assertSame('userName', $targetCatalog['items'][0]['store_class_field_name'] ?? '');

        $havingInput = [
            'project_key' => 'SQLITE_SELECT_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'selectUser',
            'left_target_prefix' => 'COUNT(',
            'left_target_field_id' => $target['item_id'],
            'left_target_suffix' => ')',
            'relational_operator' => '>',
            'right_target_prefix' => '',
            'right_parameter_type' => 'fixed',
            'right_parameter_data_type' => 'int',
            'right_fixed_parameter' => '0',
            'right_target_field_id' => '0',
            'right_target_suffix' => '',
            'having_order' => '10',
            'source_of_truth' => 'manual',
        ];
        $having = app_pdo_create_db_access_function_select_having($app, $havingInput);
        self::assertTrue($having['ok'], $having['error']);
        $havingInput['select_having_id'] = $having['item_id'];
        $havingInput['right_fixed_parameter'] = '1';
        self::assertTrue(app_pdo_update_db_access_function_select_having($app, $havingInput)['ok']);
        $havingCatalog = app_pdo_fetch_db_access_function_select_having_catalog(
            $app,
            'SQLITE_SELECT_TEST',
            'UserDbAccess',
            'selectUser',
        );
        self::assertTrue($havingCatalog['ok'], $havingCatalog['error']);
        self::assertSame('1', $havingCatalog['items'][0]['right_fixed_parameter'] ?? '');

        self::assertTrue(app_pdo_delete_db_access_function_select_having($app, 'SQLITE_SELECT_TEST', 'UserDbAccess', 'selectUser', $having['item_id'])['ok']);
        self::assertTrue(app_pdo_delete_db_access_function_select_target_field($app, 'SQLITE_SELECT_TEST', 'UserDbAccess', 'selectUser', $target['item_id'])['ok']);
        self::assertTrue(app_pdo_delete_db_access_function_select_where($app, 'SQLITE_SELECT_TEST', 'UserDbAccess', 'selectUser', $where['item_id'])['ok']);
    }

    public function testDbAccessMutationDetailsWorkWithSqliteConfigStore(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $project = app_pdo_insert_project($app, [
            'project_key' => 'SQLITE_MUTATION_TEST',
            'name' => 'SQLite Mutation Test',
            'slug' => 'sqlite-mutation-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'mutation detail sqlite smoke',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $function = app_pdo_upsert_db_access_function_metadata($app, $this->functionInput([
            'project_key' => 'SQLITE_MUTATION_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'updateUser',
            'action_type' => 'UPDATE',
            'last_detected_dbaccess_file' => 'UserDbAccess.php',
            'last_detected_data_file' => 'UserData.php',
        ]));
        self::assertTrue($function['ok'], $function['error']);

        $whereInput = [
            'project_key' => 'SQLITE_MUTATION_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'updateUser',
            'target_table_column_name' => 'id',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'int',
            'fixed_parameter' => '',
            'or_group' => '',
            'relational_operator' => '=',
            'where_order' => '10',
            'source_of_truth' => 'manual',
        ];
        $where = app_pdo_create_db_access_function_update_delete_where($app, $whereInput);
        self::assertTrue($where['ok'], $where['error']);
        $whereInput['update_delete_where_id'] = $where['item_id'];
        $whereInput['target_table_column_name'] = 'user_id';
        self::assertTrue(app_pdo_update_db_access_function_update_delete_where($app, $whereInput)['ok']);
        $whereCatalog = app_pdo_fetch_db_access_function_update_delete_where_catalog(
            $app,
            'SQLITE_MUTATION_TEST',
            'UserDbAccess',
            'updateUser',
        );
        self::assertTrue($whereCatalog['ok'], $whereCatalog['error']);
        self::assertSame('user_id', $whereCatalog['items'][0]['target_table_column_name'] ?? '');

        $updateTargetInput = [
            'project_key' => 'SQLITE_MUTATION_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'updateUser',
            'target_table_column_name' => 'name',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'varchar',
            'fixed_parameter' => '',
            'field_list_order' => '10',
            'source_of_truth' => 'manual',
        ];
        $updateTarget = app_pdo_create_db_access_function_update_target_field($app, $updateTargetInput);
        self::assertTrue($updateTarget['ok'], $updateTarget['error']);
        $updateTargetInput['update_target_field_id'] = $updateTarget['item_id'];
        $updateTargetInput['target_table_column_name'] = 'display_name';
        self::assertTrue(app_pdo_update_db_access_function_update_target_field($app, $updateTargetInput)['ok']);
        $updateTargetCatalog = app_pdo_fetch_db_access_function_update_target_field_catalog(
            $app,
            'SQLITE_MUTATION_TEST',
            'UserDbAccess',
            'updateUser',
        );
        self::assertTrue($updateTargetCatalog['ok'], $updateTargetCatalog['error']);
        self::assertSame('display_name', $updateTargetCatalog['items'][0]['target_table_column_name'] ?? '');

        $insertFunction = app_pdo_upsert_db_access_function_metadata($app, $this->functionInput([
            'project_key' => 'SQLITE_MUTATION_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'insertUser',
            'action_type' => 'INSERT',
            'last_detected_dbaccess_file' => 'UserDbAccess.php',
            'last_detected_data_file' => 'UserData.php',
        ]));
        self::assertTrue($insertFunction['ok'], $insertFunction['error']);

        $insertTargetInput = [
            'project_key' => 'SQLITE_MUTATION_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'insertUser',
            'target_table_column_name' => 'name',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'varchar',
            'fixed_parameter' => '',
            'field_list_order' => '10',
            'source_of_truth' => 'manual',
        ];
        $insertTarget = app_pdo_create_db_access_function_insert_target_field($app, $insertTargetInput);
        self::assertTrue($insertTarget['ok'], $insertTarget['error']);
        $insertTargetInput['insert_target_field_id'] = $insertTarget['item_id'];
        $insertTargetInput['target_table_column_name'] = 'display_name';
        self::assertTrue(app_pdo_update_db_access_function_insert_target_field($app, $insertTargetInput)['ok']);
        $insertTargetCatalog = app_pdo_fetch_db_access_function_insert_target_field_catalog(
            $app,
            'SQLITE_MUTATION_TEST',
            'UserDbAccess',
            'insertUser',
        );
        self::assertTrue($insertTargetCatalog['ok'], $insertTargetCatalog['error']);
        self::assertSame('display_name', $insertTargetCatalog['items'][0]['target_table_column_name'] ?? '');

        self::assertTrue(app_pdo_delete_db_access_function_insert_target_field($app, 'SQLITE_MUTATION_TEST', 'UserDbAccess', 'insertUser', $insertTarget['item_id'])['ok']);
        self::assertTrue(app_pdo_delete_db_access_function_update_target_field($app, 'SQLITE_MUTATION_TEST', 'UserDbAccess', 'updateUser', $updateTarget['item_id'])['ok']);
        self::assertTrue(app_pdo_delete_db_access_function_update_delete_where($app, 'SQLITE_MUTATION_TEST', 'UserDbAccess', 'updateUser', $where['item_id'])['ok']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-dbaccess-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
     * @return array<string,string>
     */
    private function sourceOutputInput(): array
    {
        return [
            'project_key' => 'SQLITE_DBACCESS_TEST',
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
        ];
    }

    /**
     * @param array<string,string> $overrides
     * @return array<string,string>
     */
    private function functionInput(array $overrides): array
    {
        return array_merge([
            'project_key' => 'SQLITE_DBACCESS_TEST',
            'source_name' => 'UserDbAccess',
            'function_name' => 'selectUser',
            'function_list_order' => '10',
            'function_suffix' => '',
            'action_type' => 'SELECT',
            'data_class_base_name' => 'User',
            'target_table_name' => 'users',
            'parameter_type' => 'argument',
            'select_by_distinct' => '0',
            'sort_order_columns' => '',
            'memo' => '',
            'limit_parameter_type' => '',
            'limit_fixed_parameter' => '',
            'or_group_type' => '',
            'single_proxy_auth_type' => '',
            'single_proxy_single_get_function_name' => '',
            'is_blob_target' => '0',
            'detected_signature' => '',
            'detected_line' => '10',
            'source_of_truth' => 'manual',
            'last_detected_dbaccess_file' => 'UserDbAccess.php',
            'last_detected_data_file' => 'UserData.php',
        ], $overrides);
    }
}
