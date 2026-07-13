<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_task.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalTaskTest extends TestCase
{
    public function testValidatesAiCandidateAndBuildsDistinctDerivedReviewArtifact(): void
    {
        $candidate = $this->candidate();
        $candidateJson = json_encode($candidate, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        $result = app_schema_proposal_task_validate($this->task(), $candidateJson, $this->sourceBytes(), $this->canonicalBytes());

        self::assertTrue($result['ok'], implode(', ', $result['errors']));
        self::assertSame('review_artifact_ready', $result['stage']);
        self::assertFalse($result['mutation_performed']);
        self::assertSame('app_schema_proposal_task_validate', $result['validation_pipeline']['validator']);
        self::assertSame('formal_candidate', $result['validation_pipeline']['candidate_authority']);
        self::assertFalse($result['validation_pipeline']['advisory']);
        self::assertSame(hash('sha256', $candidateJson), $result['candidate_sha256']);
        self::assertNotSame('', $result['review_artifact_sha256']);
        $review = json_decode($result['review_artifact_json'], true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(4, $review['canonical_diff']);
        self::assertSame('mtool_derived', $review['canonical_diff_derivation']['kind']);
        self::assertSame($result['candidate_sha256'], $review['canonical_diff_derivation']['candidate_sha256']);
        self::assertTrue(app_schema_proposal_verify_declared_diff($review, json_decode($this->canonicalBytes(), true, 512, JSON_THROW_ON_ERROR))['ok']);
    }

    public function testRejectsNonEmptyAiDiffAndInputHashMismatchAtStableStages(): void
    {
        $candidate = $this->candidate();
        $golden = json_decode((string) file_get_contents($this->sampleRoot() . '/golden/schema-proposal.json'), true, 512, JSON_THROW_ON_ERROR);
        $candidate['canonical_diff'] = $golden['canonical_diff'];
        $result = app_schema_proposal_task_validate($this->task(), json_encode($candidate, JSON_THROW_ON_ERROR), $this->sourceBytes(), $this->canonicalBytes());
        self::assertFalse($result['ok']);
        self::assertSame('candidate_validation', $result['stage']);
        self::assertContains('candidate_canonical_diff_must_be_empty', $result['errors']);

        $task = $this->task();
        $task['inputs']['source']['sha256'] = str_repeat('0', 64);
        $result = app_schema_proposal_task_validate($task, '{}', $this->sourceBytes(), $this->canonicalBytes());
        self::assertSame('input_integrity', $result['stage']);
        self::assertContains('task_source_sha256_mismatch', $result['errors']);
    }

    public function testRejectsUnsafeTaskContractBeforeReadingCandidate(): void
    {
        $task = $this->task();
        $task['outputs']['candidate'] = '../candidate.json';
        $task['prohibitions']['network'] = false;
        $result = app_schema_proposal_task_validate($task, '{', $this->sourceBytes(), $this->canonicalBytes());
        self::assertSame('task_validation', $result['stage']);
        self::assertContains('invalid_candidate_output_path', $result['errors']);
        self::assertContains('missing_prohibition:network', $result['errors']);
    }

    /** @return array<string,mixed> */
    private function task(): array
    {
        return [
            'task_version' => 'ai-schema-proposal-task-v0', 'task_id' => 'sample19-test', 'project_key' => 'SAMPLE19',
            'operation' => 'schema_proposal_candidate', 'state' => 'pending_user_confirmation',
            'inputs' => [
                'source' => ['path' => 'input/source.json', 'sha256' => hash('sha256', $this->sourceBytes())],
                'canonical' => ['path' => 'input/canonical.json', 'sha256' => hash('sha256', $this->canonicalBytes())],
                'scan' => ['path' => 'input/scan.json', 'sha256' => str_repeat('c', 64)],
                'output_shape' => ['path' => 'input/shape.json', 'sha256' => str_repeat('d', 64)],
            ],
            'outputs' => ['candidate' => 'output/candidate.json', 'validation' => 'output/validation.json', 'review_artifact' => 'output/review.json'],
            'confirmation' => ['required' => true, 'prompt' => 'Proceed?'],
            'validation' => ['command_argv' => ['php', 'mtool/scripts/validate_schema_proposal_task.php'], 'success_stage' => 'review_artifact_ready'],
            'prohibitions' => array_fill_keys(['database_write', 'config_write', 'sql', 'import', 'apply', 'build', 'publish', 'network'], true),
        ];
    }

    /** @return array<string,mixed> */
    private function candidate(): array
    {
        $candidate = json_decode((string) file_get_contents($this->sampleRoot() . '/golden/schema-proposal.json'), true, 512, JSON_THROW_ON_ERROR);
        $candidate['provenance'] = ['kind' => 'interactive_agent_candidate', 'generator' => 'test-agent', 'ai_authored' => true];
        $candidate['canonical_diff'] = [];
        return $candidate;
    }

    private function sourceBytes(): string { return (string) file_get_contents($this->sampleRoot() . '/proposal/source/article.json'); }
    private function canonicalBytes(): string { return (string) file_get_contents($this->sampleRoot() . '/golden/canonical-schema-snapshot.json'); }
    private function sampleRoot(): string { return dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo'; }
}
