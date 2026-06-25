<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function app_allowed_project_lifecycle_statuses(): array
{
    return ['draft', 'active', 'paused', 'archived'];
}

/**
 * @return list<string>
 */
function app_allowed_experiment_statuses(): array
{
    return ['draft', 'ready', 'running', 'completed', 'paused'];
}

/**
 * @return list<string>
 */
function app_allowed_runtime_targets(): array
{
    return ['local-docker', 'manual', 'prototype'];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_program_languages(): array
{
    return ['php', 'cs', 'java', 'objectivech', 'objectivecm', 'swift', 'json', 'md'];
}

/**
 * @return list<string>
 */
function app_allowed_html_template_target_types(): array
{
    return [
        'html',
        'db',
        'proxyserver',
        'proxyclient',
        'dbaasproxyserver',
        'dbaasproxyclient',
        'unittest',
        'uploadsetting',
        'LanguageResource',
    ];
}

/**
 * @return list<string>
 */
function app_allowed_html_template_program_languages(): array
{
    return array_values(array_filter(
        app_allowed_source_output_program_languages(),
        static fn (string $value): bool => !in_array($value, ['json', 'md'], true),
    ));
}

/**
 * @return list<string>
 */
function app_allowed_html_template_parameter_target_value_types(): array
{
    return ['EachHTML', 'code', 'AnotherTemplate'];
}

/**
 * @return list<string>
 */
function app_allowed_html_template_parameter_data_types(): array
{
    return ['', 'dataclassname', 'dbaccessclassname'];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_class_types(): array
{
    return ['DBAccess', 'DataClass', 'OpenAPI', 'AIContext', 'ProxyServer', 'ProxyClient', 'DBaaSProxyServer', 'DBaaSProxyClient', 'html', 'LanguageResource'];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_release_target_types(): array
{
    return ['Release', 'Beta'];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_artifact_strategies(): array
{
    return [
        'generated-bootstrap-dbclasses',
        'canonical-dbaccess-php',
        'canonical-dataclass-php',
        'openapi-json',
        'ai-context-md',
        'html-module-catalog',
        'legacy-directory-mirror',
        'single-proxy-server',
        'single-proxy-client',
        'custom-proxy-server',
        'custom-proxy-client',
        'metadata-only',
    ];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_target_binding_types(): array
{
    return [
        '',
        'runtime',
        'custom-proxy',
        'single-function-proxy',
        'proxy-metadata-only',
        'metadata-only',
    ];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_archive_formats(): array
{
    return ['tar.gz', 'none'];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_spec_visibilities(): array
{
    return ['internal-only', 'disabled'];
}

/**
 * @return list<string>
 */
function app_allowed_source_output_source_of_truths(): array
{
    return ['bootstrap-default', 'manual'];
}

/**
 * @return list<string>
 */
function app_allowed_compare_output_file_types(): array
{
    return ['Text', 'WindowsBatch', 'MacCommand'];
}

/**
 * @return list<string>
 */
function app_allowed_compare_output_source_of_truths(): array
{
    return ['bootstrap-default', 'manual'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_action_types(): array
{
    return ['', 'SELECTSINGLE', 'SELECTLIST', 'INSERT', 'UPDATE', 'DELETE'];
}

/**
 * @return list<string>
 */
function app_allowed_proxy_auth_types(): array
{
    return [
        '',
        'ProjectToken',
        'GetFunc',
        'ProjectTokenOrGetFunc',
        'StaticBearer',
        'NoSecurity',
        'Manual',
        'LoginCookieToken',
    ];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_single_proxy_auth_types(): array
{
    return app_allowed_proxy_auth_types();
}

/**
 * @return list<string>
 */
function app_allowed_db_access_source_of_truths(): array
{
    return ['preview-bootstrap', 'sync-bootstrap', 'seed-legacy', 'manual'];
}

/**
 * @return list<string>
 */
function app_allowed_custom_proxy_source_of_truths(): array
{
    return ['manual', 'seed-legacy'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_select_where_parameter_types(): array
{
    return ['argument', 'fixed', 'anotherfield'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_select_having_parameter_types(): array
{
    return ['argument', 'fixed', 'field'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_update_delete_where_parameter_types(): array
{
    return ['argument', 'fixed'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_insert_update_target_field_parameter_data_types(): array
{
    return ['', 'raw', 'file'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_select_where_parameter_data_types(): array
{
    return ['', 'raw'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_select_where_join_types(): array
{
    return ['', 'inner', 'left', 'right'];
}

/**
 * @return list<string>
 */
function app_allowed_db_access_relational_operators(): array
{
    return ['=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS', 'IS NOT'];
}

function app_normalize_project_key(string $value): string
{
    return strtoupper(trim($value));
}

function app_project_key_is_valid(string $value): bool
{
    return preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $value) === 1;
}

function app_normalize_source_output_key(string $value): string
{
    return strtoupper(trim($value));
}

function app_source_output_key_is_valid(string $value): bool
{
    return preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $value) === 1;
}

function app_normalize_compare_output_key(string $value): string
{
    return strtoupper(trim($value));
}

function app_compare_output_key_is_valid(string $value): bool
{
    return preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $value) === 1;
}

function app_normalize_compare_output_additional_path_key(string $value): string
{
    return strtoupper(trim($value));
}

function app_compare_output_additional_path_key_is_valid(string $value): bool
{
    return preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $value) === 1;
}

function app_normalize_custom_proxy_key(string $value): string
{
    return strtoupper(trim($value));
}

function app_custom_proxy_key_is_valid(string $value): bool
{
    return preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $value) === 1;
}

function app_normalize_database_source_key(string $value): string
{
    return strtolower(trim($value));
}

function app_database_source_key_is_valid(string $value): bool
{
    return preg_match('/^[a-z][a-z0-9_]{1,63}$/', $value) === 1;
}

function app_build_custom_proxy_key_candidate(string $basename, string $name): string
{
    $parts = [];

    foreach ([$basename, $name] as $segment) {
        $normalized = preg_replace('/[^A-Za-z0-9]+/', '-', strtoupper(trim($segment)));
        if (!is_string($normalized)) {
            continue;
        }

        $normalized = trim($normalized, '-');
        if ($normalized !== '') {
            $parts[] = $normalized;
        }
    }

    return implode('-', $parts);
}

function app_source_output_program_language_caption(string $value): string
{
    return match ($value) {
        'php' => 'PHP',
        'cs' => 'C#',
        'java' => 'Java',
        'objectivech' => 'Objective-C Header',
        'objectivecm' => 'Objective-C Implementation',
        'swift' => 'SWIFT',
        'json' => 'JSON',
        'md' => 'Markdown',
        default => $value,
    };
}

function app_html_template_target_type_caption(string $value): string
{
    return match (strtolower(trim($value))) {
        'html' => 'HTML',
        'db' => 'DB',
        'proxyserver' => 'Proxy Server',
        'proxyclient' => 'Proxy Client',
        'dbaasproxyserver' => 'DBaaS Proxy Server',
        'dbaasproxyclient' => 'DBaaS Proxy Client',
        'unittest' => 'Unit Test',
        'uploadsetting' => 'Upload Setting',
        'languageresource' => 'Language Resource',
        default => trim($value) === '' ? '(none)' : trim($value),
    };
}

function app_html_template_program_language_caption(string $value): string
{
    return app_source_output_program_language_caption($value);
}

function app_html_template_parameter_target_value_type_caption(string $value): string
{
    return match (strtolower(trim($value))) {
        'eachhtml' => 'Each HTML',
        'code' => 'Code',
        'anothertemplate' => 'Another Template',
        default => trim($value) === '' ? '(none)' : trim($value),
    };
}

function app_html_template_parameter_data_type_caption(string $value): string
{
    return match (strtolower(trim($value))) {
        '', 'default' => 'Default',
        'dataclassname' => 'Data Class Name',
        'dbaccessclassname' => 'DB Access Class Name',
        default => trim($value) === '' ? 'Default' : trim($value),
    };
}

function app_source_output_class_type_caption(string $value): string
{
    return match ($value) {
        'DBAccess' => 'Database Access',
        'DataClass' => 'Data Class',
        'OpenAPI' => 'OpenAPI',
        'AIContext' => 'AI Context',
        'ProxyServer' => 'Proxy Server',
        'ProxyClient' => 'Proxy Client',
        'DBaaSProxyServer' => 'Proxy Server for DBaaS',
        'DBaaSProxyClient' => 'Proxy Client for DBaaS',
        'html' => 'Html',
        'LanguageResource' => 'Language Resource',
        default => $value,
    };
}

function app_source_output_release_target_type_caption(string $value): string
{
    return match ($value) {
        'Release' => 'Release',
        'Beta' => 'Beta',
        default => $value,
    };
}

function app_source_output_artifact_strategy_caption(string $value): string
{
    return match ($value) {
        'generated-bootstrap-dbclasses' => 'Runtime DBClasses Reference',
        'canonical-dbaccess-php' => 'Canonical DB Access PHP',
        'canonical-dataclass-php' => 'Canonical Data Class PHP',
        'openapi-json' => 'OpenAPI JSON Artifact',
        'ai-context-md' => 'AI Context Markdown Artifact',
        'html-module-catalog' => 'HTML Module Catalog Artifact',
        'legacy-directory-mirror' => 'Legacy Directory Mirror Artifact',
        'single-proxy-server' => 'Single Function Proxy Server Artifact',
        'single-proxy-client' => 'Single Function Proxy Client Artifact',
        'custom-proxy-server' => 'Custom Proxy Server Artifact',
        'custom-proxy-client' => 'Custom Proxy Client Artifact',
        'metadata-only' => 'Metadata Only (No Artifact)',
        default => $value,
    };
}

function app_source_output_target_binding_type_caption(string $value): string
{
    return match ($value) {
        '' => 'Auto (By Strategy)',
        'runtime' => 'Runtime Bundle',
        'custom-proxy' => 'Custom Proxy Target',
        'single-function-proxy' => 'Single Function Proxy Target',
        'proxy-metadata-only' => 'Proxy Metadata Only',
        'metadata-only' => 'Metadata Only',
        default => $value,
    };
}

function app_source_output_spec_visibility_caption(string $value): string
{
    return match ($value) {
        'internal-only' => 'Internal Only (Authenticated Viewer)',
        'disabled' => 'Disabled',
        default => trim($value) === '' ? 'Internal Only (Authenticated Viewer)' : trim($value),
    };
}

/**
 * @param array{
 *     spec_visibility?:mixed
 * } $sourceOutput
 */
function app_source_output_effective_spec_visibility(array $sourceOutput): string
{
    $specVisibility = is_string($sourceOutput['spec_visibility'] ?? null)
        ? trim($sourceOutput['spec_visibility'])
        : '';

    if (in_array($specVisibility, app_allowed_source_output_spec_visibilities(), true)) {
        return $specVisibility;
    }

    return 'internal-only';
}

function app_source_output_artifact_strategy_supports_generation(string $value): bool
{
    return in_array($value, [
        'generated-bootstrap-dbclasses',
        'canonical-dbaccess-php',
        'canonical-dataclass-php',
        'openapi-json',
        'ai-context-md',
        'html-module-catalog',
        'legacy-directory-mirror',
        'single-proxy-server',
        'single-proxy-client',
        'custom-proxy-server',
        'custom-proxy-client',
    ], true);
}

function app_source_output_artifact_strategy_requires_runtime_source(string $value): bool
{
    return in_array($value, [
        'generated-bootstrap-dbclasses',
        'canonical-dbaccess-php',
        'canonical-dataclass-php',
        'openapi-json',
        'ai-context-md',
        'html-module-catalog',
        'legacy-directory-mirror',
        'single-proxy-server',
        'single-proxy-client',
        'custom-proxy-server',
        'custom-proxy-client',
    ], true);
}

/**
 * @param array{
 *     target_binding_type?:mixed,
 *     artifact_strategy?:mixed,
 *     class_type?:mixed
 * } $sourceOutput
 */
function app_source_output_target_binding_scope(array $sourceOutput): string
{
    $targetBindingType = is_string($sourceOutput['target_binding_type'] ?? null)
        ? trim($sourceOutput['target_binding_type'])
        : '';
    $artifactStrategy = is_string($sourceOutput['artifact_strategy'] ?? null)
        ? $sourceOutput['artifact_strategy']
        : '';
    $classType = is_string($sourceOutput['class_type'] ?? null)
        ? $sourceOutput['class_type']
        : '';

    if (
        $targetBindingType !== ''
        && in_array($targetBindingType, app_allowed_source_output_target_binding_types(), true)
    ) {
        return $targetBindingType;
    }

    if (in_array($artifactStrategy, ['single-proxy-server', 'single-proxy-client'], true)) {
        return 'single-function-proxy';
    }

    if ($artifactStrategy === 'openapi-json') {
        return 'single-function-proxy';
    }

    if (in_array($artifactStrategy, ['custom-proxy-server', 'custom-proxy-client'], true)) {
        return 'custom-proxy';
    }

    if (
        $artifactStrategy === 'generated-bootstrap-dbclasses'
        || $artifactStrategy === 'canonical-dbaccess-php'
        || $artifactStrategy === 'canonical-dataclass-php'
        || $classType === 'DBAccess'
    ) {
        return 'runtime';
    }

    if (
        $artifactStrategy === 'metadata-only'
        && in_array($classType, ['ProxyServer', 'ProxyClient', 'DBaaSProxyServer', 'DBaaSProxyClient'], true)
    ) {
        return 'proxy-metadata-only';
    }

    return 'metadata-only';
}

function app_source_output_target_binding_scope_caption(string $value): string
{
    return app_source_output_target_binding_type_caption($value);
}

/**
 * @param array{
 *     target_binding_type?:mixed,
 *     artifact_strategy?:mixed,
 *     class_type?:mixed
 * } $sourceOutput
 */
function app_source_output_supports_custom_proxy_targets(array $sourceOutput): bool
{
    return app_source_output_target_binding_scope($sourceOutput) === 'custom-proxy';
}

/**
 * @param array{
 *     target_binding_type?:mixed,
 *     artifact_strategy?:mixed,
 *     class_type?:mixed
 * } $sourceOutput
 */
function app_source_output_supports_single_function_proxy_targets(array $sourceOutput): bool
{
    return app_source_output_target_binding_scope($sourceOutput) === 'single-function-proxy';
}

function app_compare_output_file_type_caption(string $value): string
{
    return match ($value) {
        'Text' => 'Text',
        'WindowsBatch' => 'Windows Batch',
        'MacCommand' => 'Mac Command',
        default => $value,
    };
}

function app_proxy_auth_type_caption(string $value): string
{
    return match ($value) {
        '' => 'Default',
        'ProjectToken' => 'Project\'s Token (default)',
        'GetFunc' => 'Get Function',
        'ProjectTokenOrGetFunc' => 'Project\'s Token or Get Function',
        'StaticBearer' => 'Static Bearer',
        'NoSecurity' => 'No Security',
        'Manual' => 'Manual',
        'LoginCookieToken' => 'Login Cookie Token',
        default => $value,
    };
}

function app_db_access_single_proxy_auth_type_caption(string $value): string
{
    return app_proxy_auth_type_caption($value);
}

function app_proxy_auth_type_requires_get_function(string $value): bool
{
    return in_array($value, ['GetFunc', 'ProjectTokenOrGetFunc'], true);
}

function app_db_access_single_proxy_auth_type_requires_get_function(string $value): bool
{
    return app_proxy_auth_type_requires_get_function($value);
}

/**
 * @return array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     description:string
 * }
 */
function app_project_form_defaults(): array
{
    return [
        'project_key' => '',
        'name' => '',
        'slug' => '',
        'lifecycle_status' => 'draft',
        'description' => '',
    ];
}

/**
 * @return array{
 *     experiment_key:string,
 *     project_key:string,
 *     name:string,
 *     execution_status:string,
 *     runtime_target:string,
 *     notes:string
 * }
 */
function app_experiment_form_defaults(): array
{
    return [
        'experiment_key' => '',
        'project_key' => '',
        'name' => '',
        'execution_status' => 'draft',
        'runtime_target' => 'local-docker',
        'notes' => '',
    ];
}

/**
 * @return array{
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
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_source_output_form_defaults(): array
{
    return [
        'source_output_key' => '',
        'name' => '',
        'program_language' => 'php',
        'class_type' => 'DBAccess',
        'release_target_type' => 'Release',
        'source_template_dir' => '',
        'source_output_dir' => '',
        'source_temp_output_dir' => '',
        'proxy_base_url' => '',
        'autoload_filename_suffix' => 'mtool',
        'source_text_char_code' => 'UTF-8',
        'runtime_source_relative_path' => 'mtool/dbclasses',
        'artifact_strategy' => 'generated-bootstrap-dbclasses',
        'target_binding_type' => 'runtime',
        'spec_visibility' => 'internal-only',
        'output_archive_format' => 'tar.gz',
        'source_output_list_order' => '100',
        'notes' => '',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     compare_output_key:string,
 *     name:string,
 *     storage_base_path:string,
 *     output_file_path:string,
 *     output_file_type:string,
 *     compare_path:string,
 *     compare_tool_file_path:string,
 *     compare_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_compare_output_form_defaults(): array
{
    return [
        'compare_output_key' => '',
        'name' => '',
        'storage_base_path' => '',
        'output_file_path' => '',
        'output_file_type' => 'Text',
        'compare_path' => '',
        'compare_tool_file_path' => '',
        'compare_output_list_order' => '100',
        'notes' => '',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_compare_output_additional_path_form_defaults(): array
{
    return [
        'additional_path_key' => '',
        'path_a_base_path' => '',
        'path_a' => '',
        'path_b_base_path' => '',
        'path_b' => '',
        'is_same_filename_only' => '0',
        'additional_path_list_order' => '100',
        'notes' => '',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     custom_proxy_key:string,
 *     basename:string,
 *     name:string,
 *     in_transaction:string,
 *     auth_type:string,
 *     single_get_function_name:string,
 *     continue_even_if_failed_to_insert:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_custom_proxy_form_defaults(): array
{
    return [
        'custom_proxy_key' => '',
        'basename' => '',
        'name' => '',
        'in_transaction' => '0',
        'auth_type' => '',
        'single_get_function_name' => '',
        'continue_even_if_failed_to_insert' => '0',
        'notes' => '',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     db_access_source_name:string,
 *     db_access_function_name:string,
 *     is_list:string,
 *     step_order:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_custom_proxy_step_form_defaults(): array
{
    return [
        'db_access_source_name' => '',
        'db_access_function_name' => '',
        'is_list' => '0',
        'step_order' => '100',
        'notes' => '',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_class_form_defaults(): array
{
    return [
        'source_name' => '',
        'store_base_path' => '',
        'is_autoload' => '0',
        'notes' => '',
        'source_of_truth' => 'preview-bootstrap',
    ];
}

/**
 * @return array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_function_form_defaults(): array
{
    return [
        'source_name' => '',
        'function_name' => '',
        'function_list_order' => '0',
        'function_suffix' => '',
        'action_type' => '',
        'data_class_base_name' => '',
        'target_table_name' => '',
        'parameter_type' => '',
        'select_by_distinct' => '0',
        'sort_order_columns' => '',
        'memo' => '',
        'limit_parameter_type' => '',
        'limit_fixed_parameter' => '',
        'or_group_type' => '',
        'single_proxy_auth_type' => '',
        'single_proxy_single_get_function_name' => '',
        'is_blob_target' => '0',
        'detected_signature' => '',
        'detected_line' => '0',
        'source_of_truth' => 'preview-bootstrap',
    ];
}

/**
 * @return array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_function_select_where_form_defaults(): array
{
    return [
        'target_table_name' => '',
        'target_table_alias_name' => '',
        'target_table_column_name' => '',
        'parameter_type' => 'argument',
        'parameter_data_type' => '',
        'fixed_parameter' => '',
        'another_table_name' => '',
        'another_table_alias_name' => '',
        'another_field_name' => '',
        'join_type' => '',
        'or_group' => '',
        'relational_operator' => '=',
        'where_order' => '0',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_function_select_target_field_form_defaults(): array
{
    return [
        'target_table_name' => '',
        'target_table_alias_name' => '',
        'target_table_column_name' => '',
        'target_table_column_prefix' => '',
        'target_table_column_suffix' => '',
        'store_class_field_name' => '',
        'group_by_target' => '0',
        'field_list_order' => '0',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string,
 *     relational_operator:string,
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string,
 *     having_order:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_function_select_having_form_defaults(): array
{
    return [
        'left_target_prefix' => '',
        'left_target_field_id' => '0',
        'left_target_suffix' => '',
        'relational_operator' => '=',
        'right_target_prefix' => '',
        'right_parameter_type' => 'argument',
        'right_parameter_data_type' => '',
        'right_fixed_parameter' => '',
        'right_target_field_id' => '0',
        'right_target_suffix' => '',
        'having_order' => '0',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     relational_operator:string,
 *     or_group:string,
 *     where_order:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_function_update_delete_where_form_defaults(): array
{
    return [
        'target_table_column_name' => '',
        'parameter_type' => 'argument',
        'parameter_data_type' => '',
        'fixed_parameter' => '',
        'relational_operator' => '=',
        'or_group' => '',
        'where_order' => '0',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @return array{
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * }
 */
function app_db_access_function_insert_update_target_field_form_defaults(): array
{
    return [
        'target_table_column_name' => '',
        'parameter_type' => 'argument',
        'parameter_data_type' => '',
        'fixed_parameter' => '',
        'field_list_order' => '0',
        'source_of_truth' => 'manual',
    ];
}

/**
 * @param array{
 *     project_key:string,
 *     name:string,
 *     slug:string,
 *     lifecycle_status:string,
 *     description:string
 * } $input
 * @return array{
 *     input:array{
 *         project_key:string,
 *         name:string,
 *         slug:string,
 *         lifecycle_status:string,
 *         description:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_project_form(array $input): array
{
    $normalized = [
        'project_key' => app_normalize_project_key($input['project_key']),
        'name' => trim($input['name']),
        'slug' => strtolower(trim($input['slug'])),
        'lifecycle_status' => trim($input['lifecycle_status']),
        'description' => trim($input['description']),
    ];

    $errors = [];

    if ($normalized['project_key'] === '') {
        $errors[] = 'project key は必須です。';
    } elseif (!app_project_key_is_valid($normalized['project_key'])) {
        $errors[] = 'project key は英大文字、数字、ハイフンで構成し、先頭は英大文字にしてください。';
    }

    if ($normalized['name'] === '') {
        $errors[] = 'project name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 191) {
        $errors[] = 'project name は 191 文字以内にしてください。';
    }

    if ($normalized['slug'] === '') {
        $errors[] = 'slug は必須です。';
    } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $normalized['slug'])) {
        $errors[] = 'slug は小文字英数字とハイフンのみを使ってください。';
    } elseif (mb_strlen($normalized['slug']) > 191) {
        $errors[] = 'slug は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['lifecycle_status'], app_allowed_project_lifecycle_statuses(), true)) {
        $errors[] = 'lifecycle status が不正です。';
    }

    if ($normalized['description'] === '') {
        $errors[] = 'description は必須です。';
    } elseif (mb_strlen($normalized['description']) > 2000) {
        $errors[] = 'description は 2000 文字以内にしてください。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     experiment_key:string,
 *     project_key:string,
 *     name:string,
 *     execution_status:string,
 *     runtime_target:string,
 *     notes:string
 * } $input
 * @return array{
 *     input:array{
 *         experiment_key:string,
 *         project_key:string,
 *         name:string,
 *         execution_status:string,
 *         runtime_target:string,
 *         notes:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_experiment_form(array $input): array
{
    $normalized = [
        'experiment_key' => strtoupper(trim($input['experiment_key'])),
        'project_key' => strtoupper(trim($input['project_key'])),
        'name' => trim($input['name']),
        'execution_status' => trim($input['execution_status']),
        'runtime_target' => trim($input['runtime_target']),
        'notes' => trim($input['notes']),
    ];

    $errors = [];

    if ($normalized['experiment_key'] === '') {
        $errors[] = 'experiment key は必須です。';
    } elseif (!preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $normalized['experiment_key'])) {
        $errors[] = 'experiment key は英大文字、数字、ハイフンで構成し、先頭は英大文字にしてください。';
    }

    if ($normalized['project_key'] === '') {
        $errors[] = 'project key は必須です。';
    } elseif (!preg_match('/^[A-Z][A-Z0-9-]{2,63}$/', $normalized['project_key'])) {
        $errors[] = 'project key は英大文字、数字、ハイフンで構成し、先頭は英大文字にしてください。';
    }

    if ($normalized['name'] === '') {
        $errors[] = 'experiment name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 191) {
        $errors[] = 'experiment name は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['execution_status'], app_allowed_experiment_statuses(), true)) {
        $errors[] = 'execution status が不正です。';
    }

    if (!in_array($normalized['runtime_target'], app_allowed_runtime_targets(), true)) {
        $errors[] = 'runtime target が不正です。';
    }

    if ($normalized['notes'] === '') {
        $errors[] = 'notes は必須です。';
    } elseif (mb_strlen($normalized['notes']) > 2000) {
        $errors[] = 'notes は 2000 文字以内にしてください。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
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
 *     spec_visibility:string,
 *     output_archive_format:string,
 *     source_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         source_output_key:string,
 *         name:string,
 *         program_language:string,
 *         class_type:string,
 *         release_target_type:string,
 *         source_template_dir:string,
 *         source_output_dir:string,
 *         source_temp_output_dir:string,
 *         proxy_base_url:string,
 *         autoload_filename_suffix:string,
 *         source_text_char_code:string,
 *         runtime_source_relative_path:string,
 *         artifact_strategy:string,
 *         target_binding_type:string,
 *         spec_visibility:string,
 *         output_archive_format:string,
 *         source_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_source_output_form(array $input): array
{
    $normalized = [
        'source_output_key' => app_normalize_source_output_key($input['source_output_key']),
        'name' => trim($input['name']),
        'program_language' => trim($input['program_language']),
        'class_type' => trim($input['class_type']),
        'release_target_type' => trim($input['release_target_type']),
        'source_template_dir' => trim($input['source_template_dir']),
        'source_output_dir' => trim($input['source_output_dir']),
        'source_temp_output_dir' => trim($input['source_temp_output_dir']),
        'proxy_base_url' => trim($input['proxy_base_url']),
        'autoload_filename_suffix' => trim($input['autoload_filename_suffix']),
        'source_text_char_code' => trim($input['source_text_char_code']),
        'runtime_source_relative_path' => trim(str_replace('\\', '/', $input['runtime_source_relative_path'])),
        'artifact_strategy' => trim($input['artifact_strategy']),
        'target_binding_type' => trim($input['target_binding_type'] ?? ''),
        'spec_visibility' => trim((string) ($input['spec_visibility'] ?? '')) !== ''
            ? trim((string) ($input['spec_visibility'] ?? ''))
            : 'internal-only',
        'output_archive_format' => trim($input['output_archive_format']),
        'source_output_list_order' => trim($input['source_output_list_order']),
        'notes' => trim($input['notes']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['source_output_key'] === '') {
        $errors[] = 'source output key は必須です。';
    } elseif (!app_source_output_key_is_valid($normalized['source_output_key'])) {
        $errors[] = 'source output key は英大文字、数字、ハイフンのみで 3 文字以上 64 文字以下にしてください。';
    }

    if ($normalized['name'] === '') {
        $errors[] = 'name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 191) {
        $errors[] = 'name は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['program_language'], app_allowed_source_output_program_languages(), true)) {
        $errors[] = 'ProgramLanguage が不正です。';
    }

    if (!in_array($normalized['class_type'], app_allowed_source_output_class_types(), true)) {
        $errors[] = 'ClassType が不正です。';
    }

    if (!in_array($normalized['release_target_type'], app_allowed_source_output_release_target_types(), true)) {
        $errors[] = 'ReleaseTargetType が不正です。';
    }

    foreach (['source_template_dir', 'source_output_dir', 'source_temp_output_dir', 'proxy_base_url'] as $field) {
        if (mb_strlen($normalized[$field]) > 512) {
            $errors[] = $field . ' は 512 文字以内にしてください。';
        }
    }

    if (mb_strlen($normalized['autoload_filename_suffix']) > 191) {
        $errors[] = 'AutoloadFilenameSuffix は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['source_text_char_code']) > 64) {
        $errors[] = 'SourceTextCharCode は 64 文字以内にしてください。';
    }

    if (app_source_output_artifact_strategy_requires_runtime_source($normalized['artifact_strategy'])) {
        if ($normalized['runtime_source_relative_path'] === '') {
            $errors[] = 'runtime source relative path は必須です。';
        } elseif (
            str_starts_with($normalized['runtime_source_relative_path'], '/')
            || str_contains($normalized['runtime_source_relative_path'], '..')
            || preg_match('/^[A-Za-z0-9._\\/-]+$/', $normalized['runtime_source_relative_path']) !== 1
        ) {
            $errors[] = 'runtime source relative path は安全な runtime source key を指定してください。';
        } elseif (mb_strlen($normalized['runtime_source_relative_path']) > 512) {
            $errors[] = 'runtime source relative path は 512 文字以内にしてください。';
        }
    } elseif (
        $normalized['runtime_source_relative_path'] !== ''
        && (
            str_starts_with($normalized['runtime_source_relative_path'], '/')
            || str_contains($normalized['runtime_source_relative_path'], '..')
            || preg_match('/^[A-Za-z0-9._\\/-]+$/', $normalized['runtime_source_relative_path']) !== 1
            || mb_strlen($normalized['runtime_source_relative_path']) > 512
        )
    ) {
        $errors[] = 'runtime source relative path は blank または安全な runtime source key を指定してください。';
    }

    if (!in_array($normalized['artifact_strategy'], app_allowed_source_output_artifact_strategies(), true)) {
        $errors[] = 'artifact strategy が不正です。';
    }

    if (!in_array($normalized['target_binding_type'], app_allowed_source_output_target_binding_types(), true)) {
        $errors[] = 'target binding type が不正です。';
    }

    if (!in_array($normalized['spec_visibility'], app_allowed_source_output_spec_visibilities(), true)) {
        $errors[] = 'spec visibility が不正です。';
    }

    if (!in_array($normalized['output_archive_format'], app_allowed_source_output_archive_formats(), true)) {
        $errors[] = 'output archive format が不正です。';
    } elseif (
        app_source_output_artifact_strategy_supports_generation($normalized['artifact_strategy'])
        && $normalized['output_archive_format'] === 'none'
    ) {
        $errors[] = 'artifact を生成する strategy では output archive format に none は指定できません。';
    } elseif (
        !app_source_output_artifact_strategy_supports_generation($normalized['artifact_strategy'])
        && $normalized['output_archive_format'] !== 'none'
    ) {
        $errors[] = 'metadata-only strategy では output archive format に none を指定してください。';
    }

    if ($normalized['source_output_list_order'] === '') {
        $normalized['source_output_list_order'] = '100';
    }
    if (!ctype_digit($normalized['source_output_list_order'])) {
        $errors[] = 'source output list order は 0 以上の整数である必要があります。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_source_output_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     compare_output_key:string,
 *     name:string,
 *     storage_base_path:string,
 *     output_file_path:string,
 *     output_file_type:string,
 *     compare_path:string,
 *     compare_tool_file_path:string,
 *     compare_output_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         compare_output_key:string,
 *         name:string,
 *         storage_base_path:string,
 *         output_file_path:string,
 *         output_file_type:string,
 *         compare_path:string,
 *         compare_tool_file_path:string,
 *         compare_output_list_order:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_compare_output_form(array $input): array
{
    $normalized = [
        'compare_output_key' => app_normalize_compare_output_key($input['compare_output_key']),
        'name' => trim($input['name']),
        'storage_base_path' => trim($input['storage_base_path']),
        'output_file_path' => trim($input['output_file_path']),
        'output_file_type' => trim($input['output_file_type']),
        'compare_path' => trim($input['compare_path']),
        'compare_tool_file_path' => trim($input['compare_tool_file_path']),
        'compare_output_list_order' => trim($input['compare_output_list_order']),
        'notes' => trim($input['notes']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['compare_output_key'] === '') {
        $errors[] = 'compare output key は必須です。';
    } elseif (!app_compare_output_key_is_valid($normalized['compare_output_key'])) {
        $errors[] = 'compare output key は英大文字、数字、ハイフンのみで 3 文字以上 64 文字以下にしてください。';
    }

    if ($normalized['name'] === '') {
        $errors[] = 'name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 191) {
        $errors[] = 'name は 191 文字以内にしてください。';
    }

    foreach (['storage_base_path', 'output_file_path', 'compare_path', 'compare_tool_file_path'] as $field) {
        if (mb_strlen($normalized[$field]) > 512) {
            $errors[] = $field . ' は 512 文字以内にしてください。';
        }
    }

    if ($normalized['output_file_path'] === '') {
        $errors[] = 'output_file_path は必須です。';
    }

    if ($normalized['compare_path'] === '') {
        $errors[] = 'compare_path は必須です。';
    }

    if (!in_array($normalized['output_file_type'], app_allowed_compare_output_file_types(), true)) {
        $errors[] = 'output_file_type が不正です。';
    }

    if (
        in_array($normalized['output_file_type'], ['WindowsBatch', 'MacCommand'], true)
        && $normalized['compare_tool_file_path'] === ''
    ) {
        $errors[] = 'Windows Batch または Mac Command を使う場合は compare_tool_file_path が必須です。';
    }

    if ($normalized['compare_output_list_order'] === '') {
        $normalized['compare_output_list_order'] = '100';
    }
    if (!ctype_digit($normalized['compare_output_list_order'])) {
        $errors[] = 'compare_output_list_order は 0 以上の整数である必要があります。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_compare_output_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     additional_path_key:string,
 *     path_a_base_path:string,
 *     path_a:string,
 *     path_b_base_path:string,
 *     path_b:string,
 *     is_same_filename_only:string,
 *     additional_path_list_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         additional_path_key:string,
 *         path_a_base_path:string,
 *         path_a:string,
 *         path_b_base_path:string,
 *         path_b:string,
 *         is_same_filename_only:string,
 *         additional_path_list_order:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_compare_output_additional_path_form(array $input): array
{
    $normalized = [
        'additional_path_key' => app_normalize_compare_output_additional_path_key($input['additional_path_key']),
        'path_a_base_path' => trim($input['path_a_base_path']),
        'path_a' => trim($input['path_a']),
        'path_b_base_path' => trim($input['path_b_base_path']),
        'path_b' => trim($input['path_b']),
        'is_same_filename_only' => trim($input['is_same_filename_only']),
        'additional_path_list_order' => trim($input['additional_path_list_order']),
        'notes' => trim($input['notes']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['additional_path_key'] === '') {
        $errors[] = 'additional_path_key は必須です。';
    } elseif (!app_compare_output_additional_path_key_is_valid($normalized['additional_path_key'])) {
        $errors[] = 'additional_path_key は英大文字、数字、ハイフンのみで 3 文字以上 64 文字以下にしてください。';
    }

    foreach (['path_a_base_path', 'path_a', 'path_b_base_path', 'path_b'] as $field) {
        if (mb_strlen($normalized[$field]) > 512) {
            $errors[] = $field . ' は 512 文字以内にしてください。';
        }
    }

    if ($normalized['path_a'] === '') {
        $errors[] = 'path_a は必須です。';
    }

    if ($normalized['path_b'] === '') {
        $errors[] = 'path_b は必須です。';
    }

    if (!in_array($normalized['is_same_filename_only'], ['0', '1'], true)) {
        $errors[] = 'is_same_filename_only は 0 または 1 を指定してください。';
    }

    if ($normalized['additional_path_list_order'] === '') {
        $normalized['additional_path_list_order'] = '100';
    }
    if (!ctype_digit($normalized['additional_path_list_order'])) {
        $errors[] = 'additional_path_list_order は 0 以上の整数である必要があります。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_compare_output_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     custom_proxy_key:string,
 *     basename:string,
 *     name:string,
 *     in_transaction:string,
 *     auth_type:string,
 *     single_get_function_name:string,
 *     continue_even_if_failed_to_insert:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         custom_proxy_key:string,
 *         basename:string,
 *         name:string,
 *         in_transaction:string,
 *         auth_type:string,
 *         single_get_function_name:string,
 *         continue_even_if_failed_to_insert:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_custom_proxy_form(array $input): array
{
    $normalized = [
        'custom_proxy_key' => app_normalize_custom_proxy_key($input['custom_proxy_key']),
        'basename' => trim($input['basename']),
        'name' => trim($input['name']),
        'in_transaction' => trim($input['in_transaction']) === '1' ? '1' : '0',
        'auth_type' => trim($input['auth_type']),
        'single_get_function_name' => trim($input['single_get_function_name']),
        'continue_even_if_failed_to_insert' => trim($input['continue_even_if_failed_to_insert']) === '1' ? '1' : '0',
        'notes' => trim($input['notes']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['custom_proxy_key'] === '') {
        $errors[] = 'custom proxy key は必須です。';
    } elseif (!app_custom_proxy_key_is_valid($normalized['custom_proxy_key'])) {
        $errors[] = 'custom proxy key は英大文字、数字、ハイフンのみで 3 文字以上 64 文字以下にしてください。';
    }

    if ($normalized['basename'] === '') {
        $errors[] = 'basename は必須です。';
    } elseif (mb_strlen($normalized['basename']) > 191) {
        $errors[] = 'basename は 191 文字以内にしてください。';
    }

    if ($normalized['name'] === '') {
        $errors[] = 'name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 191) {
        $errors[] = 'name は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['auth_type']) > 64) {
        $errors[] = 'AuthType は 64 文字以内にしてください。';
    } elseif (!in_array($normalized['auth_type'], app_allowed_proxy_auth_types(), true)) {
        $errors[] = 'AuthType が不正です。';
    }

    if (mb_strlen($normalized['single_get_function_name']) > 191) {
        $errors[] = 'SingleGetFunc 相当名は 191 文字以内にしてください。';
    }

    if (
        app_proxy_auth_type_requires_get_function($normalized['auth_type'])
        && $normalized['single_get_function_name'] === ''
    ) {
        $errors[] = 'GetFunc 系の AuthType では SingleGetFunc 相当名が必須です。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_custom_proxy_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     db_access_source_name:string,
 *     db_access_function_name:string,
 *     is_list:string,
 *     step_order:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         db_access_source_name:string,
 *         db_access_function_name:string,
 *         is_list:string,
 *         step_order:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_custom_proxy_step_form(array $input): array
{
    $normalized = [
        'db_access_source_name' => trim($input['db_access_source_name']),
        'db_access_function_name' => trim($input['db_access_function_name']),
        'is_list' => trim($input['is_list']) === '1' ? '1' : '0',
        'step_order' => trim($input['step_order']),
        'notes' => trim($input['notes']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['db_access_source_name'] === '') {
        $errors[] = 'db access key は必須です。';
    } elseif (mb_strlen($normalized['db_access_source_name']) > 191) {
        $errors[] = 'db access key は 191 文字以内にしてください。';
    }

    if ($normalized['db_access_function_name'] === '') {
        $errors[] = 'function name は必須です。';
    } elseif (mb_strlen($normalized['db_access_function_name']) > 191) {
        $errors[] = 'function name は 191 文字以内にしてください。';
    }

    if ($normalized['step_order'] === '') {
        $normalized['step_order'] = '100';
    }

    if (!ctype_digit($normalized['step_order'])) {
        $errors[] = 'step order は 0 以上の整数である必要があります。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_custom_proxy_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     source_name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     notes:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         source_name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         notes:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_class_form(array $input): array
{
    $normalized = [
        'source_name' => trim($input['source_name']),
        'store_base_path' => trim($input['store_base_path']),
        'is_autoload' => trim($input['is_autoload']) === '1' ? '1' : '0',
        'notes' => trim($input['notes']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['source_name'] === '') {
        $errors[] = 'db access key は必須です。';
    } elseif (mb_strlen($normalized['source_name']) > 191) {
        $errors[] = 'db access key は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['store_base_path']) > 512) {
        $errors[] = 'StoreBasePath は 512 文字以内にしてください。';
    }

    if (mb_strlen($normalized['notes']) > 4000) {
        $errors[] = 'notes は 4000 文字以内にしてください。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     source_name:string,
 *     function_name:string,
 *     function_list_order:string,
 *     function_suffix:string,
 *     action_type:string,
 *     data_class_base_name:string,
 *     target_table_name:string,
 *     parameter_type:string,
 *     select_by_distinct:string,
 *     sort_order_columns:string,
 *     memo:string,
 *     limit_parameter_type:string,
 *     limit_fixed_parameter:string,
 *     or_group_type:string,
 *     single_proxy_auth_type:string,
 *     single_proxy_single_get_function_name:string,
 *     is_blob_target:string,
 *     detected_signature:string,
 *     detected_line:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         source_name:string,
 *         function_name:string,
 *         function_list_order:string,
 *         function_suffix:string,
 *         action_type:string,
 *         data_class_base_name:string,
 *         target_table_name:string,
 *         parameter_type:string,
 *         select_by_distinct:string,
 *         sort_order_columns:string,
 *         memo:string,
 *         limit_parameter_type:string,
 *         limit_fixed_parameter:string,
 *         or_group_type:string,
 *         single_proxy_auth_type:string,
 *         single_proxy_single_get_function_name:string,
 *         is_blob_target:string,
 *         detected_signature:string,
 *         detected_line:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_function_form(array $input): array
{
    $normalized = [
        'source_name' => trim($input['source_name']),
        'function_name' => trim($input['function_name']),
        'function_list_order' => trim($input['function_list_order']),
        'function_suffix' => trim($input['function_suffix']),
        'action_type' => trim($input['action_type']),
        'data_class_base_name' => trim($input['data_class_base_name']),
        'target_table_name' => trim($input['target_table_name']),
        'parameter_type' => trim($input['parameter_type']),
        'select_by_distinct' => trim($input['select_by_distinct']) === '1' ? '1' : '0',
        'sort_order_columns' => trim($input['sort_order_columns']),
        'memo' => trim($input['memo']),
        'limit_parameter_type' => trim($input['limit_parameter_type']),
        'limit_fixed_parameter' => trim($input['limit_fixed_parameter']),
        'or_group_type' => trim($input['or_group_type']),
        'single_proxy_auth_type' => trim($input['single_proxy_auth_type']),
        'single_proxy_single_get_function_name' => trim($input['single_proxy_single_get_function_name']),
        'is_blob_target' => trim($input['is_blob_target']) === '1' ? '1' : '0',
        'detected_signature' => trim($input['detected_signature']),
        'detected_line' => trim($input['detected_line']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['source_name'] === '') {
        $errors[] = 'db access key は必須です。';
    } elseif (mb_strlen($normalized['source_name']) > 191) {
        $errors[] = 'db access key は 191 文字以内にしてください。';
    }

    if ($normalized['function_name'] === '') {
        $errors[] = 'function name は必須です。';
    } elseif (mb_strlen($normalized['function_name']) > 191) {
        $errors[] = 'function name は 191 文字以内にしてください。';
    }

    if ($normalized['function_list_order'] === '') {
        $normalized['function_list_order'] = '0';
    }

    if (!ctype_digit($normalized['function_list_order'])) {
        $errors[] = 'function list order は 0 以上の整数である必要があります。';
    }

    if (mb_strlen($normalized['function_suffix']) > 191) {
        $errors[] = 'function suffix は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['action_type'], app_allowed_db_access_action_types(), true)) {
        $errors[] = 'ActionType が不正です。';
    }

    if (mb_strlen($normalized['data_class_base_name']) > 191) {
        $errors[] = 'DataClassBaseNameForSelectAction は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['target_table_name']) > 191) {
        $errors[] = 'InsertUpdateDeleteTargetTable は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['parameter_type']) > 64) {
        $errors[] = 'parameter type は 64 文字以内にしてください。';
    }

    if (mb_strlen($normalized['sort_order_columns']) > 512) {
        $errors[] = 'SortOrderColumns は 512 文字以内にしてください。';
    }

    if (mb_strlen($normalized['memo']) > 4000) {
        $errors[] = 'memo は 4000 文字以内にしてください。';
    }

    if (mb_strlen($normalized['limit_parameter_type']) > 64) {
        $errors[] = 'limitParameterType は 64 文字以内にしてください。';
    }

    if (mb_strlen($normalized['limit_fixed_parameter']) > 191) {
        $errors[] = 'limitFixedParameter は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['or_group_type']) > 64) {
        $errors[] = 'ORGroupType は 64 文字以内にしてください。';
    }

    if (mb_strlen($normalized['single_proxy_auth_type']) > 64) {
        $errors[] = 'SingleProxy_AuthType は 64 文字以内にしてください。';
    }

    if (!in_array($normalized['single_proxy_auth_type'], app_allowed_db_access_single_proxy_auth_types(), true)) {
        $errors[] = 'SingleProxy_AuthType が不正です。';
    }

    if ($normalized['is_blob_target'] === '1' && !in_array($normalized['action_type'], ['INSERT', 'UPDATE'], true)) {
        $errors[] = 'IsBlobTarget=1 は INSERT/UPDATE のみ設定できます。';
    }

    if (mb_strlen($normalized['single_proxy_single_get_function_name']) > 191) {
        $errors[] = 'SingleProxy_SingleGetFuncPID 相当値は 191 文字以内にしてください。';
    }

    if (
        app_db_access_single_proxy_auth_type_requires_get_function($normalized['single_proxy_auth_type'])
        && $normalized['single_proxy_single_get_function_name'] === ''
    ) {
        $errors[] = 'GetFunc 系の SingleProxy_AuthType では SingleProxy_SingleGetFuncPID 相当名が必須です。';
    }

    if (mb_strlen($normalized['detected_signature']) > 512) {
        $errors[] = 'detected signature は 512 文字以内にしてください。';
    }

    if ($normalized['detected_line'] === '') {
        $normalized['detected_line'] = '0';
    }

    if (!ctype_digit($normalized['detected_line'])) {
        $errors[] = 'detected line は数値である必要があります。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     another_table_name:string,
 *     another_table_alias_name:string,
 *     another_field_name:string,
 *     join_type:string,
 *     or_group:string,
 *     relational_operator:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         another_table_name:string,
 *         another_table_alias_name:string,
 *         another_field_name:string,
 *         join_type:string,
 *         or_group:string,
 *         relational_operator:string,
 *         where_order:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_function_select_where_form(array $input): array
{
    $normalized = [
        'target_table_name' => trim($input['target_table_name']),
        'target_table_alias_name' => trim($input['target_table_alias_name']),
        'target_table_column_name' => trim($input['target_table_column_name']),
        'parameter_type' => trim($input['parameter_type']),
        'parameter_data_type' => trim($input['parameter_data_type']),
        'fixed_parameter' => trim($input['fixed_parameter']),
        'another_table_name' => trim($input['another_table_name']),
        'another_table_alias_name' => trim($input['another_table_alias_name']),
        'another_field_name' => trim($input['another_field_name']),
        'join_type' => trim($input['join_type']),
        'or_group' => trim($input['or_group']),
        'relational_operator' => strtoupper(trim($input['relational_operator'])),
        'where_order' => trim($input['where_order']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['target_table_name'] === '') {
        $errors[] = 'Target Table Name は必須です。';
    } elseif (mb_strlen($normalized['target_table_name']) > 191) {
        $errors[] = 'Target Table Name は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['target_table_alias_name']) > 191) {
        $errors[] = 'Target Table Alias Name は 191 文字以内にしてください。';
    }

    if ($normalized['target_table_column_name'] === '') {
        $errors[] = 'Target Column Name は必須です。';
    } elseif (mb_strlen($normalized['target_table_column_name']) > 191) {
        $errors[] = 'Target Column Name は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['parameter_type'], app_allowed_db_access_select_where_parameter_types(), true)) {
        $errors[] = 'Parameter Type が不正です。';
    }

    if (!in_array($normalized['parameter_data_type'], app_allowed_db_access_select_where_parameter_data_types(), true)) {
        $errors[] = 'Parameter Data Type が不正です。';
    }

    if (mb_strlen($normalized['fixed_parameter']) > 4000) {
        $errors[] = 'Fixed Parameter は 4000 文字以内にしてください。';
    }

    if (mb_strlen($normalized['another_table_name']) > 191) {
        $errors[] = 'Another Table Name は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['another_table_alias_name']) > 191) {
        $errors[] = 'Another Table Alias Name は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['another_field_name']) > 191) {
        $errors[] = 'Another Field Name は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['join_type'], app_allowed_db_access_select_where_join_types(), true)) {
        $errors[] = 'Join Type が不正です。';
    }

    if (mb_strlen($normalized['or_group']) > 64) {
        $errors[] = 'OR Group は 64 文字以内にしてください。';
    }

    if ($normalized['relational_operator'] === '') {
        $normalized['relational_operator'] = '=';
    }
    if (!in_array($normalized['relational_operator'], app_allowed_db_access_relational_operators(), true)) {
        $errors[] = 'Relational Operator が不正です。';
    }

    if ($normalized['where_order'] === '') {
        $normalized['where_order'] = '0';
    }
    if (!ctype_digit($normalized['where_order'])) {
        $errors[] = 'Where Order は数値である必要があります。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    if ($normalized['parameter_type'] === 'fixed' && $normalized['fixed_parameter'] === '') {
        $errors[] = 'Parameter Type が fixed の場合は Fixed Parameter が必須です。';
    }

    if ($normalized['parameter_type'] === 'anotherfield') {
        $normalized['parameter_data_type'] = '';
        $normalized['fixed_parameter'] = '';

        if ($normalized['another_table_name'] === '') {
            $errors[] = 'Parameter Type が anotherfield の場合は Another Table Name が必須です。';
        }
        if ($normalized['another_field_name'] === '') {
            $errors[] = 'Parameter Type が anotherfield の場合は Another Field Name が必須です。';
        }
        if (!in_array($normalized['join_type'], ['left', 'right'], true)) {
            $errors[] = 'Parameter Type が anotherfield の場合は Join Type に left または right を指定してください。';
        }
    } else {
        $normalized['another_table_name'] = '';
        $normalized['another_table_alias_name'] = '';
        $normalized['another_field_name'] = '';

        if (!in_array($normalized['join_type'], ['', 'inner'], true)) {
            $errors[] = 'Parameter Type が argument/fixed の場合、Join Type は空欄または inner のみ指定できます。';
        }
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     target_table_name:string,
 *     target_table_alias_name:string,
 *     target_table_column_name:string,
 *     target_table_column_prefix:string,
 *     target_table_column_suffix:string,
 *     store_class_field_name:string,
 *     group_by_target:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         target_table_name:string,
 *         target_table_alias_name:string,
 *         target_table_column_name:string,
 *         target_table_column_prefix:string,
 *         target_table_column_suffix:string,
 *         store_class_field_name:string,
 *         group_by_target:string,
 *         field_list_order:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_function_select_target_field_form(array $input): array
{
    $normalized = [
        'target_table_name' => trim($input['target_table_name']),
        'target_table_alias_name' => trim($input['target_table_alias_name']),
        'target_table_column_name' => trim($input['target_table_column_name']),
        'target_table_column_prefix' => trim($input['target_table_column_prefix']),
        'target_table_column_suffix' => trim($input['target_table_column_suffix']),
        'store_class_field_name' => trim($input['store_class_field_name']),
        'group_by_target' => trim($input['group_by_target']) === '1' ? '1' : '0',
        'field_list_order' => trim($input['field_list_order']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['target_table_name'] === '') {
        $errors[] = 'Target Table Name は必須です。';
    } elseif (mb_strlen($normalized['target_table_name']) > 191) {
        $errors[] = 'Target Table Name は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['target_table_alias_name']) > 191) {
        $errors[] = 'Target Table Alias Name は 191 文字以内にしてください。';
    }

    if ($normalized['target_table_column_name'] === '') {
        $errors[] = 'Target Column Name は必須です。';
    } elseif (mb_strlen($normalized['target_table_column_name']) > 191) {
        $errors[] = 'Target Column Name は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['target_table_column_prefix']) > 191) {
        $errors[] = 'Target Column Prefix は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['target_table_column_suffix']) > 191) {
        $errors[] = 'Target Column Suffix は 191 文字以内にしてください。';
    }

    if ($normalized['target_table_column_name'] !== '*' && $normalized['store_class_field_name'] === '') {
        $errors[] = 'Store Class Field Name は必須です。';
    } elseif (mb_strlen($normalized['store_class_field_name']) > 191) {
        $errors[] = 'Store Class Field Name は 191 文字以内にしてください。';
    }

    if ($normalized['field_list_order'] === '') {
        $normalized['field_list_order'] = '0';
    }
    if (!ctype_digit($normalized['field_list_order'])) {
        $errors[] = 'Field List Order は数値である必要があります。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     left_target_prefix:string,
 *     left_target_field_id:string,
 *     left_target_suffix:string,
 *     relational_operator:string,
 *     right_target_prefix:string,
 *     right_parameter_type:string,
 *     right_parameter_data_type:string,
 *     right_fixed_parameter:string,
 *     right_target_field_id:string,
 *     right_target_suffix:string,
 *     having_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         left_target_prefix:string,
 *         left_target_field_id:string,
 *         left_target_suffix:string,
 *         relational_operator:string,
 *         right_target_prefix:string,
 *         right_parameter_type:string,
 *         right_parameter_data_type:string,
 *         right_fixed_parameter:string,
 *         right_target_field_id:string,
 *         right_target_suffix:string,
 *         having_order:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_function_select_having_form(array $input): array
{
    $normalized = [
        'left_target_prefix' => trim($input['left_target_prefix']),
        'left_target_field_id' => trim($input['left_target_field_id']),
        'left_target_suffix' => trim($input['left_target_suffix']),
        'relational_operator' => strtoupper(trim($input['relational_operator'])),
        'right_target_prefix' => trim($input['right_target_prefix']),
        'right_parameter_type' => trim($input['right_parameter_type']),
        'right_parameter_data_type' => trim($input['right_parameter_data_type']),
        'right_fixed_parameter' => trim($input['right_fixed_parameter']),
        'right_target_field_id' => trim($input['right_target_field_id']),
        'right_target_suffix' => trim($input['right_target_suffix']),
        'having_order' => trim($input['having_order']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['left_target_field_id'] === '') {
        $normalized['left_target_field_id'] = '0';
    }
    if (!ctype_digit($normalized['left_target_field_id']) || $normalized['left_target_field_id'] === '0') {
        $errors[] = 'Left Target Field は必須です。';
    }

    if (mb_strlen($normalized['left_target_prefix']) > 191) {
        $errors[] = 'Left Target Prefix は 191 文字以内にしてください。';
    }

    if (mb_strlen($normalized['left_target_suffix']) > 191) {
        $errors[] = 'Left Target Suffix は 191 文字以内にしてください。';
    }

    if ($normalized['relational_operator'] === '') {
        $normalized['relational_operator'] = '=';
    }
    if (!in_array($normalized['relational_operator'], app_allowed_db_access_relational_operators(), true)) {
        $errors[] = 'Relational Operator が不正です。';
    }

    if (mb_strlen($normalized['right_target_prefix']) > 191) {
        $errors[] = 'Right Target Prefix は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['right_parameter_type'], app_allowed_db_access_select_having_parameter_types(), true)) {
        $errors[] = 'Right Parameter Type が不正です。';
    }

    if (!in_array($normalized['right_parameter_data_type'], app_allowed_db_access_select_where_parameter_data_types(), true)) {
        $errors[] = 'Right Parameter Data Type が不正です。';
    }

    if (mb_strlen($normalized['right_fixed_parameter']) > 4000) {
        $errors[] = 'Right Fixed Parameter は 4000 文字以内にしてください。';
    }

    if ($normalized['right_target_field_id'] === '') {
        $normalized['right_target_field_id'] = '0';
    }
    if (!ctype_digit($normalized['right_target_field_id'])) {
        $errors[] = 'Right Target Field は数値である必要があります。';
    }

    if (mb_strlen($normalized['right_target_suffix']) > 191) {
        $errors[] = 'Right Target Suffix は 191 文字以内にしてください。';
    }

    if ($normalized['having_order'] === '') {
        $normalized['having_order'] = '0';
    }
    if (!ctype_digit($normalized['having_order'])) {
        $errors[] = 'Having Order は数値である必要があります。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    if ($normalized['right_parameter_type'] === 'fixed') {
        if ($normalized['right_fixed_parameter'] === '') {
            $errors[] = 'Right Parameter Type が fixed の場合は Right Fixed Parameter が必須です。';
        }
        $normalized['right_target_field_id'] = '0';
    } elseif ($normalized['right_parameter_type'] === 'field') {
        if ($normalized['right_target_field_id'] === '0') {
            $errors[] = 'Right Parameter Type が field の場合は Right Target Field が必須です。';
        }
        $normalized['right_parameter_data_type'] = '';
        $normalized['right_fixed_parameter'] = '';
    } else {
        $normalized['right_fixed_parameter'] = '';
        $normalized['right_target_field_id'] = '0';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     relational_operator:string,
 *     or_group:string,
 *     where_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         relational_operator:string,
 *         or_group:string,
 *         where_order:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_function_update_delete_where_form(array $input): array
{
    $normalized = [
        'target_table_column_name' => trim($input['target_table_column_name']),
        'parameter_type' => trim($input['parameter_type']),
        'parameter_data_type' => trim($input['parameter_data_type']),
        'fixed_parameter' => trim($input['fixed_parameter']),
        'relational_operator' => strtoupper(trim($input['relational_operator'])),
        'or_group' => trim($input['or_group']),
        'where_order' => trim($input['where_order']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];

    if ($normalized['target_table_column_name'] === '') {
        $errors[] = 'Target Column Name は必須です。';
    } elseif (mb_strlen($normalized['target_table_column_name']) > 191) {
        $errors[] = 'Target Column Name は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['parameter_type'], app_allowed_db_access_update_delete_where_parameter_types(), true)) {
        $errors[] = 'Parameter Type が不正です。';
    }

    if (!in_array($normalized['parameter_data_type'], app_allowed_db_access_select_where_parameter_data_types(), true)) {
        $errors[] = 'Parameter Data Type が不正です。';
    }

    if (mb_strlen($normalized['fixed_parameter']) > 4000) {
        $errors[] = 'Fixed Parameter は 4000 文字以内にしてください。';
    }

    if ($normalized['relational_operator'] === '') {
        $normalized['relational_operator'] = '=';
    }
    if (!in_array($normalized['relational_operator'], app_allowed_db_access_relational_operators(), true)) {
        $errors[] = 'Relational Operator が不正です。';
    }

    if (mb_strlen($normalized['or_group']) > 64) {
        $errors[] = 'OR Group は 64 文字以内にしてください。';
    }

    if ($normalized['where_order'] === '') {
        $normalized['where_order'] = '0';
    }
    if (!ctype_digit($normalized['where_order'])) {
        $errors[] = 'Where Order は数値である必要があります。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    if ($normalized['parameter_type'] === 'fixed') {
        if ($normalized['fixed_parameter'] === '') {
            $errors[] = 'Parameter Type が fixed の場合は Fixed Parameter が必須です。';
        }
    } else {
        $normalized['fixed_parameter'] = '';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     target_table_column_name:string,
 *     parameter_type:string,
 *     parameter_data_type:string,
 *     fixed_parameter:string,
 *     field_list_order:string,
 *     source_of_truth:string
 * } $input
 * @return array{
 *     input:array{
 *         target_table_column_name:string,
 *         parameter_type:string,
 *         parameter_data_type:string,
 *         fixed_parameter:string,
 *         field_list_order:string,
 *         source_of_truth:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_db_access_function_insert_update_target_field_form(
    array $input,
    bool $allowFileParameterDataType,
    bool $blobRuntimeContractSupported = true,
): array
{
    $normalized = [
        'target_table_column_name' => trim($input['target_table_column_name']),
        'parameter_type' => trim($input['parameter_type']),
        'parameter_data_type' => trim($input['parameter_data_type']),
        'fixed_parameter' => trim($input['fixed_parameter']),
        'field_list_order' => trim($input['field_list_order']),
        'source_of_truth' => trim($input['source_of_truth']),
    ];

    $errors = [];
    $allowedParameterDataTypes = app_allowed_db_access_insert_update_target_field_parameter_data_types();
    if (!$allowFileParameterDataType) {
        $allowedParameterDataTypes = array_values(
            array_filter(
                $allowedParameterDataTypes,
                static fn (string $value): bool => $value !== 'file',
            ),
        );
    }

    if ($normalized['target_table_column_name'] === '') {
        $errors[] = 'Target Column Name は必須です。';
    } elseif (mb_strlen($normalized['target_table_column_name']) > 191) {
        $errors[] = 'Target Column Name は 191 文字以内にしてください。';
    }

    if (!in_array($normalized['parameter_type'], app_allowed_db_access_update_delete_where_parameter_types(), true)) {
        $errors[] = 'Parameter Type が不正です。';
    }

    if (!in_array($normalized['parameter_data_type'], $allowedParameterDataTypes, true)) {
        $errors[] = 'Parameter Data Type が不正です。';
    }

    if (mb_strlen($normalized['fixed_parameter']) > 4000) {
        $errors[] = 'Fixed Parameter は 4000 文字以内にしてください。';
    }

    if ($normalized['field_list_order'] === '') {
        $normalized['field_list_order'] = '0';
    }
    if (!ctype_digit($normalized['field_list_order'])) {
        $errors[] = 'Field List Order は数値である必要があります。';
    }

    if (!in_array($normalized['source_of_truth'], app_allowed_db_access_source_of_truths(), true)) {
        $errors[] = 'source_of_truth が不正です。';
    }

    if ($normalized['parameter_type'] === 'fixed') {
        if ($normalized['fixed_parameter'] === '') {
            $errors[] = 'Parameter Type が fixed の場合は Fixed Parameter が必須です。';
        }
    } else {
        $normalized['fixed_parameter'] = '';
    }

    if (!$allowFileParameterDataType && $normalized['parameter_data_type'] === 'file') {
        $errors[] = 'この function では file data type は利用できません。';
    }

    if ($normalized['parameter_data_type'] === 'file' && !$blobRuntimeContractSupported) {
        $errors[] = 'file data type を使うには legacy method source に prepare()/bind_param("b")/send_long_data() が必要です。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     name:string
 * } $input
 * @return array{
 *     input:array{
 *         name:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_table_metadata_item_form(array $input): array
{
    $normalized = [
        'name' => trim((string) ($input['name'] ?? '')),
    ];

    $errors = [];

    if ($normalized['name'] === '') {
        $errors[] = 'table name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 255) {
        $errors[] = 'table name は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['name']) === 1) {
        $errors[] = 'table name に制御文字、/、\\ は使えません。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     name:string,
 *     datatype:string,
 *     is_null:string,
 *     is_key:string,
 *     is_default:string,
 *     extra:string,
 *     memo:string
 * } $input
 * @return array{
 *     input:array{
 *         name:string,
 *         datatype:string,
 *         is_null:string,
 *         is_key:string,
 *         is_default:string,
 *         extra:string,
 *         memo:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_table_metadata_column_form(array $input): array
{
    $normalized = [
        'name' => trim((string) ($input['name'] ?? '')),
        'datatype' => trim((string) ($input['datatype'] ?? '')),
        'is_null' => trim((string) ($input['is_null'] ?? '')),
        'is_key' => trim((string) ($input['is_key'] ?? '')),
        'is_default' => trim((string) ($input['is_default'] ?? '')),
        'extra' => trim((string) ($input['extra'] ?? '')),
        'memo' => trim((string) ($input['memo'] ?? '')),
    ];

    $errors = [];

    if ($normalized['name'] === '') {
        $errors[] = 'column name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 255) {
        $errors[] = 'column name は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['name']) === 1) {
        $errors[] = 'column name に制御文字、/、\\ は使えません。';
    }

    if ($normalized['datatype'] === '') {
        $errors[] = 'datatype は必須です。';
    } elseif (mb_strlen($normalized['datatype']) > 255) {
        $errors[] = 'datatype は 255 文字以内にしてください。';
    }

    if (mb_strlen($normalized['is_null']) > 255) {
        $errors[] = 'IsNull は 255 文字以内にしてください。';
    }

    if (mb_strlen($normalized['is_key']) > 255) {
        $errors[] = 'IsKey は 255 文字以内にしてください。';
    }

    if (mb_strlen($normalized['is_default']) > 255) {
        $errors[] = 'IsDefault は 255 文字以内にしてください。';
    }

    if (mb_strlen($normalized['extra']) > 255) {
        $errors[] = 'Extra は 255 文字以内にしてください。';
    }

    if (mb_strlen($normalized['memo']) > 255) {
        $errors[] = 'memo は 255 文字以内にしてください。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     name:string,
 *     store_base_path:string,
 *     is_autoload:string,
 *     inherit_parent_data_class_name:string
 * } $input
 * @return array{
 *     input:array{
 *         name:string,
 *         store_base_path:string,
 *         is_autoload:string,
 *         inherit_parent_data_class_name:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_data_class_metadata_item_form(array $input): array
{
    $normalized = [
        'name' => trim((string) ($input['name'] ?? '')),
        'store_base_path' => trim((string) ($input['store_base_path'] ?? '')),
        'is_autoload' => trim((string) ($input['is_autoload'] ?? '0')) === '1' ? '1' : '0',
        'inherit_parent_data_class_name' => trim((string) ($input['inherit_parent_data_class_name'] ?? '')),
    ];

    $errors = [];

    if ($normalized['name'] === '') {
        $errors[] = 'data class name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 255) {
        $errors[] = 'data class name は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['name']) === 1) {
        $errors[] = 'data class name に制御文字、/、\\ は使えません。';
    }

    if (mb_strlen($normalized['store_base_path']) > 4000) {
        $errors[] = 'StoreBasePath は 4000 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f]/u', $normalized['store_base_path']) === 1) {
        $errors[] = 'StoreBasePath に制御文字は使えません。';
    }

    if (mb_strlen($normalized['inherit_parent_data_class_name']) > 255) {
        $errors[] = 'InheritParentDataClassName は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['inherit_parent_data_class_name']) === 1) {
        $errors[] = 'InheritParentDataClassName に制御文字、/、\\ は使えません。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}

/**
 * @param array{
 *     name:string,
 *     datatype:string,
 *     ref_data_class_name:string,
 *     ref_data_class_field_name:string
 * } $input
 * @return array{
 *     input:array{
 *         name:string,
 *         datatype:string,
 *         ref_data_class_name:string,
 *         ref_data_class_field_name:string
 *     },
 *     errors:list<string>
 * }
 */
function app_validate_data_class_metadata_field_form(array $input): array
{
    $normalized = [
        'name' => trim((string) ($input['name'] ?? '')),
        'datatype' => trim((string) ($input['datatype'] ?? '')),
        'ref_data_class_name' => trim((string) ($input['ref_data_class_name'] ?? '')),
        'ref_data_class_field_name' => trim((string) ($input['ref_data_class_field_name'] ?? '')),
    ];

    $errors = [];

    if ($normalized['name'] === '') {
        $errors[] = 'field name は必須です。';
    } elseif (mb_strlen($normalized['name']) > 255) {
        $errors[] = 'field name は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['name']) === 1) {
        $errors[] = 'field name に制御文字、/、\\ は使えません。';
    }

    if ($normalized['datatype'] === '') {
        $errors[] = 'datatype は必須です。';
    } elseif (mb_strlen($normalized['datatype']) > 255) {
        $errors[] = 'datatype は 255 文字以内にしてください。';
    }

    if (mb_strlen($normalized['ref_data_class_name']) > 255) {
        $errors[] = 'RefDataClassName は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['ref_data_class_name']) === 1) {
        $errors[] = 'RefDataClassName に制御文字、/、\\ は使えません。';
    }

    if (mb_strlen($normalized['ref_data_class_field_name']) > 255) {
        $errors[] = 'RefDataClassFieldName は 255 文字以内にしてください。';
    }

    if (preg_match('/[\x00-\x1f\x7f\/\\\\]/u', $normalized['ref_data_class_field_name']) === 1) {
        $errors[] = 'RefDataClassFieldName に制御文字、/、\\ は使えません。';
    }

    return [
        'input' => $normalized,
        'errors' => $errors,
    ];
}
