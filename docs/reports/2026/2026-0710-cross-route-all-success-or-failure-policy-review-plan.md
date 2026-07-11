# Cross-Route All Success Or Failure Policy Review Plan

Date: 2026-07-10
Plan: #639
Status: DONE

## Summary

#639 records a cross-route review plan for the all-success-or-failure UI/API contract.

The sample18 transaction adapter preflight should not remain a sample-only policy. Mutation and execution routes should converge on the same user-facing rule: success is returned only when every required operation step succeeds. Any required-step failure fails closed, even when physical cross-store atomicity is not yet complete.

## Policy Direction

- User-facing success means every required step succeeded.
- Required steps include validation, authorization, CSRF, idempotency admission, audit admission when required, app data mutation, transaction commit, execution audit, and idempotency outcome update as applicable to the route.
- A required-step failure must return failure, not partial success.
- Physical cross-store atomicity gaps are internal failure/recovery metadata.
- Post-commit recording failure is still user-facing failure.
- Duplicate/retry behavior must be fail-closed unless a dedicated replay/recovery path is explicitly designed.

## Review Scope

Review the policy against:

- sample18 generated submit execution;
- review workflow request persistence;
- source-output review/publish custom operation routes;
- future generated no-code action execution routes;
- any route that combines user-facing mutation with audit/idempotency/config DB recording.

## Exit Condition

- A shared policy document or current design section defines the common success/failure contract. Done: `docs/execution-success-policy.md`.
- Active route plans reference the common policy instead of repeating sample-specific wording. Done for current plan boundary.
- Sample18 #638 uses the shared policy as its preflight baseline. Done for the #638 / #640 current plan chain.
- Any existing route that intentionally differs is listed with a reason.

## Verification

- Docs-only: `git diff --check`
