<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_response.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalResponseTest extends TestCase
{
    public function testAcceptsInjectedAiResponseForReadOnlyReviewWithImmutableAttemptEvidence(): void
    {
        $response = $this->responseBytes();
        $result = app_schema_proposal_response_accept($response, $this->runMetadata(), $this->sourceBytes(), $this->canonicalBytes());

        self::assertTrue($result['ok'], implode(', ', $result['errors']));
        self::assertSame('accepted_for_read_only_review', $result['status']);
        self::assertSame(hash('sha256', $response), $result['attempt']['response_sha256']);
        self::assertSame(strlen($response), $result['attempt']['response_byte_length']);
        self::assertSame('accepted', $result['attempt']['acceptance_status']);
        self::assertFalse($result['attempt']['network_performed_here']);
        self::assertFalse($result['attempt']['credential_accessed_here']);
        self::assertFalse($result['attempt']['persisted']);
        self::assertFalse($result['attempt']['mutation_performed']);
        self::assertCount(4, $result['derived_diff']);
        self::assertSame(['unchanged'], array_values(array_unique(array_column($result['derived_diff'], 'category'))));
    }

    public function testRejectsUnsafeOrFalselyAttributedResponseWithoutRepair(): void
    {
        $proposal = $this->proposal();
        $proposal['state'] = 'approved';
        $proposal['apply_supported'] = true;
        $proposal['provenance'] = ['kind' => 'deterministic_fixture', 'ai_authored' => false];
        $response = json_encode($proposal, JSON_THROW_ON_ERROR);

        $result = app_schema_proposal_response_accept($response, $this->runMetadata(), $this->sourceBytes(), $this->canonicalBytes());

        self::assertFalse($result['ok']);
        self::assertContains('state_must_be_proposal_only', $result['errors']);
        self::assertContains('apply_supported_must_be_false', $result['errors']);
        self::assertContains('ai_provenance_required', $result['errors']);
        self::assertSame([], $result['proposal']);
        self::assertSame(hash('sha256', $response), $result['attempt']['response_sha256']);
    }

    public function testRejectsSourceDiffAndRunMetadataMismatches(): void
    {
        $proposal = $this->proposal();
        $proposal['source']['sha256'] = str_repeat('0', 64);
        $proposal['canonical_diff'][0]['category'] = 'change';
        $run = $this->runMetadata();
        $run['attempt_number'] = 3;
        $run['prompt_final_sha256'] = 'bad';

        $result = app_schema_proposal_response_accept(
            json_encode($proposal, JSON_THROW_ON_ERROR),
            $run,
            $this->sourceBytes(),
            $this->canonicalBytes(),
        );

        self::assertFalse($result['ok']);
        self::assertContains('source_sha256_mismatch', $result['errors']);
        self::assertContains('declared_canonical_diff_mismatch', $result['errors']);
        self::assertContains('invalid_run_attempt_number', $result['errors']);
        self::assertContains('invalid_run_prompt_final_sha256', $result['errors']);
    }

    /** @return array<string,mixed> */
    private function runMetadata(): array
    {
        return [
            'provider' => 'approved-provider-placeholder',
            'model' => 'approved-model-placeholder',
            'prompt_template_sha256' => str_repeat('a', 64),
            'prompt_final_sha256' => str_repeat('b', 64),
            'attempt_number' => 1,
            'generated_at' => '2026-07-12T00:00:00Z',
            'provider_request_id' => 'injected-test-response',
        ];
    }

    private function responseBytes(): string
    {
        return json_encode($this->proposal(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    /** @return array<string,mixed> */
    private function proposal(): array
    {
        $proposal = json_decode((string) file_get_contents($this->sampleRoot() . '/golden/schema-proposal.json'), true, 512, JSON_THROW_ON_ERROR);
        $proposal['provenance'] = [
            'kind' => 'ai_generated_proposal',
            'generator' => 'request-envelope-caller',
            'ai_authored' => true,
        ];
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
