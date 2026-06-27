<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample21EbookCatalogApiDemoTest extends TestCase
{
    public function testEbookCatalogApiReferenceOutputs(): void
    {
        $app = app_bootstrap();
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');
        try {
            $result = app_sample21_ebook_catalog_api_run(
                $app,
                'phpunit-sample21',
                app_sample21_ebook_catalog_api_default_reference_root(),
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

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
                : 'sample21 ebook catalog API verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(3, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
    }
}
