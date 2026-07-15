# sample36-shared-state-sync-server-input

`sample36` proves that an external Node.js sync-server owner can consume a representative Mtool `sync-server-input.json` packet without running a production server.

This sample is intentionally static.
It does not install dependencies, initialize a Node.js project, open a port, run WebSocket/SSE runtime, or implement an SSO provider.

## Scope

Covered Mtool handoff surface:

- `sync-server-input.sample.json` import;
- schema version and contract references;
- backend integration authority;
- WebSocket/SSE/HTTP/polling route map;
- auth/session boundary;
- room subscription boundary;
- state update boundary;
- event fan-out boundary;
- fallback profile;
- validation checklist;
- forbidden implicit actions.

Non-goals:

- generating a production Node.js sync server;
- installing npm dependencies;
- starting a server process;
- opening a public port;
- implementing Redis/pubsub/queue/scaling;
- implementing SSO/OIDC provider verification;
- claiming guaranteed event replay, CRDT/OT, or game-loop support.

## Mandatory validation

This gate uses only Node.js built-ins.

```bash
node sample/tutorials/sample36-shared-state-sync-server-input/scripts/validate-sample.mjs
```

The validation checks that the fixture is implementation-ready but execution-neutral:

- it references RSS-1/RSS-2/RSS-3 contracts;
- WebSocket is the primary transport;
- SSE and polling are fallback profiles;
- backend/app server remains the authority for identity, membership, state, and conflict policy;
- forbidden implicit actions block dependency install, server startup, public port opening, token broadcast, cross-room broadcast, and unsupported realtime claims.

## Expected output

```json
{
  "ok": true,
  "sample": "sample36-shared-state-sync-server-input",
  "schema_version": "shared_state_sync_server_input.v1"
}
```
