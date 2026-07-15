# Shared State Sync Contract / shared state sync contract

English companion:
This document defines the first supported contract boundary for room-based shared state synchronization.
It is a contract and ownership document, not a production realtime server implementation.

この文書は room 単位の shared state synchronization の最初の supported contract boundary を定義する。
production realtime server 実装ではなく、contract と ownership の正本である。

## Position / 位置づけ

Shared state sync is a new scoped lane after the mobile external output work.

The product shape is:

```text
SSO-authenticated app users
  -> join authorized room/group
  -> read/update room shared state
  -> receive realtime state events through a separate sync server
```

Mtool should emit contracts and input packets.
Mtool should not silently become the production WebSocket/SSE runtime.

## Ownership boundary / ownership boundary

| Owner | Responsibility |
| --- | --- |
| Mtool | room/state contract, schema/API/realtime packet definitions, validation checklist, non-goals |
| App/backend server | SSO verification, app user identity, room membership authorization, persistence, audit, conflict authority |
| Node.js sync server | realtime connection lifecycle, room subscription, event fan-out, heartbeat, reconnect handling |
| App client | login, room join UI, subscribe/update behavior, reconnect/latest-fetch behavior |

The Node.js sync server is a separate runtime owner.
Mtool can generate its input packet, but does not operate or scale it by default.

## Identity and token model / identity・token model

Do not share SSO/OIDC tokens between users.

| Concept | Meaning | Shared? | Authority |
| --- | --- | --- | --- |
| SSO/OIDC token | Authenticates a person/session/device. | No | SSO provider + backend verification |
| `app_user_id` | Stable application user identity derived from SSO identity. | No | app/backend |
| invite token | Allows a user to request or accept room membership. | Intentionally shareable when scoped/expiring | app/backend |
| room/join token | Identifies a limited room join flow. | Intentionally shareable only if scoped/expiring | app/backend |
| room membership | Server-side authorization record for user and room. | No | app/backend |
| sync session id | Runtime connection/session identifier. | No | sync server |

Rules:

- room authorization must be based on verified user identity plus server-side membership;
- client-provided room IDs are not sufficient authority;
- invite/join tokens must be scoped, expiring, and persisted only as hashes when stored;
- SSO tokens must not be embedded in Mtool artifacts, room tokens, or sync events.

## Room boundary / room boundary

A room is the smallest synchronization boundary.

Minimum room concepts:

- room identity;
- owner or administrator;
- status: active, closed, archived;
- membership list;
- shared state keys;
- latest revision per state key.

Non-goals for v1:

- public unauthenticated rooms;
- peer-to-peer rooms;
- cross-room shared state;
- global broadcast channels;
- game-loop/tick authority.

## Membership boundary / membership boundary

Membership is a server-side authorization record, not a bearer token substitute.

Minimum membership concepts:

- room;
- app user;
- role;
- status;
- joined timestamp.

Suggested v1 roles:

- owner;
- editor;
- viewer.

Suggested v1 membership statuses:

- invited;
- active;
- suspended;
- left.

Authorization rules:

- subscribe requires active membership;
- read state requires active membership;
- update state requires active membership plus an update-capable role;
- invite creation requires owner or equivalent role;
- membership changes must produce audit/event evidence.

## Shared state boundary / shared state boundary

Shared state is room-scoped.

Minimum state concepts:

- room;
- state key;
- JSON state body;
- revision;
- updated by;
- updated at.

Rules:

- every update must produce a monotonic revision;
- state body must be JSON-serializable;
- state body must not contain SSO tokens or secrets;
- large binary payloads are out of scope for v1;
- offline mutation is out of scope unless a separate sync contract exists.

## Event boundary / event boundary

State changes should produce events that the sync server can fan out.

Minimum event concepts:

- room;
- state key;
- revision;
- event type;
- actor;
- timestamp;
- optional summary.

Suggested v1 events:

- `state.updated`;
- `membership.changed`;
- `room.closed`;
- `heartbeat`;
- `reconnect.required`.

Event payloads are contract data, not authority by themselves.
Clients should fetch latest state after reconnect or when revision continuity is uncertain.

## Conflict policy / conflict policy

Default v1 conflict policy:

```text
last-write-wins with monotonic revision
```

Optional stricter policy:

```text
reject stale update when expected_revision does not match latest revision
```

Rules:

- conflict policy must be explicit in generated packets;
- advanced merge, CRDT, OT, and game-loop arbitration are not part of v1;
- stale update behavior must be testable;
- accepted updates must emit the resulting revision.

## Transport boundary / transport boundary

The product direction is WebSocket-first.

Transport profiles:

1. WebSocket-first
   - room subscription;
   - server-to-client event fan-out;
   - optional client-to-server update command;
   - heartbeat and reconnect policy required.
2. SSE + HTTP POST fallback
   - server-to-client event stream;
   - update through ordinary HTTP request.
3. HTTP polling fallback
   - latest state/revision polling;
   - lowest priority fallback.

RSS-1 does not define final payload schemas.
Those belong to RSS-3 realtime event contract.

## Validation checklist / validation checklist

Future implementation slices should prove:

- same room members receive `state.updated`;
- different rooms do not receive each other's updates;
- unauthenticated subscribe fails;
- non-member subscribe fails;
- unauthorized update fails;
- invite expiry is enforced;
- stale revision behavior follows the selected conflict policy;
- reconnect fetches latest state;
- SSO tokens are not embedded in events, room tokens, or artifacts.

## Non-goals / non-goals

Mtool does not own by default:

- production Node.js server operation;
- WebSocket/SSE connection pool;
- production scaling;
- Redis/pubsub infrastructure;
- SSO provider runtime;
- token secret values;
- mobile push notification;
- advanced conflict resolution;
- CRDT/OT;
- peer-to-peer synchronization;
- game-loop runtime.

## Next slices / next slices

- RSS-2: Schema/API contract.
- RSS-3: Realtime event contract.
- RSS-4: Node.js server input packet.
- RSS-5: App client input packet.
- RSS-6: Reference sample.
- RSS-7: Validation and boundary review.
