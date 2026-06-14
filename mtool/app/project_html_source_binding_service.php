<?php

declare(strict_types=1);

require_once __DIR__ . '/project_output_html_module_catalog.php';

/**
 * @return list<string>
 */
function app_allowed_project_html_source_binding_refresh_policies(): array
{
    return ['follow-source-output', 'manual'];
}

/**
 * @return list<string>
 */
function app_allowed_project_html_source_binding_source_of_truths(): array
{
    return ['bootstrap-default', 'manual'];
}

function app_project_html_source_binding_default_source_ref(string $projectKey, string $sourceOutputKey): string
{
    return app_project_output_html_module_source_ref($projectKey, $sourceOutputKey);
}

/**
 * @param array{
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string
 * } $binding
 */
function app_project_html_source_binding_effective_source_ref(string $projectKey, array $binding): string
{
    $defaultSourceRef = '';
    $sourceOutputKey = trim((string) ($binding['source_output_key'] ?? ''));
    if ($sourceOutputKey !== '' && app_source_output_key_is_valid($sourceOutputKey)) {
        $defaultSourceRef = app_project_html_source_binding_default_source_ref($projectKey, $sourceOutputKey);
    }

    $moduleSourceRef = trim((string) ($binding['module_source_ref'] ?? ''));
    $refreshPolicy = trim((string) ($binding['refresh_policy'] ?? 'follow-source-output'));
    if ($refreshPolicy === 'manual' && $moduleSourceRef !== '') {
        return $moduleSourceRef;
    }

    if ($defaultSourceRef !== '') {
        return $defaultSourceRef;
    }

    return $moduleSourceRef;
}

/**
 * @param array{
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string
 * } $binding
 * @return array{
 *     ok:bool,
 *     effective_source_ref:string,
 *     effective_source_output_key:string,
 *     source_root_relative_path:string,
 *     source_kind:string,
 *     error:string
 * }
 */
function app_project_html_source_binding_resolve_source_root(string $projectKey, array $binding): array
{
    $effectiveSourceRef = app_project_html_source_binding_effective_source_ref($projectKey, $binding);
    if ($effectiveSourceRef === '') {
        return [
            'ok' => false,
            'effective_source_ref' => '',
            'effective_source_output_key' => '',
            'source_root_relative_path' => '',
            'source_kind' => '',
            'error' => 'effective source ref が未設定です。',
        ];
    }

    $parsedRef = app_project_output_html_module_source_ref_parse($effectiveSourceRef);
    if (!$parsedRef['ok']) {
        return [
            'ok' => false,
            'effective_source_ref' => $effectiveSourceRef,
            'effective_source_output_key' => '',
            'source_root_relative_path' => '',
            'source_kind' => '',
            'error' => $parsedRef['error'],
        ];
    }

    $resolvedRoot = app_project_output_html_module_resolve_source_root($effectiveSourceRef);
    if (!$resolvedRoot['ok']) {
        return [
            'ok' => false,
            'effective_source_ref' => $effectiveSourceRef,
            'effective_source_output_key' => $parsedRef['source_output_key'],
            'source_root_relative_path' => '',
            'source_kind' => '',
            'error' => $resolvedRoot['error'],
        ];
    }

    return [
        'ok' => true,
        'effective_source_ref' => $effectiveSourceRef,
        'effective_source_output_key' => $parsedRef['source_output_key'],
        'source_root_relative_path' => $resolvedRoot['relative_path'],
        'source_kind' => $resolvedRoot['source_kind'],
        'error' => '',
    ];
}

/**
 * @param array{
 *     source_output_key:string,
 *     name:string,
 *     source_output_dir:string,
 *     class_type:string,
 *     notes:string
 * } $sourceOutput
 * @return array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }
 */
function app_project_html_source_binding_bootstrap_candidate(
    string $projectKey,
    int $legacyProjectSourceOutputPid,
    array $sourceOutput,
): array {
    $sourceOutputKey = trim((string) ($sourceOutput['source_output_key'] ?? ''));

    return [
        'legacy_project_source_output_pid' => $legacyProjectSourceOutputPid,
        'source_output_key' => $sourceOutputKey,
        'module_source_ref' => $sourceOutputKey !== ''
            ? app_project_html_source_binding_default_source_ref($projectKey, $sourceOutputKey)
            : '',
        'refresh_policy' => 'follow-source-output',
        'notes' => '',
        'source_of_truth' => 'bootstrap-default',
        'updated_at' => '',
    ];
}

/**
 * @param list<array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * }> $bindings
 * @param array<string,array{
 *     source_output_key:string,
 *     name:string,
 *     source_output_dir:string,
 *     class_type:string,
 *     notes:string
 * }> $bootstrapSourceOutputByLegacyPid
 * @param array<string,array{
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
 * }> $sourceOutputByKey
 * @return array<string,array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_dir:string,
 *     module_source_ref:string,
 *     effective_source_ref:string,
 *     effective_source_output_key:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     binding_state:string,
 *     is_persisted:bool,
 *     source_root_ok:bool,
 *     source_root_relative_path:string,
 *     source_kind:string,
 *     source_root_error:string,
 *     updated_at:string
 * }>
 */
function app_project_html_source_binding_catalog(
    string $projectKey,
    array $bindings,
    array $bootstrapSourceOutputByLegacyPid,
    array $sourceOutputByKey,
): array {
    $catalog = [];

    foreach ($bootstrapSourceOutputByLegacyPid as $legacyPid => $sourceOutput) {
        $legacyProjectSourceOutputPid = (int) $legacyPid;
        if ($legacyProjectSourceOutputPid <= 0) {
            continue;
        }

        $catalog[(string) $legacyProjectSourceOutputPid] = app_project_html_source_binding_catalog_item(
            $projectKey,
            app_project_html_source_binding_bootstrap_candidate(
                $projectKey,
                $legacyProjectSourceOutputPid,
                $sourceOutput,
            ),
            $sourceOutputByKey,
            false,
            'bootstrap',
        );
    }

    foreach ($bindings as $binding) {
        $legacyProjectSourceOutputPid = (int) ($binding['legacy_project_source_output_pid'] ?? 0);
        if ($legacyProjectSourceOutputPid <= 0) {
            continue;
        }

        $catalog[(string) $legacyProjectSourceOutputPid] = app_project_html_source_binding_catalog_item(
            $projectKey,
            $binding,
            $sourceOutputByKey,
            true,
            'canonical',
        );
    }

    ksort($catalog, SORT_NUMERIC);

    return $catalog;
}

/**
 * @param array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     module_source_ref:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     updated_at:string
 * } $binding
 * @param array<string,array{
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
 * }> $sourceOutputByKey
 * @return array{
 *     legacy_project_source_output_pid:int,
 *     source_output_key:string,
 *     source_output_name:string,
 *     source_output_dir:string,
 *     module_source_ref:string,
 *     effective_source_ref:string,
 *     effective_source_output_key:string,
 *     refresh_policy:string,
 *     notes:string,
 *     source_of_truth:string,
 *     binding_state:string,
 *     is_persisted:bool,
 *     source_root_ok:bool,
 *     source_root_relative_path:string,
 *     source_kind:string,
 *     source_root_error:string,
 *     updated_at:string
 * }
 */
function app_project_html_source_binding_catalog_item(
    string $projectKey,
    array $binding,
    array $sourceOutputByKey,
    bool $isPersisted,
    string $bindingState,
): array {
    $sourceOutputKey = trim((string) ($binding['source_output_key'] ?? ''));
    $sourceOutput = $sourceOutputByKey[$sourceOutputKey] ?? null;
    $resolvedSourceRoot = app_project_html_source_binding_resolve_source_root($projectKey, $binding);

    return [
        'legacy_project_source_output_pid' => (int) ($binding['legacy_project_source_output_pid'] ?? 0),
        'source_output_key' => $sourceOutputKey,
        'source_output_name' => $sourceOutput !== null
            ? trim((string) ($sourceOutput['name'] ?? ''))
            : '',
        'source_output_dir' => $sourceOutput !== null
            ? trim((string) ($sourceOutput['source_output_dir'] ?? ''))
            : '',
        'module_source_ref' => trim((string) ($binding['module_source_ref'] ?? '')),
        'effective_source_ref' => $resolvedSourceRoot['effective_source_ref'],
        'effective_source_output_key' => $resolvedSourceRoot['effective_source_output_key'],
        'refresh_policy' => trim((string) ($binding['refresh_policy'] ?? 'follow-source-output')),
        'notes' => (string) ($binding['notes'] ?? ''),
        'source_of_truth' => trim((string) ($binding['source_of_truth'] ?? 'manual')),
        'binding_state' => $bindingState,
        'is_persisted' => $isPersisted,
        'source_root_ok' => $resolvedSourceRoot['ok'],
        'source_root_relative_path' => $resolvedSourceRoot['source_root_relative_path'],
        'source_kind' => $resolvedSourceRoot['source_kind'],
        'source_root_error' => $resolvedSourceRoot['error'],
        'updated_at' => (string) ($binding['updated_at'] ?? ''),
    ];
}
