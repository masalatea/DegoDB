# 2026-0629 Managed Operation Server DBAccess Real Coverage

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation server DBAccess executor が、fake DBAccess ではなく generated DBAccess reference class を通じて real SQLite row を更新できることを確認する first slice を追加した。

これまでの server DBAccess slices は、method map / binding discovery / project catalog wiring を固定していたが、実 generated DBAccess method が runtime DB adapter 経由で row を更新する proof はまだなかった。この slice で、sample07 の generated `TodoItemDBAccess` と `TodoItemData` を使い、managed operation sync intent -> generated DBAccess update -> SQLite row update の最小経路を検証した。

## Implemented

- Added `ManagedOperationServerDbAccessRealCoverageTest`.
- The test:
  - creates a temporary SQLite server DB
  - creates a `todo_item` table compatible with sample07 generated DBAccess SQL
  - loads generated sample07 reference classes:
    - `TodoItemData`
    - `TodoItemDBAccess`
  - sets `MTOOL_RUNTIME_SQLITE_PATH` so generated `$mtooldb` runtime uses the real SQLite DB
  - executes `app_managed_operation_server_dbaccess_execute_intent()` with method map `update -> UpdateTodoItem`
  - verifies the row is updated in SQLite

## Boundary

This first slice intentionally does not:

- generate the DBAccess artifact during the test
- select binding from project catalog in the same test
- add sample-facing managed operation seed/runtime wiring
- cover server DB conflict handling or transaction semantics

Those remain in the active managed data operation layer.

## Verification

- `php -l tests/Integration/ManagedOperationServerDbAccessRealCoverageTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationServerDbAccessRealCoverageTest.php`
  - `OK (1 test, 4 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 283, Assertions: 9660, Skipped: 1.`

## Next

Continue the managed operation layer with sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> project-catalog-selected server DBAccess binding.
