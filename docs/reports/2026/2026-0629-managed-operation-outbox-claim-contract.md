# 2026-0629 Managed Operation Outbox Claim Contract

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation sync outbox の processor contract first slice として、pending outbox record を `running` に claim する PDO repository API を追加した。

この slice はまだ DBAccess execution を実行しない。目的は、processor が pending record を安全に処理対象として掴むための claim boundary を固定すること。

## Implemented

- Added `app_pdo_claim_managed_operation_sync_outbox_item()`.
- Claim behavior:
  - requires existing outbox record
  - only claims rows currently in `pending`
  - moves claimed row to `running`
  - increments `attempts`
  - clears `last_error`
  - returns `claimed: false` when the row is already non-pending
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - pending row can be claimed
  - double claim does not increment attempts again
  - claimed row no longer appears in next pending selection

## Boundary

This first slice intentionally does not:

- execute generated DBAccess
- execute App-local persistence helpers
- implement database-level skip-locked worker behavior
- implement retry backoff
- implement multi-process worker orchestration

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_sync_outbox_repository_pdo.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 100 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9584, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- DBAccess / App-local helper execution from claimed operation plans
- outbox processor wrapper around claim -> execute -> done / failed
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> claim
