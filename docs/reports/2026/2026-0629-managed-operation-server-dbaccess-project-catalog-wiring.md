# 2026-0629 Managed Operation Server DBAccess Project Catalog Wiring

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation server DBAccess executor 用に、project DBAccess bootstrap candidate catalog から operation binding を作る first slice を追加した。

前 slice までは、複数 candidate list が手元にある前提で `contract_key` / `source_name` / `generated_name` を照合していた。この slice では、実 project metadata から `app_project_db_access_bootstrap_candidate_catalog()` を呼び、そこで得た generated / canonical-bootstrap candidate list を managed operation binding に渡す境界を固定した。

## Implemented

- Added `app_managed_operation_server_dbaccess_binding_from_project_catalog()`.
- The helper:
  - reads project DBAccess bootstrap candidates through `app_project_db_access_bootstrap_candidate_catalog()`
  - returns catalog read errors as fail-closed binding errors
  - delegates matching and method binding to `app_managed_operation_server_dbaccess_binding_from_candidates()`
- Extended `ManagedOperationLayerFoundationTest` with canonical metadata fixture:
  - project table metadata: `Task` with primary key `id` and editable `note`
  - project DataClass metadata: `Task` with `id` / `note`
  - project catalog fallback method generation: `UpdateTask`
  - managed operation `contract_key` `task` resolves to catalog source `Task`

## Boundary

This first slice intentionally does not:

- load generated PHP classes from project output
- open a real server DB connection
- execute the catalog-derived binding against real generated DBAccess
- add sample-facing runtime wiring
- define multi-candidate conflict policy beyond first bindable match

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_server_dbaccess_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 172 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9656, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- real server DB coverage for a generated DBAccess method
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> project-catalog-selected server DBAccess binding
