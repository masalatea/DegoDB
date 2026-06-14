#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/legacy_html_reference.php';

function app_cli_export_legacy_html_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_legacy_html_reference.php \
    --host-side \
    --project-key=MTOOL \
    --project-pid=1 \
    --sql-dump=original-codes/mtool.sql \
    --output=mtool/reference/mtool-legacy-html-catalog.json

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
function app_cli_export_legacy_html_reference_parse_args(array $argv): array
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

function app_cli_export_legacy_html_reference_sql_unescape(string $value): string
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

function app_cli_export_legacy_html_reference_html_key_candidate(string $name, int $legacyHtmlPid): string
{
    return app_legacy_html_reference_html_key_candidate($name, $legacyHtmlPid);
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }> $htmls
 * @return list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }>
 */
function app_cli_export_legacy_html_reference_assign_html_keys(array $htmls): array
{
    return app_legacy_html_reference_assign_html_keys($htmls);
}

/**
 * @return array<string,string>
 */
function app_cli_export_legacy_html_reference_existing_html_key_map(string $outputPath): array
{
    if (!is_file($outputPath)) {
        return [];
    }

    $contents = file_get_contents($outputPath);
    if (!is_string($contents) || $contents === '') {
        return [];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded) || !is_array($decoded['htmls'] ?? null)) {
        return [];
    }

    $map = [];
    foreach ($decoded['htmls'] as $html) {
        if (!is_array($html)) {
            continue;
        }

        $legacyHtmlPid = (int) ($html['legacy_html_pid'] ?? 0);
        $htmlKey = trim((string) ($html['html_key'] ?? ''));
        if ($legacyHtmlPid <= 0 || $htmlKey === '') {
            continue;
        }

        $map[(string) $legacyHtmlPid] = $htmlKey;
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array{
 *     ok:bool,
 *     htmls:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         html_key:string,
 *         name:string,
 *         legacy_project_source_output_pid:int,
 *         legacy_html_template_pid:int,
 *         last_modified_dt:string
 *     }>,
 *     parameters:list<array{
 *         project_pid:int,
 *         legacy_html_pid:int,
 *         legacy_parameter_pid:int,
 *         parameter_name:string,
 *         parameter_value:string
 *     }>,
 *     templates:list<array{
 *         legacy_html_template_pid:int,
 *         target_type:string,
 *         parent_html_template_pid:int,
 *         name:string,
 *         program_language:string,
 *         file_name:string,
 *         comment:string
 *     }>,
 *     template_parameters:list<array{
 *         legacy_html_template_pid:int,
 *         legacy_template_parameter_pid:int,
 *         parameter_name:string,
 *         target_value_type:string,
 *         target_variable_or_class_object:string,
 *         target_property_of_class_object:string,
 *         another_template_pid:int,
 *         trim_last_space:int,
 *         trim_last_return:int,
 *         data_type:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_export_legacy_html_reference_extract_from_dump(string $sqlDumpPath, int $projectPid): array
{
    if (!is_file($sqlDumpPath)) {
        return [
            'ok' => false,
            'htmls' => [],
            'parameters' => [],
            'templates' => [],
            'template_parameters' => [],
            'error' => 'SQL dump が見つかりません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $handle = fopen($sqlDumpPath, 'rb');
    if (!is_resource($handle)) {
        return [
            'ok' => false,
            'htmls' => [],
            'parameters' => [],
            'templates' => [],
            'template_parameters' => [],
            'error' => 'SQL dump を開けません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $htmls = [];
    $parameters = [];
    $templates = [];
    $templateParameters = [];
    $inHtmlInsert = false;
    $inHtmlParameterInsert = false;
    $inHtmlTemplateInsert = false;
    $inHtmlTemplateParameterInsert = false;

    try {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($trimmed === 'INSERT INTO `html` (`ProjectPID`, `PID`, `name`, `ProjectSourceOutputPID`, `htmlTemplatePID`, `LastModifiedDT`) VALUES') {
                $inHtmlInsert = true;
                $inHtmlParameterInsert = false;
                $inHtmlTemplateInsert = false;
                $inHtmlTemplateParameterInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `htmlParameter` (`ProjectPID`, `htmlPID`, `PID`, `ParameterName`, `ParameterValue`) VALUES') {
                $inHtmlInsert = false;
                $inHtmlParameterInsert = true;
                $inHtmlTemplateInsert = false;
                $inHtmlTemplateParameterInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `htmlTemplate` (`PID`, `TargetType`, `ParentHtmlTemplatePID`, `name`, `ProgramLanguage`, `FileName`, `Comment`) VALUES') {
                $inHtmlInsert = false;
                $inHtmlParameterInsert = false;
                $inHtmlTemplateInsert = true;
                $inHtmlTemplateParameterInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `htmlTemplateParameter` (`htmlTemplatePID`, `PID`, `ParameterName`, `TargetValueType`, `TargetVariableOrClassObject`, `TargetPropertyOfClassObject`, `AnotherTemplatePID`, `TrimLastSpace`, `TrimLastReturn`, `DataType`) VALUES') {
                $inHtmlInsert = false;
                $inHtmlParameterInsert = false;
                $inHtmlTemplateInsert = false;
                $inHtmlTemplateParameterInsert = true;
                continue;
            }

            if ($inHtmlInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
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

                        $legacyHtmlPid = (int) ($match[2] ?? 0);
                        $name = app_cli_export_legacy_html_reference_sql_unescape((string) ($match[3] ?? ''));
                        if ($legacyHtmlPid <= 0 || $name === '') {
                            continue;
                        }

                        $htmls[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_html_pid' => $legacyHtmlPid,
                            'name' => $name,
                            'legacy_project_source_output_pid' => (int) ($match[4] ?? 0),
                            'legacy_html_template_pid' => (int) ($match[5] ?? 0),
                            'last_modified_dt' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[6] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inHtmlInsert = false;
                }

                continue;
            }

            if ($inHtmlParameterInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
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

                        $legacyHtmlPid = (int) ($match[2] ?? 0);
                        $legacyParameterPid = (int) ($match[3] ?? 0);
                        $parameterName = app_cli_export_legacy_html_reference_sql_unescape((string) ($match[4] ?? ''));
                        if ($legacyHtmlPid <= 0 || $legacyParameterPid <= 0 || $parameterName === '') {
                            continue;
                        }

                        $parameters[] = [
                            'project_pid' => $rowProjectPid,
                            'legacy_html_pid' => $legacyHtmlPid,
                            'legacy_parameter_pid' => $legacyParameterPid,
                            'parameter_name' => $parameterName,
                            'parameter_value' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[5] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inHtmlParameterInsert = false;
                }

                continue;
            }

            if ($inHtmlTemplateInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $legacyTemplatePid = (int) ($match[1] ?? 0);
                        $name = app_cli_export_legacy_html_reference_sql_unescape((string) ($match[4] ?? ''));
                        if ($legacyTemplatePid <= 0 || $name === '') {
                            continue;
                        }

                        $templates[] = [
                            'legacy_html_template_pid' => $legacyTemplatePid,
                            'target_type' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[2] ?? '')),
                            'parent_html_template_pid' => (int) ($match[3] ?? 0),
                            'name' => $name,
                            'program_language' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[5] ?? '')),
                            'file_name' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[6] ?? '')),
                            'comment' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[7] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inHtmlTemplateInsert = false;
                }

                continue;
            }

            if ($inHtmlTemplateParameterInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $legacyTemplatePid = (int) ($match[1] ?? 0);
                        $legacyTemplateParameterPid = (int) ($match[2] ?? 0);
                        $parameterName = app_cli_export_legacy_html_reference_sql_unescape((string) ($match[3] ?? ''));
                        if ($legacyTemplatePid <= 0 || $legacyTemplateParameterPid <= 0 || $parameterName === '') {
                            continue;
                        }

                        $templateParameters[] = [
                            'legacy_html_template_pid' => $legacyTemplatePid,
                            'legacy_template_parameter_pid' => $legacyTemplateParameterPid,
                            'parameter_name' => $parameterName,
                            'target_value_type' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[4] ?? '')),
                            'target_variable_or_class_object' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[5] ?? '')),
                            'target_property_of_class_object' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[6] ?? '')),
                            'another_template_pid' => (int) ($match[7] ?? 0),
                            'trim_last_space' => (int) ($match[8] ?? 0),
                            'trim_last_return' => (int) ($match[9] ?? 0),
                            'data_type' => app_cli_export_legacy_html_reference_sql_unescape((string) ($match[10] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inHtmlTemplateParameterInsert = false;
                }
            }
        }
    } finally {
        fclose($handle);
    }

    usort(
        $parameters,
        static function (array $left, array $right): int {
            if ($left['legacy_html_pid'] !== $right['legacy_html_pid']) {
                return $left['legacy_html_pid'] <=> $right['legacy_html_pid'];
            }

            return $left['legacy_parameter_pid'] <=> $right['legacy_parameter_pid'];
        },
    );

    usort(
        $templates,
        static fn (array $left, array $right): int
            => $left['legacy_html_template_pid'] <=> $right['legacy_html_template_pid'],
    );

    usort(
        $templateParameters,
        static function (array $left, array $right): int {
            if ($left['legacy_html_template_pid'] !== $right['legacy_html_template_pid']) {
                return $left['legacy_html_template_pid'] <=> $right['legacy_html_template_pid'];
            }

            return $left['legacy_template_parameter_pid'] <=> $right['legacy_template_parameter_pid'];
        },
    );

    return [
        'ok' => true,
        'htmls' => app_cli_export_legacy_html_reference_assign_html_keys($htmls),
        'parameters' => $parameters,
        'templates' => $templates,
        'template_parameters' => $templateParameters,
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     html_key:string,
 *     name:string,
 *     legacy_project_source_output_pid:int,
 *     legacy_html_template_pid:int,
 *     last_modified_dt:string
 * }> $htmls
 * @param list<array{
 *     project_pid:int,
 *     legacy_html_pid:int,
 *     legacy_parameter_pid:int,
 *     parameter_name:string,
 *     parameter_value:string
 * }> $parameters
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     target_type:string,
 *     parent_html_template_pid:int,
 *     name:string,
 *     program_language:string,
 *     file_name:string,
 *     comment:string
 * }> $templates
 * @param list<array{
 *     legacy_html_template_pid:int,
 *     legacy_template_parameter_pid:int,
 *     parameter_name:string,
 *     target_value_type:string,
 *     target_variable_or_class_object:string,
 *     target_property_of_class_object:string,
 *     another_template_pid:int,
 *     trim_last_space:int,
 *     trim_last_return:int,
 *     data_type:string
 * }> $templateParameters
 */
function app_cli_export_legacy_html_reference_write_json(
    string $projectKey,
    int $projectPid,
    string $sqlDumpPath,
    string $outputPath,
    array $htmls,
    array $parameters,
    array $templates,
    array $templateParameters,
): array {
    $directory = dirname($outputPath);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        return [
            'ok' => false,
            'error' => '出力先 directory を作成できません: ' . $directory,
        ];
    }

    $payload = [
        'project_key' => $projectKey,
        'project_pid' => $projectPid,
        'source_dump_path' => $sqlDumpPath,
        'generated_at' => gmdate('c'),
        'html_count' => count($htmls),
        'parameter_count' => count($parameters),
        'template_count' => count($templates),
        'template_parameter_count' => count($templateParameters),
        'htmls' => $htmls,
        'parameters' => $parameters,
        'templates' => $templates,
        'template_parameters' => $templateParameters,
    ];

    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        return [
            'ok' => false,
            'error' => 'JSON encode に失敗しました。',
        ];
    }

    $encoded .= "\n";
    if (file_put_contents($outputPath, $encoded) === false) {
        return [
            'ok' => false,
            'error' => 'JSON を書き込めません: ' . $outputPath,
        ];
    }

    return [
        'ok' => true,
        'error' => '',
    ];
}

function app_cli_export_legacy_html_reference_main(array $argv): int
{
    $parsed = app_cli_export_legacy_html_reference_parse_args($argv);
    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL);
        fwrite(STDERR, PHP_EOL . app_cli_export_legacy_html_reference_usage() . PHP_EOL);
        return 1;
    }

    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_export_legacy_html_reference_usage() . PHP_EOL);
        return 0;
    }

    $extract = app_cli_export_legacy_html_reference_extract_from_dump(
        $parsed['sql_dump_path'],
        $parsed['project_pid'],
    );
    if (!$extract['ok']) {
        fwrite(STDERR, $extract['error'] . PHP_EOL);
        return 1;
    }

    $extract['htmls'] = app_legacy_html_reference_assign_html_keys(
        $extract['htmls'],
        app_cli_export_legacy_html_reference_existing_html_key_map($parsed['output_path']),
    );

    $write = app_cli_export_legacy_html_reference_write_json(
        $parsed['project_key'],
        $parsed['project_pid'],
        $parsed['sql_dump_path'],
        $parsed['output_path'],
        $extract['htmls'],
        $extract['parameters'],
        $extract['templates'],
        $extract['template_parameters'],
    );
    if (!$write['ok']) {
        fwrite(STDERR, $write['error'] . PHP_EOL);
        return 1;
    }

    fwrite(
        STDOUT,
        'Exported legacy html reference: '
        . $parsed['output_path']
        . ' (html=' . count($extract['htmls'])
        . ', parameters=' . count($extract['parameters'])
        . ', templates=' . count($extract['templates'])
        . ', template_parameters=' . count($extract['template_parameters']) . ')'
        . PHP_EOL,
    );

    return 0;
}

exit(app_cli_export_legacy_html_reference_main($argv));
