<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/material_insight_preview_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class MaterialInsightPreviewPageTest extends TestCase
{
    private string|false $previousEnabled;
    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        $this->previousEnabled = getenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED');
    }

    protected function tearDown(): void
    {
        $this->previousEnabled === false
            ? putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED')
            : putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED=' . $this->previousEnabled);
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
    }

    public function testSwitchDefaultsOffAndAcceptsExplicitTruthyValues(): void
    {
        foreach (['', '0', 'false', 'unexpected'] as $value) {
            putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED=' . $value);
            self::assertFalse(app_material_insight_preview_enabled(), $value);
        }
        foreach (['1', 'true', 'TRUE', 'yes', 'on'] as $value) {
            putenv('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED=' . $value);
            self::assertTrue(app_material_insight_preview_enabled(), $value);
        }
    }

    public function testRouteRequiresAuthentication(): void
    {
        $route = app_route_match(['path' => '/projects/SAMPLE19/material-insight']);
        self::assertSame('project_sample19_material_insight_preview', $route['name']);
        self::assertSame('SAMPLE19', $route['params']['project_key']);
        self::assertTrue(app_route_requires_auth($route['name']));
    }

    public function testValidFixtureRendersReadOnlyPreviewMarkers(): void
    {
        $preview = app_material_insight_preview_load();
        self::assertTrue($preview['ok'], $preview['error']);

        $html = app_material_insight_preview_html($preview);
        foreach ([
            'data-material-insight-preview="true"',
            'data-material-insight-mutation="false"',
            'data-material-insight-ai-call="false"',
            'data-material-insight-source-hash="true"',
            'data-material-insight-basis="true"',
            'data-material-insight-qa-card="entities_implied"',
            'data-material-insight-qa-card="relationships_supported"',
            'data-material-insight-qa-category="structure"',
            'data-material-insight-qa-category="relationship"',
            'data-material-insight-qa-category="ui_outline"',
            'data-material-insight-qa-evidence="/article"',
            'data-material-insight-qa-evidence="/article/author"',
            'data-material-insight-ui-screen="material_entity_list"',
            'data-material-insight-ui-screen="material_qa_cards"',
            'data-material-insight-ui-section="entity_review"',
            'data-material-insight-ui-section="qa_review"',
            'data-material-insight-prohibited-action="apply"',
            'data-material-insight-prohibited-action="route_execution"',
            'data-material-insight-return',
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

    public function testLoaderFailsClosedForUnreadableAndInvalidFixtures(): void
    {
        $paths = app_schema_proposal_sample19_paths();
        $missing = $paths;
        $missing['source'] = '/missing/source.json';
        self::assertSame('fixture_unreadable:source', app_material_insight_preview_load($missing)['error']);

        $badProposal = $this->tempFile('{');
        $invalid = $paths;
        $invalid['proposal'] = $badProposal;
        self::assertStringStartsWith('proposal_invalid:', app_material_insight_preview_load($invalid)['error']);
    }

    public function testRootComposePassesDefaultOffPreviewSwitchToAdmin(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2) . '/compose.yaml');
        self::assertIsString($compose);
        self::assertStringContainsString('MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED: ${MTOOL_SAMPLE19_MATERIAL_INSIGHT_PREVIEW_ENABLED:-}', $compose);
    }

    private function tempFile(string $contents): string
    {
        $file = tempnam(sys_get_temp_dir(), 'material-insight-preview-');
        self::assertIsString($file);
        file_put_contents($file, $contents);
        $this->tempFiles[] = $file;
        return $file;
    }
}
