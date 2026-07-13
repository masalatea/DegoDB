<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_task_packet.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalTaskPacketTest extends TestCase
{
    public function testBuildsAgentReadableHashBoundPendingPacket(): void
    {
        $packet = $this->packet(); $task = $packet['task'];
        self::assertSame('pending_user_confirmation', $task['state']);
        self::assertTrue($task['confirmation']['required']);
        self::assertSame('source_of_truth', $task['inputs']['source']['authority']);
        self::assertSame('advisory', $task['inputs']['scan']['authority']);
        $scan = json_decode($packet['files']['input/scan.json'], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(APP_TASK_PACKET_SCAN_VERSION, $scan['scan_version']);
        self::assertSame('/article', $scan['items'][0]['pointer']);
        self::assertSame([], $scan['inference']);
        self::assertFalse($scan['mutation_performed']);
        self::assertSame(['output/candidate.json'], $task['allowed_writes']);
        self::assertSame('app_schema_proposal_task_validate', $task['validation_pipeline']['validator']);
        self::assertFalse($task['validation_pipeline']['formal_candidate']['advisory']);
        self::assertSame('output/candidate.json', $task['validation_pipeline']['formal_candidate']['candidate_path']);
        self::assertTrue($task['validation_pipeline']['fallback_candidate']['advisory']);
        self::assertSame('input/fallback-candidate.json', $task['validation_pipeline']['fallback_candidate']['candidate_path']);
        self::assertSame('review_and_copy_or_adapt_to_output_candidate_then_run_declared_validator', $task['validation_pipeline']['fallback_candidate']['promotion_rule']);
        self::assertTrue($task['prohibitions']['network']);
        self::assertStringContainsString('Do not continue until the user answers affirmatively', $packet['task_markdown']);
        self::assertStringContainsString('canonical_diff=[]', $packet['task_markdown']);
        self::assertStringContainsString('validate_schema_proposal_task.php', $packet['task_markdown']);
        self::assertSame([], app_schema_proposal_task_contract_errors($task));
    }

    public function testWritesNewTaskRootWithoutCandidateOrExecutionArtifacts(): void
    {
        $root = sys_get_temp_dir() . '/mtool-ai-task-' . bin2hex(random_bytes(6));
        try {
            app_schema_proposal_task_packet_write($this->packet(), $root);
            self::assertFileExists($root . '/task.json'); self::assertFileExists($root . '/TASK.md');
            self::assertFileExists($root . '/input/source.json'); self::assertFileExists($root . '/input/canonical-snapshot.json');
            self::assertFileExists($root . '/input/scan.json');
            self::assertFileDoesNotExist($root . '/output/candidate.json');
            self::assertFileDoesNotExist($root . '/output/validation.json');
            self::assertFileDoesNotExist($root . '/output/review-artifact.json');
            $this->expectException(RuntimeException::class); $this->expectExceptionMessage('task_root_already_exists');
            app_schema_proposal_task_packet_write($this->packet(), $root);
        } finally {
            if (is_dir($root)) { foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()); rmdir($root); }
        }
    }

    public function testLocalFallbackIsExplicitAndUsesCommonTaskValidator(): void
    {
        $script = (string) file_get_contents(dirname(__DIR__, 2) . '/mtool/scripts/run_sample19_local_ai_proposal.php');
        self::assertStringContainsString("'ollama-endpoint:'", $script);
        self::assertStringContainsString("'ollama-model:'", $script);
        self::assertStringContainsString('This optional local fallback never auto-runs.', $script);
        self::assertStringContainsString('app_task_packet_local_fallback_run(', $script);
        self::assertStringContainsString('app_task_packet_ollama_generate_candidate(', $script);
        self::assertStringNotContainsString('app_schema_proposal_response_accept(', $script);
    }

    private function packet(): array
    {
        $root = dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
        return app_schema_proposal_sample19_task_packet((string) file_get_contents($root . '/proposal/source/article.json'), (string) file_get_contents($root . '/golden/canonical-schema-snapshot.json'), (string) file_get_contents($root . '/proposal/prompt/schema-proposal-v1-shape.json'));
    }
}
