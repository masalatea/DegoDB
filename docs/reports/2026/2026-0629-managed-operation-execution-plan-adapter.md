# 2026-0629 Managed Operation Execution Plan Adapter

Status: `FIRST_SLICE_DONE`

## Summary

Managed data operation layer の execution adapter first slice として、managed operation metadata と shared contract と principal から副作用なしの execution plan を作る関数を追加した。

この slice はまだ DBAccess を呼び出さない。目的は、policy evaluation 後の key / input / filter / output field の境界を固定し、次の generated DBAccess execution / App-local sync skeleton が同じ plan contract を使えるようにすること。

## Implemented

- Added `mtool/app/managed_operation_executor.php`.
- Added `app_managed_operation_execution_prepare()`.
- The adapter:
  - evaluates existing fail-closed managed operation policy
  - rejects unknown client input keys
  - requires key fields for read / update / delete operations
  - requires operation fields marked `is_required`
  - accepts physical or generated field names, then normalizes output to generated DTO names
  - coerces primitive values using shared contract field type
  - returns a `plan-only` execution plan with `key`, `input`, `filter`, `output_fields`, and `field_map`
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - successful update operation plan
  - unknown local metadata input is rejected
  - missing required operation input is rejected

## Boundary

This first slice intentionally does not:

- execute generated DBAccess
- write server DB rows
- write App-local SQLite rows
- create sync queue entries
- expose admin UI or runtime HTTP endpoints

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 42 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9526, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- operation execution adapter that invokes generated DBAccess / App-local helpers from the plan
- server-copy / local-copy sync skeleton
- sample coverage that drives policy -> operation plan -> App-local persistence
