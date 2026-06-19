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
