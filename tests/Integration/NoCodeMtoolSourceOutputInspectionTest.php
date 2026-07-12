<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_mtool_source_output_inspection_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_source_outputs_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class NoCodeMtoolSourceOutputInspectionTest extends TestCase
{
    private string|false $previousEnabled;

    protected function setUp(): void
    {
        $this->previousEnabled = getenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED');
    }

    protected function tearDown(): void
    {
        if ($this->previousEnabled === false) {
            putenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED');
            return;
        }

        putenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED=' . $this->previousEnabled);
    }

    public function testFeatureSwitchIsDefaultOffAndAcceptsOnlyExplicitTruthyValues(): void
    {
        foreach (['', '0', 'false', 'disabled', 'unexpected'] as $value) {
            putenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED=' . $value);
            self::assertFalse(app_no_code_mtool_source_output_inspection_enabled(), $value);
        }

        foreach (['1', 'true', 'TRUE', 'yes', 'on'] as $value) {
            putenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED=' . $value);
            self::assertTrue(app_no_code_mtool_source_output_inspection_enabled(), $value);
        }
    }

    public function testSpecificInspectionRoutePrecedesGenericSourceOutputDetailAndRequiresAuth(): void
    {
        $route = app_route_match([
            'path' => '/projects/MTOOL/source-outputs/no-code-inspection',
        ]);

        self::assertSame('project_source_outputs_no_code_inspection', $route['name']);
        self::assertSame('MTOOL', $route['params']['project_key'] ?? '');
        self::assertTrue(app_route_requires_auth($route['name']));
    }

    public function testRootComposePassesDefaultOffInspectionSwitchToAdmin(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2) . '/compose.yaml');
        self::assertIsString($compose);
        self::assertStringContainsString(
            'MTOOL_NO_CODE_SELF_INSPECTION_ENABLED: ${MTOOL_NO_CODE_SELF_INSPECTION_ENABLED:-}',
            $compose,
        );
    }

    public function testCanonicalSourceOutputsEntryPointIsMtoolOnlyAndDefaultOff(): void
    {
        putenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED');
        self::assertFalse(app_project_source_outputs_show_no_code_self_inspection_link('MTOOL'));

        putenv('MTOOL_NO_CODE_SELF_INSPECTION_ENABLED=1');
        self::assertTrue(app_project_source_outputs_show_no_code_self_inspection_link('MTOOL'));
        self::assertTrue(app_project_source_outputs_show_no_code_self_inspection_link('mtool'));
        self::assertFalse(app_project_source_outputs_show_no_code_self_inspection_link('SAMPLE28'));

        $sourceOutputsPage = (string) file_get_contents(dirname(__DIR__, 2) . '/mtool/app/project_source_outputs_page.php');
        self::assertStringContainsString('data-mtool-no-code-inspection-entry-point="true"', $sourceOutputsPage);
        self::assertStringContainsString('/projects/MTOOL/source-outputs/no-code-inspection', $sourceOutputsPage);
        self::assertStringContainsString('Open read-only no-code inspection', $sourceOutputsPage);
        self::assertStringContainsString('does not replace canonical Source Outputs', $sourceOutputsPage);
        self::assertStringNotContainsString('data-runtime-execute', $sourceOutputsPage);
        self::assertStringNotContainsString('data-guarded-click-submit', $sourceOutputsPage);
    }

    public function testHybridContractDeclaresGeneratedCustomAndRollbackBoundaries(): void
    {
        $contract = app_no_code_mtool_source_output_inspection_hybrid_contract();

        self::assertSame(
            'no-code-mtool-source-output-inspection-hybrid-v0',
            $contract['contract_version'] ?? '',
        );
        self::assertSame('mtool_source_output_inspection', $contract['workflow_key'] ?? '');
        self::assertSame('GET', $contract['route']['method'] ?? '');
        self::assertSame(
            '/projects/MTOOL/source-outputs/no-code-inspection',
            $contract['route']['path'] ?? '',
        );
        self::assertSame(
            'MTOOL_NO_CODE_SELF_INSPECTION_ENABLED',
            $contract['route']['feature_flag'] ?? '',
        );
        self::assertContains(
            'read-only list/detail screen rendering',
            $contract['generated_owns'] ?? [],
        );
        self::assertContains(
            'Source Output repository reads',
            $contract['custom_mtool_owns'] ?? [],
        );
        self::assertSame(
            [
                'source_output_key',
                'name',
                'class_type',
                'artifact_strategy',
                'target_binding_type',
                'spec_visibility',
                'source_output_dir',
            ],
            $contract['row_adapter_fields'] ?? [],
        );
        self::assertSame(
            'missing_detail_no_fallback',
            $contract['selector_policy']['unknown_selector'] ?? '',
        );
        self::assertSame(
            ['GET'],
            $contract['authority_boundary']['allowed_methods'] ?? [],
        );
        self::assertSame(
            'not_supported',
            $contract['authority_boundary']['post_routes'] ?? '',
        );
        self::assertSame(
            'none',
            $contract['rollback_boundary']['state_change'] ?? '',
        );
        self::assertContains(
            'generated_post_execution',
            $contract['excluded_operations'] ?? [],
        );
    }

    public function testRowAdapterKeepsOnlyDeclaredFieldsAndNormalizesScalars(): void
    {
        $rows = app_no_code_mtool_source_output_inspection_rows([[
            'source_output_key' => 'NO-CODE-RUNTIME',
            'name' => 'No-Code Runtime',
            'class_type' => 'NO-CODE-RUNTIME',
            'artifact_strategy' => 'runtime-preview',
            'target_binding_type' => 'managed-screen',
            'spec_visibility' => 'internal',
            'source_output_dir' => 'work/source-outputs/MTOOL/NO-CODE-RUNTIME',
            'notes' => 'must not leak',
            'internal_id' => 123,
        ]]);

        self::assertSame([[
            'source_output_key' => 'NO-CODE-RUNTIME',
            'name' => 'No-Code Runtime',
            'class_type' => 'NO-CODE-RUNTIME',
            'artifact_strategy' => 'runtime-preview',
            'target_binding_type' => 'managed-screen',
            'spec_visibility' => 'internal',
            'source_output_dir' => 'work/source-outputs/MTOOL/NO-CODE-RUNTIME',
        ]], $rows);
    }

    public function testSelectionDefaultsOnlyWhenSelectorIsAbsentAndUnknownSelectorFailsClosed(): void
    {
        $rows = $this->rows();

        $default = app_no_code_mtool_source_output_inspection_selection($rows, '');
        self::assertFalse($default['missing']);
        self::assertSame('NO-CODE-RUNTIME', $default['item']['source_output_key'] ?? '');

        $selected = app_no_code_mtool_source_output_inspection_selection($rows, 'api-output');
        self::assertFalse($selected['missing']);
        self::assertSame('API-OUTPUT', $selected['item']['source_output_key'] ?? '');

        $missing = app_no_code_mtool_source_output_inspection_selection($rows, 'missing-output');
        self::assertTrue($missing['missing']);
        self::assertSame([], $missing['item']);
        self::assertSame('MISSING-OUTPUT', $missing['selector']);

        $invalid = app_no_code_mtool_source_output_inspection_selection($rows, '@@@');
        self::assertTrue($invalid['missing']);
        self::assertSame([], $invalid['item']);
    }

    public function testRendersLiveListAndDetailWithoutEditableScreenOrExecutionBinding(): void
    {
        $html = app_no_code_mtool_source_output_inspection_html(
            $this->rows(),
            $this->principal(),
            'API-OUTPUT',
        );

        self::assertStringContainsString('data-mtool-no-code-source-output-inspection="true"', $html);
        self::assertStringContainsString('data-mtool-no-code-hybrid-contract="true"', $html);
        self::assertStringContainsString('no-code-mtool-source-output-inspection-hybrid-v0', $html);
        self::assertStringContainsString('generated_post_execution', $html);
        self::assertStringContainsString('data-screen-key="mtool_source_output_review_list"', $html);
        self::assertStringContainsString('data-screen-key="mtool_source_output_review_detail"', $html);
        self::assertStringContainsString('API Output', $html);
        self::assertStringContainsString('data-canonical-source-outputs-link', $html);
        self::assertStringNotContainsString('data-screen-key="mtool_source_output_review_form"', $html);
        self::assertStringNotContainsString('data-runtime-execute', $html);
        self::assertStringNotContainsString('data-guarded-click-submit', $html);
        self::assertStringNotContainsString('/runtime-execution', $html);
    }

    public function testEmptyAndUnknownSelectionStatesDoNotFabricateRows(): void
    {
        $emptyHtml = app_no_code_mtool_source_output_inspection_html([], $this->principal());
        self::assertStringContainsString('No records to show yet.', $emptyHtml);
        self::assertStringContainsString('No preview data is available yet.', $emptyHtml);

        $missingHtml = app_no_code_mtool_source_output_inspection_html(
            $this->rows(),
            $this->principal(),
            'UNKNOWN',
        );
        self::assertStringContainsString('data-mtool-no-code-selection-missing="true"', $missingHtml);
        self::assertStringContainsString('UNKNOWN', $missingHtml);
        self::assertStringContainsString('No preview data is available yet.', $missingHtml);
    }

    /** @return list<array<string,mixed>> */
    private function rows(): array
    {
        return [
            [
                'source_output_key' => 'NO-CODE-RUNTIME',
                'name' => 'No-Code Runtime',
                'class_type' => 'NO-CODE-RUNTIME',
                'artifact_strategy' => 'runtime-preview',
                'target_binding_type' => 'managed-screen',
                'spec_visibility' => 'internal',
                'source_output_dir' => 'work/source-outputs/MTOOL/NO-CODE-RUNTIME',
            ],
            [
                'source_output_key' => 'API-OUTPUT',
                'name' => 'API Output',
                'class_type' => 'OpenAPI',
                'artifact_strategy' => 'generated',
                'target_binding_type' => 'api',
                'spec_visibility' => 'internal',
                'source_output_dir' => 'work/source-outputs/MTOOL/API-OUTPUT',
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function principal(): array
    {
        return [
            'id' => 'mtool-viewer-1',
            'display_name' => 'Mtool Viewer',
            'auth_source' => 'stub',
            'site' => 'admin',
            'roles' => ['viewer'],
            'project_roles' => ['MTOOL' => ['viewer']],
            'scopes' => [],
            'claims' => [],
        ];
    }
}
