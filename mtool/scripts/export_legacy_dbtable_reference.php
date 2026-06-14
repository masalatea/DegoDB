#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';

function app_cli_export_legacy_dbtable_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_legacy_dbtable_reference.php \
    --host-side \
    --project-key=MTOOL \
    --project-pid=1 \
    --sql-dump=original-codes/mtool.sql \
    --output=mtool/reference/mtool-legacy-dbtable-catalog.json

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
function app_cli_export_legacy_dbtable_reference_parse_args(array $argv): array
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

function app_cli_export_legacy_dbtable_reference_sql_unescape(string $value): string
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

/**
 * @return array{
 *     ok:bool,
 *     tables:list<array{
 *         project_pid:int,
 *         legacy_table_pid:int,
 *         name:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_export_legacy_dbtable_reference_extract_from_dump(string $sqlDumpPath, int $projectPid): array
{
    if (!is_file($sqlDumpPath)) {
        return [
            'ok' => false,
            'tables' => [],
            'error' => 'SQL dump が見つかりません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $handle = fopen($sqlDumpPath, 'rb');
    if (!is_resource($handle)) {
        return [
            'ok' => false,
            'tables' => [],
            'error' => 'SQL dump を開けません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $tables = [];
    $inTargetInsert = false;

    try {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if (!$inTargetInsert) {
                if ($trimmed === 'INSERT INTO `dbtable` (`ProjectPID`, `PID`, `name`) VALUES') {
                    $inTargetInsert = true;
                }
                continue;
            }

            if ($trimmed === '') {
                continue;
            }

            if (
                preg_match_all(
                    "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)'\\)\\s*[,;]/u",
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

                    $tables[] = [
                        'project_pid' => $rowProjectPid,
                        'legacy_table_pid' => (int) ($match[2] ?? 0),
                        'name' => app_cli_export_legacy_dbtable_reference_sql_unescape((string) ($match[3] ?? '')),
                    ];
                }
            }

            if (str_contains($trimmed, ';')) {
                fclose($handle);

                usort(
                    $tables,
                    static fn (array $left, array $right): int => $left['legacy_table_pid'] <=> $right['legacy_table_pid'],
                );

                return [
                    'ok' => true,
                    'tables' => $tables,
                    'error' => '',
                ];
            }
        }
    } finally {
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    return [
        'ok' => false,
        'tables' => [],
        'error' => 'dbtable INSERT section を SQL dump から見つけられませんでした。',
    ];
}

$parsed = app_cli_export_legacy_dbtable_reference_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_export_legacy_dbtable_reference_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_export_legacy_dbtable_reference_usage() . PHP_EOL);
    exit(64);
}

$extracted = app_cli_export_legacy_dbtable_reference_extract_from_dump(
    $parsed['sql_dump_path'],
    $parsed['project_pid'],
);
if (!$extracted['ok']) {
    fwrite(STDERR, $extracted['error'] . PHP_EOL);
    exit(1);
}

$document = [
    'project_key' => $parsed['project_key'],
    'project_pid' => $parsed['project_pid'],
    'source_dump_path' => $parsed['sql_dump_path'],
    'generated_at' => gmdate('c'),
    'table_count' => count($extracted['tables']),
    'tables' => $extracted['tables'],
];

$outputDir = dirname($parsed['output_path']);
if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
    fwrite(STDERR, 'output directory を作れません: ' . $outputDir . PHP_EOL);
    exit(1);
}

$json = json_encode(
    $document,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
);
if (!is_string($json)) {
    fwrite(STDERR, 'JSON encode に失敗しました。' . PHP_EOL);
    exit(1);
}

if (file_put_contents($parsed['output_path'], $json . PHP_EOL) === false) {
    fwrite(STDERR, 'reference JSON を書き込めません: ' . $parsed['output_path'] . PHP_EOL);
    exit(1);
}

fwrite(
    STDOUT,
    json_encode(
        [
            'ok' => true,
            'project_key' => $parsed['project_key'],
            'project_pid' => $parsed['project_pid'],
            'sql_dump_path' => $parsed['sql_dump_path'],
            'output_path' => $parsed['output_path'],
            'table_count' => count($extracted['tables']),
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);
