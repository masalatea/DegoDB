<?php

declare(strict_types=1);

/**
 * @param array{
 *     path:string
 * } $request
 * @return array{
 *     name:string,
 *     params:array<string,string>
 * }
 */
function app_route_match(array $request): array
{
    $exactRoute = match ($request['path']) {
        '/', '/index.php' => 'bootstrap',
        '/health', '/health.php' => 'health',
        '/login', '/login.php' => 'login',
        '/auth/oidc/callback' => 'auth_oidc_callback',
        '/logout', '/logout.php' => 'logout',
        '/dashboard', '/dashboard.php' => 'dashboard',
        '/projects', '/projects.php' => 'projects',
        '/experiments', '/experiments.php' => 'experiments',
        default => '',
    };

    if ($exactRoute !== '') {
        return [
            'name' => $exactRoute,
            'params' => [],
        ];
    }

    if (preg_match('#^/api/runs/endpoints/([0-9]{8}-[0-9]{6}-[a-f0-9]{8})/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_endpoint_job_api',
            'params' => [
                'job_key' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/runs/endpoints/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_endpoint',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/runs/proxy/([^/]+)/([^/]+)/([^/]+\.php)$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_published_single_proxy',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'source_output_key' => rawurldecode(trim($matches[2])),
                'endpoint_filename' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/api/runs/builds/([0-9]{8}-[0-9]{6}-[a-f0-9]{8})/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_build_job_api',
            'params' => [
                'job_key' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/runs/builds/([0-9]{8}-[0-9]{6}-[a-f0-9]{8})/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_build_job',
            'params' => [
                'job_key' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/runs/builds/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_build',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/runs/swagger/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_swagger',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/samples/sample18-task-board/?$#', $request['path']) === 1) {
        return [
            'name' => 'lab_sample18_task_board',
            'params' => [],
        ];
    }

    if (preg_match('#^/api/runs/compare-output/([0-9]{8}-[0-9]{6}-[a-f0-9]{8})/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_compare_output_job_api',
            'params' => [
                'job_key' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/runs/compare-output/([0-9]{8}-[0-9]{6}-[a-f0-9]{8})/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_compare_output_job',
            'params' => [
                'job_key' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/runs/compare-output/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'lab_compare_output',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/settings/html-templates/([0-9]+)/parameters/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'html_template_parameters',
            'params' => [
                'legacy_template_pid' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/settings/html-templates/([0-9]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'html_template_detail',
            'params' => [
                'legacy_template_pid' => trim($matches[1]),
            ],
        ];
    }

    if (preg_match('#^/settings/html-templates/?$#', $request['path']) === 1) {
        return [
            'name' => 'html_templates',
            'params' => [],
        ];
    }

    if (preg_match('#^/settings/database-sources/?$#', $request['path']) === 1) {
        return [
            'name' => 'database_sources',
            'params' => [],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/settings/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_settings',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/security/users/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_security_users',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/security/pages/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_security_pages',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/security/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_security',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/host-assignments/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_host_assignments',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/language-resources/groups/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_language_resource_groups',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/language-resources/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_language_resource_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'resource_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/language-resources/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_language_resources',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/source-outputs/artifacts/([^/]+)/download/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_source_output_download',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'artifact_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/source-outputs/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_source_outputs',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/source-outputs/change-order/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_source_output_change_order',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/source-outputs/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_source_output_new',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/source-outputs/([^/]+)/edit/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_source_output_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'source_output_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/source-outputs/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_source_output_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'source_output_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/compare-output-settings/additional-paths/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_compare_output_additional_paths',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/compare-output-settings/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_compare_output_settings',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/proxy/single/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_single_proxy',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/proxy/custom/([^/]+)/functions/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_custom_proxy_functions',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'custom_proxy_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/proxy/custom/([^/]+)/endpoint/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_custom_proxy_endpoint',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'custom_proxy_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/proxy/custom/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_custom_proxy_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'custom_proxy_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/proxy/custom/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_custom_proxies',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/html/([^/]+)/parameters/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_html_parameters',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'html_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/html/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_html_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'html_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/html/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_htmls',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_tables',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/import/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_tables_import',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/([^/]+)/columns/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_table_column_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'table_key' => rawurldecode(trim($matches[2])),
                'column_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/([^/]+)/columns/([^/]+)/edit/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_table_column_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'table_key' => rawurldecode(trim($matches[2])),
                'column_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/([^/]+)/columns/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_table_columns',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'table_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/([^/]+)/edit/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_table_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'table_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/tables/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_table_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'table_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_classes',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/sync/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_classes_sync',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/([^/]+)/fields/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_class_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'data_class_key' => rawurldecode(trim($matches[2])),
                'field_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/([^/]+)/fields/([^/]+)/edit/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_class_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'data_class_key' => rawurldecode(trim($matches[2])),
                'field_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/([^/]+)/fields/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_class_fields',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'data_class_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/([^/]+)/edit/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_class_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'data_class_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/([^/]+)/source/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_class_source',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'data_class_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/data-classes/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_data_class_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'data_class_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/sync/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_sync',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/source/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_source',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-where/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_where_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'select_where_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-where/input-aid/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_where_input_aid',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-where/change-order/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_where_change_order',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-where/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_where_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'select_where_key' => rawurldecode(trim($matches[4])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-where/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_where',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-target-fields/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_target_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'select_target_field_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-target-fields/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_target_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'select_target_field_key' => rawurldecode(trim($matches[4])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-target-fields/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_target_fields',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-having/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_having_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'select_having_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-having/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_having_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'select_having_key' => rawurldecode(trim($matches[4])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/select-having/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_select_having',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-delete-where/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_delete_where_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'update_delete_where_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-delete-where/input-aid/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_delete_where_input_aid',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-delete-where/change-order/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_delete_where_change_order',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-delete-where/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_delete_where_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'update_delete_where_key' => rawurldecode(trim($matches[4])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-delete-where/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_delete_where',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/insert-target-fields/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_insert_target_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'insert_target_field_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/insert-target-fields/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_insert_target_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'insert_target_field_key' => rawurldecode(trim($matches[4])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/insert-target-fields/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_insert_target_fields',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-target-fields/new/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_target_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'update_target_field_key' => '',
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-target-fields/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_target_field_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
                'update_target_field_key' => rawurldecode(trim($matches[4])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/update-target-fields/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_update_target_fields',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/endpoint/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_endpoint',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/change-order/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_change_order',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/move/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_move',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_function_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
                'function_key' => rawurldecode(trim($matches[3])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/functions/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_functions',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/edit/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_edit',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/source/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_source',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/db-access/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_db_access_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
                'db_access_key' => rawurldecode(trim($matches[2])),
            ],
        ];
    }

    if (preg_match('#^/projects/([^/]+)/?$#', $request['path'], $matches) === 1) {
        return [
            'name' => 'project_detail',
            'params' => [
                'project_key' => strtoupper(trim($matches[1])),
            ],
        ];
    }

    return [
        'name' => 'not_found',
        'params' => [],
    ];
}

/**
 * @param array{
 *     path:string
 * } $request
 */
function app_route_name(array $request): string
{
    return app_route_match($request)['name'];
}

/**
 * @param array{
 *     route_params?:array<string,string>
 * } $request
 */
function app_route_param(array $request, string $name, string $default = ''): string
{
    $params = $request['route_params'] ?? [];
    if (!is_array($params)) {
        return $default;
    }

    $value = $params[$name] ?? null;
    if (!is_string($value) || $value === '') {
        return $default;
    }

    return $value;
}

function app_route_requires_auth(string $routeName): bool
{
    return in_array($routeName, ['dashboard', 'projects', 'project_detail', 'project_settings', 'project_security', 'project_security_users', 'project_security_pages', 'project_host_assignments', 'project_source_outputs', 'project_source_output_change_order', 'project_source_output_new', 'project_source_output_detail', 'project_source_output_edit', 'project_source_output_download', 'project_compare_output_settings', 'project_compare_output_additional_paths', 'project_custom_proxies', 'project_custom_proxy_detail', 'project_custom_proxy_endpoint', 'project_custom_proxy_functions', 'project_single_proxy', 'project_htmls', 'project_html_detail', 'project_html_parameters', 'html_templates', 'html_template_detail', 'html_template_parameters', 'project_tables', 'project_tables_import', 'project_table_detail', 'project_table_edit', 'project_table_columns', 'project_table_column_edit', 'project_data_classes', 'project_data_classes_sync', 'project_data_class_detail', 'project_data_class_edit', 'project_data_class_fields', 'project_data_class_field_edit', 'project_data_class_source', 'project_db_access', 'project_db_access_sync', 'project_db_access_detail', 'project_db_access_edit', 'project_db_access_functions', 'project_db_access_source', 'project_db_access_function_detail', 'project_db_access_function_source', 'project_db_access_function_endpoint', 'experiments', 'lab_build', 'lab_compare_output', 'lab_endpoint', 'lab_published_single_proxy', 'lab_swagger', 'lab_sample18_task_board'], true);
}
