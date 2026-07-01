# 2026-0629 Managed Operation Outbox Status Transitions

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync outbox の processing first slice として、outbox record を `running` / `done` / `failed` に遷移する PDO repository API を追加した。

この slice はまだ outbox processor や DBAccess execution を実行しない。目的は、永続化済み sync intent を処理するときの attempts / last_error / status contract を先に固定すること。

## Implemented

- Added outbox status transition functions:
  - `app_pdo_mark_managed_operation_sync_outbox_running()`
  - `app_pdo_mark_managed_operation_sync_outbox_done()`
  - `app_pdo_mark_managed_operation_sync_outbox_failed()`
  - `app_pdo_update_managed_operation_sync_outbox_status()`
- `running` increments `attempts` and clears `last_error`.
- `failed` records `last_error` without incrementing attempts.
- `done` clears `last_error` without changing attempts.
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - pending outbox item can move to running
  - failed item records error
  - retry increments attempts
  - done preserves attempts and clears error

## Boundary

This first slice intentionally does not:

- select the next pending outbox row for processing
- execute generated DBAccess
- execute App-local persistence helpers
- implement locking / concurrency
- implement retry backoff

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_sync_outbox_repository_pdo.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 84 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9568, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- next pending outbox selection / processing contract
- DBAccess / App-local helper execution from operation plans
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> status transitions
