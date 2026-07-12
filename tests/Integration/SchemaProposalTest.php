<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalTest extends TestCase
{
    public function testSample19GoldenProposalIsValidAndBoundToSourceBytes(): void
    {
        $proposal = $this->proposal();
        $validation = app_schema_proposal_validate($proposal);

        self::assertTrue($validation['ok'], implode(', ', $validation['errors']));
        self::assertSame([], $validation['errors']);
        self::assertSame('proposal_only', $proposal['state']);
        self::assertFalse($proposal['apply_supported']);
        self::assertSame('deterministic_fixture', $proposal['provenance']['kind'] ?? '');
        self::assertFalse($proposal['provenance']['ai_authored'] ?? true);
        self::assertSame(
            hash_file('sha256', $this->sampleRoot() . '/proposal/source/article.json'),
            $proposal['source']['sha256'] ?? '',
        );
        self::assertSame(
            ['json_author', 'json_category', 'article_json_model', 'article_public_summary'],
            array_column($proposal['entities'], 'entity_key'),
        );
        self::assertSame(['unchanged'], array_values(array_unique(array_column($proposal['canonical_diff'], 'category'))));
    }

    public function testDerivedMarkdownMatchesGoldenReviewView(): void
    {
        self::assertSame(
            file_get_contents($this->sampleRoot() . '/golden/schema-proposal.md'),
            app_schema_proposal_markdown($this->proposal()),
        );
    }

    public function testDecodeFailsClosedForInvalidJsonAndNonObjectJson(): void
    {
        self::assertSame(['invalid_json: Syntax error'], app_schema_proposal_decode('{')['errors']);
        self::assertSame(['proposal_must_be_object'], app_schema_proposal_decode('"text"')['errors']);
    }

    public function testRejectsUnknownVersionUnsafeStateAndApplySupport(): void
    {
        $proposal = $this->proposal();
        $proposal['proposal_version'] = 'future';
        $proposal['state'] = 'approved';
        $proposal['apply_supported'] = true;

        $errors = app_schema_proposal_validate($proposal)['errors'];
        self::assertContains('unsupported_proposal_version', $errors);
        self::assertContains('state_must_be_proposal_only', $errors);
        self::assertContains('apply_supported_must_be_false', $errors);
    }

    public function testRejectsMalformedHashDuplicateKeysAndEvidenceOutsideRoot(): void
    {
        $proposal = $this->proposal();
        $proposal['source']['sha256'] = 'bad';
        $proposal['entities'][] = $proposal['entities'][0];
        $proposal['entities'][0]['fields'][0]['evidence'][0]['pointer'] = '/outside/id';

        $errors = app_schema_proposal_validate($proposal)['errors'];
        self::assertContains('invalid_source_sha256', $errors);
        self::assertContains('duplicate_entity_key:json_author', $errors);
        self::assertContains('evidence_outside_source_root:field:json_author:id:0', $errors);
    }

    public function testRejectsUnknownRelationshipAndDbAccessReferences(): void
    {
        $proposal = $this->proposal();
        $proposal['relationships'][0]['to_entity'] = 'missing_entity';
        $proposal['degodb_targets']['db_access'][0]['entity_key'] = 'missing_entity';
        $proposal['degodb_targets']['db_access'][0]['output_data_class'] = 'MissingClass';

        $errors = app_schema_proposal_validate($proposal)['errors'];
        self::assertContains('unknown_relationship_entity:article_author', $errors);
        self::assertContains('unknown_db_access_entity:GetPublishedArticlePublicSummaryList', $errors);
        self::assertContains('unknown_db_access_data_class:GetPublishedArticlePublicSummaryList', $errors);
    }

    public function testRejectsUnknownDiffCategoryAndMarkdownRefusesInvalidProposal(): void
    {
        $proposal = $this->proposal();
        $proposal['canonical_diff'][0]['category'] = 'apply';

        self::assertContains('invalid_diff_category:0', app_schema_proposal_validate($proposal)['errors']);

        $this->expectException(InvalidArgumentException::class);
        app_schema_proposal_markdown($proposal);
    }

    public function testDerivesGoldenCanonicalDiffAndVerifiesDeclaredEntries(): void
    {
        $proposal = $this->proposal();
        $snapshot = $this->snapshot();

        $derived = app_schema_proposal_derive_canonical_diff($proposal, $snapshot);
        self::assertTrue($derived['ok'], implode(', ', $derived['errors']));
        self::assertSame($proposal['canonical_diff'], $derived['diff']);
        self::assertSame(['unchanged'], array_values(array_unique(array_column($derived['diff'], 'category'))));

        $verification = app_schema_proposal_verify_declared_diff($proposal, $snapshot);
        self::assertTrue($verification['ok'], implode(', ', $verification['errors']));
    }

    public function testDerivesAddChangeRemoveAndConflictWithoutMutation(): void
    {
        $proposal = $this->proposal();
        $snapshot = $this->snapshot();

        array_pop($snapshot['entities']);
        $snapshot['entities'][0]['field_keys'][] = 'legacy_name';
        $snapshot['entities'][1]['conflict'] = true;
        $snapshot['entities'][] = [
            'entity_key' => 'legacy_only',
            'field_keys' => ['id'],
            'key_keys' => ['pk_legacy_only'],
            'relationship_keys' => [],
        ];

        $derived = app_schema_proposal_derive_canonical_diff($proposal, $snapshot);
        self::assertTrue($derived['ok'], implode(', ', $derived['errors']));
        self::assertSame([
            'json_author' => 'change',
            'json_category' => 'conflict',
            'article_json_model' => 'unchanged',
            'article_public_summary' => 'add',
            'legacy_only' => 'remove',
        ], array_column($derived['diff'], 'category', 'object_key'));
        self::assertSame('proposal_only', $proposal['state']);
        self::assertFalse($proposal['apply_supported']);
    }

    public function testRejectsDeclaredDiffThatContradictsDerivedSnapshot(): void
    {
        $proposal = $this->proposal();
        $proposal['canonical_diff'][0]['category'] = 'change';

        $verification = app_schema_proposal_verify_declared_diff($proposal, $this->snapshot());
        self::assertFalse($verification['ok']);
        self::assertSame(['declared_canonical_diff_mismatch'], $verification['errors']);
        self::assertSame('unchanged', $verification['derived_diff'][0]['category'] ?? '');
    }

    public function testCanonicalSnapshotValidationFailsClosed(): void
    {
        $snapshot = $this->snapshot();
        $snapshot['snapshot_version'] = 'future';
        $snapshot['project_key'] = 'OTHER';
        $snapshot['entities'][] = $snapshot['entities'][0];

        $errors = app_schema_proposal_validate_canonical_snapshot($snapshot, 'SAMPLE19');
        self::assertContains('unsupported_canonical_snapshot_version', $errors);
        self::assertContains('canonical_snapshot_project_mismatch', $errors);
        self::assertContains('duplicate_canonical_entity_key:json_author', $errors);
    }

    /** @return array<string,mixed> */
    private function proposal(): array
    {
        $proposal = json_decode(
            (string) file_get_contents($this->sampleRoot() . '/golden/schema-proposal.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        self::assertIsArray($proposal);
        return $proposal;
    }

    /** @return array<string,mixed> */
    private function snapshot(): array
    {
        $snapshot = json_decode(
            (string) file_get_contents($this->sampleRoot() . '/golden/canonical-schema-snapshot.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        self::assertIsArray($snapshot);
        return $snapshot;
    }

    private function sampleRoot(): string
    {
        return dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
    }
}
