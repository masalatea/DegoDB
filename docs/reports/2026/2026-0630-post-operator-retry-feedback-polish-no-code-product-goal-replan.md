# Post-Operator Retry Feedback Polish No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

Operator retry feedback polish made the sync outbox detail page explain the requeue result, current status, attempts before the next processor claim, cleared error state, and existing processor handoff.

The operator/admin path can now identify failed items, inspect details, decide retry eligibility, requeue, prove processor pickup, and show post-requeue feedback. The next small product-facing gap is runtime-side visibility: generated runtime artifacts should be able to expose failed/retryable state as read-only information without moving retry mutation into the generated runtime.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| No-code runtime error/retry visibility | 1 - 3 days | Selected. Operator retry is understandable now; runtime-visible error/retry state is the next product gap. |
| Retry audit trail | 0.5 - 2 days | Deferred. Accountability is less visible to the product path than runtime error/retry state. |
| Another operator workflow polish slice | 0.5 - 2 days | Deferred. No concrete new operator navigation gap is identified. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **No-code runtime error/retry visibility first slice**.

The first slice should surface failed/retryable sync state in generated runtime artifacts as read-only hints. Retry mutation should remain in operator/admin pages. The slice should not add a scheduler, remote transport, conflict resolution, retry audit table, broad dashboard, or generated runtime retry mutation.

## Next Active Implementation

No-code runtime error/retry visibility first slice:

- keep retry mutation out of generated runtime
- add deterministic generated runtime data or HTML hints for failed/retryable/requeued state
- update a focused sample/runtime smoke
- update report/current plan after verification
