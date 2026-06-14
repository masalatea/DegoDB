<?php

declare(strict_types=1);

require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_legacy_source_strategy_is_supported(string $strategy): bool
{
    return $strategy === 'legacy-directory-mirror';
}

function app_project_output_legacy_source_repo_root(): string
{
    return app_runtime_storage_repo_root();
}

function app_project_output_legacy_source_default_runtime_source_relative_path(
    string $projectKey,
    string $sourceOutputKey,
): string {
    return app_runtime_storage_legacy_source_outputs_relative_path(
        $projectKey,
        $sourceOutputKey,
    );
}

/**
 * @return array{
 *     ok:bool,
 *     source_root:string,
 *     source_root_relative_path:string,
 *     source_kind:string,
 *     repo_root:string,
 *     error:string
 * }
 */
function app_project_output_legacy_source_resolve_root(string $sourceTemplateDir): array
{
    $normalizedTemplateDir = trim(str_replace('\\', '/', $sourceTemplateDir));
    if ($normalizedTemplateDir === '') {
        return [
            'ok' => false,
            'source_root' => '',
            'source_root_relative_path' => '',
            'source_kind' => '',
            'repo_root' => app_project_output_legacy_source_repo_root(),
            'error' => 'seed directory mirror では source_template_dir が必須です。',
        ];
    }

    $repoRoot = app_project_output_legacy_source_repo_root();
    $candidate = str_starts_with($normalizedTemplateDir, '/')
        ? $normalizedTemplateDir
        : $repoRoot . '/' . ltrim(app_runtime_storage_canonical_repo_relative_path($normalizedTemplateDir), '/');
    $resolved = realpath($candidate);
    if (!is_string($resolved) || $resolved === '') {
        return [
            'ok' => false,
            'source_root' => '',
            'source_root_relative_path' => '',
            'source_kind' => '',
            'repo_root' => $repoRoot,
            'error' => 'seed source dir が見つかりません: ' . $normalizedTemplateDir,
        ];
    }

    $normalizedResolved = str_replace('\\', '/', $resolved);
    if ($normalizedResolved !== $repoRoot && !str_starts_with($normalizedResolved, $repoRoot . '/')) {
        return [
            'ok' => false,
            'source_root' => '',
            'source_root_relative_path' => '',
            'source_kind' => '',
            'repo_root' => $repoRoot,
            'error' => 'seed source dir は repo root 配下のみ指定できます: ' . $normalizedTemplateDir,
        ];
    }

    if (!is_dir($normalizedResolved)) {
        return [
            'ok' => false,
            'source_root' => '',
            'source_root_relative_path' => '',
            'source_kind' => '',
            'repo_root' => $repoRoot,
            'error' => 'seed source dir が directory ではありません: ' . $normalizedTemplateDir,
        ];
    }

    $relativePath = $normalizedResolved === $repoRoot
        ? '.'
        : ltrim(substr($normalizedResolved, strlen($repoRoot)), '/');

    return [
        'ok' => true,
        'source_root' => $normalizedResolved,
        'source_root_relative_path' => $relativePath,
        'source_kind' => 'direct-path',
        'repo_root' => $repoRoot,
        'error' => '',
    ];
}

/**
 * @param array{
 *     source_output_key:string,
 *     source_template_dir:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string
 * } $definition
 * @return array{
 *     ok:bool,
 *     runtime_source_relative_path:string,
 *     runtime_source_root:string,
 *     scan_result:array{
 *         ok:bool,
 *         files:list<array{
 *             relative_path:string,
 *             size:int
 *         }>,
 *         total_bytes:int,
 *         error:string
 *     }|null,
 *     error:string
 * }
 */
function app_project_output_prepare_legacy_source_tree(array $app, string $projectKey, array $definition): array
{
    $strategy = (string) ($definition['artifact_strategy'] ?? '');
    if (!app_project_output_legacy_source_strategy_is_supported($strategy)) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => '未対応の seed source artifact strategy です。',
        ];
    }

    $sourceRootResult = app_project_output_legacy_source_resolve_root((string) ($definition['source_template_dir'] ?? ''));
    if (!$sourceRootResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $sourceRootResult['error'],
        ];
    }

    $runtimeSourceRelativePath = trim((string) ($definition['runtime_source_relative_path'] ?? ''));
    if ($runtimeSourceRelativePath === '') {
        $runtimeSourceRelativePath = app_project_output_legacy_source_default_runtime_source_relative_path(
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

    $scanResult = app_project_output_scan_tree($sourceRootResult['source_root']);
    if (!$scanResult['ok']) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => $scanResult['error'],
        ];
    }

    $runtimeSourceRoot = app_runtime_storage_runtime_source_root($app, $runtimeSourceRelativePath);

    try {
        app_project_output_delete_tree($runtimeSourceRoot);
        app_project_output_copy_tree(
            $sourceRootResult['source_root'],
            $runtimeSourceRoot,
            $scanResult['files'],
        );
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'runtime_source_relative_path' => '',
            'runtime_source_root' => '',
            'scan_result' => null,
            'error' => 'seed source tree の staging に失敗しました: ' . $throwable->getMessage(),
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
