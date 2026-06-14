#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/domain_validation.php';
require_once dirname(__DIR__) . '/app/legacy_language_resource_reference.php';

function app_cli_export_legacy_language_resource_reference_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/export_legacy_language_resource_reference.php \
    --host-side \
    --project-key=MTOOL \
    --project-pid=1 \
    --sql-dump=original-codes/mtool.sql \
    --output=mtool/reference/mtool-legacy-language-resource-catalog.json

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
function app_cli_export_legacy_language_resource_reference_parse_args(array $argv): array
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

function app_cli_export_legacy_language_resource_reference_sql_unescape(string $value): string
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
 * @return array<string,string>
 */
function app_cli_export_legacy_language_resource_reference_existing_resource_key_map(string $outputPath): array
{
    if (!is_file($outputPath)) {
        return [];
    }

    $contents = file_get_contents($outputPath);
    if (!is_string($contents) || $contents === '') {
        return [];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded) || !is_array($decoded['resources'] ?? null)) {
        return [];
    }

    $map = [];
    foreach ($decoded['resources'] as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        $resourceKey = trim((string) ($resource['resource_key'] ?? ''));
        if ($legacyResourcePid <= 0 || $resourceKey === '') {
            continue;
        }

        $map[(string) $legacyResourcePid] = $resourceKey;
    }

    ksort($map, SORT_NATURAL);

    return $map;
}

/**
 * @return array{
 *     ok:bool,
 *     resources:list<array{
 *         legacy_resource_pid:int,
 *         project_pid:int,
 *         legacy_group_pid:int,
 *         resource_key:string,
 *         key_for_update:string,
 *         sort_group:string,
 *         key_name:string,
 *         key_name_for_xcode:string,
 *         uwp_target_property:string,
 *         is_resource_fixed:int,
 *         use_default_if_caption_is_blank:int
 *     }>,
 *     groups:list<array{
 *         legacy_group_pid:int,
 *         project_pid:int,
 *         name:string,
 *         function_name_prefix:string,
 *         function_name_suffix:string,
 *         filename_suffix_for_php:string,
 *         filename_suffix:string,
 *         filename_for_xcode:string,
 *         last_modified_dt:string
 *     }>,
 *     group_languages:list<array{
 *         legacy_group_language_pid:int,
 *         project_pid:int,
 *         legacy_group_pid:int,
 *         legacy_language_pid:int
 *     }>,
 *     group_source_outputs:list<array{
 *         legacy_group_source_output_pid:int,
 *         project_pid:int,
 *         legacy_group_pid:int,
 *         legacy_project_source_output_pid:int
 *     }>,
 *     additional_group_assignments:list<array{
 *         legacy_assignment_pid:int,
 *         project_pid:int,
 *         legacy_resource_pid:int,
 *         legacy_group_pid:int
 *     }>,
 *     captions:list<array{
 *         legacy_caption_pid:int,
 *         project_pid:int,
 *         legacy_resource_pid:int,
 *         legacy_group_pid:int,
 *         legacy_language_pid:int,
 *         caption:string,
 *         caption_auto_translated:string
 *     }>,
 *     languages:list<array{
 *         legacy_language_pid:int,
 *         filename_suffix:string,
 *         template_key:string,
 *         is_default:int,
 *         caption:string,
 *         lang_for_cs:string,
 *         lang_for_android:string,
 *         lang_for_ios:string,
 *         lang_for_google:string
 *     }>,
 *     error:string
 * }
 */
function app_cli_export_legacy_language_resource_reference_extract_from_dump(
    string $sqlDumpPath,
    int $projectPid,
): array {
    if (!is_file($sqlDumpPath)) {
        return [
            'ok' => false,
            'resources' => [],
            'groups' => [],
            'group_languages' => [],
            'group_source_outputs' => [],
            'additional_group_assignments' => [],
            'captions' => [],
            'languages' => [],
            'error' => 'SQL dump が見つかりません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $handle = fopen($sqlDumpPath, 'rb');
    if (!is_resource($handle)) {
        return [
            'ok' => false,
            'resources' => [],
            'groups' => [],
            'group_languages' => [],
            'group_source_outputs' => [],
            'additional_group_assignments' => [],
            'captions' => [],
            'languages' => [],
            'error' => 'SQL dump を開けません (host-side path expected): ' . $sqlDumpPath,
        ];
    }

    $resources = [];
    $groups = [];
    $groupLanguages = [];
    $groupSourceOutputs = [];
    $additionalGroupAssignments = [];
    $captions = [];
    $languages = [];
    $inResourceInsert = false;
    $inGroupInsert = false;
    $inGroupLanguageInsert = false;
    $inGroupSourceOutputInsert = false;
    $inAdditionalGroupAssignmentInsert = false;
    $inCaptionInsert = false;
    $inLanguageInsert = false;

    try {
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResource` (`PID`, `ProjectPID`, `LanguageResourceGroupPID`, `KeyForUpdate`, `SortGroup`, `KeyName`, `KeyNameForXcode`, `UWPTargetProperty`, `IsResourceFixed`, `UseDefaultIfCaptionIsBlank`) VALUES') {
                $inResourceInsert = true;
                $inGroupInsert = false;
                $inGroupLanguageInsert = false;
                $inGroupSourceOutputInsert = false;
                $inAdditionalGroupAssignmentInsert = false;
                $inCaptionInsert = false;
                $inLanguageInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResourceGroup` (`PID`, `ProjectPID`, `Name`, `FunctionNamePrefix`, `FunctionNameSuffix`, `FilenameSuffixForPHP`, `FilenameSuffix`, `FilenameForXcode`, `LastModifiedDT`) VALUES') {
                $inResourceInsert = false;
                $inGroupInsert = true;
                $inGroupLanguageInsert = false;
                $inGroupSourceOutputInsert = false;
                $inAdditionalGroupAssignmentInsert = false;
                $inCaptionInsert = false;
                $inLanguageInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResourceGroupLang` (`PID`, `ProjectPID`, `LanguageResourceGroupPID`, `LanguageResourceLangPID`) VALUES') {
                $inResourceInsert = false;
                $inGroupInsert = false;
                $inGroupLanguageInsert = true;
                $inGroupSourceOutputInsert = false;
                $inAdditionalGroupAssignmentInsert = false;
                $inCaptionInsert = false;
                $inLanguageInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResourceGroupProjectSourceOutput` (`PID`, `ProjectPID`, `LanguageResourceGroupPID`, `ProjectSourceOutputPID`) VALUES') {
                $inResourceInsert = false;
                $inGroupInsert = false;
                $inGroupLanguageInsert = false;
                $inGroupSourceOutputInsert = true;
                $inAdditionalGroupAssignmentInsert = false;
                $inCaptionInsert = false;
                $inLanguageInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResourceAdditionalGroupAssignment` (`PID`, `ProjectPID`, `LanguageResourcePID`, `LanguageResourceGroupPID`) VALUES') {
                $inResourceInsert = false;
                $inGroupInsert = false;
                $inGroupLanguageInsert = false;
                $inGroupSourceOutputInsert = false;
                $inAdditionalGroupAssignmentInsert = true;
                $inCaptionInsert = false;
                $inLanguageInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResourceCaption` (`PID`, `ProjectPID`, `LanguageResourcePID`, `LanguageResourceGroupPID`, `LanguageResourceLangPID`, `Caption`, `CaptionAutoTranslated`) VALUES') {
                $inResourceInsert = false;
                $inGroupInsert = false;
                $inGroupLanguageInsert = false;
                $inGroupSourceOutputInsert = false;
                $inAdditionalGroupAssignmentInsert = false;
                $inCaptionInsert = true;
                $inLanguageInsert = false;
                continue;
            }

            if ($trimmed === 'INSERT INTO `LanguageResourceLang` (`PID`, `FilenameSuffix`, `TemplateKey`, `IsDefault`, `Caption`, `LangForCS`, `LangForAndroid`, `LangForiOS`, `LangForGoogle`) VALUES') {
                $inResourceInsert = false;
                $inGroupInsert = false;
                $inGroupLanguageInsert = false;
                $inGroupSourceOutputInsert = false;
                $inAdditionalGroupAssignmentInsert = false;
                $inCaptionInsert = false;
                $inLanguageInsert = true;
                continue;
            }

            if ($inResourceInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[2] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $legacyResourcePid = (int) ($match[1] ?? 0);
                        $legacyGroupPid = (int) ($match[3] ?? 0);
                        $keyName = app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[6] ?? ''));
                        if ($legacyResourcePid <= 0 || $legacyGroupPid <= 0 || $keyName === '') {
                            continue;
                        }

                        $resources[] = [
                            'legacy_resource_pid' => $legacyResourcePid,
                            'project_pid' => $rowProjectPid,
                            'legacy_group_pid' => $legacyGroupPid,
                            'resource_key' => '',
                            'key_for_update' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[4] ?? '')),
                            'sort_group' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[5] ?? '')),
                            'key_name' => $keyName,
                            'key_name_for_xcode' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[7] ?? '')),
                            'uwp_target_property' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[8] ?? '')),
                            'is_resource_fixed' => (int) ($match[9] ?? 0),
                            'use_default_if_caption_is_blank' => (int) ($match[10] ?? 1),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inResourceInsert = false;
                }

                continue;
            }

            if ($inGroupInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[2] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $legacyGroupPid = (int) ($match[1] ?? 0);
                        $name = app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[3] ?? ''));
                        if ($legacyGroupPid <= 0 || $name === '') {
                            continue;
                        }

                        $groups[] = [
                            'legacy_group_pid' => $legacyGroupPid,
                            'project_pid' => $rowProjectPid,
                            'name' => $name,
                            'function_name_prefix' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[4] ?? '')),
                            'function_name_suffix' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[5] ?? '')),
                            'filename_suffix_for_php' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[6] ?? '')),
                            'filename_suffix' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[7] ?? '')),
                            'filename_for_xcode' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[8] ?? '')),
                            'last_modified_dt' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[9] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inGroupInsert = false;
                }

                continue;
            }

            if ($inGroupLanguageInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[2] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $groupLanguages[] = [
                            'legacy_group_language_pid' => (int) ($match[1] ?? 0),
                            'project_pid' => $rowProjectPid,
                            'legacy_group_pid' => (int) ($match[3] ?? 0),
                            'legacy_language_pid' => (int) ($match[4] ?? 0),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inGroupLanguageInsert = false;
                }

                continue;
            }

            if ($inGroupSourceOutputInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[2] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $groupSourceOutputs[] = [
                            'legacy_group_source_output_pid' => (int) ($match[1] ?? 0),
                            'project_pid' => $rowProjectPid,
                            'legacy_group_pid' => (int) ($match[3] ?? 0),
                            'legacy_project_source_output_pid' => (int) ($match[4] ?? 0),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inGroupSourceOutputInsert = false;
                }

                continue;
            }

            if ($inAdditionalGroupAssignmentInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+)\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[2] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $additionalGroupAssignments[] = [
                            'legacy_assignment_pid' => (int) ($match[1] ?? 0),
                            'project_pid' => $rowProjectPid,
                            'legacy_resource_pid' => (int) ($match[3] ?? 0),
                            'legacy_group_pid' => (int) ($match[4] ?? 0),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inAdditionalGroupAssignmentInsert = false;
                }

                continue;
            }

            if ($inCaptionInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $rowProjectPid = (int) ($match[2] ?? 0);
                        if ($rowProjectPid !== $projectPid) {
                            continue;
                        }

                        $captions[] = [
                            'legacy_caption_pid' => (int) ($match[1] ?? 0),
                            'project_pid' => $rowProjectPid,
                            'legacy_resource_pid' => (int) ($match[3] ?? 0),
                            'legacy_group_pid' => (int) ($match[4] ?? 0),
                            'legacy_language_pid' => (int) ($match[5] ?? 0),
                            'caption' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[6] ?? '')),
                            'caption_auto_translated' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[7] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inCaptionInsert = false;
                }

                continue;
            }

            if ($inLanguageInsert) {
                if (
                    preg_match_all(
                        "/\\((\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*(\\d+),\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)',\\s*'((?:\\\\.|[^'])*)'\\)\\s*[,;]/u",
                        $trimmed,
                        $matches,
                        PREG_SET_ORDER,
                    ) >= 1
                ) {
                    foreach ($matches as $match) {
                        $legacyLanguagePid = (int) ($match[1] ?? 0);
                        $caption = app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[5] ?? ''));
                        if ($legacyLanguagePid <= 0 || $caption === '') {
                            continue;
                        }

                        $languages[] = [
                            'legacy_language_pid' => $legacyLanguagePid,
                            'filename_suffix' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[2] ?? '')),
                            'template_key' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[3] ?? '')),
                            'is_default' => (int) ($match[4] ?? 0),
                            'caption' => $caption,
                            'lang_for_cs' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[6] ?? '')),
                            'lang_for_android' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[7] ?? '')),
                            'lang_for_ios' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[8] ?? '')),
                            'lang_for_google' => app_cli_export_legacy_language_resource_reference_sql_unescape((string) ($match[9] ?? '')),
                        ];
                    }
                }

                if (str_ends_with($trimmed, ';')) {
                    $inLanguageInsert = false;
                }
            }
        }
    } finally {
        fclose($handle);
    }

    $resources = app_legacy_language_resource_reference_assign_resource_keys($resources);

    usort(
        $groups,
        static fn (array $left, array $right): int
            => $left['legacy_group_pid'] <=> $right['legacy_group_pid'],
    );
    usort(
        $groupLanguages,
        static fn (array $left, array $right): int
            => [$left['legacy_group_pid'], $left['legacy_language_pid'], $left['legacy_group_language_pid']]
            <=> [$right['legacy_group_pid'], $right['legacy_language_pid'], $right['legacy_group_language_pid']],
    );
    usort(
        $groupSourceOutputs,
        static fn (array $left, array $right): int
            => [$left['legacy_group_pid'], $left['legacy_project_source_output_pid'], $left['legacy_group_source_output_pid']]
            <=> [$right['legacy_group_pid'], $right['legacy_project_source_output_pid'], $right['legacy_group_source_output_pid']],
    );
    usort(
        $additionalGroupAssignments,
        static fn (array $left, array $right): int
            => [$left['legacy_resource_pid'], $left['legacy_group_pid'], $left['legacy_assignment_pid']]
            <=> [$right['legacy_resource_pid'], $right['legacy_group_pid'], $right['legacy_assignment_pid']],
    );
    usort(
        $captions,
        static fn (array $left, array $right): int
            => [$left['legacy_resource_pid'], $left['legacy_language_pid'], $left['legacy_caption_pid']]
            <=> [$right['legacy_resource_pid'], $right['legacy_language_pid'], $right['legacy_caption_pid']],
    );
    usort(
        $languages,
        static fn (array $left, array $right): int
            => $left['legacy_language_pid'] <=> $right['legacy_language_pid'],
    );

    return [
        'ok' => true,
        'resources' => $resources,
        'groups' => $groups,
        'group_languages' => $groupLanguages,
        'group_source_outputs' => $groupSourceOutputs,
        'additional_group_assignments' => $additionalGroupAssignments,
        'captions' => $captions,
        'languages' => $languages,
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     legacy_resource_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     resource_key:string,
 *     key_for_update:string,
 *     sort_group:string,
 *     key_name:string,
 *     key_name_for_xcode:string,
 *     uwp_target_property:string,
 *     is_resource_fixed:int,
 *     use_default_if_caption_is_blank:int
 * }> $resources
 * @param list<array{
 *     legacy_group_pid:int,
 *     project_pid:int,
 *     name:string,
 *     function_name_prefix:string,
 *     function_name_suffix:string,
 *     filename_suffix_for_php:string,
 *     filename_suffix:string,
 *     filename_for_xcode:string,
 *     last_modified_dt:string
 * }> $groups
 * @param list<array{
 *     legacy_group_language_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     legacy_language_pid:int
 * }> $groupLanguages
 * @param list<array{
 *     legacy_group_source_output_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     legacy_project_source_output_pid:int
 * }> $groupSourceOutputs
 * @param list<array{
 *     legacy_assignment_pid:int,
 *     project_pid:int,
 *     legacy_resource_pid:int,
 *     legacy_group_pid:int
 * }> $additionalGroupAssignments
 * @param list<array{
 *     legacy_caption_pid:int,
 *     project_pid:int,
 *     legacy_resource_pid:int,
 *     legacy_group_pid:int,
 *     legacy_language_pid:int,
 *     caption:string,
 *     caption_auto_translated:string
 * }> $captions
 * @param list<array{
 *     legacy_language_pid:int,
 *     filename_suffix:string,
 *     template_key:string,
 *     is_default:int,
 *     caption:string,
 *     lang_for_cs:string,
 *     lang_for_android:string,
 *     lang_for_ios:string,
 *     lang_for_google:string
 * }> $languages
 * @return array{ok:bool,error:string}
 */
function app_cli_export_legacy_language_resource_reference_write_json(
    string $projectKey,
    int $projectPid,
    string $sqlDumpPath,
    string $outputPath,
    array $resources,
    array $groups,
    array $groupLanguages,
    array $groupSourceOutputs,
    array $additionalGroupAssignments,
    array $captions,
    array $languages,
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
        'resource_count' => count($resources),
        'group_count' => count($groups),
        'group_language_count' => count($groupLanguages),
        'group_source_output_count' => count($groupSourceOutputs),
        'additional_group_assignment_count' => count($additionalGroupAssignments),
        'caption_count' => count($captions),
        'language_count' => count($languages),
        'resources' => $resources,
        'groups' => $groups,
        'group_languages' => $groupLanguages,
        'group_source_outputs' => $groupSourceOutputs,
        'additional_group_assignments' => $additionalGroupAssignments,
        'captions' => $captions,
        'languages' => $languages,
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

function app_cli_export_legacy_language_resource_reference_main(array $argv): int
{
    $parsed = app_cli_export_legacy_language_resource_reference_parse_args($argv);
    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL);
        fwrite(STDERR, PHP_EOL . app_cli_export_legacy_language_resource_reference_usage() . PHP_EOL);
        return 1;
    }

    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_export_legacy_language_resource_reference_usage() . PHP_EOL);
        return 0;
    }

    $extract = app_cli_export_legacy_language_resource_reference_extract_from_dump(
        $parsed['sql_dump_path'],
        $parsed['project_pid'],
    );
    if (!$extract['ok']) {
        fwrite(STDERR, $extract['error'] . PHP_EOL);
        return 1;
    }

    $extract['resources'] = app_legacy_language_resource_reference_assign_resource_keys(
        $extract['resources'],
        app_cli_export_legacy_language_resource_reference_existing_resource_key_map($parsed['output_path']),
    );

    $write = app_cli_export_legacy_language_resource_reference_write_json(
        $parsed['project_key'],
        $parsed['project_pid'],
        $parsed['sql_dump_path'],
        $parsed['output_path'],
        $extract['resources'],
        $extract['groups'],
        $extract['group_languages'],
        $extract['group_source_outputs'],
        $extract['additional_group_assignments'],
        $extract['captions'],
        $extract['languages'],
    );
    if (!$write['ok']) {
        fwrite(STDERR, $write['error'] . PHP_EOL);
        return 1;
    }

    fwrite(
        STDOUT,
        'Exported legacy language resource reference: '
        . $parsed['output_path']
        . ' (resources=' . count($extract['resources'])
        . ', groups=' . count($extract['groups'])
        . ', group_languages=' . count($extract['group_languages'])
        . ', group_source_outputs=' . count($extract['group_source_outputs'])
        . ', additional_group_assignments=' . count($extract['additional_group_assignments'])
        . ', captions=' . count($extract['captions'])
        . ', languages=' . count($extract['languages']) . ')'
        . PHP_EOL,
    );

    return 0;
}

exit(app_cli_export_legacy_language_resource_reference_main($argv));
