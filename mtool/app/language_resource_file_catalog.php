<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';
require_once __DIR__ . '/legacy_source_output_registry.php';
require_once __DIR__ . '/legacy_language_resource_reference.php';
require_once __DIR__ . '/sample_pack_catalog.php';

function app_language_resource_file_catalog_schema_version(): int
{
    return 1;
}

/**
 * @return array<string,string>
 */
function app_language_resource_file_catalog_sample_pack_name_map(): array
{
    return [];
}

/**
 * @return array<string,string>
 */
function app_language_resource_file_catalog_known_root_map(): array
{
    $map = [
        'MTOOL' => dirname(__DIR__) . '/resources',
    ];

    foreach (app_language_resource_file_catalog_sample_pack_name_map() as $projectKey => $packName) {
        $map[$projectKey] = app_sample_pack_resource_root($packName);
    }

    return $map;
}

/**
 * @return list<array{
 *     project_key:string,
 *     root_path:string
 * }>
 */
function app_language_resource_file_catalog_known_projects(): array
{
    $projects = [];

    foreach (app_legacy_language_resource_reference_project_keys() as $projectKey) {
        $projects[] = [
            'project_key' => $projectKey,
            'root_path' => app_language_resource_file_catalog_default_root($projectKey),
        ];
    }

    return $projects;
}

function app_language_resource_file_catalog_default_root(string $projectKey): string
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $knownRootMap = app_language_resource_file_catalog_known_root_map();

    if (array_key_exists($normalizedProjectKey, $knownRootMap)) {
        return $knownRootMap[$normalizedProjectKey];
    }

    return dirname(__DIR__) . '/resources/' . $normalizedProjectKey;
}

function app_language_resource_file_catalog_default_overlay_seed_path(string $projectKey): string
{
    $normalizedProjectKey = app_normalize_project_key($projectKey);
    $seedMap = [];

    return $seedMap[$normalizedProjectKey] ?? '';
}

function app_language_resource_file_catalog_extract_legacy_source_output_pid(string $notes): int
{
    if (
        preg_match(
            '/\bProjectSourceOutput(?:\.PID|\s+PID)?\s*(?:=\s*)?(\d+)/u',
            $notes,
            $matches,
        ) !== 1
    ) {
        return 0;
    }

    return (int) ($matches[1] ?? 0);
}

/**
 * @param array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }> $items
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }>
 */
function app_language_resource_file_catalog_sort_source_output_map(array $items): array
{
    uasort(
        $items,
        static function (array $left, array $right): int {
            $leftOrder = (int) ($left['source_output_list_order'] ?? 0);
            $rightOrder = (int) ($right['source_output_list_order'] ?? 0);
            if ($leftOrder !== $rightOrder) {
                return $leftOrder <=> $rightOrder;
            }

            $leftKey = (string) ($left['source_output_key'] ?? '');
            $rightKey = (string) ($right['source_output_key'] ?? '');

            return strcmp($leftKey, $rightKey);
        },
    );

    return $items;
}

/**
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }>
 */
function app_language_resource_file_catalog_source_output_map_from_select_overlay_seed(string $contents): array
{
    $pattern = "/SELECT\\s+'(?<source_output_key>[^']+)'(?:\\s+AS\\s+source_output_key)?,"
        . "\\s+'(?<name>[^']+)'(?:\\s+AS\\s+name)?,"
        . "\\s+'(?<program_language>[^']+)'(?:\\s+AS\\s+program_language)?,"
        . "\\s+'(?<source_template_dir>[^']+)'(?:\\s+AS\\s+source_template_dir)?,"
        . "\\s+(?<source_output_list_order>\\d+)(?:\\s+AS\\s+source_output_list_order)?,"
        . "\\s+'(?<notes>[^']*)'(?:\\s+AS\\s+notes)?/s";
    if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER) === false) {
        return [];
    }

    $items = [];
    foreach ($matches as $match) {
        if (!is_array($match)) {
            continue;
        }

        $notes = (string) ($match['notes'] ?? '');
        $legacyProjectSourceOutputPid = app_language_resource_file_catalog_extract_legacy_source_output_pid($notes);
        if ($legacyProjectSourceOutputPid <= 0) {
            continue;
        }

        $items[(string) $legacyProjectSourceOutputPid] = [
            'legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
            'source_output_key' => trim((string) ($match['source_output_key'] ?? '')),
            'name' => trim((string) ($match['name'] ?? '')),
            'program_language' => trim((string) ($match['program_language'] ?? '')),
            'source_template_dir' => trim((string) ($match['source_template_dir'] ?? '')),
            'source_output_list_order' => (int) ($match['source_output_list_order'] ?? 0),
            'notes' => $notes,
        ];
    }

    return $items;
}

function app_language_resource_file_catalog_sql_unquote_string(string $token): string
{
    $normalized = trim($token);
    if ($normalized === '' || strtoupper($normalized) === 'NULL') {
        return '';
    }

    $length = strlen($normalized);
    if ($length >= 2 && $normalized[0] === "'" && $normalized[$length - 1] === "'") {
        $body = substr($normalized, 1, -1);
        $body = str_replace("''", "'", $body);
        $body = str_replace(
            ["\\\\", "\\'", "\\n", "\\r", "\\t", "\\0", "\\Z"],
            ["\\", "'", "\n", "\r", "\t", "\0", "\x1a"],
            $body,
        );

        return $body;
    }

    return $normalized;
}

function app_language_resource_file_catalog_sql_int_value(string $token): int
{
    $normalized = trim($token);
    if ($normalized === '' || preg_match('/^-?\d+$/', $normalized) !== 1) {
        return 0;
    }

    return (int) $normalized;
}

/**
 * @return list<list<string>>
 */
function app_language_resource_file_catalog_parse_sql_value_tuples(string $valuesClause): array
{
    $rows = [];
    $buffer = '';
    $depth = 0;
    $inString = false;
    $length = strlen($valuesClause);
    for ($index = 0; $index < $length; $index++) {
        $char = $valuesClause[$index];
        if ($inString) {
            $buffer .= $char;
            if ($char === '\\' && ($index + 1) < $length) {
                $index++;
                $buffer .= $valuesClause[$index];
                continue;
            }
            if ($char === "'") {
                if (($index + 1) < $length && $valuesClause[$index + 1] === "'") {
                    $index++;
                    $buffer .= $valuesClause[$index];
                    continue;
                }

                $inString = false;
            }
            continue;
        }

        if ($char === "'") {
            $inString = true;
            $buffer .= $char;
            continue;
        }

        if ($char === '(') {
            if ($depth > 0) {
                $buffer .= $char;
            }
            $depth++;
            continue;
        }

        if ($char === ')') {
            if ($depth <= 0) {
                continue;
            }

            $depth--;
            if ($depth === 0) {
                $rows[] = app_language_resource_file_catalog_split_sql_tuple_values($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $char;
            continue;
        }

        if ($depth > 0) {
            $buffer .= $char;
        }
    }

    return $rows;
}

/**
 * @return list<string>
 */
function app_language_resource_file_catalog_split_sql_tuple_values(string $tupleBody): array
{
    $values = [];
    $buffer = '';
    $depth = 0;
    $inString = false;
    $length = strlen($tupleBody);
    for ($index = 0; $index < $length; $index++) {
        $char = $tupleBody[$index];
        if ($inString) {
            $buffer .= $char;
            if ($char === '\\' && ($index + 1) < $length) {
                $index++;
                $buffer .= $tupleBody[$index];
                continue;
            }
            if ($char === "'") {
                if (($index + 1) < $length && $tupleBody[$index + 1] === "'") {
                    $index++;
                    $buffer .= $tupleBody[$index];
                    continue;
                }

                $inString = false;
            }
            continue;
        }

        if ($char === "'") {
            $inString = true;
            $buffer .= $char;
            continue;
        }

        if ($char === '(') {
            $depth++;
            $buffer .= $char;
            continue;
        }

        if ($char === ')' && $depth > 0) {
            $depth--;
            $buffer .= $char;
            continue;
        }

        if ($char === ',' && $depth === 0) {
            $values[] = trim($buffer);
            $buffer = '';
            continue;
        }

        $buffer .= $char;
    }

    $values[] = trim($buffer);

    return $values;
}

/**
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }>
 */
function app_language_resource_file_catalog_source_output_map_from_values_overlay_seed(string $contents): array
{
    $pattern = '/INSERT(?:\s+IGNORE)?\s+INTO\s+project_source_outputs\s*'
        . '\((?<columns>.*?)\)\s*VALUES\s*(?<values>.*?)(?=ON\s+DUPLICATE\s+KEY\s+UPDATE\b|;)/is';
    if (preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER) === false) {
        return [];
    }

    $items = [];
    foreach ($matches as $match) {
        if (!is_array($match)) {
            continue;
        }

        $columns = preg_split('/\s*,\s*/', trim((string) ($match['columns'] ?? '')));
        if (!is_array($columns) || $columns === []) {
            continue;
        }

        $normalizedColumns = [];
        foreach ($columns as $column) {
            $normalizedColumns[] = trim((string) $column, " \t\n\r\0\x0B`");
        }
        $columnIndex = array_flip($normalizedColumns);
        $requiredColumns = [
            'source_output_key',
            'name',
            'program_language',
            'source_template_dir',
            'source_output_list_order',
            'notes',
        ];
        $missingRequiredColumn = false;
        foreach ($requiredColumns as $requiredColumn) {
            if (!array_key_exists($requiredColumn, $columnIndex)) {
                $missingRequiredColumn = true;
                break;
            }
        }
        if ($missingRequiredColumn) {
            continue;
        }

        $rows = app_language_resource_file_catalog_parse_sql_value_tuples((string) ($match['values'] ?? ''));
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $notes = app_language_resource_file_catalog_sql_unquote_string(
                (string) ($row[(int) $columnIndex['notes']] ?? ''),
            );
            $legacyProjectSourceOutputPid = app_language_resource_file_catalog_extract_legacy_source_output_pid($notes);
            if ($legacyProjectSourceOutputPid <= 0) {
                continue;
            }

            $items[(string) $legacyProjectSourceOutputPid] = [
                'legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
                'source_output_key' => app_language_resource_file_catalog_sql_unquote_string(
                    (string) ($row[(int) $columnIndex['source_output_key']] ?? ''),
                ),
                'name' => app_language_resource_file_catalog_sql_unquote_string(
                    (string) ($row[(int) $columnIndex['name']] ?? ''),
                ),
                'program_language' => app_language_resource_file_catalog_sql_unquote_string(
                    (string) ($row[(int) $columnIndex['program_language']] ?? ''),
                ),
                'source_template_dir' => app_language_resource_file_catalog_sql_unquote_string(
                    (string) ($row[(int) $columnIndex['source_template_dir']] ?? ''),
                ),
                'source_output_list_order' => app_language_resource_file_catalog_sql_int_value(
                    (string) ($row[(int) $columnIndex['source_output_list_order']] ?? ''),
                ),
                'notes' => $notes,
            ];
        }
    }

    return $items;
}

/**
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }>
 */
function app_language_resource_file_catalog_source_output_map_from_overlay_seed(string $seedPath): array
{
    $normalizedPath = trim($seedPath);
    if ($normalizedPath === '' || !is_file($normalizedPath)) {
        return [];
    }

    $contents = file_get_contents($normalizedPath);
    if (!is_string($contents) || $contents === '') {
        return [];
    }

    $items = app_language_resource_file_catalog_source_output_map_from_select_overlay_seed($contents);
    foreach (app_language_resource_file_catalog_source_output_map_from_values_overlay_seed($contents) as $legacyPid => $item) {
        $items[(string) $legacyPid] = $item;
    }

    return app_language_resource_file_catalog_sort_source_output_map($items);
}

/**
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }>
 */
function app_language_resource_file_catalog_source_output_map_for_project(
    string $projectKey,
    string $seedPath,
): array {
    $items = app_legacy_project_language_resource_source_output_binding_map($projectKey);

    foreach (app_language_resource_file_catalog_source_output_map_from_overlay_seed($seedPath) as $legacyPid => $item) {
        $normalizedLegacyPid = (string) $legacyPid;
        if (is_array($items[$normalizedLegacyPid] ?? null)) {
            $items[$normalizedLegacyPid] = array_merge($items[$normalizedLegacyPid], $item);
            continue;
        }

        $items[$normalizedLegacyPid] = $item;
    }

    return app_language_resource_file_catalog_sort_source_output_map($items);
}

function app_language_resource_file_catalog_group_slug(string $name): string
{
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = preg_replace('/-{2,}/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'group';
}

function app_language_resource_file_catalog_group_key(int $legacyGroupPid, string $name): string
{
    return sprintf(
        'grp-%03d-%s',
        max(0, $legacyGroupPid),
        app_language_resource_file_catalog_group_slug($name),
    );
}

function app_language_resource_file_catalog_normalize_locale_key(string $candidate): string
{
    $normalized = trim($candidate);
    $normalized = preg_replace('/[\s_]+/', '-', $normalized) ?? '';
    $normalized = preg_replace('/[^A-Za-z0-9-]+/', '-', $normalized) ?? '';
    $normalized = preg_replace('/-{2,}/', '-', $normalized) ?? '';
    $normalized = trim($normalized, '-');

    return $normalized;
}

/**
 * @param array{
 *     legacy_language_pid?:int,
 *     filename_suffix?:string,
 *     template_key?:string,
 *     is_default?:int,
 *     caption?:string,
 *     lang_for_cs?:string,
 *     lang_for_android?:string,
 *     lang_for_ios?:string,
 *     lang_for_google?:string
 * } $language
 */
function app_language_resource_file_catalog_locale_key(array $language): string
{
    $legacyLanguagePid = (int) ($language['legacy_language_pid'] ?? 0);
    $candidates = [
        (string) ($language['lang_for_google'] ?? ''),
        (string) ($language['filename_suffix'] ?? ''),
        (string) ($language['template_key'] ?? ''),
        'legacy-lang-' . $legacyLanguagePid,
    ];
    foreach ($candidates as $candidate) {
        $normalized = app_language_resource_file_catalog_normalize_locale_key($candidate);
        if ($normalized !== '') {
            return $normalized;
        }
    }

    return 'legacy-lang-' . max(1, $legacyLanguagePid);
}

/**
 * @param array<string,bool> $usedKeys
 */
function app_language_resource_file_catalog_unique_locale_key(
    string $baseKey,
    int $legacyLanguagePid,
    array $usedKeys,
): string {
    $candidate = trim($baseKey);
    if ($candidate === '') {
        $candidate = 'legacy-lang-' . $legacyLanguagePid;
    }

    if (!array_key_exists($candidate, $usedKeys)) {
        return $candidate;
    }

    $suffixCandidate = $candidate . '-' . $legacyLanguagePid;
    if (!array_key_exists($suffixCandidate, $usedKeys)) {
        return $suffixCandidate;
    }

    $counter = 2;
    while (array_key_exists($suffixCandidate . '-' . $counter, $usedKeys)) {
        $counter++;
    }

    return $suffixCandidate . '-' . $counter;
}

function app_language_resource_file_catalog_json_encode(array $payload): string
{
    $encoded = json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
    );
    if (!is_string($encoded)) {
        throw new RuntimeException('language resource file catalog JSON の生成に失敗しました。');
    }

    return $encoded . PHP_EOL;
}

function app_language_resource_file_catalog_ensure_directory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('directory を作成できません: ' . $path);
    }
}

function app_language_resource_file_catalog_remove_tree(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        if (!unlink($path)) {
            throw new RuntimeException('file を削除できません: ' . $path);
        }

        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iterator as $entry) {
        $pathname = $entry->getPathname();
        if ($entry->isDir()) {
            if (!rmdir($pathname)) {
                throw new RuntimeException('directory を削除できません: ' . $pathname);
            }
            continue;
        }

        if (!unlink($pathname)) {
            throw new RuntimeException('file を削除できません: ' . $pathname);
        }
    }

    if (!rmdir($path)) {
        throw new RuntimeException('directory を削除できません: ' . $path);
    }
}

function app_language_resource_file_catalog_clean_generated_tree(string $rootPath): void
{
    if (!file_exists($rootPath)) {
        return;
    }

    if (is_file($rootPath) || is_link($rootPath)) {
        app_language_resource_file_catalog_remove_tree($rootPath);

        return;
    }

    foreach (['manifest.json', 'groups'] as $generatedPath) {
        $targetPath = rtrim($rootPath, '/') . '/' . $generatedPath;
        if (!file_exists($targetPath)) {
            continue;
        }

        app_language_resource_file_catalog_remove_tree($targetPath);
    }
}

/**
 * @param array{
 *     manifest?:array<string,mixed>,
 *     groups?:list<array{
 *         group_key?:string,
 *         group?:array<string,mixed>,
 *         resources?:list<array{
 *             resource_key?:string,
 *             item?:array<string,mixed>
 *         }>
 *     }>
 * } $tree
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
function app_language_resource_file_catalog_actual_counts(array $tree): array
{
    $manifest = is_array($tree['manifest'] ?? null) ? $tree['manifest'] : [];
    $groups = is_array($tree['groups'] ?? null) ? $tree['groups'] : [];
    $locales = is_array($manifest['locales'] ?? null) ? $manifest['locales'] : [];
    $localeOrder = is_array($manifest['locale_order'] ?? null) ? $manifest['locale_order'] : [];

    $knownLocaleKeys = [];
    foreach ($localeOrder as $localeKey) {
        $normalizedLocaleKey = trim((string) $localeKey);
        if ($normalizedLocaleKey === '' || !is_array($locales[$normalizedLocaleKey] ?? null)) {
            continue;
        }

        $knownLocaleKeys[$normalizedLocaleKey] = true;
    }
    if ($knownLocaleKeys === []) {
        foreach ($locales as $localeKey => $locale) {
            $normalizedLocaleKey = trim((string) $localeKey);
            if ($normalizedLocaleKey === '' || !is_array($locale)) {
                continue;
            }

            $knownLocaleKeys[$normalizedLocaleKey] = true;
        }
    }

    $counts = [
        'resources' => 0,
        'groups' => 0,
        'languages' => count($knownLocaleKeys),
        'group_languages' => 0,
        'group_source_outputs' => 0,
        'additional_group_assignments' => 0,
        'captions' => 0,
    ];

    foreach ($groups as $groupEntry) {
        if (!is_array($groupEntry)) {
            continue;
        }

        $group = is_array($groupEntry['group'] ?? null) ? $groupEntry['group'] : [];
        $groupKey = trim((string) ($group['group_key'] ?? $groupEntry['group_key'] ?? ''));
        if ($groupKey === '') {
            continue;
        }

        $counts['groups']++;

        $languageBindings = is_array($group['language_bindings'] ?? null) ? $group['language_bindings'] : [];
        if ($languageBindings !== []) {
            foreach ($languageBindings as $binding) {
                if (!is_array($binding)) {
                    continue;
                }

                $counts['group_languages']++;
            }
        } else {
            foreach ((is_array($group['locales'] ?? null) ? $group['locales'] : []) as $localeKey) {
                if (trim((string) $localeKey) === '') {
                    continue;
                }

                $counts['group_languages']++;
            }
        }

        foreach ((is_array($group['source_outputs'] ?? null) ? $group['source_outputs'] : []) as $sourceOutput) {
            if (!is_array($sourceOutput)) {
                continue;
            }

            $counts['group_source_outputs']++;
        }

        foreach ((is_array($groupEntry['resources'] ?? null) ? $groupEntry['resources'] : []) as $resourceEntry) {
            if (!is_array($resourceEntry)) {
                continue;
            }

            $resource = is_array($resourceEntry['item'] ?? null) ? $resourceEntry['item'] : [];
            $resourceKey = trim((string) ($resource['resource_key'] ?? $resourceEntry['resource_key'] ?? ''));
            if ($resourceKey === '') {
                continue;
            }

            $counts['resources']++;

            foreach ((is_array($resource['captions'] ?? null) ? $resource['captions'] : []) as $caption) {
                if (!is_array($caption)) {
                    continue;
                }

                $counts['captions']++;
            }

            $additionalGroups = is_array($resource['additional_groups'] ?? null)
                ? $resource['additional_groups']
                : [];
            if ($additionalGroups !== []) {
                foreach ($additionalGroups as $additionalGroup) {
                    if (!is_array($additionalGroup)) {
                        continue;
                    }

                    $additionalGroupKey = trim((string) ($additionalGroup['group_key'] ?? ''));
                    if ($additionalGroupKey === '') {
                        continue;
                    }

                    $counts['additional_group_assignments']++;
                }
                continue;
            }

            foreach ((is_array($resource['additional_group_keys'] ?? null) ? $resource['additional_group_keys'] : []) as $additionalGroupKey) {
                if (trim((string) $additionalGroupKey) === '') {
                    continue;
                }

                $counts['additional_group_assignments']++;
            }
        }
    }

    return $counts;
}

/**
 * @param array{
 *     resources?:list<mixed>,
 *     groups?:list<mixed>,
 *     group_languages?:list<mixed>,
 *     group_source_outputs?:list<mixed>,
 *     additional_group_assignments?:list<mixed>,
 *     captions?:list<mixed>,
 *     languages?:list<mixed>
 * } $reference
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
function app_language_resource_file_catalog_reference_counts(array $reference): array
{
    return [
        'resources' => count(is_array($reference['resources'] ?? null) ? $reference['resources'] : []),
        'groups' => count(is_array($reference['groups'] ?? null) ? $reference['groups'] : []),
        'languages' => count(is_array($reference['languages'] ?? null) ? $reference['languages'] : []),
        'group_languages' => count(is_array($reference['group_languages'] ?? null) ? $reference['group_languages'] : []),
        'group_source_outputs' => count(is_array($reference['group_source_outputs'] ?? null) ? $reference['group_source_outputs'] : []),
        'additional_group_assignments' => count(
            is_array($reference['additional_group_assignments'] ?? null)
                ? $reference['additional_group_assignments']
                : [],
        ),
        'captions' => count(is_array($reference['captions'] ?? null) ? $reference['captions'] : []),
    ];
}

/**
 * @param array{
 *     groups?:list<array<string,mixed>>,
 *     resources?:list<array<string,mixed>>,
 *     languages?:list<array<string,mixed>>,
 *     group_source_outputs?:list<array<string,mixed>>,
 *     captions?:list<array<string,mixed>>
 * } $reference
 * @return array{
 *     dropped_counts:array{
 *         group_source_outputs:int,
 *         captions:int
 *     },
 *     dropped_rows:array{
 *         group_source_outputs:list<array<string,mixed>>,
 *         captions:list<array<string,mixed>>
 *     }
 * }
 */
function app_language_resource_file_catalog_reference_normalization(array $reference): array
{
    $knownGroupPids = [];
    foreach ((is_array($reference['groups'] ?? null) ? $reference['groups'] : []) as $group) {
        if (!is_array($group)) {
            continue;
        }

        $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0);
        if ($legacyGroupPid <= 0) {
            continue;
        }

        $knownGroupPids[(string) $legacyGroupPid] = true;
    }

    $knownResourcePids = [];
    foreach ((is_array($reference['resources'] ?? null) ? $reference['resources'] : []) as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        if ($legacyResourcePid <= 0) {
            continue;
        }

        $knownResourcePids[(string) $legacyResourcePid] = true;
    }

    $knownLanguagePids = [];
    foreach ((is_array($reference['languages'] ?? null) ? $reference['languages'] : []) as $language) {
        if (!is_array($language)) {
            continue;
        }

        $legacyLanguagePid = (int) ($language['legacy_language_pid'] ?? 0);
        if ($legacyLanguagePid <= 0) {
            continue;
        }

        $knownLanguagePids[(string) $legacyLanguagePid] = true;
    }

    $droppedGroupSourceOutputs = [];
    foreach ((is_array($reference['group_source_outputs'] ?? null) ? $reference['group_source_outputs'] : []) as $groupSourceOutput) {
        if (!is_array($groupSourceOutput)) {
            continue;
        }

        $reason = [];
        $legacyGroupPid = (int) ($groupSourceOutput['legacy_group_pid'] ?? 0);
        $legacyProjectSourceOutputPid = (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0);

        if ($legacyGroupPid <= 0 || !array_key_exists((string) $legacyGroupPid, $knownGroupPids)) {
            $reason[] = 'missing_group';
        }
        if ($legacyProjectSourceOutputPid <= 0) {
            $reason[] = 'missing_project_source_output_pid';
        }

        if ($reason === []) {
            continue;
        }

        $droppedGroupSourceOutputs[] = [
            'reason' => $reason,
            'row' => $groupSourceOutput,
        ];
    }

    $droppedCaptions = [];
    foreach ((is_array($reference['captions'] ?? null) ? $reference['captions'] : []) as $caption) {
        if (!is_array($caption)) {
            continue;
        }

        $reason = [];
        $legacyResourcePid = (int) ($caption['legacy_resource_pid'] ?? 0);
        $legacyLanguagePid = (int) ($caption['legacy_language_pid'] ?? 0);

        if ($legacyResourcePid <= 0 || !array_key_exists((string) $legacyResourcePid, $knownResourcePids)) {
            $reason[] = 'missing_resource';
        }
        if ($legacyLanguagePid <= 0 || !array_key_exists((string) $legacyLanguagePid, $knownLanguagePids)) {
            $reason[] = 'missing_language';
        }

        if ($reason === []) {
            continue;
        }

        $droppedCaptions[] = [
            'reason' => $reason,
            'row' => $caption,
        ];
    }

    return [
        'dropped_counts' => [
            'group_source_outputs' => count($droppedGroupSourceOutputs),
            'captions' => count($droppedCaptions),
        ],
        'dropped_rows' => [
            'group_source_outputs' => $droppedGroupSourceOutputs,
            'captions' => $droppedCaptions,
        ],
    ];
}

/**
 * @param array{
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
 * } $reference
 * @param array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     name:string,
 *     program_language:string,
 *     source_template_dir:string,
 *     source_output_list_order:int,
 *     notes:string
 * }> $sourceOutputMap
 * @return array{
 *     manifest:array<string,mixed>,
 *     groups:list<array{
 *         group_key:string,
 *         group:array<string,mixed>,
 *         resources:list<array{
 *             resource_key:string,
 *             item:array<string,mixed>
 *         }>
 *     }>
 * }
 */
function app_language_resource_file_catalog_build_from_reference(
    array $reference,
    array $sourceOutputMap = [],
): array {
    $languages = is_array($reference['languages'] ?? null) ? $reference['languages'] : [];
    $groups = is_array($reference['groups'] ?? null) ? $reference['groups'] : [];
    $resources = is_array($reference['resources'] ?? null) ? $reference['resources'] : [];
    $groupLanguages = is_array($reference['group_languages'] ?? null) ? $reference['group_languages'] : [];
    $groupSourceOutputs = is_array($reference['group_source_outputs'] ?? null) ? $reference['group_source_outputs'] : [];
    $additionalGroupAssignments = is_array($reference['additional_group_assignments'] ?? null)
        ? $reference['additional_group_assignments']
        : [];
    $captions = is_array($reference['captions'] ?? null) ? $reference['captions'] : [];

    $orderedLanguages = [];
    foreach ($languages as $language) {
        if (is_array($language) && (int) ($language['legacy_language_pid'] ?? 0) > 0) {
            $orderedLanguages[] = $language;
        }
    }
    usort(
        $orderedLanguages,
        static function (array $left, array $right): int {
            return ((int) ($left['legacy_language_pid'] ?? 0)) <=> ((int) ($right['legacy_language_pid'] ?? 0));
        },
    );

    $usedLocaleKeys = [];
    $localeOrder = [];
    $localeMetadata = [];
    $localeKeyByLegacyLanguagePid = [];
    $defaultLocale = '';
    foreach ($orderedLanguages as $language) {
        $legacyLanguagePid = (int) ($language['legacy_language_pid'] ?? 0);
        if ($legacyLanguagePid <= 0) {
            continue;
        }

        $localeKey = app_language_resource_file_catalog_unique_locale_key(
            app_language_resource_file_catalog_locale_key($language),
            $legacyLanguagePid,
            $usedLocaleKeys,
        );
        $usedLocaleKeys[$localeKey] = true;
        $localeKeyByLegacyLanguagePid[(string) $legacyLanguagePid] = $localeKey;
        $localeOrder[] = $localeKey;
        $localeMetadata[$localeKey] = [
            'legacy_language_pid' => $legacyLanguagePid,
            'label' => (string) ($language['caption'] ?? ''),
            'filename_suffix' => (string) ($language['filename_suffix'] ?? ''),
            'template_key' => (string) ($language['template_key'] ?? ''),
            'is_default' => ((int) ($language['is_default'] ?? 0)) === 1,
            'lang_for_cs' => (string) ($language['lang_for_cs'] ?? ''),
            'lang_for_android' => (string) ($language['lang_for_android'] ?? ''),
            'lang_for_ios' => (string) ($language['lang_for_ios'] ?? ''),
            'lang_for_google' => (string) ($language['lang_for_google'] ?? ''),
        ];
        if ($defaultLocale === '' && ((int) ($language['is_default'] ?? 0)) === 1) {
            $defaultLocale = $localeKey;
        }
    }
    if ($defaultLocale === '' && $localeOrder !== []) {
        $defaultLocale = (string) $localeOrder[0];
    }

    $orderedGroups = [];
    foreach ($groups as $group) {
        if (is_array($group) && (int) ($group['legacy_group_pid'] ?? 0) > 0) {
            $orderedGroups[] = $group;
        }
    }
    usort(
        $orderedGroups,
        static function (array $left, array $right): int {
            return ((int) ($left['legacy_group_pid'] ?? 0)) <=> ((int) ($right['legacy_group_pid'] ?? 0));
        },
    );

    $groupsByLegacyPid = [];
    $groupKeyByLegacyPid = [];
    $groupEntries = [];
    foreach ($orderedGroups as $group) {
        $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0);
        $groupKey = app_language_resource_file_catalog_group_key(
            $legacyGroupPid,
            (string) ($group['name'] ?? ''),
        );
        $groupsByLegacyPid[(string) $legacyGroupPid] = $group;
        $groupKeyByLegacyPid[(string) $legacyGroupPid] = $groupKey;
        $groupEntries[$groupKey] = [
            'group_key' => $groupKey,
            'group' => [
                'group_key' => $groupKey,
                'legacy_group_pid' => $legacyGroupPid,
                'name' => (string) ($group['name'] ?? ''),
                'function_name_prefix' => (string) ($group['function_name_prefix'] ?? ''),
                'function_name_suffix' => (string) ($group['function_name_suffix'] ?? ''),
                'filename_suffix_for_php' => (string) ($group['filename_suffix_for_php'] ?? ''),
                'filename_suffix' => (string) ($group['filename_suffix'] ?? ''),
                'filename_for_xcode' => (string) ($group['filename_for_xcode'] ?? ''),
                'last_modified_dt' => (string) ($group['last_modified_dt'] ?? ''),
                'locales' => [],
                'language_bindings' => [],
                'source_output_keys' => [],
                'source_outputs' => [],
                'resource_count' => 0,
            ],
            'resources' => [],
        ];
    }

    $groupLanguageLocaleKeysByGroupPid = [];
    $groupLanguageBindingsByGroupPid = [];
    foreach ($groupLanguages as $groupLanguage) {
        if (!is_array($groupLanguage)) {
            continue;
        }

        $legacyGroupPid = (int) ($groupLanguage['legacy_group_pid'] ?? 0);
        $legacyLanguagePid = (int) ($groupLanguage['legacy_language_pid'] ?? 0);
        $groupKey = $groupKeyByLegacyPid[(string) $legacyGroupPid] ?? '';
        $localeKey = $localeKeyByLegacyLanguagePid[(string) $legacyLanguagePid] ?? '';
        if ($groupKey === '' || $localeKey === '') {
            continue;
        }

        $groupLanguageLocaleKeysByGroupPid[(string) $legacyGroupPid][$localeKey] = true;
        $groupLanguageBindingsByGroupPid[(string) $legacyGroupPid][] = [
            'legacy_group_language_pid' => (int) ($groupLanguage['legacy_group_language_pid'] ?? 0),
            'legacy_language_pid' => $legacyLanguagePid,
            'locale' => $localeKey,
        ];
    }
    foreach ($groupLanguageLocaleKeysByGroupPid as $legacyGroupPid => $localeKeys) {
        $groupKey = $groupKeyByLegacyPid[(string) $legacyGroupPid] ?? '';
        if ($groupKey === '') {
            continue;
        }

        $orderedLocaleKeys = [];
        foreach ($localeOrder as $localeKey) {
            if (array_key_exists($localeKey, $localeKeys)) {
                $orderedLocaleKeys[] = $localeKey;
            }
        }
        $groupEntries[$groupKey]['group']['locales'] = $orderedLocaleKeys;

        $bindings = $groupLanguageBindingsByGroupPid[(string) $legacyGroupPid] ?? [];
        $bindingByLocale = [];
        foreach ($bindings as $binding) {
            if (!is_array($binding)) {
                continue;
            }

            $bindingByLocale[(string) ($binding['locale'] ?? '')] = $binding;
        }

        $orderedBindings = [];
        foreach ($orderedLocaleKeys as $localeKey) {
            if (is_array($bindingByLocale[$localeKey] ?? null)) {
                $orderedBindings[] = $bindingByLocale[$localeKey];
            }
        }
        $groupEntries[$groupKey]['group']['language_bindings'] = $orderedBindings;
    }

    $groupSourceOutputKeysByGroupPid = [];
    foreach ($groupSourceOutputs as $groupSourceOutput) {
        if (!is_array($groupSourceOutput)) {
            continue;
        }

        $legacyGroupPid = (int) ($groupSourceOutput['legacy_group_pid'] ?? 0);
        $legacyProjectSourceOutputPid = (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0);
        $groupKey = $groupKeyByLegacyPid[(string) $legacyGroupPid] ?? '';
        if ($groupKey === '' || $legacyProjectSourceOutputPid <= 0) {
            continue;
        }

        $resolved = $sourceOutputMap[(string) $legacyProjectSourceOutputPid] ?? null;
        $groupSourceOutputKeysByGroupPid[(string) $legacyGroupPid][] = [
            'legacy_group_source_output_pid' => (int) ($groupSourceOutput['legacy_group_source_output_pid'] ?? 0),
            'legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
            'source_output_key' => is_array($resolved) ? (string) ($resolved['source_output_key'] ?? '') : '',
            'name' => is_array($resolved) ? (string) ($resolved['name'] ?? '') : '',
            'program_language' => is_array($resolved) ? (string) ($resolved['program_language'] ?? '') : '',
            'source_template_dir' => is_array($resolved) ? (string) ($resolved['source_template_dir'] ?? '') : '',
        ];
    }
    foreach ($groupSourceOutputKeysByGroupPid as $legacyGroupPid => $items) {
        $groupKey = $groupKeyByLegacyPid[(string) $legacyGroupPid] ?? '';
        if ($groupKey === '') {
            continue;
        }

        usort(
            $items,
            static function (array $left, array $right): int {
                $leftRelationPid = (int) ($left['legacy_group_source_output_pid'] ?? 0);
                $rightRelationPid = (int) ($right['legacy_group_source_output_pid'] ?? 0);
                if ($leftRelationPid > 0 && $rightRelationPid > 0 && $leftRelationPid !== $rightRelationPid) {
                    return $leftRelationPid <=> $rightRelationPid;
                }

                $leftKey = (string) ($left['source_output_key'] ?? '');
                $rightKey = (string) ($right['source_output_key'] ?? '');
                if ($leftKey !== '' || $rightKey !== '') {
                    return strcmp($leftKey, $rightKey);
                }

                return ((int) ($left['legacy_project_source_output_pid'] ?? 0))
                    <=> ((int) ($right['legacy_project_source_output_pid'] ?? 0));
            },
        );

        $groupEntries[$groupKey]['group']['source_outputs'] = $items;
        $sourceOutputKeys = [];
        foreach ($items as $item) {
            $sourceOutputKey = trim((string) ($item['source_output_key'] ?? ''));
            if ($sourceOutputKey !== '') {
                $sourceOutputKeys[$sourceOutputKey] = $sourceOutputKey;
            }
        }
        $groupEntries[$groupKey]['group']['source_output_keys'] = array_values($sourceOutputKeys);
    }

    $captionsByResourcePid = [];
    foreach ($captions as $caption) {
        if (!is_array($caption)) {
            continue;
        }

        $legacyResourcePid = (int) ($caption['legacy_resource_pid'] ?? 0);
        $legacyLanguagePid = (int) ($caption['legacy_language_pid'] ?? 0);
        $localeKey = $localeKeyByLegacyLanguagePid[(string) $legacyLanguagePid] ?? '';
        if ($legacyResourcePid <= 0 || $localeKey === '') {
            continue;
        }

        $captionsByResourcePid[(string) $legacyResourcePid][$localeKey] = [
            'legacy_caption_pid' => (int) ($caption['legacy_caption_pid'] ?? 0),
            'legacy_language_pid' => $legacyLanguagePid,
            'text' => (string) ($caption['caption'] ?? ''),
            'auto_translated' => (string) ($caption['caption_auto_translated'] ?? ''),
        ];
    }

    $additionalGroupKeysByResourcePid = [];
    foreach ($additionalGroupAssignments as $assignment) {
        if (!is_array($assignment)) {
            continue;
        }

        $legacyResourcePid = (int) ($assignment['legacy_resource_pid'] ?? 0);
        $legacyGroupPid = (int) ($assignment['legacy_group_pid'] ?? 0);
        $groupKey = $groupKeyByLegacyPid[(string) $legacyGroupPid] ?? '';
        if ($legacyResourcePid <= 0 || $groupKey === '') {
            continue;
        }

        $additionalGroupKeysByResourcePid[(string) $legacyResourcePid][$groupKey] = true;
    }

    $orderedResources = [];
    foreach ($resources as $resource) {
        if (is_array($resource) && (int) ($resource['legacy_resource_pid'] ?? 0) > 0) {
            $orderedResources[] = $resource;
        }
    }
    usort(
        $orderedResources,
        static function (array $left, array $right): int {
            $leftGroupPid = (int) ($left['legacy_group_pid'] ?? 0);
            $rightGroupPid = (int) ($right['legacy_group_pid'] ?? 0);
            if ($leftGroupPid !== $rightGroupPid) {
                return $leftGroupPid <=> $rightGroupPid;
            }

            $leftSortGroup = (string) ($left['sort_group'] ?? '');
            $rightSortGroup = (string) ($right['sort_group'] ?? '');
            if ($leftSortGroup !== $rightSortGroup) {
                return strcmp($leftSortGroup, $rightSortGroup);
            }

            $leftKeyName = (string) ($left['key_name'] ?? '');
            $rightKeyName = (string) ($right['key_name'] ?? '');
            if ($leftKeyName !== $rightKeyName) {
                return strcmp($leftKeyName, $rightKeyName);
            }

            return ((int) ($left['legacy_resource_pid'] ?? 0)) <=> ((int) ($right['legacy_resource_pid'] ?? 0));
        },
    );

    foreach ($orderedResources as $resource) {
        $legacyGroupPid = (int) ($resource['legacy_group_pid'] ?? 0);
        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        $groupKey = $groupKeyByLegacyPid[(string) $legacyGroupPid] ?? '';
        if ($groupKey === '' || $legacyResourcePid <= 0) {
            continue;
        }

        $captionsMap = [];
        foreach ($localeOrder as $localeKey) {
            if (array_key_exists($localeKey, $captionsByResourcePid[(string) $legacyResourcePid] ?? [])) {
                $captionsMap[$localeKey] = $captionsByResourcePid[(string) $legacyResourcePid][$localeKey];
            }
        }

        $additionalGroupKeys = array_keys($additionalGroupKeysByResourcePid[(string) $legacyResourcePid] ?? []);
        sort($additionalGroupKeys, SORT_NATURAL);

        $resourceItem = [
            'resource_key' => (string) ($resource['resource_key'] ?? ''),
            'legacy_resource_pid' => $legacyResourcePid,
            'base_group_key' => $groupKey,
            'additional_group_keys' => $additionalGroupKeys,
            'key_for_update' => (string) ($resource['key_for_update'] ?? ''),
            'sort_group' => (string) ($resource['sort_group'] ?? ''),
            'key_name' => (string) ($resource['key_name'] ?? ''),
            'key_name_for_xcode' => (string) ($resource['key_name_for_xcode'] ?? ''),
            'uwp_target_property' => (string) ($resource['uwp_target_property'] ?? ''),
            'is_resource_fixed' => ((int) ($resource['is_resource_fixed'] ?? 0)) === 1,
            'use_default_if_caption_is_blank' => ((int) ($resource['use_default_if_caption_is_blank'] ?? 0)) === 1,
            'captions' => $captionsMap,
        ];
        $resourceKey = (string) ($resourceItem['resource_key'] ?? '');
        $groupEntries[$groupKey]['resources'][] = [
            'resource_key' => $resourceKey,
            'item' => $resourceItem,
        ];
    }

    $manifestGroups = [];
    $groupList = array_values($groupEntries);
    usort(
        $groupList,
        static function (array $left, array $right): int {
            return strcmp((string) ($left['group_key'] ?? ''), (string) ($right['group_key'] ?? ''));
        },
    );
    foreach ($groupList as &$groupEntry) {
        usort(
            $groupEntry['resources'],
            static function (array $left, array $right): int {
                return strcmp((string) ($left['resource_key'] ?? ''), (string) ($right['resource_key'] ?? ''));
            },
        );
        $groupEntry['group']['resource_count'] = count($groupEntry['resources']);
        $manifestGroups[] = [
            'group_key' => $groupEntry['group']['group_key'],
            'legacy_group_pid' => $groupEntry['group']['legacy_group_pid'],
            'name' => $groupEntry['group']['name'],
            'resource_count' => $groupEntry['group']['resource_count'],
        ];
    }
    unset($groupEntry);

    $enabledSourceOutputKeys = [];
    foreach ($sourceOutputMap as $item) {
        $sourceOutputKey = trim((string) ($item['source_output_key'] ?? ''));
        if ($sourceOutputKey !== '') {
            $enabledSourceOutputKeys[$sourceOutputKey] = $sourceOutputKey;
        }
    }

    $treeCounts = app_language_resource_file_catalog_actual_counts([
        'manifest' => [
            'locales' => $localeMetadata,
            'locale_order' => $localeOrder,
        ],
        'groups' => $groupList,
    ]);
    $normalization = app_language_resource_file_catalog_reference_normalization($reference);

    $manifest = [
        'schema_version' => app_language_resource_file_catalog_schema_version(),
        'project_key' => app_normalize_project_key((string) ($reference['project_key'] ?? '')),
        'project_pid' => (int) ($reference['project_pid'] ?? 0),
        'catalog_source' => 'file-canonical',
        'origin' => [
            // Keep the historical export provenance, but runtime never opens this host-side path.
            'type' => 'bootstrap-reference',
            'source_dump_path' => (string) ($reference['source_dump_path'] ?? ''),
            'legacy_reference_generated_at' => (string) ($reference['generated_at'] ?? ''),
        ],
        'generated_at' => date(DATE_ATOM),
        'default_locale' => $defaultLocale,
        'locale_order' => $localeOrder,
        'locales' => $localeMetadata,
        'enabled_source_output_keys' => array_values($enabledSourceOutputKeys),
        'counts' => $treeCounts,
        'normalization' => [
            'raw_reference_counts' => app_language_resource_file_catalog_reference_counts($reference),
            'dropped_counts' => $normalization['dropped_counts'],
            'dropped_rows' => $normalization['dropped_rows'],
        ],
        'groups' => $manifestGroups,
    ];

    return [
        'manifest' => $manifest,
        'groups' => $groupList,
    ];
}

function app_language_resource_file_catalog_synthetic_group_language_pid(
    int $legacyGroupPid,
    int $legacyLanguagePid,
): int {
    return max(1, ($legacyGroupPid * 100000) + $legacyLanguagePid);
}

function app_language_resource_file_catalog_synthetic_group_source_output_pid(
    int $legacyGroupPid,
    int $legacyProjectSourceOutputPid,
): int {
    return max(1, ($legacyGroupPid * 100000) + $legacyProjectSourceOutputPid);
}

function app_language_resource_file_catalog_synthetic_caption_pid(
    int $legacyResourcePid,
    int $legacyLanguagePid,
): int {
    return max(1, ($legacyResourcePid * 100000) + $legacyLanguagePid);
}

function app_language_resource_file_catalog_synthetic_assignment_pid(
    int $legacyResourcePid,
    int $legacyGroupPid,
): int {
    return max(1, ($legacyResourcePid * 100000) + $legacyGroupPid);
}

/**
 * @param array{
 *     manifest:array<string,mixed>,
 *     groups:list<array{
 *         group_key:string,
 *         group:array<string,mixed>,
 *         resources:list<array{
 *             resource_key:string,
 *             item:array<string,mixed>
 *         }>
 *     }>
 * } $tree
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
 *     resources:list<array<string,mixed>>,
 *     groups:list<array<string,mixed>>,
 *     group_languages:list<array<string,mixed>>,
 *     group_source_outputs:list<array<string,mixed>>,
 *     additional_group_assignments:list<array<string,mixed>>,
 *     captions:list<array<string,mixed>>,
 *     languages:list<array<string,mixed>>
 * }
 */
function app_language_resource_file_catalog_to_legacy_catalog(array $tree): array
{
    $manifest = is_array($tree['manifest'] ?? null) ? $tree['manifest'] : [];
    $groups = is_array($tree['groups'] ?? null) ? $tree['groups'] : [];
    $projectKey = app_normalize_project_key((string) ($manifest['project_key'] ?? ''));
    $projectPid = (int) ($manifest['project_pid'] ?? 0);
    $origin = is_array($manifest['origin'] ?? null) ? $manifest['origin'] : [];
    // Preserve provenance metadata when round-tripping the file catalog back to legacy-shaped data.
    $sourceDumpPath = (string) ($origin['source_dump_path'] ?? '');
    $generatedAt = (string) ($manifest['generated_at'] ?? '');
    if ($generatedAt === '') {
        $generatedAt = (string) ($origin['legacy_reference_generated_at'] ?? '');
    }

    $locales = is_array($manifest['locales'] ?? null) ? $manifest['locales'] : [];
    $localeOrder = is_array($manifest['locale_order'] ?? null) ? $manifest['locale_order'] : [];

    $languageItems = [];
    $legacyLanguagePidByLocale = [];
    $languageOrder = 10;
    foreach ($localeOrder as $localeKey) {
        $normalizedLocaleKey = trim((string) $localeKey);
        $locale = is_array($locales[$normalizedLocaleKey] ?? null) ? $locales[$normalizedLocaleKey] : [];
        $legacyLanguagePid = (int) ($locale['legacy_language_pid'] ?? 0);
        if ($normalizedLocaleKey === '' || $legacyLanguagePid <= 0) {
            continue;
        }

        $legacyLanguagePidByLocale[$normalizedLocaleKey] = $legacyLanguagePid;
        $languageItems[] = [
            'legacy_language_pid' => $legacyLanguagePid,
            'filename_suffix' => (string) ($locale['filename_suffix'] ?? ''),
            'template_key' => (string) ($locale['template_key'] ?? ''),
            'is_default' => ((bool) ($locale['is_default'] ?? false)) ? 1 : 0,
            'caption' => (string) ($locale['label'] ?? ''),
            'lang_for_cs' => (string) ($locale['lang_for_cs'] ?? ''),
            'lang_for_android' => (string) ($locale['lang_for_android'] ?? ''),
            'lang_for_ios' => (string) ($locale['lang_for_ios'] ?? ''),
            'lang_for_google' => (string) ($locale['lang_for_google'] ?? ''),
            'language_list_order' => $languageOrder,
        ];
        $languageOrder += 10;
    }

    $groupItems = [];
    $groupLanguageItems = [];
    $groupSourceOutputItems = [];
    $resourceItems = [];
    $captionItems = [];
    $additionalGroupItems = [];
    $legacyGroupPidByGroupKey = [];

    $groupOrder = 10;
    foreach ($groups as $groupEntry) {
        if (!is_array($groupEntry)) {
            continue;
        }

        $group = is_array($groupEntry['group'] ?? null) ? $groupEntry['group'] : [];
        $groupKey = trim((string) ($group['group_key'] ?? $groupEntry['group_key'] ?? ''));
        $legacyGroupPid = (int) ($group['legacy_group_pid'] ?? 0);
        if ($groupKey === '' || $legacyGroupPid <= 0) {
            continue;
        }

        $legacyGroupPidByGroupKey[$groupKey] = $legacyGroupPid;
        $lastModifiedDt = trim((string) ($group['last_modified_dt'] ?? ''));
        if ($lastModifiedDt === '') {
            $lastModifiedDt = date('Y-m-d H:i:s');
        }

        $groupItems[] = [
            'legacy_group_pid' => $legacyGroupPid,
            'project_pid' => $projectPid,
            'name' => (string) ($group['name'] ?? ''),
            'function_name_prefix' => (string) ($group['function_name_prefix'] ?? ''),
            'function_name_suffix' => (string) ($group['function_name_suffix'] ?? ''),
            'filename_suffix_for_php' => (string) ($group['filename_suffix_for_php'] ?? ''),
            'filename_suffix' => (string) ($group['filename_suffix'] ?? ''),
            'filename_for_xcode' => (string) ($group['filename_for_xcode'] ?? ''),
            'last_modified_dt' => $lastModifiedDt,
            'group_list_order' => $groupOrder,
        ];
        $groupOrder += 10;

        $languageBindings = is_array($group['language_bindings'] ?? null) ? $group['language_bindings'] : [];
        if ($languageBindings === []) {
            foreach ((is_array($group['locales'] ?? null) ? $group['locales'] : []) as $localeKey) {
                $normalizedLocaleKey = trim((string) $localeKey);
                $legacyLanguagePid = $legacyLanguagePidByLocale[$normalizedLocaleKey] ?? 0;
                if ($legacyLanguagePid <= 0) {
                    continue;
                }

                $languageBindings[] = [
                    'legacy_group_language_pid' => app_language_resource_file_catalog_synthetic_group_language_pid(
                        $legacyGroupPid,
                        $legacyLanguagePid,
                    ),
                    'legacy_language_pid' => $legacyLanguagePid,
                    'locale' => $normalizedLocaleKey,
                ];
            }
        }

        $groupLanguageOrder = 10;
        foreach ($languageBindings as $binding) {
            if (!is_array($binding)) {
                continue;
            }

            $bindingLocaleKey = trim((string) ($binding['locale'] ?? ''));
            $legacyLanguagePid = (int) ($binding['legacy_language_pid'] ?? ($legacyLanguagePidByLocale[$bindingLocaleKey] ?? 0));
            if ($legacyLanguagePid <= 0) {
                continue;
            }

            $legacyGroupLanguagePid = (int) ($binding['legacy_group_language_pid'] ?? 0);
            if ($legacyGroupLanguagePid <= 0) {
                $legacyGroupLanguagePid = app_language_resource_file_catalog_synthetic_group_language_pid(
                    $legacyGroupPid,
                    $legacyLanguagePid,
                );
            }

            $groupLanguageItems[] = [
                'legacy_group_language_pid' => $legacyGroupLanguagePid,
                'project_pid' => $projectPid,
                'legacy_group_pid' => $legacyGroupPid,
                'legacy_language_pid' => $legacyLanguagePid,
                'relation_list_order' => $groupLanguageOrder,
            ];
            $groupLanguageOrder += 10;
        }

        $sourceOutputs = is_array($group['source_outputs'] ?? null) ? $group['source_outputs'] : [];
        $groupSourceOutputOrder = 10;
        foreach ($sourceOutputs as $sourceOutput) {
            if (!is_array($sourceOutput)) {
                continue;
            }

            $legacyProjectSourceOutputPid = (int) ($sourceOutput['legacy_project_source_output_pid'] ?? 0);
            if ($legacyProjectSourceOutputPid <= 0) {
                continue;
            }

            $legacyGroupSourceOutputPid = (int) ($sourceOutput['legacy_group_source_output_pid'] ?? 0);
            if ($legacyGroupSourceOutputPid <= 0) {
                $legacyGroupSourceOutputPid = app_language_resource_file_catalog_synthetic_group_source_output_pid(
                    $legacyGroupPid,
                    $legacyProjectSourceOutputPid,
                );
            }

            $groupSourceOutputItems[] = [
                'legacy_group_source_output_pid' => $legacyGroupSourceOutputPid,
                'project_pid' => $projectPid,
                'legacy_group_pid' => $legacyGroupPid,
                'legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
                'relation_list_order' => $groupSourceOutputOrder,
            ];
            $groupSourceOutputOrder += 10;
        }

        $resourceEntries = is_array($groupEntry['resources'] ?? null) ? $groupEntry['resources'] : [];
        $resourceOrder = 10;
        foreach ($resourceEntries as $resourceEntry) {
            if (!is_array($resourceEntry)) {
                continue;
            }

            $resource = is_array($resourceEntry['item'] ?? null) ? $resourceEntry['item'] : [];
            $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
            $resourceKey = trim((string) ($resource['resource_key'] ?? $resourceEntry['resource_key'] ?? ''));
            if ($legacyResourcePid <= 0 || $resourceKey === '') {
                continue;
            }

            $resourceItems[] = [
                'legacy_resource_pid' => $legacyResourcePid,
                'project_pid' => $projectPid,
                'legacy_group_pid' => $legacyGroupPid,
                'resource_key' => $resourceKey,
                'key_for_update' => (string) ($resource['key_for_update'] ?? ''),
                'sort_group' => (string) ($resource['sort_group'] ?? ''),
                'key_name' => (string) ($resource['key_name'] ?? ''),
                'key_name_for_xcode' => (string) ($resource['key_name_for_xcode'] ?? ''),
                'uwp_target_property' => (string) ($resource['uwp_target_property'] ?? ''),
                'is_resource_fixed' => ((bool) ($resource['is_resource_fixed'] ?? false)) ? 1 : 0,
                'use_default_if_caption_is_blank' => ((bool) ($resource['use_default_if_caption_is_blank'] ?? true)) ? 1 : 0,
                'resource_list_order' => $resourceOrder,
                'last_modified_dt' => $lastModifiedDt,
            ];
            $resourceOrder += 10;

            $captions = is_array($resource['captions'] ?? null) ? $resource['captions'] : [];
            $captionOrder = 10;
            foreach ($localeOrder as $localeKey) {
                $normalizedLocaleKey = trim((string) $localeKey);
                $caption = is_array($captions[$normalizedLocaleKey] ?? null) ? $captions[$normalizedLocaleKey] : null;
                if ($caption === null) {
                    continue;
                }

                $legacyLanguagePid = (int) ($caption['legacy_language_pid'] ?? ($legacyLanguagePidByLocale[$normalizedLocaleKey] ?? 0));
                if ($legacyLanguagePid <= 0) {
                    continue;
                }

                $legacyCaptionPid = (int) ($caption['legacy_caption_pid'] ?? 0);
                if ($legacyCaptionPid <= 0) {
                    $legacyCaptionPid = app_language_resource_file_catalog_synthetic_caption_pid(
                        $legacyResourcePid,
                        $legacyLanguagePid,
                    );
                }

                $captionItems[] = [
                    'legacy_caption_pid' => $legacyCaptionPid,
                    'project_pid' => $projectPid,
                    'legacy_resource_pid' => $legacyResourcePid,
                    'legacy_group_pid' => $legacyGroupPid,
                    'legacy_language_pid' => $legacyLanguagePid,
                    'caption' => (string) ($caption['text'] ?? ''),
                    'caption_auto_translated' => (string) ($caption['auto_translated'] ?? ''),
                    'caption_list_order' => $captionOrder,
                ];
                $captionOrder += 10;
            }

            $additionalGroups = [];
            if (is_array($resource['additional_groups'] ?? null)) {
                $additionalGroups = $resource['additional_groups'];
            } elseif (is_array($resource['additional_group_keys'] ?? null)) {
                foreach ($resource['additional_group_keys'] as $additionalGroupKey) {
                    $additionalGroups[] = [
                        'group_key' => (string) $additionalGroupKey,
                    ];
                }
            }

            $assignmentOrder = 10;
            foreach ($additionalGroups as $additionalGroup) {
                if (!is_array($additionalGroup)) {
                    continue;
                }

                $additionalGroupKey = trim((string) ($additionalGroup['group_key'] ?? ''));
                $additionalLegacyGroupPid = $legacyGroupPidByGroupKey[$additionalGroupKey] ?? 0;
                if ($additionalGroupKey === '' || $additionalLegacyGroupPid <= 0) {
                    continue;
                }

                $legacyAssignmentPid = (int) ($additionalGroup['legacy_assignment_pid'] ?? 0);
                if ($legacyAssignmentPid <= 0) {
                    $legacyAssignmentPid = app_language_resource_file_catalog_synthetic_assignment_pid(
                        $legacyResourcePid,
                        $additionalLegacyGroupPid,
                    );
                }

                $additionalGroupItems[] = [
                    'legacy_assignment_pid' => $legacyAssignmentPid,
                    'project_pid' => $projectPid,
                    'legacy_resource_pid' => $legacyResourcePid,
                    'legacy_group_pid' => $additionalLegacyGroupPid,
                    'relation_list_order' => $assignmentOrder,
                ];
                $assignmentOrder += 10;
            }
        }
    }

    return [
        'project_key' => $projectKey,
        'project_pid' => $projectPid,
        'source_dump_path' => $sourceDumpPath,
        'generated_at' => $generatedAt,
        'resource_count' => count($resourceItems),
        'group_count' => count($groupItems),
        'group_language_count' => count($groupLanguageItems),
        'group_source_output_count' => count($groupSourceOutputItems),
        'additional_group_assignment_count' => count($additionalGroupItems),
        'caption_count' => count($captionItems),
        'language_count' => count($languageItems),
        'resources' => $resourceItems,
        'groups' => $groupItems,
        'group_languages' => $groupLanguageItems,
        'group_source_outputs' => $groupSourceOutputItems,
        'additional_group_assignments' => $additionalGroupItems,
        'captions' => $captionItems,
        'languages' => $languageItems,
    ];
}

/**
 * @param array{
 *     manifest:array<string,mixed>,
 *     groups:list<array{
 *         group_key:string,
 *         group:array<string,mixed>,
 *         resources:list<array{
 *             resource_key:string,
 *             item:array<string,mixed>
 *         }>
 *     }>
 * } $tree
 * @return array{
 *     ok:bool,
 *     errors:list<string>,
 *     warnings:list<string>,
 *     summary:array{
 *         locales:int,
 *         groups:int,
 *         resources:int,
 *         group_languages:int,
 *         group_source_outputs:int,
 *         additional_group_assignments:int,
 *         captions:int
 *     }
 * }
 */
function app_language_resource_file_catalog_validate_tree(array $tree): array
{
    $errors = [];
    $warnings = [];

    $manifest = is_array($tree['manifest'] ?? null) ? $tree['manifest'] : [];
    $groups = is_array($tree['groups'] ?? null) ? $tree['groups'] : [];

    $projectKey = trim((string) ($manifest['project_key'] ?? ''));
    if ($projectKey === '' || !app_project_key_is_valid($projectKey)) {
        $errors[] = 'manifest.project_key が不正です。';
    }

    $schemaVersion = (int) ($manifest['schema_version'] ?? 0);
    if ($schemaVersion !== app_language_resource_file_catalog_schema_version()) {
        $errors[] = 'manifest.schema_version が想定と一致しません。';
    }

    $locales = is_array($manifest['locales'] ?? null) ? $manifest['locales'] : [];
    $localeOrder = is_array($manifest['locale_order'] ?? null) ? $manifest['locale_order'] : [];
    $knownLocaleKeys = [];
    foreach ($locales as $localeKey => $localeItem) {
        $normalizedLocaleKey = trim((string) $localeKey);
        if ($normalizedLocaleKey === '') {
            $errors[] = 'manifest.locales に空 locale key があります。';
            continue;
        }
        if (array_key_exists($normalizedLocaleKey, $knownLocaleKeys)) {
            $errors[] = 'locale key が重複しています: ' . $normalizedLocaleKey;
            continue;
        }

        $knownLocaleKeys[$normalizedLocaleKey] = true;
        if (!is_array($localeItem)) {
            $errors[] = 'manifest.locales.' . $normalizedLocaleKey . ' が object ではありません。';
        }
    }
    foreach ($localeOrder as $localeKey) {
        $normalizedLocaleKey = trim((string) $localeKey);
        if ($normalizedLocaleKey === '' || !array_key_exists($normalizedLocaleKey, $knownLocaleKeys)) {
            $errors[] = 'manifest.locale_order に unknown locale があります: ' . $normalizedLocaleKey;
        }
    }

    $defaultLocale = trim((string) ($manifest['default_locale'] ?? ''));
    if ($defaultLocale === '' || !array_key_exists($defaultLocale, $knownLocaleKeys)) {
        $errors[] = 'manifest.default_locale が未定義または unknown locale です。';
    }

    $knownGroupKeys = [];
    $knownResourceKeys = [];
    $resourceCount = 0;
    foreach ($groups as $groupEntry) {
        if (!is_array($groupEntry)) {
            $errors[] = 'group entry が object ではありません。';
            continue;
        }

        $group = is_array($groupEntry['group'] ?? null) ? $groupEntry['group'] : [];
        $groupKey = trim((string) ($group['group_key'] ?? $groupEntry['group_key'] ?? ''));
        if ($groupKey === '') {
            $errors[] = 'group_key が空です。';
            continue;
        }
        if (array_key_exists($groupKey, $knownGroupKeys)) {
            $errors[] = 'group_key が重複しています: ' . $groupKey;
            continue;
        }

        $knownGroupKeys[$groupKey] = true;
        $groupLocales = is_array($group['locales'] ?? null) ? $group['locales'] : [];
        foreach ($groupLocales as $localeKey) {
            $normalizedLocaleKey = trim((string) $localeKey);
            if ($normalizedLocaleKey === '' || !array_key_exists($normalizedLocaleKey, $knownLocaleKeys)) {
                $errors[] = 'group ' . $groupKey . ' に unknown locale があります: ' . $normalizedLocaleKey;
            }
        }

        $languageBindings = is_array($group['language_bindings'] ?? null) ? $group['language_bindings'] : [];
        $bindingLocales = [];
        foreach ($languageBindings as $binding) {
            if (!is_array($binding)) {
                $errors[] = 'group ' . $groupKey . ' の language_bindings entry が object ではありません。';
                continue;
            }

            $bindingLocale = trim((string) ($binding['locale'] ?? ''));
            if ($bindingLocale === '' || !array_key_exists($bindingLocale, $knownLocaleKeys)) {
                $errors[] = 'group ' . $groupKey . ' の language_bindings に unknown locale があります: ' . $bindingLocale;
                continue;
            }

            $bindingLocales[$bindingLocale] = true;
        }
        if ($languageBindings !== [] && count($bindingLocales) !== count($groupLocales)) {
            $errors[] = 'group ' . $groupKey . ' の locales と language_bindings が一致しません。';
        }

        $groupSourceOutputs = is_array($group['source_outputs'] ?? null) ? $group['source_outputs'] : [];
        foreach ($groupSourceOutputs as $sourceOutput) {
            if (!is_array($sourceOutput)) {
                $errors[] = 'group ' . $groupKey . ' の source_outputs entry が object ではありません。';
                continue;
            }

            $legacyGroupSourceOutputPid = (int) ($sourceOutput['legacy_group_source_output_pid'] ?? 0);
            if ($legacyGroupSourceOutputPid < 0) {
                $errors[] = 'group ' . $groupKey . ' の source output binding に不正な legacy_group_source_output_pid があります。';
            }

            $legacyProjectSourceOutputPid = (int) ($sourceOutput['legacy_project_source_output_pid'] ?? 0);
            if ($legacyProjectSourceOutputPid <= 0) {
                $errors[] = 'group ' . $groupKey . ' の source output binding に legacy pid がありません。';
            }

            if (trim((string) ($sourceOutput['source_output_key'] ?? '')) === '') {
                $warnings[] = 'group ' . $groupKey . ' の source output binding '
                    . $legacyProjectSourceOutputPid
                    . ' は current source_output_key 未解決です。';
            }
        }

        $resources = is_array($groupEntry['resources'] ?? null) ? $groupEntry['resources'] : [];
        $declaredResourceCount = (int) ($group['resource_count'] ?? -1);
        if ($declaredResourceCount >= 0 && $declaredResourceCount !== count($resources)) {
            $errors[] = 'group ' . $groupKey . ' の resource_count が実体と一致しません。';
        }
        foreach ($resources as $resourceEntry) {
            if (!is_array($resourceEntry)) {
                $errors[] = 'group ' . $groupKey . ' の resource entry が object ではありません。';
                continue;
            }

            $resource = is_array($resourceEntry['item'] ?? null) ? $resourceEntry['item'] : [];
            $resourceKey = trim((string) ($resource['resource_key'] ?? $resourceEntry['resource_key'] ?? ''));
            if ($resourceKey === '') {
                $errors[] = 'group ' . $groupKey . ' に空 resource_key があります。';
                continue;
            }
            if (array_key_exists($resourceKey, $knownResourceKeys)) {
                $errors[] = 'resource_key が重複しています: ' . $resourceKey;
                continue;
            }

            $knownResourceKeys[$resourceKey] = true;
            $resourceCount++;

            $baseGroupKey = trim((string) ($resource['base_group_key'] ?? ''));
            if ($baseGroupKey !== $groupKey) {
                $errors[] = 'resource ' . $resourceKey . ' の base_group_key が親 directory と一致しません。';
            }

            $additionalGroupKeys = is_array($resource['additional_group_keys'] ?? null)
                ? $resource['additional_group_keys']
                : [];
            foreach ($additionalGroupKeys as $additionalGroupKey) {
                $normalizedAdditionalGroupKey = trim((string) $additionalGroupKey);
                if ($normalizedAdditionalGroupKey === '') {
                    $errors[] = 'resource ' . $resourceKey . ' に空 additional_group_key があります。';
                    continue;
                }

                if ($normalizedAdditionalGroupKey === $baseGroupKey) {
                    $errors[] = 'resource ' . $resourceKey . ' が base group を additional group に重複指定しています。';
                }
            }

            $captions = is_array($resource['captions'] ?? null) ? $resource['captions'] : [];
            foreach ($captions as $localeKey => $caption) {
                $normalizedLocaleKey = trim((string) $localeKey);
                if ($normalizedLocaleKey === '' || !array_key_exists($normalizedLocaleKey, $knownLocaleKeys)) {
                    $errors[] = 'resource ' . $resourceKey . ' に unknown caption locale があります: ' . $normalizedLocaleKey;
                    continue;
                }

                if (!is_array($caption)) {
                    $errors[] = 'resource ' . $resourceKey . ' の caption ' . $normalizedLocaleKey . ' が object ではありません。';
                }
            }
        }
    }

    foreach ($groups as $groupEntry) {
        if (!is_array($groupEntry)) {
            continue;
        }
        $group = is_array($groupEntry['group'] ?? null) ? $groupEntry['group'] : [];
        $resources = is_array($groupEntry['resources'] ?? null) ? $groupEntry['resources'] : [];
        foreach ($resources as $resourceEntry) {
            if (!is_array($resourceEntry)) {
                continue;
            }
            $resource = is_array($resourceEntry['item'] ?? null) ? $resourceEntry['item'] : [];
            $resourceKey = trim((string) ($resource['resource_key'] ?? $resourceEntry['resource_key'] ?? ''));
            if ($resourceKey === '') {
                continue;
            }

            $additionalGroupKeys = is_array($resource['additional_group_keys'] ?? null)
                ? $resource['additional_group_keys']
                : [];
            foreach ($additionalGroupKeys as $additionalGroupKey) {
                $normalizedAdditionalGroupKey = trim((string) $additionalGroupKey);
                if ($normalizedAdditionalGroupKey === '' || !array_key_exists($normalizedAdditionalGroupKey, $knownGroupKeys)) {
                    $errors[] = 'resource ' . $resourceKey . ' に unknown additional_group_key があります: ' . $normalizedAdditionalGroupKey;
                }
            }
        }
    }

    $actualCounts = app_language_resource_file_catalog_actual_counts($tree);

    return [
        'ok' => $errors === [],
        'errors' => array_values(array_unique($errors)),
        'warnings' => array_values(array_unique($warnings)),
        'summary' => [
            'locales' => $actualCounts['languages'],
            'groups' => $actualCounts['groups'],
            'resources' => $actualCounts['resources'],
            'group_languages' => $actualCounts['group_languages'],
            'group_source_outputs' => $actualCounts['group_source_outputs'],
            'additional_group_assignments' => $actualCounts['additional_group_assignments'],
            'captions' => $actualCounts['captions'],
        ],
    ];
}

/**
 * @param array{
 *     manifest:array<string,mixed>,
 *     groups:list<array{
 *         group_key:string,
 *         group:array<string,mixed>,
 *         resources:list<array{
 *             resource_key:string,
 *             item:array<string,mixed>
 *         }>
 *     }>
 * } $tree
 * @return array{
 *     ok:bool,
 *     root_path:string,
 *     files_written:int,
 *     error:string
 * }
 */
function app_language_resource_file_catalog_write_tree(
    string $rootPath,
    array $tree,
    bool $clean = false,
): array {
    try {
        $normalizedRootPath = rtrim($rootPath, '/');
        if ($normalizedRootPath === '') {
            throw new RuntimeException('output root が空です。');
        }

        if ($clean && file_exists($normalizedRootPath)) {
            app_language_resource_file_catalog_clean_generated_tree($normalizedRootPath);
        }

        app_language_resource_file_catalog_ensure_directory($normalizedRootPath);
        app_language_resource_file_catalog_ensure_directory($normalizedRootPath . '/groups');

        $filesWritten = 0;
        $manifestPath = $normalizedRootPath . '/manifest.json';
        if (file_put_contents($manifestPath, app_language_resource_file_catalog_json_encode($tree['manifest'])) === false) {
            throw new RuntimeException('manifest.json の書き込みに失敗しました。');
        }
        $filesWritten++;

        foreach ($tree['groups'] as $groupEntry) {
            if (!is_array($groupEntry)) {
                continue;
            }

            $groupKey = trim((string) ($groupEntry['group_key'] ?? ''));
            if ($groupKey === '') {
                continue;
            }

            $groupDirectory = $normalizedRootPath . '/groups/' . $groupKey;
            $resourceDirectory = $groupDirectory . '/resources';
            app_language_resource_file_catalog_ensure_directory($groupDirectory);
            app_language_resource_file_catalog_ensure_directory($resourceDirectory);

            $groupPath = $groupDirectory . '/group.json';
            if (
                file_put_contents(
                    $groupPath,
                    app_language_resource_file_catalog_json_encode(
                        is_array($groupEntry['group'] ?? null) ? $groupEntry['group'] : [],
                    ),
                ) === false
            ) {
                throw new RuntimeException('group.json の書き込みに失敗しました: ' . $groupKey);
            }
            $filesWritten++;

            foreach (($groupEntry['resources'] ?? []) as $resourceEntry) {
                if (!is_array($resourceEntry)) {
                    continue;
                }

                $resourceKey = trim((string) ($resourceEntry['resource_key'] ?? ''));
                if ($resourceKey === '') {
                    continue;
                }

                $resourcePath = $resourceDirectory . '/' . $resourceKey . '.json';
                if (
                    file_put_contents(
                        $resourcePath,
                        app_language_resource_file_catalog_json_encode(
                            is_array($resourceEntry['item'] ?? null) ? $resourceEntry['item'] : [],
                        ),
                    ) === false
                ) {
                    throw new RuntimeException('resource file の書き込みに失敗しました: ' . $resourceKey);
                }
                $filesWritten++;
            }
        }

        return [
            'ok' => true,
            'root_path' => $normalizedRootPath,
            'files_written' => $filesWritten,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'root_path' => rtrim($rootPath, '/'),
            'files_written' => 0,
            'error' => $throwable->getMessage(),
        ];
    }
}

/**
 * @return array{
 *     ok:bool,
 *     manifest:array<string,mixed>,
 *     groups:list<array{
 *         group_key:string,
 *         file_path:string,
 *         group:array<string,mixed>,
 *         resources:list<array{
 *             resource_key:string,
 *             item:array<string,mixed>,
            *             file_path:string
 *         }>
 *     }>,
 *     errors:list<string>,
 *     warnings:list<string>
 * }
 */
function app_language_resource_file_catalog_load_tree(string $rootPath): array
{
    $errors = [];
    $warnings = [];
    $normalizedRootPath = rtrim($rootPath, '/');
    $manifestPath = $normalizedRootPath . '/manifest.json';
    if (!is_file($manifestPath)) {
        return [
            'ok' => false,
            'manifest' => [],
            'groups' => [],
            'errors' => ['manifest.json が見つかりません。'],
            'warnings' => [],
        ];
    }

    $manifestContents = file_get_contents($manifestPath);
    $manifest = json_decode(is_string($manifestContents) ? $manifestContents : '', true);
    if (!is_array($manifest)) {
        return [
            'ok' => false,
            'manifest' => [],
            'groups' => [],
            'errors' => ['manifest.json が不正な JSON です。'],
            'warnings' => [],
        ];
    }

    $groupSummaries = is_array($manifest['groups'] ?? null) ? $manifest['groups'] : [];
    $groupEntries = [];
    foreach ($groupSummaries as $groupSummary) {
        if (!is_array($groupSummary)) {
            $errors[] = 'manifest.groups に object ではない entry があります。';
            continue;
        }

        $groupKey = trim((string) ($groupSummary['group_key'] ?? ''));
        if ($groupKey === '') {
            $errors[] = 'manifest.groups に空 group_key があります。';
            continue;
        }

        $groupDirectory = $normalizedRootPath . '/groups/' . $groupKey;
        $groupPath = $groupDirectory . '/group.json';
        if (!is_file($groupPath)) {
            $errors[] = 'group.json が見つかりません: ' . $groupKey;
            continue;
        }

        $groupContents = file_get_contents($groupPath);
        $group = json_decode(is_string($groupContents) ? $groupContents : '', true);
        if (!is_array($group)) {
            $errors[] = 'group.json が不正な JSON です: ' . $groupKey;
            continue;
        }

        $resourceDirectory = $groupDirectory . '/resources';
        $resourceEntries = [];
        if (!is_dir($resourceDirectory)) {
            $errors[] = 'resources directory が見つかりません: ' . $groupKey;
        } else {
            $resourcePaths = glob($resourceDirectory . '/*.json');
            if ($resourcePaths === false) {
                $errors[] = 'resource file 一覧の取得に失敗しました: ' . $groupKey;
                $resourcePaths = [];
            }
            sort($resourcePaths, SORT_NATURAL);

            foreach ($resourcePaths as $resourcePath) {
                $resourceContents = file_get_contents($resourcePath);
                $resource = json_decode(is_string($resourceContents) ? $resourceContents : '', true);
                if (!is_array($resource)) {
                    $errors[] = 'resource JSON が不正です: ' . $resourcePath;
                    continue;
                }

                $resourceKey = trim((string) ($resource['resource_key'] ?? ''));
                if ($resourceKey === '') {
                    $errors[] = 'resource_key が空です: ' . $resourcePath;
                    continue;
                }

                $basename = basename($resourcePath, '.json');
                if ($basename !== $resourceKey) {
                    $warnings[] = 'resource file 名と resource_key が一致しません: ' . $resourcePath;
                }

                $resourceEntries[] = [
                    'resource_key' => $resourceKey,
                    'item' => $resource,
                    'file_path' => $resourcePath,
                ];
            }
        }

        $groupEntries[] = [
            'group_key' => $groupKey,
            'file_path' => $groupPath,
            'group' => $group,
            'resources' => $resourceEntries,
        ];
    }

    return [
        'ok' => $errors === [],
        'manifest' => $manifest,
        'groups' => $groupEntries,
        'errors' => $errors,
        'warnings' => $warnings,
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     root_path:string,
 *     manifest:array<string,mixed>,
 *     groups:list<array{
 *         group_key:string,
 *         file_path:string,
 *         group:array<string,mixed>,
 *         resources:list<array{
 *             resource_key:string,
 *             item:array<string,mixed>,
 *             file_path:string
 *         }>
 *     }>,
 *     catalog:array<string,mixed>,
 *     actual_counts:array{
 *         resources:int,
 *         groups:int,
 *         languages:int,
 *         group_languages:int,
 *         group_source_outputs:int,
 *         additional_group_assignments:int,
 *         captions:int
 *     },
 *     errors:list<string>,
 *     warnings:list<string>
 * }
 */
function app_language_resource_file_catalog_load_catalog(string $rootPath): array
{
    $loaded = app_language_resource_file_catalog_load_tree($rootPath);
    $tree = [
        'manifest' => $loaded['manifest'],
        'groups' => $loaded['groups'],
    ];
    $validation = app_language_resource_file_catalog_validate_tree($tree);
    $actualCounts = app_language_resource_file_catalog_actual_counts($tree);
    $manifestCounts = is_array($loaded['manifest']['counts'] ?? null) ? $loaded['manifest']['counts'] : [];

    $countErrors = [];
    foreach (
        [
            'resources' => 'resources',
            'groups' => 'groups',
            'languages' => 'locales',
            'group_languages' => 'group_languages',
            'group_source_outputs' => 'group_source_outputs',
            'additional_group_assignments' => 'additional_group_assignments',
            'captions' => 'captions',
        ] as $manifestKey => $summaryKey
    ) {
        $manifestValue = $manifestCounts[$manifestKey] ?? null;
        if ($manifestValue === null) {
            continue;
        }

        if ((int) $manifestValue !== (int) ($actualCounts[$manifestKey] ?? 0)) {
            $countErrors[] = 'manifest.counts.' . $manifestKey . ' と actual count が一致しません。';
        }
    }

    $errors = array_values(array_unique(array_merge(
        $loaded['errors'],
        $validation['errors'],
        $countErrors,
    )));
    $warnings = array_values(array_unique(array_merge(
        $loaded['warnings'],
        $validation['warnings'],
    )));

    return [
        'ok' => $errors === [],
        'root_path' => rtrim($rootPath, '/'),
        'manifest' => $loaded['manifest'],
        'groups' => $loaded['groups'],
        'catalog' => app_language_resource_file_catalog_to_legacy_catalog($tree),
        'actual_counts' => $actualCounts,
        'errors' => $errors,
        'warnings' => $warnings,
    ];
}
