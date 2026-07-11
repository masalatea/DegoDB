# Sample18 Generated Submit Route Feature-Flag Integration Preflight

Date: 2026-07-10
Status: DONE
Plan: #663

## Context

The route-unwired execution chain is now covered through:

- generated-submit validation, dispatcher dry-run, request audit append, and idempotency create-or-reuse;
- mutation gate, DBAccess execution plan, transaction plan, execution update plan, execution guard, and executor coordination;
- real-compatible DBAccess invocation and generated runtime transaction binding;
- DB-backed post-commit execution audit append and idempotency outcome update.

The current generated-submit HTTP route still always returns HTTP 409 blocked and builds executor coordination with `executorEnabled=false`.

## Policy

The route should follow the shared all-success-or-failure policy.

- If the explicit executor feature flag is off, the route remains the current non-mutating 409 blocked response.
- If the flag is on, the route may execute only after request audit append and idempotency create-or-reuse produce a fresh `recorded` request.
- A successful user-visible execution requires all required steps to succeed: transaction begin, DBAccess invocation, transaction commit, execution audit append, and idempotency outcome update.
- DBAccess failure rolls back and returns a user-facing failure response.
- Commit failure returns a user-facing failure with `recovery_required=true` and `recovery_reason=commit_status_unknown`.
- Post-commit recording failure returns a user-facing failure with `recovery_required=true` and `recovery_reason=post_commit_recording_failed`.
- Duplicate idempotency records remain non-executing, even when the executor flag is on.

## First Slice Boundary

#664 should wire the generated-submit route to the existing route execution plan only under the explicit feature flag.

Implementation boundary:

- Preserve current default behavior when `sample18_generated_submit_mutation_enabled` / `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED` is not enabled.
- Build executor coordination with the same feature flag value used by the mutation gate.
- When coordination is ready, call `app_lab_sample18_task_board_generated_submit_route_execution_plan()`.
- Use transaction binding callables from `app_lab_sample18_task_board_generated_submit_transaction_binding_callables()` and a generated runtime DB handle for the sample18 DBAccess path.
- Use DB-backed execution audit and idempotency outcome recorders.
- Return route payload metadata that includes transaction result and post-commit recording result for both success and failure.

Test boundary:

- Route default remains blocked/non-mutating.
- Flag-on fresh valid request executes once and returns success only after post-commit recording succeeds.
- TaskCard row is persisted by DBAccess execution.
- Execution audit and idempotency outcome are persisted.
- Duplicate replay with the flag on does not execute.
- At least one route-level failure path proves failure/recovery metadata is surfaced.

## Next

Promote #664 as the first route-level feature-flag execution slice.
