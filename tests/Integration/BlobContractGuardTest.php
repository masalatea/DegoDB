<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/generated_catalog.php';
require_once dirname(__DIR__, 2) . '/mtool/app/db_access_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/db_access_seed_export_guard.php';
require_once dirname(__DIR__, 2) . '/mtool/app/domain_validation.php';

use PHPUnit\Framework\TestCase;

final class BlobContractGuardTest extends TestCase
{
    private string $fixtureRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureRoot = sys_get_temp_dir() . '/mtool-blob-contract-guard-' . bin2hex(random_bytes(6));
        mkdir($this->fixtureRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->fixtureRoot);
        parent::tearDown();
    }

    public function testBlobContractDetectorChecksLegacySupportCompanion(): void
    {
        mkdir($this->fixtureRoot . '/_support/legacy-dbaccess', 0777, true);
        file_put_contents(
            $this->fixtureRoot . '/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccess
{
    public function InsertBlobThing($obj)
    {
        return parent::InsertBlobThing(...func_get_args());
    }
}
PHP,
        );
        file_put_contents(
            $this->fixtureRoot . '/_support/legacy-dbaccess/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccessLegacy
{
    public function InsertBlobThing($obj)
    {
        $stmt = $db->prepare('insert into BlobThing (FileBody) values(?)');
        $dummy = null;
        $stmt->bind_param("b", $dummy);
        $stmt->send_long_data(0, 'chunk');
        return $stmt->execute();
    }
}
PHP,
        );

        self::assertTrue(
            app_generated_file_method_has_blob_streaming_contract(
                $this->fixtureRoot . '/dbaccess-BlobThing.php',
                'InsertBlobThing',
            ),
        );
    }

    public function testBlobContractDetectorReturnsFalseForRegularMethod(): void
    {
        file_put_contents(
            $this->fixtureRoot . '/dbaccess-RegularThing.php',
            <<<'PHP'
<?php
class RegularThingDBAccess
{
    public function InsertRegularThing($obj)
    {
        return true;
    }
}
PHP,
        );

        self::assertFalse(
            app_generated_file_method_has_blob_streaming_contract(
                $this->fixtureRoot . '/dbaccess-RegularThing.php',
                'InsertRegularThing',
            ),
        );
    }

    public function testValidationRejectsBlobTargetOutsideInsertOrUpdate(): void
    {
        $result = app_validate_db_access_function_form([
            'source_name' => 'Article',
            'function_name' => 'GetArticleList',
            'function_list_order' => '1',
            'function_suffix' => 'ArticleList',
            'action_type' => 'SELECTLIST',
            'data_class_base_name' => 'Article',
            'target_table_name' => '',
            'parameter_type' => '',
            'select_by_distinct' => '0',
            'sort_order_columns' => '',
            'memo' => '',
            'limit_parameter_type' => '',
            'limit_fixed_parameter' => '',
            'or_group_type' => '',
            'single_proxy_auth_type' => '',
            'single_proxy_single_get_function_name' => '',
            'is_blob_target' => '1',
            'detected_signature' => 'public function GetArticleList()',
            'detected_line' => '10',
            'source_of_truth' => 'manual',
        ]);

        self::assertContains('IsBlobTarget=1 は INSERT/UPDATE のみ設定できます。', $result['errors']);
    }

    public function testRepositoryBlobTargetGuardRejectsMissingLegacyContract(): void
    {
        file_put_contents(
            $this->fixtureRoot . '/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccess
{
    public function InsertBlobThing($obj)
    {
        return true;
    }
}
PHP,
        );

        self::assertSame(
            'IsBlobTarget=1 は legacy method source に prepare()/bind_param("b")/send_long_data() がある function でのみ保存できます。',
            app_pdo_validate_db_access_function_blob_target_constraint(
                [
                    'generated' => [
                        'dbclasses_root' => $this->fixtureRoot,
                    ],
                ],
                [
                    'source_name' => 'BlobThing',
                    'function_name' => 'InsertBlobThing',
                    'action_type' => 'INSERT',
                    'is_blob_target' => '1',
                    'last_detected_dbaccess_file' => 'dbaccess-BlobThing.php',
                ],
            ),
        );
    }

    public function testRepositoryBlobTargetGuardAllowsDetectedLegacyContract(): void
    {
        mkdir($this->fixtureRoot . '/_support/legacy-dbaccess', 0777, true);
        file_put_contents(
            $this->fixtureRoot . '/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccess
{
    public function InsertBlobThing($obj)
    {
        return parent::InsertBlobThing(...func_get_args());
    }
}
PHP,
        );
        file_put_contents(
            $this->fixtureRoot . '/_support/legacy-dbaccess/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccessLegacy
{
    public function InsertBlobThing($obj)
    {
        $stmt = $db->prepare('insert into BlobThing (FileBody) values(?)');
        $dummy = null;
        $stmt->bind_param("b", $dummy);
        $stmt->send_long_data(0, 'chunk');
        return $stmt->execute();
    }
}
PHP,
        );

        self::assertSame(
            '',
            app_pdo_validate_db_access_function_blob_target_constraint(
                [
                    'generated' => [
                        'dbclasses_root' => $this->fixtureRoot,
                    ],
                ],
                [
                    'source_name' => 'BlobThing',
                    'function_name' => 'InsertBlobThing',
                    'action_type' => 'INSERT',
                    'is_blob_target' => '1',
                    'last_detected_dbaccess_file' => 'dbaccess-BlobThing.php',
                ],
            ),
        );
    }

    public function testInsertUpdateTargetFieldValidationRejectsFileWithoutBlobContract(): void
    {
        $result = app_validate_db_access_function_insert_update_target_field_form(
            [
                'target_table_column_name' => 'FileBody',
                'parameter_type' => 'argument',
                'parameter_data_type' => 'file',
                'fixed_parameter' => '',
                'field_list_order' => '1',
                'source_of_truth' => 'manual',
            ],
            false,
            false,
        );

        self::assertContains('この function では file data type は利用できません。', $result['errors']);
        self::assertContains(
            'file data type を使うには legacy method source に prepare()/bind_param("b")/send_long_data() が必要です。',
            $result['errors'],
        );
    }

    public function testRepositoryFileParameterGuardRejectsNonBlobTargetFunction(): void
    {
        self::assertSame(
            'この function では file data type は利用できません。',
            app_pdo_validate_db_access_function_file_parameter_constraint(
                [
                    'generated' => [
                        'dbclasses_root' => $this->fixtureRoot,
                    ],
                ],
                'BlobThing',
                'InsertBlobThing',
                'file',
                '0',
                'dbaccess-BlobThing.php',
            ),
        );
    }

    public function testSeedExportGuardRejectsFileInUnsupportedDesignerSection(): void
    {
        $errors = app_db_access_seed_export_collect_blob_contract_errors(
            [
                'generated' => [
                    'dbclasses_root' => $this->fixtureRoot,
                ],
            ],
            [
                [
                    'source_name' => 'BlobThing',
                    'last_detected_dbaccess_file' => 'dbaccess-BlobThing.php',
                ],
            ],
            [
                [
                    'source_name' => 'BlobThing',
                    'function_name' => 'GetBlobThingList',
                    'action_type' => 'SELECTLIST',
                    'is_blob_target' => 0,
                ],
            ],
            [
                'select_wheres' => [
                    [
                        'source_name' => 'BlobThing',
                        'function_name' => 'GetBlobThingList',
                        'parameter_data_type' => 'file',
                    ],
                ],
                'insert_target_fields' => [],
                'update_target_fields' => [],
                'update_delete_wheres' => [],
            ],
        );

        self::assertContains(
            'select_wheres BlobThing.GetBlobThingList: file data type はこの designer row type では未対応です。',
            $errors,
        );
    }

    public function testSeedExportGuardAllowsSupportedBlobInsertField(): void
    {
        mkdir($this->fixtureRoot . '/_support/legacy-dbaccess', 0777, true);
        file_put_contents(
            $this->fixtureRoot . '/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccess
{
    public function InsertBlobThing($obj)
    {
        return parent::InsertBlobThing(...func_get_args());
    }
}
PHP,
        );
        file_put_contents(
            $this->fixtureRoot . '/_support/legacy-dbaccess/dbaccess-BlobThing.php',
            <<<'PHP'
<?php
class BlobThingDBAccessLegacy
{
    public function InsertBlobThing($obj)
    {
        $stmt = $db->prepare('insert into BlobThing (FileBody) values(?)');
        $dummy = null;
        $stmt->bind_param("b", $dummy);
        $stmt->send_long_data(0, 'chunk');
        return $stmt->execute();
    }
}
PHP,
        );

        $errors = app_db_access_seed_export_collect_blob_contract_errors(
            [
                'generated' => [
                    'dbclasses_root' => $this->fixtureRoot,
                ],
            ],
            [
                [
                    'source_name' => 'BlobThing',
                    'last_detected_dbaccess_file' => 'dbaccess-BlobThing.php',
                ],
            ],
            [
                [
                    'source_name' => 'BlobThing',
                    'function_name' => 'InsertBlobThing',
                    'action_type' => 'INSERT',
                    'is_blob_target' => 1,
                ],
            ],
            [
                'select_wheres' => [],
                'insert_target_fields' => [
                    [
                        'source_name' => 'BlobThing',
                        'function_name' => 'InsertBlobThing',
                        'parameter_data_type' => 'file',
                    ],
                ],
                'update_target_fields' => [],
                'update_delete_wheres' => [],
            ],
        );

        self::assertSame([], $errors);
    }

    private function removeTree(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            @unlink($path);
            return;
        }

        $entries = scandir($path);
        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $this->removeTree($path . '/' . $entry);
            }
        }

        @rmdir($path);
    }
}
