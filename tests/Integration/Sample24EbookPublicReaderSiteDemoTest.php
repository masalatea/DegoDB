<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample24EbookPublicReaderSiteDemoTest extends TestCase
{
    public function testEbookPublicReaderSiteReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $result = app_sample24_ebook_public_reader_site_run(
            $app,
            'phpunit-sample24',
            app_sample24_ebook_public_reader_site_default_reference_root(),
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
                : 'sample24 ebook public reader site verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(4, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('HTML-PAGE', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
    }
}
