#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/database.php';
require_once dirname(__DIR__, 3) . '/app/domain_validation.php';
require_once dirname(__DIR__, 3) . '/app/project_language_resource_catalog_loader.php';
require_once __DIR__ . '/lib/project_language_resource_db_bridge.php';
require_once dirname(__DIR__, 3) . '/app/project_language_resource_route_common.php';

function app_cli_lang_res_retire_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/debug/language_resource/retire_project_language_resource_db_rows.php [options]

Options:
  --project-key=KEY             target project key
  --legacy-project-pid=PID      legacy Project.PID fallback (default: 1 for MTOOL, else 0)
  --row-limit=N                 max manual residual rows to inspect per table (default: 50)
  --apply                       delete project-scoped LanguageResource canonical rows
  --force                       allow apply even when manual rows are not classified as safe
  --db-host=HOST                APP_DB_HOST override
  --db-port=PORT                APP_DB_PORT override
  --db-name=NAME                APP_DB_NAME override
  --db-user=USER                APP_DB_USER override
  --db-password=PASSWORD        APP_DB_PASSWORD override
  --config-db-host=HOST         APP_CONFIG_DB_HOST override
  --config-db-port=PORT         APP_CONFIG_DB_PORT override
  --config-db-name=NAME         APP_CONFIG_DB_NAME override
  --config-db-user=USER         APP_CONFIG_DB_USER override
  --config-db-password=PASSWORD APP_CONFIG_DB_PASSWORD override
  --help                        show this help
TEXT;
}

function app_cli_lang_res_retire_apply_env(array $overrides): void
{
    foreach ($overrides as $key => $value) {
        if (!is_string($key) || !is_string($value)) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

/**
 * @param list<string> $argv
 * @return array{
 *     ok:bool,
 *     help:bool,
 *     project_key:string,
 *     legacy_project_pid:int,
 *     row_limit:int,
 *     apply:bool,
 *     force:bool,
 *     db_host:string,
 *     db_port:string,
 *     db_name:string,
 *     db_user:string,
 *     db_password:string,
 *     config_db_host:string,
 *     config_db_port:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string,
 *     error:string
 * }
 */
function app_cli_lang_res_retire_parse_args(array $argv): array
{
    $parsed = [
        'project_key' => '',
        'legacy_project_pid' => 0,
        'row_limit' => 50,
        'apply' => false,
        'force' => false,
        'db_host' => getenv('APP_DB_HOST') ?: '',
        'db_port' => getenv('APP_DB_PORT') ?: '',
        'db_name' => getenv('APP_DB_NAME') ?: '',
        'db_user' => getenv('APP_DB_USER') ?: '',
        'db_password' => getenv('APP_DB_PASSWORD') ?: '',
        'config_db_host' => getenv('APP_CONFIG_DB_HOST') ?: '',
        'config_db_port' => getenv('APP_CONFIG_DB_PORT') ?: '',
        'config_db_name' => getenv('APP_CONFIG_DB_NAME') ?: '',
        'config_db_user' => getenv('APP_CONFIG_DB_USER') ?: '',
        'config_db_password' => getenv('APP_CONFIG_DB_PASSWORD') ?: '',
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            return [
                'ok' => true,
                'help' => true,
                'project_key' => '',
                'legacy_project_pid' => 0,
                'row_limit' => 0,
                'apply' => false,
                'force' => false,
                'db_host' => '',
                'db_port' => '',
                'db_name' => '',
                'db_user' => '',
                'db_password' => '',
                'config_db_host' => '',
                'config_db_port' => '',
                'config_db_name' => '',
                'config_db_user' => '',
                'config_db_password' => '',
                'error' => '',
            ];
        }

        if ($argument === '--apply') {
            $parsed['apply'] = true;
            continue;
        }

        if ($argument === '--force') {
            $parsed['force'] = true;
            continue;
        }

        if (!str_starts_with($argument, '--') || !str_contains($argument, '=')) {
            return [
                'ok' => false,
                'help' => false,
                'project_key' => '',
                'legacy_project_pid' => 0,
                'row_limit' => 0,
                'apply' => false,
                'force' => false,
                'db_host' => '',
                'db_port' => '',
                'db_name' => '',
                'db_user' => '',
                'db_password' => '',
                'config_db_host' => '',
                'config_db_port' => '',
                'config_db_name' => '',
                'config_db_user' => '',
                'config_db_password' => '',
                'error' => 'unsupported argument: ' . $argument,
            ];
        }

        [$name, $value] = explode('=', substr($argument, 2), 2);
        $normalizedValue = trim($value);

        switch ($name) {
            case 'project-key':
                $parsed['project_key'] = app_normalize_project_key($normalizedValue);
                break;
            case 'legacy-project-pid':
                $parsed['legacy_project_pid'] = ctype_digit($normalizedValue)
                    ? (int) $normalizedValue
                    : -1;
                break;
            case 'row-limit':
                $parsed['row_limit'] = ctype_digit($normalizedValue)
                    ? (int) $normalizedValue
                    : -1;
                break;
            case 'db-host':
                $parsed['db_host'] = $normalizedValue;
                break;
            case 'db-port':
                $parsed['db_port'] = $normalizedValue;
                break;
            case 'db-name':
                $parsed['db_name'] = $normalizedValue;
                break;
            case 'db-user':
                $parsed['db_user'] = $normalizedValue;
                break;
            case 'db-password':
                $parsed['db_password'] = $value;
                break;
            case 'config-db-host':
                $parsed['config_db_host'] = $normalizedValue;
                break;
            case 'config-db-port':
                $parsed['config_db_port'] = $normalizedValue;
                break;
            case 'config-db-name':
                $parsed['config_db_name'] = $normalizedValue;
                break;
            case 'config-db-user':
                $parsed['config_db_user'] = $normalizedValue;
                break;
            case 'config-db-password':
                $parsed['config_db_password'] = $value;
                break;
            default:
                return [
                    'ok' => false,
                    'help' => false,
                    'project_key' => '',
                    'legacy_project_pid' => 0,
                    'row_limit' => 0,
                    'apply' => false,
                    'force' => false,
                    'db_host' => '',
                    'db_port' => '',
                    'db_name' => '',
                    'db_user' => '',
                    'db_password' => '',
                    'config_db_host' => '',
                    'config_db_port' => '',
                    'config_db_name' => '',
                    'config_db_user' => '',
                    'config_db_password' => '',
                    'error' => 'unsupported option: --' . $name,
                ];
        }
    }

    if ($parsed['project_key'] === '' || !app_project_key_is_valid($parsed['project_key'])) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'row_limit' => 0,
            'apply' => false,
            'force' => false,
            'db_host' => '',
            'db_port' => '',
            'db_name' => '',
            'db_user' => '',
            'db_password' => '',
            'config_db_host' => '',
            'config_db_port' => '',
            'config_db_name' => '',
            'config_db_user' => '',
            'config_db_password' => '',
            'error' => 'valid --project-key=... is required.',
        ];
    }

    if ($parsed['legacy_project_pid'] < 0) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'row_limit' => 0,
            'apply' => false,
            'force' => false,
            'db_host' => '',
            'db_port' => '',
            'db_name' => '',
            'db_user' => '',
            'db_password' => '',
            'config_db_host' => '',
            'config_db_port' => '',
            'config_db_name' => '',
            'config_db_user' => '',
            'config_db_password' => '',
            'error' => '--legacy-project-pid must be a positive integer or 0.',
        ];
    }

    if ($parsed['legacy_project_pid'] === 0 && $parsed['project_key'] === 'MTOOL') {
        $parsed['legacy_project_pid'] = 1;
    }

    if ($parsed['row_limit'] <= 0) {
        return [
            'ok' => false,
            'help' => false,
            'project_key' => '',
            'legacy_project_pid' => 0,
            'row_limit' => 0,
            'apply' => false,
            'force' => false,
            'db_host' => '',
            'db_port' => '',
            'db_name' => '',
            'db_user' => '',
            'db_password' => '',
            'config_db_host' => '',
            'config_db_port' => '',
            'config_db_name' => '',
            'config_db_user' => '',
            'config_db_password' => '',
            'error' => '--row-limit must be a positive integer.',
        ];
    }

    return [
        'ok' => true,
        'help' => false,
        'project_key' => $parsed['project_key'],
        'legacy_project_pid' => $parsed['legacy_project_pid'],
        'row_limit' => $parsed['row_limit'],
        'apply' => $parsed['apply'],
        'force' => $parsed['force'],
        'db_host' => $parsed['db_host'],
        'db_port' => $parsed['db_port'],
        'db_name' => $parsed['db_name'],
        'db_user' => $parsed['db_user'],
        'db_password' => $parsed['db_password'],
        'config_db_host' => $parsed['config_db_host'],
        'config_db_port' => $parsed['config_db_port'],
        'config_db_name' => $parsed['config_db_name'],
        'config_db_user' => $parsed['config_db_user'],
        'config_db_password' => $parsed['config_db_password'],
        'error' => '',
    ];
}

/**
 * @param array{
 *     db_host:string,
 *     db_port:string,
 *     db_name:string,
 *     db_user:string,
 *     db_password:string,
 *     config_db_host:string,
 *     config_db_port:string,
 *     config_db_name:string,
 *     config_db_user:string,
 *     config_db_password:string
 * } $parsed
 * @return array<string,string>
 */
function app_cli_lang_res_retire_app_env(array $parsed): array
{
    $env = [
        'APP_SITE' => 'admin',
        'APP_AUTH_MODE' => 'stub',
        'APP_AUTH_STUB_USER' => 'admin',
        'APP_AUTH_STUB_PASSWORD' => getenv('APP_AUTH_STUB_PASSWORD') ?: '',
        'APP_AUTH_STUB_NAME' => 'Language Resource DB Retirement',
        'APP_AUTH_STUB_ROLES' => 'admin,config',
    ];

    foreach ([
        'APP_DB_HOST' => $parsed['db_host'],
        'APP_DB_PORT' => $parsed['db_port'],
        'APP_DB_NAME' => $parsed['db_name'],
        'APP_DB_USER' => $parsed['db_user'],
        'APP_DB_PASSWORD' => $parsed['db_password'],
    ] as $key => $value) {
        if ($value !== '') {
            $env[$key] = $value;
        }
    }

    $configFallbacks = [
        'APP_CONFIG_DB_HOST' => $parsed['config_db_host'] !== '' ? $parsed['config_db_host'] : $parsed['db_host'],
        'APP_CONFIG_DB_PORT' => $parsed['config_db_port'] !== '' ? $parsed['config_db_port'] : $parsed['db_port'],
        'APP_CONFIG_DB_NAME' => $parsed['config_db_name'] !== '' ? $parsed['config_db_name'] : $parsed['db_name'],
        'APP_CONFIG_DB_USER' => $parsed['config_db_user'] !== '' ? $parsed['config_db_user'] : $parsed['db_user'],
        'APP_CONFIG_DB_PASSWORD' => $parsed['config_db_password'] !== '' ? $parsed['config_db_password'] : $parsed['db_password'],
    ];
    foreach ($configFallbacks as $key => $value) {
        if ($value !== '') {
            $env[$key] = $value;
        }
    }

    return $env;
}

/**
 * @return array<string,array<string,mixed>>
 */
function app_cli_lang_res_retire_resources_by_legacy_pid(array $resources): array
{
    $mapped = [];
    foreach ($resources as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $legacyResourcePid = (int) ($resource['legacy_resource_pid'] ?? 0);
        if ($legacyResourcePid <= 0) {
            continue;
        }

        $mapped[(string) $legacyResourcePid] = $resource;
    }

    return $mapped;
}

/**
 * @return list<string>
 */
function app_cli_lang_res_retire_diff_assoc(array $left, array $right, array $keys): array
{
    $diffs = [];
    foreach ($keys as $key) {
        if ((string) ($left[$key] ?? null) === (string) ($right[$key] ?? null)) {
            continue;
        }

        $diffs[] = $key;
    }

    return $diffs;
}

/**
 * @return array{
 *     status:string,
 *     diff_fields:list<string>
 * }
 */
function app_cli_lang_res_retire_resource_file_match(
    array $dbRow,
    array $fileResourcesByLegacyPid,
): array {
    $legacyResourcePid = (int) ($dbRow['legacy_resource_pid'] ?? 0);
    $fileResource = $fileResourcesByLegacyPid[(string) $legacyResourcePid] ?? null;
    if (!is_array($fileResource)) {
        return [
            'status' => 'missing-in-file',
            'diff_fields' => [],
        ];
    }

    $dbComparable = [
        'legacy_group_pid' => (int) ($dbRow['legacy_group_pid'] ?? 0),
        'resource_key' => (string) ($dbRow['resource_key'] ?? ''),
        'key_for_update' => (string) ($dbRow['key_for_update'] ?? ''),
        'sort_group' => (string) ($dbRow['sort_group'] ?? ''),
        'key_name' => (string) ($dbRow['key_name'] ?? ''),
        'key_name_for_xcode' => (string) ($dbRow['key_name_for_xcode'] ?? ''),
        'uwp_target_property' => (string) ($dbRow['uwp_target_property'] ?? ''),
        'is_resource_fixed' => (int) ($dbRow['is_resource_fixed'] ?? 0),
        'use_default_if_caption_is_blank' => (int) ($dbRow['use_default_if_caption_is_blank'] ?? 0),
    ];
    $fileComparable = [
        'legacy_group_pid' => (int) ($fileResource['legacy_group_pid'] ?? 0),
        'resource_key' => (string) ($fileResource['resource_key'] ?? ''),
        'key_for_update' => (string) ($fileResource['key_for_update'] ?? ''),
        'sort_group' => (string) ($fileResource['sort_group'] ?? ''),
        'key_name' => (string) ($fileResource['key_name'] ?? ''),
        'key_name_for_xcode' => (string) ($fileResource['key_name_for_xcode'] ?? ''),
        'uwp_target_property' => (string) ($fileResource['uwp_target_property'] ?? ''),
        'is_resource_fixed' => (int) ($fileResource['is_resource_fixed'] ?? 0),
        'use_default_if_caption_is_blank' => (int) ($fileResource['use_default_if_caption_is_blank'] ?? 0),
    ];
    $diffFields = app_cli_lang_res_retire_diff_assoc($dbComparable, $fileComparable, array_keys($dbComparable));

    return [
        'status' => $diffFields === [] ? 'exact-match' : 'diff',
        'diff_fields' => $diffFields,
    ];
}

/**
 * @param array<string,array<string,array<string,mixed>>> $captionsByResourcePid
 * @return array{
 *     status:string,
 *     diff_fields:list<string>
 * }
 */
function app_cli_lang_res_retire_caption_file_match(
    array $dbRow,
    array $captionsByResourcePid,
): array {
    $legacyResourcePid = (int) ($dbRow['legacy_resource_pid'] ?? 0);
    $legacyLanguagePid = (int) ($dbRow['legacy_language_pid'] ?? 0);
    $fileCaption = $captionsByResourcePid[(string) $legacyResourcePid][(string) $legacyLanguagePid] ?? null;
    if (!is_array($fileCaption)) {
        return [
            'status' => 'missing-in-file',
            'diff_fields' => [],
        ];
    }

    $dbComparable = [
        'legacy_group_pid' => (int) ($dbRow['legacy_group_pid'] ?? 0),
        'caption' => (string) ($dbRow['caption'] ?? ''),
        'caption_auto_translated' => (string) ($dbRow['caption_auto_translated'] ?? ''),
    ];
    $fileComparable = [
        'legacy_group_pid' => (int) ($fileCaption['legacy_group_pid'] ?? 0),
        'caption' => (string) ($fileCaption['caption'] ?? ''),
        'caption_auto_translated' => (string) ($fileCaption['caption_auto_translated'] ?? ''),
    ];
    $diffFields = app_cli_lang_res_retire_diff_assoc($dbComparable, $fileComparable, array_keys($dbComparable));

    return [
        'status' => $diffFields === [] ? 'exact-match' : 'diff',
        'diff_fields' => $diffFields,
    ];
}

/**
 * @param array<string,mixed> $catalog
 * @return array{
 *     resources:list<array<string,mixed>>,
 *     captions:list<array<string,mixed>>,
 *     safe:bool,
 *     blockers:list<string>
 * }
 */
function app_cli_lang_res_retire_manual_rows_report(
    PDO $pdo,
    int $projectId,
    int $rowLimit,
    array $catalog,
): array {
    $groupsByPid = app_project_language_resource_groups_by_pid(
        is_array($catalog['groups'] ?? null) ? $catalog['groups'] : [],
    );
    $languagesByPid = app_project_language_resource_languages_by_pid(
        is_array($catalog['languages'] ?? null) ? $catalog['languages'] : [],
    );
    $fileResourcesByLegacyPid = app_cli_lang_res_retire_resources_by_legacy_pid(
        is_array($catalog['resources'] ?? null) ? $catalog['resources'] : [],
    );
    $captionsByResourcePid = app_project_language_resource_captions_by_resource_pid(
        is_array($catalog['captions'] ?? null) ? $catalog['captions'] : [],
    );

    $resourceStatement = $pdo->prepare(
        'SELECT
            r.id,
            r.legacy_resource_pid,
            r.resource_key,
            r.key_for_update,
            r.sort_group,
            r.key_name,
            r.key_name_for_xcode,
            r.uwp_target_property,
            r.is_resource_fixed,
            r.use_default_if_caption_is_blank,
            r.last_modified_dt,
            r.notes,
            r.source_of_truth,
            g.legacy_group_pid,
            g.name AS group_name
        FROM project_language_resources r
        LEFT JOIN project_language_resource_groups g
            ON g.id = r.project_language_resource_group_id
        WHERE r.project_id = :project_id
          AND r.source_of_truth = :source_of_truth
        ORDER BY r.id
        LIMIT ' . (int) $rowLimit
    );
    $resourceStatement->execute([
        ':project_id' => $projectId,
        ':source_of_truth' => 'manual',
    ]);

    $resources = [];
    $blockers = [];
    foreach ($resourceStatement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $resource = [
            'id' => (int) ($row['id'] ?? 0),
            'legacy_resource_pid' => (int) ($row['legacy_resource_pid'] ?? 0),
            'legacy_group_pid' => (int) ($row['legacy_group_pid'] ?? 0),
            'group_name' => (string) ($row['group_name'] ?? ''),
            'resource_key' => (string) ($row['resource_key'] ?? ''),
            'key_for_update' => (string) ($row['key_for_update'] ?? ''),
            'sort_group' => (string) ($row['sort_group'] ?? ''),
            'key_name' => (string) ($row['key_name'] ?? ''),
            'key_name_for_xcode' => (string) ($row['key_name_for_xcode'] ?? ''),
            'uwp_target_property' => (string) ($row['uwp_target_property'] ?? ''),
            'is_resource_fixed' => (int) ($row['is_resource_fixed'] ?? 0),
            'use_default_if_caption_is_blank' => (int) ($row['use_default_if_caption_is_blank'] ?? 0),
            'last_modified_dt' => (string) ($row['last_modified_dt'] ?? ''),
            'notes' => (string) ($row['notes'] ?? ''),
            'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        ];

        $fileMatch = app_cli_lang_res_retire_resource_file_match(
            $resource,
            $fileResourcesByLegacyPid,
        );
        $allowedDiffFields = ['key_for_update'];
        $disallowedDiffFields = array_values(array_diff($fileMatch['diff_fields'], $allowedDiffFields));
        $safeToDrop = $fileMatch['status'] === 'exact-match'
            || ($fileMatch['status'] === 'diff' && $disallowedDiffFields === []);

        $resource['file_match'] = $fileMatch;
        $resource['safe_to_drop'] = $safeToDrop;
        if (!$safeToDrop) {
            $blockers[] = 'manual resource row is not mirrored in file canonical: '
                . $resource['resource_key']
                . ' (id=' . $resource['id'] . ')';
        }

        $resources[] = $resource;
    }

    $captionStatement = $pdo->prepare(
        'SELECT
            c.id,
            c.legacy_caption_pid,
            c.legacy_language_pid,
            c.caption,
            c.caption_auto_translated,
            c.notes,
            c.source_of_truth,
            r.legacy_resource_pid,
            r.resource_key,
            g.legacy_group_pid,
            g.name AS group_name
        FROM project_language_resource_captions c
        LEFT JOIN project_language_resources r
            ON r.id = c.project_language_resource_id
        LEFT JOIN project_language_resource_groups g
            ON g.id = c.project_language_resource_group_id
        WHERE c.project_id = :project_id
          AND c.source_of_truth = :source_of_truth
        ORDER BY c.id
        LIMIT ' . (int) $rowLimit
    );
    $captionStatement->execute([
        ':project_id' => $projectId,
        ':source_of_truth' => 'manual',
    ]);

    $captions = [];
    foreach ($captionStatement->fetchAll() as $row) {
        if (!is_array($row)) {
            continue;
        }

        $legacyLanguagePid = (int) ($row['legacy_language_pid'] ?? 0);
        $language = $languagesByPid[(string) $legacyLanguagePid] ?? [];
        $caption = [
            'id' => (int) ($row['id'] ?? 0),
            'legacy_caption_pid' => (int) ($row['legacy_caption_pid'] ?? 0),
            'legacy_resource_pid' => (int) ($row['legacy_resource_pid'] ?? 0),
            'resource_key' => (string) ($row['resource_key'] ?? ''),
            'legacy_group_pid' => (int) ($row['legacy_group_pid'] ?? 0),
            'group_name' => (string) ($row['group_name'] ?? ''),
            'legacy_language_pid' => $legacyLanguagePid,
            'locale_hint' => (string) ($language['filename_suffix'] ?? ''),
            'caption' => (string) ($row['caption'] ?? ''),
            'caption_auto_translated' => (string) ($row['caption_auto_translated'] ?? ''),
            'notes' => (string) ($row['notes'] ?? ''),
            'source_of_truth' => (string) ($row['source_of_truth'] ?? ''),
        ];

        $fileMatch = app_cli_lang_res_retire_caption_file_match(
            $caption,
            $captionsByResourcePid,
        );
        $safeToDrop = $fileMatch['status'] === 'exact-match';

        $caption['file_match'] = $fileMatch;
        $caption['safe_to_drop'] = $safeToDrop;
        if (!$safeToDrop) {
            $blockers[] = 'manual caption row is not mirrored in file canonical: '
                . $caption['resource_key']
                . ' / ' . $caption['locale_hint']
                . ' (id=' . $caption['id'] . ')';
        }

        $captions[] = $caption;
    }

    return [
        'resources' => $resources,
        'captions' => $captions,
        'safe' => $blockers === [],
        'blockers' => $blockers,
    ];
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
function app_cli_lang_res_retire_delete_project_rows(PDO $pdo, int $projectId): array
{
    $tableMap = [
        'captions' => 'project_language_resource_captions',
        'additional_group_assignments' => 'project_language_resource_additional_groups',
        'group_source_outputs' => 'project_language_resource_group_source_outputs',
        'group_languages' => 'project_language_resource_group_languages',
        'resources' => 'project_language_resources',
        'groups' => 'project_language_resource_groups',
        'languages' => 'project_language_resource_languages',
    ];

    $deletedCounts = app_project_language_resource_canonical_table_counts($pdo, $projectId);
    foreach ($tableMap as $summaryKey => $tableName) {
        if (($deletedCounts[$summaryKey] ?? 0) <= 0) {
            continue;
        }

        $statement = $pdo->prepare(
            'DELETE FROM ' . $tableName . '
            WHERE project_id = :project_id'
        );
        $statement->execute([
            ':project_id' => $projectId,
        ]);
    }

    return $deletedCounts;
}

$parsed = app_cli_lang_res_retire_parse_args($argv);
if ($parsed['help']) {
    fwrite(STDOUT, app_cli_lang_res_retire_usage() . PHP_EOL);
    exit(0);
}

if (!$parsed['ok']) {
    fwrite(STDERR, $parsed['error'] . PHP_EOL . PHP_EOL . app_cli_lang_res_retire_usage() . PHP_EOL);
    exit(64);
}

try {
    app_cli_lang_res_retire_apply_env(
        app_cli_lang_res_retire_app_env($parsed),
    );

    $app = app_bootstrap();
    $catalogResult = app_fetch_project_language_resource_catalog(
        $app,
        $parsed['project_key'],
        $parsed['legacy_project_pid'],
    );
    if (!$catalogResult['ok'] || !is_array($catalogResult['item'] ?? null)) {
        throw new RuntimeException(
            $catalogResult['error'] !== ''
                ? $catalogResult['error']
                : 'language resource catalog could not be loaded.',
        );
    }

    $probe = app_probe_database($app);
    if (!$probe['ok']) {
        throw new RuntimeException($probe['detail']);
    }

    $pdo = app_create_pdo($app);
    if (!app_project_language_resource_canonical_tables_available($pdo)) {
        throw new RuntimeException('canonical tables are not available.');
    }

    $projectId = app_project_language_resource_pdo_resolve_project_id(
        $pdo,
        $parsed['project_key'],
        $parsed['legacy_project_pid'],
    );
    $countsBefore = app_project_language_resource_canonical_table_counts($pdo, $projectId);
    $manualRowsReport = app_cli_lang_res_retire_manual_rows_report(
        $pdo,
        $projectId,
        $parsed['row_limit'],
        $catalogResult['item'],
    );

    $result = [
        'ok' => true,
        'project_key' => $parsed['project_key'],
        'project_id' => $projectId,
        'catalog_source' => (string) ($catalogResult['source'] ?? ''),
        'database' => [
            'label' => $probe['label'],
            'detail' => $probe['detail'],
        ],
        'apply' => $parsed['apply'],
        'force' => $parsed['force'],
        'safe_to_clear_project_rows' => $manualRowsReport['safe'],
        'counts_before' => $countsBefore,
        'manual_rows' => $manualRowsReport,
        'deleted_counts' => [
            'captions' => 0,
            'additional_group_assignments' => 0,
            'group_source_outputs' => 0,
            'group_languages' => 0,
            'resources' => 0,
            'groups' => 0,
            'languages' => 0,
        ],
        'counts_after' => $countsBefore,
        'error' => '',
    ];

    if ($parsed['apply']) {
        if (!$manualRowsReport['safe'] && !$parsed['force']) {
            throw new RuntimeException(
                'manual rows are not classified as safe to drop. Re-run without --apply, or use --force after review.',
            );
        }

        $pdo->beginTransaction();
        try {
            $result['deleted_counts'] = app_cli_lang_res_retire_delete_project_rows($pdo, $projectId);
            $result['counts_after'] = app_project_language_resource_canonical_table_counts($pdo, $projectId);
            $pdo->commit();
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $throwable;
        }
    }

    fwrite(
        STDOUT,
        json_encode(
            $result,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit(0);
} catch (Throwable $throwable) {
    fwrite(
        STDERR,
        json_encode(
            [
                'ok' => false,
                'error' => $throwable->getMessage(),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ) . PHP_EOL,
    );
    exit(1);
}
