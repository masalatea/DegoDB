# Sample18 Generated-Submit Route Response Contract First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #684

## Context

#683 defined the response/status matrix for sample18 generated-submit outcomes. This slice makes that contract durable in the no-code UI testing design note and adds focused PHPUnit assertions across existing route outcome tests.

## Changes

- Added a compact generated-submit response contract table to `docs/no-code-ui-testing.md`.
- Added `assertGeneratedSubmitRouteResponseContract()` to `Sample18MiniTaskBoardDemoTest`.
- Applied the assertion to representative route outcomes:
  - method/CSRF/validation/unknown invalid responses;
  - default blocked and duplicate non-execution;
  - executed success;
  - dependency/config failures;
  - DBAccess rollback failure;
  - commit-status-unknown recovery;
  - post-commit recording recovery.

No route execution behavior changed.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample18-mini-task-board-demo/compose.yaml --run-script=./sample/tutorials/sample18-mini-task-board-demo/run.sh --phpunit-target=/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php`
  - OK (28 tests, 1540 assertions)
- `make test`
  - OK, but incomplete, skipped, or risky tests!
  - Tests: 412, Assertions: 13395, Skipped: 1.

## Next

Promote #685 as a lane closure to decide whether broader browser smoke, the next sample18 no-code action/input gap, or route response refactoring should be promoted next.
