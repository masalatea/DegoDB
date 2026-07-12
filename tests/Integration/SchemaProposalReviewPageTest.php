<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_review_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalReviewPageTest extends TestCase
{
    private string|false $previousEnabled;
    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void { $this->previousEnabled = getenv('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED'); }
    protected function tearDown(): void
    {
        $this->previousEnabled === false ? putenv('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED') : putenv('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED=' . $this->previousEnabled);
        foreach ($this->tempFiles as $file) @unlink($file);
    }

    public function testSwitchDefaultsOffAndAcceptsExplicitTruthyValues(): void
    {
        foreach (['', '0', 'false', 'unexpected'] as $value) { putenv('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED=' . $value); self::assertFalse(app_schema_proposal_review_enabled(), $value); }
        foreach (['1', 'true', 'TRUE', 'yes', 'on'] as $value) { putenv('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED=' . $value); self::assertTrue(app_schema_proposal_review_enabled(), $value); }
    }

    public function testRouteUsesExactProposalIdentityAndRequiresAuthentication(): void
    {
        $route = app_route_match(['path' => '/projects/SAMPLE19/schema-proposals/sample19-article-content-model-v1']);
        self::assertSame('project_schema_proposal_review', $route['name']);
        self::assertSame('SAMPLE19', $route['params']['project_key']);
        self::assertSame(APP_SCHEMA_PROPOSAL_SAMPLE19_ID, $route['params']['proposal_id']);
        self::assertTrue(app_route_requires_auth($route['name']));
    }

    public function testValidFixtureRendersReadOnlyReviewablePage(): void
    {
        $review = app_schema_proposal_review_load();
        self::assertTrue($review['ok'], $review['error']);
        self::assertSame('reviewable', $review['page_state']);
        self::assertCount(4, $review['diff']);

        $html = app_schema_proposal_review_html($review);
        foreach (['data-schema-proposal-review="true"', 'data-schema-proposal-review-state="reviewable"', 'data-proposal-state="proposal_only"', 'data-apply-supported="false"', 'data-ai-authored="false"', 'data-source-hash-verified="true"', 'data-proposal-entity="article_json_model"', 'data-proposal-diff-category="unchanged"', 'data-proposal-blocking-question="author_name_identity"', 'data-proposal-assumption="category_name_identity"', 'data-schema-proposal-return'] as $marker) {
            self::assertStringContainsString($marker, $html);
        }
        self::assertStringNotContainsString('<form', $html);
        self::assertStringNotContainsString('<button', $html);
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('method="post"', strtolower($html));
        self::assertStringNotContainsString('data-runtime-execute', $html);
    }

    public function testSeverityAndPageStateCoverEveryDiffCategory(): void
    {
        self::assertSame('ok', app_schema_proposal_review_diff_severity('unchanged'));
        self::assertSame('info', app_schema_proposal_review_diff_severity('add'));
        self::assertSame('review_required', app_schema_proposal_review_diff_severity('change'));
        self::assertSame('blocking', app_schema_proposal_review_diff_severity('remove'));
        self::assertSame('blocking', app_schema_proposal_review_diff_severity('conflict'));
        self::assertSame('reviewable', app_schema_proposal_review_page_state([['category' => 'add']]));
        self::assertSame('review_required', app_schema_proposal_review_page_state([['category' => 'change']]));
        self::assertSame('blocking', app_schema_proposal_review_page_state([['category' => 'change'], ['category' => 'remove']]));
        self::assertSame('blocking', app_schema_proposal_review_page_state([['category' => 'conflict']]));
    }

    public function testLoaderFailsClosedForUnreadableSourceHashMismatchAndDiffMismatch(): void
    {
        $paths = app_schema_proposal_sample19_paths();
        $missing = $paths; $missing['source'] = '/missing/source.json';
        self::assertSame('fixture_unreadable:source', app_schema_proposal_review_load($missing)['error']);

        $badSource = $this->tempFile('changed source');
        $hashMismatch = $paths; $hashMismatch['source'] = $badSource;
        self::assertSame('source_hash_mismatch', app_schema_proposal_review_load($hashMismatch)['error']);

        $proposal = json_decode((string) file_get_contents($paths['proposal']), true, 512, JSON_THROW_ON_ERROR);
        $proposal['canonical_diff'][0]['review_note'] = 'contradiction';
        $badProposal = $this->tempFile((string) json_encode($proposal, JSON_UNESCAPED_SLASHES));
        $mismatch = $paths; $mismatch['proposal'] = $badProposal;
        $result = app_schema_proposal_review_load($mismatch);
        self::assertSame(409, $result['status_code']);
        self::assertSame('declared_canonical_diff_mismatch', $result['error']);
    }

    public function testRootComposePassesDefaultOffReviewSwitchToAdmin(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2) . '/compose.yaml');
        self::assertIsString($compose);
        self::assertStringContainsString('MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED: ${MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED:-}', $compose);
    }

    private function tempFile(string $contents): string
    {
        $file = tempnam(sys_get_temp_dir(), 'schema-proposal-review-');
        self::assertIsString($file);
        file_put_contents($file, $contents);
        $this->tempFiles[] = $file;
        return $file;
    }
}
