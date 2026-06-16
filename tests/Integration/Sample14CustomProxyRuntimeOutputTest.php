<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample14CustomProxyRuntimeOutputTest extends TestCase
{
    public function testCustomProxyRuntimeOutputStaysInSync(): void
    {
        $result = app_sample14_custom_proxy_run(
            app_bootstrap(),
            'phpunit',
            app_sample14_custom_proxy_default_reference_root(),
        );

        self::assertTrue($result['ok'], $this->failureMessageFromResult($result));
        self::assertSame('NoSecurity', (string) ($result['steps']['custom_proxy']['item']['auth_type'] ?? ''));
        self::assertSame(2, (int) ($result['steps']['custom_proxy']['item']['step_count'] ?? 0));
        self::assertSame(1, (int) ($result['steps']['custom_proxy']['item']['target_count'] ?? 0));
        self::assertGreaterThan(0, (int) ($result['steps']['output']['published_file_count'] ?? 0));
        self::assertSame(
            ['custom_proxy_count' => 1, 'step_count' => 2, 'unresolved_step_count' => 0],
            $result['steps']['output']['build_plan_summary'] ?? [],
        );

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
            'custom_proxy' => $result['steps']['custom_proxy'] ?? null,
            'custom_proxy_steps' => $result['steps']['custom_proxy_steps'] ?? null,
            'custom_proxy_targets' => $result['steps']['custom_proxy_targets'] ?? null,
            'output' => $result['steps']['output'] ?? null,
        ];

        $encoded = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        return is_string($encoded) && $encoded !== ''
            ? $encoded
            : 'sample14 custom proxy runtime verification returned ok=false';
    }
}
