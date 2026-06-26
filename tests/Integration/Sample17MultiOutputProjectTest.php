<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample17MultiOutputProjectTest extends TestCase
{
    public function testMultiOutputProjectReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $result = app_sample17_multi_output_run(
            $app,
            'phpunit-sample17',
            app_sample17_multi_output_default_reference_root(),
        );

        if (!$result['ok']) {
            fwrite(
                STDERR,
                json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            );
        }

        self::assertTrue(
            $result['ok'],
            is_string($result['error'] ?? null) && $result['error'] !== ''
                ? $result['error']
                : 'sample17 multi-output verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(6, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('HTML-PAGE', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
        self::assertArrayHasKey('AI-CONTEXT-MD', $result['steps']['outputs']);
        self::assertArrayHasKey('MODERNIZATION-AUDIT-MD', $result['steps']['outputs']);
        self::assertSame(
            ['CapstoneTask'],
            $result['steps']['outputs']['AI-CONTEXT-MD']['ai_context']['tables'] ?? [],
        );
        self::assertSame(
            ['CapstoneTask'],
            $result['steps']['outputs']['MODERNIZATION-AUDIT-MD']['modernization_audit']['review_order'] ?? [],
        );
    }
}
