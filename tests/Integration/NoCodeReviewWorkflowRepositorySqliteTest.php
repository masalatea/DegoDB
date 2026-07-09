<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_review_workflow_repository.php';

use PHPUnit\Framework\TestCase;

final class NoCodeReviewWorkflowRepositorySqliteTest extends TestCase
{
    public function testReviewWorkflowRequestCanBePersistedAndFetched(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $requestKey = 'review_test_' . bin2hex(random_bytes(4));
        $create = app_no_code_review_workflow_create_or_reuse_request($app, [
            'review_request_key' => $requestKey,
            'project_key' => 'MTOOL',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'artifact_key' => 'artifact-current',
            'operation_key' => 'review_source_output_artifact',
            'adapter_handoff' => 'mtool_source_output_review',
            'requested_by' => 'operator@example.test',
            'source_output_dir' => '/work/projects/MTOOL/source-outputs/NO-CODE-RUNTIME',
            'policy_key' => 'source_output.review',
            'audit_event' => [
                'event_type' => 'mtool.source_output.artifact_review_requested',
                'target_type' => 'source_output_artifact',
            ],
            'metadata' => [
                'route_boundary' => 'review_source_output_artifact',
                'expected_artifact_key' => 'artifact-current',
            ],
        ]);

        self::assertTrue($create['ok'], $create['error']);
        self::assertTrue($create['created']);
        self::assertSame('accepted', $create['result']);
        self::assertSame($requestKey, $create['item']['review_request_key'] ?? '');
        self::assertSame('requested', $create['item']['status'] ?? '');
        self::assertSame('source_output.review', $create['item']['policy_key'] ?? '');
        self::assertSame('artifact-current', $create['item']['metadata']['expected_artifact_key'] ?? '');
        self::assertSame(
            'mtool.source_output.artifact_review_requested',
            $create['item']['audit_event']['event_type'] ?? '',
        );

        $latest = app_no_code_review_workflow_fetch_latest_requests($app, [
            'project_key' => 'MTOOL',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        self::assertSame($requestKey, $latest['items'][0]['review_request_key'] ?? '');
    }

    public function testOpenReviewWorkflowRequestIsReusedForDuplicateArtifactRequest(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $first = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_first_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-current',
        ]));
        self::assertTrue($first['ok'], $first['error']);
        self::assertTrue($first['created']);

        $duplicate = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_duplicate_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-current',
        ]));
        self::assertTrue($duplicate['ok'], $duplicate['error']);
        self::assertFalse($duplicate['created']);
        self::assertSame('duplicate', $duplicate['result']);
        self::assertSame(
            $first['item']['review_request_key'] ?? '',
            $duplicate['item']['review_request_key'] ?? '',
        );

        $nextArtifact = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_next_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-next',
        ]));
        self::assertTrue($nextArtifact['ok'], $nextArtifact['error']);
        self::assertTrue($nextArtifact['created']);
        self::assertSame('accepted', $nextArtifact['result']);
        self::assertNotSame(
            $first['item']['review_request_key'] ?? '',
            $nextArtifact['item']['review_request_key'] ?? '',
        );
    }

    public function testReviewWorkflowRepositoryRejectsInvalidStatusAndMissingRequiredFields(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $invalidStatus = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_invalid_status_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-current',
            'status' => 'ready_for_review',
        ]));
        self::assertFalse($invalidStatus['ok']);
        self::assertSame('failed', $invalidStatus['result']);
        self::assertStringContainsString('review workflow status is not supported', $invalidStatus['error']);

        $missingProject = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_missing_project_' . bin2hex(random_bytes(4)),
            'project_key' => '',
            'artifact_key' => 'artifact-current',
        ]));
        self::assertFalse($missingProject['ok']);
        self::assertSame('failed', $missingProject['result']);
        self::assertStringContainsString('review workflow field is required: project_key', $missingProject['error']);

        $latest = app_no_code_review_workflow_fetch_latest_requests($app, [
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertSame([], $latest['items']);
    }

    public function testReviewWorkflowFetchLatestCanFilterByStatusRequestedByAndLimit(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $aliceFirst = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_alice_requested_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-alice-requested',
            'requested_by' => 'alice@example.test',
            'status' => 'requested',
        ]));
        self::assertTrue($aliceFirst['ok'], $aliceFirst['error']);

        $aliceInReview = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_alice_in_review_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-alice-in-review',
            'requested_by' => 'alice@example.test',
            'status' => 'in_review',
        ]));
        self::assertTrue($aliceInReview['ok'], $aliceInReview['error']);

        $bobRequested = app_no_code_review_workflow_create_or_reuse_request($app, $this->requestInput([
            'review_request_key' => 'review_bob_requested_' . bin2hex(random_bytes(4)),
            'artifact_key' => 'artifact-bob-requested',
            'requested_by' => 'bob@example.test',
            'status' => 'requested',
        ]));
        self::assertTrue($bobRequested['ok'], $bobRequested['error']);

        $aliceRequested = app_no_code_review_workflow_fetch_latest_requests($app, [
            'project_key' => 'MTOOL',
            'requested_by' => 'alice@example.test',
            'status' => 'requested',
            'limit' => 10,
        ]);
        self::assertTrue($aliceRequested['ok'], $aliceRequested['error']);
        self::assertCount(1, $aliceRequested['items']);
        self::assertSame($aliceFirst['item']['review_request_key'] ?? '', $aliceRequested['items'][0]['review_request_key'] ?? '');

        $limited = app_no_code_review_workflow_fetch_latest_requests($app, [
            'project_key' => 'MTOOL',
            'limit' => 2,
        ]);
        self::assertTrue($limited['ok'], $limited['error']);
        self::assertCount(2, $limited['items']);
    }

    /**
     * @param array<string,mixed> $overrides
     * @return array<string,mixed>
     */
    private function requestInput(array $overrides): array
    {
        return array_merge([
            'project_key' => 'MTOOL',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'operation_key' => 'review_source_output_artifact',
            'adapter_handoff' => 'mtool_source_output_review',
            'requested_by' => 'operator@example.test',
            'source_output_dir' => '/work/projects/MTOOL/source-outputs/NO-CODE-RUNTIME',
            'policy_key' => 'source_output.review',
            'audit_event' => [
                'event_type' => 'mtool.source_output.artifact_review_requested',
            ],
            'metadata' => [],
        ], $overrides);
    }

    private function sqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-review-workflow-sqlite-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            $storeDir,
        );

        return [
            'site' => 'admin',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
    }
}
