<?php

declare(strict_types=1);

/**
 * @return array{
 *     project_key:string,
 *     project_pid:int,
 *     source_dump_path:string,
 *     generated_at:string,
 *     resource_count:int,
 *     group_count:int,
 *     group_language_count:int,
 *     group_source_output_count:int,
 *     additional_group_assignment_count:int,
 *     caption_count:int,
 *     language_count:int,
 *     resources:list<array{
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
 *     }>,
 *     groups:list<array{
 *         legacy_group_pid:int,
 *         project_pid:int,
 *         name:string,
 *         function_name_prefix:string,
 *         function_name_suffix:string,
 *         filename_suffix_for_php:string,
 *         filename_suffix:string,
 *         filename_for_xcode:string,
 *         last_modified_dt:string
 *     }>,
 *     group_languages:list<array{
 *         legacy_group_language_pid:int,
 *         project_pid:int,
 *         legacy_group_pid:int,
 *         legacy_language_pid:int
 *     }>,
 *     group_source_outputs:list<array{
 *         legacy_group_source_output_pid:int,
 *         project_pid:int,
 *         legacy_group_pid:int,
 *         legacy_project_source_output_pid:int
 *     }>,
 *     additional_group_assignments:list<array{
 *         legacy_assignment_pid:int,
 *         project_pid:int,
 *         legacy_resource_pid:int,
 *         legacy_group_pid:int
 *     }>,
 *     captions:list<array{
 *         legacy_caption_pid:int,
 *         project_pid:int,
 *         legacy_resource_pid:int,
 *         legacy_group_pid:int,
 *         legacy_language_pid:int,
 *         caption:string,
 *         caption_auto_translated:string
 *     }>,
 *     languages:list<array{
 *         legacy_language_pid:int,
 *         filename_suffix:string,
 *         template_key:string,
 *         is_default:int,
 *         caption:string,
 *         lang_for_cs:string,
 *         lang_for_android:string,
 *         lang_for_ios:string,
 *         lang_for_google:string
 *     }>
 * }
 */
function app_legacy_language_resource_reference_empty(string $projectKey = '', int $projectPid = 0): array
{
    return [
        'project_key' => strtoupper(trim($projectKey)),
        'project_pid' => $projectPid,
        'source_dump_path' => '',
        'generated_at' => '',
        'resource_count' => 0,
        'group_count' => 0,
        'group_language_count' => 0,
        'group_source_output_count' => 0,
        'additional_group_assignment_count' => 0,
        'caption_count' => 0,
        'language_count' => 0,
        'resources' => [],
        'groups' => [],
        'group_languages' => [],
        'group_source_outputs' => [],
        'additional_group_assignments' => [],
        'captions' => [],
        'languages' => [],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     item:array{
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
 *     }|null,
 *     error:string
 * }
 */
function app_load_legacy_language_resource_reference(string $projectKey): array
{
    $referencePath = app_legacy_language_resource_reference_path($projectKey);
    if ($referencePath === '') {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'この project に対応する legacy language resource reference はまだありません。',
        ];
    }

    if (!is_file($referencePath)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy language resource reference が見つかりません: ' . $referencePath,
        ];
    }

    $contents = file_get_contents($referencePath);
    if (!is_string($contents)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy language resource reference を読み込めません。',
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy language resource reference の JSON が不正です。',
        ];
    }

    $resources = $decoded['resources'] ?? null;
    $groups = $decoded['groups'] ?? null;
    $groupLanguages = $decoded['group_languages'] ?? null;
    $groupSourceOutputs = $decoded['group_source_outputs'] ?? null;
    $additionalGroupAssignments = $decoded['additional_group_assignments'] ?? null;
    $captions = $decoded['captions'] ?? null;
    $languages = $decoded['languages'] ?? null;
    if (
        !is_array($resources)
        || !is_array($groups)
        || !is_array($groupLanguages)
        || !is_array($groupSourceOutputs)
        || !is_array($additionalGroupAssignments)
        || !is_array($captions)
        || !is_array($languages)
    ) {
        return [
            'ok' => false,
            'item' => null,
            'error' => 'legacy language resource reference の resources / groups / relations が不正です。',
        ];
    }

    $normalizedResources = [];
    $existingResourceKeys = [];
    foreach ($resources as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? $resource['legacy_language_resource_pid'] ?? 0);
        $keyName = trim((string) ($resource['key_name'] ?? ''));
        if ($legacyResourcePid <= 0 || $keyName === '') {
            continue;
        }

        $resourceKey = trim((string) ($resource['resource_key'] ?? ''));
        if ($resourceKey !== '') {
            $existingResourceKeys[(string) $legacyResourcePid] = $resourceKey;
        }

        $normalizedResources[] = [
            'legacy_resource_pid' => $legacyResourcePid,
            'project_pid' => (int) ($resource['project_pid'] ?? 0),
            'legacy_group_pid' => (int) ($resource['legacy_group_pid'] ?? $resource['legacy_language_resource_group_pid'] ?? 0),
            'resource_key' => $resourceKey,
            'key_for_update' => (string) ($resource['key_for_update'] ?? ''),
            'sort_group' => (string) ($resource['sort_group'] ?? ''),
            'key_name' => $keyName,
            'key_name_for_xcode' => (string) ($resource['key_name_for_xcode'] ?? ''),
            'uwp_target_property' => (string) ($resource['uwp_target_property'] ?? ''),
            'is_resource_fixed' => (int) ($resource['is_resource_fixed'] ?? 0),
            'use_default_if_caption_is_blank' => (int) ($resource['use_default_if_caption_is_blank'] ?? 1),
        ];
    }

    $normalizedGroups = [];
    foreach ($groups as $group) {
        if (!is_array($group)) {
            continue;
        }

        $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? $group['legacy_language_resource_group_pid'] ?? 0);
        $name = trim((string) ($group['name'] ?? ''));
        if ($legacyGroupPid <= 0 || $name === '') {
            continue;
        }

        $normalizedGroups[] = [
            'legacy_group_pid' => $legacyGroupPid,
            'project_pid' => (int) ($group['project_pid'] ?? 0),
            'name' => $name,
            'function_name_prefix' => (string) ($group['function_name_prefix'] ?? ''),
            'function_name_suffix' => (string) ($group['function_name_suffix'] ?? ''),
            'filename_suffix_for_php' => (string) ($group['filename_suffix_for_php'] ?? ''),
            'filename_suffix' => (string) ($group['filename_suffix'] ?? ''),
            'filename_for_xcode' => (string) ($group['filename_for_xcode'] ?? ''),
            'last_modified_dt' => (string) ($group['last_modified_dt'] ?? ''),
        ];
    }

    $normalizedGroupLanguages = [];
    foreach ($groupLanguages as $groupLanguage) {
        if (!is_array($groupLanguage)) {
            continue;
        }

        $legacyGroupLanguagePid = (int) ($groupLanguage['legacy_group_language_pid'] ?? $groupLanguage['legacy_pid'] ?? 0);
        $legacyGroupPid = (int) ($groupLanguage['legacy_group_pid'] ?? $groupLanguage['legacy_language_resource_group_pid'] ?? 0);
        $legacyLanguagePid = (int) ($groupLanguage['legacy_language_pid'] ?? $groupLanguage['legacy_language_resource_lang_pid'] ?? 0);
        if ($legacyGroupLanguagePid <= 0 || $legacyGroupPid <= 0 || $legacyLanguagePid <= 0) {
            continue;
        }

        $normalizedGroupLanguages[] = [
            'legacy_group_language_pid' => $legacyGroupLanguagePid,
            'project_pid' => (int) ($groupLanguage['project_pid'] ?? 0),
            'legacy_group_pid' => $legacyGroupPid,
            'legacy_language_pid' => $legacyLanguagePid,
        ];
    }

    $normalizedGroupSourceOutputs = [];
    foreach ($groupSourceOutputs as $groupSourceOutput) {
        if (!is_array($groupSourceOutput)) {
            continue;
        }

        $legacyGroupSourceOutputPid = (int) ($groupSourceOutput['legacy_group_source_output_pid'] ?? $groupSourceOutput['legacy_pid'] ?? 0);
        $legacyGroupPid = (int) ($groupSourceOutput['legacy_group_pid'] ?? $groupSourceOutput['legacy_language_resource_group_pid'] ?? 0);
        $legacyProjectSourceOutputPid = (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0);
        if ($legacyGroupSourceOutputPid <= 0 || $legacyGroupPid <= 0 || $legacyProjectSourceOutputPid <= 0) {
            continue;
        }

        $normalizedGroupSourceOutputs[] = [
            'legacy_group_source_output_pid' => $legacyGroupSourceOutputPid,
            'project_pid' => (int) ($groupSourceOutput['project_pid'] ?? 0),
            'legacy_group_pid' => $legacyGroupPid,
            'legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
        ];
    }

    $normalizedAdditionalGroupAssignments = [];
    foreach ($additionalGroupAssignments as $assignment) {
        if (!is_array($assignment)) {
            continue;
        }

        $legacyAssignmentPid = (int) ($assignment['legacy_assignment_pid'] ?? $assignment['legacy_pid'] ?? 0);
        $legacyResourcePid = (int) ($assignment['legacy_resource_pid'] ?? $assignment['legacy_language_resource_pid'] ?? 0);
        $legacyGroupPid = (int) ($assignment['legacy_group_pid'] ?? $assignment['legacy_language_resource_group_pid'] ?? 0);
        if ($legacyAssignmentPid <= 0 || $legacyResourcePid <= 0 || $legacyGroupPid <= 0) {
            continue;
        }

        $normalizedAdditionalGroupAssignments[] = [
            'legacy_assignment_pid' => $legacyAssignmentPid,
            'project_pid' => (int) ($assignment['project_pid'] ?? 0),
            'legacy_resource_pid' => $legacyResourcePid,
            'legacy_group_pid' => $legacyGroupPid,
        ];
    }

    $normalizedCaptions = [];
    foreach ($captions as $caption) {
        if (!is_array($caption)) {
            continue;
        }

        $legacyCaptionPid = (int) ($caption['legacy_caption_pid'] ?? $caption['legacy_pid'] ?? 0);
        $legacyResourcePid = (int) ($caption['legacy_resource_pid'] ?? $caption['legacy_language_resource_pid'] ?? 0);
        $legacyGroupPid = (int) ($caption['legacy_group_pid'] ?? $caption['legacy_language_resource_group_pid'] ?? 0);
        $legacyLanguagePid = (int) ($caption['legacy_language_pid'] ?? $caption['legacy_language_resource_lang_pid'] ?? 0);
        if ($legacyCaptionPid <= 0 || $legacyResourcePid <= 0 || $legacyGroupPid <= 0 || $legacyLanguagePid <= 0) {
            continue;
        }

        $normalizedCaptions[] = [
            'legacy_caption_pid' => $legacyCaptionPid,
            'project_pid' => (int) ($caption['project_pid'] ?? 0),
            'legacy_resource_pid' => $legacyResourcePid,
            'legacy_group_pid' => $legacyGroupPid,
            'legacy_language_pid' => $legacyLanguagePid,
            'caption' => (string) ($caption['caption'] ?? ''),
            'caption_auto_translated' => (string) ($caption['caption_auto_translated'] ?? ''),
        ];
    }

    $normalizedLanguages = [];
    foreach ($languages as $language) {
        if (!is_array($language)) {
            continue;
        }

        $legacyLanguagePid = (int) ($language['legacy_language_pid'] ?? $language['legacy_pid'] ?? 0);
        $caption = trim((string) ($language['caption'] ?? ''));
        if ($legacyLanguagePid <= 0 || $caption === '') {
            continue;
        }

        $normalizedLanguages[] = [
            'legacy_language_pid' => $legacyLanguagePid,
            'filename_suffix' => (string) ($language['filename_suffix'] ?? ''),
            'template_key' => (string) ($language['template_key'] ?? ''),
            'is_default' => (int) ($language['is_default'] ?? 0),
            'caption' => $caption,
            'lang_for_cs' => (string) ($language['lang_for_cs'] ?? ''),
            'lang_for_android' => (string) ($language['lang_for_android'] ?? ''),
            'lang_for_ios' => (string) ($language['lang_for_ios'] ?? ''),
            'lang_for_google' => (string) ($language['lang_for_google'] ?? ''),
        ];
    }

    $normalizedResources = app_legacy_language_resource_reference_assign_resource_keys(
        $normalizedResources,
        $existingResourceKeys,
    );

    return [
        'ok' => true,
        'item' => [
            'project_key' => (string) ($decoded['project_key'] ?? strtoupper(trim($projectKey))),
            'project_pid' => (int) ($decoded['project_pid'] ?? 0),
            'source_dump_path' => (string) ($decoded['source_dump_path'] ?? ''),
            'generated_at' => (string) ($decoded['generated_at'] ?? ''),
            'resource_count' => (int) ($decoded['resource_count'] ?? count($normalizedResources)),
            'group_count' => (int) ($decoded['group_count'] ?? count($normalizedGroups)),
            'group_language_count' => (int) ($decoded['group_language_count'] ?? count($normalizedGroupLanguages)),
            'group_source_output_count' => (int) ($decoded['group_source_output_count'] ?? count($normalizedGroupSourceOutputs)),
            'additional_group_assignment_count' => (int) ($decoded['additional_group_assignment_count'] ?? count($normalizedAdditionalGroupAssignments)),
            'caption_count' => (int) ($decoded['caption_count'] ?? count($normalizedCaptions)),
            'language_count' => (int) ($decoded['language_count'] ?? count($normalizedLanguages)),
            'resources' => $normalizedResources,
            'groups' => $normalizedGroups,
            'group_languages' => $normalizedGroupLanguages,
            'group_source_outputs' => $normalizedGroupSourceOutputs,
            'additional_group_assignments' => $normalizedAdditionalGroupAssignments,
            'captions' => $normalizedCaptions,
            'languages' => $normalizedLanguages,
        ],
        'error' => '',
    ];
}

/**
 * @return array<string,string>
 */
function app_legacy_language_resource_reference_map(): array
{
    return [
        'MTOOL' => dirname(__DIR__) . '/reference/mtool-legacy-language-resource-catalog.json',
    ];
}

/**
 * @return list<string>
 */
function app_legacy_language_resource_reference_project_keys(): array
{
    $projectKeys = array_keys(app_legacy_language_resource_reference_map());
    sort($projectKeys, SORT_NATURAL);

    return array_values(array_filter(
        $projectKeys,
        static function ($projectKey): bool {
            return is_string($projectKey) && $projectKey !== '';
        },
    ));
}

function app_legacy_language_resource_reference_path(string $projectKey): string
{
    $normalizedProjectKey = strtoupper(trim($projectKey));
    $referenceMap = app_legacy_language_resource_reference_map();

    if (array_key_exists($normalizedProjectKey, $referenceMap)) {
        return $referenceMap[$normalizedProjectKey];
    }

    return '';
}

function app_legacy_language_resource_reference_resource_key_candidate(
    string $keyName,
    int $legacyResourcePid,
): string {
    $candidate = trim($keyName);
    $candidate = preg_replace('/[^A-Za-z0-9_.:-]+/u', '-', $candidate) ?? '';
    $candidate = preg_replace('/-{2,}/', '-', $candidate) ?? '';
    $candidate = trim($candidate, '-');

    if ($candidate === '') {
        $candidate = 'LANGRES-' . $legacyResourcePid;
    }

    return $candidate;
}

/**
 * @param array<string,bool> $usedKeys
 */
function app_legacy_language_resource_reference_unique_resource_key(
    string $baseKey,
    int $legacyResourcePid,
    array $usedKeys,
): string {
    $candidate = trim($baseKey);
    if ($candidate === '') {
        $candidate = 'LANGRES-' . $legacyResourcePid;
    }

    if (!array_key_exists($candidate, $usedKeys)) {
        return $candidate;
    }

    $suffixCandidate = $candidate . '-' . $legacyResourcePid;
    if (!array_key_exists($suffixCandidate, $usedKeys)) {
        return $suffixCandidate;
    }

    $counter = 2;
    while (array_key_exists($suffixCandidate . '-' . $counter, $usedKeys)) {
        $counter++;
    }

    return $suffixCandidate . '-' . $counter;
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
 * @param array<string,string> $existingKeyMap
 * @return list<array{
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
 * }>
 */
function app_legacy_language_resource_reference_assign_resource_keys(
    array $resources,
    array $existingKeyMap = [],
): array {
    usort(
        $resources,
        static function (array $left, array $right): int {
            if ($left['legacy_group_pid'] !== $right['legacy_group_pid']) {
                return $left['legacy_group_pid'] <=> $right['legacy_group_pid'];
            }
            if ($left['sort_group'] !== $right['sort_group']) {
                return strcmp($left['sort_group'], $right['sort_group']);
            }
            if ($left['key_name'] !== $right['key_name']) {
                return strcmp($left['key_name'], $right['key_name']);
            }

            return $left['legacy_resource_pid'] <=> $right['legacy_resource_pid'];
        },
    );

    $usedKeys = [];
    $normalized = [];
    foreach ($resources as $resource) {
        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        if ($legacyResourcePid <= 0) {
            continue;
        }

        $resourceKey = trim((string) ($existingKeyMap[(string) $legacyResourcePid] ?? ($resource['resource_key'] ?? '')));
        if ($resourceKey === '') {
            $resourceKey = app_legacy_language_resource_reference_resource_key_candidate(
                (string) ($resource['key_name'] ?? ''),
                $legacyResourcePid,
            );
        }
        $resourceKey = app_legacy_language_resource_reference_unique_resource_key(
            $resourceKey,
            $legacyResourcePid,
            $usedKeys,
        );
        $usedKeys[$resourceKey] = true;
        $resource['resource_key'] = $resourceKey;
        $normalized[] = $resource;
    }

    return $normalized;
}
