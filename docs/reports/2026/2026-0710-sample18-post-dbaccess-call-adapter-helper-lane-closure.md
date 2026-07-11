# Sample18 Post DBAccess Call Adapter Helper Lane Closure

Date: 2026-07-10
Plan: #637
Status: DONE

## Summary

#637 closes the route-unwired sample18 DBAccess call adapter helper lane.

#636 is accepted as the current adapter boundary for classifying DBAccess call outcomes with an injected fake callable before any real route execution is enabled.

## Accepted Capability From #636

- `app_lab_sample18_task_board_generated_submit_dbaccess_call_adapter` validates normalized request, dispatcher, execution guard, and executor coordination metadata.
- The helper invokes only an injected callable and is not wired into the generated-submit route.
- Focused coverage proves create/update/complete inputs invoke the fake callable exactly once.
- Disabled executor flag, blocked guard, blocked coordination plan, DBAccess allowlist mismatch, and malformed payload skip without invoking the callable.
- Failed fake result and thrown exception return stable failed adapter metadata.

## Decision

Promote transaction adapter preflight next.

Reason:

- The DBAccess call boundary is now testable without real mutation.
- Route integration would still be premature without a covered begin/commit/rollback adapter boundary.
- Real DBAccess invocation should wait until transaction behavior and rollback/failure metadata are explicit.

## Next

#638 should define a route-unwired transaction adapter boundary around DBAccess invocation.

Required boundaries:

- no generated-submit route execution;
- no real TaskCard mutation;
- fake transaction adapter tests for begin, commit, rollback, begin failure, commit failure, rollback failure, DBAccess failure, and unexpected exception;
- UI/API success requires every required step to succeed: request audit, idempotency create, app DB transaction begin, DBAccess call, app DB commit, execution audit append, and idempotency execution outcome update;
- any required step failure must produce a failure route result, including post-commit recording failures;
- physical cross-store atomicity remains future work, but the user-facing contract should not expose partial success as success;
- explicit metadata for `transaction_status`, `dbaccess_status`, `rolled_back`, `recording_status`, and internal failure codes such as `post_commit_recording_failed`;
- no execution audit or idempotency update wiring yet.

## Verification

- `git diff --check`
