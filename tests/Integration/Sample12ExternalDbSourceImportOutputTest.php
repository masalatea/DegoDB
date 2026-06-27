<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample12ExternalDbSourceImportOutputTest extends TestCase
{
    public function testExternalSourceImportOutputStaysInSync(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample12_external_db_run(
                app_bootstrap(),
                'phpunit',
                app_sample12_external_db_default_reference_root(),
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        self::assertTrue($result['ok'], $this->failureMessageFromResult($result));
        self::assertSame('sample12_lab', (string) ($result['steps']['database_source']['source_key'] ?? ''));
        self::assertSame(1, (int) ($result['steps']['table_preview_after_import']['summary']['source_table_count'] ?? 0));
        self::assertSame(1, (int) ($result['steps']['data_class_preview_after_sync']['summary']['canonical_data_class_count'] ?? 0));

        foreach ($result['steps']['output']['file_checks'] ?? [] as $fileCheck) {
            self::assertIsArray($fileCheck);
            self::assertTrue(
                (bool) ($fileCheck['ok'] ?? false),
                'generated file does not match reference: '
                . (string) ($fileCheck['relative_path'] ?? ''),
            );
        }
    }

    private function failureMessageFromResult(array $result): string
    {
        $payload = [
            'error' => $result['error'] ?? '',
            'assertion_errors' => $result['assertion_errors'] ?? [],
            'database_source' => $result['steps']['database_source'] ?? null,
            'table_import' => $result['steps']['table_import'] ?? null,
            'table_preview_after_import' => $result['steps']['table_preview_after_import'] ?? null,
            'data_class_sync' => $result['steps']['data_class_sync'] ?? null,
            'data_class_preview_after_sync' => $result['steps']['data_class_preview_after_sync'] ?? null,
            'output' => $result['steps']['output'] ?? null,
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample12 external DB source import verification returned ok=false';
    }
}
