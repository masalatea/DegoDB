<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/legacy_db_access_dump.php';

use PHPUnit\Framework\TestCase;

final class LegacyDbAccessDumpTest extends TestCase
{
    private string $fixtureRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureRoot = sys_get_temp_dir() . '/mtool-legacy-db-access-dump-' . bin2hex(random_bytes(6));
        mkdir($this->fixtureRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->fixtureRoot);
        parent::tearDown();
    }

    public function testExtractAndBuildSeedExportRowsFromDumpMatchesCanonicalFunctions(): void
    {
        $sqlDumpPath = $this->fixtureRoot . '/legacy.sql';
        file_put_contents(
            $sqlDumpPath,
            <<<'SQL'
INSERT INTO `da` (`ProjectPID`, `PID`, `name`, `StoreBasePath`, `IsAutoload`, `LastModifiedDT`) VALUES
(1, 10, 'Article', 'dbclasses', 1, '2026-05-19 00:00:00'),
(1, 11, 'Ignored', 'dbclasses', 1, '2026-05-19 00:00:00');
INSERT INTO `dafunc` (`ProjectPID`, `daPID`, `PID`, `name`, `ActionType`, `InsertUpdateDeleteTargetTable`, `InsertUpdateDeleteParamType`, `SelectByDistinct`, `SortOrderColumns`, `DataClassBaseNameForSelectAction`, `FunctionListOrder`, `memo`, `limitParameterType`, `limitFixedParameter`, `ORGroupType`, `SingleProxy_AuthType`, `SingleProxy_SingleGetFuncPID`, `IsBlobTarget`) VALUES
(1, 10, 100, 'Article', 'selectlist', 'articles', 'argument', 0, 'ArticleID DESC', 'Article', 1, '', '', '', '', '', 0, 0),
(1, 10, 101, 'Article', 'insert', 'articles', 'argument', 0, '', '', 2, '', '', '', '', '', 0, 1),
(1, 11, 110, 'Ignored', 'selectlist', 'ignored', 'argument', 0, 'IgnoredID DESC', 'Ignored', 1, '', '', '', '', '', 0, 0);
INSERT INTO `dafuncselectwhere` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableName`, `targetTableAliasName`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `AnotherTableName`, `AnotherTableAliasName`, `AnotherFieldName`, `JoinType`, `ORGroup`, `RelationalOperator`, `WhereOrder`) VALUES
(1, 10, 100, 1, 'articles', 'a', 'ArticleID', 'argument', 'int', '', '', '', '', '', '', '=', 1),
(1, 11, 110, 2, 'ignored', 'i', 'IgnoredID', 'argument', 'int', '', '', '', '', '', '', '=', 1);
INSERT INTO `dafuncselecttargetfields` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableName`, `targetTableAliasName`, `targetTableColumnName`, `targetTableColumnPrefix`, `targetTableColumnSuffix`, `storeClassFieldName`, `GroupByTarget`, `FieldListOrder`) VALUES
(1, 10, 100, 1, 'articles', 'a', 'Title', '', '', 'Title', 0, 1);
INSERT INTO `dafuncinserttargetfields` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `FieldListOrder`) VALUES
(1, 10, 101, 1, 'FileBody', 'argument', 'file', '', 1);
INSERT INTO `dafuncupdatetargetfields` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `FieldListOrder`) VALUES
(1, 10, 101, 1, 'FileBody', 'argument', 'file', '', 1);
INSERT INTO `dafuncupdatedeletewhere` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `targetTableColumnName`, `ParameterType`, `ParameterDataType`, `FixedParameter`, `ORGroup`, `RelationalOperator`, `WhereOrder`) VALUES
(1, 10, 101, 1, 'ArticleID', 'argument', 'int', '', '', '=', 1);
INSERT INTO `dafuncselecthaving` (`ProjectPID`, `daPID`, `dafuncPID`, `PID`, `LeftTargetPrefix`, `LeftTargetFieldPID`, `LeftTargetSuffix`, `RelationalOperator`, `RightTargetPrefix`, `RightParameterType`, `RightParameterDataType`, `RightFixedParameter`, `RightTargetFieldPID`, `RightTargetSuffix`, `HavingListOrder`) VALUES
(1, 10, 100, 1, '', 1, '', '=', '', 'fixed', 'int', '1', 0, '', 1);
SQL,
        );

        $legacyData = app_legacy_db_access_extract_seed_export_data_from_dump($sqlDumpPath, 1);

        self::assertTrue($legacyData['ok'], (string) ($legacyData['error'] ?? ''));
        self::assertCount(2, $legacyData['db_access_classes']);
        self::assertSame('Article', $legacyData['db_access_classes'][0]['source_name']);
        self::assertCount(3, $legacyData['functions']);
        self::assertSame('GetArticleList', $legacyData['functions'][0]['function_name']);
        self::assertSame('InsertArticle', $legacyData['functions'][1]['function_name']);
        self::assertCount(2, $legacyData['select_wheres']);
        self::assertCount(1, $legacyData['select_havings']);

        $seedRows = app_legacy_db_access_build_seed_export_rows_from_dump(
            [
                'functions' => $legacyData['functions'],
                'select_wheres' => $legacyData['select_wheres'],
                'select_target_fields' => $legacyData['select_target_fields'],
                'insert_target_fields' => $legacyData['insert_target_fields'],
                'update_target_fields' => $legacyData['update_target_fields'],
                'update_delete_wheres' => $legacyData['update_delete_wheres'],
                'select_havings' => $legacyData['select_havings'],
            ],
            [
                [
                    'source_name' => 'Article',
                    'function_name' => 'GetArticleList',
                ],
                [
                    'source_name' => 'Article',
                    'function_name' => 'InsertArticle',
                ],
            ],
        );

        self::assertSame(
            [
                [
                    'source_name' => 'Article',
                    'function_name' => 'GetArticleList',
                    'sort_order_columns' => 'ArticleID DESC',
                ],
            ],
            $seedRows['selectlist_sort_order_rows'],
        );
        self::assertCount(1, $seedRows['designer_rows']['select_wheres']);
        self::assertSame('Article', $seedRows['designer_rows']['select_wheres'][0]['source_name']);
        self::assertSame('seed-legacy', $seedRows['designer_rows']['select_wheres'][0]['source_of_truth']);
        self::assertCount(1, $seedRows['designer_rows']['select_target_fields']);
        self::assertCount(1, $seedRows['designer_rows']['insert_target_fields']);
        self::assertSame('file', $seedRows['designer_rows']['insert_target_fields'][0]['parameter_data_type']);
        self::assertSame('seed-legacy', $seedRows['designer_rows']['insert_target_fields'][0]['source_of_truth']);
        self::assertCount(1, $seedRows['designer_rows']['update_target_fields']);
        self::assertCount(1, $seedRows['designer_rows']['update_delete_wheres']);
        self::assertSame(1, $seedRows['select_having_count']);
    }

    public function testExtractSeedExportDataFromDumpReturnsErrorForMissingFile(): void
    {
        $result = app_legacy_db_access_extract_seed_export_data_from_dump($this->fixtureRoot . '/missing.sql', 1);

        self::assertFalse($result['ok']);
        self::assertSame('SQL dump が見つかりません: ' . $this->fixtureRoot . '/missing.sql', $result['error']);
    }

    /**
     * @param string $path
     */
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
