<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/error_page.php';
require_once __DIR__ . '/project_repository.php';
require_once __DIR__ . '/project_language_resource_catalog_loader.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/source_output_repository.php';

function app_project_language_resources_path(string $projectKey): string
{
    return '/projects/' . rawurlencode($projectKey) . '/language-resources';
}

function app_project_language_resource_groups_path(string $projectKey): string
{
    return app_project_language_resources_path($projectKey) . '/groups';
}

function app_project_language_resource_detail_path(string $projectKey, string $resourceKey): string
{
    return app_project_language_resources_path($projectKey) . '/' . rawurlencode($resourceKey);
}

/**
 * @return list<string>
 */
function app_project_language_resource_bridge_errors_from_request(): array
{
    $items = [];
    foreach ([($_GET['bridge_errors'] ?? null), ($_POST['bridge_errors'] ?? null)] as $rawValue) {
        if (is_array($rawValue)) {
            foreach ($rawValue as $rawItem) {
                if (!is_string($rawItem) && !is_numeric($rawItem)) {
                    continue;
                }

                $normalized = trim((string) $rawItem);
                if ($normalized === '') {
                    continue;
                }

                $items[$normalized] = $normalized;
            }
            continue;
        }

        if (!is_string($rawValue) && !is_numeric($rawValue)) {
            continue;
        }

        $normalized = trim((string) $rawValue);
        if ($normalized === '') {
            continue;
        }

        $items[$normalized] = $normalized;
    }

    return array_values($items);
}

function app_project_language_resource_catalog_source_caption(string $source): string
{
    return match (trim($source)) {
        'file-canonical' => 'file canonical',
        'reference' => 'copied reference fallback',
        'empty' => 'empty catalog',
        default => 'unknown',
    };
}

/**
 * @param array<string,mixed> $catalog
 * @return array{
 *     state:string,
 *     module_status:string,
 *     title:string,
 *     summary:string,
 *     readonly_message:string,
 *     editor_available:bool
 * }
 */
function app_project_language_resource_module_state(
    string $catalogSource,
    array $catalog,
    string $catalogError = '',
): array {
    $resourceCount = max(0, (int) ($catalog['resource_count'] ?? 0));
    $groupCount = max(0, (int) ($catalog['group_count'] ?? 0));
    $countSummary = 'resources '
        . $resourceCount
        . ' / groups '
        . $groupCount
        . '。';

    return match (trim($catalogSource)) {
        'file-canonical' => [
            'state' => 'file-canonical',
            'module_status' => 'available-partial',
            'title' => 'file canonical available',
            'summary' => 'file-based canonical catalog を primary source として表示しています。current admin は browse/search/detail の確認導線に寄せ、LanguageResource の編集自体は repo file を直接更新する前提です。'
                . $countSummary,
            'readonly_message' => 'LanguageResource は file canonical を正本として表示中です。Lang 編集画面は前提にせず、変更は repo 配下の JSON file を直接更新してください。',
            'editor_available' => false,
        ],
        'reference' => [
            'state' => 'reference',
            'module_status' => 'optional-readonly',
            'title' => 'reference fallback',
            'summary' => 'copied legacy reference fallback で optional module を表示しています。LanguageResource の編集は current admin では扱わず、移行時に file canonical へ変換する前提です。'
                . $countSummary,
            'readonly_message' => 'LanguageResource optional module は reference fallback の read-only 表示です。編集が必要なら file canonical へ移し、repo 配下の JSON file を直接更新してください。',
            'editor_available' => false,
        ],
        'empty' => [
            'state' => 'empty',
            'module_status' => 'optional-off',
            'title' => 'optional module off',
            'summary' => 'LanguageResource optional module は未ロードです。current admin は empty catalog の read-only 表示だけを行います。'
                . $countSummary,
            'readonly_message' => 'LanguageResource optional module はこの環境では未ロードです。current admin は empty catalog の read-only 表示だけを行います。',
            'editor_available' => false,
        ],
        default => [
            'state' => 'error',
            'module_status' => 'blocked',
            'title' => 'module state unknown',
            'summary' => 'LanguageResource module state の解決に失敗しました。'
                . ($catalogError !== '' ? ' error: ' . $catalogError : ''),
            'readonly_message' => 'LanguageResource module state の解決に失敗したため、editor を無効化しています。'
                . ($catalogError !== '' ? ' ' . $catalogError : ''),
            'editor_available' => false,
        ],
    };
}

/**
 * @return array{
 *     exists:bool,
 *     ok:bool,
 *     root_path:string,
 *     manifest_path:string,
 *     group_file_paths_by_pid:array<string,string>,
 *     resource_file_paths_by_key:array<string,string>,
 *     errors:list<string>,
 *     warnings:list<string>
 * }
 */
function app_project_language_resource_file_locations(string $projectKey): array
{
    $rootPath = app_language_resource_file_catalog_default_root($projectKey);
    $normalizedRootPath = rtrim($rootPath, '/');
    $manifestPath = $normalizedRootPath . '/manifest.json';
    if (!is_file($manifestPath)) {
        return [
            'exists' => false,
            'ok' => false,
            'root_path' => $normalizedRootPath,
            'manifest_path' => $manifestPath,
            'group_file_paths_by_pid' => [],
            'resource_file_paths_by_key' => [],
            'errors' => [],
            'warnings' => [],
        ];
    }

    $loaded = app_language_resource_file_catalog_load_catalog($normalizedRootPath);
    $groupFilePathsByPid = [];
    $resourceFilePathsByKey = [];
    foreach ($loaded['groups'] as $groupEntry) {
        if (!is_array($groupEntry)) {
            continue;
        }

        $group = is_array($groupEntry['group'] ?? null) ? $groupEntry['group'] : [];
        $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0);
        $groupFilePath = trim((string) ($groupEntry['file_path'] ?? ''));
        if ($legacyGroupPid > 0 && $groupFilePath !== '') {
            $groupFilePathsByPid[(string) $legacyGroupPid] = $groupFilePath;
        }

        foreach ((is_array($groupEntry['resources'] ?? null) ? $groupEntry['resources'] : []) as $resourceEntry) {
            if (!is_array($resourceEntry)) {
                continue;
            }

            $resource = is_array($resourceEntry['item'] ?? null) ? $resourceEntry['item'] : [];
            $resourceKey = trim((string) ($resource['resource_key'] ?? $resourceEntry['resource_key'] ?? ''));
            $resourceFilePath = trim((string) ($resourceEntry['file_path'] ?? ''));
            if ($resourceKey === '' || $resourceFilePath === '') {
                continue;
            }

            $resourceFilePathsByKey[$resourceKey] = $resourceFilePath;
        }
    }

    return [
        'exists' => true,
        'ok' => $loaded['ok'],
        'root_path' => $loaded['root_path'],
        'manifest_path' => $manifestPath,
        'group_file_paths_by_pid' => $groupFilePathsByPid,
        'resource_file_paths_by_key' => $resourceFilePathsByKey,
        'errors' => $loaded['errors'],
        'warnings' => $loaded['warnings'],
    ];
}

/**
 * @return array{
 *     state:string,
 *     module_status:string,
 *     title:string,
 *     summary:string,
 *     readonly_message:string,
 *     editor_available:bool
 * }
 */
function app_project_language_resource_module_state_for_project(
    array $app,
    string $projectKey,
    int $fallbackProjectPid = 0,
): array {
    $catalogResult = app_fetch_project_language_resource_catalog(
        $app,
        $projectKey,
        $fallbackProjectPid,
    );
    $catalog = is_array($catalogResult['item'] ?? null)
        ? $catalogResult['item']
        : app_legacy_language_resource_reference_empty($projectKey, $fallbackProjectPid);

    return app_project_language_resource_module_state(
        $catalogResult['ok'] ? (string) ($catalogResult['source'] ?? 'unknown') : 'error',
        $catalog,
        (string) ($catalogResult['error'] ?? ''),
    );
}

function app_project_language_resource_extract_legacy_source_output_pid(string $notes): int
{
    if (
        preg_match(
            '/\bProjectSourceOutput\.PID\s*=\s*(\d+)/u',
            $notes,
            $matches,
        ) !== 1
    ) {
        return 0;
    }

    return (int) ($matches[1] ?? 0);
}

/**
 * @param list<array{
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     class_type:string,
 *     release_target_type:string,
 *     source_template_dir:string,
 *     source_output_dir:string,
 *     source_temp_output_dir:string,
 *     proxy_base_url:string,
 *     autoload_filename_suffix:string,
 *     source_text_char_code:string,
 *     runtime_source_relative_path:string,
 *     artifact_strategy:string,
 *     target_binding_type:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $catalog
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     class_type:string,
 *     artifact_strategy:string,
 *     source_output_dir:string,
 *     notes:string
 * }>
 */
function app_project_language_resource_source_output_catalog_by_legacy_pid_from_items(array $catalog): array
{
    $items = [];
    foreach ($catalog as $sourceOutput) {
        $legacyPid = app_project_language_resource_extract_legacy_source_output_pid((string) ($sourceOutput['notes'] ?? ''));
        if ($legacyPid <= 0) {
            continue;
        }

        $items[(string) $legacyPid] = [
            'legacy_project_source_output_pid' => $legacyPid,
            'source_output_key' => trim((string) ($sourceOutput['source_output_key'] ?? '')),
            'name' => trim((string) ($sourceOutput['name'] ?? '')),
            'class_type' => trim((string) ($sourceOutput['class_type'] ?? '')),
            'artifact_strategy' => trim((string) ($sourceOutput['artifact_strategy'] ?? '')),
            'source_output_dir' => trim((string) ($sourceOutput['source_output_dir'] ?? '')),
            'notes' => (string) ($sourceOutput['notes'] ?? ''),
        ];
    }

    ksort($items, SORT_NATURAL);

    return $items;
}

/**
 * @return array{
 *     ok:bool,
 *     items:array<string,array{
 *         legacy_project_source_output_pid:int,
 *         source_output_key:string,
 *         name:string,
 *         class_type:string,
 *         artifact_strategy:string,
 *         source_output_dir:string,
 *         notes:string
 *     }>,
 *     error:string
 * }
 */
function app_project_language_resource_source_output_catalog_by_legacy_pid(array $app, string $projectKey): array
{
    $catalogResult = app_fetch_project_source_output_catalog($app, $projectKey);
    if (!$catalogResult['ok']) {
        return [
            'ok' => false,
            'items' => [],
            'error' => $catalogResult['error'],
        ];
    }

    return [
        'ok' => true,
        'items' => app_project_language_resource_source_output_catalog_by_legacy_pid_from_items($catalogResult['items']),
        'error' => '',
    ];
}

/**
 * @param list<array{
 *     legacy_group_pid:int,
 *     project_pid:int,
 *     name:string,
 *     function_name_prefix:string,
 *     function_name_suffix:string,
 *     filename_suffix_for_php:string,
 *     filename_suffix:string,
 *     filename_for_xcode:string,
 *     last_modified_dt:string
 * }> $groups
 * @return array<string,array{
 *     legacy_group_pid:int,
 *     project_pid:int,
 *     name:string,
 *     function_name_prefix:string,
 *     function_name_suffix:string,
 *     filename_suffix_for_php:string,
 *     filename_suffix:string,
 *     filename_for_xcode:string,
 *     last_modified_dt:string
 * }>
 */
function app_project_language_resource_groups_by_pid(array $groups): array
{
    $indexed = [];
    foreach ($groups as $group) {
        $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0);
        if ($legacyGroupPid <= 0) {
            continue;
        }

        $indexed[(string) $legacyGroupPid] = $group;
    }

    return $indexed;
}

/**
 * @param list<array{
 *     legacy_language_pid:int,
 *     filename_suffix:string,
 *     template_key:string,
 *     is_default:int,
 *     caption:string,
 *     lang_for_cs:string,
 *     lang_for_android:string,
 *     lang_for_ios:string,
 *     lang_for_google:string
 * }> $languages
 * @return array<string,array{
 *     legacy_language_pid:int,
 *     filename_suffix:string,
 *     template_key:string,
 *     is_default:int,
 *     caption:string,
 *     lang_for_cs:string,
 *     lang_for_android:string,
 *     lang_for_ios:string,
 *     lang_for_google:string
 * }>
 */
function app_project_language_resource_languages_by_pid(array $languages): array
{
    $indexed = [];
    foreach ($languages as $language) {
        $legacyLanguagePid = (int) ($language['legacy_language_pid'] ?? 0);
        if ($legacyLanguagePid <= 0) {
            continue;
        }

        $indexed[(string) $legacyLanguagePid] = $language;
    }

    return $indexed;
}

/**
 * @param list<array{
 *     legacy_group_language_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     legacy_language_pid:int
 * }> $groupLanguages
 * @return array<string,list<array{
 *     legacy_group_language_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     legacy_language_pid:int
 * }>>
 */
function app_project_language_resource_group_languages_by_group_pid(array $groupLanguages): array
{
    $grouped = [];
    foreach ($groupLanguages as $groupLanguage) {
        $legacyGroupPid = (string) ($groupLanguage['legacy_group_pid'] ?? 0);
        if ($legacyGroupPid === '0') {
            continue;
        }

        if (!array_key_exists($legacyGroupPid, $grouped)) {
            $grouped[$legacyGroupPid] = [];
        }

        $grouped[$legacyGroupPid][] = $groupLanguage;
    }

    return $grouped;
}

/**
 * @param list<array{
 *     legacy_group_source_output_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     legacy_project_source_output_pid:int
 * }> $groupSourceOutputs
 * @return array<string,list<array{
 *     legacy_group_source_output_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     legacy_project_source_output_pid:int
 * }>>
 */
function app_project_language_resource_group_source_outputs_by_group_pid(array $groupSourceOutputs): array
{
    $grouped = [];
    foreach ($groupSourceOutputs as $groupSourceOutput) {
        $legacyGroupPid = (string) ($groupSourceOutput['legacy_group_pid'] ?? 0);
        if ($legacyGroupPid === '0') {
            continue;
        }

        if (!array_key_exists($legacyGroupPid, $grouped)) {
            $grouped[$legacyGroupPid] = [];
        }

        $grouped[$legacyGroupPid][] = $groupSourceOutput;
    }

    return $grouped;
}

/**
 * @param list<array{
 *     legacy_assignment_pid:int,
 *     project_pid:int,
 *     legacy_resource_pid:int,
 *     legacy_group_pid:int
 * }> $assignments
 * @return array<string,list<array{
 *     legacy_assignment_pid:int,
 *     project_pid:int,
 *     legacy_resource_pid:int,
 *     legacy_group_pid:int
 * }>>
 */
function app_project_language_resource_additional_groups_by_resource_pid(array $assignments): array
{
    $grouped = [];
    foreach ($assignments as $assignment) {
        $legacyResourcePid = (string) ($assignment['legacy_resource_pid'] ?? 0);
        if ($legacyResourcePid === '0') {
            continue;
        }

        if (!array_key_exists($legacyResourcePid, $grouped)) {
            $grouped[$legacyResourcePid] = [];
        }

        $grouped[$legacyResourcePid][] = $assignment;
    }

    return $grouped;
}

/**
 * @param list<array{
 *     legacy_caption_pid:int,
 *     project_pid:int,
 *     legacy_resource_pid:int,
 *     legacy_group_pid:int,
 *     legacy_language_pid:int,
 *     caption:string,
 *     caption_auto_translated:string
 * }> $captions
 * @return array<string,array<string,array{
 *     legacy_caption_pid:int,
 *     project_pid:int,
 *     legacy_resource_pid:int,
 *     legacy_group_pid:int,
 *     legacy_language_pid:int,
 *     caption:string,
 *     caption_auto_translated:string
 * }>>
 */
function app_project_language_resource_captions_by_resource_pid(array $captions): array
{
    $grouped = [];
    foreach ($captions as $caption) {
        $legacyResourcePid = (string) ($caption['legacy_resource_pid'] ?? 0);
        $legacyLanguagePid = (string) ($caption['legacy_language_pid'] ?? 0);
        if ($legacyResourcePid === '0' || $legacyLanguagePid === '0') {
            continue;
        }

        if (!array_key_exists($legacyResourcePid, $grouped)) {
            $grouped[$legacyResourcePid] = [];
        }

        $grouped[$legacyResourcePid][$legacyLanguagePid] = $caption;
    }

    return $grouped;
}

/**
 * @param list<array{
 *     legacy_resource_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     resource_key:string,
 *     key_for_update:string,
 *     sort_group:string,
 *     key_name:string,
 *     key_name_for_xcode:string,
 *     uwp_target_property:string,
 *     is_resource_fixed:int,
 *     use_default_if_caption_is_blank:int
 * }> $resources
 * @return array<string,list<array{
 *     legacy_resource_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     resource_key:string,
 *     key_for_update:string,
 *     sort_group:string,
 *     key_name:string,
 *     key_name_for_xcode:string,
 *     uwp_target_property:string,
 *     is_resource_fixed:int,
 *     use_default_if_caption_is_blank:int
 * }>>
 */
function app_project_language_resource_resources_by_group_pid(array $resources): array
{
    $grouped = [];
    foreach ($resources as $resource) {
        $legacyGroupPid = (string) ($resource['legacy_group_pid'] ?? 0);
        if ($legacyGroupPid === '0') {
            continue;
        }

        if (!array_key_exists($legacyGroupPid, $grouped)) {
            $grouped[$legacyGroupPid] = [];
        }

        $grouped[$legacyGroupPid][] = $resource;
    }

    return $grouped;
}

/**
 * @param list<array{
 *     legacy_resource_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     resource_key:string,
 *     key_for_update:string,
 *     sort_group:string,
 *     key_name:string,
 *     key_name_for_xcode:string,
 *     uwp_target_property:string,
 *     is_resource_fixed:int,
 *     use_default_if_caption_is_blank:int
 * }> $resources
 * @return array{
 *     legacy_resource_pid:int,
 *     project_pid:int,
 *     legacy_group_pid:int,
 *     resource_key:string,
 *     key_for_update:string,
 *     sort_group:string,
 *     key_name:string,
 *     key_name_for_xcode:string,
 *     uwp_target_property:string,
 *     is_resource_fixed:int,
 *     use_default_if_caption_is_blank:int
 * }|null
 */
function app_project_language_resource_resource_by_key(array $resources, string $resourceKey): ?array
{
    $normalizedResourceKey = trim($resourceKey);
    if ($normalizedResourceKey === '') {
        return null;
    }

    foreach ($resources as $resource) {
        if ((string) ($resource['resource_key'] ?? '') === $normalizedResourceKey) {
            return $resource;
        }
    }

    return null;
}

/**
 * @param list<array{
 *     legacy_language_pid:int,
 *     filename_suffix:string,
 *     template_key:string,
 *     is_default:int,
 *     caption:string,
 *     lang_for_cs:string,
 *     lang_for_android:string,
 *     lang_for_ios:string,
 *     lang_for_google:string
 * }> $languages
 */
function app_project_language_resource_find_language_pid_by_suffix(array $languages, string $filenameSuffix): int
{
    $normalizedSuffix = trim($filenameSuffix);
    if ($normalizedSuffix === '') {
        return 0;
    }

    foreach ($languages as $language) {
        if ((string) ($language['filename_suffix'] ?? '') === $normalizedSuffix) {
            return (int) ($language['legacy_language_pid'] ?? 0);
        }
    }

    return 0;
}

/**
 * @param list<array{
 *     legacy_language_pid:int,
 *     filename_suffix:string,
 *     template_key:string,
 *     is_default:int,
 *     caption:string,
 *     lang_for_cs:string,
 *     lang_for_android:string,
 *     lang_for_ios:string,
 *     lang_for_google:string
 * }> $languages
 */
function app_project_language_resource_default_language_pid(array $languages): int
{
    foreach ($languages as $language) {
        if ((int) ($language['is_default'] ?? 0) === 1) {
            return (int) ($language['legacy_language_pid'] ?? 0);
        }
    }

    return (int) (($languages[0]['legacy_language_pid'] ?? 0));
}

function app_project_language_resource_preview_text(string $value, int $maxWidth = 80): string
{
    $normalized = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    if ($normalized === '') {
        return '';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($normalized, 0, $maxWidth, '…', 'UTF-8');
    }

    if (strlen($normalized) <= $maxWidth) {
        return $normalized;
    }

    return substr($normalized, 0, max(0, $maxWidth - 3)) . '...';
}

function app_project_language_resource_page_styles(): string
{
    return <<<CSS
        body {
            font-family: sans-serif;
            margin: 2rem;
            line-height: 1.6;
            background: #f8fafc;
            color: #0f172a;
        }
        main {
            max-width: 96rem;
            background: #ffffff;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 2rem;
        }
        code {
            background: #edf2f7;
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
        }
        .breadcrumbs {
            margin-bottom: 1rem;
        }
        .summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin: 1.5rem 0;
        }
        .summary-card,
        .note-card,
        .warning-card,
        .error-card,
        .success-card,
        .editor-card {
            border: 1px solid #d7dde5;
            border-radius: 12px;
            padding: 1rem;
        }
        .summary-card {
            background: #f8fafc;
        }
        .note-card {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .warning-card {
            background: #fefce8;
            border-color: #facc15;
        }
        .error-card {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .success-card {
            background: #dcfce7;
            border-color: #86efac;
        }
        .editor-card {
            background: #f8fafc;
            margin-top: 1.5rem;
        }
        .toolbar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: end;
            margin: 1.5rem 0;
            padding: 1rem;
            border: 1px solid #d7dde5;
            border-radius: 12px;
            background: #f8fafc;
        }
        .toolbar label {
            display: block;
            font-weight: 600;
            min-width: 16rem;
        }
        .toolbar input,
        .toolbar select,
        .toolbar button {
            font: inherit;
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.7rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        .toolbar button {
            width: auto;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
            border: 0;
            min-width: 10rem;
        }
        label {
            display: block;
            font-weight: 600;
        }
        input,
        select,
        textarea,
        button {
            font: inherit;
        }
        input[type="text"],
        input[type="search"],
        select,
        textarea {
            width: 100%;
            box-sizing: border-box;
            margin-top: 0.35rem;
            padding: 0.7rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
        }
        textarea {
            min-height: 6rem;
            resize: vertical;
        }
        button {
            margin-top: 1rem;
            padding: 0.7rem 1rem;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
        }
        button.secondary {
            background: #334155;
        }
        button.danger {
            background: #991b1b;
        }
        .form-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
        .form-field-wide {
            grid-column: 1 / -1;
        }
        .checkbox-grid {
            display: grid;
            gap: 0.5rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: 0.5rem;
        }
        .checkbox-item {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
            padding: 0.65rem 0.75rem;
            border: 1px solid #d7dde5;
            border-radius: 10px;
            background: #ffffff;
        }
        .checkbox-item input {
            width: auto;
            margin: 0.2rem 0 0;
        }
        .button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .button-row.compact button {
            margin-top: 0.5rem;
            padding: 0.45rem 0.7rem;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        th, td {
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f8fafc;
            font-weight: 700;
        }
        .muted {
            color: #475569;
        }
        .chip-list {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .chip {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 0.9rem;
        }
        .caption-cell {
            white-space: pre-wrap;
            word-break: break-word;
        }
        .path-meta {
            display: block;
            margin-top: 0.25rem;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .inline-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .translation-status {
            margin-top: 0.75rem;
            min-height: 1.5rem;
        }
        .translation-error {
            color: #991b1b;
        }
CSS;
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     },
 *     project:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     },
 *     project_key:string,
 *     reference:array{
 *         project_key:string,
 *         project_pid:int,
 *         source_dump_path:string,
 *         generated_at:string,
 *         resource_count:int,
 *         group_count:int,
 *         group_language_count:int,
 *         group_source_output_count:int,
 *         additional_group_assignment_count:int,
 *         caption_count:int,
 *         language_count:int,
 *         resources:list<array{
 *             legacy_resource_pid:int,
 *             project_pid:int,
 *             legacy_group_pid:int,
 *             resource_key:string,
 *             key_for_update:string,
 *             sort_group:string,
 *             key_name:string,
 *             key_name_for_xcode:string,
 *             uwp_target_property:string,
 *             is_resource_fixed:int,
 *             use_default_if_caption_is_blank:int
 *         }>,
 *         groups:list<array{
 *             legacy_group_pid:int,
 *             project_pid:int,
 *             name:string,
 *             function_name_prefix:string,
 *             function_name_suffix:string,
 *             filename_suffix_for_php:string,
 *             filename_suffix:string,
 *             filename_for_xcode:string,
 *             last_modified_dt:string
 *         }>,
 *         group_languages:list<array{
 *             legacy_group_language_pid:int,
 *             project_pid:int,
 *             legacy_group_pid:int,
 *             legacy_language_pid:int
 *         }>,
 *         group_source_outputs:list<array{
 *             legacy_group_source_output_pid:int,
 *             project_pid:int,
 *             legacy_group_pid:int,
 *             legacy_project_source_output_pid:int
 *         }>,
 *         additional_group_assignments:list<array{
 *             legacy_assignment_pid:int,
 *             project_pid:int,
 *             legacy_resource_pid:int,
 *             legacy_group_pid:int
 *         }>,
 *         captions:list<array{
 *             legacy_caption_pid:int,
 *             project_pid:int,
 *             legacy_resource_pid:int,
 *             legacy_group_pid:int,
 *             legacy_language_pid:int,
 *             caption:string,
 *             caption_auto_translated:string
 *         }>,
 *         languages:list<array{
 *             legacy_language_pid:int,
 *             filename_suffix:string,
 *             template_key:string,
 *             is_default:int,
 *             caption:string,
 *             lang_for_cs:string,
 *             lang_for_android:string,
 *             lang_for_ios:string,
 *             lang_for_google:string
 *         }>
 *     },
 *     reference_error:string,
 *     catalog_source:string,
 *     file_locations:array{
 *         exists:bool,
 *         ok:bool,
 *         root_path:string,
 *         manifest_path:string,
 *         group_file_paths_by_pid:array<string,string>,
 *         resource_file_paths_by_key:array<string,string>,
 *         errors:list<string>,
 *         warnings:list<string>
 *     }
 * }|null
 */
function app_project_language_resource_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    if ($app['site'] !== 'admin') {
        app_render_forbidden_page($app, $request, 'この route は 設定変更用サイト でのみ利用します。');
        return null;
    }

    $principal = app_auth_principal();
    if ($principal === null) {
        app_send_redirect_response($request, app_auth_login_path());
        return null;
    }

    if (!app_auth_has_any_role(['admin', 'config'], $principal)) {
        app_render_forbidden_page($app, $request, 'language resource 参照には admin または config role が必要です。');
        return null;
    }

    $normalizedAllowedMethods = array_values(
        array_filter(
            array_map(
                static fn (string $method): string => strtoupper(trim($method)),
                $allowedMethods,
            ),
            static fn (string $method): bool => $method !== '',
        ),
    );
    if ($normalizedAllowedMethods === []) {
        $normalizedAllowedMethods = ['GET'];
    }

    if (!in_array(strtoupper($request['method']), $normalizedAllowedMethods, true)) {
        app_render_method_not_allowed_page($app, $request, $normalizedAllowedMethods);
        return null;
    }

    $projectKey = app_normalize_project_key(app_route_param($request, 'project_key'));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        app_render_bad_request_page($app, $request, 'project key の形式が不正です。');
        return null;
    }

    $projectResult = app_fetch_project_by_key($app, $projectKey);
    if (!$projectResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Language Resources</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>language resource project の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($projectResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    if ($projectResult['item'] === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $catalogResult = app_fetch_project_language_resource_catalog(
        $app,
        $projectKey,
        (int) ($projectResult['item']['legacy_project_pid'] ?? 0),
    );
    if (!$catalogResult['ok']) {
        app_send_html_response_headers($request, 500);
        ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo app_h($app['site_name']); ?> - Language Resources</title>
</head>
<body>
<main>
    <h1><?php echo app_h($app['site_name']); ?></h1>
    <p>language resource catalog の読み込みに失敗しました。</p>
    <ul>
        <li>project key: <code><?php echo app_h($projectKey); ?></code></li>
        <li>request id: <code><?php echo app_h($request['request_id']); ?></code></li>
        <li>error: <code><?php echo app_h($catalogResult['error']); ?></code></li>
    </ul>
</main>
</body>
</html>
        <?php
        return null;
    }

    $catalog = $catalogResult['item'];
    if (!is_array($catalog)) {
        $catalog = app_legacy_language_resource_reference_empty(
            $projectKey,
            (int) ($projectResult['item']['legacy_project_pid'] ?? 0),
        );
    }

    $fileLocations = app_project_language_resource_file_locations($projectKey);

    return [
        'app' => $app,
        'request' => $request,
        'principal' => $principal,
        'project' => $projectResult['item'],
        'project_key' => $projectKey,
        'reference' => $catalog,
        'reference_error' => (string) ($catalogResult['error'] ?? ''),
        'catalog_source' => (string) ($catalogResult['source'] ?? 'unknown'),
        'file_locations' => $fileLocations,
    ];
}

/**
 * @param array{
 *     site:string,
 *     site_name:string,
 *     db:array{
 *         name:string
 *     }
 * } $app
 * @param array{
 *     request_id:string,
 *     method:string,
 *     path:string,
 *     route_params?:array<string,string>
 * } $request
 * @param list<string> $allowedMethods
 * @return array{
 *     app:array,
 *     request:array,
 *     principal:array{
 *         id:string,
 *         display_name:string,
 *         roles:list<string>
 *     },
 *     project:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         owner_login_id:string,
 *         member_count:int,
 *         updated_at:string,
 *         description:string
 *     },
 *     project_key:string,
 *     reference:array,
 *     reference_error:string,
 *     resource:array{
 *         legacy_resource_pid:int,
 *         project_pid:int,
 *         legacy_group_pid:int,
 *         resource_key:string,
 *         key_for_update:string,
 *         sort_group:string,
 *         key_name:string,
 *         key_name_for_xcode:string,
 *         uwp_target_property:string,
 *         is_resource_fixed:int,
 *         use_default_if_caption_is_blank:int
 *     }
 * }|null
 */
function app_project_language_resource_item_route_bootstrap(
    array $app,
    array $request,
    array $allowedMethods = ['GET'],
): ?array {
    $context = app_project_language_resource_route_bootstrap($app, $request, $allowedMethods);
    if ($context === null) {
        return null;
    }

    $resourceKey = trim(app_route_param($request, 'resource_key'));
    if ($resourceKey === '') {
        app_render_bad_request_page($app, $request, 'resource key が必要です。');
        return null;
    }

    $resource = app_project_language_resource_resource_by_key(
        $context['reference']['resources'],
        $resourceKey,
    );
    if ($resource === null) {
        app_render_not_found_page($app, $request);
        return null;
    }

    $context['resource'] = $resource;

    return $context;
}
