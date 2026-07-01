# 2026-06-29 No-Code Screen Definition First Slice

## Status

- implementation status: `FIRST_SLICE_IMPLEMENTED`
- current-plan link: `docs/current-plans.md` order 1, No-code screen definition and runtime MVP
- runtime status: `PENDING`

## Purpose

No-code app generation should start from managed data operations, not from a visual UI builder.

This slice adds the first stable screen-definition artifact shape that can be consumed by a later minimal runtime. It proves that shared contract metadata and managed operation metadata can produce list/detail/form behavior without hand-written screen logic.

## Implemented Scope

- Added `mtool/app/no_code_screen_definition.php`.
- Added `no-code-screen-definition-v0`.
- Added snapshot-based definition generation from:
  - shared contract manifest.
  - managed operation snapshot.
  - optional normalized principal for permission-aware action availability.
- Added project-based entrypoint that reads:
  - `app_shared_contract_manifest_from_project()`.
  - `app_pdo_fetch_managed_operation_snapshot()`.
- Generated per managed-screen contract:
  - list screen definition.
  - detail screen definition.
  - form screen definition.
  - field label/type/required/readonly/visibility hints.
  - storage/sync display hint from shared contract metadata.
  - action definitions from active managed operations.
  - policy result and enabled/disabled availability.
- Added fail-closed behavior when no active `no_code_role=managed-screen` contract exists.
- Added `NoCodeScreenDefinitionTest`.

## Boundary

This is not the no-code runtime MVP yet.

The new code generates runtime-readable screen definitions, but it does not render HTML, execute browser actions, or dispatch operations from a UI. Runtime rendering and operation dispatch remain the next slice.

## Verification

- `php -l mtool/app/no_code_screen_definition.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample07-dbaccess-crud-basic/compose.yaml --run-script=sample/tutorials/sample07-dbaccess-crud-basic/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeScreenDefinitionTest.php`
  - `OK (3 tests, 29 assertions)`
- `make test`
  - `OK, but incomplete, skipped, or risky tests! Tests: 286, Assertions: 9691, Skipped: 1.`

## Result

The roadmap assumption is now represented in code: data-first metadata can become stable no-code screen behavior.

The next useful slice is a minimal runtime renderer that consumes `no-code-screen-definition-v0`, shows list/detail/form screens, and routes enabled actions through the existing managed operation boundary.
