<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/no_code_operator_sync_inspection.php';

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
}
