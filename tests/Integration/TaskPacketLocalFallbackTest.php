<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_task_packet.php';
require_once dirname(__DIR__, 2) . '/mtool/app/task_packet_local_fallback.php';

use PHPUnit\Framework\TestCase;

final class TaskPacketLocalFallbackTest extends TestCase
{
    public function testWritesOnlyDeclaredAdvisoryFallbackArtifacts(): void
    {
        $root = sys_get_temp_dir() . '/mtool-local-fallback-' . bin2hex(random_bytes(6));
        try {
            app_schema_proposal_task_packet_write($this->packet(), $root);
            $candidate = $this->aiAuthoredCandidateJson();
            $result = app_task_packet_local_fallback_run(
                $root . '/task.json',
                static fn (array $task, array $inputs): array => ['candidate_json' => $candidate, 'provider' => 'fake-local', 'model' => 'fixture'],
            );

            self::assertTrue($result['ok'], implode(',', $result['errors']));
            self::assertSame('review_artifact_ready', $result['stage']);
            self::assertSame('fake-local', $result['provider']);
            self::assertTrue($result['advisory']);
            self::assertSame('app_schema_proposal_task_validate', $result['validation_pipeline']['validator']);
            self::assertSame('advisory_fallback_candidate', $result['validation_pipeline']['candidate_authority']);
            self::assertTrue($result['validation_pipeline']['advisory']);
            self::assertFalse($result['mutation_performed']);
            self::assertSame($root . '/input/fallback-candidate.json', $result['candidate_path']);
            self::assertSame($root . '/input/fallback-validation.json', $result['validation_path']);
            self::assertFileExists($root . '/input/fallback-candidate.json');
            self::assertFileExists($root . '/input/fallback-validation.json');
            self::assertFileDoesNotExist($root . '/output/candidate.json');
            self::assertFileDoesNotExist($root . '/output/review-artifact.json');
        } finally {
            $this->removeTree($root);
        }
    }

    public function testFailsClosedWhenTaskInputHashChanges(): void
    {
        $root = sys_get_temp_dir() . '/mtool-local-fallback-' . bin2hex(random_bytes(6));
        try {
            app_schema_proposal_task_packet_write($this->packet(), $root);
            file_put_contents($root . '/input/source.json', "{}\n");
            $result = app_task_packet_local_fallback_run(
                $root . '/task.json',
                fn (): array => ['candidate_json' => $this->aiAuthoredCandidateJson(), 'provider' => 'fake-local', 'model' => 'fixture'],
            );

            self::assertFalse($result['ok']);
            self::assertSame('input_integrity', $result['stage']);
            self::assertSame(['task_input_hash_mismatch:source'], $result['errors']);
            self::assertFileDoesNotExist($root . '/input/fallback-candidate.json');
        } finally {
            $this->removeTree($root);
        }
    }

    public function testGenericCliRequiresExplicitExecutionFlag(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 2) . '/mtool/scripts/run_task_local_fallback.php');
        self::assertStringContainsString("['task:', 'candidate-json:', 'execute-local-fallback']", $script);
        self::assertStringContainsString('This optional local fallback never auto-runs.', $script);
        self::assertStringContainsString('app_task_packet_local_fallback_run(', $script);
    }

    private function packet(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
        return app_schema_proposal_sample19_task_packet((string) file_get_contents($root . '/proposal/source/article.json'), (string) file_get_contents($root . '/golden/canonical-schema-snapshot.json'), (string) file_get_contents($root . '/proposal/prompt/schema-proposal-v1-shape.json'));
    }

    private function aiAuthoredCandidateJson(): string
    {
        $path = dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo/golden/schema-proposal.json';
        $candidate = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $candidate['provenance']['kind'] = 'local_fallback_fixture';
        $candidate['provenance']['ai_authored'] = true;
        $candidate['canonical_diff'] = [];
        return json_encode($candidate, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    private function removeTree(string $root): void
    {
        if (!is_dir($root)) return;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($root);
    }
}
