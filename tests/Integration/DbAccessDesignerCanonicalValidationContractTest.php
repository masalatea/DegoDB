<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/data_class_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_db_access_metadata_helper.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/table_metadata_repository.php';

use PHPUnit\Framework\TestCase;

final class DbAccessDesignerCanonicalValidationContractTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupProjectKeys = [];

    protected function tearDown(): void
    {
        $app = app_bootstrap();
        $pdo = app_create_config_pdo($app);

        foreach (array_reverse($this->cleanupProjectKeys) as $projectKey) {
            try {
                $projectId = $this->resolveProjectId($pdo, $projectKey);
                if ($projectId <= 0) {
                    continue;
                }

                $statement = $pdo->prepare('DELETE FROM dataclassfields WHERE ProjectPID = :project_id');
                $statement->execute([
                    ':project_id' => $projectId,
                ]);

                $statement = $pdo->prepare('DELETE FROM dataclass WHERE ProjectPID = :project_id');
                $statement->execute([
                    ':project_id' => $projectId,
                ]);

                $statement = $pdo->prepare('DELETE FROM dbtablecolumns WHERE ProjectPID = :project_id');
                $statement->execute([
                    ':project_id' => $projectId,
                ]);

                $statement = $pdo->prepare('DELETE FROM dbtable WHERE ProjectPID = :project_id');
                $statement->execute([
                    ':project_id' => $projectId,
                ]);

                $statement = $pdo->prepare('DELETE FROM project_memberships WHERE project_id = :project_id');
                $statement->execute([
                    ':project_id' => $projectId,
                ]);

                $statement = $pdo->prepare('DELETE FROM projects WHERE id = :project_id');
                $statement->execute([
                    ':project_id' => $projectId,
                ]);
            } catch (Throwable) {
            }
        }

        $this->cleanupProjectKeys = [];

        parent::tearDown();
    }

    public function testSelectTargetFieldValidationAcceptsCanonicalWildcardAndRejectsUnknownStoreField(): void
    {
        $app = app_bootstrap();
        $projectKey = $this->createProjectFixture($app);
        $this->seedTableFixture($app, $projectKey, 'Article', ['PID', 'Title']);
        $this->seedDataClassFixture($app, $projectKey, 'Article', ['PID', 'Title']);

        $wildcardErrors = app_project_db_access_validate_select_target_field_metadata_refs(
            $app,
            $projectKey,
            'Article',
            [
                'target_table_name' => 'Article',
                'target_table_alias_name' => '',
                'target_table_column_name' => '*',
                'target_table_column_prefix' => '',
                'target_table_column_suffix' => '',
                'store_class_field_name' => '',
                'group_by_target' => '0',
                'field_list_order' => '10',
                'source_of_truth' => 'manual',
            ],
        );
        self::assertSame([], $wildcardErrors);

        $missingStoreFieldErrors = app_project_db_access_validate_select_target_field_metadata_refs(
            $app,
            $projectKey,
            'Article',
            [
                'target_table_name' => 'Article',
                'target_table_alias_name' => '',
                'target_table_column_name' => 'Title',
                'target_table_column_prefix' => '',
                'target_table_column_suffix' => '',
                'store_class_field_name' => 'MissingField',
                'group_by_target' => '0',
                'field_list_order' => '10',
                'source_of_truth' => 'manual',
            ],
        );
        self::assertSame(
            ['Store Class Field Name に指定した canonical data class field が見つかりません: Article.MissingField'],
            $missingStoreFieldErrors,
        );
    }

    public function testSelectWhereValidationRejectsUnknownCanonicalAnotherFieldReference(): void
    {
        $app = app_bootstrap();
        $projectKey = $this->createProjectFixture($app);
        $this->seedTableFixture($app, $projectKey, 'Article', ['PID', 'AuthorPID']);
        $this->seedTableFixture($app, $projectKey, 'Person', ['PID']);

        $errors = app_project_db_access_validate_select_where_metadata_refs(
            $app,
            $projectKey,
            [
                'target_table_name' => 'Article',
                'target_table_alias_name' => '',
                'target_table_column_name' => 'AuthorPID',
                'parameter_type' => 'anotherfield',
                'parameter_data_type' => '',
                'fixed_parameter' => '',
                'another_table_name' => 'Person',
                'another_table_alias_name' => '',
                'another_field_name' => 'MissingPID',
                'join_type' => 'left',
                'or_group' => '',
                'relational_operator' => '=',
                'where_order' => '10',
                'source_of_truth' => 'manual',
            ],
        );

        self::assertSame(
            ['Another Field Name に指定した canonical column が見つかりません: Person.MissingPID'],
            $errors,
        );
    }

    public function testUpdateDeleteWhereValidationRejectsUnknownCanonicalTargetColumn(): void
    {
        $app = app_bootstrap();
        $projectKey = $this->createProjectFixture($app);
        $this->seedTableFixture($app, $projectKey, 'Article', ['PID', 'Title']);

        $errors = app_project_db_access_validate_update_delete_where_metadata_refs(
            $app,
            $projectKey,
            'Article',
            [
                'target_table_column_name' => 'MissingColumn',
                'parameter_type' => 'argument',
                'parameter_data_type' => 'raw',
                'fixed_parameter' => '',
                'or_group' => '',
                'relational_operator' => '=',
                'where_order' => '10',
                'source_of_truth' => 'manual',
            ],
        );

        self::assertSame(
            ['Target Column Name に指定した canonical column が見つかりません: Article.MissingColumn'],
            $errors,
        );
    }

    private function createProjectFixture(array $app): string
    {
        $projectKey = 'DBVAL' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $this->cleanupProjectKeys[] = $projectKey;

        $result = app_insert_project($app, [
            'project_key' => $projectKey,
            'name' => 'DB Access Validation ' . $projectKey,
            'slug' => strtolower($projectKey),
            'lifecycle_status' => 'active',
            'owner_login_id' => 'phpunit',
            'description' => 'db access validation fixture',
        ]);
        self::assertTrue($result['ok'], $result['error']);

        return $projectKey;
    }

    /**
     * @param list<string> $columnNames
     */
    private function seedTableFixture(array $app, string $projectKey, string $tableName, array $columnNames): void
    {
        $tableResult = app_create_table_metadata_item($app, $projectKey, $tableName);
        self::assertTrue($tableResult['ok'], $tableResult['error']);
        self::assertIsArray($tableResult['item']);

        $tablePid = (string) ($tableResult['item']['pid'] ?? '');
        self::assertNotSame('', $tablePid);

        foreach ($columnNames as $columnName) {
            $columnResult = app_create_table_metadata_column($app, $projectKey, $tablePid, [
                'name' => $columnName,
                'datatype' => 'varchar(255)',
                'is_null' => '0',
                'is_key' => '0',
                'is_default' => '0',
                'extra' => '',
                'memo' => '',
            ]);
            self::assertTrue($columnResult['ok'], $columnResult['error']);
        }
    }

    /**
     * @param list<string> $fieldNames
     */
    private function seedDataClassFixture(array $app, string $projectKey, string $dataClassName, array $fieldNames): void
    {
        $dataClassResult = app_create_data_class_metadata_item($app, $projectKey, [
            'name' => $dataClassName,
            'store_base_path' => '',
            'is_autoload' => '0',
            'inherit_parent_data_class_name' => '',
        ]);
        self::assertTrue($dataClassResult['ok'], $dataClassResult['error']);
        self::assertIsArray($dataClassResult['item']);

        $dataClassPid = (string) ($dataClassResult['item']['pid'] ?? '');
        self::assertNotSame('', $dataClassPid);

        foreach ($fieldNames as $fieldName) {
            $fieldResult = app_create_data_class_metadata_field($app, $projectKey, $dataClassPid, [
                'name' => $fieldName,
                'datatype' => 'string',
                'ref_data_class_name' => '',
                'ref_data_class_field_name' => '',
            ]);
            self::assertTrue($fieldResult['ok'], $fieldResult['error']);
        }
    }

    private function resolveProjectId(PDO $pdo, string $projectKey): int
    {
        $statement = $pdo->prepare('SELECT id FROM projects WHERE project_key = :project_key LIMIT 1');
        $statement->execute([
            ':project_key' => $projectKey,
        ]);

        return (int) ($statement->fetchColumn() ?: 0);
    }
}
