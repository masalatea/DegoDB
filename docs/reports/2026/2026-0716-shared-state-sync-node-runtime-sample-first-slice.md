# 2026-0716 Shared-State Sync Node Runtime Sample First Slice

## Summary

Added `sample38-shared-state-sync-node-runtime` as the first runtime-shaped Node.js sample for shared-state sync.

This sample consumes:

- `sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json`
- `sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json`

## What changed

- Added a dependency-free Node.js reference runtime under `sample/tutorials/sample38-shared-state-sync-node-runtime/src/`.
- Added a validator under `sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/`.
- Updated sample tutorial docs and shared-state validation checklist to include sample38.
- Promoted the current plan from product-scope selection to shared-state sync runtime integration.

## Covered behavior

The first slice validates:

- member subscribe;
- non-member subscribe rejection;
- viewer update rejection;
- editor update success;
- required expected revision;
- stale revision rejection with latest state;
- same-room event fanout;
- no cross-room event fanout;
- reconnect/latest-fetch behavior;
- secret-free event and audit payloads.

## Boundary

The sample uses an in-process event bus to represent the WebSocket-style boundary.

It does not:

- install dependencies;
- initialize a production Node.js project;
- open a public port;
- run a real WebSocket server;
- implement SSO/OIDC setup;
- choose token storage;
- generate SDK or app source;
- claim Redis/pubsub, durable replay, CRDT/OT, or game-loop support.

## Validation

```bash
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
git diff --check
```

All passed for this first slice.

## Next decision

Select the next runtime slice:

- real WebSocket transport sample;
- HTTP/SSE fallback sample;
- Mtool artifact linkage from generated output to sample38;
- or checkpoint/PR before widening the scope.
