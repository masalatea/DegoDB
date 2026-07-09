<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_review_workflow_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_source_output_operation_page.php';

use PHPUnit\Framework\TestCase;

final class ProjectSourceOutputOperationPersistenceTest extends TestCase
{
    public function testDeferredGuardResultDoesNotPersistReviewRequest(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $result = app_project_source_output_operation_apply_review_request_persistence(
            $app,
            [
                'allowed' => false,
                'result' => 'blocked',
                'status_code' => 409,
                'failure_code' => 'deferred_availability',
                'audit_event' => $this->auditEvent('accepted_plan'),
            ],
            $this->context(),
        );

        self::assertSame('skipped', app_project_source_output_operation_review_request_persistence_status(
            $result['review_request_persistence'] ?? [],
        ));
        self::assertSame('accepted_plan', $result['audit_event']['result'] ?? '');

        $latest = app_no_code_review_workflow_fetch_latest_requests($app, [
            'project_key' => 'MTOOL',
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertSame([], $latest['items']);
    }

    public function testAcceptedPlanPersistsAndReusesReviewRequest(): void
    {
        $app = $this->sqliteApp();
        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        $first = app_project_source_output_operation_apply_review_request_persistence(
            $app,
            $this->acceptedPlanResult(),
            $this->context(),
        );

        self::assertSame('recorded', app_project_source_output_operation_review_request_persistence_status(
            $first['review_request_persistence'] ?? [],
        ));
        self::assertSame('accepted', $first['audit_event']['result'] ?? '');
        self::assertSame('', $first['audit_event']['metadata']['failure_code'] ?? 'not-empty');
        $reviewRequestKey = (string) ($first['audit_event']['metadata']['review_request_key'] ?? '');
        self::assertNotSame('', $reviewRequestKey);

        $duplicate = app_project_source_output_operation_apply_review_request_persistence(
            $app,
            $this->acceptedPlanResult(),
            $this->context(),
        );

        self::assertSame('duplicate', app_project_source_output_operation_review_request_persistence_status(
            $duplicate['review_request_persistence'] ?? [],
        ));
        self::assertSame('duplicate', $duplicate['audit_event']['result'] ?? '');
        self::assertSame(
            $reviewRequestKey,
            $duplicate['audit_event']['metadata']['review_request_key'] ?? '',
        );

        $latest = app_no_code_review_workflow_fetch_latest_requests($app, [
            'project_key' => 'MTOOL',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'limit' => 10,
        ]);
        self::assertTrue($latest['ok'], $latest['error']);
        self::assertCount(1, $latest['items']);
        self::assertSame($reviewRequestKey, $latest['items'][0]['review_request_key'] ?? '');
    }

    /**
     * @return array<string,mixed>
     */
    private function acceptedPlanResult(): array
    {
        return [
            'ok' => true,
            'allowed' => true,
            'result' => 'accepted_plan',
            'status_code' => 202,
            'failure_code' => '',
            'operation' => [
                'operation_key' => 'review_source_output_artifact',
            ],
            'plan' => [
                'execution_mode' => 'plan-only',
                'operation_key' => 'review_source_output_artifact',
                'adapter_handoff' => 'source_output_artifact_review',
                'project_key' => 'MTOOL',
                'source_output_key' => 'NO-CODE-RUNTIME',
                'artifact_key' => 'artifact-current',
            ],
            'audit_event' => $this->auditEvent('accepted_plan'),
            'error' => '',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function auditEvent(string $result): array
    {
        return [
            'actor_login_id' => 'operator@example.test',
            'actor_source' => 'phpunit',
            'project_key' => 'MTOOL',
            'event_type' => 'mtool.source_output.artifact_review_requested',
            'target_type' => 'artifact',
            'target_key' => 'NO-CODE-RUNTIME',
            'result' => $result,
            'message' => '',
            'metadata' => [
                'operation_key' => 'review_source_output_artifact',
                'source_output_key' => 'NO-CODE-RUNTIME',
                'artifact_key' => 'artifact-current',
                'adapter_handoff' => 'source_output_artifact_review',
                'policy_key' => 'source_output.review',
                'failure_code' => '',
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function context(): array
    {
        return [
            'source_output' => [
                'source_output_key' => 'NO-CODE-RUNTIME',
                'source_output_dir' => '/work/projects/MTOOL/source-outputs/NO-CODE-RUNTIME',
            ],
            'principal' => [
                'id' => 'operator@example.test',
                'roles' => ['admin'],
                'auth_source' => 'phpunit',
            ],
        ];
    }

    private function sqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-source-output-operation-persistence-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
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
            'site_name' => 'DegoDB Test',
            'db' => $configDb,
            'config_db' => $configDb,
        ];
    }
}
