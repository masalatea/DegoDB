# 2026-0702 Post-Public-Delivery-Closure No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Rollback workflow polish first slice** as the next no-code product-facing slice.

The public delivery lane is closed for the minimum route/storage capability. The remaining product-facing gap is clarity: rollback already works by selecting an older approved candidate as current, but the operator/admin UI should name that behavior explicitly and clarify that alias routes do not automatically move with current.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Rollback workflow polish first slice | 0.5 - 1 day | Selected. Clarifies current rollback semantics in the existing candidate/alias UI. |
| Alias lifecycle audit trail | 1 - 2 days | Deferred until auditability is a concrete operations need. |
| Public delivery browser smoke | 0.5 - 1 day | Deferred until public URL fixtures are promoted as stable browser-smoke targets. |

## Boundary

In scope:

- label older approved candidates as rollback targets for current;
- clarify that artifact-key URLs remain unchanged;
- clarify that alias rows do not automatically follow current rollback;
- static contract coverage and docs.

Out of scope:

- new rollback storage;
- rollback event stream;
- alias automatic follow-current mode;
- new public routes;
- push.

## Verification

Implementation selected immediately after this planning step.
