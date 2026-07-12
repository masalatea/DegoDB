<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/request.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/auth_oidc.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/health.php';
require_once __DIR__ . '/bootstrap_page.php';
require_once __DIR__ . '/login_page.php';
require_once __DIR__ . '/dashboard_page.php';
require_once __DIR__ . '/project_list_page.php';
require_once __DIR__ . '/project_detail_page.php';
require_once __DIR__ . '/project_settings_page.php';
require_once __DIR__ . '/project_security_page.php';
require_once __DIR__ . '/project_security_users_page.php';
require_once __DIR__ . '/project_security_pages_page.php';
require_once __DIR__ . '/project_host_assignments_page.php';
require_once __DIR__ . '/project_language_resources_page.php';
require_once __DIR__ . '/project_language_resource_groups_page.php';
require_once __DIR__ . '/project_language_resource_detail_page.php';
require_once __DIR__ . '/project_shared_contracts_page.php';
require_once __DIR__ . '/schema_proposal_review_page.php';
require_once __DIR__ . '/schema_proposal_task_review_page.php';
require_once __DIR__ . '/material_insight_preview_page.php';
require_once __DIR__ . '/material_insight_no_code_handoff_preview_page.php';
require_once __DIR__ . '/project_source_outputs_page.php';
require_once __DIR__ . '/no_code_mtool_source_output_inspection_page.php';
require_once __DIR__ . '/project_source_output_change_order_page.php';
require_once __DIR__ . '/project_source_output_new_page.php';
require_once __DIR__ . '/project_source_output_detail_page.php';
require_once __DIR__ . '/project_source_output_edit_page.php';
require_once __DIR__ . '/project_source_output_operation_page.php';
require_once __DIR__ . '/project_source_output_artifact_detail_page.php';
require_once __DIR__ . '/project_source_output_download_page.php';
require_once __DIR__ . '/no_code_public_runtime_page.php';
require_once __DIR__ . '/project_sync_outbox_detail_page.php';
require_once __DIR__ . '/project_compare_output_settings_page.php';
require_once __DIR__ . '/project_compare_output_additional_paths_page.php';
require_once __DIR__ . '/project_custom_proxies_page.php';
require_once __DIR__ . '/project_custom_proxy_detail_page.php';
require_once __DIR__ . '/project_custom_proxy_endpoint_page.php';
require_once __DIR__ . '/project_custom_proxy_functions_page.php';
require_once __DIR__ . '/project_single_proxy_page.php';
require_once __DIR__ . '/project_htmls_page.php';
require_once __DIR__ . '/project_html_detail_page.php';
require_once __DIR__ . '/project_html_parameters_page.php';
require_once __DIR__ . '/html_templates_page.php';
require_once __DIR__ . '/html_template_detail_page.php';
require_once __DIR__ . '/html_template_parameters_page.php';
require_once __DIR__ . '/database_sources_page.php';
require_once __DIR__ . '/lab_build_page.php';
require_once __DIR__ . '/lab_build_job_page.php';
require_once __DIR__ . '/lab_build_job_api_page.php';
require_once __DIR__ . '/lab_published_single_proxy_page.php';
require_once __DIR__ . '/lab_swagger_page.php';
require_once __DIR__ . '/lab_sample18_task_board_page.php';
require_once __DIR__ . '/lab_endpoint_test_page.php';
require_once __DIR__ . '/lab_endpoint_test_job_api_page.php';
require_once __DIR__ . '/lab_compare_output_page.php';
require_once __DIR__ . '/lab_compare_output_job_page.php';
require_once __DIR__ . '/lab_compare_output_job_api_page.php';
require_once __DIR__ . '/project_tables_page.php';
require_once __DIR__ . '/project_tables_import_page.php';
require_once __DIR__ . '/project_table_detail_page.php';
require_once __DIR__ . '/project_table_edit_page.php';
require_once __DIR__ . '/project_table_columns_page.php';
require_once __DIR__ . '/project_table_column_edit_page.php';
require_once __DIR__ . '/project_data_classes_page.php';
require_once __DIR__ . '/project_data_classes_sync_page.php';
require_once __DIR__ . '/project_data_class_detail_page.php';
require_once __DIR__ . '/project_data_class_edit_page.php';
require_once __DIR__ . '/project_data_class_fields_page.php';
require_once __DIR__ . '/project_data_class_field_edit_page.php';
require_once __DIR__ . '/project_data_class_source_page.php';
require_once __DIR__ . '/project_db_access_page.php';
require_once __DIR__ . '/project_db_access_sync_page.php';
require_once __DIR__ . '/project_db_access_detail_page.php';
require_once __DIR__ . '/project_db_access_edit_page.php';
require_once __DIR__ . '/project_db_access_functions_page.php';
require_once __DIR__ . '/project_db_access_function_change_order_page.php';
require_once __DIR__ . '/project_db_access_source_page.php';
require_once __DIR__ . '/project_db_access_function_detail_page.php';
require_once __DIR__ . '/project_db_access_function_move_page.php';
require_once __DIR__ . '/project_db_access_function_select_where_page.php';
require_once __DIR__ . '/project_db_access_function_select_where_input_aid_page.php';
require_once __DIR__ . '/project_db_access_function_select_where_change_order_page.php';
require_once __DIR__ . '/project_db_access_function_select_where_edit_page.php';
require_once __DIR__ . '/project_db_access_function_select_target_fields_page.php';
require_once __DIR__ . '/project_db_access_function_select_target_field_edit_page.php';
require_once __DIR__ . '/project_db_access_function_select_having_page.php';
require_once __DIR__ . '/project_db_access_function_select_having_edit_page.php';
require_once __DIR__ . '/project_db_access_function_update_delete_where_page.php';
require_once __DIR__ . '/project_db_access_function_update_delete_where_input_aid_page.php';
require_once __DIR__ . '/project_db_access_function_update_delete_where_change_order_page.php';
require_once __DIR__ . '/project_db_access_function_update_delete_where_edit_page.php';
require_once __DIR__ . '/project_db_access_function_insert_target_fields_page.php';
require_once __DIR__ . '/project_db_access_function_insert_target_field_edit_page.php';
require_once __DIR__ . '/project_db_access_function_update_target_fields_page.php';
require_once __DIR__ . '/project_db_access_function_update_target_field_edit_page.php';
require_once __DIR__ . '/project_db_access_function_source_page.php';
require_once __DIR__ . '/project_db_access_function_endpoint_page.php';
require_once __DIR__ . '/experiment_list_page.php';
require_once __DIR__ . '/error_page.php';

function app_run_http_request(): void
{
    $app = app_bootstrap();
    app_boot_session($app);
    $request = app_request_context();
    $route = app_route_match($request);
    $request['route_params'] = $route['params'];
    $routeName = $route['name'];

    try {
        if (app_handle_before_dispatch($app, $request, $routeName)) {
            return;
        }

        switch ($routeName) {
            case 'bootstrap':
                app_render_bootstrap_page($app, app_probe_database($app), $request);
                return;

            case 'health':
                $databaseStatus = app_probe_database($app);
                app_render_health_json(
                    $request,
                    app_health_payload($app, $request, $databaseStatus),
                );
                return;

            case 'login':
                app_handle_login_request($app, $request);
                return;

            case 'auth_oidc_callback':
                app_auth_oidc_handle_callback($app, $request);
                return;

            case 'logout':
                app_handle_logout_request($app, $request);
                return;

            case 'dashboard':
                app_render_dashboard_page($app, $request);
                return;

            case 'projects':
                app_render_project_list_page($app, $request);
                return;

            case 'project_detail':
                app_render_project_detail_page($app, $request);
                return;

            case 'project_settings':
                app_render_project_settings_page($app, $request);
                return;

            case 'project_security':
                app_render_project_security_page($app, $request);
                return;

            case 'project_security_users':
                app_render_project_security_users_page($app, $request);
                return;

            case 'project_security_pages':
                app_render_project_security_pages_page($app, $request);
                return;

            case 'project_host_assignments':
                app_render_project_host_assignments_page($app, $request);
                return;

            case 'project_language_resources':
                app_render_project_language_resources_page($app, $request);
                return;

            case 'project_language_resource_groups':
                app_render_project_language_resource_groups_page($app, $request);
                return;

            case 'project_language_resource_detail':
                app_render_project_language_resource_detail_page($app, $request);
                return;

            case 'project_shared_contracts':
                app_render_project_shared_contracts_page($app, $request);
                return;

            case 'project_schema_proposal_review':
                app_render_schema_proposal_review_page($app, $request);
                return;

            case 'project_schema_proposal_task_review':
                app_render_schema_proposal_task_review_page($app, $request);
                return;

            case 'project_sample19_material_insight_preview':
                app_render_material_insight_preview_page($app, $request);
                return;

            case 'project_sample19_material_insight_no_code_handoff_preview':
                app_render_material_insight_no_code_handoff_preview_page($app, $request);
                return;

            case 'project_source_outputs':
                app_render_project_source_outputs_page($app, $request);
                return;

            case 'project_source_outputs_no_code_inspection':
                app_render_no_code_mtool_source_output_inspection_page($app, $request);
                return;

            case 'project_source_output_change_order':
                app_render_project_source_output_change_order_page($app, $request);
                return;

            case 'project_source_output_new':
                app_render_project_source_output_new_page($app, $request);
                return;

            case 'project_source_output_detail':
                app_render_project_source_output_detail_page($app, $request);
                return;

            case 'project_source_output_edit':
                app_render_project_source_output_edit_page($app, $request);
                return;

            case 'project_source_output_operation':
                app_handle_project_source_output_operation_request($app, $request);
                return;

            case 'project_source_output_artifact_detail':
                app_render_project_source_output_artifact_detail_page($app, $request);
                return;

            case 'project_source_output_download':
                app_render_project_source_output_download_page($app, $request);
                return;

            case 'no_code_public_runtime_preview':
                app_render_no_code_public_runtime_preview_page($app, $request);
                return;

            case 'no_code_public_runtime_current_preview':
                app_render_no_code_public_runtime_current_preview_page($app, $request);
                return;

            case 'no_code_public_runtime_alias_preview':
                app_render_no_code_public_runtime_alias_preview_page($app, $request);
                return;

            case 'no_code_public_runtime_execution':
                app_render_no_code_public_runtime_execution_page($app, $request);
                return;

            case 'no_code_public_runtime_action_availability':
                app_render_no_code_public_runtime_action_availability_page($app, $request);
                return;

            case 'no_code_public_runtime_current_action_availability':
                app_render_no_code_public_runtime_current_action_availability_page($app, $request);
                return;

            case 'no_code_public_runtime_alias_action_availability':
                app_render_no_code_public_runtime_alias_action_availability_page($app, $request);
                return;

            case 'no_code_public_runtime_current_execution':
                app_render_no_code_public_runtime_current_execution_page($app, $request);
                return;

            case 'no_code_public_runtime_current_data':
                app_render_no_code_public_runtime_current_data_page($app, $request);
                return;

            case 'no_code_public_runtime_alias_execution':
                app_render_no_code_public_runtime_alias_execution_page($app, $request);
                return;

            case 'no_code_public_runtime_alias_data':
                app_render_no_code_public_runtime_alias_data_page($app, $request);
                return;

            case 'project_sync_outbox_detail':
                app_render_project_sync_outbox_detail_page($app, $request);
                return;

            case 'project_sync_outbox_status_json':
                app_render_project_sync_outbox_status_json_page($app, $request);
                return;

            case 'project_compare_output_settings':
                app_render_project_compare_output_settings_page($app, $request);
                return;

            case 'project_compare_output_additional_paths':
                app_render_project_compare_output_additional_paths_page($app, $request);
                return;

            case 'project_custom_proxies':
                app_render_project_custom_proxies_page($app, $request);
                return;

            case 'project_custom_proxy_detail':
                app_render_project_custom_proxy_detail_page($app, $request);
                return;

            case 'project_custom_proxy_endpoint':
                app_render_project_custom_proxy_endpoint_page($app, $request);
                return;

            case 'project_custom_proxy_functions':
                app_render_project_custom_proxy_functions_page($app, $request);
                return;

            case 'project_single_proxy':
                app_render_project_single_proxy_page($app, $request);
                return;

            case 'project_htmls':
                app_render_project_htmls_page($app, $request);
                return;

            case 'project_html_detail':
                app_render_project_html_detail_page($app, $request);
                return;

            case 'project_html_parameters':
                app_render_project_html_parameters_page($app, $request);
                return;

            case 'html_templates':
                app_render_html_templates_page($app, $request);
                return;

            case 'html_template_detail':
                app_render_html_template_detail_page($app, $request);
                return;

            case 'html_template_parameters':
                app_render_html_template_parameters_page($app, $request);
                return;

            case 'database_sources':
                app_render_database_sources_page($app, $request);
                return;

            case 'lab_build':
                app_render_lab_build_page($app, $request);
                return;

            case 'lab_build_job':
                app_render_lab_build_job_page($app, $request);
                return;

            case 'lab_build_job_api':
                app_render_lab_build_job_api_page($app, $request);
                return;

            case 'lab_published_single_proxy':
                app_render_lab_published_single_proxy_page($app, $request);
                return;

            case 'lab_swagger':
                app_render_lab_swagger_page($app, $request);
                return;

            case 'lab_sample18_task_board':
                app_render_lab_sample18_task_board_page($app, $request);
                return;

            case 'lab_sample18_task_board_generated_submit':
                app_render_lab_sample18_task_board_generated_submit_page($app, $request);
                return;

            case 'lab_endpoint':
                app_render_lab_endpoint_test_page($app, $request);
                return;

            case 'lab_endpoint_job_api':
                app_render_lab_endpoint_test_job_api_page($app, $request);
                return;

            case 'lab_compare_output':
                app_render_lab_compare_output_page($app, $request);
                return;

            case 'lab_compare_output_job':
                app_render_lab_compare_output_job_page($app, $request);
                return;

            case 'lab_compare_output_job_api':
                app_render_lab_compare_output_job_api_page($app, $request);
                return;

            case 'project_tables':
                app_render_project_tables_page($app, $request);
                return;

            case 'project_tables_import':
                app_render_project_tables_import_page($app, $request);
                return;

            case 'project_table_detail':
                app_render_project_table_detail_page($app, $request);
                return;

            case 'project_table_edit':
                app_render_project_table_edit_page($app, $request);
                return;

            case 'project_table_columns':
                app_render_project_table_columns_page($app, $request);
                return;

            case 'project_table_column_edit':
                app_render_project_table_column_edit_page($app, $request);
                return;

            case 'project_data_classes':
                app_render_project_data_classes_page($app, $request);
                return;

            case 'project_data_classes_sync':
                app_render_project_data_classes_sync_page($app, $request);
                return;

            case 'project_data_class_detail':
                app_render_project_data_class_detail_page($app, $request);
                return;

            case 'project_data_class_edit':
                app_render_project_data_class_edit_page($app, $request);
                return;

            case 'project_data_class_fields':
                app_render_project_data_class_fields_page($app, $request);
                return;

            case 'project_data_class_field_edit':
                app_render_project_data_class_field_edit_page($app, $request);
                return;

            case 'project_data_class_source':
                app_render_project_data_class_source_page($app, $request);
                return;

            case 'project_db_access':
                app_render_project_db_access_page($app, $request);
                return;

            case 'project_db_access_sync':
                app_render_project_db_access_sync_page($app, $request);
                return;

            case 'project_db_access_detail':
                app_render_project_db_access_detail_page($app, $request);
                return;

            case 'project_db_access_edit':
                app_render_project_db_access_edit_page($app, $request);
                return;

            case 'project_db_access_functions':
                app_render_project_db_access_functions_page($app, $request);
                return;

            case 'project_db_access_function_change_order':
                app_render_project_db_access_function_change_order_page($app, $request);
                return;

            case 'project_db_access_source':
                app_render_project_db_access_source_page($app, $request);
                return;

            case 'project_db_access_function_detail':
                app_render_project_db_access_function_detail_page($app, $request);
                return;

            case 'project_db_access_function_move':
                app_render_project_db_access_function_move_page($app, $request);
                return;

            case 'project_db_access_function_select_where':
                app_render_project_db_access_function_select_where_page($app, $request);
                return;

            case 'project_db_access_function_select_where_input_aid':
                app_render_project_db_access_function_select_where_input_aid_page($app, $request);
                return;

            case 'project_db_access_function_select_where_change_order':
                app_render_project_db_access_function_select_where_change_order_page($app, $request);
                return;

            case 'project_db_access_function_select_where_edit':
                app_render_project_db_access_function_select_where_edit_page($app, $request);
                return;

            case 'project_db_access_function_select_target_fields':
                app_render_project_db_access_function_select_target_fields_page($app, $request);
                return;

            case 'project_db_access_function_select_target_field_edit':
                app_render_project_db_access_function_select_target_field_edit_page($app, $request);
                return;

            case 'project_db_access_function_select_having':
                app_render_project_db_access_function_select_having_page($app, $request);
                return;

            case 'project_db_access_function_select_having_edit':
                app_render_project_db_access_function_select_having_edit_page($app, $request);
                return;

            case 'project_db_access_function_update_delete_where':
                app_render_project_db_access_function_update_delete_where_page($app, $request);
                return;

            case 'project_db_access_function_update_delete_where_input_aid':
                app_render_project_db_access_function_update_delete_where_input_aid_page($app, $request);
                return;

            case 'project_db_access_function_update_delete_where_change_order':
                app_render_project_db_access_function_update_delete_where_change_order_page($app, $request);
                return;

            case 'project_db_access_function_update_delete_where_edit':
                app_render_project_db_access_function_update_delete_where_edit_page($app, $request);
                return;

            case 'project_db_access_function_insert_target_fields':
                app_render_project_db_access_function_insert_target_fields_page($app, $request);
                return;

            case 'project_db_access_function_insert_target_field_edit':
                app_render_project_db_access_function_insert_target_field_edit_page($app, $request);
                return;

            case 'project_db_access_function_update_target_fields':
                app_render_project_db_access_function_update_target_fields_page($app, $request);
                return;

            case 'project_db_access_function_update_target_field_edit':
                app_render_project_db_access_function_update_target_field_edit_page($app, $request);
                return;

            case 'project_db_access_function_source':
                app_render_project_db_access_function_source_page($app, $request);
                return;

            case 'project_db_access_function_endpoint':
                app_render_project_db_access_function_endpoint_page($app, $request);
                return;

            case 'experiments':
                app_render_experiment_list_page($app, $request);
                return;

            default:
                app_render_not_found_page($app, $request);
                return;
        }
    } catch (Throwable $throwable) {
        app_handle_unexpected_http_error($app, $request, $throwable);
    }
}
