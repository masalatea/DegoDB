<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/runtime_storage_paths.php';

function app_project_output_html_module_catalog_repo_root(): string
{
    return app_runtime_storage_repo_root();
}

function app_project_output_html_module_source_ref(string $projectKey, string $sourceOutputKey): string
{
    return 'catalog://html-module/'
        . app_normalize_project_key($projectKey)
        . '/'
        . app_normalize_source_output_key($sourceOutputKey);
}

/**
 * @return array{
 *     ok:bool,
 *     project_key:string,
 *     source_output_key:string,
 *     error:string
 * }
 */
function app_project_output_html_module_source_ref_parse(string $value): array
{
    $normalizedValue = trim(str_replace('\\', '/', $value));
    if (!preg_match('#^catalog://html-module/([^/]+)/([^/]+)$#', $normalizedValue, $matches)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'error' => 'html module catalog ref の形式が不正です。',
        ];
    }

    $projectKey = app_normalize_project_key($matches[1]);
    $sourceOutputKey = app_normalize_source_output_key($matches[2]);
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'error' => 'html module catalog ref の project key が不正です。',
        ];
    }

    if ($sourceOutputKey === '' || !app_source_output_key_is_valid($sourceOutputKey)) {
        return [
            'ok' => false,
            'project_key' => '',
            'source_output_key' => '',
            'error' => 'html module catalog ref の source output key が不正です。',
        ];
    }

    return [
        'ok' => true,
        'project_key' => $projectKey,
        'source_output_key' => $sourceOutputKey,
        'error' => '',
    ];
}

function app_project_output_html_module_source_ref_is_supported(string $value): bool
{
    return app_project_output_html_module_source_ref_parse($value)['ok'];
}

/**
 * @return list<array{
 *     source_kind:string,
 *     relative_path:string
 * }>
 */
function app_project_output_html_module_source_candidates(string $projectKey, string $sourceOutputKey): array
{
    $projectSlug = strtolower(app_normalize_project_key($projectKey));
    $normalizedSourceOutputKey = app_normalize_source_output_key($sourceOutputKey);

    return [
        [
            'source_kind' => 'canonical-html-module',
            'relative_path' => app_runtime_storage_mtool_reference_relative_path(
                'html-modules/'
                . $projectSlug
                . '/'
                . $normalizedSourceOutputKey
                . '/current'
            ),
        ],
        [
            'source_kind' => 'legacy-html-snapshot',
            'relative_path' => app_runtime_storage_mtool_reference_relative_path(
                'legacy-source-snapshots/'
                . $projectSlug
                . '/html/'
                . $normalizedSourceOutputKey
            ),
        ],
        [
            'source_kind' => 'legacy-html-placeholder',
            'relative_path' => app_runtime_storage_mtool_reference_relative_path(
                'legacy-source-placeholders/'
                . $projectSlug
                . '/html/'
                . $normalizedSourceOutputKey
            ),
        ],
    ];
}

function app_project_output_html_module_source_kind_caption(string $value): string
{
    return match ($value) {
        'canonical-html-module' => 'Canonical Html Module',
        'legacy-html-snapshot' => 'Legacy Html Snapshot',
        'legacy-html-placeholder' => 'Legacy Html Placeholder',
        default => $value,
    };
}

/**
 * @return array{
 *     ok:bool,
 *     source_root:string,
 *     relative_path:string,
 *     source_kind:string,
 *     repo_root:string,
 *     error:string
 * }
 */
function app_project_output_html_module_resolve_source_root(string $value): array
{
    $parsed = app_project_output_html_module_source_ref_parse($value);
    if (!$parsed['ok']) {
        return [
            'ok' => false,
            'source_root' => '',
            'relative_path' => '',
            'source_kind' => '',
            'repo_root' => app_project_output_html_module_catalog_repo_root(),
            'error' => $parsed['error'],
        ];
    }

    $repoRoot = app_project_output_html_module_catalog_repo_root();
    foreach (app_project_output_html_module_source_candidates($parsed['project_key'], $parsed['source_output_key']) as $candidate) {
        $candidateRoot = $repoRoot . '/' . $candidate['relative_path'];
        $resolved = realpath($candidateRoot);
        if (!is_string($resolved) || $resolved === '') {
            continue;
        }

        $normalizedResolved = str_replace('\\', '/', $resolved);
        if (!is_dir($normalizedResolved)) {
            continue;
        }

        if ($normalizedResolved !== $repoRoot && !str_starts_with($normalizedResolved, $repoRoot . '/')) {
            continue;
        }

        return [
            'ok' => true,
            'source_root' => $normalizedResolved,
            'relative_path' => $candidate['relative_path'],
            'source_kind' => $candidate['source_kind'],
            'repo_root' => $repoRoot,
            'error' => '',
        ];
    }

    return [
        'ok' => false,
        'source_root' => '',
        'relative_path' => '',
        'source_kind' => '',
        'repo_root' => $repoRoot,
        'error' => 'html module catalog ref の source tree が見つかりません: '
            . app_project_output_html_module_source_ref($parsed['project_key'], $parsed['source_output_key']),
    ];
}
