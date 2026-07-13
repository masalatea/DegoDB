<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_verification.php';

final class SqliteMysqlVerificationTest extends TestCase
{
    public function testOnlyEveryRequiredPassedIsCutoverReadyAndDeterministic(): void
    {
        $checks = array_map(static fn (string $key): array => ['check_key' => $key, 'status' => 'passed', 'source' => ['digest' => $key], 'target' => ['digest' => $key]], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED);
        $context = ['promotion_manifest_sha256' => str_repeat('a', 64), 'target_schema_sha256' => str_repeat('b', 64), 'import_checkpoint_sha256' => str_repeat('c', 64)];
        $first = app_sqlite_mysql_verification_artifact($context, $checks);
        self::assertTrue($first['cutover_ready']);
        self::assertFalse($first['mutation_performed']);
        self::assertSame($first, app_sqlite_mysql_verification_artifact($context, array_reverse($checks)));
    }

    /** @dataProvider blockingStatusProvider */
    public function testEveryNonPassedRequiredStatusBlocks(string $status): void
    {
        $checks = array_map(static fn (string $key): array => ['check_key' => $key, 'status' => $key === 'row_count' ? $status : 'passed'], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED);
        $artifact = app_sqlite_mysql_verification_artifact(['promotion_manifest_sha256' => str_repeat('a', 64), 'target_schema_sha256' => str_repeat('b', 64), 'import_checkpoint_sha256' => str_repeat('c', 64)], $checks);
        self::assertFalse($artifact['cutover_ready']);
        self::assertContains($status, array_column($artifact['blockers'], 'status'));
    }

    public function testMissingCheckAndInvalidDigestBlock(): void
    {
        $artifact = app_sqlite_mysql_verification_artifact([], []);
        self::assertFalse($artifact['cutover_ready']);
        self::assertContains('required_check_missing', array_column($artifact['blockers'], 'code'));
        self::assertContains('invalid_context_digest', array_column($artifact['blockers'], 'code'));
    }

    public function testCollectsDeterministicReadOnlySqliteCoreEvidence(): void
    {
        $pdo = $this->sqlite();
        $first = app_sqlite_mysql_verification_collect_database($pdo, $this->manifest());
        $second = app_sqlite_mysql_verification_collect_database($pdo, $this->manifest());

        self::assertTrue($first['ok'], implode(',', $first['errors']));
        self::assertSame($first, $second);
        self::assertFalse($first['mutation_performed']);
        self::assertSame('sqlite', $first['driver']);
        self::assertSame(['parent', 'record'], array_column($first['tables'], 'name'));
        self::assertSame([1, 2], array_column($first['tables'], 'row_count'));
        self::assertSame([true, true], array_column($first['tables'], 'stable_count'));
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['tables'][1]['primary_key_sha256']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['tables'][1]['rows_sha256']);
        self::assertSame(['payload'], $first['tables'][1]['json_values']['columns']);
        self::assertSame(['bytes'], $first['tables'][1]['blob_values']['columns']);
        self::assertSame(['recorded_at'], $first['tables'][1]['timestamp_values']['columns']);
        self::assertSame('9', $first['tables'][1]['next_id']['max_primary_key']);
        self::assertSame('10', $first['tables'][1]['next_id']['required_next_id']);
        self::assertSame('none', $first['tables'][1]['next_id']['db_sequence_owner']);
        self::assertSame('P2', $pdo->query('SELECT code FROM parent WHERE id = 2')->fetchColumn());
    }

    public function testCoreComparisonPassesForEquivalentEvidenceAndDetectsPkOrValueDrift(): void
    {
        $source = app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest());
        $target = $source;
        $target['driver'] = 'mysql';

        $passed = app_sqlite_mysql_verification_compare_core($source, $target);
        self::assertSame(['passed', 'passed', 'passed'], array_column($passed, 'status'));

        $pkDrift = $target;
        $pkDrift['tables'][1]['primary_key_sha256'] = str_repeat('0', 64);
        $pkChecks = app_sqlite_mysql_verification_compare_core($source, $pkDrift);
        self::assertSame('passed', $pkChecks[0]['status']);
        self::assertSame('failed', $pkChecks[1]['status']);
        self::assertSame('primary_key_set_mismatch', $pkChecks[1]['failure_code']);

        $valueDrift = $target;
        $valueDrift['tables'][1]['rows_sha256'] = str_repeat('1', 64);
        $valueChecks = app_sqlite_mysql_verification_compare_core($source, $valueDrift);
        self::assertSame('passed', $valueChecks[1]['status']);
        self::assertSame('failed', $valueChecks[2]['status']);
        self::assertSame('row_values_mismatch', $valueChecks[2]['failure_code']);
    }

    public function testSchemaComparisonPassesForExpectedNullabilityKeysAndForeignKeys(): void
    {
        $source = app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest());
        $target = $source;
        $target['driver'] = 'mysql';

        $checks = app_sqlite_mysql_verification_compare_schema($source, $target, $this->manifest());
        self::assertSame(['nullability', 'unique_keys', 'foreign_keys'], array_column($checks, 'check_key'));
        self::assertSame(['passed', 'passed', 'passed'], array_column($checks, 'status'));

        $nullabilityDrift = $target;
        $nullabilityDrift['tables'][1]['nullability'][0]['nullable'] = true;
        self::assertSame('failed', app_sqlite_mysql_verification_compare_schema($source, $nullabilityDrift, $this->manifest())[0]['status']);

        $uniqueDrift = $target;
        array_pop($uniqueDrift['tables'][1]['unique_keys']);
        self::assertSame('unique_keys_mismatch', app_sqlite_mysql_verification_compare_schema($source, $uniqueDrift, $this->manifest())[1]['failure_code']);

        $foreignKeyDrift = $target;
        $foreignKeyDrift['tables'][1]['foreign_key_violation_count'] = 1;
        self::assertSame('foreign_keys_mismatch', app_sqlite_mysql_verification_compare_schema($source, $foreignKeyDrift, $this->manifest())[2]['failure_code']);
    }

    public function testValueClassComparisonPassesAndDetectsJsonBlobOrTimestampDrift(): void
    {
        $source = app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest());
        $target = $source;
        $target['driver'] = 'mysql';

        $checks = app_sqlite_mysql_verification_compare_value_classes($source, $target);
        self::assertSame(['json_values', 'blob_values', 'timestamp_values'], array_column($checks, 'check_key'));
        self::assertSame(['passed', 'passed', 'passed'], array_column($checks, 'status'));

        $jsonDrift = $target;
        $jsonDrift['tables'][1]['json_values']['digest_sha256'] = str_repeat('2', 64);
        self::assertSame('json_values_mismatch', app_sqlite_mysql_verification_compare_value_classes($source, $jsonDrift)[0]['failure_code']);

        $blobDrift = $target;
        $blobDrift['tables'][1]['blob_values']['digest_sha256'] = str_repeat('3', 64);
        self::assertSame('blob_values_mismatch', app_sqlite_mysql_verification_compare_value_classes($source, $blobDrift)[1]['failure_code']);

        $timestampDrift = $target;
        $timestampDrift['tables'][1]['timestamp_values']['digest_sha256'] = str_repeat('4', 64);
        self::assertSame('timestamp_values_mismatch', app_sqlite_mysql_verification_compare_value_classes($source, $timestampDrift)[2]['failure_code']);
    }

    public function testNextIdComparisonPassesAndDetectsSequenceDriftOrUnsafeSequence(): void
    {
        $source = app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest());
        $target = $source;
        $target['driver'] = 'mysql';

        $checks = app_sqlite_mysql_verification_compare_next_ids($source, $target);
        self::assertSame('next_ids', $checks[0]['check_key']);
        self::assertSame('passed', $checks[0]['status']);

        $maxDrift = $target;
        $maxDrift['tables'][1]['next_id']['max_primary_key'] = '8';
        self::assertSame('next_ids_mismatch', app_sqlite_mysql_verification_compare_next_ids($source, $maxDrift)[0]['failure_code']);

        $unsafeSequence = $target;
        $unsafeSequence['tables'][1]['next_id']['db_sequence_owner'] = 'mysql_auto_increment';
        $unsafeSequence['tables'][1]['next_id']['db_next_id'] = '9';
        $unsafeSequence['tables'][1]['next_id']['sequence_safe'] = false;
        self::assertSame('failed', app_sqlite_mysql_verification_compare_next_ids($source, $unsafeSequence)[0]['status']);
    }

    public function testNextIdDecimalHelpersHandleLargeIntegerStrings(): void
    {
        self::assertSame('100000000000000000000', app_sqlite_mysql_verification_decimal_increment('99999999999999999999'));
        self::assertSame('0', app_sqlite_mysql_verification_decimal_increment('-1'));
        self::assertGreaterThan(0, app_sqlite_mysql_verification_decimal_compare('100000000000000000000', '99999999999999999999'));
    }

    public function testBuildsCutoverReadyArtifactFromAllRealCollectorChecksAndDbaccessSmoke(): void
    {
        $source = app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest());
        $target = $source;
        $target['driver'] = 'mysql';
        $smoke = app_sqlite_mysql_verification_dbaccess_smoke_artifact([
            ['name' => 'record_find_by_id', 'rows' => [['id' => '9', 'parent_id' => '2']]],
            ['name' => 'parent_list', 'rows' => [['id' => '2']]],
        ]);

        $checks = app_sqlite_mysql_verification_checks($source, $target, $this->manifest(), $smoke);
        self::assertSame(APP_SQLITE_MYSQL_VERIFICATION_REQUIRED, array_column($checks, 'check_key'));
        self::assertSame(array_fill(0, count(APP_SQLITE_MYSQL_VERIFICATION_REQUIRED), 'passed'), array_column($checks, 'status'));

        $artifact = app_sqlite_mysql_verification_build_artifact($this->context(), $source, $target, $this->manifest(), $smoke);
        self::assertTrue($artifact['cutover_ready'], json_encode($artifact['blockers']));
        self::assertFalse($artifact['mutation_performed']);
    }

    public function testDbaccessSmokeFailsClosedForMutationFailureOrSecrets(): void
    {
        $good = app_sqlite_mysql_verification_dbaccess_smoke_artifact([['name' => 'record_find_by_id', 'rows' => [['id' => '9']]]]);
        self::assertSame('passed', app_sqlite_mysql_verification_dbaccess_smoke_check($good)['status']);

        $failed = $good;
        $failed['operations'][0]['status'] = 'failed';
        self::assertSame('dbaccess_smoke_failed', app_sqlite_mysql_verification_dbaccess_smoke_check($failed)['failure_code']);

        $mutating = $good;
        $mutating['mutation_performed'] = true;
        self::assertSame('failed', app_sqlite_mysql_verification_dbaccess_smoke_check($mutating)['status']);

        $secret = $good;
        $secret['password'] = 'do-not-copy';
        self::assertSame('failed', app_sqlite_mysql_verification_dbaccess_smoke_check($secret)['status']);
    }

    public function testLiveMysqlCollectorMatchesEquivalentSqliteSource(): void
    {
        $database = trim((string) getenv('PROMOTION_MYSQL_TEST_DB'));
        if ($database === '') self::markTestSkipped('dedicated promotion MySQL schema is not configured');
        self::assertMatchesRegularExpression('/^mtool_promotion_test_[a-z0-9_]+$/', $database);
        $mysql = new PDO('mysql:host=' . getenv('APP_LAB_DB_HOST') . ';port=' . (getenv('APP_LAB_DB_PORT') ?: '3306') . ';dbname=' . $database . ';charset=utf8mb4', (string) getenv('APP_LAB_DB_USER'), (string) getenv('APP_LAB_DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $mysql->exec('CREATE TABLE `parent` (`id` BIGINT NOT NULL PRIMARY KEY, `code` VARCHAR(40) NOT NULL) ENGINE=InnoDB');
        $mysql->exec('CREATE TABLE `record` (`id` BIGINT NOT NULL PRIMARY KEY, `parent_id` BIGINT NOT NULL, `amount` DECIMAL(12,2) NOT NULL, `payload` JSON NOT NULL, `bytes` LONGBLOB NULL, `recorded_at` DATETIME(6) NULL, CONSTRAINT `uq_record_parent_amount` UNIQUE (`parent_id`, `amount`), CONSTRAINT `fk_record_parent` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`)) ENGINE=InnoDB');
        try {
            $mysql->exec("INSERT INTO `parent` VALUES (2, 'P2')");
            $insert = $mysql->prepare('INSERT INTO `record` VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([9, 2, '10.50', '{"z":2,"a":1}', "\0A\1", '2026-07-13 01:02:03.123456']);
            $insert->execute([3, 2, '0.00', '{"a":0}', null, null]);

            $checks = app_sqlite_mysql_verification_compare_core(
                app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest()),
                app_sqlite_mysql_verification_collect_database($mysql, $this->manifest()),
            );
            self::assertSame(['passed', 'passed', 'passed'], array_column($checks, 'status'));
            $schemaChecks = app_sqlite_mysql_verification_compare_schema(
                app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest()),
                app_sqlite_mysql_verification_collect_database($mysql, $this->manifest()),
                $this->manifest(),
            );
            self::assertSame(['passed', 'passed', 'passed'], array_column($schemaChecks, 'status'));
            $valueChecks = app_sqlite_mysql_verification_compare_value_classes(
                app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest()),
                app_sqlite_mysql_verification_collect_database($mysql, $this->manifest()),
            );
            self::assertSame(['passed', 'passed', 'passed'], array_column($valueChecks, 'status'));
            $nextIdChecks = app_sqlite_mysql_verification_compare_next_ids(
                app_sqlite_mysql_verification_collect_database($this->sqlite(), $this->manifest()),
                app_sqlite_mysql_verification_collect_database($mysql, $this->manifest()),
            );
            self::assertSame(['passed'], array_column($nextIdChecks, 'status'));
        } finally {
            $mysql->exec('DROP TABLE IF EXISTS `record`');
            $mysql->exec('DROP TABLE IF EXISTS `parent`');
        }
    }

    public static function blockingStatusProvider(): array { return [['failed'], ['missing'], ['skipped'], ['unsupported'], ['warning']]; }

    /** @return array<string,string> */
    private function context(): array
    {
        return ['promotion_manifest_sha256' => str_repeat('a', 64), 'target_schema_sha256' => str_repeat('b', 64), 'import_checkpoint_sha256' => str_repeat('c', 64)];
    }

    private function sqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('CREATE TABLE parent (id INTEGER PRIMARY KEY, code TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE record (id INTEGER PRIMARY KEY, parent_id INTEGER NOT NULL, amount TEXT NOT NULL, payload TEXT NOT NULL, bytes BLOB, recorded_at TEXT, UNIQUE(parent_id, amount), FOREIGN KEY(parent_id) REFERENCES parent(id))');
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
                ['name' => 'parent', 'row_count' => 1, 'primary_key' => ['id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_parent', 'columns' => ['id']]], 'foreign_keys' => [], 'columns' => [
                    ['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false], ['name' => 'code', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                ]],
                ['name' => 'record', 'row_count' => 2, 'primary_key' => ['id'], 'keys' => [
                    ['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']],
                    ['kind' => 'unique', 'name' => 'uq_record_parent_amount', 'columns' => ['parent_id', 'amount']],
                ], 'foreign_keys' => [['name' => 'fk_record_parent', 'columns' => ['parent_id'], 'referenced_table' => 'parent', 'referenced_columns' => ['id']]], 'columns' => [
                    ['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false], ['name' => 'parent_id', 'target_type' => 'BIGINT', 'nullable' => false],
                    ['name' => 'amount', 'target_type' => 'DECIMAL(12,2)', 'nullable' => false], ['name' => 'payload', 'target_type' => 'JSON', 'nullable' => false],
                    ['name' => 'bytes', 'target_type' => 'LONGBLOB', 'nullable' => true], ['name' => 'recorded_at', 'target_type' => 'DATETIME(6)', 'nullable' => true],
                ]],
            ],
        ];
    }
}
