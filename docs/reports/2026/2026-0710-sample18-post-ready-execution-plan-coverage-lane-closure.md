# Sample18 Post Ready Execution-Plan Coverage Lane Closure

Date: 2026-07-10
Plan: #609
Status: DONE

## Summary

#609 closes the sample18 route-level ready execution-plan coverage lane.

#608 is accepted as the current route-level proof that a fresh flag-on request can expose ready/planned metadata while remaining non-mutating.

## Accepted Capability From #608

- Fresh flag-on valid generated-submit route response is covered.
- The route still returns HTTP 409 `generated_submit_disabled`.
- Audit append and idempotency record exactly one fresh request.
- `mutation_gate.status=ready`.
- `dbaccess_execution_plan.status=planned`.
- `mutation_enabled=false`, `executed=false`, and `transaction=not_opened` are preserved.
- DBAccess mutation remains disabled.

## Decision

Promote transaction boundary preflight next.

Reason:

- The route now has disabled, duplicate, failed, and ready/planned metadata coverage.
- The remaining risk before any execution enablement is the transaction and rollback contract.
- Execution audit/idempotency update behavior depends on the transaction decision, so it should follow the transaction boundary preflight.

## Next

#610 should define the DBAccess transaction boundary before implementation.

Required decisions:

- which PDO/application DB handle is eligible for execution;
- when the transaction opens and closes;
- rollback behavior for DBAccess exceptions and invalid affected-row outcomes;
- post-execution audit update shape;
- idempotency record update shape;
- response shape for planned, executed, failed, duplicate, and rollback outcomes;
- tests required before any generated-submit DBAccess execution is enabled.

## Verification

- `git diff --check`
