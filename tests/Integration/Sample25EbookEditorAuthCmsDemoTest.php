<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Sample25EbookEditorAuthCmsDemoTest extends TestCase
{
    public function testEbookEditorAuthCmsReferenceOutputs(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        $app = app_bootstrap();
        try {
            $result = app_sample25_ebook_editor_auth_cms_run(
                $app,
                'phpunit-sample25',
                app_sample25_ebook_editor_auth_cms_default_reference_root(),
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
                : 'sample25 ebook editor auth cms verification returned ok=false',
        );
        self::assertSame([], $result['assertion_errors']);
        self::assertCount(4, $result['steps']['outputs']);
        self::assertArrayHasKey('DATACLASS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('DBACCESS-PHP', $result['steps']['outputs']);
        self::assertArrayHasKey('OPENAPI-JSON', $result['steps']['outputs']);
        self::assertArrayHasKey('AUTH-PROXY-SERVER', $result['steps']['outputs']);
    }
}
