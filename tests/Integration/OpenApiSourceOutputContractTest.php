<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/domain_validation.php';
require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_published_single_proxy_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_swagger_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_openapi_generator.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class OpenApiSourceOutputContractTest extends TestCase
{
    public function testPublishedSingleProxySourceNameResolutionSupportsSingleProxyBuildPlanItems(): void
    {
        $fixtureRoot = sys_get_temp_dir() . '/mtool-single-proxy-build-plan-' . bin2hex(random_bytes(6));
        mkdir($fixtureRoot, 0777, true);

        try {
            file_put_contents(
                $fixtureRoot . '/build-plan.json',
                json_encode([
                    'items' => [
                        [
                            'source_name' => 'lab_experiments',
                            'function_name' => 'Getlab_experimentsList',
                        ],
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            );

            self::assertSame(
                'lab_experiments',
                app_lab_published_single_proxy_resolve_source_name_from_build_plan(
                    $fixtureRoot,
                    'proxyserver-lab_experiments-Getlab_experimentsList.php',
                ),
            );
        } finally {
            @unlink($fixtureRoot . '/build-plan.json');
            @rmdir($fixtureRoot);
        }
    }

    public function testDomainValidationAndRouteExposeOpenApiArtifacts(): void
    {
        self::assertContains('json', app_allowed_source_output_program_languages());
        self::assertContains('OpenAPI', app_allowed_source_output_class_types());
        self::assertContains('openapi-json', app_allowed_source_output_artifact_strategies());
        self::assertContains('internal-only', app_allowed_source_output_spec_visibilities());
        self::assertContains('disabled', app_allowed_source_output_spec_visibilities());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('openapi-json'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('openapi-json'));
        self::assertSame(
            'single-function-proxy',
            app_source_output_target_binding_scope([
                'artifact_strategy' => 'openapi-json',
                'class_type' => 'OpenAPI',
                'target_binding_type' => '',
            ]),
        );
        self::assertSame(
            'OpenAPI JSON Artifact',
            app_source_output_artifact_strategy_caption('openapi-json'),
        );
        self::assertSame(
            'Internal Only (Authenticated Viewer)',
            app_source_output_spec_visibility_caption('internal-only'),
        );
        self::assertSame('internal-only', app_source_output_effective_spec_visibility([]));

        $route = app_route_match([
            'path' => '/runs/swagger/MTOOL',
        ]);

        self::assertSame('lab_swagger', $route['name']);
        self::assertSame('MTOOL', $route['params']['project_key'] ?? '');

        $proxyRoute = app_route_match([
            'path' => '/runs/proxy/MTOOL/PAYPAL-PROXY-SERVER/proxyserver-Project-GetProjectList.php',
        ]);

        self::assertSame('lab_published_single_proxy', $proxyRoute['name']);
        self::assertSame('MTOOL', $proxyRoute['params']['project_key'] ?? '');
        self::assertSame('PAYPAL-PROXY-SERVER', $proxyRoute['params']['source_output_key'] ?? '');
        self::assertSame(
            'proxyserver-Project-GetProjectList.php',
            $proxyRoute['params']['endpoint_filename'] ?? '',
        );
        self::assertSame(
            '/runs/proxy/MTOOL/PAYPAL-PROXY-SERVER/proxyserver-Project-GetProjectList.php',
            app_lab_published_single_proxy_path(
                'MTOOL',
                'PAYPAL-PROXY-SERVER',
                'proxyserver-Project-GetProjectList.php',
            ),
        );

        $downloadRoute = app_route_match([
            'path' => '/projects/MTOOL/source-outputs/artifacts/20260521-023351-d52e8c8b/download',
        ]);

        self::assertSame('project_source_output_download', $downloadRoute['name']);
        self::assertSame('MTOOL', $downloadRoute['params']['project_key'] ?? '');
        self::assertSame('20260521-023351-d52e8c8b', $downloadRoute['params']['artifact_key'] ?? '');
    }

    public function testOpenApiPublicRawRouteRemainsDeferredAndInternalRoutesRequireAuth(): void
    {
        $labSwaggerPage = $this->readRepoFile('mtool/app/lab_swagger_page.php');
        $downloadPage = $this->readRepoFile('mtool/app/project_source_output_download_page.php');
        $newPage = $this->readRepoFile('mtool/app/project_source_output_new_page.php');
        $detailPage = $this->readRepoFile('mtool/app/project_source_output_detail_page.php');

        self::assertTrue(app_route_requires_auth('lab_swagger'));
        self::assertTrue(app_route_requires_auth('lab_published_single_proxy'));
        self::assertTrue(app_route_requires_auth('project_source_output_download'));
        self::assertSame(
            'not_found',
            app_route_name([
                'path' => '/artifacts/openapi/MTOOL/public-demo-key',
            ]),
        );
        self::assertMatchesRegularExpression(
            '/app_auth_has_any_role\(\[\'lab\', \'admin\'\], \$principal\)/',
            $labSwaggerPage,
        );
        self::assertMatchesRegularExpression(
            '/app_auth_has_any_role\(\[\'admin\', \'config\'\], \$principal\)/',
            $downloadPage,
        );
        self::assertStringContainsString('public raw route や public alias key route はまだ持ちません', $newPage);
        self::assertStringContainsString('public raw route や public alias key route は持ちません', $detailPage);
    }

    public function testSourceOutputValidationAcceptsKnownSpecVisibilityValuesOnly(): void
    {
        $input = app_source_output_form_defaults();
        $input['source_output_key'] = 'OPENAPI-JSON';
        $input['name'] = 'Mtool OpenAPI JSON';
        $input['program_language'] = 'json';
        $input['class_type'] = 'OpenAPI';
        $input['artifact_strategy'] = 'openapi-json';
        $input['target_binding_type'] = 'single-function-proxy';
        $input['spec_visibility'] = 'public-read';

        $invalidValidation = app_validate_source_output_form($input);
        self::assertContains('spec visibility が不正です。', $invalidValidation['errors']);

        $input['spec_visibility'] = 'disabled';
        $validValidation = app_validate_source_output_form($input);
        self::assertSame([], $validValidation['errors']);
        self::assertSame('disabled', $validValidation['input']['spec_visibility']);
    }

    public function testLabSwaggerSupportedSourceOutputsExcludeDisabledSpecVisibility(): void
    {
        $catalog = [
            [
                'source_output_key' => 'OPENAPI-JSON',
                'artifact_strategy' => 'openapi-json',
                'spec_visibility' => 'internal-only',
            ],
            [
                'source_output_key' => 'OPENAPI-HIDDEN',
                'artifact_strategy' => 'openapi-json',
                'spec_visibility' => 'disabled',
            ],
            [
                'source_output_key' => 'OPENAPI-LEGACY',
                'artifact_strategy' => 'openapi-json',
            ],
            [
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'artifact_strategy' => 'generated-bootstrap-dbclasses',
                'spec_visibility' => 'internal-only',
            ],
        ];

        self::assertTrue(app_lab_swagger_supports_source_output($catalog[0]));
        self::assertFalse(app_lab_swagger_supports_source_output($catalog[1]));
        self::assertTrue(app_lab_swagger_supports_source_output($catalog[2]));

        $supported = app_lab_swagger_supported_source_outputs($catalog);
        self::assertSame(
            ['OPENAPI-JSON', 'OPENAPI-LEGACY'],
            array_column($supported, 'source_output_key'),
        );
    }

    public function testOpenApiDocumentBuildsSingleProxyContract(): void
    {
        $context = [
            'project_key' => 'MTOOL',
            'source_output_key' => 'OPENAPI-JSON',
            'definition' => [
                'source_output_key' => 'OPENAPI-JSON',
                'name' => 'Mtool OpenAPI JSON',
                'proxy_base_url' => 'http://127.0.0.1:8081',
            ],
            'plan' => [
                'function_count' => 2,
                'unresolved_function_count' => 0,
                'unresolved_auth_count' => 0,
                'items' => [],
            ],
            'source_entities' => [
                'Project' => [
                    'data_class' => 'ProjectData',
                    'data_properties' => ['ProjectName', 'OwnerUser'],
                ],
            ],
            'proxy_items' => [
                [
                    'source_name' => 'Project',
                    'function_name' => 'InsertProject',
                    'display_name' => 'Project.InsertProject',
                    'auth_policy' => [
                        'strategy_key' => 'project-token',
                        'summary' => 'project token 認証です。',
                    ],
                    'endpoint_filename' => 'proxyserver-Project-InsertProject.php',
                    'response_property_type' => 'long?',
                    'steps' => [
                        [
                            'action' => 'insert',
                            'input_kind' => 'object',
                            'object_param_name' => 'ProjectObj',
                            'object_class' => 'ProjectData',
                            'data_class' => 'ProjectData',
                            'parameter_names' => ['ProjectObj'],
                            'response_key' => 'InsertID',
                            'response_mode' => 'insert-id-single',
                        ],
                    ],
                ],
                [
                    'source_name' => 'Project',
                    'function_name' => 'GetProjectList',
                    'display_name' => 'Project.GetProjectList',
                    'auth_policy' => [
                        'strategy_key' => 'no-security',
                        'summary' => '認証を掛けません。',
                    ],
                    'endpoint_filename' => 'proxyserver-Project-GetProjectList.php',
                    'response_property_type' => 'ProjectDataList',
                    'steps' => [
                        [
                            'action' => 'select-list',
                            'input_kind' => 'scalar',
                            'object_param_name' => '',
                            'object_class' => '',
                            'data_class' => 'ProjectData',
                            'parameter_names' => ['OwnerID', 'Status'],
                            'response_key' => 'Result',
                            'response_mode' => 'direct-result',
                        ],
                    ],
                ],
            ],
        ];

        $snapshotItems = [
            [
                'name' => 'ProjectData',
                'inherit_parent_data_class_name' => '',
                'fields' => [
                    [
                        'name' => 'ProjectName',
                        'datatype' => 'varchar',
                        'ref_data_class_name' => '',
                        'ref_data_class_field_name' => '',
                    ],
                    [
                        'name' => 'OwnerUser',
                        'datatype' => '',
                        'ref_data_class_name' => 'ProjectUserData',
                        'ref_data_class_field_name' => '',
                    ],
                ],
            ],
            [
                'name' => 'ProjectUserData',
                'inherit_parent_data_class_name' => '',
                'fields' => [
                    [
                        'name' => 'UserID',
                        'datatype' => 'int',
                        'ref_data_class_name' => '',
                        'ref_data_class_field_name' => '',
                    ],
                ],
            ],
        ];

        $document = app_project_output_openapi_document($context, $snapshotItems);

        self::assertSame('3.0.3', $document['openapi'] ?? '');
        self::assertSame('http://127.0.0.1:8081', $document['servers'][0]['url'] ?? '');
        self::assertArrayHasKey('/proxyserver-Project-InsertProject.php', $document['paths']);
        self::assertArrayHasKey('/proxyserver-Project-GetProjectList.php', $document['paths']);
        self::assertSame(
            '#/components/schemas/ProjectUserData',
            $document['components']['schemas']['ProjectData']['properties']['OwnerUser']['$ref'] ?? '',
        );

        $insertOperation = $document['paths']['/proxyserver-Project-InsertProject.php']['post'] ?? null;
        self::assertIsArray($insertOperation);
        self::assertSame('project-token', $insertOperation['x-mtool']['auth_strategy'] ?? '');
        self::assertSame(
            '#/components/schemas/ProjectData',
            $insertOperation['requestBody']['content']['application/json']['schema']['properties']['ProjectObj']['$ref'] ?? '',
        );
        self::assertSame(
            'integer',
            $insertOperation['responses']['200']['content']['application/json']['schema']['properties']['InsertID']['type'] ?? '',
        );
        self::assertSame(
            [
                'TOKEN' => 'project-token',
                'ProjectObj' => [
                    'ProjectName' => 'string',
                    'OwnerUser' => [
                        'UserID' => 0,
                    ],
                ],
            ],
            $insertOperation['requestBody']['content']['application/json']['example'] ?? null,
        );

        $listOperation = $document['paths']['/proxyserver-Project-GetProjectList.php']['post'] ?? null;
        self::assertIsArray($listOperation);
        self::assertSame(
            ['OwnerID', 'Status'],
            $listOperation['requestBody']['content']['application/json']['schema']['required'] ?? [],
        );
        self::assertSame(
            'integer',
            $listOperation['requestBody']['content']['application/json']['schema']['properties']['OwnerID']['type'] ?? '',
        );
        self::assertSame(
            0,
            $listOperation['requestBody']['content']['application/json']['schema']['properties']['OwnerID']['example'] ?? null,
        );
        self::assertSame(
            'string',
            $listOperation['requestBody']['content']['application/json']['schema']['properties']['Status']['type'] ?? '',
        );
        self::assertSame(
            [
                'OwnerID' => 0,
                'Status' => 'string',
            ],
            $listOperation['requestBody']['content']['application/json']['example'] ?? null,
        );
        self::assertSame(
            'array',
            $listOperation['responses']['200']['content']['application/json']['schema']['properties']['Result']['type'] ?? '',
        );
        self::assertSame(
            '#/components/schemas/ProjectData',
            $listOperation['responses']['200']['content']['application/json']['schema']['properties']['Result']['items']['$ref'] ?? '',
        );
    }

    public function testOpenApiScalarRequestSchemaPrefersProxyParameterMetadata(): void
    {
        $schema = app_project_output_openapi_request_schema([
            'source_name' => 'Project',
            'function_name' => 'FindProject',
            'auth_policy' => [
                'strategy_key' => 'no-security',
            ],
            'steps' => [
                [
                    'input_kind' => 'scalar',
                    'object_param_name' => '',
                    'object_class' => '',
                    'parameter_names' => ['ExternalCode'],
                    'parameter_schemas' => [
                        'ExternalCode' => [
                            'datatype' => 'int',
                            'source' => 'select-where-parameter-data-type',
                            'target_table_column_name' => 'ExternalCode',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame(
            'integer',
            $schema['properties']['ExternalCode']['type'] ?? '',
        );
        self::assertSame(0, $schema['properties']['ExternalCode']['example'] ?? null);
    }

    public function testLabSwaggerOperationCatalogBuildsAuthHelperMetadata(): void
    {
        $operations = app_lab_swagger_operation_catalog([
            'paths' => [
                '/proxyserver-Project-InsertProject.php' => [
                    'post' => [
                        'operationId' => 'Project.InsertProject',
                        'summary' => 'Project.InsertProject',
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'example' => [
                                        'ProjectObj' => [
                                            'ProjectName' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            '_status' => 'OK',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'x-mtool' => [
                            'source_name' => 'Project',
                            'function_name' => 'InsertProject',
                            'auth_strategy' => 'project-token',
                            'input_kind' => 'object',
                            'response_mode' => 'insert-id-single',
                        ],
                    ],
                ],
                '/proxyserver-Project-GetProjectList.php' => [
                    'post' => [
                        'operationId' => 'Project.GetProjectList',
                        'summary' => 'Project.GetProjectList',
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'example' => [
                                        'OwnerID' => 'string',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            '_status' => 'OK',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'x-mtool' => [
                            'source_name' => 'Project',
                            'function_name' => 'GetProjectList',
                            'auth_strategy' => 'project-token-or-get-function',
                            'input_kind' => 'scalar',
                            'response_mode' => 'direct-result',
                        ],
                    ],
                ],
                '/proxyserver-Project-LoginCookie.php' => [
                    'post' => [
                        'operationId' => 'Project.LoginCookie',
                        'summary' => 'Project.LoginCookie',
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'example' => (object) [],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'content' => [
                                    'application/json' => [
                                        'example' => [
                                            '_status' => 'OK',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'x-mtool' => [
                            'source_name' => 'Project',
                            'function_name' => 'LoginCookie',
                            'auth_strategy' => 'login-cookie-token',
                            'input_kind' => 'scalar',
                            'response_mode' => 'direct-result',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertCount(3, $operations);

        $insertOperation = $operations[1];
        self::assertSame('project-token', $insertOperation['auth_strategy']);
        self::assertSame(['TOKEN'], $insertOperation['auth_required_fields']);
        self::assertSame([], $insertOperation['auth_optional_fields']);
        self::assertStringContainsString('TOKEN', $insertOperation['auth_notice']);
        self::assertStringContainsString('MTOOL_PROXY_PROJECT_TOKEN', $insertOperation['auth_notice']);

        $listOperation = $operations[0];
        self::assertSame('project-token-or-get-function', $listOperation['auth_strategy']);
        self::assertSame([], $listOperation['auth_required_fields']);
        self::assertSame(['TOKEN'], $listOperation['auth_optional_fields']);
        self::assertStringContainsString('get-function', $listOperation['auth_notice']);

        $loginCookieOperation = $operations[2];
        self::assertSame('login-cookie-token', $loginCookieOperation['auth_strategy']);
        self::assertSame(['LOGIN_COOKIE_TOKEN'], $loginCookieOperation['auth_required_fields']);
        self::assertStringContainsString('LOGIN_COOKIE_TOKEN', $loginCookieOperation['auth_notice']);
        self::assertSame("{}\n", $loginCookieOperation['request_example_pretty']);
    }

    public function testLabSwaggerOperationCatalogPreservesEmptyObjectExamplesFromDecodedSpec(): void
    {
        $operations = app_lab_swagger_operation_catalog([
            'paths' => [
                '/proxyserver-Project-GetProjectList.php' => [
                    'post' => [
                        'operationId' => 'Project.GetProjectList',
                        'summary' => 'Project.GetProjectList',
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [],
                                        'additionalProperties' => true,
                                    ],
                                    'example' => [],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'Result' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'example' => [
                                            'Result' => [],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'x-mtool' => [
                            'source_name' => 'Project',
                            'function_name' => 'GetProjectList',
                            'auth_strategy' => 'no-security',
                            'input_kind' => 'scalar',
                            'response_mode' => 'direct-result',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertCount(1, $operations);
        self::assertSame("{}\n", $operations[0]['request_example_pretty']);
        self::assertStringContainsString("\"Result\": []", $operations[0]['success_example_pretty']);
        self::assertStringContainsString('NoSecurity', $operations[0]['auth_notice']);
    }

    public function testLabSwaggerAuthHelperSummaryCountsLegacyAuthStrategies(): void
    {
        $summary = app_lab_swagger_auth_helper_summary([
            [
                'auth_strategy' => 'no-security',
            ],
            [
                'auth_strategy' => 'project-token',
            ],
            [
                'auth_strategy' => 'project-token-or-get-function',
            ],
            [
                'auth_strategy' => 'login-cookie-token',
            ],
        ]);

        self::assertSame(3, $summary['auth_operation_count']);
        self::assertSame(1, $summary['project_token_required_count']);
        self::assertSame(1, $summary['project_token_optional_count']);
        self::assertSame(1, $summary['login_cookie_token_required_count']);
        self::assertTrue($summary['requires_auth_helper']);
    }

    public function testGeneratedSingleProxyProjectTokenAuthFailsClosedWhenTokenEnvIsMissing(): void
    {
        $baseClass = 'MtoolGeneratedSingleProxyEndpointBaseContract';
        $subjectClass = 'MtoolGeneratedSingleProxyEndpointContractSubject';

        $this->ensureGeneratedSingleProxyRuntimeClassExists($baseClass);
        $this->ensureGeneratedSingleProxyContractSubjectExists($baseClass, $subjectClass, [
            'auth_strategy' => 'project-token',
        ]);

        $previousToken = getenv('MTOOL_PROXY_PROJECT_TOKEN');
        putenv('MTOOL_PROXY_PROJECT_TOKEN');

        try {
            $subject = new $subjectClass();

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('MTOOL_PROXY_PROJECT_TOKEN が未設定です。');
            $this->invokeGeneratedProxyAuthorizeRequest($subject, $baseClass, [
                'TOKEN' => 'supplied-token',
            ]);
        } finally {
            $this->restoreEnvValue('MTOOL_PROXY_PROJECT_TOKEN', $previousToken);
        }
    }

    public function testGeneratedSingleProxyProjectTokenOrGetFunctionFallsBackWhenTokenEnvIsMissing(): void
    {
        $baseClass = 'MtoolGeneratedSingleProxyEndpointBaseContract';
        $subjectClass = 'MtoolGeneratedSingleProxyEndpointOrGetFunctionContractSubject';

        $this->ensureGeneratedSingleProxyRuntimeClassExists($baseClass);
        $this->ensureGeneratedSingleProxyContractSubjectExists($baseClass, $subjectClass, [
            'auth_strategy' => 'project-token-or-get-function',
            'single_get_function_name' => 'ResolveProjectToken',
            'authorize_by_get_function' => true,
        ]);

        $previousToken = getenv('MTOOL_PROXY_PROJECT_TOKEN');
        putenv('MTOOL_PROXY_PROJECT_TOKEN');

        try {
            $subject = new $subjectClass();
            $this->invokeGeneratedProxyAuthorizeRequest($subject, $baseClass, [
                'TOKEN' => 'supplied-token',
            ]);

            self::assertTrue(true);
        } finally {
            $this->restoreEnvValue('MTOOL_PROXY_PROJECT_TOKEN', $previousToken);
        }
    }

    public function testLabSwaggerRuntimeDatabaseSourceSelectionSupportsExplicitDbSourceKey(): void
    {
        $app = [
            'database_sources' => [
                'db' => [
                    'key' => 'db',
                    'label' => 'site default db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => false,
                    'proxy_runtime_priority' => 300,
                    'is_canonical_store' => false,
                    'host' => 'db-host',
                    'port' => '3306',
                    'name' => 'db_name',
                    'user' => 'db_user',
                    'password' => 'db_password',
                    'dsn' => 'mysql:host=db-host;port=3306;dbname=db_name;charset=utf8mb4',
                ],
                'config_db' => [
                    'key' => 'config_db',
                    'label' => 'config db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'config_db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 200,
                    'is_canonical_store' => true,
                    'host' => 'config-host',
                    'port' => '3306',
                    'name' => 'config_name',
                    'user' => 'config_user',
                    'password' => 'config_password',
                    'dsn' => 'mysql:host=config-host;port=3306;dbname=config_name;charset=utf8mb4',
                ],
                'lab_db' => [
                    'key' => 'lab_db',
                    'label' => 'lab db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'lab_db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 100,
                    'is_canonical_store' => false,
                    'host' => 'lab-host',
                    'port' => '3306',
                    'name' => 'lab_name',
                    'user' => 'lab_user',
                    'password' => 'lab_password',
                    'dsn' => 'mysql:host=lab-host;port=3306;dbname=lab_name;charset=utf8mb4',
                ],
                'external_lab' => [
                    'key' => 'external_lab',
                    'label' => 'external lab db',
                    'description' => 'external named source',
                    'source_of_truth' => 'manual',
                    'db_config_key' => 'external_lab',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 150,
                    'is_canonical_store' => false,
                    'host' => 'external-host',
                    'port' => '3306',
                    'name' => 'external_name',
                    'user' => 'external_user',
                    'password' => 'external_password',
                    'dsn' => 'mysql:host=external-host;port=3306;dbname=external_name;charset=utf8mb4',
                ],
            ],
        ];

        $options = app_lab_swagger_runtime_database_source_options($app);

        self::assertSame(
            ['lab_db', 'external_lab', 'config_db'],
            array_column($options, 'key'),
        );

        $explicitCandidate = app_lab_swagger_resolve_runtime_database_source_selection(
            $app,
            'external_lab',
            $options,
        );
        self::assertSame('external_lab', $explicitCandidate['selected_key']);
        self::assertSame('', $explicitCandidate['notice']);

        $explicitNonCandidate = app_lab_swagger_resolve_runtime_database_source_selection(
            $app,
            'db',
            $options,
        );
        self::assertSame('', $explicitNonCandidate['selected_key']);
        self::assertNull($explicitNonCandidate['selected_source']);
        self::assertStringContainsString('proxy runtime candidate', $explicitNonCandidate['notice']);

        $missing = app_lab_swagger_resolve_runtime_database_source_selection(
            $app,
            'missing_source',
            $options,
        );
        self::assertSame('', $missing['selected_key']);
        self::assertNull($missing['selected_source']);
        self::assertStringContainsString('見つかりません', $missing['notice']);

        self::assertSame(
            '/runs/swagger/MTOOL?source_output_key=OPENAPI-JSON&db_source_key=external_lab',
            app_lab_swagger_path('MTOOL', [
                'source_output_key' => 'OPENAPI-JSON',
                'db_source_key' => 'external_lab',
            ]),
        );
    }

    public function testPublishedSingleProxyRejectsExplicitDbSourceWithoutProxyRuntimeRead(): void
    {
        $app = [
            'database_sources' => [
                'db' => [
                    'key' => 'db',
                    'label' => 'site default db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => false,
                    'proxy_runtime_priority' => 300,
                    'is_canonical_store' => false,
                    'host' => 'db-host',
                    'port' => '3306',
                    'name' => 'db_name',
                    'user' => 'db_user',
                    'password' => 'db_password',
                    'dsn' => 'mysql:host=db-host;port=3306;dbname=db_name;charset=utf8mb4',
                ],
                'config_db' => [
                    'key' => 'config_db',
                    'label' => 'config db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'config_db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 200,
                    'is_canonical_store' => true,
                    'host' => 'config-host',
                    'port' => '3306',
                    'name' => 'config_name',
                    'user' => 'config_user',
                    'password' => 'config_password',
                    'dsn' => 'mysql:host=config-host;port=3306;dbname=config_name;charset=utf8mb4',
                ],
                'lab_db' => [
                    'key' => 'lab_db',
                    'label' => 'lab db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'lab_db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 100,
                    'is_canonical_store' => false,
                    'host' => 'lab-host',
                    'port' => '3306',
                    'name' => 'lab_name',
                    'user' => 'lab_user',
                    'password' => 'lab_password',
                    'dsn' => 'mysql:host=lab-host;port=3306;dbname=lab_name;charset=utf8mb4',
                ],
            ],
        ];

        $previousGet = $_GET;

        try {
            $_GET = [
                'db_source_key' => 'db',
            ];
            $deniedValidation = app_lab_published_single_proxy_validate_requested_db_source_key($app);
            self::assertFalse($deniedValidation['ok']);
            self::assertSame(422, $deniedValidation['status_code']);
            self::assertSame('db', $deniedValidation['requested_key']);
            self::assertStringContainsString('proxy runtime read が無効', $deniedValidation['error']);
            self::assertSame('', app_lab_published_single_proxy_endpoint_requested_db_config_key($app));

            $_GET = [
                'db_config_key' => 'lab_db',
            ];
            $legacyAcceptedValidation = app_lab_published_single_proxy_validate_requested_db_source_key($app);
            self::assertTrue($legacyAcceptedValidation['ok']);
            self::assertSame('lab_db', $legacyAcceptedValidation['requested_key']);
            self::assertSame('lab_db', app_lab_published_single_proxy_endpoint_requested_db_config_key($app));

            $_GET = [
                'db_source_key' => 'missing_source',
            ];
            $missingValidation = app_lab_published_single_proxy_validate_requested_db_source_key($app);
            self::assertFalse($missingValidation['ok']);
            self::assertSame('missing_source', $missingValidation['requested_key']);
            self::assertStringContainsString('見つかりません', $missingValidation['error']);
        } finally {
            $_GET = $previousGet;
        }
    }

    public function testLabCanonicalMetadataReadersUseConfigDatabase(): void
    {
        $sourceOutputRepository = $this->readRepoFile('mtool/app/source_output_repository_pdo.php');
        $dbAccessRepository = $this->readRepoFile('mtool/app/db_access_repository_pdo.php');
        $publishedSingleProxyPage = $this->readRepoFile('mtool/app/lab_published_single_proxy_page.php');

        self::assertMatchesRegularExpression(
            '/function app_pdo_fetch_project_source_output_catalog\(.*?\)\s*:\s*array\s*\{\s*try\s*\{\s*\$pdo = app_create_(?:config|metadata)_pdo\(\$app\);/s',
            $sourceOutputRepository,
        );
        self::assertMatchesRegularExpression(
            '/function app_pdo_fetch_project_source_output_item\(.*?\)\s*:\s*array\s*\{\s*try\s*\{\s*\$pdo = app_create_(?:config|metadata)_pdo\(\$app\);/s',
            $sourceOutputRepository,
        );
        self::assertMatchesRegularExpression(
            '/function app_pdo_fetch_source_output_db_access_function_target_catalog\(.*?\)\s*:\s*array\s*\{\s*try\s*\{\s*\$pdo = app_create_(?:config|metadata)_pdo\(\$app\);/s',
            $dbAccessRepository,
        );
        self::assertMatchesRegularExpression(
            '/function app_lab_published_single_proxy_runtime_db_config_key\(.*?\$canonicalStoreKey = app_database_source_canonical_store_key\(\$app\);/s',
            $publishedSingleProxyPage,
        );
        self::assertMatchesRegularExpression(
            '/function app_lab_published_single_proxy_apply_runtime_globals\(.*?\$effectiveDatabaseSourceKey = app_database_source_exists\(\$app, \$dbConfigKey\)/s',
            $publishedSingleProxyPage,
        );
    }

    public function testPublishedSingleProxyRuntimeCandidatesUseNamedDatabaseSourceCatalog(): void
    {
        $app = [
            'database_sources' => [
                'db' => [
                    'key' => 'db',
                    'label' => 'site default db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => false,
                    'proxy_runtime_priority' => 300,
                    'is_canonical_store' => false,
                    'host' => 'db-host',
                    'port' => '3306',
                    'name' => 'db_name',
                    'user' => 'db_user',
                    'password' => 'db_password',
                    'dsn' => 'mysql:host=db-host;port=3306;dbname=db_name;charset=utf8mb4',
                ],
                'config_db' => [
                    'key' => 'config_db',
                    'label' => 'config db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'config_db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 200,
                    'is_canonical_store' => true,
                    'host' => 'config-host',
                    'port' => '3306',
                    'name' => 'config_name',
                    'user' => 'config_user',
                    'password' => 'config_password',
                    'dsn' => 'mysql:host=config-host;port=3306;dbname=config_name;charset=utf8mb4',
                ],
                'lab_db' => [
                    'key' => 'lab_db',
                    'label' => 'lab db',
                    'description' => '',
                    'source_of_truth' => 'test',
                    'db_config_key' => 'lab_db',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 100,
                    'is_canonical_store' => false,
                    'host' => 'lab-host',
                    'port' => '3306',
                    'name' => 'lab_name',
                    'user' => 'lab_user',
                    'password' => 'lab_password',
                    'dsn' => 'mysql:host=lab-host;port=3306;dbname=lab_name;charset=utf8mb4',
                ],
            ],
        ];

        self::assertSame('config_db', app_database_source_canonical_store_key($app));
        self::assertSame(
            ['lab_db', 'config_db'],
            app_lab_published_single_proxy_runtime_database_source_key_candidates($app),
        );
        self::assertSame(
            [
                'host' => 'lab-host',
                'port' => '3306',
                'name' => 'lab_name',
                'user' => 'lab_user',
                'password' => 'lab_password',
                'dsn' => 'mysql:host=lab-host;port=3306;dbname=lab_name;charset=utf8mb4',
            ],
            app_database_source_config($app, 'lab_db'),
        );
    }

    public function testSingleProxyServerBundleIncludesRuntimeLoaderDependencies(): void
    {
        $app = app_bootstrap();
        $entityResult = app_project_output_proxy_load_source_entity($app, 'Project');

        self::assertTrue($entityResult['ok']);
        self::assertIsArray($entityResult['entity']);

        $result = app_project_output_single_proxy_build_server_emitted_files([
            'project_key' => 'MTOOL',
            'source_output_key' => 'PAYPAL-PROXY-SERVER',
            'runtime_source_relative_path' => 'mtool/proxy-source-outputs/MTOOL/PAYPAL-PROXY-SERVER',
            'plan' => [
                'items' => [],
            ],
            'source_entities' => [$entityResult['entity']],
            'proxy_items' => [],
        ]);

        self::assertTrue($result['ok']);
        $files = [];
        $fileContentsByPath = [];
        foreach ($result['files'] as $file) {
            self::assertIsArray($file);
            $relativePath = $file['relative_path'] ?? null;
            self::assertIsString($relativePath);
            $files[$relativePath] = true;
            $contents = $file['contents'] ?? null;
            self::assertIsString($contents);
            $fileContentsByPath[$relativePath] = $contents;
        }

        self::assertArrayHasKey('_support/runtime_dbclasses/_runtime_loader.php', $files);
        self::assertArrayHasKey('_support/runtime_dbclasses/base/data-ProjectBase.php', $files);
        self::assertArrayHasKey('_support/runtime_dbclasses/base/dbaccess-ProjectBase.php', $files);
        self::assertArrayHasKey(
            '_support/runtime_dbclasses/_support/legacy-dbaccess/dbaccess-Project.php',
            $files,
        );
        self::assertStringNotContainsString(
            "/../_support/legacy-dbaccess/",
            $fileContentsByPath['_support/runtime_dbclasses/base/dbaccess-ProjectBase.php'],
        );
        self::assertStringContainsString(
            'class ProjectDBAccessBase',
            $fileContentsByPath['_support/runtime_dbclasses/base/dbaccess-ProjectBase.php'],
        );
        self::assertStringNotContainsString(
            'extends ProjectDBAccessLegacy',
            $fileContentsByPath['_support/runtime_dbclasses/base/dbaccess-ProjectBase.php'],
        );
        self::assertStringNotContainsString(
            "require_once __DIR__ . '/_runtime_loader.php';",
            $fileContentsByPath['_support/runtime_dbclasses/_support/legacy-dbaccess/dbaccess-Project.php'],
        );
        self::assertStringContainsString(
            'class ProjectDBAccessLegacy',
            $fileContentsByPath['_support/runtime_dbclasses/_support/legacy-dbaccess/dbaccess-Project.php'],
        );
        self::assertStringNotContainsString(
            "require_once __DIR__ . '/base/dbaccess-ProjectBase.php';",
            $fileContentsByPath['_support/runtime_dbclasses/_support/legacy-dbaccess/dbaccess-Project.php'],
        );
        self::assertStringNotContainsString(
            'mtool_runtime_bundle_load_custom_wrapper(',
            $fileContentsByPath['_support/runtime_dbclasses/_support/legacy-dbaccess/dbaccess-Project.php'],
        );
        self::assertStringNotContainsString(
            'extends ProjectDBAccessBase',
            $fileContentsByPath['_support/runtime_dbclasses/_support/legacy-dbaccess/dbaccess-Project.php'],
        );
    }

    private function readRepoFile(string $relativePath): string
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . $relativePath;
        self::assertFileExists($absolutePath, 'missing file: ' . $relativePath);

        $content = file_get_contents($absolutePath);
        self::assertIsString($content, 'failed to read: ' . $relativePath);

        return $content;
    }

    private function ensureGeneratedSingleProxyRuntimeClassExists(string $baseClass): void
    {
        if (class_exists($baseClass, false)) {
            return;
        }

        $runtimeSource = preg_replace(
            '/^<\?php\s*/',
            '',
            app_project_output_proxy_server_runtime_text($baseClass),
            1,
        );

        self::assertIsString($runtimeSource);

        eval($runtimeSource);
    }

    /**
     * @param array{
     *     auth_strategy:string,
     *     single_get_function_name?:string,
     *     authorize_by_get_function?:bool
     * } $options
     */
    private function ensureGeneratedSingleProxyContractSubjectExists(
        string $baseClass,
        string $subjectClass,
        array $options,
    ): void {
        if (class_exists($subjectClass, false)) {
            return;
        }

        $authStrategy = var_export($options['auth_strategy'], true);
        $singleGetFunctionName = var_export((string) ($options['single_get_function_name'] ?? ''), true);
        $authorizeByGetFunction = !empty($options['authorize_by_get_function']) ? 'true' : 'false';

        eval(<<<PHP
class {$subjectClass} extends {$baseClass}
{
    protected function proxyDisplayName(): string
    {
        return 'Contract.Subject';
    }

    protected function stepDefinitions(): array
    {
        return [];
    }

    protected function authStrategy(): string
    {
        return {$authStrategy};
    }

    protected function singleGetFunctionName(): string
    {
        return {$singleGetFunctionName};
    }

    protected function authorizeByGetFunction(array \$payload, string \$singleGetFunctionName): bool
    {
        return {$authorizeByGetFunction};
    }
}
PHP);
    }

    private function invokeGeneratedProxyAuthorizeRequest(object $subject, string $baseClass, array $payload): void
    {
        $authorize = \Closure::bind(
            function (array $payload): void {
                $this->authorizeRequest($payload);
            },
            $subject,
            $baseClass,
        );

        self::assertInstanceOf(\Closure::class, $authorize);
        $authorize($payload);
    }

    private function restoreEnvValue(string $name, string|false $value): void
    {
        if ($value === false) {
            putenv($name);
            return;
        }

        putenv($name . '=' . $value);
    }
}
