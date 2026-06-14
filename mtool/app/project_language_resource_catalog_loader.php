<?php

declare(strict_types=1);

require_once __DIR__ . '/language_resource_file_catalog.php';
require_once __DIR__ . '/legacy_language_resource_reference.php';

function app_project_language_resource_bootstrap_reference_source_of_truth(): string
{
    return 'bootstrap-reference';
}

function app_project_language_resource_bootstrap_reference_notes(): string
{
    return 'Copied from legacy language resource reference catalog; source_dump_path stays provenance-only host metadata.';
}

function app_project_language_resource_file_catalog_source_of_truth(): string
{
    return 'file-canonical';
}

function app_project_language_resource_file_catalog_notes(): string
{
    return 'Synced from file-based language resource catalog; any origin.source_dump_path remains provenance-only metadata.';
}

/**
 * @return array{
 *     ok:bool,
 *     exists:bool,
 *     root_path:string,
 *     manifest:array<string,mixed>,
 *     item:array<string,mixed>|null,
 *     warnings:list<string>,
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_language_resource_load_file_catalog(string $projectKey): array
{
    $rootPath = app_language_resource_file_catalog_default_root($projectKey);
    $manifestPath = rtrim($rootPath, '/') . '/manifest.json';
    if (!is_file($manifestPath)) {
        return [
            'ok' => false,
            'exists' => false,
            'root_path' => rtrim($rootPath, '/'),
            'manifest' => [],
            'item' => null,
            'warnings' => [],
            'errors' => [],
            'error' => '',
        ];
    }

    $loaded = app_language_resource_file_catalog_load_catalog($rootPath);

    return [
        'ok' => $loaded['ok'],
        'exists' => true,
        'root_path' => $loaded['root_path'],
        'manifest' => $loaded['manifest'],
        'item' => $loaded['catalog'],
        'warnings' => $loaded['warnings'],
        'errors' => $loaded['errors'],
        'error' => $loaded['ok'] ? '' : implode("\n", $loaded['errors']),
    ];
}

function app_fetch_project_language_resource_catalog(
    array $app,
    string $projectKey,
    int $fallbackProjectPid = 0,
): array {
    $fileCatalog = app_project_language_resource_load_file_catalog($projectKey);
    if ($fileCatalog['exists']) {
        if ($fileCatalog['ok'] && is_array($fileCatalog['item'])) {
            return [
                'ok' => true,
                'item' => $fileCatalog['item'],
                'source' => app_project_language_resource_file_catalog_source_of_truth(),
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'item' => null,
            'source' => 'error',
            'error' => $fileCatalog['error'],
        ];
    }

    try {
        $reference = app_load_legacy_language_resource_reference($projectKey);
        if ($reference['ok'] && is_array($reference['item'])) {
            return [
                'ok' => true,
                'item' => $reference['item'],
                'source' => 'reference',
                'error' => '',
            ];
        }

        return [
            'ok' => true,
            'item' => app_legacy_language_resource_reference_empty($projectKey, $fallbackProjectPid),
            'source' => 'empty',
            'error' => (string) ($reference['error'] ?? ''),
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'item' => null,
            'source' => 'error',
            'error' => $throwable->getMessage(),
        ];
    }
}
