# Post-Operator Sync Retry Action No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

Operator sync retry action added a CSRF-protected operator mutation that requeues eligible failed sync outbox items to `pending`, clears `last_error`, and keeps `attempts` unchanged until the existing processor claims the item.

The next product-facing confidence gap is proving that a requeued item is picked up by the existing processor path. That should happen before adding more retry UI polish or runtime-facing retry visibility.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Operator retry feedback polish | 0.5 - 2 days | Deferred. The operator action already has a narrow success message; product confidence needs processor proof first. |
| Retry processing smoke | 0.5 - 2 days | Selected. This closes the behavior loop after requeue without adding scheduler, transport, conflict resolution, or broader UI. |
| No-code runtime error/retry visibility | 1 - 3 days | Deferred. Runtime-facing retry visibility should follow processor confidence. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **Retry processing smoke first slice**.

The first slice should prove a deterministic flow from failed item -> operator requeue semantics -> `pending` -> existing processor claim/handler result. It should not add a background scheduler, remote transport, conflict resolution, new retry UI, broad dashboard, or retry audit table.

## Next Active Implementation

Retry processing smoke first slice:

- define the narrow smoke boundary
- build or extend a focused repository/sample flow from failed -> requeued pending -> existing processor claim/handler
- assert final status, attempts behavior, cleared error, and processor result
- update report/current plan after verification
