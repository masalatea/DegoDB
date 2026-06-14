#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';

function app_cli_export_legacy_db_access_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_legacy_db_access_reference.php \
    --host-side \
    --project-key=MTOOL \
    --project-pid=1 \
    --sql-dump=original-codes/mtool.sql \
    --output=mtool/reference/mtool-legacy-db-access-catalog.json

Options:
  --host-side          host-side 明示実行であることを確認する
  --project-key=KEY    reference を作る project key
  --project-pid=N      dump 内の legacy ProjectPID
  --sql-dump=PATH      host filesystem 上の legacy SQL dump path
  --output=PATH        output JSON path
  --help               このヘルプを表示

Notes:
  - `original-codes/` は host-side reference only であり、base Docker runtime には mount しない。
  - `--sql-dump=original-codes/mtool.sql` は host から明示実行する場合の例。
  - 更新作業だと分かる場合にだけ `--host-side` を付けて実行する。
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     project_pid:int,
 *     sql_dump_path:string,
 *     output_path:string,
 *     error:string
 * }
 */
function app_cli_export_legacy_db_access_reference_parse_args(array $argv): array
{
    $projectKey = '';
    $projectPid = 0;
    $sqlDumpPath = '';
    $outputPath = '';
    $hostSideConfirmed = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'project_pid' => 0,
                'sql_dump_path' => '',
                'output_path' => '',
                'error' => '',
            ];
        }
        if ($argument === '--host-side') {
            $hostSideConfirmed = true;
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--project-pid=')) {
            $projectPid = (int) trim(substr($argument, strlen('--project-pid=')));
            continue;
        }
        if (str_starts_with($argument, '--sql-dump=')) {
            $sqlDumpPath = trim(substr($argument, strlen('--sql-dump=')));
            continue;
        }
        if (str_starts_with($argument, '--output=')) {
            $outputPath = trim(substr($argument, strlen('--output=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'project_pid' => 0,
            'sql_dump_path' => '',
            'output_path' => '',
            'error' => '未対応の引数です: ' . $argument,
        ];
    }

    if (!$hostSideConfirmed) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'project_pid' => 0,
            'sql_dump_path' => '',
            'output_path' => '',
            'error' => 'この helper は host-side 明示実行専用です。`--host-side` を付けて再実行してください。',
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'project_pid' => 0,
            'sql_dump_path' => '',
            'output_path' => '',
            'error' => '有効な --project-key=... を指定してください。',
        ];
    }
    if ($projectPid <= 0) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'project_pid' => 0,
            'sql_dump_path' => '',
            'output_path' => '',
            'error' => '有効な --project-pid=... を指定してください。',
        ];
    }
    if ($sqlDumpPath === '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'project_pid' => 0,
            'sql_dump_path' => '',
            'output_path' => '',
            'error' => '--sql-dump=... を指定してください。host filesystem 上の path を渡します。',
        ];
    }
    if ($outputPath === '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'project_pid' => 0,
            'sql_dump_path' => '',
            'output_path' => '',
            'error' => '--output=... を指定してください。',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'project_pid' => $projectPid,
        'sql_dump_path' => $sqlDumpPath,
        'output_path' => $outputPath,
        'error' => '',
    ];
}

function app_cli_export_legacy_db_access_reference_sql_unescape(string $value): string
{
    return strtr(
        $value,
        [
            "\\0" => "\0",
            "\\n" => "\n",
            "\\r" => "\r",
            "\\t" => "\t",
            "\\Z" => chr(26),
            "\\'" => "'",
            '\\"' => '"',
            "\\\\" => "\\",
        ],
    );
}

function app_cli_export_legacy_db_access_reference_map_function_name(string $legacyName, string $legacyActionType): string
{
    $normalizedName = trim($legacyName);
    if ($normalizedName === '') {
        return '';
    }

    return match (strtolower(trim($legacyActionType))) {
        'selectsingle' => 'Get' . $normalizedName,
        'selectlist' => 'Get' . $normalizedName . 'List',
        'insert' => 'Insert' . $normalizedName,
        'update' => 'Update' . $normalizedName,
        'delete' => 'Delete' . $normalizedName,
        default => $normalizedName,
    };
}

/**
 * @return array{
 *     ok:bool,
 *     db_access_classes:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         source_name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         last_modified_dt:string
 *     }>,
 *     functions:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         action_type:string,
 *         function_list_order:int,
 *         single_proxy_single_get_function_pid:int
 *     }>,
 *     error:string
 * }
 */
function app_cli_export_legacy_db_access_reference_extract_from_dump(string $sqlDumpPath, int $projectPid): array
{
    if (!is_file($sqlDumpPath)) {
        return [
            'ok' => false,
            'db_access_classes' => [],
            'functions' => [],
            'error' => 'SQL dump が見つかりません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $handle = fopen($sqlDumpPath, 'rb');
    if (!is_resource($handle)) {
        return [
            'ok' => false,
            'db_access_classes' => [],
            'functions' => [],
            'error' => 'SQL dump を開けません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $dbAccessClasses = [];
    $sourceNameByLegacyDaPid = [];
    $functionRows = [];
    $inDaInsert = false;
    $inDafuncInsert = false;

    try {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($trimmed === 'INSERT INTO `da` (`ProjectPID`, `PID`, `name`, `StoreBasePath`, `IsAutoload`, `LastModifiedDT`) VALUES') {
                $inDaInsert = true;
                $inDafuncInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `dafunc` (`ProjectPID`, `daPID`, `PID`, `name`, `ActionType`, `InsertUpdateDeleteTargetTable`, `InsertUpdateDeleteParamType`, `SelectByDistinct`, `SortOrderColumns`, `DataClassBaseNameForSelectAction`, `FunctionListOrder`, `memo`, `limitParameterType`, `limitFixedParameter`, `ORGroupType`, `SingleProxy_AuthType`, `SingleProxy_SingleGetFuncPID`, `IsBlobTarget`) VALUES') {
                $inDafuncInsert = true;
                $inDaInsert = false;
                continue;
            }

            if ($inDaInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $legacyDaPid = (int) ($match[2] ?? 0);
                        $sourceName = app_cli_export_legacy_db_access_reference_sql_unescape((string) ($match[3] ?? ''));
                        if ($legacyDaPid <= 0 || $sourceName === '') {
                            continue;
                        }

                        $dbAccessClasses[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => $legacyDaPid,
                            'source_name' => $sourceName,
                            'store_base_path' => app_cli_export_legacy_db_access_reference_sql_unescape((string) ($match[4] ?? '')),
                            'is_autoload' => ((int) ($match[5] ?? 0)) === 1 ? '1' : '0',
                            'last_modified_dt' => app_cli_export_legacy_db_access_reference_sql_unescape((string) ($match[6] ?? '')),
                        ];
                        $sourceNameByLegacyDaPid[$legacyDaPid] = $sourceName;
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inDaInsert = false;
                }
                continue;
            }

            if ($inDafuncInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[1] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $legacyDaPid = (int) ($match[2] ?? 0);
                        $legacyFunctionPid = (int) ($match[3] ?? 0);
                        $legacyFunctionName = app_cli_export_legacy_db_access_reference_sql_unescape((string) ($match[4] ?? ''));
                        $legacyActionType = app_cli_export_legacy_db_access_reference_sql_unescape((string) ($match[5] ?? ''));
                        $functionName = app_cli_export_legacy_db_access_reference_map_function_name(
                            $legacyFunctionName,
                            $legacyActionType,
                        );
                        if ($legacyDaPid <= 0 || $legacyFunctionPid <= 0 || $functionName === '') {
                            continue;
                        }

                        $functionRows[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_da_pid' => $legacyDaPid,
                            'legacy_function_pid' => $legacyFunctionPid,
                            'function_name' => $functionName,
                            'action_type' => strtoupper($legacyActionType),
                            'function_list_order' => (int) ($match[11] ?? 0),
                            'single_proxy_single_get_function_pid' => (int) ($match[17] ?? 0),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inDafuncInsert = false;
                }
                continue;
            }
        }
    } finally {
        fclose($handle);
    }

    usort(
        $dbAccessClasses,
        static function (array $left, array $right): int {
            if ($left['legacy_da_pid'] !== $right['legacy_da_pid']) {
                return $left['legacy_da_pid'] <=> $right['legacy_da_pid'];
            }

            return strcmp($left['source_name'], $right['source_name']);
        },
    );

    $functions = [];
    foreach ($functionRows as $functionRow) {
        $sourceName = $sourceNameByLegacyDaPid[$functionRow['legacy_da_pid']] ?? '';
        if ($sourceName === '') {
            continue;
        }

        $functions[] = [
            'project_pid' => $functionRow['project_pid'],
            'legacy_da_pid' => $functionRow['legacy_da_pid'],
            'legacy_function_pid' => $functionRow['legacy_function_pid'],
            'source_name' => $sourceName,
            'function_name' => $functionRow['function_name'],
            'action_type' => $functionRow['action_type'],
            'function_list_order' => $functionRow['function_list_order'],
            'single_proxy_single_get_function_pid' => $functionRow['single_proxy_single_get_function_pid'],
        ];
    }

    usort(
        $functions,
        static function (array $left, array $right): int {
            if ($left['legacy_da_pid'] !== $right['legacy_da_pid']) {
                return $left['legacy_da_pid'] <=> $right['legacy_da_pid'];
            }
            if ($left['function_list_order'] !== $right['function_list_order']) {
                return $left['function_list_order'] <=> $right['function_list_order'];
            }
            if ($left['legacy_function_pid'] !== $right['legacy_function_pid']) {
                return $left['legacy_function_pid'] <=> $right['legacy_function_pid'];
            }

            return strcmp($left['function_name'], $right['function_name']);
        },
    );

    return [
        'ok' => true,
        'db_access_classes' => $dbAccessClasses,
        'functions' => $functions,
        'error' => '',
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     project_pid:int,
 *     source_dump_path:string,
 *     generated_at:string,
 *     db_access_count:int,
 *     function_count:int,
 *     db_access_classes:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         source_name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         last_modified_dt:string
 *     }>,
 *     functions:list<array{
 *         project_pid:int,
 *         legacy_da_pid:int,
 *         legacy_function_pid:int,
 *         source_name:string,
 *         function_name:string,
 *         action_type:string,
 *         function_list_order:int,
 *         single_proxy_single_get_function_pid:int
 *     }>
 * } $payload
 */
function app_cli_export_legacy_db_access_reference_write_json(string $outputPath, array $payload): void
{
    $directory = dirname($outputPath);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException('output directory を作成できません: ' . $directory);
    }

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        throw new RuntimeException('reference JSON の encode に失敗しました。');
    }

    $result = file_put_contents($outputPath, $json . PHP_EOL);
    if ($result === false) {
        throw new RuntimeException('reference JSON を書き込めませんでした: ' . $outputPath);
    }
}

/**
 * @param list<string> $argv
 */
function app_cli_export_legacy_db_access_reference_main(array $argv): int
{
    $parsed = app_cli_export_legacy_db_access_reference_parse_args($argv);
    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_export_legacy_db_access_reference_usage() . PHP_EOL);
        return 1;
    }

    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_export_legacy_db_access_reference_usage() . PHP_EOL);
        return 0;
    }

    $result = app_cli_export_legacy_db_access_reference_extract_from_dump(
        $parsed['sql_dump_path'],
        $parsed['project_pid'],
    );
    if (!$result['ok']) {
        fwrite(STDERR, $result['error'] . PHP_EOL);
        return 1;
    }

    $payload = [
        'project_key' => $parsed['project_key'],
        'project_pid' => $parsed['project_pid'],
        'source_dump_path' => $parsed['sql_dump_path'],
        'generated_at' => gmdate('c'),
        'db_access_count' => count($result['db_access_classes']),
        'function_count' => count($result['functions']),
        'db_access_classes' => $result['db_access_classes'],
        'functions' => $result['functions'],
    ];

    try {
        app_cli_export_legacy_db_access_reference_write_json($parsed['output_path'], $payload);
    } catch (Throwable $throwable) {
        fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
        return 1;
    }

    fwrite(
        STDOUT,
        'legacy db access reference exported:'
        . ' project_key=' . $parsed['project_key']
        . ' db_access_count=' . count($result['db_access_classes'])
        . ' function_count=' . count($result['functions'])
        . ' output=' . $parsed['output_path']
        . PHP_EOL,
    );

    return 0;
}

exit(app_cli_export_legacy_db_access_reference_main($argv));
