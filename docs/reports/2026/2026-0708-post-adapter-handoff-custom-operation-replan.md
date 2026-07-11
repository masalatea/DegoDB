# Post Adapter Handoff Custom Operation Replan

Date: 2026-07-08

Status: `DONE`

## Summary

#457 chooses the next lane after the custom operation metadata / adapter handoff stack review.

The next step should not enable execution yet. The smallest safe continuation is to inventory the route boundary for one custom operation, including policy, authorization, CSRF, audit, idempotency, and adapter expectations.

## Selected Next Work

**Policy/auth/CSRF/audit route inventory for one custom operation.**

Recommended first operation: `review_source_output_artifact`.

Why this one first:

- It is already visible as a disabled operator action in the Mtool Source Output review probe.
- It can remain operator-facing and non-executing during the inventory.
- It exercises the same boundary shape needed by later `request_publish`, `approve`, or `rollback` operations.
- It forces the route contract to name policy, permission, CSRF, audit, and idempotency expectations before implementation.

## Scope For The Next Slice

- Choose the first route key and method shape.
- Define the permission guard and policy key.
- Define CSRF expectations for browser-origin execution.
- Define audit event names and minimum event payload.
- Define idempotency / duplicate request behavior.
- Define failure modes for unavailable, unauthorized, stale artifact, missing candidate, and disabled operation states.
- Define generated HTML / React bridge adapter handoff expectations.

## Out Of Scope

- No executable route implementation.
- No build, publish, review-request, approval, rollback, or mutation behavior.
- No custom React component execution.
- No server-side operation dispatch.
- No change to disabled generated operator action buttons.

## Follow-Up Candidates

- #458: `review_artifact` route boundary inventory.
- Later: metadata update to attach the inventory result to `custom_operations`.
- Later: disabled UI wording polish for policy/CSRF/audit readiness.
- Later: execution route implementation only after the boundary is explicit.
