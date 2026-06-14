#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/project_output_runtime_generator.php';

function app_cli_runtime_replacement_rollout_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/show_runtime_replacement_rollout.php [--manifest=PATH] [--non-plain-only]

Options:
  --manifest=PATH   runtime-generation-manifest.json path
                    (default: mtool/reference/dbclasses/_support/runtime-generation-manifest.json)
  --non-plain-only  non-plain `data-*` だけ表示する
  --help            この help を表示する
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     manifest_path:string,
 *     non_plain_only:bool,
 *     help:bool,
 *     error:string
 * }
 */
function app_cli_runtime_replacement_rollout_parse_args(array $argv): array
{
    $manifestPath = dirname(__DIR__) . '/reference/dbclasses/_support/runtime-generation-manifest.json';
    $nonPlainOnly = false;

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'manifest_path' => $manifestPath,
                'non_plain_only' => $nonPlainOnly,
                'help' => true,
                'error' => '',
            ];
        }

        if ($argument === '--non-plain-only') {
            $nonPlainOnly = true;
            continue;
        }

        if (str_starts_with($argument, '--manifest=')) {
            $manifestPath = substr($argument, strlen('--manifest='));
            continue;
        }

        return [
            'ok' => false,
            'manifest_path' => $manifestPath,
            'non_plain_only' => $nonPlainOnly,
            'help' => false,
            'error' => 'Unknown option: ' . $argument,
        ];
    }

    return [
        'ok' => true,
        'manifest_path' => $manifestPath,
        'non_plain_only' => $nonPlainOnly,
        'help' => false,
        'error' => '',
    ];
}

/**
 * @param array<string,mixed> $manifest
 * @return array{
 *     counts:array<string,mixed>,
 *     items:list<array<string,mixed>>
 * }
 */
function app_cli_runtime_replacement_rollout_payload(array $manifest, bool $nonPlainOnly): array
{
    $generationSummary = is_array($manifest['generation_summary'] ?? null)
        ? $manifest['generation_summary']
        : [];
    $rawItems = $generationSummary['data_generation_items'] ?? [];
    if (!is_array($rawItems)) {
        $rawItems = [];
    }

    $items = [];
    $laneCounts = [];
    $gateTypeCounts = [];
    $totalItems = 0;
    $nonPlainItems = 0;
    $unclassifiedNonPlainItems = 0;

    foreach ($rawItems as $rawItem) {
        if (!is_array($rawItem)) {
            continue;
        }

        $isPlainCandidate = (bool) ($rawItem['is_plain_candidate'] ?? false);
        $rolloutGate = app_project_output_runtime_data_rollout_gate(
            (string) ($rawItem['source_name'] ?? ''),
            $isPlainCandidate,
        );

        $totalItems++;
        if (!$isPlainCandidate) {
            $nonPlainItems++;
            if ($rolloutGate['gate_type'] === 'manual-classification') {
                $unclassifiedNonPlainItems++;
            }
        }

        if ($nonPlainOnly && $isPlainCandidate) {
            continue;
        }

        $lane = $rolloutGate['lane'];
        $gateType = $rolloutGate['gate_type'];
        $laneCounts[$lane] = ($laneCounts[$lane] ?? 0) + 1;
        $gateTypeCounts[$gateType] = ($gateTypeCounts[$gateType] ?? 0) + 1;

        $rawReasonCode = (string) ($rawItem['reason_code'] ?? '');

        $items[] = [
            'source_name' => (string) ($rawItem['source_name'] ?? ''),
            'data_file' => (string) ($rawItem['data_file'] ?? ''),
            'decision' => (string) ($rawItem['decision'] ?? ''),
            'reason_code' => app_project_output_runtime_normalize_generation_reason_code($rawReasonCode),
            'raw_reason_code' => $rawReasonCode,
            'is_plain_candidate' => $isPlainCandidate,
            'class_count' => (int) ($rawItem['class_count'] ?? 0),
            'extra_method_names' => is_array($rawItem['extra_method_names'] ?? null)
                ? array_values($rawItem['extra_method_names'])
                : [],
            'has_top_level_function' => (bool) ($rawItem['has_top_level_function'] ?? false),
            'has_default_property_value' => (bool) ($rawItem['has_default_property_value'] ?? false),
            'rollout_gate_type' => $gateType,
            'rollout_lane' => $lane,
            'rollout_gate_reference' => $rolloutGate['gate_reference'],
            'rollout_note' => $rolloutGate['note'],
        ];
    }

    ksort($laneCounts, SORT_STRING);
    ksort($gateTypeCounts, SORT_STRING);

    return [
        'counts' => [
            'total_items' => $totalItems,
            'non_plain_items' => $nonPlainItems,
            'unclassified_non_plain_items' => $unclassifiedNonPlainItems,
            'lane_counts' => $laneCounts,
            'gate_type_counts' => $gateTypeCounts,
        ],
        'items' => $items,
    ];
}

$parsed = app_cli_runtime_replacement_rollout_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_runtime_replacement_rollout_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_runtime_replacement_rollout_usage() . PHP_EOL);
    exit(2);
}

$manifestPath = $parsed['manifest_path'];
if ($manifestPath === '' || !is_file($manifestPath) || !is_readable($manifestPath)) {
    fwrite(STDERR, 'manifest file が読めません: ' . $manifestPath . PHP_EOL);
    exit(1);
}

$manifestContents = file_get_contents($manifestPath);
if (!is_string($manifestContents) || $manifestContents === '') {
    fwrite(STDERR, 'manifest file の読み込みに失敗しました: ' . $manifestPath . PHP_EOL);
    exit(1);
}

$manifest = json_decode($manifestContents, true);
if (!is_array($manifest)) {
    fwrite(STDERR, 'manifest JSON の decode に失敗しました: ' . $manifestPath . PHP_EOL);
    exit(1);
}

$payload = app_cli_runtime_replacement_rollout_payload($manifest, $parsed['non_plain_only']);
$payload['manifest_path'] = $manifestPath;
$payload['artifact_key'] = (string) ($manifest['artifact_key'] ?? '');
$payload['project_key'] = (string) ($manifest['project_key'] ?? '');
$payload['source_output_key'] = (string) ($manifest['source_output_key'] ?? '');
$payload['generated_at'] = (string) ($manifest['generated_at'] ?? '');
$payload['non_plain_only'] = $parsed['non_plain_only'];

$encoded = json_encode(
    $payload,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
);
if (!is_string($encoded) || $encoded === '') {
    fwrite(STDERR, "payload encode に失敗しました\n");
    exit(1);
}

fwrite(STDOUT, $encoded . PHP_EOL);
