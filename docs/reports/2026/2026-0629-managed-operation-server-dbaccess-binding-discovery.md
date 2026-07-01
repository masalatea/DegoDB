# 2026-0629 Managed Operation Server DBAccess Binding Discovery

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation server DBAccess executor 用に、generated / canonical DBAccess candidate catalog item から execution binding を作る first slice を追加した。

この slice は project artifact discovery 全体や real server DB execution にはまだ入らない。目的は、既存 candidate catalog shape の `data_class` / `dbaccess_class` / `method_catalog` から、operation type に対応する method map を作る境界を固定すること。

## Implemented

- Added `app_managed_operation_server_dbaccess_binding_from_candidate()`.
- Added method-name helpers:
  - `app_managed_operation_server_dbaccess_method_name_from_catalog()`
  - `app_managed_operation_server_dbaccess_expected_method_name()`
- Binding behavior:
  - reads `source_name`, `data_class`, `dbaccess_class`, and `method_catalog` from a candidate item
  - maps operation types to generated DBAccess method names:
    - `create` -> `Insert<SourceName>`
    - `read` -> `Get<SourceName>`
    - `update` -> `Update<SourceName>`
    - `delete` -> `Delete<SourceName>`
  - fails closed when the expected method is absent
  - returns a binding shape accepted by `app_managed_operation_server_dbaccess_execute_intent()`
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - a fake candidate catalog item produces an `update -> UpdateTask` method map
  - the produced binding can execute the server DBAccess adapter

## Boundary

This first slice intentionally does not:

- call `app_project_db_access_bootstrap_candidate_catalog()` directly
- choose a candidate from multiple project sources
- execute against a real server DB connection
- generate sample-facing runtime wiring
- implement namespace/autoload discovery

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_server_dbaccess_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 158 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9642, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- project-level candidate selection from generated / canonical DBAccess catalog
- real server DB coverage for a generated DBAccess method
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> discovered server DBAccess binding
