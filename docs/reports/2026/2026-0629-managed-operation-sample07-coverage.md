# 2026-0629 Managed Operation Sample07 Coverage

Status: `FIRST_SLICE_SCOPE_COMPLETED`

## Summary

Managed data operation layer の sample coverage first slice として、`sample07-dbaccess-crud-basic` に managed operation metadata seed と sample pack verification を追加した。

これにより、DBAccess CRUD sample の実 project metadata から `update_todo_item` managed operation を読み、project DBAccess candidate catalog で generated `TodoItemDBAccess::UpdateTodoItem()` binding まで解決できることを sample pack で確認した。

## Implemented

- Added `sample07` managed operation seed:
  - operation key: `update_todo_item`
  - contract key: `todo_item`
  - operation type: `update`
  - permission key: `project.edit`
  - key field: `id`
  - input fields: `title`, `status`, `body`
- Extended `app_sample7_dbaccess_crud_basic_run()` verification:
  - fetches managed operation snapshot
  - asserts operation count / key / contract / type / field count
  - creates server DBAccess binding from project catalog
  - asserts binding resolves to:
    - `source_name`: `TodoItem`
    - `data_class`: `TodoItemData`
    - `dbaccess_class`: `TodoItemDBAccess`
    - method map: `update -> UpdateTodoItem`
- Updated sample07 PHPUnit failure diagnostics to include managed operation snapshot and binding result.

## Design Note

This sample exposed the important physical/logical split:

- physical source / contract key: `todo_item`
- generated PHP surface: `TodoItem`

To make that work, DBAccess bootstrap candidates now carry `generated_name`, and managed operation server DBAccess binding uses that generated name for method resolution. This keeps sample07 aligned with the physical/logical naming policy instead of requiring operation metadata to pretend the physical table name is the PHP class stem.

## Boundary

This first-slice sample coverage intentionally does not:

- run a full no-code UI workflow
- add transport / conflict resolution
- execute App-local -> server sync against a published sample app

Those are broader product/runtime follow-ups, not part of the managed operation first-slice spine.

## Verification

- `php -l mtool/app/managed_operation_server_dbaccess_executor.php`
- `php -l mtool/app/project_db_access_bootstrap_service.php`
- `php -l mtool/scripts/lib/sample7_dbaccess_crud_basic_output_check.php`
- `php -l tests/Integration/Sample7DbAccessCrudBasicOutputTest.php`
- `make sample07-pack-runtime-test`
  - `OK (1 test, 19 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 283, Assertions: 9662, Skipped: 1.`

## Result

Managed data operation layer first-slice spine is complete through sample coverage:

policy / metadata -> plan-only execution -> sync intent -> outbox processing contracts -> App-local handler -> server DBAccess handler -> project catalog binding -> generated DBAccess real coverage -> sample07 managed operation coverage.
