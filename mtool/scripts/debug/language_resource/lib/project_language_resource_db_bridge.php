<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/app/database.php';
require_once dirname(__DIR__, 4) . '/app/project_language_resource_catalog_loader.php';

// DB canonical bridge helpers for migration/debug only. Runtime uses project_language_resource_catalog_loader.php.
function app_project_language_resource_pdo_current_schema(PDO $pdo): string
{
    static $cache = [];

    $objectId = spl_object_id($pdo);
    if (array_key_exists($objectId, $cache)) {
        return $cache[$objectId];
    }

    $schema = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $cache[$objectId] = is_string($schema) ? $schema : '';

    return $cache[$objectId];
}

function app_project_language_resource_pdo_table_exists(PDO $pdo, string $tableName): bool
{
    $schema = app_project_language_resource_pdo_current_schema($pdo);
    $normalizedTableName = trim($tableName);
    if ($schema === '' || $normalizedTableName === '') {
        return false;
    }

    static $cache = [];

    $cacheKey = $schema . ':' . $normalizedTableName;
    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    $statement = $pdo->prepare(
        'SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = :table_schema
          AND TABLE_NAME = :table_name
        LIMIT 1'
    );
    $statement->execute([
        ':table_schema' => $schema,
        ':table_name' => $normalizedTableName,
    ]);

    $cache[$cacheKey] = $statement->fetchColumn() !== false;

    return $cache[$cacheKey];
}

function app_project_language_resource_canonical_tables_available(PDO $pdo): bool
{
    return app_project_language_resource_pdo_table_exists($pdo, 'project_language_resource_groups')
        && app_project_language_resource_pdo_table_exists($pdo, 'project_language_resource_group_languages')
        && app_project_language_resource_pdo_table_exists($pdo, 'project_language_resource_group_source_outputs')
        && app_project_language_resource_pdo_table_exists($pdo, 'project_language_resource_languages')
        && app_project_language_resource_pdo_table_exists($pdo, 'project_language_resources')
        && app_project_language_resource_pdo_table_exists($pdo, 'project_language_resource_captions')
        && app_project_language_resource_pdo_table_exists($pdo, 'project_language_resource_additional_groups');
}

function app_project_language_resource_pdo_resolve_project_id(
    PDO $pdo,
    string $projectKey,
    int $fallbackProjectPid = 0,
): int {
    $normalizedProjectKey = trim($projectKey);
    if ($normalizedProjectKey !== '') {
        $statement = $pdo->prepare(
            'SELECT id
            FROM projects
            WHERE project_key = :project_key
            LIMIT 1'
        );
        $statement->execute([
            ':project_key' => $normalizedProjectKey,
        ]);

        $projectId = $statement->fetchColumn();
        if (is_numeric($projectId)) {
            return (int) $projectId;
        }
    }

    if ($fallbackProjectPid > 0) {
        return $fallbackProjectPid;
    }

    throw new RuntimeException('project が見つかりません。');
}

/**
 * @return list<string>
 */
function app_project_language_resource_normalize_source_of_truths(array $sourceOfTruths): array
{
    $normalized = [];
    foreach ($sourceOfTruths as $sourceOfTruth) {
        $candidate = trim((string) $sourceOfTruth);
        if ($candidate === '') {
            continue;
        }

        $normalized[$candidate] = $candidate;
    }

    return array_values($normalized);
}

/**
 * @param array<string,mixed> $params
 */
function app_project_language_resource_source_of_truth_clause(
    array $sourceOfTruths,
    array &$params,
    string $prefix = 'source_of_truth_',
): string {
    $normalized = app_project_language_resource_normalize_source_of_truths($sourceOfTruths);
    if ($normalized === []) {
        return '';
    }

    $placeholders = [];
    foreach ($normalized as $index => $sourceOfTruth) {
        $placeholder = ':' . $prefix . $index;
        $placeholders[] = $placeholder;
        $params[$placeholder] = $sourceOfTruth;
    }

    return ' AND source_of_truth IN (' . implode(', ', $placeholders) . ')';
}

/**
 * @return array{
 *     captions:int,
 *     additional_group_assignments:int,
 *     group_source_outputs:int,
 *     group_languages:int,
 *     resources:int,
 *     groups:int,
 *     languages:int
 * }
 */
function app_project_language_resource_canonical_table_counts(
    PDO $pdo,
    int $projectId,
    array $sourceOfTruths = [],
): array {
    $tableMap = [
        'captions' => 'project_language_resource_captions',
        'additional_group_assignments' => 'project_language_resource_additional_groups',
        'group_source_outputs' => 'project_language_resource_group_source_outputs',
        'group_languages' => 'project_language_resource_group_languages',
        'resources' => 'project_language_resources',
        'groups' => 'project_language_resource_groups',
        'languages' => 'project_language_resource_languages',
    ];

    $counts = [];
    foreach ($tableMap as $summaryKey => $tableName) {
        $params = [
            ':project_id' => $projectId,
        ];
        $sourceClause = app_project_language_resource_source_of_truth_clause(
            $sourceOfTruths,
            $params,
            $summaryKey . '_source_',
        );
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
            FROM ' . $tableName . '
            WHERE project_id = :project_id'
            . $sourceClause
        );
        $statement->execute($params);
        $counts[$summaryKey] = (int) ($statement->fetchColumn() ?? 0);
    }

    return $counts;
}

/**
 * @return array{
 *     captions:int,
 *     additional_group_assignments:int,
 *     group_source_outputs:int,
 *     group_languages:int,
 *     resources:int,
 *     groups:int,
 *     languages:int
 * }
 */
function app_project_language_resource_prune_canonical_rows_by_source_of_truths(
    PDO $pdo,
    int $projectId,
    array $sourceOfTruths,
): array {
    $normalizedSourceOfTruths = app_project_language_resource_normalize_source_of_truths($sourceOfTruths);
    $emptyCounts = [
        'captions' => 0,
        'additional_group_assignments' => 0,
        'group_source_outputs' => 0,
        'group_languages' => 0,
        'resources' => 0,
        'groups' => 0,
        'languages' => 0,
    ];
    if ($normalizedSourceOfTruths === []) {
        return $emptyCounts;
    }

    $tableMap = [
        'captions' => 'project_language_resource_captions',
        'additional_group_assignments' => 'project_language_resource_additional_groups',
        'group_source_outputs' => 'project_language_resource_group_source_outputs',
        'group_languages' => 'project_language_resource_group_languages',
        'resources' => 'project_language_resources',
        'groups' => 'project_language_resource_groups',
        'languages' => 'project_language_resource_languages',
    ];

    $deletedCounts = app_project_language_resource_canonical_table_counts(
        $pdo,
        $projectId,
        $normalizedSourceOfTruths,
    );

    foreach ($tableMap as $summaryKey => $tableName) {
        if (($deletedCounts[$summaryKey] ?? 0) <= 0) {
            continue;
        }

        $params = [
            ':project_id' => $projectId,
        ];
        $sourceClause = app_project_language_resource_source_of_truth_clause(
            $normalizedSourceOfTruths,
            $params,
            $summaryKey . '_delete_source_',
        );
        $statement = $pdo->prepare(
            'DELETE FROM ' . $tableName . '
            WHERE project_id = :project_id'
            . $sourceClause
        );
        $statement->execute($params);
    }

    return $deletedCounts;
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
/**
 * @param array{
 *     project_key?:string,
 *     project_pid?:int,
 *     source_dump_path?:string,
 *     generated_at?:string,
 *     resource_count?:int,
 *     group_count?:int,
 *     group_language_count?:int,
 *     group_source_output_count?:int,
 *     additional_group_assignment_count?:int,
 *     caption_count?:int,
 *     language_count?:int,
 *     resources?:list<array<string,mixed>>,
 *     groups?:list<array<string,mixed>>,
 *     group_languages?:list<array<string,mixed>>,
 *     group_source_outputs?:list<array<string,mixed>>,
 *     additional_group_assignments?:list<array<string,mixed>>,
 *     captions?:list<array<string,mixed>>,
 *     languages?:list<array<string,mixed>>
 * } $catalog
 * @return array{
 *     ok:bool,
 *     summary:array{
 *         project_key:string,
 *         project_id:int,
 *         source_of_truth:string,
 *         resource_count:int,
 *         group_count:int,
 *         group_language_count:int,
 *         group_source_output_count:int,
 *         additional_group_assignment_count:int,
 *         caption_count:int,
 *         language_count:int,
 *         pruned_counts:array{
 *             captions:int,
 *             additional_group_assignments:int,
 *             group_source_outputs:int,
 *             group_languages:int,
 *             resources:int,
 *             groups:int,
 *             languages:int
 *         }
 *     },
 *     error:string
 * }
 */
function app_project_language_resource_import_catalog_into_canonical(
    PDO $pdo,
    string $projectKey,
    array $catalog,
    string $sourceOfTruth,
    string $notes,
    int $fallbackProjectPid = 0,
    array $replaceSourceOfTruths = [],
): array {
    if (!app_project_language_resource_canonical_tables_available($pdo)) {
        return [
            'ok' => false,
            'summary' => [
                'project_key' => app_normalize_project_key($projectKey),
                'project_id' => 0,
                'source_of_truth' => trim($sourceOfTruth),
                'resource_count' => 0,
                'group_count' => 0,
                'group_language_count' => 0,
                'group_source_output_count' => 0,
                'additional_group_assignment_count' => 0,
                'caption_count' => 0,
                'language_count' => 0,
                'pruned_counts' => [
                    'captions' => 0,
                    'additional_group_assignments' => 0,
                    'group_source_outputs' => 0,
                    'group_languages' => 0,
                    'resources' => 0,
                    'groups' => 0,
                    'languages' => 0,
                ],
            ],
            'error' => 'canonical table が未作成です。',
        ];
    }

    $projectId = app_project_language_resource_pdo_resolve_project_id(
        $pdo,
        $projectKey,
        $fallbackProjectPid,
    );

    $summary = [
        'project_key' => app_normalize_project_key($projectKey),
        'project_id' => $projectId,
        'source_of_truth' => trim($sourceOfTruth),
        'resource_count' => count(is_array($catalog['resources'] ?? null) ? $catalog['resources'] : []),
        'group_count' => count(is_array($catalog['groups'] ?? null) ? $catalog['groups'] : []),
        'group_language_count' => count(is_array($catalog['group_languages'] ?? null) ? $catalog['group_languages'] : []),
        'group_source_output_count' => count(is_array($catalog['group_source_outputs'] ?? null) ? $catalog['group_source_outputs'] : []),
        'additional_group_assignment_count' => count(
            is_array($catalog['additional_group_assignments'] ?? null)
                ? $catalog['additional_group_assignments']
                : [],
        ),
        'caption_count' => count(is_array($catalog['captions'] ?? null) ? $catalog['captions'] : []),
        'language_count' => count(is_array($catalog['languages'] ?? null) ? $catalog['languages'] : []),
        'pruned_counts' => [
            'captions' => 0,
            'additional_group_assignments' => 0,
            'group_source_outputs' => 0,
            'group_languages' => 0,
            'resources' => 0,
            'groups' => 0,
            'languages' => 0,
        ],
    ];

    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }

    try {
        $normalizedReplaceSourceOfTruths = app_project_language_resource_normalize_source_of_truths($replaceSourceOfTruths);
        if ($normalizedReplaceSourceOfTruths !== []) {
            $summary['pruned_counts'] = app_project_language_resource_prune_canonical_rows_by_source_of_truths(
                $pdo,
                $projectId,
                $normalizedReplaceSourceOfTruths,
            );
        }

        $languageStatement = $pdo->prepare(
            'INSERT INTO project_language_resource_languages (
                project_id,
                legacy_language_pid,
                filename_suffix,
                template_key,
                is_default,
                caption,
                lang_for_cs,
                lang_for_android,
                lang_for_ios,
                lang_for_google,
                language_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :legacy_language_pid,
                :filename_suffix,
                :template_key,
                :is_default,
                :caption,
                :lang_for_cs,
                :lang_for_android,
                :lang_for_ios,
                :lang_for_google,
                :language_list_order,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                filename_suffix = IF(source_of_truth = \'manual\', filename_suffix, VALUES(filename_suffix)),
                template_key = IF(source_of_truth = \'manual\', template_key, VALUES(template_key)),
                is_default = IF(source_of_truth = \'manual\', is_default, VALUES(is_default)),
                caption = IF(source_of_truth = \'manual\', caption, VALUES(caption)),
                lang_for_cs = IF(source_of_truth = \'manual\', lang_for_cs, VALUES(lang_for_cs)),
                lang_for_android = IF(source_of_truth = \'manual\', lang_for_android, VALUES(lang_for_android)),
                lang_for_ios = IF(source_of_truth = \'manual\', lang_for_ios, VALUES(lang_for_ios)),
                lang_for_google = IF(source_of_truth = \'manual\', lang_for_google, VALUES(lang_for_google)),
                language_list_order = IF(source_of_truth = \'manual\', language_list_order, VALUES(language_list_order)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $languageOrder = 10;
        foreach ((is_array($catalog['languages'] ?? null) ? $catalog['languages'] : []) as $language) {
            if (!is_array($language)) {
                continue;
            }

            $languageStatement->execute([
                ':project_id' => $projectId,
                ':legacy_language_pid' => (int) ($language['legacy_language_pid'] ?? 0),
                ':filename_suffix' => (string) ($language['filename_suffix'] ?? ''),
                ':template_key' => (string) ($language['template_key'] ?? ''),
                ':is_default' => (int) ($language['is_default'] ?? 0),
                ':caption' => (string) ($language['caption'] ?? ''),
                ':lang_for_cs' => (string) ($language['lang_for_cs'] ?? ''),
                ':lang_for_android' => (string) ($language['lang_for_android'] ?? ''),
                ':lang_for_ios' => (string) ($language['lang_for_ios'] ?? ''),
                ':lang_for_google' => (string) ($language['lang_for_google'] ?? ''),
                ':language_list_order' => $languageOrder,
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);

            $languageOrder += 10;
        }

        $groupStatement = $pdo->prepare(
            'INSERT INTO project_language_resource_groups (
                project_id,
                legacy_group_pid,
                name,
                function_name_prefix,
                function_name_suffix,
                filename_suffix_for_php,
                filename_suffix,
                filename_for_xcode,
                group_list_order,
                last_modified_dt,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :legacy_group_pid,
                :name,
                :function_name_prefix,
                :function_name_suffix,
                :filename_suffix_for_php,
                :filename_suffix,
                :filename_for_xcode,
                :group_list_order,
                :last_modified_dt,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                name = IF(source_of_truth = \'manual\', name, VALUES(name)),
                function_name_prefix = IF(source_of_truth = \'manual\', function_name_prefix, VALUES(function_name_prefix)),
                function_name_suffix = IF(source_of_truth = \'manual\', function_name_suffix, VALUES(function_name_suffix)),
                filename_suffix_for_php = IF(source_of_truth = \'manual\', filename_suffix_for_php, VALUES(filename_suffix_for_php)),
                filename_suffix = IF(source_of_truth = \'manual\', filename_suffix, VALUES(filename_suffix)),
                filename_for_xcode = IF(source_of_truth = \'manual\', filename_for_xcode, VALUES(filename_for_xcode)),
                group_list_order = IF(source_of_truth = \'manual\', group_list_order, VALUES(group_list_order)),
                last_modified_dt = IF(source_of_truth = \'manual\', last_modified_dt, VALUES(last_modified_dt)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $groupOrder = 10;
        foreach ((is_array($catalog['groups'] ?? null) ? $catalog['groups'] : []) as $group) {
            if (!is_array($group)) {
                continue;
            }

            $lastModifiedDt = trim((string) ($group['last_modified_dt'] ?? ''));
            if ($lastModifiedDt === '') {
                $lastModifiedDt = date('Y-m-d H:i:s');
            }

            $groupStatement->execute([
                ':project_id' => $projectId,
                ':legacy_group_pid' => (int) ($group['legacy_group_pid'] ?? 0),
                ':name' => (string) ($group['name'] ?? ''),
                ':function_name_prefix' => (string) ($group['function_name_prefix'] ?? ''),
                ':function_name_suffix' => (string) ($group['function_name_suffix'] ?? ''),
                ':filename_suffix_for_php' => (string) ($group['filename_suffix_for_php'] ?? ''),
                ':filename_suffix' => (string) ($group['filename_suffix'] ?? ''),
                ':filename_for_xcode' => (string) ($group['filename_for_xcode'] ?? ''),
                ':group_list_order' => $groupOrder,
                ':last_modified_dt' => $lastModifiedDt,
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);

            $groupOrder += 10;
        }

        $groupIdStatement = $pdo->prepare(
            'SELECT id, legacy_group_pid
            FROM project_language_resource_groups
            WHERE project_id = :project_id'
        );
        $groupIdStatement->execute([
            ':project_id' => $projectId,
        ]);

        $groupIdsByLegacyPid = [];
        foreach ($groupIdStatement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $legacyGroupPid = (int) ($row['legacy_group_pid'] ?? 0);
            $groupId = (int) ($row['id'] ?? 0);
            if ($legacyGroupPid <= 0 || $groupId <= 0) {
                continue;
            }

            $groupIdsByLegacyPid[(string) $legacyGroupPid] = $groupId;
        }

        $groupLanguageStatement = $pdo->prepare(
            'INSERT INTO project_language_resource_group_languages (
                project_id,
                project_language_resource_group_id,
                legacy_group_language_pid,
                legacy_language_pid,
                relation_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :group_id,
                :legacy_group_language_pid,
                :legacy_language_pid,
                :relation_list_order,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                project_language_resource_group_id = IF(source_of_truth = \'manual\', project_language_resource_group_id, VALUES(project_language_resource_group_id)),
                legacy_language_pid = IF(source_of_truth = \'manual\', legacy_language_pid, VALUES(legacy_language_pid)),
                relation_list_order = IF(source_of_truth = \'manual\', relation_list_order, VALUES(relation_list_order)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $groupLanguageOrderByGroupPid = [];
        foreach ((is_array($catalog['group_languages'] ?? null) ? $catalog['group_languages'] : []) as $groupLanguage) {
            if (!is_array($groupLanguage)) {
                continue;
            }

            $legacyGroupPid = (int) ($groupLanguage['legacy_group_pid'] ?? 0);
            $groupId = $groupIdsByLegacyPid[(string) $legacyGroupPid] ?? 0;
            if ($groupId <= 0) {
                continue;
            }

            $orderKey = (string) $legacyGroupPid;
            $groupLanguageOrderByGroupPid[$orderKey] = ($groupLanguageOrderByGroupPid[$orderKey] ?? 0) + 10;

            $groupLanguageStatement->execute([
                ':project_id' => $projectId,
                ':group_id' => $groupId,
                ':legacy_group_language_pid' => (int) ($groupLanguage['legacy_group_language_pid'] ?? 0),
                ':legacy_language_pid' => (int) ($groupLanguage['legacy_language_pid'] ?? 0),
                ':relation_list_order' => $groupLanguageOrderByGroupPid[$orderKey],
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);
        }

        $groupSourceOutputStatement = $pdo->prepare(
            'INSERT INTO project_language_resource_group_source_outputs (
                project_id,
                project_language_resource_group_id,
                legacy_group_source_output_pid,
                legacy_project_source_output_pid,
                relation_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :group_id,
                :legacy_group_source_output_pid,
                :legacy_project_source_output_pid,
                :relation_list_order,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                project_language_resource_group_id = IF(source_of_truth = \'manual\', project_language_resource_group_id, VALUES(project_language_resource_group_id)),
                legacy_project_source_output_pid = IF(source_of_truth = \'manual\', legacy_project_source_output_pid, VALUES(legacy_project_source_output_pid)),
                relation_list_order = IF(source_of_truth = \'manual\', relation_list_order, VALUES(relation_list_order)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $groupSourceOutputOrderByGroupPid = [];
        foreach ((is_array($catalog['group_source_outputs'] ?? null) ? $catalog['group_source_outputs'] : []) as $groupSourceOutput) {
            if (!is_array($groupSourceOutput)) {
                continue;
            }

            $legacyGroupPid = (int) ($groupSourceOutput['legacy_group_pid'] ?? 0);
            $groupId = $groupIdsByLegacyPid[(string) $legacyGroupPid] ?? 0;
            if ($groupId <= 0) {
                continue;
            }

            $orderKey = (string) $legacyGroupPid;
            $groupSourceOutputOrderByGroupPid[$orderKey] = ($groupSourceOutputOrderByGroupPid[$orderKey] ?? 0) + 10;

            $groupSourceOutputStatement->execute([
                ':project_id' => $projectId,
                ':group_id' => $groupId,
                ':legacy_group_source_output_pid' => (int) ($groupSourceOutput['legacy_group_source_output_pid'] ?? 0),
                ':legacy_project_source_output_pid' => (int) ($groupSourceOutput['legacy_project_source_output_pid'] ?? 0),
                ':relation_list_order' => $groupSourceOutputOrderByGroupPid[$orderKey],
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);
        }

        $resourceStatement = $pdo->prepare(
            'INSERT INTO project_language_resources (
                project_id,
                project_language_resource_group_id,
                legacy_resource_pid,
                resource_key,
                key_for_update,
                sort_group,
                key_name,
                key_name_for_xcode,
                uwp_target_property,
                is_resource_fixed,
                use_default_if_caption_is_blank,
                resource_list_order,
                last_modified_dt,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :group_id,
                :legacy_resource_pid,
                :resource_key,
                :key_for_update,
                :sort_group,
                :key_name,
                :key_name_for_xcode,
                :uwp_target_property,
                :is_resource_fixed,
                :use_default_if_caption_is_blank,
                :resource_list_order,
                :last_modified_dt,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                project_language_resource_group_id = IF(source_of_truth = \'manual\', project_language_resource_group_id, VALUES(project_language_resource_group_id)),
                resource_key = IF(source_of_truth = \'manual\', resource_key, VALUES(resource_key)),
                key_for_update = IF(source_of_truth = \'manual\', key_for_update, VALUES(key_for_update)),
                sort_group = IF(source_of_truth = \'manual\', sort_group, VALUES(sort_group)),
                key_name = IF(source_of_truth = \'manual\', key_name, VALUES(key_name)),
                key_name_for_xcode = IF(source_of_truth = \'manual\', key_name_for_xcode, VALUES(key_name_for_xcode)),
                uwp_target_property = IF(source_of_truth = \'manual\', uwp_target_property, VALUES(uwp_target_property)),
                is_resource_fixed = IF(source_of_truth = \'manual\', is_resource_fixed, VALUES(is_resource_fixed)),
                use_default_if_caption_is_blank = IF(source_of_truth = \'manual\', use_default_if_caption_is_blank, VALUES(use_default_if_caption_is_blank)),
                resource_list_order = IF(source_of_truth = \'manual\', resource_list_order, VALUES(resource_list_order)),
                last_modified_dt = IF(source_of_truth = \'manual\', last_modified_dt, VALUES(last_modified_dt)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $resourceOrderByGroupPid = [];
        foreach ((is_array($catalog['resources'] ?? null) ? $catalog['resources'] : []) as $resource) {
            if (!is_array($resource)) {
                continue;
            }

            $legacyGroupPid = (int) ($resource['legacy_group_pid'] ?? 0);
            $groupId = $groupIdsByLegacyPid[(string) $legacyGroupPid] ?? 0;
            if ($groupId <= 0) {
                continue;
            }

            $orderKey = (string) $legacyGroupPid;
            $resourceOrderByGroupPid[$orderKey] = ($resourceOrderByGroupPid[$orderKey] ?? 0) + 10;

            $lastModifiedDt = trim((string) ($resource['last_modified_dt'] ?? ''));
            if ($lastModifiedDt === '') {
                $lastModifiedDt = date('Y-m-d H:i:s');
            }

            $resourceStatement->execute([
                ':project_id' => $projectId,
                ':group_id' => $groupId,
                ':legacy_resource_pid' => (int) ($resource['legacy_resource_pid'] ?? 0),
                ':resource_key' => (string) ($resource['resource_key'] ?? ''),
                ':key_for_update' => (string) ($resource['key_for_update'] ?? ''),
                ':sort_group' => (string) ($resource['sort_group'] ?? ''),
                ':key_name' => (string) ($resource['key_name'] ?? ''),
                ':key_name_for_xcode' => (string) ($resource['key_name_for_xcode'] ?? ''),
                ':uwp_target_property' => (string) ($resource['uwp_target_property'] ?? ''),
                ':is_resource_fixed' => (int) ($resource['is_resource_fixed'] ?? 0),
                ':use_default_if_caption_is_blank' => (int) ($resource['use_default_if_caption_is_blank'] ?? 1),
                ':resource_list_order' => $resourceOrderByGroupPid[$orderKey],
                ':last_modified_dt' => $lastModifiedDt,
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);
        }

        $resourceIdStatement = $pdo->prepare(
            'SELECT id, legacy_resource_pid
            FROM project_language_resources
            WHERE project_id = :project_id'
        );
        $resourceIdStatement->execute([
            ':project_id' => $projectId,
        ]);

        $resourceIdsByLegacyPid = [];
        foreach ($resourceIdStatement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $legacyResourcePid = (int) ($row['legacy_resource_pid'] ?? 0);
            $resourceId = (int) ($row['id'] ?? 0);
            if ($legacyResourcePid <= 0 || $resourceId <= 0) {
                continue;
            }

            $resourceIdsByLegacyPid[(string) $legacyResourcePid] = $resourceId;
        }

        $captionStatement = $pdo->prepare(
            'INSERT INTO project_language_resource_captions (
                project_id,
                project_language_resource_id,
                project_language_resource_group_id,
                legacy_caption_pid,
                legacy_language_pid,
                caption,
                caption_auto_translated,
                caption_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :resource_id,
                :group_id,
                :legacy_caption_pid,
                :legacy_language_pid,
                :caption,
                :caption_auto_translated,
                :caption_list_order,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                project_language_resource_id = IF(source_of_truth = \'manual\', project_language_resource_id, VALUES(project_language_resource_id)),
                project_language_resource_group_id = IF(source_of_truth = \'manual\', project_language_resource_group_id, VALUES(project_language_resource_group_id)),
                legacy_language_pid = IF(source_of_truth = \'manual\', legacy_language_pid, VALUES(legacy_language_pid)),
                caption = IF(source_of_truth = \'manual\', caption, VALUES(caption)),
                caption_auto_translated = IF(source_of_truth = \'manual\', caption_auto_translated, VALUES(caption_auto_translated)),
                caption_list_order = IF(source_of_truth = \'manual\', caption_list_order, VALUES(caption_list_order)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $captionOrderByResourceGroup = [];
        foreach ((is_array($catalog['captions'] ?? null) ? $catalog['captions'] : []) as $caption) {
            if (!is_array($caption)) {
                continue;
            }

            $legacyResourcePid = (int) ($caption['legacy_resource_pid'] ?? 0);
            $legacyGroupPid = (int) ($caption['legacy_group_pid'] ?? 0);
            $resourceId = $resourceIdsByLegacyPid[(string) $legacyResourcePid] ?? 0;
            $groupId = $groupIdsByLegacyPid[(string) $legacyGroupPid] ?? 0;
            if ($resourceId <= 0 || $groupId <= 0) {
                continue;
            }

            $orderKey = $legacyResourcePid . ':' . $legacyGroupPid;
            $captionOrderByResourceGroup[$orderKey] = ($captionOrderByResourceGroup[$orderKey] ?? 0) + 10;

            $captionStatement->execute([
                ':project_id' => $projectId,
                ':resource_id' => $resourceId,
                ':group_id' => $groupId,
                ':legacy_caption_pid' => (int) ($caption['legacy_caption_pid'] ?? 0),
                ':legacy_language_pid' => (int) ($caption['legacy_language_pid'] ?? 0),
                ':caption' => (string) ($caption['caption'] ?? ''),
                ':caption_auto_translated' => (string) ($caption['caption_auto_translated'] ?? ''),
                ':caption_list_order' => $captionOrderByResourceGroup[$orderKey],
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);
        }

        $additionalGroupStatement = $pdo->prepare(
            'INSERT INTO project_language_resource_additional_groups (
                project_id,
                project_language_resource_id,
                project_language_resource_group_id,
                legacy_assignment_pid,
                relation_list_order,
                notes,
                source_of_truth
            ) VALUES (
                :project_id,
                :resource_id,
                :group_id,
                :legacy_assignment_pid,
                :relation_list_order,
                :notes,
                :source_of_truth
            )
            ON DUPLICATE KEY UPDATE
                project_language_resource_id = IF(source_of_truth = \'manual\', project_language_resource_id, VALUES(project_language_resource_id)),
                project_language_resource_group_id = IF(source_of_truth = \'manual\', project_language_resource_group_id, VALUES(project_language_resource_group_id)),
                relation_list_order = IF(source_of_truth = \'manual\', relation_list_order, VALUES(relation_list_order)),
                notes = IF(source_of_truth = \'manual\', notes, VALUES(notes)),
                source_of_truth = IF(source_of_truth = \'manual\', source_of_truth, VALUES(source_of_truth)),
                updated_at = IF(source_of_truth = \'manual\', updated_at, CURRENT_TIMESTAMP)'
        );

        $additionalGroupOrderByResource = [];
        foreach ((is_array($catalog['additional_group_assignments'] ?? null) ? $catalog['additional_group_assignments'] : []) as $assignment) {
            if (!is_array($assignment)) {
                continue;
            }

            $legacyResourcePid = (int) ($assignment['legacy_resource_pid'] ?? 0);
            $legacyGroupPid = (int) ($assignment['legacy_group_pid'] ?? 0);
            $resourceId = $resourceIdsByLegacyPid[(string) $legacyResourcePid] ?? 0;
            $groupId = $groupIdsByLegacyPid[(string) $legacyGroupPid] ?? 0;
            if ($resourceId <= 0 || $groupId <= 0) {
                continue;
            }

            $orderKey = (string) $legacyResourcePid;
            $additionalGroupOrderByResource[$orderKey] = ($additionalGroupOrderByResource[$orderKey] ?? 0) + 10;

            $additionalGroupStatement->execute([
                ':project_id' => $projectId,
                ':resource_id' => $resourceId,
                ':group_id' => $groupId,
                ':legacy_assignment_pid' => (int) ($assignment['legacy_assignment_pid'] ?? 0),
                ':relation_list_order' => $additionalGroupOrderByResource[$orderKey],
                ':notes' => $notes,
                ':source_of_truth' => $sourceOfTruth,
            ]);
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }

        return [
            'ok' => true,
            'summary' => $summary,
            'error' => '',
        ];
    } catch (Throwable $throwable) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'ok' => false,
            'summary' => $summary,
            'error' => $throwable->getMessage(),
        ];
    }
}
