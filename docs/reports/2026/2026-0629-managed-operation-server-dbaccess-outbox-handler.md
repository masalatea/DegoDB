# 2026-0629 Managed Operation Server DBAccess Outbox Handler

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation outbox processor から server DBAccess executor を呼び出す handler boundary を追加した。

この slice は generated artifact binding discovery や real server DB connection にはまだ入らない。目的は、`outbox -> processor -> server DBAccess executor -> generated DBAccess method` の最小接続を固定すること。

## Implemented

- Added `app_managed_operation_server_dbaccess_outbox_handler()`.
- Handler behavior:
  - accepts a claimed outbox item from `app_managed_operation_sync_outbox_process_next()`
  - reads the persisted sync intent from the outbox item
  - delegates execution to `app_managed_operation_server_dbaccess_execute_intent()`
  - fails closed when the outbox item has no intent payload
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - pending outbox item is processed through the server DBAccess handler
  - processor marks the item `done`
  - fake generated DBAccess method receives a hydrated DataClass object through the handler path

## Boundary

This first slice intentionally does not:

- discover generated DBAccess binding from project source output artifacts
- execute against a real server DB connection
- generate sample-facing runtime wiring
- implement conflict resolution
- implement transaction / retry policy

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_server_dbaccess_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 152 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9636, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- generated DBAccess binding discovery from project artifacts / metadata
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> server DBAccess handler
- retry / requeue policy for failed server DBAccess outbox records
