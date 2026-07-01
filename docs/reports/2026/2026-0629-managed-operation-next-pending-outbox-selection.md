# 2026-0629 Managed Operation Next Pending Outbox Selection

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync outbox の processor entry first slice として、次に処理する `pending` outbox record を選択する PDO repository API を追加した。

この slice はまだ record を lock せず、DBAccess execution も実行しない。目的は、concrete outbox processor が使う selection contract を先に固定すること。

## Implemented

- Added `app_pdo_fetch_next_pending_managed_operation_sync_outbox_item()`.
- Selection rule:
  - project-scoped
  - `status = pending`
  - `attempts ASC, id ASC`
  - one item only
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - first pending item is selected
  - second pending item remains pending while the first is processed
  - after the first item is done, next pending selection returns the second item

## Boundary

This first slice intentionally does not:

- lock or claim rows
- mark the selected row running
- execute generated DBAccess
- execute App-local persistence helpers
- implement retry backoff or concurrency

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_sync_outbox_repository_pdo.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 90 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9574, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- claim-and-run processor contract
- DBAccess / App-local helper execution from operation plans
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> next pending selection
