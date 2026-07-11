# Sample18 Post-Commit Recording DB-Backed Coverage First Slice

Date: 2026-07-10
Status: FIRST_SLICE_DONE
Plan: #661

## Summary

Added route-unwired integration coverage that feeds a committed generated-submit transaction result into the post-commit recording adapter and uses real config DB persistence for both required recorders.

The generated-submit route still does not execute DBAccess mutation. This slice only proves the DB-backed post-commit recording path can be composed after a committed transaction result.

## Coverage

- Builds a valid generated-submit metadata chain with request audit append, idempotency create-or-reuse, mutation gate, execution plan, transaction plan, execution update plan, execution guard, and executor coordination.
- Produces a committed transaction result through `app_lab_sample18_task_board_generated_submit_transaction_adapter()`.
- Calls `app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter()` with:
  - `app_lab_sample18_task_board_generated_submit_append_execution_audit_event()`
  - `app_lab_sample18_generated_submit_idempotency_update_execution_outcome()`
- Verifies the execution audit event is persisted and linked to the original request audit event / dedupe key.
- Verifies the idempotency record is updated from `blocked` to `executed` and stores execution status, result code, transaction status, and execution audit event key.
- Verifies a missing idempotency record after audit append returns `status=failed`, `recording_status=failed`, `recovery_required=true`, and `recovery_reason=post_commit_recording_failed`.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
  - OK: 23 tests, 1288 assertions.
- `make test`
  - OK, but incomplete, skipped, or risky tests.
  - Tests: 407, Assertions: 13133, Skipped: 1.

## Next

Close this lane in #662 and choose whether route feature-flag integration preflight, recovery/repair preflight, or additional route-unwired failure coverage should be promoted next.
