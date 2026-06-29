<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/shared/shared_contract_core.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_schema.php';

use PHPUnit\Framework\TestCase;

final class AppLocalSqliteSchemaGeneratorTest extends TestCase
{
    public function testGeneratesAndAppliesSqliteSchemaFromSharedContractManifest(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $result = app_local_sqlite_schema_generate($manifest);

        self::assertTrue($result['ok'], $result['error']);
        self::assertTrue($result['validation']['ok']);
        self::assertSame('shared-contract-manifest-v0', $result['summary']['manifest_version']);
        self::assertSame(1, $result['summary']['table_count']);
        self::assertSame('task', $result['summary']['tables'][0]['table'] ?? '');
        self::assertSame(
            ['id', 'title', 'status', 'sort_order', 'is_pinned', 'published_at', 'note'],
            $result['summary']['tables'][0]['core_fields'] ?? [],
        );
        self::assertSame(['id'], $result['summary']['tables'][0]['key_fields'] ?? []);
        self::assertSame(
            ['local_updated_at', 'last_synced_at', 'sync_status', 'dirty', 'tombstone'],
            $result['summary']['tables'][0]['local_metadata_columns'] ?? [],
        );

        $schemaSql = $result['schema_sql'];
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "__app_local_schema_version"', $schemaSql);
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "task"', $schemaSql);
        self::assertStringContainsString('"status" TEXT NOT NULL DEFAULT \'draft\'', $schemaSql);
        self::assertStringContainsString('"is_pinned" INTEGER NOT NULL DEFAULT 0', $schemaSql);
        self::assertStringContainsString('PRIMARY KEY ("id")', $schemaSql);

        $pdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($pdo, $schemaSql);
        self::assertTrue($apply['ok'], $apply['error']);
        self::assertSame(
            [
                'id',
                'title',
                'status',
                'sort_order',
                'is_pinned',
                'published_at',
                'note',
                'local_updated_at',
                'last_synced_at',
                'sync_status',
                'dirty',
                'tombstone',
            ],
            $apply['tables']['task'] ?? [],
        );
        self::assertContains('idx_task_sync_status_dirty', $apply['indexes']);
    }

    public function testDtoShapedBusinessFieldsRoundTripWithoutLocalMetadataShapeLeak(): void
    {
        $result = app_local_sqlite_schema_generate(app_shared_contract_core_sample02_task_manifest());
        self::assertTrue($result['ok'], $result['error']);

        $pdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($pdo, $result['schema_sql']);
        self::assertTrue($apply['ok'], $apply['error']);

        $pdo->exec(
            "INSERT INTO \"task\" (\"id\", \"title\", \"status\", \"sort_order\", \"is_pinned\", \"published_at\", \"note\", \"dirty\", \"sync_status\") "
            . "VALUES (1001, 'Draft local task', 'draft', 10, 0, NULL, 'saved by App-local schema test', 1, 'dirty')",
        );

        $row = $pdo->query('SELECT * FROM "task" WHERE "id" = 1001')->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);

        $dto = [];
        foreach (['id', 'title', 'status', 'sort_order', 'is_pinned', 'published_at', 'note'] as $fieldName) {
            $dto[$fieldName] = $row[$fieldName] ?? null;
        }

        self::assertSame([
            'id' => 1001,
            'title' => 'Draft local task',
            'status' => 'draft',
            'sort_order' => 10,
            'is_pinned' => 0,
            'published_at' => null,
            'note' => 'saved by App-local schema test',
        ], $dto);
        self::assertSame(1, $row['dirty']);
        self::assertSame('dirty', $row['sync_status']);
        self::assertSame(0, $row['tombstone']);
        self::assertArrayHasKey('local_updated_at', $row);
        self::assertArrayHasKey('last_synced_at', $row);
    }

    public function testRejectsManifestWhenBusinessFieldCollidesWithLocalMetadataColumn(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $manifest['contracts'][0]['fields'][1]['physical_name'] = 'local_updated_at';

        $result = app_local_sqlite_schema_generate($manifest);

        self::assertFalse($result['ok']);
        self::assertFalse($result['validation']['ok']);
        self::assertContains(
            'business field collides with reserved local metadata column: local_updated_at',
            $result['validation']['errors'],
        );
    }
}
