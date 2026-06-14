<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample11DaDataclassMethodOnlyOutputTest extends TestCase
{
    public function testReferenceOutputsStayInSync(): void
    {
        $result = app_sample11_da_dataclass_method_only_run(
            'phpunit',
            app_sample11_da_dataclass_method_only_default_reference_root(),
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
            'input_files' => $result['input_files'] ?? [],
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
            : 'sample11 da / dataclass method-only verification returned ok=false';
    }
}
