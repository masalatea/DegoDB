<?php

declare(strict_types=1);

require_once __DIR__ . '/managed_operation_repository_pdo.php';
require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/shared_contract_manifest.php';

function app_project_output_managed_operation_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'managed-operation-docs-md';
}

function app_project_output_managed_operation_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_managed_operation_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_managed_operation_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('managed operation JSON generation failed.');
    }

    return $json . PHP_EOL;
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,string>
 */
function app_project_output_managed_operation_build_emitted_files(array $payload): array
{
    return [
        'managed-operations.json' => app_project_output_managed_operation_json_text($payload),
        'managed-operations.md' => app_project_output_managed_operation_markdown_text($payload),
        'README.md' => app_project_output_managed_operation_readme_text($payload),
    ];
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_managed_operation_markdown_text(array $payload): string
{
    $operations = is_array($payload['operations'] ?? null) ? $payload['operations'] : [];
    $lines = [
        '# Managed Operations',
        '',
        'Generated operation definitions for policy, App-local persistence, sync, and no-code metadata consumers.',
        '',
        '| Operation | Contract | Type | Permission | Roles | Scopes | Fields | Status |',
        '| --- | --- | --- | --- | --- | --- | --- | --- |',
    ];

    if ($operations === []) {
        $lines[] = '| none | none | none | none | none | none | 0 | none |';
    }

    foreach ($operations as $operation) {
        if (!is_array($operation)) {
            continue;
        }
        $lines[] = sprintf(
            '| `%s` | `%s` | `%s` | `%s` | %s | %s | %d | `%s` |',
            app_project_output_managed_operation_md_cell((string) ($operation['operation_key'] ?? '')),
            app_project_output_managed_operation_md_cell((string) ($operation['contract_key'] ?? '')),
            app_project_output_managed_operation_md_cell((string) ($operation['operation_type'] ?? '')),
            app_project_output_managed_operation_md_cell((string) ($operation['permission_key'] ?? '')),
            app_project_output_managed_operation_md_cell(implode(', ', app_project_output_managed_operation_string_list($operation['required_roles'] ?? []))),
            app_project_output_managed_operation_md_cell(implode(', ', app_project_output_managed_operation_string_list($operation['required_scopes'] ?? []))),
            count(is_array($operation['fields'] ?? null) ? $operation['fields'] : []),
            app_project_output_managed_operation_md_cell((string) ($operation['status'] ?? '')),
        );
    }

    $lines[] = '';
    foreach ($operations as $operation) {
        if (!is_array($operation)) {
            continue;
        }

        $lines[] = '## `' . app_project_output_managed_operation_md_text((string) ($operation['operation_key'] ?? '')) . '`';
        $lines[] = '';
        $lines[] = '- Contract: `' . app_project_output_managed_operation_md_text((string) ($operation['contract_key'] ?? '')) . '`';
        $lines[] = '- Storage policy: `' . app_project_output_managed_operation_md_text((string) ($operation['storage_policy'] ?? '')) . '`';
        $lines[] = '- Permission key: `' . app_project_output_managed_operation_md_text((string) ($operation['permission_key'] ?? '')) . '`';
        $lines[] = '';
        $lines[] = '| Field | Role | Required | Client write |';
        $lines[] = '| --- | --- | --- | --- |';
        $fields = is_array($operation['fields'] ?? null) ? $operation['fields'] : [];
        if ($fields === []) {
            $lines[] = '| none | none | false | false |';
        }
        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $lines[] = sprintf(
                '| `%s` | `%s` | `%s` | `%s` |',
                app_project_output_managed_operation_md_cell((string) ($field['field_physical_name'] ?? '')),
                app_project_output_managed_operation_md_cell((string) ($field['field_role'] ?? '')),
                ((bool) ($field['is_required'] ?? false)) ? 'true' : 'false',
                ((bool) ($field['allow_client_write'] ?? false)) ? 'true' : 'false',
            );
        }
        $lines[] = '';
    }

    return implode("\n", $lines);
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_managed_operation_readme_text(array $payload): string
{
    $operations = is_array($payload['operations'] ?? null) ? $payload['operations'] : [];
    $contracts = is_array($payload['contracts'] ?? null) ? $payload['contracts'] : [];

    return implode("\n", [
        '# Managed Operation Artifact',
        '',
        'Generated operation metadata documentation from canonical Mtool metadata.',
        '',
        '- `managed-operations.json` is the machine-readable operation snapshot.',
        '- `managed-operations.md` is the human-readable operation summary.',
        '- Do not hand-edit generated files; update canonical Mtool metadata instead.',
        '',
        'Operation count: ' . count($operations),
        'Contract count: ' . count($contracts),
        'Status: ' . ((bool) ($payload['ok'] ?? false) ? 'ok' : 'failed'),
        '',
    ]);
}

function app_project_output_managed_operation_md_cell(string $value): string
{
    return str_replace(["\n", '|'], [' ', '\\|'], app_project_output_managed_operation_md_text($value));
}

function app_project_output_managed_operation_md_text(string $value): string
{
    $trimmed = trim(str_replace(["\r\n", "\r"], "\n", $value));
    return $trimmed !== '' ? $trimmed : 'none';
}

/**
 * @return list<string>
 */
function app_project_output_managed_operation_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $items = [];
    foreach ($value as $item) {
        if (is_string($item) && trim($item) !== '') {
            $items[] = trim($item);
        }
    }

    return array_values(array_unique($items));
}

/**
 * @param array<string,mixed> $manifestResult
 * @param array{ok:bool,items:list<array<string,mixed>>,error:string} $operationSnapshot
 * @return array<string,mixed>
 */
function app_project_output_managed_operation_payload(
    string $projectKey,
    array $manifestResult,
    array $operationSnapshot,
): array {
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $contracts = is_array($manifest['contracts'] ?? null) ? $manifest['contracts'] : [];
    $operations = is_array($operationSnapshot['items'] ?? null) ? $operationSnapshot['items'] : [];

    return [
        'ok' => (bool) ($manifestResult['ok'] ?? false) && (bool) ($operationSnapshot['ok'] ?? false),
        'artifact_type' => 'managed-operation-docs-md',
        'project_key' => app_normalize_project_key($projectKey),
        'manifest_version' => 'managed-operation-artifact-v0',
        'contracts' => $contracts,
        'operations' => $operations,
        'summary' => [
            'contract_count' => count($contracts),
            'operation_count' => count($operations),
            'operation_field_count' => array_sum(array_map(
                static fn (array $operation): int => count(is_array($operation['fields'] ?? null) ? $operation['fields'] : []),
                $operations,
            )),
        ],
        'errors' => array_values(array_filter([
            (string) ($manifestResult['error'] ?? ''),
            (string) ($operationSnapshot['error'] ?? ''),
        ])),
    ];
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
function app_project_output_prepare_managed_operation_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_managed_operation_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Unsupported managed operation artifact strategy.',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'md') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'managed operation artifact currently supports md only.',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_managed_operation_default_runtime_source_relative_path(
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
            'error' => 'runtime source relative path is invalid.',
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

    $operationSnapshot = app_pdo_fetch_managed_operation_snapshot($app, app_normalize_project_key($projectKey));
    if (!$operationSnapshot['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $operationSnapshot['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $payload = app_project_output_managed_operation_payload($projectKey, $manifestResult, $operationSnapshot);

    try {
        $files = app_project_output_managed_operation_build_emitted_files($payload);
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
            'error' => 'Failed to create managed operation staging tree: ' . $throwable->getMessage(),
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
