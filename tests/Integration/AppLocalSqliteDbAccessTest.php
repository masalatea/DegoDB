<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/shared/shared_contract_core.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_schema.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_dbaccess.php';

use PHPUnit\Framework\TestCase;

final class AppLocalSqliteDbAccessTest extends TestCase
{
    public function testSavesAndReadsDtoShapeThroughSharedContractMapping(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $pdo = $this->createLocalPdo($manifest);
        $dto = [
            'id' => 1001,
            'title' => 'Draft local task',
            'status' => 'draft',
            'sortOrder' => 10,
            'isPinned' => false,
            'publishedAt' => null,
            'note' => 'saved by App-local DBAccess test',
        ];

        $save = app_local_sqlite_dbaccess_save_dto($pdo, $manifest, 'task', $dto);
        self::assertTrue($save['ok'], $save['error']);
        self::assertSame(['id' => 1001], $save['key']);
        self::assertSame([
            'dirty' => 1,
            'sync_status' => 'dirty',
            'tombstone' => 0,
            'last_synced_at' => null,
        ], $save['local_metadata']);

        $read = app_local_sqlite_dbaccess_read_dto($pdo, $manifest, 'task', ['id' => 1001]);
        self::assertTrue($read['ok'], $read['error']);
        self::assertSame($dto, $read['dto']);
        self::assertSame(1, $read['local_metadata']['dirty']);
        self::assertSame('dirty', $read['local_metadata']['sync_status']);
        self::assertSame(0, $read['local_metadata']['tombstone']);
        self::assertNotSame('', $read['local_metadata']['local_updated_at']);
        self::assertNull($read['local_metadata']['last_synced_at']);
    }

    public function testUpsertsExistingDtoAndKeepsDtoShapeSeparateFromLocalMetadata(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $pdo = $this->createLocalPdo($manifest);

        $first = [
            'id' => 1002,
            'title' => 'Original local task',
            'status' => 'draft',
            'sortOrder' => 0,
            'isPinned' => false,
            'publishedAt' => null,
            'note' => null,
        ];
        $second = [
            'id' => 1002,
            'title' => 'Updated local task',
            'status' => 'published',
            'sortOrder' => 20,
            'isPinned' => true,
            'publishedAt' => '2026-06-29 15:00:00',
            'note' => 'updated',
        ];

        self::assertTrue(app_local_sqlite_dbaccess_save_dto($pdo, $manifest, 'task', $first)['ok']);
        $save = app_local_sqlite_dbaccess_save_dto($pdo, $manifest, 'task', $second, [
            'dirty' => false,
            'sync_status' => 'clean',
            'last_synced_at' => '2026-06-29 15:01:00',
        ]);
        self::assertTrue($save['ok'], $save['error']);

        $read = app_local_sqlite_dbaccess_read_dto($pdo, $manifest, 'task', ['id' => 1002]);
        self::assertTrue($read['ok'], $read['error']);
        self::assertSame($second, $read['dto']);
        self::assertSame([
            'local_updated_at',
            'last_synced_at',
            'sync_status',
            'dirty',
            'tombstone',
        ], array_keys($read['local_metadata']));
        self::assertSame(0, $read['local_metadata']['dirty']);
        self::assertSame('clean', $read['local_metadata']['sync_status']);
        self::assertSame('2026-06-29 15:01:00', $read['local_metadata']['last_synced_at']);
    }

    public function testFailsClosedWhenDtoIsMissingContractField(): void
    {
        $manifest = app_shared_contract_core_sample02_task_manifest();
        $pdo = $this->createLocalPdo($manifest);
        $dto = [
            'id' => 1003,
            'title' => 'Missing status task',
        ];

        $save = app_local_sqlite_dbaccess_save_dto($pdo, $manifest, 'task', $dto);

        self::assertFalse($save['ok']);
        self::assertSame('DTO is missing field: status', $save['error']);
    }

    private function createLocalPdo(array $manifest): PDO
    {
        $schema = app_local_sqlite_schema_generate($manifest);
        self::assertTrue($schema['ok'], $schema['error']);

        $pdo = new PDO('sqlite::memory:');
        $apply = app_local_sqlite_schema_apply_to_pdo($pdo, $schema['schema_sql']);
        self::assertTrue($apply['ok'], $apply['error']);

        return $pdo;
    }
}
