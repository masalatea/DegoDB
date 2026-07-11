# Sample18 Post Idempotency Repository Lane Closure

Date: 2026-07-10
Plan: #594
Status: DONE

## Accepted Capability

#593 is accepted as the storage-backed sample18 generated submit idempotency repository/helper slice:

- config DB table `sample18_generated_submit_idempotency_records` exists;
- SQLite bootstrap can create the table;
- repository create-or-reuse records first blocked submit attempts as `recorded`;
- duplicate dedupe keys return `duplicate` and increment `duplicate_count`;
- invalid inputs and repository failures fail closed;
- route integration and DBAccess mutation remain disabled.

## Decision

Promote route integration preflight next, before wiring the route to the repository.

Reason:

- the repository now has a stable storage and duplicate contract;
- the route already has audit append and failure visibility;
- route integration still needs an explicit ordering decision for audit append vs idempotency persistence, plus a skip matrix for method, CSRF, validation, and unknown-operation failures.

## Next

#595 Sample18 generated submit idempotency route integration preflight:

- define call ordering between audit append and idempotency create-or-reuse;
- define `idempotency` response metadata for recorded, duplicate, failed, and skipped results;
- define which route outcomes must not persist idempotency records;
- keep HTTP 409 `generated_submit_disabled`, `mutation_enabled=false`, and dispatcher `executed=false`.
