<?php

declare(strict_types=1);

require_once __DIR__ . '/no_code_runtime.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_no_code_runtime_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'no-code-runtime-json';
}

function app_project_output_no_code_runtime_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_no_code_runtime_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_no_code_runtime_json_text(array $payload): string
{
    $json = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($json) || $json === '') {
        throw new RuntimeException('no-code runtime JSON generation failed.');
    }

    return $json . PHP_EOL;
}

/**
 * @param array<string,mixed> $payload
 * @return array<string,string>
 */
function app_project_output_no_code_runtime_build_emitted_files(array $payload): array
{
    $screenDefinition = is_array($payload['screen_definition'] ?? null) ? $payload['screen_definition'] : [];
    $runtimePreview = is_array($payload['runtime_preview'] ?? null) ? $payload['runtime_preview'] : [];

    return [
        'screen-definition.json' => app_project_output_no_code_runtime_json_text($screenDefinition),
        'runtime-preview.json' => app_project_output_no_code_runtime_json_text($runtimePreview),
        'runtime-preview.html' => app_no_code_runtime_render_preview_html($runtimePreview),
        'README.md' => app_project_output_no_code_runtime_readme_text($payload),
    ];
}

/**
 * @param array<string,mixed> $payload
 */
function app_project_output_no_code_runtime_readme_text(array $payload): string
{
    $summary = is_array($payload['summary'] ?? null) ? $payload['summary'] : [];

    return implode("\n", [
        '# No-Code Runtime Artifact',
        '',
        'Generated no-code screen definition and runtime preview from canonical Mtool metadata.',
        '',
        '- `screen-definition.json` is the machine-readable no-code screen definition.',
        '- `runtime-preview.json` is a fail-closed render preview for generated screens.',
        '- `runtime-preview.html` is a minimal HTML preview rendered from the same runtime model.',
        '- Do not hand-edit generated files; update canonical Mtool metadata instead.',
        '',
        'Contract count: ' . (int) ($summary['contract_count'] ?? 0),
        'Screen count: ' . (int) ($summary['screen_count'] ?? 0),
        'Preview count: ' . (int) ($summary['preview_count'] ?? 0),
        'Status: ' . ((bool) ($payload['ok'] ?? false) ? 'ok' : 'failed'),
        '',
    ]);
}

/**
 * @param array<string,mixed> $definitionResult
 * @return array<string,mixed>
 */
function app_project_output_no_code_runtime_payload(string $projectKey, array $definitionResult): array
{
    $screenDefinition = is_array($definitionResult['definition'] ?? null)
        ? $definitionResult['definition']
        : [];
    $preview = app_project_output_no_code_runtime_preview($screenDefinition);
    $screenCount = 0;
    foreach (($screenDefinition['contracts'] ?? []) as $contract) {
        if (is_array($contract) && is_array($contract['screens'] ?? null)) {
            $screenCount += count($contract['screens']);
        }
    }

    return [
        'ok' => (bool) ($definitionResult['ok'] ?? false) && (bool) ($preview['ok'] ?? false),
        'artifact_type' => 'no-code-runtime-json',
        'project_key' => app_normalize_project_key($projectKey),
        'manifest_version' => 'no-code-runtime-artifact-v0',
        'screen_definition' => $screenDefinition,
        'runtime_preview' => $preview,
        'summary' => [
            'contract_count' => count(is_array($screenDefinition['contracts'] ?? null) ? $screenDefinition['contracts'] : []),
            'screen_count' => $screenCount,
            'preview_count' => count(is_array($preview['screens'] ?? null) ? $preview['screens'] : []),
        ],
        'errors' => array_values(array_filter([
            (string) ($definitionResult['error'] ?? ''),
            (string) ($preview['error'] ?? ''),
        ])),
    ];
}

/**
 * @param array<string,mixed> $screenDefinition
 * @return array<string,mixed>
 */
function app_project_output_no_code_runtime_preview(array $screenDefinition): array
{
    $screens = [];
    $errors = [];
    foreach (($screenDefinition['contracts'] ?? []) as $contract) {
        if (!is_array($contract)) {
            continue;
        }

        foreach (($contract['screens'] ?? []) as $screen) {
            if (!is_array($screen)) {
                continue;
            }

            $screenKey = (string) ($screen['screen_key'] ?? '');
            if ($screenKey === '') {
                continue;
            }

            $renderResult = app_no_code_runtime_render_screen($screenDefinition, $screenKey);
            if (!$renderResult['ok']) {
                $errors[] = $renderResult['error'];
                continue;
            }

            $screens[] = $renderResult['render'];
        }
    }

    return [
        'ok' => $errors === [],
        'runtime_version' => app_no_code_runtime_version(),
        'definition_version' => (string) ($screenDefinition['definition_version'] ?? ''),
        'project_key' => (string) ($screenDefinition['project_key'] ?? ''),
        'screens' => $screens,
        'error' => implode('; ', $errors),
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
function app_project_output_prepare_no_code_runtime_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_no_code_runtime_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'Unsupported no-code runtime artifact strategy.',
        ];
    }

    $programLanguage = trim((string) ($definition['program_language'] ?? ''));
    if ($programLanguage !== '' && $programLanguage !== 'json') {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'no-code runtime artifact currently supports json only.',
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_no_code_runtime_default_runtime_source_relative_path(
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

    $definitionResult = app_no_code_screen_definition_from_project($app, $projectKey);
    if (!$definitionResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $definitionResult['error'],
        ];
    }

    $payload = app_project_output_no_code_runtime_payload($projectKey, $definitionResult);
    if (!$payload['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => implode(', ', $payload['errors']),
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);
    $files = app_project_output_no_code_runtime_build_emitted_files($payload);

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
            'error' => 'no-code runtime staging tree creation failed: ' . $throwable->getMessage(),
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
