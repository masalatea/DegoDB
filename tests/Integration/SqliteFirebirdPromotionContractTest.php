<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_firebird_promotion_contract.php';

final class SqliteFirebirdPromotionContractTest extends TestCase
{
    public function testSupportedFixtureBuildsDeterministicLocalDurableContract(): void
    {
        [$canonical, $source] = $this->fixture();
        $first = app_sqlite_firebird_promotion_contract_build($canonical, $source, ['target_identity' => 'local-firebird-file']);
        $second = app_sqlite_firebird_promotion_contract_build($canonical, $source, ['target_identity' => 'local-firebird-file']);

        self::assertTrue($first['ok'], json_encode($first['blockers']));
        self::assertSame($first, $second);
        self::assertFalse($first['mutation_performed']);
        self::assertSame('firebird', $first['target']['driver']);
        self::assertSame('local_durable_file', $first['target']['profile']);
        self::assertTrue($first['source']['retain_after_promotion']);
        self::assertSame(['parent', 'record'], array_column($first['tables'], 'name'));
        self::assertSame(['source_backup', 'target_file_prepare', 'data_import_rehearsal', 'local_profile_switch'], $first['required_approvals']);
        self::assertContains('firebird_backup_restore_smoke', $first['required_verification']);
        self::assertContains('automatic_source_delete', $first['non_goals']);
        self::assertSame([], app_sqlite_firebird_promotion_contract_errors($first));
    }

    public function testInputOrderingDoesNotChangeContract(): void
    {
        [$canonical, $source] = $this->fixture();
        $reversedCanonical = $canonical;
        $reversedCanonical['tables'] = array_reverse($reversedCanonical['tables']);
        $reversedSource = $source;
        $reversedSource['tables'] = array_reverse($reversedSource['tables']);

        self::assertSame(
            app_sqlite_firebird_promotion_contract_build($canonical, $source),
            app_sqlite_firebird_promotion_contract_build($reversedCanonical, $reversedSource),
        );
    }

    public function testFirebirdTypeMappingsAreExplicit(): void
    {
        [$canonical, $source] = $this->fixture();
        $contract = app_sqlite_firebird_promotion_contract_build($canonical, $source);
        $record = $contract['tables'][1];
        $types = array_column($record['columns'], 'target_type', 'name');

        self::assertSame('BIGINT', $types['id']);
        self::assertSame('SMALLINT', $types['enabled']);
        self::assertSame('DECIMAL(12,2)', $types['amount']);
        self::assertSame('BLOB SUB_TYPE TEXT', $types['payload']);
        self::assertSame('TIMESTAMP', $types['recorded_at']);
        self::assertSame('BLOB SUB_TYPE BINARY', $types['bytes']);
        self::assertSame('preserve_source_values_then_advance_firebird_identity_or_generator', $record['identity_strategy']);
        self::assertContains('json_stored_as_text', array_column($contract['warnings'], 'code'));
    }

    public function testMismatchAndMissingPrimaryKeyFailClosed(): void
    {
        [$canonical, $source] = $this->fixture();
        $canonical['tables'][0]['keys'] = [];
        array_pop($source['tables'][1]['columns']);
        $contract = app_sqlite_firebird_promotion_contract_build($canonical, $source);

        self::assertFalse($contract['ok']);
        self::assertContains('stable_primary_key_missing', array_column($contract['blockers'], 'code'));
        self::assertContains('source_schema_mismatch', array_column($contract['blockers'], 'code'));
    }

    public function testValueProfilesProduceStableFirebirdBlockersAndWarnings(): void
    {
        [$canonical, $source] = $this->fixture();
        foreach ($source['tables'][0]['columns'] as &$column) {
            if ($column['name'] === 'payload') $column['profile']['invalid_json_count'] = 1;
            if ($column['name'] === 'amount') $column['profile']['decimal_precision_violation_count'] = 1;
            if ($column['name'] === 'recorded_at') $column['profile']['ambiguous_timestamp_count'] = 1;
            if ($column['name'] === 'title') $column['profile']['trailing_space_count'] = 1;
        }
        unset($column);
        $contract = app_sqlite_firebird_promotion_contract_build($canonical, $source);

        self::assertContains('invalid_json_value', array_column($contract['blockers'], 'code'));
        self::assertContains('decimal_precision_violation', array_column($contract['blockers'], 'code'));
        self::assertContains('ambiguous_timestamp_value', array_column($contract['blockers'], 'code'));
        self::assertContains('trailing_space_semantics_risk', array_column($contract['warnings'], 'code'));
    }

    public function testSecretBearingInputIsRejectedAndNeverCopied(): void
    {
        [$canonical, $source] = $this->fixture();
        $contract = app_sqlite_firebird_promotion_contract_build($canonical, $source, ['password' => 'do-not-copy']);
        $json = json_encode($contract, JSON_THROW_ON_ERROR);

        self::assertFalse($contract['ok']);
        self::assertContains('secret_in_artifact', array_column($contract['blockers'], 'code'));
        self::assertStringNotContainsString('do-not-copy', $json);

        $dsn = app_sqlite_firebird_promotion_contract_build($canonical, $source, ['target_identity' => 'firebird://user:pass@local/app.fdb']);
        self::assertContains('secret_in_artifact', array_column($dsn['blockers'], 'code'));
        self::assertSame('firebird-local-profile', $dsn['target']['identity']);
        self::assertStringNotContainsString('user:pass', json_encode($dsn, JSON_THROW_ON_ERROR));
    }

    public function testContractRejectsMutationWrongDriverAndSourceDeletion(): void
    {
        [$canonical, $source] = $this->fixture();
        $contract = app_sqlite_firebird_promotion_contract_build($canonical, $source);
        $contract['mutation_performed'] = true;
        $contract['target']['driver'] = 'sqlite';
        $contract['source']['retain_after_promotion'] = false;
        $contract['non_goals'] = [];

        self::assertSame(
            ['mutation_performed', 'source_retention', 'target_driver', 'automatic_source_delete_non_goal'],
            app_sqlite_firebird_promotion_contract_errors($contract),
        );
    }

    /** @return array{array<string,mixed>,array<string,mixed>} */
    private function fixture(): array
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
        $source = ['source_identity' => 'fixture.sqlite', 'tables' => [
            ['name' => 'record', 'row_count' => 3, 'keys' => [
                ['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']],
                ['kind' => 'unique', 'name' => 'uq_record_parent_title', 'columns' => ['parent_id', 'title']],
            ], 'foreign_keys' => [[
                'name' => 'fk_record_parent', 'columns' => ['parent_id'],
                'referenced_table' => 'parent', 'referenced_columns' => ['id'],
            ]], 'columns' => [
                $this->sourceColumn('id', 'INTEGER', ['integer']),
                $this->sourceColumn('parent_id', 'INTEGER', ['integer']),
                $this->sourceColumn('title', 'TEXT', ['text']),
                $this->sourceColumn('enabled', 'INTEGER', ['integer', 'null']),
                $this->sourceColumn('amount', 'TEXT', ['text']),
                $this->sourceColumn('payload', 'TEXT', ['text']),
                $this->sourceColumn('recorded_at', 'TEXT', ['text', 'null']),
                $this->sourceColumn('bytes', 'BLOB', ['blob', 'null']),
            ]],
            ['name' => 'parent', 'row_count' => 2, 'keys' => [['kind' => 'primary', 'name' => 'pk_parent', 'columns' => ['id']]], 'foreign_keys' => [], 'columns' => [
                $this->sourceColumn('id', 'INTEGER', ['integer']),
                $this->sourceColumn('code', 'TEXT', ['text']),
            ]],
        ]];
        return [$canonical, $source];
    }

    /** @return array<string,mixed> */
    private function sourceColumn(string $name, string $type, array $classes): array
    {
        return ['name' => $name, 'type' => $type, 'profile' => ['storage_classes' => $classes]];
    }
}
