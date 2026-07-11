# Sample18 Post Execution Update-Plan Helper Lane Closure

Date: 2026-07-10
Plan: #617
Status: DONE

## Summary

#617 closes the sample18 execution update-plan helper lane.

#616 is accepted as the current non-mutating post-execution audit/idempotency update-plan baseline before any guarded DBAccess execution is enabled.

## Accepted Capability From #616

- A non-mutating `execution_update_plan` helper derives execution audit and idempotency update metadata from a planned transaction plan.
- The helper preserves `will_write_audit=false`, `will_update_idempotency=false`, and `will_execute=false`.
- Planned execution audit metadata includes the execution event type, target dedupe key, request audit event linkage, result, transaction status, and DBAccess class/function metadata.
- Planned idempotency update metadata includes the dedupe key, execution status, result code, and transaction status.
- Blocked transaction plans carry `transaction_plan_not_ready` and source reasons.
- Unsafe transaction plans that imply mutation fail closed with `transaction_plan_not_metadata_only`.
- Missing dedupe linkage blocks the update plan with `dedupe_key_missing`.
- The generated-submit route remains unwired to `execution_update_plan`, and DBAccess mutation remains disabled.

## Decision

Promote execution update-plan route metadata integration next.

Reason:

- The helper is now covered, but route responses do not yet expose it.
- Route-level metadata should be observable before guarded DBAccess execution is considered.
- Persistence update schema or guarded execution would be premature until valid route responses prove the full request -> audit/idempotency -> gate -> execution plan -> transaction plan -> execution update-plan chain.

## Next

#618 should wire non-mutating `execution_update_plan` metadata into valid generated-submit route responses.

Required boundaries:

- Preserve HTTP 409 `generated_submit_disabled`.
- Preserve `mutation_enabled=false`, `executed=false`, `will_execute=false`, and transaction-not-opened metadata.
- Do not write execution audit rows.
- Do not update idempotency execution state.
- Omit `execution_update_plan` for method, CSRF, validation, and unknown-operation failures.
- Cover duplicate, failed, blocked, and ready/planned route outcomes.

## Verification

- `git diff --check`
