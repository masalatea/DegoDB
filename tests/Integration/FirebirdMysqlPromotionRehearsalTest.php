<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/firebird_mysql_promotion_rehearsal.php';

final class FirebirdMysqlPromotionRehearsalTest extends TestCase
{
    public function testBuildsTargetSchemaExportAndRehearsalPackage(): void
    {
        $manifest = $this->manifest();
        $schema = app_firebird_mysql_target_schema_plan($manifest);
        self::assertTrue($schema['ok'], implode(',', $schema['errors']));
        self::assertFalse($schema['mutation_performed']);
        self::assertSame(['parent', 'record'], array_column($schema['tables'], 'name'));
        self::assertStringContainsString('CREATE TABLE `parent`', $schema['statements'][0]);
        self::assertStringContainsString('`payload` JSON NOT NULL', $schema['statements'][1]);
        self::assertStringContainsString('`bytes` LONGBLOB NULL', $schema['statements'][1]);
        self::assertStringContainsString('CONSTRAINT `fk_record_parent` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`)', $schema['statements'][1]);

        $export = app_firebird_mysql_export_from_rows($manifest, $this->rows(), 1);
        self::assertTrue($export['ok'], implode(',', $export['errors']));
        self::assertFalse($export['mutation_performed']);
        self::assertSame('export_ready', $export['stage']);
        self::assertCount(3, $export['chunks']);
        self::assertSame(['encoding' => 'json', 'value' => ['a' => 1, 'z' => 2]], $export['chunks'][2]['rows'][0]['payload']);
        self::assertSame(['encoding' => 'base64', 'byte_length' => 3, 'value' => 'AEEB'], $export['chunks'][2]['rows'][0]['bytes']);
        self::assertSame(['id' => '9'], $export['chunks'][2]['resume_after_primary_key']);

        $package = app_firebird_mysql_promotion_rehearsal_package($manifest, $schema, $export);
        self::assertTrue($package['rehearsal_ready'], implode(',', $package['errors']));
        self::assertSame('firebird_mysql_rehearsal_ready', $package['stage']);
        self::assertFalse($package['mutation_performed']);
        self::assertSame(2, $package['export_summary']['table_count']);
        self::assertSame(3, $package['export_summary']['chunk_count']);
        self::assertSame(3, $package['export_summary']['row_count']);
        self::assertTrue($package['requires_explicit_cutover']);
        self::assertContains('firebird_to_sqlite', $package['non_goals']);
    }

    public function testExportFailsClosedOnRowCountAndInvalidJson(): void
    {
        $rows = $this->rows();
        $rows['record'][1]['payload'] = '{bad';
        $invalid = app_firebird_mysql_export_from_rows($this->manifest(), $rows, 2);
        self::assertFalse($invalid['ok']);
        self::assertContains('json_conversion_failed:record.payload', $invalid['errors']);

        $rows = $this->rows();
        array_pop($rows['record']);
        $mismatch = app_firebird_mysql_export_from_rows($this->manifest(), $rows, 2);
        self::assertFalse($mismatch['ok']);
        self::assertStringContainsString('source_row_count_mismatch:record', $mismatch['errors'][0]);
    }

    public function testLiveExportAdapterFailsClosedForNonFirebirdPdo(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $export = app_firebird_mysql_export($pdo, $this->manifest(), 1);

        self::assertFalse($export['ok']);
        self::assertFalse($export['mutation_performed']);
        self::assertContains('firebird_source_required', $export['errors']);
    }

    public function testImportRequiresApprovalBeforeDriverOrMutation(): void
    {
        $export = app_firebird_mysql_export_from_rows($this->manifest(), $this->rows(), 2);
        self::assertTrue($export['ok'], implode(',', $export['errors']));

        $result = app_firebird_mysql_import_chunk(new PDO('sqlite::memory:'), $this->manifest(), $export['chunks'][0], [], false);

        self::assertFalse($result['ok']);
        self::assertSame('approval', $result['stage']);
        self::assertSame('explicit_approval_required', $result['error']);
        self::assertFalse($result['mutation_performed']);
    }

    public function testImportFailsClosedForNonMysqlTargetAfterApproval(): void
    {
        $export = app_firebird_mysql_export_from_rows($this->manifest(), $this->rows(), 2);
        self::assertTrue($export['ok'], implode(',', $export['errors']));

        $result = app_firebird_mysql_import_chunk(new PDO('sqlite::memory:'), $this->manifest(), $export['chunks'][0], [], true);

        self::assertFalse($result['ok']);
        self::assertSame('preflight', $result['stage']);
        self::assertSame('mysql_target_required', $result['error']);
        self::assertFalse($result['mutation_performed']);
    }

    public function testLiveMysqlImportCommitsChunksAndReusesCheckpoint(): void
    {
        $database = trim((string) getenv('PROMOTION_MYSQL_TEST_DB'));
        if ($database === '') self::markTestSkipped('dedicated promotion MySQL schema is not configured');
        self::assertMatchesRegularExpression('/^mtool_promotion_test_[a-z0-9_]+$/', $database);

        $manifest = $this->manifest();
        $schema = app_firebird_mysql_target_schema_plan($manifest);
        self::assertTrue($schema['ok'], implode(',', $schema['errors']));
        $export = app_firebird_mysql_export_from_rows($manifest, $this->rows(), 1);
        self::assertTrue($export['ok'], implode(',', $export['errors']));
        $pdo = new PDO(
            'mysql:host=' . getenv('APP_LAB_DB_HOST') . ';port=' . (getenv('APP_LAB_DB_PORT') ?: '3306') . ';dbname=' . $database . ';charset=utf8mb4',
            (string) getenv('APP_LAB_DB_USER'),
            (string) getenv('APP_LAB_DB_PASSWORD'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        try {
            foreach (array_reverse($manifest['tables']) as $table) {
                $pdo->exec('DROP TABLE IF EXISTS ' . app_sqlite_mysql_target_quote_identifier((string) $table['name']));
            }
            foreach ($schema['statements'] as $statement) {
                $pdo->exec((string) $statement);
            }
            $checkpoint = [];
            foreach ($export['chunks'] as $chunk) {
                $import = app_firebird_mysql_import_chunk($pdo, $manifest, $chunk, $checkpoint, true);
                self::assertTrue($import['ok'], $import['error']);
                $checkpoint = $import['checkpoint'];
            }
            self::assertSame(APP_FIREBIRD_MYSQL_IMPORT_CHECKPOINT_VERSION, $checkpoint['checkpoint_version']);
            self::assertSame(3, count($checkpoint['completed']));
            self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM `parent`')->fetchColumn());
            self::assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM `record`')->fetchColumn());
            self::assertSame('{"a":1,"z":2}', (string) $pdo->query('SELECT JSON_COMPACT(`payload`) FROM `record` WHERE `id` = 9')->fetchColumn());

            $retry = app_firebird_mysql_import_chunk($pdo, $manifest, $export['chunks'][0], $checkpoint, true);
            self::assertSame('already_committed', $retry['stage']);
            self::assertFalse($retry['mutation_performed']);
        } finally {
            $pdo->exec('DROP TABLE IF EXISTS `record`');
            $pdo->exec('DROP TABLE IF EXISTS `parent`');
        }
    }

    public function testBuildsFirebirdMysqlVerificationArtifactFromExportEvidence(): void
    {
        $manifest = $this->manifest();
        $schema = app_firebird_mysql_target_schema_plan($manifest);
        $export = app_firebird_mysql_export_from_rows($manifest, $this->rows(), 1);
        self::assertTrue($export['ok'], implode(',', $export['errors']));

        $source = app_firebird_mysql_verification_source_evidence_from_export($manifest, $export);
        self::assertTrue($source['ok'], implode(',', $source['errors']));
        self::assertSame('firebird', $source['driver']);
        self::assertSame(['parent', 'record'], array_column($source['tables'], 'name'));
        self::assertSame([1, 2], array_column($source['tables'], 'row_count'));
        self::assertSame('10', $source['tables'][1]['next_id']['required_next_id']);

        $target = $source;
        $target['driver'] = 'mysql';
        $smoke = app_sqlite_mysql_verification_dbaccess_smoke_artifact([
            ['name' => 'record_find_by_id', 'rows' => [['id' => '9', 'parent_id' => '2']]],
            ['name' => 'parent_list', 'rows' => [['id' => '2']]],
        ]);
        $artifact = app_firebird_mysql_verification_build_artifact([
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'import_checkpoint_sha256' => str_repeat('c', 64),
        ], $source, $target, $manifest, $smoke);

        self::assertSame(APP_FIREBIRD_MYSQL_VERIFICATION_VERSION, $artifact['verification_version']);
        self::assertTrue($artifact['cutover_ready'], json_encode($artifact['blockers'], JSON_THROW_ON_ERROR));
        self::assertFalse($artifact['mutation_performed']);
        $actualCheckKeys = array_column($artifact['checks'], 'check_key');
        $expectedCheckKeys = APP_SQLITE_MYSQL_VERIFICATION_REQUIRED;
        sort($actualCheckKeys);
        sort($expectedCheckKeys);
        self::assertSame($expectedCheckKeys, $actualCheckKeys);

        $drift = $target;
        $drift['tables'][1]['rows_sha256'] = str_repeat('0', 64);
        $blocked = app_firebird_mysql_verification_build_artifact([
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'import_checkpoint_sha256' => str_repeat('c', 64),
        ], $source, $drift, $manifest, $smoke);
        self::assertFalse($blocked['cutover_ready']);
        self::assertContains('row_values_mismatch', array_column($blocked['blockers'], 'code'));
    }

    public function testResumeWhereSupportsCompoundPrimaryKeys(): void
    {
        [$where, $params] = app_firebird_mysql_export_resume_where(
            ['tenant_id', 'id'],
            ['tenant_id' => 't-1', 'id' => '9'],
            'record',
        );

        self::assertSame('("tenant_id" > ? OR ("tenant_id" = ? AND "id" > ?))', $where);
        self::assertSame(['t-1', 't-1', '9'], $params);
    }

    /** @return array<string,mixed> */
    private function manifest(): array
    {
        $canonical = ['tables' => [
            ['name' => 'record', 'keys' => [
                ['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']],
                ['kind' => 'unique', 'name' => 'uq_record_parent_title', 'columns' => ['parent_id', 'title']],
            ], 'foreign_keys' => [[
                'name' => 'fk_record_parent', 'columns' => ['parent_id'],
                'referenced_table' => 'parent', 'referenced_columns' => ['id'],
            ]], 'columns' => [
                ['name' => 'id', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'parent_id', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'title', 'type' => 'varchar(255)', 'nullable' => false, 'default' => ''],
                ['name' => 'enabled', 'type' => 'boolean', 'nullable' => true],
                ['name' => 'amount', 'type' => 'decimal(12,2)', 'nullable' => false],
                ['name' => 'payload', 'type' => 'json', 'nullable' => false],
                ['name' => 'recorded_at', 'type' => 'datetime', 'nullable' => true],
                ['name' => 'bytes', 'type' => 'blob', 'nullable' => true],
            ]],
            ['name' => 'parent', 'keys' => [['kind' => 'primary', 'name' => 'pk_parent', 'columns' => ['id']]], 'foreign_keys' => [], 'columns' => [
                ['name' => 'id', 'type' => 'bigint', 'nullable' => false],
                ['name' => 'code', 'type' => 'varchar(40)', 'nullable' => false],
            ]],
        ]];
        $inspection = app_firebird_source_inspection_normalize([
            'source_identity' => 'sample34-firebird-local-file',
            'relations' => [
                ['relation_name' => 'RECORD', 'system_flag' => 0],
                ['relation_name' => 'PARENT', 'system_flag' => 0],
            ],
            'fields' => [
                $this->field('RECORD', 'ID', 'BIGINT', 0, null, 1),
                $this->field('RECORD', 'PARENT_ID', 'BIGINT', 1, null, 1),
                $this->field('RECORD', 'TITLE', 'VARCHAR', 2, 255, 1),
                $this->field('RECORD', 'ENABLED', 'SMALLINT', 3),
                $this->field('RECORD', 'AMOUNT', 'DECIMAL', 4, null, 1, 12, -2),
                $this->field('RECORD', 'PAYLOAD', 'BLOB', 5, null, 1, 0, null, null, 1),
                $this->field('RECORD', 'RECORDED_AT', 'TIMESTAMP', 6),
                $this->field('RECORD', 'BYTES', 'BLOB', 7, null, 0, 0, null, null, 0),
                $this->field('PARENT', 'ID', 'BIGINT', 0, null, 1),
                $this->field('PARENT', 'CODE', 'VARCHAR', 1, 40, 1),
            ],
            'constraints' => [
                $this->constraint('PARENT', 'PK_PARENT', 'PRIMARY KEY', 'PK_PARENT_IDX'),
                $this->constraint('RECORD', 'PK_RECORD', 'PRIMARY KEY', 'PK_RECORD_IDX'),
                $this->constraint('RECORD', 'UQ_RECORD_PARENT_TITLE', 'UNIQUE', 'UQ_RECORD_PARENT_TITLE_IDX'),
                $this->constraint('RECORD', 'FK_RECORD_PARENT', 'FOREIGN KEY', 'FK_RECORD_PARENT_IDX'),
            ],
            'index_segments' => [
                $this->segment('PK_PARENT_IDX', 'ID', 0),
                $this->segment('PK_RECORD_IDX', 'ID', 0),
                $this->segment('UQ_RECORD_PARENT_TITLE_IDX', 'PARENT_ID', 0),
                $this->segment('UQ_RECORD_PARENT_TITLE_IDX', 'TITLE', 1),
                $this->segment('FK_RECORD_PARENT_IDX', 'PARENT_ID', 0),
            ],
            'ref_constraints' => [
                ['constraint_name' => 'FK_RECORD_PARENT', 'referenced_constraint_name' => 'PK_PARENT'],
            ],
            'row_counts' => ['parent' => 1, 'record' => 2],
            'value_profiles' => [
                ['table' => 'RECORD', 'column' => 'PAYLOAD', 'profile' => ['storage_classes' => ['text'], 'invalid_json_count' => 0]],
                ['table' => 'RECORD', 'column' => 'BYTES', 'profile' => ['storage_classes' => ['blob', 'null']]],
            ],
        ]);
        return app_firebird_mysql_promotion_manifest_build($canonical, $inspection);
    }

    /** @return array<string,list<array<string,mixed>>> */
    private function rows(): array
    {
        return [
            'parent' => [
                ['id' => '2', 'code' => 'P2'],
            ],
            'record' => [
                ['id' => '9', 'parent_id' => '2', 'title' => 'First', 'enabled' => 1, 'amount' => '10.50', 'payload' => '{"z":2,"a":1}', 'recorded_at' => '2026-07-13 01:02:03.1234', 'bytes' => "\0A\1"],
                ['id' => '3', 'parent_id' => '2', 'title' => 'Second', 'enabled' => null, 'amount' => '0.00', 'payload' => '{"a":0}', 'recorded_at' => null, 'bytes' => null],
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function field(string $relation, string $name, string $type, int $position, ?int $length = null, int $nullFlag = 0, int $precision = 0, ?int $scale = null, ?string $default = null, ?int $subType = null): array
    {
        return ['relation_name' => $relation, 'field_name' => $name, 'type_name' => $type, 'field_position' => $position, 'field_length' => $length, 'null_flag' => $nullFlag, 'field_precision' => $precision, 'field_scale' => $scale, 'default_source' => $default, 'field_sub_type' => $subType];
    }

    /** @return array<string,string> */
    private function constraint(string $relation, string $name, string $type, string $index): array
    {
        return ['relation_name' => $relation, 'constraint_name' => $name, 'constraint_type' => $type, 'index_name' => $index];
    }

    /** @return array<string,mixed> */
    private function segment(string $index, string $field, int $position): array
    {
        return ['index_name' => $index, 'field_name' => $field, 'field_position' => $position];
    }
}
