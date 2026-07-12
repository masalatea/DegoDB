<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_request.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalRequestTest extends TestCase
{
    public function testBuildsDeterministicOfflineEnvelopeForFixedSample19Inputs(): void
    {
        $first = $this->build();
        $second = $this->build();

        self::assertTrue($first['ok'], implode(', ', $first['errors']));
        self::assertSame($first, $second);
        $envelope = $first['envelope'];
        self::assertSame('schema-proposal-request-v0', $envelope['envelope_version']);
        self::assertSame('SAMPLE19', $envelope['project_key']);
        self::assertSame(hash('sha256', $envelope['prompt']['content']), $envelope['prompt']['final_sha256']);
        self::assertStringContainsString($envelope['source']['sha256'], $envelope['prompt']['content']);
        self::assertStringContainsString($envelope['canonical_context']['sha256'], $envelope['prompt']['content']);
        self::assertSame(hash('sha256', $this->shapeBytes()), $envelope['prompt']['output_shape_sha256']);
        self::assertStringContainsString('"fields"', $envelope['prompt']['content']);
        self::assertStringNotContainsString('json_author', $this->shapeBytes());
        self::assertStringNotContainsString('article_json_model', $this->shapeBytes());
        self::assertSame([
            'network_allowed' => false,
            'credential_access_allowed' => false,
            'persistence_allowed' => false,
            'mutation_allowed' => false,
            'apply_supported' => false,
        ], $envelope['execution']);
        self::assertArrayNotHasKey('provider', $envelope);
        self::assertArrayNotHasKey('model', $envelope);
        self::assertArrayNotHasKey('credential', $envelope);
    }

    public function testFailsClosedForMissingTemplatePlaceholders(): void
    {
        $result = app_schema_proposal_request_build('no placeholders', '{}', '{"article":{}}', $this->canonicalBytes());

        self::assertFalse($result['ok']);
        foreach (['{{OUTPUT_SHAPE_JSON}}', '{{SOURCE_SHA256}}', '{{SOURCE_JSON}}', '{{CANONICAL_SHA256}}', '{{CANONICAL_JSON}}'] as $placeholder) {
            self::assertContains('missing_prompt_placeholder:' . $placeholder, $result['errors']);
        }
        self::assertSame([], $result['envelope']);
    }

    public function testFailsClosedForInvalidOrUnexpectedInputs(): void
    {
        $template = $this->templateBytes();
        self::assertContains('invalid_source_json', app_schema_proposal_request_build($template, $this->shapeBytes(), '{', $this->canonicalBytes())['errors']);
        self::assertContains('source_root_article_required', app_schema_proposal_request_build($template, $this->shapeBytes(), '{"other":{}}', $this->canonicalBytes())['errors']);

        $canonical = json_decode($this->canonicalBytes(), true, 512, JSON_THROW_ON_ERROR);
        $canonical['project_key'] = 'OTHER';
        self::assertContains(
            'unexpected_canonical_snapshot',
            app_schema_proposal_request_build($template, $this->shapeBytes(), $this->sourceBytes(), json_encode($canonical, JSON_THROW_ON_ERROR))['errors'],
        );
    }

    public function testRejectsIncompleteOutputShapeContract(): void
    {
        $shape = json_decode($this->shapeBytes(), true, 512, JSON_THROW_ON_ERROR);
        unset($shape['entities'][0]['evidence']);

        $errors = app_schema_proposal_request_build(
            $this->templateBytes(),
            json_encode($shape, JSON_THROW_ON_ERROR),
            $this->sourceBytes(),
            $this->canonicalBytes(),
        )['errors'];

        self::assertContains('incomplete_output_shape:entities.0.evidence.0.pointer', $errors);
    }

    /** @return array{ok:bool,envelope:array<string,mixed>,errors:list<string>} */
    private function build(): array
    {
        return app_schema_proposal_request_build($this->templateBytes(), $this->shapeBytes(), $this->sourceBytes(), $this->canonicalBytes());
    }

    private function templateBytes(): string
    {
        return (string) file_get_contents($this->sampleRoot() . '/proposal/prompt/schema-proposal-v0.txt');
    }

    private function sourceBytes(): string
    {
        return (string) file_get_contents($this->sampleRoot() . '/proposal/source/article.json');
    }

    private function shapeBytes(): string
    {
        return (string) file_get_contents($this->sampleRoot() . '/proposal/prompt/schema-proposal-v1-shape.json');
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
