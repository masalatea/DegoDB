# Post-Sync Error-State Visibility No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

The sync error-state visibility first slice made a deterministic failed outbox state visible in sample30 using the existing outbox lifecycle fields: `status`, `attempts`, and `last_error`.

The next step should keep building toward a usable no-code product path without expanding into transport, conflict resolution, or a full retry scheduler too early.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Sync retry visibility | 1 - 3 days | Deferred. First make the failed state inspectable to an operator before adding behavior for retrying it. |
| Operator failed-sync inspection | 1 - 3 days | Selected. This is the smallest product-facing continuation after sample30 made failed outbox state deterministic. |
| No-code runtime error feedback | 0.5 - 2 days | Deferred. The confirmed gap is operator inspection, not generated runtime behavior. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **Operator failed-sync inspection first slice**.

The first slice should add a read-only operator/admin view of failed sync outbox items using existing fields. It should not add retry, requeue, remote transport, conflict resolution, or generated runtime behavior changes.

## Next Active Implementation

Operator failed-sync inspection first slice:

- choose the smallest read-only operator/admin surface
- summarize failed outbox items with status, attempts, last_error, operation, origin/target, and timestamps where available
- add focused tests for failed item visibility and empty-state behavior
- update current plan/report after verification
