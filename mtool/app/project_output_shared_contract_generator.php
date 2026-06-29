<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_storage_paths.php';
require_once __DIR__ . '/shared_contract_manifest.php';

function app_project_output_shared_contract_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'shared-contract-json';
}

function app_project_output_shared_contract_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_shared_contract_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<mixed> $payload
 */
function app_project_output_shared_contract_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('shared contract JSON の生成に失敗しました。');
    }

    return $json . PHP_EOL;
}

/**
 * @param array<string,mixed> $manifestResult
 * @return array<string,string>
 */
function app_project_output_shared_contract_build_emitted_files(array $manifestResult): array
{
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $validation = is_array($manifestResult['validation'] ?? null) ? $manifestResult['validation'] : [];
    $compare = is_array($manifestResult['compare'] ?? null) ? $manifestResult['compare'] : [];

    return [
        'shared-contract.json' => app_project_output_shared_contract_json_text($manifest),
        'shared-contract-report.json' => app_project_output_shared_contract_json_text([
            'ok' => (bool) ($manifestResult['ok'] ?? false),
            'validation' => $validation,
            'compare' => $compare,
            'error' => (string) ($manifestResult['error'] ?? ''),
        ]),
        'README.md' => app_project_output_shared_contract_readme_text($manifestResult),
    ];
}

/**
 * @param array<string,mixed> $manifestResult
 */
function app_project_output_shared_contract_readme_text(array $manifestResult): string
{
    $manifest = is_array($manifestResult['manifest'] ?? null) ? $manifestResult['manifest'] : [];
    $contracts = is_array($manifest['contracts'] ?? null) ? $manifest['contracts'] : [];

    return implode("\n", [
        '# Shared Contract',
        '',
        'Generated shared contract manifest for App-local persistence, sync, API, and no-code metadata consumers.',
        '',
        '- `shared-contract.json` is the language-neutral manifest.',
        '- `shared-contract-report.json` records validation and DataClass shape compare results.',
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
function app_project_output_prepare_shared_contract_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_shared_contract_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の shared contract artifact strategy です。',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'json') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'shared contract artifact は現在 json のみ対応です。',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_shared_contract_default_runtime_source_relative_path(
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
    $files = app_project_output_shared_contract_build_emitted_files($manifestResult);

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
            'error' => 'shared contract staging tree の作成に失敗しました: ' . $throwable->getMessage(),
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
