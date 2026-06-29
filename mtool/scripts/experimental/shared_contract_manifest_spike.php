#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/data_class_repository.php';
require_once dirname(__DIR__, 2) . '/app/domain_validation.php';

function app_cli_shared_contract_manifest_spike_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/experimental/shared_contract_manifest_spike.php --project-key=SAMPLE02 --output-dir=work/feasibility/shared-contract-manifest-sample02 [--generated-root=sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP]

Options:
  --project-key=KEY       project key to read from config DB
  --output-dir=PATH       output directory for manifest.json and summary.json
  --generated-root=PATH   optional generated PHP DataClass root for property comparison
  --help                  show this help
TEXT;
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     output_dir:string,
 *     generated_root:string,
 *     error:string
 * }
 */
function app_cli_shared_contract_manifest_spike_parse_args(array $argv): array
{
    $projectKey = '';
    $outputDir = '';
    $generatedRoot = '';

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'output_dir' => '',
                'generated_root' => '',
                'error' => '',
            ];
        }

        if (str_starts_with($argument, '--project-key=')) {
            $projectKey = app_normalize_project_key(substr($argument, strlen('--project-key=')));
            continue;
        }
        if (str_starts_with($argument, '--output-dir=')) {
            $outputDir = trim(substr($argument, strlen('--output-dir=')));
            continue;
        }
        if (str_starts_with($argument, '--generated-root=')) {
            $generatedRoot = trim(substr($argument, strlen('--generated-root=')));
            continue;
        }

        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'generated_root' => '',
            'error' => 'unsupported argument: ' . $argument,
        ];
    }

    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'generated_root' => '',
            'error' => 'valid --project-key=... is required.',
        ];
    }
    if ($outputDir === '') {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'output_dir' => '',
            'generated_root' => '',
            'error' => '--output-dir=... is required.',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $projectKey,
        'output_dir' => $outputDir,
        'generated_root' => $generatedRoot,
        'error' => '',
    ];
}

/**
 * @param list<array<string,mixed>> $dataClasses
 * @return array<string,mixed>
 */
function app_shared_contract_manifest_spike_build_manifest(string $projectKey, array $dataClasses): array
{
    $contracts = [];
    $missingSemantics = [
        'nullable' => 0,
        'default' => 0,
        'key' => 0,
        'enum_like_status' => 0,
    ];

    foreach ($dataClasses as $dataClass) {
        $fields = [];
        foreach (($dataClass['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fields[] = [
                'logical_name' => (string) ($field['logical_name'] ?? ''),
                'physical_name' => (string) ($field['physical_name'] ?? ''),
                'generated_name' => (string) ($field['generated_name'] ?? ''),
                'type' => (string) ($field['datatype'] ?? ''),
                'nullable' => null,
                'default' => null,
                'is_key' => null,
                'enum_like_status' => null,
                'missing_semantics' => ['nullable', 'default', 'key', 'enum_like_status'],
            ];
            $missingSemantics['nullable']++;
            $missingSemantics['default']++;
            $missingSemantics['key']++;
            $missingSemantics['enum_like_status']++;
        }

        $contracts[] = [
            'contract_key' => strtolower((string) ($dataClass['physical_name'] ?? $dataClass['name'] ?? '')),
            'dataclass' => [
                'logical_name' => (string) ($dataClass['logical_name'] ?? ''),
                'physical_name' => (string) ($dataClass['physical_name'] ?? ''),
                'generated_name' => (string) ($dataClass['generated_name'] ?? $dataClass['name'] ?? ''),
            ],
            'fields' => $fields,
        ];
    }

    return [
        'manifest_version' => 'shared-contract-manifest-spike-v0',
        'project_key' => $projectKey,
        'source' => 'canonical dataclass metadata',
        'status' => 'feasibility-spike',
        'contracts' => $contracts,
        'known_missing_semantics' => $missingSemantics,
    ];
}

/**
 * @return array<string,list<string>>
 */
function app_shared_contract_manifest_spike_generated_properties(string $generatedRoot): array
{
    $root = rtrim($generatedRoot, '/');
    if ($root === '' || !is_dir($root . '/base')) {
        return [];
    }

    $map = [];
    foreach (glob($root . '/base/data-*Base.php') ?: [] as $path) {
        if (!is_string($path)) {
            continue;
        }

        $basename = basename($path);
        if (!preg_match('/^data-(.+)Base\.php$/', $basename, $matches)) {
            continue;
        }

        $className = $matches[1];
        $text = file_get_contents($path);
        if (!is_string($text)) {
            continue;
        }

        preg_match_all('/public\s+\$([A-Za-z_][A-Za-z0-9_]*)\s*;/', $text, $propertyMatches);
        $map[$className] = array_values(array_unique($propertyMatches[1] ?? []));
    }

    ksort($map);

    return $map;
}

/**
 * @param array<string,mixed> $manifest
 * @param array<string,list<string>> $generatedProperties
 * @return array<string,mixed>
 */
function app_shared_contract_manifest_spike_summarize(array $manifest, array $generatedProperties): array
{
    $contracts = $manifest['contracts'] ?? [];
    $comparisons = [];
    $fieldCount = 0;
    $mismatchCount = 0;

    foreach (is_array($contracts) ? $contracts : [] as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $generatedName = (string) ($contract['dataclass']['generated_name'] ?? '');
        $manifestFields = [];
        foreach (($contract['fields'] ?? []) as $field) {
            if (is_array($field)) {
                $manifestFields[] = (string) ($field['generated_name'] ?? '');
            }
        }
        $fieldCount += count($manifestFields);

        $generatedFields = $generatedProperties[$generatedName] ?? [];
        $missingInGenerated = array_values(array_diff($manifestFields, $generatedFields));
        $extraInGenerated = array_values(array_diff($generatedFields, $manifestFields));
        if ($missingInGenerated !== [] || $extraInGenerated !== []) {
            $mismatchCount++;
        }

        $comparisons[] = [
            'dataclass' => $generatedName,
            'manifest_fields' => $manifestFields,
            'generated_php_fields' => $generatedFields,
            'match' => $missingInGenerated === [] && $extraInGenerated === [],
            'missing_in_generated_php' => $missingInGenerated,
            'extra_in_generated_php' => $extraInGenerated,
        ];
    }

    return [
        'ok' => $mismatchCount === 0,
        'contract_count' => count(is_array($contracts) ? $contracts : []),
        'field_count' => $fieldCount,
        'generated_php_comparison_available' => $generatedProperties !== [],
        'generated_php_mismatch_count' => $mismatchCount,
        'known_missing_semantics' => $manifest['known_missing_semantics'] ?? [],
        'comparisons' => $comparisons,
        'conclusion' => $mismatchCount === 0
            ? 'Existing DataClass metadata can produce a field-compatible language-neutral manifest, but nullable/default/key semantics remain missing in DataClass metadata.'
            : 'Generated PHP properties and manifest fields disagree; inspect comparisons before promotion.',
    ];
}

function app_shared_contract_manifest_spike_write_json(string $path, array $payload): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('failed to create output directory: ' . $dir);
    }

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (!is_string($json)) {
        throw new RuntimeException('failed to encode JSON: ' . json_last_error_msg());
    }

    if (file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('failed to write: ' . $path);
    }
}

$parsed = app_cli_shared_contract_manifest_spike_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_shared_contract_manifest_spike_usage() . PHP_EOL);
    exit(0);
}
if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_shared_contract_manifest_spike_usage() . PHP_EOL);
    exit(64);
}

$app = app_bootstrap();
$snapshot = app_fetch_data_class_metadata_snapshot($app, $parsed['project_key']);
if (!$snapshot['ok']) {
    fwrite(STDERR, $snapshot['error'] . PHP_EOL);
    exit(1);
}

$manifest = app_shared_contract_manifest_spike_build_manifest($parsed['project_key'], $snapshot['items']);
$generatedProperties = app_shared_contract_manifest_spike_generated_properties($parsed['generated_root']);
$summary = app_shared_contract_manifest_spike_summarize($manifest, $generatedProperties);

$outputDir = rtrim($parsed['output_dir'], '/');
app_shared_contract_manifest_spike_write_json($outputDir . '/manifest.json', $manifest);
app_shared_contract_manifest_spike_write_json($outputDir . '/summary.json', $summary);

fwrite(
    STDOUT,
    json_encode(
        [
            'ok' => $summary['ok'],
            'project_key' => $parsed['project_key'],
            'manifest' => $outputDir . '/manifest.json',
            'summary' => $outputDir . '/summary.json',
            'contract_count' => $summary['contract_count'],
            'field_count' => $summary['field_count'],
            'known_missing_semantics' => $summary['known_missing_semantics'],
            'conclusion' => $summary['conclusion'],
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    ) . PHP_EOL,
);

exit($summary['ok'] ? 0 : 1);
