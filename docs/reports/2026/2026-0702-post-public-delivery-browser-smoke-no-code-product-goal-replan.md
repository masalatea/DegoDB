# 2026-0702 Post-Public-Delivery-Browser-Smoke No-Code Product Goal Replan

Status: `DONE`.

## Decision

Selected **Alias lifecycle audit trail first slice** as the next no-code product-facing slice.

Public delivery browser smoke closed the route verification gap for artifact-key, current, and alias public preview URLs. The remaining public-delivery operations gap is accountability: alias create/update/delete mutations should be visible after the fact, especially because aliases do not automatically follow current rollback.

## Candidates

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Alias lifecycle audit trail first slice | 0.5 - 1 day | Selected. Adds append-only create/update/delete event visibility for alias lifecycle operations. |
| New product-facing continuation outside public delivery | Replan first | Deferred until alias lifecycle auditability is no longer a public-delivery gap. |
| Broader public hardening | 1 - 3 days | Deferred. Browser smoke and cache policy are already covered for this slice. |

## Boundary

In scope:

- append-only alias lifecycle event storage;
- repository coverage for create/update/delete events;
- operator/admin detail UI visibility;
- docs and plan index update.

Out of scope:

- new public route behavior;
- automatic alias follow-current mode;
- audit export/search UI;
- push.

## Verification

Implementation selected immediately after this planning step.
