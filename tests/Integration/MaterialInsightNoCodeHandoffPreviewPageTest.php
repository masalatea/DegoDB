<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/material_insight_no_code_handoff_preview_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class MaterialInsightNoCodeHandoffPreviewPageTest extends TestCase
{
    private string|false $previousEnabled;

    protected function setUp(): void
    {
        $this->previousEnabled = getenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED');
    }

    protected function tearDown(): void
    {
        $this->previousEnabled === false
            ? putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED')
            : putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED=' . $this->previousEnabled);
    }

    public function testSwitchDefaultsOffAndAcceptsExplicitTruthyValues(): void
    {
        foreach (['', '0', 'false', 'unexpected'] as $value) {
            putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED=' . $value);
            self::assertFalse(app_material_insight_no_code_handoff_preview_enabled(), $value);
        }
        foreach (['1', 'true', 'TRUE', 'yes', 'on'] as $value) {
            putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED=' . $value);
            self::assertTrue(app_material_insight_no_code_handoff_preview_enabled(), $value);
        }
    }

    public function testRouteRequiresAuthentication(): void
    {
        $route = app_route_match(['path' => '/projects/SAMPLE19/material-insight/no-code-handoff']);
        self::assertSame('project_sample19_material_insight_no_code_handoff_preview', $route['name']);
        self::assertSame('SAMPLE19', $route['params']['project_key']);
        self::assertTrue(app_route_requires_auth($route['name']));
    }

    public function testValidFixtureRendersReadOnlyNoCodeHandoffMarkers(): void
    {
        $payload = app_material_insight_no_code_handoff_preview_load();
        self::assertTrue($payload['ok'], $payload['error']);

        $html = app_material_insight_no_code_handoff_preview_html($payload);
        foreach ([
            'data-material-insight-no-code-handoff="true"',
            'data-no-code-handoff-mutation="false"',
            'data-no-code-handoff-ai-call="false"',
            'data-no-code-screen-definition-version="no-code-screen-definition-v0"',
            'data-no-code-runtime-version="no-code-runtime-v0"',
            'data-no-code-handoff-screen="material_entity_list"',
            'data-no-code-handoff-screen="material_qa_cards"',
            'data-no-code-handoff-actions="0"',
            'data-no-code-handoff-custom-operations="0"',
            'data-no-code-handoff-return',
        ] as $marker) {
            self::assertStringContainsString($marker, $html);
        }
        self::assertStringNotContainsString('<form', $html);
        self::assertStringNotContainsString('<button', $html);
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('method="post"', strtolower($html));
        self::assertStringNotContainsString('data-runtime-execute', $html);
        self::assertStringNotContainsString('data-guarded-click-submit', $html);
    }

    public function testRootComposePassesDefaultOffHandoffSwitchToAdmin(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2) . '/compose.yaml');
        self::assertIsString($compose);
        self::assertStringContainsString('MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED: ${MTOOL_SAMPLE19_MATERIAL_INSIGHT_NO_CODE_HANDOFF_PREVIEW_ENABLED:-}', $compose);
    }
}
