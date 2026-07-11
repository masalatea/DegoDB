# Sample18 Post Blocked Audit Append Lane Closure

Date: 2026-07-10
Plan: #589
Status: DONE

## Accepted Capability

#588 is accepted as the first sample18 generated submit audit append slice:

- valid blocked generated submit requests append `sample18.generated_submit.requested` audit rows;
- route responses expose `audit_append.status=appended` with the appended audit item;
- actor identity is filled from the authenticated principal;
- CSRF, validation, unknown-operation, and method failures remain fail-closed without audit append in this slice;
- DBAccess mutation remains disabled and valid submits still return HTTP 409 `generated_submit_disabled`.

## Decision

Promote audit append failure visibility before duplicate/idempotency persistence or mutation enablement gate coverage.

Reason:

- valid blocked audit append is now a real route side effect;
- mutation enablement should not proceed while append failure behavior is only implicitly covered by the generic append result;
- failure visibility is a narrow slice and can keep the route blocked and mutation-free.

## Next

#590 Sample18 generated submit audit append failure visibility coverage:

- add focused coverage for audit append failure response shape;
- keep HTTP 409 `generated_submit_disabled` for valid blocked submits;
- keep `mutation_enabled=false` and dispatcher `executed=false`;
- do not enable duplicate/idempotency persistence or DBAccess mutation.
