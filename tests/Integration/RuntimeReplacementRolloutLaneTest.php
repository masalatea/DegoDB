<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/project_output_runtime_generator.php';

use PHPUnit\Framework\TestCase;

final class RuntimeReplacementRolloutLaneTest extends TestCase
{
    public function testKnownRepresentativeSourcesMapToExpectedRolloutLanes(): void
    {
        self::assertSame(
            [
                'gate_type' => 'direct-replacement',
                'lane' => 'plain-dto',
                'gate_reference' => '',
                'note' => 'plain DTO lane は manifest/self-loop が green なら direct replacement で進める。',
            ],
            app_project_output_runtime_data_rollout_gate('BuildToken', true),
        );

        self::assertSame(
            'default-property',
            app_project_output_runtime_data_rollout_gate('TestPattern', false)['lane'],
        );
        self::assertSame(
            'tests/Integration/Sample9TestPatternDefaultPropertyOutputTest.php',
            app_project_output_runtime_data_rollout_gate('TestPattern', false)['gate_reference'],
        );

        self::assertSame(
            'companion-declarations',
            app_project_output_runtime_data_rollout_gate('CompareOutput', false)['lane'],
        );
        self::assertSame(
            'method-only',
            app_project_output_runtime_data_rollout_gate('da', false)['lane'],
        );
        self::assertSame(
            'wrapper-property-method',
            app_project_output_runtime_data_rollout_gate('dbtablecolumns', false)['lane'],
        );
        self::assertSame(
            'method-and-enum',
            app_project_output_runtime_data_rollout_gate('Req', false)['lane'],
        );
        self::assertSame(
            'top-level-declaration',
            app_project_output_runtime_data_rollout_gate('ProjectUser', false)['lane'],
        );
    }

    public function testHistoricalReasonCodeAliasNormalizesToCurrentWrapperBaseLabel(): void
    {
        self::assertSame(
            'generated-existing-runtime-wrapper-base',
            app_project_output_runtime_normalize_generation_reason_code(
                'generated-layered-runtime-wrapper-base',
            ),
        );
        self::assertSame(
            'generated-canonical-plain-dto',
            app_project_output_runtime_normalize_generation_reason_code('generated-canonical-plain-dto'),
        );
    }

    public function testCurrentManifestNonPlainItemsAreAllClassified(): void
    {
        $manifestPath = dirname(__DIR__, 2) . '/mtool/reference/dbclasses/_support/runtime-generation-manifest.json';
        $contents = file_get_contents($manifestPath);
        self::assertIsString($contents);

        $manifest = json_decode($contents, true);
        self::assertIsArray($manifest);
        self::assertMatchesRegularExpression(
            '/^[0-9]{8}-[0-9]{6}-[a-f0-9]{8}$/',
            (string) ($manifest['artifact_key'] ?? ''),
            'runtime reference manifest should retain promoted artifact provenance',
        );

        $generationSummary = $manifest['generation_summary'] ?? null;
        self::assertIsArray($generationSummary);

        $items = $generationSummary['data_generation_items'] ?? null;
        self::assertIsArray($items);

        foreach ($items as $item) {
            self::assertIsArray($item);

            $sourceName = (string) ($item['source_name'] ?? '');
            $isPlainCandidate = (bool) ($item['is_plain_candidate'] ?? false);
            $rolloutGate = app_project_output_runtime_data_rollout_gate($sourceName, $isPlainCandidate);

            self::assertNotSame('', $rolloutGate['lane'], 'rollout lane missing: ' . $sourceName);
            self::assertNotSame('', $rolloutGate['note'], 'rollout note missing: ' . $sourceName);
            self::assertSame(
                $rolloutGate['gate_type'],
                (string) ($item['rollout_gate_type'] ?? ''),
                'stored rollout gate mismatch: ' . $sourceName,
            );
            self::assertSame(
                $rolloutGate['lane'],
                (string) ($item['rollout_lane'] ?? ''),
                'stored rollout lane mismatch: ' . $sourceName,
            );
            self::assertSame(
                $rolloutGate['gate_reference'],
                (string) ($item['rollout_gate_reference'] ?? ''),
                'stored rollout gate reference mismatch: ' . $sourceName,
            );
            self::assertSame(
                $rolloutGate['note'],
                (string) ($item['rollout_note'] ?? ''),
                'stored rollout note mismatch: ' . $sourceName,
            );

            if ($isPlainCandidate) {
                self::assertSame(
                    'direct-replacement',
                    $rolloutGate['gate_type'],
                    'plain item must stay in direct replacement lane: ' . $sourceName,
                );
                continue;
            }

            self::assertNotSame(
                'manual-classification',
                $rolloutGate['gate_type'],
                'non-plain item must be assigned to a sample gate lane: ' . $sourceName,
            );
            self::assertNotSame(
                '',
                $rolloutGate['gate_reference'],
                'non-plain item must point at a sample gate test: ' . $sourceName,
            );
        }
    }
}
