<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_output_data_class_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_db_access_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_proxy_generator.php';

use PHPUnit\Framework\TestCase;

final class GeneratedNamePolicyOutputTest extends TestCase
{
    public function testDataClassGeneratorKeepsDefaultNamesWithoutOptIn(): void
    {
        $previous = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY');

        try {
            self::assertSame(
                'support_ticket',
                app_project_output_data_class_output_class_name([
                    'name' => 'support_ticket',
                    'physical_name' => 'support_ticket',
                ]),
            );
            self::assertSame(
                'updated_at',
                app_project_output_data_class_output_field_name([
                    'name' => 'updated_at',
                    'physical_name' => 'updated_at',
                ]),
            );
            self::assertSame(
                'support_ticket',
                app_project_output_db_access_output_source_name([
                    'source_name' => 'support_ticket',
                    'physical_name' => 'support_ticket',
                ]),
            );
            self::assertSame(
                'dbaccess-support_ticket.php',
                app_project_output_db_access_wrapper_relative_path('', 'support_ticket'),
            );
            self::assertSame(
                'support_ticket',
                app_project_output_single_proxy_output_source_name([
                    'source_name' => 'support_ticket',
                    'physical_name' => 'support_ticket',
                ]),
            );
            self::assertSame(
                'Getlab_experimentsList',
                app_project_output_single_proxy_output_function_name([
                    'source_name' => 'lab_experiments',
                    'physical_name' => 'lab_experiments',
                    'function_name' => 'Getlab_experimentsList',
                ]),
            );
            self::assertSame(
                'proxyserver-lab_experiments-Getlab_experimentsList.php',
                app_project_output_single_proxy_output_endpoint_filename([
                    'source_name' => 'lab_experiments',
                    'physical_name' => 'lab_experiments',
                    'function_name' => 'Getlab_experimentsList',
                ]),
            );
        } finally {
            if ($previous === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previous);
            }
        }
    }

    public function testDataClassGeneratorUsesPhysicalNameWhenPolicyIsOptedIn(): void
    {
        $previous = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            self::assertSame(
                'SupportTicket',
                app_project_output_data_class_output_class_name([
                    'name' => 'SupportTicket',
                    'physical_name' => 'support_ticket',
                ]),
            );
            self::assertSame(
                'updatedAt',
                app_project_output_data_class_output_field_name([
                    'name' => 'UpdatedAt',
                    'physical_name' => 'updated_at',
                ]),
            );
            self::assertSame(
                'SupportTicket',
                app_project_output_db_access_output_source_name([
                    'source_name' => 'SupportTicket',
                    'physical_name' => 'support_ticket',
                ]),
            );
            self::assertSame(
                'dbaccess-SupportTicket.php',
                app_project_output_db_access_wrapper_relative_path('', 'SupportTicket'),
            );
            self::assertSame(
                'LabExperiments',
                app_project_output_single_proxy_output_source_name([
                    'source_name' => 'lab_experiments',
                    'physical_name' => 'lab_experiments',
                ]),
            );
            self::assertSame(
                'GetLabExperimentsList',
                app_project_output_single_proxy_output_function_name([
                    'source_name' => 'lab_experiments',
                    'physical_name' => 'lab_experiments',
                    'function_name' => 'Getlab_experimentsList',
                ]),
            );
            self::assertSame(
                'proxyserver-LabExperiments-GetLabExperimentsList.php',
                app_project_output_single_proxy_output_endpoint_filename([
                    'source_name' => 'lab_experiments',
                    'physical_name' => 'lab_experiments',
                    'function_name' => 'Getlab_experimentsList',
                ]),
            );
            self::assertSame(
                [
                    'endpoint_filename' => 'proxyserver-LabExperiments-GetLabExperimentsList.php',
                    'handler_class' => 'LabExperimentsGetLabExperimentsListProxyHandler',
                ],
                array_intersect_key(
                    app_project_output_single_proxy_enrich_item(
                        [
                            'source_name' => 'lab_experiments',
                            'physical_name' => 'lab_experiments',
                            'function_name' => 'Getlab_experimentsList',
                            'display_name' => 'lab_experiments.Getlab_experimentsList',
                            'signature' => 'public function Getlab_experimentsList(int $limit)',
                            'action_type' => 'SELECTLIST',
                            'function_list_order' => '1',
                            'select_wheres' => [],
                            'auth_policy' => [
                                'strategy_key' => 'no-security',
                                'summary' => '',
                            ],
                        ],
                        [
                            'data_class' => 'LabExperimentsData',
                            'data_list_class' => 'LabExperimentsDataList',
                            'dbaccess_class' => 'LabExperimentsDBAccess',
                        ],
                    ),
                    [
                        'endpoint_filename' => true,
                        'handler_class' => true,
                    ],
                ),
            );
        } finally {
            if ($previous === false) {
                putenv('MTOOL_GENERATED_NAME_POLICY');
            } else {
                putenv('MTOOL_GENERATED_NAME_POLICY=' . $previous);
            }
        }
    }
}
