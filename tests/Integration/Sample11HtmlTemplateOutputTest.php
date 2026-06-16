<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample11HtmlTemplateOutputTest extends TestCase
{
    public function testReferenceOutputStaysInSync(): void
    {
        $result = app_sample11_html_template_output_run(
            app_bootstrap(),
            'phpunit',
            app_sample11_html_template_output_default_reference_root(),
        );

        self::assertTrue($result['ok'], $this->failureMessageFromResult($result));

        self::assertSame(
            APP_SAMPLE11_HTML_TEMPLATE_OUTPUT_REFERENCE_FILES,
            array_map(
                static fn (array $file): string => (string) ($file['relative_path'] ?? ''),
                $result['steps']['published_snapshot']['files'] ?? [],
            ),
            'unexpected published file list',
        );

        foreach ($result['steps']['file_checks'] ?? [] as $fileCheck) {
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
            'metadata' => $result['steps']['metadata'] ?? null,
            'artifact' => [
                'source_file_count' => $result['steps']['artifact']['source_file_count'] ?? null,
                'runtime_source_relative_path' => $result['steps']['artifact']['runtime_source_relative_path'] ?? null,
            ],
            'published' => $result['steps']['published'] ?? null,
            'published_snapshot' => $result['steps']['published_snapshot'] ?? null,
            'reference_snapshot' => $result['steps']['reference_snapshot'] ?? null,
            'file_checks' => $result['steps']['file_checks'] ?? [],
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample11 HTML template output verification returned ok=false';
    }
}
