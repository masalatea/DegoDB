# 2026-0629 Managed Operation Server DBAccess Candidate Selection

Status: `FIRST_SLICE_DONE`

## Summary

Managed operation server DBAccess executor 用に、project-level candidate list から operation に合う DBAccess binding を選ぶ first slice を追加した。

前 slice の `binding_from_candidate()` は単一 candidate を binding に変換する境界だった。この slice では、その前段として複数候補から `contract_key` / `source_name` / `generated_name` を照合し、対象 operation に対応する generated DBAccess method を持つ candidate を選ぶ境界を固定した。

## Implemented

- Added `app_managed_operation_server_dbaccess_binding_from_candidates()`.
- Added candidate matching helpers:
  - `app_managed_operation_server_dbaccess_candidate_matches_operation()`
  - `app_managed_operation_server_dbaccess_match_key()`
- Selection behavior:
  - explicit `options['source_name']` is matched first when provided
  - otherwise operation `contract_key` is matched against candidate `contract_key`, `source_name`, and `generated_name`
  - `_` / `-` and case differences are normalized for matching
  - candidate binding still fails closed through `binding_from_candidate()` when the generated DBAccess method is absent
  - returns a binding shape accepted by `app_managed_operation_server_dbaccess_execute_intent()`
- Extended `ManagedOperationLayerFoundationTest` to verify:
  - an unrelated candidate is ignored
  - `task` operation selects `Task`
  - the selected binding executes the fake server DBAccess adapter

## Boundary

This first slice intentionally does not:

- call `app_project_db_access_bootstrap_candidate_catalog()` directly
- open a real server DB connection
- prove generated namespace/autoload behavior
- add sample-facing runtime wiring
- decide conflict policy for two valid candidates with the same normalized contract key

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_server_dbaccess_executor.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 161 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9645, Skipped: 1.`

## Next

Continue the managed operation layer with one of:

- real server DB coverage for a generated DBAccess method
- sample coverage that drives policy -> operation plan -> sync intent -> outbox -> processor -> selected server DBAccess binding
- generated artifact / project catalog wiring around the pure candidate-selection helper
