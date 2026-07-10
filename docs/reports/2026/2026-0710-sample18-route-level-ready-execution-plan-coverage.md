# Sample18 Route-Level Ready Execution-Plan Coverage

Date: 2026-07-10
Plan: #608
Status: FIRST_SLICE_DONE

## Summary

#608 adds route-level coverage for the fresh flag-on generated-submit path.

The test proves the route can expose `mutation_gate.ready` and planned `dbaccess_execution_plan` metadata while still returning HTTP 409 and executing no mutation.

## Covered

- Fresh flag-on valid generated-submit request returns HTTP 409 `generated_submit_disabled`.
- Audit append is `appended`.
- Idempotency is `recorded`, `created=true`, and duplicate count is `0`.
- `mutation_gate.status=ready`.
- `mutation_gate.mutation_enabled=false`.
- `mutation_gate.executed=false`.
- `dbaccess_execution_plan.status=planned`.
- `dbaccess_execution_plan.mutation_enabled=false`.
- `dbaccess_execution_plan.executed=false`.
- `dbaccess_execution_plan.transaction=not_opened`.
- Audit and idempotency persistence each record exactly one fresh request.

## Boundaries Kept

- DBAccess mutation is not executed.
- No transaction is opened.
- The route still returns HTTP 409 for valid generated-submit requests.
- Transaction preflight remains deferred.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (9 tests, 600 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 391, Assertions: 12408, Skipped: 1.`
- `git diff --check`

## Next

#609 should close the ready execution-plan coverage lane and decide whether transaction boundary preflight, execution audit update preflight, or route integration hardening should be promoted next.
