# 2026-0715 Shared State Sync Server Input Next Selection

## Status

`RSS_5_DONE_STATIC_FIXTURE_FIRST`

## Purpose

Choose the next bounded step after defining the Node.js sync server input packet.

## Options considered

### Option A: implement Mtool artifact emission now

This would add a real Mtool artifact such as:

- `sync-server-input.json`;
- `SYNC-SERVER-INPUT.md`;
- bundle manifest entry.

Pros:

- moves directly toward product output;
- matches the long-term goal that Mtool emits contracts and handoff packets.

Cons:

- implementation would be based only on docs, without a consumer fixture proving the packet is readable and sufficient;
- risk of encoding fields too early before checking external Node.js ownership needs;
- validation would likely be shallow unless a sample exists.

### Option B: add static/sample consumer fixture first

This would create a small checked-in fixture/sample that consumes a representative `sync-server-input.json` without running a production Node.js server.

Pros:

- validates packet shape before Mtool emission;
- keeps production server/runtime out of scope;
- gives Codex/Claude/human implementers a concrete example;
- follows the sample-first approach already used for larger backend/database additions;
- reduces the chance that Mtool generates an artifact that external owners cannot use directly.

Cons:

- one extra slice before product emission;
- still does not prove runtime WebSocket behavior.

## Decision

Choose Option B: static/sample consumer fixture first.

Reason:

The shared-state sync lane is a cross-runtime feature.
The important boundary is not just "Mtool can write JSON", but "an external Node.js owner can read the packet, understand backend authority, routes, auth, events, fallback, validation, and forbidden actions without guessing".

Therefore the next slice should prove the packet is consumable before adding Mtool artifact emission.

## Next

RSS-6 should add a static/sample consumer fixture for `sync-server-input.json`.

Expected scope:

- representative `sync-server-input.sample.json`;
- a small static validation script or documentation check;
- no dependency install;
- no Node.js server startup;
- no WebSocket runtime;
- no production process/port;
- no SSO provider implementation;
- report and current-plan update.
