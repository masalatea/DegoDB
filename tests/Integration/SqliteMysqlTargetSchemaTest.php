<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_target_schema.php';

final class SqliteMysqlTargetSchemaTest extends TestCase
{
    public function testBuildsDeterministicParentFirstSchemaPlan(): void
    {
        $plan = app_sqlite_mysql_target_schema_plan($this->manifest());
        self::assertTrue($plan['ok'], implode(',', $plan['errors']));
        self::assertFalse($plan['mutation_performed']);
        self::assertSame(['parent', 'record'], array_column($plan['tables'], 'name'));
        self::assertStringContainsString('ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin', $plan['statements'][0]);
        self::assertStringContainsString('CONSTRAINT `uq_record_parent_title` UNIQUE (`parent_id`, `title`)', $plan['statements'][1]);
        self::assertStringContainsString('CONSTRAINT `fk_record_parent` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`)', $plan['statements'][1]);
        self::assertSame($plan, app_sqlite_mysql_target_schema_plan($this->manifest()));
    }

    public function testUnsafeIdentifierAndTypeFailClosed(): void
    {
        $manifest = $this->manifest();
        $manifest['tables'][0]['name'] = 'parent;drop';
        $manifest['tables'][1]['columns'][0]['target_type'] = 'BIGINT); DROP TABLE x; --';
        $plan = app_sqlite_mysql_target_schema_plan($manifest);
        self::assertFalse($plan['ok']);
        self::assertContains('invalid_table_identifier:parent;drop', $plan['errors']);
        self::assertContains('invalid_target_type:record.id', $plan['errors']);
    }

    public function testApplyRequiresApprovalBeforeInspectingOrMutating(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $result = app_sqlite_mysql_target_schema_apply($pdo, app_sqlite_mysql_target_schema_plan($this->manifest()), false);
        self::assertFalse($result['ok']);
        self::assertSame('explicit_approval_required', $result['error']);
        self::assertFalse($result['mutation_performed']);
    }

    public function testAppliesAndReinspectsDedicatedLiveMysqlSchema(): void
    {
        $database = trim((string) getenv('PROMOTION_MYSQL_TEST_DB'));
        if ($database === '') self::markTestSkipped('dedicated promotion MySQL schema is not configured');
        self::assertMatchesRegularExpression('/^mtool_promotion_test_[a-z0-9_]+$/', $database);
        $host = trim((string) getenv('APP_LAB_DB_HOST'));
        $port = trim((string) getenv('APP_LAB_DB_PORT')) ?: '3306';
        $user = (string) getenv('APP_LAB_DB_USER');
        $password = (string) getenv('APP_LAB_DB_PASSWORD');
        self::assertNotSame('', $host);
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        try {
            self::assertTrue(app_sqlite_mysql_target_schema_inspect($pdo)['empty']);
            $plan = app_sqlite_mysql_target_schema_plan($this->manifest());
            $applied = app_sqlite_mysql_target_schema_apply($pdo, $plan, true);
            self::assertTrue($applied['ok'], $applied['error']);
            self::assertSame(['parent', 'record'], $applied['created_tables']);
            $inspection = app_sqlite_mysql_target_schema_inspect($pdo);
            self::assertFalse($inspection['empty']);
            self::assertSame(['parent', 'record'], $inspection['tables']);
            self::assertSame('target_not_empty', app_sqlite_mysql_target_schema_apply($pdo, $plan, true)['error']);
            $fkCount = $pdo->query("SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND CONSTRAINT_NAME = 'fk_record_parent'")->fetchColumn();
            self::assertSame(1, (int) $fkCount);
        } finally {
            $pdo->exec('DROP TABLE IF EXISTS `record`');
            $pdo->exec('DROP TABLE IF EXISTS `parent`');
        }
    }

    /** @return array<string,mixed> */
    private function manifest(): array
    {
        $base = [
            'manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION, 'ok' => true, 'stage' => 'preflight', 'mutation_performed' => false,
            'source' => ['driver' => 'sqlite', 'identity' => 'fixture.sqlite', 'snapshot_sha256' => str_repeat('a', 64)],
            'target' => ['driver' => 'mysql', 'identity' => 'target', 'must_be_empty' => true], 'canonical_sha256' => str_repeat('b', 64),
            'blockers' => [], 'warnings' => [], 'required_approvals' => [], 'required_verification' => [], 'non_goals' => [],
        ];
        $base['tables'] = [
            ['name' => 'parent', 'row_count' => 1, 'primary_key' => ['id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_parent', 'columns' => ['id']]], 'foreign_keys' => [], 'columns' => [
                ['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false, 'default' => null],
                ['name' => 'code', 'target_type' => 'VARCHAR(40)', 'nullable' => false, 'default' => ''],
            ]],
            ['name' => 'record', 'row_count' => 2, 'primary_key' => ['id'], 'keys' => [
                ['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']],
                ['kind' => 'unique', 'name' => 'uq_record_parent_title', 'columns' => ['parent_id', 'title']],
            ], 'foreign_keys' => [['name' => 'fk_record_parent', 'columns' => ['parent_id'], 'referenced_table' => 'parent', 'referenced_columns' => ['id']]], 'columns' => [
                ['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false, 'default' => null],
                ['name' => 'parent_id', 'target_type' => 'BIGINT', 'nullable' => false, 'default' => null],
                ['name' => 'title', 'target_type' => 'VARCHAR(255)', 'nullable' => false, 'default' => 'draft'],
            ]],
        ];
        return $base;
    }
}
