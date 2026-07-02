# Post-retry audit trail no-code product goal replan

Status: `DONE`

Date: 2026-07-01

## Decision

Choose Retry audit display follow-up as the next small no-code product-facing implementation.

## Context

Retry audit trail now records `sync_outbox.retry_requeued` events after successful operator retry requeue. The next narrow gap is visibility: operators reviewing a sync outbox item should be able to see recent retry audit events directly on the same detail page, without leaving the current workflow.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Operator/admin no-code workflow polish | 1 - 3 days | Deferred for one narrow visibility step. Useful after the retry audit loop is readable end-to-end. |
| Runtime preview action affordance follow-up | 0.5 - 2 days | Deferred. No concrete generated preview payload-guidance gap is active. |
| Retry audit display follow-up | 0.5 - 2 days | Selected. Recording exists; the next smallest gap is showing recent retry audit events on the sync outbox detail page. |

## Scope

In scope:

- audit latest fetch by `target_key`;
- recent retry audit section on sync outbox detail;
- focused tests and docs.

Out of scope:

- new audit tables;
- audit event editing;
- retry scheduler;
- inline processing;
- transport;
- conflict resolution.

## Notes

This keeps retry audit visibility scoped to the current sync outbox detail workflow.
