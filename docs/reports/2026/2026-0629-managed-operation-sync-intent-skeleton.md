# 2026-0629 Managed Operation Sync Intent Skeleton

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation execution plan から、server-copy / local-copy sync skeleton の最小単位として使える `managed-operation-sync-intent-v0` を作る関数を追加した。

この slice は outbox table や sync transport にはまだ入らない。目的は、operation plan の key / input / filter を同期キューへ渡せる安定した intent shape に変換し、次の永続化・実行 slice の境界を固定すること。

## Implemented

- Added `mtool/app/managed_operation_sync.php`.
- Added `app_managed_operation_sync_intent_from_plan()`.
- The sync intent:
  - requires `plan-only` execution plans
  - supports `local-copy` and `server-copy` storage modes
  - supports `app-local` and `server` endpoints
  - carries project / operation / contract identity
  - carries `key`, `input`, and `filter` payload sections
  - carries result output field policy
  - includes deterministic `dedupe_key`
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - local-copy app-local -> server intent creation
  - deterministic dedupe key shape
  - invalid storage mode rejection
  - non-plan execution rejection

## Boundary

This first slice intentionally does not:

- persist outbox rows
- execute sync transport
- call generated DBAccess
- resolve conflicts
- mark local rows dirty / clean

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_sync.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 56 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9540, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- sync outbox persistence for managed operation intents
- DBAccess / App-local helper execution from operation plans
- sample coverage that drives policy -> operation plan -> sync intent -> App-local persistence
