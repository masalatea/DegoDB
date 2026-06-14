#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';

function app_cli_export_legacy_dataclass_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_legacy_dataclass_reference.php \
    --host-side \
    --project-key=MTOOL \
    --project-pid=1 \
    --sql-dump=original-codes/mtool.sql \
    --output=mtool/reference/mtool-legacy-dataclass-catalog.json

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
function app_cli_export_legacy_dataclass_reference_parse_args(array $argv): array
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

function app_cli_export_legacy_dataclass_reference_sql_unescape(string $value): string
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
 *     data_classes:list<array{
 *         project_pid:int,
 *         legacy_data_class_pid:int,
 *         name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         inherit_parent_data_class_name:string,
 *         last_modified_dt:string
 *     }>,
 *     fields:list<array{
 *         project_pid:int,
 *         legacy_data_class_pid:int,
 *         legacy_field_pid:int,
 *         data_class_name:string,
 *         name:string,
 *         datatype:string,
 *         field_list_order:int,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_export_legacy_dataclass_reference_extract_from_dump(string $sqlDumpPath, int $projectPid): array
{
    if (!is_file($sqlDumpPath)) {
        return [
            'ok' => false,
            'data_classes' => [],
            'fields' => [],
            'error' => 'SQL dump が見つかりません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $handle = fopen($sqlDumpPath, 'rb');
    if (!is_resource($handle)) {
        return [
            'ok' => false,
            'data_classes' => [],
            'fields' => [],
            'error' => 'SQL dump を開けません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $dataClasses = [];
    $fields = [];
    $dataClassNameByPid = [];
    $inDataClassInsert = false;
    $inFieldInsert = false;

    try {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($trimmed === 'INSERT INTO `dataclass` (`ProjectPID`, `PID`, `name`, `StoreBasePath`, `IsAutoload`, `InheritParentDataClassName`, `LastModifiedDT`) VALUES') {
                $inDataClassInsert = true;
                $inFieldInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `dataclassfields` (`ProjectPID`, `dataclassPID`, `PID`, `name`, `datatype`, `FieldListOrder`, `RefDataClassName`, `RefDataClassFieldName`) VALUES') {
                $inFieldInsert = true;
                $inDataClassInsert = false;
                continue;
            }

            if ($inDataClassInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)'\\)\\s*[,;]/u",
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

                        $legacyDataClassPid = (int) ($match[2] ?? 0);
                        $name = app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[3] ?? ''));
                        if ($legacyDataClassPid <= 0 || $name === '') {
                            continue;
                        }

                        $dataClasses[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_data_class_pid' => $legacyDataClassPid,
                            'name' => $name,
                            'store_base_path' => app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[4] ?? '')),
                            'is_autoload' => ((int) ($match[5] ?? 0)) === 1 ? '1' : '0',
                            'inherit_parent_data_class_name' => app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[6] ?? '')),
                            'last_modified_dt' => app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[7] ?? '')),
                        ];
                        $dataClassNameByPid[$legacyDataClassPid] = $name;
                    }
                }

                if (str_contains($trimmed, ';')) {
                    $inDataClassInsert = false;
                }
                continue;
            }

            if ($inFieldInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)',\\s*(-?\\d+),\\s*'((?:\\\\\\\\.|[^'])*)',\\s*'((?:\\\\\\\\.|[^'])*)'\\)\\s*[,;]/u",
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

                        $legacyDataClassPid = (int) ($match[2] ?? 0);
                        $legacyFieldPid = (int) ($match[3] ?? 0);
                        $fieldName = app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[4] ?? ''));
                        if ($legacyDataClassPid <= 0 || $legacyFieldPid <= 0 || $fieldName === '') {
                            continue;
                        }

                        $fields[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_data_class_pid' => $legacyDataClassPid,
                            'legacy_field_pid' => $legacyFieldPid,
                            'data_class_name' => $dataClassNameByPid[$legacyDataClassPid] ?? '',
                            'name' => $fieldName,
                            'datatype' => app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[5] ?? '')),
                            'field_list_order' => (int) ($match[6] ?? 0),
                            'ref_data_class_name' => app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[7] ?? '')),
                            'ref_data_class_field_name' => app_cli_export_legacy_dataclass_reference_sql_unescape((string) ($match[8] ?? '')),
                        ];
                    }
                }

                if (str_contains($trimmed, ';')) {
                    $inFieldInsert = false;
                }
            }
        }
    } finally {
        if (is_resource($handle)) {
            fclose($handle);
        }
    }

    if ($dataClasses === []) {
        return [
            'ok' => false,
            'data_classes' => [],
            'fields' => [],
            'error' => 'dataclass INSERT section を SQL dump から見つけられませんでした。',
        ];
    }

    usort(
        $dataClasses,
        static fn (array $left, array $right): int => $left['legacy_data_class_pid'] <=> $right['legacy_data_class_pid'],
    );
    usort(
        $fields,
        static function (array $left, array $right): int {
            $classOrder = $left['legacy_data_class_pid'] <=> $right['legacy_data_class_pid'];
            if ($classOrder !== 0) {
                return $classOrder;
            }

            $fieldOrder = $left['field_list_order'] <=> $right['field_list_order'];
            if ($fieldOrder !== 0) {
                return $fieldOrder;
            }

            return $left['legacy_field_pid'] <=> $right['legacy_field_pid'];
        },
    );

    return [
        'ok' => true,
        'data_classes' => $dataClasses,
        'fields' => $fields,
        'error' => '',
    ];
}

$parsed = app_cli_export_legacy_dataclass_reference_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_export_legacy_dataclass_reference_usage() . PHP_EOL);
    exit(0);
}
if (!$parsed['ok']) {
    fwrite(STDERR, 'Error: ' . $parsed['error'] . PHP_EOL);
    fwrite(STDERR, app_cli_export_legacy_dataclass_reference_usage() . PHP_EOL);
    exit(1);
}

$extract = app_cli_export_legacy_dataclass_reference_extract_from_dump(
    $parsed['sql_dump_path'],
    $parsed['project_pid'],
);
if (!$extract['ok']) {
    fwrite(STDERR, 'Error: ' . $extract['error'] . PHP_EOL);
    exit(1);
}

$outputDir = dirname($parsed['output_path']);
if ($outputDir !== '' && $outputDir !== '.' && !is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
    fwrite(STDERR, 'Error: output directory を作成できません: ' . $outputDir . PHP_EOL);
    exit(1);
}

$payload = [
    'project_key' => $parsed['project_key'],
    'project_pid' => $parsed['project_pid'],
    'source_dump_path' => $parsed['sql_dump_path'],
    'generated_at' => gmdate('c'),
    'data_class_count' => count($extract['data_classes']),
    'field_count' => count($extract['fields']),
    'data_classes' => $extract['data_classes'],
    'fields' => $extract['fields'],
];

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($json)) {
    fwrite(STDERR, 'Error: JSON encode に失敗しました。' . PHP_EOL);
    exit(1);
}

if (file_put_contents($parsed['output_path'], $json . PHP_EOL) === false) {
    fwrite(STDERR, 'Error: output file に書き込めません: ' . $parsed['output_path'] . PHP_EOL);
    exit(1);
}

fwrite(
    STDOUT,
    json_encode(
        [
            'ok' => true,
            'project_key' => $parsed['project_key'],
            'project_pid' => $parsed['project_pid'],
            'data_class_count' => count($extract['data_classes']),
            'field_count' => count($extract['fields']),
            'output_path' => $parsed['output_path'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ) . PHP_EOL,
);
