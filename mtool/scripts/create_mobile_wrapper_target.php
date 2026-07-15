#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/mobile_wrapper_target.php';

function app_cli_mobile_wrapper_target_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=c1 --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/mobile-wrapper-target
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=react-wrapper-app --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-wrapper-app-handoff
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=external-output --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-web-capacitor-output
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=ai-task-packet --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/ai-task-packet
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=output-mode-config --output-mode=hybrid --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=pwa-readiness --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/pwa-readiness
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=platform-input-packets --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/later-platform-input-packets
  php mtool/scripts/create_mobile_wrapper_target.php --sample=sample28 --artifact=bundle-manifest --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/mobile-wrapper-bundle
  php mtool/scripts/create_mobile_wrapper_target.php --handoff-file=work/mobile-app-handoff.json --artifact=react-wrapper-app --target-dir=work/mobile-wrapper-target/react-wrapper-app-handoff
  php mtool/scripts/create_mobile_wrapper_target.php --project-key=PROJECT --source-output-key=MOBILE-HANDOFF --artifact=bundle-manifest --target-dir=work/mobile-wrapper-target/mobile-wrapper-bundle

Options:
  --sample=sample28       Emit the current supported sample mobile wrapper C1 package.
  --handoff-file=PATH     Emit from a validated mobile-app-handoff JSON packet.
  --project-key=KEY       Resolve work/source-outputs/{PROJECT}/{SOURCE_OUTPUT}/mobile-app-handoff.json.
  --source-output-key=KEY Source output key used with --project-key.
  --source-output-root=DIR Root for project/source-output lookup. Default: work/source-outputs.
  --artifact=NAME         c1, react-wrapper-app, external-output, ai-task-packet, output-mode-config, pwa-readiness, platform-input-packets, or bundle-manifest. Default: c1.
  --output-mode=MODE      mtool_no_code, external_no_code, or hybrid. Used by output-mode-config. Default: hybrid.
  --target-dir=DIR        Controlled artifact directory to create. Existing files are not overwritten.
  --help                  Show this help.

Boundary:
  The c1 artifact emits only wrapper-target-contract.json and WRAPPER-CONSUMER-NOTES.md.
  The react-wrapper-app artifact emits only react-wrapper-app-handoff.json and REACT-WRAPPER-APP-HANDOFF.md.
  The external-output artifact emits only external-output.json and EXTERNAL-OUTPUT.md.
  The ai-task-packet artifact emits only task.json, TASK.md, and declared input JSON files.
  The output-mode-config artifact emits only output-mode-config.json and OUTPUT-MODE-CONFIG.md.
  The pwa-readiness artifact emits only pwa-readiness.json and PWA-READINESS.md.
  The platform-input-packets artifact emits Flutter WebView wrapper and React Native input packets only.
  The bundle-manifest artifact emits an index/checklist for the mobile wrapper package set.
  No artifact creates package.json, capacitor.config.ts, ios/, android/, signing config, or store submission files.
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{ok:bool,help:bool,sample:string,handoff_file:string,project_key:string,source_output_key:string,source_output_root:string,artifact:string,output_mode:string,target_dir:string,error:string}
 */
function app_cli_mobile_wrapper_target_parse_args(array $argv): array
{
    $sample = '';
    $handoffFile = '';
    $projectKey = '';
    $sourceOutputKey = '';
    $sourceOutputRoot = 'work/source-outputs';
    $artifact = 'c1';
    $outputMode = 'hybrid';
    $targetDir = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'sample' => '',
                'handoff_file' => '',
                'project_key' => '',
                'source_output_key' => '',
                'source_output_root' => $sourceOutputRoot,
                'artifact' => $artifact,
                'output_mode' => $outputMode,
                'target_dir' => '',
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--sample=')) {
            $sample = strtolower(trim(substr($argument, strlen('--sample='))));
            continue;
        }

        if (str_starts_with($argument, '--handoff-file=')) {
            $handoffFile = trim(substr($argument, strlen('--handoff-file=')));
            continue;
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = trim(substr($argument, strlen('--project-key=')));
            continue;
        }

        if (str_starts_with($argument, '--source-output-key=')) {
            $sourceOutputKey = trim(substr($argument, strlen('--source-output-key=')));
            continue;
        }

        if (str_starts_with($argument, '--source-output-root=')) {
            $sourceOutputRoot = rtrim(trim(substr($argument, strlen('--source-output-root='))), DIRECTORY_SEPARATOR);
            continue;
        }

        if (str_starts_with($argument, '--artifact=')) {
            $artifact = strtolower(trim(substr($argument, strlen('--artifact='))));
            continue;
        }

        if (str_starts_with($argument, '--output-mode=')) {
            $outputMode = strtolower(trim(substr($argument, strlen('--output-mode='))));
            continue;
        }

        if (str_starts_with($argument, '--target-dir=')) {
            $targetDir = trim(substr($argument, strlen('--target-dir=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'sample' => '',
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => '',
            'error' => 'unsupported argument: ' . $argument,
        ];
    }

    $sourceModes = 0;
    if ($sample !== '') $sourceModes++;
    if ($handoffFile !== '') $sourceModes++;
    if ($projectKey !== '' || $sourceOutputKey !== '') $sourceModes++;

    if ($sourceModes !== 1) {
        return [
            'ok' => false,
            'help' => false,
            'sample' => $sample,
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => $targetDir,
            'error' => 'specify exactly one source: --sample=sample28, --handoff-file=PATH, or --project-key=KEY --source-output-key=KEY',
        ];
    }

    if ($sample !== '' && $sample !== 'sample28') {
        return [
            'ok' => false,
            'help' => false,
            'sample' => $sample,
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => $targetDir,
            'error' => 'supported --sample is currently sample28',
        ];
    }

    if (($projectKey !== '' || $sourceOutputKey !== '') && ($projectKey === '' || $sourceOutputKey === '')) {
        return [
            'ok' => false,
            'help' => false,
            'sample' => $sample,
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => $targetDir,
            'error' => '--project-key and --source-output-key must be specified together',
        ];
    }

    if (!in_array($artifact, ['c1', 'react-wrapper-app', 'external-output', 'ai-task-packet', 'output-mode-config', 'pwa-readiness', 'platform-input-packets', 'bundle-manifest'], true)) {
        return [
            'ok' => false,
            'help' => false,
            'sample' => $sample,
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => $targetDir,
            'error' => 'supported --artifact values are c1, react-wrapper-app, external-output, ai-task-packet, output-mode-config, pwa-readiness, platform-input-packets, and bundle-manifest',
        ];
    }

    if (app_mobile_wrapper_target_normalize_output_mode($outputMode) === '') {
        return [
            'ok' => false,
            'help' => false,
            'sample' => $sample,
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => $targetDir,
            'error' => 'supported --output-mode values are mtool_no_code, external_no_code, and hybrid',
        ];
    }

    if ($targetDir === '' || $targetDir === '.' || $targetDir === DIRECTORY_SEPARATOR) {
        return [
            'ok' => false,
            'help' => false,
            'sample' => $sample,
            'handoff_file' => $handoffFile,
            'project_key' => $projectKey,
            'source_output_key' => $sourceOutputKey,
            'source_output_root' => $sourceOutputRoot,
            'artifact' => $artifact,
            'output_mode' => $outputMode,
            'target_dir' => $targetDir,
            'error' => 'valid --target-dir is required',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'sample' => $sample,
        'handoff_file' => $handoffFile,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'source_output_root' => $sourceOutputRoot,
        'artifact' => $artifact,
        'output_mode' => $outputMode,
        'target_dir' => $targetDir,
        'error' => '',
    ];
}

/**
 * @param array{sample:string,handoff_file:string,project_key:string,source_output_key:string,source_output_root:string} $parsed
 * @return array{ok:bool,error:string,handoff:array<string,mixed>|null,source:string}
 */
function app_cli_mobile_wrapper_target_load_handoff(array $parsed): array
{
    if ($parsed['sample'] === 'sample28') {
        return [
            'ok' => true,
            'error' => '',
            'handoff' => app_mobile_wrapper_target_sample28_c1_handoff(),
            'source' => 'sample28',
        ];
    }

    $path = $parsed['handoff_file'] !== ''
        ? $parsed['handoff_file']
        : app_cli_mobile_wrapper_target_project_source_output_handoff_path(
            $parsed['source_output_root'],
            $parsed['project_key'],
            $parsed['source_output_key'],
        );
    if ($path === '' || !is_file($path)) {
        return [
            'ok' => false,
            'error' => 'handoff file not found',
            'handoff' => null,
            'source' => $parsed['handoff_file'] !== '' ? 'handoff-file' : 'project-source-output',
        ];
    }

    try {
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        return [
            'ok' => false,
            'error' => 'invalid handoff JSON',
            'handoff' => null,
            'source' => $parsed['handoff_file'] !== '' ? 'handoff-file' : 'project-source-output',
        ];
    }

    if (!is_array($decoded) || array_is_list($decoded)) {
        return [
            'ok' => false,
            'error' => 'handoff JSON must be an object',
            'handoff' => null,
            'source' => $parsed['handoff_file'] !== '' ? 'handoff-file' : 'project-source-output',
        ];
    }

    return [
        'ok' => true,
        'error' => '',
        'handoff' => $decoded,
        'source' => $parsed['handoff_file'] !== '' ? 'handoff-file' : 'project-source-output',
    ];
}

function app_cli_mobile_wrapper_target_project_source_output_handoff_path(
    string $sourceOutputRoot,
    string $projectKey,
    string $sourceOutputKey,
): string {
    if ($sourceOutputRoot === '' || $projectKey === '' || $sourceOutputKey === '') {
        return '';
    }
    if (str_contains($projectKey, '..') || str_contains($sourceOutputKey, '..')) {
        return '';
    }
    return rtrim($sourceOutputRoot, DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . $projectKey
        . DIRECTORY_SEPARATOR . $sourceOutputKey
        . DIRECTORY_SEPARATOR . 'mobile-app-handoff.json';
}

/**
 * @param array{sample:string,handoff_file:string,project_key:string,source_output_key:string,source_output_root:string,artifact:string,output_mode:string,target_dir:string} $parsed
 * @return array{ok:bool,error:string,target_dir:string,files:list<string>,validation:array<string,mixed>,source:string}
 */
function app_cli_mobile_wrapper_target_emit_from_parsed(array $parsed): array
{
    $handoffResult = app_cli_mobile_wrapper_target_load_handoff($parsed);
    if (!$handoffResult['ok'] || !is_array($handoffResult['handoff'])) {
        return [
            'ok' => false,
            'error' => $handoffResult['error'],
            'target_dir' => $parsed['target_dir'],
            'files' => [],
            'validation' => [],
            'source' => $handoffResult['source'],
        ];
    }

    $result = match ($parsed['artifact']) {
        'react-wrapper-app' => app_mobile_wrapper_target_emit_react_app_handoff_proof($handoffResult['handoff'], $parsed['target_dir']),
        'external-output' => app_mobile_wrapper_target_emit_external_optional_output_packet($handoffResult['handoff'], $parsed['target_dir']),
        'ai-task-packet' => app_mobile_wrapper_target_emit_external_ai_task_packet($handoffResult['handoff'], $parsed['target_dir']),
        'output-mode-config' => app_mobile_wrapper_target_emit_output_mode_config($handoffResult['handoff'], $parsed['target_dir'], $parsed['output_mode']),
        'pwa-readiness' => app_mobile_wrapper_target_emit_pwa_readiness($handoffResult['handoff'], $parsed['target_dir']),
        'platform-input-packets' => app_mobile_wrapper_target_emit_later_platform_input_packets($handoffResult['handoff'], $parsed['target_dir']),
        'bundle-manifest' => app_mobile_wrapper_target_emit_bundle_manifest($handoffResult['handoff'], $parsed['target_dir'], $parsed['output_mode']),
        default => app_mobile_wrapper_target_emit_c1_package($handoffResult['handoff'], $parsed['target_dir']),
    };

    return $result + ['source' => $handoffResult['source']];
}

/**
 * @param list<string> $argv
 */
function app_cli_mobile_wrapper_target_main(array $argv): int
{
    $parsed = app_cli_mobile_wrapper_target_parse_args($argv);
    if ($parsed['help']) {
        fwrite(STDOUT, app_cli_mobile_wrapper_target_usage() . PHP_EOL);
        return 0;
    }

    if (!$parsed['ok']) {
        fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_mobile_wrapper_target_usage() . PHP_EOL);
        return 64;
    }

    $result = app_cli_mobile_wrapper_target_emit_from_parsed($parsed);
    $summary = [
        'ok' => $result['ok'],
        'source' => $result['source'],
        'sample' => $parsed['sample'],
        'handoff_file' => $parsed['handoff_file'],
        'project_key' => $parsed['project_key'],
        'source_output_key' => $parsed['source_output_key'],
        'artifact' => $parsed['artifact'],
        'output_mode' => $parsed['output_mode'],
        'target_dir' => $result['target_dir'],
        'files' => $result['files'],
        'error' => $result['error'],
    ];

    $stream = $result['ok'] ? STDOUT : STDERR;
    fwrite($stream, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

    return $result['ok'] ? 0 : 1;
}

if (PHP_SAPI === 'cli' && realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(app_cli_mobile_wrapper_target_main($argv));
}
