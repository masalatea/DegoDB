<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_firebird_promotion_rehearsal.php';

final class Sample34SqliteFirebirdPromotionTest extends TestCase
{
    public function testSample34ReferenceBuildsReadyFirebirdPromotionContract(): void
    {
        $fixture = $this->fixture();
        $contract = app_sqlite_firebird_promotion_contract_build(
            $fixture['canonical_snapshot'],
            $fixture['sqlite_inspection'],
            $fixture['options'],
        );

        self::assertSame('sample34-sqlite-to-firebird-promotion', $fixture['sample']);
        self::assertSame('promotion-tutorial-sample', app_sample_pack_structure_type($fixture['sample']));
        self::assertSame(APP_SQLITE_FIREBIRD_PROMOTION_CONTRACT_VERSION, $fixture['promotion_contract']);
        self::assertTrue($contract['ok'], json_encode($contract['blockers'], JSON_THROW_ON_ERROR));
        self::assertSame([], app_sqlite_firebird_promotion_contract_errors($contract));
        self::assertFalse($contract['mutation_performed']);
        self::assertSame('firebird', $contract['target']['driver']);
        self::assertSame('local_durable_file', $contract['target']['profile']);
        self::assertTrue($contract['source']['retain_after_promotion']);
        self::assertTrue($contract['target']['must_be_new_or_empty']);
        self::assertTrue($contract['target']['requires_backup_restore_smoke']);
        self::assertSame(['parent', 'record'], array_column($contract['tables'], 'name'));
        self::assertContains('firebird_backup_restore_smoke', $contract['required_verification']);
        self::assertContains('local_profile_switch', $contract['required_approvals']);
        self::assertContains('automatic_source_delete', $contract['non_goals']);
        self::assertContains('json_stored_as_text', array_column($contract['warnings'], 'code'));
        self::assertContains('text_columns_mapped_to_blob_sub_type_text', array_column($contract['warnings'], 'code'));

        $record = $contract['tables'][1];
        $types = array_column($record['columns'], 'target_type', 'name');
        self::assertSame('BIGINT', $types['id']);
        self::assertSame('SMALLINT', $types['enabled']);
        self::assertSame('DECIMAL(12,2)', $types['amount']);
        self::assertSame('BLOB SUB_TYPE TEXT', $types['payload']);
        self::assertSame('BLOB SUB_TYPE TEXT', $types['body']);
        self::assertSame('TIMESTAMP', $types['recorded_at']);
        self::assertSame('BLOB SUB_TYPE BINARY', $types['bytes']);

        $schema = app_sqlite_firebird_target_schema_plan($contract);
        self::assertTrue($schema['ok'], implode(',', $schema['errors']));
        self::assertFalse($schema['mutation_performed']);
        self::assertStringContainsString('CREATE TABLE "parent"', $schema['statements'][0]);
        self::assertStringContainsString('BLOB SUB_TYPE TEXT', implode("\n", $schema['statements']));
        self::assertStringContainsString('BLOB SUB_TYPE BINARY', implode("\n", $schema['statements']));
        self::assertStringContainsString('TIMESTAMP', implode("\n", $schema['statements']));

        $export = app_sqlite_firebird_export($this->sqlite($fixture), $contract, 1);
        self::assertTrue($export['ok'], implode(',', $export['errors']));
        self::assertFalse($export['mutation_performed']);
        self::assertCount(3, $export['chunks']);
        self::assertSame('export_ready', $export['stage']);
        self::assertSame(['encoding' => 'json-text', 'value' => '{"a":1,"z":2}'], $export['chunks'][2]['rows'][0]['payload']);
        self::assertSame(['encoding' => 'base64', 'byte_length' => 3, 'value' => 'AEEB'], $export['chunks'][2]['rows'][0]['bytes']);

        $rehearsal = app_sqlite_firebird_import_rehearsal_package($contract, $schema, $export);
        self::assertTrue($rehearsal['rehearsal_ready'], implode(',', $rehearsal['errors']));
        self::assertSame('firebird_import_rehearsal_ready', $rehearsal['stage']);
        self::assertFalse($rehearsal['mutation_performed']);
        self::assertSame(2, $rehearsal['export_summary']['table_count']);
        self::assertSame(3, $rehearsal['export_summary']['chunk_count']);
        self::assertSame(3, $rehearsal['export_summary']['row_count']);
        self::assertTrue($rehearsal['requires_explicit_local_profile_switch']);
    }

    public function testSample34ValidatorCliReportsReadyContractWithoutMutation(): void
    {
        $script = dirname(__DIR__, 2) . '/mtool/scripts/validate_sample34_firebird_promotion.php';
        $json = shell_exec(PHP_BINARY . ' ' . escapeshellarg($script) . ' --json');
        self::assertIsString($json);
        $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($result['ok'], json_encode($result, JSON_THROW_ON_ERROR));
        self::assertSame('sample34-sqlite-to-firebird-promotion', $result['sample']);
        self::assertSame('promotion-tutorial-sample', $result['structure_type']);
        self::assertSame('preflight', $result['contract']['stage']);
        self::assertSame('firebird_import_rehearsal_ready', $result['import_rehearsal']['stage']);
        self::assertFalse($result['contract']['mutation_performed']);
        self::assertTrue($result['component_status']['target_schema_ok']);
        self::assertTrue($result['component_status']['export_ok']);
        self::assertTrue($result['component_status']['rehearsal_ready']);
        self::assertSame(3, $result['component_status']['export_row_count']);
        self::assertSame(3, $result['component_status']['export_chunk_count']);
        self::assertSame('firebird', $result['component_status']['target_driver']);
        self::assertSame('local_durable_file', $result['component_status']['target_profile']);

        $text = shell_exec(PHP_BINARY . ' ' . escapeshellarg($script));
        self::assertIsString($text);
        self::assertStringContainsString('OK: yes', $text);
        self::assertStringContainsString('Rehearsal stage: firebird_import_rehearsal_ready', $text);
        self::assertStringContainsString('Export rows/chunks: 3/3', $text);
        self::assertStringContainsString('Mutation performed by validator: no', $text);
        self::assertStringContainsString('Requires explicit local profile switch: yes', $text);
    }

    /** @return array<string,mixed> */
    private function fixture(): array
    {
        $path = app_sample_pack_reference_root('sample34-sqlite-to-firebird-promotion') . '/promotion-contract-input.json';
        $json = file_get_contents($path);
        self::assertIsString($json, 'failed to read sample34 fixture');
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
