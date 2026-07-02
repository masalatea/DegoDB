# Retry Processing Smoke First Slice

Date: 2026-06-30
Status: `FIRST_SLICE_DONE`

## Summary

Added a focused smoke proof that a requeued sync outbox item is picked up by the existing processor path.

The smoke covers failed item -> requeue to `pending` -> processor claim -> handler success -> `done`. It keeps the retry behavior inside the existing outbox lifecycle and does not add a scheduler, transport, conflict resolution, retry UI, audit table, or dashboard.

## Implemented

- Extended `ManagedOperationLayerFoundationTest`.
- Reuses `app_pdo_requeue_failed_managed_operation_sync_outbox_item()`.
- Calls `app_managed_operation_sync_outbox_process_next()` after requeue.
- Verifies the processor sees the requeued item as `running`.
- Verifies `attempts` increments on processor claim from 1 to 2.
- Verifies `last_error` stays cleared through processing.
- Verifies final status becomes `done`.

## Boundary

In scope:

- one deterministic retry processing smoke
- existing sync outbox processor
- existing handler contract
- existing requeue action semantics

Out of scope:

- background scheduler
- new retry UI
- remote transport
- conflict resolution
- retry audit table
- broad dashboard

## Verification

- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- focused PHPUnit for `ManagedOperationLayerFoundationTest`
- `git diff --check`
- `make test`

## Next

Run a short post-retry-processing-smoke product goal replan before choosing the next implementation slice.
