<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_promotion_rehearsal.php';

final class SqliteMysqlPromotionRehearsalTest extends TestCase
{
    public function testBuildsDeterministicDigestChainedRehearsalPackage(): void
    {
        $manifest = $this->manifest();
        $schema = app_sqlite_mysql_target_schema_plan($manifest);
        $export = app_sqlite_mysql_export($this->sqlite(), $manifest, 1);
        $import = $this->importResult();
        $verification = $this->verification($manifest, $schema, $import);
        $cutover = $this->cutoverPlan($manifest, $verification);
        $operator = app_sqlite_mysql_cutover_operator_package($cutover, $this->switchPackage(), $this->operatorRehearsal(), APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);

        $first = app_sqlite_mysql_promotion_rehearsal_package($manifest, $schema, $export, $import, $verification, $cutover, $operator);
        $second = app_sqlite_mysql_promotion_rehearsal_package($manifest, $schema, $export, $import, $verification, $cutover, $operator);

        self::assertTrue($first['rehearsal_ready'], implode(',', $first['errors']));
        self::assertSame('promotion_rehearsal_ready', $first['stage']);
        self::assertFalse($first['mutation_performed']);
        self::assertSame(app_sqlite_mysql_promotion_digest($manifest), $first['promotion_manifest_sha256']);
        self::assertSame($schema['schema_sha256'], $first['target_schema_sha256']);
        self::assertSame(2, $first['export_summary']['table_count']);
        self::assertSame(3, $first['export_summary']['chunk_count']);
        self::assertSame(3, $first['export_summary']['row_count']);
        self::assertSame($cutover['cutover_contract_sha256'], $first['cutover_contract_sha256']);
        self::assertSame($operator['operator_package_sha256'], $first['operator_package_sha256']);
        self::assertTrue($first['requires_explicit_cutover']);
        self::assertSame($first, $second);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['rehearsal_package_sha256']);
    }

    public function testFailsClosedOnBrokenDigestChainAndUnsafeArtifacts(): void
    {
        $manifest = $this->manifest();
        $schema = app_sqlite_mysql_target_schema_plan($manifest);
        $export = app_sqlite_mysql_export($this->sqlite(), $manifest, 2);
        $export['chunks'][0]['rows'][0]['code'] = 'tampered';
        $import = $this->importResult();
        $verification = $this->verification($manifest, $schema, $import);
        $verification['context']['target_schema_sha256'] = str_repeat('f', 64);
        $cutover = $this->cutoverPlan($manifest, $verification);
        $operator = app_sqlite_mysql_cutover_operator_package($cutover, $this->switchPackage(), $this->operatorRehearsal(), APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);
        $operator['operator_package_ready'] = false;
        $operator['password'] = 'do-not-copy';

        $package = app_sqlite_mysql_promotion_rehearsal_package($manifest, $schema, $export, $import, $verification, $cutover, $operator);

        self::assertFalse($package['rehearsal_ready']);
        self::assertSame('promotion_rehearsal_blocked', $package['stage']);
        self::assertContains('secret_in_rehearsal_package', $package['errors']);
        self::assertContains('export_chunk_contract_invalid', $package['errors']);
        self::assertContains('verification_schema_digest_mismatch', $package['errors']);
        self::assertContains('operator_package_not_ready', $package['errors']);
        self::assertStringNotContainsString('do-not-copy', json_encode($package, JSON_THROW_ON_ERROR));
    }

    /** @return array<string,mixed> */
    private function manifest(): array
    {
        return [
            'manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION,
            'ok' => true,
            'stage' => 'preflight',
            'mutation_performed' => false,
            'source' => ['driver' => 'sqlite', 'identity' => 'sample-rehearsal.sqlite', 'snapshot_sha256' => str_repeat('a', 64)],
            'target' => ['driver' => 'mysql', 'identity' => 'sample-rehearsal-mysql', 'must_be_empty' => true],
            'canonical_sha256' => str_repeat('b', 64),
            'blockers' => [],
            'warnings' => [],
            'required_approvals' => ['target_schema_prepare', 'data_import', 'cutover'],
            'required_verification' => APP_SQLITE_MYSQL_VERIFICATION_REQUIRED,
            'non_goals' => ['mysql_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover'],
            'tables' => [
                ['name' => 'parent', 'row_count' => 1, 'primary_key' => ['id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_parent', 'columns' => ['id']]], 'foreign_keys' => [], 'columns' => [
                    ['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false],
                    ['name' => 'code', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                ]],
                ['name' => 'record', 'row_count' => 2, 'primary_key' => ['id'], 'keys' => [
                    ['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']],
                    ['kind' => 'unique', 'name' => 'uq_record_parent_amount', 'columns' => ['parent_id', 'amount']],
                ], 'foreign_keys' => [['name' => 'fk_record_parent', 'columns' => ['parent_id'], 'referenced_table' => 'parent', 'referenced_columns' => ['id']]], 'columns' => [
                    ['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false],
                    ['name' => 'parent_id', 'target_type' => 'BIGINT', 'nullable' => false],
                    ['name' => 'amount', 'target_type' => 'DECIMAL(12,2)', 'nullable' => false],
                    ['name' => 'payload', 'target_type' => 'JSON', 'nullable' => false],
                    ['name' => 'bytes', 'target_type' => 'LONGBLOB', 'nullable' => true],
                    ['name' => 'recorded_at', 'target_type' => 'DATETIME(6)', 'nullable' => true],
                ]],
            ],
        ];
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
    private function importResult(): array
    {
        return [
            'ok' => true,
            'stage' => 'chunk_committed',
            'error' => '',
            'checkpoint' => [
                'checkpoint_version' => APP_SQLITE_MYSQL_IMPORT_CHECKPOINT_VERSION,
                'completed' => ['parent:0' => str_repeat('1', 64), 'record:0' => str_repeat('2', 64), 'record:1' => str_repeat('3', 64)],
                'last_table' => 'record',
                'last_chunk_index' => 1,
                'resume_after_primary_key' => ['id' => '9'],
            ],
            'mutation_performed' => true,
        ];
    }

    /** @param array<string,mixed> $manifest @param array<string,mixed> $schema @param array<string,mixed> $import @return array<string,mixed> */
    private function verification(array $manifest, array $schema, array $import): array
    {
        return app_sqlite_mysql_verification_artifact([
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'import_checkpoint_sha256' => app_sqlite_mysql_promotion_digest($import['checkpoint']),
        ], array_map(static fn (string $key): array => ['check_key' => $key, 'status' => 'passed'], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED));
    }

    /** @param array<string,mixed> $manifest @param array<string,mixed> $verification @return array<string,mixed> */
    private function cutoverPlan(array $manifest, array $verification): array
    {
        return app_sqlite_mysql_cutover_plan($manifest, $verification, [
            'freeze_window_id' => 'freeze-20260713T140000Z',
            'writes_frozen' => true,
            'final_source_snapshot_sha256' => str_repeat('4', 64),
            'final_verification_sha256' => str_repeat('5', 64),
            'target_config_ref' => 'config/database/mysql-target',
            'post_cutover_smoke_ref' => 'validation/post-cutover-smoke',
            'post_cutover_smoke_passed' => true,
            'automatic_source_delete' => false,
        ], [
            'retain_source' => true,
            'source_retention_ref' => 'rollback/sqlite/frozen-source',
            'rollback_procedure_ref' => 'runbooks/sqlite-restore',
            'rollback_window_until' => '2026-07-20T12:00:00Z',
            'post_window_source_disposition' => 'archive',
        ], APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS);
    }

    /** @return array<string,mixed> */
    private function switchPackage(): array
    {
        return [
            'package_id' => 'switch-20260713T150000Z',
            'switch_target_driver' => 'mysql',
            'switch_config_ref' => 'config/database/mysql-target',
            'switch_command_ref' => 'runbooks/switch-to-mysql',
            'pre_switch_backup_ref' => 'backups/pre-switch-config',
            'post_switch_smoke_ref' => 'validation/post-switch-smoke',
            'rollback_command_ref' => 'runbooks/rollback-to-sqlite',
            'automatic_apply' => false,
            'source_delete' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function operatorRehearsal(): array
    {
        return [
            'rehearsal_report_ref' => 'validation/cutover-rehearsal',
            'switch_dry_run_passed' => true,
            'rollback_rehearsal_passed' => true,
            'post_switch_smoke_rehearsed' => true,
            'mutation_performed' => false,
        ];
    }
}
