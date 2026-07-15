# Next product slice selection after mobile external output

## Status

`EF_M18_DONE_RSS_PROMOTED`

## Purpose

Choose the next scoped product slice after the mobile external output stack was merged through `develop` and `master`.

## Candidate review

Current active mobile/app output work is complete for the recent scope:

- React Native second-pass metadata;
- PWA + Flutter WebView + React/Web Capacitor app surface config;
- Flutter WebView wrapper metadata;
- durable docs and PR merge cleanup.

The next useful product direction should not continue mobile wrapper work merely because it is nearby.
It should open a new bounded lane.

## Decision

Promote the room/shared-state sync roadmap to the next active lane.

Reasoning:

- app surfaces now have clearer web/PWA/native-wrapper output boundaries;
- the earlier roadmap already defined a separate Node.js sync server as the runtime owner;
- Mtool can add value by emitting contracts/input packets without owning production realtime infrastructure;
- this directly addresses the previously discussed use case: authenticated users joining a room/group and sharing synchronized state.

## Selected lane

`RSS` = Room/shared-state sync with separate Node.js server.

Initial sequence:

| Step | Work unit | Exit condition |
| --- | --- | --- |
| RSS-1 | Shared state sync contract | Room/membership/state/event boundary, token model, conflict policy, and non-goals are documented. |
| RSS-2 | Schema/API contract | DB schema and REST API contract are defined for room join/read/update. |
| RSS-3 | Realtime event contract | WebSocket-first event/command payloads, heartbeat, reconnect/latest behavior, and SSE/HTTP fallback profile are defined. |
| RSS-4 | Node.js server input packet | Mtool emits server-facing input packet without running production Node.js. |
| RSS-5 | App client input packet | Mtool emits app-facing input packet for join/subscribe/update. |
| RSS-6 | Reference sample | Two-client room sync proof passes with authorization checks. |
| RSS-7 | Validation and boundary review | Unsafe token sharing, unauthorized subscribe, stale update, and reconnect behavior are verified. |

## Boundary

RSS work must not imply:

- sharing SSO/OIDC tokens between users;
- Mtool owning production WebSocket/SSE infrastructure;
- Redis/pubsub/scaling implementation;
- mobile push notification support;
- CRDT/OT or game-loop runtime;
- offline sync unless separately contracted.

## Next

Start with `RSS-1 Shared state sync contract`.
This is a contract/documentation slice before code generation.
