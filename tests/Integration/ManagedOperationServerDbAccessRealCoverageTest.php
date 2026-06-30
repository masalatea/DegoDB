<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_server_dbaccess_executor.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_managed_operation_bridge.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_runtime.php';
require_once dirname(__DIR__, 2) . '/sample/tutorials/sample07-dbaccess-crud-basic/reference/DATACLASS-PHP/data-TodoItem.php';
require_once dirname(__DIR__, 2) . '/sample/tutorials/sample07-dbaccess-crud-basic/reference/DBACCESS-PHP/dbaccess-TodoItem.php';

use PHPUnit\Framework\TestCase;

final class ManagedOperationServerDbAccessRealCoverageTest extends TestCase
{
    public function testManagedOperationServerDbAccessExecutorUpdatesGeneratedSqliteRuntimeRow(): void
    {
        $sqlitePath = sys_get_temp_dir() . '/dego-managed-ops-server-dbaccess-' . getmypid() . '-' . bin2hex(random_bytes(4)) . '.sqlite';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE todo_item (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                status TEXT NOT NULL,
                body TEXT NOT NULL
            )',
        );
        $pdo->prepare('INSERT INTO todo_item (title, status, body) VALUES (?, ?, ?)')->execute([
            'Original generated DBAccess task',
            'open',
            'Before managed operation.',
        ]);

        global $mtooldb;
        $mtooldb = null;
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);

        try {
            $execute = app_managed_operation_server_dbaccess_execute_intent(
                [
                    'intent_version' => 'managed-operation-sync-intent-v0',
                    'origin' => 'app-local',
                    'target' => 'server',
                    'operation_type' => 'update',
                    'payload' => [
                        'key' => [
                            'id' => 1,
                        ],
                        'input' => [
                            'title' => 'Updated through managed operation',
                            'status' => 'done',
                            'body' => 'Generated DBAccess wrote this row.',
                        ],
                    ],
                ],
                [
                    'endpoint' => 'server',
                    'data_class' => TodoItemData::class,
                    'dbaccess_class' => TodoItemDBAccess::class,
                    'method_map' => [
                        'update' => 'UpdateTodoItem',
                    ],
                ],
            );
        } finally {
            putenv('MTOOL_RUNTIME_SQLITE_PATH');
            $mtooldb = null;
        }

        self::assertTrue($execute['ok'], $execute['error']);
        self::assertTrue($execute['executed']);
        self::assertSame('UpdateTodoItem', $execute['method_name']);

        $row = $pdo->query('SELECT title, status, body FROM todo_item WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        self::assertSame([
            'title' => 'Updated through managed operation',
            'status' => 'done',
            'body' => 'Generated DBAccess wrote this row.',
        ], $row);

        $pdo = null;
        @unlink($sqlitePath);
    }

    public function testManagedOperationServerDbAccessExecutorMergesPartialUpdateInput(): void
    {
        $sqlitePath = sys_get_temp_dir() . '/dego-managed-ops-server-dbaccess-partial-' . getmypid() . '-' . bin2hex(random_bytes(4)) . '.sqlite';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE todo_item (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                status TEXT NOT NULL,
                body TEXT NOT NULL
            )',
        );
        $pdo->prepare('INSERT INTO todo_item (title, status, body) VALUES (?, ?, ?)')->execute([
            'Partial update task',
            'open',
            'Keep this body.',
        ]);

        global $mtooldb;
        $mtooldb = null;
        PartialMergeTaskDBAccess::$pdo = $pdo;
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);

        try {
            $execute = app_managed_operation_server_dbaccess_execute_intent(
                [
                    'intent_version' => 'managed-operation-sync-intent-v0',
                    'origin' => 'app-local',
                    'target' => 'server',
                    'operation_type' => 'update',
                    'payload' => [
                        'key' => [
                            'id' => 1,
                        ],
                        'input' => [
                            'status' => 'done',
                        ],
                    ],
                ],
                [
                    'endpoint' => 'server',
                    'data_class' => PartialMergeTaskData::class,
                    'dbaccess_class' => PartialMergeTaskDBAccess::class,
                    'method_map' => [
                        'update' => 'UpdatePartialMergeTask',
                    ],
                ],
            );
        } finally {
            PartialMergeTaskDBAccess::$pdo = null;
            putenv('MTOOL_RUNTIME_SQLITE_PATH');
            $mtooldb = null;
        }

        self::assertTrue($execute['ok'], $execute['error']);
        self::assertTrue($execute['executed']);
        self::assertSame('UpdatePartialMergeTask', $execute['method_name']);

        $row = $pdo->query('SELECT title, status, body FROM todo_item WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        self::assertSame([
            'title' => 'Partial update task',
            'status' => 'done',
            'body' => 'Keep this body.',
        ], $row);

        $pdo = null;
        @unlink($sqlitePath);
    }

    public function testNoCodeRuntimeDispatchUpdatesGeneratedSqliteRuntimeRow(): void
    {
        $sqlitePath = sys_get_temp_dir() . '/dego-no-code-managed-ops-' . getmypid() . '-' . bin2hex(random_bytes(4)) . '.sqlite';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE todo_item (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                status TEXT NOT NULL,
                body TEXT NOT NULL
            )',
        );
        $pdo->prepare('INSERT INTO todo_item (title, status, body) VALUES (?, ?, ?)')->execute([
            'Original no-code task',
            'open',
            'Before no-code runtime dispatch.',
        ]);

        global $mtooldb;
        $mtooldb = null;
        putenv('MTOOL_RUNTIME_SQLITE_PATH=' . $sqlitePath);

        try {
            $dispatch = app_no_code_runtime_dispatch_action(
                $this->noCodeTodoItemDefinition(),
                'update_todo_item',
                [
                    'id' => 1,
                    'title' => 'Updated through no-code runtime',
                    'status' => 'done',
                    'body' => 'Generated DBAccess persisted this no-code action.',
                ],
                app_no_code_managed_operation_dispatcher(
                    [
                        'contract_key' => 'todo_item',
                        'storage_mode' => 'local-copy',
                        'origin' => 'app-local',
                        'target' => 'server',
                    ],
                    static fn (array $syncIntent): array => app_managed_operation_server_dbaccess_execute_intent(
                        $syncIntent,
                        [
                            'endpoint' => 'server',
                            'data_class' => TodoItemData::class,
                            'dbaccess_class' => TodoItemDBAccess::class,
                            'method_map' => [
                                'update' => 'UpdateTodoItem',
                            ],
                        ],
                    ),
                ),
            );
        } finally {
            putenv('MTOOL_RUNTIME_SQLITE_PATH');
            $mtooldb = null;
        }

        self::assertTrue($dispatch['ok'], $dispatch['error']);
        self::assertTrue($dispatch['executed']);
        self::assertSame('managed-operation-sync-intent-v0', $dispatch['result']['sync_intent']['intent_version'] ?? '');
        self::assertSame('UpdateTodoItem', $dispatch['result']['executor_result']['method_name'] ?? '');

        $row = $pdo->query('SELECT title, status, body FROM todo_item WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        self::assertSame([
            'title' => 'Updated through no-code runtime',
            'status' => 'done',
            'body' => 'Generated DBAccess persisted this no-code action.',
        ], $row);

        $pdo = null;
        @unlink($sqlitePath);
    }

    /**
     * @return array<string,mixed>
     */
    private function noCodeTodoItemDefinition(): array
    {
        return [
            'definition_version' => 'no-code-screen-definition-v0',
            'project_key' => 'SAMPLE07',
            'contracts' => [
                [
                    'contract_key' => 'todo_item',
                    'actions' => [
                        [
                            'action_key' => 'update_todo_item',
                            'operation_key' => 'update_todo_item',
                            'operation_type' => 'update',
                            'availability' => 'enabled',
                            'fields' => [
                                [
                                    'field_key' => 'id',
                                    'role' => 'key',
                                    'required' => true,
                                    'client_write' => false,
                                ],
                                [
                                    'field_key' => 'title',
                                    'role' => 'input',
                                    'required' => true,
                                    'client_write' => true,
                                ],
                                [
                                    'field_key' => 'status',
                                    'role' => 'input',
                                    'required' => true,
                                    'client_write' => true,
                                ],
                                [
                                    'field_key' => 'body',
                                    'role' => 'input',
                                    'required' => true,
                                    'client_write' => true,
                                ],
                            ],
                        ],
                    ],
                    'screens' => [],
                ],
            ],
        ];
    }
}

final class PartialMergeTaskData
{
    public $id;
    public $title;
    public $status;
    public $body;
}

final class PartialMergeTaskDBAccess
{
    public static ?PDO $pdo = null;

    public function GetPartialMergeTask($id): ?PartialMergeTaskData
    {
        $statement = self::$pdo?->prepare('SELECT id, title, status, body FROM todo_item WHERE id = ?');
        if ($statement === null) {
            return null;
        }

        $statement->execute([$id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }

        $data = new PartialMergeTaskData();
        $data->id = (int) $row['id'];
        $data->title = (string) $row['title'];
        $data->status = (string) $row['status'];
        $data->body = (string) $row['body'];

        return $data;
    }

    public function UpdatePartialMergeTask(PartialMergeTaskData $data): bool
    {
        $statement = self::$pdo?->prepare('UPDATE todo_item SET title = ?, status = ?, body = ? WHERE id = ?');
        if ($statement === null) {
            return false;
        }

        return $statement->execute([
            $data->title,
            $data->status,
            $data->body,
            $data->id,
        ]);
    }
}
