<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/sample_pack_catalog.php';
require_once dirname(__DIR__, 2) . '/mtool/app/language_resource_file_catalog.php';

use PHPUnit\Framework\TestCase;

final class SamplePackCatalogTest extends TestCase
{
    public function testActiveSamplePacksStayUnderCategorizedRoots(): void
    {
        $sampleRoot = app_sample_pack_root();
        $relativePathMap = app_sample_pack_active_relative_path_map();

        self::assertSame('tutorials/sample01-simple-table-runtime', $relativePathMap['sample01-simple-table-runtime'] ?? '');
        self::assertSame('legacy-projects/sample56-runtime-misc-proxy', $relativePathMap['sample56-runtime-misc-proxy'] ?? '');
        self::assertSame(
            'internal-patterns/pattern14-method-and-enum-heavy-multimethod',
            $relativePathMap['pattern14-method-and-enum-heavy-multimethod'] ?? '',
        );

        foreach ($relativePathMap as $packName => $relativePath) {
            $matchesExpectedCategory = preg_match(
                '#^(tutorials/sample[0-9]+-|internal-patterns/pattern[0-9]+-|legacy-projects/sample[0-9]+-)#',
                $relativePath,
            ) === 1;
            self::assertTrue($matchesExpectedCategory, 'unexpected sample pack category path: ' . $packName);

            $absolutePath = $sampleRoot . '/' . $relativePath;
            self::assertDirectoryExists($absolutePath);
            self::assertFileExists($absolutePath . '/README.md');

            $structureType = app_sample_pack_structure_type($packName);
            self::assertNotSame('', $structureType, 'sample pack structure type missing: ' . $packName);

            if ($structureType === 'runtime-pack') {
                self::assertFileExists($absolutePath . '/compose.yaml');
                self::assertFileExists($absolutePath . '/run.sh');
                self::assertDirectoryExists($absolutePath . '/seed');

                $runScript = file_get_contents($absolutePath . '/run.sh');
                self::assertIsString($runScript);
                self::assertStringContainsString(
                    '../../_pack-support/sample-pack-runner.sh',
                    $runScript,
                    'sample pack runner indirection mismatch: ' . $packName,
                );
                continue;
            }

            self::assertContains($structureType, ['file-reference-sample', 'promotion-tutorial-sample', 'app-wrapper-tutorial-sample']);
            if ($structureType === 'app-wrapper-tutorial-sample') {
                self::assertFileExists($absolutePath . '/package.json');
                self::assertFileExists($absolutePath . '/capacitor.config.ts');
                self::assertFileExists($absolutePath . '/scripts/validate-sample.mjs');
                self::assertDirectoryExists($absolutePath . '/src/mtool-artifacts');
                self::assertFileDoesNotExist($absolutePath . '/compose.yaml');
                self::assertFileDoesNotExist($absolutePath . '/run.sh');
                self::assertDirectoryDoesNotExist($absolutePath . '/seed');
                self::assertDirectoryDoesNotExist($absolutePath . '/ios');
                self::assertDirectoryDoesNotExist($absolutePath . '/android');
                continue;
            }

            self::assertDirectoryExists($absolutePath . '/reference');
            self::assertFileDoesNotExist($absolutePath . '/compose.yaml');
            self::assertFileDoesNotExist($absolutePath . '/run.sh');
            self::assertDirectoryDoesNotExist($absolutePath . '/seed');
        }

        $topLevelPackDirs = array_values(
            array_filter(
                glob($sampleRoot . '/sample*') ?: [],
                static function (string $path): bool {
                    if (!is_dir($path)) {
                        return false;
                    }

                    return is_file($path . '/README.md')
                        || is_file($path . '/compose.yaml')
                        || is_file($path . '/run.sh')
                        || is_dir($path . '/reference');
                },
            ),
        );
        self::assertSame([], $topLevelPackDirs, 'active sample pack should not live directly under sample/');
    }

    public function testSupportAndHistoricalRootsExist(): void
    {
        self::assertDirectoryExists(app_sample_pack_support_root());
        self::assertFileExists(app_sample_pack_runner_path());
        self::assertDirectoryExists(app_sample_pack_archive_root());
    }

    public function testCategoryGuideReadmesExist(): void
    {
        $sampleRoot = app_sample_pack_root();

        foreach (['tutorials', 'internal-patterns', 'legacy-projects', 'archive'] as $relativeDir) {
            $absolutePath = $sampleRoot . '/' . $relativeDir;
            self::assertDirectoryExists($absolutePath);
            self::assertFileExists($absolutePath . '/README.md');
        }
    }

    public function testCurrentSamplePackOrderingStaysAlignedWithPackNumbers(): void
    {
        self::assertSame(
            [
                'sample01-simple-table-runtime',
                'sample02-dataclass-nullable-default-status',
                'sample03-dataclass-lookup-and-helper',
                'sample04-dataclass-parent-child-basic',
                'sample05-dbaccess-select-basic',
                'sample06-dbaccess-filter-sort-page',
                'sample07-dbaccess-crud-basic',
                'sample08-dbaccess-join-read-model',
                'sample09-dbaccess-aggregate-report',
                'sample10-dbaccess-mini-crud-flow',
                'sample11-html-template-output',
                'sample12-external-db-source-import',
                'sample13-openapi-api-surface',
                'sample14-custom-proxy-runtime',
                'sample15-project-metadata-export-import',
                'sample16-authenticated-proxy',
                'sample17-multi-output-project',
                'sample18-mini-task-board-demo',
                'sample19-json-first-content-model-demo',
                'sample20-content-publishing-demo',
                'sample21-ebook-catalog-api-demo',
                'sample22-ebook-chapter-workflow-demo',
                'sample23-ebook-media-metadata-demo',
                'sample24-ebook-public-reader-site-demo',
                'sample25-ebook-editor-auth-cms-demo',
                'sample26-ebook-headless-cms-capstone',
                'sample27-app-local-persistence-demo',
                'sample28-no-code-data-app-mvp',
                'sample29-no-code-support-case-demo',
                'sample30-no-code-app-local-sync-demo',
                'sample31-no-code-inventory-request-demo',
                'sample32-no-code-ui-test-lab',
                'sample33-sqlite-to-mysql-promotion',
                'sample34-sqlite-to-firebird-promotion',
                'sample35-capacitor-artifact-import',
            ],
            app_sample_pack_category_map()['tutorials'] ?? [],
        );
        self::assertSame(
            [
                'pattern01-default-property-split',
                'pattern02-wrapper-property-helper',
                'pattern03-method-only-split',
                'pattern04-method-and-enum-basic',
                'pattern05-companion-declarations-basic',
                'pattern06-companion-declarations-no-top-level',
                'pattern07-companion-declarations-multiclass',
                'pattern08-companion-declarations-multi-helper',
                'pattern09-top-level-declaration-single',
                'pattern10-top-level-declaration-multiclass',
                'pattern11-top-level-declaration-html-template',
                'pattern12-method-and-enum-no-top-level',
                'pattern13-method-and-enum-multimethod',
                'pattern14-method-and-enum-heavy-multimethod',
            ],
            app_sample_pack_category_map()['internal-patterns'] ?? [],
        );
        self::assertSame(
            [
                'sample51-runtime-sql-server',
                'sample53-runtime-whiteboard',
                'sample56-runtime-misc-proxy',
            ],
            app_sample_pack_category_map()['legacy-projects'] ?? [],
        );
        self::assertSame(
            [
                'sample01-simple-table-runtime',
                'sample02-dataclass-nullable-default-status',
                'sample03-dataclass-lookup-and-helper',
                'sample04-dataclass-parent-child-basic',
                'sample05-dbaccess-select-basic',
                'sample06-dbaccess-filter-sort-page',
                'sample07-dbaccess-crud-basic',
                'sample08-dbaccess-join-read-model',
                'sample09-dbaccess-aggregate-report',
                'sample10-dbaccess-mini-crud-flow',
                'sample11-html-template-output',
                'sample12-external-db-source-import',
                'sample13-openapi-api-surface',
                'sample14-custom-proxy-runtime',
                'sample15-project-metadata-export-import',
                'sample16-authenticated-proxy',
                'sample17-multi-output-project',
                'sample18-mini-task-board-demo',
                'sample19-json-first-content-model-demo',
                'sample20-content-publishing-demo',
                'sample21-ebook-catalog-api-demo',
                'sample22-ebook-chapter-workflow-demo',
                'sample23-ebook-media-metadata-demo',
                'sample24-ebook-public-reader-site-demo',
                'sample25-ebook-editor-auth-cms-demo',
                'sample26-ebook-headless-cms-capstone',
                'sample27-app-local-persistence-demo',
                'sample28-no-code-data-app-mvp',
                'sample29-no-code-support-case-demo',
                'sample30-no-code-app-local-sync-demo',
                'sample31-no-code-inventory-request-demo',
                'sample32-no-code-ui-test-lab',
                'sample51-runtime-sql-server',
                'sample53-runtime-whiteboard',
                'sample56-runtime-misc-proxy',
            ],
            app_sample_pack_runtime_pack_names(),
        );
    }

    public function testTopLevelSampleReadmeTracksCurrentCatalog(): void
    {
        $readmePath = app_sample_pack_root() . '/README.md';
        $readme = file_get_contents($readmePath);
        self::assertIsString($readme, 'failed to read: ' . $readmePath);

        foreach (['sample/tutorials/', 'sample/internal-patterns/', 'sample/legacy-projects/', 'sample/_pack-support/'] as $needle) {
            self::assertStringContainsString($needle, $readme);
        }

        foreach (array_keys(app_sample_pack_active_relative_path_map()) as $packName) {
            self::assertStringContainsString(
                '`' . $packName . '`',
                $readme,
                'top-level sample README missing active pack listing: ' . $packName,
            );
        }

        self::assertStringContainsString('`tests/Integration/SamplePackCatalogTest.php`', $readme);
        self::assertStringContainsString('`tests/Integration/LegacyProjectSampleCatalogTest.php`', $readme);
    }

    public function testRuntimePackComposeFilesStayAlignedWithCatalog(): void
    {
        foreach (app_sample_pack_runtime_pack_names() as $packName) {
            $relativePath = app_sample_pack_relative_path($packName);
            $composePath = app_sample_pack_absolute_path($packName) . '/compose.yaml';
            $compose = file_get_contents($composePath);
            self::assertIsString($compose, 'failed to read: ' . $composePath);

            self::assertStringContainsString('name: mtool-sample-' . $packName, $compose);
            self::assertSame(
                2,
                substr_count($compose, 'APP_WORK_ROOT: /var/www/work/sample-packs/' . $packName),
                'APP_WORK_ROOT drifted from pack name: ' . $packName,
            );
            self::assertStringContainsString(
                '- ./sample/' . $relativePath . '/seed:/docker-entrypoint-initdb-sample:ro',
                $compose,
                'seed mount drifted from pack catalog path: ' . $packName,
            );
            self::assertStringContainsString(
                '- ./docker/mariadb/config-initdb:/docker-entrypoint-initdb-core:ro',
                $compose,
            );
            self::assertStringContainsString('cp -R /docker-entrypoint-initdb-core/. /docker-entrypoint-initdb.d/', $compose);
            self::assertStringContainsString('cp -R /docker-entrypoint-initdb-sample/. /docker-entrypoint-initdb.d/', $compose);
        }
    }

    public function testRuntimePackRunScriptsStayAlignedWithSharedRunner(): void
    {
        foreach (app_sample_pack_runtime_pack_names() as $packName) {
            $runScriptPath = app_sample_pack_absolute_path($packName) . '/run.sh';
            $runScript = file_get_contents($runScriptPath);
            self::assertIsString($runScript, 'failed to read: ' . $runScriptPath);

            self::assertStringContainsString('#!/usr/bin/env bash', $runScript);
            self::assertStringContainsString('set -euo pipefail', $runScript);
            self::assertStringContainsString('SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"', $runScript);
            self::assertStringContainsString('set -- up', $runScript);
            self::assertStringContainsString(
                'exec "$SCRIPT_DIR/../../_pack-support/sample-pack-runner.sh" "$SCRIPT_DIR" "$@"',
                $runScript,
                'runtime pack runner indirection mismatch: ' . $packName,
            );
        }
    }

    public function testSharedSamplePackRunnerStaysAlignedWithPackLayout(): void
    {
        $runnerPath = app_sample_pack_runner_path();
        $runner = file_get_contents($runnerPath);
        self::assertIsString($runner, 'failed to read: ' . $runnerPath);

        self::assertStringContainsString('usage: sample-pack-runner.sh PACK_DIR {up|down|reset|ps|logs|apply-seed}', $runner);
        self::assertStringContainsString('PACK_COMPOSE="${SAMPLE_PACK_COMPOSE_FILE:-$PACK_DIR/compose.yaml}"', $runner);
        self::assertStringContainsString('PACK_COMPOSE_LANE="${SAMPLE_PACK_COMPOSE_LANE:-local}"', $runner);
        self::assertStringContainsString('PACK_INCLUDE_LIFECYCLE="${SAMPLE_PACK_INCLUDE_LIFECYCLE:-1}"', $runner);
        self::assertStringContainsString('PACK_SEED_DIR="$PACK_DIR/seed"', $runner);
        self::assertStringContainsString('compose_stack_args=(', $runner);
        self::assertStringContainsString('"--lane=$PACK_COMPOSE_LANE"', $runner);
        self::assertStringContainsString('"--compose-file=$PACK_COMPOSE"', $runner);
        self::assertStringContainsString('bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" "${compose_stack_args[@]}"', $runner);
        self::assertStringContainsString('"--compose-file=sample/_pack-support/sample-pack-lifecycle.compose.yaml"', $runner);
        self::assertStringContainsString('exec bash "$REPO_ROOT/mtool/scripts/apply_config_sample_seed.sh" \\', $runner);
        self::assertStringContainsString('"--compose-file=$PACK_COMPOSE" \\', $runner);
        self::assertStringContainsString('"$PACK_SEED_DIR" \\', $runner);
    }

    public function testComposeStackHelperKeepsLocalOverlayAsDefaultLane(): void
    {
        $helper = file_get_contents(dirname(__DIR__, 2) . '/mtool/scripts/list_compose_stack_files.sh');
        self::assertIsString($helper, 'failed to read compose stack helper');

        self::assertStringContainsString('usage: list_compose_stack_files.sh [--lane=local|base] [--compose-file=FILE ...]', $helper);
        self::assertStringContainsString('lane="local"', $helper);
        self::assertStringContainsString('resolved_files=("$REPO_ROOT/compose.yaml")', $helper);
        self::assertStringContainsString('resolved_files+=("$REPO_ROOT/compose.local-db-config.yaml")', $helper);
        self::assertStringContainsString('unsupported compose lane: $lane', $helper);
    }

    public function testRuntimePackReadmesKeepLocalOverlayForManualComposeLane(): void
    {
        foreach (app_sample_pack_runtime_pack_names() as $packName) {
            $readmePath = app_sample_pack_absolute_path($packName) . '/README.md';
            $readme = file_get_contents($readmePath);
            self::assertIsString($readme, 'failed to read: ' . $readmePath);

            if (!str_contains($readme, 'docker compose -f compose.yaml')) {
                continue;
            }

            self::assertStringContainsString(
                'compose.local-db-config.yaml',
                $readme,
                'runtime pack README manual compose lane is missing local overlay: ' . $packName,
            );
        }
    }

    public function testReferenceOnlySamplesResolveCuratedFixtureInputs(): void
    {
        $fixtureRoot = app_runtime_storage_legacy_dbclasses_fixture_root();
        $fixtureMap = app_sample_pack_fixture_relative_path_map();

        self::assertSame(
            app_sample_pack_reference_only_sample_names(),
            array_keys($fixtureMap),
            'fixture catalog must cover every file-reference sample exactly once',
        );

        foreach ($fixtureMap as $packName => $relativePaths) {
            self::assertSame('file-reference-sample', app_sample_pack_structure_type($packName));
            self::assertNotSame([], $relativePaths, 'fixture input missing: ' . $packName);

            $absolutePaths = app_sample_pack_fixture_absolute_paths($packName);
            self::assertCount(count($relativePaths), $absolutePaths);

            foreach ($relativePaths as $index => $relativePath) {
                self::assertStringEndsWith('.php', $relativePath, 'unexpected fixture extension: ' . $packName);

                $absolutePath = $absolutePaths[$index] ?? '';
                self::assertStringStartsWith(
                    $fixtureRoot . '/',
                    $absolutePath,
                    'fixture path must stay under tests/fixtures/legacy-dbclasses: ' . $packName,
                );
                self::assertFileExists(
                    $absolutePath,
                    'fixture input missing: ' . $packName . ' -> ' . $relativePath,
                );
            }
        }

        foreach (app_sample_pack_runtime_pack_names() as $packName) {
            self::assertSame([], app_sample_pack_fixture_relative_paths($packName));
            self::assertSame([], app_sample_pack_fixture_absolute_paths($packName));
        }
        foreach (app_sample_pack_promotion_tutorial_sample_names() as $packName) {
            self::assertSame([], app_sample_pack_fixture_relative_paths($packName));
            self::assertSame([], app_sample_pack_fixture_absolute_paths($packName));
        }
    }

    public function testReferenceOnlySamplesHaveDedicatedOutputTests(): void
    {
        $integrationRoot = dirname(__FILE__);
        $expectedTestFileMap = [
            'pattern01-default-property-split' => 'Sample9TestPatternDefaultPropertyOutputTest.php',
            'pattern02-wrapper-property-helper' => 'Sample12DbtablecolumnsWrapperPropertyOutputTest.php',
            'pattern03-method-only-split' => 'Sample11DaDataclassMethodOnlyOutputTest.php',
            'pattern04-method-and-enum-basic' => 'Sample13ReqMethodAndEnumOutputTest.php',
            'pattern05-companion-declarations-basic' => 'Sample10CompareOutputCompanionDeclarationsOutputTest.php',
            'pattern06-companion-declarations-no-top-level' => 'Sample15BuildLogCompanionDeclarationsOutputTest.php',
            'pattern07-companion-declarations-multiclass' => 'Sample16LiveCheckResultCompanionDeclarationsOutputTest.php',
            'pattern08-companion-declarations-multi-helper' => 'Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php',
            'pattern09-top-level-declaration-single' => 'Sample17SpecContentTopLevelDeclarationOutputTest.php',
            'pattern10-top-level-declaration-multiclass' => 'Sample18ProjectUserTopLevelDeclarationOutputTest.php',
            'pattern11-top-level-declaration-html-template' => 'Sample19HtmlTemplateTopLevelDeclarationOutputTest.php',
            'pattern12-method-and-enum-no-top-level' => 'Sample20DaCustomProxyMethodAndEnumOutputTest.php',
            'pattern13-method-and-enum-multimethod' => 'Sample21ProjectMethodAndEnumOutputTest.php',
            'pattern14-method-and-enum-heavy-multimethod' => 'Sample22ProjectSourceOutputMethodAndEnumOutputTest.php',
        ];

        self::assertSame(
            app_sample_pack_reference_only_sample_names(),
            array_keys($expectedTestFileMap),
            'output test coverage map must cover every file-reference sample exactly once',
        );

        foreach ($expectedTestFileMap as $packName => $testFile) {
            self::assertFileExists(
                $integrationRoot . '/' . $testFile,
                'missing dedicated output test for ' . $packName,
            );
        }

        self::assertFileExists($integrationRoot . '/LegacyTopLevelDeclarationMigrationTest.php');
    }

    public function testLanguageResourceSampleRootsResolveFromPackCatalog(): void
    {
        self::assertSame([], app_language_resource_file_catalog_sample_pack_name_map());
        self::assertSame('', app_language_resource_file_catalog_default_overlay_seed_path('MTOOL'));
        self::assertSame(
            dirname(__DIR__, 2) . '/mtool/resources',
            app_language_resource_file_catalog_default_root('MTOOL'),
        );
    }
}
