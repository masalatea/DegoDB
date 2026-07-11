# Sample18 Mutation Dispatcher Helper Dry-Run First Slice

Date: 2026-07-10
Plan: #583
Status: DONE

## Summary

Added a non-mutating dispatcher helper for sample18 generated submit requests.

The helper converts normalized generated-submit payloads into DBAccess-bound `TaskCardData` field metadata but does not instantiate or execute `TaskCardDBAccess`. The generated submit route still returns HTTP 409 with `failure_code=generated_submit_disabled`.

## Changes

- Added `app_lab_sample18_task_board_generated_submit_dispatcher_dry_run()`.
- Added snake-case to `TaskCardData` property name mapping for dispatcher metadata.
- Added `dispatcher_result` metadata to valid blocked generated-submit responses.
- Extended the sample18 fast checklist with expected dispatcher-bound fields for create/update/complete.
- Extended focused PHPUnit coverage for create/update/complete dispatcher dry-run output.
- Extended HTTP smoke coverage to assert dispatcher dry-run state and no execution.

## Guarantees

- `mutation_enabled=false` remains unchanged.
- `executed=false` is returned by the dispatcher helper.
- Valid generated submit still returns `generated_submit_disabled`.
- Invalid, unknown, and CSRF-failed requests still fail before dispatcher success.
- Public runtime guarded click still reports blocked feedback.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- JSON parse: `sample/tutorials/sample18-mini-task-board-demo/golden/no-code-fast-contract-checklist.json`
- `make sample18-pack-runtime-test` (`4 tests`, `322 assertions`)
- `make sample18-http-runtime-smoke`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
- `make test` (`382 tests`, `12078 assertions`, `Skipped: 1`)
- `git diff --check`

## Next

Promote #584 to close this helper lane and decide whether the next slice should cover idempotency/audit inventory or mutation enablement gate coverage.
