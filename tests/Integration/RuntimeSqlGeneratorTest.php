<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_output_runtime_sql_generator.php';

use PHPUnit\Framework\TestCase;

final class RuntimeSqlGeneratorTest extends TestCase
{
    public function testGroupedConditionPartsSupportsAndOrAndMode(): void
    {
        $result = app_project_output_runtime_sql_grouped_condition_parts(
            [
                ['or_group' => '', 'expression' => 'Base.IsActive = 1'],
                ['or_group' => '1', 'expression' => 'Base.OwnerPID = Person.PID'],
                ['or_group' => '1', 'expression' => 'Base.EditorPID = Person.PID'],
                ['or_group' => '2', 'expression' => 'Base.CreatedByPID = Person.PID'],
                ['or_group' => '2', 'expression' => 'Base.UpdatedByPID = Person.PID'],
            ],
            static fn (array $row): array => [
                'ok' => true,
                'parts' => [
                    [
                        'type' => 'string',
                        'value' => (string) ($row['expression'] ?? ''),
                    ],
                ],
                'reason' => '',
            ],
            'andorand',
        );

        self::assertTrue($result['ok'], (string) ($result['reason'] ?? ''));
        self::assertSame(
            'Base.IsActive = 1 and ((Base.OwnerPID = Person.PID and Base.EditorPID = Person.PID) or (Base.CreatedByPID = Person.PID and Base.UpdatedByPID = Person.PID))',
            $this->flattenParts($result['parts']),
        );
    }

    public function testBuildSelectFromSqlSupportsAnotherfieldJoinOrGroups(): void
    {
        $result = app_project_output_runtime_sql_build_select_from_sql(
            [
                [
                    'target_table_name' => 'Article',
                    'target_table_alias_name' => '',
                    'target_table_column_name' => 'PID',
                ],
            ],
            [
                [
                    'target_table_name' => 'Article',
                    'target_table_alias_name' => '',
                    'target_table_column_name' => 'AuthorPID',
                    'parameter_type' => 'anotherfield',
                    'another_table_name' => 'Person',
                    'another_table_alias_name' => '',
                    'another_field_name' => 'PID',
                    'join_type' => 'left',
                    'or_group' => '1',
                    'relational_operator' => '=',
                ],
                [
                    'target_table_name' => 'Article',
                    'target_table_alias_name' => '',
                    'target_table_column_name' => 'ReviewerPID',
                    'parameter_type' => 'anotherfield',
                    'another_table_name' => 'Person',
                    'another_table_alias_name' => '',
                    'another_field_name' => 'PID',
                    'join_type' => 'left',
                    'or_group' => '1',
                    'relational_operator' => '=',
                ],
            ],
        );

        self::assertTrue($result['ok'], (string) ($result['reason'] ?? ''));
        self::assertSame([], $result['where_rows']);
        self::assertSame(
            'from Article left outer join Person on (Article.AuthorPID = Person.PID or Article.ReviewerPID = Person.PID)',
            $result['from_sql'],
        );
    }

    public function testTryGenerateSelectMethodUsesFunctionOrGroupTypeForJoinConditions(): void
    {
        $result = app_project_output_runtime_sql_try_generate_select_method(
            'GetArticleList',
            true,
            'Article',
            [
                'select_by_distinct' => '0',
                'sort_order_columns' => '',
                'limit_parameter_type' => '',
                'data_class_base_name' => 'Article',
                'or_group_type' => 'andorand',
            ],
            [],
            [
                'select_target_fields' => [
                    [
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'PID',
                        'target_table_column_prefix' => '',
                        'target_table_column_suffix' => '',
                        'store_class_field_name' => 'PID',
                        'group_by_target' => '0',
                    ],
                ],
                'select_havings' => [],
                'select_wheres' => [
                    [
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'AuthorPID',
                        'parameter_type' => 'anotherfield',
                        'parameter_data_type' => '',
                        'fixed_parameter' => '',
                        'another_table_name' => 'Person',
                        'another_table_alias_name' => '',
                        'another_field_name' => 'PID',
                        'join_type' => 'left',
                        'or_group' => '1',
                        'relational_operator' => '=',
                    ],
                    [
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'ReviewerPID',
                        'parameter_type' => 'anotherfield',
                        'parameter_data_type' => '',
                        'fixed_parameter' => '',
                        'another_table_name' => 'Person',
                        'another_table_alias_name' => '',
                        'another_field_name' => 'PID',
                        'join_type' => 'left',
                        'or_group' => '1',
                        'relational_operator' => '=',
                    ],
                ],
                'update_delete_wheres' => [],
            ],
        );

        self::assertTrue($result['ok']);
        self::assertSame('canonical-sql', $result['result']['mode']);
        self::assertContains(
            "        \$last_sql_command_for_mtooldb = 'select Article.PID from Article left outer join Person on (Article.AuthorPID = Person.PID and Article.ReviewerPID = Person.PID)';",
            $result['result']['body_lines'],
        );
        self::assertContains(
            '        $ret = $mtooldb->execute($last_sql_command_for_mtooldb, [',
            $result['result']['body_lines'],
        );
    }

    public function testTryGenerateSelectMethodSupportsHavingArgumentAfterWhereAndBeforeLimit(): void
    {
        $result = app_project_output_runtime_sql_try_generate_select_method(
            'GetArticleCategorySummaryList',
            true,
            'Article',
            [
                'select_by_distinct' => '0',
                'sort_order_columns' => 'count(Article.PID) desc',
                'limit_parameter_type' => 'argument',
                'data_class_base_name' => 'ArticleCategorySummary',
                'or_group_type' => '',
            ],
            ['$status', '$minCount', '$limit'],
            [
                'select_target_fields' => [
                    [
                        'select_target_field_id' => '1',
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'Category',
                        'target_table_column_prefix' => '',
                        'target_table_column_suffix' => '',
                        'store_class_field_name' => 'Category',
                        'group_by_target' => '1',
                    ],
                    [
                        'select_target_field_id' => '2',
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'PID',
                        'target_table_column_prefix' => 'count(',
                        'target_table_column_suffix' => ')',
                        'store_class_field_name' => 'ArticleCount',
                        'group_by_target' => '0',
                    ],
                ],
                'select_havings' => [
                    [
                        'left_target_prefix' => '',
                        'left_target_field_id' => '2',
                        'left_target_suffix' => '',
                        'relational_operator' => '>=',
                        'right_target_prefix' => '',
                        'right_parameter_type' => 'argument',
                        'right_parameter_data_type' => '',
                        'right_fixed_parameter' => '',
                        'right_target_field_id' => '0',
                        'right_target_suffix' => '',
                    ],
                ],
                'select_wheres' => [
                    [
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'Status',
                        'parameter_type' => 'argument',
                        'parameter_data_type' => '',
                        'fixed_parameter' => '',
                        'another_table_name' => '',
                        'another_table_alias_name' => '',
                        'another_field_name' => '',
                        'join_type' => '',
                        'or_group' => '',
                        'relational_operator' => '=',
                    ],
                ],
                'update_delete_wheres' => [],
            ],
        );

        self::assertTrue($result['ok']);
        self::assertSame('canonical-sql', $result['result']['mode']);

        $body = implode("\n", $result['result']['body_lines']);
        self::assertStringContainsString(
            "\$last_sql_command_for_mtooldb = 'select Article.Category, count(Article.PID) from Article where Article.Status = ? group by Article.Category having count(Article.PID) >= ? order by count(Article.PID) desc limit ?';",
            $body,
        );
        self::assertStringContainsString('$mtooldb->execute($last_sql_command_for_mtooldb, [', $body);
        self::assertStringContainsString(
            "            \$status,\n            \$minCount,\n            \$limit,",
            $body,
        );
    }

    public function testTryGenerateSelectMethodSupportsHavingFieldAndFixedConditions(): void
    {
        $result = app_project_output_runtime_sql_try_generate_select_method(
            'GetArticleCategorySummaryList',
            true,
            'Article',
            [
                'select_by_distinct' => '0',
                'sort_order_columns' => '',
                'limit_parameter_type' => '',
                'data_class_base_name' => 'ArticleCategorySummary',
                'or_group_type' => '',
            ],
            [],
            [
                'select_target_fields' => [
                    [
                        'select_target_field_id' => '1',
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'Category',
                        'target_table_column_prefix' => '',
                        'target_table_column_suffix' => '',
                        'store_class_field_name' => 'Category',
                        'group_by_target' => '1',
                    ],
                    [
                        'select_target_field_id' => '2',
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'PID',
                        'target_table_column_prefix' => 'count(',
                        'target_table_column_suffix' => ')',
                        'store_class_field_name' => 'ArticleCount',
                        'group_by_target' => '0',
                    ],
                    [
                        'select_target_field_id' => '3',
                        'target_table_name' => 'Article',
                        'target_table_alias_name' => '',
                        'target_table_column_name' => 'RequiredCount',
                        'target_table_column_prefix' => 'sum(',
                        'target_table_column_suffix' => ')',
                        'store_class_field_name' => 'RequiredCountSum',
                        'group_by_target' => '0',
                    ],
                ],
                'select_havings' => [
                    [
                        'left_target_prefix' => '',
                        'left_target_field_id' => '2',
                        'left_target_suffix' => '',
                        'relational_operator' => '>=',
                        'right_target_prefix' => 'coalesce(',
                        'right_parameter_type' => 'field',
                        'right_parameter_data_type' => '',
                        'right_fixed_parameter' => '',
                        'right_target_field_id' => '3',
                        'right_target_suffix' => ', 0)',
                    ],
                    [
                        'left_target_prefix' => 'lower(',
                        'left_target_field_id' => '1',
                        'left_target_suffix' => ')',
                        'relational_operator' => '=',
                        'right_target_prefix' => 'lower(',
                        'right_parameter_type' => 'fixed',
                        'right_parameter_data_type' => '',
                        'right_fixed_parameter' => 'published',
                        'right_target_field_id' => '0',
                        'right_target_suffix' => ')',
                    ],
                ],
                'select_wheres' => [],
                'update_delete_wheres' => [],
            ],
        );

        self::assertTrue($result['ok']);
        self::assertSame('canonical-sql', $result['result']['mode']);

        $body = implode("\n", $result['result']['body_lines']);
        self::assertStringContainsString(
            "\$last_sql_command_for_mtooldb = 'select Article.Category, count(Article.PID), sum(Article.RequiredCount) from Article group by Article.Category having count(Article.PID) >= coalesce(sum(Article.RequiredCount), 0) and lower(Article.Category) = lower(?)';",
            $body,
        );
        self::assertStringContainsString("            'published',", $body);
    }

    public function testTryGenerateInsertMethodDelegatesBlobTargets(): void
    {
        $result = app_project_output_runtime_sql_try_generate_insert_method(
            'InsertDegoWorkplaceFile',
            'DegoWorkplaceFile',
            [
                'target_table_name' => 'DegoWorkplaceFile',
                'parameter_type' => 'classobject',
                'is_blob_target' => '1',
            ],
            ['$DegoWorkplaceFileObj'],
            [
                'insert_target_fields' => [
                    [
                        'target_table_column_name' => 'Filename',
                        'parameter_type' => 'argument',
                        'parameter_data_type' => '',
                        'fixed_parameter' => '',
                    ],
                    [
                        'target_table_column_name' => 'File',
                        'parameter_type' => 'argument',
                        'parameter_data_type' => 'file',
                        'fixed_parameter' => '',
                    ],
                ],
            ],
        );

        self::assertTrue($result['ok']);
        self::assertSame('legacy-delegate', $result['result']['mode']);
        self::assertSame(
            'blob target requires prepared statement send_long_data handling',
            $result['result']['reason'],
        );
        self::assertSame(
            ['        return parent::InsertDegoWorkplaceFile(...func_get_args());'],
            $result['result']['body_lines'],
        );
    }

    public function testTryGenerateUpdateMethodDelegatesBlobTargets(): void
    {
        $result = app_project_output_runtime_sql_try_generate_update_method(
            'UpdateDegoWorkplaceFile',
            'DegoWorkplaceFile',
            [
                'target_table_name' => 'DegoWorkplaceFile',
                'parameter_type' => 'classobject',
                'is_blob_target' => '1',
                'or_group_type' => '',
            ],
            ['$DegoWorkplaceFileObj'],
            [
                'update_target_fields' => [
                    [
                        'target_table_column_name' => 'Filename',
                        'parameter_type' => 'argument',
                        'parameter_data_type' => '',
                        'fixed_parameter' => '',
                    ],
                    [
                        'target_table_column_name' => 'File',
                        'parameter_type' => 'argument',
                        'parameter_data_type' => 'file',
                        'fixed_parameter' => '',
                    ],
                ],
                'update_delete_wheres' => [
                    [
                        'target_table_column_name' => 'FileKey',
                        'parameter_type' => 'argument',
                        'parameter_data_type' => '',
                        'fixed_parameter' => '',
                        'relational_operator' => '=',
                        'or_group' => '',
                    ],
                ],
            ],
        );

        self::assertTrue($result['ok']);
        self::assertSame('legacy-delegate', $result['result']['mode']);
        self::assertSame(
            'blob target requires prepared statement send_long_data handling',
            $result['result']['reason'],
        );
        self::assertSame(
            ['        return parent::UpdateDegoWorkplaceFile(...func_get_args());'],
            $result['result']['body_lines'],
        );
    }

    /**
     * @param list<array{type:string,value:string}> $parts
     */
    private function flattenParts(array $parts): string
    {
        return implode(
            '',
            array_map(
                static fn (array $part): string => (string) ($part['value'] ?? ''),
                $parts,
            ),
        );
    }
}
