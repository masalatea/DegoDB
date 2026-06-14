<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample21ProjectMethodAndEnumOutputTest extends TestCase
{
    public function testReferenceOutputsStayInSync(): void
    {
        $result = app_sample21_project_run(
            'phpunit',
            app_sample21_project_default_reference_root(),
        );

        self::assertTrue(
            $result['ok'],
            $this->failureMessageFromResult($result),
        );

        $fileChecks = $result['file_checks'] ?? [];
        self::assertIsArray($fileChecks, 'file_checks is not an array');

        foreach ($fileChecks as $fileCheck) {
            self::assertIsArray($fileCheck, 'file_check entry is not an array');

            $relativePath = (string) ($fileCheck['relative_path'] ?? '');
            self::assertTrue(
                (bool) ($fileCheck['ok'] ?? false),
                'generated file does not match reference: DATACLASS-PHP/' . $relativePath,
            );
        }
    }

    /**
     * @param array<string,mixed> $result
     */
    private function failureMessageFromResult(array $result): string
    {
        $payload = [
            'error' => $result['error'] ?? '',
            'assertion_errors' => $result['assertion_errors'] ?? [],
            'input_file' => $result['input_file'] ?? '',
            'published_root' => $result['published_root'] ?? '',
            'reference_root' => $result['reference_root'] ?? '',
            'file_checks' => $result['file_checks'] ?? [],
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample21 Project method-and-enum verification returned ok=false';
    }
}
