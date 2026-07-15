# Shared State Sync Schema/API Contract / shared state sync schema・API contract

English companion:
This document defines the v1 DB schema and REST API contract for room-based shared state synchronization.
It builds on [Shared State Sync Contract](shared-state-sync-contract.md).

この文書は room-based shared state synchronization の v1 DB schema と REST API contract を定義する。  
[Shared State Sync Contract](shared-state-sync-contract.md) を前提にする。

## Scope / scope

RSS-2 covers:

- room schema;
- membership schema;
- invite schema;
- shared state schema;
- state event schema;
- REST API endpoints for room join/read/update;
- authorization and stale revision rules.

RSS-2 does not cover:

- WebSocket/SSE payload schemas;
- Node.js sync server input packet;
- app client input packet;
- production realtime runtime;
- Redis/pubsub/scaling;
- CRDT/OT.

## Entity model / entity model

### `sync_room`

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `room_id` | string/uuid | yes | Stable room identity. |
| `owner_app_user_id` | string | yes | Owner app user. |
| `title` | string | yes | User-visible room title. |
| `status` | enum | yes | `active`, `closed`, `archived`. |
| `created_at` | timestamp | yes | Server timestamp. |
| `updated_at` | timestamp | yes | Server timestamp. |

Rules:

- only active rooms can accept normal state updates;
- closed/archived room behavior must be explicit in API responses;
- owner changes are out of scope for v1 unless separately defined.

### `sync_room_membership`

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `membership_id` | string/uuid | yes | Stable membership identity. |
| `room_id` | string/uuid | yes | FK to `sync_room`. |
| `app_user_id` | string | yes | Stable app user identity. |
| `role` | enum | yes | `owner`, `editor`, `viewer`. |
| `status` | enum | yes | `invited`, `active`, `suspended`, `left`. |
| `joined_at` | timestamp/null | no | Set when active. |
| `created_at` | timestamp | yes | Server timestamp. |
| `updated_at` | timestamp | yes | Server timestamp. |

Rules:

- `(room_id, app_user_id)` should be unique for active/current membership;
- subscribe/read requires active membership;
- update requires active membership and role `owner` or `editor`;
- membership changes should emit `membership.changed`.

### `sync_room_invite`

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `invite_id` | string/uuid | yes | Stable invite identity. |
| `room_id` | string/uuid | yes | FK to `sync_room`. |
| `invite_token_hash` | string | yes | Store hash only, not raw token. |
| `role` | enum | yes | Role granted after accept. |
| `expires_at` | timestamp | yes | Invite expiry. |
| `max_uses` | integer/null | no | Optional use cap. |
| `used_count` | integer | yes | Starts at 0. |
| `status` | enum | yes | `active`, `expired`, `revoked`, `exhausted`. |
| `created_by_app_user_id` | string | yes | Creator. |
| `created_at` | timestamp | yes | Server timestamp. |

Rules:

- raw invite tokens must not be stored;
- expired/revoked/exhausted invite use must fail;
- successful invite use creates or activates membership;
- invite token is not room authorization after membership is created.

### `sync_shared_state`

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `state_id` | string/uuid | yes | Stable state identity. |
| `room_id` | string/uuid | yes | FK to `sync_room`. |
| `state_key` | string | yes | Room-local state key. |
| `state_body_json` | JSON | yes | JSON-serializable state body. |
| `revision` | integer | yes | Monotonic revision. |
| `updated_by_app_user_id` | string | yes | Last updater. |
| `updated_at` | timestamp | yes | Server timestamp. |

Rules:

- `(room_id, state_key)` should be unique;
- revision starts at 1 or another explicit initial value;
- accepted update increments revision;
- state body must not contain SSO tokens or secrets;
- large binary payloads are out of scope.

### `sync_state_event`

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `event_id` | string/uuid | yes | Stable event identity. |
| `room_id` | string/uuid | yes | FK to `sync_room`. |
| `state_key` | string/null | no | Required for state events. |
| `revision` | integer/null | no | Revision after update. |
| `event_type` | enum | yes | `state.updated`, `membership.changed`, `room.closed`. |
| `actor_app_user_id` | string/null | no | Actor when available. |
| `summary_json` | JSON/null | no | Small event summary. |
| `created_at` | timestamp | yes | Server timestamp. |

Rules:

- accepted state update should create `state.updated`;
- membership change should create `membership.changed`;
- event rows must not include secrets or raw invite tokens;
- event history retention policy is product-specific.

## REST API contract / REST API contract

All endpoints require authenticated app user context unless explicitly stated.

### Create room

```text
POST /sync/rooms
```

Request:

```json
{
  "title": "Room title"
}
```

Response:

```json
{
  "room_id": "room_...",
  "membership": {
    "role": "owner",
    "status": "active"
  }
}
```

Rules:

- creator becomes owner membership;
- room starts as active.

### List current user's rooms

```text
GET /sync/rooms
```

Response:

```json
{
  "rooms": [
    {
      "room_id": "room_...",
      "title": "Room title",
      "role": "editor",
      "status": "active"
    }
  ]
}
```

Rules:

- return only rooms where current user has visible membership.

### Create invite

```text
POST /sync/rooms/{room_id}/invites
```

Request:

```json
{
  "role": "viewer",
  "expires_at": "2026-07-16T00:00:00Z",
  "max_uses": 1
}
```

Response:

```json
{
  "invite_token": "raw-token-shown-once",
  "expires_at": "2026-07-16T00:00:00Z"
}
```

Rules:

- only owner/editor policy may create invite, as configured;
- raw token is returned once and stored only as hash;
- invite creation should be audited.

### Join room by invite

```text
POST /sync/room-joins
```

Request:

```json
{
  "invite_token": "raw-token"
}
```

Response:

```json
{
  "room_id": "room_...",
  "membership": {
    "role": "viewer",
    "status": "active"
  }
}
```

Rules:

- verify token hash, expiry, status, and use count;
- create/activate membership for current app user;
- invite token is not reused as sync authorization.

### Get room state

```text
GET /sync/rooms/{room_id}/states/{state_key}
```

Response:

```json
{
  "room_id": "room_...",
  "state_key": "board",
  "revision": 12,
  "state": {}
}
```

Rules:

- active membership required;
- missing state can return 404 or an explicit empty initial state policy.

### Update room state

```text
PUT /sync/rooms/{room_id}/states/{state_key}
```

Request:

```json
{
  "expected_revision": 12,
  "state": {}
}
```

Response:

```json
{
  "room_id": "room_...",
  "state_key": "board",
  "revision": 13,
  "state": {}
}
```

Rules:

- active membership and update-capable role required;
- accepted update increments revision;
- accepted update emits `state.updated`;
- if stale revision rejection is enabled and `expected_revision` mismatches latest revision, return conflict.

### Get latest revision

```text
GET /sync/rooms/{room_id}/states/{state_key}/revision
```

Response:

```json
{
  "room_id": "room_...",
  "state_key": "board",
  "revision": 13
}
```

Rules:

- useful for reconnect/latest-fetch behavior;
- active membership required.

## Error model / error model

Recommended stable error codes:

| Code | Meaning |
| --- | --- |
| `unauthenticated` | No verified user session. |
| `room_not_found` | Room does not exist or is not visible. |
| `membership_required` | User is not an active room member. |
| `role_not_allowed` | User role cannot perform the action. |
| `invite_invalid` | Invite token is invalid. |
| `invite_expired` | Invite is expired. |
| `invite_exhausted` | Invite use count exceeded. |
| `room_closed` | Room no longer accepts action. |
| `state_not_found` | State key does not exist. |
| `stale_revision` | Expected revision does not match latest revision. |
| `invalid_state_body` | State body violates JSON/size/policy constraints. |

## Authorization summary / authorization summary

| Operation | Required authority |
| --- | --- |
| create room | authenticated app user |
| list rooms | authenticated app user |
| create invite | active membership + owner/editor policy |
| join room | authenticated app user + valid invite |
| read state | active room membership |
| update state | active room membership + owner/editor |
| latest revision | active room membership |

## Next / next

RSS-3 should define realtime event/command payloads, WebSocket heartbeat, reconnect/latest-fetch behavior, and SSE/HTTP fallback profile.
