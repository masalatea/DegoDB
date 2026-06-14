#!/usr/bin/env php
<?php

declare(strict_types=1);

function app_cli_export_legacy_table_schema_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_legacy_table_schema_reference.php \\
    --host-side \\
    --project-key=MTOOL \\
    --dsn='mysql:host=127.0.0.1;port=33061;dbname=legacy_seed_tmp;charset=utf8mb4' \\
    --user=root \\
    --password=... \\
    --schema-name=legacy_seed_tmp \\
    --output=mtool/reference/mtool-legacy-table-schema.json

Options:
  --host-side          host-side 明示実行であることを確認する
  --project-key=KEY     reference の project key
  --dsn=DSN             host-side で到達できる temporary legacy schema 用 PDO DSN
  --user=USER           DB user
  --password=PASS       DB password
  --schema-name=NAME    information_schema 上の source schema name
  --output=PATH         JSON 出力先
  --help                このヘルプを表示

Notes:
  - This is a host-side export helper.
  - `original-codes/` は base Docker runtime には mount しない。
  - この helper 自体は `--sql-dump` を受けず、temporary imported legacy schema
    (例: `legacy_seed_tmp`) を `--dsn` / `--schema-name` で参照する。
  - DSN の port は host 側で公開した MariaDB port を使う
    (default 例: `CONFIG_DB_HOST_PORT=33061`)。
  - 旧 dump `original-codes/mtool.sql` しか無い場合は、先に host 側で一時 schema へ import してから実行する。
  - 更新作業だと分かる場合にだけ `--host-side` を付けて実行する。
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     dsn:string,
 *     user:string,
 *     password:string,
 *     schema_name:string,
 *     output:string,
 *     error:string
 * }
 */
function app_cli_export_legacy_table_schema_reference_parse_args(array $argv): array
{
    $parsed = [
        'project_key' => '',
        'dsn' => '',
        'user' => '',
        'password' => '',
        'schema_name' => '',
        'output' => '',
    ];
    $hostSideConfirmed = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'dsn' => '',
                'user' => '',
                'password' => '',
                'schema_name' => '',
                'output' => '',
                'error' => '',
            ];
        }
        if ($argument === '--host-side') {
            $hostSideConfirmed = true;
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $parsed['project_key'] = trim(substr($argument, strlen('--project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--dsn=')) {
            $parsed['dsn'] = trim(substr($argument, strlen('--dsn=')));
            continue;
        }
        if (str_starts_with($argument, '--user=')) {
            $parsed['user'] = trim(substr($argument, strlen('--user=')));
            continue;
        }
        if (str_starts_with($argument, '--password=')) {
            $parsed['password'] = (string) substr($argument, strlen('--password='));
            continue;
        }
        if (str_starts_with($argument, '--schema-name=')) {
            $parsed['schema_name'] = trim(substr($argument, strlen('--schema-name=')));
            continue;
        }
        if (str_starts_with($argument, '--output=')) {
            $parsed['output'] = trim(substr($argument, strlen('--output=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'dsn' => '',
            'user' => '',
            'password' => '',
            'schema_name' => '',
            'output' => '',
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if (!$hostSideConfirmed) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'dsn' => '',
            'user' => '',
            'password' => '',
            'schema_name' => '',
            'output' => '',
            'error' => 'この helper は host-side 明示実行専用です。`--host-side` を付けて再実行してください。',
        ];
    }

    foreach (['project_key', 'dsn', 'user', 'schema_name', 'output'] as $requiredKey) {
        if ($parsed[$requiredKey] === '') {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'dsn' => '',
                'user' => '',
                'password' => '',
                'schema_name' => '',
                'output' => '',
                'error' => '--' . str_replace('_', '-', $requiredKey) . '=... を指定してください。host-side temporary legacy schema 前提です。',
            ];
        }
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $parsed['project_key'],
        'dsn' => $parsed['dsn'],
        'user' => $parsed['user'],
        'password' => $parsed['password'],
        'schema_name' => $parsed['schema_name'],
        'output' => $parsed['output'],
        'error' => '',
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     document:array{
 *         project_key:string,
 *         source_schema_name:string,
 *         table_count:int,
 *         column_count:int,
 *         generated_at:string,
 *         tables:list<array{
 *             name:string,
 *             column_count:int,
 *             columns:list<array{
 *                 name:string,
 *                 datatype:string,
 *                 is_null:string,
 *                 is_key:string,
 *                 is_default:string,
 *                 extra:string,
 *                 column_list_order:int
 *             }>
 *         }>
 *     }|null,
 *     error:string
 * }
 */
function app_cli_export_legacy_table_schema_reference_build_document(
    string $projectKey,
    string $dsn,
    string $user,
    string $password,
    string $schemaName,
): array {
    try {
        $pdo = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );

        $statement = $pdo->prepare(
            'SELECT
                c.TABLE_NAME,
                c.COLUMN_NAME,
                c.COLUMN_TYPE,
                c.IS_NULLABLE,
                c.COLUMN_KEY,
                c.COLUMN_DEFAULT,
                c.EXTRA,
                c.ORDINAL_POSITION
            FROM information_schema.COLUMNS AS c
            INNER JOIN information_schema.TABLES AS t
                ON t.TABLE_SCHEMA = c.TABLE_SCHEMA
               AND t.TABLE_NAME = c.TABLE_NAME
            WHERE c.TABLE_SCHEMA = :schema_name
              AND t.TABLE_TYPE = "BASE TABLE"
            ORDER BY c.TABLE_NAME, c.ORDINAL_POSITION'
        );
        $statement->execute([
            ':schema_name' => $schemaName,
        ]);

        $rows = $statement->fetchAll();
        $tables = [];
        $indexByTableName = [];
        $columnCount = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $tableName = (string) ($row['TABLE_NAME'] ?? '');
            if ($tableName === '') {
                continue;
            }

            if (!array_key_exists($tableName, $indexByTableName)) {
                $indexByTableName[$tableName] = count($tables);
                $tables[] = [
                    'name' => $tableName,
                    'column_count' => 0,
                    'columns' => [],
                ];
            }

            $tableIndex = $indexByTableName[$tableName];
            $tables[$tableIndex]['columns'][] = [
                'name' => (string) ($row['COLUMN_NAME'] ?? ''),
                'datatype' => (string) ($row['COLUMN_TYPE'] ?? ''),
                'is_null' => (string) ($row['IS_NULLABLE'] ?? ''),
                'is_key' => (string) ($row['COLUMN_KEY'] ?? ''),
                'is_default' => ($row['COLUMN_DEFAULT'] ?? null) === null ? '' : (string) $row['COLUMN_DEFAULT'],
                'extra' => (string) ($row['EXTRA'] ?? ''),
                'column_list_order' => (int) ($row['ORDINAL_POSITION'] ?? 0),
            ];
            $tables[$tableIndex]['column_count'] = count($tables[$tableIndex]['columns']);
            $columnCount++;
        }

        return [
            'ok' => true,
            'document' => [
                'project_key' => $projectKey,
                'source_schema_name' => $schemaName,
                'table_count' => count($tables),
                'column_count' => $columnCount,
                'generated_at' => gmdate('c'),
                'tables' => $tables,
            ],
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'document' => null,
            'error' => $throwable->getMessage(),
        ];
    }
}

$parsed = app_cli_export_legacy_table_schema_reference_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_export_legacy_table_schema_reference_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_export_legacy_table_schema_reference_usage() . PHP_EOL);
    exit(64);
}

$built = app_cli_export_legacy_table_schema_reference_build_document(
    $parsed['project_key'],
    $parsed['dsn'],
    $parsed['user'],
    $parsed['password'],
    $parsed['schema_name'],
);
if (!$built['ok'] || $built['document'] === null) {
    fwrite(STDERR, $built['error'] . PHP_EOL);
    exit(1);
}

$outputPath = $parsed['output'];
$outputDir = dirname($outputPath);
if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
    fwrite(STDERR, '出力先ディレクトリを作成できません: ' . $outputDir . PHP_EOL);
    exit(1);
}

$encoded = json_encode(
    $built['document'],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
);
if (!is_string($encoded)) {
    fwrite(STDERR, 'JSON encode に失敗しました。' . PHP_EOL);
    exit(1);
}

if (file_put_contents($outputPath, $encoded . PHP_EOL) === false) {
    fwrite(STDERR, '出力先へ書き込めません: ' . $outputPath . PHP_EOL);
    exit(1);
}

fwrite(
    STDOUT,
    json_encode(
        [
            'ok' => true,
            'output' => $outputPath,
            'summary' => [
                'project_key' => $built['document']['project_key'],
                'source_schema_name' => $built['document']['source_schema_name'],
                'table_count' => $built['document']['table_count'],
                'column_count' => $built['document']['column_count'],
            ],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
