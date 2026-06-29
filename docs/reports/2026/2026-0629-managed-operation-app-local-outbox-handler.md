# 2026-0629 Managed Operation App-local Outbox Handler

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation outbox processor から App-local executor を呼び出す handler boundary を追加した。

この slice は server DBAccess execution や transport にはまだ入らない。目的は、`outbox -> processor -> App-local executor -> App-local SQLite DTO` の最小接続を固定すること。

## Implemented

- Added `app_managed_operation_app_local_outbox_handler()`.
- Handler behavior:
  - accepts a claimed outbox item from `app_managed_operation_sync_outbox_process_next()`
  - reads the persisted sync intent from the outbox item
  - delegates execution to `app_managed_operation_app_local_execute_intent()`
  - fails closed when the outbox item has no intent payload
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - pending outbox item is processed through the App-local handler
  - processor marks the item `done`
  - App-local DTO is updated through the handler path

## Boundary

This first slice intentionally does not:

- execute generated server DBAccess
- implement server conflict resolution
- implement retry / requeue policy
- implement delete / tombstone semantics
- generate sample-facing runtime wiring

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_app_local_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 138 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9622, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- generated server DBAccess execution adapter
- retry / requeue policy for failed outbox records
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> App-local handler
