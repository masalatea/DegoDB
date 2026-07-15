# Sample 38: Shared-State Sync Node Runtime Reference

This sample proves the first runtime-shaped slice for shared-state sync without claiming production server ownership.

It consumes the existing handoff packets from:

- `sample36-shared-state-sync-server-input/reference/sync-server-input.sample.json`
- `sample37-shared-state-sync-client-input/reference/sync-client-input.sample.json`

## What this sample does

- Loads the server/client input packets.
- Creates a tiny in-memory Node.js reference runtime.
- Validates room membership before subscribe/update.
- Validates editor-only update authority.
- Requires `expected_revision`.
- Rejects stale updates.
- Emits `state.updated` only to subscribers in the same room.
- Supports latest-state fetch after reconnect or stale revision.
- Verifies emitted events contain no SSO token, refresh token, raw invite token, or secret.
- Provides a loopback-only HTTP/SSE fallback reference using Node.js standard library.

## What this sample intentionally does not do

This sample does not install dependencies, initialize a production Node.js project, open a public port, run a real WebSocket server, implement SSO/OIDC provider setup, generate a client SDK, store tokens, or claim durable replay / Redis pubsub / CRDT / game-loop support.

The realtime transport is represented by an in-process event bus. That keeps the sample small and deterministic while preserving the important product boundary: Mtool can define and validate the sync contract shape, while production Node.js deployment remains a later explicit scope.

The HTTP/SSE slice starts a temporary `127.0.0.1` server inside the validator and closes it before exit. It is a reference for fallback route behavior, not a production server or public listener.

## Validate

```bash
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-sample.mjs
node sample/tutorials/sample38-shared-state-sync-node-runtime/scripts/validate-http-sse-sample.mjs
```

Expected result:

```json
{
  "ok": true,
  "sample": "sample38-shared-state-sync-node-runtime"
}
```
