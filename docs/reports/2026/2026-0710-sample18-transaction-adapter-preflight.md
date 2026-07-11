# Sample18 Transaction Adapter Preflight

Date: 2026-07-10
Plan: #638
Status: DONE

## Summary

#638 defines the route-unwired sample18 transaction adapter boundary around DBAccess invocation.

This preflight uses the shared all-success-or-failure UI/API contract: a user-facing success is valid only when every required step succeeds. If any required step fails, the route result must be failure. Physical cross-store atomicity remains future work, but the API must not present a partial result as success.

## Boundary

The transaction adapter should remain separate from the generated-submit route and from real `TaskCardDBAccess` invocation in its first slice.

Inputs:

- allowed `execution_guard`;
- planned `executor_coordination_plan`;
- DBAccess call adapter inputs;
- fake transaction adapter with begin, commit, and rollback hooks;
- fake DBAccess callable from the existing adapter helper path;
- explicit executor feature flag.

Outputs:

- `status`: `executed` or `failed`;
- `success`: boolean, true only when all required steps succeed;
- `transaction_status`: `not_started`, `begun`, `committed`, `rolled_back`, `begin_failed`, `commit_failed`, or `rollback_failed`;
- `dbaccess_status`: `not_called`, `executed`, or `failed`;
- `rolled_back`: boolean;
- `recording_status`: `not_started`, `planned_not_written`, `recorded`, or `failed`;
- stable `failure_code`;
- internal `recovery_required` metadata when a failure occurs after an app DB commit.

## Required Step Contract

For future route integration, success requires all of these route-level steps to succeed:

- request validation;
- authorization and CSRF;
- request audit append when required;
- idempotency admission / create;
- app DB transaction begin;
- DBAccess call;
- app DB transaction commit;
- execution audit append;
- idempotency execution outcome update.

Any required-step failure must return failure.

Important implication:

- DBAccess success followed by commit failure is failure.
- Commit success followed by execution audit or idempotency outcome update failure is also failure.
- The latter may require internal recovery metadata, but it is not user-facing success.

## First Helper Slice Test Matrix

#640 should add a route-unwired helper using fake transaction and fake DBAccess callables.

Required coverage:

- begin -> DBAccess success -> commit success returns `executed` / `success=true`;
- begin failure fails before DBAccess is called;
- DBAccess failure rolls back and returns failure;
- DBAccess exception rolls back and returns failure;
- rollback failure returns failure with `transaction_status=rollback_failed`;
- commit failure returns failure;
- unsafe/blocked guard or coordinator metadata fails before begin;
- all paths expose `transaction_status`, `dbaccess_status`, `rolled_back`, `recording_status`, `failure_code`, and `recovery_required`.

## Boundaries Kept

- No generated-submit route execution.
- No real `TaskCardDBAccess` method call.
- No real app DB transaction.
- No TaskCard mutation.
- No execution audit append or idempotency outcome update wiring.

## Verification

- `git diff --check`

## Next

#639 should define the cross-route all-success-or-failure policy as a shared design baseline, then #640 should implement the route-unwired transaction adapter helper.
