<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample19JsonFirstContentModelOutputTest extends TestCase
{
    public function testReferenceOutputsStayInSync(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
        try {
            $result = app_sample19_json_first_content_model_run(
                app_bootstrap(),
                'phpunit',
                app_sample19_json_first_content_model_default_reference_root(),
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
            count(APP_SAMPLE19_JSON_FIRST_CONTENT_MODEL_REFERENCE_SOURCE_OUTPUT_KEYS),
            $result['steps']['outputs'],
            'unexpected source output count in verification result',
        );

        self::assertCount(
            count(APP_SAMPLE19_JSON_FIRST_CONTENT_MODEL_TABLE_NAMES),
            is_array($result['steps']['table_imports'] ?? null)
                ? $result['steps']['table_imports']
                : [],
            'unexpected imported table count in verification result',
        );

        self::assertCount(
            count(APP_SAMPLE19_JSON_FIRST_CONTENT_MODEL_SELECT_TARGET_LABELS),
            is_array($result['steps']['db_access_select_target_fields']['items'] ?? null)
                ? $result['steps']['db_access_select_target_fields']['items']
                : [],
            'unexpected DB Access select target field count in verification result',
        );

        self::assertCount(
            3,
            is_array($result['steps']['db_access_select_wheres']['items'] ?? null)
                ? $result['steps']['db_access_select_wheres']['items']
                : [],
            'unexpected DB Access select where count in verification result',
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
            'table_imports' => $result['steps']['table_imports'] ?? null,
            'data_class_preview_after_sync' => $result['steps']['data_class_preview_after_sync'] ?? null,
            'db_access_class_catalog' => $result['steps']['db_access_class_catalog'] ?? null,
            'db_access_function_catalog' => $result['steps']['db_access_function_catalog'] ?? null,
            'db_access_function' => $result['steps']['db_access_function'] ?? null,
            'db_access_select_target_fields' => $result['steps']['db_access_select_target_fields'] ?? null,
            'db_access_select_wheres' => $result['steps']['db_access_select_wheres'] ?? null,
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
            : 'sample19 json first content model verification returned ok=false';
    }
}
