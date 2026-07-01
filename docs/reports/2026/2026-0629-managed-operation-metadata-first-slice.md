# 2026-0629 Managed Operation Metadata First Slice

Status: `FIRST_SLICE_DONE`

## Summary

Managed data operation layer の first slice として、operation metadata の保存形と fail-closed policy evaluator を追加した。

この slice は DBAccess 実行や sync runtime にはまだ入らない。目的は、shared contract 上の field semantics と permission / principal policy を同じ operation definition に載せる最小基盤を固定すること。

## Implemented

- Added canonical config metadata tables:
  - `project_managed_operations`
  - `project_managed_operation_fields`
- Added SQLite bootstrap required-table coverage for both tables.
- Added PDO repository:
  - `app_pdo_upsert_managed_operation()`
  - `app_pdo_upsert_managed_operation_field()`
  - `app_pdo_fetch_managed_operation_snapshot()`
  - `app_pdo_fetch_managed_operation_item()`
- Added fail-closed evaluator:
  - `app_managed_operation_policy_evaluate()`
- Evaluator checks:
  - operation status is `active`
  - `permission_key` through existing auth foundation permission evaluator
  - required roles
  - required scopes
  - required claims
  - operation contract key matches the shared contract
  - operation fields exist in the shared contract
  - key fields are actual key fields
  - client-writable fields require `contract_metadata.operation_role = editable`
  - `business-only` storage policy rejects non-business fields

## Boundary

This first slice intentionally does not add:

- operation execution adapter
- generated operation artifact
- server/app sync runtime
- no-code screen binding
- admin UI

Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/managed_operation_repository_pdo.php`
- `php -l mtool/app/managed_operation_policy.php`
- `php -l mtool/app/config_db_bootstrap.php`
- `php -l tests/Integration/ManagedOperationLayerFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/ManagedOperationLayerFoundationTest.php`
  - `OK (1 test, 28 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 281, Assertions: 9468, Skipped: 1.`

## Next

Continue the managed operation layer with one of these slices:

- operation execution adapter over existing DBAccess / App-local helpers
- generated operation documentation / artifact
- sync skeleton for server-copy / local-copy operation semantics
- sample coverage that drives an operation through policy -> execution -> App-local persistence
