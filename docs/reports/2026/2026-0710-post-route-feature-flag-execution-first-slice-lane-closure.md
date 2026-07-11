# Post Route Feature-Flag Execution First Slice Lane Closure

Date: 2026-07-10
Status: DONE
Plan: #665

## Accepted

#664 is accepted as the first route-level generated-submit execution slice.

Accepted capability:

- The generated-submit route remains blocked by default.
- The existing mutation-gate metadata-only mode remains available when only `sample18_generated_submit_mutation_enabled` is enabled.
- A separate explicit executor flag, `sample18_generated_submit_executor_enabled`, enables route execution only when transaction callables are injected.
- A fresh valid request can execute generated `TaskCardDBAccess` inside a transaction, commit, append execution audit, update idempotency outcome, and return HTTP 200 / `result=executed`.
- Duplicate replay remains non-executing even with the executor flag enabled.

## Decision

Promote route-level failure/recovery coverage next.

Reasoning:

- The success path now exists, so the next risk is user-facing behavior when one required step fails.
- The shared all-success-or-failure policy needs route-level proof, not only helper-level proof.
- Recovery metadata is especially important now that the route can commit DBAccess before post-commit recording.
- Real sample runtime default binding and UI success/error rendering should follow after the failure surface is stable.

## Next

Promote #666: sample18 route execution failure/recovery coverage first slice.
