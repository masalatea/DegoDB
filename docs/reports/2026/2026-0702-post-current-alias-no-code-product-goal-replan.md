# 2026-0702 Post-Current-Alias No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Candidate event display polish first slice** as the next product-facing code slice.

The public `current` runtime alias is now visible, but the operator/admin page still hides the transition events that justify how a candidate reached review, approved, or rejected status. Showing those existing append-only events is the smallest useful continuation before larger public-delivery decisions such as cache/version policy, explicit revision selection, rollback, or custom alias storage.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Candidate event display polish first slice | 0.5 - 1 day | Selected. Reuses existing transition events and improves operator auditability without changing public URL semantics. |
| Cache/version policy | 0.5 - 1 day | Deferred. Useful before long-lived public operation, but less urgent than exposing already-recorded approval events. |
| Revision selection / rollback boundary | 1 - 3 days | Deferred. Needs a clearer published-revision model after current alias and event visibility settle. |
| Custom public alias key storage | 1 - 3 days | Deferred until current/cache/revision semantics are clear. |

## Boundary

In scope:

- repository read helper for existing candidate transition events;
- candidate-scoped event display on the existing `NO-CODE-RUNTIME` Source Output detail page;
- static UI contract coverage and focused repository coverage;
- docs/current-plan/report updates.

Out of scope:

- new transition storage;
- new approval workflow states;
- public cache/version policy;
- explicit rollback / published revision selection;
- custom public alias key storage;
- push.

## Verification

Implementation selected immediately after this planning step.
