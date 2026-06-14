<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/language_resource_file_catalog.php';

use PHPUnit\Framework\TestCase;

final class LanguageResourceSourceOutputBindingTest extends TestCase
{
    public function testMtoolFallbackResolvesLanguageResourceSourceOutputsWithoutOverlay(): void
    {
        $expectedKeysByPid = [
            '265' => 'LANGRES-PHP-DEV-LIB',
            '269' => 'LANGRES-PHP-MTOOL-LIB',
            '274' => 'LANGRES-PHP-MATSUESOFT-LIB',
            '279' => 'LANGRES-PHP-JA-WEB-LIB',
            '280' => 'LANGRES-PHP-PUBLIC-WEB-LIB',
            '329' => 'LANGRES-JAVA-MATSUESOFT-COMMON',
            '353' => 'LANGRES-SWIFT-IOS',
            '355' => 'LANGRES-CS-UWP-COMMONSTRINGS',
            '361' => 'LANGRES-CS-DEGODB-RESOURCES',
            '369' => 'LANGRES-PHP-JA-WEB-LIB-ALT',
        ];

        $sourceOutputMap = app_language_resource_file_catalog_source_output_map_for_project('MTOOL', '');
        foreach ($expectedKeysByPid as $legacyPid => $sourceOutputKey) {
            self::assertArrayHasKey($legacyPid, $sourceOutputMap);
            self::assertSame($sourceOutputKey, $sourceOutputMap[$legacyPid]['source_output_key']);
            self::assertNotSame('', (string) ($sourceOutputMap[$legacyPid]['program_language'] ?? ''));
        }

        $referenceResult = app_load_legacy_language_resource_reference('MTOOL');
        self::assertTrue($referenceResult['ok']);
        self::assertIsArray($referenceResult['item']);

        $tree = app_language_resource_file_catalog_build_from_reference(
            $referenceResult['item'],
            $sourceOutputMap,
        );
        $validation = app_language_resource_file_catalog_validate_tree($tree);

        self::assertTrue(
            $validation['ok'],
            json_encode(
                $validation,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            ) ?: 'validation failed',
        );

        $unresolvedWarnings = array_values(
            array_filter(
                is_array($validation['warnings'] ?? null) ? $validation['warnings'] : [],
                static fn ($warning): bool => is_string($warning) && str_contains($warning, 'source_output_key 未解決'),
            ),
        );

        self::assertSame([], $unresolvedWarnings);
        self::assertSame(array_values($expectedKeysByPid), $tree['manifest']['enabled_source_output_keys']);
    }
}
