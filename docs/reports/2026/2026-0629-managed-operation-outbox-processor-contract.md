# 2026-0629 Managed Operation Outbox Processor Contract

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync outbox の concrete processor first slice として、pending outbox record を 1 件取得し、claim できた record だけを handler に渡して `done` / `failed` へ遷移する processor wrapper を追加した。

この slice はまだ generated DBAccess / App-local helper の実行内容を実装しない。目的は、次の execution adapter を差し込む前に、outbox processor の lifecycle contract を固定すること。

## Implemented

- Added `mtool/app/managed_operation_sync_outbox_processor.php`.
- Added `app_managed_operation_sync_outbox_process_next()`.
- Processor behavior:
  - fetches the next pending outbox record
  - returns `outcome: no_pending` without invoking handler when the queue is empty
  - claims the selected record before invoking handler
  - returns `outcome: not_claimed` without invoking handler when claim is lost
  - marks claimed item `done` when handler returns `ok: true`
  - marks claimed item `failed` when handler returns `ok: false`
  - marks claimed item `failed` when handler throws
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - successful process path moves item to `done`
  - handler failure moves item to `failed` and records `last_error`
  - empty queue returns `no_pending`

## Boundary

This first slice intentionally does not:

- execute generated DBAccess
- execute App-local persistence helpers
- implement retry backoff or requeue policy
- implement database-level skip-locked worker behavior
- implement multi-process worker orchestration

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_sync_outbox_processor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 119 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9603, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- DBAccess / App-local helper execution from claimed operation plans
- retry / requeue policy for failed outbox records
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor
