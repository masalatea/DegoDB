<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/domain_validation.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_db_access_sync_service.php';

use PHPUnit\Framework\TestCase;

final class ProjectDbAccessSyncBootstrapContractTest extends TestCase
{
    public function testCanonicalBootstrapSyncDefaultsSingleProxyAuthAndObjectParameterType(): void
    {
        $input = app_project_db_access_sync_function_input(
            'MTOOL',
            [
                'source_name' => 'lab_experiments',
                'source_kind' => 'canonical-bootstrap',
                'data_file' => 'data-lab_experiments.php',
                'dbaccess_file' => 'dbaccess-lab_experiments.php',
                'data_path' => '',
                'dbaccess_path' => '',
                'has_data_file' => false,
                'has_dbaccess_file' => false,
            ],
            [
                'name' => 'Insertlab_experiments',
                'line' => 30,
                'end_line' => 30,
                'signature' => 'public function Insertlab_experiments($lab_experimentsObj)',
            ],
            null,
        );

        self::assertSame('sync-bootstrap', $input['source_of_truth']);
        self::assertSame('INSERT', $input['action_type']);
        self::assertSame('lab_experiments', $input['target_table_name']);
        self::assertSame('classobject', $input['parameter_type']);
        self::assertSame('NoSecurity', $input['single_proxy_auth_type']);
    }

    public function testCanonicalBootstrapSyncPreservesManualFunctionSettings(): void
    {
        $existingItem = [
            'source_name' => 'lab_experiments',
            'function_name' => 'Insertlab_experiments',
            'function_list_order' => '90',
            'function_suffix' => 'lab_experiments',
            'action_type' => 'INSERT',
            'data_class_base_name' => '',
            'target_table_name' => 'lab_experiments',
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
            'detected_signature' => 'public function Insertlab_experiments($legacyObj)',
            'detected_line' => '90',
            'source_of_truth' => 'manual',
        ];

        $input = app_project_db_access_sync_function_input(
            'MTOOL',
            [
                'source_name' => 'lab_experiments',
                'source_kind' => 'canonical-bootstrap',
                'data_file' => 'data-lab_experiments.php',
                'dbaccess_file' => 'dbaccess-lab_experiments.php',
                'data_path' => '',
                'dbaccess_path' => '',
                'has_data_file' => false,
                'has_dbaccess_file' => false,
            ],
            [
                'name' => 'Insertlab_experiments',
                'line' => 30,
                'end_line' => 30,
                'signature' => 'public function Insertlab_experiments($lab_experimentsObj)',
            ],
            $existingItem,
        );

        self::assertSame('manual', $input['source_of_truth']);
        self::assertSame('', $input['single_proxy_auth_type']);
        self::assertSame('', $input['parameter_type']);
        self::assertSame('90', $input['function_list_order']);
    }

    public function testCanonicalBootstrapSyncDefaultsGenericSingleProxyTargetsOnFirstInsert(): void
    {
        $resolved = app_project_db_access_sync_resolved_target_source_output_keys(
            [
                'source_name' => 'lab_experiments',
                'source_kind' => 'canonical-bootstrap',
            ],
            null,
            [
                [
                    'source_output_key' => 'DBTABLE-PROXY-SERVER',
                    'target_binding_type' => 'single-function-proxy',
                ],
                [
                    'source_output_key' => 'OPENAPI-JSON',
                    'target_binding_type' => 'single-function-proxy',
                ],
                [
                    'source_output_key' => 'DBIMPORT-PROXY-SERVER',
                    'target_binding_type' => 'custom-proxy',
                ],
            ],
            [],
        );

        self::assertSame(['DBTABLE-PROXY-SERVER', 'OPENAPI-JSON'], $resolved);
    }

    public function testCanonicalBootstrapSyncDoesNotReaddTargetsForExistingFunctionWithoutAssignments(): void
    {
        $resolved = app_project_db_access_sync_resolved_target_source_output_keys(
            [
                'source_name' => 'lab_experiments',
                'source_kind' => 'canonical-bootstrap',
            ],
            [
                'source_of_truth' => 'sync-bootstrap',
            ],
            [
                [
                    'source_output_key' => 'DBTABLE-PROXY-SERVER',
                    'target_binding_type' => 'single-function-proxy',
                ],
                [
                    'source_output_key' => 'OPENAPI-JSON',
                    'target_binding_type' => 'single-function-proxy',
                ],
            ],
            [],
        );

        self::assertSame([], $resolved);
    }
}
