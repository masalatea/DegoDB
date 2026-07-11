# Post DB-Backed Post-Commit Recording Coverage Lane Closure

Date: 2026-07-10
Status: DONE
Plan: #662

## Accepted

#661 is accepted as the current route-unwired proof that committed generated-submit execution can be followed by DB-backed post-commit recording.

Accepted capability:

- A committed transaction result can flow through `app_lab_sample18_task_board_generated_submit_post_commit_recording_adapter()`.
- The execution audit recorder can persist a `sample18.generated_submit.executed` audit event linked to the original request audit event and dedupe key.
- The idempotency outcome recorder can update the existing generated-submit idempotency record to `executed` with execution status, result code, transaction status, and execution audit event key.
- A post-commit idempotency failure is surfaced as user-facing failure/recovery metadata instead of being treated as success.

## Decision

Promote route feature-flag integration preflight next.

Reasoning:

- The route-unwired chain now covers guard, coordination, transaction adapter, generated runtime transaction support, real-compatible DBAccess invocation, DB-backed transaction binding, and DB-backed post-commit recording.
- The next risk is not another isolated helper but the exact route-level enablement boundary: disabled by default, explicit feature flag, all-success-or-failure response semantics, and failure/recovery mapping.
- Recovery/repair tooling remains important, but it should be planned against the route-level response and persisted metadata contract rather than before that contract is defined.

## Next

Promote #663: sample18 generated-submit route feature-flag integration preflight.
