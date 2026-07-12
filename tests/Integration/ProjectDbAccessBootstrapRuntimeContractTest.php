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

    public function testCanonicalBootstrapWriteUsesDeclaredObjectAndLogicalProperties(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $result = app_project_db_access_bootstrap_generated_method_result(
                [
                    'action_type' => 'INSERT',
                    'detected_signature' => 'public function InsertSampleItem($SampleItemObj)',
                ],
                [
                    'name' => 'sample_item',
                    'columns' => [
                        [
                            'name' => 'id',
                            'datatype' => 'bigint',
                            'is_key' => 'PRI',
                            'extra' => 'auto_increment',
                            'column_list_order' => 1,
                        ],
                        [
                            'name' => 'transaction_key',
                            'datatype' => 'varchar',
                            'is_key' => '',
                            'extra' => '',
                            'column_list_order' => 2,
                        ],
                    ],
                ],
            );
        } finally {
            if ($previousPolicy === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previousPolicy);
            }
        }

        $body = implode("\n", $result['body_lines']);
        self::assertStringContainsString('$SampleItemObj->transactionKey,', $body);
        self::assertStringNotContainsString('$sample_itemObj', $body);
        self::assertStringNotContainsString('->transaction_key', $body);
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

    public function testGeneratedRuntimeDbSupportExposesMysqliTransactionDelegation(): void
    {
        $runtime = app_project_output_db_access_runtime_support_php_text();

        self::assertStringContainsString('private bool $mysqliTransactionActive = false;', $runtime);
        self::assertStringContainsString('$this->mysqli->begin_transaction()', $runtime);
        self::assertStringContainsString('$this->mysqli->commit()', $runtime);
        self::assertStringContainsString('$this->mysqli->rollback()', $runtime);
        self::assertStringContainsString('return $this->mysqliTransactionActive;', $runtime);
        self::assertStringContainsString('public function lastInsertId(): ?int', $runtime);
        self::assertStringContainsString('$this->pdo->lastInsertId()', $runtime);
        self::assertStringContainsString('$this->mysqli->insert_id', $runtime);
        self::assertStringContainsString("getenv('MTOOL_PROXY_DB_HOST')", $runtime);
        self::assertStringContainsString("getenv('MTOOL_PROXY_DB_PASSWORD')", $runtime);
    }

    public function testGeneratedDbAccessCallsShareOnePdoTransactionForCommitAndRollback(): void
    {
        $root = sys_get_temp_dir() . '/dego-generated-dbaccess-transaction-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $this->temporaryPaths[] = $root;
        mkdir($root . '/_support', 0777, true);
        mkdir($root . '/base', 0777, true);

        file_put_contents(
            $root . '/' . app_project_output_db_access_runtime_support_relative_path(),
            app_project_output_db_access_runtime_support_php_text(),
        );
        file_put_contents(
            $root . '/base/dbaccess-TransactionItemBase.php',
            <<<'PHP'
<?php
require_once __DIR__ . '/../_support/mtool_runtime_db.php';

class TransactionItemDBAccessBase
{
    public function InsertItem(string $name)
    {
        global $mtooldb;
        connect_mtooldb_if_not_yet();

        return $mtooldb->execute('INSERT INTO transaction_item (name) VALUES (?)', [$name]);
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
putenv('MTOOL_RUNTIME_DB_DSN=sqlite:' . $sqlitePath);
require __DIR__ . '/base/dbaccess-TransactionItemBase.php';

connect_mtooldb_if_not_yet();
$mtooldb->execute('CREATE TABLE transaction_item (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE)');
$firstDbAccess = new TransactionItemDBAccessBase();
$secondDbAccess = new TransactionItemDBAccessBase();

$commitStarted = $mtooldb->beginTransaction();
$firstCommitted = $firstDbAccess->InsertItem('committed-one') !== false;
$secondCommitted = $secondDbAccess->InsertItem('committed-two') !== false;
$commitFinished = $mtooldb->commit();

$rollbackStarted = $mtooldb->beginTransaction();
$firstRolledBack = $firstDbAccess->InsertItem('rolled-back-one') !== false;
$secondFailed = $secondDbAccess->InsertItem('committed-one') === false;
$rollbackFinished = $mtooldb->rollBack();

$rows = $mtooldb->query('SELECT name FROM transaction_item ORDER BY id ASC');
$names = [];
while ($row = $rows->fetch_row()) {
    $names[] = $row[0];
}

echo json_encode([
    'commit_started' => $commitStarted,
    'first_committed' => $firstCommitted,
    'second_committed' => $secondCommitted,
    'commit_finished' => $commitFinished,
    'rollback_started' => $rollbackStarted,
    'first_rolled_back' => $firstRolledBack,
    'second_failed' => $secondFailed,
    'rollback_finished' => $rollbackFinished,
    'names' => $names,
], JSON_UNESCAPED_SLASHES);
PHP,
        );

        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($sqlitePath);
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode("\n", $output));
        $decoded = json_decode(implode("\n", $output), true);
        self::assertIsArray($decoded);
        self::assertTrue($decoded['commit_started'] ?? false);
        self::assertTrue($decoded['first_committed'] ?? false);
        self::assertTrue($decoded['second_committed'] ?? false);
        self::assertTrue($decoded['commit_finished'] ?? false);
        self::assertTrue($decoded['rollback_started'] ?? false);
        self::assertTrue($decoded['first_rolled_back'] ?? false);
        self::assertTrue($decoded['second_failed'] ?? false);
        self::assertTrue($decoded['rollback_finished'] ?? false);
        self::assertSame(['committed-one', 'committed-two'], $decoded['names'] ?? null);
    }

    public function testGeneratedDbAccessCallsShareOneMysqliTransactionForCommitAndRollback(): void
    {
        if (!extension_loaded('mysqli') || trim((string) getenv('APP_LAB_DB_HOST')) === '') {
            self::markTestSkipped('mysqli lab database is not available');
        }

        $root = sys_get_temp_dir() . '/dego-generated-dbaccess-mysqli-transaction-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $this->temporaryPaths[] = $root;
        mkdir($root . '/_support', 0777, true);
        mkdir($root . '/base', 0777, true);

        file_put_contents(
            $root . '/' . app_project_output_db_access_runtime_support_relative_path(),
            app_project_output_db_access_runtime_support_php_text(),
        );
        file_put_contents(
            $root . '/base/dbaccess-TransactionItemBase.php',
            <<<'PHP'
<?php
require_once __DIR__ . '/../_support/mtool_runtime_db.php';

class TransactionItemDBAccessBase
{
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function InsertItem(string $name)
    {
        global $mtooldb;
        connect_mtooldb_if_not_yet();

        return $mtooldb->execute('INSERT INTO `' . $this->table . '` (`name`) VALUES (?)', [$name]);
    }
}
PHP,
        );

        $scriptPath = $root . '/run.php';
        file_put_contents(
            $scriptPath,
            <<<'PHP'
<?php
$table = $argv[1];
putenv('MTOOL_RUNTIME_DB_DSN');
putenv('MTOOL_RUNTIME_SQLITE_PATH');
putenv('MTOOL_RUNTIME_DB_HOST=' . getenv('APP_LAB_DB_HOST'));
putenv('MTOOL_RUNTIME_DB_PORT=' . getenv('APP_LAB_DB_PORT'));
putenv('MTOOL_RUNTIME_DB_NAME=' . getenv('APP_LAB_DB_NAME'));
putenv('MTOOL_RUNTIME_DB_USER=' . getenv('APP_LAB_DB_USER'));
putenv('MTOOL_RUNTIME_DB_PASSWORD=' . getenv('APP_LAB_DB_PASSWORD'));
require __DIR__ . '/base/dbaccess-TransactionItemBase.php';

connect_mtooldb_if_not_yet();
$mtooldb->query('DROP TABLE IF EXISTS `' . $table . '`');
$mtooldb->query('CREATE TABLE `' . $table . '` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY (`name`)) ENGINE=InnoDB');
$firstDbAccess = new TransactionItemDBAccessBase($table);
$secondDbAccess = new TransactionItemDBAccessBase($table);

$commitStarted = $mtooldb->beginTransaction();
$commitActive = $mtooldb->inTransaction();
$firstCommitted = $firstDbAccess->InsertItem('committed-one') !== false;
$secondCommitted = $secondDbAccess->InsertItem('committed-two') !== false;
$commitFinished = $mtooldb->commit();
$commitInactive = !$mtooldb->inTransaction();

$rollbackStarted = $mtooldb->beginTransaction();
$rollbackActive = $mtooldb->inTransaction();
$firstRolledBack = $firstDbAccess->InsertItem('rolled-back-one') !== false;
$secondFailed = $secondDbAccess->InsertItem('committed-one') === false;
$rollbackFinished = $mtooldb->rollBack();
$rollbackInactive = !$mtooldb->inTransaction();

$rows = $mtooldb->query('SELECT `name` FROM `' . $table . '` ORDER BY `id` ASC');
$names = [];
while ($row = $rows->fetch_row()) {
    $names[] = $row[0];
}
$mtooldb->query('DROP TABLE `' . $table . '`');

echo json_encode([
    'commit_started' => $commitStarted,
    'commit_active' => $commitActive,
    'first_committed' => $firstCommitted,
    'second_committed' => $secondCommitted,
    'commit_finished' => $commitFinished,
    'commit_inactive' => $commitInactive,
    'rollback_started' => $rollbackStarted,
    'rollback_active' => $rollbackActive,
    'first_rolled_back' => $firstRolledBack,
    'second_failed' => $secondFailed,
    'rollback_finished' => $rollbackFinished,
    'rollback_inactive' => $rollbackInactive,
    'names' => $names,
], JSON_UNESCAPED_SLASHES);
PHP,
        );

        $table = 'transaction_item_' . getmypid() . '_' . bin2hex(random_bytes(4));
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($table);
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode("\n", $output));
        $decoded = json_decode(implode("\n", $output), true);
        self::assertIsArray($decoded);
        self::assertTrue($decoded['commit_started'] ?? false);
        self::assertTrue($decoded['commit_active'] ?? false);
        self::assertTrue($decoded['first_committed'] ?? false);
        self::assertTrue($decoded['second_committed'] ?? false);
        self::assertTrue($decoded['commit_finished'] ?? false);
        self::assertTrue($decoded['commit_inactive'] ?? false);
        self::assertTrue($decoded['rollback_started'] ?? false);
        self::assertTrue($decoded['rollback_active'] ?? false);
        self::assertTrue($decoded['first_rolled_back'] ?? false);
        self::assertTrue($decoded['second_failed'] ?? false);
        self::assertTrue($decoded['rollback_finished'] ?? false);
        self::assertTrue($decoded['rollback_inactive'] ?? false);
        self::assertSame(['committed-one', 'committed-two'], $decoded['names'] ?? null);
    }

    public function testGeneratedCustomProxyUsesSharedRuntimeTransactionContract(): void
    {
        $baseClass = 'TransactionAwareProxyEndpointBaseContract';
        $runtime = app_project_output_proxy_server_runtime_text($baseClass);

        self::assertStringContainsString("method_exists(\$mtooldb, 'beginTransaction')", $runtime);
        self::assertStringContainsString('if (!$mtooldb->beginTransaction())', $runtime);
        self::assertStringContainsString('if (!$mtooldb->commit())', $runtime);
        self::assertStringContainsString('if ($mtooldb->inTransaction() && !$mtooldb->rollBack())', $runtime);
        self::assertStringContainsString("method_exists(\$mtooldb, 'lastInsertId')", $runtime);
        self::assertStringNotContainsString('$mtooldb->autocommit(false)', $runtime);

        eval(substr($runtime, strlen("<?php\n")));

        $endpoint = new class extends TransactionAwareProxyEndpointBaseContract {
            protected function proxyDisplayName(): string
            {
                return 'transaction contract';
            }

            protected function stepDefinitions(): array
            {
                return [];
            }

            protected function usesTransaction(): bool
            {
                return true;
            }
        };
        $method = new ReflectionMethod($baseClass, 'withOptionalTransaction');
        $method->setAccessible(true);

        if (!function_exists('connect_mtooldb_if_not_yet')) {
            eval('function connect_mtooldb_if_not_yet(): void {}');
        }

        $mtooldb = new class {
            /** @var list<string> */
            public array $events = [];
            public string $error = '';
            private bool $active = false;

            public function beginTransaction(): bool
            {
                $this->events[] = 'begin';
                $this->active = true;

                return true;
            }

            public function commit(): bool
            {
                $this->events[] = 'commit';
                $this->active = false;

                return true;
            }

            public function rollBack(): bool
            {
                $this->events[] = 'rollback';
                $this->active = false;

                return true;
            }

            public function inTransaction(): bool
            {
                return $this->active;
            }
        };
        $GLOBALS['mtooldb'] = $mtooldb;

        $method->invoke($endpoint, static function () use ($mtooldb): void {
            $mtooldb->events[] = 'callback';
        });
        self::assertSame(['begin', 'callback', 'commit'], $mtooldb->events);

        $mtooldb->events = [];
        try {
            $method->invoke($endpoint, static function () use ($mtooldb): void {
                $mtooldb->events[] = 'callback';
                throw new RuntimeException('required step failed');
            });
            self::fail('transaction callback failure must be rethrown');
        } catch (RuntimeException $exception) {
            self::assertSame('required step failed', $exception->getMessage());
        }
        self::assertSame(['begin', 'callback', 'rollback'], $mtooldb->events);
    }

    public function testGeneratedCustomProxyMutationEndpointCommitsAllAndRollsBackAll(): void
    {
        $root = sys_get_temp_dir() . '/dego-generated-custom-proxy-transaction-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $this->temporaryPaths[] = $root;
        mkdir($root . '/_support', 0777, true);

        file_put_contents(
            $root . '/_support/mtool_runtime_db.php',
            app_project_output_db_access_runtime_support_php_text(),
        );
        file_put_contents(
            $root . '/_support/custom_proxy_runtime.php',
            app_project_output_proxy_server_runtime_text('GeneratedTransactionFixtureEndpointBase'),
        );

        $scriptPath = $root . '/run.php';
        $sqlitePath = $root . '/runtime.sqlite';
        $resultPath = $root . '/result.json';
        file_put_contents(
            $scriptPath,
            <<<'PHP'
<?php
$sqlitePath = $argv[1];
$resultPath = $argv[2];
putenv('MTOOL_RUNTIME_DB_DSN=sqlite:' . $sqlitePath);
require __DIR__ . '/_support/mtool_runtime_db.php';
require __DIR__ . '/_support/custom_proxy_runtime.php';

class GeneratedTransactionFixtureDBAccess
{
    public static string $scenario = 'success';

    public function InsertFirst()
    {
        global $mtooldb;
        $name = self::$scenario === 'success' ? 'success-one' : 'rollback-one';

        return $mtooldb->execute(
            'INSERT INTO transaction_fixture (name) VALUES (?)',
            [$name],
        );
    }

    public function InsertSecond()
    {
        global $mtooldb;
        $name = self::$scenario === 'success' ? 'success-two' : 'success-one';

        return $mtooldb->execute(
            'INSERT INTO transaction_fixture (name) VALUES (?)',
            [$name],
        );
    }
}

class GeneratedTransactionFixtureEndpoint extends GeneratedTransactionFixtureEndpointBase
{
    protected function proxyDisplayName(): string
    {
        return 'transaction fixture';
    }

    protected function usesTransaction(): bool
    {
        return true;
    }

    protected function authStrategy(): string
    {
        return 'no-security';
    }

    protected function stepDefinitions(): array
    {
        return [
            [
                'request_key' => '',
                'is_list' => false,
                'dbaccess_class' => GeneratedTransactionFixtureDBAccess::class,
                'function_name' => 'InsertFirst',
                'action' => 'insert',
                'input_kind' => 'arguments',
                'parameter_names' => [],
                'response_key' => 'FirstInsertId',
                'response_mode' => 'insert-id-single',
            ],
            [
                'request_key' => '',
                'is_list' => false,
                'dbaccess_class' => GeneratedTransactionFixtureDBAccess::class,
                'function_name' => 'InsertSecond',
                'action' => 'insert',
                'input_kind' => 'arguments',
                'parameter_names' => [],
                'response_key' => 'SecondInsertId',
                'response_mode' => 'insert-id-single',
            ],
        ];
    }
}

connect_mtooldb_if_not_yet();
$mtooldb->execute('CREATE TABLE transaction_fixture (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE)');
$endpoint = new GeneratedTransactionFixtureEndpoint();
$_SERVER['REQUEST_METHOD'] = 'POST';

GeneratedTransactionFixtureDBAccess::$scenario = 'success';
ob_start();
$endpoint->handle();
$successResponse = json_decode((string) ob_get_clean(), true);

GeneratedTransactionFixtureDBAccess::$scenario = 'failure';
ob_start();
$endpoint->handle();
$failureResponse = json_decode((string) ob_get_clean(), true);

$rows = $mtooldb->query('SELECT name FROM transaction_fixture ORDER BY id ASC');
$names = [];
while ($row = $rows->fetch_row()) {
    $names[] = $row[0];
}

file_put_contents($resultPath, json_encode([
    'success_response' => $successResponse,
    'failure_response' => $failureResponse,
    'names' => $names,
    'transaction_active' => $mtooldb->inTransaction(),
], JSON_UNESCAPED_SLASHES));
PHP,
        );

        $command = escapeshellarg(PHP_BINARY)
            . ' ' . escapeshellarg($scriptPath)
            . ' ' . escapeshellarg($sqlitePath)
            . ' ' . escapeshellarg($resultPath);
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode("\n", $output));
        $decoded = json_decode((string) file_get_contents($resultPath), true);
        self::assertIsArray($decoded);
        self::assertSame('OK', $decoded['success_response']['_status'] ?? null);
        self::assertSame(1, $decoded['success_response']['FirstInsertId'] ?? null);
        self::assertSame(2, $decoded['success_response']['SecondInsertId'] ?? null);
        self::assertSame('NG', $decoded['failure_response']['_status'] ?? null);
        self::assertSame('step failed: InsertSecond', $decoded['failure_response']['Message'] ?? null);
        self::assertSame(['success-one', 'success-two'], $decoded['names'] ?? null);
        self::assertFalse($decoded['transaction_active'] ?? true);
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
