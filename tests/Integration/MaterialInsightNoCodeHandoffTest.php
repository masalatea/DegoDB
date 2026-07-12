<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/material_insight_no_code_handoff.php';

use PHPUnit\Framework\TestCase;

final class MaterialInsightNoCodeHandoffTest extends TestCase
{
    public function testBuildsReadOnlyNoCodeHandoffFromMaterialInsight(): void
    {
        $artifact = app_material_insight_from_schema_proposal(
            $this->proposal(),
            $this->sourceBytes(),
            $this->canonicalBytes(),
        );

        $result = app_material_insight_no_code_handoff($artifact);

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame('no-code-screen-definition-v0', $result['screen_definition']['definition_version'] ?? '');
        self::assertSame('no-code-runtime-v0', $result['runtime_preview']['runtime_version'] ?? '');
        self::assertSame('SAMPLE19', $result['screen_definition']['project_key'] ?? '');
        self::assertSame('SAMPLE19', $result['runtime_preview']['project_key'] ?? '');

        $contract = $result['screen_definition']['contracts'][0] ?? null;
        self::assertIsArray($contract);
        self::assertSame('sample19_material_insight', $contract['contract_key'] ?? '');
        self::assertSame([], $contract['actions'] ?? ['unexpected']);
        self::assertSame([], $contract['custom_operations'] ?? ['unexpected']);
        self::assertSame('material_insight_v0', $contract['traceability']['source_version'] ?? '');

        $screens = $contract['screens'] ?? [];
        self::assertSame(['material_entity_list', 'material_qa_cards'], array_column($screens, 'screen_key'));
        self::assertSame(['review_list', 'review_list'], array_column($screens, 'view_variant'));
        foreach ($screens as $screen) {
            self::assertSame([], $screen['actions'] ?? ['unexpected']);
            foreach (($screen['fields'] ?? []) as $field) {
                self::assertTrue($field['readonly'] ?? false, (string) ($field['field_key'] ?? 'missing field_key'));
            }
        }

        $runtimeScreens = $result['runtime_preview']['screens'] ?? [];
        self::assertSame(['material_entity_list', 'material_qa_cards'], array_column($runtimeScreens, 'screen_key'));
        self::assertSame([], $runtimeScreens[0]['actions'] ?? ['unexpected']);
        self::assertSame([], $runtimeScreens[1]['actions'] ?? ['unexpected']);
        self::assertSame('entity_review', $runtimeScreens[0]['material_insight_traceability']['section'] ?? '');
        self::assertSame('qa_review', $runtimeScreens[1]['material_insight_traceability']['section'] ?? '');
        self::assertNotSame([], $runtimeScreens[0]['data']['rows'] ?? []);
        self::assertNotSame([], $runtimeScreens[1]['data']['rows'] ?? []);
        self::assertSame(
            'material_insight_v0',
            $result['runtime_preview']['material_insight_traceability']['source_version'] ?? '',
        );
    }

    public function testRejectsInvalidMaterialInsightBeforeHandoff(): void
    {
        $artifact = app_material_insight_from_schema_proposal(
            $this->proposal(),
            $this->sourceBytes(),
            $this->canonicalBytes(),
        );
        $artifact['ui_outline']['actions'][] = ['action_key' => 'apply'];

        $result = app_material_insight_no_code_handoff($artifact);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('ui_outline_actions_must_be_empty', $result['error']);
        self::assertSame([], $result['screen_definition']);
        self::assertSame([], $result['runtime_preview']);
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
