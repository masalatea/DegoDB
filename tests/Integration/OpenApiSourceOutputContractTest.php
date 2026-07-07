<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/domain_validation.php';
require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_published_single_proxy_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/lab_swagger_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_public_runtime_page.php';
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

        $artifactDetailRoute = app_route_match([
            'path' => '/projects/MTOOL/source-outputs/artifacts/20260521-023351-d52e8c8b',
        ]);

        self::assertSame('project_source_output_artifact_detail', $artifactDetailRoute['name']);
        self::assertSame('MTOOL', $artifactDetailRoute['params']['project_key'] ?? '');
        self::assertSame('20260521-023351-d52e8c8b', $artifactDetailRoute['params']['artifact_key'] ?? '');

        $syncOutboxDetailRoute = app_route_match([
            'path' => '/projects/MTOOL/sync-outbox/abcdef123456',
        ]);

        self::assertSame('project_sync_outbox_detail', $syncOutboxDetailRoute['name']);
        self::assertSame('MTOOL', $syncOutboxDetailRoute['params']['project_key'] ?? '');
        self::assertSame('abcdef123456', $syncOutboxDetailRoute['params']['dedupe_key'] ?? '');

        $syncOutboxStatusJsonRoute = app_route_match([
            'path' => '/projects/MTOOL/sync-outbox/abcdef123456.json',
        ]);

        self::assertSame('project_sync_outbox_status_json', $syncOutboxStatusJsonRoute['name']);
        self::assertSame('MTOOL', $syncOutboxStatusJsonRoute['params']['project_key'] ?? '');
        self::assertSame('abcdef123456', $syncOutboxStatusJsonRoute['params']['dedupe_key'] ?? '');

        $sharedContractsRoute = app_route_match([
            'path' => '/projects/MTOOL/shared-contracts',
        ]);

        self::assertSame('project_shared_contracts', $sharedContractsRoute['name']);
        self::assertSame('MTOOL', $sharedContractsRoute['params']['project_key'] ?? '');

        $noCodeRuntimePreviewRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/20260702-010203-abcdef12/runtime-preview.html',
        ]);

        self::assertSame('no_code_public_runtime_preview', $noCodeRuntimePreviewRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimePreviewRoute['params']['project_key'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $noCodeRuntimePreviewRoute['params']['artifact_key'] ?? '');

        $noCodeRuntimeCurrentPreviewRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/current/runtime-preview.html',
        ]);

        self::assertSame('no_code_public_runtime_current_preview', $noCodeRuntimeCurrentPreviewRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimeCurrentPreviewRoute['params']['project_key'] ?? '');
        self::assertArrayNotHasKey('artifact_key', $noCodeRuntimeCurrentPreviewRoute['params']);

        $noCodeRuntimeCurrentExecutionRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/current/execute.json',
        ]);

        self::assertSame('no_code_public_runtime_current_execution', $noCodeRuntimeCurrentExecutionRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimeCurrentExecutionRoute['params']['project_key'] ?? '');
        self::assertArrayNotHasKey('artifact_key', $noCodeRuntimeCurrentExecutionRoute['params']);
        self::assertSame(
            '/runs/no-code/SAMPLE28/current/execute.json',
            app_no_code_public_runtime_current_execution_path('SAMPLE28'),
        );

        $noCodeRuntimeCurrentDataRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/current/runtime-data.json',
        ]);

        self::assertSame('no_code_public_runtime_current_data', $noCodeRuntimeCurrentDataRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimeCurrentDataRoute['params']['project_key'] ?? '');
        self::assertArrayNotHasKey('artifact_key', $noCodeRuntimeCurrentDataRoute['params']);
        self::assertSame(
            '/runs/no-code/SAMPLE28/current/runtime-data.json',
            app_no_code_public_runtime_current_data_path('SAMPLE28'),
        );

        $noCodeRuntimeAliasExecutionRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/alias/stable-demo/execute.json',
        ]);

        self::assertSame('no_code_public_runtime_alias_execution', $noCodeRuntimeAliasExecutionRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimeAliasExecutionRoute['params']['project_key'] ?? '');
        self::assertSame('stable-demo', $noCodeRuntimeAliasExecutionRoute['params']['alias_key'] ?? '');
        self::assertSame(
            '/runs/no-code/SAMPLE28/alias/stable-demo/execute.json',
            app_no_code_public_runtime_alias_execution_path('SAMPLE28', 'Stable-Demo'),
        );

        $noCodeRuntimeAliasDataRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/alias/stable-demo/runtime-data.json',
        ]);

        self::assertSame('no_code_public_runtime_alias_data', $noCodeRuntimeAliasDataRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimeAliasDataRoute['params']['project_key'] ?? '');
        self::assertSame('stable-demo', $noCodeRuntimeAliasDataRoute['params']['alias_key'] ?? '');
        self::assertSame(
            '/runs/no-code/SAMPLE28/alias/stable-demo/runtime-data.json',
            app_no_code_public_runtime_alias_data_path('SAMPLE28', 'Stable-Demo'),
        );

        $noCodeRuntimeExecutionRoute = app_route_match([
            'path' => '/runs/no-code/SAMPLE28/20260702-010203-abcdef12/execute.json',
        ]);

        self::assertSame('no_code_public_runtime_execution', $noCodeRuntimeExecutionRoute['name']);
        self::assertSame('SAMPLE28', $noCodeRuntimeExecutionRoute['params']['project_key'] ?? '');
        self::assertSame('20260702-010203-abcdef12', $noCodeRuntimeExecutionRoute['params']['artifact_key'] ?? '');
        self::assertSame(
            '/runs/no-code/SAMPLE28/20260702-010203-abcdef12/execute.json',
            app_no_code_public_runtime_execution_path('SAMPLE28', '20260702-010203-abcdef12'),
        );

        self::assertSame(
            'public, max-age=31536000, immutable',
            app_no_code_public_runtime_artifact_cache_control(),
        );
        self::assertSame('no-store', app_no_code_public_runtime_current_cache_control());
    }

    public function testRuntimeDataDatetimeValuesRejectTimezoneOffsets(): void
    {
        self::assertSame(
            '2026-07-15T09:30:00',
            app_no_code_public_runtime_data_datetime_value(
                '2026-07-15 09:30:00',
                'published_at',
                'datetime',
                'filter',
            ),
        );

        foreach (['2026-07-15T09:30:00+09:00', '2026-07-15T00:30:00Z'] as $offsetValue) {
            try {
                app_no_code_public_runtime_data_datetime_value(
                    $offsetValue,
                    'published_at',
                    'datetime',
                    'filter',
                );
                self::fail('timezone-offset datetime value should fail closed: ' . $offsetValue);
            } catch (RuntimeException $exception) {
                self::assertStringContainsString(
                    'runtime data date/time filter value was not parseable: published_at',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function testRuntimeDataDateTimeOrderedValuesRejectNullAndEmptyValues(): void
    {
        $cases = [
            ['value' => null, 'type' => 'date', 'context' => 'filter'],
            ['value' => '', 'type' => 'date', 'context' => 'filter'],
            ['value' => null, 'type' => 'datetime', 'context' => 'sort'],
            ['value' => '', 'type' => 'datetime', 'context' => 'sort'],
            ['value' => null, 'type' => 'time', 'context' => 'sort'],
            ['value' => '', 'type' => 'time', 'context' => 'sort'],
        ];

        foreach ($cases as $case) {
            try {
                app_no_code_public_runtime_data_datetime_value(
                    $case['value'],
                    'published_at',
                    $case['type'],
                    $case['context'],
                );
                self::fail('null/empty date-time value should fail closed: ' . json_encode($case));
            } catch (RuntimeException $exception) {
                self::assertStringContainsString(
                    'runtime data date/time ' . $case['context'] . ' value was not parseable: published_at',
                    $exception->getMessage(),
                );
            }
        }
    }

    public function testOpenApiPublicRawRouteRemainsDeferredAndInternalRoutesRequireAuth(): void
    {
        $labSwaggerPage = $this->readRepoFile('mtool/app/lab_swagger_page.php');
        $downloadPage = $this->readRepoFile('mtool/app/project_source_output_download_page.php');
        $artifactDetailPage = $this->readRepoFile('mtool/app/project_source_output_artifact_detail_page.php');
        $sourceOutputsPage = $this->readRepoFile('mtool/app/project_source_outputs_page.php');
        $syncOutboxDetailPage = $this->readRepoFile('mtool/app/project_sync_outbox_detail_page.php');
        $publicRuntimePage = $this->readRepoFile('mtool/app/no_code_public_runtime_page.php');
        $newPage = $this->readRepoFile('mtool/app/project_source_output_new_page.php');
        $detailPage = $this->readRepoFile('mtool/app/project_source_output_detail_page.php');

        self::assertTrue(app_route_requires_auth('lab_swagger'));
        self::assertTrue(app_route_requires_auth('lab_published_single_proxy'));
        self::assertTrue(app_route_requires_auth('project_source_output_artifact_detail'));
        self::assertTrue(app_route_requires_auth('project_source_output_download'));
        self::assertTrue(app_route_requires_auth('project_sync_outbox_detail'));
        self::assertTrue(app_route_requires_auth('project_sync_outbox_status_json'));
        self::assertTrue(app_route_requires_auth('project_shared_contracts'));
        self::assertFalse(app_route_requires_auth('no_code_public_runtime_preview'));
        self::assertFalse(app_route_requires_auth('no_code_public_runtime_current_preview'));
        self::assertTrue(app_route_requires_auth('no_code_public_runtime_execution'));
        self::assertTrue(app_route_requires_auth('no_code_public_runtime_current_execution'));
        self::assertTrue(app_route_requires_auth('no_code_public_runtime_alias_execution'));
        self::assertTrue(app_route_requires_auth('no_code_public_runtime_current_data'));
        self::assertTrue(app_route_requires_auth('no_code_public_runtime_alias_data'));
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
        self::assertMatchesRegularExpression(
            '/app_auth_has_any_role\(\[\'admin\', \'config\'\], \$principal\)/',
            $artifactDetailPage,
        );
        self::assertStringContainsString("'source_output.download'", $artifactDetailPage);
        self::assertStringContainsString('Operator Workflow Checklist', $sourceOutputsPage);
        self::assertStringContainsString('workflow_steps', $sourceOutputsPage);
        self::assertStringContainsString('$workflowStep', $sourceOutputsPage);
        self::assertStringContainsString('Delivery Overview', $sourceOutputsPage);
        self::assertStringContainsString('delivery_overview', $sourceOutputsPage);
        self::assertStringContainsString('Web no-code preview and App-local package readiness are separate delivery tracks.', $sourceOutputsPage);
        self::assertStringContainsString('Continue the Web preview tryout through <code>NO-CODE-RUNTIME</code>', $sourceOutputsPage);
        self::assertStringContainsString('public runtime:', $sourceOutputsPage);
        self::assertStringContainsString('app-local package:', $sourceOutputsPage);
        self::assertStringContainsString('Inspect App-local package definition', $sourceOutputsPage);
        self::assertMatchesRegularExpression(
            '/app_auth_has_any_role\(\[\'admin\', \'config\'\], \$principal\)/',
            $syncOutboxDetailPage,
        );
        self::assertStringContainsString("'source_output.download'", $syncOutboxDetailPage);
        self::assertStringContainsString('app_verify_csrf_token', $syncOutboxDetailPage);
        self::assertStringContainsString('retry-sync-outbox', $syncOutboxDetailPage);
        self::assertStringContainsString(
            'app_pdo_requeue_failed_managed_operation_sync_outbox_item',
            $syncOutboxDetailPage,
        );
        self::assertStringContainsString('Retry Queued', $syncOutboxDetailPage);
        self::assertStringContainsString('It was not processed inline by this page.', $syncOutboxDetailPage);
        self::assertStringContainsString('sync_outbox.retry_requeued', $syncOutboxDetailPage);
        self::assertStringContainsString('audit trail:', $syncOutboxDetailPage);
        self::assertStringContainsString('Recent Retry Audit', $syncOutboxDetailPage);
        self::assertStringContainsString('No retry audit event has been recorded for this sync outbox item yet.', $syncOutboxDetailPage);
        self::assertStringContainsString("'target_key' => \$dedupeKey", $syncOutboxDetailPage);
        self::assertStringContainsString('existing processor can claim this item', $syncOutboxDetailPage);
        self::assertStringContainsString('Processing Handoff', $syncOutboxDetailPage);
        self::assertStringContainsString('not performed by this page', $syncOutboxDetailPage);
        self::assertStringContainsString('This sync outbox item is queued for the existing processor.', $syncOutboxDetailPage);
        self::assertStringContainsString('public raw route や public alias key route はまだ持ちません', $newPage);
        self::assertStringContainsString('public raw route や public alias key route は持ちません', $detailPage);
        self::assertStringContainsString('Publish Candidates', $detailPage);
        self::assertStringContainsString('create-publish-candidate', $detailPage);
        self::assertStringContainsString('sample28-demo-tryout-approval', $detailPage);
        self::assertStringContainsString('transition-publish-candidate', $detailPage);
        self::assertStringContainsString('select-current-public-revision', $detailPage);
        self::assertStringContainsString('set-public-runtime-alias', $detailPage);
        self::assertStringContainsString('delete-public-runtime-alias', $detailPage);
        self::assertStringContainsString('app_pdo_create_no_code_publish_candidate_from_readiness_snapshot', $detailPage);
        self::assertStringContainsString('app_pdo_transition_no_code_publish_candidate', $detailPage);
        self::assertStringContainsString('app_pdo_find_current_approved_no_code_publish_candidate', $detailPage);
        self::assertStringContainsString('app_pdo_select_current_no_code_publish_candidate', $detailPage);
        self::assertStringContainsString('app_pdo_set_no_code_public_runtime_alias', $detailPage);
        self::assertStringContainsString('app_pdo_delete_no_code_public_runtime_alias', $detailPage);
        self::assertStringContainsString('app_pdo_list_no_code_public_runtime_aliases_for_source_output', $detailPage);
        self::assertStringContainsString('app_pdo_list_no_code_public_runtime_alias_events_for_source_output', $detailPage);
        self::assertStringContainsString('app_pdo_list_no_code_publish_candidate_transition_events', $detailPage);
        self::assertStringContainsString('No-Code Runtime Workflow', $detailPage);
        self::assertStringContainsString('database metadata から生成した no-code runtime', $detailPage);
        self::assertStringContainsString('DB 基盤から切り離された別物ではなく', $detailPage);
        self::assertStringContainsString('Runtime data boundary: artifact-key preview URLs stay static', $detailPage);
        self::assertStringContainsString('Current and alias preview URLs can fetch authenticated read-only live runtime data', $detailPage);
        self::assertStringContainsString('submit / outbox processing remains a separate mutation path', $detailPage);
        self::assertStringContainsString('Tryout Next Steps', $detailPage);
        self::assertStringContainsString('For the fastest local demo, run <code>Run Sample28 Tryout Approval</code>', $detailPage);
        self::assertStringContainsString('App-local package readiness is a separate scenario from this Web preview.', $detailPage);
        self::assertStringContainsString('candidate を作成して review request の後に approve', $detailPage);
        self::assertStringContainsString('Run Sample28 Tryout Approval', $detailPage);
        self::assertStringContainsString('Demo shortcut: creates a candidate, requests review, approves it, selects current public revision', $detailPage);
        self::assertStringContainsString('Sample28 Tryout Ready', $detailPage);
        self::assertStringContainsString('Approved package exposure', $detailPage);
        self::assertStringContainsString('Current public revision', $detailPage);
        self::assertStringContainsString('Approved non-current revision', $detailPage);
        self::assertStringContainsString('Set Current Public Revision', $detailPage);
        self::assertStringContainsString('Current Public Revision Selected', $detailPage);
        self::assertStringContainsString('Set Public Alias', $detailPage);
        self::assertStringContainsString('Public Runtime Alias Selected', $detailPage);
        self::assertStringContainsString('Public Runtime Aliases', $detailPage);
        self::assertStringContainsString('Alias lifecycle events', $detailPage);
        self::assertStringContainsString('No public runtime alias lifecycle event has been recorded yet.', $detailPage);
        self::assertStringContainsString('app_project_source_output_app_local_package_readiness', $detailPage);
        self::assertStringContainsString('App-local Package Readiness', $detailPage);
        self::assertStringContainsString('This card is for the App-local package lane.', $detailPage);
        self::assertStringContainsString('Package readiness blockers:', $detailPage);
        self::assertStringContainsString('app-local-package-manifest.json is missing from the output root.', $detailPage);
        self::assertStringContainsString('Delete Public Alias', $detailPage);
        self::assertStringContainsString('Public Runtime Alias Deleted', $detailPage);
        self::assertStringContainsString('Rollback target: current', $detailPage);
        self::assertStringContainsString('Rollback Current To This Revision', $detailPage);
        self::assertStringContainsString('Alias routes do not automatically follow current public revision rollback', $detailPage);
        self::assertStringContainsString('opened by the current public runtime preview URL', $detailPage);
        self::assertStringContainsString('explicitly resolves to this approved candidate when selected', $detailPage);
        self::assertStringContainsString('Transition events', $detailPage);
        self::assertStringContainsString('No transition event has been recorded for this candidate yet.', $detailPage);
        self::assertStringContainsString('download package', $detailPage);
        self::assertStringContainsString('public runtime preview', $detailPage);
        self::assertStringContainsString('Package exposure is guarded until this candidate is approved.', $detailPage);
        self::assertStringContainsString('artifact-key, current, and custom alias public runtime preview routes', $detailPage);
        self::assertStringContainsString('Runtime data behavior: artifact-key preview is static', $detailPage);
        self::assertStringContainsString('current and alias previews can refresh authenticated read-only live runtime data', $detailPage);
        self::assertStringContainsString('current public runtime preview', $detailPage);
        self::assertStringContainsString('custom alias public runtime preview routes', $detailPage);
        self::assertStringContainsString('/alias/', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_alias_preview_path', $publicRuntimePage);
        self::assertStringContainsString('app_render_no_code_public_runtime_alias_preview_page', $publicRuntimePage);
        self::assertStringContainsString('app_pdo_find_approved_no_code_publish_candidate_for_alias', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_execution_path', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_current_execution_path', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_alias_execution_path', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_current_data_path', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_alias_data_path', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_preview_html_with_execution_binding', $publicRuntimePage);
        self::assertStringContainsString('id="no-code-runtime-execution-binding"', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_preview_execution_binding', $publicRuntimePage);
        self::assertStringContainsString("'runtime_data_url'", $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_current_execution_path($projectKey)', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_current_data_path($projectKey)', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_alias_execution_path($projectKey, $aliasKey)', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_alias_data_path($projectKey, $aliasKey)', $publicRuntimePage);
        self::assertStringContainsString('app_render_no_code_public_runtime_execution_page', $publicRuntimePage);
        self::assertStringContainsString('app_render_no_code_public_runtime_current_execution_page', $publicRuntimePage);
        self::assertStringContainsString('app_render_no_code_public_runtime_alias_execution_page', $publicRuntimePage);
        self::assertStringContainsString('app_render_no_code_public_runtime_current_data_page', $publicRuntimePage);
        self::assertStringContainsString('app_render_no_code_public_runtime_alias_data_page', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_execution_response_for_candidate', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_data_response_for_candidate', $publicRuntimePage);
        self::assertStringContainsString('no-code-runtime-data-v0', $publicRuntimePage);
        self::assertStringContainsString('app_auth_principal()', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_runtime_definition_with_action_policy_overlay', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_demo_processing_enabled()', $publicRuntimePage);
        self::assertStringContainsString("getenv('MTOOL_NO_CODE_RUNTIME_SYNC_DEMO')", $publicRuntimePage);
        self::assertStringContainsString("getenv('MTOOL_RUNTIME_SQLITE_PATH')", $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_demo_processing_requested($post)', $publicRuntimePage);
        self::assertStringContainsString("'runtime_demo_process'", $publicRuntimePage);
        self::assertStringContainsString("'demo_processing_disabled'", $publicRuntimePage);
        self::assertStringContainsString("'runtime_entity_failed'", $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_demo_process_execution_outbox', $publicRuntimePage);
        self::assertStringContainsString('app_managed_operation_sync_outbox_process_next', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_artifact_cache_control()', $publicRuntimePage);
        self::assertStringContainsString('app_no_code_public_runtime_current_cache_control()', $publicRuntimePage);
        self::assertStringContainsString("'public, max-age=31536000, immutable'", $publicRuntimePage);
        self::assertStringContainsString("'no-store'", $publicRuntimePage);
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

    public function testOpenApiDocumentUsesGeneratedNamesForSchemasWhenPolicyIsEnabled(): void
    {
        $previousPolicy = getenv('MTOOL_GENERATED_NAME_POLICY');
        putenv('MTOOL_GENERATED_NAME_POLICY=physical-logical-v1');

        try {
            $context = [
                'project_key' => 'MTOOL',
                'source_output_key' => 'OPENAPI-JSON',
                'definition' => [
                    'source_output_key' => 'OPENAPI-JSON',
                    'name' => 'Mtool OpenAPI JSON',
                    'proxy_base_url' => 'http://127.0.0.1:8081',
                ],
                'plan' => [
                    'function_count' => 1,
                    'unresolved_function_count' => 0,
                    'unresolved_auth_count' => 0,
                    'items' => [],
                ],
                'source_entities' => [],
                'proxy_items' => [
                    [
                        'source_name' => 'support_ticket',
                        'function_name' => 'GetSupportTicketList',
                        'display_name' => 'support_ticket.GetSupportTicketList',
                        'auth_policy' => [
                            'strategy_key' => 'no-security',
                            'summary' => '認証を掛けません。',
                        ],
                        'endpoint_filename' => 'proxyserver-support_ticket-GetSupportTicketList.php',
                        'response_property_type' => 'support_ticketList',
                        'steps' => [
                            [
                                'action' => 'select-list',
                                'input_kind' => 'object',
                                'object_param_name' => 'TicketObj',
                                'object_class' => 'support_ticket',
                                'data_class' => 'support_ticket',
                                'parameter_names' => ['TicketObj'],
                                'response_key' => 'Result',
                                'response_mode' => 'direct-result',
                            ],
                        ],
                    ],
                ],
            ];

            $snapshotItems = [
                [
                    'name' => 'support_ticket',
                    'physical_name' => 'support_ticket',
                    'inherit_parent_data_class_name' => '',
                    'fields' => [
                        [
                            'name' => 'updated_at',
                            'physical_name' => 'updated_at',
                            'datatype' => 'datetime',
                            'ref_data_class_name' => '',
                            'ref_data_class_field_name' => '',
                        ],
                        [
                            'name' => 'assigned_user',
                            'physical_name' => 'assigned_user',
                            'datatype' => '',
                            'ref_data_class_name' => 'project_user',
                            'ref_data_class_field_name' => '',
                        ],
                    ],
                ],
                [
                    'name' => 'project_user',
                    'physical_name' => 'project_user',
                    'inherit_parent_data_class_name' => '',
                    'fields' => [
                        [
                            'name' => 'display_name',
                            'physical_name' => 'display_name',
                            'datatype' => 'varchar',
                            'ref_data_class_name' => '',
                            'ref_data_class_field_name' => '',
                        ],
                    ],
                ],
            ];

            $document = app_project_output_openapi_document($context, $snapshotItems);

            self::assertArrayHasKey('/proxyserver-support_ticket-GetSupportTicketList.php', $document['paths']);
            self::assertArrayHasKey('SupportTicket', $document['components']['schemas']);
            self::assertArrayHasKey('ProjectUser', $document['components']['schemas']);
            self::assertArrayHasKey(
                'updatedAt',
                $document['components']['schemas']['SupportTicket']['properties'],
            );
            self::assertSame(
                '#/components/schemas/ProjectUser',
                $document['components']['schemas']['SupportTicket']['properties']['assignedUser']['$ref'] ?? '',
            );

            $operation = $document['paths']['/proxyserver-support_ticket-GetSupportTicketList.php']['post'] ?? null;
            self::assertIsArray($operation);
            self::assertSame(
                '#/components/schemas/SupportTicket',
                $operation['requestBody']['content']['application/json']['schema']['properties']['TicketObj']['$ref'] ?? '',
            );
            self::assertSame(
                '#/components/schemas/SupportTicket',
                $operation['responses']['200']['content']['application/json']['schema']['properties']['Result']['items']['$ref'] ?? '',
            );
            self::assertSame(
                [
                    'TicketObj' => [
                        'updatedAt' => '2026-05-25T00:00:00+09:00',
                        'assignedUser' => [
                            'displayName' => 'string',
                        ],
                    ],
                ],
                $operation['requestBody']['content']['application/json']['example'] ?? null,
            );
        } finally {
            $this->restoreEnvValue('MTOOL_GENERATED_NAME_POLICY', $previousPolicy);
        }
    }

    public function testOpenApiDocumentEmitsStaticBearerSecurityScheme(): void
    {
        $document = app_project_output_openapi_document([
            'project_key' => 'MTOOL',
            'source_output_key' => 'OPENAPI-JSON',
            'definition' => [
                'source_output_key' => 'OPENAPI-JSON',
                'name' => 'Mtool OpenAPI JSON',
                'proxy_base_url' => 'http://127.0.0.1:8081',
            ],
            'plan' => [
                'function_count' => 1,
                'unresolved_function_count' => 0,
                'unresolved_auth_count' => 0,
                'items' => [],
            ],
            'source_entities' => [],
            'proxy_items' => [
                [
                    'source_name' => 'Project',
                    'function_name' => 'GetProjectList',
                    'display_name' => 'Project.GetProjectList',
                    'auth_policy' => [
                        'strategy_key' => 'static-bearer',
                        'summary' => 'static bearer 認証です。',
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
                            'parameter_names' => [],
                            'response_key' => 'Result',
                            'response_mode' => 'direct-result',
                        ],
                    ],
                ],
            ],
        ], []);

        $operation = $document['paths']['/proxyserver-Project-GetProjectList.php']['post'] ?? null;
        self::assertIsArray($operation);
        self::assertSame([['StaticBearerAuth' => []]], $operation['security'] ?? null);
        self::assertSame(
            [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'opaque',
            ],
            $document['components']['securitySchemes']['StaticBearerAuth'] ?? null,
        );
        self::assertStringNotContainsString(
            'TOKEN',
            json_encode($operation['requestBody']['content']['application/json'] ?? [], JSON_THROW_ON_ERROR),
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
            [
                'auth_strategy' => 'static-bearer',
            ],
        ]);

        self::assertSame(4, $summary['auth_operation_count']);
        self::assertSame(1, $summary['project_token_required_count']);
        self::assertSame(1, $summary['project_token_optional_count']);
        self::assertSame(1, $summary['login_cookie_token_required_count']);
        self::assertSame(1, $summary['static_bearer_required_count']);
        self::assertTrue($summary['requires_auth_helper']);
    }

    public function testLabSwaggerOperationCatalogMarksStaticBearerHeaderAuth(): void
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
                            'auth_strategy' => 'static-bearer',
                            'input_kind' => 'scalar',
                            'response_mode' => 'direct-result',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertCount(1, $operations);
        self::assertSame(['Authorization: Bearer'], $operations[0]['auth_required_fields']);
        self::assertStringContainsString('Authorization: Bearer', $operations[0]['auth_notice']);
        self::assertSame("{}\n", $operations[0]['request_example_pretty']);
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

    public function testGeneratedSingleProxyStaticBearerAuthValidatesAuthorizationHeader(): void
    {
        $baseClass = 'MtoolGeneratedSingleProxyEndpointBaseContract';
        $subjectClass = 'MtoolGeneratedSingleProxyEndpointStaticBearerContractSubject';

        $this->ensureGeneratedSingleProxyRuntimeClassExists($baseClass);
        $this->ensureGeneratedSingleProxyContractSubjectExists($baseClass, $subjectClass, [
            'auth_strategy' => 'static-bearer',
        ]);

        $previousBearerToken = getenv('DEGODB_PROXY_BEARER_TOKEN');
        $previousLegacyBearerToken = getenv('MTOOL_PROXY_BEARER_TOKEN');
        $previousAuthorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $previousRedirectAuthorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        try {
            putenv('DEGODB_PROXY_BEARER_TOKEN');
            putenv('MTOOL_PROXY_BEARER_TOKEN');
            unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);

            $subject = new $subjectClass();
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'Authorization bearer header が必要です。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Token abc';
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'Authorization header は Bearer token 形式である必要があります。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer supplied-token';
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'DEGODB_PROXY_BEARER_TOKEN が未設定です。',
            );

            putenv('DEGODB_PROXY_BEARER_TOKEN=expected-token');
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'Bearer token が一致しません。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer expected-token';
            $this->invokeGeneratedProxyAuthorizeRequest($subject, $baseClass, []);
            self::assertTrue(true);
        } finally {
            $this->restoreEnvValue('DEGODB_PROXY_BEARER_TOKEN', $previousBearerToken);
            $this->restoreEnvValue('MTOOL_PROXY_BEARER_TOKEN', $previousLegacyBearerToken);
            $this->restoreServerValue('HTTP_AUTHORIZATION', $previousAuthorization);
            $this->restoreServerValue('REDIRECT_HTTP_AUTHORIZATION', $previousRedirectAuthorization);
        }
    }

    public function testGeneratedSingleProxyOidcJwtBearerAuthValidatesJwtClaims(): void
    {
        $baseClass = 'MtoolGeneratedSingleProxyEndpointBaseContract';
        $subjectClass = 'MtoolGeneratedSingleProxyEndpointOidcJwtBearerContractSubject';

        $this->ensureGeneratedSingleProxyRuntimeClassExists($baseClass);
        $oidcFixture = $this->createOidcJwtFixture();
        $this->ensureGeneratedSingleProxyContractSubjectExists($baseClass, $subjectClass, [
            'auth_strategy' => 'oidc-jwt-bearer',
            'auth_policy' => [
                'type' => 'oidc-jwt-bearer',
                'issuer' => 'https://idp.example.test/realms/dego',
                'audience' => 'dego-generated-api',
                'jwks_json_env' => 'DEGODB_TEST_OIDC_JWKS_JSON',
                'required_claims' => [
                    'scope' => 'dego.read',
                    'tenant' => 'dego',
                ],
            ],
        ]);

        $previousJwksJson = getenv('DEGODB_TEST_OIDC_JWKS_JSON');
        $previousAuthorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        $previousRedirectAuthorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        try {
            putenv('DEGODB_TEST_OIDC_JWKS_JSON');
            unset($_SERVER['HTTP_AUTHORIZATION'], $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            $subject = new $subjectClass();

            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'Authorization bearer header が必要です。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Token not-a-bearer-token';
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'Authorization header は Bearer token 形式である必要があります。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $oidcFixture['valid_token'];
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'OIDC JWKS env が未設定です: DEGODB_TEST_OIDC_JWKS_JSON',
            );

            putenv('DEGODB_TEST_OIDC_JWKS_JSON=' . $oidcFixture['jwks_json']);
            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $oidcFixture['invalid_signature_token'];
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'Signature verification failed',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $oidcFixture['wrong_issuer_token'];
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'OIDC JWT issuer が一致しません。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $oidcFixture['wrong_audience_token'];
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'OIDC JWT audience が一致しません。',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $oidcFixture['missing_claim_token'];
            $this->assertGeneratedProxyAuthorizeThrows(
                $subject,
                $baseClass,
                [],
                'OIDC JWT required claim が一致しません: scope',
            );

            $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $oidcFixture['valid_token'];
            $this->invokeGeneratedProxyAuthorizeRequest($subject, $baseClass, []);
            self::assertTrue(true);
        } finally {
            $this->restoreEnvValue('DEGODB_TEST_OIDC_JWKS_JSON', $previousJwksJson);
            $this->restoreServerValue('HTTP_AUTHORIZATION', $previousAuthorization);
            $this->restoreServerValue('REDIRECT_HTTP_AUTHORIZATION', $previousRedirectAuthorization);
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
     *     auth_policy?:array<string,mixed>,
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
        $authPolicy = var_export($options['auth_policy'] ?? [], true);
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

    protected function authPolicy(): array
    {
        return {$authPolicy};
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

    /**
     * @return array{
     *     jwks_json:string,
     *     valid_token:string,
     *     invalid_signature_token:string,
     *     wrong_issuer_token:string,
     *     wrong_audience_token:string,
     *     missing_claim_token:string
     * }
     */
    private function createOidcJwtFixture(): array
    {
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse($key);

        $privateKey = '';
        self::assertTrue(openssl_pkey_export($key, $privateKey));
        $otherKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        self::assertNotFalse($otherKey);
        $otherPrivateKey = '';
        self::assertTrue(openssl_pkey_export($otherKey, $otherPrivateKey));
        $details = openssl_pkey_get_details($key);
        self::assertIsArray($details);
        self::assertIsArray($details['rsa'] ?? null);

        $kid = 'dego-test-key';
        $now = time();
        $baseClaims = [
            'iss' => 'https://idp.example.test/realms/dego',
            'aud' => 'dego-generated-api',
            'sub' => 'user-1',
            'iat' => $now,
            'nbf' => $now - 30,
            'exp' => $now + 300,
            'scope' => 'openid dego.read',
            'tenant' => 'dego',
        ];

        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'kid' => $kid,
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => $this->base64UrlEncodeUnsignedInteger($details['rsa']['n']),
                    'e' => $this->base64UrlEncodeUnsignedInteger($details['rsa']['e']),
                ],
            ],
        ];

        $wrongIssuerClaims = $baseClaims;
        $wrongIssuerClaims['iss'] = 'https://idp.example.test/realms/other';
        $wrongAudienceClaims = $baseClaims;
        $wrongAudienceClaims['aud'] = 'other-api';
        $missingClaimClaims = $baseClaims;
        $missingClaimClaims['scope'] = 'openid profile';

        return [
            'jwks_json' => json_encode($jwks, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'valid_token' => \Firebase\JWT\JWT::encode($baseClaims, $privateKey, 'RS256', $kid),
            'invalid_signature_token' => \Firebase\JWT\JWT::encode($baseClaims, $otherPrivateKey, 'RS256', $kid),
            'wrong_issuer_token' => \Firebase\JWT\JWT::encode($wrongIssuerClaims, $privateKey, 'RS256', $kid),
            'wrong_audience_token' => \Firebase\JWT\JWT::encode($wrongAudienceClaims, $privateKey, 'RS256', $kid),
            'missing_claim_token' => \Firebase\JWT\JWT::encode($missingClaimClaims, $privateKey, 'RS256', $kid),
        ];
    }

    private function base64UrlEncodeUnsignedInteger(string $value): string
    {
        return rtrim(strtr(base64_encode(ltrim($value, "\x00")), '+/', '-_'), '=');
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

    private function restoreServerValue(string $name, ?string $value): void
    {
        if ($value === null) {
            unset($_SERVER[$name]);
            return;
        }

        $_SERVER[$name] = $value;
    }

    private function assertGeneratedProxyAuthorizeThrows(
        object $subject,
        string $baseClass,
        array $payload,
        string $expectedMessage,
    ): void {
        try {
            $this->invokeGeneratedProxyAuthorizeRequest($subject, $baseClass, $payload);
            self::fail('Expected generated proxy authorization to throw: ' . $expectedMessage);
        } catch (RuntimeException $exception) {
            self::assertSame($expectedMessage, $exception->getMessage());
        }
    }
}
