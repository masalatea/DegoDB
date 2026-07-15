# Shared State Sync Realtime Contract / shared state sync realtime contract

English companion:
This document defines the v1 realtime event and command contract for room-based shared state synchronization.
It builds on [Shared State Sync Contract](shared-state-sync-contract.md) and [Shared State Sync Schema/API Contract](shared-state-sync-schema-api-contract.md).

この文書は room-based shared state synchronization の v1 realtime event / command contract を定義する。  
[Shared State Sync Contract](shared-state-sync-contract.md) と [Shared State Sync Schema/API Contract](shared-state-sync-schema-api-contract.md) を前提にする。

## Scope / scope

RSS-3 covers:

- WebSocket-first connection profile;
- event envelope;
- command envelope;
- subscribe/update/heartbeat/reconnect behavior;
- SSE + HTTP POST fallback profile;
- HTTP polling fallback profile;
- validation expectations.

RSS-3 does not cover:

- production WebSocket server implementation;
- Node.js server input packet emission;
- client SDK generation;
- Redis/pubsub/scaling;
- mobile push notifications;
- CRDT/OT or game-loop runtime.

## Transport profiles / transport profile

### Primary: WebSocket

```text
GET /sync/ws
```

The WebSocket connection must be authenticated.
The exact authentication mechanism is product/environment specific, but the server must verify a user identity that maps to `app_user_id`.

Rules:

- client-provided room IDs are not authority;
- subscribe/update commands must check active room membership server-side;
- SSO/OIDC tokens must not be broadcast in events;
- heartbeat and reconnect behavior must be explicit.

### Fallback: SSE + HTTP POST

SSE stream:

```text
GET /sync/rooms/{room_id}/events
```

HTTP update:

```text
PUT /sync/rooms/{room_id}/states/{state_key}
```

Rules:

- SSE is server-to-client only;
- update commands use the RSS-2 REST API;
- same membership and role checks apply.

### Fallback: HTTP polling

```text
GET /sync/rooms/{room_id}/states/{state_key}
GET /sync/rooms/{room_id}/states/{state_key}/revision
```

Rules:

- polling is the lowest-priority fallback;
- clients should back off;
- polling does not imply realtime UX.

## WebSocket message envelope / WebSocket message envelope

All WebSocket messages should use a stable envelope.

Server event envelope:

```json
{
  "message_type": "event",
  "event_type": "state.updated",
  "message_id": "msg_...",
  "room_id": "room_...",
  "state_key": "board",
  "revision": 13,
  "actor_app_user_id": "user_...",
  "occurred_at": "2026-07-15T00:00:00Z",
  "payload": {}
}
```

Client command envelope:

```json
{
  "message_type": "command",
  "command_type": "state.update",
  "command_id": "cmd_...",
  "room_id": "room_...",
  "state_key": "board",
  "expected_revision": 12,
  "payload": {}
}
```

Command result envelope:

```json
{
  "message_type": "command_result",
  "command_id": "cmd_...",
  "ok": true,
  "room_id": "room_...",
  "state_key": "board",
  "revision": 13,
  "payload": {}
}
```

Error envelope:

```json
{
  "message_type": "error",
  "message_id": "msg_...",
  "correlation_id": "cmd_...",
  "error_code": "membership_required",
  "message": "Active room membership is required."
}
```

Rules:

- `message_id` / `command_id` must be unique enough for client-side dedupe and debugging;
- `correlation_id` should reference the command that caused an error/result;
- events must not contain raw invite tokens, SSO tokens, refresh tokens, or secrets;
- payload shape is state-key specific and must stay JSON-serializable.

## Event types / event type

### `state.updated`

Sent after an accepted room state update.

Required fields:

- `room_id`;
- `state_key`;
- `revision`;
- `actor_app_user_id`;
- `occurred_at`;
- `payload` or summary of latest state.

Client behavior:

- if revision is exactly the next expected revision, apply or fetch according to client policy;
- if revision jumps or client is uncertain, fetch latest state through REST;
- never treat event receipt as authorization proof.

### `membership.changed`

Sent when room membership changes.

Required fields:

- `room_id`;
- `actor_app_user_id` when available;
- changed `app_user_id` when safe to expose;
- membership status/role summary;
- `occurred_at`.

Client behavior:

- refresh room/member state;
- if current user loses membership, unsubscribe and clear room-local state as product policy requires.

### `room.closed`

Sent when a room is closed.

Client behavior:

- stop sending update commands;
- fetch final state if needed;
- show closed-room UI.

### `heartbeat`

Server-to-client heartbeat.

Example:

```json
{
  "message_type": "event",
  "event_type": "heartbeat",
  "message_id": "msg_...",
  "occurred_at": "2026-07-15T00:00:00Z"
}
```

Client behavior:

- update connection liveness timestamp;
- reconnect if heartbeat timeout is exceeded.

### `reconnect.required`

Server tells client to reconnect and fetch latest state.

Client behavior:

- close current connection;
- reconnect with backoff;
- resubscribe to rooms;
- fetch latest state/revision after reconnect.

## Command types / command type

### `room.subscribe`

Client requests subscription to a room.

```json
{
  "message_type": "command",
  "command_type": "room.subscribe",
  "command_id": "cmd_...",
  "room_id": "room_..."
}
```

Rules:

- active membership required;
- successful subscribe may return latest known revision summary;
- non-member subscribe must fail with `membership_required` or `room_not_found`.

### `room.unsubscribe`

Client leaves a room subscription.

```json
{
  "message_type": "command",
  "command_type": "room.unsubscribe",
  "command_id": "cmd_...",
  "room_id": "room_..."
}
```

Rules:

- unsubscribe is connection-local;
- it does not change room membership.

### `state.update`

Client sends state update through WebSocket.

```json
{
  "message_type": "command",
  "command_type": "state.update",
  "command_id": "cmd_...",
  "room_id": "room_...",
  "state_key": "board",
  "expected_revision": 12,
  "payload": {}
}
```

Rules:

- active membership and update-capable role required;
- accepted update increments revision and emits `state.updated`;
- stale revision behavior follows the selected conflict policy;
- HTTP update path and WebSocket update command must share the same authorization/conflict authority.

### `ping`

Client-to-server heartbeat probe.

```json
{
  "message_type": "command",
  "command_type": "ping",
  "command_id": "cmd_..."
}
```

Response:

```json
{
  "message_type": "command_result",
  "command_id": "cmd_...",
  "ok": true,
  "payload": {
    "pong": true
  }
}
```

## Reconnect and latest-fetch behavior / reconnect behavior

Client reconnect policy:

1. detect disconnect or heartbeat timeout;
2. reconnect with backoff;
3. resubscribe to required rooms;
4. fetch latest revision/state for each active room state key;
5. resume applying events only after latest state is known.

Rules:

- missed events are expected during disconnect;
- v1 does not require guaranteed event replay;
- event history may help diagnostics but is not the primary reconnect authority;
- latest REST state/revision is the authority after reconnect.

## SSE fallback profile / SSE fallback profile

SSE event envelope should mirror WebSocket server event envelope.

Example:

```text
event: state.updated
data: {"message_type":"event","event_type":"state.updated","room_id":"room_...","state_key":"board","revision":13,"payload":{}}
```

Rules:

- SSE subscribe requires active room membership;
- state updates use REST `PUT`;
- reconnect uses EventSource retry plus latest-fetch behavior;
- SSE fallback does not support client-to-server commands over the stream.

## HTTP polling fallback profile / HTTP polling fallback profile

Polling clients should use:

- latest revision endpoint;
- get state endpoint.

Rules:

- use backoff or fixed interval appropriate to product;
- do not claim realtime UX;
- same authorization checks apply.

## Validation expectations / validation expectation

Future runtime/sample validation should prove:

- member can subscribe to a room;
- non-member cannot subscribe;
- viewer cannot update;
- editor/owner can update;
- accepted update emits `state.updated`;
- different room does not receive the event;
- stale update returns `stale_revision` when stale rejection is enabled;
- heartbeat timeout triggers reconnect;
- reconnect fetches latest state;
- event payloads do not contain SSO tokens, refresh tokens, raw invite tokens, or secrets.

## Next / next

RSS-4 should define the Node.js sync server input packet that consumes:

- RSS-1 shared state sync contract;
- RSS-2 schema/API contract;
- RSS-3 realtime event contract.
