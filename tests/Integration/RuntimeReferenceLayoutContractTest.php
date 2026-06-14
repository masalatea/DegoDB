<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_output_service.php';

use PHPUnit\Framework\TestCase;

final class RuntimeReferenceLayoutContractTest extends TestCase
{
    public function testPromotedRuntimeReferenceUsesWrapperBaseLayoutOnly(): void
    {
        $runtimeRoot = dirname(__DIR__, 2) . '/mtool/reference/dbclasses';
        $dataWrapperFiles = glob($runtimeRoot . '/data-*.php') ?: [];
        $dataBaseFiles = glob($runtimeRoot . '/base/data-*Base.php') ?: [];

        self::assertDirectoryExists($runtimeRoot . '/base');
        self::assertFileExists($runtimeRoot . '/data-Project.php');
        self::assertFileExists($runtimeRoot . '/dbaccess-Project.php');
        self::assertFileExists($runtimeRoot . '/base/data-ProjectBase.php');
        self::assertFileExists($runtimeRoot . '/base/dbaccess-ProjectBase.php');
        self::assertFileExists($runtimeRoot . '/autoload_mtool.php');
        self::assertFileExists($runtimeRoot . '/_runtime_loader.php');
        self::assertFileExists($runtimeRoot . '/_support/runtime-generation-manifest.json');
        self::assertDirectoryDoesNotExist($runtimeRoot . '/_base');
        self::assertDirectoryDoesNotExist($runtimeRoot . '/_wrappers');
        self::assertNotSame([], $dataWrapperFiles);
        self::assertNotSame([], $dataBaseFiles);

        foreach ($dataWrapperFiles as $dataFile) {
            $contents = file_get_contents($dataFile);
            self::assertIsString($contents);
            $dataBasename = basename($dataFile);
            $expectedBaseRequire = "require_once __DIR__ . '/base/"
                . preg_replace('/\.php$/', 'Base.php', $dataBasename)
                . "';";
            self::assertStringNotContainsString('mtool_runtime_bundle_load_layered_file(', $contents, $dataBasename);
            self::assertStringContainsString($expectedBaseRequire, $contents, $dataBasename);
            self::assertStringContainsString(
                "mtool_runtime_bundle_load_custom_wrapper('{$dataBasename}')",
                $contents,
                $dataBasename,
            );
        }

        foreach ($dataBaseFiles as $dataBaseFile) {
            $contents = file_get_contents($dataBaseFile);
            self::assertIsString($contents);
            self::assertStringContainsString(
                'AUTO-GENERATED BASE FILE.',
                $contents,
                basename($dataBaseFile),
            );
        }

        foreach (glob($runtimeRoot . '/dbaccess-*.php') ?: [] as $dbaccessFile) {
            $contents = file_get_contents($dbaccessFile);
            self::assertIsString($contents);
            self::assertStringNotContainsString(
                'mtool_runtime_bundle_load_layered_file(',
                $contents,
                basename($dbaccessFile),
            );
        }

        foreach (glob($runtimeRoot . '/base/dbaccess-*Base.php') ?: [] as $dbaccessBaseFile) {
            $contents = file_get_contents($dbaccessBaseFile);
            self::assertIsString($contents);
            self::assertStringNotContainsString(
                "/../_support/legacy-dbaccess/",
                $contents,
                basename($dbaccessBaseFile),
            );
            self::assertDoesNotMatchRegularExpression(
                '/class\s+[A-Za-z0-9_]+Base\s+extends\s+[A-Za-z0-9_]+Legacy\b/',
                $contents,
                basename($dbaccessBaseFile),
            );
        }

        foreach (glob($runtimeRoot . '/_support/legacy-dbaccess/dbaccess-*.php') ?: [] as $legacySupportFile) {
            $contents = file_get_contents($legacySupportFile);
            self::assertIsString($contents);
            self::assertStringNotContainsString(
                "require_once __DIR__ . '/_runtime_loader.php';",
                $contents,
                basename($legacySupportFile),
            );
            self::assertStringNotContainsString(
                "require_once __DIR__ . '/base/",
                $contents,
                basename($legacySupportFile),
            );
            self::assertStringNotContainsString(
                'mtool_runtime_bundle_load_custom_wrapper(',
                $contents,
                basename($legacySupportFile),
            );
            self::assertDoesNotMatchRegularExpression(
                '/class\s+[A-Za-z0-9_]+Legacy\s+extends\s+[A-Za-z0-9_]+Base\b/',
                $contents,
                basename($legacySupportFile),
            );
        }

        $manifestContents = file_get_contents($runtimeRoot . '/_support/runtime-generation-manifest.json');
        self::assertIsString($manifestContents);
        $manifest = json_decode($manifestContents, true);
        self::assertIsArray($manifest);
        $generationSummary = $manifest['generation_summary'] ?? null;
        self::assertIsArray($generationSummary);
        self::assertSame(count($dataWrapperFiles), $generationSummary['canonical_data_class_count'] ?? null);
        self::assertSame(count($dataWrapperFiles), $generationSummary['data_entity_count'] ?? null);
        self::assertSame(0, $generationSummary['bootstrap_data_class_count'] ?? null);

        $dataGenerationItems = $generationSummary['data_generation_items'] ?? null;
        self::assertIsArray($dataGenerationItems);
        self::assertCount(count($dataWrapperFiles), $dataGenerationItems);

        $allowedReasonCodes = array_fill_keys(
            [
                'generated-canonical-plain-dto',
                'generated-layered-runtime-wrapper-base',
                'generated-existing-runtime-wrapper-base',
            ],
            true,
        );
        foreach ($dataGenerationItems as $item) {
            self::assertIsArray($item);
            self::assertSame('generated', (string) ($item['decision'] ?? ''));
            self::assertArrayHasKey(
                (string) ($item['reason_code'] ?? ''),
                $allowedReasonCodes,
                (string) ($item['source_name'] ?? ''),
            );
        }
    }

    public function testRuntimeCustomLayerDocsDescribeLayeredPathsAsHistoricalInputOnly(): void
    {
        $definition = [
            'source_output_key' => 'RUNTIME-DBCLASSES',
            'artifact_strategy' => 'generated-bootstrap-dbclasses',
            'runtime_source_relative_path' => 'mtool/dbclasses',
        ];

        $scaffold = app_project_output_custom_layer_scaffold_text(
            'MTOOL',
            $definition,
            'mtool/extensions/MTOOL/RUNTIME-DBCLASSES',
        );
        self::assertStringContainsString('mtool/dbclasses/base/dbaccess-*Base.php', $scaffold);
        self::assertStringContainsString('mtool/dbclasses/base/data-*Base.php', $scaffold);
        self::assertStringContainsString(
            'Current emitted runtime tree does not include `mtool/dbclasses/_base/` or `mtool/dbclasses/_wrappers/`.',
            $scaffold,
        );
        self::assertStringContainsString('historical self-generated bundle input only', $scaffold);
        self::assertStringNotContainsString(
            'remain only for transition-state non-plain data classes',
            $scaffold,
        );

        $readmePath = dirname(__DIR__, 2) . '/mtool/extensions/MTOOL/RUNTIME-DBCLASSES/README.md';
        $readme = file_get_contents($readmePath);
        self::assertIsString($readme);
        self::assertStringContainsString('docs/internal/generated-code-strategy.md', $readme);
        self::assertStringContainsString(
            'Current emitted runtime tree does not include `mtool/dbclasses/_base/` or `mtool/dbclasses/_wrappers/`.',
            $readme,
        );
        self::assertStringContainsString('historical self-generated bundle input only', $readme);
    }
}
