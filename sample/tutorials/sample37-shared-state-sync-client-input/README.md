# sample37-shared-state-sync-client-input

`sample37` proves that an external app client owner can consume a representative Mtool `sync-client-input.json` packet without generating SDKs or app source.

This sample is intentionally static.
It does not install dependencies, generate React/Flutter/React Native source, choose token storage, start WebSocket runtime, or implement SSO provider setup.

## Scope

Covered Mtool handoff surface:

- `sync-client-input.sample.json` import;
- schema version and contract references;
- backend/auth authority boundary;
- room create/list/join flow;
- state read/update/latest-revision flow;
- WebSocket subscribe/update/ping flow;
- SSE + HTTP fallback;
- polling fallback;
- reconnect/latest-fetch behavior;
- validation checklist;
- forbidden implicit actions.

Non-goals:

- generated client SDK;
- generated React, Flutter, or React Native source;
- dependency installation;
- token storage choice;
- SSO/OIDC provider client setup;
- production offline sync;
- CRDT/OT or game-loop support.

## Mandatory validation

This gate uses only Node.js built-ins.

```bash
node sample/tutorials/sample37-shared-state-sync-client-input/scripts/validate-sample.mjs
```

The validation checks that the fixture is implementation-ready but source-neutral:

- backend remains authoritative for session, membership, state, and conflict policy;
- raw invite token is not persisted after join;
- expected revision is required for update;
- WebSocket is primary transport;
- polling does not claim realtime behavior;
- reconnect uses resubscribe + latest-fetch;
- forbidden implicit actions block SDK/source generation, dependency install, token storage choice, offline sync, and unsupported realtime claims.

## Expected output

```json
{
  "ok": true,
  "sample": "sample37-shared-state-sync-client-input",
  "schema_version": "shared_state_sync_client_input.v1"
}
```
