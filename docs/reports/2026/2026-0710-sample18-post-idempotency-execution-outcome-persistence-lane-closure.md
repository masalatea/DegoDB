# Sample18 Post Idempotency Execution Outcome Persistence Lane Closure

Date: 2026-07-10
Plan: #627
Status: DONE

## Summary

#627 closes the sample18 idempotency execution outcome persistence lane.

#626 is accepted as the current idempotency execution outcome persistence baseline before any DBAccess execution is enabled.

## Accepted Capability From #626

- Existing generated-submit idempotency records can be updated with execution outcome metadata.
- Request metadata, request identity, and duplicate count are preserved.
- Execution outcome is stored under `metadata.execution`.
- Table-level `result` and `failure_code` can reflect final execution outcome.
- Missing record, invalid execution status, invalid execution metadata, and duplicate replay fail closed.
- DBAccess mutation, transaction opening, execution audit write, and route executor wiring remain disabled.

## Decision

Promote execution audit append persistence next.

Reason:

- The idempotency side can now persist final execution outcome.
- The matching execution audit event append path must be covered before the executor coordinates both writes.
- Adding audit append persistence next keeps the work independent of DBAccess mutation and transaction handling.

## Next

#628 should add a repository/helper path to append execution audit events for planned execution outcomes.

Required boundaries:

- use existing audit event storage;
- include request audit event linkage, dedupe key, DBAccess class/function, execution status, transaction status, result code, and optional result metadata;
- fail closed for missing request audit key, missing dedupe key, invalid result, and invalid metadata;
- do not open transactions, call DBAccess, update idempotency, or wire the route executor.

## Verification

- `git diff --check`
