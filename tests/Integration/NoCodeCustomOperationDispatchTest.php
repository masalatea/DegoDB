<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_custom_operation_dispatch.php';
require_once dirname(__DIR__, 2) . '/mtool/app/no_code_mtool_dogfooding_probe.php';

use PHPUnit\Framework\TestCase;

final class NoCodeCustomOperationDispatchTest extends TestCase
{
    public function testReviewArtifactDispatchBlocksDeferredOperationBeforeExecution(): void
    {
        $result = app_no_code_custom_operation_dispatch_preflight([], $this->request([
            'csrf_valid' => true,
            'artifact_key' => 'artifact-current',
            'current_artifact_key' => 'artifact-current',
        ]));

        self::assertFalse($result['allowed']);
        self::assertSame('blocked', $result['result']);
        self::assertSame(409, $result['status_code']);
        self::assertSame('deferred_availability', $result['failure_code']);
        self::assertNull($result['plan']);
        self::assertSame('review_source_output_artifact', $result['audit_event']['metadata']['operation_key'] ?? '');
        self::assertSame('deferred_availability', $result['audit_event']['metadata']['failure_code'] ?? '');
    }

    public function testReviewArtifactDispatchCanPreparePlanOnlyWhenOperationIsAvailable(): void
    {
        $operations = $this->customOperations();
        $operations[0]['availability'] = 'available';

        $result = app_no_code_custom_operation_dispatch_preflight([], $this->request([
            'custom_operations' => $operations,
            'csrf_valid' => true,
            'artifact_key' => 'artifact-current',
            'current_artifact_key' => 'artifact-current',
        ]));

        self::assertTrue($result['allowed']);
        self::assertSame('accepted_plan', $result['result']);
        self::assertSame(202, $result['status_code']);
        self::assertSame('plan-only', $result['plan']['execution_mode'] ?? '');
        self::assertSame('source_output_artifact_review', $result['plan']['adapter_handoff'] ?? '');
        self::assertSame('mtool.source_output.artifact_review_requested', $result['audit_event']['event_type'] ?? '');
        self::assertSame('accepted_plan', $result['audit_event']['result'] ?? '');
    }

    public function testReviewArtifactDispatchRejectsUnknownOperation(): void
    {
        $result = app_no_code_custom_operation_dispatch_preflight([], $this->request([
            'operation_key' => 'missing_operation',
            'csrf_valid' => true,
        ]));

        self::assertFalse($result['allowed']);
        self::assertSame('invalid', $result['result']);
        self::assertSame(404, $result['status_code']);
        self::assertSame('unknown_operation', $result['failure_code']);
    }

    public function testReviewArtifactDispatchRejectsMissingCsrf(): void
    {
        $result = app_no_code_custom_operation_dispatch_preflight([], $this->request([
            'csrf_valid' => false,
        ]));

        self::assertFalse($result['allowed']);
        self::assertSame('blocked', $result['result']);
        self::assertSame(400, $result['status_code']);
        self::assertSame('missing_csrf', $result['failure_code']);
    }

    public function testReviewArtifactDispatchRejectsUnauthenticatedPrincipal(): void
    {
        $result = app_no_code_custom_operation_dispatch_preflight([], $this->request([
            'principal' => null,
            'csrf_valid' => true,
        ]));

        self::assertFalse($result['allowed']);
        self::assertSame('unauthorized', $result['result']);
        self::assertSame(401, $result['status_code']);
        self::assertSame('unauthenticated', $result['failure_code']);
    }

    public function testReviewArtifactDispatchRejectsStaleArtifact(): void
    {
        $operations = $this->customOperations();
        $operations[0]['availability'] = 'available';

        $result = app_no_code_custom_operation_dispatch_preflight([], $this->request([
            'custom_operations' => $operations,
            'csrf_valid' => true,
            'artifact_key' => 'artifact-old',
            'current_artifact_key' => 'artifact-current',
        ]));

        self::assertFalse($result['allowed']);
        self::assertSame('stale', $result['result']);
        self::assertSame(409, $result['status_code']);
        self::assertSame('stale_artifact', $result['failure_code']);
    }

    /**
     * @param array<string,mixed> $overrides
     * @return array<string,mixed>
     */
    private function request(array $overrides = []): array
    {
        return array_merge([
            'project_key' => 'MTOOL',
            'source_output_key' => 'NO-CODE-RUNTIME',
            'operation_key' => 'review_source_output_artifact',
            'custom_operations' => $this->customOperations(),
            'principal' => $this->adminPrincipal(),
            'csrf_valid' => false,
            'source_output' => [
                'source_output_key' => 'NO-CODE-RUNTIME',
            ],
            'artifact_key' => '',
            'current_artifact_key' => '',
        ], $overrides);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function customOperations(): array
    {
        $definition = app_no_code_mtool_dogfooding_probe_screen_definition($this->adminPrincipal());
        self::assertTrue($definition['ok'], $definition['error']);

        $contract = $definition['definition']['contracts'][0] ?? [];
        self::assertIsArray($contract);

        $operations = $contract['custom_operations'] ?? [];
        self::assertIsArray($operations);

        return $operations;
    }

    /**
     * @return array<string,mixed>
     */
    private function adminPrincipal(): array
    {
        return [
            'id' => 'admin@example.test',
            'display_name' => 'Admin',
            'roles' => ['admin'],
            'scopes' => [],
            'project_roles' => [],
            'auth_source' => 'phpunit',
            'site' => 'admin',
        ];
    }
}
