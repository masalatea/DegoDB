# Shared State Sync App Client Input Packet / shared state sync app client input packet

English companion:
This document defines the v1 app-client-facing input packet for room-based shared state synchronization.
It builds on the shared-state sync contract, schema/API contract, realtime contract, and Node.js server input packet.

この文書は room-based shared state synchronization の app client 向け v1 input packet を定義する。  
shared-state sync contract、schema/API contract、realtime contract、Node.js server input packet を前提にする。

## Scope / scope

RSS-9 covers:

- `sync-client-input.json` packet shape;
- app client responsibilities;
- room join/read/update flow;
- WebSocket subscribe/update flow;
- SSE + HTTP fallback flow;
- polling fallback flow;
- reconnect/latest-fetch behavior;
- client validation checklist;
- forbidden implicit actions.

RSS-9 does not cover:

- generated client SDK;
- React/Flutter/React Native source generation;
- UI component generation;
- production offline sync;
- CRDT/OT, game-loop runtime, or guaranteed event replay;
- SSO/OIDC provider client setup.

## Product position / product position

Mtool emits the client-facing packet so an app creator, AI agent, or external app framework can wire the sync behavior without guessing.

```text
Mtool shared-state contracts
  -> sync-server-input.json for Node.js sync server owner
  -> sync-client-input.json for app client owner
```

The client packet is a handoff artifact.
It is not a generated app and not a runtime SDK.

## Artifact name / artifact name

Recommended artifact names:

- `sync-client-input.json`
- `SYNC-CLIENT-INPUT.md`

Recommended bundle key:

```json
{
  "shared_state_sync_client_input": {
    "json": "sync-client-input.json",
    "markdown": "SYNC-CLIENT-INPUT.md"
  }
}
```

## Packet shape / packet shape

Minimum packet:

```json
{
  "schema_version": "shared_state_sync_client_input.v1",
  "generated_by": {
    "tool": "mtool",
    "artifact": "shared_state_sync_client_input"
  },
  "contracts": {
    "shared_state_sync_contract": "docs/shared-state-sync-contract.md",
    "schema_api_contract": "docs/shared-state-sync-schema-api-contract.md",
    "realtime_contract": "docs/shared-state-sync-realtime-contract.md",
    "node_server_input_contract": "docs/shared-state-sync-node-server-input-packet.md"
  },
  "client": {
    "ownership": "external_app_client_owner",
    "source_generation": false,
    "sdk_generation": false
  },
  "backend": {},
  "room_flow": {},
  "state_flow": {},
  "realtime_flow": {},
  "fallbacks": {},
  "reconnect": {},
  "validation": {},
  "forbidden_actions": []
}
```

Rules:

- `schema_version` must be explicit;
- contract references must point to the RSS source contracts;
- source/SDK generation must be false unless a later scoped implementation changes that;
- forbidden actions must be present.

## Backend profile / backend profile

Recommended section:

```json
{
  "backend": {
    "api_base_url_env": "APP_BACKEND_BASE_URL",
    "auth": {
      "mode": "app_owned_sso_session",
      "token_storage_owner": "app_client_owner",
      "do_not_store_tokens_in_packet": true
    },
    "authority": {
      "session_verification": "app_backend",
      "membership": "app_backend",
      "state_persistence": "app_backend",
      "conflict_policy": "app_backend"
    }
  }
}
```

Rules:

- app client owns SSO client setup and secure token/session handling;
- Mtool packet must not contain tokens or secrets;
- backend remains authoritative for membership, persistence, and conflict policy.

## Room flow / room flow

Recommended section:

```json
{
  "room_flow": {
    "create_room": {
      "method": "POST",
      "path": "/sync/rooms"
    },
    "list_rooms": {
      "method": "GET",
      "path": "/sync/rooms"
    },
    "join_by_invite": {
      "method": "POST",
      "path": "/sync/room-joins",
      "raw_invite_token_storage": "do_not_persist_after_join"
    },
    "membership_required_after_join": true
  }
}
```

Client rules:

- invite token is a join input, not long-term authorization;
- after join, use server-side membership;
- if membership is lost, unsubscribe and clear room-local state as product policy requires.

## State flow / state flow

Recommended section:

```json
{
  "state_flow": {
    "get_state": {
      "method": "GET",
      "path": "/sync/rooms/{room_id}/states/{state_key}"
    },
    "update_state": {
      "method": "PUT",
      "path": "/sync/rooms/{room_id}/states/{state_key}",
      "expected_revision_required": true
    },
    "latest_revision": {
      "method": "GET",
      "path": "/sync/rooms/{room_id}/states/{state_key}/revision"
    },
    "conflict_error": "stale_revision"
  }
}
```

Client rules:

- store current revision per room/state key;
- send `expected_revision` when updating;
- handle `stale_revision` by fetching latest state;
- do not store secrets in shared state body.

## Realtime flow / realtime flow

Recommended section:

```json
{
  "realtime_flow": {
    "primary_transport": "websocket",
    "websocket": {
      "path": "/sync/ws",
      "subscribe_command": "room.subscribe",
      "unsubscribe_command": "room.unsubscribe",
      "update_command": "state.update",
      "ping_command": "ping"
    },
    "events": [
      "state.updated",
      "membership.changed",
      "room.closed",
      "heartbeat",
      "reconnect.required"
    ],
    "event_application_policy": "apply_next_revision_or_fetch_latest"
  }
}
```

Client rules:

- subscribe after authenticated connection is established;
- if event revision is not the expected next revision, fetch latest;
- `membership.changed` should trigger room/member refresh;
- `room.closed` should disable updates for that room;
- heartbeat timeout should trigger reconnect.

## Fallback profile / fallback profile

Recommended section:

```json
{
  "fallbacks": {
    "sse_http": {
      "enabled": true,
      "event_stream": "/sync/rooms/{room_id}/events",
      "update_transport": "http_put"
    },
    "polling": {
      "enabled": true,
      "revision_path": "/sync/rooms/{room_id}/states/{state_key}/revision",
      "state_path": "/sync/rooms/{room_id}/states/{state_key}",
      "realtime_claim_allowed": false
    }
  }
}
```

Client rules:

- SSE receives events only; updates go through HTTP;
- polling should use explicit interval/backoff;
- polling UI must not claim realtime behavior.

## Reconnect/latest-fetch profile / reconnect profile

Recommended section:

```json
{
  "reconnect": {
    "strategy": "backoff_resubscribe_latest_fetch",
    "steps": [
      "detect_disconnect_or_heartbeat_timeout",
      "reconnect_with_backoff",
      "resubscribe_rooms",
      "fetch_latest_revision",
      "fetch_latest_state_when_revision_changed_or_unknown"
    ],
    "event_replay_required": false
  }
}
```

Client rules:

- missed events are expected during disconnect;
- latest REST state/revision is the authority after reconnect;
- event replay is not required in v1.

## Validation profile / validation profile

Recommended section:

```json
{
  "validation": {
    "required_checks": [
      "join_room_by_invite_discards_raw_token_after_join",
      "subscribe_requires_authenticated_session",
      "state_update_sends_expected_revision",
      "stale_revision_fetches_latest_state",
      "heartbeat_timeout_reconnects",
      "reconnect_resubscribes_and_fetches_latest",
      "membership_loss_unsubscribes_room",
      "polling_does_not_claim_realtime",
      "packet_contains_no_tokens_or_secrets"
    ]
  }
}
```

## Forbidden implicit actions / forbidden implicit actions

Recommended section:

```json
{
  "forbidden_actions": [
    "generate_client_sdk",
    "generate_react_source",
    "generate_flutter_source",
    "generate_react_native_source",
    "install_dependencies",
    "choose_token_storage",
    "persist_raw_invite_token",
    "enable_offline_sync",
    "claim_realtime_when_polling",
    "claim_crdt_or_game_loop_support"
  ]
}
```

Mtool or an AI agent must not perform these actions from the packet alone.
They require a separate explicit user decision and implementation plan.

## Completion definition / completion definition

RSS-9 is complete when:

- client packet shape is documented;
- room/state/realtime/fallback/reconnect flow is explicit;
- generated source/SDK boundary is explicit;
- forbidden implicit actions are listed;
- next implementation step is selected.

## Next / next

RSS-10 should decide whether to add a static/sample consumer fixture first or implement Mtool artifact emission directly.
