# Sample18 Post Audit Failure Visibility Lane Closure

Date: 2026-07-10
Plan: #591
Status: DONE

## Accepted Capability

#590 is accepted as the sample18 generated submit audit append failure visibility slice:

- valid generated submit requests remain HTTP 409 `generated_submit_disabled`;
- dispatcher and top-level mutation flags remain disabled;
- audit append failures are visible through `audit_append.status=failed`;
- failure responses keep `item=[]`, `skipped=false`, and a non-empty `error`;
- no DBAccess mutation, idempotency persistence, or generated submit acceptance is enabled.

## Decision

Promote idempotency persistence preflight before mutation enablement gate coverage.

Reason:

- generated submit already has deterministic dedupe keys and audit append;
- mutation enablement should not proceed until duplicate handling has a storage-backed contract;
- the next safe step is to define whether duplicate blocked requests are recorded, reused, or only reported before adding any persistence code.

## Next

#592 Sample18 generated submit idempotency persistence preflight:

- define storage ownership and schema boundary for generated submit idempotency;
- define first duplicate response shape for blocked valid requests;
- define audit interaction between first request, duplicate request, and append failure;
- keep DBAccess mutation disabled.
