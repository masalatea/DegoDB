<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_runtime_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_service.php';

use PHPUnit\Framework\TestCase;

final class LegacyTopLevelDeclarationMigrationTest extends TestCase
{
    public function testProjectMethodAndEnumMigrationKeepsEnumsInBase(): void
    {
        $support = $this->legacyMigrationSupport('Project');
        self::assertTrue((bool) ($support['supports_legacy_method_and_enum_migration'] ?? false));

        $migration = $support['migration_info'];
        $baseText = app_project_output_generated_legacy_data_class_base_php_text(
            'ProjectDataBase',
            (string) ($migration['parent_class'] ?? ''),
            $migration['generated_property_names'] ?? [],
            (string) ($migration['generated_trailing_section'] ?? ''),
        );

        self::assertStringContainsString('class ProjectStorageTypeEnum', $baseText);
        self::assertStringContainsString('class ProjectDBTypeEnum', $baseText);
    }

    public function testProjectUserTopLevelDeclarationMigrationSplitsBottomClassAndFunctions(): void
    {
        $support = $this->legacyMigrationSupport('ProjectUser');
        self::assertTrue((bool) ($support['supports_legacy_top_level_declaration_migration'] ?? false));

        $migration = $support['migration_info'];
        $wrapperText = app_project_output_runtime_data_wrapper_text(
            'data-ProjectUser.php',
            'base/data-ProjectUserBase.php',
            'ProjectUserData',
            'ProjectUserDataBase',
            'mtool/extensions/MTOOL/RUNTIME-DBCLASSES',
            (string) ($migration['editable_areas']['above'] ?? ''),
            (string) ($migration['editable_areas']['additional_class_definition'] ?? ''),
            (string) ($migration['generated_wrapper_bottom_section'] ?? ''),
        );
        $baseText = app_project_output_generated_legacy_data_class_base_php_text(
            'ProjectUserDataBase',
            (string) ($migration['parent_class'] ?? ''),
            $migration['generated_property_names'] ?? [],
            (string) ($migration['generated_base_additional_section'] ?? ''),
        );

        self::assertStringContainsString('function GetProjectUserSerurityCaption', $wrapperText);
        self::assertStringNotContainsString('class ProjectUserSerurityEnum', $wrapperText);
        self::assertStringContainsString('class ProjectUserSerurityEnum', $baseText);
        self::assertStringContainsString('class ProjectUserIsOwnerEnum', $baseText);
    }

    public function testSpecContentTopLevelDeclarationMigrationKeepsMethodAndHelper(): void
    {
        $support = $this->legacyMigrationSupport('SpecContent');
        self::assertTrue((bool) ($support['supports_legacy_top_level_declaration_migration'] ?? false));

        $migration = $support['migration_info'];
        $wrapperText = app_project_output_runtime_data_wrapper_text(
            'data-SpecContent.php',
            'base/data-SpecContentBase.php',
            'SpecContentData',
            'SpecContentDataBase',
            'mtool/extensions/MTOOL/RUNTIME-DBCLASSES',
            (string) ($migration['editable_areas']['above'] ?? ''),
            (string) ($migration['editable_areas']['additional_class_definition'] ?? ''),
            (string) ($migration['generated_wrapper_bottom_section'] ?? ''),
        );
        $baseText = app_project_output_generated_legacy_data_class_base_php_text(
            'SpecContentDataBase',
            (string) ($migration['parent_class'] ?? ''),
            $migration['generated_property_names'] ?? [],
            (string) ($migration['generated_base_additional_section'] ?? ''),
        );

        self::assertStringContainsString('public function GetDepthCaption()', $wrapperText);
        self::assertStringContainsString('function GetDepthCaptionCommon', $wrapperText);
        self::assertStringNotContainsString('function GetDepthCaptionCommon', $baseText);
    }

    public function testHtmlTemplateTopLevelDeclarationMigrationMovesSupportClassToBase(): void
    {
        $support = $this->legacyMigrationSupport('htmlTemplate');
        self::assertTrue((bool) ($support['supports_legacy_top_level_declaration_migration'] ?? false));

        $migration = $support['migration_info'];
        self::assertNotContains('htmlTemplate', $migration['generated_property_names'] ?? []);
        self::assertNotContains('ChildList', $migration['generated_property_names'] ?? []);
        $wrapperText = app_project_output_runtime_data_wrapper_text(
            'data-htmlTemplate.php',
            'base/data-htmlTemplateBase.php',
            'htmlTemplateData',
            'htmlTemplateDataBase',
            'mtool/extensions/MTOOL/RUNTIME-DBCLASSES',
            (string) ($migration['editable_areas']['above'] ?? ''),
            (string) ($migration['editable_areas']['additional_class_definition'] ?? ''),
            (string) ($migration['generated_wrapper_bottom_section'] ?? ''),
        );
        $baseText = app_project_output_generated_legacy_data_class_base_php_text(
            'htmlTemplateDataBase',
            (string) ($migration['parent_class'] ?? ''),
            $migration['generated_property_names'] ?? [],
            (string) ($migration['generated_base_additional_section'] ?? ''),
        );

        self::assertStringContainsString('function MakehtmlTemplateTree', $wrapperText);
        self::assertStringNotContainsString('class SortedhtmlTemplateDataContainer', $wrapperText);
        self::assertStringContainsString('class SortedhtmlTemplateDataContainer', $baseText);
        self::assertStringContainsString('class htmlTemplateTargetTypeEnum', $baseText);
        self::assertStringContainsString('class htmlTemplateProgramLanguageEnum', $baseText);
    }

    public function testBuildPlanUpgradesTopLevelDeclarationMigrationSources(): void
    {
        $sourceRoot = sys_get_temp_dir() . '/mtool-top-level-build-plan-' . bin2hex(random_bytes(6));
        mkdir($sourceRoot . '/_base', 0777, true);
        mkdir($sourceRoot . '/_wrappers', 0777, true);

        try {
            file_put_contents(
                $sourceRoot . '/data-ProjectUser.php',
                <<<'PHP'
<?php

require_once __DIR__ . '/_runtime_loader.php';
mtool_runtime_bundle_load_layered_file('data-ProjectUser.php');

?>
PHP,
            );

            $legacySourcePath = app_runtime_storage_legacy_dbclasses_fixture_path('data-ProjectUser.php');
            $legacySourceContents = file_get_contents($legacySourcePath);
            self::assertIsString($legacySourceContents);

            $baseContents = preg_replace(
                '/^class\s+ProjectUserData\b/m',
                'class ProjectUserDataBase',
                $legacySourceContents,
                1,
                $replacementCount,
            );
            self::assertIsString($baseContents);
            self::assertSame(1, $replacementCount);

            file_put_contents($sourceRoot . '/_base/data-ProjectUser.php', $baseContents);
            file_put_contents(
                $sourceRoot . '/_wrappers/data-ProjectUser.php',
                <<<'PHP'
<?php

class ProjectUserData extends ProjectUserDataBase
{
}

?>
PHP,
            );

            $files = [
                ['relative_path' => 'data-ProjectUser.php', 'size' => filesize($sourceRoot . '/data-ProjectUser.php')],
                ['relative_path' => '_base/data-ProjectUser.php', 'size' => filesize($sourceRoot . '/_base/data-ProjectUser.php')],
                ['relative_path' => '_wrappers/data-ProjectUser.php', 'size' => filesize($sourceRoot . '/_wrappers/data-ProjectUser.php')],
            ];

            $plan = app_project_output_runtime_build_plan($sourceRoot, $files);

            self::assertSame(['data-ProjectUser.php'], array_column($plan['layered_files'], 'relative_path'));
            self::assertSame([], array_column($plan['passthrough_files'], 'relative_path'));
        } finally {
            $this->removeTree($sourceRoot);
        }
    }

    /**
     * @return array{
     *     migration_info:array<string,mixed>,
     *     supports_legacy_method_and_enum_migration:bool,
     *     supports_legacy_top_level_declaration_migration:bool
     * }
     */
    private function legacyMigrationSupport(string $sourceName): array
    {
        $path = app_runtime_storage_legacy_dbclasses_fixture_path('data-' . $sourceName . '.php');
        $bootstrapInfo = app_project_output_runtime_bootstrap_data_file_info($path);
        self::assertTrue((bool) ($bootstrapInfo['ok'] ?? false), 'bootstrap info failed for ' . $sourceName);

        $support = app_project_output_runtime_data_legacy_migration_support($path, $bootstrapInfo);
        self::assertIsArray($support['migration_info'] ?? null, 'migration_info missing for ' . $sourceName);

        return $support;
    }

    private function removeTree(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            @unlink($path);

            return;
        }

        $entries = scandir($path);
        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $this->removeTree($path . '/' . $entry);
            }
        }

        @rmdir($path);
    }
}
