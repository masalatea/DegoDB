# Sample18 DBAccess Call Adapter Preflight

Date: 2026-07-10
Plan: #635
Status: DONE

## Summary

#635 defines the smallest sample18 DBAccess call adapter boundary before any route execution is enabled.

This is a preflight only. No generated-submit route is made executable, no real `TaskCardDBAccess` call is made, no app DB transaction is opened, and no TaskCard row is mutated by this plan.

## Current Baseline

- Valid generated-submit responses expose the full non-mutating metadata chain through `executor_coordination_plan`.
- The route still passes `executorEnabled=false`, so coordinator metadata remains blocked even for ready/planned guard inputs.
- Supported generated-submit operations are `create_task_card`, `update_task_card`, and `complete_task_card`.
- The dry-run dispatcher already produces `TaskCardDBAccess`, an allowlisted DBAccess function, `TaskCardData`, and `TaskCardObj` bound fields.
- Transaction, execution audit append, and idempotency outcome update are modeled separately and remain route-unexecuted.

## Adapter Boundary

The first adapter helper should be separate from the route wrapper and from the coordinator.

Inputs:

- `execution_guard` with `status=allowed`, `ready=true`, `will_call_dbaccess=true`, and stable dedupe/audit linkage;
- `executor_coordination_plan` with `status=planned`, `ready=true`, and ordered call metadata;
- dispatcher metadata with `db_access_class=TaskCardDBAccess`, `data_object=TaskCardData`, and allowlisted `db_access_function`;
- normalized operation payload;
- explicit executor feature flag;
- injected callable DBAccess invoker for tests.

Outputs:

- `status`: `executed`, `failed`, or `skipped`;
- `executed`: boolean;
- `db_access_class`, `db_access_function`, `data_object`, and `operation_key`;
- `result_code`: stable adapter-level result code;
- `failure_code`: stable failure code when not executed;
- `error`: sanitized error string for unexpected exceptions;
- `rows_affected` / `insert_id` only when present in adapter result metadata;
- `request_audit_event_key` and `dedupe_key` carried through.

## Allowed Operation Mapping

- `create_task_card` maps to `TaskCardDBAccess::InsertTaskCard`.
- `update_task_card` maps to `TaskCardDBAccess::UpdateTaskCard`.
- `complete_task_card` maps to `TaskCardDBAccess::CompleteTaskCard`.
- Reopen and delete remain out of scope for generated-submit execution until their contracts are promoted from disabled no-code action metadata into generated-submit contracts.

## Fail-Closed Rules

The helper must skip or fail without invoking the injected callable when:

- executor feature flag is disabled;
- execution guard is not allowed or not ready;
- coordinator plan is not planned or not ready;
- operation key is unsupported;
- DBAccess class/function/data object does not match the allowlist;
- `TaskCardObj` payload is missing or malformed;
- dedupe key or request audit event key is missing;
- metadata already indicates an unsafe partial execution state.

The helper must return `failed` after invoking the callable when:

- the callable returns a failed result shape;
- the callable throws an exception;
- the callable returns a malformed result.

## Test Matrix For First Slice

The next code slice should use an injected fake callable only.

Required coverage:

- allowed create/update/complete inputs invoke the callable exactly once and return `executed`;
- feature-flag-disabled, blocked guard, blocked coordinator, duplicate, missing dedupe, missing request audit key, unsupported operation, class/function mismatch, and malformed payload do not invoke the callable;
- failed callable result returns stable `failed` metadata;
- thrown exception returns stable `failed` metadata with sanitized error;
- route responses remain unwired and non-executing.

## Decision

Promote a route-unwired DBAccess call adapter helper first slice next.

Reason:

- The route-visible metadata chain is stable enough to feed a call adapter.
- Testing against an injected fake callable proves the execution boundary without mutating real TaskCard rows.
- Real DBAccess invocation and route wiring should remain separate later commits.

## Next

#636 should add the route-unwired adapter helper and focused tests using an injected fake callable only.

## Verification

- `git diff --check`
