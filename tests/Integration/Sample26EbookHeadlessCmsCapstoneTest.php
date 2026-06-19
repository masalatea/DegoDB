<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample26EbookHeadlessCmsCapstoneTest extends TestCase
{
    public function testEbookHeadlessCmsCapstoneReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $result = app_sample26_ebook_headless_cms_capstone_run(
            $app,
            'phpunit-sample26',
            app_sample26_ebook_headless_cms_capstone_default_reference_root(),
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
                : 'sample26 ebook headless cms capstone verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(5, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('HTML-PAGE', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
        self::assertArrayHasKey('AUTH-PROXY-SERVER', $result['steps']['outputs']);
        self::assertIsArray($result['steps']['metadata_bundle']);
        self::assertTrue((bool) ($result['steps']['metadata_bundle']['manifest_ok'] ?? false));
        self::assertTrue((bool) ($result['steps']['metadata_bundle']['sections_ok'] ?? false));
    }
}
