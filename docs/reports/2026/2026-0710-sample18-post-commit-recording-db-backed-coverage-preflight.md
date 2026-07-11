# Sample18 Post-Commit Recording DB-Backed Coverage Preflight

Date: 2026-07-10
Status: DONE
Plan: #660

## Context

#658 proved that the generated sample18 DBAccess runtime, transaction binding callables, and real-compatible `TaskCardDBAccess::InsertTaskCard` can commit and roll back against SQLite/PDO while the generated-submit route remains disabled.

The next risk is the required post-commit recording step. A committed DBAccess mutation must only be treated as fully successful when both the execution audit append and idempotency execution outcome update are recorded. This follows the shared all-success-or-failure policy: UI/API callers should see success only when every required step succeeds; any recording failure returns a failure with recovery metadata, even though the DB transaction has already committed.

## Boundary

The first DB-backed coverage should stay route-unwired and should not enable generated-submit route execution.

It should connect the existing `app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter()` to real persistence-backed recorders:

- `app_lab_sample18_task_board_generated_submit_append_execution_audit_event()` for the execution audit event.
- `app_lab_sample18_generated_submit_idempotency_update_execution_outcome()` for the idempotency execution outcome.

The test setup should reuse the existing generated-submit metadata chain: normalized request, dispatcher metadata, blocked request audit append, idempotency create-or-reuse record, mutation gate, execution plan, transaction plan, execution update plan, execution guard, coordination, and a committed transaction result.

## Acceptance

The first slice should prove:

- A committed transaction result plus ready execution metadata records an execution audit event and updates the existing idempotency record to `executed`.
- The idempotency metadata stores the execution status, result code, transaction status, and execution audit event key.
- The execution audit remains linked to the original request audit event and dedupe key.
- If one required post-commit recorder fails, the adapter returns `status=failed`, `recording_status=failed`, `recovery_required=true`, and `recovery_reason=post_commit_recording_failed`.
- The generated-submit HTTP route remains blocked/disabled for execution; this is coverage only, not route feature-flag integration.

## Next

Promote #661 as the first route-unwired DB-backed post-commit recording coverage slice.
