# Shared state sync realtime contract

## Status

`RSS_3_DONE`

## Purpose

Define WebSocket-first realtime event/command payloads, heartbeat, reconnect/latest-fetch behavior, and SSE/HTTP fallback profile before generating Node.js sync server input packets.

## Output

Added:

```text
docs/shared-state-sync-realtime-contract.md
```

The document defines:

- WebSocket primary transport profile;
- SSE + HTTP POST fallback;
- HTTP polling fallback;
- server event envelope;
- client command envelope;
- command result and error envelopes;
- `state.updated`, `membership.changed`, `room.closed`, `heartbeat`, `reconnect.required`;
- `room.subscribe`, `room.unsubscribe`, `state.update`, `ping`;
- reconnect/latest-fetch behavior;
- validation expectations.

## Boundary

This slice does not implement:

- production WebSocket server;
- Node.js sync server input packet emission;
- app client packet emission;
- Redis/pubsub/scaling;
- event replay guarantees;
- CRDT/OT.

## Next

Proceed to RSS-4 Node.js sync server input packet.
