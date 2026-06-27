<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample4DataclassParentChildBasicOutputTest extends TestCase
{
    public function testReferenceOutputsStayInSync(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample4_dataclass_run(
                app_bootstrap(),
                'phpunit',
                app_sample4_dataclass_default_reference_root(),
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        self::assertTrue(
            $result['ok'],
            $this->failureMessageFromResult($result),
        );

        self::assertCount(
            count(APP_SAMPLE4_DATACLASS_TABLE_NAMES),
            $result['steps']['table_imports'],
            'unexpected table import step count in verification result',
        );

        self::assertCount(
            count(APP_SAMPLE4_DATACLASS_TABLE_NAMES),
            $result['steps']['table_previews_after_import'],
            'unexpected table preview step count in verification result',
        );

        self::assertCount(
            count(APP_SAMPLE4_DATACLASS_REFERENCE_SOURCE_OUTPUT_KEYS),
            $result['steps']['outputs'],
            'unexpected source output count in verification result',
        );

        foreach ($result['steps']['outputs'] as $output) {
            $sourceOutputKey = (string) ($output['source_output_key'] ?? '');
            $fileChecks = $output['file_checks'] ?? [];

            self::assertIsArray($fileChecks, 'file_checks is not an array: ' . $sourceOutputKey);

            foreach ($fileChecks as $fileCheck) {
                self::assertIsArray($fileCheck, 'file_check entry is not an array: ' . $sourceOutputKey);

                $relativePath = (string) ($fileCheck['relative_path'] ?? '');
                self::assertTrue(
                    (bool) ($fileCheck['ok'] ?? false),
                    'generated file does not match reference: '
                    . $sourceOutputKey
                    . '/'
                    . $relativePath,
                );
            }
        }
    }

    private function failureMessageFromResult(array $result): string
    {
        $payload = [
            'error' => $result['error'] ?? '',
            'assertion_errors' => $result['assertion_errors'] ?? [],
            'table_imports' => $result['steps']['table_imports'] ?? [],
            'table_previews_after_import' => $result['steps']['table_previews_after_import'] ?? [],
            'data_class_preview_after_sync' => $result['steps']['data_class_preview_after_sync'] ?? null,
            'outputs' => array_map(
                static function ($output): array {
                    if (!is_array($output)) {
                        return ['invalid_output' => true];
                    }

                    return [
                        'source_output_key' => $output['source_output_key'] ?? '',
                        'published_root' => $output['published_root'] ?? '',
                        'reference_root' => $output['reference_root'] ?? '',
                        'file_checks' => $output['file_checks'] ?? [],
                    ];
                },
                is_array($result['steps']['outputs'] ?? null) ? $result['steps']['outputs'] : [],
            ),
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample4 dataclass verification returned ok=false';
    }
}
