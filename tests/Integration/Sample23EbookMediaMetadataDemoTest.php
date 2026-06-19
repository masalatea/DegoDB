<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample23EbookMediaMetadataDemoTest extends TestCase
{
    public function testEbookMediaMetadataReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $result = app_sample23_ebook_media_metadata_run(
            $app,
            'phpunit-sample23',
            app_sample23_ebook_media_metadata_default_reference_root(),
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
                : 'sample23 ebook media metadata verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(3, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
    }
}
