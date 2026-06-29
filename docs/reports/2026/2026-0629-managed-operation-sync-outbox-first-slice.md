# 2026-0629 Managed Operation Sync Outbox First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync intent を config DB に idempotent に保存する outbox first slice を追加した。

この slice は transport や conflict resolution にはまだ入らない。目的は、`managed-operation-sync-intent-v0` を永続化できる canonical queue boundary を作り、DBAccess execution / App-local sync の次 slice が同じ outbox record を使えるようにすること。

## Implemented

- Added `project_managed_operation_sync_outbox`.
- Added SQLite bootstrap required-table coverage.
- Added `mtool/app/managed_operation_sync_outbox_repository_pdo.php`.
- Added repository functions:
  - `app_pdo_enqueue_managed_operation_sync_intent()`
  - `app_pdo_fetch_managed_operation_sync_outbox_item()`
  - `app_pdo_fetch_managed_operation_sync_outbox_catalog()`
- Enqueue is idempotent by `(project_id, dedupe_key)`.
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - sync intent enqueue
  - stored intent round trip
  - duplicate enqueue keeps the same outbox row
  - catalog fetch returns the pending operation

## Boundary

This first slice intentionally does not:

- execute outbox records
- call generated DBAccess
- call App-local persistence helpers
- implement retries / status transitions beyond initial `pending`
- resolve conflicts

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_sync_outbox_repository_pdo.php`
- `php -l mtool/app/config_db_bootstrap.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 68 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9552, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- DBAccess / App-local helper execution from operation plans
- outbox status transition and retry contract
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> App-local persistence
