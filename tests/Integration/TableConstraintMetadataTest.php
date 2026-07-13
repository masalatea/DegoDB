<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/table_metadata_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/table_constraint_metadata_repository.php';

final class TableConstraintMetadataTest extends TestCase
{
    public function testCompositeKeysAndForeignKeysRoundTripInOrder(): void
    {
        [$app, $projectKey, $tables, $columns] = $this->fixture();
        $result = app_replace_project_table_constraints($app, $projectKey, [
            'keys' => [[
                'table_pid' => $tables['identity'],
                'key_name' => 'uq_identity_issuer_subject',
                'key_kind' => 'unique',
                'source_of_truth' => 'test',
                'columns' => [
                    ['column_pid' => $columns['identity.issuer']],
                    ['column_pid' => $columns['identity.subject']],
                ],
            ]],
            'foreign_keys' => [[
                'table_pid' => $tables['identity'],
                'constraint_name' => 'fk_identity_user',
                'referenced_table_pid' => $tables['user'],
                'on_update_action' => 'cascade',
                'on_delete_action' => 'restrict',
                'columns' => [[
                    'column_pid' => $columns['identity.app_user_id'],
                    'referenced_column_pid' => $columns['user.app_user_id'],
                ]],
            ]],
        ]);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame([$columns['identity.issuer'], $columns['identity.subject']], array_column($result['snapshot']['keys'][0]['columns'], 'column_pid'));
        self::assertSame([1, 2], array_column($result['snapshot']['keys'][0]['columns'], 'ordinal_position'));
        self::assertSame('CASCADE', $result['snapshot']['foreign_keys'][0]['on_update_action']);
        self::assertSame($columns['user.app_user_id'], $result['snapshot']['foreign_keys'][0]['columns'][0]['referenced_column_pid']);
    }

    public function testInvalidCrossTableColumnFailsWithoutReplacingExistingSnapshot(): void
    {
        [$app, $projectKey, $tables, $columns] = $this->fixture();
        $initial = app_replace_project_table_constraints($app, $projectKey, [
            'keys' => [[
                'table_pid' => $tables['user'],
                'key_name' => 'pk_user',
                'key_kind' => 'primary',
                'columns' => [['column_pid' => $columns['user.app_user_id']]],
            ]],
            'foreign_keys' => [],
        ]);
        self::assertTrue($initial['ok'], $initial['error']);

        $invalid = app_replace_project_table_constraints($app, $projectKey, [
            'keys' => [[
                'table_pid' => $tables['identity'],
                'key_name' => 'bad_key',
                'key_kind' => 'unique',
                'columns' => [['column_pid' => $columns['user.app_user_id']]],
            ]],
            'foreign_keys' => [],
        ]);
        self::assertFalse($invalid['ok']);
        self::assertStringContainsString('does not belong', $invalid['error']);
        $preserved = app_fetch_project_table_constraints($app, $projectKey);
        self::assertSame('pk_user', $preserved['snapshot']['keys'][0]['key_name']);
    }

    public function testPortableLiveSchemaSnapshotResolvesPhysicalNamesInsideCallerTransaction(): void
    {
        [$app, $projectKey, , $columns] = $this->fixture();
        $pdo = app_create_config_pdo($app);
        $projectId = app_table_constraint_metadata_project_id($pdo, $projectKey);
        $pdo->beginTransaction();
        app_replace_project_table_constraints_portable_pdo($pdo, $projectId, [
            'keys' => [[
                'table_name' => 'identity',
                'key_name' => 'uq_identity_issuer_subject',
                'key_kind' => 'unique',
                'columns' => [
                    ['column_name' => 'issuer', 'ordinal_position' => 1],
                    ['column_name' => 'subject', 'ordinal_position' => 2],
                ],
            ]],
            'foreign_keys' => [[
                'table_name' => 'identity',
                'constraint_name' => 'fk_identity_user',
                'referenced_table_name' => 'user',
                'on_update_action' => 'CASCADE',
                'on_delete_action' => 'RESTRICT',
                'columns' => [[
                    'column_name' => 'app_user_id',
                    'referenced_column_name' => 'app_user_id',
                    'ordinal_position' => 1,
                ]],
            ]],
        ]);
        $pdo->commit();

        $result = app_fetch_project_table_constraints($app, $projectKey);
        self::assertTrue($result['ok'], $result['error']);
        self::assertSame([$columns['identity.issuer'], $columns['identity.subject']], array_column($result['snapshot']['keys'][0]['columns'], 'column_pid'));
        self::assertSame('live-schema', $result['snapshot']['keys'][0]['source_of_truth']);
        self::assertSame($columns['user.app_user_id'], $result['snapshot']['foreign_keys'][0]['columns'][0]['referenced_column_pid']);
    }

    private function fixture(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-constraint-metadata-test-' . bin2hex(random_bytes(6));
        $configDb = app_config_store_config('sqlite', 'db', '0', 'config', 'user', 'secret', '/tmp', $storeDir);
        $app = ['site' => 'admin', 'db' => $configDb, 'config_db' => $configDb];
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);
        $projectKey = 'CONSTRAINT-' . strtoupper(bin2hex(random_bytes(3)));
        $project = app_pdo_insert_project($app, [
            'project_key' => $projectKey,
            'name' => 'Constraint Test',
            'slug' => strtolower($projectKey),
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner',
            'description' => 'constraint metadata',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $tables = [];
        $columns = [];
        foreach (['user' => ['app_user_id'], 'identity' => ['app_user_id', 'issuer', 'subject']] as $role => $fieldNames) {
            $table = app_create_table_metadata_item($app, $projectKey, $role);
            self::assertTrue($table['ok'], $table['error']);
            $tables[$role] = (int) $table['item']['pid'];
            foreach ($fieldNames as $fieldName) {
                $column = app_create_table_metadata_column($app, $projectKey, (string) $tables[$role], [
                    'name' => $fieldName,
                    'datatype' => 'text',
                    'is_null' => 'NO',
                    'is_key' => '',
                    'is_default' => '',
                    'extra' => '',
                    'memo' => '',
                ]);
                self::assertTrue($column['ok'], $column['error']);
                $columns[$role . '.' . $fieldName] = (int) $column['item']['pid'];
            }
        }
        return [$app, $projectKey, $tables, $columns];
    }
}
