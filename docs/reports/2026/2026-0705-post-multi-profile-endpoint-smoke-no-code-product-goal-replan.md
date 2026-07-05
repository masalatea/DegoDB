# Post-Multi-Profile Endpoint Smoke No-Code Product Goal Replan

Status: `DONE`

Push: not performed.

## Decision

Choose sample29 runtime outbox processing smoke as the next implementation slice.

## Why This Next

The previous slice made sample29 direct current / alias endpoint enqueue visible. That removed the sample28-only direct endpoint gap, leaving the next smallest confidence gap: sample29 had not yet proven that queued endpoint work can be processed by the existing server DBAccess outbox handler.

This remains narrower than live polling, synchronous demo processing, retry mutation, or push cleanup.

## Candidate Review

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Sample29 outbox processing smoke | 0.25 - 0.5 day | Selected. Direct endpoint enqueue is now profile-aware, so processing proof is the next narrow confidence step. |
| Live result refresh / polling | 1 - 3 days | Deferred. Manual refresh remains the accepted user-facing behavior. |
| Synchronous local/demo processing | 1 - 3 days | Deferred. Needs an explicit demo-only boundary. |
| Runtime retry mutation | 1 - 3 days | Deferred. Retry mutation remains operator/admin-owned. |
| Commit stack cleanup / push | 0.25 - 1 day | Deferred until explicitly requested. |

## Scope

In scope:

- parameterize the existing outbox processing smoke for sample29;
- keep sample28 processing smoke behavior intact;
- enable sample29 processing proof from the public runtime smoke wrapper;
- record verification and boundary.

Out of scope:

- changing runtime submit to process synchronously;
- live polling;
- production scheduler/transport;
- push or history rewrite.

