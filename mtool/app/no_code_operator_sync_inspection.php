<?php

declare(strict_types=1);

/**
 * @param list<array<string,mixed>> $outboxItems
 * @return array{
 *     ok:bool,
 *     total_count:int,
 *     failed_count:int,
 *     pending_count:int,
 *     running_count:int,
 *     done_count:int,
 *     latest_failed_item:array<string,mixed>|null,
 *     failed_items:list<array<string,mixed>>,
 *     health:array{state:string,label:string,reasons:list<string>}
 * }
 */
function app_no_code_operator_sync_inspection_from_outbox_catalog(array $outboxItems): array
{
    $counts = [
        'pending' => 0,
        'running' => 0,
        'done' => 0,
        'failed' => 0,
    ];
    $failedItems = [];

    foreach ($outboxItems as $item) {
        $status = (string) ($item['status'] ?? '');
        if (array_key_exists($status, $counts)) {
            $counts[$status]++;
        }

        if ($status === 'failed') {
            $failedItems[] = app_no_code_operator_sync_inspection_failed_item($item);
        }
    }

    usort(
        $failedItems,
        static function (array $left, array $right): int {
            $updatedCompare = strcmp((string) ($right['updated_at'] ?? ''), (string) ($left['updated_at'] ?? ''));
            if ($updatedCompare !== 0) {
                return $updatedCompare;
            }

            return (int) ($right['id'] ?? 0) <=> (int) ($left['id'] ?? 0);
        },
    );

    $latestFailedItem = $failedItems[0] ?? null;
    $reasons = [];
    if ($counts['failed'] > 0) {
        $reasons[] = 'Failed sync outbox items need operator review.';
    }

    return [
        'ok' => true,
        'total_count' => count($outboxItems),
        'failed_count' => $counts['failed'],
        'pending_count' => $counts['pending'],
        'running_count' => $counts['running'],
        'done_count' => $counts['done'],
        'latest_failed_item' => $latestFailedItem,
        'failed_items' => array_slice($failedItems, 0, 5),
        'health' => [
            'state' => $counts['failed'] > 0 ? 'warning' : 'ready',
            'label' => $counts['failed'] > 0 ? 'Failed sync needs review' : 'No failed sync items',
            'reasons' => $reasons,
        ],
    ];
}

/**
 * @param array<string,mixed> $item
 * @return array<string,mixed>
 */
function app_no_code_operator_sync_inspection_failed_item(array $item): array
{
    return [
        'id' => (string) ($item['id'] ?? ''),
        'dedupe_key' => (string) ($item['dedupe_key'] ?? ''),
        'status' => (string) ($item['status'] ?? ''),
        'operation_key' => (string) ($item['operation_key'] ?? ''),
        'operation_type' => (string) ($item['operation_type'] ?? ''),
        'contract_key' => (string) ($item['contract_key'] ?? ''),
        'origin' => (string) ($item['origin'] ?? ''),
        'target' => (string) ($item['target'] ?? ''),
        'storage_mode' => (string) ($item['storage_mode'] ?? ''),
        'attempts' => (int) ($item['attempts'] ?? 0),
        'last_error' => (string) ($item['last_error'] ?? ''),
        'created_at' => (string) ($item['created_at'] ?? ''),
        'updated_at' => (string) ($item['updated_at'] ?? ''),
    ];
}

/**
 * @param array<string,mixed> $item
 * @return array{
 *     allowed:bool,
 *     state:string,
 *     label:string,
 *     action_label:string,
 *     reasons:list<string>
 * }
 */
function app_no_code_operator_sync_retry_eligibility(array $item): array
{
    $reasons = [];

    if ((string) ($item['status'] ?? '') !== 'failed') {
        $reasons[] = 'Only failed sync outbox items can be retried.';
    }
    if (trim((string) ($item['dedupe_key'] ?? '')) === '') {
        $reasons[] = 'Retry requires a dedupe key.';
    }
    if (trim((string) ($item['operation_key'] ?? '')) === '') {
        $reasons[] = 'Retry requires an operation key.';
    }
    if (trim((string) ($item['last_error'] ?? '')) === '') {
        $reasons[] = 'Retry requires a recorded last_error.';
    }

    $allowed = $reasons === [];
    return [
        'allowed' => $allowed,
        'state' => $allowed ? 'eligible' : 'blocked',
        'label' => $allowed ? 'Eligible for retry' : 'Retry blocked',
        'action_label' => 'Retry sync item',
        'reasons' => $reasons,
    ];
}
