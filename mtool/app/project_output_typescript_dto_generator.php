<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/shared_contract_manifest.php';

function app_project_output_typescript_dto_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'shared-contract-typescript';
}

function app_project_output_typescript_dto_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_typescript_dto_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<string,mixed> $manifestResult
 * @return array<string,string>
 */
function app_project_output_typescript_dto_build_emitted_files(array $manifestResult): array
{
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];

    return [
        'dto.ts' => app_project_output_typescript_dto_text($manifest),
        'README.md' => app_project_output_typescript_dto_readme_text($manifestResult),
    ];
}

/**
 * @param array<string,mixed> $manifest
 */
function app_project_output_typescript_dto_text(array $manifest): string
{
    $lines = [
        '// Generated from shared contract manifest. Do not edit directly.',
        '',
    ];

    foreach (($manifest['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        $entity = is_array($contract['entity'] ?? null) ? $contract['entity'] : [];
        $interfaceName = app_project_output_typescript_dto_interface_name(
            (string) ($entity['generated_name'] ?? $contract['contract_key'] ?? ''),
        );
        $lines[] = 'export interface ' . $interfaceName . ' {';

        foreach (($contract['fields'] ?? []) as $field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldName = app_project_output_typescript_dto_property_name(
                (string) ($field['generated_name'] ?? $field['physical_name'] ?? ''),
            );
            if ($fieldName === '') {
                continue;
            }

            $type = app_project_output_typescript_dto_field_type($field);
            $lines[] = '  ' . $fieldName . ': ' . $type . ';';
        }

        $lines[] = '}';
        $lines[] = '';
    }

    return implode("\n", $lines);
}

function app_project_output_typescript_dto_interface_name(string $name): string
{
    $candidate = preg_replace('/[^A-Za-z0-9_]+/', '', trim($name));
    if (!is_string($candidate) || $candidate === '') {
        $candidate = 'Contract';
    }
    if (preg_match('/^[A-Za-z_]/', $candidate) !== 1) {
        $candidate = 'Contract' . $candidate;
    }
    if (!str_ends_with($candidate, 'Dto')) {
        $candidate .= 'Dto';
    }

    return $candidate;
}

function app_project_output_typescript_dto_property_name(string $name): string
{
    $candidate = preg_replace('/[^A-Za-z0-9_]+/', '', trim($name));
    if (!is_string($candidate) || $candidate === '') {
        return '';
    }
    if (preg_match('/^[A-Za-z_]/', $candidate) !== 1) {
        return '';
    }

    return $candidate;
}

/**
 * @param array<string,mixed> $field
 */
function app_project_output_typescript_dto_field_type(array $field): string
{
    $baseType = match ((string) ($field['type'] ?? '')) {
        'integer' => 'number',
        'boolean' => 'boolean',
        'text', 'datetime', 'string' => 'string',
        default => 'unknown',
    };

    return (bool) ($field['nullable'] ?? false) ? $baseType . ' | null' : $baseType;
}

/**
 * @param array<string,mixed> $manifestResult
 */
function app_project_output_typescript_dto_readme_text(array $manifestResult): string
{
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $contracts = is_array($manifest['contracts'] ?? null) ? $manifest['contracts'] : [];

    return implode("\n", [
        '# TypeScript DTO',
        '',
        'Generated TypeScript DTO interfaces from the shared contract manifest.',
        '',
        '- `dto.ts` contains generated interfaces.',
        '- Do not hand-edit generated files; update canonical Mtool metadata instead.',
        '',
        'Contract count: ' . count($contracts),
        'Status: ' . ((bool) ($manifestResult['ok'] ?? false) ? 'ok' : 'failed'),
        '',
    ]);
}

/**
 * @param array{
 *     source_output_key:string,
 *     program_language:string,
 *     artifact_strategy:string,
 *     runtime_source_relative_path:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{relative_path:string,size:int}>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_typescript_dto_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_typescript_dto_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の TypeScript DTO artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'ts') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'TypeScript DTO artifact は現在 ts のみ対応です。',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_typescript_dto_default_runtime_source_relative_path(
            $projectKey,
            (string) ($definition['source_output_key'] ?? ''),
        );
    }
    if (!app_project_output_relative_path_is_safe($runtimeSourceRelativePath)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'runtime source relative path の形式が不正です。',
        ];
    }

    $manifestResult = app_shared_contract_manifest_from_project($app, $projectKey);
    if (!$manifestResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $manifestResult['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $files = app_project_output_typescript_dto_build_emitted_files($manifestResult);

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_ensure_directory($runtimeSourceRoot);

        foreach ($files as $relativePath => $contents) {
            app_project_output_write_text_file($runtimeSourceRoot . '/' . $relativePath, $contents);
        }
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'TypeScript DTO staging tree の作成に失敗しました: ' . $throwable->getMessage(),
        ];
    }

    $scanResult = app_project_output_scan_tree($runtimeSourceRoot);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    return [
        'ok' => true,
        'runtime_source_relative_path' => $runtimeSourceRelativePath,
        'runtime_source_root' => $runtimeSourceRoot,
        'scan_result' => $scanResult,
        'error' => '',
    ];
}
