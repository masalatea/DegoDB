# Post-Sync Retry Eligibility Guard No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

Sync retry eligibility guard added a fail-closed read-only decision for sync outbox items. Eligible failed items can now be identified before mutation.

The next product-facing step can safely add the smallest operator mutation: requeue an eligible failed item to pending so the existing outbox processor can claim it later.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Sync retry/requeue action | 1 - 3 days | Selected. Eligibility guard is now in place, so the smallest product-facing mutation is requeue-to-pending. |
| Retry audit trail | 0.5 - 2 days | Deferred. Existing permission audit and updated_at are sufficient for this first mutation slice. |
| No-code runtime error feedback | 0.5 - 2 days | Deferred. The confirmed gap remains operator retry action, not runtime behavior. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **Operator sync retry action first slice**.

The first slice should add a CSRF-protected operator POST action that requeues eligible failed sync outbox items to `pending`, clears `last_error`, and leaves attempts unchanged until the existing processor claims the item. It should not process inline, add a background scheduler, remote transport, conflict resolution, broad dashboard, or retry audit table.

## Next Active Implementation

Operator sync retry action first slice:

- add a small repository wrapper around existing status update behavior
- enforce the existing retry eligibility guard before mutation
- add a project-scoped POST action on sync outbox detail
- redirect back to the detail page with a status flag
- add focused mutation tests and update docs/current plan
