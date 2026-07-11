# Sample18 Post-Idempotency Audit Helper Lane Closure

Date: 2026-07-10
Plan: #587
Status: DONE

## Accepted Capability

#586 is accepted as the dry-run idempotency/audit helper slice:

- valid generated submit responses expose stable `dedupe_key_preview`;
- payload fingerprints are canonical across field order changes;
- `audit_event_preview` contains dispatcher, failure, ignored field, normalized payload, and bound field metadata;
- no audit append, persistence, outbox enqueue, or DBAccess mutation is performed;
- focused PHPUnit, HTTP smoke, public runtime browser smoke, and `make test` passed.

## Decision

Promote audit append persistence next, before mutation enablement gate coverage.

Reason:

- the route can now construct the exact audit event payload without side effects;
- appending blocked valid generated submit attempts gives traceability while still avoiding mutation;
- mutation enablement should wait until audit append success/failure behavior is tested.

## Next

#588 Sample18 generated submit blocked audit append first slice:

- append audit rows for valid blocked generated submit requests;
- keep HTTP 409 `generated_submit_disabled`;
- keep `mutation_enabled=false` and dispatcher `executed=false`;
- do not append audit rows for method, CSRF, validation, or unknown operation failures in the first slice unless explicitly covered;
- add focused PHPUnit / HTTP smoke coverage for audit append success and no mutation.
