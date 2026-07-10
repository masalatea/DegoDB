# Sample18 Real Runtime Default Binding First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #669

## Summary

Added default route executor dependency construction for sample18 generated runtime DBAccess classes.

The route still keeps executor execution disabled by default. When the explicit executor flag is enabled and no transaction callables are injected, the route can now load sample18 reference runtime classes, construct a generated runtime DB handle, and bind transaction callables for `TaskCardDBAccess`.

## Implementation

- Added `app_lab_sample18_task_board_generated_submit_default_transaction_callables()`.
- Updated route executor dependency resolution to:
  - prefer injected `sample18_generated_submit_transaction_callables`;
  - otherwise load sample18 reference runtime files;
  - construct `MtoolGeneratedDbAccessRuntimeDb`;
  - set legacy `$mtooldb`;
  - return callables from `app_lab_sample18_task_board_generated_submit_transaction_binding_callables()`;
  - fail closed when reference runtime files/classes are missing or runtime binding construction throws.

## Coverage

Added route-level coverage that:

- executes a fresh generated-submit request with executor flag on and no injected transaction callables;
- persists the generated `TaskCardDBAccess` insert through the default runtime binding;
- records post-commit audit/idempotency outcomes;
- fails closed when the configured sample18 runtime reference directory is missing.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 26 tests, 1367 assertions.
- `make test`
  - OK, but incomplete, skipped, or risky tests.
  - Tests: 410, Assertions: 13212, Skipped: 1.

## Next

Close this lane in #670 and decide whether UI success/error rendering, commit-unknown recovery coverage, or production runtime config hardening should be promoted next.
