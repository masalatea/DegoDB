# Shared state sync with separate Node.js server / shared state sync Node.js server roadmap

Date: 2026-07-14

## Summary / summary

This report records a future product direction for group/room-based shared state synchronization.

The direction is:

- keep SSO as individual authentication;
- do not share SSO tokens between users;
- introduce a separate room/group/invite token concept;
- synchronize state for users who are authorized members of the same room/group;
- assume a separate Node.js sync server for realtime runtime;
- let Mtool manage and emit DB/API/sync contracts and input packets for that server and app clients.

この report は、group / room 単位の shared state synchronization を将来 product direction として記録する。

方向性:

- SSO は個人認証として扱う。
- SSO token 自体を user 間で共有しない。
- 別概念として room / group / invite token を導入する。
- 同じ room / group の authorized member 間で state を同期する。
- realtime runtime は別 Node.js sync server 前提にする。
- Mtool はその server と app client 向けの DB / API / sync contract / input packet を管理・出力する。

## Product intuition / product intuition

No-code output alone is a minimum useful surface. A more useful app platform can provide a path for small collaborative or room-based experiences where multiple authenticated users see the same shared state.

This is not necessarily a game engine. The first target is simpler:

- users authenticate through SSO;
- users join a room/group;
- membership is checked server-side;
- a shared state object is associated with the room;
- updates are sent to a sync server;
- authorized members receive state change notifications;
- clients can reconnect and fetch latest state.

## Boundary decision / 境界判断

Mtool should not become the realtime connection runtime by default.

Recommended ownership split:

| Owner | Responsibility |
| --- | --- |
| Mtool | schema design, room/membership/state contracts, REST API contract, realtime event contract, Node.js server input packet, app client input packet, validation checklist |
| Node.js sync server | realtime connections, room subscription, state update fan-out, reconnect handling, optional adapter to DB/API |
| App client | SSO login, room join flow, state display, state update request, reconnect/latest fetch behavior |
| App/server backend | authorization, membership checks, persistence, audit, conflict policy authority |

Mtool may generate artifacts that help a Node.js server and clients implement the contract, but it should not run or own production realtime infrastructure by default.

## SSO and token model / SSOとtoken model

Do not share SSO tokens.

Use separate token concepts:

| Token / identity | Meaning | Shared? |
| --- | --- | --- |
| SSO token / OIDC token | Authenticates a person/device/session. | No |
| `app_user_id` | Stable application user identity derived from SSO identity. | No |
| invite token | Allows a user to request/accept room membership. | Can be shared intentionally |
| room token / join token | Identifies a room join or limited room access flow. | Can be shared intentionally if scoped/expiring |
| room membership | Server-side authorization record. | Not a bearer substitute |
| sync session id | Runtime connection/session identifier. | No |

The sync server should authorize by verified user identity plus room membership, not by trusting client-provided room IDs alone.

## Candidate v1 domain model / v1 domain model

Candidate tables/entities:

- `sync_room`
  - room identity, owner, title, status.
- `sync_room_membership`
  - room, app user, role, status, joined timestamp.
- `sync_room_invite`
  - room, invite token hash, expiry, role, max use, status.
- `sync_shared_state`
  - room, state key, JSON state body, revision, updated by, updated at.
- `sync_state_event`
  - room, state key, revision, event type, actor, timestamp, optional summary.

Initial conflict policy:

- last-write-wins with monotonic revision;
- reject stale update if revision check is enabled;
- explicit conflict policy artifact required for anything more advanced.

## Candidate v1 API contract / v1 API contract

REST endpoints:

- create room;
- list rooms for current user;
- create invite;
- join room by invite token;
- get room state;
- update room state;
- get latest revision;
- optional event history.

Realtime endpoints:

- subscribe to room state changes;
- send state update command, if using bidirectional WebSocket updates;
- receive `state.updated`;
- receive `membership.changed`;
- receive `room.closed`;
- heartbeat/reconnect.

Initial transport preference:

1. WebSocket
   - best fit when the product goal is a realtime shared-state experience;
   - supports bidirectional room subscription and update commands;
   - makes the Node.js sync server boundary easy to understand;
   - requires explicit connection lifecycle, heartbeat, reconnect, authorization, and scaling policy.
2. SSE + HTTP POST fallback
   - useful when bidirectional WebSocket is not available or not needed;
   - server-to-client event stream with updates through ordinary HTTP requests;
   - simpler operational fallback, not the primary realtime target.
3. HTTP polling fallback
   - lowest common denominator for constrained environments;
   - not the main product experience.

The first target should be WebSocket-first because the intended value is realtime shared state. SSE + HTTP POST can remain a fallback profile.

## Mtool outputs / Mtool output候補

Mtool can output:

1. DB schema / canonical metadata
   - room, membership, invite, shared state, event history.
2. REST API contract
   - OpenAPI-style endpoint definitions.
3. Realtime sync event contract
   - event names, payload shapes, revision rules.
4. Node.js sync server input packet
   - DB/API refs;
   - auth verification policy;
   - room authorization policy;
   - WebSocket-first transport profile;
   - optional SSE/HTTP fallback profile;
   - event schema;
   - client command schema;
   - heartbeat policy;
   - reconnect behavior.
5. App client input packet
   - join flow;
   - subscribe URL;
   - update command shape;
   - event handling shape;
   - reconnect/latest-fetch behavior.
6. AI/code-builder task packet
   - ask-before rules;
   - forbidden guesses;
   - validation commands;
   - server/app boundary.
7. Validation checklist
   - two clients in same room receive updates;
   - two clients can update through the selected command path;
   - unauthorized room subscribe fails;
   - unauthorized room update fails;
   - invite expiry works;
   - stale revision behavior is explicit;
   - reconnect fetches latest state.

## Mtool non-goals / Mtool non-goals

Mtool should not own by default:

- production Node.js server operation;
- WebSocket/SSE connection pool;
- production scaling;
- Redis/pub-sub infrastructure;
- token secret values;
- SSO provider runtime;
- mobile push notification;
- advanced conflict resolution;
- peer-to-peer synchronization;
- CRDT/OT implementation;
- game-loop runtime.

These can be reopened only as separate product scopes.

## Validation plan / validation plan

First proof should stay small:

1. define contract and schema;
2. generate Node.js sync server input packet;
3. generate app client input packet;
4. create a tiny reference sample with two simulated clients;
5. verify:
   - same room receives state update;
   - WebSocket heartbeat/reconnect is explicit;
   - different room does not receive update;
   - unauthorized user cannot subscribe;
   - unauthorized user cannot update;
   - reconnect fetches latest state;
   - stale update policy is explicit.

No production scaling or advanced conflict resolution in v1.

## Relationship to current mobile/no-code work / 現行mobile・no-codeとの関係

This is a new scoped implementation lane, not unfinished mobile wrapper work.

It builds on existing decisions:

- SSO app user standard remains the identity basis.
- Mobile output modes remain valid.
- Ownership boundaries remain valid.
- Offline sync remains disabled unless an explicit sync contract exists.

This lane would define that explicit sync contract for a narrow room/shared-state case.

Related stable references:

- `docs/sso-application-user-standard.md`
- `docs/mobile-ownership-boundaries.md`
- `docs/mobile-output-modes.md`
- `docs/mobile-external-feasibility-study.md`

## Suggested future plan / 将来計画案

If promoted, use this sequence:

| Step | Work unit | Exit condition |
| --- | --- | --- |
| RSS-1 | Shared state sync contract | Room/membership/state/event boundary, token model, conflict policy, and non-goals are documented. |
| RSS-2 | Schema/API contract | DB schema and REST API contract are defined for room join/read/update. |
| RSS-3 | Realtime event contract | WebSocket-first event/command payloads, heartbeat, reconnect/latest behavior, and SSE/HTTP fallback profile are defined. |
| RSS-4 | Node.js server input packet | Mtool emits server-facing input packet without running production Node.js. |
| RSS-5 | App client input packet | Mtool emits app-facing input packet for join/subscribe/update. |
| RSS-6 | Reference sample | Two-client room sync proof passes with authorization checks. |
| RSS-7 | Validation and boundary review | Unsafe token sharing, unauthorized subscribe, stale update, and reconnect behavior are verified. |

Status: `ROADMAP_CANDIDATE_NOT_ACTIVE`.
