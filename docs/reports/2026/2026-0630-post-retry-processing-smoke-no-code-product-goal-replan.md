# Post-Retry Processing Smoke No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

Retry processing smoke proved that a requeued failed sync outbox item returns to `pending`, is claimed by the existing processor, increments `attempts`, stays clear of `last_error`, and ends as `done` when the handler succeeds.

With the behavior loop proven, the next product-facing gap is operator clarity after retry. The detail page should make it easy to understand that retry does not process inline: it requeues the item for the existing processor.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Operator retry feedback polish | 0.5 - 2 days | Selected. The behavior loop is proven, so the next smallest product gap is clarity after requeue. |
| No-code runtime error/retry visibility | 1 - 3 days | Deferred. Runtime-facing state should wait until the operator flow is understandable. |
| Retry audit trail | 0.5 - 2 days | Deferred. Existing updated_at/status/attempts plus clear operator feedback are enough for the next slice. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **Operator retry feedback polish first slice**.

The first slice should improve the sync outbox detail page's post-requeue feedback so the operator understands the item is now `pending` and will be picked up by the existing processor. It should not add scheduler behavior, remote transport, conflict resolution, a retry audit table, broad dashboard, or generated runtime UI changes.

## Next Active Implementation

Operator retry feedback polish first slice:

- keep the boundary to operator detail feedback only
- make the success state explain pending status and existing processor handoff
- clarify current status / attempts / last_error after retry
- add focused source/page contract coverage if needed
- update report/current plan after verification
