<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/generated_catalog.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_runtime_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_service.php';

use PHPUnit\Framework\TestCase;

final class SelfGeneratedRuntimeResolverTest extends TestCase
{
    private string $fixtureRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureRoot = sys_get_temp_dir() . '/mtool-runtime-resolver-' . bin2hex(random_bytes(6));
        $this->writeFixtureFiles();
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->fixtureRoot);
        parent::tearDown();
    }

    public function testLayeredRuntimeResolversReadBackingFiles(): void
    {
        $dbaccessCatalog = app_generated_file_method_catalog($this->fixtureRoot . '/dbaccess-Article.php');
        self::assertCount(1, $dbaccessCatalog);
        self::assertSame('GetArticleList', $dbaccessCatalog[0]['name']);

        $plainInfo = app_project_output_runtime_bootstrap_data_file_info(
            $this->fixtureRoot . '/data-Plain.php',
        );
        self::assertTrue($plainInfo['ok']);
        self::assertSame('generated-wrapper-base', $plainInfo['source_layout']);
        self::assertSame('PlainData', $plainInfo['class_name']);
        self::assertSame('', $plainInfo['parent_class']);
        self::assertSame(['ID', 'Title'], $plainInfo['property_names']);
        self::assertSame(1, $plainInfo['class_count']);
        self::assertSame([], $plainInfo['extra_method_names']);
        self::assertTrue($plainInfo['is_plain_candidate']);

        $layeredInfo = app_project_output_runtime_bootstrap_data_file_info(
            $this->fixtureRoot . '/data-Complex.php',
        );
        self::assertTrue($layeredInfo['ok']);
        self::assertSame('generated-layered-stub', $layeredInfo['source_layout']);
        self::assertSame('ComplexData', $layeredInfo['class_name']);
        self::assertSame('', $layeredInfo['parent_class']);
        self::assertSame(['ID'], $layeredInfo['property_names']);
        self::assertSame(2, $layeredInfo['class_count']);
        self::assertSame(['Touch'], $layeredInfo['extra_method_names']);
        self::assertFalse($layeredInfo['is_plain_candidate']);

        $plan = app_project_output_runtime_build_plan(
            $this->fixtureRoot,
            [
                ['relative_path' => 'dbaccess-Article.php', 'size' => filesize($this->fixtureRoot . '/dbaccess-Article.php')],
                ['relative_path' => 'base/dbaccess-ArticleBase.php', 'size' => filesize($this->fixtureRoot . '/base/dbaccess-ArticleBase.php')],
                ['relative_path' => 'data-Plain.php', 'size' => filesize($this->fixtureRoot . '/data-Plain.php')],
                ['relative_path' => 'base/data-PlainBase.php', 'size' => filesize($this->fixtureRoot . '/base/data-PlainBase.php')],
                ['relative_path' => 'data-Complex.php', 'size' => filesize($this->fixtureRoot . '/data-Complex.php')],
                ['relative_path' => '_base/data-Complex.php', 'size' => filesize($this->fixtureRoot . '/_base/data-Complex.php')],
                ['relative_path' => '_wrappers/data-Complex.php', 'size' => filesize($this->fixtureRoot . '/_wrappers/data-Complex.php')],
            ],
        );

        self::assertSame([], $plan['layered_files']);
        self::assertCount(7, $plan['passthrough_files']);
    }

    public function testLegacyDbaccessSupportRewriteTurnsGeneratedWrapperIntoStandaloneCompatibilityClass(): void
    {
        $contents = file_get_contents($this->fixtureRoot . '/dbaccess-Article.php');
        self::assertIsString($contents);

        $rewritten = app_project_output_runtime_transform_legacy_dbaccess_support_text(
            $contents,
            'ArticleDBAccess',
            'ArticleDBAccessLegacy',
            'dbaccess-Article.php',
        );

        self::assertStringContainsString(
            'class ArticleDBAccessLegacy',
            $rewritten,
        );
        self::assertStringNotContainsString(
            "require_once __DIR__ . '/_runtime_loader.php';",
            $rewritten,
        );
        self::assertStringNotContainsString(
            "require_once __DIR__ . '/base/dbaccess-ArticleBase.php';",
            $rewritten,
        );
        self::assertStringNotContainsString(
            'mtool_runtime_bundle_load_custom_wrapper(',
            $rewritten,
        );
        self::assertStringNotContainsString(
            'extends ArticleDBAccessBase',
            $rewritten,
        );
    }

    public function testGeneratedDbaccessTextOmitsLegacyParentWhenDelegationIsNotNeeded(): void
    {
        $generated = app_project_output_runtime_generated_dbaccess_text(
            ['source_name' => 'Article'],
            [
                [
                    'function_name' => 'GetArticleList',
                    'function_list_order' => '10',
                    'function_suffix' => '',
                    'action_type' => 'SELECTLIST',
                    'select_by_distinct' => '',
                    'is_blob_target' => '0',
                    'detected_line' => '',
                    'source_of_truth' => 'manual',
                    'updated_at' => '',
                ],
            ],
            [
                'GetArticleList' => [
                    'name' => 'GetArticleList',
                    'line' => 1,
                    'end_line' => 3,
                    'signature' => 'public function GetArticleList()',
                ],
            ],
            [
                'GetArticleList' => [
                    'mode' => 'canonical-sql',
                    'body_lines' => ['        return [];'],
                    'reason' => '',
                    'warning' => '',
                ],
            ],
            [],
            '',
            'ArticleDBAccess',
            '',
        );

        self::assertStringContainsString('class ArticleDBAccess', $generated);
        self::assertStringNotContainsString('../_support/legacy-dbaccess/', $generated);
        self::assertStringNotContainsString('extends ArticleDBAccessLegacy', $generated);
    }

    public function testRuntimeArtifactScopeExcludesApacheHostSettingSources(): void
    {
        $dbaccessEntities = app_project_output_runtime_dbaccess_entities($this->fixtureRoot);
        self::assertSame(['Article'], array_column($dbaccessEntities['entities'], 'source_name'));

        $dataEntities = app_project_output_runtime_data_entities($this->fixtureRoot);
        self::assertSame(['Complex', 'Plain'], array_column($dataEntities['entities'], 'source_name'));

        $filteredFiles = app_project_output_runtime_filter_files([
            ['relative_path' => 'dbaccess-Article.php', 'size' => 100],
            ['relative_path' => 'dbaccess-ApacheHostSetting.php', 'size' => 100],
            ['relative_path' => 'dbaccess-ApacheHostSettingTemplate.php', 'size' => 100],
            ['relative_path' => 'data-Plain.php', 'size' => 100],
            ['relative_path' => 'data-ApacheHostSetting.php', 'size' => 100],
            ['relative_path' => 'data-ApacheHostSettingTemplate.php', 'size' => 100],
            ['relative_path' => 'base/dbaccess-ApacheHostSettingBase.php', 'size' => 100],
            ['relative_path' => 'base/data-ApacheHostSettingTemplateBase.php', 'size' => 100],
            ['relative_path' => '_base/data-ApacheHostSetting.php', 'size' => 100],
            ['relative_path' => '_wrappers/data-ApacheHostSetting.php', 'size' => 100],
            ['relative_path' => '_support/legacy-dbaccess/dbaccess-ApacheHostSetting.php', 'size' => 100],
            ['relative_path' => '_support/legacy-dbaccess/dbaccess-ApacheHostSettingTemplate.php', 'size' => 100],
            ['relative_path' => 'autoload_mtool.php', 'size' => 100],
        ]);
        self::assertSame(
            [
                'dbaccess-Article.php',
                'data-Plain.php',
                'autoload_mtool.php',
            ],
            array_column($filteredFiles, 'relative_path'),
        );

        $autoloadContents = file_get_contents($this->fixtureRoot . '/autoload_mtool.php');
        self::assertIsString($autoloadContents);

        $rewrittenAutoload = app_project_output_runtime_filter_autoload_contents($autoloadContents);
        self::assertStringContainsString('include_once("data-Plain.php");', $rewrittenAutoload);
        self::assertStringContainsString('include_once("dbaccess-Article.php");', $rewrittenAutoload);
        self::assertStringNotContainsString('include_once("data-ApacheHostSetting.php");', $rewrittenAutoload);
        self::assertStringNotContainsString('include_once("data-ApacheHostSettingTemplate.php");', $rewrittenAutoload);
        self::assertStringNotContainsString('include_once("dbaccess-ApacheHostSetting.php");', $rewrittenAutoload);
        self::assertStringNotContainsString('include_once("dbaccess-ApacheHostSettingTemplate.php");', $rewrittenAutoload);
    }

    public function testRuntimeAutoloadRegistryPreloadsFunctionFilesAndLazyLoadsClasses(): void
    {
        $suffix = strtoupper(bin2hex(random_bytes(4)));
        $lazySourceName = 'Lazy' . $suffix;
        $lazyClassName = $lazySourceName . 'Data';
        $helperSourceName = 'Helper' . $suffix;
        $helperClassName = $helperSourceName . 'Data';
        $helperFunctionName = 'fixture_runtime_helper_' . strtolower($suffix);

        file_put_contents(
            $this->fixtureRoot . '/data-' . $lazySourceName . '.php',
            <<<PHP
<?php

class {$lazyClassName}
{
}

?>
PHP,
        );
        file_put_contents(
            $this->fixtureRoot . '/data-' . $helperSourceName . '.php',
            <<<PHP
<?php

function {$helperFunctionName}(): string
{
    return 'ok';
}

class {$helperClassName}
{
}

?>
PHP,
        );

        app_project_output_runtime_rewrite_autoload_file(
            $this->fixtureRoot . '/autoload_mtool.php',
            $this->fixtureRoot,
        );

        $autoloadContents = file_get_contents($this->fixtureRoot . '/autoload_mtool.php');
        self::assertIsString($autoloadContents);
        self::assertStringContainsString('// == START OF GENERATED RUNTIME AUTOLOAD ==', $autoloadContents);
        self::assertStringNotContainsString('include_once("data-Plain.php");', $autoloadContents);
        self::assertStringNotContainsString('include_once("dbaccess-Article.php");', $autoloadContents);

        require_once $this->fixtureRoot . '/autoload_mtool.php';

        self::assertTrue(function_exists($helperFunctionName));
        self::assertSame('ok', $helperFunctionName());
        self::assertTrue(class_exists($helperClassName, false));
        self::assertFalse(class_exists($lazyClassName, false));
        self::assertTrue(class_exists($lazyClassName));
    }

    private function writeFixtureFiles(): void
    {
        mkdir($this->fixtureRoot . '/base', 0777, true);
        mkdir($this->fixtureRoot . '/_base', 0777, true);
        mkdir($this->fixtureRoot . '/_wrappers', 0777, true);
        mkdir($this->fixtureRoot . '/_support/legacy-dbaccess', 0777, true);

        file_put_contents(
            $this->fixtureRoot . '/dbaccess-Article.php',
            <<<'PHP'
<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-ArticleBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-Article.php')) {
    class ArticleDBAccess extends ArticleDBAccessBase
    {
    }
}

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/base/dbaccess-ArticleBase.php',
            <<<'PHP'
<?php

class ArticleDBAccessBase
{
    public function GetArticleList()
    {
        return [];
    }
}

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/data-Plain.php',
            <<<'PHP'
<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-PlainBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Plain.php')) {
    class PlainData extends PlainDataBase
    {
    }
}

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/base/data-PlainBase.php',
            <<<'PHP'
<?php

class PlainDataBase
{
    public $ID;
    public $Title;

    public function __construct()
    {
    }
}

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/data-Complex.php',
            <<<'PHP'
<?php

require_once __DIR__ . '/_runtime_loader.php';
mtool_runtime_bundle_load_layered_file('data-Complex.php');

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/_base/data-Complex.php',
            <<<'PHP'
<?php

class ComplexDataBase
{
    public $ID;

    public function __construct()
    {
    }

    public function Touch()
    {
    }
}

class ComplexSupportData
{
    public $Ignored;
}

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/_wrappers/data-Complex.php',
            <<<'PHP'
<?php

class ComplexData extends ComplexDataBase
{
}

?>
PHP,
        );

        file_put_contents(
            $this->fixtureRoot . '/dbaccess-ApacheHostSetting.php',
            "<?php\n\nclass ApacheHostSettingDBAccess\n{\n}\n",
        );

        file_put_contents(
            $this->fixtureRoot . '/dbaccess-ApacheHostSettingTemplate.php',
            "<?php\n\nclass ApacheHostSettingTemplateDBAccess\n{\n}\n",
        );

        file_put_contents(
            $this->fixtureRoot . '/data-ApacheHostSetting.php',
            "<?php\n\nclass ApacheHostSettingData\n{\n}\n",
        );

        file_put_contents(
            $this->fixtureRoot . '/data-ApacheHostSettingTemplate.php',
            "<?php\n\nclass ApacheHostSettingTemplateData\n{\n}\n",
        );

        file_put_contents(
            $this->fixtureRoot . '/autoload_mtool.php',
            <<<'PHP'
<?php

include_once("data-Plain.php");
include_once("data-ApacheHostSetting.php");
include_once("data-ApacheHostSettingTemplate.php");
include_once("dbaccess-Article.php");
include_once("dbaccess-ApacheHostSetting.php");
include_once("dbaccess-ApacheHostSettingTemplate.php");
PHP,
        );

        file_put_contents($this->fixtureRoot . '/_runtime_loader.php', "<?php\n");
    }

    private function removeTree(string $path): void
    {
        if ($path === '' || !file_exists($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($path);
    }
}
