<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample15ProjectMetadataExportImportTest extends TestCase
{
    public function testProjectMetadataBundleExportImportStaysInSync(): void
    {
        $result = app_sample15_bundle_run(
            app_bootstrap(),
            'phpunit',
            app_sample15_bundle_default_reference_root(),
        );

        self::assertTrue($result['ok'], $this->failureMessageFromResult($result));
        self::assertSame('project-core', (string) ($result['steps']['export']['manifest']['scope'] ?? ''));
        self::assertSame('replace-core', (string) ($result['steps']['preview']['summary']['target_action'] ?? ''));
        self::assertSame('1', (string) ($result['steps']['preview']['summary']['target_exists'] ?? ''));
        self::assertSame('SAMPLE15', (string) ($result['steps']['target_summary']['project_key'] ?? ''));
        self::assertSame(1, (int) ($result['steps']['target_summary']['table_count'] ?? 0));
        self::assertSame(1, (int) ($result['steps']['target_summary']['data_class_count'] ?? 0));
        self::assertSame(2, (int) ($result['steps']['target_summary']['source_output_count'] ?? 0));
    }

    private function failureMessageFromResult(array $result): string
    {
        $encoded = json_encode(
            [
                'error' => $result['error'] ?? '',
                'assertion_errors' => $result['assertion_errors'] ?? [],
                'table_import' => $result['steps']['table_import'] ?? null,
                'data_class_sync' => $result['steps']['data_class_sync'] ?? null,
                'export' => $result['steps']['export'] ?? null,
                'reference_compare' => $result['steps']['reference_compare'] ?? null,
                'preview' => $result['steps']['preview'] ?? null,
                'apply' => $result['steps']['apply'] ?? null,
                'target_summary' => $result['steps']['target_summary'] ?? null,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample15 project metadata export/import verification returned ok=false';
    }
}
