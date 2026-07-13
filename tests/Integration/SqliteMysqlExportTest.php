<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_export.php';

final class SqliteMysqlExportTest extends TestCase
{
    public function testExportsStableChunksWithLosslessEncodings(): void
    {
        $pdo = $this->sqlite();
        $manifest = $this->manifest();
        $first = app_sqlite_mysql_export($pdo, $manifest, 1);
        $second = app_sqlite_mysql_export($pdo, $manifest, 1);
        self::assertTrue($first['ok'], implode(',', $first['errors']));
        self::assertSame($first, $second);
        self::assertFalse($first['mutation_performed']);
        self::assertSame([1, 2], array_column($first['tables'], 'chunk_count'));
        self::assertSame('3', $first['chunks'][1]['rows'][0]['id']);
        self::assertSame(['encoding' => 'json', 'value' => ['a' => 1, 'z' => 2]], $first['chunks'][2]['rows'][0]['payload']);
        self::assertSame(['encoding' => 'base64', 'byte_length' => 3, 'value' => 'AEEB'], $first['chunks'][2]['rows'][0]['bytes']);
        self::assertSame(['id' => '9'], $first['chunks'][2]['resume_after_primary_key']);
    }

    public function testConsumerReceivesChunksWithoutRetainingRowsInResult(): void
    {
        $captured = [];
        $result = app_sqlite_mysql_export($this->sqlite(), $this->manifest(), 2, static function (array $chunk) use (&$captured): void { $captured[] = $chunk; });
        self::assertTrue($result['ok']);
        self::assertSame([], $result['chunks']);
        self::assertCount(2, $captured);
    }

    public function testResumesAfterPrimaryKeyCursorAndStillChecksFullSourceCount(): void
    {
        $result = app_sqlite_mysql_export($this->sqlite(), $this->manifest(), 1, null, ['record' => ['id' => '3']]);
        self::assertTrue($result['ok'], implode(',', $result['errors']));
        self::assertSame([1, 1], array_column($result['tables'], 'exported_row_count'));
        self::assertFalse($result['tables'][0]['resumed']);
        self::assertTrue($result['tables'][1]['resumed']);
        self::assertSame('9', $result['chunks'][1]['rows'][0]['id']);
    }

    public function testInvalidJsonAndRowCountMismatchFailClosed(): void
    {
        $pdo = $this->sqlite();
        $pdo->exec("UPDATE record SET payload = '{bad' WHERE id = 9");
        $invalid = app_sqlite_mysql_export($pdo, $this->manifest(), 2);
        self::assertFalse($invalid['ok']);
        self::assertContains('json_conversion_failed:record.payload', $invalid['errors']);

        $pdo = $this->sqlite();
        $manifest = $this->manifest();
        $manifest['tables'][1]['row_count'] = 3;
        $mismatch = app_sqlite_mysql_export($pdo, $manifest, 2);
        self::assertFalse($mismatch['ok']);
        self::assertStringContainsString('source_row_count_mismatch:record', $mismatch['errors'][0]);
    }

    private function sqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE parent (id INTEGER PRIMARY KEY, code TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE record (id INTEGER PRIMARY KEY, parent_id INTEGER NOT NULL, amount TEXT NOT NULL, payload TEXT NOT NULL, bytes BLOB, recorded_at TEXT)');
        $pdo->exec("INSERT INTO parent VALUES (2, 'P2')");
        $insert = $pdo->prepare('INSERT INTO record VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute([9, 2, '10.50', '{"z":2,"a":1}', "\0A\1", '2026-07-13 01:02:03.123456']);
        $insert->execute([3, 2, '0.00', '{"a":0}', null, null]);
        return $pdo;
    }

    /** @return array<string,mixed> */
    private function manifest(): array
    {
        return [
            'manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION, 'ok' => true, 'stage' => 'preflight', 'mutation_performed' => false,
            'source' => ['driver' => 'sqlite', 'identity' => 'fixture.sqlite', 'snapshot_sha256' => str_repeat('a', 64)],
            'target' => ['driver' => 'mysql', 'identity' => 'target', 'must_be_empty' => true], 'canonical_sha256' => str_repeat('b', 64),
            'blockers' => [], 'warnings' => [], 'required_approvals' => [], 'required_verification' => [], 'non_goals' => [],
            'tables' => [
                ['name' => 'parent', 'row_count' => 1, 'primary_key' => ['id'], 'keys' => [], 'foreign_keys' => [], 'columns' => [
                    ['name' => 'id', 'target_type' => 'BIGINT'], ['name' => 'code', 'target_type' => 'VARCHAR(40)'],
                ]],
                ['name' => 'record', 'row_count' => 2, 'primary_key' => ['id'], 'keys' => [], 'foreign_keys' => [], 'columns' => [
                    ['name' => 'id', 'target_type' => 'BIGINT'], ['name' => 'parent_id', 'target_type' => 'BIGINT'],
                    ['name' => 'amount', 'target_type' => 'DECIMAL(12,2)'], ['name' => 'payload', 'target_type' => 'JSON'],
                    ['name' => 'bytes', 'target_type' => 'LONGBLOB'], ['name' => 'recorded_at', 'target_type' => 'DATETIME(6)'],
                ]],
            ],
        ];
    }
}
