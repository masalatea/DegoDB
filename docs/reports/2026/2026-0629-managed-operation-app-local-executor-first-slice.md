# 2026-0629 Managed Operation App-local Executor First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync intent を App-local SQLite persistence helper に接続する first slice を追加した。

この slice は generated server DBAccess 実行や sync transport にはまだ入らない。目的は、`managed-operation-sync-intent-v0` の payload を App-local DTO read / save helper に渡す execution boundary を固定すること。

## Implemented

- Added `mtool/app/managed_operation_app_local_executor.php`.
- Added `app_managed_operation_app_local_execute_intent()`.
- App-local execution behavior:
  - requires `managed-operation-sync-intent-v0`
  - requires `app-local` endpoint to be part of the intent
  - executes `read` through `app_local_sqlite_dbaccess_read_dto()`
  - executes `create` through `app_local_sqlite_dbaccess_save_dto()`
  - executes `update` by reading the existing DTO, merging operation input, then saving
  - marks local save metadata as dirty for create / update
  - fails closed for unsupported operation types such as `delete`
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - an update intent can modify an existing App-local DTO
  - partial update preserves existing DTO fields outside the operation input
  - local metadata is marked dirty after execution

## Boundary

This first slice intentionally does not:

- execute generated server DBAccess
- implement server conflict resolution
- implement delete / tombstone semantics
- implement retry / backoff policy
- attach the App-local executor directly to the outbox processor handler

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_app_local_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 130 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9614, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- connect App-local executor as an outbox processor handler
- generated server DBAccess execution adapter
- delete / tombstone semantics for App-local operation execution
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> App-local executor
