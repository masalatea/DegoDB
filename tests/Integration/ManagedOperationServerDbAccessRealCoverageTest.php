<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/managed_operation_server_dbaccess_executor.php';
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
}
