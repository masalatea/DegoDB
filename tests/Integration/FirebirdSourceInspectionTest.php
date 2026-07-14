<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/firebird_source_inspection.php';

final class FirebirdSourceInspectionTest extends TestCase
{
    public function testNormalizesFirebirdMetadataIntoPromotionSourceShape(): void
    {
        $inspection = app_firebird_source_inspection_normalize($this->fixture());

        self::assertTrue($inspection['ok'], json_encode($inspection['blockers']));
        self::assertSame('firebird', $inspection['driver']);
        self::assertFalse($inspection['mutation_performed']);
        self::assertSame(['parent', 'record'], array_column($inspection['tables'], 'name'));
        self::assertSame([], app_firebird_source_inspection_contract_errors($inspection));

        $record = $inspection['tables'][1];
        self::assertSame(3, $record['row_count']);
        self::assertSame(['id'], $record['keys'][0]['columns']);
        self::assertSame('primary', $record['keys'][0]['kind']);
        self::assertSame(['parent_id', 'title'], $record['keys'][1]['columns']);
        self::assertSame('unique', $record['keys'][1]['kind']);
        self::assertSame([[
            'name' => 'fk_record_parent',
            'columns' => ['parent_id'],
            'referenced_table' => 'parent',
            'referenced_columns' => ['id'],
        ]], $record['foreign_keys']);

        $types = array_column($record['columns'], 'type', 'name');
        self::assertSame('BIGINT', $types['id']);
        self::assertSame('VARCHAR(255)', $types['title']);
        self::assertSame('SMALLINT', $types['enabled']);
        self::assertSame('DECIMAL(12,2)', $types['amount']);
        self::assertSame('BLOB SUB_TYPE TEXT', $types['payload']);
        self::assertSame('TIMESTAMP', $types['recorded_at']);
        self::assertSame('BLOB SUB_TYPE BINARY', $types['bytes']);
        self::assertSame(['integer'], $record['columns'][0]['profile']['storage_classes']);
    }

    public function testNormalizationIsDeterministicAcrossInputOrdering(): void
    {
        $fixture = $this->fixture();
        $reordered = $fixture;
        $reordered['relations'] = array_reverse($reordered['relations']);
        $reordered['fields'] = array_reverse($reordered['fields']);
        $reordered['constraints'] = array_reverse($reordered['constraints']);
        $reordered['index_segments'] = array_reverse($reordered['index_segments']);

        self::assertSame(
            app_firebird_source_inspection_normalize($fixture),
            app_firebird_source_inspection_normalize($reordered),
        );
    }

    public function testQuotedOrCaseSensitiveIdentifiersFailClosed(): void
    {
        $fixture = $this->fixture();
        $fixture['relations'][0]['relation_name'] = 'MixedCase';
        $inspection = app_firebird_source_inspection_normalize($fixture);

        self::assertFalse($inspection['ok']);
        self::assertContains('firebird_quoted_or_case_sensitive_identifier_unsupported', array_column($inspection['blockers'], 'code'));
    }

    public function testMissingForeignKeyReferenceFailsClosed(): void
    {
        $fixture = $this->fixture();
        $fixture['ref_constraints'] = [];
        $inspection = app_firebird_source_inspection_normalize($fixture);

        self::assertFalse($inspection['ok']);
        self::assertContains('firebird_foreign_key_reference_missing', array_column($inspection['blockers'], 'code'));
    }

    public function testSecretBearingInputIsRejectedAndNeverCopied(): void
    {
        $fixture = $this->fixture();
        $inspection = app_firebird_source_inspection_normalize($fixture, ['token' => 'do-not-copy']);
        $json = json_encode($inspection, JSON_THROW_ON_ERROR);

        self::assertFalse($inspection['ok']);
        self::assertContains('secret_in_artifact', array_column($inspection['blockers'], 'code'));
        self::assertStringNotContainsString('do-not-copy', $json);

        $fixture['source_identity'] = 'firebird://user:pass@local/app.fdb';
        $withDsn = app_firebird_source_inspection_normalize($fixture);
        self::assertSame('firebird-source', $withDsn['source_identity']);
        self::assertStringNotContainsString('user:pass', json_encode($withDsn, JSON_THROW_ON_ERROR));
    }

    public function testContractRejectsMutationAndWrongDriver(): void
    {
        $inspection = app_firebird_source_inspection_normalize($this->fixture());
        $inspection['mutation_performed'] = true;
        $inspection['driver'] = 'sqlite';

        self::assertSame(['driver', 'mutation_performed'], app_firebird_source_inspection_contract_errors($inspection));
    }

    /** @return array<string,mixed> */
    private function fixture(): array
    {
        return [
            'source_identity' => 'local-firebird-profile',
            'relations' => [
                ['relation_name' => 'RECORD', 'system_flag' => 0],
                ['relation_name' => 'PARENT', 'system_flag' => 0],
                ['relation_name' => 'RDB$RELATIONS', 'system_flag' => 1],
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
            'row_counts' => ['parent' => 2, 'record' => 3],
            'value_profiles' => [
                ['table' => 'RECORD', 'column' => 'ID', 'profile' => ['storage_classes' => ['integer']]],
                ['table' => 'RECORD', 'column' => 'PAYLOAD', 'profile' => ['storage_classes' => ['text'], 'invalid_json_count' => 0]],
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
