<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/material_insight.php';

use PHPUnit\Framework\TestCase;

final class MaterialInsightTest extends TestCase
{
    public function testBuildsSample19MaterialInsightFromExistingProposalWithoutMutation(): void
    {
        $artifact = app_material_insight_from_schema_proposal(
            $this->proposal(),
            $this->sourceBytes(),
            $this->canonicalBytes(),
        );
        $validation = app_material_insight_validate($artifact, $this->sourceBytes(), $this->canonicalBytes());

        self::assertTrue($validation['ok'], implode(', ', $validation['errors']));
        self::assertSame('material_insight_v0', $artifact['version']);
        self::assertSame('SAMPLE19', $artifact['project_key']);
        self::assertSame(hash('sha256', $this->sourceBytes()), $artifact['source']['sha256']);
        self::assertSame(hash('sha256', $this->canonicalBytes()), $artifact['basis']['canonical_snapshot_sha256']);
        self::assertSame('sample19_schema_proposal_review', $artifact['basis']['kind']);
        self::assertSame(
            ['json_author', 'json_category', 'article_json_model', 'article_public_summary'],
            array_column($artifact['entities'], 'entity_key'),
        );
        self::assertSame(
            ['entities_implied', 'relationships_supported', 'ui_outline_candidate'],
            array_column($artifact['qa_cards'], 'question_key'),
        );
        self::assertSame(
            ['structure', 'relationship', 'ui_outline'],
            array_column($artifact['qa_cards'], 'answer_category'),
        );
        self::assertSame('read_only_review', $artifact['ui_outline']['mode']);
        self::assertSame(
            ['entity_review', 'qa_review'],
            array_column($artifact['ui_outline']['screens'], 'section'),
        );
        self::assertSame([], $artifact['ui_outline']['actions']);
        self::assertFalse($artifact['validation']['mutation_performed']);
        self::assertContains('apply', $artifact['prohibited_actions']);
        self::assertContains('route_execution', $artifact['prohibited_actions']);
    }

    public function testRejectsBrokenReferencesAndUnsafeUiActions(): void
    {
        $artifact = app_material_insight_from_schema_proposal(
            $this->proposal(),
            $this->sourceBytes(),
            $this->canonicalBytes(),
        );
        $artifact['qa_cards'][0]['entity_refs'][] = 'missing_entity';
        $artifact['qa_cards'][1]['answer_category'] = 'chat';
        $artifact['qa_cards'][2]['evidence_refs'] = [];
        $artifact['ui_outline']['screens'][1]['qa_refs'][] = 'missing_question';
        $artifact['ui_outline']['screens'][0]['section'] = '';
        $artifact['ui_outline']['actions'][] = ['action_key' => 'apply'];
        $artifact['prohibited_actions'] = ['apply'];
        $artifact['validation']['mutation_performed'] = true;

        $errors = app_material_insight_validate($artifact, $this->sourceBytes(), $this->canonicalBytes())['errors'];

        self::assertContains('unknown_qa_entity_ref:entities_implied:missing_entity', $errors);
        self::assertContains('invalid_qa_answer_category:relationships_supported', $errors);
        self::assertContains('missing_qa_evidence_refs:ui_outline_candidate', $errors);
        self::assertContains('unknown_ui_qa_ref:material_qa_cards:missing_question', $errors);
        self::assertContains('invalid_ui_section:material_entity_list', $errors);
        self::assertContains('ui_outline_actions_must_be_empty', $errors);
        self::assertContains('missing_prohibited_action:import', $errors);
        self::assertContains('mutation_performed_must_be_false', $errors);
    }

    public function testRejectsSourceAndCanonicalHashMismatches(): void
    {
        $artifact = app_material_insight_from_schema_proposal(
            $this->proposal(),
            $this->sourceBytes(),
            $this->canonicalBytes(),
        );
        $artifact['source']['sha256'] = str_repeat('0', 64);
        $artifact['basis']['canonical_snapshot_sha256'] = str_repeat('1', 64);

        $errors = app_material_insight_validate($artifact, $this->sourceBytes(), $this->canonicalBytes())['errors'];

        self::assertContains('source_sha256_mismatch', $errors);
        self::assertContains('canonical_snapshot_sha256_mismatch', $errors);
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

    private function sourceBytes(): string
    {
        return (string) file_get_contents($this->sampleRoot() . '/proposal/source/article.json');
    }

    private function canonicalBytes(): string
    {
        return (string) file_get_contents($this->sampleRoot() . '/golden/canonical-schema-snapshot.json');
    }

    private function sampleRoot(): string
    {
        return dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
    }
}
