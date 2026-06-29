<?php

declare(strict_types=1);

require_once __DIR__ . '/managed_operation_sync_outbox_repository_pdo.php';

/**
 * @param callable(array<string,mixed>):array<string,mixed> $handler
 * @return array{
 *     ok:bool,
 *     processed:bool,
 *     outcome:string,
 *     item:array<string,mixed>|null,
 *     handler_result:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_managed_operation_sync_outbox_process_next(
    array $app,
    string $projectKey,
    callable $handler,
): array {
    $next = app_pdo_fetch_next_pending_managed_operation_sync_outbox_item($app, $projectKey);
    if (!$next['ok']) {
        return app_managed_operation_sync_outbox_processor_result(
            false,
            false,
            'fetch_failed',
            null,
            null,
            $next['error'],
        );
    }

    if ($next['item'] === null) {
        return app_managed_operation_sync_outbox_processor_result(
            true,
            false,
            'no_pending',
            null,
            null,
            '',
        );
    }

    $dedupeKey = (string) ($next['item']['dedupe_key'] ?? '');
    $claim = app_pdo_claim_managed_operation_sync_outbox_item($app, $projectKey, $dedupeKey);
    if (!$claim['ok']) {
        return app_managed_operation_sync_outbox_processor_result(
            false,
            false,
            'claim_failed',
            $claim['item'],
            null,
            $claim['error'],
        );
    }

    if (!$claim['claimed']) {
        return app_managed_operation_sync_outbox_processor_result(
            true,
            false,
            'not_claimed',
            $claim['item'],
            null,
            '',
        );
    }

    $claimedItem = is_array($claim['item']) ? $claim['item'] : [];
    try {
        $handlerResult = $handler($claimedItem);
    } catch (Throwable $throwable) {
        $failed = app_pdo_mark_managed_operation_sync_outbox_failed(
            $app,
            $projectKey,
            $dedupeKey,
            $throwable->getMessage(),
        );

        return app_managed_operation_sync_outbox_processor_result(
            $failed['ok'],
            true,
            'failed',
            $failed['item'],
            [
                'ok' => false,
                'error' => $throwable->getMessage(),
            ],
            $failed['ok'] ? '' : $failed['error'],
        );
    }

    $handlerOk = (bool) ($handlerResult['ok'] ?? false);
    if (!$handlerOk) {
        $failed = app_pdo_mark_managed_operation_sync_outbox_failed(
            $app,
            $projectKey,
            $dedupeKey,
            app_managed_operation_sync_outbox_processor_error_text($handlerResult),
        );

        return app_managed_operation_sync_outbox_processor_result(
            $failed['ok'],
            true,
            'failed',
            $failed['item'],
            $handlerResult,
            $failed['ok'] ? '' : $failed['error'],
        );
    }

    $done = app_pdo_mark_managed_operation_sync_outbox_done($app, $projectKey, $dedupeKey);
    return app_managed_operation_sync_outbox_processor_result(
        $done['ok'],
        true,
        'done',
        $done['item'],
        $handlerResult,
        $done['ok'] ? '' : $done['error'],
    );
}

/**
 * @param array<string,mixed>|null $item
 * @param array<string,mixed>|null $handlerResult
 * @return array{
 *     ok:bool,
 *     processed:bool,
 *     outcome:string,
 *     item:array<string,mixed>|null,
 *     handler_result:array<string,mixed>|null,
 *     error:string
 * }
 */
function app_managed_operation_sync_outbox_processor_result(
    bool $ok,
    bool $processed,
    string $outcome,
    ?array $item,
    ?array $handlerResult,
    string $error,
): array {
    return [
        'ok' => $ok,
        'processed' => $processed,
        'outcome' => $outcome,
        'item' => $item,
        'handler_result' => $handlerResult,
        'error' => $error,
    ];
}

/**
 * @param array<string,mixed> $handlerResult
 */
function app_managed_operation_sync_outbox_processor_error_text(array $handlerResult): string
{
    $error = trim((string) ($handlerResult['error'] ?? ''));
    return $error !== '' ? $error : 'managed operation sync outbox handler failed.';
}
