# Post-Sample29 Stack Review No-Code Product Goal Replan

Status: `DONE`

Push: not performed.

## Decision

Choose generic multi-profile runtime execution endpoint smoke extraction as the next implementation slice.

## Why This Next

The sample29 browser smoke already proves current / alias real-submit handoff for the second no-code domain. The remaining gap is that direct endpoint enqueue proof is still effectively sample28-shaped.

Before sample29 outbox processing, live polling, synchronous demo processing, retry mutation, or push cleanup, the smallest useful move is to make the existing direct endpoint smoke profile-aware:

- sample28 remains the full baseline with direct endpoint and outbox processing;
- sample29 gains direct current / alias endpoint enqueue proof for `update_support_case`;
- sample29 outbox processing stays deferred until the endpoint proof is no longer sample28-specific.

## Candidate Review

| Candidate | Estimate | Decision |
| --- | --- | --- |
| Generic multi-profile endpoint smoke extraction | 0.25 - 0.5 day | Selected. It is the narrowest confidence gap after the stack review. |
| Sample29 outbox processing smoke | 0.5 - 1.5 days | Deferred. Add it after sample29 direct endpoint enqueue is covered. |
| Live result refresh / polling | 1 - 3 days | Deferred. Manual refresh handoff is already explicit. |
| Synchronous local/demo processing | 1 - 3 days | Deferred. Needs an explicit demo-only boundary. |
| Runtime retry mutation | 1 - 3 days | Deferred. Retry mutation remains operator/admin-owned. |
| Commit stack cleanup / push | 0.25 - 1 day | Deferred until explicitly requested. |

## Scope

In scope:

- profile-aware direct endpoint smoke payloads;
- sample28 backward compatibility;
- sample29 current / alias direct endpoint enqueue proof;
- documentation/status update.

Out of scope:

- sample29 outbox processing;
- direct business-row mutation in the public endpoint;
- live polling;
- synchronous endpoint processing;
- push or history rewrite.

