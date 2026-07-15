# 2026-0715 Shared State Sync Server Input Static Fixture

## Status

`RSS_6_DONE`

## Purpose

Prove that the RSS-4 `sync-server-input.json` shape is consumable by an external Node.js sync-server owner before implementing Mtool artifact emission.

## Output

Added `sample36-shared-state-sync-server-input`.

Files:

- `sample/tutorials/sample36-shared-state-sync-server-input/README.md`
- `sample/tutorials/sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json`
- `sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs`

## Validation

Command:

```bash
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
```

Validation checks:

- schema version;
- RSS-1/RSS-2/RSS-3/RSS-4 contract references;
- external Node.js runtime ownership;
- backend/app server authority;
- WebSocket/SSE/HTTP/polling route map;
- auth/session and room authorization boundary;
- state update/conflict boundary;
- room-scoped event fan-out and latest-fetch reconnect behavior;
- validation checklist;
- forbidden implicit actions.

## Boundary

This slice intentionally does not:

- install dependencies;
- create `package.json`;
- create production server source;
- start a Node.js process;
- open a public port;
- implement WebSocket/SSE runtime;
- implement SSO provider verification;
- claim Redis/pubsub, queue, guaranteed replay, CRDT/OT, or game-loop support.

## Decision

The packet shape is concrete enough for a later Mtool emission slice.
The next step can implement artifact generation, because the external-owner consumption contract now has a static fixture and validation gate.

## Next

RSS-7 should implement Mtool artifact emission for:

- `sync-server-input.json`;
- `SYNC-SERVER-INPUT.md`;
- bundle manifest key such as `shared_state_sync_server_input`;
- focused validation that emitted artifact keeps the RSS-6 boundary.
