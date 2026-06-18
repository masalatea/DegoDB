<?php

declare(strict_types=1);

require_once __DIR__ . '/sql_dialect.php';

/**
 * @param array{
 *     config_db?:array{
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string
 *     }
 * } $app
 */
function app_database_source_repository_create_config_pdo(array $app): PDO
{
    $configDb = $app['config_db'] ?? null;
    if (
        !is_array($configDb)
        || !isset(
            $configDb['dsn'],
            $configDb['user'],
            $configDb['password'],
        )
    ) {
        throw new RuntimeException('config_db 接続情報が未設定です。');
    }

    $dsn = (string) $configDb['dsn'];
    if (str_starts_with(strtolower(trim($dsn)), 'sqlite:')) {
        $sqlitePath = substr($dsn, strlen('sqlite:'));
        if ($sqlitePath !== '' && $sqlitePath !== ':memory:') {
            $sqliteDir = dirname($sqlitePath);
            if ($sqliteDir !== '' && $sqliteDir !== '.' && !is_dir($sqliteDir)) {
                mkdir($sqliteDir, 0775, true);
            }
        }
    }

    return new PDO(
        $dsn,
        (string) $configDb['user'],
        (string) $configDb['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );
}

function app_database_source_repository_mysql_dsn(
    string $host,
    string $port,
    string $databaseName,
): string {
    return sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $databaseName,
    );
}

function app_database_source_pdo_table_exists(PDO $pdo, string $tableName): bool
{
    return app_sql_table_exists($pdo, $tableName);
}

/**
 * @param array<string,mixed> $row
 * @return array{
 *     id:int,
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     source_of_truth:string,
 *     db_config_key:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     is_canonical_store:bool,
 *     host:string,
 *     port:string,
 *     name:string,
 *     user:string,
 *     password:string,
 *     dsn:string,
 *     created_at:string,
 *     updated_at:string
 * }
 */
function app_database_source_repository_row_to_item(array $row): array
{
    $host = trim((string) ($row['host'] ?? ''));
    $port = trim((string) ($row['port'] ?? ''));
    $databaseName = trim((string) ($row['database_name'] ?? ''));

    return [
        'id' => (int) ($row['id'] ?? 0),
        'source_key' => trim((string) ($row['source_key'] ?? '')),
        'label' => trim((string) ($row['label'] ?? '')),
        'description' => trim((string) ($row['description'] ?? '')),
        'source_of_truth' => trim((string) ($row['source_of_truth'] ?? 'manual')),
        'db_config_key' => trim((string) ($row['source_key'] ?? '')),
        'supports_live_schema_import' => (bool) ($row['supports_live_schema_import'] ?? false),
        'supports_proxy_runtime_read' => (bool) ($row['supports_proxy_runtime_read'] ?? false),
        'proxy_runtime_priority' => (int) ($row['proxy_runtime_priority'] ?? 1000),
        'is_canonical_store' => false,
        'host' => $host,
        'port' => $port,
        'name' => $databaseName,
        'user' => trim((string) ($row['user_name'] ?? '')),
        'password' => (string) ($row['password'] ?? ''),
        'dsn' => app_database_source_repository_mysql_dsn($host, $port, $databaseName),
        'created_at' => trim((string) ($row['created_at'] ?? '')),
        'updated_at' => trim((string) ($row['updated_at'] ?? '')),
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     items:list<array{
 *         id:int,
 *         source_key:string,
 *         label:string,
 *         description:string,
 *         source_of_truth:string,
 *         db_config_key:string,
 *         supports_live_schema_import:bool,
 *         supports_proxy_runtime_read:bool,
 *         proxy_runtime_priority:int,
 *         is_canonical_store:bool,
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string,
 *         created_at:string,
 *         updated_at:string
 *     }>,
 *     error:string
 * }
 */
function app_pdo_fetch_database_source_catalog(array $app): array
{
    try {
        $pdo = app_database_source_repository_create_config_pdo($app);
        if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
            return [
                'ok' => false,
                'items' => [],
                'error' => 'database_sources canonical table が未初期化です。config DB を作り直してください。',
            ];
        }

        $statement = $pdo->query(
            'SELECT
                id,
                source_key,
                label,
                description,
                host,
                port,
                database_name,
                user_name,
                password,
                supports_live_schema_import,
                supports_proxy_runtime_read,
                proxy_runtime_priority,
                source_of_truth,
                created_at,
                updated_at
            FROM database_sources
            ORDER BY
                source_key,
                id'
        );

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $items[] = app_database_source_repository_row_to_item($row);
        }

        return [
            'ok' => true,
            'items' => $items,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
 *         id:int,
 *         source_key:string,
 *         label:string,
 *         description:string,
 *         source_of_truth:string,
 *         db_config_key:string,
 *         supports_live_schema_import:bool,
 *         supports_proxy_runtime_read:bool,
 *         proxy_runtime_priority:int,
 *         is_canonical_store:bool,
 *         host:string,
 *         port:string,
 *         name:string,
 *         user:string,
 *         password:string,
 *         dsn:string,
 *         created_at:string,
 *         updated_at:string
 *     }|null,
 *     error:string
 * }
 */
function app_pdo_fetch_database_source_item(array $app, int $sourceId): array
{
    if ($sourceId <= 0) {
        return [
            'ok' => true,
            'item' => null,
            'error' => '',
        ];
    }

    try {
        $pdo = app_database_source_repository_create_config_pdo($app);
        if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
            return [
                'ok' => false,
                'item' => null,
                'error' => 'database_sources canonical table が未初期化です。config DB を作り直してください。',
            ];
        }

        $statement = $pdo->prepare(
            'SELECT
                id,
                source_key,
                label,
                description,
                host,
                port,
                database_name,
                user_name,
                password,
                supports_live_schema_import,
                supports_proxy_runtime_read,
                proxy_runtime_priority,
                source_of_truth,
                created_at,
                updated_at
            FROM database_sources
            WHERE id = :id
            LIMIT 1'
        );
        $statement->execute([
            ':id' => $sourceId,
        ]);

        $row = $statement->fetch();
        if (!is_array($row)) {
            return [
                'ok' => true,
                'item' => null,
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_database_source_repository_row_to_item($row),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     database_name:string,
 *     user_name:string,
 *     password:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     source_id:int,
 *     error:string
 * }
 */
function app_pdo_create_database_source(array $app, array $input): array
{
    try {
        $pdo = app_database_source_repository_create_config_pdo($app);
        if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
            return [
                'ok' => false,
                'source_id' => 0,
                'error' => 'database_sources canonical table が未初期化です。config DB を作り直してください。',
            ];
        }

        $statement = $pdo->prepare(
            'INSERT INTO database_sources (
                source_key,
                label,
                description,
                host,
                port,
                database_name,
                user_name,
                password,
                supports_live_schema_import,
                supports_proxy_runtime_read,
                proxy_runtime_priority,
                source_of_truth
            ) VALUES (
                :source_key,
                :label,
                :description,
                :host,
                :port,
                :database_name,
                :user_name,
                :password,
                :supports_live_schema_import,
                :supports_proxy_runtime_read,
                :proxy_runtime_priority,
                :source_of_truth
            )'
        );
        $statement->execute([
            ':source_key' => $input['source_key'],
            ':label' => $input['label'],
            ':description' => $input['description'],
            ':host' => $input['host'],
            ':port' => $input['port'],
            ':database_name' => $input['database_name'],
            ':user_name' => $input['user_name'],
            ':password' => $input['password'],
            ':supports_live_schema_import' => $input['supports_live_schema_import'] ? 1 : 0,
            ':supports_proxy_runtime_read' => $input['supports_proxy_runtime_read'] ? 1 : 0,
            ':proxy_runtime_priority' => $input['proxy_runtime_priority'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
        ]);

        return [
            'ok' => true,
            'source_id' => (int) $pdo->lastInsertId(),
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'source_id' => 0,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @param array{
 *     source_key:string,
 *     label:string,
 *     description:string,
 *     host:string,
 *     port:string,
 *     database_name:string,
 *     user_name:string,
 *     password:string,
 *     supports_live_schema_import:bool,
 *     supports_proxy_runtime_read:bool,
 *     proxy_runtime_priority:int,
 *     source_of_truth?:string
 * } $input
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_update_database_source(array $app, int $sourceId, array $input): array
{
    if ($sourceId <= 0) {
        return [
            'ok' => false,
            'error' => '更新対象の database source を選択してください。',
        ];
    }

    try {
        $pdo = app_database_source_repository_create_config_pdo($app);
        if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
            return [
                'ok' => false,
                'error' => 'database_sources canonical table が未初期化です。config DB を作り直してください。',
            ];
        }

        $statement = $pdo->prepare(
            'UPDATE database_sources
            SET
                source_key = :source_key,
                label = :label,
                description = :description,
                host = :host,
                port = :port,
                database_name = :database_name,
                user_name = :user_name,
                password = :password,
                supports_live_schema_import = :supports_live_schema_import,
                supports_proxy_runtime_read = :supports_proxy_runtime_read,
                proxy_runtime_priority = :proxy_runtime_priority,
                source_of_truth = :source_of_truth,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id'
        );
        $statement->execute([
            ':id' => $sourceId,
            ':source_key' => $input['source_key'],
            ':label' => $input['label'],
            ':description' => $input['description'],
            ':host' => $input['host'],
            ':port' => $input['port'],
            ':database_name' => $input['database_name'],
            ':user_name' => $input['user_name'],
            ':password' => $input['password'],
            ':supports_live_schema_import' => $input['supports_live_schema_import'] ? 1 : 0,
            ':supports_proxy_runtime_read' => $input['supports_proxy_runtime_read'] ? 1 : 0,
            ':proxy_runtime_priority' => $input['proxy_runtime_priority'],
            ':source_of_truth' => $input['source_of_truth'] ?? 'manual',
        ]);

        if ($statement->rowCount() === 0) {
            $existing = app_pdo_fetch_database_source_item($app, $sourceId);
            if (!$existing['ok']) {
                return [
                    'ok' => false,
                    'error' => $existing['error'],
                ];
            }

            if ($existing['item'] === null) {
                return [
                    'ok' => false,
                    'error' => '更新対象の database source が見つかりません。',
                ];
            }
        }

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     error:string
 * }
 */
function app_pdo_delete_database_source(array $app, int $sourceId): array
{
    if ($sourceId <= 0) {
        return [
            'ok' => false,
            'error' => '削除対象の database source を選択してください。',
        ];
    }

    try {
        $pdo = app_database_source_repository_create_config_pdo($app);
        if (!app_database_source_pdo_table_exists($pdo, 'database_sources')) {
            return [
                'ok' => false,
                'error' => 'database_sources canonical table が未初期化です。config DB を作り直してください。',
            ];
        }

        $statement = $pdo->prepare('DELETE FROM database_sources WHERE id = :id');
        $statement->execute([
            ':id' => $sourceId,
        ]);

        if ($statement->rowCount() === 0) {
            return [
                'ok' => false,
                'error' => '削除対象の database source が見つかりません。',
            ];
        }

        return [
            'ok' => true,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}
