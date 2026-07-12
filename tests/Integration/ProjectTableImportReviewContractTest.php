<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_table_import_service.php';

use PHPUnit\Framework\TestCase;

final class ProjectTableImportReviewContractTest extends TestCase
{
    public function testImportPlanIncludesReadableReviewForUpdateDeleteAndStaleDiffs(): void
    {
        $sourceTables = [
            $this->table('Article', [
                $this->column('Id', 'int', 0, 1, 0, 'auto_increment', 10),
                $this->column('Title', 'varchar(255)', 0, 0, 0, '', 20),
                $this->column('Body', 'text', 1, 0, 0, '', 30),
            ]),
        ];
        $canonicalTables = [
            $this->table('Article', [
                $this->column('Id', 'int', 0, 1, 0, 'auto_increment', 10),
                $this->column('Title', 'varchar(64)', 0, 0, 0, '', 20),
                $this->column('LegacySlug', 'varchar(128)', 1, 0, 0, '', 40),
            ]),
            $this->table('OldArticleDraft', [
                $this->column('Id', 'int', 0, 1, 0, 'auto_increment', 10),
            ]),
        ];

        $plan = app_project_table_import_build_plan(
            'SAMPLE',
            'app_schema',
            'live-schema',
            'live schema',
            true,
            $sourceTables,
            $canonicalTables,
        );

        self::assertTrue($plan['ok'], $plan['error']);
        self::assertTrue($plan['summary']['review_required']);
        self::assertSame(3, $plan['summary']['destructive_change_count']);
        self::assertSame(3, $plan['summary']['metadata_update_count']);

        $tablesByName = [];
        foreach ($plan['tables'] as $table) {
            $tablesByName[$table['name']] = $table;
        }

        self::assertSame('changed', $tablesByName['Article']['status']);
        self::assertSame('destructive', $tablesByName['Article']['review']['risk_level']);
        self::assertTrue($tablesByName['Article']['review']['requires_review']);
        self::assertSame(
            ['Title', 'Body', 'LegacySlug'],
            array_map(
                static fn (array $change): string => (string) $change['name'],
                $tablesByName['Article']['review']['column_changes'],
            ),
        );
        self::assertSame(
            ['update', 'insert', 'delete'],
            array_map(
                static fn (array $change): string => (string) $change['status'],
                $tablesByName['Article']['review']['column_changes'],
            ),
        );
        self::assertSame('varchar(64)', $tablesByName['Article']['review']['column_changes'][0]['before']['datatype']);
        self::assertSame('varchar(255)', $tablesByName['Article']['review']['column_changes'][0]['after']['datatype']);

        self::assertSame('stale', $tablesByName['OldArticleDraft']['status']);
        self::assertSame('destructive', $tablesByName['OldArticleDraft']['review']['risk_level']);
        self::assertStringContainsString(
            'will be removed on apply',
            implode(' ', $tablesByName['OldArticleDraft']['review']['reasons']),
        );
    }

    public function testInformationSchemaRowsAcceptUppercaseInformationSchemaAliases(): void
    {
        $tables = app_project_table_import_source_tables_from_information_schema_rows([
            [
                'TABLE_NAME' => 'SupportTicket',
                'COLUMN_NAME' => 'Id',
                'COLUMN_TYPE' => 'bigint',
                'IS_NULLABLE' => 'NO',
                'COLUMN_KEY' => 'PRI',
                'COLUMN_DEFAULT' => null,
                'EXTRA' => 'auto_increment',
                'ORDINAL_POSITION' => 1,
            ],
            [
                'TABLE_NAME' => 'SupportTicket',
                'COLUMN_NAME' => 'Title',
                'COLUMN_TYPE' => 'character varying(255)',
                'IS_NULLABLE' => 'NO',
                'COLUMN_KEY' => '',
                'COLUMN_DEFAULT' => null,
                'EXTRA' => '',
                'ORDINAL_POSITION' => 2,
            ],
        ]);

        self::assertSame(['SupportTicket'], array_column($tables, 'name'));
        self::assertSame('SupportTicket', $tables[0]['physical_name']);
        self::assertSame('SupportTicket', $tables[0]['logical_name']);
        self::assertSame('SupportTicket', $tables[0]['generated_name']);
        self::assertSame(['Id', 'Title'], array_column($tables[0]['columns'], 'name'));
        self::assertSame('Id', $tables[0]['columns_by_name']['Id']['physical_name']);
        self::assertSame('Id', $tables[0]['columns_by_name']['Id']['logical_name']);
        self::assertSame('id', $tables[0]['columns_by_name']['Id']['generated_name']);
        self::assertSame('bigint', $tables[0]['columns_by_name']['Id']['datatype']);
        self::assertSame('PRI', $tables[0]['columns_by_name']['Id']['is_key']);
        self::assertSame('auto_increment', $tables[0]['columns_by_name']['Id']['extra']);
        self::assertSame('character varying(255)', $tables[0]['columns_by_name']['Title']['datatype']);
    }

    public function testImportPlanReportsConstraintReviewAndFocusedApplyBoundary(): void
    {
        $constraints = [
            'keys' => [['table_name' => 'Article', 'key_name' => 'PRIMARY']],
            'foreign_keys' => [['table_name' => 'Article', 'constraint_name' => 'fk_article_user']],
        ];
        $full = app_project_table_import_build_plan('SAMPLE', 'app', 'live-schema', 'live', true, [], [], true, $constraints, false);
        self::assertTrue($full['summary']['constraints_supported']);
        self::assertTrue($full['summary']['constraint_apply_supported']);
        self::assertSame(1, $full['summary']['source_key_constraint_count']);
        self::assertSame(1, $full['summary']['source_foreign_key_constraint_count']);

        $focused = app_project_table_import_build_plan('SAMPLE', 'app', 'live-schema', 'live', true, [], [], true, $constraints, true);
        self::assertFalse($focused['summary']['constraint_apply_supported']);
    }

    public function testInformationSchemaRowsAcceptLowercasePostgresqlAliases(): void
    {
        $tables = app_project_table_import_source_tables_from_information_schema_rows([
            [
                'table_name' => 'support_ticket',
                'column_name' => 'updated_at',
                'column_type' => 'timestamp without time zone',
                'is_nullable' => 'YES',
                'column_key' => '',
                'column_default' => null,
                'extra' => '',
                'ordinal_position' => 1,
            ],
        ]);

        self::assertSame(['support_ticket'], array_column($tables, 'name'));
        self::assertSame('support_ticket', $tables[0]['physical_name']);
        self::assertSame('SupportTicket', $tables[0]['logical_name']);
        self::assertSame('SupportTicket', $tables[0]['generated_name']);
        self::assertSame('updated_at', $tables[0]['columns'][0]['physical_name']);
        self::assertSame('UpdatedAt', $tables[0]['columns'][0]['logical_name']);
        self::assertSame('updatedAt', $tables[0]['columns'][0]['generated_name']);
        self::assertSame('timestamp without time zone', $tables[0]['columns'][0]['datatype']);
        self::assertSame('YES', $tables[0]['columns'][0]['is_null']);
        self::assertSame(1, $tables[0]['columns'][0]['column_list_order']);
    }

    public function testMysqlConstraintRowsProducePortableCompositeKeysAndForeignKeys(): void
    {
        $constraints = app_project_table_import_source_constraints_from_mysql_rows(
            [
                ['TABLE_NAME' => 'app_users', 'CONSTRAINT_NAME' => 'PRIMARY', 'CONSTRAINT_TYPE' => 'PRIMARY KEY', 'COLUMN_NAME' => 'app_user_id', 'ORDINAL_POSITION' => 1],
                ['TABLE_NAME' => 'external_identities', 'CONSTRAINT_NAME' => 'uq_issuer_subject', 'CONSTRAINT_TYPE' => 'UNIQUE', 'COLUMN_NAME' => 'issuer', 'ORDINAL_POSITION' => 1],
                ['TABLE_NAME' => 'external_identities', 'CONSTRAINT_NAME' => 'uq_issuer_subject', 'CONSTRAINT_TYPE' => 'UNIQUE', 'COLUMN_NAME' => 'subject', 'ORDINAL_POSITION' => 2],
            ],
            [
                ['TABLE_NAME' => 'external_identities', 'CONSTRAINT_NAME' => 'fk_identity_user', 'COLUMN_NAME' => 'app_user_id', 'REFERENCED_TABLE_NAME' => 'app_users', 'REFERENCED_COLUMN_NAME' => 'app_user_id', 'ORDINAL_POSITION' => 1, 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE'],
            ],
        );

        self::assertSame(['primary', 'unique'], array_column($constraints['keys'], 'key_kind'));
        self::assertSame(['issuer', 'subject'], array_column($constraints['keys'][1]['columns'], 'column_name'));
        self::assertSame('live-schema', $constraints['keys'][1]['source_of_truth']);
        self::assertSame('fk_identity_user', $constraints['foreign_keys'][0]['constraint_name']);
        self::assertSame('app_users', $constraints['foreign_keys'][0]['referenced_table_name']);
        self::assertSame('CASCADE', $constraints['foreign_keys'][0]['on_delete_action']);
        self::assertSame('app_user_id', $constraints['foreign_keys'][0]['columns'][0]['referenced_column_name']);
    }

    public function testLiveConstraintsAreLimitedToManagedTablesAndInternalForeignKeys(): void
    {
        $filtered = app_project_table_import_source_constraints_for_managed_tables([
            'keys' => [
                ['table_name' => 'managed_user', 'key_name' => 'PRIMARY'],
                ['table_name' => 'unmanaged_audit', 'key_name' => 'PRIMARY'],
            ],
            'foreign_keys' => [
                ['table_name' => 'managed_profile', 'referenced_table_name' => 'managed_user', 'constraint_name' => 'fk_internal'],
                ['table_name' => 'managed_profile', 'referenced_table_name' => 'unmanaged_audit', 'constraint_name' => 'fk_external'],
            ],
        ], ['managed_user', 'managed_profile']);

        self::assertSame(['PRIMARY'], array_column($filtered['keys'], 'key_name'));
        self::assertSame(['fk_internal'], array_column($filtered['foreign_keys'], 'constraint_name'));
    }

    public function testInformationSchemaRowsExposeSnakeCasePhysicalLogicalAndGeneratedNames(): void
    {
        $tables = app_project_table_import_source_tables_from_information_schema_rows([
            [
                'TABLE_NAME' => 'support_ticket',
                'COLUMN_NAME' => 'updated_at',
                'COLUMN_TYPE' => 'timestamp',
                'IS_NULLABLE' => 'NO',
                'COLUMN_KEY' => '',
                'COLUMN_DEFAULT' => null,
                'EXTRA' => '',
                'ORDINAL_POSITION' => 1,
            ],
        ]);

        self::assertSame('support_ticket', $tables[0]['physical_name']);
        self::assertSame('SupportTicket', $tables[0]['logical_name']);
        self::assertSame('SupportTicket', $tables[0]['generated_name']);
        self::assertSame('updated_at', $tables[0]['columns'][0]['physical_name']);
        self::assertSame('UpdatedAt', $tables[0]['columns'][0]['logical_name']);
        self::assertSame('updatedAt', $tables[0]['columns'][0]['generated_name']);
    }

    public function testImportPlanWarnsAboutUnsafeUnquotedPhysicalNames(): void
    {
        $plan = app_project_table_import_build_plan(
            'SAMPLE',
            'app_schema',
            'live-schema',
            'live schema',
            true,
            [
                $this->table('user', [
                    $this->column('order', 'int', 0, 0, 0, '', 10),
                ]),
                $this->table('support_ticket', [
                    $this->column('updated_at', 'datetime', 0, 0, 0, '', 10),
                ]),
            ],
            [],
        );

        self::assertTrue($plan['ok'], $plan['error']);
        self::assertSame(2, $plan['summary']['unsafe_physical_name_count']);

        $tablesByName = [];
        foreach ($plan['tables'] as $table) {
            $tablesByName[$table['name']] = $table;
        }

        self::assertSame([], $tablesByName['support_ticket']['review']['naming_warnings']);
        self::assertSame(
            ['user', 'order'],
            array_map(
                static fn (array $warning): string => (string) $warning['physical_name'],
                $tablesByName['user']['review']['naming_warnings'],
            ),
        );
    }

    /**
     * @param list<array<string,mixed>> $columns
     * @return array<string,mixed>
     */
    private function table(string $name, array $columns): array
    {
        $columnsByName = [];
        foreach ($columns as $column) {
            $columnsByName[$column['name']] = $column;
        }

        return [
            'name' => $name,
            'columns' => $columns,
            'columns_by_name' => $columnsByName,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function column(
        string $name,
        string $datatype,
        int $isNull,
        int $isKey,
        int $isDefault,
        string $extra,
        int $order,
    ): array {
        return [
            'name' => $name,
            'datatype' => $datatype,
            'is_null' => $isNull,
            'is_key' => $isKey,
            'is_default' => $isDefault,
            'extra' => $extra,
            'column_list_order' => $order,
            'memo' => '',
        ];
    }
}
