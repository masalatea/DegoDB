<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_import.php';

final class SqliteMysqlImportTest extends TestCase
{
    public function testRequiresApprovalBeforeDriverOrMutation(): void
    {
        $result = app_sqlite_mysql_import_chunk(new PDO('sqlite::memory:'), $this->manifest(), $this->chunk(), [], false);
        self::assertFalse($result['ok']);
        self::assertSame('explicit_approval_required', $result['error']);
        self::assertFalse($result['mutation_performed']);
    }

    public function testDecodesJsonBlobAndRejectsBrokenEnvelope(): void
    {
        self::assertSame('{"a":1}', app_sqlite_mysql_import_decode_value(['encoding' => 'json', 'value' => ['a' => 1]], 'JSON'));
        self::assertSame("\0A", app_sqlite_mysql_import_decode_value(['encoding' => 'base64', 'byte_length' => 2, 'value' => 'AEE='], 'LONGBLOB'));
        $this->expectException(RuntimeException::class);
        app_sqlite_mysql_import_decode_value(['encoding' => 'base64', 'byte_length' => 9, 'value' => 'AEE='], 'LONGBLOB');
    }

    public function testLiveChunkCommitRollbackAndCheckpointRetry(): void
    {
        $database = trim((string) getenv('PROMOTION_MYSQL_TEST_DB'));
        if ($database === '') self::markTestSkipped('dedicated promotion MySQL schema is not configured');
        self::assertMatchesRegularExpression('/^mtool_promotion_test_[a-z0-9_]+$/', $database);
        $pdo = new PDO('mysql:host=' . getenv('APP_LAB_DB_HOST') . ';port=' . (getenv('APP_LAB_DB_PORT') ?: '3306') . ';dbname=' . $database . ';charset=utf8mb4', (string) getenv('APP_LAB_DB_USER'), (string) getenv('APP_LAB_DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE TABLE `record` (`id` BIGINT NOT NULL PRIMARY KEY, `payload` JSON NOT NULL, `bytes` LONGBLOB NULL) ENGINE=InnoDB');
        try {
            $first = app_sqlite_mysql_import_chunk($pdo, $this->manifest(), $this->chunk(), [], true);
            self::assertTrue($first['ok'], $first['error']);
            self::assertSame('chunk_committed', $first['stage']);
            self::assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM record')->fetchColumn());
            $retry = app_sqlite_mysql_import_chunk($pdo, $this->manifest(), $this->chunk(), $first['checkpoint'], true);
            self::assertSame('already_committed', $retry['stage']);
            self::assertFalse($retry['mutation_performed']);
            $duplicateWithoutCheckpoint = app_sqlite_mysql_import_chunk($pdo, $this->manifest(), $this->chunk(), [], true);
            self::assertFalse($duplicateWithoutCheckpoint['ok']);
            self::assertSame('chunk_rolled_back', $duplicateWithoutCheckpoint['stage']);
            self::assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM record')->fetchColumn());
        } finally { $pdo->exec('DROP TABLE IF EXISTS `record`'); }
    }

    private function chunk(): array
    {
        $rows = [['id' => '2', 'payload' => ['encoding' => 'json', 'value' => ['a' => 1]], 'bytes' => ['encoding' => 'base64', 'byte_length' => 2, 'value' => 'AEE=']], ['id' => '9', 'payload' => ['encoding' => 'json', 'value' => ['b' => 2]], 'bytes' => null]];
        return ['export_version' => APP_SQLITE_MYSQL_EXPORT_VERSION, 'table' => 'record', 'chunk_index' => 0, 'row_count' => 2, 'rows_sha256' => hash('sha256', json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)), 'resume_after_primary_key' => ['id' => '9'], 'rows' => $rows];
    }

    private function manifest(): array
    {
        return ['manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION, 'ok' => true, 'stage' => 'preflight', 'mutation_performed' => false, 'source' => ['driver' => 'sqlite', 'identity' => 'fixture', 'snapshot_sha256' => str_repeat('a', 64)], 'target' => ['driver' => 'mysql', 'identity' => 'target', 'must_be_empty' => true], 'canonical_sha256' => str_repeat('b', 64), 'blockers' => [], 'warnings' => [], 'required_approvals' => [], 'required_verification' => [], 'non_goals' => [], 'tables' => [['name' => 'record', 'row_count' => 2, 'primary_key' => ['id'], 'keys' => [], 'foreign_keys' => [], 'columns' => [['name' => 'id', 'target_type' => 'BIGINT'], ['name' => 'payload', 'target_type' => 'JSON'], ['name' => 'bytes', 'target_type' => 'LONGBLOB']]]]];
    }
}
