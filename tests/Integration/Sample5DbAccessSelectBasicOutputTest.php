<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample5DbAccessSelectBasicOutputTest extends TestCase
{
    public function testReferenceOutputsStayInSync(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_sample5_dbaccess_select_basic_run(
                app_bootstrap(),
                'phpunit',
                app_sample5_dbaccess_select_basic_default_reference_root(),
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
            count(APP_SAMPLE5_DBACCESS_SELECT_BASIC_REFERENCE_SOURCE_OUTPUT_KEYS),
            $result['steps']['outputs'],
            'unexpected source output count in verification result',
        );

        self::assertCount(
            count(APP_SAMPLE5_DBACCESS_SELECT_BASIC_TARGET_FIELDS),
            is_array($result['steps']['db_access_select_target_fields']['items'] ?? null)
                ? $result['steps']['db_access_select_target_fields']['items']
                : [],
            'unexpected DB Access select target field count in verification result',
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
            : 'sample5 dbaccess select basic verification returned ok=false';
    }
}
