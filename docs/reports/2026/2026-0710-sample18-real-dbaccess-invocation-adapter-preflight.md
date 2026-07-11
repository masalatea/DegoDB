# Sample18 Real DBAccess Invocation Adapter Preflight

Date: 2026-07-10
Plan: #648
Status: DONE

## Summary

#648 defines the route-unwired real `TaskCardDBAccess` invocation adapter boundary for sample18 generated-submit execution.

This is a preflight only. It does not enable generated-submit route execution, default-on executor behavior, real route mutation, or post-commit recording from the HTTP route.

## Adapter Boundary

The first real invocation adapter must remain route-unwired and must consume the existing normalized request, dispatcher result, execution guard, and executor coordination plan.

Required inputs:

- normalized generated-submit request with an allowlisted operation key;
- dispatcher metadata with `TaskCardDBAccess`, the expected operation method, `TaskCardData`, and `method_arguments.TaskCardObj`;
- an allowed execution guard and ready executor coordination plan;
- explicit executor enablement;
- an explicit in-transaction dependency, such as an already-created DBAccess instance or factory bound to the app DB transaction context.

The adapter must not own transaction begin, commit, or rollback. It may assert that it was called inside the planned app DB transaction boundary, but transaction lifecycle stays with the transaction adapter.

## Object Construction

The adapter must build a `TaskCardData`-compatible object from `method_arguments.TaskCardObj`.

Allowlisted mapping:

- `create_task_card` calls `InsertTaskCard($TaskCardObj)`;
- `update_task_card` calls `UpdateTaskCard($TaskCardObj)`;
- `complete_task_card` calls `CompleteTaskCard($TaskCardObj)`.

The DTO construction should copy only known `TaskCardData` fields:

- `id`;
- `title`;
- `body`;
- `status`;
- `assignedTo`;
- `priority`;
- `dueDate`;
- `completedAt`;
- `updatedAt`.

Missing payload, wrong DBAccess class, wrong method, wrong data object, non-ready guard, or non-ready coordination plan must fail closed before invocation.

## Result Normalization

Real DBAccess methods currently return the underlying DB execute result. The adapter must normalize this into the existing `dbaccess_call_adapter` shape.

Required result handling:

- successful DBAccess call returns `status=executed`, `executed=true`, `result_code=dbaccess_executed`;
- DB/driver error-like results return `status=failed`, `result_code=dbaccess_failed`, and a stable `failure_code`;
- non-array / object results must be handled deliberately, not by accidental truthiness;
- thrown exceptions normalize to `status=failed`, `result_code=dbaccess_exception`, `failure_code=dbaccess_exception`;
- optional metadata such as `rows_affected` or `insert_id` may be included when the DB layer exposes it.

The UI/API all-success-or-failure policy still applies: DBAccess success is only one required step, not the final user-facing success by itself.

## First Slice

#649 should add a route-unwired helper that:

- constructs an allowlisted `TaskCardData` object from dispatcher method arguments;
- invokes only the expected method on a supplied real-compatible `TaskCardDBAccess` object or factory;
- requires an explicit `in_transaction` / transaction context marker;
- normalizes success, DB error, malformed result, missing method, and exception outcomes;
- leaves the generated-submit route blocked by default.

Focused tests can use a fake object that has the same method names as `TaskCardDBAccess`; an isolated sample18 DB-backed test can follow after the adapter shape is stable.

## Verification

- `git diff --check`
