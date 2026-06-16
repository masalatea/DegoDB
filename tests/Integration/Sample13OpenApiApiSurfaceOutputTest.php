<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample13OpenApiApiSurfaceOutputTest extends TestCase
{
    public function testOpenApiApiSurfaceOutputStaysInSync(): void
    {
        $result = app_sample13_openapi_run(
            app_bootstrap(),
            'phpunit',
            app_sample13_openapi_default_reference_root(),
        );

        self::assertTrue($result['ok'], $this->failureMessageFromResult($result));
        self::assertSame(1, (int) ($result['steps']['table_preview_after_import']['summary']['source_table_count'] ?? 0));
        self::assertSame(1, (int) ($result['steps']['data_class_preview_after_sync']['summary']['canonical_data_class_count'] ?? 0));
        self::assertSame('published-output', (string) ($result['steps']['swagger_spec']['spec_source'] ?? ''));
        self::assertContains('/proxyserver-ApiTask-GetApiTaskList.php', $result['steps']['output']['openapi_paths'] ?? []);
        self::assertContains('/proxyserver-ApiTask-GetApiTask.php', $result['steps']['output']['openapi_paths'] ?? []);
        self::assertContains('ApiTaskData', $result['steps']['output']['schema_keys'] ?? []);

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
            'table_import' => $result['steps']['table_import'] ?? null,
            'table_preview_after_import' => $result['steps']['table_preview_after_import'] ?? null,
            'data_class_sync' => $result['steps']['data_class_sync'] ?? null,
            'data_class_preview_after_sync' => $result['steps']['data_class_preview_after_sync'] ?? null,
            'output' => $result['steps']['output'] ?? null,
            'swagger_spec' => $result['steps']['swagger_spec'] ?? null,
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample13 OpenAPI API surface verification returned ok=false';
    }
}
