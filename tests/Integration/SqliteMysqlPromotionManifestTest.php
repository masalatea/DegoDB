<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_promotion_manifest.php';

final class SqliteMysqlPromotionManifestTest extends TestCase
{
    public function testSupportedFixtureBuildsDeterministicReviewOnlyManifest(): void
    {
        [$canonical, $source] = $this->fixture();
        $first = app_sqlite_mysql_promotion_manifest_build($canonical, $source, ['target_identity' => 'local-mysql']);
        $second = app_sqlite_mysql_promotion_manifest_build($canonical, $source, ['target_identity' => 'local-mysql']);

        self::assertTrue($first['ok'], json_encode($first['blockers']));
        self::assertSame($first, $second);
        self::assertFalse($first['mutation_performed']);
        self::assertSame(['parent', 'record'], array_column($first['tables'], 'name'));
        self::assertSame(['target_schema_prepare', 'data_import', 'cutover'], $first['required_approvals']);
        self::assertSame([], app_sqlite_mysql_promotion_manifest_contract_errors($first));
    }

    public function testInputOrderingDoesNotChangeManifest(): void
    {
        [$canonical, $source] = $this->fixture();
        $reversedCanonical = $canonical;
        $reversedCanonical['tables'] = array_reverse($reversedCanonical['tables']);
        $reversedSource = $source;
        $reversedSource['tables'] = array_reverse($reversedSource['tables']);

        self::assertSame(
            app_sqlite_mysql_promotion_manifest_build($canonical, $source),
            app_sqlite_mysql_promotion_manifest_build($reversedCanonical, $reversedSource),
        );
    }

    public function testSchemaMismatchAndMissingPrimaryKeyFailClosed(): void
    {
        [$canonical, $source] = $this->fixture();
        $canonical['tables'][0]['keys'] = [];
        array_pop($source['tables'][1]['columns']);
        $manifest = app_sqlite_mysql_promotion_manifest_build($canonical, $source);

        self::assertFalse($manifest['ok']);
        self::assertContains('stable_primary_key_missing', array_column($manifest['blockers'], 'code'));
        self::assertContains('source_schema_mismatch', array_column($manifest['blockers'], 'code'));
    }

    public function testValueProfilesProduceStableBlockersAndWarning(): void
    {
        [$canonical, $source] = $this->fixture();
        foreach ($source['tables'][0]['columns'] as &$column) {
            if ($column['name'] === 'payload') $column['profile']['invalid_json_count'] = 1;
            if ($column['name'] === 'amount') $column['profile']['decimal_precision_violation_count'] = 1;
            if ($column['name'] === 'recorded_at') $column['profile']['ambiguous_timestamp_count'] = 1;
            if ($column['name'] === 'title') $column['profile']['trailing_space_count'] = 1;
        }
        unset($column);
        $manifest = app_sqlite_mysql_promotion_manifest_build($canonical, $source);

        self::assertSame(
            ['ambiguous_timestamp_value', 'decimal_precision_violation', 'invalid_json_value'],
            array_values(array_intersect(
                ['ambiguous_timestamp_value', 'decimal_precision_violation', 'invalid_json_value'],
                array_column($manifest['blockers'], 'code'),
            )),
        );
        self::assertContains('trailing_space_semantics_risk', array_column($manifest['warnings'], 'code'));
    }

    public function testMixedStorageClassFailsClosed(): void
    {
        [$canonical, $source] = $this->fixture();
        $source['tables'][0]['columns'][0]['profile']['storage_classes'][] = 'text';
        $manifest = app_sqlite_mysql_promotion_manifest_build($canonical, $source);

        self::assertContains('sqlite_dynamic_type_violation', array_column($manifest['blockers'], 'code'));
    }

    public function testForeignKeyCycleFailsClosed(): void
    {
        [$canonical, $source] = $this->fixture();
        $canonical['tables'][1]['foreign_keys'] = [[
            'name' => 'fk_parent_record', 'columns' => ['id'],
            'referenced_table' => 'record', 'referenced_columns' => ['id'],
        ]];
        $source['tables'][1]['foreign_keys'] = $canonical['tables'][1]['foreign_keys'];
        $manifest = app_sqlite_mysql_promotion_manifest_build($canonical, $source);

        self::assertContains('foreign_key_cycle_unsupported', array_column($manifest['blockers'], 'code'));
    }

    public function testSecretBearingInputIsRejectedAndNeverCopied(): void
    {
        [$canonical, $source] = $this->fixture();
        $manifest = app_sqlite_mysql_promotion_manifest_build($canonical, $source, ['password' => 'do-not-copy']);
        $json = json_encode($manifest, JSON_THROW_ON_ERROR);

        self::assertFalse($manifest['ok']);
        self::assertContains('secret_in_artifact', array_column($manifest['blockers'], 'code'));
        self::assertStringNotContainsString('do-not-copy', $json);

        $dsn = app_sqlite_mysql_promotion_manifest_build($canonical, $source, ['target_identity' => 'mysql://user:pass@db/app']);
        self::assertContains('secret_in_artifact', array_column($dsn['blockers'], 'code'));
        self::assertSame('mysql-target', $dsn['target']['identity']);
        self::assertStringNotContainsString('user:pass', json_encode($dsn, JSON_THROW_ON_ERROR));
    }

    public function testContractRejectsMutationOrWrongDriver(): void
    {
        [$canonical, $source] = $this->fixture();
        $manifest = app_sqlite_mysql_promotion_manifest_build($canonical, $source);
        $manifest['mutation_performed'] = true;
        $manifest['target']['driver'] = 'sqlite';

        self::assertSame(['mutation_performed', 'target_driver'], app_sqlite_mysql_promotion_manifest_contract_errors($manifest));
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
