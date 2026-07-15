# 2026-0715 Shared State Sync Client Input Contract

## Status

`RSS_9_DONE`

## Purpose

Define the app-client-facing input packet for room join, state read/update, realtime subscribe, fallback, and reconnect/latest-fetch behavior.

## Output

Added:

- `docs/shared-state-sync-app-client-input-packet.md`

Defines:

- `sync-client-input.json`;
- `SYNC-CLIENT-INPUT.md`;
- bundle key `shared_state_sync_client_input`;
- backend/auth authority boundary;
- room flow;
- state flow;
- WebSocket realtime flow;
- SSE/HTTP and polling fallback;
- reconnect/latest-fetch profile;
- validation checklist;
- forbidden implicit actions.

## Boundary

This slice does not implement:

- generated SDK;
- generated React/Flutter/React Native source;
- UI components;
- production offline sync;
- CRDT/OT or game-loop support;
- SSO/OIDC provider client setup.

## Decision

Client packet is a handoff artifact, not a generated app.
It should help app creators and AI/external framework consumers wire the sync behavior while keeping secure token storage, app source, SDK, UI, and offline behavior outside the implicit Mtool scope.

## Next

RSS-10 should decide the implementation route:

1. static/sample consumer fixture first; or
2. Mtool artifact emission directly.

Given the Node.js server packet path, static/sample fixture first is likely the safer default unless the packet remains too small to justify the extra slice.
