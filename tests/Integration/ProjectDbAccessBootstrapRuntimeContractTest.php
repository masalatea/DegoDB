<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_db_access_bootstrap_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_proxy_generator.php';

use PHPUnit\Framework\TestCase;

final class ProjectDbAccessBootstrapRuntimeContractTest extends TestCase
{
    private array $temporaryPaths = [];

    protected function tearDown(): void
    {
        foreach (array_reverse($this->temporaryPaths) as $path) {
            $this->removeTree($path);
        }
        $this->temporaryPaths = [];
    }

    public function testCanonicalBootstrapBodyLinesUseExecutablePhpConcatenation(): void
    {
        $table = [
            'name' => 'lab_experiments',
            'columns' => [
                [
                    'name' => 'id',
                    'datatype' => 'int',
                    'is_key' => 'PRI',
                    'extra' => 'auto_increment',
                    'column_list_order' => 1,
                ],
                [
                    'name' => 'name',
                    'datatype' => 'varchar',
                    'is_key' => '',
                    'extra' => '',
                    'column_list_order' => 2,
                ],
            ],
        ];

        $selectLines = app_project_db_access_bootstrap_select_body_lines(
            $table,
            $table['columns'],
            [$table['columns'][0]],
            false,
        );
        $insertLines = app_project_db_access_bootstrap_insert_body_lines(
            $table,
            [$table['columns'][1]],
        );

        $selectText = implode("\n", $selectLines);
        $insertText = implode("\n", $insertLines);

        self::assertStringContainsString(
            <<<'TEXT'
$last_sql_command_for_mtooldb = 'select `lab_experiments`.`id`, `lab_experiments`.`name` from `lab_experiments` where `lab_experiments`.`id` = ?';
TEXT,
            $selectText,
        );
        self::assertStringContainsString(
            <<<'TEXT'
$last_sql_command_for_mtooldb = 'insert into `lab_experiments` (`name`) values(?)';
TEXT,
            $insertText,
        );
        self::assertStringContainsString('$ret = $mtooldb->execute($last_sql_command_for_mtooldb, [', $selectText);
        self::assertStringContainsString('$result = $mtooldb->execute($last_sql_command_for_mtooldb, [', $insertText);
        self::assertStringContainsString('$param_lab_experiments_id_where,', $selectText);
        self::assertStringContainsString('$lab_experimentsObj->name,', $insertText);
        self::assertStringNotContainsString('\\$param_lab_experiments_id_where', $selectText);
        self::assertStringNotContainsString('\\$lab_experimentsObj', $insertText);
    }

    public function testGeneratedRuntimeDbSupportCanRunAgainstSqliteDsn(): void
    {
        $root = sys_get_temp_dir() . '/dego-generated-dbaccess-runtime-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $this->temporaryPaths[] = $root;
        mkdir($root . '/_support', 0777, true);
        mkdir($root . '/base', 0777, true);

        file_put_contents(
            $root . '/' . app_project_output_db_access_runtime_support_relative_path(),
            app_project_output_db_access_runtime_support_php_text(),
        );
        file_put_contents(
            $root . '/base/dbaccess-ArticleBase.php',
            <<<'PHP'
<?php
require_once __DIR__ . '/../_support/mtool_runtime_db.php';

class ArticleData
{
    public $Id;
    public $Title;
    public $Status;
}

class ArticleDBAccessBase
{
    public function InsertArticle($ArticleObj)
    {
        global $mtooldb, $last_sql_command_for_mtooldb;
        connect_mtooldb_if_not_yet();
        reconnect_mtooldb_if_necessary();

        $last_sql_command_for_mtooldb = 'insert into Article (Title, Status) values(' . '\'' . $mtooldb->real_escape_string($ArticleObj->Title) . '\'' . ', ' . '\'' . $mtooldb->real_escape_string($ArticleObj->Status) . '\'' . ')';
        $result = $mtooldb->query($last_sql_command_for_mtooldb);
        if ($mtooldb->errno != 0) {
            error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
        }
        return $result;
    }

    public function GetArticleList($status)
    {
        global $mtooldb, $last_sql_command_for_mtooldb;
        connect_mtooldb_if_not_yet();
        reconnect_mtooldb_if_necessary();

        $result = array();
        $last_sql_command_for_mtooldb = 'select Article.Id, Article.Title, Article.Status from Article' . ' where ' . 'Article.Status = ' . '\'' . $mtooldb->real_escape_string($status) . '\'' . ' order by Article.Id asc';
        $ret = $mtooldb->query($last_sql_command_for_mtooldb);
        if ($mtooldb->errno != 0) {
            error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
            return $ret;
        }
        while($thisline=$ret->fetch_row()) {
            $thisresult = new ArticleData();
            $thisresult->Id = $thisline[0];
            $thisresult->Title = $thisline[1];
            $thisresult->Status = $thisline[2];
            array_push($result, $thisresult);
        }
        return $result;
    }
}
PHP,
        );

        $scriptPath = $root . '/run.php';
        $sqlitePath = $root . '/runtime.sqlite';
        file_put_contents(
            $scriptPath,
            <<<'PHP'
<?php
$sqlitePath = $argv[1];
$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE Article (Id INTEGER PRIMARY KEY AUTOINCREMENT, Title TEXT NOT NULL, Status TEXT NOT NULL)');
putenv('MTOOL_RUNTIME_DB_DSN=sqlite:' . $sqlitePath);
require __DIR__ . '/base/dbaccess-ArticleBase.php';
$dbAccess = new ArticleDBAccessBase();
$first = new ArticleData();
$first->Title = "Bob's note";
$first->Status = 'published';
$dbAccess->InsertArticle($first);
$second = new ArticleData();
$second->Title = 'Draft note';
$second->Status = 'draft';
$dbAccess->InsertArticle($second);
$rows = $dbAccess->GetArticleList('published');
echo json_encode([
    'count' => count($rows),
    'title' => $rows[0]->Title ?? null,
    'status' => $rows[0]->Status ?? null,
], JSON_UNESCAPED_SLASHES);
PHP,
        );

        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($sqlitePath);
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode("\n", $output));
        $decoded = json_decode(implode("\n", $output), true);
        self::assertIsArray($decoded);
        self::assertSame(1, $decoded['count'] ?? null);
        self::assertSame("Bob's note", $decoded['title'] ?? null);
        self::assertSame('published', $decoded['status'] ?? null);
    }

    public function testProxyRuntimeBundleTransformAllowsCanonicalBootstrapBaseClass(): void
    {
        $contents = <<<'PHP'
<?php
class lab_experimentsDBAccessBase
{
}
PHP;

        $rewritten = app_project_output_proxy_runtime_bundle_transform_file(
            'base/dbaccess-lab_experimentsBase.php',
            $contents,
        );

        self::assertSame($contents, $rewritten);
    }

    private function removeTree(string $path): void
    {
        if ($path === '' || !file_exists($path)) {
            return;
        }

        if (!is_dir($path) || is_link($path)) {
            @unlink($path);
            return;
        }

        $items = scandir($path);
        if (!is_array($items)) {
            @rmdir($path);
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $this->removeTree($path . '/' . $item);
        }

        @rmdir($path);
    }
}
