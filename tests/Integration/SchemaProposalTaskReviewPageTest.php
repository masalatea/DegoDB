<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_task_packet.php';
require_once dirname(__DIR__, 2) . '/mtool/app/schema_proposal_task_review_page.php';
require_once dirname(__DIR__, 2) . '/mtool/app/router.php';

use PHPUnit\Framework\TestCase;

final class SchemaProposalTaskReviewPageTest extends TestCase
{
    private string $tasksRoot;
    private string|false $previousEnabled;

    protected function setUp(): void
    {
        $this->previousEnabled = getenv('MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED');
        $this->tasksRoot = sys_get_temp_dir() . '/mtool-task-review-' . bin2hex(random_bytes(6)); mkdir($this->tasksRoot);
    }
    protected function tearDown(): void
    {
        $this->previousEnabled === false ? putenv('MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED') : putenv('MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED=' . $this->previousEnabled);
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->tasksRoot, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname()); rmdir($this->tasksRoot);
    }

    public function testFlagDefaultsOffAndRouteIsAuthenticated(): void
    {
        putenv('MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED'); self::assertFalse(app_schema_proposal_task_review_enabled());
        putenv('MTOOL_SCHEMA_PROPOSAL_TASK_REVIEW_ENABLED=1'); self::assertTrue(app_schema_proposal_task_review_enabled());
        $route = app_route_match(['path' => '/projects/SAMPLE19/schema-proposal-tasks/sample19-schema-proposal-1606f9fe652d/review']);
        self::assertSame('project_schema_proposal_task_review', $route['name']); self::assertTrue(app_route_requires_auth($route['name']));
    }

    public function testValidatedTaskArtifactRendersAiAndMtoolProvenanceWithoutActions(): void
    {
        $taskId = $this->readyTask(); $review = app_schema_proposal_task_review_load($taskId, $this->tasksRoot);
        self::assertTrue($review['ok'], $review['error']); self::assertSame('reviewable', $review['page_state']);
        $html = app_schema_proposal_task_review_html($review);
        foreach (['data-ai-authored="true"', 'data-schema-proposal-task-evidence="true"', 'data-candidate-hash-verified="true"', 'data-review-artifact-hash-verified="true"', 'data-canonical-diff-owner="mtool"'] as $marker) self::assertStringContainsString($marker, $html);
        foreach (['<form', '<button', '<script', 'method="post"', 'data-runtime-execute'] as $forbidden) self::assertStringNotContainsString($forbidden, strtolower($html));
    }

    public function testLoaderRejectsInvalidIdMissingArtifactAndHashTampering(): void
    {
        self::assertSame('invalid_task_id', app_schema_proposal_task_review_load('../bad', $this->tasksRoot)['error']);
        $taskId = $this->readyTask(); $root = $this->tasksRoot . '/' . $taskId;
        unlink($root . '/output/review-artifact.json');
        self::assertSame('task_review_artifact_not_ready', app_schema_proposal_task_review_load($taskId, $this->tasksRoot)['error']);
        $this->writeReadyOutputs($root);
        file_put_contents($root . '/output/candidate.json', "{}\n");
        self::assertSame('task_candidate_hash_mismatch', app_schema_proposal_task_review_load($taskId, $this->tasksRoot)['error']);
    }

    private function readyTask(): string
    {
        $sample = dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
        $packet = app_schema_proposal_sample19_task_packet((string) file_get_contents($sample . '/proposal/source/article.json'), (string) file_get_contents($sample . '/golden/canonical-schema-snapshot.json'), (string) file_get_contents($sample . '/proposal/prompt/schema-proposal-v1-shape.json'));
        $root = $this->tasksRoot . '/' . $packet['task']['task_id']; app_schema_proposal_task_packet_write($packet, $root); $this->writeReadyOutputs($root);
        return $packet['task']['task_id'];
    }

    private function writeReadyOutputs(string $root): void
    {
        $task = json_decode((string) file_get_contents($root . '/task.json'), true, 512, JSON_THROW_ON_ERROR);
        $sample = dirname(__DIR__, 2) . '/sample/tutorials/sample19-json-first-content-model-demo';
        $candidate = json_decode((string) file_get_contents($sample . '/golden/schema-proposal.json'), true, 512, JSON_THROW_ON_ERROR);
        $candidate['provenance'] = ['kind' => 'interactive_agent_candidate', 'generator' => 'test-agent', 'ai_authored' => true]; $candidate['canonical_diff'] = [];
        $candidateJson = json_encode($candidate, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
        file_put_contents($root . '/output/candidate.json', $candidateJson);
        $result = app_schema_proposal_task_validate($task, $candidateJson, (string) file_get_contents($root . '/input/source.json'), (string) file_get_contents($root . '/input/canonical-snapshot.json'));
        self::assertTrue($result['ok'], implode(',', $result['errors']));
        file_put_contents($root . '/output/review-artifact.json', $result['review_artifact_json']);
        file_put_contents($root . '/output/validation.json', json_encode(array_diff_key($result, ['review_artifact_json' => true, 'derived_diff' => true]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    }
}
