# Shared state sync contract first slice

## Status

`RSS_1_DONE`

## Purpose

Promote the room/shared-state sync roadmap into a durable contract document before generating schema/API/realtime packets.

## Output

Added:

```text
docs/shared-state-sync-contract.md
```

The contract defines:

- ownership boundary between Mtool, app/backend, Node.js sync server, and app client;
- SSO/app user/invite token/room membership/sync session identity model;
- room boundary;
- membership boundary;
- shared state boundary;
- event boundary;
- v1 conflict policy;
- WebSocket-first transport boundary;
- validation checklist;
- non-goals.

## Boundary

This slice does not implement:

- DB schema generation;
- REST API contract generation;
- WebSocket payload generation;
- Node.js sync server input packet;
- app client input packet;
- reference sample;
- production realtime infrastructure.

Those remain RSS-2 through RSS-7.

## Next

Proceed to RSS-2 Schema/API contract.
