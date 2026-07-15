# Shared State Sync Node Server Input Packet / shared state sync Node server input packet

English companion:
This document defines the v1 Mtool-emitted input packet for a separate Node.js shared-state sync server.
It builds on [Shared State Sync Contract](shared-state-sync-contract.md), [Shared State Sync Schema/API Contract](shared-state-sync-schema-api-contract.md), and [Shared State Sync Realtime Contract](shared-state-sync-realtime-contract.md).

この文書は、別 runtime として動く Node.js shared-state sync server に Mtool が渡す v1 input packet を定義する。
[Shared State Sync Contract](shared-state-sync-contract.md)、[Shared State Sync Schema/API Contract](shared-state-sync-schema-api-contract.md)、[Shared State Sync Realtime Contract](shared-state-sync-realtime-contract.md) を前提にする。

## Scope / scope

RSS-4 covers:

- `sync-server-input.json` packet shape;
- references to RSS-1/RSS-2/RSS-3 contracts;
- backend integration requirements;
- WebSocket/SSE/polling route map;
- room/state/event authority boundaries;
- validation checklist for a generated or external Node.js server;
- forbidden implicit actions.

RSS-4 does not cover:

- production Node.js server source generation;
- dependency installation;
- process manager, Docker, hosting, TLS, load balancer, Redis/pubsub, queue, or observability setup;
- client SDK generation;
- app-specific SSO provider implementation;
- CRDT/OT, game-loop authority, or guaranteed event replay.

## Product position / product position

Mtool emits a server-facing packet.
The packet is an input to an external owner, coding agent, or future generator.

```text
Mtool design / metadata
  -> shared-state sync contracts
  -> sync-server-input.json
  -> external Node.js sync server implementation
```

The Node.js sync server may be implemented by Codex/Claude/human developer/external framework, but the packet must make the boundary obvious before implementation starts.

## Artifact name / artifact name

Recommended artifact names:

- `sync-server-input.json`
- `SYNC-SERVER-INPUT.md`

Recommended bundle key:

```json
{
  "shared_state_sync_server_input": {
    "json": "sync-server-input.json",
    "markdown": "SYNC-SERVER-INPUT.md"
  }
}
```

## Mtool CLI emission / Mtool CLI emission

Mtool can emit the packet as a controlled artifact:

```bash
php mtool/scripts/create_shared_state_sync_server_input.php \
  --project-key=PROJECT \
  --backend-base-url-env=APP_BACKEND_BASE_URL \
  --target-dir=work/source-outputs/PROJECT/SHARED-STATE-SYNC-SERVER-INPUT
```

Generated files:

- `sync-server-input.json`;
- `SYNC-SERVER-INPUT.md`.

The command refuses an invalid target directory and refuses to overwrite existing files.

The CLI does not:

- install Node.js dependencies;
- initialize a Node.js project;
- start a server;
- open a public port;
- implement SSO/OIDC provider verification;
- claim Redis/pubsub, queue, guaranteed replay, CRDT/OT, or game-loop support.

Focused validation:

```bash
docker compose run --rm web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SharedStateSyncServerInputTest.php
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
git diff --check
```

## Packet shape / packet shape

Minimum packet:

```json
{
  "schema_version": "shared_state_sync_server_input.v1",
  "generated_by": {
    "tool": "mtool",
    "artifact": "shared_state_sync_server_input"
  },
  "contracts": {
    "shared_state_sync_contract": "docs/shared-state-sync-contract.md",
    "schema_api_contract": "docs/shared-state-sync-schema-api-contract.md",
    "realtime_contract": "docs/shared-state-sync-realtime-contract.md"
  },
  "server": {
    "runtime": "nodejs",
    "ownership": "external_runtime_owner",
    "transport_profiles": ["websocket", "sse_http", "polling"]
  },
  "backend_integration": {},
  "routes": {},
  "auth": {},
  "rooms": {},
  "state": {},
  "events": {},
  "fallbacks": {},
  "validation": {},
  "forbidden_actions": []
}
```

Rules:

- `schema_version` must be stable and explicit;
- `contracts` should identify the source contracts consumed by this packet;
- `ownership` must not imply Mtool operates the production Node.js process;
- missing required sections should fail validation.

## Backend integration / backend integration

The Node.js sync server should not become the source of truth for app identity or room authorization unless explicitly selected by the application owner.

Recommended section:

```json
{
  "backend_integration": {
    "authority": "app_backend",
    "base_url_env": "APP_BACKEND_BASE_URL",
    "auth_context": {
      "app_user_id_source": "verified_backend_session",
      "sso_token_broadcast_allowed": false
    },
    "required_backend_operations": [
      "verify_session",
      "check_room_membership",
      "read_state",
      "update_state",
      "read_latest_revision",
      "record_event"
    ],
    "conflict_policy": "reject_stale_revision"
  }
}
```

Rules:

- app/backend remains the authority for SSO verification;
- room membership must be checked server-side;
- update authorization must reuse RSS-2 rules;
- raw SSO/OIDC tokens and refresh tokens must not be broadcast through sync events;
- conflict policy must match the app/backend behavior.

## Route map / route map

Recommended route section:

```json
{
  "routes": {
    "websocket": {
      "path": "/sync/ws",
      "enabled": true,
      "commands": ["room.subscribe", "room.unsubscribe", "state.update", "ping"],
      "events": ["state.updated", "membership.changed", "room.closed", "heartbeat", "reconnect.required"]
    },
    "sse": {
      "path": "/sync/rooms/{room_id}/events",
      "enabled": true,
      "server_to_client_only": true
    },
    "http_update": {
      "path": "/sync/rooms/{room_id}/states/{state_key}",
      "method": "PUT",
      "authority": "app_backend_or_shared_authority"
    },
    "polling": {
      "state_path": "/sync/rooms/{room_id}/states/{state_key}",
      "revision_path": "/sync/rooms/{room_id}/states/{state_key}/revision"
    }
  }
}
```

Rules:

- WebSocket is the primary transport;
- SSE is server-to-client only;
- HTTP update and WebSocket `state.update` must share authorization/conflict authority;
- polling is fallback and should not claim realtime UX.

## Auth and session profile / auth profile

Recommended section:

```json
{
  "auth": {
    "required": true,
    "session_verification": "delegate_to_app_backend",
    "connection_identity": "app_user_id",
    "room_authorization": "active_membership_required",
    "forbidden_in_events": [
      "sso_token",
      "refresh_token",
      "raw_invite_token",
      "secret"
    ]
  }
}
```

Rules:

- authenticated connection is required;
- connection identity must resolve to `app_user_id`;
- subscribe/read/update authority comes from active room membership and role;
- client-provided room ID is never sufficient authority.

## Room subscription profile / room subscription profile

Recommended section:

```json
{
  "rooms": {
    "subscription_command": "room.subscribe",
    "unsubscribe_command": "room.unsubscribe",
    "membership_required": true,
    "subscribe_result_includes_latest_revision_summary": true,
    "cross_room_broadcast_allowed": false
  }
}
```

Rules:

- subscribe checks active membership;
- unsubscribe is connection-local and does not change membership;
- different rooms must not receive each other's events;
- room closure should send `room.closed` or `reconnect.required` as appropriate.

## State update profile / state update profile

Recommended section:

```json
{
  "state": {
    "update_command": "state.update",
    "http_update_method": "PUT",
    "state_body_type": "json",
    "expected_revision_required": true,
    "accepted_update_event": "state.updated",
    "large_binary_payload_allowed": false
  }
}
```

Rules:

- state body must be JSON-serializable;
- accepted update increments revision;
- accepted update emits `state.updated`;
- stale update behavior must match `conflict_policy`;
- large binary payloads are out of scope.

## Event fan-out profile / event fan-out profile

Recommended section:

```json
{
  "events": {
    "envelope": "shared_state_sync_realtime_event.v1",
    "fanout_scope": "room",
    "delivery_guarantee": "best_effort_realtime_plus_latest_fetch",
    "replay_required": false,
    "dedupe_fields": ["message_id", "room_id", "state_key", "revision"],
    "heartbeat": {
      "enabled": true,
      "event_type": "heartbeat",
      "timeout_action": "reconnect_and_latest_fetch"
    },
    "reconnect": {
      "event_type": "reconnect.required",
      "client_action": "reconnect_resubscribe_latest_fetch"
    }
  }
}
```

Rules:

- v1 realtime delivery is best-effort;
- latest REST state/revision is authority after reconnect;
- event history can exist for audit/diagnostics, but v1 does not require guaranteed replay;
- event payloads must not include secrets.

## Fallback profile / fallback profile

Recommended section:

```json
{
  "fallbacks": {
    "sse_http": {
      "enabled": true,
      "event_stream": "/sync/rooms/{room_id}/events",
      "update_path": "/sync/rooms/{room_id}/states/{state_key}",
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

Rules:

- fallback routes use the same app/backend authority;
- polling should use backoff or explicit interval policy;
- fallback mode must still protect room boundary.

## Validation profile / validation profile

Recommended section:

```json
{
  "validation": {
    "required_checks": [
      "authenticated_member_can_subscribe",
      "non_member_cannot_subscribe",
      "viewer_cannot_update",
      "editor_can_update",
      "accepted_update_emits_state_updated",
      "other_room_does_not_receive_event",
      "stale_revision_returns_conflict_when_enabled",
      "heartbeat_timeout_triggers_reconnect",
      "reconnect_fetches_latest_state",
      "events_do_not_contain_tokens_or_secrets"
    ],
    "implementation_required_before_production": true
  }
}
```

Rules:

- the packet may be emitted before implementation exists;
- production use requires server implementation and validation evidence;
- validation should include both authorization and event isolation.

## Forbidden implicit actions / forbidden implicit actions

Recommended section:

```json
{
  "forbidden_actions": [
    "install_node_dependencies",
    "initialize_node_project",
    "start_production_server",
    "open_public_port",
    "store_raw_sso_token",
    "broadcast_sso_token",
    "enable_cross_room_broadcast",
    "claim_guaranteed_event_replay",
    "claim_crdt_or_game_loop_support"
  ]
}
```

Mtool or an AI agent must not perform these actions from the packet alone.
They require a separate explicit user decision and implementation plan.

## Markdown companion / Markdown companion

`SYNC-SERVER-INPUT.md` should summarize:

- which contracts are consumed;
- which routes are expected;
- which backend operations must exist;
- which authority remains outside the Node.js sync server;
- which validation checks are required;
- which actions are forbidden without explicit approval.

## Completion definition / completion definition

RSS-4 is complete when:

- the packet schema is documented;
- route/auth/state/event/fallback/validation sections are defined;
- the Mtool vs Node.js runtime ownership boundary is explicit;
- forbidden implicit actions are listed;
- current plan points to the next implementation slice.

## Next / next

RSS-5 should decide whether to implement the artifact emission in Mtool now, or first add a sample/static fixture that proves an external Node.js owner can consume the packet shape without running a production server.
