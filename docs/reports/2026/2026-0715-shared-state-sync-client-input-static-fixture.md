# 2026-0715 Shared State Sync Client Input Static Fixture

## Status

`RSS_11_DONE`

## Purpose

Prove that the RSS-9 `sync-client-input.json` shape is consumable by an external app client owner before implementing Mtool artifact emission.

## Output

Added `sample37-shared-state-sync-client-input`.

Files:

- `sample/tutorials/sample37-shared-state-sync-client-input/README.md`
- `sample/tutorials/sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json`
- `sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs`

## Validation

Command:

```bash
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
```

Validation checks:

- schema version;
- RSS contract references;
- external app client ownership;
- no source/SDK generation claim;
- backend/app authority;
- room join behavior and raw invite token disposal;
- state update with expected revision;
- WebSocket primary transport;
- SSE/HTTP and polling fallback;
- reconnect/latest-fetch behavior;
- forbidden implicit actions.

## Boundary

This slice intentionally does not:

- install dependencies;
- create `package.json`;
- create generated app source;
- choose token storage;
- implement SSO/OIDC provider client setup;
- start WebSocket runtime;
- implement offline sync.

## Decision

The client packet shape is concrete enough for a later Mtool emission slice.
The next step can implement artifact generation, because the external app owner consumption contract now has a static fixture and validation gate.

## Next

RSS-12 should implement Mtool artifact emission for:

- `sync-client-input.json`;
- `SYNC-CLIENT-INPUT.md`;
- bundle manifest key `shared_state_sync_client_input`;
- focused validation that emitted artifact keeps the RSS-9/RSS-11 boundaries.
