# 2026-0716 Shared-State Sync HTTP/SSE Reference First Slice

## Summary

Added a loopback-only HTTP/SSE fallback reference to `sample38-shared-state-sync-node-runtime`.

This is the second runtime-shaped slice after the in-process reference runtime.

## What changed

- Added `src/shared-state-sync-http-server.mjs`.
- Added `scripts/validate-http-sse-sample.mjs`.
- Extended the runtime core with membership-aware read/latest helpers.
- Updated sample and validation documentation.

## Covered behavior

The validator starts a temporary `127.0.0.1` server on an ephemeral port and closes it before exit.

It validates:

- member HTTP read;
- non-member HTTP read rejection;
- viewer HTTP update rejection;
- editor HTTP update success;
- stale revision returns HTTP conflict;
- latest revision endpoint;
- SSE `state.updated` event delivery;
- SSE payload does not contain SSO token, refresh token, raw invite token, or secret.

## Boundary

This is still not a production Node.js server.

It does not:

- install npm dependencies;
- open a public port;
- run a real WebSocket server;
- implement SSO/OIDC setup;
- choose token storage;
- generate client SDK or app source;
- claim Redis/pubsub, durable replay, CRDT/OT, or game-loop support.

## Validation

```bash
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-http-sse-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
git diff --check
```

All passed for this slice.

## Next decision

Choose whether to:

- add a real WebSocket transport sample;
- link sample38 directly to generated Mtool artifacts;
- create a production-hardening checklist;
- or checkpoint/PR before widening scope.
