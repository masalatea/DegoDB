# 2026-0715 Shared State Sync Node Server Input Packet

## Status

`RSS_4_DONE`

## Purpose

Define the Mtool-emitted input packet for a separate Node.js shared-state sync server, without generating or running the production server.

## Output

`docs/shared-state-sync-node-server-input-packet.md`

Defines:

- `sync-server-input.json` and `SYNC-SERVER-INPUT.md` artifact names;
- bundle manifest key recommendation;
- packet shape and `schema_version`;
- RSS-1/RSS-2/RSS-3 contract references;
- backend integration authority;
- WebSocket/SSE/HTTP/polling route map;
- auth/session profile;
- room subscription profile;
- state update profile;
- event fan-out profile;
- fallback profile;
- validation profile;
- forbidden implicit actions.

## Boundary

This slice does not implement:

- production Node.js server source;
- dependency install or project initialization;
- process manager / Docker / hosting / TLS / load balancer / Redis / queue;
- client SDK;
- app-specific SSO provider code;
- CRDT/OT, game-loop authority, or guaranteed event replay.

## Decision

Mtool should own the contract and the input packet shape.
The separate Node.js sync server remains an external runtime owner unless a later scoped implementation explicitly changes that.

The packet is intentionally implementation-ready but execution-neutral:

- useful for Codex/Claude/human/external framework implementation;
- safe to emit before a server exists;
- not enough authority to open ports, install dependencies, or claim production readiness.

## Next

Proceed to RSS-5 selection:

1. implement Mtool artifact emission for `sync-server-input.json`; or
2. first add a sample/static fixture that proves an external Node.js owner can consume the packet shape without running a production server.
