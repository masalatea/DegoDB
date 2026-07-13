<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_promotion_rehearsal.php';

final class Sample33SqliteMysqlPromotionTest extends TestCase
{
    public function testSample33ReferenceBuildsReadyPromotionRehearsalPackage(): void
    {
        $fixture = $this->fixture();
        $manifest = $fixture['manifest'];
        $schema = app_sqlite_mysql_target_schema_plan($manifest);
        $export = app_sqlite_mysql_export($this->sqlite($fixture), $manifest, 1);
        $import = [
            'ok' => true,
            'stage' => 'chunk_committed',
            'error' => '',
            'checkpoint' => $fixture['import_checkpoint'],
            'mutation_performed' => true,
        ];
        $verification = app_sqlite_mysql_verification_artifact([
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'import_checkpoint_sha256' => app_sqlite_mysql_promotion_digest($fixture['import_checkpoint']),
        ], array_map(static fn (string $key): array => ['check_key' => $key, 'status' => 'passed'], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED));
        $cutover = app_sqlite_mysql_cutover_plan($manifest, $verification, $fixture['cutover'], $fixture['rollback'], APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS);
        $operator = app_sqlite_mysql_cutover_operator_package($cutover, $fixture['switch'], $fixture['operator_rehearsal'], APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);
        $package = app_sqlite_mysql_promotion_rehearsal_package($manifest, $schema, $export, $import, $verification, $cutover, $operator);

        self::assertSame('sample33-sqlite-to-mysql-promotion', $fixture['sample']);
        self::assertSame('promotion-tutorial-sample', app_sample_pack_structure_type($fixture['sample']));
        self::assertSame(APP_SQLITE_MYSQL_PROMOTION_REHEARSAL_PACKAGE_VERSION, $fixture['promotion_contract']);
        self::assertTrue($schema['ok'], implode(',', $schema['errors']));
        self::assertTrue($export['ok'], implode(',', $export['errors']));
        self::assertTrue($verification['cutover_ready'], json_encode($verification['blockers'], JSON_THROW_ON_ERROR));
        self::assertTrue($cutover['cutover_allowed'], implode(',', $cutover['errors']));
        self::assertTrue($operator['operator_package_ready'], implode(',', $operator['errors']));
        self::assertTrue($package['rehearsal_ready'], implode(',', $package['errors']));
        self::assertSame('promotion_rehearsal_ready', $package['stage']);
        self::assertSame(2, $package['export_summary']['table_count']);
        self::assertSame(3, $package['export_summary']['chunk_count']);
        self::assertSame(3, $package['export_summary']['row_count']);
        self::assertFalse($package['mutation_performed']);
        self::assertTrue($package['requires_explicit_cutover']);
        self::assertContains('automatic_cutover', $package['non_goals']);
    }

    public function testSample33ValidatorCliReportsReadyPackageWithoutMutation(): void
    {
        $script = dirname(__DIR__, 2) . '/mtool/scripts/validate_sample33_promotion.php';
        $json = shell_exec(PHP_BINARY . ' ' . escapeshellarg($script) . ' --json');
        self::assertIsString($json);
        $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($result['ok'], json_encode($result, JSON_THROW_ON_ERROR));
        self::assertSame('sample33-sqlite-to-mysql-promotion', $result['sample']);
        self::assertSame('promotion-tutorial-sample', $result['structure_type']);
        self::assertSame('promotion_rehearsal_ready', $result['rehearsal_package']['stage']);
        self::assertFalse($result['rehearsal_package']['mutation_performed']);
        self::assertTrue($result['rehearsal_package']['requires_explicit_cutover']);

        $text = shell_exec(PHP_BINARY . ' ' . escapeshellarg($script));
        self::assertIsString($text);
        self::assertStringContainsString('OK: yes', $text);
        self::assertStringContainsString('Mutation performed by validator: no', $text);
    }

    /** @return array<string,mixed> */
    private function fixture(): array
    {
        $path = app_sample_pack_reference_root('sample33-sqlite-to-mysql-promotion') . '/promotion-rehearsal-contract.json';
        $json = file_get_contents($path);
        self::assertIsString($json, 'failed to read sample33 fixture');
        $fixture = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($fixture);
        return $fixture;
    }

    /** @param array<string,mixed> $fixture */
    private function sqlite(array $fixture): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        foreach ($fixture['source_sql'] as $sql) {
            $pdo->exec((string) $sql);
        }
        foreach ($fixture['source_rows'] as $row) {
            $statement = $pdo->prepare((string) $row['sql']);
            $values = [];
            foreach ($row['values'] as $value) {
                if (is_array($value) && ($value['encoding'] ?? '') === 'base64') {
                    $decoded = base64_decode((string) $value['value'], true);
                    self::assertIsString($decoded);
                    $values[] = $decoded;
                    continue;
                }
                $values[] = $value;
            }
            $statement->execute($values);
        }
        return $pdo;
    }
}
