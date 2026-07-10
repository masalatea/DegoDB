# Sample18 Generated Submit Route Feature-Flag Execution First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #664

## Summary

Added the first route-level generated-submit execution slice behind an explicit executor flag.

The existing mutation gate flag still controls readiness metadata. A separate `sample18_generated_submit_executor_enabled` / `MTOOL_SAMPLE18_GENERATED_SUBMIT_EXECUTOR_ENABLED` flag now controls whether the route may actually execute. This keeps the existing metadata-only tests stable while allowing a narrower execution enablement switch.

## Implementation

- Added `app_lab_sample18_task_board_generated_submit_executor_enablement_flag()`.
- Added route executor dependency validation for injected transaction callables.
- Updated the generated-submit route response builder so it:
  - preserves current HTTP 409 blocked behavior when the executor flag is off;
  - builds executor coordination with the executor flag value;
  - calls `app_lab_sample18_task_board_generated_submit_route_execution_plan()` only when coordination is ready;
  - creates DB-backed execution audit and idempotency outcome recorders inside the route boundary;
  - returns `route_execution`, `transaction_result`, and `post_commit_recording` metadata for execution outcomes.
- Extended post-commit recording context with DBAccess result code and affected row count.

## Coverage

Added route-level coverage that:

- keeps the existing metadata-only route behavior when only the mutation gate flag is enabled;
- enables both mutation metadata and executor execution flags for a fresh valid generated-submit request;
- injects generated runtime transaction binding callables backed by SQLite/PDO;
- verifies the route returns HTTP 200 / `result=executed` only after transaction commit and post-commit recording succeed;
- verifies the generated `TaskCardDBAccess` insert persists a task row;
- verifies the execution audit and idempotency execution outcome are persisted;
- verifies duplicate replay with the executor flag enabled does not execute again.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 24 tests, 1322 assertions.
- `make test`
  - OK, but incomplete, skipped, or risky tests.
  - Tests: 408, Assertions: 13167, Skipped: 1.

## Next

Close this lane in #665 and decide whether to promote failure/recovery route coverage, real sample runtime default binding, or UI success/error rendering next.
