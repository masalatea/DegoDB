<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_output_openapi_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_proxy_generator.php';

use PHPUnit\Framework\TestCase;

final class PostgresqlRepresentativeOutputNamingTest extends TestCase
{
    public function testSample13StyleOpenApiUsesGeneratedNamesFromSnakeCasePhysicalNames(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $proxyItem = app_project_output_single_proxy_enrich_item(
                [
                    'source_name' => 'api_task',
                    'physical_name' => 'api_task',
                    'function_name' => 'Getapi_taskList',
                    'display_name' => 'api_task.Getapi_taskList',
                    'signature' => 'public function Getapi_taskList(int $status_id)',
                    'action_type' => 'SELECTLIST',
                    'function_list_order' => '1',
                    'auth_policy' => [
                        'strategy_key' => 'no-security',
                        'summary' => '認証を掛けません。',
                    ],
                ],
                [
                    'data_class' => 'api_task',
                    'data_list_class' => 'api_taskList',
                    'dbaccess_class' => 'ApiTaskDBAccess',
                ],
            );

            $document = app_project_output_openapi_document(
                [
                    'project_key' => 'SAMPLE13',
                    'source_output_key' => 'OPENAPI-JSON',
                    'definition' => [
                        'source_output_key' => 'OPENAPI-JSON',
                        'name' => 'Sample13 OpenAPI JSON',
                        'proxy_base_url' => 'http://127.0.0.1:18222',
                    ],
                    'plan' => [
                        'function_count' => 1,
                        'unresolved_function_count' => 0,
                        'unresolved_auth_count' => 0,
                        'items' => [],
                    ],
                    'source_entities' => [],
                    'proxy_items' => [$proxyItem],
                ],
                [
                    [
                        'name' => 'api_task',
                        'physical_name' => 'api_task',
                        'inherit_parent_data_class_name' => '',
                        'fields' => [
                            [
                                'name' => 'task_title',
                                'physical_name' => 'task_title',
                                'datatype' => 'varchar',
                                'ref_data_class_name' => '',
                                'ref_data_class_field_name' => '',
                            ],
                            [
                                'name' => 'published_at',
                                'physical_name' => 'published_at',
                                'datatype' => 'datetime',
                                'ref_data_class_name' => '',
                                'ref_data_class_field_name' => '',
                            ],
                        ],
                    ],
                ],
            );

            self::assertSame('proxyserver-ApiTask-GetApiTaskList.php', $proxyItem['endpoint_filename']);
            self::assertArrayHasKey('/proxyserver-ApiTask-GetApiTaskList.php', $document['paths']);
            self::assertArrayHasKey('ApiTask', $document['components']['schemas']);
            self::assertArrayHasKey('taskTitle', $document['components']['schemas']['ApiTask']['properties']);
            self::assertArrayHasKey('publishedAt', $document['components']['schemas']['ApiTask']['properties']);
            self::assertSame(
                '#/components/schemas/ApiTask',
                $document['paths']['/proxyserver-ApiTask-GetApiTaskList.php']['post']['responses']['200']['content']['application/json']['schema']['properties']['Result']['items']['$ref'] ?? '',
            );
        } finally {
            $this->restoreEnvValue('MTOOL_GENERATED_NAME_POLICY', $previousPolicy);
        }
    }

    public function testSample16StyleAuthenticatedProxyKeepsAuthPolicyWhileUsingGeneratedEndpointNames(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $proxyItem = app_project_output_single_proxy_enrich_item(
                [
                    'source_name' => 'auth_task',
                    'physical_name' => 'auth_task',
                    'function_name' => 'Getauth_task',
                    'display_name' => 'auth_task.Getauth_task',
                    'signature' => 'public function Getauth_task(int $id)',
                    'action_type' => 'SELECT',
                    'function_list_order' => '1',
                    'auth_policy' => [
                        'strategy_key' => 'static-bearer',
                        'summary' => 'static bearer 認証です。',
                        'single_get_function_name' => '',
                        'policy' => [
                            'token_env' => 'DEGODB_PROXY_BEARER_TOKEN',
                        ],
                    ],
                ],
                [
                    'data_class' => 'AuthTaskData',
                    'data_list_class' => 'AuthTaskDataList',
                    'dbaccess_class' => 'AuthTaskDBAccess',
                ],
            );

            self::assertSame('proxyserver-AuthTask-GetAuthTask.php', $proxyItem['endpoint_filename']);
            self::assertSame('AuthTaskGetAuthTaskProxyHandler', $proxyItem['handler_class']);
            self::assertSame('AuthTaskGetAuthTaskRequestParams', $proxyItem['request_class']);
            self::assertSame('AuthTaskGetAuthTaskProxyResult', $proxyItem['result_class']);
            self::assertSame('static-bearer', $proxyItem['auth_policy']['strategy_key']);
            self::assertSame('select-single', $proxyItem['steps'][0]['action']);
            self::assertSame('AuthTaskData', $proxyItem['steps'][0]['data_class']);
            self::assertSame('AuthTaskDBAccess', $proxyItem['steps'][0]['dbaccess_class']);
        } finally {
            $this->restoreEnvValue('MTOOL_GENERATED_NAME_POLICY', $previousPolicy);
        }
    }

    public function testSample14StyleCustomProxyKeepsUserDefinedEndpointNames(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $proxyItem = app_project_output_proxy_enrich_item(
                [
                    'custom_proxy_key' => 'CATALOG-SUMMARY',
                    'display_name' => 'Catalog Summary',
                    'basename' => 'Catalog',
                    'name' => 'Summary',
                    'in_transaction' => false,
                    'continue_even_if_failed_to_insert' => false,
                    'auth_policy' => [
                        'strategy_key' => 'no-security',
                        'summary' => '認証を掛けません。',
                        'single_get_function_name' => '',
                        'policy' => [],
                    ],
                    'steps' => [
                        [
                            'step_order' => '1',
                            'db_access_source_name' => 'catalog_item',
                            'db_access_function_name' => 'Getcatalog_itemList',
                            'signature' => 'public function Getcatalog_itemList(int $limit)',
                            'line' => 1,
                            'is_list' => true,
                        ],
                    ],
                ],
                [
                    'catalog_item' => [
                        'data_class' => 'CatalogItemData',
                        'data_list_class' => 'CatalogItemDataList',
                        'dbaccess_class' => 'CatalogItemDBAccess',
                    ],
                ],
                'CatalogSummary',
            );

            self::assertSame('proxyserver-Catalog-Summary.php', $proxyItem['endpoint_filename']);
            self::assertSame('CatalogSummaryProxyHandler', $proxyItem['handler_class']);
            self::assertSame('CatalogSummaryProxySummaryRequestParams', $proxyItem['overall_request_class']);
            self::assertSame('catalog_item', $proxyItem['steps'][0]['source_name']);
            self::assertSame('select-list', $proxyItem['steps'][0]['action']);
            self::assertSame('CatalogItemDataList', $proxyItem['steps'][0]['result_data_type']);
        } finally {
            $this->restoreEnvValue('MTOOL_GENERATED_NAME_POLICY', $previousPolicy);
        }
    }

    private function restoreEnvValue(string $name, string|false $previousValue): void
    {
        if ($previousValue === false) {
            putenv($name);
            return;
        }

        putenv($name . '=' . $previousValue);
    }
}
