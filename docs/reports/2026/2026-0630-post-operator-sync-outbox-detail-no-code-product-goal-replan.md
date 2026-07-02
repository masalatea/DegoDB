# Post-Operator Sync Outbox Detail No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

Operator sync outbox detail added a read-only project-scoped page for one sync outbox item, including status, attempts, last_error, endpoints, operation metadata, dedupe key, timestamps, and decoded intent payload.

The read-only inspection precondition is now in place. Before exposing a retry/requeue action, the product path needs a fail-closed eligibility decision so the UI can explain whether retry is available.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Sync retry/requeue action | 1 - 3 days | Deferred. Add a fail-closed eligibility decision first, then wire an action. |
| Retry eligibility guard | 0.5 - 2 days | Selected. This is the smallest safe continuation before mutating failed outbox state. |
| No-code runtime error feedback | 0.5 - 2 days | Deferred. The confirmed gap remains operator retry readiness, not runtime behavior. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **Sync retry eligibility guard first slice**.

The first slice should add a pure helper that decides whether a sync outbox item is eligible for retry, returns fail-closed reasons, and exposes the decision read-only on the operator sync outbox detail page. It should not mutate outbox state, run a retry, add a scheduler, transport, conflict resolution, or broad dashboard.

## Next Active Implementation

Sync retry eligibility guard first slice:

- define the smallest retry eligibility contract
- allow only failed items with a dedupe key, operation key, and non-empty last_error
- return fail-closed reasons and an action label
- show the read-only decision on the sync outbox detail page
- add focused helper coverage and update docs/current plan
