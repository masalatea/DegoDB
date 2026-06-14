<?php

declare(strict_types=1);

require_once __DIR__ . '/project_language_resource_db_bridge.php';

// Migration/debug sync helper. Runtime never writes LanguageResource DB tables.

/**
 * @param array<string,mixed>|null $catalog
 * @return array{
 *     resources:int,
 *     groups:int,
 *     languages:int,
 *     group_languages:int,
 *     group_source_outputs:int,
 *     additional_group_assignments:int,
 *     captions:int
 * }
 */
function app_project_language_resource_sync_catalog_counts(?array $catalog): array
{
    return [
        'resources' => (int) ($catalog['resource_count'] ?? 0),
        'groups' => (int) ($catalog['group_count'] ?? 0),
        'languages' => (int) ($catalog['language_count'] ?? 0),
        'group_languages' => (int) ($catalog['group_language_count'] ?? 0),
        'group_source_outputs' => (int) ($catalog['group_source_output_count'] ?? 0),
        'additional_group_assignments' => (int) ($catalog['additional_group_assignment_count'] ?? 0),
        'captions' => (int) ($catalog['caption_count'] ?? 0),
    ];
}

// DB canonical is no longer a runtime read source. This service remains only for migration/debug sync.
/**
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         project_id:int,
 *         root_path:string,
 *         source_of_truth:string,
 *         apply:bool,
 *         catalog_counts:array{
 *             resources:int,
 *             groups:int,
 *             languages:int,
 *             group_languages:int,
 *             group_source_outputs:int,
 *             additional_group_assignments:int,
 *             captions:int
 *         },
 *         existing_counts:array{
 *             captions:int,
 *             additional_group_assignments:int,
 *             group_source_outputs:int,
 *             group_languages:int,
 *             resources:int,
 *             groups:int,
 *             languages:int
 *         },
 *         replaceable_counts:array{
 *             captions:int,
 *             additional_group_assignments:int,
 *             group_source_outputs:int,
 *             group_languages:int,
 *             resources:int,
 *             groups:int,
 *             languages:int
 *         },
 *         normalization:array<string,mixed>,
 *         pruned_counts:array{
 *             captions:int,
 *             additional_group_assignments:int,
 *             group_source_outputs:int,
 *             group_languages:int,
 *             resources:int,
 *             groups:int,
 *             languages:int
 *         },
 *         post_counts:array{
 *             captions:int,
 *             additional_group_assignments:int,
 *             group_source_outputs:int,
 *             group_languages:int,
 *             resources:int,
 *             groups:int,
 *             languages:int
 *         }
 *     },
 *     warnings:list<string>,
 *     errors:list<string>,
 *     error:string
 * }
 */
function app_project_language_resource_sync_from_file_tree(
    array $app,
    string $projectKey,
    bool $apply = false,
): array {
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $emptyTableCounts = [
        'captions' => 0,
        'additional_group_assignments' => 0,
        'group_source_outputs' => 0,
        'group_languages' => 0,
        'resources' => 0,
        'groups' => 0,
        'languages' => 0,
    ];
    $summary = [
        'project_key' => $normalizedProjectKey,
        'project_id' => 0,
        'root_path' => app_language_resource_file_catalog_default_root($normalizedProjectKey),
        'source_of_truth' => app_project_language_resource_file_catalog_source_of_truth(),
        'apply' => $apply,
        'catalog_counts' => [
            'resources' => 0,
            'groups' => 0,
            'languages' => 0,
            'group_languages' => 0,
            'group_source_outputs' => 0,
            'additional_group_assignments' => 0,
            'captions' => 0,
        ],
        'existing_counts' => $emptyTableCounts,
        'replaceable_counts' => $emptyTableCounts,
        'normalization' => [],
        'pruned_counts' => $emptyTableCounts,
        'post_counts' => $emptyTableCounts,
    ];

    if ($normalizedProjectKey === '' || !app_project_key_is_valid($normalizedProjectKey)) {
        return [
            'ok' => false,
            'summary' => $summary,
            'warnings' => [],
            'errors' => ['project key の形式が不正です。'],
            'error' => 'project key の形式が不正です。',
        ];
    }

    $fileCatalog = app_project_language_resource_load_file_catalog($normalizedProjectKey);
    $summary['root_path'] = $fileCatalog['root_path'];
    $summary['normalization'] = is_array($fileCatalog['manifest']['normalization'] ?? null)
        ? $fileCatalog['manifest']['normalization']
        : [];
    if ($fileCatalog['exists'] && is_array($fileCatalog['item'])) {
        $summary['catalog_counts'] = app_project_language_resource_sync_catalog_counts($fileCatalog['item']);
    }

    if (!$fileCatalog['exists']) {
        return [
            'ok' => false,
            'summary' => $summary,
            'warnings' => [],
            'errors' => ['file tree が見つかりません。先に export を実行してください。'],
            'error' => 'file tree が見つかりません。先に export を実行してください。',
        ];
    }

    if (!$fileCatalog['ok'] || !is_array($fileCatalog['item'])) {
        $error = $fileCatalog['error'] !== '' ? $fileCatalog['error'] : 'file tree の読み込みに失敗しました。';

        return [
            'ok' => false,
            'summary' => $summary,
            'warnings' => $fileCatalog['warnings'],
            'errors' => $fileCatalog['errors'] !== [] ? $fileCatalog['errors'] : [$error],
            'error' => $error,
        ];
    }

    try {
        $pdo = app_create_pdo($app);
    } catch (Throwable $throwable) {
        if (!$apply) {
            return [
                'ok' => true,
                'summary' => $summary,
                'warnings' => array_values(array_unique(array_merge(
                    $fileCatalog['warnings'],
                    ['DB preview を取得できませんでした: ' . $throwable->getMessage()],
                ))),
                'errors' => [],
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'summary' => $summary,
            'warnings' => $fileCatalog['warnings'],
            'errors' => [$throwable->getMessage()],
            'error' => $throwable->getMessage(),
        ];
    }

    if (!app_project_language_resource_canonical_tables_available($pdo)) {
        if (!$apply) {
            return [
                'ok' => true,
                'summary' => $summary,
                'warnings' => array_values(array_unique(array_merge(
                    $fileCatalog['warnings'],
                    ['canonical table が未作成のため DB preview は空です。'],
                ))),
                'errors' => [],
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'summary' => $summary,
            'warnings' => $fileCatalog['warnings'],
            'errors' => ['canonical table が未作成です。'],
            'error' => 'canonical table が未作成です。',
        ];
    }

    try {
        $projectId = app_project_language_resource_pdo_resolve_project_id(
            $pdo,
            $normalizedProjectKey,
        );
    } catch (Throwable $throwable) {
        if (!$apply) {
            return [
                'ok' => true,
                'summary' => $summary,
                'warnings' => array_values(array_unique(array_merge(
                    $fileCatalog['warnings'],
                    ['project 解決に失敗したため DB preview は空です: ' . $throwable->getMessage()],
                ))),
                'errors' => [],
                'error' => '',
            ];
        }

        return [
            'ok' => false,
            'summary' => $summary,
            'warnings' => $fileCatalog['warnings'],
            'errors' => [$throwable->getMessage()],
            'error' => $throwable->getMessage(),
        ];
    }

    $summary['project_id'] = $projectId;
    $summary['existing_counts'] = app_project_language_resource_canonical_table_counts($pdo, $projectId);
    $replaceSourceOfTruths = [
        app_project_language_resource_file_catalog_source_of_truth(),
        app_project_language_resource_bootstrap_reference_source_of_truth(),
    ];
    $summary['replaceable_counts'] = app_project_language_resource_canonical_table_counts(
        $pdo,
        $projectId,
        $replaceSourceOfTruths,
    );

    if (!$apply) {
        return [
            'ok' => true,
            'summary' => $summary,
            'warnings' => $fileCatalog['warnings'],
            'errors' => [],
            'error' => '',
        ];
    }

    $importResult = app_project_language_resource_import_catalog_into_canonical(
        $pdo,
        $normalizedProjectKey,
        $fileCatalog['item'],
        app_project_language_resource_file_catalog_source_of_truth(),
        app_project_language_resource_file_catalog_notes(),
        0,
        $replaceSourceOfTruths,
    );
    $summary['pruned_counts'] = $importResult['summary']['pruned_counts'] ?? $emptyTableCounts;
    if ($importResult['ok']) {
        $summary['post_counts'] = app_project_language_resource_canonical_table_counts($pdo, $projectId);
    }

    return [
        'ok' => $importResult['ok'],
        'summary' => $summary,
        'warnings' => $fileCatalog['warnings'],
        'errors' => $importResult['ok'] ? [] : [$importResult['error']],
        'error' => $importResult['error'],
    ];
}
