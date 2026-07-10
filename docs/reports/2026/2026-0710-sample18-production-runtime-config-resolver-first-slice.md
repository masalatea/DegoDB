# Sample18 Production Runtime Config Resolver First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #677

## Context

#676 defined a production-safe generated-submit executor config boundary. The route already had separate mutation/executor flags and default runtime binding, but there was no single resolver that exposed precedence, dependency source, runtime reference path, and fail-closed readiness metadata before execution.

## Changes

- Added `app_lab_sample18_task_board_generated_submit_executor_config()`.
- Added shared helpers for:
  - app/env flag normalization with app override precedence;
  - default runtime reference dir resolution;
  - default runtime file list construction;
  - complete injected transaction callable detection.
- Reused the shared runtime file list in default transaction callable construction.
- Added `executor_config` metadata to generated-submit route responses.
- Added pre-dependency fail-closed route behavior when config is ready by flags but the default runtime reference files are missing or unreadable.
- Preserved injected transaction callables as the highest-priority dependency source when all required callables are present.
- Added focused resolver coverage for default disabled state, env enablement, app override, missing runtime reference failure, and injected callable bypass.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample18-mini-task-board-demo/compose.yaml --run-script=./sample/tutorials/sample18-mini-task-board-demo/run.sh --phpunit-target=/var/www/tests/Integration/Sample18MiniTaskBoardDemoTest.php`
  - OK (27 tests, 1415 assertions)

## Next

Promote #678 as a lane closure to decide whether the next small step should add broader browser smoke coverage, refine route response/status documentation, or document sample18 generated-submit availability.
