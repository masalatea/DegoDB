<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/firebird_mysql_promotion_manifest.php';

final class FirebirdMysqlPromotionManifestTest extends TestCase
{
    public function testSupportedFixtureBuildsDeterministicManifest(): void
    {
        $canonical = $this->canonical();
        $inspection = app_firebird_source_inspection_normalize($this->firebirdMetadata());
        $first = app_firebird_mysql_promotion_manifest_build($canonical, $inspection, ['target_identity' => 'mysql-target']);
        $second = app_firebird_mysql_promotion_manifest_build($canonical, $inspection, ['target_identity' => 'mysql-target']);

        self::assertTrue($inspection['ok'], json_encode($inspection['blockers'], JSON_THROW_ON_ERROR));
        self::assertTrue($first['ok'], json_encode($first['blockers'], JSON_THROW_ON_ERROR));
        self::assertSame($first, $second);
        self::assertSame(APP_FIREBIRD_MYSQL_PROMOTION_MANIFEST_VERSION, $first['manifest_version']);
        self::assertFalse($first['mutation_performed']);
        self::assertSame('firebird', $first['source']['driver']);
        self::assertTrue($first['source']['requires_source_backup']);
        self::assertSame('mysql', $first['target']['driver']);
        self::assertSame(['parent', 'record'], array_column($first['tables'], 'name'));
        self::assertContains('firebird_backup_restore_smoke', $first['required_verification']);
        self::assertContains('firebird_to_sqlite', $first['non_goals']);
        self::assertSame([], app_firebird_mysql_promotion_manifest_contract_errors($first));

        $record = $first['tables'][1];
        $types = array_column($record['columns'], 'target_type', 'name');
        self::assertSame('BIGINT', $types['id']);
        self::assertSame('TINYINT(1)', $types['enabled']);
        self::assertSame('DECIMAL(12,2)', $types['amount']);
        self::assertSame('JSON', $types['payload']);
        self::assertSame('DATETIME(6)', $types['recorded_at']);
        self::assertSame('LONGBLOB', $types['bytes']);
    }

    public function testInputOrderingDoesNotChangeManifest(): void
    {
        $canonical = $this->canonical();
        $metadata = $this->firebirdMetadata();
        $reordered = $metadata;
        $reordered['relations'] = array_reverse($reordered['relations']);
        $reordered['fields'] = array_reverse($reordered['fields']);
        $reordered['constraints'] = array_reverse($reordered['constraints']);
        $reordered['index_segments'] = array_reverse($reordered['index_segments']);

        self::assertSame(
            app_firebird_mysql_promotion_manifest_build($canonical, app_firebird_source_inspection_normalize($metadata)),
            app_firebird_mysql_promotion_manifest_build($canonical, app_firebird_source_inspection_normalize($reordered)),
        );
    }

    public function testInspectionBlockersAndSecretTargetsFailClosed(): void
    {
        $canonical = $this->canonical();
        $metadata = $this->firebirdMetadata();
        $metadata['relations'][0]['relation_name'] = 'MixedCase';
        $inspection = app_firebird_source_inspection_normalize($metadata);
        $manifest = app_firebird_mysql_promotion_manifest_build($canonical, $inspection, ['target_identity' => 'mysql://user:pass@db/app']);

        self::assertFalse($inspection['ok']);
        self::assertFalse($manifest['ok']);
        self::assertContains('firebird_source_inspection_not_ready', array_column($manifest['blockers'], 'code'));
        self::assertContains('secret_in_artifact', array_column($manifest['blockers'], 'code'));
        self::assertStringNotContainsString('user:pass', json_encode($manifest, JSON_THROW_ON_ERROR));
    }

    public function testContractRejectsMutationWrongDriverAndMissingBackupGate(): void
    {
        $manifest = app_firebird_mysql_promotion_manifest_build($this->canonical(), app_firebird_source_inspection_normalize($this->firebirdMetadata()));
        $manifest['mutation_performed'] = true;
        $manifest['source']['driver'] = 'sqlite';
        $manifest['source']['requires_source_backup'] = false;
        $manifest['required_verification'] = [];
        $manifest['non_goals'] = [];

        self::assertSame(
            ['mutation_performed', 'source_driver', 'source_backup', 'firebird_backup_restore_smoke', 'firebird_to_sqlite_non_goal'],
            app_firebird_mysql_promotion_manifest_contract_errors($manifest),
        );
    }

    /** @return array<string,mixed> */
    private function canonical(): array
    {
        return ['tables' => [
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
    }

    /** @return array<string,mixed> */
    private function firebirdMetadata(): array
    {
        return [
            'source_identity' => 'sample34-firebird-local-file',
            'relations' => [
                ['relation_name' => 'RECORD', 'system_flag' => 0],
                ['relation_name' => 'PARENT', 'system_flag' => 0],
            ],
            'fields' => [
                $this->field('RECORD', 'ID', 'BIGINT', 0, null, 1),
                $this->field('RECORD', 'PARENT_ID', 'BIGINT', 1, null, 1),
                $this->field('RECORD', 'TITLE', 'VARCHAR', 2, 255, 1, 0, null, "DEFAULT ''"),
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
                ['table' => 'RECORD', 'column' => 'ID', 'profile' => ['storage_classes' => ['integer']]],
                ['table' => 'RECORD', 'column' => 'ENABLED', 'profile' => ['storage_classes' => ['integer', 'null']]],
                ['table' => 'RECORD', 'column' => 'PAYLOAD', 'profile' => ['storage_classes' => ['text'], 'invalid_json_count' => 0]],
                ['table' => 'RECORD', 'column' => 'BYTES', 'profile' => ['storage_classes' => ['blob', 'null']]],
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function field(
        string $relation,
        string $name,
        string $type,
        int $position,
        ?int $length = null,
        int $nullFlag = 0,
        int $precision = 0,
        ?int $scale = null,
        ?string $default = null,
        ?int $subType = null,
    ): array {
        return [
            'relation_name' => $relation,
            'field_name' => $name,
            'type_name' => $type,
            'field_position' => $position,
            'field_length' => $length,
            'null_flag' => $nullFlag,
            'field_precision' => $precision,
            'field_scale' => $scale,
            'default_source' => $default,
            'field_sub_type' => $subType,
        ];
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
