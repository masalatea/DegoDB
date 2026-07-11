# Sample18 Guarded Executor Coordination Preflight

Date: 2026-07-10
Plan: #630
Status: DONE

## Summary

#630 defines how the first sample18 guarded executor coordinator should combine execution guard, DBAccess call adapter, app DB transaction boundary, execution audit append, and idempotency outcome update.

This is a preflight only. No DBAccess method is called, no transaction is opened, no post-execution records are written, and no route executor is enabled.

## Coordination Boundary

The executor coordinator must be a separate helper from the route wrapper.

Inputs:

- normalized request and dispatcher metadata;
- request audit append result;
- idempotency create result;
- mutation gate;
- DBAccess execution plan;
- transaction plan;
- execution update-plan;
- execution guard;
- explicit executor feature flag.

Outputs:

- `status`: `planned`, `blocked`, or `failed`;
- `ready`;
- `will_open_transaction`;
- `will_call_dbaccess`;
- `will_write_execution_audit`;
- `will_update_idempotency_execution`;
- `app_db_transaction_boundary`;
- `config_db_persistence_boundary`;
- ordered steps;
- fail-closed reasons.

## Ordering Model

The first mutating executor must eventually coordinate these steps:

1. Re-check `execution_guard` immediately before execution.
2. Verify the executor feature flag is enabled.
3. Open the sample18 application DB transaction.
4. Call exactly one allowlisted DBAccess method.
5. Classify the DBAccess result into executed / failed / rolled_back.
6. Commit or rollback the application DB transaction.
7. Append execution audit event in config DB audit storage.
8. Update generated-submit idempotency execution outcome in config DB idempotency storage.
9. Return response metadata that makes any partial failure visible.

## Cross-Store Boundary

The application DB transaction and config DB persistence are not currently one atomic transaction.

Implication:

- The first mutating executor must not claim all-or-nothing semantics across task-card mutation, audit append, and idempotency update.
- Response metadata must distinguish `app_db_transaction_status`, `execution_audit_status`, and `idempotency_execution_update_status`.
- A DBAccess success followed by audit/idempotency failure is a partial-success state, not a clean rollback, unless a compensating strategy is explicitly added later.

For this reason, the next slice should remain non-mutating and model the coordination plan before any DBAccess call is implemented.

## Fail-Closed Matrix

Coordinator planning must block or fail for:

- executor feature flag disabled;
- execution guard not allowed;
- duplicate idempotency state;
- missing request audit key or dedupe key;
- DBAccess class/function mismatch;
- wrong DB handle;
- unsafe metadata that already marks a write or DBAccess call as enabled;
- missing execution audit append helper inputs;
- missing idempotency outcome update helper inputs.

## Decision

Promote a dry-run coordinator plan helper next.

Reason:

- The persistence helpers are covered independently.
- The remaining risk is ordering and partial-failure semantics across app DB and config DB.
- A non-mutating coordinator plan helper lets route-level and helper-level tests stabilize that boundary before execution is enabled.

## Next

#631 should add a non-mutating coordinator plan helper that models DBAccess call, app-db transaction, execution audit append, and idempotency outcome update ordering without opening transactions, calling DBAccess, or writing post-execution records.

## Verification

- `git diff --check`
