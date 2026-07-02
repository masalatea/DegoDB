# Post-Operator Failed-Sync Inspection No-Code Product Goal Replan

Date: 2026-06-30
Status: `DONE`

## Context

Operator failed-sync inspection made failed sync outbox items visible in the Source Outputs admin page. Operators can now see counts, latest failed item, attempts, and last_error without mutating sync state.

Before adding retry/requeue behavior, the product path needs a safe read-only detail surface for one outbox item.

## Candidates

| Candidate | First slice estimate | Decision |
| --- | --- | --- |
| Sync retry/requeue action | 1 - 3 days | Deferred. Retry needs a safe read-only item detail surface first. |
| Operator sync outbox detail page | 0.5 - 2 days | Selected. This is the smallest operator-facing continuation after list-level failed sync inspection. |
| No-code runtime error feedback | 0.5 - 2 days | Deferred. The confirmed gap remains operator diagnosis, not runtime behavior. |
| Mtool implementation namespace cleanup | 1 - 3 days | Remains parked until a narrow helper cluster is selected. |

## Decision

Select **Operator sync outbox detail first slice**.

The first slice should add a read-only project-scoped detail page for one sync outbox item, linked from the existing Source Outputs sync inspection list. It should show the existing outbox fields and decoded intent payload. It should not add retry, requeue, status mutation, remote transport, conflict resolution, or a broad dashboard.

## Next Active Implementation

Operator sync outbox detail first slice:

- add a project-scoped detail route
- look up the outbox item by existing dedupe_key/catalog data
- render status, attempts, last_error, endpoints, operation metadata, dedupe key, timestamps, and intent payload
- link failed items from Source Outputs sync inspection
- update focused route/auth/helper coverage and docs
