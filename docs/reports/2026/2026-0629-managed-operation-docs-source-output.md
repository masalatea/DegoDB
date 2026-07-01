# 2026-0629 Managed Operation Docs Source Output

Status: `COMPLETED`

## Summary

Managed operation metadata を generated Source Output artifact として出力できるようにした。

`managed-operation-docs-md` strategy は shared contract manifest と managed operation metadata snapshot から以下を出力する。

- `managed-operations.json`
- `managed-operations.md`
- `README.md`

これにより、operation metadata は DB に保存されるだけでなく、後続の execution / sync / no-code work が参照できる artifact boundary を持つ。

## Implemented

- Added `mtool/app/project_output_managed_operation_generator.php`.
- Added `ManagedOperation` class type.
- Added `managed-operation-docs-md` artifact strategy validation, caption, generation support, and runtime-source requirement.
- Added runtime path helper:
  - `mtool/managed-operation-source-outputs/<PROJECT>/<SOURCE_OUTPUT>`
- Connected generation dispatch through `app_project_output_create_from_definition()`.
- Extended `SharedDataClassContractFoundationTest` to verify:
  - strategy allow-list / caption / generation support
  - managed operation metadata seeding
  - generated JSON payload
  - generated Markdown summary
  - artifact create / publish path

## Boundary

This is documentation / machine-readable metadata output only.

It does not execute operations, call generated DBAccess, or apply sync. Those remain in the active managed data operation layer.

## Verification

- `php -l mtool/app/project_output_managed_operation_generator.php`
- `php -l mtool/app/project_output_service.php`
- `php -l mtool/app/domain_validation.php`
- `php -l mtool/app/runtime_storage_paths.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/SharedDataClassContractFoundationTest.php`
  - `OK (7 tests, 267 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 282, Assertions: 9512, Skipped: 1.`

## Next

Continue with one of:

- operation execution adapter over existing generated DBAccess / App-local helpers
- server-copy / local-copy sync skeleton
- sample coverage that drives policy -> operation artifact -> App-local persistence
