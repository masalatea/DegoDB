<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_operator_sync_inspection.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_sync_outbox_detail_page.php';

use PHPUnit\Framework\TestCase;

final class NoCodeOperatorSyncInspectionTest extends TestCase
{
    public function testSummarizesFailedSyncOutboxItemsForOperatorInspection(): void
    {
        $summary = app_no_code_operator_sync_inspection_from_outbox_catalog([
            [
                'id' => '1',
                'status' => 'done',
                'operation_key' => 'update_sync_task',
                'attempts' => 1,
                'updated_at' => '2026-06-30 10:00:00',
            ],
            [
                'id' => '2',
                'status' => 'failed',
                'operation_key' => 'update_sync_task',
                'operation_type' => 'update',
                'contract_key' => 'sync_task',
                'origin' => 'app-local',
                'target' => 'server',
                'storage_mode' => 'local-copy',
                'attempts' => 1,
                'last_error' => 'sample30 deterministic sync failure for visibility.',
                'created_at' => '2026-06-30 10:01:00',
                'updated_at' => '2026-06-30 10:01:00',
            ],
            [
                'id' => '3',
                'status' => 'failed',
                'operation_key' => 'update_other_task',
                'operation_type' => 'update',
                'contract_key' => 'sync_task',
                'origin' => 'app-local',
                'target' => 'server',
                'attempts' => 2,
                'last_error' => 'newer failure',
                'created_at' => '2026-06-30 10:02:00',
                'updated_at' => '2026-06-30 10:02:00',
            ],
            [
                'id' => '4',
                'status' => 'pending',
                'operation_key' => 'update_sync_task',
                'attempts' => 0,
                'updated_at' => '2026-06-30 10:03:00',
            ],
        ]);

        self::assertTrue($summary['ok']);
        self::assertSame(4, $summary['total_count']);
        self::assertSame(2, $summary['failed_count']);
        self::assertSame(1, $summary['pending_count']);
        self::assertSame(0, $summary['running_count']);
        self::assertSame(1, $summary['done_count']);
        self::assertSame('warning', $summary['health']['state']);
        self::assertSame('Failed sync needs review', $summary['health']['label']);
        self::assertContains('Failed sync outbox items need operator review.', $summary['health']['reasons']);
        self::assertSame('update_other_task', $summary['latest_failed_item']['operation_key'] ?? '');
        self::assertSame('newer failure', $summary['latest_failed_item']['last_error'] ?? '');
        self::assertSame(2, $summary['failed_items'][0]['attempts'] ?? 0);
        self::assertSame('sample30 deterministic sync failure for visibility.', $summary['failed_items'][1]['last_error'] ?? '');
    }

    public function testReportsReadyWhenNoFailedSyncOutboxItemsExist(): void
    {
        $summary = app_no_code_operator_sync_inspection_from_outbox_catalog([
            [
                'id' => '1',
                'status' => 'done',
                'operation_key' => 'update_sync_task',
                'attempts' => 1,
            ],
        ]);

        self::assertTrue($summary['ok']);
        self::assertSame(1, $summary['total_count']);
        self::assertSame(0, $summary['failed_count']);
        self::assertSame(1, $summary['done_count']);
        self::assertNull($summary['latest_failed_item']);
        self::assertSame([], $summary['failed_items']);
        self::assertSame('ready', $summary['health']['state']);
        self::assertSame('No failed sync items', $summary['health']['label']);
        self::assertSame([], $summary['health']['reasons']);
    }

    public function testRetryEligibilityAllowsOnlyFailedItemsWithRetryMetadata(): void
    {
        $eligible = app_no_code_operator_sync_retry_eligibility([
            'status' => 'failed',
            'dedupe_key' => 'retry-dedupe',
            'operation_key' => 'update_sync_task',
            'last_error' => 'server write rejected',
        ]);

        self::assertTrue($eligible['allowed']);
        self::assertSame('eligible', $eligible['state']);
        self::assertSame('Eligible for retry', $eligible['label']);
        self::assertSame('Retry sync item', $eligible['action_label']);
        self::assertSame([], $eligible['reasons']);

        $blocked = app_no_code_operator_sync_retry_eligibility([
            'status' => 'done',
            'dedupe_key' => '',
            'operation_key' => '',
            'last_error' => '',
        ]);

        self::assertFalse($blocked['allowed']);
        self::assertSame('blocked', $blocked['state']);
        self::assertSame('Retry blocked', $blocked['label']);
        self::assertContains('Only failed sync outbox items can be retried.', $blocked['reasons']);
        self::assertContains('Retry requires a dedupe key.', $blocked['reasons']);
        self::assertContains('Retry requires an operation key.', $blocked['reasons']);
        self::assertContains('Retry requires a recorded last_error.', $blocked['reasons']);
    }

    public function testBuildsRetryRequeueAuditEventInput(): void
    {
        $event = app_project_sync_outbox_retry_audit_event_input(
            [
                'id' => 'operator@example.test',
                'auth_source' => 'oidc',
            ],
            'SYNC-PROJECT',
            'retry-dedupe',
            [
                'status' => 'failed',
                'attempts' => 2,
                'last_error' => 'server write rejected',
                'operation_key' => 'update_sync_task',
                'operation_type' => 'update',
                'contract_key' => 'sync_task',
            ],
            [
                'status' => 'pending',
                'attempts' => 2,
                'last_error' => '',
                'operation_key' => 'update_sync_task',
                'operation_type' => 'update',
                'contract_key' => 'sync_task',
            ],
        );

        self::assertSame('operator@example.test', $event['actor_login_id']);
        self::assertSame('oidc', $event['actor_source']);
        self::assertSame('SYNC-PROJECT', $event['project_key']);
        self::assertSame('sync_outbox.retry_requeued', $event['event_type']);
        self::assertSame('sync_outbox', $event['target_type']);
        self::assertSame('retry-dedupe', $event['target_key']);
        self::assertSame('success', $event['result']);
        self::assertSame('failed', $event['metadata']['status_before'] ?? '');
        self::assertSame('pending', $event['metadata']['status_after'] ?? '');
        self::assertSame(2, $event['metadata']['attempts_before'] ?? 0);
        self::assertSame(2, $event['metadata']['attempts_after'] ?? 0);
        self::assertSame('server write rejected', $event['metadata']['last_error_before'] ?? '');
        self::assertSame('', $event['metadata']['last_error_after'] ?? 'unexpected');
        self::assertSame('update_sync_task', $event['metadata']['operation_key'] ?? '');
    }

    public function testBuildsProcessingHandoffStateForSyncOutboxItems(): void
    {
        $pending = app_project_sync_outbox_processing_handoff([
            'status' => 'pending',
            'origin' => 'public-runtime',
            'target' => 'server',
            'operation_key' => 'update_no_code_ticket',
        ]);

        self::assertSame('queued', $pending['state']);
        self::assertStringContainsString('queued', $pending['label']);
        self::assertStringContainsString('processor can claim', $pending['next_step']);
        self::assertContains('origin=public-runtime.', $pending['reasons']);
        self::assertContains('operation=update_no_code_ticket.', $pending['reasons']);

        $failed = app_project_sync_outbox_processing_handoff([
            'status' => 'failed',
            'operation_key' => 'update_sync_task',
        ]);

        self::assertSame('needs_review', $failed['state']);
        self::assertStringContainsString('failed', $failed['label']);
        self::assertStringContainsString('retry eligibility', $failed['next_step']);
    }

    public function testBuildsSyncOutboxStatusJsonPayloadWithoutIntentBody(): void
    {
        $payload = app_project_sync_outbox_status_payload('sample31', [
            'status' => 'done',
            'attempts' => 2,
            'last_error' => '',
            'operation_key' => 'update_inventory_request',
            'operation_type' => 'server_dbaccess',
            'dedupe_key' => 'status-json-dedupe',
            'updated_at' => '2026-07-05T12:34:56+00:00',
            'intent' => [
                'input' => [
                    'secret_note' => 'not exposed',
                ],
            ],
        ]);

        self::assertTrue($payload['ok']);
        self::assertSame('SAMPLE31', $payload['project_key']);
        self::assertSame('status-json-dedupe', $payload['dedupe_key']);
        self::assertSame('done', $payload['status']);
        self::assertSame('complete', $payload['handoff']['state'] ?? '');
        self::assertSame('update_inventory_request', $payload['operation_key']);
        self::assertSame('/projects/sample31/sync-outbox/status-json-dedupe', $payload['detail_path']);
        self::assertArrayNotHasKey('intent', $payload);
    }
}
