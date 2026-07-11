# Sample18 Post Idempotency Route Integration Lane Closure

Date: 2026-07-10
Plan: #597
Status: DONE

## Accepted Capability

#596 is accepted as the sample18 generated submit idempotency route integration slice:

- valid blocked generated submit responses include `idempotency` metadata;
- first valid blocked request records an idempotency row;
- duplicate valid blocked request reuses the row and increments `duplicate_count`;
- no-app helper calls are explicitly skipped;
- repository failure is visible as `idempotency.status=failed`;
- method, CSRF, validation, and unknown-operation failures still do not persist idempotency rows;
- DBAccess mutation remains disabled.

## Decision

Promote mutation enablement gate preflight next, without enabling mutation.

Reason:

- route idempotency now has storage-backed recorded/duplicate behavior;
- audit append and idempotency failure visibility are already represented in route responses;
- the next risk is not the DBAccess call itself, but the exact gate conditions required before such a call may ever execute.

## Next

#598 Sample18 generated submit mutation enablement gate preflight:

- define the explicit enablement flag or configuration surface;
- define required audit/idempotency states before mutation dispatch can execute;
- define duplicate behavior once mutation is eventually enabled;
- define fail-closed tests proving DBAccess remains disabled when any gate is missing.
