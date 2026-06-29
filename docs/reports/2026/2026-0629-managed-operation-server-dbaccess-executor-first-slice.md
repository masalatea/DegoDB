# 2026-0629 Managed Operation Server DBAccess Executor First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync intent を generated server DBAccess class / method に渡す execution adapter first slice を追加した。

この slice は実 server DB への接続や generated artifact wiring にはまだ入らない。目的は、`managed-operation-sync-intent-v0` の payload を DBAccess method argument に変換し、DataClass object を hydrate して呼び出す境界を固定すること。

## Implemented

- Added `mtool/app/managed_operation_server_dbaccess_executor.php`.
- Added `app_managed_operation_server_dbaccess_execute_intent()`.
- Server DBAccess execution behavior:
  - requires `managed-operation-sync-intent-v0`
  - requires configured server endpoint to be part of the intent
  - resolves DBAccess method from `operation_type` and a provided method map
  - checks DBAccess class and method existence
  - hydrates DataClass object from intent key + input for `create` / `update` / `delete`
  - passes key values as positional arguments for `read`
  - treats `false` DBAccess result as failure
- Extended `ManagedOperationLayerFoundationTest` with fake generated classes to verify:
  - update intent selects the configured DBAccess method
  - DataClass object is hydrated from key + input
  - DBAccess method result is returned through the executor result

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
  - `OK (1 test, 145 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9629, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- generated DBAccess binding discovery from project artifacts / metadata
- outbox processor handler for server DBAccess executor
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> server DBAccess adapter
