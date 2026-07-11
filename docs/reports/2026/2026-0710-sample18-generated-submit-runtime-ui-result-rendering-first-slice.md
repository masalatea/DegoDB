# Sample18 Generated-Submit Runtime UI Result Rendering First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #674

## Context

#673 defined the rendering contract for guarded generated-submit route responses. The runtime still treated most network-submit responses as either blocked or generic error, even though the route now distinguishes executed, blocked/duplicate, ordinary failure, and recovery-required outcomes.

## Changes

- Added guarded generated-submit result helpers in `mtool/app/no_code_runtime.php`:
  - `guardedSubmitRecoveryReason()`
  - `guardedSubmitResultState()`
  - `writeGuardedSubmitResultAttributes()`
- Mapped `ok=true` and `result=executed` to UI state `success`.
- Preserved `result=blocked` as UI state `blocked`, with duplicate replay copy when `idempotency.status=duplicate`.
- Mapped recovery metadata from `route_execution`, `transaction_result`, or `post_commit_recording` to UI state `recovery-required`.
- Exposed stable `data-action-recovery-required` and `data-action-recovery-reason` attributes on the generated action button and feedback region.
- Updated the runtime dispatch record so `ok` follows the payload and `executed=true` only when the route reports `result=executed`.
- Added lightweight HTML contract assertions in `NoCodeRuntimeTest` without adding a headless browser dependency.

## Verification

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/NoCodeRuntimeTest.php`
  - OK (13 tests, 321 assertions)

## Next

Promote #675 as a lane closure to decide whether the next small step should harden production runtime config, refine route response HTTP/status semantics, or add broader browser smoke coverage.
