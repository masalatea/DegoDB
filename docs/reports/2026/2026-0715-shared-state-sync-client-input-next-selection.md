# 2026-0715 Shared State Sync Client Input Next Selection

## Status

`RSS_10_DONE_STATIC_FIXTURE_FIRST`

## Purpose

Choose the next implementation route after defining the app client input packet contract.

## Options considered

### Option A: implement Mtool artifact emission directly

This would add generation for:

- `sync-client-input.json`;
- `SYNC-CLIENT-INPUT.md`;
- bundle key `shared_state_sync_client_input`.

Pros:

- moves directly toward product output;
- mirrors the already implemented server input artifact emission.

Cons:

- client flow includes more app-owner choices than the server packet;
- source/SDK/UI boundaries are easy to blur;
- without a consumer fixture, it is harder to prove the packet is readable by external app builders without implying source generation.

### Option B: add static/sample consumer fixture first

This would create a small checked-in fixture/sample that reads a representative `sync-client-input.json` without generating app source or installing dependencies.

Pros:

- proves the client packet is consumable before Mtool emits it;
- keeps SDK/source/UI generation out of scope;
- makes room/state/realtime/fallback/reconnect expectations concrete;
- matches the safer path used for the Node.js server input packet.

Cons:

- one additional slice before Mtool emission.

## Decision

Choose Option B: static/sample consumer fixture first.

Reason:

The app client packet is more likely than the server packet to be misread as permission to generate SDKs, React/Flutter/React Native source, or token storage choices.
A static fixture lets us validate the packet contract and forbidden-action boundary before adding product emission.

## Next

RSS-11 should add a static/sample consumer fixture for `sync-client-input.json`.

Expected scope:

- representative `sync-client-input.sample.json`;
- static validation script;
- no package install;
- no generated SDK;
- no React/Flutter/React Native source;
- no SSO provider setup;
- no offline sync implementation;
- no WebSocket runtime.
